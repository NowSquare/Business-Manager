<?php namespace Platform\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Core;
use Illuminate\Encryption\Encrypter;

class UpdateController extends \App\Http\Controllers\Controller {

	/*
	|--------------------------------------------------------------------------
	| Update Controller
	|--------------------------------------------------------------------------
	|
	*/

	/**
	 * Reset installation
	 */
	public static function resetInstallation($app_key) {
    if ($app_key !== config('app.key')) abort(404);

    set_time_limit(500);

    // Clean uploads and attachments
    UpdateController::clean();

    \Artisan::call('cache:clear');
    \Artisan::call('route:cache');
    \Artisan::call('view:clear');
    \Artisan::call('config:clear');
    \Artisan::call('config:cache');

    // Set session temprorarily to file
    config(['session.driver' => 'file']);

    //\Artisan::call('migrate', ['--force' => true]);
    \Artisan::call('migrate:refresh', ['--force' => true]);
    \Artisan::call('db:seed', ['--force' => true]);

    // Migrate modules
    $modules = \Module::getOrdered();

    foreach ($modules as $module) {
      \Artisan::call('module:migrate-refresh', [
          'module' => $module->getName(),
          '--force' => true,
      ]);
    }

    // Clear all caches, necessary to prevent unexpected behaviour
    \Artisan::call('cache:clear');
    \Artisan::call('route:cache');
    \Artisan::call('view:clear');
    \Artisan::call('config:clear');
    \Artisan::call('config:cache');
	}

  /**
   * Delete uploads and database tables
   */
  public static function clean($drop_tables = false) {

    /**
     * Empty directories and put .gitignore back
     */
    $gitignore = '*
!.gitignore';

    $dirs = [
      '/public/attachments/',
      '/public/files/',
    ];

    foreach($dirs as $dir) {
      $full_dir = base_path() . $dir;

      if(\File::isDirectory($full_dir)) {

        $success = \File::deleteDirectory($full_dir, true);
        if($success) {
          // Deploy .gitignore
          \File::put($full_dir . '.gitignore', $gitignore);
        }
      }
    }

    /**
     * Delete directories
     */
    $dirs = [
      '/storage/app/invoices/',
    ];

    foreach($dirs as $dir) {
      $full_dir = base_path() . $dir;

      if(\File::isDirectory($full_dir)) {
        \File::deleteDirectory($full_dir, true);
      }
    }

    /**
     * Drop all tables in database
     */
    if ($drop_tables) {
      $tables = [];
      \DB::statement('SET FOREIGN_KEY_CHECKS=0');
 
      foreach(\DB::select('SHOW TABLES') as $k => $v) {
        $tables[] = array_values((array)$v)[0];
      }
 
      foreach($tables as $table) {
        \Schema::drop($table);
      }
    }
  }

}