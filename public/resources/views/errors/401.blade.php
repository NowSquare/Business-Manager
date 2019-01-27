@extends('../layouts.auth')

@section('page_title', __('Unauthorized') . ' - ' . config('system.name'))

@section('content')

  <div class="container text-center">
    <div class="display-1 text-muted mb-5"><i class="fas fa-exclamation-triangle"></i> 401</div>
    <h1 class="h2 mb-6">{{ __('Sorry, you are not authorized to access this page.') }}</h1>
    <a class="btn btn-primary" href="javascript:history.back()">
      <i class="fe fe-arrow-left mr-2"></i> {{ __('Go back') }}
    </a>
  </div>

@stop