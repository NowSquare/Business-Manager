<?php namespace Platform\Controllers;

class AssetController extends \App\Http\Controllers\Controller {

  /*
   |--------------------------------------------------------------------------
   | Assets Controller
   |--------------------------------------------------------------------------
   |
   | Assets related logic
   |--------------------------------------------------------------------------
   */

  /**
   * JavaScript
   */
  public function getJavascript() {
    $translation = trans('javascript');

    $js = '';

    //$js .= 'var APP_URL="' . config('app.url') . '";';
    //$js .= 'var csrf_token="' . csrf_token() . '";';

    $js .= 'var _trans=[];';
    foreach($translation as $key => $val) {
      $js .= '_trans["' . $key . '"]="' . $val . '";';
    }

    $response = response()->make($js);
    $response->header('Content-Type', 'application/javascript');

    return $response;
  }
}