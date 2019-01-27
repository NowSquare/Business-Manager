@extends('../../layouts.app')

@section('page_title', trans('g.view_user') . ' - ' . $user->name . ' - ' . config('system.name'))

@section('page_head')
  <link rel="stylesheet" href="{{ url('assets/css/wysiwyg.css?' . config('system.client_side_timestamp')) }}">
  <script src="{{ url('assets/js/wysiwyg.js?' . config('system.client_side_timestamp')) }}"></script>
@stop

@section('content')

  <div class="my-3 my-md-5">
    <div class="container">
      <div class="row">

        <div class="col-12">

          {!! form_start($form) !!}

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">{{ $user->active_role_name }}</h3>
            </div>

            <div class="card-body p-0 px-3">

              <div class="tab-content">
                <div class="card-body px-3">
                  <div class="row">
                    <div class="col-md-6 col-lg-5">
                      {!! form_until($form, 'email') !!}
                    </div>
                    <div class="col-md-6 col-lg-5 ml-auto text-right">
                      <div class="m-0">
                        {!! form_until($form, 'avatar') !!}
                        <style type="text/css">
                          #imagePreviewAvatar {
                            margin: 0 !important;
                          }
                        </style>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-body px-3 pt-1 pb-0 mb-0">
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
              </div>

            </div>

            <div class="card-footer text-right">
                <button class="btn btn-secondary" type="button" onclick="document.location = '{{ url('users') }}';">{{ trans('g.back') }}</button>
<?php if ($can_access_user) { ?>
<?php if (auth()->user()->can('edit-user')) { ?>
              <a href="{{ url('users/edit/' . $sl) }}" class="btn btn-primary ml-1">{{ trans('g.edit_user') }}</a>
<?php } ?>
<?php if (auth()->user()->can('login-as-user')) { ?>
              <a href="{{ url('users/login/' . $sl) }}" class="btn btn-info ml-1">{{ trans('g.login_as_user') }}</a>
<?php } ?>
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