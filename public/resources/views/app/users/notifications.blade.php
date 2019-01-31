@extends('../../layouts.app')

@section('page_title', trans('g.notifications') . ' - ' . config('system.name'))

@section('content')

  <div class="my-3 my-md-5">
    <div class="container">

      <div class="row row-cards row-deck">

        @if(session()->has('success'))
        <div class="alert alert-success rounded-0">
          {!! session()->get('success') !!}
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger rounded-0">
          {!! trans('g.form_error') !!}
        </div>
        @endif

        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">{{ trans('g.notifications') }} ({{ $total_count }})</h3>
            </div>
            <div class="table-responsive">
              <table class="table table-hover table-striped table-outline table-vcenter text-nowrap card-table">
                <tbody>
<?php
foreach ($notifications as $notification) {
  $created_by = \App\User::find($notification->data['created_by']);
  $avatar = '';
  if ($created_by !== null) {
    $avatar = (string) $created_by->getAvatarHtml();
  }

  $link = 'javascript:void(0);';
  switch ($notification->data['type']) {
    case 'project'; $link = url('projects/view/' . \Platform\Controllers\Core\Secure::array2string(['project_id' => $notification->data['project_id']])); break;
    case 'project-task'; $link = url('projects/view/' . \Platform\Controllers\Core\Secure::array2string(['project_id' => $notification->data['project_id']]) . '?task=' . $notification->data['task_id'] . '#tasks'); break;
    case 'project-comment'; $link = url('projects/view/' . \Platform\Controllers\Core\Secure::array2string(['project_id' => $notification->data['project_id']]) . '#comments'); break;
    case 'project-proposition'; $link = url('projects/view/' . \Platform\Controllers\Core\Secure::array2string(['project_id' => $notification->data['project_id']]) . '#proposition'); break;
  }
?>
                  <tr>
                    <td class="text-center" width="72">
                      {!! $avatar !!}
                    </td>
                    <td>
                      <div><a href="{{ $link }}" class="text-dark">{!! $notification->data['title'] !!}</a></div>
                      <div class="small text-muted">
                        {{ \Carbon\Carbon::createFromTimeStamp(strtotime($notification->created_at))->diffForHumans() }}
                      </div>
                    </td>
                  </tr>
<?php
}
?>
                </tbody>
              </table>

            </div>
<?php if ($total_count > $limit) { ?>
            <div class="card-footer text-right">
              {!! $pagination !!}
            </div>
<?php } ?>
          </div>
        </div>
      </div>

    </div>
  </div>
@stop

@section('page_bottom')
<script>
</script>
@stop