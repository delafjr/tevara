@php
    $user = $user ?? session('user', []);
@endphp

<x-student-layout active="project" title="Project Saya">

    {{-- ===== HEADER TITLE ===== --}}
    <section class="space-y-1">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F172A] tracking-tight">
            Project <span class="text-[#2563EB]">Saya</span>
        </h1>
        <p class="text-slate-500 text-sm leading-relaxed max-w-3xl">
            Daftar seluruh proyek Teaching Factory industri yang sedang dan telah Anda kerjakan bersama tim pengembang TEVARA.
        </p>
    </section>

    {{-- ===== 4 STAT CARDS ===== --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" aria-label="Ringkasan project">
        {{-- Card 1: Total Proyek --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold tracking-wider uppercase">
                <span>Total Proyek</span>
                <span class="w-9 h-9 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </span>
            </div>
            <div>
                <strong class="text-3xl font-extrabold text-[#0F172A] tracking-tight block">4</strong>
                <span class="text-xs text-slate-400 mt-1 block">Semester Ganjil 2026</span>
            </div>
        </div>

        {{-- Card 2: Sedang Berjalan --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold tracking-wider uppercase">
                <span>Sedang Berjalan</span>
                <span class="w-9 h-9 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </span>
            </div>
            <div>
                <strong class="text-3xl font-extrabold text-[#0F172A] tracking-tight block">2</strong>
                <span class="text-xs text-slate-400 mt-1 block">Proyek Aktif Sprint</span>
            </div>
        </div>

        {{-- Card 3: Dalam Review --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold tracking-wider uppercase">
                <span>Dalam Review</span>
                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </div>
            <div>
                <strong class="text-3xl font-extrabold text-[#0F172A] tracking-tight block">1</strong>
                <span class="text-xs text-slate-400 mt-1 block">Menunggu Verifikasi PM</span>
            </div>
        </div>

        {{-- Card 4: Deadline Terdekat --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold tracking-wider uppercase">
                <span>Deadline Terdekat</span>
                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div>
                <strong class="text-2xl font-extrabold text-[#0F172A] tracking-tight block">5 Okt 2026</strong>
                <span class="text-xs text-slate-400 mt-1 block">Smart Farming IoT</span>
            </div>
        </div>
    </section>

    {{-- ===== FILTER & PROJECT LIST WRAPPER ===== --}}
    <div x-data="{ currentFilter: 'all', sortBy: 'terbaru' }" class="space-y-5">

        {{-- Filter toolbar --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2.5">
                <button
                    type="button"
                    @click="currentFilter = 'all'"
                    :class="currentFilter === 'all' ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                >
                    Semua (4)
                </button>
                <button
                    type="button"
                    @click="currentFilter = 'running'"
                    :class="currentFilter === 'running' ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                >
                    Sedang Berjalan (2)
                </button>
                <button
                    type="button"
                    @click="currentFilter = 'review'"
                    :class="currentFilter === 'review' ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                >
                    Dalam Review (1)
                </button>
                <button
                    type="button"
                    @click="currentFilter = 'done'"
                    :class="currentFilter === 'done' ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                >
                    Selesai (1)
                </button>
            </div>

            <button type="button" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 10h12M9 16h6" />
                </svg>
                <span>Terbaru</span>
                <span class="text-[10px] text-slate-400">⌄</span>
            </button>
        </div>

        {{-- Project Cards List --}}
        <div class="space-y-4">

            {{-- ── CARD 1: SISTEM INFORMASI UMKM (Expanded accordion by default) ── --}}
            <article
                x-data="{ open: true }"
                x-show="currentFilter === 'all' || currentFilter === 'running'"
                class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs transition"
            >
                {{-- Card Header --}}
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-[#0B2A5E] text-white flex items-center justify-center flex-shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-bold text-slate-400">PRJ-2026/001</span>
                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-[#2563EB] font-bold text-[11px]">Fullstack Lead</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                    Berjalan
                                </span>
                            </div>
                            <h2 class="text-lg font-bold text-slate-900 tracking-tight mt-1.5">
                                Sistem Informasi UMKM &amp; Inventaris Digital
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                PT. ABC Indonesia <span class="mx-1">·</span> 4 Modul
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="open = !open"
                        class="px-3.5 py-1.5 rounded-xl border border-blue-200 text-[#2563EB] bg-white hover:bg-blue-50 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    >
                        <span x-text="open ? 'Tutup Detail' : 'Lihat Detail'">Tutup Detail</span>
                        <span x-text="open ? '⌃' : '⌄'">⌃</span>
                    </button>
                </div>

                {{-- Progress Bar Row --}}
                <div class="mt-5 flex items-center justify-between gap-4">
                    <span class="text-xs font-semibold text-slate-500 whitespace-nowrap">Progress Proyek</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-[#0B3B82] h-full rounded-full transition-all duration-300" style="width: 75%"></div>
                    </div>
                    <span class="text-xs font-bold text-slate-700 whitespace-nowrap">75%</span>
                    <div class="text-right whitespace-nowrap border-l border-slate-200/80 pl-4 ml-2">
                        <span class="text-[10px] text-slate-400 block uppercase font-medium">Target Selesai</span>
                        <strong class="text-xs font-bold text-slate-800">12 Okt 2026</strong>
                    </div>
                </div>

                {{-- Expandable Content --}}
                <div x-show="open" x-collapse class="mt-6 space-y-6 pt-5 border-t border-slate-100">

                    {{-- 4-Column Meta Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-slate-50/70 border border-slate-100 rounded-xl p-4">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">MITRA INDUSTRI</span>
                            <strong class="text-xs font-bold text-slate-800 block mt-1">PT. ABC Indonesia</strong>
                            <span class="text-[11px] text-slate-400 block mt-0.5">Retail &amp; Supply Chain Modern</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">KONTAK PIC MITRA</span>
                            <strong class="text-xs font-bold text-slate-800 block mt-1">Budi Pratama</strong>
                            <span class="text-[11px] text-slate-400 block mt-0.5">Lead Product Manager Mitra</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">DOSEN PEMBIMBING PM</span>
                            <strong class="text-xs font-bold text-slate-800 block mt-1">Dimas Setiawan, S.Kom., M.T.</strong>
                            <span class="text-[11px] text-slate-400 block mt-0.5">NIDN. 0618098201</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">PERAN ANDA DI TIM</span>
                            <strong class="text-xs font-bold text-[#2563EB] block mt-1">Fullstack Lead</strong>
                            <span class="text-[11px] text-slate-400 block mt-0.5">Kelola 3 Developer &amp; 1 QA</span>
                        </div>
                    </div>

                    {{-- Modul List Section --}}
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span>Daftar Modul &amp; Kebutuhan Proyek</span>
                        </h3>

                        <div class="space-y-2.5">
                            {{-- Modul 1 --}}
                            <div class="bg-white border border-slate-200/80 rounded-xl p-3.5 flex items-center gap-3.5 hover:border-slate-300 transition">
                                <span class="w-9 h-9 rounded-lg bg-blue-50 text-[#2563EB] flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <strong class="text-xs font-bold text-slate-800 block">Modul 1: Autentikasi &amp; Multi-Outlet Role Permission</strong>
                                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                        Pengelolaan hak akses berjenjang untuk kasir, supervisor, dan admin gudang terintegrasi token JWT serta audit log aktivitas.
                                    </p>
                                </div>
                            </div>

                            {{-- Modul 2 --}}
                            <div class="bg-white border border-slate-200/80 rounded-xl p-3.5 flex items-center gap-3.5 hover:border-slate-300 transition">
                                <span class="w-9 h-9 rounded-lg bg-blue-50 text-[#2563EB] flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <strong class="text-xs font-bold text-slate-800 block">Modul 2: Katalog Produk, Barcode Scanner &amp; Manajemen Stok</strong>
                                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                        Pencatatan SKU produk otomatis, update stok realtime dengan validasi stok minus, serta sinkronisasi multi-outlet offline-first.
                                    </p>
                                </div>
                            </div>

                            {{-- Modul 3 --}}
                            <div class="bg-white border border-slate-200/80 rounded-xl p-3.5 flex items-center gap-3.5 hover:border-slate-300 transition">
                                <span class="w-9 h-9 rounded-lg bg-blue-50 text-[#2563EB] flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <strong class="text-xs font-bold text-slate-800 block">Modul 3: Modul Kasir POS &amp; Integrasi Pembayaran QRIS</strong>
                                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                        Pencatatan transaksi kasir kilat, print struk thermal bluetooth, dan webhook verifikasi pembayaran otomatis payment gateway.
                                    </p>
                                </div>
                            </div>

                            {{-- Modul 4 --}}
                            <div class="bg-white border border-slate-200/80 rounded-xl p-3.5 flex items-center gap-3.5 hover:border-slate-300 transition">
                                <span class="w-9 h-9 rounded-lg bg-blue-50 text-[#2563EB] flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <strong class="text-xs font-bold text-slate-800 block">Modul 4: Rekap Laporan Penjualan &amp; Ekspor Laporan Excel/PDF</strong>
                                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                        Dashboard analitik omzet harian, performa kasir per shift, dan ekspor berkala pembukuan kas dalam format spreadsheet.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </article>

            {{-- ── CARD 2: SMART FARMING IOT (Collapsed card) ── --}}
            <article
                x-data="{ open: false }"
                x-show="currentFilter === 'all' || currentFilter === 'running' || currentFilter === 'review'"
                class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs transition"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-bold text-slate-400">PRJ-2026/004</span>
                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-[#2563EB] font-bold text-[11px]">IoT &amp; Backend Dev</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Review
                                </span>
                            </div>
                            <h2 class="text-base font-bold text-slate-900 tracking-tight mt-1.5">
                                Smart Farming IoT &amp; Monitoring Irigasi Tanah
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                CV. Tani Maju <span class="mx-1">·</span> 3 Modul
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="open = !open"
                        class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-slate-600 bg-slate-50 hover:bg-slate-100 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    >
                        <span x-text="open ? 'Tutup Detail' : 'Lihat Detail'">Lihat Detail</span>
                        <span x-text="open ? '⌃' : '⌄'">⌄</span>
                    </button>
                </div>

                {{-- Progress Bar Row --}}
                <div class="mt-5 flex items-center justify-between gap-4">
                    <span class="text-xs font-semibold text-slate-500 whitespace-nowrap">Progress Proyek</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-[#0B3B82] h-full rounded-full transition-all duration-300" style="width: 60%"></div>
                    </div>
                    <span class="text-xs font-bold text-slate-700 whitespace-nowrap">60%</span>
                    <div class="text-right whitespace-nowrap border-l border-slate-200/80 pl-4 ml-2">
                        <span class="text-[10px] text-slate-400 block uppercase font-medium">Target Selesai</span>
                        <strong class="text-xs font-bold text-slate-800">5 Okt 2026</strong>
                    </div>
                </div>

                {{-- Expandable Content --}}
                <div x-show="open" x-collapse class="mt-6 space-y-4 pt-5 border-t border-slate-100">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-slate-50/70 border border-slate-100 rounded-xl p-4">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">MITRA INDUSTRI</span>
                            <strong class="text-xs font-bold text-slate-800 block mt-1">CV. Tani Maju</strong>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">DOSEN PEMBIMBING PM</span>
                            <strong class="text-xs font-bold text-slate-800 block mt-1">Ir. Hendra Wijaya, M.T.</strong>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">PERAN ANDA DI TIM</span>
                            <strong class="text-xs font-bold text-[#2563EB] block mt-1">IoT Engineer &amp; Backend</strong>
                        </div>
                        <div>
                            <a href="{{ route('mahasiswa.task') }}" class="text-xs font-semibold text-[#2563EB] hover:underline block mt-2">Buka Task Project →</a>
                        </div>
                    </div>
                </div>
            </article>

            {{-- ── CARD 3: WEBSITE PROFIL PERUSAHAAN (Collapsed card) ── --}}
            <article
                x-data="{ open: false }"
                x-show="currentFilter === 'all' || currentFilter === 'done'"
                class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs transition"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-bold text-slate-400">PRJ-2026/002</span>
                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-[#2563EB] font-bold text-[11px]">Frontend Developer</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Selesai
                                </span>
                            </div>
                            <h2 class="text-base font-bold text-slate-900 tracking-tight mt-1.5">
                                Website Profil Perusahaan &amp; Portofolio Interaktif
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                PT. DEF Solusi <span class="mx-1">·</span> 3 Modul
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="open = !open"
                        class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-slate-600 bg-slate-50 hover:bg-slate-100 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    >
                        <span x-text="open ? 'Tutup Detail' : 'Lihat Detail'">Lihat Detail</span>
                        <span x-text="open ? '⌃' : '⌄'">⌄</span>
                    </button>
                </div>

                {{-- Progress Bar Row --}}
                <div class="mt-5 flex items-center justify-between gap-4">
                    <span class="text-xs font-semibold text-slate-500 whitespace-nowrap">Progress Proyek</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full transition-all duration-300" style="width: 100%"></div>
                    </div>
                    <span class="text-xs font-bold text-slate-700 whitespace-nowrap">100%</span>
                    <div class="text-right whitespace-nowrap border-l border-slate-200/80 pl-4 ml-2">
                        <span class="text-[10px] text-slate-400 block uppercase font-medium">Tanggal Rilis</span>
                        <strong class="text-xs font-bold text-slate-800">28 Sep 2026</strong>
                    </div>
                </div>

                {{-- Expandable Content --}}
                <div x-show="open" x-collapse class="mt-6 space-y-4 pt-5 border-t border-slate-100">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-slate-50/70 border border-slate-100 rounded-xl p-4">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">MITRA INDUSTRI</span>
                            <strong class="text-xs font-bold text-slate-800 block mt-1">PT. DEF Solusi</strong>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">STATUS VALIDASI</span>
                            <strong class="text-xs font-bold text-emerald-600 block mt-1">✓ Lulus UAT &amp; Selesai</strong>
                        </div>
                    </div>
                </div>
            </article>

            {{-- ── CARD 4: APLIKASI MANAJEMEN INVENTORI (Collapsed card) ── --}}
            <article
                x-data="{ open: false }"
                x-show="currentFilter === 'all' || currentFilter === 'running'"
                class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs transition"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-bold text-slate-400">PRJ-2026/006</span>
                                <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-bold text-[11px]">UI/UX &amp; Frontend</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                    Proposal / Kickoff
                                </span>
                            </div>
                            <h2 class="text-base font-bold text-slate-900 tracking-tight mt-1.5">
                                Aplikasi Manajemen Inventori Gudang Sekolah
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Dinas Pendidikan <span class="mx-1">·</span> 4 Modul
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="open = !open"
                        class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-slate-600 bg-slate-50 hover:bg-slate-100 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    >
                        <span x-text="open ? 'Tutup Detail' : 'Lihat Detail'">Lihat Detail</span>
                        <span x-text="open ? '⌃' : '⌄'">⌄</span>
                    </button>
                </div>

                {{-- Progress Bar Row --}}
                <div class="mt-5 flex items-center justify-between gap-4">
                    <span class="text-xs font-semibold text-slate-500 whitespace-nowrap">Progress Proyek</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-indigo-600 h-full rounded-full transition-all duration-300" style="width: 25%"></div>
                    </div>
                    <span class="text-xs font-bold text-slate-700 whitespace-nowrap">25%</span>
                    <div class="text-right whitespace-nowrap border-l border-slate-200/80 pl-4 ml-2">
                        <span class="text-[10px] text-slate-400 block uppercase font-medium">Target Selesai</span>
                        <strong class="text-xs font-bold text-slate-800">18 Okt 2026</strong>
                    </div>
                </div>

                {{-- Expandable Content --}}
                <div x-show="open" x-collapse class="mt-6 space-y-4 pt-5 border-t border-slate-100">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-slate-50/70 border border-slate-100 rounded-xl p-4">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">MITRA INDUSTRI</span>
                            <strong class="text-xs font-bold text-slate-800 block mt-1">Dinas Pendidikan</strong>
                        </div>
                    </div>
                </div>
            </article>

        </div>

    </div>

</x-student-layout>
