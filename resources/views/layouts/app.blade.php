<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Dasbor') · {{ config('kelas.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-paper font-sans text-ink antialiased">
        <div class="mx-auto flex min-h-screen max-w-6xl">
            <aside class="hidden w-60 shrink-0 border-r border-line bg-card px-5 py-6 md:block">
                <x-brand />
                <nav class="mt-10 flex flex-col gap-1 text-sm">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dasbor</x-nav-link>
                    <x-nav-link :href="route('announcements.index')" :active="request()->routeIs('announcements.*')">Pengumuman</x-nav-link>
                    <x-nav-link :href="route('roster.index')" :active="request()->routeIs('roster.*') || request()->routeIs('mahasiswa-imports.*')">Anggota</x-nav-link>
                    <x-nav-link :href="route('absence-requests.index')" :active="request()->routeIs('absence-requests.*')">Izin</x-nav-link>
                    <x-nav-link :href="route('lecturer-questions.index')" :active="request()->routeIs('lecturer-questions.*')">Pertanyaan</x-nav-link>
                    <x-nav-link :href="route('message-templates.index')" :active="request()->routeIs('message-templates.*')">Template</x-nav-link>
                    <x-nav-link :href="route('profiles.edit')" :active="request()->routeIs('profiles.*')">Profil</x-nav-link>
                </nav>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="flex items-center justify-between gap-3 border-b border-line bg-card px-4 py-3 md:px-8">
                    <div class="flex min-w-0 items-center gap-3 md:hidden">
                        <x-brand compact />
                    </div>
                    <a href="{{ route('profiles.edit') }}" class="flex min-w-0 items-center gap-3">
                        <x-avatar :user="auth()->user()" size="sm" />
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-ink/50">{{ auth()->user()->nim }} · {{ auth()->user()->role->label() }}</p>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-btn variant="ghost" type="submit">Keluar</x-btn>
                    </form>
                </header>

                <nav class="flex gap-1 overflow-x-auto border-b border-line bg-card px-3 py-2 text-sm md:hidden">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dasbor</x-nav-link>
                    <x-nav-link :href="route('announcements.index')" :active="request()->routeIs('announcements.*')">Info</x-nav-link>
                    <x-nav-link :href="route('roster.index')" :active="request()->routeIs('roster.*')">Anggota</x-nav-link>
                    <x-nav-link :href="route('absence-requests.index')" :active="request()->routeIs('absence-requests.*')">Izin</x-nav-link>
                    <x-nav-link :href="route('lecturer-questions.index')" :active="request()->routeIs('lecturer-questions.*')">Tanya</x-nav-link>
                    <x-nav-link :href="route('message-templates.index')" :active="request()->routeIs('message-templates.*')">Template</x-nav-link>
                    <x-nav-link :href="route('profiles.edit')" :active="request()->routeIs('profiles.*')">Profil</x-nav-link>
                </nav>

                <main class="flex-1 px-4 py-8 md:px-8">
                    <div data-toast-host class="mb-5 empty:mb-0">
                        @if (session('status'))
                            <p class="rounded-2xl border border-navy/15 bg-navy/5 px-4 py-2.5 text-sm text-navy">{{ session('status') }}</p>
                        @endif
                    </div>

                    @if (session('import_errors'))
                        <ul class="mb-5 rounded-2xl border border-accent-hot/20 bg-accent-hot/5 px-4 py-2.5 text-sm text-accent-hot">
                            @foreach (session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
