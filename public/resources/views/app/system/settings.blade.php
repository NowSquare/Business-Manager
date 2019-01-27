@extends('../../layouts.app')

@section('page_title', trans('g.settings') . ' - ' . config('system.name'))

@section('content')

  <div class="my-3 my-md-5">
    <div class="container">
      <div class="row">

        <div class="col-12">

          @if(session()->has('success'))
          <div class="alert alert-success rounded-0">
            {!! session()->get('success') !!}
          </div>
          @endif

          @if(session()->has('warning'))
          <div class="alert alert-warning rounded-0">
            {!! session()->get('warning') !!}
          </div>
          @endif

          @if ($errors->any())
          <div class="alert alert-danger rounded-0">
            {!! trans('g.form_error') !!}
          </div>
          @endif

          {!! form_start($form) !!}

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">{{ trans('g.system_settings') }}</h3>
            </div>

            <div class="card-body p-0 px-3">

              <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item pl-5">
                  <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-selected="true">{{ trans('g.general') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="update-tab" data-toggle="tab" href="#update" role="tab" aria-selected="true">{{ trans('g.update') }}</a>
                </li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane fade show active px-3 pt-5 pb-3" id="general" role="tabpanel" aria-labelledby="general-tab">
                  <div class="row">
                    <div class="col-md-6 col-lg-5">
                      {!! form_until($form, 'system_icon') !!}
                    </div>
                    <div class="col-md-6 col-lg-5 offset-lg-1">
                      {!! form_until($form, 'system_signup') !!}
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade px-3 pt-5 pb-3" id="update" role="tabpanel" aria-labelledby="update-tab">
                  <div class="row">
                    <div class="col-md-8">
                      <h4>{{ trans('g.current_version', ['version' => $version]) }}</h4>
                      <p class="text-muted">{{ trans('g.update_text') }}</p>
                      <button type="button" id="run_migrations" class="btn btn-success btn-lg mb-3">{{ trans('g.run_migrations') }}</button>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <div class="card-footer text-right">
                {!! form_rest($form) !!}
            </div>
          </div>

          {!! form_end($form) !!}

        </div>

      </div>
    </div>
  </div>

@stop

@section('page_bottom')
<script>
$(function() {
  $('#run_migrations').on('click', function() {
    var $btn = $(this);
    $btn.prop('disabled', 1);
    $btn.text("{{ trans('g.running_migrations') }}");

    var jqxhr = $.ajax({
      url: "{{ url('settings/run-migrations') }}",
      data: {_token: '<?= csrf_token() ?>'},
      method: 'POST'
    })
    .done(function(data) {
      $btn.text("{{ trans('g.ready') }}");
    })
    .fail(function() {
      $btn.prop('disabled', null);
      alert('An error occurred. Please reload this page.');
    });

  });
});
</script>
@stop