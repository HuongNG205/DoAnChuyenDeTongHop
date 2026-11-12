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
<body>
    @include('partial.header')

    <div class="page">
        @auth
            @include('partial.sidebar')
        @endauth

        <main class="main">
            @include('partial.alert')
            @yield('content')
        </main>

        @hasSection('aside')
            <aside class="aside">
                @yield('aside')
            </aside>
        @endif
    </div>

    @include('partial.footer')
    @stack('scripts')
</body>
</html>
