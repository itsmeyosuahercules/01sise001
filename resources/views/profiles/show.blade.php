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
@endsection
