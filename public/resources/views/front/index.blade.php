@extends('../layouts.auth')

@section('page_title', config('system.name'))
@section('page_description', '')

@section('content')

  <div class="container">
    <div class="row">
      <div class="col mx-auto">
        <div class="text-center mb-6">
          <span style="background-color: rgba(255,255,255,0.7); display: inline-block" class="mdl-shadow--2dp">
            <h3 class="display-3 mx-5 my-3" style="cursor: pointer" onclick="document.location = '{{ url('login') }}';">
              <img src="{{ url('assets/img/branding/icon.svg') }}" class="h-8 mb-2 mr-2" alt="{{ config('system.name') }}">
              {{ config('system.name') }}
            </h3>
          </span>
        </div>
      </div>
    </div>
  </div>

@stop

@section('page_bottom')

@stop