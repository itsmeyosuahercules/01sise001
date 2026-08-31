<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Masuk · {{ config('kelas.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-paper font-sans text-ink antialiased">
        <div class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-5 py-12">
            <x-brand />
            <p class="mt-6 text-sm leading-6 text-ink/65">
                Papan kelas Sistem Informasi. Info dua arah, tanpa chat yang menimbun pengumuman.
            </p>
            <div class="mt-8">
                @yield('content')
            </div>
        </div>
    </body>
</html>
