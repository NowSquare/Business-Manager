@extends('../layouts.auth')

@section('page_title', __('Page Expired') . ' - ' . config('system.name'))

@section('content')

  <div class="container text-center">
    <div class="display-1 text-muted mb-5"><i class="fas fa-exclamation-triangle"></i> 419</div>
    <h1 class="h2 mb-6">{{ __('Sorry, your session has expired. Please refresh and try again.') }}</h1>
  </div>

@stop