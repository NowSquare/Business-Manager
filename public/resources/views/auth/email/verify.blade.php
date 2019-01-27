@extends('../../layouts.auth')

@section('page_title', trans('g.verify_email_address') . ' - ' . config('system.name'))

@section('content')

<div class="container">
  <div class="row">
    <div class="col col-login mx-auto">
      <div class="text-center mb-6">
         <a class="header-brand" href="{{ url('/') }}">
          <img src="{{ $system_icon }}" class="h-6 mb-1 mr-1" alt="{{ config('system.name') }}">
          {{ config('system.name') }}
        </a>
      </div>

      <div class="card">
        <div class="card-body p-6">
          <div class="card-title">{{ trans('g.verify_email_address') }}</div>

          @if ($success)
          <div class="alert alert-success rounded-0">
            {!! $success !!}
          </div>
          @endif

          @if ($error)
          <div class="alert alert-danger rounded-0">
            {!! $error !!}
          </div>
          @endif

          <div class="form-footer">
            <a href="{{ url('login') }}" class="btn btn-primary btn-block">{{ trans('g.go_to_login') }}</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@stop

@section('page_bottom')

@stop