@extends('../../layouts.auth')

@section('page_title', trans('g.signup') . ' - ' . config('system.name'))
@section('page_description', trans('g.signup_header'))

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
        <form class="card" action="{{ url('register') }}" method="post">
          @csrf
          <div class="card-body p-6">
            <div class="card-title">{{ trans('g.signup_header') }}</div>

            @if(session()->has('error'))
            <div class="alert alert-danger rounded-0">
              {{ session()->get('error') }}
            </div>
            @endif

            <div class="form-group">
              <label class="form-label">{{ trans('g.name') }}</label>
              <input type="text" class="form-control" placeholder="{{ trans('g.enter_name') }}" name="name" value="{{ old('name') }}" required <?php if ($errors->isEmpty()) echo 'autofocus'; ?>>

              @if ($errors->has('name'))
              <span class="form-text text-danger">
                  <strong>{{ $errors->first('name') }}</strong>
              </span>
              @endif

            </div>
            <div class="form-group">
              <label class="form-label">{{ trans('g.email_address') }}</label>
              <input type="email" class="form-control" placeholder="{{ trans('g.enter_email') }}" name="email" value="{{ old('email') }}" required>

              @if ($errors->has('email'))
              <span class="form-text text-danger">
                  <strong>{{ $errors->first('email') }}</strong>
              </span>
              @endif

            </div>
            <div class="form-group">
              <label class="form-label">{{ trans('g.password') }}</label>
              <input type="password" class="form-control" placeholder="{{ trans('g.password') }}" name="password" required>

              @if ($errors->has('password'))
              <span class="form-text text-danger">
                  <strong>{{ $errors->first('password') }}</strong>
              </span>
              @endif

            </div>
            <div class="form-group">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" name="terms" id="terms" value="1" class="custom-control-input" {{ old('terms') ? 'checked' : '' }}>
                <label class="custom-control-label" for="terms">{!! trans('g.agree_to_terms', ['link' => url('terms')]) !!}</label>
              </div>

              @if ($errors->has('terms'))
              <span class="form-text text-danger">
                  <strong>{{ $errors->first('terms') }}</strong>
              </span>
              @endif

            </div>
            <div class="form-footer">
              <button type="submit" class="btn btn-primary btn-block">{{ trans('g.create_new_account') }}</button>
            </div>
          </div>
        </form>
        <div class="text-center text-muted">
          {{ trans('g.sign_in_cta') }} <a href="{{ url('login') }}">{{ trans('g.sign_in') }}</a>
        </div>
      </div>
    </div>
  </div>

@stop

@section('page_bottom')

@stop