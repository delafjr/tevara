<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Persetujuan Proposal & Pembentukan Proyek - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user');
    $proposal = is_array($proposal ?? null) ? $proposal : [];
    $pic = is_array($proposal['pic'] ?? null) ? $proposal['pic'] : [];
    $category = (string) ($proposal['category_name'] ?? 'Other');
    $status = (string) ($proposal['status'] ?? 'Submitted');

    $categoryStyle = [
        'web'     => 'bg-blue-50 text-blue-700',
        'mobile'  => 'bg-purple-50 text-purple-700',
        'desktop' => 'bg-indigo-50 text-indigo-700',
        'iot'     => 'bg-amber-50 text-amber-700',
        'other'   => 'bg-slate-100 text-slate-600',
    ];

    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $formatDate = fn ($value) => $value
        ? \Carbon\Carbon::parse($value)->translatedFormat('d M Y')
        : '—';

    $estimatedBudget = (float) ($proposal['estimated_budget'] ?? 0);
    $initialTotalAmount = $estimatedBudget > 0 ? $estimatedBudget : 25000000;
    $defaultStartDate = date('Y-m-d');
    $defaultTerm1DueDate = date('Y-m-d', strtotime('+1 week'));
    $defaultDeadline = !empty($proposal['desired_deadline']) 
        ? date('Y-m-d', strtotime((string)$proposal['desired_deadline'])) 
        : date('Y-m-d', strtotime('+3 months'));
    $midTimestamp = (strtotime($defaultTerm1DueDate) + strtotime($defaultDeadline)) / 2;
    $defaultTerm2DueDate = date('Y-m-d', (int) $midTimestamp);
    $proposalId = (int) ($proposal['id'] ?? 0);
    $defaultSpkNumber = !empty($proposal['spk_number'])
        ? $proposal['spk_number']
        : 'SPK/TEFA/' . date('Y') . '/' . str_pad((string) $proposalId, 3, '0', STR_PAD_LEFT);
@endphp

