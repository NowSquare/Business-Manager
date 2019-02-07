<link rel="stylesheet" type="text/css" href="{{ url('packages/barryvdh/elfinder/css/elfinder.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ url('packages/barryvdh/elfinder/css/theme.css') }}">
<link rel="stylesheet" type="text/css" href="{{ url('packages/Material/css/theme-gray.min.css') }}">
<script src="{{ url('packages/barryvdh/elfinder/js/elfinder.min.js') }}"></script>

<?php if (app()->getLocale() != 'en') { ?>
    <script src="{{ url('packages/barryvdh/elfinder/js/i18n/elfinder.' . app()->getLocale() . '.js') }}"></script>
<?php } ?>

<script type="text/javascript" charset="utf-8">
  $(function() {
    if ($('#elfinder').length) {
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
    }

    if ($('.selectFile').length) {
      $('.selectFile').each(function(index) {
        var $btn = $(this);
        var cb = $btn.attr('data-callback');
        var mimes = $btn.attr('data-mimes'); // e.g. ['image']
        var mimes_title = $btn.attr('data-mimes-title');
        if (typeof cb !== 'undefined') {
          $btn.on('click', function() {
            initElfinderModal(cb, mimes);
            
            if (typeof mimes_title !== 'undefined') {
              $('#elfinderModalTitleAddition').text('(' + mimes_title + ')');
            } else {
              $('#elfinderModalTitleAddition').text('');
            }
            $('#elfinderSelectFile').modal('show');
          });

        }
      });
    }

    function initElfinderModal(cb, mimes) {
      if (typeof mimes === 'undefined') {
        mimes = null;
      } else {
        mimes = JSON.parse(mimes);
      }

      var elfinderModal = $('#elfinderModal').elfinder({
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
        onlyMimes: mimes,
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
        commandsOptions: {
          getfile: {
            onlyURL: true,
            folders: false,
            multiple: false,
            oncomplete: 'destroy'
          }
        },
        getFileCallback: function (file) {
          window.parent.window[cb](file);
          $('#elfinderSelectFile').modal('hide');
        },
        handlers: {
          select : function(event, elfinderInstance) {
            var selected = event.data.selected;
            if (selected.length && selected.length == 1) {
              $('#elfinderModalSelectFile').prop('disabled', false);
              //console.log(elfinderInstance.file(selected[0]))
            } else {
              $('#elfinderModalSelectFile').prop('disabled', 1);
            }
          }
        },
      }).elfinder('instance');
    }

  });
</script>

<div class="modal" id="elfinderSelectFile" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-lg modal-dialog-centered" role="document">
    <div class="modal-content border-0 rounded-0 shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title">{{ trans('g.select_file') }} <small class="text-muted" id="elfinderModalTitleAddition"></small></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('g.close') }}">
        </button>
      </div>
      <div class="modal-body p-0">
        <div id="elfinderModal"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('g.close') }}</button>
        <button type="button" class="btn btn-primary" onClick="$('#elfinderModal').elfinder('instance').exec('getfile')" disabled id="elfinderModalSelectFile">{{ trans('g.select_file') }}</button>
      </div>
    </div>
  </div>
</div>