<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lembar UAT — {{ $proposal['title'] ?? 'Proyek' }} - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user');
    $proposal = is_array($proposal ?? null) ? $proposal : [];
    $proposalId = $proposal['id'] ?? null;
    $proposalCode = $proposal['proposal_code'] ?? ('PRJ-' . $proposalId);
    $title = $proposal['title'] ?? 'Proyek Tanpa Judul';
    $clientName = $proposal['client']['name'] ?? $proposal['client_name'] ?? 'Klien Mitra';
    $companyName = $proposal['client']['company_name'] ?? $proposal['client_company_name'] ?? '-';
    $statusId = (int) ($proposal['status_id'] ?? 0);
    $statusName = $proposal['status']['name'] ?? $proposal['status'] ?? 'Draft';
    $isTesting = ($statusId === 8 || strtolower($statusName) === 'testing');
    $isHandover = ($statusId === 9 || strtolower($statusName) === 'handover');
    $isCompleted = ($statusId === 10 || strtolower($statusName) === 'completed');

    $modules = $proposal['modules'] ?? [];

    $uat = is_array($uat ?? null) ? $uat : null;
    $hasUat = !empty($uat);
    $isSigned = !empty($uat['signed_off_at']);
    $signerName = $uat['client_signoff_name'] ?? null;
    $signedAt = $uat['signed_off_at'] ?? null;

    $initialSessionDate = $uat['session_date'] ?? date('Y-m-d');
    $initialOverallResult = $uat['overall_result'] ?? 'PASSED';
    $initialClientNotes = $uat['client_notes'] ?? '';

    // Transform initial items
    $rawItems = $uat['uat_items'] ?? $uat['items'] ?? [];
    $initialItems = [];
    foreach ($rawItems as $item) {
        $initialItems[] = [
            'id'              => $item['id'] ?? null,
            'feature_name'    => $item['feature_name'] ?? '',
            'test_scenario'   => $item['test_scenario'] ?? '',
            'result'          => $item['result'] ?? 'PASSED',
            'bug_severity'    => $item['bug_severity'] ?? 'MINOR',
            'bug_description' => $item['bug_description'] ?? '',
            'client_feedback' => $item['client_feedback'] ?? '',
        ];
    }

    if (empty($initialItems)) {
        // Pre-fill from proposal modules if available, otherwise 1 default item
        if (!empty($modules)) {
            foreach ($modules as $m) {
                $initialItems[] = [
                    'id'              => null,
                    'feature_name'    => $m['title'] ?? 'Fitur Utama',
                    'test_scenario'   => $m['description'] ?? 'Uji fungsionalitas fitur sesuai spesifikasi modul.',
                    'result'          => 'PASSED',
                    'bug_severity'    => 'MINOR',
                    'bug_description' => '',
                    'client_feedback' => 'Fitur telah berjalan dengan lancar.',
                ];
            }
        } else {
            $initialItems = [
                [
                    'id'              => null,
                    'feature_name'    => '',
                    'test_scenario'   => '',
                    'result'          => 'PASSED',
                    'bug_severity'    => 'MINOR',
                    'bug_description' => '',
                    'client_feedback' => '',
                ]
            ];
        }
    }

    // Default modules array for import button in Alpine
    $proposalModulesJson = array_map(function($m) {
        return [
            'title' => $m['title'] ?? '',
            'description' => $m['description'] ?? '',
        ];
    }, $modules);
@endphp

