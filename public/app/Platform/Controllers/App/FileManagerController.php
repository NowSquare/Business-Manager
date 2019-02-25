<?php namespace Platform\Controllers\App;

use Platform\Controllers\Core;

class FileManagerController extends \App\Http\Controllers\Controller {

  /*
   |--------------------------------------------------------------------------
   | File Manager Controller
   |--------------------------------------------------------------------------
   */

  /**
   * Uploads
   */

  public function getUploads() {
    // Set admin root
    session(['elfinder.type' => 'admin']);

    return view('app.filemanager.uploads');
  }
}