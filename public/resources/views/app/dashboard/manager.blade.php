@extends('../../layouts.app')

@section('page_title', trans('g.dashboard') . ' - ' . config('system.name'))

@section('content')
        <div class="my-3 my-md-5">
          <div class="container">
            <div class="page-header">
              <h1 class="page-title">
                {{ trans('g.dashboard') }}
              </h1>
            </div>

            <div class="row">
              <div class="col-sm-6 col-lg-3">
                <div class="card p-3">
                  <div class="d-flex align-items-center">
                    <span class="stamp stamp-md bg-blue mr-3">
                      <i class="fe fe-bell"></i>
                    </span>
                    <div>
                      <h4 class="m-0"><a href="{{ url('notifications') }}">{{ auth()->user()->notifications->count() }} <small>{{ trans_choice('g.notification_notifications', auth()->user()->notifications->count()) }}</small></a></h4>
                      <small class="text-muted">{{ auth()->user()->unreadNotifications->count() }} {{ trans('g.unread') }}</small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-lg-3">
                <div class="card p-3">
                  <div class="d-flex align-items-center">
                    <span class="stamp stamp-md bg-green mr-3">
                      <i class="fe fe-briefcase"></i>
                    </span>
                    <div>
                      <h4 class="m-0"><a href="{{ url('companies') }}">{{ $client_companies->where('active', 1)->count() }} <small>{{ trans_choice('g.client_clients', $client_companies->where('active', 1)->count()) }}</small></a></h4>
                      <small class="text-muted">{{ $client_companies->where('active', 0)->count() }} {{ trans('g.inactive') }}</small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-lg-3">
                <div class="card p-3">
                  <div class="d-flex align-items-center">
                    <span class="stamp stamp-md bg-red mr-3">
                      <i class="fe fe-folder"></i>
                    </span>
                    <div>
                      <h4 class="m-0"><a href="{{ url('projects') }}">{{ $projects->where('completed_date', null)->count() }} <small>{{ trans_choice('g.project_projects', $projects->where('completed_date', null)->count()) }}</small></a></h4>
                      <small class="text-muted">{{ $projects->where('completed_date', '<>', null)->count() }} {{ trans('g.completed') }}</small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-lg-3">
                <div class="card p-3">
                  <div class="d-flex align-items-center">
                    <span class="stamp stamp-md bg-yellow mr-3">
                      <i class="fe fe-check-circle"></i>
                    </span>
                    <div>
                      <h4 class="m-0"><a href="{{ url('projects') }}">{{ $tasks_count }} <small>{{ trans_choice('g.task_tasks', $tasks_count) }}</small></a></h4>
                      <small class="text-muted">{{ $tasks_completed_count }} {{ trans('g.completed') }}</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row row-cards row-deck">

              <div class="col-sm-6 col-lg-8">
                <div class="card">
                  <div class="card-header">
                    <h2 class="card-title">{{ trans('g.projects_and_tasks') }}</h2>
                  </div>
                  <div class="card-body p-0 o-auto" style="max-height: 31.5rem">
<?php
if ($projects->count() == 0) {
?>
                    <div class="text-muted lead m-5">{{ trans('g.nothing_to_show') }}</div>

<?php
}
?>
                  <table class="table card-table table-hover">
<?php
$team_members = collect();

