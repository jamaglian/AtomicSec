<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <link rel="stylesheet" href="/css/fontawesome-all.min.css">
        <!-- Scripts -->
        @vite([
            'resources/css/bootstrap.css',
            'resources/css/datatables.css',
            'resources/css/fullcalendar.css',
            'resources/css/atomic.css'
        ])

        <title>Login | {{ config('app.name', 'Laravel') }}</title>
    </head>
    <body class="bg-light">

        <div class="container h-100">
            <div class="row h-100 justify-content-center align-items-center">
                <div class="col-md-4">
                    <div>
                        <a href="/">
                            <img class="rounded mx-auto d-block" style="height: 150px;" src="/images/Logo.png" />
                        </a>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        @vite([
            'resources/js/jquery.js',
            'resources/js/bootstrap.bundle.js',
            'resources/js/fullcalendar.js',
            'resources/js/atomic.js'
        ])
        <script src="/js/datatables.min.js"></script>
        <script src="/js/moment.min.js"></script>

    </body>
</html>