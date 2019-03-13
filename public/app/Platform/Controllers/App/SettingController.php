<?php namespace Platform\Controllers\App;

use Platform\Controllers\Core;

use Kris\LaravelFormBuilder\FormBuilder;
use Kris\LaravelFormBuilder\FormBuilderTrait;

use App\Forms\Settings;

class SettingController extends \App\Http\Controllers\Controller {

  use FormBuilderTrait;

  /*
   |--------------------------------------------------------------------------
   | Setting Controller
   |--------------------------------------------------------------------------
   */

  /**
   * Settings
   */

  public function getSettings(FormBuilder $formBuilder) {
    // Set model
    $model = new \stdClass;
    // General
    $model->system_name = Core\Settings::get('system_name', 'string', config('system.name'));
    $model->system_icon = Core\Settings::get('system_icon', 'image');
    $model->system_signup = Core\Settings::get('system_signup', 'boolean', 1);
    // Pusher
    $model->pusher_app_id = Core\Settings::get('pusher_app_id', 'string', null);
    $model->pusher_key = Core\Settings::get('pusher_key', 'string', null);
    $model->pusher_secret = Core\Settings::get('pusher_secret', 'string', null);
    $model->pusher_cluster = Core\Settings::get('pusher_cluster', 'string', null);

    // Version
    if (\File::exists(base_path('storage/version'))) {
      $version = \File::get(base_path('storage/version'));
    } else {
      $version = '?';
    }

    // Get form
    $form = $formBuilder->create('App\Forms\Settings', [
      'method' => 'POST',
      'enctype' => 'multipart/form-data',
      'url' => url('settings'),
      'model' => (array) $model, // Pass model as array so hidden fields are respected
      'language_name' => 'g',
      'data' =>  ['model' => $model] // Pass model as collection for field processing
    ]);

    return view('app.system.settings', compact('form', 'version'));
  }

  /**
   * Update settings
   */

  public function postSettings(FormBuilder $formBuilder) {
    if (config('app.demo')) return redirect('settings')->with('warning', trans('g.demo_mode_update_settings'));

    // Form and model
    $form = $this->form(Settings::class);

    // Validate form
    if (! $form->isValid()) {
      return redirect()->back()->withErrors($form->getErrors())->withInput();
    }

    // Get form post
    $form_fields = $form->getFieldValues();

    // Process image attachment field
    if (request()->get('system_icon_changed') == 1) {
      if ($form_fields['system_icon'] == null) {
        $form_fields['system_icon'] = \Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT;
      }
    } else {
      array_forget($form_fields, 'system_icon');
    }

    // Save settings
    // General
    Core\Settings::set('system_name', 'string', $form_fields['system_name']);
    if (isset($form_fields['system_icon'])) Core\Settings::set('system_icon', 'image', $form_fields['system_icon']);
    Core\Settings::set('system_signup', 'boolean', $form_fields['system_signup']);
    // Pusher
    Core\Settings::set('pusher_app_id', 'string', $form_fields['pusher_app_id']);
    Core\Settings::set('pusher_key', 'string', $form_fields['pusher_key']);
    Core\Settings::set('pusher_secret', 'string', $form_fields['pusher_secret']);
    Core\Settings::set('pusher_cluster', 'string', $form_fields['pusher_cluster']);

    // Log
    Core\Log::add(
      'update_settings', 
      trans('g.log_user_update_settings', ['name' => auth()->user()->name]),
      '\App\User',
      auth()->user()->id,
      auth()->user()
    );

    return redirect('settings')->with('success', trans('g.form_success'));
  }

  /**
   * Run migrations
   */

  public function postRunMigrations() {
    if (config('app.demo')) return response()->json(true);

    \Artisan::call('cache:clear');
    \Artisan::call('route:cache');
    \Artisan::call('view:clear');
    \Artisan::call('config:clear');
    \Artisan::call('config:cache');

    \Artisan::call('migrate', [
      '--force' => true
    ]);

    // Migrate modules
    $modules = \Module::getOrdered();

    foreach ($modules as $module) {
      \Artisan::call('module:migrate', [
          'module' => $module->getName(),
          '--force' => true,
      ]);
    }

    sleep(1);

    // Clear config cache again to prevent unexpected behaviour
    \Artisan::call('config:cache');

    return response()->json(true);
  }

  /**
   * Tax rates list json
   */

  public function getTaxRatesListJson() {
    // DataTables parameters
    $order_by = request()->input('order.0.column', 1);
    $order = request()->input('order.0.dir', 'asc');
    $search = request()->input('search.regex', '');
    $q = request()->input('search.value', '');
    $start = request()->input('start', 0);
    $draw = request()->input('draw', 1);
    $length = request()->input('length', 10);
    if ($length == -1) $length = 1000;
    $data = array();

    $table = 'tax_rates';
    $select_columns = [];
    $select_columns[] = $table . '.rate';
    $select_columns[] = $table . '.id';
    $select_columns[] = $table . '.default';
    $search_columns = [];
    $search_columns[] = $table . '.rate';

    $order_by = (isset($select_columns[$order_by])) ? $select_columns[$order_by] : null;

    // Query model
    $query = \Platform\Models\Core\TaxRate::select($select_columns);

    $count = $query->where(function ($query) use($q, $search_columns) {
        if($q != '') {
          foreach ($search_columns as $search_column) {
            $query->orWhere($search_column, 'like', '%' . $q . '%');
          }
        }
      })->count();

    $records = $query->orderBy($order_by, $order)
      ->where(function ($query) use($q, $search_columns) {
        if($q != '') {
          foreach ($search_columns as $search_column) {
            $query->orWhere($search_column, 'like', '%' . $q . '%');
          }
        }
      })
      ->take($length)->skip($start)->get();

    if($length == -1) $length = $count;

    $data = [];

    foreach ($records as $record) {
      $row['id'] = $record->id;
      $row['DT_RowId'] = 'row_' . $record->id;
      $row['rate'] = $record->percentage;
      $row['default'] = $record->default;
      $row['sl'] = Core\Secure::array2string(array('tax_rate_id' => $record->id));

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
   * Create rate
   */

  public function postCreateTaxRate() {
    if (config('app.demo')) return response()->json(['msg' => trans('g.demo_mode_update_settings')]);

    $rate = request()->get('rate');

    // No duplicate
    $tax_rate = \Platform\Models\Core\TaxRate::where('rate', $rate)->first();

    if ($tax_rate !== null) {
      return response()->json(['msg' => trans('g.tax_rate_exists')]);
    }

    if (is_numeric($rate)) {
      $tax_rate = new \Platform\Models\Core\TaxRate;
      $tax_rate->rate = $rate;
      $tax_rate->save();
    }

    return response()->json(true);
  }

  /**
   * Delete rate
   */

  public function postDeleteTaxRates() {
    if (config('app.demo')) return response()->json(['msg' => trans('g.demo_mode_update_settings')]);

    $ids = request()->get('ids');

    if (is_array($ids)) {
      foreach ($ids as $id) {
        $query = \Platform\Models\Core\TaxRate::find($id);

        if ($query !== null) {
          // No in use
          $proposition = \Platform\Models\ProjectPropositionItem::where('tax_rate', $query->rate)->first();

          if ($proposition !== null) {
            return response()->json(['msg' => trans('g.tax_rate_in_use')]);
          }

          // Delete
          $query->delete();
        }
      }
    }

    return response()->json(true);
  }
}