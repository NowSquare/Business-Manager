<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" dir="ltr">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="{{ url('assets/css/pdf.css?' . config('system.client_side_timestamp')) }}">
		<style type="text/css">
			@page {
				size: 216mm 279mm;
			}
			.container-fluid > .row {
				width: 727px;
			}
			h1, h2, h3, h4, h5 {
				font-family: Gotham, 'Helvetica Neue', Helvetica, Arial, 'sans-serif';
			}
		</style>
  <body>

@yield('content')

	</body>
</html>