<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Guidance')</title>
    @include('partial.assets')
    @stack('styles')
</head>
<body class="flex min-h-screen flex-col">
    @include('partial.header')

    <div class="flex flex-1 flex-col md:flex-row">
        @auth
            @include('partial.sidebar')
        @endauth

        <main class="min-w-0 flex-1 p-4">
            @include('partial.alert')
            @yield('content')
        </main>

        @hasSection('aside')
            <aside class="shrink-0 p-4 lg:w-80">
                @yield('aside')
            </aside>
        @endif
    </div>

    @include('partial.footer')
    @stack('scripts')
</body>
</html>
