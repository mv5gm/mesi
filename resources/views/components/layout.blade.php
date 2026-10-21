<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Mesi' }}</title>
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('img/favicon-64.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/apple-touch-icon.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-100 font-sans antialiased text-gray-900">
    {{ $slot }}
</body>
</html>