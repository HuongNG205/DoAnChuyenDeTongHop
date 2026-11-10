<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Guidance')</title>
    @include('partial.assets')
</head>
<body class="grid min-h-screen place-items-center bg-primary p-4">
    <div class="w-full max-w-xl rounded-3xl bg-white p-9">
        <a href="{{ url('/') }}" class="logo block text-center">Guidance</a>
        <div class="mt-4">@include('partial.alert')</div>
        @yield('content')
    </div>
</body>
</html>
