<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Jurusan - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#F8F9FB] font-['Inter'] text-[#1E293B] antialiased">

@php
    $user = $user ?? session('user');
    $userName = $user['name'] ?? 'Jurusan';

    $dash = $dashboard ?? [];
    $totalProjects     = (int) ($dash['total_projects'] ?? 0);
    $runningProjects   = (int) ($dash['running_projects'] ?? 0);
    $inReviewProjects  = (int) ($dash['in_review_projects'] ?? 0);
    $submittedProposals= (int) ($dash['submitted_proposals'] ?? 0);
    $projectsList      = is_array($dash['projects'] ?? null) ? $dash['projects'] : [];
    $projectCount      = count($projectsList);

    // Label & warna kategori
    $categoryLabels = [
        'web' => 'Website', 'website' => 'Website',
        'iot' => 'IoT & Emb.', 'embedded' => 'IoT & Emb.',
        'app' => 'Aplikasi', 'apps' => 'Aplikasi', 'aplikasi' => 'Aplikasi', 'mobile' => 'Aplikasi',
        'sis' => 'Sistem Informasi', 'sistem' => 'Sistem Informasi', 'software' => 'Sistem Informasi', 'system' => 'Sistem Informasi',
        'other' => 'Lainnya', 'lainnya' => 'Lainnya',
    ];
    $categoryColors = [
        'web' => '#0B3B82', 'website' => '#0B3B82',
        'iot' => '#F59E0B', 'embedded' => '#F59E0B',
        'app' => '#3B82F6', 'apps' => '#3B82F6', 'aplikasi' => '#3B82F6', 'mobile' => '#3B82F6',
        'sis' => '#CBD5E1', 'sistem' => '#CBD5E1', 'software' => '#CBD5E1', 'system' => '#CBD5E1',
        'other' => '#94A3B8', 'lainnya' => '#94A3B8',
    ];

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
            'label' => $categoryLabels[$key] ?? ucfirst($key),
            'color' => $categoryColors[$key] ?? '#94A3B8',
            'count' => (int) $count,
        ];
    }
    $categoryTotal = array_sum(array_column($categoryChart, 'count'));
@endphp

