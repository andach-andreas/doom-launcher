@include('layouts.head')

<body class="font-inter antialiased bg-slate-800 text-slate-200">
    <div class="flex items-center justify-center min-h-screen py-48">
        <div class="flex flex-col">
            <div class="flex flex-col items-center">
                <div class="text-8xl font-bold">
                    <h1>@yield('number')</h1>
                </div>

                <div class="font-bold text-3xl xl:text-5xl lg:text-4xl mt-10">
                    <h2>@yield('header')</h2>
                </div>

                <div class="font-medium text-sm md:text-xl mt-8">
                    @yield('content')
                </div>

                <div class="mt-8">
                    <a href="/">Go back home</a>
                </div>

                <div class="mt-8">
                    <img src="/img/logo-circle-dark-256.png" alt="Andach Logo" class="w-24 h-24">
                </div>
            </div>
        </div>
    </div>
</body>

</html>
