<?php namespace Platform\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Core;
use Illuminate\Encryption\Encrypter;

class InstallationController extends \App\Http\Controllers\Controller {

	/*
	|--------------------------------------------------------------------------
	| Installation Controller
	|--------------------------------------------------------------------------
	|
	*/

  public function __construct() {
    if ($this->isInstalled() && \Request::segment(2) != 'update') {
      abort(404);
    }
  }

	/**
	 * Check for installation
	 */
	public static function isInstalled() {
    return (\File::exists(base_path('.env'))) ? true : false;
	}

	/**
	 * Installation view
	 */
	public function getInstall() {
		return view('installation.install');
	}

	/**
	 * Post installation
	 */
  public function postInstall(Request $request) {

    set_time_limit(500);

    $name = $request->input('name', '');
    $email = $request->input('email', '');
    $phone = $request->input('phone', null);
    $pass = $request->input('pass', '');

    $company_name = $request->input('company_name', '');
    $company_email = $request->input('company_email', null);
    $company_phone = $request->input('company_phone', null);

    $APP_URL = $request->input('APP_URL', '');
    $APP_KEY = 'base64:'.base64_encode(
      Encrypter::generateKey(config('app.cipher'))
    );

    // Get .env.example file as blueprint
    $env = \File::get(base_path('.env.example'));

    $all = $request->except(['email', 'pass']);
    $all['APP_KEY'] = $APP_KEY;
    $all['APP_DEBUG'] = 'false';
    $all['APP_ENV'] = 'production';

    // Loop through .env.example and set config
    $new_env = '';

    foreach(preg_split("/((\r?\n)|(\r\n?))/", $env) as $line) {
      $cfg_found = false;

      foreach ($all as $key => $value) {
        if (starts_with($line, $key . '=')) {
          $cfg_found = true;
          if ($value == 'true' || $value == 'false' || is_numeric($value)) {
            $new_env .= $key . '=' . $value . '' . PHP_EOL;
          } else { 
            $new_env .= $key . '="' . $value . '"' . PHP_EOL;
          }
        }
      }

      if (! $cfg_found) {
        $new_env .= $line . PHP_EOL;
      }
    }

    \File::put(base_path('.env'), $new_env);

    \Artisan::call('config:cache');

    sleep(1);

    \Artisan::call('migrate', [
      '--seed' => true,
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

    \Artisan::call('config:cache');

    $user = \App\User::find(1);
    $user->name = $name;
    $user->email = $email;
    $user->phone = $phone;
    $user->password = bcrypt($pass);
    $user->save();

    $company = \Platform\Models\Company::find(1);
    $company->name = $company_name;
    $company->email = $company_email;
    $company->phone = $company_phone;
    $company->save();

    return redirect(url('login'));
	}
}