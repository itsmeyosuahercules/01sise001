@extends('layouts.app')

@section('title', 'Template')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Template pesan</h1>
            <p class="mt-1 text-sm text-ink/60">Variabel: <code>@{{nama}}</code>, <code>@{{nim}}</code>, <code>@{{kelas}}</code>.</p>
        </div>
        @can('create', App\Models\MessageTemplate::class)
            <x-btn tag="a" href="{{ route('message-templates.create') }}">Template baru</x-btn>
        @endcan
    </div>

    <div data-filter-root class="mt-6">
        <input
            type="search"
            data-filter-q
            placeholder="Cari template…"
            class="mb-4 w-full max-w-md rounded-xl border border-line bg-card px-3 py-2 text-sm outline-none focus:border-navy"
        >

        <div class="space-y-4">
            @forelse ($templates as $template)
                <article
                    data-filter-row
                    data-search="{{ $template->title }} {{ $template->body }}"
                    class="rounded-2xl border border-line bg-card p-5"
                >
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <h2 class="font-medium">{{ $template->title }}</h2>
                        @can('delete', $template)
                            <form method="POST" action="{{ route('message-templates.destroy', $template) }}" onsubmit="return confirm('Hapus template ini?')">
                                @csrf
                                @method('DELETE')
                                <x-btn variant="ghost" type="submit">Hapus</x-btn>
                            </form>
                        @endcan
                    </div>
                    <textarea readonly rows="7" class="mt-3 w-full rounded-xl border border-line bg-paper px-3 py-2 font-mono text-sm" id="tpl-{{ $template->id }}">{{ $template->renderedFor(auth()->user()) }}</textarea>
                    <x-btn variant="secondary" type="button" data-copy="#tpl-{{ $template->id }}" class="mt-2">Salin</x-btn>
                </article>
            @empty
                <p class="text-sm text-ink/55">Belum ada template.</p>
            @endforelse
            <p data-filter-empty class="hidden text-sm text-ink/55">Tidak ada template yang cocok.</p>
        </div>
    </div>
@endsection
