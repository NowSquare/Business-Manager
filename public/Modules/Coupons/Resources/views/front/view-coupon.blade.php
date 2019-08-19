<!DOCTYPE html>
<html itemscope itemtype="http://schema.org/Article">
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

    <title>{{ $coupon->title }}</title>
    <link rel="canonical" href="{{ url()->full() }}">
<?php if ($ga_code != '') { ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga_code }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ $ga_code }}');
    </script>
<?php } ?>
<?php if ($fb_pixel != '') { ?>
<!-- Facebook Pixel Code -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '{{ $fb_pixel }}');
  fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id={{ $fb_pixel }}&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->
<?php } ?>

<?php if ($coupon->favicon_file_name != null) { ?>
    <link rel="icon" href="{{ $coupon->favicon->url('16') }}" sizes="16x16">
    <link rel="icon" href="{{ $coupon->favicon->url('32') }}" sizes="32x32">
    <link rel="icon" href="{{ $coupon->favicon->url('96') }}" sizes="96x96">
<?php } elseif ($coupon->image_file_name != null) { ?>
    <link rel="icon" type="image/png" href="{{ url($coupon->image->url('favicon')) }}" />
<?php } ?>

    <meta name="description" content="{!! $description !!}">
    <meta name="theme-color" content="{{ $coupon->additional_fields['primary_bg_color'] ?? '#000000' }}">

    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $coupon->title }}">
    <meta itemprop="description" content="{!! $description !!}">
<?php if ($coupon->image_file_name != null) { ?>
    <meta itemprop="image" content="{{ url($coupon->image->url('preview')) }}">
<?php } ?>

    <!-- Twitter Card data -->
<?php if ($coupon->image_file_name != null) { ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image:src" content="{{ url($coupon->image->url('preview')) }}">
<?php } else { ?>
    <meta name="twitter:card" content="summary">
<?php } ?>
    <meta name="twitter:title" content="{{ $coupon->title }}">
    <meta name="twitter:description" content="{!! $description !!}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $coupon->title }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->full() }}">
    <meta property="og:description" content="{!! $description !!}">
<?php if ($coupon->image_file_name != null) { ?>
    <meta property="og:image" content="{{ url($coupon->image->url('preview')) }}">
<?php } ?>

<?php if ($coupon->lat != null && $coupon->lng != null) { ?>
    <meta property="place:location:latitude" content="{{ $coupon->lat }}">
    <meta property="place:location:longitude" content="{{ $coupon->lng }}">
    <meta name="geo.position" content="{{ $coupon->lat }}; {{ $coupon->lng }}">
<?php } ?>
<?php if ($coupon->city != null) { ?>
    <meta name="geo.placename" content="{{ $coupon->city }}">
<?php } ?>
<?php 
    if ($coupon->country != null || $coupon->state != null || $coupon->postal_code != null) { 
      $code = '';
      if ($coupon->country != null) $code .= $coupon->country;
      if ($coupon->state != null) $code .= ', ' . $coupon->state;
      if ($coupon->postal_code != null) $code .= ', ' . $coupon->postal_code;
?>
    <meta name="geo.region" content="{{ $code }}">
<?php } ?>
<style type="text/css">
.btn-custom-primary, .btn-custom-primary:hover, .btn-custom-primary.active, .btn-custom-primary:active, .btn-custom-primary:visited {
  background-color: {{ $coupon->additional_fields['primary_bg_color'] ?? '#58bd24' }};
  color: {{ $coupon->additional_fields['primary_text_color'] ?? '#fff' }};
  font-size: 21px;
}
.btn-custom-secondary, .btn-custom-secondary:hover, .btn-custom-secondary.active, .btn-custom-secondary:active, .btn-custom-secondary:visited {
  background-color: {{ $coupon->additional_fields['secondary_bg_color'] ?? '#58bd24' }};
  color: {{ $coupon->additional_fields['secondary_text_color'] ?? '#fff' }};
}
</style>
  </head>
  <body>
    <div class="container max-width-600">
      <div class="row">
        <div class="col-12">
          <div class="mt-3 mt-md-5">
            <h1 class="mb-0 mb-md-2">{!! $coupon->name !!}</h1>
<?php if ($coupon->image_file_name != null) { ?>
            <img src="{{ url($coupon->image->url('large')) }}" class="img-fluid mt-3 mb-4 mdl-shadow--2dp" alt="<?php echo str_replace('"', '&quot;', $coupon->title); ?>" style="min-width: 100%">
<?php } ?>

            <p class="lead">{!! $coupon->content !!}</p>

            <div class="row mt-0 d-print-none">
              <div class="col-12">
