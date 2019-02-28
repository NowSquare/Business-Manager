<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>elFinder 2.0</title>

        <!-- jQuery and jQuery UI (REQUIRED) -->
        <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>

        <link rel="stylesheet" type="text/css" href="{{ url('packages/barryvdh/elfinder/css/elfinder.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ url('packages/barryvdh/elfinder/css/theme.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ url('packages/Material/css/theme-gray.min.css') }}">
        <script src="{{ url('packages/barryvdh/elfinder/js/elfinder.min.js') }}"></script>
        
        <!-- elFinder initialization (REQUIRED) -->
        <script type="text/javascript">
            var FileBrowserDialogue = {
                init: function() {
                    // Here goes your code for setting your custom things onLoad.
                },
                mySubmit: function (URL) {
                    // pass selected file path to TinyMCE
                    parent.tinymce.activeEditor.windowManager.getParams().setUrl(URL);

                    // close popup window
                    parent.tinymce.activeEditor.windowManager.close();
                }
            }

            $().ready(function() {
                var elf = $('#elfinder').elfinder({
            <?php if (app()->getLocale() != 'en') { ?>
                  lang: '{{ app()->getLocale() }}',
            <?php } ?>
                  customData: { 
                    _token: '{{ csrf_token() }}'
                  },
                  url : '{{ route("elfinder.connector") }}',
                  width: '100%',
                  height: '100%',
                  soundPath: '{{ url('packages/barryvdh/elfinder/sounds') }}',
                  getFileCallback: function(file) { // editor callback
                      FileBrowserDialogue.mySubmit(file.url); // pass selected file path to TinyMCE
                  },
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
                      ['search']
                    ]
                  },
                  contextmenu : {
                    files  : [
                      'getfile', '|','open', '|', 'copy', 'cut', 'paste', 'duplicate', '|',
                      'rm', '|', 'edit', 'rename', '|', 'archive', 'extract', '|', 'info'
                    ]
                  }
                }).elfinder('instance');
            });
        </script>
    </head>
    <body>

        <!-- Element where elFinder will be created (REQUIRED) -->
        <div id="elfinder"></div>

    </body>
</html>
