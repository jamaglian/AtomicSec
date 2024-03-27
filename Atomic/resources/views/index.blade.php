<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>


        <!-- Styles -->
        <style>
            body {
                margin: 0;
                overflow: hidden;
                background-color: #000;
            }

            #binaryCanvas {
                position: absolute;
                top: 0;
                left: 0;
                z-index: -2;
            }
            #background {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5); /* Cor de fundo preto com 70% de transparência */
                z-index:-1;
            }
            .hider {
                visibility: hidden;
            }
            .button-container {
                position: absolute;
                top: 20px; /* Ajuste conforme necessário */
                right: 20px; /* Ajuste conforme necessário */
                z-index: 100; /* Certifica-se de que os botões estejam acima de outros elementos */
            }
            .button-container nav a {
                margin-left: 10px; /* Espaçamento entre botões */
            }
            h1, h2, h3, h4, h5, h6{
                font-weight: 200;
                color: rgb(226, 226, 226);
            }
            .grid-container {
            
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                grid-template-rows: 2.5fr;
                gap: 0px 0px;
                grid-template-areas:
                "pageLeft pageCenter pageRight";
            }
            .pageCenter {
                margin-top: 10vh;
                display: grid;
                grid-template-columns: 1fr;
                grid-template-rows: 0.6fr 0.1fr 0.5fr 0.1fr;
                gap: 25px 0px;
                grid-template-areas:
                "pageTitle"
                "pageSeparator"
                "pageDescription"
                "pageButton";
                grid-area: pageCenter;
            }
            .pageTitle {
                grid-area: pageTitle;
                text-align: center;
            }
            .pageSeparator {
                grid-area: pageSeparator;
                display: grid;
                place-items: center;
            }
            .pageSeparator .separator{
                background-color: #02D975;
                width: 70px;
                height: 5px;
                border-radius: 100px;
            }
            .pageDescription {
                grid-area: pageDescription;
                place-items: center;
                text-align: center;
                font-size: 1.2em;
                font-weight: 300;
                
            }
            .pageDescription h3 {
                margin: 0px;
                color: rgb(192, 192, 192);
            }
            .pageLeft {
                grid-area: pageLeft;
                margin-top: 20vh;
                display: grid;
                place-items: center;
            }
            .pageRight {
                grid-area: pageRight;
                margin-top: 20vh;
                display: grid;
                place-items: center;
            }
            .container {
                display: flex;
            }
            .container > div {
                flex: 1; /*grow*/
            }
            /* Landscape phones and down */
            @media (max-width: 480px) {
                .pageLeft, .pageRight {
                    display: none;
                }
            }

            /* Landscape phone to portrait tablet */
            @media (max-width: 767px) { 
                .pageTitle h1 {
                    margin: 0;
                    font-weight: bold;
                    font-size: 5em;
                }
                .pageTitle h2 {
                    margin: 0;
                    margin-top: -0.5em;
                    font-weight: 700;
                    font-size: 1.5em;
                }
                .pageLeft, .pageRight {
                    display: none;
                }
            }
            /* Portrait tablet to landscape and desktop */
            @media (min-width: 768px) and (max-width: 979px) { 
                .pageLeft, .pageRight {
                    display: none;
                }

                .pageTitle h1 {
                    margin: 0;
                    font-weight: bold;
                    font-size: 7em;
                }
                .pageTitle h2 {
                    margin: 0;
                    margin-top: -0.5em;
                    font-weight: 700;
                    font-size: 2.5em;
                }
            }
            /* Large desktop */
            @media (min-width: 1000px) {
                .pageTitle h1 {
                    margin: 0;
                    font-weight: bold;
                    font-size: 10em;
                }
                .pageTitle h2 {
                    margin: 0;
                    margin-top: -0.5em;
                    font-weight: 700;
                    font-size: 3.5em;
                }
            }
            .button-container {
                min-height: 100vh;
                display: flex;
                text-align: center;
            }
            .button-container a{
                cursor: pointer;
                border: 0;
                border-radius: 4px;
                font-weight: 600;
                margin: 0 10px;
                width: 200px;
                padding: 10px 6px;
                box-shadow: 0 0 20px rgba(104, 85, 224, 0.2);
                transition: 0.4s;
                text-decoration: none; /* no underline */
            }
            .reg {
                color: white;
                padding: 2em;
                background-color: rgba(104, 85, 224, 1);
            }

            .login {
                color: rgb(104, 85, 224);
                background-color: rgba(255, 255, 255, 1);
                border: 1px solid rgba(104, 85, 224, 1);
            }

            .dash {
                color: rgb(104, 85, 224);
                background-color: rgba(255, 255, 255, 1);
                border: 1px solid rgba(104, 85, 224, 1);
            }
     </style>
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
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="login"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="reg"
                            >
                                Register
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
                <div class="pageSeparator">
                    <div class="separator"></div>
                </div>
                <div class="pageDescription">
                    <h3>Atomic</h3>
                </div>
            </div>
        </div>
        <div id="background"></div>
        <canvas id="binaryCanvas"></canvas>
        <script>
            const canvas = document.getElementById('binaryCanvas');
            const ctx = canvas.getContext('2d');

            // Set canvas dimensions to match window size
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;

            // Generate random binary digit
            function generateBinaryDigit() {
                return Math.random() > 0.5 ? '1' : '0';
            }

            // Generate random binary string
            function generateBinaryString(length) {
                let binaryString = '';
                for (let i = 0; i < length; i++) {
                    binaryString += generateBinaryDigit();
                }
                return binaryString;
            }

            // Create binary raindrop object
            function createRaindrop() {
                //const speed = Math.random() * 5 + 1; // Random speed
                const speed = Math.random() * 5 + 1; // Random speed
                //const length = Math.random() * 20 + 10; // Random length
                const length = Math.random() * 400 + 10; // Random length
                const x = Math.random() * canvas.width; // Random horizontal position
                const y = -length; // Start above the canvas
                const binaryString = generateBinaryString(Math.ceil(length / 10)); // Generate binary string
                return { x, y, speed, length, binaryString };
            }

            const raindrops = [];

            // Initialize raindrops
            for (let i = 0; i < 50; i++) {
                raindrops.push(createRaindrop());
            }

            function draw() {
                // Clear canvas
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                // Draw raindrops
                for (const raindrop of raindrops) {
                    ctx.fillStyle = 'red';//lime
                    ctx.font = '10px monospace';
                    ctx.fillText(raindrop.binaryString, raindrop.x, raindrop.y);
                    raindrop.y += raindrop.speed;
                    // Reset raindrop position if it goes beyond the canvas
                    if (raindrop.y > canvas.height) {
                        Object.assign(raindrop, createRaindrop());
                    }
                }
            }

            function animate() {
                draw();
                requestAnimationFrame(animate);
            }

            animate();

            // Update canvas dimensions on window resize
            window.addEventListener('resize', () => {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            });
        </script>
    </body>
</html>