<div class="flex min-h-screen">
    <x-sidebar active="validasi-proposal" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 pb-36 space-y-7"
              x-data="approvalForm({
                  initialAmount: {{ $initialTotalAmount }},
                  defaultSpk: '{{ $defaultSpkNumber }}',
                  defaultScope: {{ json_encode($proposal['background'] ?? 'Pengembangan sistem software dan implementasi modul sesuai kebutuhan klien.') }},
                  defaultStart: '{{ $defaultStartDate }}',
                  defaultEnd: '{{ $defaultDeadline }}',
                  defaultTerm1Due: '{{ $defaultTerm1DueDate }}',
                  defaultTerm2Due: '{{ $defaultTerm2DueDate }}',
                  dosens: {{ json_encode($dosens ?? []) }},
                  mahasiswas: {{ json_encode($mahasiswas ?? []) }},
                  roles: {{ json_encode($projectRoles ?? []) }},
                  defaultWarranty: {{ (int) ($proposal['warranty_months'] ?? 3) }}
              })">

            {{-- Breadcrumb & Code --}}
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('pengurus-tefa.validasi-proposal.detail', $proposalId) }}"
                   class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82] transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke Detail Proposal
                </a>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Tahap Persetujuan &amp; SPK
                    </span>
                    @if (!empty($proposal['proposal_code']))
                        <span class="text-[11px] font-mono font-semibold text-slate-500 bg-white border border-slate-200 px-3 py-1 rounded-lg">
                            {{ $proposal['proposal_code'] }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div role="status" class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <p class="text-xs font-semibold leading-relaxed">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div role="alert" class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    <p class="text-xs font-semibold leading-relaxed">{{ session('error') }}</p>
                </div>
            @endif
            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-xs text-red-600 space-y-1">
                    <p class="font-bold">Mohon periksa kembali isian formulir:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Hero Header --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#E9F9EE] opacity-70 pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></span>
                        Validasi Proposal · Tindakan Setujui
                    </span>
                    <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                        Persetujuan Proposal &amp; <span class="text-[#16A34A]">Pembentukan Proyek</span>
                    </h1>
                    <p class="text-sm text-slate-500 mt-1.5 max-w-2xl leading-relaxed">
                        Lengkapi dokumen surat perjanjian kerja sama (SPK), sepakati nilai kontrak dan skema termin pembayaran, serta tunjuk Project Manager dan mahasiswa pengembang TeFa.
                    </p>
                </div>

                <div class="relative z-10 flex-shrink-0 hidden md:block">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-xs">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Context Card (Ringkasan Proposal) --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-5">
                <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg {{ $categoryStyle[strtolower($category)] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ $category }}
                        </span>
                        <h2 class="text-base font-bold text-[#1E293B]">{{ $proposal['title'] ?? '-' }}</h2>
                    </div>
                    <div class="text-xs text-slate-400">
                        Diajukan oleh: <span class="font-semibold text-slate-700">{{ $proposal['client_name'] ?? $pic['name'] ?? 'Mitra Klien' }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4">
                    <div>
                        <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Kode Proposal</p>
                        <p class="text-xs font-semibold text-slate-800 mt-0.5 font-mono">{{ $proposal['proposal_code'] ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Estimasi Anggaran Klien</p>
                        <p class="text-xs font-bold text-[#0B3B82] mt-0.5">{{ $formatRupiah($estimatedBudget) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Deadline Permintaan</p>
                        <p class="text-xs font-semibold text-slate-800 mt-0.5">{{ $formatDate($proposal['desired_deadline'] ?? null) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">PIC Mitra</p>
                        <p class="text-xs font-semibold text-slate-800 mt-0.5 truncate">{{ $pic['name'] ?? '-' }} ({{ $pic['no_hp'] ?? '-' }})</p>
                    </div>
                </div>
            </div>

            {{-- Main Form --}}
            <form id="approvalProjectForm"
                  action="{{ route('pengurus-tefa.validasi-proposal.setujui.store', $proposalId) }}"
                  method="POST"
                  @submit="prepareSubmit"
                  class="space-y-7">
                @csrf

                {{-- Hidden Inputs for serialized Alpine state --}}
                <input type="hidden" name="terms" :value="JSON.stringify(terms)">
                <input type="hidden" name="members" :value="JSON.stringify(teamMembers)">

                {{-- ======================================================== --}}
                {{-- SECTION 1: SURAT PERJANJIAN & KONTRAK (SPK)              --}}
                {{-- ======================================================== --}}
                <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-7 space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                        <span class="w-10 h-10 rounded-xl bg-blue-50 text-[#0B3B82] flex items-center justify-center font-bold text-sm ring-1 ring-[#0B3B82]/10">
                            01
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-[#1E293B]">Surat Perjanjian Kerja Sama &amp; SPK</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Tentukan ruang lingkup kerja serta periode pengerjaan. Dokumen SPK akan digenerate otomatis oleh sistem.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Nomor SPK & Scope --}}
                        <div class="space-y-5">
                            <div>
                                <label for="spk_number" class="block text-xs font-bold text-[#1E293B] mb-1.5">
                                    Nomor Dokumen SPK / Perjanjian <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    </span>
                                    <input type="text"
                                           id="spk_number"
                                           name="spk_number"
                                           x-model="spkNumber"
                                           required
                                           placeholder="Contoh: SPK/TEFA/2026/001"
                                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs text-[#1E293B] font-mono placeholder:text-slate-400 focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition">
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">Dapat disesuaikan dengan nomor surat resmi tata usaha TeFa Polines.</p>
                            </div>

                            <div>
                                <label for="scope_of_work" class="block text-xs font-bold text-[#1E293B] mb-1.5">
                                    Ruang Lingkup Pekerjaan (Scope of Work) <span class="text-red-500">*</span>
                                </label>
                                <textarea id="scope_of_work"
                                          name="scope_of_work"
                                          rows="4"
                                          x-model="scopeOfWork"
                                          required
                                          placeholder="Uraikan ruang lingkup pengerjaan sistem, batasan modul, dan deliverable yang disepakati..."
                                          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs text-[#1E293B] placeholder:text-slate-400 focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition leading-relaxed"></textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label for="start_date" class="block text-xs font-bold text-[#1E293B] mb-1.5">
                                        Tanggal Mulai <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date"
                                           id="start_date"
                                           name="start_date"
                                           x-model="startDate"
                                           required
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-[#1E293B] focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition">
                                </div>
                                <div>
                                    <label for="target_completion_date" class="block text-xs font-bold text-[#1E293B] mb-1.5">
                                        Target Selesai <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date"
                                           id="target_completion_date"
                                           name="target_completion_date"
                                           x-model="targetCompletionDate"
                                           required
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-[#1E293B] focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition">
                                </div>
                                <div>
                                    <label for="maintenance_warranty_months" class="block text-xs font-bold text-[#1E293B] mb-1.5">
                                        Garansi (Bulan)
                                    </label>
                                    <select id="maintenance_warranty_months"
                                            name="maintenance_warranty_months"
                                            x-model="maintenanceWarrantyMonths"
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-[#1E293B] focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition bg-white">
                                        <option value="1">1 Bulan</option>
                                        <option value="3">3 Bulan (Standar)</option>
                                        <option value="6">6 Bulan</option>
                                        <option value="12">12 Bulan (1 Tahun)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Info: Dokumen SPK digenerate otomatis --}}
                        <div class="flex flex-col">
                            <label class="block text-xs font-bold text-[#1E293B] mb-1.5">
                                Berkas Surat Perjanjian / Kontrak (SPK)
                            </label>

                            <div class="flex-1 flex flex-col justify-center items-center border-2 border-dashed border-emerald-200 rounded-2xl p-6 text-center bg-emerald-50/30">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 ring-1 ring-emerald-200">
                                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-emerald-700">Dokumen SPK akan digenerate otomatis</p>
                                <p class="text-[11px] text-slate-500 mt-1 max-w-xs leading-relaxed">
                                    Sistem akan membuat dokumen DOCX berdasarkan data yang diisi di formulir ini. File dapat diunduh setelah persetujuan selesai.
                                </p>
                            </div>

                            <p class="text-[11px] text-slate-400 mt-2">
                                Dokumen ini menjadi rujukan hukum dan bukti kesepakatan formal antara Politeknik Negeri Semarang (TeFa) dan mitra industri.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- ======================================================== --}}
                {{-- SECTION 2: BIAYA YANG DISEPAKATI & TERMIN PEMBAYARAN     --}}
                {{-- ======================================================== --}}
                <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-7 space-y-6">
                    <div class="flex items-center justify-between gap-4 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-amber-50 text-[#FFB800] flex items-center justify-center font-bold text-sm ring-1 ring-amber-500/10">
                                02
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-[#1E293B]">Biaya yang Disepakati &amp; Skema Termin</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Tentukan nilai kontrak final dan bagi ke dalam tahapan pembayaran (termin).</p>
                            </div>
                        </div>

                        {{-- Total Badge --}}
                        <div class="text-right">
                            <span class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Total Kontrak</span>
                            <p class="text-lg font-bold text-[#0B3B82]" x-text="formatRupiah(totalAmount)"></p>
                        </div>
                    </div>

                    {{-- Input Biaya yang Disepakati --}}
                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex-1 max-w-md">
                                <label for="total_amount" class="block text-xs font-bold text-[#1E293B] mb-1.5">
                                    Total Nilai Biaya yang Disepakati (Rp) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">
                                        Rp
                                    </span>
                                    <input type="number"
                                           id="total_amount"
                                           name="total_amount"
                                           x-model.number="totalAmount"
                                           @input="onTotalAmountChange"
                                           min="0"
                                           step="1000"
                                           required
                                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm text-[#1E293B] font-bold focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition bg-white">
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button"
                                        @click="setEstimatedBudget({{ $estimatedBudget }})"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0B3B82] bg-blue-50 hover:bg-blue-100 px-3.5 py-2 rounded-xl transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                    Pakai Estimasi Klien ({{ $formatRupiah($estimatedBudget) }})
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Termin List --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-xs font-bold text-[#1E293B] uppercase tracking-wide">Rincian Termin Pembayaran</h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">Persentase dan nominal termin terhitung otomatis dan dapat diubah secara interaktif.</p>
                            </div>
                            <button type="button"
                                    @click="addTerm"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0B3B82] bg-white border border-slate-200 hover:border-[#0B3B82] hover:bg-blue-50/50 px-3.5 py-1.5 rounded-xl transition-all shadow-2xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                Tambah Termin
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(term, index) in terms" :key="term.id">
                                <div class="bg-white border rounded-2xl p-4 sm:p-5 transition-all shadow-2xs"
                                     :class="!term.payment_date 
                                        ? 'border-amber-300 border-l-4 border-l-amber-500 bg-amber-50/15' 
                                        : 'border-slate-200/90 hover:border-slate-300'">
                                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                        {{-- Title & Info --}}
                                        <div class="flex items-center gap-3 min-w-[200px]">
                                            <span class="w-8 h-8 rounded-lg font-bold text-xs flex items-center justify-center flex-shrink-0"
                                                  :class="index === 0 
                                                     ? 'bg-blue-50 text-blue-700' 
                                                     : (index === terms.length - 1 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700')"
                                                  x-text="index + 1">
                                            </span>
                                            <div class="flex-1">
                                                <input type="text"
                                                       x-model="term.name"
                                                       @input="term.customName = true"
                                                       placeholder="Nama termin (misal: Termin 1 - Uang Muka)"
                                                       class="w-full text-xs font-bold text-[#1E293B] border-b border-transparent hover:border-slate-300 focus:border-[#0B3B82] focus:outline-none pb-0.5 transition">
                                                <input type="text"
                                                       x-model="term.notes"
                                                       placeholder="Syarat pencairan (misal: Setelah SPK ditandatangani)"
                                                       class="w-full text-[11px] text-slate-400 border-b border-transparent hover:border-slate-300 focus:border-[#0B3B82] focus:outline-none pt-0.5 transition">
                                            </div>
                                        </div>

                                        {{-- Inputs: % & Amount & Date --}}
                                        <div class="flex flex-wrap items-center gap-3">
                                            {{-- Percentage with Lock Toggle --}}
                                            <div class="w-28">
                                                <div class="flex items-center justify-between mb-1">
                                                    <label class="block text-[10px] font-bold text-slate-400">Porsi (%)</label>
                                                    <button type="button"
                                                            @click="toggleLock(index)"
                                                            :title="term.locked ? 'Terkunci: persentase ini tidak diubah saat ratakan alokasi' : 'Bebas: persentase ini akan ikut disesuaikan saat ratakan alokasi'"
                                                            class="inline-flex items-center gap-0.5 text-[9px] px-1 py-0.5 rounded transition cursor-pointer"
                                                            :class="term.locked ? 'bg-amber-100 text-amber-800 font-bold' : 'text-slate-300 hover:text-slate-500'">
                                                        <span x-show="term.locked">🔒 Kunci</span>
                                                        <span x-show="!term.locked">🔓 Bebas</span>
                                                    </button>
                                                </div>
                                                <div class="relative">
                                                    <input type="number"
                                                           x-model.number="term.percentage"
                                                           @input="onTermPercentageChange(index)"
                                                           min="1"
                                                           max="100"
                                                           step="1"
                                                           :class="term.locked ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200'"
                                                           class="w-full pr-6 pl-3 py-1.5 rounded-lg border text-xs font-bold text-[#1E293B] focus:outline-none focus:border-[#0B3B82] transition">
                                                    <span class="absolute inset-y-0 right-0 pr-2 flex items-center text-xs text-slate-400 pointer-events-none">%</span>
                                                </div>
                                            </div>

                                            {{-- Amount --}}
                                            <div class="w-40">
                                                <label class="block text-[10px] font-bold text-slate-400 mb-1">Nominal (Rp)</label>
                                                <input type="number"
                                                       x-model.number="term.amount"
                                                       @input="onTermAmountChange(index)"
                                                       min="0"
                                                       step="1000"
                                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-[#0B3B82] focus:outline-none focus:border-[#0B3B82] transition">
                                            </div>

                                            {{-- Date (Highlighted if empty) --}}
                                            <div class="w-44">
                                                <div class="flex items-center justify-between mb-1">
                                                    <label class="block text-[10px] font-bold text-slate-400">Jatuh Tempo</label>
                                                    <span x-show="!term.payment_date"
                                                          class="inline-flex items-center gap-1 text-[9px] font-bold text-amber-700 bg-amber-100 border border-amber-300 px-1.5 py-0.5 rounded-full">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                        Belum diisi
                                                    </span>
                                                    <span x-show="term.payment_date" class="text-[9px] font-bold text-emerald-600">
                                                        ✓ Terisi
                                                    </span>
                                                </div>
                                                <input type="date"
                                                       x-model="term.payment_date"
                                                       :class="!term.payment_date 
                                                          ? 'border-amber-400 bg-amber-50/70 text-amber-900 ring-2 ring-amber-400/30' 
                                                          : 'border-slate-200 bg-white text-slate-700'"
                                                       class="w-full px-2.5 py-1.5 rounded-lg border text-xs font-medium focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition">
                                                <p x-show="!term.payment_date" class="text-[10px] text-amber-600 font-semibold mt-1">
                                                    ⚠️ Wajib diisi
                                                </p>
                                            </div>

                                            {{-- Delete Button --}}
                                            <div class="pt-4">
                                                <button type="button"
                                                        @click="removeTerm(index)"
                                                        :disabled="terms.length <= 1"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 disabled:opacity-30 disabled:pointer-events-none transition-colors"
                                                        title="Hapus termin">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Alert jika ada deadline jatuh tempo belum diisi --}}
                        <template x-if="hasEmptyDueDate()">
                            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5 text-xs text-amber-800 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                                    </svg>
                                    <span>
                                        Perhatian: Terdapat <strong x-text="emptyDueDateCount() + ' termin'"></strong> yang belum memiliki tanggal jatuh tempo.
                                    </span>
                                </div>
                                <button type="button"
                                        @click="fillDefaultDueDates"
                                        class="text-[11px] font-bold text-[#0B3B82] bg-white border border-slate-200 hover:bg-blue-50 px-3 py-1 rounded-lg transition shadow-2xs cursor-pointer">
                                    Isi Otomatis (Bertahap)
                                </button>
                            </div>
                        </template>

                        {{-- Calculation Balance Bar --}}
                        <div class="rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border transition-colors"
                             :class="totalTermPercentage() === 100 
                                ? 'bg-emerald-50/70 border-emerald-200 text-emerald-800' 
                                : 'bg-amber-50/70 border-amber-200 text-amber-800'">
                            <div class="flex items-center gap-3">
                                <template x-if="totalTermPercentage() === 100">
                                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </template>
                                <template x-if="totalTermPercentage() !== 100">
                                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                                    </svg>
                                </template>
                                <div class="text-xs">
                                    <span class="font-bold">Total Alokasi Termin: </span>
                                    <span class="font-bold font-mono" x-text="formatRupiah(totalTermAmount())"></span>
                                    (<span class="font-bold" x-text="totalTermPercentage() + '%'"></span>)
                                    <template x-if="totalTermPercentage() === 100">
                                        <span class="ml-2 font-semibold text-emerald-700">✓ Pas 100% dari total nilai kontrak</span>
                                    </template>
                                    <template x-if="totalTermPercentage() < 100">
                                        <span class="ml-2 font-semibold text-amber-700" x-text="'(Kurang ' + (100 - totalTermPercentage()) + '%)'"></span>
                                    </template>
                                    <template x-if="totalTermPercentage() > 100">
                                        <span class="ml-2 font-semibold text-red-700" x-text="'(Lebih ' + (totalTermPercentage() - 100) + '%)'"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="flex items-center gap-2" x-show="totalTermPercentage() !== 100">
                                <button type="button"
                                        @click="adjustToFinalTerm"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-amber-900 bg-amber-100 hover:bg-amber-200 border border-amber-300 px-3 py-1.5 rounded-lg transition-colors cursor-pointer"
                                        title="Sesuaikan kekurangan/kelebihan persentase langsung ke Termin Pelunasan">
                                    Pas-kan ke Pelunasan
                                </button>
                                <button type="button"
                                        @click="rebalanceTerms"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 px-3 py-1.5 rounded-lg transition-colors cursor-pointer"
                                        title="Ratakan sisa persentase ke termin yang tidak dikunci">
                                    Ratakan Sisa Alokasi
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Catatan Ketentuan Pembayaran --}}
                    <div>
                        <label for="payment_terms_description" class="block text-xs font-bold text-[#1E293B] mb-1.5">
                            Keterangan Umum Ketentuan Pembayaran
                        </label>
                        <textarea id="payment_terms_description"
                                  name="payment_terms_description"
                                  rows="2"
                                  placeholder="Catatan rekening tujuan pembayaran TeFa Polines, batas transfer, atau denda keterlambatan..."
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs text-[#1E293B] placeholder:text-slate-400 focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition leading-relaxed"></textarea>
                    </div>
                </section>

                {{-- ======================================================== --}}
                {{-- SECTION 3: PEMBENTUKAN TIM PENGEMBANG (PM & MAHASISWA)   --}}
                {{-- ======================================================== --}}
                <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-7 space-y-6">
                    <div class="flex items-center justify-between gap-4 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-sm ring-1 ring-purple-500/10">
                                03
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-[#1E293B]">Pembentukan Tim Pengembang TeFa</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Tunjuk Dosen Pembimbing sebagai Project Manager (PM) dan bentuk tim mahasiswa pelaksana proyek.</p>
                            </div>
                        </div>

                        {{-- Total Team Member Badge --}}
                        <div class="text-right">
                            <p class="text-sm font-bold text-[#1E293B]">
                                1 PM + <span class="text-[#0B3B82]" x-text="teamMembers.length"></span> Mahasiswa
                            </p>
                        </div>
                    </div>

                    {{-- SUB-SECTION: PEMILIHAN PROJECT MANAGER (DOSEN) --}}
                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-5 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <label for="pm_id" class="block text-xs font-bold text-[#1E293B]">
                                    Project Manager (Dosen TeFa) <span class="text-red-500">*</span>
                                </label>
                                <p class="text-[11px] text-slate-400">Dosen bertanggung jawab mengawasi sprint, me-review task, dan validasi BAST.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                            <div>
                                <select id="pm_id"
                                        name="pm_id"
                                        x-model.number="selectedPmId"
                                        required
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs text-[#1E293B] font-semibold focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition bg-white">
                                    <option value="">-- Pilih Dosen Project Manager --</option>
                                    @foreach ($dosens as $dosen)
                                        <option value="{{ $dosen['id'] }}">{{ $dosen['name'] }} ({{ $dosen['nidn'] ?? $dosen['email'] }})</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Selected PM Card Preview --}}
                            <div>
                                <template x-if="selectedPm">
                                    <div class="flex items-center gap-3 bg-white border border-slate-200/90 rounded-xl px-4 py-2.5 shadow-2xs">
                                        <div class="w-10 h-10 rounded-xl bg-[#0B3B82] text-white font-bold text-xs flex items-center justify-center flex-shrink-0"
                                             x-text="getInitials(selectedPm.name)">
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-[#1E293B] truncate" x-text="selectedPm.name"></p>
                                            <p class="text-[11px] text-slate-400 truncate" x-text="selectedPm.email"></p>
                                        </div>
                                        <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-100 flex-shrink-0">
                                            Project Manager
                                        </span>
                                    </div>
                                </template>
                                <template x-if="!selectedPm">
                                    <p class="text-xs text-slate-400 italic">Belum ada dosen yang dipilih sebagai PM proyek.</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- SUB-SECTION: PEMILIHAN ANGGOTA MAHASISWA & ROLE --}}
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h3 class="text-xs font-bold text-[#1E293B] uppercase tracking-wide">Daftar Anggota Mahasiswa &amp; Peran</h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">Pilih mahasiswa dan tetapkan role teknis mereka dalam pengembangan sistem.</p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button"
                                        @click="addMemberWithRole(1)"
                                        class="text-[11px] font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 px-2.5 py-1 rounded-lg transition-colors">
                                    + UI/UX
                                </button>
                                <button type="button"
                                        @click="addMemberWithRole(2)"
                                        class="text-[11px] font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-lg transition-colors">
                                    + Frontend
                                </button>
                                <button type="button"
                                        @click="addMemberWithRole(3)"
                                        class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-lg transition-colors">
                                    + Backend
                                </button>
                                <button type="button"
                                        @click="addMemberWithRole(5)"
                                        class="text-[11px] font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 px-2.5 py-1 rounded-lg transition-colors">
                                    + QA Tester
                                </button>
                                <button type="button"
                                        @click="addMember"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0B3B82] bg-white border border-slate-200 hover:border-[#0B3B82] hover:bg-blue-50/50 px-3 py-1.5 rounded-xl transition-all shadow-2xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Tambah Mahasiswa
                                </button>
                            </div>
                        </div>

                        {{-- Mahasiswa Rows --}}
                        <div class="space-y-3">
                            <template x-for="(member, mIndex) in teamMembers" :key="member.id">
                                <div class="bg-white border border-slate-200/90 rounded-2xl p-4 hover:border-slate-300 transition-all shadow-2xs">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                        {{-- Student Selector --}}
                                        <div class="flex items-center gap-3 flex-1 min-w-[280px]">
                                            <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center flex-shrink-0"
                                                  x-text="mIndex + 1">
                                            </span>
                                            <div class="flex-1">
                                                <label class="block text-[10px] font-bold text-slate-400 mb-1">Pilih Mahasiswa</label>
                                                <select x-model.number="member.student_id"
                                                        required
                                                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-[#1E293B] font-medium focus:outline-none focus:border-[#0B3B82] transition bg-white">
                                                    <option value="">-- Pilih Mahasiswa --</option>
                                                    @foreach ($mahasiswas as $mhs)
                                                        <option value="{{ $mhs['id'] }}">{{ $mhs['name'] }} ({{ $mhs['nim'] ?? $mhs['study_program'] ?? $mhs['email'] }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Role Selector --}}
                                        <div class="w-full md:w-56">
                                            <label class="block text-[10px] font-bold text-slate-400 mb-1">Peran / Role Teknis</label>
                                            <select x-model.number="member.role_id"
                                                    required
                                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-[#1E293B] font-semibold focus:outline-none focus:border-[#0B3B82] transition bg-white">
                                                @foreach ($projectRoles as $role)
                                                    <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Joined At --}}
                                        <div class="w-full md:w-40">
                                            <label class="block text-[10px] font-bold text-slate-400 mb-1">Mulai Bergabung</label>
                                            <input type="date"
                                                   x-model="member.joined_at"
                                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:outline-none focus:border-[#0B3B82] transition">
                                        </div>

                                        {{-- Delete Button --}}
                                        <div class="pt-4 md:pt-4">
                                            <button type="button"
                                                    @click="removeMember(mIndex)"
                                                    :disabled="teamMembers.length <= 1"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 disabled:opacity-30 disabled:pointer-events-none transition-colors"
                                                    title="Hapus mahasiswa">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Role Summary Distribution Chips --}}
                        <div class="flex flex-wrap items-center gap-2 pt-2">
                            <span class="text-xs font-bold text-slate-400">Komposisi Tim Terbentuk:</span>
                            <template x-for="r in roles" :key="r.id">
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-slate-200 bg-slate-50 text-slate-700"
                                      x-show="countMembersWithRole(r.id) > 0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                                    <span x-text="r.name + ': ' + countMembersWithRole(r.id)"></span>
                                </span>
                            </template>
                        </div>
                    </div>
                </section>

                {{-- ======================================================== --}}
                {{-- STICKY FLOATING ACTION DOCK                              --}}
                {{-- ======================================================== --}}
                <div class="fixed bottom-0 left-64 right-0 z-30 px-8 pb-5 pointer-events-none">
                    <div class="pointer-events-auto mx-auto max-w-[1500px] overflow-hidden rounded-2xl border border-slate-200/90 bg-white/95 shadow-2xl shadow-slate-900/10 backdrop-blur-md">
                        <div class="h-1 bg-gradient-to-r from-[#0B3B82] via-[#2F6BD8] to-[#16A34A]"></div>

                        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4">
                            {{-- Left side summary badges --}}
                            <div class="flex items-center gap-4 min-w-0">
                                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0 ring-1 ring-emerald-500/10">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-xs font-bold text-[#1E293B]">Finalisasi Persetujuan Proyek</p>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            Status Baru: Aktif / Disetujui
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                                        Total: <span class="font-bold text-slate-700" x-text="formatRupiah(totalAmount)"></span> · 
                                        Termin: <span class="font-bold text-slate-700" x-text="terms.length + ' Tahap'"></span> · 
                                        Tim: <span class="font-bold text-slate-700" x-text="(selectedPm ? '1 PM + ' : '0 PM + ') + teamMembers.length + ' Mahasiswa'"></span>
                                    </p>
                                </div>
                            </div>

                            {{-- Right side buttons --}}
                            <div class="flex items-center gap-3">
                                <a href="{{ route('pengurus-tefa.validasi-proposal.detail', $proposalId) }}"
                                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all">
                                    Batal / Kembali
                                </a>

                                <button type="submit"
                                        :disabled="isSubmitting"
                                        class="inline-flex items-center gap-2 rounded-xl bg-[#16A34A] px-6 py-2.5 text-xs font-bold text-white shadow-sm shadow-green-600/25 hover:bg-[#15803D] hover:shadow-md transition-all cursor-pointer disabled:opacity-50">
                                    <template x-if="!isSubmitting">
                                        <span class="inline-flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                            </svg>
                                            Setujui &amp; Resmikan Proyek
                                        </span>
                                    </template>
                                    <template x-if="isSubmitting">
                                        <span class="inline-flex items-center gap-2">
                                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            Menyimpan &amp; Meresmikan...
                                        </span>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </main>
    </div>
</div>

{{-- Alpine Form Logic --}}
<script>
function approvalForm(config) {
    return {
        totalAmount: config.initialAmount || 0,
        spkNumber: config.defaultSpk || '',
        scopeOfWork: config.defaultScope || '',
        startDate: config.defaultStart || '',
        targetCompletionDate: config.defaultEnd || '',
        maintenanceWarrantyMonths: config.defaultWarranty || 3,

        dosens: config.dosens || [],
        mahasiswas: config.mahasiswas || [],
        roles: config.roles || [],

        selectedPmId: '',
        isSubmitting: false,

        init() {
            this.fillDefaultDueDates();
        },

        // Skema Termin Default: 3 Termin (30%, 40%, 30%)
        // Jatuh tempo Termin 1 default H+1 minggu (7 hari dari sekarang)
        terms: [
            {
                id: 1,
                name: 'Termin 1 - Uang Muka (DP 30%)',
                percentage: 30,
                amount: Math.round((config.initialAmount || 0) * 0.3),
                payment_date: config.defaultTerm1Due || '',
                notes: 'Dibayarkan saat penandatanganan SPK / Kick-off proyek',
                locked: true,
                customName: false
            },
            {
                id: 2,
                name: 'Termin 2 - Progress Sprint Utama (40%)',
                percentage: 40,
                amount: Math.round((config.initialAmount || 0) * 0.4),
                payment_date: config.defaultTerm2Due || '',
                notes: 'Dibayarkan setelah demonstrasi modul utama & progress 50%',
                locked: false,
                customName: false
            },
            {
                id: 3,
                name: 'Termin 3 - Pelunasan & BAST (30%)',
                percentage: 30,
                amount: Math.round((config.initialAmount || 0) * 0.3),
                payment_date: config.defaultEnd || '',
                notes: 'Dibayarkan setelah UAT selesai dan BAST ditandatangani',
                locked: false,
                customName: false
            }
        ],

        // Default Tim Mahasiswa: 2 Slot (Frontend & Backend)
        teamMembers: [
            { id: 1, student_id: '', role_id: 2, joined_at: config.defaultStart || '' },
            { id: 2, student_id: '', role_id: 3, joined_at: config.defaultStart || '' }
        ],

        get selectedPm() {
            if (!this.selectedPmId) return null;
            return this.dosens.find(d => Number(d.id) === Number(this.selectedPmId)) || null;
        },

        getInitials(name) {
            if (!name) return 'DS';
            const parts = name.replace(/^(Prof\.|Dr\.|Ir\.|Drs\.)\s*/gi, '').trim().split(' ');
            if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
            return (parts[0][0] + parts[1][0]).toUpperCase();
        },

        formatRupiah(amount) {
            const num = Number(amount) || 0;
            return 'Rp ' + num.toLocaleString('id-ID');
        },

        setEstimatedBudget(amount) {
            this.totalAmount = Number(amount) || 0;
            this.recalculateAllTermAmounts();
        },

        onTotalAmountChange() {
            this.recalculateAllTermAmounts();
        },

        toggleLock(index) {
            this.terms[index].locked = !this.terms[index].locked;
        },

        onTermPercentageChange(index) {
            const term = this.terms[index];
            term.locked = true;
            term.amount = Math.round((this.totalAmount * (term.percentage || 0)) / 100);
            this.resequenceTerms();
        },

        onTermAmountChange(index) {
            const term = this.terms[index];
            term.locked = true;
            if (this.totalAmount > 0) {
                term.percentage = Math.round(((term.amount || 0) / this.totalAmount) * 100);
            }
            this.resequenceTerms();
        },

        recalculateAllTermAmounts() {
            this.terms.forEach(term => {
                term.amount = Math.round((this.totalAmount * (term.percentage || 0)) / 100);
            });
        },

        totalTermAmount() {
            return this.terms.reduce((sum, t) => sum + (Number(t.amount) || 0), 0);
        },

        totalTermPercentage() {
            return this.terms.reduce((sum, t) => sum + (Number(t.percentage) || 0), 0);
        },

        resequenceTerms() {
            const total = this.terms.length;
            this.terms.forEach((term, idx) => {
                const seq = idx + 1;
                if (!term.customName) {
                    if (idx === 0) {
                        term.name = 'Termin 1 - Uang Muka (DP ' + (term.percentage || 0) + '%)';
                    } else if (idx === total - 1) {
                        term.name = 'Termin ' + seq + ' - Pelunasan & BAST (' + (term.percentage || 0) + '%)';
                    } else {
                        const stageNum = total === 3 ? 'Sprint Utama' : 'Tahap ' + idx;
                        term.name = 'Termin ' + seq + ' - Progress ' + stageNum + ' (' + (term.percentage || 0) + '%)';
                    }
                }
                if (!term.notes || term.notes.startsWith('Syarat') || term.notes.startsWith('Dibayarkan')) {
                    if (idx === 0) {
                        term.notes = 'Dibayarkan saat penandatanganan SPK / Kick-off proyek';
                    } else if (idx === total - 1) {
                        term.notes = 'Dibayarkan setelah UAT selesai dan BAST ditandatangani';
                    } else {
                        const stageNum = total === 3 ? '50%' : 'tahap ' + idx;
                        term.notes = 'Dibayarkan setelah demonstrasi modul utama & progress ' + stageNum;
                    }
                }
            });
        },

        addTerm() {
            // Selalu sisipkan SEBELUM termin terakhir (Pelunasan & BAST)
            const insertIndex = Math.max(1, this.terms.length - 1);
            
            let newPct = Math.max(0, 100 - this.totalTermPercentage());
            
            // Jika total alokasi sudah 100%, kurangi dari termin pelunasan secara proporsional
            if (newPct === 0 && this.terms.length >= 2) {
                const lastTerm = this.terms[this.terms.length - 1];
                if (lastTerm.percentage > 15) {
                    const borrow = 10;
                    lastTerm.percentage -= borrow;
                    lastTerm.amount = Math.round((this.totalAmount * lastTerm.percentage) / 100);
                    newPct = borrow;
                } else {
                    newPct = 10;
                }
            } else if (newPct === 0) {
                newPct = 10;
            }

            const newTerm = {
                id: Date.now(),
                name: '',
                percentage: newPct,
                amount: Math.round((this.totalAmount * newPct) / 100),
                payment_date: '',
                notes: '',
                locked: false,
                customName: false
            };

            this.terms.splice(insertIndex, 0, newTerm);
            this.resequenceTerms();
        },

        removeTerm(index) {
            if (this.terms.length > 1) {
                this.terms.splice(index, 1);
                this.resequenceTerms();
                this.recalculateAllTermAmounts();
            }
        },

        adjustToFinalTerm() {
            if (this.terms.length <= 1) return;
            const otherSum = this.terms.slice(0, -1).reduce((sum, t) => sum + (Number(t.percentage) || 0), 0);
            const remaining = 100 - otherSum;
            if (remaining >= 0) {
                const last = this.terms[this.terms.length - 1];
                last.percentage = remaining;
                last.amount = Math.round((this.totalAmount * remaining) / 100);
                this.resequenceTerms();
            } else {
                alert('Total porsi termin sebelum pelunasan sudah ' + otherSum + '% (melebihi 100%). Kurangi salah satu termin terlebih dahulu.');
            }
        },

        rebalanceTerms() {
            if (this.terms.length === 0) return;

            // Pisahkan termin yang dikunci (manual) vs yang bebas diratakan
            const lockedTerms = this.terms.filter(t => t.locked);
            const unlockedTerms = this.terms.filter(t => !t.locked);

            // Jika ada termin terkunci dan ada termin bebas, bagikan sisa persentase hanya ke termin bebas
            if (lockedTerms.length > 0 && unlockedTerms.length > 0) {
                const lockedSum = lockedTerms.reduce((sum, t) => sum + (Number(t.percentage) || 0), 0);
                const remainingPct = 100 - lockedSum;

                if (remainingPct > 0) {
                    const equalPct = Math.floor(remainingPct / unlockedTerms.length);
                    const remainder = remainingPct - (equalPct * unlockedTerms.length);

                    unlockedTerms.forEach((term, idx) => {
                        term.percentage = idx === unlockedTerms.length - 1 ? equalPct + remainder : equalPct;
                        term.amount = Math.round((this.totalAmount * term.percentage) / 100);
                    });
                    this.resequenceTerms();
                    return;
                }
            }

            // Jika semua termin terkunci, cukup sesuaikan ke termin pelunasan
            if (lockedTerms.length === this.terms.length && this.terms.length > 1) {
                this.adjustToFinalTerm();
                return;
            }

            // Jika tidak ada yang dikunci, ratakan ke semua termin
            const equalPct = Math.floor(100 / this.terms.length);
            const remainder = 100 - (equalPct * this.terms.length);

            this.terms.forEach((term, idx) => {
                term.percentage = idx === 0 ? equalPct + remainder : equalPct;
                term.amount = Math.round((this.totalAmount * term.percentage) / 100);
            });
            this.resequenceTerms();
        },

        hasEmptyDueDate() {
            return this.terms.some(t => !t.payment_date);
        },

        emptyDueDateCount() {
            return this.terms.filter(t => !t.payment_date).length;
        },

        fillDefaultDueDates() {
            const n = this.terms.length;
            if (n === 0) return;

            // Termin 1: H+1 minggu dari sekarang
            if (!this.terms[0].payment_date) {
                this.terms[0].payment_date = config.defaultTerm1Due || '';
            }

            // Termin terakhir: target deadline proyek
            if (n > 1 && !this.terms[n - 1].payment_date) {
                this.terms[n - 1].payment_date = this.targetCompletionDate || config.defaultEnd || '';
            }

            // Termin pertengahan: distribusikan bertahap di antara tanggal termin 1 dan akhir
            if (n > 2) {
                const startStr = this.terms[0].payment_date || config.defaultTerm1Due;
                const endStr = this.terms[n - 1].payment_date || this.targetCompletionDate || config.defaultEnd;
                let start = new Date(startStr).getTime();
                let end = new Date(endStr).getTime();

                if (isNaN(start)) start = Date.now() + 7 * 86400000;
                if (isNaN(end) || end <= start) end = start + (n - 1) * 30 * 86400000;

                const step = (end - start) / (n - 1);
                for (let i = 1; i < n - 1; i++) {
                    if (!this.terms[i].payment_date) {
                        const d = new Date(start + step * i);
                        this.terms[i].payment_date = d.toISOString().split('T')[0];
                    }
                }
            }
        },

        addMember() {
            this.teamMembers.push({
                id: Date.now() + Math.random(),
                student_id: '',
                role_id: this.roles.length > 0 ? this.roles[0].id : 1,
                joined_at: this.startDate || ''
            });
        },

        addMemberWithRole(roleId) {
            this.teamMembers.push({
                id: Date.now() + Math.random(),
                student_id: '',
                role_id: roleId,
                joined_at: this.startDate || ''
            });
        },

        removeMember(index) {
            if (this.teamMembers.length > 1) {
                this.teamMembers.splice(index, 1);
            }
        },

        countMembersWithRole(roleId) {
            return this.teamMembers.filter(m => Number(m.role_id) === Number(roleId)).length;
        },

        prepareSubmit(event) {
            if (!this.selectedPmId) {
                alert('Silakan pilih Dosen yang akan ditugaskan sebagai Project Manager (PM).');
                event.preventDefault();
                return false;
            }

            const unselectedStudent = this.teamMembers.some(m => !m.student_id);
            if (unselectedStudent) {
                alert('Pastikan semua anggota tim mahasiswa telah dipilih dari daftar.');
                event.preventDefault();
                return false;
            }

            const studentIds = this.teamMembers.map(m => Number(m.student_id));
            if (new Set(studentIds).size !== studentIds.length) {
                alert('Terdapat mahasiswa yang dipilih lebih dari satu kali dalam tim. Harap pilih mahasiswa yang berbeda untuk setiap peran.');
                event.preventDefault();
                return false;
            }

            if (this.totalTermPercentage() !== 100) {
                alert('Total alokasi termin harus pas 100% (saat ini ' + this.totalTermPercentage() + '%). Silakan klik "Pas-kan ke Pelunasan" atau "Ratakan Sisa Alokasi".');
                event.preventDefault();
                return false;
            }

            if (this.hasEmptyDueDate()) {
                alert('Terdapat ' + this.emptyDueDateCount() + ' termin yang tanggal jatuh temponya belum diisi. Mohon lengkapi seluruh tanggal jatuh tempo atau klik "Isi Otomatis (Bertahap)".');
                event.preventDefault();
                return false;
            }

            // Sync hidden inputs directly before submit to guarantee values
            const termsInput = document.querySelector('input[name="terms"]');
            if (termsInput) termsInput.value = JSON.stringify(this.terms);
            const membersInput = document.querySelector('input[name="members"]');
            if (membersInput) membersInput.value = JSON.stringify(this.teamMembers);

            this.isSubmitting = true;
            return true;
        }
    };
}
</script>
</body>
</html>
