@extends('../layouts.auth')

@section('page_title', __('Page Not Found') . ' - ' . config('system.name'))

@section('content')

  <div class="container text-center">
    <div class="display-1 text-muted mb-5"><i class="fas fa-exclamation-triangle"></i> 404</div>
    <h1 class="h2 mb-6">{{ __('Page Not Found') }}</h1>
    <a class="btn btn-primary" href="javascript:history.back()">
      <i class="fe fe-arrow-left mr-2"></i> {{ __('Go back') }}
    </a>
  </div>

@stop