<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" dir="ltr">
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

    <title>{{ trans('newsletters::g.unsubscribed') }}</title>

    <link rel="stylesheet" href="{{ url('assets/css/app.css?' . config('system.client_side_timestamp')) }}">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="{{ url('assets/js/app.js?' . config('system.client_side_timestamp')) }}"></script>

  <body>

    <div class="page">
      <div class="page-main">

        <div class="container">
          <div class="row">
            <div class="col mx-auto mt-5">
              <div class="text-center my-5">
                <h3 class="display-4"><i class="material-icons" style="position: relative; top: 8px">unsubscribe</i> {{ trans('newsletters::g.unsubscribed') }}<?php if ($test == 1) echo ' <div class="badge badge-warning">TEST</div>'; ?></h3>
                <p class="lead font-weight-bold">{{ ($unsubscribed == 0) ? trans('newsletters::g.unsubscribed_msg') : trans('newsletters::g.unsubscribed_already_msg') }}</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
</body>
</html>