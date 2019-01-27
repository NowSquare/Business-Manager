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

          @if(session()->has('success'))
          <div class="alert alert-success rounded-0">
            {!! session()->get('success') !!}
          </div>
          @else

          <p class="text-muted">{{ trans('g.email_not_verified') }}</p>

          <div class="form-footer">
            <a href="{{ url('email/resend') }}" class="btn btn-primary btn-block">{{ trans('g.resend_verification_email') }}</a>
          </div>
          @endif

        </div>
      </div>
    </div>
  </div>
</div>

@stop

@section('page_bottom')

@stop