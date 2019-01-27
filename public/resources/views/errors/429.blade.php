@extends('../layouts.auth')

@section('page_title', __('Too Many Requests') . ' - ' . config('system.name'))

@section('content')

  <div class="container text-center">
    <div class="display-1 text-muted mb-5"><i class="fas fa-exclamation-triangle"></i> 429</div>
    <h1 class="h2 mb-6">{{ __('Sorry, you are making too many requests to our servers.') }}</h1>
  </div>

@stop