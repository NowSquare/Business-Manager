        <div class="header py-4">
          <div class="container">
            <div class="d-flex">
              <a class="header-brand" href="{{ url('dashboard') }}">
                <img src="{{ $system_icon }}" class="header-brand-img" alt="{{ config('system.name') }}">
                {{ config('system.name') }}
              </a>
              <div class="nav-item d-none d-md-flex">
                <i class="material-icons mr-1 text-muted" style="font-size: 18px">access_time</i><span onclick="document.location.href = '{{ url('profile#localization') }}';" class="text-muted" id="app_clock"></span>
              </div>
<script>
updateClock();
function updateClock() {
  var now = moment().tz('{{ auth()->user()->getTimezone() }}');
<?php if (auth()->user()->getUserTimeFormat() == 'g:i a') { ?>
  $('#app_clock').html('<span style="font-size: 21px">' + now.format('h:mm') + '</span> ' + now.format('a') + '');
<?php } else if (auth()->user()->getUserTimeFormat() == 'g:i A') { ?>
  $('#app_clock').html('<span style="font-size: 21px">' + now.format('h:mm') + '</span> ' + now.format('A') + '');
<?php } else { ?>
  $('#app_clock').html('<span style="font-size: 21px">' + now.format('HH:mm') + '</span>');
<?php } ?>

  setTimeout(updateClock, 1000);
}
</script>
              <div class="d-flex order-lg-2 ml-auto">
<?php if (auth()->user()->notifications()->count() > 0) { ?>
                <div class="dropdown d-none d-md-flex">
                  <a class="nav-link icon" data-toggle="dropdown" data-target="#">
                    <i class="fe fe-bell"></i>
<?php if (auth()->user()->unreadNotifications()->count() > 0) { ?>
                    <span class="nav-unread"></span>
<?php } ?>
                  </a>
                  <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
<?php
foreach (auth()->user()->unreadNotifications()->take(10)->get() as $notification) {
  $created_by = \App\User::find($notification->data['created_by']);
  $avatar = '';
  if ($created_by !== null) {
    $avatar = (string) $created_by->getAvatar();
  }

  $link = 'javascript:void(0);';
  switch ($notification->data['type']) {
    case 'project'; $link = url('projects/view/' . \Platform\Controllers\Core\Secure::array2string(['project_id' => $notification->data['project_id']])); break;
    case 'project-task'; $link = url('projects/view/' . \Platform\Controllers\Core\Secure::array2string(['project_id' => $notification->data['project_id']]) . '?task=' . $notification->data['task_id'] . '#tasks'); break;
    case 'project-comment'; $link = url('projects/view/' . \Platform\Controllers\Core\Secure::array2string(['project_id' => $notification->data['project_id']]) . '#comments'); break;
    case 'project-proposition'; $link = url('projects/view/' . \Platform\Controllers\Core\Secure::array2string(['project_id' => $notification->data['project_id']]) . '#proposition'); break;
  }
?>
                    <a href="{{ $link }}" class="dropdown-item d-flex">
                      <span class="avatar mr-3 align-self-center" style="background-image: url({{ $avatar }})"></span>
                      <div>
                        {!! str_limit($notification->data['title'], 42) !!}
                        <div class="small text-muted">{{ \Carbon\Carbon::createFromTimeStamp(strtotime($notification->created_at))->diffForHumans() }}</div>
                      </div>
                    </a>
<?php } ?>
<?php if (auth()->user()->unreadNotifications()->count() > 0) { ?>
                    <div class="dropdown-divider"></div>
<?php } ?>
                    <a href="{{ url('notifications') }}" class="dropdown-item text-center text-muted-dark">{{ trans('g.view_notifications') }}</a>
                  </div>
                </div>
<?php } ?>
                <div class="dropdown">
                  <a href="javascript:void(0);" class="nav-link pr-0 leading-none" data-toggle="dropdown" data-target="#">
                    <span class="avatar" style="background-image: url('{{ auth()->user()->getAvatar() }}')"></span>
                    <span class="ml-2 d-none d-lg-block">
                      <span class="text-default">{{ auth()->user()->name }}</span>
                      <small class="text-muted d-block mt-1">{{ auth()->user()->email }}</small>
                    </span>
                  </a>
                  <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
