<?php

namespace Modules\Newsletters\Http\Controllers;

use Platform\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use Kris\LaravelFormBuilder\FormBuilder;
use Kris\LaravelFormBuilder\FormBuilderTrait;

use Illuminate\Support\Facades\Mail;
use Modules\Newsletters\Emails\SendNewsletter;

use Modules\Newsletters\Forms\Newsletter as NewsletterForm;

class NewslettersController extends Controller {

  use FormBuilderTrait;

  /**
   * Get newsletters list
   */
  public function getNewsletterList() {

    // Get templates
    $templates = \Storage::disk('public')->directories('modules/newsletters/templates');

    $templates = collect($templates)->map(function ($path) {

      if (\Storage::disk('public')->exists($path . '/config.php')) {
        
        return [
          'config' => include($path . '/config.php'),
          'thumb' => url($path . '/thumb.svg'),
          'url' => basename($path)
        ];
      }
    });

    return view('newsletters::list-newsletters', compact('templates'));
  }

  /**
   * Newsletters list json
   */

  public function getNewsletterListJson() {
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

    $table = 'newsletters';
    $select_columns = [];
    $select_columns[] = $table . '.id';
    $select_columns[] = $table . '.name';
    $select_columns[] = $table . '.number_of_recepients';
    $select_columns[] = $table . '.last_sent_date';
    $select_columns[] = $table . '.created_at';
    $select_columns[] = $table . '.subject';
    $search_columns = [];
    $search_columns[] = $table . '.name';
    $search_columns[] = $table . '.subject';

    $order_by = (isset($select_columns[$order_by])) ? $select_columns[$order_by] : null;

    // Query model
    $query = \Modules\Newsletters\Models\Newsletter::select($select_columns)->with(['roles', 'users']);

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

      $recepients = [];

      $sources = \DB::table('newsletter_lead_source')
        ->select(['lead_source'])
        ->where('newsletter_id', $record->id)
        ->orderBy('lead_source', 'asc')
        ->get();

      foreach ($sources as $source) {
        $recepients[] = $source->lead_source;
      }
      foreach ($record->roles as $role) {
        $recepients[] = $role->name;
      }
      foreach ($record->users as $user) {
        $recepients[] = $user->name;
      }

      $row['id'] = $record->id;
      $row['DT_RowId'] = 'row_' . $record->id;
      $row['name'] = $record->name;
      $row['subject'] = $record->subject;
      $row['recepients'] = (count($recepients) > 0) ? implode(', ', $recepients) : '-';
      $row['number_of_recepients'] = ($record->number_of_recepients === null) ? '-' : $record->number_of_recepients;
      $row['last_sent_date'] = ($record->last_sent_date === null) ? '-' : auth()->user()->formatDate($record->last_sent_date, 'datetime_medium');
      $row['created_at'] = ($record->created_at === null) ? '-' : auth()->user()->formatDate($record->created_at, 'datetime_medium');
      $row['token'] = Core\Secure::staticHash($record->id);
      $row['sl'] = Core\Secure::array2string(array('newsletter_id' => $record->id));

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
   * Create newsletter
   */

  public function getCreateNewsletter($template, FormBuilder $formBuilder) {
    $action = 'create';
    $title = trans('newsletters::g.create_newsletter');

    // Get form
    $form = $formBuilder->create('Modules\Newsletters\Forms\Newsletter', [
      'method' => 'POST',
        'id' => 'frmPost',
      'enctype' => 'multipart/form-data',
      'url' => url('newsletters/create'),
      'language_name' => 'g',
      'data' => ['create' => true]
    ]);

    $style_file = 'modules/newsletters/assets/email.css';
    $template_path = 'modules/newsletters/templates/' . $template;

    if ($template !== null && \Storage::disk('public')->exists($template_path . '/template.blade.php')) {
      // Set blade dir
      view()->addLocation(public_path($template_path));
      $content = view('template')->render(); ///\Storage::disk('public')->get($template_path . '/template.blade.php');

      if (\Storage::disk('public')->exists($template_path . '/style.css')) {
        $style = $assets = \Storage::disk('public')->get($template_path . '/style.css');
      } else {
        $style = $assets = \Storage::disk('public')->get($style_file);
      }
    } else {
      $content = '<div class="container"></div>';
      if (\Storage::disk('public')->exists($style_file)) {
        $style = $assets = \Storage::disk('public')->get($style_file);
      } else {
        $style = '';
      }
    }

    return view('newsletters::newsletter', compact('form', 'action', 'title', 'style', 'content'));
  }

  /**
   * Create newsletter post
   */

  public function postCreateNewsletter(FormBuilder $formBuilder) {
    // Form
    $form = $this->form('Modules\Newsletters\Forms\Newsletter');

    // Validate form
    if (! $form->isValid()) {
      return redirect()->back()->withErrors($form->getErrors())->withInput();
    }

    $form_fields = $form->getFieldValues();

    // Create record
    $newsletter = \Modules\Newsletters\Models\Newsletter::create($form_fields);

    // Sync recepients
    $newsletter->sources()->sync($form_fields['sources']);
    $newsletter->roles()->sync($form_fields['roles']);
    $newsletter->users()->sync($form_fields['users']);

    return redirect('newsletters')->with('success', trans('g.form_success'));
  }

  /**
   * Duplicate newsletter post
   */

  public function postDuplicateNewsletter() {
    $sl = request()->get('sl', null);
    $qs = Core\Secure::string2array($sl);
    $id = $qs['newsletter_id'];

    if (is_numeric($id)) {
      $newsletter = \Modules\Newsletters\Models\Newsletter::where('id', '=', $id)->with(['roles', 'users'])->first();

      $sources = \DB::table('newsletter_lead_source')
        ->select(['lead_source'])
        ->where('newsletter_id', $id)
        ->orderBy('lead_source', 'asc')
        ->get();

      $newsletter->name = $newsletter->name . ' (' . auth()->user()->formatDate(\Carbon\Carbon::now(), 'datetime_medium') . ')';
      $newsletter->number_of_recepients = null;
      $newsletter->times_sent = 0;
      $newsletter->last_sent_date = null;

      $clone = $newsletter->replicate();
      $clone->push();

      foreach($newsletter->roles as $role) {
        $clone->roles()->attach($role);
      }

      foreach($newsletter->users as $user) {
        $clone->users()->attach($user);
      }

      foreach($sources as $source) {
        $clone->sources()->attach($source->lead_source);
      }

      return response()->json(true);
    }
  }

  /**
   * Edit newsletter
   */

  public function getEditNewsletter($sl, FormBuilder $formBuilder) {
    $qs = Core\Secure::string2array($sl);
    $id = $qs['newsletter_id'];

    $action = 'edit';

    if (is_numeric($id)) {
      // Set newsletter
      $newsletter = \Modules\Newsletters\Models\Newsletter::where('id', '=', $id)->with(['roles', 'users'])->first();

      $title = trans('newsletters::g.edit_newsletter') . ' - ' . $newsletter->name;

      $sources = \DB::table('newsletter_lead_source')
        ->select(['lead_source'])
        ->where('newsletter_id', $id)
        ->orderBy('lead_source', 'asc')
        ->get();

      $newsletter->sources = $sources;

      // Get form
      $form = $formBuilder->create('Modules\Newsletters\Forms\Newsletter', [
        'method' => 'POST',
        'id' => 'frmPost',
        'enctype' => 'multipart/form-data',
        'url' => url('newsletters/edit/' . $sl),
        'model' => $newsletter->toArray(), // Pass model as array so hidden fields are respected
        'language_name' => 'g',
        'data' =>  ['model' => $newsletter, 'sl' => $sl] // Pass model as collection for field processing
      ]);

      $style = $newsletter->style;
      $content = $newsletter->content;

      return view('newsletters::newsletter', compact('sl', 'newsletter', 'form', 'title', 'style', 'content'));
    }
  }

  /**
   * Edit newsletter post
   */

  public function postEditNewsletter($sl) {
    $msg = trans('g.form_success');
    $qs = Core\Secure::string2array($sl);
    $id = $qs['newsletter_id'];

    if (is_numeric($id)) {
      // Form
      $form = $this->form('Modules\Newsletters\Forms\Newsletter');

      $newsletter = \Modules\Newsletters\Models\Newsletter::findOrFail($id);

      // Validate form
      if (! $form->isValid()) {
        return redirect()->back()->withErrors($form->getErrors())->withInput();
      }

      $form_fields = $form->getFieldValues();

      $newsletter->fill($form_fields);
      $newsletter->save();

      // Sync recepients
      $newsletter->sources()->sync($form_fields['sources']);
      $newsletter->roles()->sync($form_fields['roles']);
      $newsletter->users()->sync($form_fields['users']);
    }

    return redirect('newsletters')->with('success', $msg);
  }

  /**
   * Delete (selected) newsletters
   */

  public function postDeleteNewsletters() {
    $ids = request()->get('ids');

    if (is_array($ids)) {
      foreach ($ids as $id) {
        // Filter assigned records
        $query = \Modules\Newsletters\Models\Newsletter::select(['id'])->find($id);

        if ($query !== null) {
          // Delete
          $query->delete();
        }
      }
    }

    return response()->json(true);
  }

  /**
   * Get newsletter assets
   */

  public function postGetEditorAssets() {
    $newsletter_dir = '/files/newsletters';

    if (\Storage::disk('public')->exists($newsletter_dir)) {
      $assets = \Storage::disk('public')->files($newsletter_dir);

      // Make url of path
      $assets = collect($assets)->map(function ($path) {
        return url($path);
      });
    } else {
      // Create newsletter assets directory if not exists
      \Storage::disk('public')->makeDirectory($newsletter_dir);
      $assets = [];
    }

    return response()->json($assets);
  }

  /**
   * Upload newsletter assets
   */

  public function postUploadEditorAssets() {
    $newsletter_dir = '/files/newsletters';

    // Get existing assets
    $assets = $this->postGetEditorAssets();
    $assets = json_decode($assets->content(), true);

    // Upload files
    $count = request()->get('count', 0);
    for ($i = 0; $i < $count; $i++) {
      $file = request()->file('file-' . $i, null);
      if ($file !== null) {
        $path = \Storage::disk('public')->putFileAs($newsletter_dir, $file, $file->getClientOriginalName());
        $assets[] = url($path);
      }
    }

    return response()->json($assets);
  }

  /**
   * Send test email
   */

  public function postSendTestNewsletter() {
    if (! config('app.demo')) {
      $from_name = request()->get('from_name', auth()->user()->name);
      $from_email = request()->get('from_email', auth()->user()->email);
      $to = request()->get('to', null);
      $lead_source = request()->get('lead_source', '');
      $subject = '[TEST] ' . request()->get('subject', null);
      $style = request()->get('style', null);
      $content = request()->get('content', null);
      $msg = trans('newsletters::g.newsletter_sent_to', ['email' => $to]);
      $unsubscribe_url = url('newsletters/unsubscribe?source=' . urlencode($lead_source) . '&email=' . urlencode($to) . '&test=1');

      if ($to !== null && $subject !== null && $style !== null && $content !== null) {
        Mail::to($to)->send(new SendNewsletter($from_name, $from_email, $subject, $style, $content, $unsubscribe_url));
      }
    } else {
      $msg = trans('newsletters::g.newsletter_no_sent_demo');
    }

    return response()->json(['msg' => $msg]);
  }

  /**
   * Send newsletter
   */

  public function postSendNewsletter() {
    if (! config('app.demo')) {
      $sl = request()->get('sl', null);
      $qs = Core\Secure::string2array($sl);
      $id = $qs['newsletter_id'];

      if (is_numeric($id)) {
        $msg = trans('newsletters::g.newsletter_sent');
        $newsletter = \Modules\Newsletters\Models\Newsletter::where('id', '=', $id)->with(['roles', 'users'])->first();

        // Get all unique recepients
        $recepients = collect();
        $sources = \DB::table('newsletter_lead_source')
          ->select(['lead_source'])
          ->where('newsletter_id', $id)
          ->orderBy('lead_source', 'asc')
          ->get();

        foreach ($sources as $source) {
          $recepients = $recepients->merge(\App\User::where('lead_source', '=', $source->lead_source)->where('active', '=', 1)->get());
        }

        $users = \App\User::with('roles')->get();
        foreach ($newsletter->roles as $role) {
          $members = $users->map(function ($user, $role) {
            if ($user->hasRole($role) && $user->active == 1) {
              return $user;
            }
          });
          if ($members !== null) $recepients = $recepients->merge($members);
        }

        foreach ($newsletter->users as $user) {
          $recepients = $recepients->push($user);
        }

        $recepients = $recepients->unique();

        foreach ($recepients as $key => $recepient) {
          if (! isset($recepient->email)) {
            $recepients->forget($key);
          }
        }

        $newsletter->number_of_recepients = $recepients->count();
        $newsletter->times_sent = $newsletter->times_sent + 1;
        $newsletter->last_sent_date = \Carbon\Carbon::now();
        $newsletter->save();

        $from_name = $newsletter->from_name;
        $from_email = $newsletter->from_email;
        $subject = $newsletter->subject;
        $style = $newsletter->style;
        $content = $newsletter->content;

        if ($subject !== null && $style !== null && $content !== null) {
          foreach ($recepients as $recepient) {
            $unsubscribed = $recepient->meta['unsubscribed'] ?? 0;
            if (! $unsubscribed) {
              $to = $recepient->email;
              $unsubscribe_url = url('newsletters/unsubscribe?source=' . urlencode($recepient->lead_source) . '&email=' . urlencode($to));
              Mail::to($to)->send(new SendNewsletter($from_name, $from_email, $subject, $style, $content, $unsubscribe_url));
            }
          }
        }
      }
    } else {
      $msg = trans('newsletters::g.newsletter_no_sent_demo');
    }

    return response()->json(['msg' => $msg]);
  }

  /**
   * Get unsubscribe page
   */
  public function getUnsubscribeNewsletter() {
    $test = request()->get('test', 0);
    $lead_source = urldecode(request()->get('source', null));
    $email = urldecode(request()->get('email', null));

    if ($test == 0 && $email !== null) {
      if ($lead_source === null) {
        $user = \App\User::whereNull('lead_source')->where('active', '=', 1)->where('email', '=', $email)->first();
      } else {
        $user = \App\User::where('lead_source', '=', $lead_source)->where('active', '=', 1)->where('email', '=', $email)->first();
      }

      $unsubscribed = $user->meta['unsubscribed'] ?? 0;

      if ($unsubscribed == 0) {
        $user['meta->unsubscribed'] = 1;
        $user->save();
      }
    }
    return view('newsletters::public.unsubscribed', compact('test', 'email', 'unsubscribed'));
  }
}
