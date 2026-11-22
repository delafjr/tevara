<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar & Kelola Proyek - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen">

@php
    $user = $user ?? session('user');
    $userName = $user['name'] ?? 'Project Manager';
    $isDosen = session('active_role') === 'dosen';

    $projectData = is_array($projectData ?? null) ? $projectData : [];
    $rawProjects = $projects ?? ($projectData['projects'] ?? []);

    $dynamicProjects = [];
    foreach ($rawProjects as $idx => $item) {
        $st = $item['status'] ?? 'Berjalan';
        $stType = $item['status_type'] ?? 'berjalan';
        $code = $item['project_code'] ?? $item['proposal_code'] ?? ('PRJ-' . str_pad($item['id'] ?? ($idx + 1), 3, '0', STR_PAD_LEFT));
        $dl = $item['deadline'] ?? null;
        $dlFormatted = !empty($dl) ? date('d M Y', strtotime($dl)) : '—';

        $totalAmt = $item['total_amount'] ?? null;
        $nilai = !empty($totalAmt) ? 'Rp ' . number_format((float) $totalAmt, 0, ',', '.') : '—';

        $prog = (int) ($item['progress'] ?? 0);
        $dlStatus = 'normal';
        if (!empty($dl)) {
            $diffDays = (strtotime($dl) - time()) / 86400;
            if ($diffDays < 0 && $prog < 100) {
                $dlStatus = 'terlambat';
            } elseif ($diffDays <= 7 && $prog < 100) {
                $dlStatus = 'mepet';
            }
        }

        $dynamicProjects[] = [
            'id'              => $code,
            'raw_id'          => $item['id'] ?? null,
            'kategori'        => $item['kategori'] ?? $item['category'] ?? 'Teaching Factory',
            'status'          => $st,
            'status_type'     => $stType,
            'judul'           => $item['title'] ?? $item['judul'] ?? 'Projek Tanpa Judul',
            'klien'           => $item['client_name'] ?? $item['klien'] ?? '—',
            'pm_name'         => $item['pm_name'] ?? $userName,
            'spk'             => $item['spk_number'] ?? ('SPK/TEFA/' . date('Y') . '/' . ($item['id'] ?? 1)),
            'nilai'           => $nilai,
            'progress'        => $prog,
            'deadline'        => $dlFormatted,
            'deadline_status' => $dlStatus,
            'lingkup'         => $item['scope_of_work'] ?? $item['lingkup'] ?? 'Pengerjaan sistem terintegrasi Teaching Factory Politeknik Negeri Semarang.',
            'mulai'           => !empty($item['start_date']) ? date('d M Y', strtotime($item['start_date'])) : '—',
            'selesai'         => $dlFormatted,
            'jadwal_klien'    => $dlFormatted,
            'jadwal_klien_st' => $dlStatus,
            'modul_count'     => count($item['moduls'] ?? []),
            'modul_selesai'   => (int) round($prog * count($item['moduls'] ?? []) / 100),
            'modul_total'     => count($item['moduls'] ?? []),
            'moduls'          => $item['moduls'] ?? [],
            'tim'             => $item['tim'] ?? [],
            'klien_detail'    => $item['client_name'] ?? '—',
            'klien_email'     => $item['client_email'] ?? '—',
            'klien_telp'      => $item['client_phone'] ?? '—',
            'expanded'        => $idx === 0,
        ];
    }

    $projects = $dynamicProjects;
    $totalProjectCount = count($projects);
    $runningProjectCount = collect($projects)->where('status_type', 'berjalan')->count();
    $reviewProjectCount = collect($projects)->where('status_type', 'review')->count();
    $completedProjectCount = collect($projects)->where('status_type', 'selesai')->count();
    $statusMap = [
        'berjalan' => ['bg'=>'bg-[#DBEAFE]','text'=>'text-[#1D4ED8]','dot'=>'bg-[#2563EB]'],
        'review'   => ['bg'=>'bg-[#FEF3C7]','text'=>'text-[#B45309]','dot'=>'bg-[#F59E0B]'],
        'selesai'  => ['bg'=>'bg-[#DCFCE7]','text'=>'text-[#15803D]','dot'=>'bg-[#16A34A]'],
        'proposal' => ['bg'=>'bg-[#FDE68A]','text'=>'text-[#92400E]','dot'=>'bg-[#F59E0B]'],
    ];

    $peranColors = [
        'Fullstack Lead'  => ['bg'=>'bg-[#DBEAFE]','text'=>'text-[#1D4ED8]'],
        'UI/UX Designer'  => ['bg'=>'bg-[#EDE9FE]','text'=>'text-[#6D28D9]'],
        'Backend Lead'    => ['bg'=>'bg-[#DCFCE7]','text'=>'text-[#15803D]'],
        'QA Tester'       => ['bg'=>'bg-[#FEF3C7]','text'=>'text-[#B45309]'],
        'Frontend Lead'   => ['bg'=>'bg-[#DBEAFE]','text'=>'text-[#1D4ED8]'],
        'DevOps'          => ['bg'=>'bg-[#FCE7F3]','text'=>'text-[#9D174D]'],
    ];

    $progressColor = function($p) {
        if ($p >= 85) return '#16A34A';
        if ($p >= 60) return '#2563EB';
        if ($p >= 30) return '#F59E0B';
        return '#DC2626';
    };
