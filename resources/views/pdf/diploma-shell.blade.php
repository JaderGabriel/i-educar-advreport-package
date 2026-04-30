<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8">
    <title>{{ $pageTitle ?? 'Diploma' }}</title>
    @stack('styles')
  </head>
  <body>
    @yield('content')
  </body>
</html>
