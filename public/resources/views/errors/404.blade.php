@extends('../layouts.auth')

@section('page_title', __('Page Not Found') . ' - ' . config('system.name'))

@section('content')

  <div class="container text-center">
    <span style="background-color: rgba(255,255,255,0.8); display: inline-block" class="mdl-shadow--2dp">
      <div class="display-1 text-muted mx-5 my-3"><i class="fas fa-exclamation-triangle"></i> 404</div>
      <h1 class="h2 mx-5 mb-6">{{ __('Page Not Found') }}</h1>
    </span>
  </div>

@stop