foreach ($projects as $project) {
  if ($project->completed_date === null) {
    $sl = \Platform\Controllers\Core\Secure::array2string(['project_id' => $project->id]);

    // Project manager(s)
    $users = $project->managers;
    $team_members = (isset($team_members)) ? $team_members->merge($users) : $users;
?>
                    <thead class="thead-dark">
                      <tr>
                        <th><a href="{{ url('projects/view/' . $sl) }}#tasks" class="text-inherit">{!! $project->status->bullet_name !!}</a></th>
                        <th class="text-right"><a href="{{ url('projects/view/' . $sl) }}#tasks" class="text-inherit">{{ $project->name }}</a></th>
                      </tr>
                    </thead>
<?php
    $tasks = $project->tasks->sortByDate('completed_date', true)->sortByDate('due_date', false)->sortByDesc('priority')->sortBy('project_status_id');

    foreach ($tasks as $task) {
      $completed = ($task->completed_date === null) ? false : true;

    // Task assignees
    $users = $task->assignees;
    $team_members = (isset($team_members)) ? $team_members->merge($users) : $users;

    // Link class
    $link_class = ($completed) ? 'text-muted' : 'text-inherit';
    $assignees = $task->assignees()->get()->pluck('id')->toArray();
    if (in_array(auth()->user()->id, $assignees)) {
      $link_class = ($completed) ? 'text-muted font-weight-bold' : 'text-inherit font-weight-bold';
    }

    $priority = '';
    if ($task->priority == 3) {
      $prio_class = ($completed) ? 'text-muted' : 'text-danger';
      $priority = '<i class="fas fa-exclamation-circle ' . $prio_class . ' mr-1" data-toggle="tooltip" data-title="' . trans('g.priority') . ': ' . trans('g.status_code')[$task->priority] . '"></i> ';
    } elseif ($task->priority == 2) {
      $prio_class = ($completed) ? 'text-muted' : 'text-warning';
      $priority = '<i class="fas fa-exclamation-circle ' . $prio_class . ' mr-1" data-toggle="tooltip" data-title="' . trans('g.priority') . ': ' . trans('g.status_code')[$task->priority] . '"></i> ';
    }
?>
                    <tr>
                      <td>{!! $priority !!} <a href="{{ url('projects/view/' . $sl . '?task=' . $task->id) }}#tasks" class="<?php echo $link_class; ?>">{!! $task->subject !!}</a></td>
                      <td class="text-right">
                        <span class="badge badge-secondary" style="background-color: {{ $task->status->color }}">{{ $task->status->name }}</span>
                      </td>
                    </tr>
<?php 
    }
  }
} 
?>
                  </table>
                  </div>
<?php 
$team_members = $team_members->unique('id');

$team_members = $team_members->sortBy(function ($user, $key) {
  return ! $user->active . ' ' . $user->roles->pluck('id')->min() . ' ' . $user->name;
}, SORT_FLAG_CASE);
?>
                </div>
              </div>
              <div class="col-md-6 col-lg-4">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">{{ trans('g.team_members') }} ({{ $team_members->count() }})</h3>
                  </div>
                  <div class="card-body o-auto" style="height: 15rem">
<?php
if ($team_members->count() == 0) {
?>
                      <div class="text-muted lead">
                        {{ trans('g.nothing_to_show') }}
                      </div>
<?php
}
?>
                    <ul class="list-unstyled list-separated">
<?php
foreach ($team_members as $user) {
?>
                      <li class="list-separated-item">
                        <div class="row align-items-center">
                          <div class="col-auto">
                            {!! $user->getAvatarHtml() !!}
                          </div>
                          <div class="col">
                            <div>
                              {{ $user->active_role_name }}
                            </div>
<?php if ($user->job_title !== null) { ?>
                            <small class="d-block item-except text-muted my-1">{{ $user->job_title }}</small>
<?php } ?>
                            <small class="d-block item-except text-sm text-muted h-1x"><i class="material-icons" style="font-size:12px; position:relative; top: 2px">alternate_email</i> {{ $user->email }}</small>
<?php if ($user->phone !== null) { ?>
                            <small class="d-block item-except text-sm text-muted h-1x"><i class="material-icons" style="font-size:12px; position:relative; top: 1px">phone</i> {{ $user->phone }}</small>
<?php } ?>
                          </div>
                        </div>
                      </li>
<?php } ?>
                    </ul>
                  </div>
                </div>
              </div>

            </div>
          </div>          
      
    </div>

@stop

@section('page_bottom')
@stop