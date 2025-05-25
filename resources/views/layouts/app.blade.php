@include('layouts.head')

<body
    class="font-inter antialiased bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400"
    :class="{ 'sidebar-expanded': sidebarExpanded }"
    x-data="{ sidebarOpen: false, sidebarExpanded: localStorage.getItem('sidebar-expanded') == 'true' }"
    x-init="$watch('sidebarExpanded', value => localStorage.setItem('sidebar-expanded', value))"
>

<script>
    if (localStorage.getItem('sidebar-expanded') == 'true') {
        document.querySelector('body').classList.add('sidebar-expanded');
    } else {
        document.querySelector('body').classList.remove('sidebar-expanded');
    }
</script>

<!-- Page wrapper -->
<div class="flex h-[100dvh] overflow-hidden">

    @include('layouts.menu')

    <!-- Content area -->
    <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">

        @include('layouts.topbar')

        <main class="grow">
            <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-6xl mx-auto">
                <!-- Page header -->
                @if(Session::has('success'))
                    <x-andach-alert color="green"><b>Success:</b> {{ Session::get('success') }}</x-andach-alert>
                @endif

                @if(Session::has('warning'))
                    <x-andach-alert color="green"><b>Success:</b> {{ Session::get('warning') }}</x-andach-alert>
                @endif

                @if(Session::has('danger'))
                    <x-andach-alert color="red"><b>Error:</b> {{ Session::get('danger') }}</x-andach-alert>
                @endif

                @if(Session::has('errors'))
                    <x-andach-alert color="red">
                        {!! implode('<br />', $errors->all()) !!}
                    </x-andach-alert>
                @endif

                <div class="sm:flex sm:justify-between sm:items-center mb-2">
                    <!-- Left: Title -->
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold">@yield('title', 'PAGE TITLE NOT SUPPLIED')</h1>
                    </div>

                    <!-- Right: Actions -->
                    <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                        @yield('title-right')
                    </div>
                </div>

                @yield('content')

            </div>
        </main>

    </div>

</div>

@yield('javascript')

</body>

</html>
