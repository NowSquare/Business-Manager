<?php namespace Platform\Controllers\App;

use Platform\Controllers\Core;

class DashboardController extends \App\Http\Controllers\Controller {

  /*
   |--------------------------------------------------------------------------
   | Dashboard Controller
   |--------------------------------------------------------------------------
   */

  /**
   * Dashboard
   */

  public function getDashboard() {
    if (auth()->user()->roles->pluck('id')->first() == 1) {

      /*
       |--------------------------------------------------------------------------
       | Admin dashboard
       |--------------------------------------------------------------------------
       */

      // Range
      $date_start = request()->get('start', date('Y-m-d', strtotime(' - 30 day')));
      $date_end = request()->get('end', date('Y-m-d'));
      $from =  $date_start . ' 00:00:00';
      $to = $date_end . ' 23:59:59';

      $people = \App\User::all();
      $companies = \Platform\Models\Company::all();
      $projects = \Platform\Models\Project::orderBy('due_date', 'asc')->get();

      // User roles
      foreach ($people->where('active') as $user) {
        $count = (isset($roles[$user->roles[0]->name])) ? $roles[$user->roles[0]->name]['count'] + 1: 1;
        $roles[$user->roles[0]->name] = ['count' => $count, 'color' => $user->roles[0]->color];
      }

      // Project statuses
      $tasks_count = 0;
      $tasks_completed_count = 0;

      foreach ($projects->where('active') as $project) {
        $count = (isset($project_statuses[$project->project_status_id])) ? $project_statuses[$project->project_status_id]['count'] + 1: 1;
        $project_statuses[$project->project_status_id] = ['count' => $count, 'count' => $count, 'name' => $project->status->name, 'color' => $project->status->color];
        $tasks_count += $project->tasks()->whereNull('completed_date')->count();
        $tasks_completed_count += $project->tasks()->whereNotNull('completed_date')->count();
      }

      if (! isset($project_statuses)) $project_statuses = [];

      // Notification grouped by date
      $notifications_by_date = \Illuminate\Notifications\DatabaseNotification::where('notifiable_type', 'App\User')->where('notifiable_id', auth()->user()->id)
        ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('count(id) as count'))
        ->where('created_at', '>=', $from)
        ->where('created_at', '<=', $to)
        ->groupBy([\DB::raw('DATE(created_at)')])
        ->get()
        ->pluck('count', 'date')
        ->toArray();

      // Users grouped by date
      $users_by_date = \App\User::select(\DB::raw('DATE(created_at) as date'), \DB::raw('count(id) as count'))
        ->where('created_at', '>=', $from)
        ->where('created_at', '<=', $to)
        ->groupBy([\DB::raw('DATE(created_at)')])
        ->get()
        ->pluck('count', 'date')
        ->toArray();

      // Companies grouped by date
      $companies_by_date = \Platform\Models\Company::select(\DB::raw('DATE(created_at) as date'), \DB::raw('count(id) as count'))
        ->where('created_at', '>=', $from)
        ->where('created_at', '<=', $to)
        ->groupBy([\DB::raw('DATE(created_at)')])
        ->get()
        ->pluck('count', 'date')
        ->toArray();

      // Projects grouped by date
      $projects_by_date = \Platform\Models\Project::select(\DB::raw('DATE(created_at) as date'), \DB::raw('count(id) as count'))
        ->where('created_at', '>=', $from)
        ->where('created_at', '<=', $to)
        ->groupBy([\DB::raw('DATE(created_at)')])
        ->get()
        ->pluck('count', 'date')
        ->toArray();

      $date_range = Core\Localization::getRange($date_start, $date_end);

      return view('app.dashboard.admin', compact('date_range', 'notifications_by_date', 'users_by_date', 'companies_by_date', 'projects_by_date', 'people', 'roles', 'companies', 'projects', 'project_statuses', 'tasks_count', 'tasks_completed_count'));

    } elseif (auth()->user()->roles->pluck('id')->first() == 2) {

      /*
       |--------------------------------------------------------------------------
       | Manager dashboard
       |--------------------------------------------------------------------------
       */

      // Project where user is manager
      $projects_manager = \Platform\Models\Project::whereHas('managers', function($q) {
        $q->where('user_id', auth()->user()->id);
      })->orderBy('due_date', 'asc')->get();

      // Project where user has tasks assigned
      $projects_assignee = \Platform\Models\Project::whereHas('tasks.assignees', function($q) {
        $q->where('user_id', auth()->user()->id);
      })->orderBy('due_date', 'asc')->get();

      $projects = $projects_manager->merge($projects_assignee);

      // Project statuses
      $client_companies = collect();
      $tasks_count = 0;
      $tasks_completed_count = 0;

      foreach ($projects->where('active') as $project) {
        $tasks_count += $project->tasks()->whereNull('completed_date')->count();
        $tasks_completed_count += $project->tasks()->whereNotNull('completed_date')->count();
        $client_companies->push($project->client);
      }

      return view('app.dashboard.manager', compact('client_companies', 'projects', 'tasks_count', 'tasks_completed_count'));

    } elseif (auth()->user()->roles->pluck('id')->first() == 3) {

      /*
       |--------------------------------------------------------------------------
       | Employee dashboard
       |--------------------------------------------------------------------------
       */

      // Project where user has tasks assigned
      $projects_assignee = \Platform\Models\Project::whereHas('tasks.assignees', function($q) {
        $q->where('user_id', auth()->user()->id);
      })->orderBy('due_date', 'asc')->get();

      $projects = $projects_assignee;

      // Project statuses
      $client_companies = collect();
      $tasks_count = 0;
      $tasks_completed_count = 0;

      foreach ($projects->where('active') as $project) {
        $tasks_count += $project->tasks()->whereHas('assignees', function($q) {
          $q->where('user_id', auth()->user()->id);
        })->whereNull('completed_date')->count();

        $tasks_completed_count += $project->tasks()->whereHas('assignees', function($q) {
          $q->where('user_id', auth()->user()->id);
        })->whereNotNull('completed_date')->count();

        $client_companies->push($project->client);
      }

      return view('app.dashboard.employee', compact('client_companies', 'projects', 'tasks_count', 'tasks_completed_count'));

    } elseif (auth()->user()->roles->pluck('id')->first() == 4) {

      /*
       |--------------------------------------------------------------------------
       | Contractor dashboard
       |--------------------------------------------------------------------------
       */

      // Project where user has tasks assigned
      $projects_assignee = \Platform\Models\Project::whereHas('tasks.assignees', function($q) {
        $q->where('user_id', auth()->user()->id);
      })->orderBy('due_date', 'asc')->get();

      $projects = $projects_assignee;

      // Project statuses
      $client_companies = collect();
      $tasks_count = 0;
      $tasks_completed_count = 0;

      foreach ($projects->where('active') as $project) {
        $tasks_count += $project->tasks()->whereHas('assignees', function($q) {
          $q->where('user_id', auth()->user()->id);
        })->whereNull('completed_date')->count();

        $tasks_completed_count += $project->tasks()->whereHas('assignees', function($q) {
          $q->where('user_id', auth()->user()->id);
        })->whereNotNull('completed_date')->count();

        $client_companies->push($project->client);
      }

      return view('app.dashboard.contractor', compact('client_companies', 'projects', 'tasks_count', 'tasks_completed_count'));

    } elseif (auth()->user()->roles->pluck('id')->first() == 5) {

      /*
       |--------------------------------------------------------------------------
       | Client dashboard
       |--------------------------------------------------------------------------
       */

      return view('app.dashboard.client');

    } elseif (auth()->user()->roles->pluck('id')->first() == 6) {

      /*
       |--------------------------------------------------------------------------
       | Agent dashboard
       |--------------------------------------------------------------------------
       */

      return view('app.dashboard.empty');

    } elseif (auth()->user()->roles->pluck('id')->first() == 7) {

      /*
       |--------------------------------------------------------------------------
       | Lead dashboard
       |--------------------------------------------------------------------------
       */

      return view('app.dashboard.empty');
    } else {
      return view('app.dashboard.empty');
    }
  }
}