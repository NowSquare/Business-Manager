@extends('../layouts.auth')

@section('page_title', config('system.name'))
@section('page_description', '')

@section('content')

  <div class="container">
    <div class="row">
      <div class="col mx-auto">
        <div class="text-center mb-6">
          <h3 class="display-3" style="cursor: pointer" onclick="document.location = '{{ url('login') }}';">
            <img src="{{ $system_icon }}" class="h-8 mb-2 mr-2" alt="{{ config('system.name') }}">
            {{ config('system.name') }}
          </h3>
        </div>
      </div>
    </div>
  </div>

@stop

@section('page_bottom')

@stop