<?php

namespace Modules\Coupons\Http\Controllers;

use Platform\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use Kris\LaravelFormBuilder\FormBuilder;
use Kris\LaravelFormBuilder\FormBuilderTrait;

use Modules\Coupons\Forms\Coupon as CouponForm;

class CouponController extends Controller {

  use FormBuilderTrait;

  /**
   * Get coupon (front-end)
   */
  public function getCoupon($slug) {
    $coupon = \Modules\Coupons\Models\Coupon::where('slug', $slug)->where('active', 1)->first();

    if ($coupon === null) {
      abort(404);
    }

    // Set id hash
    $coupon_hash = Core\Secure::staticHash($coupon->id);

    // Check if c(oupon)v(iewed) cookie is set
    $viewed = (bool) \Cookie::get('cv-' . $coupon_hash);
    if (! $viewed) {
      // Increment views
      $coupon->increment('views');

      // Set cookie
      \Cookie::queue('cv-' . $coupon_hash, 1, 60 * 24 * 365);
    }

    // Set language
    if ($coupon->language !== null) {
      app()->setLocale($coupon->language);
    }

    // Description that fits one line for html tags
    $description = Core\Helpers::parseDescription($coupon->content);

    // Check if c(oupon) cookie is set
    $redeemed = (bool) \Cookie::get('c-' . $coupon_hash);

    // Get form
    $form = $this->generateForm([
      'url' => url('coupon/redeem/' . $coupon->slug),
      'slug' => $coupon->slug,
      'locale' => $coupon->language,
      'form_fields' => json_encode($coupon->form_fields),
      'submit_button' => $coupon->additional_fields['submit_button'] ?? trans('coupons::g.redeem')
    ]);

    // General settings
    $ga_code = '';
    $fb_pixel = '';

    // Check if Pusher is configured
    $pusher_configured = (config('broadcasting.connections.pusher.key') == '') ? false : true;

    return view('coupons::front.view-coupon', compact('coupon', 'form', 'description', 'coupon_hash', 'redeemed', 'pusher_configured', 'ga_code', 'fb_pixel'));
  }

  /**
   * Post redeem coupon (front-end)
   */
  public function postRedeemCoupon($slug) {
    $coupon = \Modules\Coupons\Models\Coupon::where('slug', $slug)->where('active', 1)->first();

    if ($coupon === null) {
      abort(404);
    }

    $pusher_channel = 'coupon_' . uniqid();
    $post = request()->except('_token');
    $post['pc'] = $pusher_channel;
    $post = http_build_query($post);

    $redeem_url = url('coupon/verify/' . $slug . '/?' . $post);

    return view('coupons::front.redeem-coupon', compact('coupon', 'redeem_url', 'pusher_channel'));
  }

	/**
	 * Show coupon redeemed
	 */
	public function getCouponRedeemed($slug) {
    $coupon = \Modules\Coupons\Models\Coupon::where('slug', $slug)->where('active', 1)->first();

    if ($coupon === null) {
      abort(404);
    }

    // Set id hash
    $coupon_hash = Core\Secure::staticHash($coupon->id);

    // Set c(oupon) cookie
    \Cookie::queue('c-' . $coupon_hash, 1, 60 * 24 * 365);

    // Set locale
    app()->setLocale($coupon->language);

    return view('coupons::front.redeemed', compact('slug', 'coupon'));
	}

	/**
	 * Verify redeemed coupon
	 */
	public function getVerifyCoupon($slug) {

    $coupon = \Modules\Coupons\Models\Coupon::where('slug', $slug)->where('active', 1)->first();

    if ($coupon === null) {
      abort(404);
    }

    // Set id hash
    $coupon_hash = Core\Secure::staticHash($coupon->id);

    // Get form
    $form = $this->generateForm([
      'url' => url('coupon/verify/' . $coupon->slug),
      'pc' => request()->get('pc', null),
      'slug' => $coupon->slug,
      'locale' => $coupon->language,
      'form_fields' => json_encode($coupon->form_fields),
      'submit_button' => $coupon->additional_fields['submit_button'] ?? trans('coupons::g.redeem')
    ]);

    // QR data
    $post = request()->all();
    $post = http_build_query($post);

    $redeem_url = url('coupon/verify/' . $slug . '/?' . $post);

    // Set locale
    app()->setLocale($coupon->language);

    return view('coupons::front.verify-coupon', compact('slug', 'coupon', 'redeem_url', 'form'));
	}