<div class="flex min-h-screen">

    {{-- Reusable Sidebar Component --}}
    <x-sidebar active="dashboard" />

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen p-8 gap-6">

        {{-- HEADER / HERO SECTION --}}
        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
            {{-- gradient dekoratif --}}
            <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                    Workspace Jurusan
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

        {{-- NOTIFIKASI & AKTIVITAS HIGHLIGHT --}}
        <x-dashboard-notifications :notifications="$notifications ?? []" :unread-count="$unreadCount ?? 0" />

        {{-- STAT CARDS --}}
        @php
            $jurusanStats = [
                ['label' => 'Total Projek',         'value' => $totalProjects,      'sub' => 'Inisiatif Terdaftar', 'color' => '#0B3B82', 'icon' => 'folder'],
                ['label' => 'Projek Berjalan',      'value' => $runningProjects,    'sub' => 'Dalam Pengerjaan',    'color' => '#0B3B82', 'icon' => 'play'],
                ['label' => 'Dalam Review',         'value' => $inReviewProjects,   'sub' => 'Perlu Review',        'color' => '#F59E0B', 'icon' => 'clock-file'],
                ['label' => 'Proposal Masuk',       'value' => $submittedProposals, 'sub' => 'Menunggu Validasi',   'color' => '#0B3B82', 'icon' => 'check'],
            ];
        @endphp

        <div class="grid grid-cols-4 gap-4">
            @foreach($jurusanStats as $stat)
                <x-stat-card :label="$stat['label']" :value="$stat['value']" :sub="$stat['sub']" :color="$stat['color']" :icon="$stat['icon']" />
            @endforeach
        </div>

        {{-- ACTIONS BAR --}}
        <div class="flex items-center justify-between" x-data="{ search: '' }">
            <div class="flex items-center gap-4">
                <a href="{{ route('jurusan.ajukan-proyek') }}" class="bg-[#0B3B82] hover:bg-[#082a5c] text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Ajukan Projek Baru
                </a>
                <a href="{{ route('jurusan.projek-saya') }}" class="bg-white hover:bg-slate-50 text-[#1E293B] border border-slate-200 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    Lihat Semua Projek
                </a>
            </div>
        </div>

        {{-- BOTTOM SECTION (Table & Chart) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-8">
            
            {{-- Table Projek Saya --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-[#1E293B]">Daftar Projek</h3>
                        <p class="text-sm text-slate-500 mt-1">Seluruh projek Teaching Factory aktif di database</p>
                    </div>
                    <a href="{{ route('jurusan.projek-saya') }}" class="text-[#0B3B82] text-sm font-semibold hover:underline flex items-center gap-1">
                        Lihat Semua 
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-500 font-semibold bg-slate-50/50 uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="px-6 py-4">Nama Project</th>
                                <th scope="col" class="px-6 py-4">Klien / Mitra</th>
                                <th scope="col" class="px-6 py-4">Status</th>
                                <th scope="col" class="px-6 py-4">Progress</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($projectsList as $p)
                            @php
                                $statusKey = strtolower($p['status'] ?? 'other');
                                $prog = (int) ($p['progress'] ?? 0);
                                $badgeClass = match($statusKey) {
                                    'active', 'berjalan' => 'bg-blue-50 text-[#0B3B82]',
                                    'testing'            => 'bg-purple-50 text-purple-700',
                                    'review'             => 'bg-amber-50 text-amber-700',
                                    'handover', 'completed', 'selesai' => 'bg-green-50 text-green-700',
                                    default              => 'bg-slate-100 text-slate-600',
                                };
                                $barColor = $prog >= 100 ? '#16A34A' : ($prog >= 60 ? '#0B3B82' : ($prog >= 30 ? '#F59E0B' : '#DC2626'));
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-4 font-semibold text-[#1E293B]">
                                    <p class="leading-tight">{{ $p['title'] }}</p>
                                    <p class="text-xs text-slate-400 capitalize mt-0.5">{{ $p['category'] ?? 'Lainnya' }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ $p['client_name'] ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <span class="{{ $badgeClass }} text-xs font-semibold px-3 py-1 rounded-full">{{ $p['status'] }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-full bg-slate-100 rounded-full h-1.5 flex-1">
                                            <div class="h-1.5 rounded-full" style="width: {{ $prog }}%; background-color: {{ $barColor }}"></div>
                                        </div>
                                        <span class="text-sm font-medium text-slate-600 w-8">{{ $prog }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-400 text-xs">
                                    Belum ada projek aktif yang berjalan di database.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-100 flex items-center justify-between text-sm text-slate-500">
                    <p>Menampilkan {{ count($projectsList) }} projek</p>
                </div>
            </div>

            {{-- Chart Kategori --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col">
                <div class="flex items-start justify-between mb-8">
                    <div>
                        <h3 class="text-lg font-bold text-[#1E293B]">Distribusi Kategori</h3>
                        <p class="text-sm text-slate-500 mt-1">Klasifikasi seluruh portofolio</p>
                    </div>
                </div>
                
                <div class="flex-1 flex flex-col items-center justify-center">
                    <div class="relative w-48 h-48 rounded-full flex items-center justify-center bg-slate-100">
                        <div class="absolute w-32 h-32 bg-white rounded-full flex flex-col items-center justify-center shadow-xs">
                            <span class="text-3xl font-bold text-[#1E293B]">{{ $categoryTotal }}</span>
                            <span class="text-[10px] font-semibold tracking-widest text-slate-500 uppercase mt-1">Project</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-y-4 gap-x-2 mt-8 text-sm">
                    @forelse($categoryChart as $cat)
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $cat['color'] }}"></span>
                        <span class="text-slate-600 truncate">{{ $cat['label'] }} <span class="font-semibold text-[#1E293B]">({{ $cat['count'] }})</span></span>
                    </div>
                    @empty
                    <div class="col-span-2 text-center text-xs text-slate-400">Belum ada kategori terdaftar</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>

</body>
</html>
