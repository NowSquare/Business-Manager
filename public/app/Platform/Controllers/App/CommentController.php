<?php namespace Platform\Controllers\App;

use Carbon\Carbon;

use Platform\Controllers\Core;
use Illuminate\Support\Facades\Mail;

class CommentController extends \App\Http\Controllers\Controller {

  /*
   |--------------------------------------------------------------------------
   | Comment Controller
   |--------------------------------------------------------------------------
   |
   | Comment related logic
   |--------------------------------------------------------------------------
   */

  /**
   * Add comment
   */

  public function postAddComment() {
    $type = request()->get('type', null);
    $id = request()->get('id', null);
    $comment = request()->get('comment', null);

    if ($type !== null && $id !== null && $comment !== null) {

      if ($type == 'project') {
        $project = \Platform\Models\Project::findOrFail($id);
        $comment = auth()->user()->comment($project, $comment);

        // Notify all project members with permission to view comments, except for auth() user
        $users = collect();

        // Get client user(s)
        if ($project->client !== null) {
          $users = $users->merge($project->client->users);
        }

        // Get managers
        if ($project->managers !== null) {
          $users = $users->merge($project->managers);
        }

        // Get task(s) assignee
        foreach($project->tasks as $task) {
          $users = $users->merge($task->assignees);
        }

        $users = $users->unique('id');

        foreach ($users as $user) {
          if (auth()->user()->id != $user->id && $user->can('view-comments') && $user->active) {
            // Send notification
            \Notification::send($user, new \App\Notifications\ProjectNewComment(env('APP_URL') . '/login', auth()->user(), $user, $project, $comment));
          }
        }        
      }
    }

    return response()->json(true);
  }

}