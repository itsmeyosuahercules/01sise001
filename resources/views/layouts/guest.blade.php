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
        <div class="lg:grid lg:min-h-screen lg:grid-cols-[minmax(0,1fr)_27rem]">
            <div class="relative flex items-center justify-center bg-navy lg:h-screen">
                <img
                    src="{{ asset('images/kelas.jpg') }}"
                    alt="Foto kelas {{ config('kelas.name') }}"
                    width="1024"
                    height="768"
                    class="h-auto w-full lg:max-h-screen lg:w-auto lg:max-w-full"
                >
                <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-28 bg-gradient-to-l from-navy to-transparent lg:block"></div>
            </div>

            <div class="flex items-center justify-center px-4 py-8 sm:px-8">
                <div class="w-full max-w-md">
                    <p class="mb-5 hidden text-sm leading-6 text-white/75 lg:block">
                        Papan kelas Sistem Informasi. Info dua arah, tanpa chat yang menimbun pengumuman.
                    </p>
                    @yield('content')
                </div>
            </div>
        </div>
    </body>
</html>
