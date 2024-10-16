<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ config('app.name') }}</title>


        <!-- Styles -->
        @vite(['resources/css/index.css', 'resources/js/index.js'])
    </head>
    <body>
        <div class="button-container">
            @if (Route::has('login'))
                <nav>
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="dash"
                        >
                            {{__("Dashboard")}}
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="login"
                        >
                        {{__("Log in")}}
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="reg"
                            >
                            {{__("Register")}}
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
            <!-- Adicione quantos botões desejar -->
        </div>
        <div class="grid-container">
            <div class="pageCenter">
                <div class="pageTitle">
                    <!-- h1>Security</h1>
                    <h2>in the first place</h2 -->
                    <img style="height: 20em;" src="{{ asset('/images/Logo.png') }}" />
                </div>
                <div class="pageDescription">
                    <h3><img src="/images/plataform_logo.png" alt="Logo Plat" style="height: 60px;"></h3>
                </div>
            </div>
        </div>
        <div id="background"></div>
        <canvas id="binaryCanvas"></canvas>
        <script>

        </script>
    </body>
</html>
