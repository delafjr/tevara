<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Bukti Transfer — Termin {{ $payment['term_sequence'] ?? 1 }} - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased min-h-screen">

@php
    $user = $user ?? session('user');
    $proposal = is_array($proposal ?? null) ? $proposal : [];
    $payment = is_array($payment ?? null) ? $payment : [];
    $error = $error ?? null;

    $proposalId = $payment['proposal_id'] ?? ($proposal['id'] ?? 1);
    $paymentId = $payment['id'] ?? 1;

    $projectCode = $proposal['project_code'] ?? ($proposal['proposal_code'] ?? ('PRJ-' . $proposalId));
    $projectTitle = $proposal['title'] ?? 'Proyek Tanpa Judul';

    $client = is_array($proposal['client'] ?? null) ? $proposal['client'] : [];
    $clientName = $client['name'] ?? ($proposal['client_name'] ?? 'Klien Mitra');
    $clientEmail = $client['email'] ?? ($proposal['client_email'] ?? 'klien@mitra.com');
    $clientCompany = $client['company_name'] ?? ($proposal['client_company_name'] ?? ($client['company'] ?? '-'));
    $clientPhone = $client['phone'] ?? ($proposal['client_phone'] ?? '-');

    $termSequence = $payment['term_sequence'] ?? 1;
    $amount = (float) ($payment['amount'] ?? 0);
    $paymentDate = !empty($payment['payment_date'])
        ? \Carbon\Carbon::parse($payment['payment_date'])->translatedFormat('d F Y')
        : '-';
    $paymentMethod = $payment['payment_method'] ?? 'Transfer Bank (Giro)';
    $transferRef = $payment['transfer_reference'] ?? '-';
    $proofUrl = $payment['proof_url'] ?? null;
    $status = strtoupper((string) ($payment['status'] ?? 'PENDING'));
    $verificationNotes = $payment['verification_notes'] ?? '';

    $isPdf = $proofUrl && (str_ends_with(strtolower(parse_url($proofUrl, PHP_URL_PATH) ?? ''), '.pdf') || str_contains(strtolower($proofUrl), '.pdf'));
@endphp

