@php
    $user = $user ?? session('user', []);
@endphp

<x-student-layout active="task-saya" title="Task & Progres Saya">

    {{-- ===== HEADER CARD ===== --}}
    <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
        {{-- gradient dekoratif --}}
        <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                Workspace Mahasiswa
            </span>
            <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                Task &amp; Progres <span class="text-[#FFB800]">Saya</span>
            </h1>
            <p class="text-sm text-slate-400 mt-1.5 max-w-2xl leading-relaxed">
                Kelola daftar tugas proyek Teaching Factory, laporkan capaian &amp; kendala pengerjaan, serta pantau catatan evaluasi dari Dosen PM secara terstruktur dan transparan.
            </p>
        </div>
    </div>

    {{-- ===== 4 STAT CARDS ===== --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" aria-label="Ringkasan task">
        {{-- Card 1: Total Tugas Terdaftar --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold tracking-wider uppercase">
                <span>TOTAL TUGAS TERDAFTAR</span>
                <span class="w-9 h-9 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </div>
            <div>
                <strong class="text-3xl font-extrabold text-[#0F172A] tracking-tight block">14</strong>
                <span class="text-xs text-slate-400 mt-1 block">Tugas Aktif <span class="mx-1">·</span> 2 Proyek Terdaftar Semester Ini</span>
            </div>
        </div>

        {{-- Card 2: Sedang Dikerjakan --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold tracking-wider uppercase">
                <span>SEDANG DIKERJAKAN</span>
                <span class="w-9 h-9 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </span>
            </div>
            <div>
                <strong class="text-3xl font-extrabold text-[#0F172A] tracking-tight block">5</strong>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 text-[10px] font-bold">IN_PROGRESS</span>
                    <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-blue-600 h-full rounded-full" style="width: 36%"></div>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-500">36%</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Review Dosen PM --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold tracking-wider uppercase">
                <span>REVIEW DOSEN PM</span>
                <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </span>
            </div>
            <div>
                <strong class="text-3xl font-extrabold text-[#0F172A] tracking-tight block">3</strong>
                <span class="text-xs text-slate-400 mt-1 block">Menunggu Evaluasi <span class="mx-1">·</span> Status PENDING / TESTING</span>
            </div>
        </div>

        {{-- Card 4: Perlu Revisi Mahasiswa --}}
        <div class="bg-white border border-amber-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold tracking-wider uppercase">
                <span>PERLU REVISI MAHASISWA</span>
                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
            </div>
            <div>
                <strong class="text-3xl font-extrabold text-amber-500 tracking-tight block">2</strong>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 text-[10px] font-bold">NEEDS_IMPROVEMENT</span>
                    <span class="text-xs text-slate-400">Prioritas tindakan</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== ACTIVE TASK DETAIL CARD ===== --}}
    <article class="bg-white border border-slate-200/80 rounded-2xl p-7 shadow-xs space-y-6">

        {{-- Header Badges & Meta --}}
        <div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-lg bg-blue-50 border border-blue-200/60 text-[#2563EB] text-xs font-semibold">
                        PRJ-2026/001 · Sistem Informasi UMKM &amp; Inventaris Digital
                    </span>
                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>PM: NEEDS_IMPROVEMENT</span>
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 mt-3">
                <span class="flex items-center gap-1.5">
                    <span class="text-slate-500 font-medium">Status Tugas:</span>
                    <span class="px-2 py-0.5 rounded bg-blue-600 text-white font-extrabold text-[10px] tracking-wide">IN_PROGRESS</span>
                </span>
                <span>·</span>
                <span>Jatuh Tempo: <strong class="text-slate-700 font-bold">12 Okt 2026</strong></span>
                <span>·</span>
                <span>Task ID: <code class="text-slate-700 font-semibold font-mono">#TSK-00384</code></span>
                <span>·</span>
                <span class="uppercase tracking-wider font-semibold text-slate-400">Kategori: Integrasi Backend Payment</span>
            </div>

            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-3">
                Implementasi Integrasi Payment Gateway QRIS &amp; Callback Webhook
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mt-1.5">
                Membuat endpoint webhook handler untuk menerima notifikasi settlement pembayaran QRIS dinamis dari Midtrans/Xendit, melakukan verifikasi signature hash SHA512, serta memperbarui status transaksi invoice secara realtime di database PostgreSQL.
            </p>
        </div>

        {{-- Progress Bar Section --}}
        <div class="space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-600">Progres Pengerjaan Aktual</span>
                <span class="font-bold text-slate-800">75% Selesai</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                <div class="bg-[#0B3B82] h-full rounded-full transition-all duration-300" style="width: 75%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-0.5">
                <span>0% Mulai</span>
                <span class="text-slate-500 font-medium">50% Mid-Phase Testing</span>
                <span>100% Production Ready</span>
            </div>
        </div>

        {{-- 2-Column Split: Mahasiswa vs Dosen PM --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">

            {{-- ── LEFT: LAPORAN PROGRES MAHASISWA ── --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#2563EB]"></span>
                        <h3 class="text-xs font-bold text-slate-900">Laporan Progres Pengerjaan (Mahasiswa)</h3>
                    </div>
                    <span class="text-[11px] text-slate-400">Terakhir diubah: Kemarin, 21:00</span>
                </div>

                {{-- Box 1: Capaian Selesai --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500">
                        Capaian yang Telah Diselesaikan <code class="text-slate-400 font-mono text-[10px]">[ACHIEVED_SUMMARY]</code>
                    </label>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-xs text-slate-700 leading-relaxed font-normal">
                        Berhasil membuat signature verification SHA512 dan tautan endpoint <code class="bg-slate-200/70 text-slate-800 px-1 py-0.5 rounded font-mono text-[11px]">/api/v1/payments/webhook</code>. Pengujian simulasi sukses untuk 12 skenario transaksi di sandbox environment tanpa kendala deserialization.
                    </div>
                </div>

                {{-- Box 2: Kendala / Blocker --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-amber-800">
                        Kendala / Blocker yang Dihadapi <code class="text-amber-600 font-mono text-[10px]">[BLOCKERS_SUMMARY]</code> · Diberitahukan ke dosen/tim
                    </label>
                    <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-3.5 text-xs text-amber-900 leading-relaxed">
                        Dokumentasi simulasi refund otomatis dari sandbox pihak ketiga masih membutuhkan akses konfigurasi sistem tambahan dari PIC mitra UMKM.
                    </div>
                </div>

                {{-- Box 3: Lampiran Dokumen & Repositori --}}
                <div class="space-y-2">
                    <label class="block text-[11px] font-bold text-slate-500">
                        Lampiran Dokumen &amp; Repositori <code class="text-slate-400 font-mono text-[10px]">[REPORT_ATTACHMENTS]</code>
                    </label>
                    <div class="flex flex-wrap items-center gap-2">
                        {{-- Attachment 1 --}}
                        <div class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-700 shadow-2xs transition cursor-pointer">
                            <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="font-medium">webhook-test-result-v2.pdf</span>
                            <span class="text-slate-400 text-[10px]">3.2 MB</span>
                            <svg class="w-3.5 h-3.5 text-slate-400 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>

                        {{-- Attachment 2 --}}
                        <div class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-700 shadow-2xs transition cursor-pointer">
                            <svg class="w-4 h-4 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                            <span class="font-medium">PR #42: feat/payment-qris-webhook</span>
                            <span class="text-[#2563EB]">↗</span>
                        </div>
                    </div>

                    {{-- Add Link Form --}}
                    <div class="flex items-center gap-2 pt-1">
                        <input
                            type="text"
                            placeholder="Tambahkan link URL (commit GitHub, Figma, atau cloud drive)..."
                            class="flex-1 bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 focus:outline-hidden focus:border-[#2563EB] transition"
                        />
                        <button type="button" class="px-3.5 py-2 rounded-xl bg-blue-50 text-[#2563EB] hover:bg-blue-100 font-semibold text-xs transition cursor-pointer whitespace-nowrap">
                            + Tautkan
                        </button>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition cursor-pointer">
                        Batal Perubahan
                    </button>
                    <button type="button" class="px-5 py-2.5 bg-[#0B3B82] hover:bg-[#082a5c] text-white rounded-xl text-xs font-bold shadow-xs transition cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        <span>Perbarui Progres &amp; Simpan Laporan</span>
                    </button>
                </div>
            </div>

            {{-- ── RIGHT: REVIEW & EVALUASI DOSEN PM ── --}}
            <div class="bg-[#F0F6FF] border border-[#D0E2FC] rounded-2xl p-5 space-y-4 flex flex-col justify-between">
                <div class="space-y-4">
                    {{-- Heading --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </span>
                            <h3 class="text-xs font-bold text-slate-900">Review &amp; Evaluasi Dosen PM</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-700 font-extrabold text-[10px]">
                            NEEDS_IMPROVEMENT
                        </span>
                    </div>

                    {{-- Evaluator Profile --}}
                    <div class="bg-white border border-blue-100 rounded-xl p-3 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-[#0F274A] text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                            DS
                        </div>
                        <div class="min-w-0 flex-1">
                            <strong class="text-xs font-bold text-slate-900 block truncate">Dr. Dimas Setiawan, S.T., M.Cs.</strong>
                            <span class="text-[10px] text-slate-400 block truncate mt-0.5">Dosen/Project Manager &amp; Evaluator Teknis</span>
                        </div>
                    </div>

                    {{-- Timestamp info --}}
                    <div class="flex items-center gap-1.5 text-[11px] text-slate-500">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Divalidasi pada: <strong class="text-slate-700 font-medium">30 Sep 2026, 16:40 WIB</strong></span>
                    </div>

                    {{-- Catatan Revisi Card (Yellow accent card) --}}
                    <div class="bg-amber-50/80 border border-amber-200 border-l-4 border-l-amber-400 rounded-r-xl p-3.5 space-y-1.5">
                        <div class="flex items-center gap-1.5 text-amber-900 font-bold text-xs">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zm-4 0h-2v2h2V9z" clip-rule="evenodd"/>
                            </svg>
                            <span>Catatan Revisi dari Dosen PM</span>
                        </div>
                        <p class="text-xs text-amber-900/90 leading-relaxed italic">
                            &ldquo;Logika webhook sudah rapi, Alvian. Namun <strong class="text-amber-950 font-bold">idempotency key handling</strong> pada database repository harus ada agar tidak terjadi duplicate processing jika webhook dikirim ulang oleh payment gateway saat terjadi timeout jaringan.&rdquo;
                        </p>
                    </div>
                </div>

                {{-- SLA info & Link --}}
                <div class="flex items-center justify-between text-xs pt-3 border-t border-blue-200/60">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        SLA EVALUASI: MAKS. 2x24 Jam
                    </span>
                    <a href="#" class="text-xs font-semibold text-[#2563EB] hover:underline">
                        Lihat Riwayat Verifikasi Lain
                    </a>
                </div>
            </div>

        </div>

    </article>

    {{-- ===== LOWER TASK LIST ===== --}}
    <section class="space-y-4">
        {{-- Task Row 1: Konfigurasi MQTT Broker --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-slate-300 transition">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold text-[11px]">
                        PRJ-2026/004 · Smart Farming IoT
                    </span>
                    <span class="px-2 py-0.5 rounded bg-blue-50 text-[#2563EB] font-bold text-[10px]">
                        TESTING
                    </span>
                </div>
                <h3 class="text-base font-bold text-slate-900 mt-1.5">
                    Konfigurasi MQTT Broker &amp; Telemetri Sensor Tanah
                </h3>
                <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                    Setup EMQX cloud broker, implementasi TLS/SSL certificates, dan penanganan auto-reconnect packet MQTT...
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                {{-- Progress --}}
                <div class="w-32">
                    <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                        <span>Progres</span>
                        <strong class="text-slate-800">75%</strong>
                    </div>
                    <div class="bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-[#0B3B82] h-full rounded-full" style="width: 75%"></div>
                    </div>
                </div>

                {{-- Jatuh Tempo --}}
                <div class="text-left md:text-right min-w-[90px]">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">JATUH TEMPO</span>
                    <strong class="text-xs font-bold text-slate-800 block mt-0.5">05 Okt 2026</strong>
                </div>

                {{-- Status Revisi PM --}}
                <div class="text-left md:text-right min-w-[110px]">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">STATUS REVISI PM</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-bold">
                        <span>✓</span> APPROVED
                    </span>
                </div>

                {{-- Button Detail --}}
                <button type="button" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition cursor-pointer">
                    Detail ⌄
                </button>
            </div>
        </div>

        {{-- Task Row 2: Slicing UI Responsive --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-slate-300 transition">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold text-[11px]">
                        PRJ-2026/001 · Sistem Informasi UMKM
                    </span>
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-bold text-[10px]">
                        TODO
                    </span>
                </div>
                <h3 class="text-base font-bold text-slate-900 mt-1.5">
                    Slicing UI Responsive Halaman Dashboard Inventori
                </h3>
                <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                    Penerjemahan mockup Figma ke komponen Tailwind CSS dengan modular view dan chart visualisasi...
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                {{-- Progress --}}
                <div class="w-32">
                    <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                        <span>Progres</span>
                        <strong class="text-slate-800">0%</strong>
                    </div>
                    <div class="bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-[#0B3B82] h-full rounded-full" style="width: 0%"></div>
                    </div>
                </div>

                {{-- Jatuh Tempo --}}
                <div class="text-left md:text-right min-w-[90px]">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">JATUH TEMPO</span>
                    <strong class="text-xs font-bold text-slate-800 block mt-0.5">18 Okt 2026</strong>
                </div>

                {{-- Status Revisi PM --}}
                <div class="text-left md:text-right min-w-[110px]">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">STATUS REVISI PM</span>
                    <span class="text-xs text-slate-400 font-medium">PENDING</span>
                </div>

                {{-- Button Detail --}}
                <button type="button" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition cursor-pointer">
                    Detail ⌄
                </button>
            </div>
        </div>

        {{-- Task Row 3: Penyusunan API Documentation --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-slate-300 transition">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold text-[11px]">
                        PRJ-2026/001 · Sistem Informasi UMKM
                    </span>
                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold text-[10px]">
                        DONE
                    </span>
                </div>
                <h3 class="text-base font-bold text-slate-900 mt-1.5">
                    Penyusunan API Documentation Swagger / OpenAPI v3
                </h3>
                <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                    Dokumentasi lengkap seluruh route API auth, inventory, dan reports beserta format payload schema...
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                {{-- Progress --}}
                <div class="w-32">
                    <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                        <span>Progres</span>
                        <strong class="text-slate-800">100%</strong>
                    </div>
                    <div class="bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full" style="width: 100%"></div>
                    </div>
                </div>

                {{-- Selesai / Dilepaskan --}}
                <div class="text-left md:text-right min-w-[90px]">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">DILEPASKAN</span>
                    <strong class="text-xs font-bold text-slate-800 block mt-0.5">28 Sep 2026</strong>
                </div>

                {{-- Status Revisi PM --}}
                <div class="text-left md:text-right min-w-[110px]">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">STATUS REVISI PM</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-bold">
                        <span>✓</span> APPROVED
                    </span>
                </div>

                {{-- Button Detail --}}
                <button type="button" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition cursor-pointer">
                    Detail ⌄
                </button>
            </div>
        </div>

        {{-- Pagination footer bar --}}
        <div class="flex flex-wrap items-center justify-between gap-4 pt-3 text-xs text-slate-400">
            <div>
                Menampilkan <strong>1 - 4</strong> dari total <strong>14</strong> Tugas Aktif <span class="mx-1">·</span> Tampilkan: <span class="text-slate-600 font-semibold">10/hlm</span>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" class="w-8 h-8 rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-50 flex items-center justify-center transition cursor-pointer">‹</button>
                <button type="button" class="w-8 h-8 rounded-lg bg-[#0B3B82] text-white font-bold flex items-center justify-center shadow-2xs">1</button>
                <button type="button" class="w-8 h-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition cursor-pointer">2</button>
                <button type="button" class="w-8 h-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition cursor-pointer">3</button>
                <button type="button" class="w-8 h-8 rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-50 flex items-center justify-center transition cursor-pointer">›</button>
            </div>
        </div>
    </section>

</x-student-layout>
