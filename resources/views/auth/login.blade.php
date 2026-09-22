@extends('layouts.guest')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="rounded-3xl border border-white/40 bg-card/95 p-6 shadow-2xl backdrop-blur sm:p-8">
        @csrf
        <x-brand />
        <p class="mt-4 text-sm leading-6 text-ink/65 lg:hidden">
            Papan kelas Sistem Informasi. Info dua arah, tanpa chat yang menimbun pengumuman.
        </p>
        <h2 class="mt-6 text-xl font-semibold tracking-tight">Masuk ke kelas</h2>

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
            class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm outline-none focus:border-navy"
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
            class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm outline-none focus:border-navy"
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
