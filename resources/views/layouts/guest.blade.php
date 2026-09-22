<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Masuk · {{ config('kelas.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-navy font-sans text-ink antialiased">
        <div class="relative flex min-h-screen items-center justify-center px-4 py-8 sm:px-6">
            <img
                src="{{ asset('images/kelas.jpg') }}"
                alt=""
                class="absolute inset-0 h-full w-full object-cover object-[center_20%]"
            >
            <div class="absolute inset-0 bg-gradient-to-b from-navy/80 via-navy/55 to-navy/85"></div>

            <div class="relative grid w-full max-w-5xl items-center gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="hidden text-white lg:block">
                    <p class="text-xs font-medium tracking-[0.18em] uppercase text-white/70">Universitas Pamulang</p>
                    <h1 class="mt-3 max-w-md text-4xl font-semibold tracking-tight">{{ config('kelas.name') }}</h1>
                    <p class="mt-4 max-w-sm text-sm leading-6 text-white/80">
                        Papan kelas Sistem Informasi. Info dua arah, tanpa chat yang menimbun pengumuman.
                    </p>
                </div>

                <div class="mx-auto w-full max-w-md">
                    @yield('content')
                </div>
            </div>
        </div>
    </body>
</html>
