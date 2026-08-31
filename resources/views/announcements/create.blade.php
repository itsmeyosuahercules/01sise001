@extends('layouts.app')

@section('title', 'Tulis pengumuman')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">Tulis pengumuman</h1>

    <form method="POST" action="{{ route('announcements.store') }}" enctype="multipart/form-data" class="mt-6 max-w-xl space-y-4 rounded-2xl border border-line bg-card p-6">
        @csrf
        @include('announcements._form')
        <x-btn type="submit">Publikasikan</x-btn>
    </form>
@endsection
