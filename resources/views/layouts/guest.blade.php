@include('layouts.head')

<body class="font-inter antialiased bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400">

<script>
    if (localStorage.getItem('sidebar-expanded') == 'true') {
        document.querySelector('body').classList.add('sidebar-expanded');
    } else {
        document.querySelector('body').classList.remove('sidebar-expanded');
    }
</script>

<div class="relative py-16">
    <div class="relative container m-auto px-6 md:px-12 xl:px-40">
        <div class="m-auto md:w-8/12 lg:w-6/12 xl:w-6/12">
            <div class="rounded-xl shadow-xl border-2 dark:border-slate-100 border-slate-900 bg-slate-200 dark:bg-slate-800">
                <div class="p-6 sm:p-16">
                    <div class="space-y-4 mb-4">
                        <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold">@yield('title')</h1>
                    </div>

                    @if(Session::has('errors'))
                        <x-andach-alert color="red">
                            {!! implode('<br />', $errors->all()) !!}
                        </x-andach-alert>
                    @endif

                    <div class="mt-8 grid space-y-4">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@yield('javascript')

</body>

</html>
