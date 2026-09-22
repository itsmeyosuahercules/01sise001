<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Masuk · {{ config('kelas.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-dvh overflow-x-hidden bg-navy font-sans text-ink antialiased">
        <img
            src="{{ asset('images/kelas.jpg') }}"
            alt=""
            class="pointer-events-none absolute inset-0 h-full w-full scale-110 object-cover opacity-50 blur-3xl"
        >
        <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-navy/30 via-navy/55 to-navy/80"></div>

        <div class="relative mx-auto grid min-h-dvh w-full max-w-7xl items-center gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[minmax(0,1.35fr)_24rem] lg:gap-10 lg:px-10">
            <div class="flex animate-fade-up justify-center">
                <img
                    src="{{ asset('images/kelas.jpg') }}"
                    alt="Foto kelas {{ config('kelas.name') }}"
                    width="1024"
                    height="768"
                    class="h-auto w-auto max-h-[58dvh] max-w-full rounded-3xl shadow-2xl ring-1 ring-white/30 lg:max-h-[calc(100dvh-3.5rem)]"
                >
            </div>

            <div class="mx-auto w-full max-w-md animate-fade-up" style="animation-delay: 0.08s">
                @yield('content')
            </div>
        </div>
    </body>
</html>
