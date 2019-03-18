@extends('../../layouts.auth')

@section('page_title', trans('g.reset_password') . ' - ' . config('system.name'))
@section('page_description', trans('g.reset_password_desc'))

@section('content')

<div class="container">
  <div class="row">
    <div class="col col-login mx-auto">
      <form class="card" action="{{ url('password/reset') }}" method="post">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="card-body p-6">
          <div class="text-center mb-6">
             <a class="header-brand" href="{{ url('/') }}">
              <img src="{{ $system_icon }}" class="h-6 mb-1 mr-1" alt="{{ config('system.name') }}">
              {{ config('system.name') }}
            </a>
          </div>

          <div class="card-title">{{ trans('g.reset_password') }}</div>

          @if (session('status'))
          <div class="alert alert-success rounded-0">
            {{ session('status') }}
          </div>
          @endif

          <div class="form-group">
            <label class="form-label" for="email">{{ trans('g.email_address') }}</label>
            <input type="email" class="form-control" placeholder="{{ trans('g.enter_email') }}" name="email" id="email" value="{{ old('email', $email) }}" required>

            @if ($errors->has('email'))
            <span class="form-text text-danger">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
            @endif

          </div>

          <div class="form-group">
            <label class="form-label" for="password">{{ trans('g.new_password') }}</label>
            <input type="password" class="form-control" placeholder="{{ trans('g.password') }}" name="password" id="password" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="password_confirmation">{{ trans('g.confirm_password') }}</label>
            <input type="password" class="form-control" placeholder="{{ trans('g.password') }}" name="password_confirmation" id="password_confirmation" required>

            @if ($errors->has('password'))
            <span class="form-text text-danger">
                <strong>{{ $errors->first('password') }}</strong>
            </span>
            @endif

            @if ($errors->has('password_confirmation'))
            <span class="form-text text-danger">
                <strong>{{ $errors->first('password_confirmation') }}</strong>
            </span>
            @endif

          </div>          
          
          <div class="form-footer">
            <button type="submit" class="btn btn-primary btn-block">{{ trans('g.reset_password') }}</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@stop

@section('page_bottom')

@stop