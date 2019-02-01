<link rel="stylesheet" type="text/css" href="{{ url('packages/barryvdh/elfinder/css/elfinder.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ url('packages/barryvdh/elfinder/css/theme.css') }}">
<link rel="stylesheet" type="text/css" href="{{ url('packages/Material/css/theme-gray.min.css') }}">
<script src="{{ url('packages/barryvdh/elfinder/js/elfinder.min.js') }}"></script>

<?php if (app()->getLocale() != 'en') { ?>
    <script src="{{ url('packages/barryvdh/elfinder/js/i18n/elfinder.' . app()->getLocale() . '.js') }}"></script>
<?php } ?>

<script type="text/javascript" charset="utf-8">
  $(function() {
    $('#elfinder').elfinder({
<?php if (app()->getLocale() != 'en') { ?>
      lang: '{{ app()->getLocale() }}',
<?php } ?>
      customData: { 
        _token: '{{ csrf_token() }}'
      },
      url : '{{ route("elfinder.connector") }}',
      soundPath: '{{ url('packages/barryvdh/elfinder/sounds') }}',
      resizable: false,
      rememberLastDir: false,
      useBrowserHistory: false,
      uiOptions: {
        toolbar : [
          ['back', 'forward'],
          ['home', 'up'],
          ['mkdir', 'upload'],
          ['paste'],
          ['rm'],
          ['duplicate', 'rename', 'edit'],
          ['extract', 'archive'],
          ['search'],
          ['view']
        ]
      },
      contextmenu : {
        files  : [
          'getfile', '|','open', '|', 'copy', 'cut', 'paste', 'duplicate', '|',
          'rm', '|', 'edit', 'rename', '|', 'archive', 'extract', '|', 'info'
        ]
      }
    });
  });
</script>