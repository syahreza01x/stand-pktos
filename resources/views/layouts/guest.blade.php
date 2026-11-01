<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Login' }} — {{ config('app.name', 'Stand-PKTOS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col justify-center items-center py-8 px-4 sm:px-6 lg:px-8 text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">
    <div class="w-full max-w-md">
        {{ $slot }}
    </div>
</body>
</html>
