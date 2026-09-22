<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Masuk · {{ config('kelas.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-dvh overflow-hidden bg-navy font-sans text-ink antialiased">
        <img
            src="{{ asset('images/kelas.jpg') }}"
            alt=""
            class="fixed inset-0 -z-10 h-full w-full scale-110 object-cover opacity-50 blur-3xl"
        >
        <div class="fixed inset-0 -z-10 bg-linear-to-br from-navy/30 via-navy/55 to-navy/80"></div>

        <div class="relative mx-auto grid h-dvh w-full max-w-7xl grid-rows-[minmax(0,1fr)_auto] items-center gap-3 px-3 py-3 sm:px-6 sm:py-4 lg:grid-cols-[minmax(0,1.35fr)_24rem] lg:grid-rows-1 lg:gap-10 lg:px-10 lg:py-6">
            <div class="flex h-full min-h-0 animate-fade-up items-center justify-center">
                <img
                    src="{{ asset('images/kelas.jpg') }}"
                    alt="Foto kelas {{ config('kelas.name') }}"
                    width="1024"
                    height="768"
                    class="h-auto max-h-full w-auto max-w-full rounded-2xl shadow-2xl ring-1 ring-white/30 sm:rounded-3xl"
                >
            </div>

            <div class="mx-auto min-h-0 w-full max-w-md animate-fade-up overflow-y-auto" style="animation-delay: 0.08s">
                @yield('content')
            </div>
        </div>
    </body>
</html>
