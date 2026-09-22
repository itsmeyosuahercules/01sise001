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
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-6 lg:min-h-screen lg:flex-row lg:items-center lg:gap-10 lg:px-8 lg:py-10">
            <figure class="min-w-0 flex-1">
                <img
                    src="{{ asset('images/kelas.jpg') }}"
                    alt="Foto kelas {{ config('kelas.name') }}"
                    width="1024"
                    height="768"
                    class="h-auto w-full rounded-2xl shadow-lg"
                >
                <figcaption class="mt-4 hidden text-white lg:block">
                    <p class="text-xs font-medium tracking-[0.18em] text-white/70 uppercase">Universitas Pamulang</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ config('kelas.name') }}</h1>
                </figcaption>
            </figure>

            <div class="mx-auto w-full max-w-md shrink-0">
                @yield('content')
            </div>
        </div>
    </body>
</html>
