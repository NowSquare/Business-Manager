<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" dir="ltr">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="{{ url('assets/css/pdf.css?' . config('system.client_side_timestamp')) }}">

		<style type="text/css">
			h1, h2, h3, h4, h5 {
				font-family: Gotham, 'Helvetica Neue', Helvetica, Arial, 'sans-serif';
			}
		</style>

  <body>

@yield('content')

	</body>
</html>