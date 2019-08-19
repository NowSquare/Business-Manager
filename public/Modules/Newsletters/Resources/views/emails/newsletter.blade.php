<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style type="text/css">
    {!! $style !!}
    </style>
  </head>
  <body>
    {!! $content !!}

    <div style="text-align: center; margin: 1rem">
      <a href="{{ $unsubscribe_url }}" style="font-size:11px; color: #333">{{ trans('newsletters::g.unsubscribe') }}</a>
    </div>
  </body>
</html>