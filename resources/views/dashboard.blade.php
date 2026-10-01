<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">

{{-- =========================================================
     LAYOUT: Sidebar + Main
     ========================================================= --}}
<div class="flex min-h-screen">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="w-64 min-h-screen bg-white border-r border-slate-100 flex flex-col fixed top-0 left-0 z-30">

        {{-- Logo --}}
        <div class="px-6 py-5 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-[#0B3B82] rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </div>
                <div>
                    <p class="text-[13px] font-bold text-[#0B3B82] leading-none tracking-tight">TEVARA</p>
                    <p class="text-[10px] text-slate-400 mt-0.5 leading-none">Knowledge Into Action</p>
                </div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            @php
            $navItems = [
                ['icon' => 'home', 'label' => 'Dashboard', 'active' => true],
                ['icon' => 'folder', 'label' => 'Project Saya', 'active' => false],
                ['icon' => 'plus-circle', 'label' => 'Ajukan Project', 'active' => false],
                ['icon' => 'document', 'label' => 'Dokumen', 'active' => false],
                ['icon' => 'chat', 'label' => 'Komunikasi', 'active' => false, 'badge' => 3],
                ['icon' => 'chart', 'label' => 'Laporan', 'active' => false],
                ['icon' => 'credit-card', 'label' => 'Tagihan & Pembayaran', 'active' => false],
                ['icon' => 'settings', 'label' => 'Pengaturan', 'active' => false],
            ];
            @endphp

            @foreach($navItems as $item)
            <a href="#"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13.5px] font-medium transition-colors
                      {{ $item['active']
                          ? 'bg-[#EAF3FF] text-[#0B3B82]'
                          : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                <span class="w-4 h-4 flex-shrink-0">
                    @if($item['icon'] === 'home')
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                    @elseif($item['icon'] === 'folder')
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>
                    @elseif($item['icon'] === 'plus-circle')
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    @elseif($item['icon'] === 'document')
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    @elseif($item['icon'] === 'chat')
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>
                    @elseif($item['icon'] === 'chart')
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                    @elseif($item['icon'] === 'credit-card')
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                    @elseif($item['icon'] === 'settings')
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    @endif
                </span>
                <span class="flex-1">{{ $item['label'] }}</span>
                @if(isset($item['badge']))
                <span class="bg-[#FFB800] text-[#1E293B] text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none">{{ $item['badge'] }}</span>
                @endif
            </a>
            @endforeach
        </nav>

        {{-- User profile --}}
        <div class="px-4 py-4 border-t border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-[#0B3B82] flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-xs font-bold">BS</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-[#1E293B] truncate">Budi Santoso</p>
                    <p class="text-[11px] text-slate-400 truncate">Klien</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen">

        {{-- Topbar (minimal — hanya breadcrumb & user action) --}}
        <header class="bg-white border-b border-slate-100 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <p class="text-xs text-slate-400 font-medium">Dashboard</p>
                <p class="text-[15px] font-semibold text-[#1E293B] leading-tight">Selamat datang, Budi 👋</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400">{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
                <div class="w-px h-4 bg-slate-200"></div>
                <a href="#" class="text-xs text-slate-500 hover:text-[#0B3B82] font-medium transition-colors">Logout</a>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 px-8 py-7 space-y-7">

            {{-- ── HERO BANNER ── --}}
            <div class="relative overflow-hidden rounded-2xl bg-[#0B3B82] px-8 py-7">
                {{-- decorative shape --}}
                <div class="absolute -right-16 -top-16 w-72 h-72 rounded-full bg-white/5"></div>
                <div class="absolute right-24 -bottom-10 w-40 h-40 rounded-full bg-[#FFB800]/10"></div>

                <div class="relative z-10 max-w-lg">
                    <p class="text-[#FFB800] text-xs font-semibold tracking-wide mb-2">Teaching Factory · Polines</p>
                    <h1 class="text-white text-2xl font-bold leading-snug">
                        Pantau seluruh project Anda<br>dalam satu tampilan.
                    </h1>
                    <p class="text-blue-200 text-sm mt-2 leading-relaxed">
                        Mulai dari pengajuan, progres pengerjaan, hingga serah terima — semua terpantau di sini.
                    </p>
                </div>
            </div>

            {{-- ── 4 STAT CARDS ── --}}
            <div class="grid grid-cols-4 gap-4">

                @php
                $stats = [
                    [
                        'label' => 'Total Project',
                        'value' => '5',
                        'sub'   => '+1 dari bulan lalu',
                        'up'    => true,
                        'color' => '#0B3B82',
                        'icon'  => 'folder',
                    ],
                    [
                        'label' => 'Project Berjalan',
                        'value' => '3',
                        'sub'   => 'Sama dari bulan lalu',
                        'up'    => null,
                        'color' => '#F59E0B',
                        'icon'  => 'play',
                    ],
                    [
                        'label' => 'Project Selesai',
                        'value' => '1',
                        'sub'   => '+1 dari bulan lalu',
                        'up'    => true,
                        'color' => '#16A34A',
                        'icon'  => 'check',
                    ],
                    [
                        'label' => 'Menunggu Persetujuan',
                        'value' => '1',
                        'sub'   => '+1 dari bulan lalu',
                        'up'    => false,
                        'color' => '#DC2626',
                        'icon'  => 'clock',
                    ],
                ];
                @endphp

                @foreach($stats as $stat)
                <div class="bg-white rounded-xl px-5 py-5 border border-slate-100 shadow-sm">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background-color: {{ $stat['color'] }}18">
                            <span style="color: {{ $stat['color'] }}" class="w-4 h-4">
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
                        <span class="text-[10px] font-semibold text-red-500 bg-red-50 px-1.5 py-0.5 rounded">↑</span>
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Ajukan Project Baru
                </a>
                <a href="#"
                   class="flex items-center gap-3.5 bg-white border border-slate-200 text-[#1E293B] rounded-xl px-5 py-4 font-semibold text-sm hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors shadow-sm">
                    <svg class="w-5 h-5 flex-shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                    </svg>
                    Lihat Semua Project
                </a>
                <a href="#"
                   class="flex items-center gap-3.5 bg-white border border-slate-200 text-[#1E293B] rounded-xl px-5 py-4 font-semibold text-sm hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors shadow-sm">
                    <svg class="w-5 h-5 flex-shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
                    </svg>
                    Hubungi Tim TEVARA
                </a>
            </div>

            {{-- ── BOTTOM GRID: Project Table + Donut ── --}}
            <div class="grid grid-cols-3 gap-6">

                {{-- Project Table (col-span-2) --}}
                <div class="col-span-2 bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-[#1E293B] text-[15px]">Project Saya</p>
                            <p class="text-xs text-slate-400 mt-0.5">Semua project yang terdaftar</p>
                        </div>
                        <a href="#" class="text-xs font-semibold text-[#0B3B82] hover:underline flex items-center gap-1">
                            Lihat Semua
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>

                    {{-- Filter tabs --}}
                    <div class="px-6 pt-3 pb-1 flex gap-1" x-data="{ tab: 'semua' }">
                        @php
                        $tabs = [
                            ['key' => 'semua',    'label' => 'Semua Project', 'count' => 5],
                            ['key' => 'berjalan', 'label' => 'Berjalan',      'count' => 3],
                            ['key' => 'review',   'label' => 'Review',        'count' => 1],
                            ['key' => 'selesai',  'label' => 'Selesai',       'count' => 1],
                        ];
                        @endphp
                        @foreach($tabs as $tab)
                        <button
                            @click="tab = '{{ $tab['key'] }}'"
                            :class="tab === '{{ $tab['key'] }}'
                                ? 'bg-[#EAF3FF] text-[#0B3B82] font-semibold'
                                : 'text-slate-500 hover:text-slate-700'"
                            class="text-xs px-3 py-1.5 rounded-lg transition-colors">
                            {{ $tab['label'] }}
                            <span class="ml-1 text-[10px] opacity-70">({{ $tab['count'] }})</span>
                        </button>
                        @endforeach
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-6 py-3">Nama Project</th>
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Kategori</th>
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Status</th>
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3 w-36">Progress</th>
                                    <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Deadline</th>
                                    <th class="px-3 py-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">

                                @php
                                $projects = [
                                    ['name' => 'Sistem Informasi UMKM', 'org' => 'Polines', 'category' => 'Website', 'status' => 'Berjalan', 'progress' => 75, 'deadline' => '12 Okt 2026'],
                                    ['name' => 'Smart Farming IoT',     'org' => 'Polines', 'category' => 'IoT',     'status' => 'Review',   'progress' => 60, 'deadline' => '5 Okt 2026'],
                                    ['name' => 'Website Company Profile','org' => 'Polines', 'category' => 'Website', 'status' => 'Selesai',  'progress' => 100,'deadline' => '—'],
                                    ['name' => 'Aplikasi Inventori',    'org' => 'Polines', 'category' => 'Aplikasi','status' => 'Proposal', 'progress' => 25, 'deadline' => '18 Okt 2026'],
                                    ['name' => 'Sistem Monitoring Produksi','org' => 'Polines','category' => 'IoT',  'status' => 'Berjalan', 'progress' => 40, 'deadline' => '25 Okt 2026'],
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
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-[#EAF3FF] flex items-center justify-center flex-shrink-0">
                                                <svg class="w-3.5 h-3.5 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-[#1E293B] text-[13px] leading-tight">{{ $project['name'] }}</p>
                                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $project['org'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <span class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded font-medium">{{ $project['category'] }}</span>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $statusStyle[$project['status']] ?? 'bg-slate-100 text-slate-600' }}">
                                            {{ $project['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 w-36">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full transition-all"
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

                {{-- Donut Chart (col-span-1) --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 flex flex-col">
                    <div class="mb-5">
                        <p class="font-semibold text-[#1E293B] text-[15px]">Distribusi Kategori</p>
                        <p class="text-xs text-slate-400 mt-0.5">Project per kategori</p>
                    </div>

                    {{-- SVG Donut --}}
                    <div class="flex items-center justify-center my-2" x-data="{
                        segments: [
                            { label: 'Website',       value: 2, color: '#0B3B82', pct: 40 },
                            { label: 'IoT & Embedded',value: 1, color: '#FFB800', pct: 20 },
                            { label: 'Aplikasi',      value: 1, color: '#6366F1', pct: 20 },
                            { label: 'Lainnya',       value: 1, color: '#94A3B8', pct: 20 },
                        ],
                        total: 5,
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
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#F1F5F9" stroke-width="18"/>
                                <template x-for="arc in arcs" :key="arc.label">
                                    <circle
                                        cx="50" cy="50" r="40"
                                        fill="none"
                                        :stroke="arc.color"
                                        stroke-width="18"
                                        stroke-linecap="butt"
                                        :stroke-dasharray="`${arc.dasharray} ${251.2 - arc.dasharray}`"
                                        :stroke-dashoffset="arc.dashoffset"
                                        style="transition: stroke-dasharray .4s ease"
                                    />
                                </template>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <p class="text-3xl font-bold text-[#1E293B]">5</p>
                                <p class="text-[11px] text-slate-400 font-medium">Project</p>
                            </div>
                        </div>
                    </div>

                    {{-- Legend --}}
                    <div class="mt-5 space-y-2.5">
                        @php
                        $donutItems = [
                            ['label' => 'Website',        'count' => 2, 'color' => '#0B3B82'],
                            ['label' => 'IoT & Embedded', 'count' => 1, 'color' => '#FFB800'],
                            ['label' => 'Aplikasi',       'count' => 1, 'color' => '#6366F1'],
                            ['label' => 'Lainnya',        'count' => 1, 'color' => '#94A3B8'],
                        ];
                        @endphp
                        @foreach($donutItems as $item)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-sm flex-shrink-0" style="background-color: {{ $item['color'] }}"></span>
                                <span class="text-[12.5px] text-slate-600">{{ $item['label'] }}</span>
                            </div>
                            <span class="text-[12.5px] font-semibold text-[#1E293B]">{{ $item['count'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>{{-- /bottom grid --}}

        </main>

        {{-- Footer --}}
        <footer class="px-8 py-4 border-t border-slate-100 bg-white">
            <p class="text-[11px] text-slate-400">© {{ date('Y') }} TEVARA · Politeknik Negeri Semarang</p>
        </footer>
    </div>
</div>

</body>
</html>