<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen">

@php
    $user = $user ?? session('user');
    $userName = $user['name'] ?? 'Project Manager';

    $currentRole = $currentRole ?? 'pm';
    $workspaceLabel = $currentRole === 'dosen' ? 'Workspace Dosen' : 'Workspace Project Manager';

    $dash = $dashboard ?? [];
    $totalProjects     = (int) ($dash['total_projects'] ?? 0);
    $activeProjects    = (int) ($dash['active_projects'] ?? 0);
    $completedProjects = (int) ($dash['completed_projects'] ?? 0);
    $waitingReview     = (int) ($dash['waiting_review'] ?? 0);
    $projectsList      = $dash['projects'] ?? [];

    // category_distribution dari API berbentuk [{"web": 1}, ...] → ratakan lalu jumlahkan per kategori.
    $categoryCounts = [];
    foreach (($dash['category_distribution'] ?? []) as $entry) {
        if (is_array($entry)) {
            foreach ($entry as $key => $count) {
                $categoryCounts[strtolower((string) $key)] = ($categoryCounts[strtolower((string) $key)] ?? 0) + (int) $count;
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
        'sis' => 3, 'software' => 3, 'system' => 3,
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
    $categoryChart[] = ['label' => 'Lainnya', 'color' => '#CBD5E1', 'count' => $categoryOther];

    // Segmen donut chart (r = 40 → keliling 251.33).
    $donutCircumference = 251.33;
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
@endphp

<div class="flex min-h-screen">

    {{-- Reusable Sidebar Component --}}
    <x-sidebar active="dashboard" />

    {{-- ===== MAIN CONTENT ===== --}}
    <main class="flex-1 ml-64 p-8 min-h-screen flex flex-col gap-6">

        {{-- ── 1. GREETING BANNER ── --}}
        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
            {{-- gradient dekoratif --}}
            <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                    Workspace Project Manager
                </span>
                <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                    Selamat Datang, <span class="text-[#FFB800]">{{ $userName }}</span>
                </h1>
                <p class="text-sm text-slate-400 mt-1.5">Pantau progres mahasiswa, review laporan sprint, dan pastikan milestone tercapai tepat waktu</p>
            </div>

            <div class="relative z-10 flex flex-col items-end gap-2 flex-shrink-0">
                <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[11.5px] font-semibold px-3 py-1.5 rounded-lg">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    Data terbaru
                </span>
            </div>
        </div>

        {{-- ── 2. 4 STAT CARDS ── --}}
        @php
            $pmStats = [
                ['label' => 'Total Proyek',    'value' => $totalProjects,     'sub' => 'Inisiatif Terdaftar', 'color' => '#0B3B82', 'icon' => 'folder'],
                ['label' => 'Proyek Berjalan', 'value' => $activeProjects,    'sub' => 'Dalam Pengerjaan',    'color' => '#0B3B82', 'icon' => 'play'],
                ['label' => 'Proyek Selesai',  'value' => $completedProjects, 'sub' => 'Siap Produksi',       'color' => '#0B3B82', 'icon' => 'check'],
                ['label' => 'Menunggu Review', 'value' => $waitingReview,     'sub' => 'Perlu Review',        'color' => '#F59E0B', 'icon' => 'clock-file'],
            ];
        @endphp

        <div class="grid grid-cols-4 gap-4">
            @foreach($pmStats as $stat)
                <x-stat-card :label="$stat['label']" :value="$stat['value']" :sub="$stat['sub']" :color="$stat['color']" :icon="$stat['icon']" />
            @endforeach
        </div>

        {{-- ── 3. BOTTOM SECTION: Table + Right Column ── --}}
        <div class="flex flex-col lg:flex-row gap-6 items-start">

            {{-- LEFT: Project yang Saya Kelola --}}
            <div class="flex-1 min-w-0 bg-white rounded-2xl border border-slate-100 shadow-2xs p-6">
                <div class="flex items-center justify-between pb-4">
                    <div>
                        <h2 class="font-bold text-[#1E293B] text-[15px]">Project yang Saya Kelola</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Semua project yang ditugaskan ke Anda</p>
                    </div>
                    <a href="{{ route('pm.project') }}" class="text-xs font-semibold text-[#0B3B82] hover:text-[#082a5c] inline-flex items-center gap-1 transition-colors">
                        Lihat Semua
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                </div>

                <div class="w-full">
                    {{-- Table Header --}}
                    <div class="bg-[#F8FAFC] rounded-xl px-5 py-3 grid grid-cols-12 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        <div class="col-span-5">NAMA PROJECT</div>
                        <div class="col-span-3">KLIEN / MITRA</div>
                        <div class="col-span-2">STATUS</div>
                        <div class="col-span-2">PROGRESS</div>
                    </div>

                    {{-- Table Rows --}}
                    <div class="divide-y divide-slate-100/80">

                        @forelse($projectsList as $proj)
                            @php
                                $statusRaw = strtolower((string) ($proj['status'] ?? ''));
                                $isCompleted = in_array($statusRaw, ['completed', 'selesai', 'done'], true);
                                $isReview = in_array($statusRaw, ['review', 'reviewing', 'in review'], true);
                                $isProposal = in_array($statusRaw, ['proposal', 'pending', 'draft', 'submitted'], true);

                                if ($isCompleted) {
                                    $pillClass = 'bg-[#DCFCE7] text-[#16A34A]';
                                    $barClass = 'bg-[#16A34A]';
                                } elseif ($isReview) {
                                    $pillClass = 'bg-[#FEF3C7] text-[#D97706]';
                                    $barClass = 'bg-[#D97706]';
                                } elseif ($isProposal) {
                                    $pillClass = 'bg-[#FFE4E6] text-[#E11D48]';
                                    $barClass = 'bg-[#E11D48]';
                                } else {
                                    $pillClass = 'bg-[#EFF6FF] text-[#2563EB]';
                                    $barClass = 'bg-[#0B3B82]';
                                }

                                $progress = max(0, min(100, (int) ($proj['progress'] ?? 0)));
                            @endphp
                            <div class="px-5 py-4 grid grid-cols-12 items-center hover:bg-slate-50/50 transition-colors">
                                <div class="col-span-5 pr-4">
                                    <p class="font-bold text-[#1E293B] text-[13px] leading-tight">{{ $proj['title'] ?? '-' }}</p>
                                </div>
                                <div class="col-span-3">
                                    <span class="text-[12px] font-medium text-slate-600">{{ $proj['client_name'] ?? '-' }}</span>
                                </div>
                                <div class="col-span-2">
                                    <span class="{{ $pillClass }} text-[11px] font-semibold px-3 py-1 rounded-full inline-block">
                                        {{ $proj['status'] ?? '-' }}
                                    </span>
                                </div>
                                <div class="col-span-2 flex items-center gap-3">
                                    <div class="w-20 h-2 bg-[#EFF6FF] rounded-full overflow-hidden">
                                        <div class="h-full {{ $barClass }} rounded-full" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-slate-500">{{ $progress }}%</span>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-10 flex flex-col items-center justify-center text-center">
                                <span class="w-11 h-11 rounded-2xl bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                </span>
                                <p class="text-sm font-semibold text-[#1E293B]">Anda belum pernah menangani project</p>
                                <p class="text-xs text-slate-400 mt-1">Project yang ditugaskan kepada Anda akan muncul di sini.</p>
                            </div>
                        @endforelse

                    </div>
                </div>
            </div>

            {{-- RIGHT: Distribusi Kategori & Status Project --}}
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
                                <div class="h-full bg-[#0B3B82] rounded-full" style="width: {{ $statusActivePercent }}%"></div>
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
                                <div class="h-full bg-[#16A34A] rounded-full" style="width: {{ $statusCompletePercent }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </main>

</div>

</body>
</html>