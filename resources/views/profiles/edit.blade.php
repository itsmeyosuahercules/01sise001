@extends('layouts.app')

@section('title', 'Profil')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Profil saya</h1>
            <p class="mt-1 text-sm text-ink/60">Foto, nama, dan bio tampil di komentar dan daftar suka.</p>
        </div>
        <x-btn tag="a" variant="secondary" href="{{ route('profiles.show', $user) }}">Lihat seperti anggota lain</x-btn>
    </div>

    <form method="POST" action="{{ route('profiles.update') }}" enctype="multipart/form-data" class="mt-6 max-w-xl space-y-4 rounded-2xl border border-line bg-card p-6">
        @csrf
        @method('PATCH')

        <div class="flex items-center gap-4">
            <x-avatar :user="$user" size="lg" />
            <div class="min-w-0 text-sm">
                <p class="font-medium">{{ $user->name }}</p>
                <p class="text-ink/50">{{ $user->nim }} · {{ $user->role->label() }}</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium">Foto</label>
            <input
                type="file"
                name="photo"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                class="mt-1.5 w-full text-sm file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-navy file:px-3 file:py-2 file:text-white"
            >
            <p class="mt-1 text-xs text-ink/50">JPG, PNG, atau WebP. Maksimal 2 MB. Hanya terlihat oleh anggota yang sudah masuk.</p>
            @error('photo')
                <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
            @enderror
            @if ($user->hasAvatar())
                <label class="mt-2 flex items-center gap-2 text-sm text-ink/70">
                    <input type="checkbox" name="remove_photo" value="1" @checked(old('remove_photo'))>
                    Hapus foto sekarang
                </label>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium">Nama</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm outline-none focus:border-navy">
            <p class="mt-1 text-xs text-ink/50">NIM tidak bisa diubah. Impor roster tidak menimpa nama yang sudah ada.</p>
            @error('name')
                <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Bio <span class="font-normal text-ink/45">(opsional)</span></label>
            <textarea name="bio" rows="3" maxlength="280" class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm outline-none focus:border-navy">{{ old('bio', $user->bio) }}</textarea>
            <p class="mt-1 text-xs text-ink/50">Maksimal 280 karakter. Satu baris singkat, misalnya peran di kelompok atau kontak WA.</p>
            @error('bio')
                <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
            @enderror
        </div>

        <x-btn type="submit">Simpan profil</x-btn>
    </form>

    <form method="POST" action="{{ route('profiles.password') }}" class="mt-6 max-w-xl space-y-4 rounded-2xl border border-line bg-card p-6">
        @csrf
        @method('PATCH')
        <div>
            <h2 class="text-lg font-semibold tracking-tight">Ganti kata sandi</h2>
            <p class="mt-1 text-sm text-ink/60">Sandi awal kelas: <code>{{ config('kelas.default_password') }}</code>. Ganti setelah masuk pertama kali.</p>
        </div>
        <div>
            <label class="block text-sm font-medium">Sandi sekarang</label>
            <input type="password" name="current_password" required autocomplete="current-password" class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm outline-none focus:border-navy">
            @error('current_password')
                <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
            @enderror
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
        <x-btn type="submit">Simpan sandi</x-btn>
    </form>
@endsection
