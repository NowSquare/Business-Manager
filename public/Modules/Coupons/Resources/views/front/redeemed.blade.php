<!DOCTYPE html>
<html lang="{{ \App::getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />

    <link rel="stylesheet" href="{{ url('modules/coupons/assets/style.css') }}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/regular.css" integrity="sha384-ZlNfXjxAqKFWCwMwQFGhmMh3i89dWDnaFU2/VZg9CvsMGA7hXHQsPIqS+JIAmgEq" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/brands.css" integrity="sha384-rf1bqOAj3+pw6NqYrtaE1/4Se2NBwkIfeYbsFdtiR6TQz0acWiwJbv1IM/Nt/ite" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/fontawesome.css" integrity="sha384-1rquJLNOM3ijoueaaeS5m+McXPJCGdr5HcA03/VHXxcp2kX2sUrQDmFc3jR5i/C7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <script src="{{ url('modules/coupons/assets/scripts.js') }}"></script>

    <title>{{ $coupon->name }}</title>

    <link rel="canonical" href="{{ url()->full() }}">

<?php if ($coupon->favicon_file_name != null) { ?>
    <link rel="icon" href="{{ $coupon->favicon->url('16') }}" sizes="16x16">
    <link rel="icon" href="{{ $coupon->favicon->url('32') }}" sizes="32x32">
    <link rel="icon" href="{{ $coupon->favicon->url('96') }}" sizes="96x96">
<?php } elseif ($coupon->image_file_name != null) { ?>
    <link rel="icon" type="image/png" href="{{ url($coupon->image->url('favicon')) }}" />
<?php } ?>
    <meta name="theme-color" content="{{ $coupon->additional_fields['primary_bg_color'] ?? '#000000' }}">

  </head>
  <body>
    <div class="container max-width-600">
      <div class="row">
        <div class="col-12">
          <div class="mt-4 mt-md-5">
            <h1 class="mb-0 mb-md-2 text-center">{!! $coupon->name !!}</h1>
            <p class="lead text-center">{{ trans('coupons::g.succesfully_redeemed_coupon') }}</p>
            <div class="text-center">
              <a href="{{ url('coupon/' . $slug) }}">
                <i class="material-icons text-success" style="font-size: 14rem">check_circle_outline</i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>