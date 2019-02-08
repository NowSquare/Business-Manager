<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Language" content="en" />
    <meta name="msapplication-TileColor" content="#146eff">
    <meta name="theme-color" content="#146eff">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page_title')</title>
    <meta name="description" content="@yield('page_description')">

    <link rel="shortcut icon" href="{{ $favicon }}" type="image/x-icon" />
    <link rel="icon" type="image/png" href="{{ $favicon_16 }}" sizes="16x16" />
    <link rel="icon" type="image/png" href="{{ $favicon_32 }}" sizes="32x32" />

    <link rel="stylesheet" href="{{ url('assets/css/app.css?' . config('system.client_side_timestamp')) }}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/solid.css" integrity="sha384-VGP9aw4WtGH/uPAOseYxZ+Vz/vaTb1ehm1bwx92Fm8dTrE+3boLfF1SpAtB1z7HW" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/regular.css" integrity="sha384-ZlNfXjxAqKFWCwMwQFGhmMh3i89dWDnaFU2/VZg9CvsMGA7hXHQsPIqS+JIAmgEq" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/brands.css" integrity="sha384-rf1bqOAj3+pw6NqYrtaE1/4Se2NBwkIfeYbsFdtiR6TQz0acWiwJbv1IM/Nt/ite" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/fontawesome.css" integrity="sha384-1rquJLNOM3ijoueaaeS5m+McXPJCGdr5HcA03/VHXxcp2kX2sUrQDmFc3jR5i/C7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <script src="{{ url('assets/js/app.js?' . config('system.client_side_timestamp')) }}"></script>

@yield('page_head')

  <body @yield('body_attr')>

    <div id="page_loader" class="dimmer active" style="position: fixed; left:0; top: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.85); z-index: 999999; display: none">
      <div class="loader"></div>
      <div style="margin: 2.5rem 0 0 0; position: absolute; top: 50%; text-align: center; width: 100%" class="text-muted" id="page_loader_text"></div>
    </div>

    <div class="page">
      <div class="page-main">

@include('../layouts.components.header-app')

@yield('content')

      </div>

@include('../layouts.components.footer')

    </div>

@yield('page_bottom')

</body>
</html>