@extends('layouts.app')

@section('title', 'Template baru')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">Template baru</h1>

    <form method="POST" action="{{ route('message-templates.store') }}" class="mt-6 max-w-xl space-y-4 rounded-2xl border border-line bg-card p-6">
        @csrf
        <div>
            <label class="block text-sm font-medium">Judul</label>
            <input type="text" name="title" value="{{ old('title') }}" required class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm">
            @error('title') <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Isi</label>
            <textarea name="body" rows="8" required class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 font-mono text-sm">{{ old('body') }}</textarea>
            @error('body') <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p> @enderror
        </div>
        <x-btn type="submit">Simpan</x-btn>
    </form>
@endsection
