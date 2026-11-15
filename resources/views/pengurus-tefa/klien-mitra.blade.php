<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Klien & Mitra - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user');
    $clients = is_array($clients ?? null) ? $clients : [];
    $totalProjects = collect($clients)->sum(fn ($client) => (int) ($client['total_projects'] ?? 0));
    $totalContacts = collect($clients)->sum(fn ($client) => count(is_array($client['contacts'] ?? null) ? $client['contacts'] : []));
@endphp
<div class="flex min-h-screen">
    <x-sidebar active="klien-mitra" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 space-y-7" x-data="{ searchQuery: '' }">

            {{-- Hero Card --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
                {{-- gradient dekoratif --}}
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        Teaching Factory · Polines
                    </span>
                    <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                        Data Klien &amp; <span class="text-[#FFB800]">Mitra TeFa</span>
                    </h1>
                    <p class="text-sm text-[#64748B] mt-1.5 max-w-2xl leading-relaxed">
                        Kelola data klien dan mitra yang telah bekerja sama dengan Teaching Factory.
                    </p>
                </div>

            </div>

            @if (!empty($error))
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">{{ $error }}</div>
            @endif

            <section class="grid grid-cols-1 sm:grid-cols-3 gap-4" aria-label="Ringkasan klien dan mitra">
                <div class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Instansi</p>
                    <p class="mt-2 text-2xl font-bold text-[#0B3B82]">{{ count($clients) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Klien/mitra tersinkron</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Projek</p>
                    <p class="mt-2 text-2xl font-bold text-[#0B3B82]">{{ $totalProjects }}</p>
                    <p class="mt-1 text-xs text-slate-500">Projek dari email instansi</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kontak PIC</p>
                    <p class="mt-2 text-2xl font-bold text-[#0B3B82]">{{ $totalContacts }}</p>
                    <p class="mt-1 text-xs text-slate-500">PIC yang tercatat di database</p>
                </div>
            </section>

            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-[#1E293B] text-[15px]">Daftar Klien & Mitra</p>
                    </div>
                    <label class="relative block w-full sm:w-80">
                        <span class="sr-only">Cari klien, instansi, atau PIC</span>
                        <input type="search" x-model.debounce.150ms="searchQuery" placeholder="Cari klien, instansi, atau PIC"
                               class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-3 pr-3 text-sm focus:border-[#0B3B82] focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10">
                    </label>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-6 py-3">Nama Klien / Instansi</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Kontak PIC</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Total Projek</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($clients as $c)
                            @php
                                $searchable = strtolower(($c['name'] ?? '') . ' ' . implode(' ', is_array($c['contacts'] ?? null) ? $c['contacts'] : []));
                            @endphp
                            <tr data-search="{{ $searchable }}" x-show="!searchQuery || $el.dataset.search.includes(searchQuery.toLowerCase().trim())" class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-[#0B3B82]/10 flex items-center justify-center text-[11px] font-bold text-[#0B3B82] flex-shrink-0">{{ strtoupper(substr($c['name'],0,2)) }}</div>
                                        <p class="font-semibold text-[#1E293B] text-[13px]">{{ $c['name'] }}</p>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-600">
                                    @forelse($c['contacts'] as $contact)
                                        <p>{{ $contact }}</p>
                                    @empty
                                        —
                                    @endforelse
                                </td>
                                <td class="px-3 py-3.5 text-[12px] font-semibold text-slate-700 text-center">{{ $c['total_projects'] }}</td>
                            </tr>
                            @empty
                                <tr><td colspan="3" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada data klien di database.</td></tr>
                            @endforelse
                            <tr x-cloak x-show="searchQuery && Array.from($el.parentElement.querySelectorAll('tr[data-search]')).every(row => !row.dataset.search.includes(searchQuery.toLowerCase().trim()))">
                                <td colspan="3" class="px-6 py-10 text-center text-sm text-slate-500">Tidak ada klien yang sesuai dengan pencarian.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <footer class="px-8 py-4 border-t border-slate-100 bg-white">
            <p class="text-[11px] text-slate-400 font-medium">© {{ date('Y') }} TEVARA · Politeknik Negeri Semarang · Teaching Factory</p>
        </footer>
    </div>
</div>
</body>
</html>
