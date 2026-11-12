<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Guidance')</title>
    @include('partial.assets')
</head>
<body class="guest-body">
    <div class="auth-card">
        <a href="{{ url('/') }}" class="logo">Guidance</a>
        @include('partial.alert')
        @yield('content')
    </div>
</body>
</html>
