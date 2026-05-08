<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title')</title>

        <link rel="stylesheet" href="{{ asset('css/pag_visualizacion_csv.css') }}">
    </head>
    <body>
        <main>
            @yield('content')
        </main>
        <script src="{{ asset('js/selector_vista.js') }}"></script>
    </body>
</html>