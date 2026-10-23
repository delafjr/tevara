<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Pengurus - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">

@php
    $user = $user ?? session('user');
    $userName = $user['name'] ?? 'Pengurus';
    $firstName = explode(' ', $userName)[0] ?? 'Pengurus';
@endphp

<div class="flex min-h-screen">

    {{-- Reusable Sidebar Component --}}
    <x-sidebar active="dashboard" />

    {{-- ===== MAIN ===== --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen">

        <main class="flex-1 px-8 py-7 space-y-7">

            {{-- ── HERO CARD ── --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
                {{-- gradient dekoratif --}}
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        Workspace Pengurus TeFa
                    </span>
                    <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                        Selamat Datang, <span class="text-[#FFB800]">{{ $userName }}</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1.5">Kelola seluruh operasional TeFa, validasi proposal, dan pantau progress tim secara terpadu</p>
                </div>

                <div class="relative z-10 flex flex-col items-end gap-2 flex-shrink-0">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[11.5px] font-semibold px-3 py-1.5 rounded-lg">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        Data terbaru
                    </span>
                </div>
            </div>

            {{-- ── 4 STAT CARDS ── --}}
            <div class="grid grid-cols-4 gap-4">
                @php
                $stats = [
                    ['label' => 'Total Project',      'value' => '28',  'sub' => '+4 dari bulan lalu', 'up' => true,  'color' => '#0B3B82', 'icon' => 'folder'],
                    ['label' => 'Total Klien / Mitra','value' => '12',  'sub' => '+2 dari bulan lalu', 'up' => true,  'color' => '#F59E0B', 'icon' => 'users'],
                    ['label' => 'Mahasiswa Terlibat', 'value' => '142', 'sub' => '+18 dari bulan lalu','up' => true,  'color' => '#6366F1', 'icon' => 'academic'],
                    ['label' => 'Project Berjalan',   'value' => '17',  'sub' => '+3 dari bulan lalu', 'up' => true,  'color' => '#16A34A', 'icon' => 'play'],
                ];
                @endphp

                @foreach($stats as $stat)
                <div class="bg-white rounded-xl px-5 py-5 border border-slate-100 shadow-sm">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background-color: {{ $stat['color'] }}18">
                            <span style="color: {{ $stat['color'] }}" class="w-4 h-4 block">
                                @if($stat['icon'] === 'folder')
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>
                                @elseif($stat['icon'] === 'users')
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                                @elseif($stat['icon'] === 'academic')
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84 51.39 51.39 0 0 0-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
                                @elseif($stat['icon'] === 'play')
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                                @endif
                            </span>
                        </div>
                        @if($stat['up'] === true)
                        <span class="text-[10px] font-semibold text-green-600 bg-green-50 px-1.5 py-0.5 rounded">↑</span>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-[#1E293B] leading-none">{{ $stat['value'] }}</p>
                    <p class="text-xs text-slate-400 mt-1.5 font-medium">{{ $stat['label'] }}</p>
                    <p class="text-[11px] mt-1" style="color: {{ $stat['color'] }}">{{ $stat['sub'] }}</p>
                </div>
                @endforeach
            </div>

            {{-- ── QUICK ACTIONS ── --}}
            <div class="grid grid-cols-3 gap-3">
                <a href="#"
                   class="flex items-center gap-3.5 bg-[#0B3B82] text-white rounded-xl px-5 py-4 font-semibold text-sm hover:bg-[#0a3372] transition-colors shadow-sm">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                    </svg>
                    Validasi Proposal
                    <span class="ml-auto bg-[#FFB800] text-[#1E293B] text-[10px] font-bold px-1.5 py-0.5 rounded-full">7</span>
                </a>
                <a href="#"
                   class="flex items-center gap-3.5 bg-white border border-slate-200 text-[#1E293B] rounded-xl px-5 py-4 font-semibold text-sm hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors shadow-sm">
                    <svg class="w-5 h-5 flex-shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Tambah Project
                </a>
                <a href="#"
                   class="flex items-center gap-3.5 bg-white border border-slate-200 text-[#1E293B] rounded-xl px-5 py-4 font-semibold text-sm hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors shadow-sm">
                    <svg class="w-5 h-5 flex-shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                    </svg>
                    Lihat Laporan
                </a>
            </div>

            {{-- ── BOTTOM GRID: Table (col-2) + 2 Charts (col-1 stacked) ── --}}
            <div class="grid grid-cols-3 gap-6">

                {{-- Project Terbaru (col-span-2) --}}
                <div class="col-span-2 bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-[#1E293B] text-[15px]">Project Terbaru</p>
                            <p class="text-xs text-slate-400 mt-0.5">Semua project yang terdaftar</p>
                        </div>
                        <a href="#" class="text-xs font-semibold text-[#0B3B82] hover:underline flex items-center gap-1">
                            Lihat Semua
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-6 py-3">Nama Project</th>
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Klien / Mitra</th>
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">PM / Dosen</th>
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Status</th>
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3 w-32">Progress</th>
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Deadline</th>
                                    <th class="px-3 py-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @php
                                $projects = [
                                    ['name' => 'Sistem Informasi UMKM',    'client' => 'PT. ABC Indonesia',  'pm' => 'Dimas Setiawan', 'category' => 'Website',  'status' => 'Berjalan', 'progress' => 75,  'deadline' => '12 Okt 2026'],
                                    ['name' => 'Smart Farming IoT',        'client' => 'CV. Tani Maju',      'pm' => 'Rizky Pratama',  'category' => 'IoT',      'status' => 'Review',   'progress' => 60,  'deadline' => '5 Okt 2026'],
                                    ['name' => 'Website Company Profile',  'client' => 'PT. DEF Solusi',     'pm' => 'Nadia Putri',    'category' => 'Website',  'status' => 'Selesai',  'progress' => 100, 'deadline' => '—'],
                                    ['name' => 'Aplikasi Inventori',       'client' => 'PT. Maju Sentosa',   'pm' => 'Thomas Elang',   'category' => 'Aplikasi', 'status' => 'Proposal', 'progress' => 25,  'deadline' => '18 Okt 2026'],
                                    ['name' => 'Monitoring Produksi',      'client' => 'CV. Tani Maju',      'pm' => 'Dimas Setiawan', 'category' => 'Aplikasi', 'status' => 'Berjalan', 'progress' => 40,  'deadline' => '25 Okt 2026'],
                                ];

                                $statusStyle = [
                                    'Berjalan' => 'bg-blue-50 text-blue-700',
                                    'Review'   => 'bg-amber-50 text-amber-700',
                                    'Selesai'  => 'bg-green-50 text-green-700',
                                    'Proposal' => 'bg-purple-50 text-purple-700',
                                ];

                                $progressColor = fn($p) => $p >= 100 ? '#16A34A' : ($p >= 60 ? '#0B3B82' : ($p >= 30 ? '#F59E0B' : '#DC2626'));
                                @endphp

                                @foreach($projects as $project)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-3.5">
                                        <div>
                                            <p class="font-semibold text-[#1E293B] text-[13px] leading-tight">{{ $project['name'] }}</p>
                                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $project['category'] }}</p>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <span class="text-[12px] text-slate-600">{{ $project['client'] }}</span>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <span class="text-[12px] text-slate-600">{{ $project['pm'] }}</span>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $statusStyle[$project['status']] ?? 'bg-slate-100 text-slate-600' }}">
                                            {{ $project['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 w-32">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full"
                                                     style="width: {{ $project['progress'] }}%; background-color: {{ $progressColor($project['progress']) }}">
                                                </div>
                                            </div>
                                            <span class="text-[11px] font-semibold text-slate-500 w-7 text-right">{{ $project['progress'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <span class="text-[12px] text-slate-500">{{ $project['deadline'] }}</span>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <button class="p-1 rounded hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm0 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm0 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z"/></svg>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Right col: Donut + Status stacked --}}
                <div class="flex flex-col gap-6">

                    {{-- Distribusi Kategori --}}
                    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 flex flex-col">
                        <div class="mb-4">
                            <p class="font-semibold text-[#1E293B] text-[15px]">Distribusi Kategori</p>
                            <p class="text-xs text-slate-400 mt-0.5">Project per kategori</p>
                        </div>

                        <div class="flex items-center justify-center" x-data="{
                            segments: [
                                { label: 'Website',         pct: 28.6, color: '#0B3B82' },
                                { label: 'IoT & Embedded',  pct: 21.4, color: '#FFB800' },
                                { label: 'Aplikasi',        pct: 17.9, color: '#6366F1' },
                                { label: 'Sistem Informasi',pct: 14.3, color: '#16A34A' },
                                { label: 'Desain',          pct: 10.7, color: '#EC4899' },
                                { label: 'Lainnya',         pct: 7.1,  color: '#94A3B8' },
                            ],
                            total: 28,
                            get arcs() {
                                let offset = 0;
                                return this.segments.map(s => {
                                    const da = (s.pct / 100) * 251.2;
                                    const off = -(offset / 100) * 251.2;
                                    offset += s.pct;
                                    return { ...s, da, off };
                                });
                            }
                        }">
                            <div class="relative w-36 h-36">
                                <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="#F1F5F9" stroke-width="16"/>
                                    <template x-for="arc in arcs" :key="arc.label">
                                        <circle cx="50" cy="50" r="40" fill="none"
                                            :stroke="arc.color" stroke-width="16"
                                            :stroke-dasharray="`${arc.da} ${251.2 - arc.da}`"
                                            :stroke-dashoffset="arc.off"/>
                                    </template>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <p class="text-2xl font-bold text-[#1E293B]">28</p>
                                    <p class="text-[10px] text-slate-400 font-medium">Project</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2">
                            @php
                            $donut = [
                                ['label' => 'Website',          'count' => 8,  'color' => '#0B3B82'],
                                ['label' => 'IoT & Embedded',   'count' => 6,  'color' => '#FFB800'],
                                ['label' => 'Aplikasi',         'count' => 5,  'color' => '#6366F1'],
                                ['label' => 'Sistem Informasi', 'count' => 4,  'color' => '#16A34A'],
                                ['label' => 'Desain & Kreatif', 'count' => 3,  'color' => '#EC4899'],
                                ['label' => 'Lainnya',          'count' => 2,  'color' => '#94A3B8'],
                            ];
                            @endphp
                            @foreach($donut as $d)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-sm flex-shrink-0" style="background-color: {{ $d['color'] }}"></span>
                                    <span class="text-[11.5px] text-slate-600">{{ $d['label'] }}</span>
                                </div>
                                <span class="text-[11.5px] font-semibold text-[#1E293B]">{{ $d['count'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Status Project --}}
                    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
                        <div class="mb-4">
                            <p class="font-semibold text-[#1E293B] text-[15px]">Status Project</p>
                            <p class="text-xs text-slate-400 mt-0.5">Distribusi berdasarkan status</p>
                        </div>

                        @php
                        $statuses = [
                            ['label' => 'Proposal',   'count' => 5,  'color' => '#6366F1', 'total' => 28],
                            ['label' => 'Review',     'count' => 4,  'color' => '#F59E0B', 'total' => 28],
                            ['label' => 'Berjalan',   'count' => 17, 'color' => '#0B3B82', 'total' => 28],
                            ['label' => 'Selesai',    'count' => 16, 'color' => '#16A34A', 'total' => 28],
                            ['label' => 'Dibatalkan', 'count' => 2,  'color' => '#DC2626', 'total' => 28],
                        ];
                        @endphp

                        <div class="space-y-3">
                            @foreach($statuses as $s)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $s['color'] }}"></span>
                                        <span class="text-[12px] text-slate-600 font-medium">{{ $s['label'] }}</span>
                                    </div>
                                    <span class="text-[12px] font-bold text-[#1E293B]">{{ $s['count'] }}</span>
                                </div>
                                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500"
                                         style="width: {{ round($s['count'] / $s['total'] * 100) }}%; background-color: {{ $s['color'] }}">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>{{-- /right col --}}
            </div>{{-- /bottom grid --}}

        </main>

        <footer class="px-8 py-4 border-t border-slate-100 bg-white">
            <p class="text-[11px] text-slate-400">© {{ date('Y') }} TEVARA · Politeknik Negeri Semarang</p>
        </footer>
    </div>
</div>

</body>
</html>