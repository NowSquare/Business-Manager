@extends('../layouts.auth')

@section('page_title', __('Error') . ' - ' . config('system.name'))

@section('content')

  <div class="container text-center">
    <div class="display-1 text-muted mb-5"><i class="fas fa-exclamation-triangle"></i> 500</div>
    <h1 class="h2 mb-6">{{ __('Whoops, something went wrong on our servers.') }}</h1>
  </div>

@stop