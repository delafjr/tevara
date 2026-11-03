{{-- == ( struktur dasar halaman ) == --}}
<!DOCTYPE html>
<html lang="id">
<head>
{{-- == ( head : meta + judul halaman ) == --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Klien - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen">

@php
    $user = $user ?? session('user');
    $userName = $user['name'] ?? 'Klien';

    $dash = $dashboard ?? [];
    $totalProjects     = (int) ($dash['total_projects'] ?? 0);
    $activeProjects    = (int) ($dash['active_projects'] ?? 0);
    $completedProjects = (int) ($dash['completed_projects'] ?? 0);
    $waitingReview     = (int) ($dash['waiting_review'] ?? 0);
    $projects          = is_array($dash['projects'] ?? null) ? $dash['projects'] : [];
    $projectCount      = count($projects);

    // Warna pill status & progress bar konsisten dengan sistem Tevara
    $getStatusBadge = function (string $status): array {
        $s = strtolower($status);
        // default colors and label
        $pill = 'bg-slate-100 text-slate-600';
        $bar = 'bg-[#0B3B82]';
        $label = ucfirst($status);

        if (str_contains($s, 'revision') || str_contains($s, 'improvement')) {
            $pill = 'bg-[#FEF3C7] text-amber-800 border border-amber-300 font-bold';
            $bar = 'bg-amber-500';
            $label = 'Improvement (Revisi)';
        } elseif (str_contains($s, 'reject') || str_contains($s, 'cancel') || str_contains($s, 'batal') || str_contains($s, 'tolak')) {
            $pill = 'bg-[#FFE4E6] text-[#E11D48]';
            $bar = 'bg-[#E11D48]';
            $label = 'Ditolak';
        } elseif (str_contains($s, 'review') || str_contains($s, 'pending') || str_contains($s, 'menunggu') || str_contains($s, 'draft') || str_contains($s, 'proposal')) {
            $pill = 'bg-[#FEF3C7] text-[#D97706]';
            $bar = 'bg-[#D97706]';
            $label = 'Review';
        } elseif (str_contains($s, 'complete') || str_contains($s, 'selesai') || $s === 'done') {
            $pill = 'bg-[#DCFCE7] text-[#16A34A]';
            $bar = 'bg-[#16A34A]';
            $label = 'Selesai';
        } elseif (str_contains($s, 'active') || str_contains($s, 'progress') || str_contains($s, 'berjalan')) {
            $pill = 'bg-[#EFF6FF] text-[#0B3B82]';
            $bar = 'bg-[#0B3B82]';
            $label = 'Berjalan';
        } elseif (str_contains($s, 'draft')) {
            $pill = 'bg-slate-100 text-slate-600';
            $bar = 'bg-[#0B3B82]';
            $label = 'Draft';
        } elseif (str_contains($s, 'testing') || str_contains($s, 'uji')) {
            $pill = 'bg-[#FEF3C7] text-[#D97706]';
            $bar = 'bg-[#D97706]';
            $label = 'Pengujian';
        } elseif (str_contains($s, 'handover') || str_contains($s, 'serah')) {
            $pill = 'bg-[#DCFCE7] text-[#16A34A]';
            $bar = 'bg-[#16A34A]';
            $label = 'Serah Terima';
        }

        return [
            'label' => $label,
            'pill' => $pill,
            'bar' => $bar,
        ];
    };

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

    $categoryDefaults = [
        ['label' => 'Website',            'color' => '#0B3B82'],
        ['label' => 'IoT & Embedded',     'color' => '#F59E0B'],
        ['label' => 'Aplikasi',           'color' => '#2563EB'],
        ['label' => 'Pengembangan Sis.',  'color' => '#94A3B8'],
    ];
    $categoryKeyMap = [
        'web' => 0, 'website' => 0,
        'iot' => 1, 'embedded' => 1,
        'app' => 2, 'apps' => 2, 'aplikasi' => 2, 'mobile' => 2,
        'sis' => 3, 'sistem' => 3, 'software' => 3, 'system' => 3,
    ];

    $categoryValues = [0, 0, 0, 0];
    $categoryOther = 0;
    foreach ($categoryCounts as $key => $count) {
        $idx = $categoryKeyMap[$key] ?? null;
        if ($idx === null) {
            $categoryOther += $count;
        } else {
            $categoryValues[$idx] += $count;
        }
    }

    $categoryChart = [];
    foreach ($categoryDefaults as $i => $default) {
        $categoryChart[] = [
            'label' => $default['label'],
            'color' => $default['color'],
            'count' => $categoryValues[$i],
        ];
    }
    if ($categoryOther > 0) {
        $categoryChart[] = ['label' => 'Lainnya', 'color' => '#CBD5E1', 'count' => $categoryOther];
    }

    // Segmen donut chart (r = 40 → keliling 251.2).
    $donutCircumference = 251.2;
    $donutTotal = array_sum(array_column($categoryChart, 'count'));
    $donutSegments = [];
    $donutOffset = 0;
    if ($donutTotal > 0) {
        foreach ($categoryChart as $segment) {
            if ($segment['count'] <= 0) {
                continue;
            }
            $length = ($segment['count'] / $donutTotal) * $donutCircumference;
            $donutSegments[] = [
                'color'     => $segment['color'],
                'dasharray' => round($length, 2) . ' ' . round($donutCircumference - $length, 2),
                'offset'    => -round($donutOffset, 2),
            ];
            $donutOffset += $length;
        }
    }

    $statusTotal = $totalProjects > 0 ? $totalProjects : 1;
    $statusActivePercent   = min(100, round(($activeProjects / $statusTotal) * 100));
    $statusCompletePercent = min(100, round(($completedProjects / $statusTotal) * 100));

    $totalPages = max(1, (int) ceil($projectCount / 10));
@endphp

{{-- == ( layout utama : sidebar + main ) == --}}
<div class="flex min-h-screen">

    {{-- == ( sidebar komponen DRY ) == --}}
    <x-sidebar active="dashboard" />

    {{-- == ( konten utama ) == --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen">

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
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[11.5px] font-semibold px-3 py-1.5 rounded-lg hover:bg-[#DCEBFF] transition-colors" title="Ambil ulang data dashboard dari backend">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    Data terbaru
                </a>
            </div>
        </div>

        {{-- == ( 4 kartu statistik ) == --}}
        @php
        $stats = [
            ['label' => 'Total Proyek',         'value' => $totalProjects,     'sub' => 'Inisiatif Terdaftar', 'color' => '#0B3B82', 'icon' => 'folder'],
            ['label' => 'Proyek Berjalan',      'value' => $activeProjects,    'sub' => 'Dalam Pengerjaan',    'color' => '#0B3B82', 'icon' => 'play'],
            ['label' => 'Proyek Selesai',       'value' => $completedProjects, 'sub' => 'Siap Produksi',       'color' => '#0B3B82', 'icon' => 'check'],
            ['label' => 'Menunggu Persetujuan', 'value' => $waitingReview,     'sub' => 'Perlu Review',        'color' => '#F59E0B', 'icon' => 'clock-file'],
        ];
        @endphp

        <div class="grid grid-cols-4 gap-4">
            @foreach($stats as $stat)
            <x-stat-card :label="$stat['label']" :value="$stat['value']" :sub="$stat['sub']" :color="$stat['color']" :icon="$stat['icon']" />
            @endforeach
        </div>

        {{-- == ( tombol aksi : ajukan & lihat semua ) == --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('klien.ajukan-proyek') }}"
                   class="inline-flex items-center gap-2 bg-[#0B3B82] text-white rounded-xl px-5 py-2.5 font-semibold text-sm hover:bg-[#082a5c] transition-all shadow-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Ajukan Proyek Baru
                </a>
                <a href="{{ route('klien.projek-saya') }}"
                   class="inline-flex items-center gap-2 bg-white border border-slate-200 text-[#1E293B] rounded-xl px-5 py-2.5 font-semibold text-sm hover:border-[#0B3B82] hover:text-[#0B3B82] transition-all shadow-xs">
                    <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    Lihat Semua Proyek
                </a>
            </div>

            <div class="hidden sm:flex items-center gap-2 text-xs text-[#64748B]">
                <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                <span>Sistem aktif · Layanan kolaborasi TeFa</span>
            </div>
        </div>

        {{-- == ( area bawah : tabel proyek + kartu kanan ) == --}}
        <div class="flex flex-col lg:flex-row gap-6 items-start">

            {{-- == ( tabel proyek saya ) == --}}
            <div class="flex-1 min-w-0 bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden flex flex-col">
                <div class="px-6 py-4.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-bold text-[#1E293B] text-[15px]">Proyek Saya</h2>
                            @if($projectCount > 0)
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-[#EAF3FF] text-[#0B3B82]">{{ $projectCount }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Semua project yang ditugaskan ke Anda</p>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Search bar projek --}}
                        <div class="relative w-full sm:w-64">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                                </svg>
                            </span>
                            <input id="project-search" type="text" placeholder="Cari nama project..."
                                   class="w-full bg-[#F8FAFC] border border-slate-200 rounded-xl pl-9 pr-3.5 py-1.5 text-xs text-[#1E293B] placeholder:text-slate-400 focus:bg-white focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition shadow-2xs">
                        </div>

                        <a href="{{ route('klien.projek-saya') }}" class="text-xs font-semibold text-[#0B3B82] hover:text-[#082a5c] inline-flex items-center gap-1 transition flex-shrink-0">
                            Lihat Semua
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>
                </div>

                {{-- == ( tabel isi ) == --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="bg-[#F8FAFC] border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                                <th class="px-6 py-3.5">Nama Project</th>
                                <th class="px-4 py-3.5 w-36">Status</th>
                                <th class="px-4 py-3.5 w-44">Progress</th>
                                <th class="px-4 py-3.5 w-40 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="project-tbody" class="divide-y divide-slate-100 text-sm">
                            @forelse($projects as $project)
                            @php
                                $projectId = $project['id'] ?? null;
                                $projectTitle = (string) ($project['title'] ?? '-');
                                $projectCategory = (string) ($project['category'] ?? '');
                                $projectStatus = (string) ($project['status'] ?? '-');
                                $projectProgress = max(0, min(100, (int) ($project['progress'] ?? 0)));
                                $badge = $getStatusBadge($projectStatus);
                                $searchData = strtolower($projectTitle . ' ' . $projectCategory . ' ' . $badge['label']);
                            @endphp
                            <tr class="project-row hover:bg-slate-50/70 transition-colors" data-search="{{ $searchData }}">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-[#1E293B] text-[13px] leading-tight">{{ $projectTitle }}</p>
                                    @if(!empty($projectCategory))
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $projectCategory }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-[11px] font-semibold px-3 py-1 rounded-full inline-block {{ $badge['pill'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-300 {{ $badge['bar'] }}"
                                                 style="width: {{ $projectProgress }}%">
                                            </div>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-500 w-8 text-right">{{ $projectProgress }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    @if($projectId)
                                        <a href="{{ route('klien.projek-saya.detail', $projectId) }}"
                                           class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors {{ str_contains(strtolower($projectStatus), 'revision') ? 'bg-amber-100 text-amber-900 hover:bg-amber-200 border border-amber-300' : 'bg-[#EAF3FF] text-[#0B3B82] hover:bg-[#DCEBFF]' }}">
                                            Detail Projek Saya
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                            </svg>
                                        </a>
                                    @else
                                        <a href="{{ route('klien.projek-saya') }}"
                                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0B3B82] bg-[#EAF3FF] hover:bg-[#DCEBFF] px-3 py-1.5 rounded-lg transition-colors">
                                            Detail Projek Saya
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2.5">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-bold text-[#1E293B]">Belum ada proyek</p>
                                        <p class="text-xs text-slate-400 mt-1">Proyek yang Anda ajukan akan muncul di sini.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                            <tr id="no-search-match-row" class="hidden">
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2.5">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                                        </div>
                                        <p class="text-sm font-bold text-[#1E293B]">Project tidak ditemukan</p>
                                        <p class="text-xs text-slate-400 mt-1">Tidak ada nama project yang sesuai dengan kata kunci.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- == ( pagination tabel ) == --}}
                <div class="px-6 py-4 flex items-center justify-between border-t border-slate-100">
                    <p class="text-xs text-slate-400">Menampilkan <span class="font-semibold text-slate-600">{{ $projectCount > 0 ? '1–' . $projectCount : '0' }}</span> dari <span class="font-semibold text-slate-600">{{ max($totalProjects, $projectCount) }}</span> proyek</p>
                    <div class="flex items-center gap-1.5">
                        <button class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors" aria-label="Halaman sebelumnya">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                        </button>
                        @foreach(range(1, $totalPages) as $page)
                        <button class="w-8 h-8 rounded-lg text-xs font-semibold transition-colors {{ $page === 1 ? 'bg-[#EAF3FF] text-[#0B3B82]' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-800' }}">{{ $page }}</button>
                        @endforeach
                        <button class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors" aria-label="Halaman selanjutnya">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- == ( kolom kanan : distribusi kategori & status project ) == --}}
            <div class="w-full lg:w-80 xl:w-[320px] flex-shrink-0 flex flex-col gap-6">

                {{-- Card 1: Distribusi Kategori --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-6">
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
                        @foreach($categoryChart as $category)
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: {{ $category['color'] }}"></span>
                                    <span class="text-slate-600 font-medium">{{ $category['label'] }}</span>
                                </div>
                                <span class="font-bold text-[#1E293B]">{{ $category['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Card 2: Status Project --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-6">
                    <div class="mb-4">
                        <h2 class="font-bold text-[#1E293B] text-[15px]">Status Project</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Distribusi berdasarkan status</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Berjalan -->
                        <div>
                            <div class="flex items-center justify-between text-xs mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#0B3B82] flex-shrink-0"></span>
                                    <span class="font-semibold text-[#1E293B]">Berjalan</span>
                                </div>
                                <span class="font-bold text-[#1E293B]">{{ $activeProjects }}</span>
                            </div>
                            <div class="h-2 bg-[#EFF6FF] rounded-full overflow-hidden">
                                <div class="h-full bg-[#0B3B82] rounded-full transition-all duration-500" style="width: {{ $statusActivePercent }}%"></div>
                            </div>
                        </div>

                        <!-- Selesai -->
                        <div>
                            <div class="flex items-center justify-between text-xs mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#16A34A] flex-shrink-0"></span>
                                    <span class="font-semibold text-[#1E293B]">Selesai</span>
                                </div>
                                <span class="font-bold text-[#1E293B]">{{ $completedProjects }}</span>
                            </div>
                            <div class="h-2 bg-[#EFF6FF] rounded-full overflow-hidden">
                                <div class="h-full bg-[#16A34A] rounded-full transition-all duration-500" style="width: {{ $statusCompletePercent }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>{{-- /bottom grid --}}

        </main>

    </div>
</div>

{{-- Script pencarian live nama project --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('project-search');
        if (!searchInput) return;

        const rows = document.querySelectorAll('tr.project-row');
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

</body>
</html>