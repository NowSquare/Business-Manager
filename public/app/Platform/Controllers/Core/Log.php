<?php namespace Platform\Controllers\Core;

class Log extends \App\Http\Controllers\Controller {

  /**
   * Create log entry - \Platform\Controllers\Core\Log::add('login', trans('log_user_logged_in', ['name' => auth()->user()->name, 'email' => auth()->user()->email]))
   */

  public static function add($action, $event, $model = null, $model_id = null, $user = null, $account_id = null) {
    $user = ($user === null) ? auth()->user() : (object) $user;

    if (! isset($user->name) || ! isset($user->email)) return;

    $log = new \Platform\Models\Log;

    if ($account_id !== null) $log->account_id = $account_id;
    $log->user_id = $user->id;
    $log->user_name = $user->name;
    $log->user_email = $user->email;
    $log->model = $model;
    $log->model_id = $model_id;
    $log->ip_address = request()->ip();
    $log->action = $action;
    $log->event = $event;
    $log->created_at = \Carbon\Carbon::now('UTC');
    $log->save();
  }

  /**
   * Get log entries id - \Platform\Controllers\Core\Log::get()
   */

  public static function get() {


  }
}