@extends('../../layouts.app')

@section('page_title', trans('g.edit_user') . ' - ' . $user->name . ' - ' . config('system.name'))

@section('page_head')
  <link rel="stylesheet" href="{{ url('assets/css/wysiwyg.css?' . config('system.client_side_timestamp')) }}">
  <script src="{{ url('assets/js/wysiwyg.js?' . config('system.client_side_timestamp')) }}"></script>
@stop

@section('content')

  <div class="my-3 my-md-5">
    <div class="container">
      <div class="row">

        <div class="col-12">

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

          {!! form_start($form) !!}

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">{{ $user->active_role_name }}</h3>
            </div>

            <div class="card-body p-0 px-3">

              <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item pl-5">
                  <a class="nav-link active" id="account-tab" data-toggle="tab" href="#account" role="tab" aria-selected="true">{{ trans('g.account') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="details-tab" data-toggle="tab" href="#details" role="tab" aria-selected="false">{{ trans('g.details') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="localization-tab" data-toggle="tab" href="#localization" role="tab" aria-selected="false">{{ trans('g.localization') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="notes-tab" data-toggle="tab" href="#user-notes" role="tab" aria-selected="false">{{ trans('g.notes') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="history-tab" data-toggle="tab" href="#history" role="tab" aria-selected="false">{{ trans('g.history') }}</a>
                </li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane fade show active px-3 pb-3" id="account" role="tabpanel" aria-labelledby="account-tab">
                  <div class="row">
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'active') !!}
                    </div>
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'role') !!}
<?php
$roles = \App\Role::all();
foreach ($roles as $role) {
?>
                      <div class="card d-none role-card" id="role{{ $role->id }}">
                        <div class="card-status card-status-left bg-blue" style="background-color: {{ $role->color }} !important"></div>
                        <div class="card-body">
                          {!! trans('g.role_description_' . $role->id, ['role' => '<strong>' . $role->name . '</strong>']) !!}
                        </div>
                      </div>
<?php
}
?>
<script>
$(function(){
  showRoleCard();

  $('#role').on('change', showRoleCard);

  function showRoleCard() {
    var r = $('#role').val();
    $('.role-card').hide();
    $('#role' + r).removeClass('d-none').show();

    $('#header4, #companies_wrapper, #projects_wrapper').show();
    switch (parseInt(r)) {
      case 5: 
        $('#header4, #companies_wrapper, #projects_wrapper').hide();
        break;
    }
  }

  $('.selectize-color').selectize({
    render: {
      option: function (data, escape) {
        return '<div>' +
        '<span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:' + data.color + '\'></span>' +
        '<span>' + escape(data.text) + '</span>' +
        '</div>';
      },
    item: function (data, escape) {
      return '<div>' +
        '<span style=\'display:inline-block;position:relative;top:0px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:' + data.color + '\'></span>' +
        '<span>' + escape(data.text) + '</span>' +
        '</div>';
      }
    }
  });
});
</script>
                    </div>
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'avatar') !!}
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade px-3 pb-3" id="details" role="tabpanel" aria-labelledby="details-tab">
                  <div class="row">
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'date_of_birth') !!}
                    </div>
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'country_code') !!}
                    </div>
                    <div class="col-md-6 col-lg-4">
                      {!! form_until($form, 'fax') !!}
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade px-3 pb-3" id="localization" role="tabpanel" aria-labelledby="localization-tab">
                  <div class="row">
                    <div class="col-md-6 col-lg-5">
                      {!! form_until($form, 'time_format') !!}
                    </div>
                    <div class="col-md-6 col-lg-5 offset-lg-1">
                      {!! form_until($form, 'seperators') !!}
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade py-3" id="user-notes" role="tabpanel" aria-labelledby="notes-tab">
                  {!! form_until($form, 'notes') !!}
                </div>
                <div class="tab-pane fade py-3" id="history" role="tabpanel" aria-labelledby="history-tab">
<?php if (count($user_actions) == 0 && count($user_log) == 0) { ?>
                  <h4 class="text-muted m-3">{{ trans('g.no_logs') }}</h4>
<?php } ?>
                  
<?php if (count($user_actions) > 0) { ?>
                  <table class="table table-striped table-hover table-borderless m-0">
                    <thead>
                      <tr>
                        <th colspan="3">{{ trans('g.actions') }} ({{ trans('g.last_number', ['number' => $limit_log]) }})</th>
                      </tr>
                    </thead>
<?php foreach ($user_actions as $log) { ?>
                    <tr>
                      <td class="align-middle text-center" width="64"><img src="{!! $log->user->getAvatar() !!}" class="avatar"></td>
                      <td class="align-middle">{!! $log->event !!}</td>
                      <td class="align-middle text-muted text-truncate text-center">IP {!! $log->ip_address !!}</td>
                      <td class="align-middle text-muted text-truncate text-right">{!! $log->created_at->timezone(auth()->user()->getTimezone())->format(auth()->user()->getUserDateFormat() . ' @ ' . auth()->user()->getUserTimeFormat()) !!}</td>
                    </tr>
<?php } ?>
                  </table>
<?php } ?>

<?php if (count($user_log) > 0) { ?>
                  <table class="table table-striped table-hover table-borderless m-0">
                    <thead>
                      <tr>
                        <th colspan="3">{{ trans('g.log') }} ({{ trans('g.last_number', ['number' => $limit_log]) }})</th>
                      </tr>
                    </thead>
<?php foreach ($user_log as $log) { ?>
                    <tr>
                      <td class="align-middle text-center" width="64"><img src="{!! $log->user->getAvatar() !!}" class="avatar"></td>
                      <td class="align-middle">{!! $log->event !!}</td>
                      <td class="align-middle text-muted text-truncate text-center">IP {!! $log->ip_address !!}</td>
                      <td class="align-middle text-muted text-truncate text-right">{!! $log->created_at->timezone(auth()->user()->getTimezone())->format(auth()->user()->getUserDateFormat() . ' @ ' . auth()->user()->getUserTimeFormat()) !!}</td>
                    </tr>
<?php } ?>
                  </table>
<?php } ?>

                </div>
              </div>

            </div>

            <div class="card-footer text-right">
                {!! form_rest($form) !!}
<?php if (auth()->user()->can('login-as-user')) { ?>
              <a href="{{ url('users/login/' . $sl) }}" class="btn btn-info ml-1">{{ trans('g.login_as_user') }}</a>
<?php } ?>
            </div>
          </div>

          {!! form_end($form) !!}

        </div>

      </div>
    </div>
  </div>
@stop

@section('page_bottom')
@stop