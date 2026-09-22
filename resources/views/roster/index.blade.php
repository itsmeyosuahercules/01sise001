@extends('layouts.app')

@section('title', 'Anggota')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Anggota kelas</h1>
            <p class="mt-1 text-sm text-ink/60">{{ $members->count() }} orang.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('import', App\Models\User::class)
                <x-btn tag="a" variant="secondary" href="{{ route('mahasiswa-imports.create') }}">Impor NIM</x-btn>
            @endcan
            @can('export', App\Models\User::class)
                <x-btn tag="a" href="{{ route('roster.export') }}">Unduh daftar</x-btn>
            @endcan
        </div>
    </div>

    <div data-filter-root class="mt-6">
        <div class="mb-4 flex flex-wrap gap-2">
            <input
                type="search"
                data-filter-q
                placeholder="Cari nama atau NIM…"
                class="w-full min-w-0 flex-1 rounded-xl border border-line bg-card px-3 py-2.5 text-base outline-none focus:border-navy sm:text-sm"
            >
            <select data-filter-key="role" class="rounded-xl border border-line bg-card px-3 py-2 text-sm">
                <option value="">Semua peran</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                @endforeach
            </select>
        </div>

        <div class="space-y-3 md:hidden">
            @foreach ($members as $member)
                <article
                    data-filter-row
                    data-search="{{ $member->nim }} {{ $member->name }} {{ $member->bio }}"
                    data-role="{{ $member->role->value }}"
                    class="rounded-2xl border border-line bg-card p-4"
                >
                    <a href="{{ route('profiles.show', $member) }}" class="flex items-center gap-3">
                        <x-avatar :user="$member" size="sm" />
                        <span class="min-w-0">
                            <span class="block font-medium">{{ $member->name }}</span>
                            <span class="block font-mono text-xs text-ink/50">{{ $member->nim }}</span>
                            @if ($member->bio)
                                <span class="mt-0.5 block line-clamp-2 text-xs text-ink/55">{{ $member->bio }}</span>
                            @endif
                        </span>
                    </a>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                        @can('updateRole', $member)
                            <form method="POST" action="{{ route('roster.update', $member) }}" data-remote="role" class="min-w-0 flex-1">
                                @csrf
                                @method('PATCH')
                                <select name="role" data-previous="{{ $member->role->value }}" class="w-full rounded-lg border border-line bg-paper px-3 py-2 text-sm" onchange="this.form.requestSubmit()">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->value }}" @selected($member->role === $role)>{{ $role->label() }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            <span class="text-sm text-ink/65">{{ $member->role->label() }}</span>
                        @endcan
                        @can('resetPassword', $member)
                            @if (auth()->id() === $member->id)
                                <a href="{{ route('profiles.edit') }}" class="text-sm text-navy hover:underline">Ganti sandi saya</a>
                            @else
                                <a href="{{ route('profiles.show', $member) }}#sandi" class="text-sm text-navy hover:underline">Setel sandi</a>
                            @endif
                        @endcan
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-4 hidden overflow-x-auto rounded-2xl border border-line bg-card md:block">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="border-b border-line text-xs tracking-wide text-ink/45 uppercase">
                    <tr>
                        <th class="px-4 py-3 font-medium">NIM</th>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Peran</th>
                        @can('resetPassword', auth()->user())
                            <th class="px-4 py-3 font-medium">Sandi</th>
                        @endcan
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
                            @can('resetPassword', $member)
                                <td class="px-4 py-3">
                                    @if (auth()->id() === $member->id)
                                        <a href="{{ route('profiles.edit') }}" class="text-navy hover:underline">Ganti sandi saya</a>
                                    @else
                                        <a href="{{ route('profiles.show', $member) }}#sandi" class="text-navy hover:underline">Setel sandi</a>
                                    @endif
                                </td>
                            @endcan
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p data-filter-empty class="hidden px-1 py-6 text-sm text-ink/50">Tidak ada anggota yang cocok.</p>
    </div>
@endsection
