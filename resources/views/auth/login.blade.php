@extends('layouts.guest')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="rounded-[28px] border border-white/60 bg-white/92 p-5 shadow-2xl ring-1 ring-white/40 backdrop-blur-xl transition-shadow duration-300 hover:shadow-[0_30px_60px_-20px_rgba(11,42,107,0.35)] sm:p-8">
        @csrf
        <x-brand />
        <h2 class="mt-4 text-xl font-semibold tracking-tight sm:mt-6 sm:text-2xl">Masuk ke kelas</h2>
        <p class="mt-1 hidden text-sm leading-6 text-ink/60 sm:block">Papan kelas. Kabar, hadir, dan tanya dosen ada di sini.</p>

        <div class="mt-4 sm:mt-5">
            <label class="block text-sm font-medium" for="nim">NIM</label>
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-ink/35">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="size-5"><path d="M4.5 4.5h15A1.5 1.5 0 0 1 21 6v12a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18V6a1.5 1.5 0 0 1 1.5-1.5Zm.75 3v1.5h13.5V7.5H5.25Zm0 3.75v6h4.5v-6h-4.5Zm6 0v1.5h7.5v-1.5h-7.5Zm0 3v1.5h7.5v-1.5h-7.5Z" /></svg>
                </span>
                <input
                    id="nim"
                    type="text"
                    name="nim"
                    value="{{ old('nim') }}"
                    required
                    autofocus
                    inputmode="numeric"
                    autocomplete="username"
                    placeholder="Contoh 261091700008"
                    class="w-full rounded-2xl border border-line bg-white py-2.5 pl-11 pr-3.5 text-base outline-none ring-navy/15 focus:border-navy focus:ring-4 sm:py-3 sm:text-sm"
                >
            </div>
            @error('nim')
                <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium" for="password">Kata sandi</label>
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-ink/35">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="size-5"><path d="M12 2.25a4.5 4.5 0 0 0-4.5 4.5v2.25h-.75A2.25 2.25 0 0 0 4.5 11.25v8.25a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-8.25a2.25 2.25 0 0 0-2.25-2.25h-.75V6.75a4.5 4.5 0 0 0-4.5-4.5Zm-3 6.75V6.75a3 3 0 1 1 6 0V9h-6Z" /></svg>
                </span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-2xl border border-line bg-white py-2.5 pl-11 pr-3.5 text-base outline-none ring-navy/15 focus:border-navy focus:ring-4 sm:py-3 sm:text-sm"
                >
            </div>
            @error('password')
                <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
            @enderror
        </div>

        <label class="mt-4 flex items-center gap-2 text-sm text-ink/70">
            <input type="checkbox" name="remember" class="rounded border-line">
            Ingat saya
        </label>

        <x-btn type="submit" class="mt-4 w-full sm:mt-6">Masuk</x-btn>

        <p class="mt-3 text-xs leading-5 text-ink/50 sm:mt-5">
            Masuk dengan NIM. Tanya ketua kelas kalau belum dapat kata sandi.
        </p>
    </form>
@endsection
