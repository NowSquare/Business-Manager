@extends('../layouts.auth')

@section('page_title', __('Service Unavailable') . ' - ' . config('system.name'))

@section('content')

  <div class="container text-center">
    <div class="display-1 text-muted mb-5"><i class="fas fa-exclamation-triangle"></i> 503</div>
    <h1 class="h2 mb-6">{{ __('Sorry, we are doing some maintenance. Please check back soon.') }}</h1>
  </div>

@stop