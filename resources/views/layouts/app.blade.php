<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Reporter Camera') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-950 text-white min-h-screen">
    @yield('content')
</body>
</html>