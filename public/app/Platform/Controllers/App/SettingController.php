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
    $model->system_name = Core\Settings::get('system_name', 'string', config('system.name'));
    $model->system_icon = Core\Settings::get('system_icon', 'image');
    $model->system_signup = Core\Settings::get('system_signup', 'boolean', 1);

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
    if (env('DEMO', false)) return redirect('settings')->with('warning', trans('g.demo_mode_update_settings'));

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
    Core\Settings::set('system_name', 'string', $form_fields['system_name']);
    if (isset($form_fields['system_icon'])) Core\Settings::set('system_icon', 'image', $form_fields['system_icon']);
    Core\Settings::set('system_signup', 'boolean', $form_fields['system_signup']);

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
    if (env('DEMO', false)) return response()->json(true);

    \Artisan::call('config:clear');

    sleep(1);

    \Artisan::call('migrate', [
      '--force' => true
    ]);

    return response()->json(true);
  }
}