<div class="flex min-h-screen"
     x-data="{
         isEditMode: {{ (!$hasUat || session('edit_mode')) ? 'true' : 'false' }},
         sessionDate: '{{ $initialSessionDate }}',
         overallResult: '{{ $initialOverallResult }}',
         clientNotes: @js($initialClientNotes),
         items: @js($initialItems),
         availableModules: @js($proposalModulesJson),
         addItem() {
             this.items.push({
                 id: null,
                 feature_name: '',
                 test_scenario: '',
                 result: 'PASSED',
                 bug_severity: 'MINOR',
                 bug_description: '',
                 client_feedback: ''
             });
         },
         removeItem(index) {
             if (this.items.length > 1) {
                 this.items.splice(index, 1);
             }
         },
         importModules() {
             if (this.availableModules.length === 0) return;
             if (this.items.length === 1 && !this.items[0].feature_name) {
                 this.items = [];
             }
             this.availableModules.forEach(m => {
                 this.items.push({
                     id: null,
                     feature_name: m.title || 'Modul Fitur',
                     test_scenario: m.description || 'Pengujian skenario fitur sistem.',
                     result: 'PASSED',
                     bug_severity: 'MINOR',
                     bug_description: '',
                     client_feedback: ''
                 });
             });
         }
     }">

    <x-sidebar active="uat" />

    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 space-y-6">

            {{-- Flash Alert Messages --}}
            @if (session('success'))
                <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800 shadow-xs">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-bold text-emerald-900">Berhasil</p>
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

            @if (isset($errors) && $errors->any())
                <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 shadow-xs">
                    <svg class="w-5 h-5 flex-shrink-0 text-red-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-bold text-red-900">Validasi Formulir Gagal</p>
                        <ul class="list-disc list-inside text-xs mt-1 space-y-0.5 text-red-700">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Breadcrumb & Quick Actions --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <a href="{{ route('pengurus-tefa.uat') }}"
                   class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82] transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke Daftar UAT
                </a>

                <div class="flex items-center gap-2.5">
                    @if($hasUat && !$isSigned)
                        <button type="button"
                                @click="isEditMode = !isEditMode"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold border transition shadow-2xs cursor-pointer"
                                :class="isEditMode ? 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200' : 'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100'">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                            </svg>
                            <span x-text="isEditMode ? 'Batalkan Edit (Lihat Lembar)' : 'Revisi / Perbarui Lembar UAT (PUT)'"></span>
                        </button>
                    @endif

                    <span class="text-[11px] font-mono font-bold text-slate-500 bg-white border border-slate-200 px-3 py-1.5 rounded-xl">
                        {{ $proposalCode }}
                    </span>
                </div>
            </div>

            {{-- Proposal Overview Card --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm p-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-lg bg-blue-50 text-blue-700">
                                {{ $proposal['category']['name'] ?? $proposal['category_name'] ?? 'Software Project' }}
                            </span>
                            @if($isTesting)
                                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Status: Testing (id=8)
                                </span>
                            @elseif($isHandover)
                                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    Status: Handover (id=9)
                                </span>
                            @elseif($isCompleted)
                                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Status: Selesai / Completed (id=10)
                                </span>
                            @elseif(strtolower(trim($statusName)) === 'submitted')
                                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-lg bg-blue-50 text-[#0B3B82] border border-blue-200 inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                                    Status: Siap Diuji
                                </span>
                            @else
                                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
                                    Status: {{ $statusName }} (id={{ $statusId }})
                                </span>
                            @endif
                        </div>
                        <h1 class="text-xl font-bold text-[#1E293B]">{{ $title }}</h1>
                        <p class="text-xs text-slate-400">
                            Klien Mitra: <strong class="text-slate-700">{{ $clientName }}</strong> ({{ $companyName }})
                        </p>
                    </div>

                    {{-- Actions: Set Testing Shortcut if not testing --}}
                    <div class="flex-shrink-0 flex items-center gap-3">
                        @if(!$isTesting && !$hasUat)
                            <form action="{{ route('pengurus-tefa.uat.status-testing', $proposalId) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-sm shadow-amber-500/25 transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                    </svg>
                                    Tandai Masuk Fase Testing
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- SIGNED CERTIFICATE BANNER (If UAT is signed) --}}
            @if($isSigned)
                <div class="rounded-2xl border-2 border-emerald-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50/40 p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full mb-1">
                                    Sign-off Telah Disahkan (Terkunci)
                                </span>
                                <h2 class="text-base font-bold text-emerald-950">Berita Acara UAT Telah Ditandatangani Klien</h2>
                                <p class="text-xs text-emerald-800/90 mt-0.5">
                                    Disahkan oleh <strong>{{ $signerName }}</strong> pada tanggal {{ \Carbon\Carbon::parse($signedAt)->translatedFormat('d F Y, H:i') }} WIB.
                                    Status proyek telah otomatis dinaikkan ke tahap Handover (id=9).
                                </p>
                            </div>
                        </div>
                        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-emerald-300 text-emerald-800 hover:bg-emerald-100 text-xs font-bold transition shadow-2xs flex-shrink-0 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.072-1.272-2.962M20.25 15.75H3.75A2.25 2.25 0 0 0 1.5 18v1.5a2.25 2.25 0 0 0 2.25 2.25h16.5A2.25 2.25 0 0 0 22.5 19.5V18a2.25 2.25 0 0 0-2.25-2.25Z"/>
                            </svg>
                            Cetak Lembar UAT
                        </button>
                    </div>
                </div>
            @elseif($hasUat)
                <div class="rounded-2xl border border-indigo-200 bg-indigo-50/60 p-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-indigo-950">Sesi UAT Tersedia — Menunggu Sign-off Klien</p>
                            <p class="text-[11px] text-indigo-700">Klien dapat meninjau butir hasil pengujian ini melalui portal projek mereka dan membubuhkan tanda tangan digital.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 1. READ-ONLY PREVIEW MODE (Tampil jika sudah ada UAT dan tidak sedang mode edit) --}}
            <div x-show="!isEditMode && {{ $hasUat ? 'true' : 'false' }}" class="space-y-6">

                {{-- Summary Header Card --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="border-b md:border-b-0 md:border-r border-slate-100 pb-4 md:pb-0 md:pr-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Sesi Pengujian</p>
                        <p class="text-lg font-bold text-[#0B254A] mt-1">
                            {{ $initialSessionDate ? \Carbon\Carbon::parse($initialSessionDate)->translatedFormat('d F Y') : '-' }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Sesi UAT bersama mitra</p>
                    </div>

                    <div class="border-b md:border-b-0 md:border-r border-slate-100 pb-4 md:pb-0 md:pr-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hasil Evaluasi Keseluruhan</p>
                        <div class="mt-1">
                            @if($initialOverallResult === 'PASSED')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                    PASSED (Diterima Penuh)
                                </span>
                            @elseif($initialOverallResult === 'PARTIAL')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                                    PARTIAL (Diterima Bersyarat)
                                </span>
                            @elseif($initialOverallResult === 'FAILED')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                    FAILED (Tidak Lulus Pengujian)
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Status verifikasi kelayakan sistem</p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Catatan Pengujian Klien</p>
                        <p class="text-xs text-slate-700 mt-1 leading-relaxed whitespace-pre-line italic">
                            "{{ $initialClientNotes ?: 'Tidak ada catatan tambahan dari klien.' }}"
                        </p>
                    </div>
                </div>

                {{-- Table of Test Items --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-[#1E293B] text-sm">Butir Pengujian Fitur (UAT Items)</h3>
                            <p class="text-[11px] text-slate-400">Total {{ count($initialItems) }} fitur &amp; skenario telah diverifikasi</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-100">
                                    <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-6 py-3 w-12 text-center">No</th>
                                    <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Nama Fitur</th>
                                    <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Skenario Pengujian</th>
                                    <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Hasil</th>
                                    <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Deskripsi Temuan / Bug</th>
                                    <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-6 py-3">Umpan Balik Klien</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($initialItems as $idx => $it)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-3.5 text-center text-xs font-bold text-slate-400">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-3.5 font-bold text-slate-900 text-xs">{{ $it['feature_name'] }}</td>
                                        <td class="px-4 py-3.5 text-xs text-slate-600 max-w-xs">{{ $it['test_scenario'] ?: '-' }}</td>
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            @if($it['result'] === 'PASSED')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    ✓ PASSED
                                                </span>
                                            @elseif($it['result'] === 'BUG_FOUND')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    ⚠ BUG DITEMUKAN
                                                </span>
                                            @elseif($it['result'] === 'FAILED')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    ✕ FAILED
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-xs">
                                            @if($it['result'] === 'BUG_FOUND' || $it['result'] === 'FAILED')
                                                <div class="space-y-1">
                                                    @if(!empty($it['bug_severity']))
                                                        <span class="inline-block text-[9.5px] font-extrabold uppercase tracking-wide px-2 py-0.5 rounded {{ $it['bug_severity'] === 'CRITICAL' ? 'bg-rose-100 text-rose-800' : ($it['bug_severity'] === 'MAJOR' ? 'bg-orange-100 text-orange-800' : 'bg-slate-100 text-slate-700') }}">
                                                            Severity: {{ $it['bug_severity'] }}
                                                        </span>
                                                    @endif
                                                    <p class="text-slate-700 leading-snug">{{ $it['bug_description'] ?: '-' }}</p>
                                                </div>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5 text-xs text-slate-600 leading-snug max-w-xs">
                                            {{ $it['client_feedback'] ?: '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-xs text-slate-400">Belum ada item pengujian.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- 2. INTERACTIVE FORM EDITOR (POST if new, PUT if update) --}}
            <div x-show="isEditMode || {{ !$hasUat ? 'true' : 'false' }}">

                @if(!$isTesting && !$hasUat)
                    {{-- Proyek belum testing: Harus tandai testing dulu sebelum formulir UAT dapat dibuat --}}
                    <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-sm">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-sm font-bold text-amber-900">Alur Pengujian: Proyek Belum Masuk Fase Testing</h3>
                                <p class="text-xs text-amber-800 leading-relaxed max-w-2xl">
                                    Sesuai alur sistem TeFa, proyek harus terlebih dahulu ditandai masuk fase <strong>Testing (Status ID = 8)</strong> sebelum formulir sesi UAT dapat dibuat dan diisi.
                                </p>
                            </div>
                        </div>

                        <form action="{{ route('pengurus-tefa.uat.status-testing', $proposalId) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <input type="hidden" name="next" value="create_uat">
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-sm shadow-amber-600/30 transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                                <span>Tandai Masuk Fase Testing Sekarang</span>
                            </button>
                        </form>
                    </div>
                @endif

                <form method="POST"
                      action="{{ $hasUat ? route('pengurus-tefa.uat.update', $proposalId) : route('pengurus-tefa.uat.store', $proposalId) }}"
                      class="space-y-6">
                    @csrf
                    @if($hasUat)
                        @method('PUT')
                    @endif

                    {{-- Form Header Section --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div>
                                <h3 class="font-bold text-[#1E293B] text-base">
                                    {{ $hasUat ? 'Formulir Revisi Lembar UAT (PUT /proposals/{id}/uat)' : 'Formulir Buat Sesi UAT (POST /proposals/{id}/uat)' }}
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Tentukan tanggal sesi, hasil keseluruhan, serta input seluruh butir verifikasi.</p>
                            </div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wide px-2.5 py-1 rounded-md bg-blue-50 text-[#0B3B82]">
                                Sesi UAT Digital
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Tanggal Sesi --}}
                            <div>
                                <label for="session_date" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                    Tanggal Sesi Pengujian <span class="text-red-500">*</span>
                                </label>
                                <input type="date"
                                       id="session_date"
                                       name="session_date"
                                       required
                                       x-model="sessionDate"
                                       class="w-full text-xs font-medium border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/15 bg-white">
                                <p class="text-[10.5px] text-slate-400 mt-1">Tanggal diselenggarakannya sesi demo & pengujian bersama klien.</p>
                            </div>

                            {{-- Hasil Keseluruhan (overall_result) --}}
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                    Hasil Evaluasi Keseluruhan (Overall Result) <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-3 gap-2">
                                    <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition select-none"
                                           :class="overallResult === 'PASSED' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                        <input type="radio" name="overall_result" value="PASSED" x-model="overallResult" class="sr-only">
                                        <span class="text-xs">PASSED</span>
                                        <span class="text-[9.5px] font-normal opacity-80 mt-0.5">Lulus Penuh</span>
                                    </label>

                                    <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition select-none"
                                           :class="overallResult === 'PARTIAL' ? 'border-amber-500 bg-amber-50 text-amber-800 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                        <input type="radio" name="overall_result" value="PARTIAL" x-model="overallResult" class="sr-only">
                                        <span class="text-xs">PARTIAL</span>
                                        <span class="text-[9.5px] font-normal opacity-80 mt-0.5">Bersyarat</span>
                                    </label>

                                    <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition select-none"
                                           :class="overallResult === 'FAILED' ? 'border-rose-500 bg-rose-50 text-rose-800 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                        <input type="radio" name="overall_result" value="FAILED" x-model="overallResult" class="sr-only">
                                        <span class="text-xs">FAILED</span>
                                        <span class="text-[9.5px] font-normal opacity-80 mt-0.5">Tidak Lulus</span>
                                    </label>
                                </div>
                                <p class="text-[10.5px] text-slate-400 mt-1">Jika PASSED, sign-off klien akan otomatis mengalihkan proyek ke Handover.</p>
                            </div>

                            {{-- Catatan Pengujian Klien --}}
                            <div class="md:col-span-2">
                                <label for="client_notes" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                    Catatan Umum Sesi UAT (Client Notes)
                                </label>
                                <textarea id="client_notes"
                                          name="client_notes"
                                          rows="3"
                                          x-model="clientNotes"
                                          placeholder="Tuliskan catatan rangkuman sesi pengujian, feedback umum, atau hal yang perlu dicatat bersama..."
                                          class="w-full text-xs font-medium border border-slate-200 rounded-xl p-3.5 focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/15 bg-white"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Repeater Items --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                            <div>
                                <h3 class="font-bold text-[#1E293B] text-base">Butir-Butir Pengujian Fitur (Items)</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Daftarkan fitur yang diuji, skenario uji, serta hasil temuan bug bila ada.</p>
                            </div>

                            <div class="flex items-center gap-2">
                                @if(!empty($modules))
                                    <button type="button"
                                            @click="importModules()"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border border-[#0B3B82] text-[#0B3B82] hover:bg-[#EAF3FF] transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                        </svg>
                                        Impor dari Modul Proyek ({{ count($modules) }})
                                    </button>
                                @endif

                                <button type="button"
                                        @click="addItem()"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#0B3B82] text-white hover:bg-[#082a5c] transition shadow-2xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                    </svg>
                                    Tambah Butir Fitur
                                </button>
                            </div>
                        </div>

                        {{-- Item list --}}
                        <div class="space-y-4">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5 space-y-4 relative transition hover:border-slate-300">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-6 h-6 rounded-lg bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center font-bold text-xs"
                                                  x-text="index + 1"></span>
                                            <span class="text-xs font-bold text-slate-700">Butir Pengujian</span>
                                        </div>

                                        <button type="button"
                                                @click="removeItem(index)"
                                                x-show="items.length > 1"
                                                title="Hapus butir ini"
                                                class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        {{-- feature_name --}}
                                        <div class="md:col-span-5">
                                            <label class="block text-[10.5px] font-bold text-slate-500 uppercase tracking-wide mb-1">
                                                Nama Fitur <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text"
                                                   :name="'items[' + index + '][feature_name]'"
                                                   x-model="item.feature_name"
                                                   required
                                                   placeholder="Contoh: Modul Login & RBAC"
                                                   class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-[#0B3B82] bg-white">
                                        </div>

                                        {{-- result --}}
                                        <div class="md:col-span-3">
                                            <label class="block text-[10.5px] font-bold text-slate-500 uppercase tracking-wide mb-1">
                                                Hasil Pengujian <span class="text-red-500">*</span>
                                            </label>
                                            <select :name="'items[' + index + '][result]'"
                                                    x-model="item.result"
                                                    required
                                                    class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-[#0B3B82] bg-white">
                                                <option value="PASSED">✓ PASSED</option>
                                                <option value="BUG_FOUND">⚠ BUG_FOUND</option>
                                                <option value="FAILED">✕ FAILED</option>
                                            </select>
                                        </div>

                                        {{-- test_scenario --}}
                                        <div class="md:col-span-4">
                                            <label class="block text-[10.5px] font-bold text-slate-500 uppercase tracking-wide mb-1">
                                                Skenario Pengujian
                                            </label>
                                            <input type="text"
                                                   :name="'items[' + index + '][test_scenario]'"
                                                   x-model="item.test_scenario"
                                                   placeholder="Contoh: Pengujian login akun multi-role"
                                                   class="w-full text-xs font-medium border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-[#0B3B82] bg-white">
                                        </div>

                                        {{-- Bug Severity & Description (Conditional when BUG_FOUND or FAILED) --}}
                                        <template x-if="item.result === 'BUG_FOUND' || item.result === 'FAILED'">
                                            <div class="md:col-span-12 grid grid-cols-1 md:grid-cols-12 gap-4 bg-amber-50/70 p-3.5 rounded-xl border border-amber-200">
                                                <div class="md:col-span-4">
                                                    <label class="block text-[10px] font-bold text-amber-900 uppercase tracking-wide mb-1">
                                                        Tingkat Keparahan Bug (Severity)
                                                    </label>
                                                    <select :name="'items[' + index + '][bug_severity]'"
                                                            x-model="item.bug_severity"
                                                            class="w-full text-xs font-bold border border-amber-300 rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-amber-600 bg-white text-slate-800">
                                                        <option value="MINOR">MINOR (Kecil / Kosmetik)</option>
                                                        <option value="MAJOR">MAJOR (Fungsi Terganggu)</option>
                                                        <option value="CRITICAL">CRITICAL (Sistem Macet)</option>
                                                    </select>
                                                </div>
                                                <div class="md:col-span-8">
                                                    <label class="block text-[10px] font-bold text-amber-900 uppercase tracking-wide mb-1">
                                                        Deskripsi Bug / Kendala yang Ditemukan
                                                    </label>
                                                    <input type="text"
                                                           :name="'items[' + index + '][bug_description]'"
                                                           x-model="item.bug_description"
                                                           placeholder="Uraikan bug yang muncul saat pengujian..."
                                                           class="w-full text-xs border border-amber-300 rounded-lg px-3 py-1.5 focus:outline-none focus:border-amber-600 bg-white">
                                                </div>
                                            </div>
                                        </template>

                                        {{-- client_feedback --}}
                                        <div class="md:col-span-12">
                                            <label class="block text-[10.5px] font-bold text-slate-500 uppercase tracking-wide mb-1">
                                                Umpan Balik Klien (Client Feedback)
                                            </label>
                                            <input type="text"
                                                   :name="'items[' + index + '][client_feedback]'"
                                                   x-model="item.client_feedback"
                                                   placeholder="Komentar atau ekspektasi klien terkait fitur ini..."
                                                   class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-[#0B3B82] bg-white">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Sticky Action Bar --}}
                    <div class="sticky bottom-0 -mx-8 px-8 py-4 bg-white/95 backdrop-blur border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 z-20 shadow-lg">
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Pastikan seluruh butir fitur telah diverifikasi bersama klien sebelum menyimpan.</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('pengurus-tefa.uat') }}"
                               class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                                Batal
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-[#0B3B82] hover:bg-[#082a5c] px-6 py-2.5 text-xs font-bold text-white shadow-sm shadow-[#0B3B82]/25 transition cursor-pointer">
                                <span>{{ $hasUat ? 'Simpan Perubahan UAT (PUT)' : 'Terbitkan Sesi UAT (POST)' }}</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                </form>
            </div>

        </main>
    </div>

</div>

</body>
</html>
