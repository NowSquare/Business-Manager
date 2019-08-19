<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ url('modules/popups/assets/modal.css') }}">
@yield('head_end')
  <body>

@yield('content')

    <script src="{{ url('modules/popups/assets/modal.js') }}"></script>
  </body>
</html>