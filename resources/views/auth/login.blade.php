@extends('layouts.guest')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="rounded-3xl border border-line bg-card p-6">
        @csrf
        <label class="block text-sm font-medium">NIM</label>
        <input
            type="text"
            name="nim"
            value="{{ old('nim') }}"
            required
            autofocus
            autocomplete="username"
            class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm outline-none focus:border-navy"
        >
        @error('nim')
            <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
        @enderror

        <label class="mt-5 block text-sm font-medium">Kata sandi</label>
        <input
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
            Masuk dengan NIM. Tanya KM kalau belum dapat kata sandi.
        </p>
    </form>
@endsection
