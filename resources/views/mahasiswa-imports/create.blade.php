@extends('layouts.app')

@section('title', 'Impor mahasiswa')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">Impor mahasiswa</h1>
    <p class="mt-1 max-w-xl text-sm text-ink/60">
        Data dari grup boleh masuk bertahap. NIM yang sudah ada dilewati — nama, peran, dan kata sandi tidak diubah.
        Format: <code>nim,nama</code>. Header opsional.
    </p>

    <form method="POST" action="{{ route('mahasiswa-imports.store') }}" enctype="multipart/form-data" class="mt-6 max-w-xl space-y-4 rounded-2xl border border-line bg-card p-6">
        @csrf
        <div>
            <label class="block text-sm font-medium">Unggah CSV</label>
            <input type="file" name="csv" accept=".csv,text/csv,text/plain" class="mt-1.5 w-full text-sm">
            @error('csv')
                <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Atau tempel dari grup</label>
            <textarea
                name="paste"
                rows="10"
                class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 font-mono text-sm outline-none focus:border-navy"
                placeholder="nim,nama&#10;2410112001,Siti Aminah&#10;2410112002,Budi Santoso"
            >{{ old('paste') }}</textarea>
            @error('paste')
                <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
            @enderror
        </div>
        <x-btn type="submit">Impor</x-btn>
    </form>
@endsection
