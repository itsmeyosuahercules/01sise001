@extends('layouts.app')

@section('title', 'Ubah pengumuman')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">Ubah pengumuman</h1>

    <form method="POST" action="{{ route('announcements.update', $announcement) }}" enctype="multipart/form-data" class="mt-6 max-w-xl space-y-4 rounded-2xl border border-line bg-card p-6">
        @csrf
        @method('PUT')
        @include('announcements._form')
        <x-btn type="submit">Simpan</x-btn>
    </form>
@endsection
