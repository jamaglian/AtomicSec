@props(
    [
        'extra_script',
        'extra_head_tag',
        'extra_end_tag'
    ]
)
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
        @if(isset($extra_head_tag))
            {!!
                 $extra_head_tag
            !!}
        @endif
        <title>Dashboard | {{ config('app.name', 'Laravel') }}</title>
    </head>
    <body class="bg-light">

        <x-atomicsec-dashboard-header></x-atomicsec-dashboard-header>
        <div class="d-flex">
            <x-atomicsec-dashboard-left-menu></x-atomicsec-dashboard-left-menu>
            <div class="content p-4">
                {{ $slot }}
            </div>
        </div>
        <!-- Scripts -->
        <script src="/js/jquery.min.js"></script>
        <script src="/js/bootstrap.bundle.min.js"></script>
        <script src="/js/atomic.min.js"></script>
        <script src="/js/datatables.min.js"></script>
        <script src="/js/moment.min.js"></script>
        <script src="/js/fullcalendar.min.js"></script>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        @if(isset($extra_script))
        <script>
            {!!
                 $extra_script
            !!}
        </script>
        @endif
        @if(isset($extra_end_tag))
            {!!
                 $extra_end_tag
            !!}
        @endif
    </body>
</html>