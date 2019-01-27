<?php

namespace Platform\Listeners;

use Illuminate\Support\Facades\Auth;
use Platform\Controllers\Core;
use Carbon\Carbon;

class UserEventSubscriber {
  /**
   * Handle user login events.
   */
  public function onUserLogin($event) {
    // Check if admin logs in
    $is_admin = false;
    $sl = \Session::get('logout', '');

    if ($sl != '') {
      $qs = Core\Secure::string2array($sl);
      $is_admin = (is_numeric($qs['user_id'])) ? true : false;
    }

    if (! $is_admin) {
      // Check if user is activated
      if ($event->user->activated !== 0) {
      }
    }
  }

  /**
   * Handle user logout events.
   */
  public function onUserLogout($event) {
    // Log admin back in
    $sl = \Session::pull('logout', '');
    if($sl != '') {
      $qs = Core\Secure::string2array($sl);
      \Auth::loginUsingId($qs['user_id'], true);
      return redirect('users');
    }
  }

  /**
   * Handle user registration events.
   */
  public function onLogRegisteredUser($event) {
  }

  /**
   * Register the listeners for the subscriber.
   *
   * @param  Illuminate\Events\Dispatcher  $events
   */
  public function subscribe($events) {
    //$events->listen('Illuminate\Auth\Events\Login', 'Platform\Listeners\UserEventSubscriber@onUserLogin');
    //$events->listen('Illuminate\Auth\Events\Logout', 'Platform\Listeners\UserEventSubscriber@onUserLogout');
    //$events->listen('Illuminate\Auth\Events\Registered', 'Platform\Listeners\UserEventSubscriber@onLogRegisteredUser');
    //$events->listen('Illuminate\Auth\Events\Attempting', 'Platform\Listeners\UserEventSubscriber@onLogAuthenticationAttempt');
    //$events->listen('Illuminate\Auth\Events\Authenticated', 'Platform\Listeners\UserEventSubscriber@onLogAuthenticated');
    //$events->listen('Illuminate\Auth\Events\Lockout', 'Platform\Listeners\UserEventSubscriber@onLogLockout');
  }
}