@extends('../../layouts.app')

@section('page_title', $title . ' - ' . config('system.name'))

@section('page_head')
  <link rel="stylesheet" href="{{ url('modules/newsletters/assets/editor.css?' . config('system.client_side_timestamp')) }}">
@stop

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

          @if ($errors->any())
          <div class="alert alert-danger rounded-0">
            {!! trans('g.form_error') !!}
          </div>
          @endif

          {!! form_start($form) !!}

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">{{ $title }}</h3>
              <div class="card-options">
                <button type="button" class="btn btn-success mr-2" id="sendTest">{{ trans('newsletters::g.send_test_email') }}</button>
              </div>
            </div>

            <div class="card-body p-0">

              <ul class="nav nav-tabs mx-0" role="tablist">
                <li class="nav-item pl-5">
                  <a class="nav-link" id="design-tab" data-toggle="tab" href="#design" role="tab" aria-selected="false">{{ trans('newsletters::g.newsletter') }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="settings-tab" data-toggle="tab" href="#settings" role="tab" aria-selected="false">{{ trans('g.settings') }}</a>
                </li>
              </ul>

              <div class="tab-content">

                <div class="tab-pane p-0" id="design" role="tabpanel" aria-labelledby="design-tab">

                  <div id="editor_container">
                    <div id="gjs">
                      {!! $content !!}
                    </div>
                  </div>

                </div>

                <div class="tab-pane px-5 pt-5 pb-3" id="settings" role="tabpanel" aria-labelledby="settings-tab">

                  {!! form_row($form->style) !!}
                  {!! form_row($form->content) !!}

                  <div class="row">
                    <div class="col-md-12 col-lg-8">
                      {!! form_row($form->name) !!}
                    </div>
                  </div>

                  <legend class="mt-5">{{ trans('newsletters::g.email') }}</legend>

                  <div class="row">
                    <div class="col-md-12 col-lg-8">
                      {!! form_row($form->subject) !!}
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 col-lg-4">
                      {!! form_row($form->from_name) !!}
                    </div>
                    <div class="col-md-6 col-lg-4">
                      {!! form_row($form->from_email) !!}
                    </div>
                  </div>

                  <legend class="mt-5">{{ trans('newsletters::g.recepients') }}</legend>
                  <p class="text-muted">{{ trans('newsletters::g.recepients_info') }}</p>

                  <div class="row">
                    <div class="col-md-6 col-lg-4">
                      {!! form_row($form->sources) !!}
                    </div>
                    <div class="col-md-6 col-lg-4">
                      {!! form_row($form->roles) !!}
                    </div>
                    <div class="col-md-6 col-lg-4">
                      {!! form_row($form->users) !!}
                    </div>
                  </div>

                </div>
              </div>

            </div>
            <div class="card-footer text-right">
                {!! form_row($form->back) !!}
                {!! form_row($form->submit) !!}
            </div>
          </div>

          </form>

        </div>
      </div>
    </div>
  </div>
@stop

@section('page_bottom')
  <script src="{{ url('modules/newsletters/assets/editor.js?' . config('system.client_side_timestamp')) }}"></script>
<script>

$(function() {
  editor.addComponents(`
  <style type="text/css">{!! $style !!}</style>
  `);

  $('#gjs').on('keypress', 'input', function(e) {
    if (e.keyCode == 13) {
      return false;
    }
  });

  $('#frmPost').submit(function() {
    //var content = editor.runCommand('gjs-get-inlined-html');
    var content = editor.getHtml();
    var style = editor.getCss();
    $('#content').val(content);
    $('#style').val(style);
  });

  $('#sendTest').on('click', function() {
    //var content = editor.runCommand('gjs-get-inlined-html');
    var content = editor.getHtml();
    var style = editor.getCss();
    var subject = $('#subject').val();
    var from_name = $('#from_name').val();
    var from_email = $('#from_email').val();

    if (subject == '') {
      $('#settings-tab').trigger('click');
      $('#subject').addClass('is-invalid');
      $('#subject').focus();
      return;
    } else {
      $('#subject').removeClass('is-invalid');
    }

    if (from_name == '') {
      $('#settings-tab').trigger('click');
      $('#from_name').addClass('is-invalid');
      $('#from_name').focus();
      return;
    } else {
      $('#from_name').removeClass('is-invalid');
    }

    if (from_email == '') {
      $('#settings-tab').trigger('click');
      $('#from_email').addClass('is-invalid');
      $('#from_email').focus();
      return;
    } else {
      $('#from_email').removeClass('is-invalid');
    }

    Swal({
      title: "{!! trans('newsletters::g.send_test_email') !!}",
      input: 'email',
      inputPlaceholder: "{{ trans('newsletters::g.email_address') }}",
      inputValue: '{{ auth()->user()->email }}',
      imageUrl: "{{ url('assets/img/icons/fe/send.svg') }}",
      imageWidth: 48,
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: "{!! trans('g.send') !!}"
    }).then((result) => {
      if (result.value) {
        var jqxhr = $.ajax({
          url: "{{ url('newsletters/test') }}",
          data: {
            subject: subject,
            from_name: from_name,
            from_email: from_email,
            to: result.value,
            content: content,
            style: style,
            _token: '<?= csrf_token() ?>'
          },
          method: 'POST'
        })
        .done(function(data) {
          if(typeof data.msg !== 'undefined') {
            Swal({ imageUrl: "{{ url('assets/img/icons/fe/send.svg') }}", imageWidth: 48, title: "{!! trans('g.sent') !!}", text: data.msg });
          }
        })
        .fail(function() {
          console.log('error');
        })
        .always(function() {
        });
      }
    });
  });

  fitEditor();

  $(window).resize(fitEditor);

  function fitEditor() {
    var height = (parseInt($(window).height()) > 1019) ? parseInt($(window).height()) - 439 : 580;
    $('#editor_container').css('height', height);
  }
});
</script>
@stop