<?php
if ($coupon->location != null) {
  $url = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($coupon->location);
?>              
                <div class="font-weight-bold my-1"><i class="material-icons" style="font-size: 16px; position: relative; top: 2px;">location_on</i> <a href="{{ $url }}" class="text-dark" target="_blank">{{ $coupon->location }}</a></div>
<?php } ?>
              </div>
            </div>

<?php
if ($coupon->expiration_date != null) {
  $expires = $coupon->expiration_date->formatLocalized('Expires %A, %B ' . $coupon->expiration_date->day . ' %Y %I:%M %p');
} else {
  $expires = null;
}

if ($expires != null) { ?>
            <div class="text-muted small my-3">{{ $expires }}</div>
<?php } ?>

<?php if (! $redeemed) { ?>
            <div class="row mt-2 d-print-none">
              <div class="col-12">
                <a href="javascript:void(0);" data-toggle="modal" data-target="#redeemModal" class="btn btn-custom-primary btn-lg text-truncate rounded-0 btn-block mb-4"><i class="material-icons" style="position: relative; top: 3px; font-size: 20px">redeem</i></i> {{ trans('coupons::g.redeem') }}</a>
              </div>
            </div>
<?php } ?>

<?php if ($redeemed) { ?>
            <div class="row mt-2 d-print-none">
              <div class="col-12 text-center">
                <h4 class="my-5 text-success"><i class="material-icons text-success" style="position: relative; top: 3px;">check_circle_outline</i> {{ trans('coupons::g.coupon_redeemed') }}</h4>
              </div>
            </div>
<?php } ?>

            <div class="row d-print-none">
<?php if ($coupon->phone != null) { ?>
              <div class="col">
                <a href="tel:{{ $coupon->phone }}" class="btn btn-custom-secondary btn-lg text-truncate rounded-0 btn-block mb-4"><i class="mi phone"></i> {{ trans('coupons::g.call') }}</a>
              </div>
<?php } ?>
<?php
if ($coupon->website != null) {
  $website = (! starts_with($coupon->website, 'http')) ? 'http://' . $coupon->website : $coupon->website;
?>
              <div class="col">
                <a href="{{ $website }}" class="btn btn-custom-secondary btn-lg text-truncate rounded-0 btn-block mb-4" target="_blank"><i class="mi info_outline"></i> {{ trans('coupons::g.more') }}</a>
              </div>
<?php } ?>
              <div class="col">
                <div class="dropdown btn-block">
                  <button class="btn btn-custom-secondary btn-lg text-truncate rounded-0 btn-block mb-4 dropdown-toggle" type="button" id="dropdownShare" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ trans('coupons::g.share') }}
                  </button>
                  <div class="dropdown-menu rounded-0 dropdown-menu-right btn-block" aria-labelledby="dropdownShare">
                    <a class="dropdown-item" href="whatsapp://send?text={{ urlencode(url()->full()) }}" data-action="share/whatsapp/share"><span style="width:30px;float:left; text-align: left"><i class="fab fa-whatsapp" aria-hidden="true"></i></span> WhatsApp</a>
                    <a class="dropdown-item" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->full()) }}" target="_blank"><span style="width:30px;float:left; text-align: left"><i class="fab fa-facebook" aria-hidden="true"></i></span> Facebook</a>
                    <a class="dropdown-item" href="https://plus.google.com/share?url={{ urlencode(url()->full()) }}" target="_blank"><span style="width:30px;float:left; text-align: left"><i class="fab fa-google" aria-hidden="true"></i></span> Google+</a>
                    <a class="dropdown-item" href="https://twitter.com/intent/tweet?url={{ urlencode(url()->full()) }}&text={{ urlencode($coupon->title . ' - ') }}" target="_blank"><span style="width:30px;float:left; text-align: left"><i class="fab fa-twitter" aria-hidden="true"></i></span> Twitter</a>
                    <a class="dropdown-item" href="mailto:?subject={{ urlencode($coupon->title) }}&body={{ urlencode(url()->full()) }}" target="_blank"><span style="width:30px;float:left; text-align: left"><i class="far fa-envelope" aria-hidden="true"></i></span> Mail</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
<?php if (! $redeemed) { ?>
    <div class="modal fade" id="redeemModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog mt-5" role="document">
        <div class="modal-content rounded-0 mdl-shadow--8dp border border-white">
          <div class="modal-header border-0 p-3">
            <h5 class="modal-title">{!! $coupon->name !!}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body pt-0 border-0">
            <p class="">
            {{ trans('coupons::g.redeem_text') }}
            </p>
            {!! form($form, $formOptions = []) !!}
          </div>
        </div>
      </div>
    </div>
<?php } ?>
  </body>
</html>