<?php if (auth()->user()->can('access-profile')) { ?>
                    <a class="dropdown-item" href="{{ url('profile') }}">
                      <i class="dropdown-icon fe fe-user"></i> {{ trans('g.profile') }}
                    </a>
<?php } ?>
<?php if (auth()->user()->can('access-settings')) { ?>
                    <a class="dropdown-item" href="{{ url('settings') }}">
                      <i class="dropdown-icon fe fe-settings"></i> {{ trans('g.settings') }}
                    </a>
<?php } ?>
<?php if (auth()->user()->hasRole(\App\Role::find(1))) { ?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ url('uploads') }}">
                      <i class="dropdown-icon fe fe-folder"></i> {{ trans('g.uploads') }}
                    </a>
<?php } ?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ url('logout') }}">
                      <i class="dropdown-icon fe fe-log-out"></i> {{ trans('g.sign_out') }}
                    </a>
                  </div>
                </div>
              </div>
              <a href="#" class="header-toggler d-lg-none ml-3 ml-lg-0" data-toggle="collapse" data-target="#headerMenuCollapse">
                <span class="header-toggler-icon"></span>
              </a>
            </div>
          </div>
        </div>
        <div class="header collapse d-lg-flex p-0" id="headerMenuCollapse">
          <div class="container">
            <div class="row align-items-center">
<?php /*
              <div class="col-lg-3 ml-auto">
                <form class="input-icon my-3 my-lg-0">
                  <input type="search" class="form-control header-search" placeholder="Search&hellip;" tabindex="1">
                  <div class="input-icon-addon">
                    <i class="fe fe-search"></i>
                  </div>
                </form>
              </div>
*/ ?>
              <div class="col-lg order-lg-first">
                <ul class="nav nav-tabs border-0 flex-column flex-lg-row">
                  <li class="nav-item">
                    <a href="{{ url('dashboard') }}" class="nav-link<?php if (\Request::route()->getName() == 'dashboard') echo ' active'; ?>"><i class="material-icons">dashboard</i> {{ trans('g.dashboard') }}</a>
                  </li>
<?php if (auth()->user()->can('list-users')) { ?>
                  <li class="nav-item">
                    <a href="{{ url('users') }}" class="nav-link<?php if (\Request::route()->getName() == 'users') echo ' active'; ?>"><i class="material-icons">people</i> {{ trans('g.people') }}</a>
                  </li>
<?php } ?>
<?php if (auth()->user()->can('list-companies')) { ?>
                  <li class="nav-item">
                    <a href="{{ url('companies') }}" class="nav-link<?php if (\Request::route()->getName() == 'companies') echo ' active'; ?>"><i class="material-icons">business</i> {{ trans('g.companies') }}</a>
                  </li>
<?php } ?>
<?php if (auth()->user()->can('list-projects')) { ?>
                  <li class="nav-item">
                    <a href="{{ url('projects') }}" class="nav-link<?php if (\Request::route()->getName() == 'projects') echo ' active'; ?>"><i class="material-icons">work</i> {{ trans('g.projects') }}</a>
                  </li>
<?php } ?>
<?php if (auth()->user()->can('list-invoices')) { ?>
                  <li class="nav-item">
                    <a href="{{ url('invoices') }}" class="nav-link<?php if (\Request::route()->getName() == 'invoices') echo ' active'; ?>"><i class="material-icons">receipt</i> {{ trans('g.invoices') }}</a>
                  </li>
<?php } ?>
                </ul>
              </div>
            </div>
          </div>
        </div>