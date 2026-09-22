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
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Beranda</x-nav-link>
                    <x-nav-link :href="route('announcements.index')" :active="request()->routeIs('announcements.*')">Info</x-nav-link>
                    <x-nav-link :href="route('roster.index')" :active="request()->routeIs('roster.*') || request()->routeIs('mahasiswa-imports.*')">Anggota</x-nav-link>
                    <x-nav-link :href="route('attendances.index')" :active="request()->routeIs('attendances.*')">Hadir</x-nav-link>
                    <x-nav-link :href="route('lecturer-questions.index')" :active="request()->routeIs('lecturer-questions.*')">Tanya</x-nav-link>
                    <x-nav-link :href="route('message-templates.index')" :active="request()->routeIs('message-templates.*')">Pesan</x-nav-link>
                    <x-nav-link :href="route('profiles.edit')" :active="request()->routeIs('profiles.*')">Profil</x-nav-link>
                </nav>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-line bg-card/95 px-4 py-3 backdrop-blur md:px-8">
                    <div class="flex min-w-0 items-center gap-3 md:hidden">
                        <x-brand compact />
                    </div>
                    <a href="{{ route('profiles.edit') }}" class="hidden min-w-0 items-center gap-3 md:flex">
                        <x-avatar :user="auth()->user()" size="sm" :lazy="false" />
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-ink/50">{{ auth()->user()->nim }} · {{ auth()->user()->role->label() }}</p>
                        </div>
                    </a>
                    <a href="{{ route('profiles.edit') }}" class="min-w-0 truncate text-sm font-medium md:hidden">{{ auth()->user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-btn variant="ghost" type="submit">Keluar</x-btn>
                    </form>
                </header>

                <main class="min-w-0 flex-1 overflow-x-hidden px-4 py-5 pb-24 md:px-8 md:py-8 md:pb-8">
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

        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-card/95 backdrop-blur md:hidden" style="padding-bottom: env(safe-area-inset-bottom)">
            <div class="flex gap-1 overflow-x-auto px-2 py-1 text-sm">
                <x-nav-link class="min-h-11 shrink-0 text-center text-xs" :href="route('dashboard')" :active="request()->routeIs('dashboard')">Beranda</x-nav-link>
                <x-nav-link class="min-h-11 shrink-0 text-center text-xs" :href="route('announcements.index')" :active="request()->routeIs('announcements.*')">Info</x-nav-link>
                <x-nav-link class="min-h-11 shrink-0 text-center text-xs" :href="route('attendances.index')" :active="request()->routeIs('attendances.*')">Hadir</x-nav-link>
                <x-nav-link class="min-h-11 shrink-0 text-center text-xs" :href="route('roster.index')" :active="request()->routeIs('roster.*')">Anggota</x-nav-link>
                <x-nav-link class="min-h-11 shrink-0 text-center text-xs" :href="route('lecturer-questions.index')" :active="request()->routeIs('lecturer-questions.*')">Tanya</x-nav-link>
                <x-nav-link class="min-h-11 shrink-0 text-center text-xs" :href="route('message-templates.index')" :active="request()->routeIs('message-templates.*')">Pesan</x-nav-link>
                <x-nav-link class="min-h-11 shrink-0 text-center text-xs" :href="route('profiles.edit')" :active="request()->routeIs('profiles.*')">Profil</x-nav-link>
            </div>
        </nav>
        @stack('scripts')
    </body>
</html>