@endphp

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <x-sidebar active="project" />

    {{-- ===== MAIN CONTENT ===== --}}
    <main class="flex-1 ml-64 p-8 min-h-screen flex flex-col gap-6">

        {{-- ── 1. PAGE HEADER ── --}}
        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
            {{-- gradient dekoratif --}}
            <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                    Workspace Project Manager
                </span>
                <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                    Daftar &amp; Kelola <span class="text-[#FFB800]">Proyek</span>
                </h1>
                <p class="text-sm text-slate-400 mt-1.5">Kelola progres, ambil keputusan eksekusi, dan validasi milestone proyek secara terpadu</p>
            </div>
        </div>

        @if(empty($projects))
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs px-8 py-14 text-center">
                <h2 class="text-lg font-bold text-[#0B254A]">Belum ada proyek terdaftar</h2>
                <p class="text-sm text-slate-500 mt-2">Proyek yang ditugaskan kepada Anda akan muncul di halaman ini.</p>
            </div>
        @else
        {{-- ── 2. STAT CARDS ── --}}
        <div class="grid grid-cols-4 gap-5">

            {{-- Card 1: Total Proyek --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Total Proyek</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Yang dikelola</p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-[#EBF3FE] flex items-center justify-center text-[#2563EB]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-[#0B254A] mt-4 leading-none">{{ $totalProjectCount }}</p>
                <p class="text-[10px] text-slate-400 mt-1">Aktif dimonitor</p>
            </div>

            {{-- Card 2: Proyek Berjalan --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Proyek Berjalan</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Sesuai milestones target</p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-[#DBEAFE] flex items-center justify-center text-[#2563EB]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-[#0B254A] mt-4 leading-none">{{ $runningProjectCount }}</p>
                <p class="text-[10px] text-slate-400 mt-1">Sesuai timeline</p>
            </div>

            {{-- Card 3: Proyek Selesai --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Proyek Selesai</p>
                        <p class="text-[10px] text-[#16A34A] font-semibold mt-0.5">Tervalidasi 36%</p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-[#DCFCE7] flex items-center justify-center text-[#16A34A]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-[#0B254A] mt-4 leading-none">{{ $completedProjectCount }}</p>
                <p class="text-[10px] text-slate-400 mt-1">Tervalidasi 36%</p>
            </div>

            {{-- Card 4: Mendekati Deadline --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Mendekati Deadline</p>
                        <p class="text-[10px] text-[#EF4444] font-semibold mt-0.5">~ 20 hari lagi</p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-[#FEF3C7] flex items-center justify-center text-[#F59E0B]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-[#0B254A] mt-4 leading-none">{{ $reviewProjectCount }}</p>
                <p class="text-[10px] text-[#EF4444] font-semibold mt-1">~ 20 hari lagi</p>
            </div>

        </div>

        {{-- ── 3. PROJECT LIST ── --}}
        <div x-data="{ activeFilter: 'semua', expandedId: 'PRJ-2026/044' }" class="flex flex-col gap-4">

            {{-- Filter Tabs --}}
            <div class="flex items-center gap-2 flex-wrap">
                @foreach([
                    ['key'=>'semua',   'label'=>'Semua (' . $totalProjectCount . ')'],
                    ['key'=>'berjalan','label'=>'Berjalan (' . $runningProjectCount . ')'],
                    ['key'=>'review',  'label'=>'Review / Testing (' . $reviewProjectCount . ')'],
                    ['key'=>'proposal','label'=>'Proposal F. 02 (1)'],
                ] as $tab)
                <button
                    type="button"
                    @click="activeFilter = '{{ $tab['key'] }}'"
                    :class="activeFilter === '{{ $tab['key'] }}' ? 'bg-[#0B254A] text-white shadow-xs' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50'"
                    class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all duration-150 cursor-pointer"
                >{{ $tab['label'] }}</button>
                @endforeach
            </div>

            {{-- Project Cards --}}
            @foreach($projects as $proj)
            @php
                $st = $statusMap[$proj['status_type']] ?? $statusMap['berjalan'];
                $pColor = $progressColor($proj['progress']);
            @endphp
            <div
                x-show="activeFilter === 'semua' || activeFilter === '{{ $proj['status_type'] }}'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden"
            >
                {{-- Card Header --}}
                <div class="px-6 pt-5 pb-4">
                    {{-- Top row --}}
                    <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold text-slate-400 tracking-wide">{{ $proj['id'] }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[10px] font-semibold">
                                {{ $proj['kategori'] }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $st['bg'] }} {{ $st['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }} inline-block"></span>
                                {{ $proj['status'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            {{-- Progress --}}
                            <div class="flex items-center gap-2">
                                <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width: {{ $proj['progress'] }}%; background: {{ $pColor }};"></div>
                                </div>
                                <span class="text-[11px] font-bold text-slate-500">{{ $proj['progress'] }}%</span>
                            </div>
                            {{-- Deadline --}}
                            <div class="flex items-center gap-1.5 text-[11px] font-semibold
                                @if($proj['deadline_status']==='terlambat') text-[#EF4444]
                                @elseif($proj['deadline_status']==='mepet') text-[#F59E0B]
                                @else text-slate-400 @endif">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <rect width="18" height="18" x="3" y="4" rx="2"/>
                                    <path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>
                                </svg>
                                {{ $proj['deadline'] }}
                            </div>
                            {{-- Lembar UAT Shortcut --}}
                            @php
                                $pmPropIdNum = (int) (preg_replace('/[^0-9]/', '', $proj['id'] ?? '') ?: 1);
                            @endphp
                            <a href="{{ route('pm.uat.detail', $pmPropIdNum) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-200 text-[11px] font-bold text-amber-800 hover:bg-amber-100 transition-all shadow-2xs">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 2h7M7 16h10"/>
                                </svg>
                                Lembar UAT
                            </a>

                            {{-- Toggle --}}
                            <button
                                type="button"
                                @click="expandedId = (expandedId === '{{ $proj['id'] }}') ? null : '{{ $proj['id'] }}'"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-[11px] font-semibold text-slate-500 hover:bg-slate-50 transition-all cursor-pointer"
                            >
                                <span x-text="expandedId === '{{ $proj['id'] }}' ? 'Tutup Detail' : 'Lihat Detail'">Lihat Detail</span>
                                <svg class="w-3 h-3 transition-transform duration-200" :class="expandedId === '{{ $proj['id'] }}' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Title + Meta --}}
                    <h2 class="text-[15px] font-extrabold text-[#0B254A] leading-tight mb-1.5">{{ $proj['judul'] }}</h2>
                    <div class="flex items-center gap-3 text-[11px] text-slate-400 flex-wrap">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                            </svg>
                            Klien: {{ $proj['klien'] }}
                        </span>
                        @if($proj['pm_name'])
                        <span>·</span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                            PM: {{ $proj['pm_name'] }}
                        </span>
                        @endif
                        @if($proj['spk'])
                        <span>·</span>
                        <span>SPK: {{ $proj['spk'] }}</span>
                        @endif
                    </div>
                </div>

                {{-- ── Expanded Detail ── --}}
                <div
                    x-show="expandedId === '{{ $proj['id'] }}'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="border-t border-slate-100"
                >
                    @if($proj['klien_detail'] || $proj['lingkup'])
                    {{-- Info 2-col --}}
                    <div class="grid grid-cols-2 divide-x divide-slate-100">

                        {{-- Informasi Klien & Kontrak --}}
                        <div class="px-6 py-5">
                            <div class="mb-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z"/>
                                    </svg>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Informasi Klien & Kontrak</span>
                                </div>
                            </div>
                            <p class="text-sm font-bold text-[#0B254A] mb-0.5">{{ $proj['klien_detail'] }}</p>
                            @if($proj['klien_email'])<p class="text-[11px] text-slate-400">{{ $proj['klien_email'] }}</p>@endif
                            @if($proj['klien_telp'])<p class="text-[11px] text-slate-400 mb-4">{{ $proj['klien_telp'] }}</p>@endif
                            @if($proj['spk'])
                            <div class="grid grid-cols-2 gap-3 mt-4">
                                <div class="bg-[#F8FAFC] rounded-xl p-3">
                                    <p class="text-[9px] text-slate-400 font-semibold uppercase tracking-wide mb-1">Nomor SPK</p>
                                    <p class="text-xs font-bold text-[#0B254A]">{{ $proj['spk'] }}</p>
                                </div>
                                @if($proj['nilai'])
                                <div class="bg-[#F8FAFC] rounded-xl p-3">
                                    <p class="text-[9px] text-slate-400 font-semibold uppercase tracking-wide mb-1">Nilai Kontrak</p>
                                    <p class="text-xs font-bold text-[#0B254A]">{{ $proj['nilai'] }}</p>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                        {{-- Lingkup Pekerjaan --}}
                        <div class="px-6 py-5">
                            <div class="mb-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                                    </svg>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lingkup Pekerjaan</span>
                                </div>
                            </div>
                            @if($proj['lingkup'])
                            <p class="text-xs text-slate-500 leading-relaxed mb-4">{{ $proj['lingkup'] }}</p>
                            @endif
                            <div class="grid grid-cols-2 gap-3">
                                @if($proj['mulai'] && $proj['selesai'])
                                <div class="bg-[#F8FAFC] rounded-xl p-3">
                                    <p class="text-[9px] text-slate-400 font-semibold uppercase tracking-wide mb-1">Durasi Pengerjaan</p>
                                    <p class="text-xs font-bold text-[#0B254A]">{{ $proj['mulai'] }} – {{ $proj['selesai'] }}</p>
                                </div>
                                @endif
                                @if($proj['jadwal_klien'])
                                <div class="bg-[#F8FAFC] rounded-xl p-3">
                                    <p class="text-[9px] text-slate-400 font-semibold uppercase tracking-wide mb-1">Jadwal Beli Klien</p>
                                    <p class="text-xs font-bold {{ $proj['jadwal_klien_st']==='terlambat' ? 'text-[#EF4444]' : 'text-[#0B254A]' }}">{{ $proj['jadwal_klien'] }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                    </div>
                    @endif

                    {{-- Modul & Spesifikasi TOR --}}
                    @if(!empty($proj['moduls']))
                    <div class="px-6 py-5 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Modul & Spesifikasi TOR</span>
                                <span class="bg-[#EBF3FE] text-[#2563EB] text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $proj['modul_count'] }} Paket Fitur</span>
                            </div>
                            @if($proj['modul_selesai'] !== null)
                            <a href="{{ route('pm.task-timeline') }}?project={{ urlencode($proj['id']) }}" class="text-[11px] text-[#2563EB] font-semibold hover:underline flex items-center gap-1">
                                Papan Task ({{ $proj['modul_selesai'] }}/{{ $proj['modul_total'] }} Selesai)
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                </svg>
                            </a>
                            @endif
                        </div>
                        <div class="space-y-2">
                            @foreach($proj['moduls'] as $mod)
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-[#F8FAFC] hover:bg-[#F1F5F9] transition-colors">
                                <div class="flex-shrink-0 w-6 h-6 rounded-lg bg-[#DDE9FA] text-[#2563EB] flex items-center justify-center text-[9px] font-black">{{ $mod['no'] }}</div>
                                <div>
                                    <p class="text-xs font-bold text-[#0B254A]">{{ $mod['label'] }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $mod['desc'] }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Tim Mahasiswa Pelaksana --}}
                    @if(!empty($proj['tim']))
                    <div class="px-6 py-5 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tim Mahasiswa Pelaksana</span>
                            <a href="{{ route('pm.anggota-tim') }}?project={{ urlencode($proj['id']) }}" class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#2563EB] hover:text-[#1D4ED8] transition-colors cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                                Kelola Anggota
                            </a>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($proj['tim'] as $member)
                            @php $pc = $peranColors[$member['peran']] ?? ['bg'=>'bg-slate-100','text'=>'text-slate-600']; @endphp
                            <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 bg-[#F8FAFC] hover:bg-white hover:shadow-xs transition-all">
                                <div class="w-9 h-9 rounded-full bg-[#0B254A] text-white flex items-center justify-center text-xs font-extrabold flex-shrink-0">{{ $member['inisial'] }}</div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-[#0B254A] truncate">{{ $member['nama'] }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">NIM. {{ $member['nim'] }}</p>
                                </div>
                                <span class="flex-shrink-0 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pc['bg'] }} {{ $pc['text'] }}">{{ $member['peran'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>
            @endforeach

            {{-- Pagination --}}
            <div class="flex items-center justify-between pt-2">
                <p class="text-xs text-slate-400">Menampilkan {{ min(5, count($projects)) }} dari {{ $totalProjectCount }} Proyek. Total {{ $totalProjectCount }} proyek</p>
                <div class="flex items-center gap-1.5">
                    <button type="button" @click="window.scrollTo({ top: 0, behavior: 'smooth' })" class="w-7 h-7 rounded-lg bg-[#0B254A] text-white text-xs font-bold flex items-center justify-center cursor-pointer">1</button>
                    <button type="button" @click="window.scrollTo({ top: 0, behavior: 'smooth' })" class="w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-500 text-xs font-semibold flex items-center justify-center hover:bg-slate-50 cursor-pointer">2</button>
                </div>
            </div>

        </div>
        @endif

    </main>

</div>

</body>
</html>