	/**
	 * Post coupon verification
	 */
	public function postVerifyCoupon($slug) {
    $coupon = \Modules\Coupons\Models\Coupon::where('slug', $slug)->where('active', 1)->first();

    if ($coupon === null) {
      abort(404);
    }

    // Set id hash
    $coupon_hash = Core\Secure::staticHash($coupon->id);

    $input = array(
      'redemption_code' => request()->get('redemption_code')
    );

    $rules = array(
      'redemption_code' => 'required'
    );

    $validator = \Validator::make($input, $rules);

    if ($input['redemption_code'] != $coupon->redemption_code) {
      $validator->getMessageBag()->add('redemption_code', trans('coupons::g.incorrect_redemption_code'));
    }

    // Form and model
    $form = $this->generateForm([
      'url' => '',
      'slug' => $coupon->slug,
      'locale' => $coupon->language,
      'form_fields' => json_encode($coupon->form_fields),
      'submit_button' => $coupon->additional_fields['submit_button'] ?? trans('coupons::g.redeem')
    ]);

    // Validate form
    if (! $form->isValid()) {
      return redirect()->back()->withErrors($form->getErrors())->withInput();
    }

    // Get form post
    $form_fields = $form->getFieldValues();

    array_forget($form_fields, [
      'form_fields', 
      'locale'
    ]);

    // Add lead
    $lead_source = $coupon->name;

    $user = \App\User::where('email', '=', $form_fields['email'])->where('lead_source', '=', $lead_source)->first();

    $form_fields['account_id'] = 1;
    $form_fields['locale'] = $coupon->language;
    $form_fields['lead_source'] = $lead_source;
    $form_fields['signup_ip_address'] = request()->ip();
    $form_fields['active'] = true;

    if ($user === null) {
      $user = \App\User::create($form_fields);

      // Assign lead role
      $lead_role = \App\Role::find(7);
      $user->assignRole($lead_role);

      // Add conversion
      $coupon->number_of_times_redeemed = $coupon->number_of_times_redeemed + 1;
      $coupon->last_redemption = \Carbon\Carbon::now('UTC');
      $coupon->save();
    } else {
      $validator->getMessageBag()->add('redemption_code', trans('coupons::g.email_already_exists'));
    }

    if(! empty($validator->errors()->all())) {
      return redirect()
        ->back()
        ->withErrors($validator)
        ->withInput();
    }

    // Push redemption
    $pusher_channel = request()->get('pc');
    $post = request()->except(['_token', 'redemption_code', 'pc']);

    $options = array(
      'cluster' => config('broadcasting.connections.pusher.options.cluster'),
      'useTLS' => true
    );

    $pusher = new \Pusher\Pusher(
      config('broadcasting.connections.pusher.key'),
      config('broadcasting.connections.pusher.secret'),
      config('broadcasting.connections.pusher.app_id'),
      $options
    );

    $data = [];
    $pusher->trigger($pusher_channel, 'redeemed', $data);

    // Set locale
    app()->setLocale($coupon->language);

    // coupons::front.verified 
    return view('coupons::front.redeemed', compact('slug', 'coupon', 'color'));
	}

  /**
   * Generate form
   */

