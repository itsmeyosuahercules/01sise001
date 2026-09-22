@extends('layouts.guest')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="rounded-[28px] border border-white/60 bg-white/92 p-6 shadow-2xl ring-1 ring-white/40 backdrop-blur-xl sm:p-8">
        @csrf
        <x-brand />
        <h2 class="mt-6 text-2xl font-semibold tracking-tight">Masuk ke kelas</h2>
        <p class="mt-1 text-sm leading-6 text-ink/60">Papan kelas. Kabar, hadir, dan tanya dosen ada di sini.</p>

        <label class="mt-5 block text-sm font-medium" for="nim">NIM</label>
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
            class="mt-1.5 w-full rounded-2xl border border-line bg-white px-3.5 py-3 text-base outline-none ring-navy/15 focus:border-navy focus:ring-4 sm:text-sm"
        >
        @error('nim')
            <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
        @enderror

        <label class="mt-5 block text-sm font-medium" for="password">Kata sandi</label>
        <input
            id="password"
            type="password"
            name="password"
            required
            autocomplete="current-password"
            class="mt-1.5 w-full rounded-2xl border border-line bg-white px-3.5 py-3 text-base outline-none ring-navy/15 focus:border-navy focus:ring-4 sm:text-sm"
        >
        @error('password')
            <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
        @enderror

        <label class="mt-5 flex items-center gap-2 text-sm text-ink/70">
            <input type="checkbox" name="remember" class="rounded border-line">
            Ingat saya
        </label>

        <x-btn type="submit" class="mt-6 w-full">Masuk</x-btn>

        <p class="mt-5 text-xs leading-5 text-ink/50">
            Masuk dengan NIM. Tanya ketua kelas kalau belum dapat kata sandi.
        </p>
    </form>
@endsection
