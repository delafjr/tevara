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

    $dash = $dashboard ?? [];
    $totalProjects   = (int) ($dash['total_projects'] ?? 0);
    $runningProjects = (int) ($dash['running_projects'] ?? 0);
    $totalClients    = (int) ($dash['total_clients'] ?? 0);
    $totalStudents   = (int) ($dash['total_students'] ?? 0);
    $projectsList    = is_array($dash['projects'] ?? null) ? $dash['projects'] : [];
    $projectCount    = count($projectsList);

    // Label & warna kategori.
    $categoryLabels = [
        'web' => 'Website', 'website' => 'Website',
        'iot' => 'IoT', 'embedded' => 'IoT',
        'app' => 'Aplikasi', 'apps' => 'Aplikasi', 'aplikasi' => 'Aplikasi', 'mobile' => 'Aplikasi',
        'sis' => 'Sistem Informasi', 'sistem' => 'Sistem Informasi', 'software' => 'Sistem Informasi', 'system' => 'Sistem Informasi',
        'design' => 'Desain & Kreatif', 'desain' => 'Desain & Kreatif',
        'other' => 'Lainnya', 'lainnya' => 'Lainnya',
    ];
    $categoryColors = [
        'web' => '#0B3B82', 'website' => '#0B3B82',
        'iot' => '#FFB800', 'embedded' => '#FFB800',
        'app' => '#6366F1', 'apps' => '#6366F1', 'aplikasi' => '#6366F1', 'mobile' => '#6366F1',
        'sis' => '#16A34A', 'sistem' => '#16A34A', 'software' => '#16A34A', 'system' => '#16A34A',
        'design' => '#EC4899', 'desain' => '#EC4899',
        'other' => '#94A3B8', 'lainnya' => '#94A3B8',
    ];
    $extraCategoryColors = ['#0EA5E9', '#8B5CF6', '#F97316', '#14B8A6'];
    $categoryLabel = fn (string $key): string => $categoryLabels[strtolower($key)] ?? ucfirst(str_replace('_', ' ', $key));

    // category_distribution dari API berbentuk [{"web": 2}, ...] → ratakan lalu jumlahkan per kategori.
    $categoryCounts = [];
    foreach (($dash['category_distribution'] ?? []) as $entry) {
        if (is_array($entry)) {
            foreach ($entry as $key => $count) {
                $key = strtolower((string) $key);
                $categoryCounts[$key] = ($categoryCounts[$key] ?? 0) + (int) $count;
            }
        }
    }

    $categoryChart = [];
    foreach ($categoryCounts as $key => $count) {
        $categoryChart[] = [
            'label' => $categoryLabel($key),
            'color' => $categoryColors[$key] ?? $extraCategoryColors[count($categoryChart) % count($extraCategoryColors)],
            'count' => $count,
        ];
    }

    $categoryTotal = array_sum(array_column($categoryChart, 'count'));
    $donutSegments = [];
    $donutOffset = 0;
    if ($categoryTotal > 0) {
        foreach ($categoryChart as $category) {
            $length = ($category['count'] / $categoryTotal) * 251.2;
            $donutSegments[] = [
                'color'     => $category['color'],
                'dasharray' => round($length, 2) . ' ' . round(251.2 - $length, 2),
                'offset'    => -round($donutOffset, 2),
            ];
            $donutOffset += $length;
        }
    }

    // status_distribution dari API berbentuk [{"active": 2}, ...].
    $statusCounts = [];
    foreach (($dash['status_distribution'] ?? []) as $entry) {
        if (is_array($entry)) {
            foreach ($entry as $key => $count) {
                $key = strtolower((string) $key);
                $statusCounts[$key] = ($statusCounts[$key] ?? 0) + (int) $count;
            }
        }
    }

    $statusColors = [
        'active' => '#0B3B82',
        'testing' => '#F59E0B',
        'completed' => '#16A34A',
        'handover' => '#10B981',
        'rejected' => '#DC2626',
        'cancelled' => '#DC2626',
        'proposal' => '#6366F1',
        'review' => '#F59E0B',
    ];

    $statusChart = [];
    foreach ($statusCounts as $key => $count) {
        $statusChart[] = [
            'label' => ucfirst($key),
            'color' => $statusColors[$key] ?? '#94A3B8',
            'count' => $count,
        ];
    }
    $statusTotal = array_sum(array_column($statusChart, 'count')) ?: 1;

    // Warna pill status pada tabel.
    $statusStyle = function (string $status): string {
        $s = strtolower($status);

        if (str_contains($s, 'reject') || str_contains($s, 'cancel')) {
            return 'bg-red-50 text-red-700';
        }
        if (str_contains($s, 'testing') || str_contains($s, 'review')) {
            return 'bg-amber-50 text-amber-700';
        }
        if (str_contains($s, 'complete') || str_contains($s, 'handover')) {
            return 'bg-green-50 text-green-700';
        }
        if (str_contains($s, 'active') || str_contains($s, 'progress')) {
            return 'bg-blue-50 text-blue-700';
        }

        return 'bg-slate-100 text-slate-600';
    };

    $progressColor = fn ($p) => $p >= 100 ? '#16A34A' : ($p >= 60 ? '#0B3B82' : ($p >= 30 ? '#F59E0B' : '#DC2626'));
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
                // Urutan kartu mengikuti urutan field pada BE: total_projects, running_projects, total_clients, total_students.
                $stats = [
                    ['label' => 'Total Project',       'value' => $totalProjects,   'sub' => 'Seluruh project',     'color' => '#0B3B82', 'icon' => 'folder'],
                    ['label' => 'Project Berjalan',    'value' => $runningProjects, 'sub' => 'Sedang berjalan',     'color' => '#0B3B82', 'icon' => 'play'],
                    ['label' => 'Total Klien / Mitra', 'value' => $totalClients,    'sub' => 'Terdaftar',           'color' => '#0B3B82', 'icon' => 'users'],
                    ['label' => 'Mahasiswa Terlibat',  'value' => $totalStudents,   'sub' => 'Aktif berkontribusi', 'color' => '#0B3B82', 'icon' => 'academic'],
                ];
                @endphp

                @foreach($stats as $stat)
                <x-stat-card :label="$stat['label']" :value="$stat['value']" :sub="$stat['sub']" :color="$stat['color']" :icon="$stat['icon']" />
                @endforeach
            </div>

            {{-- ── ACTIONS BAR ── --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('pengurus-tefa.validasi-proposal') }}"
                       class="inline-flex items-center gap-2 bg-[#0B3B82] text-white rounded-xl px-5 py-2.5 font-semibold text-sm hover:bg-[#082a5c] transition-all shadow-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                        </svg>
                        Validasi Proposal
                    </a>
                    <a href="{{ route('pengurus-tefa.projek') }}"
                       class="inline-flex items-center gap-2 bg-white border border-slate-200 text-[#1E293B] rounded-xl px-5 py-2.5 font-semibold text-sm hover:border-[#0B3B82] hover:text-[#0B3B82] transition-all shadow-xs">
                        <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Tambah Project
                    </a>
                </div>

                <div class="hidden sm:flex items-center gap-2 text-xs text-[#64748B]">
                    <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                    <span>Sistem aktif · Monitoring operasional TeFa</span>
                </div>
            </div>

            {{-- ── BOTTOM GRID: Table (col-2) + 2 Charts (col-1 stacked) ── --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                {{-- Project Terbaru (col-span-2) --}}
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-6 py-4.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="font-bold text-[#1E293B] text-[15px]">Project Terbaru</h2>
                                @if($projectCount > 0)
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-[#EAF3FF] text-[#0B3B82]">{{ $projectCount }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-[#64748B] mt-0.5">Semua project yang terdaftar di sistem TeFa</p>
                        </div>

                        <div class="flex items-center gap-3">
                            {{-- Search bar projek --}}
                            <div class="relative w-full sm:w-64">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                                    </svg>
                                </span>
                                <input id="tefa-project-search" type="text" placeholder="Cari nama project..."
                                       class="w-full bg-[#F8FAFC] border border-slate-200 rounded-xl pl-9 pr-3.5 py-1.5 text-xs text-[#1E293B] placeholder:text-[#64748B] focus:bg-white focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition shadow-2xs">
                            </div>

                            <a href="{{ route('pengurus-tefa.projek') }}" class="text-xs font-semibold text-[#0B3B82] hover:text-[#082a5c] inline-flex items-center gap-1 transition flex-shrink-0">
                                Lihat Semua
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="bg-[#F8FAFC] border-b border-slate-100 text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">
                                    <th class="px-6 py-3.5">Nama Project</th>
                                    <th class="px-3 py-3.5">Klien / Mitra</th>
                                    <th class="px-3 py-3.5">PM / Dosen</th>
                                    <th class="px-3 py-3.5">Status</th>
                                    <th class="px-3 py-3.5 w-32">Progress</th>
                                    <th class="px-3 py-3.5">Deadline</th>
                                    <th class="px-3 py-3.5 w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="tefa-project-tbody" class="divide-y divide-slate-50">
                                @forelse($projectsList as $project)
                                @php
                                    $projectStatus = (string) ($project['status'] ?? '-');
                                    $projectProgress = max(0, min(100, (int) ($project['progress'] ?? 0)));
                                    $projectCategory = $categoryLabel((string) ($project['category'] ?? ''));
                                    $projectDeadline = !empty($project['deadline'])
                                        ? \Carbon\Carbon::parse($project['deadline'])->translatedFormat('d M Y')
                                        : '—';
                                    $searchData = strtolower(($project['title'] ?? '') . ' ' . ($projectCategory ?? '') . ' ' . ($project['client_name'] ?? '') . ' ' . ($project['pm_name'] ?? ''));
                                @endphp
                                <tr class="tefa-project-row hover:bg-slate-50/70 transition-colors" data-search="{{ $searchData }}">
                                    <td class="px-6 py-3.5">
                                        <div>
                                            <p class="font-semibold text-[#1E293B] text-[13px] leading-tight">{{ $project['title'] ?? '-' }}</p>
                                            <p class="text-[11px] text-[#64748B] mt-0.5">{{ $projectCategory }}</p>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <span class="text-[12px] text-slate-600 font-medium">{{ $project['client_name'] ?? '-' }}</span>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <span class="text-[12px] text-slate-600 font-medium">{{ $project['pm_name'] ?? '—' }}</span>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $statusStyle($projectStatus) }}">
                                            {{ $projectStatus }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 w-32">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-300"
                                                     style="width: {{ $projectProgress }}%; background-color: {{ $progressColor($projectProgress) }}">
                                                </div>
                                            </div>
                                            <span class="text-[11px] font-semibold text-slate-500 w-7 text-right">{{ $projectProgress }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <span class="text-[12px] text-slate-500 font-medium">{{ $projectDeadline }}</span>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <button class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors cursor-pointer" title="Opsi project">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm0 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm0 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z"/></svg>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr id="empty-state-row">
                                    <td colspan="7" class="px-6 py-14 text-center">
                                        <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                            <div class="w-12 h-12 rounded-2xl bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center mb-3 shadow-2xs">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-bold text-[#1E293B]">Belum ada project terdaftar</p>
                                            <p class="text-xs text-[#64748B] mt-1 text-center leading-relaxed">Project yang diajukan dan disetujui akan tampil di tabel ini.</p>
                                            <div class="mt-4 flex items-center gap-2">
                                                <a href="{{ route('pengurus-tefa.projek') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#0B3B82] text-white text-xs font-semibold hover:bg-[#082a5c] transition shadow-xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                    Tambah Project
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                                <tr id="no-search-match-row" class="hidden">
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2.5">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                                            </div>
                                            <p class="text-sm font-bold text-[#1E293B]">Project tidak ditemukan</p>
                                            <p class="text-xs text-[#64748B] mt-1">Tidak ada nama project atau pihak terkait yang sesuai dengan kata kunci.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Right col: Donut + Status stacked --}}
                <div class="flex flex-col gap-6">

                    {{-- Distribusi Kategori --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col">
                        <div class="mb-4">
                            <h3 class="font-bold text-[#1E293B] text-[15px]">Distribusi Kategori</h3>
                            <p class="text-xs text-[#64748B] mt-0.5">Project per kategori</p>
                        </div>

                        <div class="flex items-center justify-center my-1">
                            <div class="relative w-36 h-36">
                                <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="#F1F5F9" stroke-width="16"/>
                                    @foreach($donutSegments as $segment)
                                        <circle cx="50" cy="50" r="40" fill="none"
                                            stroke="{{ $segment['color'] }}" stroke-width="16"
                                            stroke-dasharray="{{ $segment['dasharray'] }}"
                                            stroke-dashoffset="{{ $segment['offset'] }}"/>
                                    @endforeach
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <p class="text-2xl font-bold text-[#0B3B82] leading-none">{{ $totalProjects }}</p>
                                    <p class="text-[10px] text-[#64748B] font-semibold uppercase tracking-wider mt-1">Project</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2.5">
                            @forelse($categoryChart as $d)
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-sm flex-shrink-0" style="background-color: {{ $d['color'] }}"></span>
                                    <span class="text-slate-600 font-medium">{{ $d['label'] }}</span>
                                </div>
                                <span class="font-semibold text-[#1E293B]">{{ $d['count'] }}</span>
                            </div>
                            @empty
                            <div class="py-3 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-50 text-slate-400 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                    Belum ada data kategori
                                </span>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Status Project --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        <div class="mb-4">
                            <h3 class="font-bold text-[#1E293B] text-[15px]">Status Project</h3>
                            <p class="text-xs text-[#64748B] mt-0.5">Distribusi berdasarkan status</p>
                        </div>

                        <div class="space-y-3.5">
                            @forelse($statusChart as $s)
                            <div>
                                <div class="flex items-center justify-between mb-1.5 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $s['color'] }}"></span>
                                        <span class="text-slate-600 font-medium">{{ $s['label'] }}</span>
                                    </div>
                                    <span class="font-bold text-[#1E293B]">{{ $s['count'] }}</span>
                                </div>
                                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500"
                                         style="width: {{ round($s['count'] / $statusTotal * 100) }}%; background-color: {{ $s['color'] }}">
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="py-3 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-50 text-slate-400 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                    Belum ada data status
                                </span>
                            </div>
                            @endforelse
                        </div>
                    </div>

                </div>{{-- /right col --}}
            </div>{{-- /bottom grid --}}

            {{-- Script pencarian live nama project --}}
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const searchInput = document.getElementById('tefa-project-search');
                    if (!searchInput) return;

                    const rows = document.querySelectorAll('tr.tefa-project-row');
                    const noMatchRow = document.getElementById('no-search-match-row');

                    searchInput.addEventListener('input', function () {
                        const query = this.value.toLowerCase().trim();
                        let visibleCount = 0;

                        rows.forEach(function (row) {
                            const searchData = (row.getAttribute('data-search') || row.innerText).toLowerCase();
                            if (searchData.includes(query)) {
                                row.classList.remove('hidden');
                                visibleCount++;
                            } else {
                                row.classList.add('hidden');
                            }
                        });

                        if (noMatchRow) {
                            if (rows.length > 0 && visibleCount === 0 && query !== '') {
                                noMatchRow.classList.remove('hidden');
                            } else {
                                noMatchRow.classList.add('hidden');
                            }
                        }
                    });
                });
            </script>

        </main>

        <footer class="px-8 py-4 border-t border-slate-100 bg-white">
            <p class="text-[11px] text-slate-400">© {{ date('Y') }} TEVARA · Politeknik Negeri Semarang</p>
        </footer>
    </div>
</div>

</body>
</html>