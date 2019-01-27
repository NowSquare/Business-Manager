@extends('../../layouts.auth')

@section('page_title', trans('g.reset_password') . ' - ' . config('system.name'))
@section('page_description', trans('g.reset_password_desc'))

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

      <form class="card" action="{{ url('password/email') }}" method="post">
        @csrf
        <div class="card-body p-6">
          <div class="card-title">{{ trans('g.reset_password_header') }}</div>

          <p class="text-muted">{{ trans('g.reset_password_desc') }}</p>

          @if (session('status'))
          <div class="alert alert-success rounded-0">
            {{ session('status') }}
          </div>
          @endif

          <div class="form-group">
            <label class="form-label">{{ trans('g.email_address') }}</label>
            <input type="email" class="form-control" name="email" id="email" placeholder="{{ trans('g.enter_email') }}" value="{{ old('email') }}" required autofocus>

            @if ($errors->has('email'))
            <span class="form-text text-danger">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
            @endif

          </div>

          <div class="form-footer">
            <button type="submit" class="btn btn-primary btn-block">{{ trans('g.send_reset_link') }}</button>
          </div>
        </div>
      </form>
      <div class="text-center text-muted">
        <a href="{{ url('login') }}">{{ trans('g.back_to_login') }}</a>
      </div>
    </div>
  </div>
</div>

@stop

@section('page_bottom')

@stop