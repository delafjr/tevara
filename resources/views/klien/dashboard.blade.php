{{-- == ( struktur dasar halaman ) == --}}
<!DOCTYPE html>
<html lang="id">
<head>
{{-- == ( head : meta + judul halaman ) == --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
{{-- == ( halaman / title ) == --}}
    <title>Dashboard - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">

@php
    $user = $user ?? session('user');
    $userName = $user['name'] ?? 'Klien';
@endphp

{{-- == ( layout utama : sidebar + main ) == --}}
<div class="flex min-h-screen">

    {{-- == ( sidebar komponen DRY ) == --}}
    <x-sidebar active="dashboard" />

    {{-- == ( konten utama ) == --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen">

        {{-- Page content --}}
        <main class="flex-1 px-8 py-7 space-y-7">

            {{-- == ( hero card : workspace klien ) == --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
                {{-- gradient dekoratif --}}
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        Workspace Klien
                    </span>
                    <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                        Selamat Datang, <span class="text-[#FFB800]">{{ $userName }}</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1.5">Pantau perkembangan proyek dan kolaborasi bersama TEVARA secara terpadu</p>
                </div>

                <div class="relative z-10 flex flex-col items-end gap-2 flex-shrink-0">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[11.5px] font-semibold px-3 py-1.5 rounded-lg">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        Data terbaru
                    </span>
                </div>
            </div>

            {{-- == ( 4 kartu statistik ) == --}}
            <div class="grid grid-cols-4 gap-4">

                @php
                $stats = [
                    ['label' => 'Total Proyek',         'value' => 10, 'sub' => 'Inisiatif Terdaftar', 'color' => '#0B3B82', 'icon' => 'folder'],
                    ['label' => 'Proyek Berjalan',      'value' => 5,  'sub' => 'Dalam Pengerjaan',    'color' => '#0B3B82', 'icon' => 'play'],
                    ['label' => 'Proyek Selesai',       'value' => 2,  'sub' => 'Siap Produksi',       'color' => '#0B3B82', 'icon' => 'check'],
                    ['label' => 'Menunggu Persetujuan', 'value' => 3,  'sub' => 'Perlu Review',        'color' => '#F59E0B', 'icon' => 'clock-file'],
                ];
                @endphp

                @foreach($stats as $stat)
                <div class="bg-white rounded-xl px-5 py-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between gap-3 mb-5">
                        <p class="text-[11.5px] font-bold tracking-wide uppercase text-slate-500">{{ $stat['label'] }}</p>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background-color: {{ $stat['color'] }}14">
                            <span style="color: {{ $stat['color'] }}" class="block w-[18px] h-[18px]">
                                @if($stat['icon'] === 'folder')
                                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>
                                @elseif($stat['icon'] === 'play')
                                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z"/></svg>
                                @elseif($stat['icon'] === 'check')
                                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                @elseif($stat['icon'] === 'clock-file')
                                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                @endif
                            </span>
                        </div>
                    </div>
                    <p class="leading-none">
                        <span class="text-3xl font-bold" style="color: {{ $stat['color'] }}">{{ $stat['value'] }}</span>
                        <span class="text-xs text-slate-400 font-medium ml-1.5">{{ $stat['sub'] }}</span>
                    </p>
                </div>
                @endforeach
            </div>

            {{-- == ( tombol aksi : ajukan & lihat semua ) == --}}
            <div class="flex items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <a href="{{ route('klien.ajukan-proyek') }}"
                       class="flex items-center gap-2 bg-[#0B3B82] text-white rounded-full px-5 py-3 font-semibold text-sm hover:bg-[#0a3372] transition-colors shadow-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Ajukan Proyek Baru
                    </a>
                    <a href="{{ route('klien.projek-saya') }}"
                       class="flex items-center gap-2 bg-white border border-slate-200 text-[#1E293B] rounded-full px-5 py-3 font-semibold text-sm hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors shadow-sm">
                        <svg class="w-4 h-4 flex-shrink-0 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                        Lihat Semua Proyek
                    </a>
                </div>

                {{-- == ( kotak pencarian proyek ) == --}}
                <div class="relative w-72">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    </span>
                    <input id="project-search" type="text" placeholder="Cari nama proyek"
                           class="w-full bg-white border border-slate-200 rounded-full pl-10 pr-4 py-3 text-sm placeholder:text-slate-400 focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition">
                </div>
            </div>

            {{-- == ( area bawah : tabel + donut ) == --}}
            <div class="grid grid-cols-3 gap-6 items-start">

                {{-- == ( tabel proyek saya ) == --}}
                <div class="col-span-2 bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="font-bold text-[#1E293B] text-[16px]">Proyek Saya</p>
                            <p class="text-xs text-slate-400 mt-0.5">Semua project yang ditugaskan ke Anda</p>
                        </div>
                        <a href="{{ route('klien.projek-saya') }}" class="text-xs font-bold text-[#0B3B82] hover:underline flex items-center gap-1.5">
                            Lihat Semua
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>

                    {{-- == ( header tabel ) == --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm table-fixed">
                            <thead>
                                <tr class="bg-[#EAF3FF]/60">
                                    <th class="text-left text-[11px] font-bold uppercase tracking-wide text-slate-500 px-6 py-3">Nama Project</th>
                                    <th class="text-left text-[11px] font-bold uppercase tracking-wide text-slate-500 px-3 py-3">Klien / Mitra</th>
                                    <th class="text-left text-[11px] font-bold uppercase tracking-wide text-slate-500 px-3 py-3">Status</th>
                                    <th class="text-left text-[11px] font-bold uppercase tracking-wide text-slate-500 px-3 py-3 w-36">Progress</th>
                                </tr>
                            </thead>
                            {{-- == ( baris data & progress bar : loop proyek ) == --}}
                            <tbody class="divide-y divide-slate-50">
                                @php
                                $projects = [
                                    ['name' => 'Sistem Informasi UMKM',        'client' => 'PT. ABC Indonesia',      'status' => 'Berjalan', 'progress' => 75],
                                    ['name' => 'Smart Farming IoT',            'client' => 'CV. Tani Maju',          'status' => 'Review',   'progress' => 45],
                                    ['name' => 'Website Company Profile',      'client' => 'PT. DEF Solusi',         'status' => 'Selesai',  'progress' => 100],
                                    ['name' => 'Aplikasi Manajemen Inventori', 'client' => 'PT. Logistik Nusantara', 'status' => 'Berjalan', 'progress' => 30],
                                ];

                                $statusStyle = [
                                    'Berjalan' => 'bg-[#EAF3FF] text-[#0B3B82]',
                                    'Review'   => 'bg-amber-100 text-amber-700',
                                    'Selesai'  => 'bg-green-50 text-green-700',
                                ];

                                $progressColor = fn($p) => $p >= 100 ? '#16A34A' : ($p >= 50 ? '#0B3B82' : '#F59E0B');
                                @endphp

                                @foreach($projects as $project)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-[#1E293B] text-[13px] leading-snug">{{ $project['name'] }}</p>
                                    </td>
                                    <td class="px-3 py-4">
                                        <p class="text-[13px] text-slate-600 leading-snug">{{ $project['client'] }}</p>
                                    </td>
                                    <td class="px-3 py-4">
                                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $statusStyle[$project['status']] ?? 'bg-slate-100 text-slate-600' }}">
                                            {{ $project['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 w-36">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full transition-all"
                                                     style="width: {{ $project['progress'] }}%; background-color: {{ $progressColor($project['progress']) }}">
                                                </div>
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-500 w-8 text-right">{{ $project['progress'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- == ( pagination tabel ) == --}}
                    <div class="px-6 py-4 flex items-center justify-between">
                        <p class="text-[11.5px] text-slate-400">Menampilkan <span class="font-semibold">1–4</span> dari <span class="font-semibold">10</span> proyek</p>
                        <div class="flex items-center gap-1.5">
                            <button class="w-7 h-7 rounded-md flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                            </button>
                            @foreach([1, 2, 3] as $page)
                            <button class="w-7 h-7 rounded-md text-xs font-semibold transition-colors {{ $page === 1 ? 'bg-[#EAF3FF] text-[#0B3B82]' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">{{ $page }}</button>
                            @endforeach
                            <button class="w-7 h-7 rounded-md flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- == ( donut chart : distribusi kategori ) == --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 flex flex-col">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <p class="font-bold text-[#1E293B] text-[16px]">Distribusi Kategori</p>
                            <p class="text-xs text-slate-400 mt-0.5">Klasifikasi seluruh portofolio</p>
                        </div>
                        <span class="w-9 h-9 rounded-lg bg-[#EAF3FF] flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 1 0-18 9 9 0 0 1 9 9H12m0 0v9"/></svg>
                        </span>
                    </div>

                    {{-- SVG Donut --}}
                    <div class="flex items-center justify-center my-2" x-data="{
                        segments: [
                            { label: 'Website',    value: 6, color: '#0B3B82', pct: 40 },
                            { label: 'IoT & Emb.', value: 4, color: '#FFB800', pct: 26.67 },
                            { label: 'Aplikasi',   value: 3, color: '#4A6FA5', pct: 20 },
                            { label: 'Sistem',     value: 2, color: '#94A3B8', pct: 13.33 },
                        ],
                        total: 15,
                        get arcs() {
                            let offset = 0;
                            return this.segments.map(s => {
                                const dasharray = (s.pct / 100) * 251.2;
                                const dashoffset = -offset * 2.512;
                                offset += s.pct;
                                return { ...s, dasharray, dashoffset };
                            });
                        }
                    }">
                        <div class="relative w-44 h-44">
                            <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#F1F5F9" stroke-width="16"/>
                                <template x-for="arc in arcs" :key="arc.label">
                                    <circle
                                        cx="50" cy="50" r="40"
                                        fill="none"
                                        :stroke="arc.color"
                                        stroke-width="16"
                                        stroke-linecap="butt"
                                        :stroke-dasharray="`${arc.dasharray} ${251.2 - arc.dasharray}`"
                                        :stroke-dashoffset="arc.dashoffset"
                                    />
                                </template>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <p class="text-3xl font-bold text-[#1E293B]" x-text="total">15</p>
                                <p class="text-[11px] text-slate-400 font-bold tracking-wide">PROYEK</p>
                            </div>
                        </div>
                    </div>

                    {{-- == ( legenda donut 2 kolom ) == --}}
                    <div class="mt-5 grid grid-cols-2 gap-x-4 gap-y-2.5">
                        @php
                        $donutItems = [
                            ['label' => 'Website',    'count' => 6, 'color' => '#0B3B82'],
                            ['label' => 'IoT & Emb.', 'count' => 4, 'color' => '#FFB800'],
                            ['label' => 'Aplikasi',   'count' => 3, 'color' => '#4A6FA5'],
                            ['label' => 'Sistem',     'count' => 2, 'color' => '#94A3B8'],
                        ];
                        @endphp
                        @foreach($donutItems as $item)
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-sm flex-shrink-0" style="background-color: {{ $item['color'] }}"></span>
                            <span class="text-[12.5px] text-slate-600 truncate">{{ $item['label'] }}</span>
                            <span class="text-[12.5px] font-semibold text-slate-400">({{ $item['count'] }})</span>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>{{-- /bottom grid --}}

        </main>

    </div>
</div>

</body>
</html>