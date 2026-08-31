@extends('layouts.app')

@section('title', $user->name)

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex min-w-0 items-start gap-4">
            <x-avatar :user="$user" size="lg" />
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold tracking-tight">{{ $user->name }}</h1>
                <p class="mt-1 text-sm text-ink/55">{{ $user->nim }} · {{ $user->role->label() }}</p>
                @if ($user->bio)
                    <p class="mt-3 max-w-xl text-sm leading-6 text-ink/75">{{ $user->bio }}</p>
                @else
                    <p class="mt-3 text-sm text-ink/45">Belum ada bio.</p>
                @endif
            </div>
        </div>
        @if (auth()->id() === $user->id)
            <x-btn tag="a" href="{{ route('profiles.edit') }}">Ubah profil</x-btn>
        @endif
    </div>

    @can('resetPassword', $user)
        @unless (auth()->id() === $user->id)
            <form id="sandi" method="POST" action="{{ route('profiles.password.reset', $user) }}" class="mt-8 max-w-xl space-y-4 rounded-2xl border border-line bg-card p-6">
                @csrf
                @method('PATCH')
                <div>
                    <h2 class="text-lg font-semibold tracking-tight">Setel kata sandi</h2>
                    <p class="mt-1 text-sm text-ink/60">Khusus KM. Anggota masuk dengan sandi baru ini. Minimal 8 karakter. Sandi awal kelas: <code>{{ config('kelas.default_password') }}</code>.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium">Sandi baru</label>
                    <input type="password" name="password" required minlength="8" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm outline-none focus:border-navy">
                    @error('password')
                        <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium">Ulangi sandi baru</label>
                    <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm outline-none focus:border-navy">
                </div>
                <x-btn type="submit">Simpan sandi anggota</x-btn>
            </form>
        @endunless
    @endcan
@endsection
