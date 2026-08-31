@extends('layouts.app')

@section('title', 'Tulis pertanyaan')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">Tulis pertanyaan ke dosen</h1>
    <p class="mt-1 text-sm text-ink/60">KM yang meneruskan. Jangan kirim acak ke chat dosen atau Mentari.</p>

    <form method="POST" action="{{ route('lecturer-questions.store') }}" class="mt-6 max-w-xl space-y-4 rounded-2xl border border-line bg-card p-6">
        @csrf
        <div>
            <label class="block text-sm font-medium">Jenis</label>
            <select name="kind" class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm">
                @foreach ($kinds as $kind)
                    <option value="{{ $kind->value }}" @selected(old('kind') === $kind->value)>{{ $kind->label() }}</option>
                @endforeach
            </select>
            @error('kind')
                <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Untuk dosen / MK</label>
            <input
                type="text"
                name="topic"
                value="{{ old('topic') }}"
                required
                maxlength="120"
                placeholder="Contoh: Pak Andi, Basis Data"
                class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm outline-none focus:border-navy"
            >
            @error('topic')
                <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Isi</label>
            <textarea name="body" rows="5" required class="mt-1.5 w-full rounded-xl border border-line bg-paper px-3 py-2.5 text-sm outline-none focus:border-navy">{{ old('body') }}</textarea>
            @error('body')
                <p class="mt-1 text-sm text-accent-hot">{{ $message }}</p>
            @enderror
        </div>
        <label class="flex items-start gap-2 text-sm text-ink/70">
            <input type="checkbox" name="hide_name" value="1" class="mt-0.5 rounded border-line" @checked(old('hide_name'))>
            <span>Jangan sebut nama saya di paket ke dosen. KM tetap melihat siapa yang menulis.</span>
        </label>
        <x-btn type="submit">Kirim ke KM</x-btn>
    </form>
@endsection
