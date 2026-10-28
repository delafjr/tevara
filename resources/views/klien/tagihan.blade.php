{{-- == ( halaman tagihan & pembayaran - klien ) == --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Tagihan & Pembayaran Proyek - Pantau status penagihan termin proyek (SPK), riwayat pembayaran terverifikasi, dan unggah bukti transfer per termin.">
    <title>Tagihan & Pembayaran Proyek - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen">

@php
    $user = $user ?? session('user', []);
    $userName = $user['name'] ?? 'Budi Santoso';

    $hasRealBilling = !empty($projectsBilling);
    if (!$hasRealBilling) {
        $projectsBilling = [
            [
                'id'             => 'PRJ-2026/014',
                'proposal_id'    => 1,
                'spk'            => 'SPK/TEFA/2026/008',
                'spk_status'     => 'SPK Ditandatangani',
                'spk_status_type'=> 'success',
                'title'          => 'Sistem Monitoring Rantai Pasok Terpadu (IoT & Web)',
                'period'         => 'Periode: 01 Agt 2026 s.d. 30 Nov 2026 • Skema: 3 Termin (30% - 40% - 30%)',
                'total_value'    => 'Rp 75.000.000',
                'paid_percent'   => 66.6,
                'paid_status'    => '66.6% Terbayar',
                'has_open'       => false,
                'termins' => [
                    [
                        'id'           => 1,
                        'proposal_id'  => 1,
                        'name'         => 'Termin 1 — Down Payment (30%)',
                        'desc'         => 'Kick-off, Spesifikasi Teknis, dan Persetujuan Arsitektur Sistem',
                        'nominal'      => 'Rp 22.500.000',
                        'channel'      => 'Virtual Account BCA',
                        'channel_icon' => 'bank',
                        'status'       => 'verified',
                        'status_label' => 'VERIFIED (Lunas)',
                        'due_date'     => null,
                        'can_download' => true,
                        'can_upload'   => false,
                    ],
                    [
                        'id'           => 2,
                        'proposal_id'  => 1,
                        'name'         => 'Termin 2 — Progres Lapangan & Integrasi IoT (40%)',
                        'desc'         => 'Implementasi Firmware Sensor, Broker MQTT, dan Visualisasi Web',
                        'nominal'      => 'Rp 30.000.000',
                        'channel'      => 'Virtual Account BCA',
                        'channel_icon' => 'bank',
                        'status'       => 'verified',
                        'status_label' => 'VERIFIED (Lunas)',
                        'due_date'     => null,
                        'can_download' => true,
                        'can_upload'   => false,
                    ],
                    [
                        'id'           => 3,
                        'proposal_id'  => 1,
                        'name'         => 'Termin 3 — Final UAT & Serah Terima (30%)',
                        'desc'         => 'Uji Terima Pengguna (UAT) Final, Pelatihan Operator, & Handover',
                        'nominal'      => 'Rp 22.500.000',
                        'channel'      => 'BCA VA / Mandiri Giro',
                        'channel_icon' => 'card',
                        'status'       => 'pending',
                        'status_label' => 'BELUM TERBUKA',
                        'due_date'     => 'Est. 25 Nov 2026',
                        'can_download' => false,
                        'can_upload'   => false,
                    ],
                ],
            ],
            [
                'id'             => 'PRJ-2026/009',
                'proposal_id'    => 2,
                'spk'            => 'SPK/TEFA/2026/005',
                'spk_status'     => 'SPK Aktif',
                'spk_status_type'=> 'success',
                'has_open'       => true,
                'open_badge'     => '! Tagihan Terbuka',
                'title'          => 'Aplikasi Mobile Portal Layanan Nasabah & Pembayaran Digital',
                'period'         => 'Periode: 10 Mei 2026 s.d. 30 Okt 2026 • Skema: 2 Termin (DP 50% & Pelunasan 50%)',
                'total_value'    => 'Kontrak: Rp 60.000.000',
                'paid_percent'   => 50.0,
                'paid_status'    => '50.0%',
                'active_action'  => 'Bayar Termin 2',
                'termins' => [
                    [
                        'id'           => 4,
                        'proposal_id'  => 2,
                        'name'         => 'Termin 1 — Down Payment (50%)',
                        'desc'         => 'Kick-off & Approval System Architecture & UI/UX',
                        'nominal'      => 'Rp 30.000.000',
                        'channel'      => 'Virtual Account BCA',
                        'channel_icon' => 'bank',
                        'status'       => 'verified',
                        'status_label' => 'VERIFIED (Lunas)',
                        'due_date'     => null,
                        'can_download' => true,
                        'can_upload'   => false,
                    ],
                    [
                        'id'           => 5,
                        'proposal_id'  => 2,
                        'name'         => 'Termin 2 — Pelunasan & Deployment (50%)',
                        'is_urgent'    => true,
                        'desc'         => 'UAT Final, User Training, & Penyerahan Source Code',
                        'nominal'      => 'Rp 30.000.000',
                        'channel'      => 'BCA VA / Mandiri Giro',
                        'channel_icon' => 'card',
                        'status'       => 'unpaid',
                        'status_label' => 'MENUNGGU PEMBAYARAN',
                        'due_date'     => 'Jatuh Tempo: 15 Okt 2026',
                        'can_download' => false,
                        'can_upload'   => true,
                    ],
                ],
            ],
        ];
    }
@endphp

<div class="flex min-h-screen"
     x-data="{
         searchQuery: '',
         activeStatusFilter: 'Semua',
         selectedProjectFilter: 'all',
         expanded: {},
         toggle(id) {
             this.expanded[id] = !this.expanded[id];
         },
         toastMessage: '',
         showToast: false,
         triggerToast(msg) {
             this.toastMessage = msg;
             this.showToast = true;
             setTimeout(() => { this.showToast = false; }, 3000);
         },
         copyText(text, label) {
             navigator.clipboard.writeText(text);
             this.triggerToast(label + ' berhasil disalin ke clipboard!');
         },

         matches(p) {
             if (this.selectedProjectFilter !== 'all' && p.id !== this.selectedProjectFilter) {
                 return false;
             }
             const q = this.searchQuery.toLowerCase().trim();
             const hasQuery = !q || p.id.toLowerCase().includes(q) ||
                              p.title.toLowerCase().includes(q) ||
                              p.spk.toLowerCase().includes(q) ||
                              p.termins.some(t => t.name.toLowerCase().includes(q) || t.nominal.toLowerCase().includes(q));

             if (!hasQuery) return false;

             if (this.activeStatusFilter === 'Semua') return true;
             if (this.activeStatusFilter === 'Menunggu Bayar') {
                 return p.termins.some(t => t.status === 'unpaid');
             }
             if (this.activeStatusFilter === 'Dalam Verifikasi') {
                 return p.termins.some(t => t.status === 'verifying');
             }
             if (this.activeStatusFilter === 'Lunas') {
                 return p.termins.some(t => t.status === 'verified');
             }
             return true;
         }
     }">

    {{-- == ( sidebar komponen DRY ) == --}}
    <x-sidebar active="tagihan" />

    {{-- == ( konten utama ) == --}}
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

        <main class="flex-1 px-8 py-7 space-y-6">

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

            @if (session('error') || ($error ?? null))
                <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 shadow-xs">
                    <svg class="w-5 h-5 flex-shrink-0 text-red-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    <div>
                        <p class="text-xs font-bold text-red-900">Perhatian</p>
                        <p class="text-xs font-medium text-red-700 leading-relaxed">{{ session('error') ?? $error }}</p>
                    </div>
                </div>
            @endif

            {{-- == ( header card : judul halaman & informasi sinkronisasi ) == --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#EFF6FF] via-[#F4F8FE] to-[#FCF8EC] border border-blue-100/70 px-8 py-7 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="relative z-10 max-w-3xl">
                    <div class="inline-flex items-center gap-2 bg-[#EAF3FF] text-[#0B3B82] text-[10.5px] font-bold tracking-wide uppercase px-3 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        TAGIHAN & KEUANGAN • WORKSPACE KLIEN
                    </div>
                    <h1 class="text-2xl lg:text-[28px] font-extrabold text-[#1E293B] tracking-tight">
                        Tagihan & Pembayaran <span class="text-[#FFB800]">Proyek</span>
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-2 leading-relaxed font-normal">
                        Pantau status penagihan termin proyek (SPK), riwayat pembayaran terverifikasi, dan unggah bukti transfer per termin secara terintegrasi dengan akuntansi Politeknik.
                    </p>
                </div>

                {{-- Date & Sync Info Right Box --}}
                <div class="relative z-10 flex flex-col items-start md:items-end gap-1.5 flex-shrink-0 bg-white/70 backdrop-blur-xs p-3.5 rounded-2xl border border-blue-100/50 shadow-xs">
                    <div class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700">
                        <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5"/>
                        </svg>
                        <span>Rabu, 30 September 2026</span>
                    </div>
                    <div class="inline-flex items-center gap-1 text-[11px] text-slate-500 font-medium">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        <span>Sync Otomatis: <strong class="text-slate-700">Aktif</strong> (Cache 60s)</span>
                    </div>
                </div>
            </div>

            {{-- == ( kartu metrik nilai kontrak & distribusi termin ) == --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6 md:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-8">

                {{-- Sisi Kiri: Total Nilai Kontrak --}}
                <div class="space-y-1.5 flex-1">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400">TOTAL NILAI KONTRAK TERIKAT SPK</p>
                    <div class="text-3xl lg:text-[36px] font-extrabold text-slate-800 tracking-tight">
                        Rp 185.000.000
                    </div>
                    <div class="inline-flex items-center gap-1.5 text-xs text-slate-500 pt-1 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#0B3B82]"></span>
                        <span>3 Proyek Aktif Terikat Dokumen SPK Sah</span>
                    </div>
                </div>

                {{-- Sisi Kanan: Distribusi Termin Pembayaran --}}
                <div class="space-y-3 w-full lg:w-[460px] flex-shrink-0">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                        <span>Distribusi Termin Pembayaran</span>
                        <span class="text-[#0B3B82]">Realisasi 60.8%</span>
                    </div>

                    {{-- Multi-color segmented progress bar --}}
                    <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden flex gap-0.5">
                        <div class="h-full bg-emerald-500 rounded-l-full transition-all duration-500" style="width: 60.8%" title="Terverifikasi: 60.8%"></div>
                        <div class="h-full bg-[#FFB800] transition-all duration-500" style="width: 17.6%" title="Verifikasi: 17.6%"></div>
                        <div class="h-full bg-[#0B3B82] rounded-r-full transition-all duration-500" style="width: 21.6%" title="Terbuka: 21.6%"></div>
                    </div>

                    {{-- Legend --}}
                    <div class="flex items-center justify-between text-[11px] text-slate-500 font-medium pt-0.5">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Terverifikasi (60.8%)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#FFB800]"></span>
                            <span>Verifikasi (17.6%)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#0B3B82]"></span>
                            <span>Terbuka (21.6%)</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- == ( banner peringatan tindakan : unggah bukti pembayaran termin berjalan ) == --}}
            <div class="bg-gradient-to-r from-[#EFF5FF] to-[#FAF5E8] border border-blue-100/90 rounded-2xl p-5 md:p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-xs">

                <div class="flex items-start gap-4">
                    {{-- Bell / Alert Icon --}}
                    <div class="w-11 h-11 rounded-full bg-[#FFB800] text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                        </svg>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="bg-amber-100 text-amber-900 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wide">
                                PERLU TINDAKAN KLIEN
                            </span>
                            <span class="bg-red-50 text-red-600 text-[10.5px] font-semibold px-2.5 py-0.5 rounded-full inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Jatuh Tempo: 15 Okt 2026
                            </span>
                        </div>

                        <h2 class="text-base font-bold text-slate-800 leading-snug">
                            Unggah Bukti Pembayaran Termin Berjalan (Termin 2)
                        </h2>

                        <p class="text-xs text-slate-500 leading-relaxed max-w-2xl">
                            Proyek <strong class="text-slate-700">Aplikasi Mobile Portal Layanan Nasabah (PRJ-2026/009)</strong> siap untuk proses pelunasan 50% (Rp 30.000.000). Silakan unggah slip transaksi atau bukti transfer bank guna mempercepat penerbitan kwitansi sah bendahara Politeknik.
                        </p>
                    </div>
                </div>

                <div class="flex-shrink-0">
                    @php
                        // Cari termin urgent yang dapat diupload dari data projectsBilling
                        $urgentProposalId = null;
                        $urgentPaymentId  = null;
                        foreach ($projectsBilling as $_proj) {
                            foreach ($_proj['termins'] ?? [] as $_t) {
                                if (!empty($_t['can_upload'])) {
                                    $urgentProposalId = $_t['proposal_id'] ?? ($_proj['proposal_id'] ?? null);
                                    $urgentPaymentId  = $_t['id'] ?? null;
                                    break 2;
                                }
                            }
                        }
                    @endphp
                    @if($urgentProposalId && $urgentPaymentId)
                        <a href="{{ route('tagihan.unggah', ['proposal' => $urgentProposalId, 'payment' => $urgentPaymentId]) }}"
                           class="bg-[#0B3B82] hover:bg-[#082d64] text-white text-xs font-semibold px-5 py-3 rounded-xl flex items-center gap-2 shadow-sm transition">
                    @else
                        <a href="{{ route('klien.tagihan') }}"
                           class="bg-[#0B3B82] hover:bg-[#082d64] text-white text-xs font-semibold px-5 py-3 rounded-xl flex items-center gap-2 shadow-sm transition">
                    @endif
                        {{-- Document Upload Icon --}}
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                        </svg>
                        <span>Unggah Bukti Bayar Sekarang</span>
                    </a>
                </div>

            </div>

            {{-- == ( filter & search bar ) == --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-2.5 flex flex-col md:flex-row items-center justify-between gap-3">

                {{-- Search Box --}}
                <div class="relative w-full md:w-72">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                        </svg>
                    </span>
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Cari termin"
                           class="w-full bg-[#F8FAFC] border border-slate-200/70 rounded-xl pl-10 pr-4 py-2 text-xs md:text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#0B3B82] focus:bg-white transition">
                </div>

                {{-- Status Pills --}}
                <div class="flex items-center gap-1.5 flex-wrap">
                    @php
                        $statusTabs = [
                            'Semua'            => 'Semua (5)',
                            'Menunggu Bayar'   => 'Menunggu Bayar (1)',
                            'Dalam Verifikasi' => 'Dalam Verifikasi (1)',
                            'Lunas'            => 'Lunas (3)',
                        ];
                    @endphp

                    @foreach($statusTabs as $tabKey => $tabLabel)
                        <button type="button"
                                @click="activeStatusFilter = '{{ $tabKey }}'"
                                :class="activeStatusFilter === '{{ $tabKey }}'
                                    ? 'bg-[#0B3B82] text-white shadow-xs font-semibold'
                                    : 'bg-transparent text-slate-600 hover:bg-slate-100 font-medium'"
                                class="px-4 py-2 rounded-xl text-xs transition cursor-pointer">
                            {{ $tabLabel }}
                        </button>
                    @endforeach
                </div>

                {{-- Project Filter Dropdown --}}
                <div class="w-full md:w-auto">
                    <select x-model="selectedProjectFilter"
                            class="w-full md:w-auto bg-[#F8FAFC] border border-slate-200/80 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:border-[#0B3B82] cursor-pointer">
                        <option value="all">Semua Proyek Aktif (3)</option>
                        <option value="PRJ-2026/014">PRJ-2026/014 - Sistem Monitoring</option>
                        <option value="PRJ-2026/009">PRJ-2026/009 - Aplikasi Mobile Nasabah</option>
                        <option value="PRJ-2026/021">PRJ-2026/021 - Smart Warehouse Sensor</option>
                    </select>
                </div>

            </div>

            {{-- == ( daftar kartu proyek & termin tagihan ) == --}}
            <div class="space-y-4">

                @foreach($projectsBilling as $proj)
                <div x-show="matches(@js($proj))"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white rounded-2xl border border-slate-100 shadow-xs transition hover:shadow-md overflow-hidden">

                    {{-- == KEPALA KARTU PROYEK == --}}
                    <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-5">

                        <div class="space-y-1.5 flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-0.5 rounded-md font-mono">
                                    {{ $proj['id'] }}
                                </span>
                                <span class="text-xs text-slate-500 font-medium">
                                    • {{ $proj['spk'] }}
                                </span>
                                <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                                    </svg>
                                    {{ $proj['spk_status'] }}
                                </span>
                                @if(!empty($proj['has_open']) && !empty($proj['open_badge']))
                                    <span class="text-xs font-bold text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-full inline-flex items-center gap-1">
                                        {{ $proj['open_badge'] }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-base font-bold text-slate-800 leading-snug">
                                {{ $proj['title'] }}
                            </h3>

                            <p class="text-xs text-slate-400">
                                {{ $proj['period'] }}
                            </p>
                        </div>

                        {{-- Sisi Kanan: Nilai Kontrak, Persentase, & Tombol Toggle --}}
                        <div class="flex items-center justify-between md:justify-end gap-5 flex-shrink-0">

                            <div class="space-y-1 text-left md:text-right">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-slate-500 font-medium">{{ $proj['total_value'] }}</span>
                                    <span class="text-xs font-bold {{ $proj['id'] === 'PRJ-2026/009' ? 'text-slate-800' : 'text-[#0B3B82]' }}">
                                        {{ $proj['paid_status'] }}
                                    </span>
                                </div>
                                <div class="w-32 md:w-36 h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $proj['id'] === 'PRJ-2026/009' ? 'bg-[#FFB800]' : 'bg-[#0B3B82]' }} rounded-full"
                                         style="width: {{ $proj['paid_percent'] }}%"></div>
                                </div>
                            </div>

                            {{-- Tombol Aksi Khusus (seperti Bayar Termin 2) jika ada --}}
                            <div class="flex items-center gap-2">
                                @if(!empty($proj['active_action']))
                                    <button type="button"
                                            @click="openUploadModal('{{ $proj['active_action'] }} ({{ $proj['id'] }})')"
                                            class="bg-[#0B3B82] hover:bg-[#082d64] text-white text-xs font-semibold px-4 py-2 rounded-xl inline-flex items-center gap-1.5 shadow-sm transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <rect width="20" height="14" x="2" y="5" rx="2"/>
                                            <line x1="2" x2="22" y1="10" y2="10"/>
                                        </svg>
                                        <span>{{ $proj['active_action'] }}</span>
                                    </button>
                                @endif

                                {{-- Accordion Toggle Button --}}
                                <button type="button"
                                        @click="toggle('{{ $proj['id'] }}')"
                                        class="bg-[#EAF3FF] hover:bg-[#D9E9FF] text-[#0B3B82] text-xs font-semibold px-3.5 py-2 rounded-xl inline-flex items-center gap-1.5 transition cursor-pointer">
                                    <span x-text="expanded['{{ $proj['id'] }}'] ? 'Tutup' : 'Lihat Rincian Tagihan'"></span>
                                    <svg class="w-3.5 h-3.5 transition-transform duration-200"
                                         :class="expanded['{{ $proj['id'] }}'] ? 'rotate-180' : ''"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                                    </svg>
                                </button>
                            </div>

                        </div>

                    </div>

                    {{-- == TABEL EXPANDED RINCIAN TERMIN PROYEK == --}}
                    <div x-show="expanded['{{ $proj['id'] }}']"
                         x-collapse
                         class="px-6 pb-6 pt-2 border-t border-slate-100/80 space-y-4">

                        <div class="flex items-center justify-between text-xs pt-2">
                            <h4 class="font-bold text-slate-700">
                                Rincian Dokumen & Riwayat Termin Proyek
                            </h4>
                            <span class="text-slate-400 font-medium">
                                Diperbarui sistem otomatis: 15 menit yang lalu
                            </span>
                        </div>

                        {{-- Tabel Termin --}}
                        <div class="overflow-x-auto rounded-xl border border-slate-100 shadow-2xs">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-[#F8FAFD] text-slate-500 uppercase tracking-wider text-[10.5px] border-b border-slate-100 font-bold">
                                        <th class="px-5 py-3.5">TERMIN & URAIAN BOBOT</th>
                                        <th class="px-5 py-3.5">NOMINAL SPK</th>
                                        <th class="px-5 py-3.5">KANAL PEMBAYARAN</th>
                                        <th class="px-5 py-3.5">STATUS TRANSAKSI</th>
                                        <th class="px-5 py-3.5 text-right">AKSI DOKUMEN</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach($proj['termins'] as $termin)
                                    <tr class="hover:bg-slate-50/70 transition">

                                        {{-- 1. Termin & Uraian --}}
                                        <td class="px-5 py-4 max-w-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-slate-800 text-[13px] leading-snug">
                                                    {{ $termin['name'] }}
                                                </span>
                                                @if(!empty($termin['is_urgent']))
                                                    <span class="bg-amber-100 text-amber-900 text-[9.5px] font-extrabold px-2 py-0.5 rounded-full uppercase tracking-wider">
                                                        URGENT
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                                                {{ $termin['desc'] ?? ($termin['name'] ?? '-') }}
                                            </p>
                                        </td>

                                        {{-- 2. Nominal SPK --}}
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="font-bold text-slate-800 text-[13px]">
                                                {{ $termin['nominal'] ?? 'Rp 0' }}
                                            </div>
                                        </td>

                                        {{-- 3. Kanal Pembayaran --}}
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="inline-flex items-center gap-2 text-slate-600 font-medium">
                                                @if(($termin['channel_icon'] ?? 'bank') === 'bank')
                                                    {{-- Bank / Building Icon --}}
                                                    <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5M4.5 21V10.5M3 21h18"/>
                                                    </svg>
                                                @else
                                                    {{-- Card / Receipt Icon --}}
                                                    <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <rect width="20" height="14" x="2" y="5" rx="2"/>
                                                        <line x1="2" x2="22" y1="10" y2="10"/>
                                                    </svg>
                                                @endif
                                                <span>{{ $termin['channel'] ?? 'Transfer Bank' }}</span>
                                            </div>
                                        </td>

                                        {{-- 4. Status Transaksi --}}
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            @if($termin['status'] === 'verified')
                                                <span class="inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full text-xs font-bold">
                                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                                    </svg>
                                                    {{ $termin['status_label'] }}
                                                </span>
                                            @elseif($termin['status'] === 'unpaid')
                                                <div class="space-y-1">
                                                    <span class="inline-flex items-center gap-1.5 text-amber-900 bg-amber-100 px-3 py-1 rounded-full text-[11px] font-extrabold tracking-wide uppercase">
                                                        <svg class="w-3.5 h-3.5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                                        </svg>
                                                        {{ $termin['status_label'] }}
                                                    </span>
                                                    @if(!empty($termin['due_date']))
                                                        <p class="text-[10px] text-red-600 font-semibold pl-1">
                                                            {{ $termin['due_date'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                            @elseif($termin['status'] === 'verifying')
                                                <span class="inline-flex items-center gap-1.5 text-blue-800 bg-blue-100 px-3 py-1 rounded-full text-[11px] font-bold">
                                                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                                                    {{ $termin['status_label'] }}
                                                </span>
                                            @elseif($termin['status'] === 'rejected')
                                                <div class="space-y-1">
                                                    <span class="inline-flex items-center gap-1.5 text-red-800 bg-red-100 px-3 py-1 rounded-full text-[11px] font-bold">
                                                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                                        {{ $termin['status_label'] }}
                                                    </span>
                                                    @if(!empty($termin['verification_notes']))
                                                        <p class="text-[10px] text-red-600 font-semibold pl-1">
                                                            Catatan: {{ $termin['verification_notes'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 text-slate-500 bg-slate-100 px-3 py-1 rounded-full text-xs font-semibold">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                    {{ $termin['status_label'] }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- 5. Aksi Dokumen --}}
                                        <td class="px-5 py-4 whitespace-nowrap text-right">
                                            @if($termin['can_download'])
                                                <button type="button"
                                                        @click="triggerToast('Mengunduh berkas Kwitansi Resmi bermeterai (PDF)...')"
                                                        class="bg-white border border-slate-200 hover:border-[#0B3B82] hover:text-[#0B3B82] text-slate-700 text-xs font-semibold px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 shadow-2xs transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                                    </svg>
                                                    <span>Unduh Kwitansi</span>
                                                </button>
                                            @elseif($termin['can_upload'])
                                                <a href="{{ route('tagihan.unggah', ['proposal' => $termin['proposal_id'] ?? ($p['proposal_id'] ?? 0), 'payment' => $termin['id'] ?? 0]) }}"
                                                   class="bg-[#0B3B82] hover:bg-[#082d64] text-white text-xs font-semibold px-3.5 py-1.5 rounded-lg inline-flex items-center gap-1.5 shadow-xs transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                                                    </svg>
                                                    <span>Unggah Bukti</span>
                                                </a>
                                            @else
                                                <span class="text-xs text-slate-300 font-medium italic">
                                                    Belum Tersedia
                                                </span>
                                            @endif
                                        </td>

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>
                @endforeach

            </div>

        </main>

    </div>

</div>

</body>
</html>
