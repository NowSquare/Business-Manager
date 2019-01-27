<?php namespace Platform\Controllers\App;

use Carbon\Carbon;

use Platform\Controllers\Core;
use Illuminate\Support\Facades\Mail;

class NotificationController extends \App\Http\Controllers\Controller {

  /*
   |--------------------------------------------------------------------------
   | Notification Controller
   |--------------------------------------------------------------------------
   |
   | Notification related logic
   |--------------------------------------------------------------------------
   */

  /**
   * View notifications
   */

  public function getNotifications() {
    // Mark all notifications as read
    auth()->user()->unreadNotifications->markAsRead();

    $total_count = auth()->user()->notifications()->count();
    $page = request()->page ?: 0;
    $limit = 10;
    
    $notifications = auth()->user()->notifications()->paginate($limit);

    $pagination = $notifications->links('vendor.pagination.bootstrap-4');

    return view('app.users.notifications', compact('notifications', 'pagination', 'total_count', 'limit'));
  }

}