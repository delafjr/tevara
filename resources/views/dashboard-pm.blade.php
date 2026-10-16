<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Dosen PM - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">

@php
    $user = $user ?? session('user');
    $userName = $user['name'] ?? 'Dosen';
    $firstName = explode(' ', $userName)[0] ?? 'Dosen';
@endphp

<div class="flex min-h-screen">

    {{-- Reusable Sidebar Component --}}
    <x-sidebar active="dashboard" />

    {{-- ===== MAIN ===== --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen">

        <header class="bg-white border-b border-slate-100 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <p class="text-xs text-slate-400 font-medium">Dashboard</p>
                <p class="text-[15px] font-semibold text-[#1E293B] leading-tight">Selamat datang, {{ $firstName }} 👋</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400">{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
                <div class="w-px h-4 bg-slate-200"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-slate-500 hover:text-[#0B3B82] font-medium transition-colors">Logout</button>
                </form>
            </div>
        </header>

        <main class="flex-1 px-8 py-7 space-y-7">

            {{-- ── HERO ── --}}
            <div class="relative overflow-hidden rounded-2xl bg-[#0B3B82] px-8 py-7">
                <div class="absolute -right-16 -top-16 w-72 h-72 rounded-full bg-white/5"></div>
                <div class="absolute right-24 -bottom-10 w-40 h-40 rounded-full bg-[#FFB800]/10"></div>
                <div class="relative z-10 max-w-lg">
                    <p class="text-[#FFB800] text-xs font-semibold tracking-wide mb-2">Teaching Factory · Polines</p>
                    <h1 class="text-white text-2xl font-bold leading-snug">
                        Kelola project, tim, dan capaian<br>Teaching Factory dengan efektif.
                    </h1>
                    <p class="text-blue-200 text-sm mt-2 leading-relaxed">
                        Pantau progress mahasiswa, review laporan sprint, dan pastikan setiap milestone tercapai tepat waktu.
                    </p>
                </div>
            </div>

            {{-- ── 4 STAT CARDS ── --}}
            <div class="grid grid-cols-4 gap-4">
                @php
                $stats = [
                    ['label' => 'Total Project',    'value' => '15', 'sub' => '+3 dari bulan lalu',  'up' => true,  'color' => '#0B3B82', 'icon' => 'folder'],
                    ['label' => 'Project Berjalan', 'value' => '9',  'sub' => '+2 dari bulan lalu',  'up' => true,  'color' => '#F59E0B', 'icon' => 'play'],
                    ['label' => 'Project Selesai',  'value' => '4',  'sub' => '+1 dari bulan lalu',  'up' => true,  'color' => '#16A34A', 'icon' => 'check'],
                    ['label' => 'Menunggu Review',  'value' => '3',  'sub' => '-1 dari bulan lalu',  'up' => false, 'color' => '#DC2626', 'icon' => 'clock'],
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
                                @elseif($stat['icon'] === 'play')
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                                @elseif($stat['icon'] === 'check')
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                @elseif($stat['icon'] === 'clock')
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                @endif
                            </span>
                        </div>
                        @if($stat['up'] === true)
                            <span class="text-[10px] font-semibold text-green-600 bg-green-50 px-1.5 py-0.5 rounded">↑</span>
                        @elseif($stat['up'] === false)
                            <span class="text-[10px] font-semibold text-red-500 bg-red-50 px-1.5 py-0.5 rounded">↓</span>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-[#1E293B] leading-none">{{ $stat['value'] }}</p>
                    <p class="text-xs text-slate-400 mt-1.5 font-medium">{{ $stat['label'] }}</p>
                    <p class="text-[11px] mt-1" style="color: {{ $stat['color'] }}">{{ $stat['sub'] }}</p>
                </div>
                @endforeach
            </div>

            {{-- ── TUGAS REVIEW ── --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div>
                            <p class="font-semibold text-[#1E293B] text-[15px]">Tugas Review</p>
                            <p class="text-xs text-slate-400 mt-0.5">Item yang membutuhkan tindakan segera</p>
                        </div>
                        <span class="bg-[#FFB800] text-[#1E293B] text-[10px] font-bold px-2 py-0.5 rounded-full">4</span>
                    </div>
                    <a href="#" class="text-xs font-semibold text-[#0B3B82] hover:underline flex items-center gap-1">
                        Lihat Semua
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>

                @php
                $reviews = [
                    [
                        'icon'    => 'doc',
                        'title'   => 'Proposal Smart Farming IoT',
                        'sub'     => 'CV. Tani Maju',
                        'time'    => '2 jam lalu',
                        'badge'   => 'Review',
                        'bcolor'  => 'bg-amber-50 text-amber-700',
                    ],
                    [
                        'icon'    => 'doc',
                        'title'   => 'Laporan Akhir Website UMKM',
                        'sub'     => 'PT. ABC Indonesia',
                        'time'    => '1 hari lalu',
                        'badge'   => 'Tinjau',
                        'bcolor'  => 'bg-blue-50 text-blue-700',
                    ],
                    [
                        'icon'    => 'alert',
                        'title'   => 'Revisi Proposal IoT Monitoring',
                        'sub'     => 'CV. Tani Maju',
                        'time'    => '1 hari lalu',
                        'badge'   => 'Review',
                        'bcolor'  => 'bg-amber-50 text-amber-700',
                    ],
                    [
                        'icon'    => 'doc',
                        'title'   => 'Progress Report Sprint 3 — Inventori',
                        'sub'     => 'Dinas Pendidikan',
                        'time'    => '2 hari lalu',
                        'badge'   => 'Tinjau',
                        'bcolor'  => 'bg-blue-50 text-blue-700',
                    ],
                ];
                @endphp

                <div class="divide-y divide-slate-50">
                    @foreach($reviews as $r)
                    <div class="flex items-center gap-4 px-6 py-3.5 hover:bg-slate-50/60 transition-colors">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                                    {{ $r['icon'] === 'alert' ? 'bg-red-50' : 'bg-[#EAF3FF]' }}">
                            @if($r['icon'] === 'alert')
                                <svg class="w-3.5 h-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                            @else
                                <svg class="w-3.5 h-3.5 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-semibold text-[#1E293B] truncate">{{ $r['title'] }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $r['sub'] }} · {{ $r['time'] }}</p>
                        </div>
                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full flex-shrink-0 {{ $r['bcolor'] }}">
                            {{ $r['badge'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ── BOTTOM GRID: Table (col-2) + Distribusi (col-1) ── --}}
            <div class="grid grid-cols-3 gap-6">

                {{-- Project yang Saya Kelola (col-span-2) --}}
                <div class="col-span-2 bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-[#1E293B] text-[15px]">Project yang Saya Kelola</p>
                            <p class="text-xs text-slate-400 mt-0.5">Semua project yang ditugaskan ke Anda</p>
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
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Status</th>
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3 w-32">Progress</th>
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Deadline</th>
                                    <th class="px-3 py-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @php
                                $projects = [
                                    ['name' => 'Sistem Informasi UMKM',      'desc' => 'Pengembangan sistem informasi...', 'client' => 'PT. ABC Indonesia',  'status' => 'Berjalan', 'progress' => 75,  'deadline' => '12 Okt 2026'],
                                    ['name' => 'Smart Farming IoT',          'desc' => 'Implementasi IoT untuk pertanian...','client' => 'CV. Tani Maju',     'status' => 'Review',   'progress' => 60,  'deadline' => '5 Okt 2026'],
                                    ['name' => 'Website Company Profile',    'desc' => 'Pembuatan website profil...',        'client' => 'PT. DEF Solusi',    'status' => 'Selesai',  'progress' => 100, 'deadline' => '—'],
                                    ['name' => 'Aplikasi Manajemen Inventori','desc' => 'Sistem inventori berbasis web...', 'client' => 'Dinas Pendidikan',  'status' => 'Berjalan', 'progress' => 40,  'deadline' => '18 Okt 2026'],
                                    ['name' => 'IoT Monitoring Pertanian',   'desc' => 'Monitoring lingkungan pertanian...','client' => 'CV. Tani Maju',     'status' => 'Proposal', 'progress' => 20,  'deadline' => '25 Okt 2026'],
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
                                        <p class="font-semibold text-[#1E293B] text-[13px] leading-tight">{{ $project['name'] }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $project['desc'] }}</p>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <span class="text-[12px] text-slate-600">{{ $project['client'] }}</span>
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

                {{-- Right col: Donut + Status stacked -- sama persis dengan pengurus --}}
                <div class="flex flex-col gap-6">

                    {{-- Distribusi Kategori --}}
                    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
                        <div class="mb-4">
                            <p class="font-semibold text-[#1E293B] text-[15px]">Distribusi Kategori</p>
                            <p class="text-xs text-slate-400 mt-0.5">Project per kategori</p>
                        </div>

                        <div class="flex items-center justify-center" x-data="{
                            segments: [
                                { label: 'Website',           pct: 40,   color: '#0B3B82' },
                                { label: 'IoT & Embedded',    pct: 26.7, color: '#FFB800' },
                                { label: 'Aplikasi',          pct: 20,   color: '#6366F1' },
                                { label: 'Pengembangan Sis.', pct: 13.3, color: '#16A34A' },
                                { label: 'Lainnya',           pct: 0,    color: '#94A3B8' },
                            ],
                            total: 15,
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
                                    <p class="text-2xl font-bold text-[#1E293B]">15</p>
                                    <p class="text-[10px] text-slate-400 font-medium">Project</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2">
                            @php
                            $donut = [
                                ['label' => 'Website',            'count' => 6, 'color' => '#0B3B82'],
                                ['label' => 'IoT & Embedded',     'count' => 4, 'color' => '#FFB800'],
                                ['label' => 'Aplikasi',           'count' => 3, 'color' => '#6366F1'],
                                ['label' => 'Pengembangan Sis.',  'count' => 2, 'color' => '#16A34A'],
                                ['label' => 'Lainnya',            'count' => 0, 'color' => '#94A3B8'],
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
                            ['label' => 'Proposal', 'count' => 2,  'color' => '#6366F1', 'total' => 15],
                            ['label' => 'Review',   'count' => 3,  'color' => '#F59E0B', 'total' => 15],
                            ['label' => 'Berjalan', 'count' => 9,  'color' => '#0B3B82', 'total' => 15],
                            ['label' => 'Selesai',  'count' => 4,  'color' => '#16A34A', 'total' => 15],
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
                                    <div class="h-full rounded-full"
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