<div class="flex min-h-screen"
     x-data="{
         status: 'VERIFIED',
         notes: '{{ addslashes($verificationNotes) }}',
         zoomLevel: 1,
         rotation: 0,
         toastMessage: '',
         showToast: false,
         triggerToast(msg) {
             this.toastMessage = msg;
             this.showToast = true;
             setTimeout(() => { this.showToast = false; }, 3000);
         },
         copyText(text, label) {
             if (!text || text === '-') return;
             navigator.clipboard.writeText(text);
             this.triggerToast(label + ' berhasil disalin!');
         },
         zoomIn() {
             if (this.zoomLevel < 3) this.zoomLevel = Math.round((this.zoomLevel + 0.25) * 100) / 100;
         },
         zoomOut() {
             if (this.zoomLevel > 0.5) this.zoomLevel = Math.round((this.zoomLevel - 0.25) * 100) / 100;
         },
         resetZoom() {
             this.zoomLevel = 1;
             this.rotation = 0;
         },
         rotate() {
             this.rotation = (this.rotation + 90) % 360;
         },
         setQuickNote(text) {
             this.notes = text;
         }
     }">

    {{-- Sidebar Komponen --}}
    <x-sidebar active="keuangan" />

    {{-- Konten Utama --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen">

        {{-- Toast Notification --}}
        <div x-show="showToast"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             style="display: none;"
             class="fixed bottom-6 right-6 z-50 bg-[#0B1E3F] text-white text-xs font-semibold px-4 py-3 rounded-xl shadow-xl flex items-center gap-2 border border-blue-900/60">
            <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <span x-text="toastMessage"></span>
        </div>

        <main class="flex-1 px-8 py-7 space-y-6 pb-20">

            {{-- Breadcrumb Navigation --}}
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('pengurus-tefa.keuangan') }}"
                   class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82] transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke Rekap Keuangan
                </a>

                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-mono font-semibold text-slate-500 bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-2xs">
                        {{ $projectCode }}
                    </span>
                    <span class="text-[11px] font-bold px-3 py-1.5 rounded-lg bg-blue-50 text-[#0B3B82] border border-blue-200">
                        Termin {{ $termSequence }}
                    </span>
                    @if($status === 'VERIFIED')
                        <span class="text-[11px] font-bold px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Terverifikasi (Lunas)
                        </span>
                    @elseif($status === 'REJECTED')
                        <span class="text-[11px] font-bold px-3 py-1.5 rounded-lg bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            Ditolak
                        </span>
                    @else
                        <span class="text-[11px] font-bold px-3 py-1.5 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 inline-flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Menunggu Verifikasi
                        </span>
                    @endif
                </div>
            </div>

            {{-- Flash Alert Messages --}}
            @if (session('success'))
                <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800 shadow-xs">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <div>
                        <p class="text-xs font-bold text-emerald-900">Berhasil</p>
                        <p class="text-xs font-medium text-emerald-700 leading-relaxed">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error') || $error)
                <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 shadow-xs">
                    <svg class="w-5 h-5 flex-shrink-0 text-red-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    <div>
                        <p class="text-xs font-bold text-red-900">Perhatian</p>
                        <p class="text-xs font-medium text-red-700 leading-relaxed">{{ session('error') ?? $error }}</p>
                    </div>
                </div>
            @endif

            {{-- Hero Header --}}
            <div class="relative overflow-hidden rounded-3xl bg-white border border-slate-100 shadow-sm px-8 py-6">
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                            Modul Keuangan F-09 · Teaching Factory
                        </span>
                        <h1 class="text-2xl font-bold text-[#1E293B]">
                            Verifikasi Bukti <span class="text-[#FFB800]">Transfer Pembayaran</span>
                        </h1>
                        <p class="text-xs md:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
                            Periksa validitas berkas struk bukti transfer klien, lakukan pencocokan mutasi rekening giro Politeknik, dan simpan keputusan verifikasi termin.
                        </p>
                    </div>

                    <div class="flex-shrink-0 bg-blue-50/70 border border-blue-100 rounded-2xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-[#0B3B82] text-white flex items-center justify-center shadow-xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Nominal Tagihan</p>
                            <p class="text-xl font-black text-emerald-600">Rp {{ number_format($amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Two-Column Layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">

                {{-- Kolom Kiri (7 Kolom): Berkas & Dokumen Bukti Transfer --}}
                <div class="lg:col-span-7 space-y-6">

                    {{-- Card Berkas Bukti --}}
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        {{-- Card Header --}}
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-blue-100 text-[#0B3B82] flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                    </svg>
                                </div>
                                <h2 class="text-sm font-bold text-slate-800">Dokumen Bukti Transfer Bank</h2>
                            </div>

                            @if($proofUrl && !$isPdf)
                                {{-- Image Controls --}}
                                <div class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl p-1 shadow-2xs">
                                    <button type="button"
                                            @click="zoomIn()"
                                            title="Perbesar"
                                            class="w-7 h-7 rounded-lg hover:bg-slate-100 text-slate-600 flex items-center justify-center transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    </button>
                                    <button type="button"
                                            @click="zoomOut()"
                                            title="Perkecil"
                                            class="w-7 h-7 rounded-lg hover:bg-slate-100 text-slate-600 flex items-center justify-center transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg>
                                    </button>
                                    <button type="button"
                                            @click="rotate()"
                                            title="Putar 90 Derajat"
                                            class="w-7 h-7 rounded-lg hover:bg-slate-100 text-slate-600 flex items-center justify-center transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                    </button>
                                    <button type="button"
                                            @click="resetZoom()"
                                            title="Reset Tampilan"
                                            class="px-2 h-7 rounded-lg hover:bg-slate-100 text-[11px] font-bold text-slate-500 flex items-center justify-center transition cursor-pointer">
                                        Reset
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Viewer Area --}}
                        <div class="p-6 bg-slate-900/5 min-h-[380px] flex items-center justify-center overflow-hidden relative">
                            @if($proofUrl)
                                @if($isPdf)
                                    <div class="text-center p-8 bg-white rounded-2xl border border-slate-200 shadow-sm max-w-md w-full">
                                        <div class="w-16 h-16 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-slate-800">Berkas Bukti Format Dokumen PDF</h3>
                                        <p class="text-xs text-slate-400 mt-1 mb-5">Klien mengunggah slip bukti transfer resmi berformat PDF.</p>
                                        <a href="{{ $proofUrl }}" target="_blank"
                                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0B3B82] hover:bg-[#082a5c] text-white text-xs font-bold shadow-sm transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                            Buka Berkas PDF di Tab Baru
                                        </a>
                                    </div>
                                @else
                                    <div class="relative overflow-auto max-h-[520px] w-full flex items-center justify-center p-2">
                                        <img :src="'{{ $proofUrl }}'"
                                             alt="Bukti Transfer Klien"
                                             :style="'transform: scale(' + zoomLevel + ') rotate(' + rotation + 'deg); transition: transform 0.2s ease-in-out;'"
                                             class="max-h-[460px] w-auto rounded-2xl shadow-md border border-white cursor-zoom-in object-contain select-none bg-white"
                                             @click="zoomIn()">
                                    </div>
                                @endif
                            @else
                                <div class="text-center p-8">
                                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">Berkas Bukti Belum Tersedia</h3>
                                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                        Klien belum menyertakan lampiran bukti atau file tidak dapat dimuat dari media storage.
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Footer Actions Bukti --}}
                        @if($proofUrl)
                            <div class="px-6 py-4 border-t border-slate-100 bg-white flex flex-wrap items-center justify-between gap-3">
                                <span class="text-[11px] text-slate-400 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    Lampiran Berkas Terunggah dari Sisi Klien
                                </span>

                                <div class="flex items-center gap-2">
                                    <a href="{{ $proofUrl }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                        Buka Gambar Penuh
                                    </a>
                                    <a href="{{ $proofUrl }}"
                                       download
                                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#0B3B82] hover:bg-[#082a5c] text-white text-xs font-bold shadow-xs transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                        Unduh File
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Panduan SOP Keuangan Box --}}
                    <div class="bg-gradient-to-br from-blue-50/80 via-white to-amber-50/40 rounded-3xl border border-blue-100 p-6 shadow-xs">
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-7 h-7 rounded-lg bg-[#0B3B82] text-white flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-2.171 7.248c-.512.078-1.037.12-1.57.12C5.908 19.118 2.25 15.46 2.25 11c0-4.46 3.658-8.118 8.009-8.118 3.57 0 6.608 2.378 7.625 5.65m2.366 1.468A9.034 9.034 0 0 1 20.25 11c0 .825-.11 1.625-.316 2.386"/>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-[#0B3B82] uppercase tracking-wider">Panduan Standar Verifikasi Bukti Bayar (SOP)</h3>
                        </div>
                        <ul class="space-y-2.5 text-xs text-slate-600">
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                <span>Pastikan nominal transfer di bukti persis sama dengan tagihan (<strong>Rp {{ number_format($amount, 0, ',', '.') }}</strong>).</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                <span>Pastikan rekening penerima sesuai dengan rekening resmi yang tertera pada invoice atau dokumen kerja sama.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                <span>Periksa mutasi rekening koran online bendahara dengan mencocokkan nomor referensi / jam transaksi.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                <span>Bila bukti buram atau tidak sesuai, pilih opsi <strong>Tolak Bukti</strong> dan tulis catatan perbaikan yang jelas untuk klien.</span>
                            </li>
                        </ul>
                    </div>

                </div>

                {{-- Kolom Kanan (5 Kolom): Rincian Transaksi & Form Keputusan Verifikasi --}}
                <div class="lg:col-span-5 space-y-6">

                    {{-- Card Rincian Tagihan & Transaksi Klien --}}
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <h2 class="text-sm font-bold text-slate-800">Rincian Transaksi Klien</h2>
                            <span class="text-[10px] font-mono text-slate-400">ID Tagihan #{{ $paymentId }}</span>
                        </div>

                        <div class="space-y-3 text-xs">
                            {{-- Proyek --}}
                            <div>
                                <span class="text-slate-400 block text-[11px]">Proyek SPK</span>
                                <p class="font-bold text-slate-800 text-[13px] mt-0.5">{{ $projectTitle }}</p>
                                <p class="font-mono text-[11px] text-[#0B3B82] font-semibold">{{ $projectCode }}</p>
                            </div>

                            {{-- Klien --}}
                            <div class="pt-2 border-t border-slate-50">
                                <span class="text-slate-400 block text-[11px]">Klien / Instansi Pemesan</span>
                                <p class="font-bold text-slate-800 mt-0.5">{{ $clientName }}</p>
                                @if($clientCompany && $clientCompany !== '-')
                                    <p class="text-slate-500 font-medium">{{ $clientCompany }}</p>
                                @endif
                                <p class="text-[11px] text-slate-400">{{ $clientEmail }} @if($clientPhone && $clientPhone !== '-') · {{ $clientPhone }} @endif</p>
                            </div>

                            {{-- Termin & Nominal --}}
                            <div class="pt-2 border-t border-slate-50 grid grid-cols-2 gap-3">
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Termin Penagihan</span>
                                    <p class="font-bold text-[#0B3B82] mt-0.5">Termin Ke-{{ $termSequence }}</p>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Nominal Tagihan</span>
                                    <p class="font-extrabold text-emerald-600 text-[15px] mt-0.5">Rp {{ number_format($amount, 0, ',', '.') }}</p>
                                </div>
                            </div>

                            {{-- Metode & Tanggal --}}
                            <div class="pt-2 border-t border-slate-50 grid grid-cols-2 gap-3">
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Metode Pembayaran</span>
                                    <p class="font-semibold text-slate-700 mt-0.5">{{ $paymentMethod }}</p>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Tanggal Transfer</span>
                                    <p class="font-semibold text-slate-700 mt-0.5">{{ $paymentDate }}</p>
                                </div>
                            </div>

                            {{-- Nomor Referensi --}}
                            <div class="pt-2 border-t border-slate-50">
                                <span class="text-slate-400 block text-[11px]">Nomor Referensi / Resi Bank</span>
                                <div class="flex items-center justify-between mt-1 bg-[#F8FAFC] border border-slate-200 rounded-xl px-3 py-2">
                                    <span class="font-mono text-xs font-bold text-slate-800 select-all">{{ $transferRef }}</span>
                                    @if($transferRef !== '-')
                                        <button type="button"
                                                @click="copyText('{{ $transferRef }}', 'Nomor referensi')"
                                                class="text-[11px] text-[#0B3B82] hover:text-[#082a5c] font-bold flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>
                                            Salin
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Formulir Keputusan Verifikasi (F-09) --}}
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-5">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-sm font-bold text-slate-800">Form Keputusan Verifikasi</h2>
                            <p class="text-[11px] text-slate-400 mt-0.5">Tentukan keputusan akhir verifikasi bukti pembayaran klien.</p>
                        </div>

                        <form method="POST"
                              action="{{ route('pengurus-tefa.payments.verify', ['proposal' => $proposalId, 'payment' => $paymentId]) }}"
                              class="space-y-5">
                            @csrf

                            {{-- Pilihan Keputusan Status --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-2.5">
                                    Pilih Keputusan <span class="text-red-500">*</span>
                                </label>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    {{-- Radio VERIFIED (Setujui) --}}
                                    <label @click="status = 'VERIFIED'"
                                           :class="status === 'VERIFIED' ? 'border-emerald-500 bg-emerald-50/40 ring-2 ring-emerald-500/20' : 'border-slate-200 bg-white hover:border-emerald-300'"
                                           class="relative flex flex-col p-3.5 rounded-2xl border transition-all cursor-pointer">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <div class="flex items-center gap-2">
                                                <input type="radio"
                                                       name="status"
                                                       value="VERIFIED"
                                                       x-model="status"
                                                       class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                                <span class="text-xs font-bold text-emerald-800">Setujui (Lunas)</span>
                                            </div>
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        </div>
                                        <p class="text-[11px] text-emerald-700 leading-snug pl-6">
                                            Bukti sah & mutasi terkonfirmasi. Termin berstatus Lunas.
                                        </p>
                                    </label>

                                    {{-- Radio REJECTED (Tolak) --}}
                                    <label @click="status = 'REJECTED'"
                                           :class="status === 'REJECTED' ? 'border-red-500 bg-red-50/40 ring-2 ring-red-500/20' : 'border-slate-200 bg-white hover:border-red-300'"
                                           class="relative flex flex-col p-3.5 rounded-2xl border transition-all cursor-pointer">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <div class="flex items-center gap-2">
                                                <input type="radio"
                                                       name="status"
                                                       value="REJECTED"
                                                       x-model="status"
                                                       class="w-4 h-4 text-red-600 focus:ring-red-500">
                                                <span class="text-xs font-bold text-red-800">Tolak Bukti</span>
                                            </div>
                                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        </div>
                                        <p class="text-[11px] text-red-700 leading-snug pl-6">
                                            Bukti tidak valid/buram. Klien diminta mengunggah ulang.
                                        </p>
                                    </label>
                                </div>
                            </div>

                            {{-- Catatan Verifikasi --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-bold text-slate-700">
                                        Catatan Verifikasi
                                        <span x-show="status === 'REJECTED'" class="text-red-500 font-normal">(Wajib diisi bila ditolak)</span>
                                        <span x-show="status === 'VERIFIED'" class="text-slate-400 font-normal">(Opsional)</span>
                                    </label>
                                    <span class="text-[10px] text-slate-400" x-text="(notes ? notes.length : 0) + ' / 1000'"></span>
                                </div>

                                <textarea name="verification_notes"
                                          x-model="notes"
                                          rows="3"
                                          maxlength="1000"
                                          :required="status === 'REJECTED'"
                                          :placeholder="status === 'VERIFIED' ? 'Contoh: Pembayaran telah dikonfirmasi masuk di rekening Giro Mandiri TEFA Politeknik.' : 'Jelaskan alasan penolakan, misalnya: Bukti transfer terpotong, nominal tidak sesuai, atau belum masuk mutasi bank.'"
                                          class="w-full bg-[#F8FAFC] border border-slate-200 rounded-2xl p-3 text-xs text-slate-700 focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition"></textarea>

                                {{-- Saran Catatan Cepat (Quick Note Chips) --}}
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <span class="text-[10px] text-slate-400 font-medium py-0.5">Template Cepat:</span>
                                    <button type="button"
                                            @click="setQuickNote('Bukti transfer telah dikonfirmasi valid dan dana masuk ke rekening Giro Politeknik.')"
                                            class="text-[10px] bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-600 px-2 py-0.5 rounded-lg border border-slate-200 transition cursor-pointer">
                                        ✓ Masuk Rekening Giro
                                    </button>
                                    <button type="button"
                                            @click="setQuickNote('Bukti pembayaran buram / teks nominal dan tanggal transfer tidak terbaca jelas. Mohon unggah ulang.')"
                                            class="text-[10px] bg-slate-100 hover:bg-red-50 hover:text-red-700 text-slate-600 px-2 py-0.5 rounded-lg border border-slate-200 transition cursor-pointer">
                                        ✗ Bukti Buram
                                    </button>
                                    <button type="button"
                                            @click="setQuickNote('Nominal pada bukti transfer belum sesuai dengan nominal termin yang ditagihkan. Mohon unggah bukti pelunasan yang benar.')"
                                            class="text-[10px] bg-slate-100 hover:bg-red-50 hover:text-red-700 text-slate-600 px-2 py-0.5 rounded-lg border border-slate-200 transition cursor-pointer">
                                        ✗ Nominal Tidak Sesuai
                                    </button>
                                </div>
                            </div>

                            {{-- Tombol Submit & Batal --}}
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                                <a href="{{ route('pengurus-tefa.keuangan') }}"
                                   class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-semibold text-xs transition">
                                    Batal
                                </a>

                                <button type="submit"
                                        :class="status === 'VERIFIED' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/25' : 'bg-red-600 hover:bg-red-700 shadow-red-600/25'"
                                        class="px-5 py-2.5 rounded-xl text-white font-bold text-xs shadow-md transition cursor-pointer flex items-center gap-2">
                                    <template x-if="status === 'VERIFIED'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </template>
                                    <template x-if="status === 'REJECTED'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    </template>
                                    <span x-text="status === 'VERIFIED' ? 'Simpan & Verifikasi Lunas' : 'Simpan & Tolak Bukti'"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

            </div>

        </main>

        <footer class="px-8 py-4 border-t border-slate-100 bg-white">
            <p class="text-[11px] text-slate-400 font-medium">© {{ date('Y') }} TEVARA · Politeknik Negeri Semarang · Teaching Factory</p>
        </footer>

    </div>

</div>

</body>
</html>
