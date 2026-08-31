@extends('layouts.app')

@section('title', 'Ajukan izin')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">Ajukan izin / sakit</h1>

    <form method="POST" action="{{ route('absence-requests.store') }}" class="mt-6 max-w-xl space-y-4 rounded-2xl border border-line bg-card p-6">
        @csrf
        <div>
            <label class="block text-sm font-medium">Jenis</label>
            <select name="type" class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm">
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Mulai</label>
                <input type="date" name="starts_on" value="{{ old('starts_on') }}" required class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm">
                @error('starts_on') <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Selesai</label>
                <input type="date" name="ends_on" value="{{ old('ends_on') }}" required class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm">
                @error('ends_on') <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p> @enderror
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium">Alasan</label>
            <textarea name="reason" rows="4" required class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm">{{ old('reason') }}</textarea>
            @error('reason') <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p> @enderror
        </div>
        <x-btn type="submit">Kirim</x-btn>
    </form>
@endsection
