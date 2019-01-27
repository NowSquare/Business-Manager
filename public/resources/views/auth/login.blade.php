@extends('../../layouts.auth')

@section('page_title', trans('g.login') . ' - ' . config('system.name'))
@section('page_description', trans('g.login_header'))

@section('content')
<?php
$system_signup = \Platform\Controllers\Core\Settings::get('system_signup', 'boolean', 1);
?>
<div class="container">
  <div class="row">
    <div class="col col-login mx-auto">
      <div class="text-center mb-6">
         <a class="header-brand" href="{{ url('/') }}">
          <img src="{{ $system_icon }}" class="h-6 mb-1 mr-1" alt="{{ config('system.name') }}">
          {{ config('system.name') }}
        </a>
      </div>

      <form class="card" action="{{ url('login') }}" method="post">
        @csrf
        <div class="card-body p-6">
          <div class="card-title">{{ trans('g.login_header') }}</div>

          @if(session()->has('error'))
          <div class="alert alert-danger rounded-0">
            {!! session()->get('error') !!}
          </div>
          @endif

          @if(session()->has('success'))
          <div class="alert alert-success rounded-0">
            {!! session()->get('success') !!}
          </div>
          @endif

<?php if (env('DEMO', false)) { ?>
          <div class="alert alert-warning rounded-0">
            {!! trans('g.demo_mode_login') !!}
          </div>
<?php } ?>

          <div class="form-group">
            <label class="form-label">{{ trans('g.email_address') }}</label>
            <input type="email" class="form-control" name="email" id="email" placeholder="{{ trans('g.enter_email') }}" value="{{ old('email', env('DEMO', false) ? 'info@example.com' : '') }}" required <?php if ($errors->isEmpty()) echo 'autofocus'; ?>>

            @if ($errors->has('email'))
            <span class="form-text text-danger">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
            @endif

          </div>
          <div class="form-group">
            <label class="form-label">
              {{ trans('g.password') }}
              <a href="{{ url('password/reset') }}" class="float-right small" tabindex="-1">{{ trans('g.forgot_password') }}</a>
            </label>
            <input type="password" class="form-control" name="password" id="password" placeholder="{{ trans('g.password') }}" value="{{ env('DEMO', false) ? 'welcome' : '' }}" required>

            @if ($errors->has('password'))
            <span class="form-text text-danger">
                <strong>{{ $errors->first('password') }}</strong>
            </span>
            @endif

          </div>
          <div class="form-group">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" name="remember" value="1" class="custom-control-input" {{ old('remember') ? 'checked' : '' }}>
              <span class="custom-control-label">{{ trans('g.remember_me') }}</span>
            </label>
          </div>
          <div class="form-footer">
            <button type="submit" class="btn btn-primary btn-block">{{ trans('g.sign_in') }}</button>
          </div>
        </div>
      </form>
<?php if ($system_signup === 1) { ?>
      <div class="text-center text-muted">
        {{ trans('g.sign_up_cta') }} <a href="{{ url('register') }}">{{ trans('g.sign_up') }}</a>
      </div>
<?php } ?>
    </div>
  </div>
</div>

@stop

@section('page_bottom')

@stop