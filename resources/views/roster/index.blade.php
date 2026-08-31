@extends('layouts.app')

@section('title', 'Anggota')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Anggota kelas</h1>
            <p class="mt-1 text-sm text-ink/60">{{ $members->count() }} orang · siap diekspor ke prodi.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('import', App\Models\User::class)
                <x-btn tag="a" variant="secondary" href="{{ route('mahasiswa-imports.create') }}">Impor NIM</x-btn>
            @endcan
            @can('export', App\Models\User::class)
                <x-btn tag="a" href="{{ route('roster.export') }}">Unduh CSV</x-btn>
            @endcan
        </div>
    </div>

    <div data-filter-root class="mt-6">
        <div class="mb-4 flex flex-wrap gap-2">
            <input
                type="search"
                data-filter-q
                placeholder="Cari nama atau NIM…"
                class="min-w-[16rem] flex-1 rounded-xl border border-line bg-card px-3 py-2 text-sm outline-none focus:border-navy"
            >
            <select data-filter-key="role" class="rounded-xl border border-line bg-card px-3 py-2 text-sm">
                <option value="">Semua peran</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-line bg-card">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="border-b border-line text-xs tracking-wide text-ink/45 uppercase">
                    <tr>
                        <th class="px-4 py-3 font-medium">NIM</th>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Peran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $member)
                        <tr
                            data-filter-row
                            data-search="{{ $member->nim }} {{ $member->name }} {{ $member->bio }}"
                            data-role="{{ $member->role->value }}"
                            class="border-b border-line/70 last:border-0"
                        >
                            <td class="px-4 py-3 font-mono text-xs">{{ $member->nim }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('profiles.show', $member) }}" class="flex items-center gap-3 hover:text-navy">
                                    <x-avatar :user="$member" size="sm" />
                                    <span>
                                        <span class="block font-medium">{{ $member->name }}</span>
                                        @if ($member->bio)
                                            <span class="mt-0.5 block line-clamp-1 text-xs text-ink/50">{{ $member->bio }}</span>
                                        @endif
                                    </span>
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                @can('updateRole', $member)
                                    <form method="POST" action="{{ route('roster.update', $member) }}" data-remote="role">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" data-previous="{{ $member->role->value }}" class="rounded-lg border border-line bg-paper px-2 py-1 text-sm" onchange="this.form.requestSubmit()">
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->value }}" @selected($member->role === $role)>{{ $role->label() }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    {{ $member->role->label() }}
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    <tr data-filter-empty class="hidden">
                        <td colspan="3" class="px-4 py-8 text-ink/50">Tidak ada anggota yang cocok.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