  public function generateForm($form_data) {
    if ($form_data['form_fields'] !== null && is_string($form_data['form_fields'])) {
      $form_fields = json_decode(urldecode($form_data['form_fields']));
    } else {
      $form_fields = $form_data['form_fields'];
    }

    $form = \FormBuilder::plain([
      'method' => 'POST',
      'enctype' => 'multipart/form-data',
      'url' => $form_data['url'],
      'language_name' => 'g'
    ]);

    if ($form_data['slug'] === null) {
      $form->add('locale', 'textarea', [
        'label_show' => false,
        'value' => $form_data['locale'],
        'attr' => ['style' => 'display:none'],
      ]);

      $form->add('form_fields', 'textarea', [
        'label_show' => false,
        'value' => $form_data['form_fields'],
        'attr' => ['style' => 'display:none'],
      ]);
    }

    if (isset($form_data['pc']) && $form_data['pc'] !== null) {
      $form->add('pc', 'textarea', [
        'label_show' => false,
        'value' => $form_data['pc'],
        'attr' => ['style' => 'display:none'],
      ]);

      $form->add('redemption_code', 'text', [
        'label' => trans('coupons::g.redemption_code'),
        'rules' => 'required|min:1|max:128',
        'attr' => ['autofocus' => 1],
        'help_block' => [
          'text' => trans('coupons::g.redeem_coupon_code_help'),
          'tag' => 'small',
          'attr' => ['class' => 'text-muted mt-2']
        ]
      ]);
    }

    if ($form_fields !== null) {
      foreach ($form_fields as $i => $field) {
        $name = $field->field;
        $required = $field->required;
        $autofocus = ($i == 0) ? ['autofocus' => 1] : [];

        if ($name == 'email') {
          $form->add('email', 'email', [
            'label' => trans('g.email_address'),
            'value' => request()->get('email', null),
            'rules' => 'required|email',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'salutation') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('salutation', 'text', [
            'rules' => $required . '|min:1|max:32',
            'value' => request()->get('salutation', null),
            'attr' => array_merge($autofocus, ['placeholder' => trans('g.salutation_placeholder')])
          ]);
        }

        if ($name == 'name') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('name', 'text', [
            'label' => trans('g.full_name'),
            'value' => request()->get('name', null),
            'rules' => $required . '|min:2|max:32',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'phone') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('phone', 'text', [
            'value' => request()->get('phone', null),
            'rules' => $required . '|min:1|max:32',
            'prefix' => 'phone',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'website') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('website', 'text', [
            'value' => request()->get('website', null),
            'rules' => $required . '|min:1|max:250',
            'attr' => ['placeholder' => 'https://'],
            'prefix' => 'language',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'street') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('street1', 'text', [
            'value' => request()->get('street1', null),
            'label' => trans('g.street'),
            'rules' => $required . '|min:1|max:250',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'postal_code') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('postal_code', 'text', [
            'value' => request()->get('postal_code', null),
            'rules' => $required . '|min:1|max:32',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'city') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('city', 'text', [
            'value' => request()->get('city', null),
            'rules' => $required . '|min:1|max:64',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'state') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('state', 'text', [
            'value' => request()->get('state', null),
            'rules' => $required . '|min:1|max:64',
            'attr' => $autofocus
          ]);
        }

        if ($name == 'country') {
          $required = ($required == 1) ? 'required' : 'nullable';
          $form->add('country_code', 'select', [
            'rules' => $required,
            'value' => request()->get('country', null),
            'attr' => array_merge($autofocus, ['class' => 'custom-select']),
            'label' => trans('g.country'),
            'choices' => \Countries::getList(auth()->user()->getLanguage(), 'php'),
            'empty_value' => ' '
          ]);
        }
      }

      $form->add('submit', 'submit', [
        'attr' => ['class' => 'btn btn-custom-primary btn-lg btn-block rounded-0 mt-4'],
        'label' => $form_data['submit_button'],
      ]);
    }

    return $form;
  }

  /**
   * Get coupons list
   */
  public function getCouponList() {
    return view('coupons::list-coupons');
  }

  /**
   * Coupons list json
   */

  public function getCouponListJson() {
    // DataTables parameters
    $order_by = request()->input('order.0.column', 1);
    //$order_by--;
    $order = request()->input('order.0.dir', 'asc');
    $search = request()->input('search.regex', '');
    $q = request()->input('search.value', '');
    $start = request()->input('start', 0);
    $draw = request()->input('draw', 1);
    $length = request()->input('length', 10);
    if ($length == -1) $length = 1000;
    $data = array();

    $table = 'coupons';
    $select_columns = [];
    $select_columns[] = $table . '.id';
    $select_columns[] = $table . '.name';
    $select_columns[] = $table . '.views';
    $select_columns[] = $table . '.number_of_times_redeemed';
    $select_columns[] = $table . '.active';
    $select_columns[] = $table . '.slug';
    $select_columns[] = $table . '.additional_fields';
    $select_columns[] = $table . '.created_at';
    $search_columns = [];
    $search_columns[] = $table . '.name';

    $order_by = (isset($select_columns[$order_by])) ? $select_columns[$order_by] : null;

    // Query model
    $query = \Modules\Coupons\Models\Coupon::select($select_columns);

    $count = $query->where(function ($query) use($q, $search_columns) {
        if($q != '') {
          foreach ($search_columns as $search_column) {
            $query->orWhere($search_column, 'like', '%' . $q . '%');
          }
        }
      })->count();

    $records = $query->where(function ($query) use($q, $search_columns) {
        if($q != '') {
          foreach ($search_columns as $search_column) {
            $query->orWhere($search_column, 'like', '%' . $q . '%');
          }
        }
      })
      ->orderBy($order_by, $order)
      ->take($length)->skip($start)->get();

    if($length == -1) $length = $count;

    $data = [];

    foreach ($records as $record) {
      $row['id'] = $record->id;
      $row['DT_RowId'] = 'row_' . $record->id;
      $row['name'] = $record->name;
      $row['views'] = $record->views;
      $row['number_of_times_redeemed'] = $record->number_of_times_redeemed;
      $row['active'] = $record->active;
      $row['url'] = $record->getUrl();
      $row['qr'] = url(\Cache::rememberForever('qr-' . md5($record->getUrl()), function() use($record) { return \DNS2D::getBarcodePNGPath($record->getUrl(), 'QRCODE', 10, 10, [0,0,0]); }));
      $row['sl'] = Core\Secure::array2string(array('coupon_id' => $record->id));

      $data[] = $row;
    }

    $response = array(
      'draw' => $draw,
      'recordsTotal' => $count,
      'recordsFiltered' => $count,
      'data' => $data
    );

    return response()->json($response);
  }

  /**
   * Create coupon
   */

  public function getCreateCoupon(FormBuilder $formBuilder) {
    $action = 'create';
    $title = trans('coupons::g.create_coupon');
    $coupon = new \stdClass;
    $coupon->favicon_file_name = null;
    $coupon->image_file_name = null;

    // Get form
    $form = $formBuilder->create('Modules\Coupons\Forms\Coupon', [
      'method' => 'POST',
      'enctype' => 'multipart/form-data',
      'url' => url('coupons/create'),
      'language_name' => 'g',
      'data' => ['create' => true]
    ]);

    return view('coupons::coupon', compact('coupon', 'form', 'action', 'title'));
  }

  /**
   * Create coupon post
   */

  public function postCreateCoupon(FormBuilder $formBuilder) {
    // Form
    $form = $this->form('Modules\Coupons\Forms\Coupon');

    // Validate form
    if (! $form->isValid()) {
      return redirect()->back()->withErrors($form->getErrors())->withInput();
    }

    $form_fields = $form->getFieldValues();

    // Process image attachment field
    if (request()->get('image_changed') == 1) {
      if ($form_fields['image'] == null) {
        $form_fields['image'] = \Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT;
      }
    } else {
      array_forget($form_fields, 'image');
    }

    // Process favicon attachment field
    if (request()->get('favicon_changed') == 1) {
      if ($form_fields['favicon'] == null) {
        $form_fields['favicon'] = \Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT;
      }
    } else {
      array_forget($form_fields, 'favicon');
    }

    $fields = [];
    foreach ($form_fields as $key => $val) {
      if (starts_with($key, 'additional_fields_')) {
        $key = str_replace('additional_fields_', '', $key);
        $key = 'additional_fields->' . $key . '';
      }

      if ($key == 'form_fields') {
        $val = json_decode($val);
      }
      
      $fields[$key] = $val;
    }

    // Create record
    $coupon = \Modules\Coupons\Models\Coupon::create($fields);

    return redirect('coupons')->with('success', trans('g.form_success'));
  }

  /**
   * Edit coupon
   */

  public function getEditCoupon($sl, FormBuilder $formBuilder) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['coupon_id'];

    $action = 'edit';
    $title = trans('coupons::g.edit_coupon');

    if (is_numeric($id)) {
      // Set coupon
      $coupon = \Modules\Coupons\Models\Coupon::findOrFail($id);

      $coupon->form_fields = json_encode($coupon->form_fields);

      foreach ($coupon->additional_fields as $key => $val) {
        $coupon->{'additional_fields_' . $key} = $val;
      }

      // Get form
      $form = $formBuilder->create('Modules\Coupons\Forms\Coupon', [
        'method' => 'POST',
        'enctype' => 'multipart/form-data',
        'url' => url('coupons/edit/' . $sl),
        'model' => $coupon->toArray(), // Pass model as array so hidden fields are respected
        'language_name' => 'g',
        'data' =>  ['model' => $coupon, 'sl' => $sl] // Pass model as collection for field processing
      ]);

      return view('coupons::coupon', compact('sl', 'coupon', 'form', 'title'));
    }
  }

  /**
   * Edit coupon post
   */

  public function postEditCoupon($sl) {
    $msg = trans('g.form_success');
    $qs = Core\Secure::string2array($sl);
    $id = $qs['coupon_id'];

    if (is_numeric($id)) {
      // Form
      $form = $this->form('Modules\Coupons\Forms\Coupon');

      $coupon = \Modules\Coupons\Models\Coupon::findOrFail($id);

      // Extend validation
      $form->validate([
        'slug' => [
          'required',
          'min:1',
          'max:128',
          'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/i',
          'unique:coupons,slug,' . $qs['coupon_id']
        ]
      ]);

      // Validate form
      if (! $form->isValid()) {
        return redirect()->back()->withErrors($form->getErrors())->withInput();
      }

      $form_fields = $form->getFieldValues();

      // Process image attachment field
      if (request()->get('image_changed') == 1) {
        if ($form_fields['image'] == null) {
          $form_fields['image'] = \Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT;
        }
      } else {
        array_forget($form_fields, 'image');
      }

      // Process favicon attachment field
      if (request()->get('favicon_changed') == 1) {
        if ($form_fields['favicon'] == null) {
          $form_fields['favicon'] = \Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT;
        }
      } else {
        array_forget($form_fields, 'favicon');
      }

      $fields = [];
      foreach ($form_fields as $key => $val) {
        if (starts_with($key, 'additional_fields_')) {
          $key = str_replace('additional_fields_', '', $key);
          $key = 'additional_fields->' . $key . '';
        }

        if ($key == 'form_fields') {
          $val = json_decode($val);
        }

        $fields[$key] = $val;
      }

      $coupon->fill($fields);
      $coupon->save();
    }

    return redirect('coupons')->with('success', $msg);
  }

  /**
   * Delete (selected) coupons
   */

  public function postDeleteCoupons() {
    $ids = request()->get('ids');

    if (is_array($ids)) {
      foreach ($ids as $id) {
        // Filter assigned records
        $query = \Modules\Coupons\Models\Coupon::select(['id'])->find($id);

        if ($query !== null) {
          // Delete
          $query->delete();
        }
      }
    }

    return response()->json(true);
  }

}
