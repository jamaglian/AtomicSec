<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

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
                <div class="col-md-6">
                    <div>
                        <a href="/">
                            <img class="rounded mx-auto d-block" style="height: 200px;" src="/images/Logo.png" />
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
        <script src="/js/jquery.min.js"></script>
        <script src="/js/bootstrap.bundle.min.js"></script>
        <script src="/js/atomic.min.js"></script>
        <script src="/js/datatables.min.js"></script>
        <script src="/js/moment.min.js"></script>
        <script src="/js/fullcalendar.min.js"></script>
    </body>
</html>