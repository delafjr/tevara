<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UAT & Pengujian Proyek - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user');
    $firstName = explode(' ', $user['name'] ?? 'Pengurus')[0];
    $proposals = $proposals ?? [];
    $stats = $stats ?? [
        'total'          => 0,
        'testing'        => 0,
        'waiting_signoff'=> 0,
        'passed'         => 0,
    ];
@endphp

<div class="flex min-h-screen"
     x-data="{
         searchQuery: '',
         filterStatus: 'all',
         showTestingModal: false,
         selectedProposalId: null,
         selectedProposalTitle: '',
         openTestingConfirm(id, title) {
             this.selectedProposalId = id;
             this.selectedProposalTitle = title;
             this.showTestingModal = true;
         }
     }">

    <x-sidebar active="uat" />

    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 space-y-7">

            {{-- Flash Alert Messages --}}
            @if (session('success'))
                <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800 shadow-xs">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-bold text-emerald-900">Operasi Berhasil</p>
                        <p class="text-xs font-medium text-emerald-700 leading-relaxed">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error') || ($error ?? null))
                <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 shadow-xs">
                    <svg class="w-5 h-5 flex-shrink-0 text-red-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-bold text-red-900">Perhatian</p>
                        <p class="text-xs font-medium text-red-700 leading-relaxed">{{ session('error') ?? $error }}</p>
                    </div>
                </div>
            @endif

            {{-- Hero Card --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82] animate-ping"></span>
                        User Acceptance Testing (UAT)
                    </span>
                    <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                        Manajemen Sesi &amp; <span class="text-[#FFB800]">Pengujian UAT</span>
                    </h1>
                    <p class="text-sm text-[#64748B] mt-1.5 max-w-2xl leading-relaxed">
                        Kelola sesi pengujian penerimaan pengguna, input butir pengujian fitur, tandai fase testing, dan pantau tanda tangan (sign-off) klien.
                    </p>
                </div>

                <div class="relative z-10 flex-shrink-0 flex items-center gap-3">
                    <a href="{{ route('pengurus-tefa.projek') }}" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 font-semibold text-xs px-4 py-2.5 rounded-xl hover:bg-slate-50 transition-colors shadow-2xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                        </svg>
                        Semua Projek TeFa
                    </a>
                </div>
            </div>

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-stretch">
                {{-- Total --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Terdaftar</p>
                        <p class="text-2xl font-bold text-[#0B254A] mt-1">{{ $stats['total'] }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Proyek dalam radar pengujian</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#0B3B82] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 1-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                        </svg>
                    </div>
                </div>

                {{-- Sedang Fase Testing --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Fase Testing</p>
                        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['testing'] }}</p>
                        <p class="text-[11px] text-amber-600/80 mt-0.5">Status id 8 (Siap UAT)</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                </div>

                {{-- Menunggu Sign-off --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Menunggu Sign-off</p>
                        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['waiting_signoff'] }}</p>
                        <p class="text-[11px] text-indigo-600/80 mt-0.5">Sesi UAT siap ditinjau klien</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                        </svg>
                    </div>
                </div>

                {{-- Lulus UAT & Signed --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Lulus &amp; Disahkan</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['passed'] }}</p>
                        <p class="text-[11px] text-emerald-600/80 mt-0.5">UAT PASSED & Sign-off Klien</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Table & List Card --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="font-bold text-[#1E293B] text-base">Daftar Proyek &amp; Lembar UAT</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Tandai fase testing, isi butir pengujian fitur, dan pantau pengesahan klien.</p>
                    </div>

                    {{-- Search & Filters --}}
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                                </svg>
                            </span>
                            <input type="text"
                                   x-model="searchQuery"
                                   placeholder="Cari kode / judul proyek..."
                                   class="text-xs border border-slate-200 rounded-xl pl-9 pr-3 py-2 focus:outline-none focus:border-[#0B3B82] w-64 bg-slate-50/50">
                        </div>

                        <select x-model="filterStatus"
                                class="text-xs border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-[#0B3B82] bg-slate-50/50 font-medium text-slate-700">
                            <option value="all">Semua Status</option>
                            <option value="testing">Fase Testing (id=8)</option>
                            <option value="has_uat">Sudah Memiliki Sesi UAT</option>
                            <option value="waiting_uat">Belum Ada Sesi UAT</option>
                            <option value="signed">UAT Disahkan (Sign-off)</option>
                        </select>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70">
                                <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-6 py-3.5">Proyek &amp; Kode</th>
                                <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3.5">Klien / Mitra</th>
                                <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3.5">Status Proyek</th>
                                <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3.5">Hasil UAT</th>
                                <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3.5">Sign-off Klien</th>
                                <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($proposals as $p)
                                @php
                                    $pId = $p['id'];
                                    $code = $p['proposal_code'] ?? ('PRJ-' . $pId);
                                    $title = $p['title'] ?? 'Proyek Tanpa Judul';
                                    $clientName = $p['client']['name'] ?? $p['client_name'] ?? 'Klien Mitra';
                                    $companyName = $p['client']['company_name'] ?? $p['client_company_name'] ?? '-';
                                    $statusId = (int) ($p['status_id'] ?? 0);
                                    $statusName = $p['status']['name'] ?? $p['status'] ?? 'Active';
                                    $isTesting = ($statusId === 8 || strtolower($statusName) === 'testing');
                                    $isHandover = ($statusId === 9 || strtolower($statusName) === 'handover');
                                    $isCompleted = ($statusId === 10 || strtolower($statusName) === 'completed');

                                    $uat = $p['uat'] ?? null;
                                    $hasUat = !empty($uat);
                                    $overallResult = $uat['overall_result'] ?? null;
                                    $itemsCount = count($uat['uat_items'] ?? $uat['items'] ?? []);
                                    $isSigned = !empty($uat['signed_off_at']);
                                    $signerName = $uat['client_signoff_name'] ?? null;
                                    $sessionDate = $uat['session_date'] ?? null;
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors"
                                    x-show="(
                                        filterStatus === 'all' ||
                                        (filterStatus === 'testing' && {{ $isTesting ? 'true' : 'false' }}) ||
                                        (filterStatus === 'has_uat' && {{ $hasUat ? 'true' : 'false' }}) ||
                                        (filterStatus === 'waiting_uat' && {{ !$hasUat ? 'true' : 'false' }}) ||
                                        (filterStatus === 'signed' && {{ $isSigned ? 'true' : 'false' }})
                                    ) && (
                                        searchQuery === '' ||
                                        '{{ strtolower($code . ' ' . $title . ' ' . $clientName . ' ' . $statusName . ' ' . (strtolower(trim($statusName)) === 'submitted' ? 'siap diuji' : '')) }}'.includes(searchQuery.toLowerCase())
                                    )">
                                    {{-- Kode & Title --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-start gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 2h7M7 16h10"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[11px] font-mono font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                                                        {{ $code }}
                                                    </span>
                                                </div>
                                                <p class="font-bold text-[#1E293B] text-sm mt-1 line-clamp-1 max-w-sm">{{ $title }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Klien --}}
                                    <td class="px-4 py-4">
                                        <p class="text-xs font-bold text-slate-800">{{ $clientName }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $companyName }}</p>
                                    </td>

                                    {{-- Status Proyek --}}
                                    <td class="px-4 py-4">
                                        @if($isTesting)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Testing
                                            </span>
                                        @elseif($isHandover)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                Handover
                                            </span>
                                        @elseif($isCompleted)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Completed
                                            </span>
                                        @elseif(strtolower(trim($statusName)) === 'submitted')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#0B3B82] border border-blue-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                                                Siap Diuji
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                                {{ $statusName }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Hasil UAT --}}
                                    <td class="px-4 py-4">
                                        @if($hasUat)
                                            @if($overallResult === 'PASSED')
                                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                    PASSED ({{ $itemsCount }} Item)
                                                </div>
                                            @elseif($overallResult === 'PARTIAL')
                                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                                    PARTIAL ({{ $itemsCount }} Item)
                                                </div>
                                            @elseif($overallResult === 'FAILED')
                                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                                    FAILED ({{ $itemsCount }} Item)
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-500">{{ $overallResult }}</span>
                                            @endif
                                            @if($sessionDate)
                                                <p class="text-[10px] text-slate-400 mt-0.5">Sesi: {{ \Carbon\Carbon::parse($sessionDate)->translatedFormat('d M Y') }}</p>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center text-xs font-medium text-slate-400">
                                                Belum Ada Sesi
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Sign-off Klien --}}
                                    <td class="px-4 py-4">
                                        @if($isSigned)
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">✓</span>
                                                <div>
                                                    <p class="text-xs font-bold text-emerald-800">Ditandatangani</p>
                                                    <p class="text-[10px] text-slate-400">Oleh: {{ $signerName }}</p>
                                                </div>
                                            </div>
                                        @elseif($hasUat)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                Menunggu Klien
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($hasUat)
                                                {{-- 1. Sudah ada lembar UAT --}}
                                                <a href="{{ route('pengurus-tefa.uat.detail', $pId) }}"
                                                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-[#0B3B82] text-white hover:bg-[#082a5c] transition shadow-2xs">
                                                    <span>Detail &amp; Revisi UAT</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                                                    </svg>
                                                </a>
                                            @elseif($isTesting)
                                                {{-- 2. Sudah fase Testing tapi belum ada sesi UAT: Buat Sesi UAT --}}
                                                <a href="{{ route('pengurus-tefa.uat.detail', $pId) }}"
                                                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                                    </svg>
                                                    <span>Buat Sesi UAT</span>
                                                </a>
                                            @else
                                                {{-- 3. Belum fase Testing: Tandai Testing terlebih dahulu (alur bertahap, tidak berbarengan) --}}
                                                <button type="button"
                                                        @click="openTestingConfirm({{ $pId }}, '{{ addslashes($title) }}')"
                                                        title="Tandai proyek masuk fase Testing terlebih dahulu agar formulir UAT dapat dibuat"
                                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 transition shadow-2xs cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                                    </svg>
                                                    <span>Tandai Testing</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                        <p class="text-sm font-semibold">Tidak ada proyek yang ditemukan.</p>
                                        <p class="text-xs mt-1">Proyek akan muncul di sini saat terdaftar pada sistem.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    {{-- MODAL KONFIRMASI TANDAI STATUS TESTING (PATCH /proposals/{id}/status/testing) --}}
    <div x-show="showTestingModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4"
             @click.outside="showTestingModal = false">
            <div class="flex items-center gap-3 text-amber-600">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Tandai Fase Testing (UAT)</h3>
                    <p class="text-xs text-slate-500">Shortcut PATCH /proposals/{id}/status/testing</p>
                </div>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Apakah Anda yakin ingin memindahkan status proyek <strong class="text-slate-900" x-text="selectedProposalTitle"></strong> ke fase <strong>Testing (Status ID = 8)</strong>? Setelah status proyek menjadi Testing, tombol <strong>Buat Sesi UAT</strong> akan aktif.
            </p>

            <form :action="'/pengurus-tefa/proposals/' + selectedProposalId + '/status/testing'" method="POST" class="flex flex-col sm:flex-row items-center justify-end gap-2.5 pt-3">
                @csrf
                <button type="button"
                        @click="showTestingModal = false"
                        class="w-full sm:w-auto px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        name="next"
                        value="stay"
                        class="w-full sm:w-auto px-4 py-2 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 text-xs font-bold transition cursor-pointer">
                    Ya, Tandai Testing
                </button>
                <button type="submit"
                        name="next"
                        value="create_uat"
                        class="w-full sm:w-auto px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm shadow-emerald-600/30 transition cursor-pointer flex items-center justify-center gap-1.5">
                    <span>Tandai &amp; Buat Sesi UAT</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

</div>

</body>
</html>
