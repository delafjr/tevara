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

    // Label & warna kategori dinamis dari category_distribution backend (DB).
    $categoryLabels = [
        'web' => 'Website', 'website' => 'Website',
        'iot' => 'IoT & Embedded', 'embedded' => 'IoT & Embedded',
        'app' => 'Aplikasi', 'apps' => 'Aplikasi', 'aplikasi' => 'Aplikasi', 'mobile' => 'Aplikasi',
        'desktop' => 'Desktop',
        'sis' => 'Pengembangan Sis.', 'sistem' => 'Pengembangan Sis.', 'software' => 'Pengembangan Sis.', 'system' => 'Pengembangan Sis.',
        'other' => 'Lainnya', 'lainnya' => 'Lainnya',
    ];
    $categoryColors = [
        'web' => '#0B3B82', 'website' => '#0B3B82',
        'iot' => '#F59E0B', 'embedded' => '#F59E0B',
        'app' => '#2563EB', 'apps' => '#2563EB', 'aplikasi' => '#2563EB', 'mobile' => '#2563EB',
        'desktop' => '#0EA5E9',
        'sis' => '#16A34A', 'sistem' => '#16A34A', 'software' => '#16A34A', 'system' => '#16A34A',
        'other' => '#CBD5E1', 'lainnya' => '#CBD5E1',
    ];
    $extraCategoryColors = ['#8B5CF6', '#EC4899', '#F97316', '#14B8A6'];

    $categoryCounts = [];
    foreach (($dash['category_distribution'] ?? []) as $entry) {
        if (is_array($entry)) {
            foreach ($entry as $key => $count) {
                $key = strtolower(trim((string) $key));
                $categoryCounts[$key] = ($categoryCounts[$key] ?? 0) + (int) $count;
            }
        }
    }

    $categoryChart = [];
    foreach ($categoryCounts as $key => $count) {
        if ($count <= 0) {
            continue;
        }
        $categoryChart[] = [
            'label' => $categoryLabels[$key] ?? ucfirst(str_replace('_', ' ', $key)),
            'color' => $categoryColors[$key] ?? $extraCategoryColors[count($categoryChart) % count($extraCategoryColors)],
            'count' => (int) $count,
        ];
    }

    // Segmen donut chart (r = 40 → keliling 251.33).
    $donutCircumference = 251.33;
    $categoryTotal = array_sum(array_column($categoryChart, 'count'));
    $donutSegments = [];
    $donutOffset = 0;
    if ($categoryTotal > 0) {
        foreach ($categoryChart as $segment) {
            $length = ($segment['count'] / $categoryTotal) * $donutCircumference;
            $donutSegments[] = [
                'color'     => $segment['color'],
                'dasharray' => round($length, 2) . ' ' . round($donutCircumference - $length, 2),
                'offset'    => -round($donutOffset, 2),
            ];
            $donutOffset += $length;
        }
    }
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
                ['label' => 'Projek Berjalan',      'value' => $runningProjects,    'sub' => 'Dalam Pengerjaan',    'color' => '#16A34A', 'icon' => 'play'],
                ['label' => 'Dalam Review',         'value' => $inReviewProjects,   'sub' => 'Perlu Review',        'color' => '#F59E0B', 'icon' => 'clock-file'],
                ['label' => 'Proposal Masuk',       'value' => $submittedProposals, 'sub' => 'Menunggu Validasi',   'color' => '#6366F1', 'icon' => 'check'],
            ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-stretch">
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
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-6 self-start">
                <div>
                    <h2 class="font-bold text-[#1E293B] text-[15px]">Distribusi Kategori</h2>
                    <p class="text-xs text-[#2563EB] font-medium mt-0.5">Klasifikasi Seluruh Portofolio</p>
                </div>

                {{-- Donut Chart SVG --}}
                <div class="flex items-center justify-center my-5">
                    <div class="relative w-36 h-36">
                        <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                            <!-- Background circle -->
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#F8FAFC" stroke-width="14" />

                            @foreach($donutSegments as $segment)
                                <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $segment['color'] }}" stroke-width="14"
                                    stroke-dasharray="{{ $segment['dasharray'] }}" stroke-dashoffset="{{ $segment['offset'] }}" />
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-bold text-[#0B254A] leading-none">{{ $totalProjects }}</span>
                            <span class="text-[9px] font-bold text-slate-400 tracking-wider mt-1">PROJECT</span>
                        </div>
                    </div>
                </div>

                {{-- Legend List --}}
                <div class="space-y-2.5">
                    @forelse($categoryChart as $category)
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: {{ $category['color'] }}"></span>
                                <span class="text-slate-600 font-medium">{{ $category['label'] }}</span>
                            </div>
                            <span class="font-bold text-[#1E293B]">{{ $category['count'] }}</span>
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

        </div>

    </div>
</div>

</body>
</html>
