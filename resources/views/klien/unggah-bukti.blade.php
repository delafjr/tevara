{{-- == ( halaman unggah bukti pembayaran - klien ) == --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Unggah bukti pembayaran termin proyek TEVARA. Konfirmasi transfer bank giro atau virtual account untuk verifikasi bagian keuangan.">
    <title>Unggah Bukti Pembayaran - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased min-h-screen">

@php
    $user = $user ?? session('user', []);
    $userName = $user['name'] ?? 'Budi Santoso';
    $proposalCode = $proposal['proposal_code'] ?? ('PRJ-' . ($proposal['id'] ?? '-'));
    $proposalTitle = $proposal['title'] ?? 'Proyek';
    $termSequence = $payment['term_sequence'] ?? 1;
    $amount = (float) ($payment['amount'] ?? 0);
    $formatRupiah = fn ($val) => 'Rp ' . number_format((float) $val, 0, ',', '.');
    $dueDate = !empty($payment['due_date']) ? \Carbon\Carbon::parse($payment['due_date'])->translatedFormat('d F Y') : '-';
@endphp

<div class="flex min-h-screen"
     x-data="{
         selectedFileName: '',
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
         handleFileChange(event) {
             const file = event.target.files[0];
             if (file) {
                 this.selectedFileName = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
             } else {
                 this.selectedFileName = '';
             }
         }
     }">

    {{-- Sidebar Komponen --}}
    <x-sidebar active="tagihan" />

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

        <main class="flex-1 px-8 py-7 space-y-6">

            {{-- Breadcrumb Navigation --}}
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('klien.tagihan') }}"
                   class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82] transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke Daftar Tagihan & Pembayaran
                </a>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-mono font-semibold text-slate-400 bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-2xs">
                        {{ $proposalCode }}
                    </span>
                    <span class="text-[11px] font-bold px-3 py-1.5 rounded-lg bg-blue-50 text-[#0B3B82] border border-blue-200">
                        Termin {{ $termSequence }}
                    </span>
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

            @if ($errors->any() || session('error') || ($error ?? null))
                <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 shadow-xs">
                    <svg class="w-5 h-5 flex-shrink-0 text-red-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    <div>
                        <p class="text-xs font-bold text-red-900">Perhatian</p>
                        <p class="text-xs font-medium text-red-700 leading-relaxed">{{ $errors->first() ?? session('error') ?? $error }}</p>
                    </div>
                </div>
            @endif

            {{-- Alert Jika Sebelumnya Ditolak --}}
            @if(!empty($payment['verification_notes']) && ($payment['status'] ?? '') === 'REJECTED')
                <div class="rounded-2xl border border-red-200 bg-red-50/80 p-5 shadow-xs flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-xs font-bold text-red-900">Bukti Pembayaran Sebelumnya Perlu Diperbaiki</h4>
                        <p class="text-xs text-red-700 leading-relaxed">
                            <strong>Catatan dari Bagian Keuangan TEFA:</strong> "{{ $payment['verification_notes'] }}"
                        </p>
                        <p class="text-[11px] text-red-600">Silakan unggah kembali berkas bukti transfer yang jelas dan sesuai nominal tagihan.</p>
                    </div>
                </div>
            @endif

            {{-- Header Card --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#EFF6FF] via-[#F4F8FE] to-[#FCF8EC] border border-blue-100/70 px-8 py-7 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="relative z-10 max-w-2xl">
                    <div class="inline-flex items-center gap-2 bg-[#EAF3FF] text-[#0B3B82] text-[10.5px] font-bold tracking-wide uppercase px-3 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        F-09 • PEMBAYARAN PROYEK
                    </div>
                    <h1 class="text-2xl lg:text-[28px] font-extrabold text-[#1E293B] tracking-tight">
                        Unggah Bukti <span class="text-[#FFB800]">Pembayaran</span>
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-2 leading-relaxed">
                        Kirimkan berkas struk atau slip bukti transfer termin proyek untuk diverifikasi oleh Bagian Keuangan TEFA Politeknik.
                    </p>
                </div>

                <div class="relative z-10 flex flex-col items-start md:items-end gap-1 bg-white/80 backdrop-blur-xs p-4 rounded-2xl border border-blue-100/60 shadow-2xs flex-shrink-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nominal Tagihan Termin {{ $termSequence }}</span>
                    <span class="text-2xl font-extrabold text-emerald-600 font-mono">{{ $formatRupiah($amount) }}</span>
                    <span class="text-[11px] text-slate-400">Jatuh Tempo: {{ $dueDate }}</span>
                </div>
            </div>

            {{-- Konten Grid 2 Kolom --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">

                {{-- Kolom Kiri (5 Kolom): Rincian Tagihan & Rekening Resmi --}}
                <div class="lg:col-span-5 space-y-6">

                    {{-- Card Ringkasan Proyek --}}
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                            <h3 class="text-sm font-bold text-slate-800">Rincian Tagihan Proyek</h3>
                            <span class="text-[11px] font-bold text-[#0B3B82] bg-blue-50 px-2.5 py-0.5 rounded-md">
                                Termin {{ $termSequence }}
                            </span>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div>
                                <p class="text-[11px] text-slate-400 font-medium">Judul Proyek</p>
                                <p class="text-sm font-bold text-slate-800 mt-0.5 leading-snug">{{ $proposalTitle }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3 pt-1">
                                <div>
                                    <p class="text-[11px] text-slate-400 font-medium">Kode Proyek</p>
                                    <p class="font-mono font-bold text-[#0B3B82] mt-0.5">{{ $proposalCode }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] text-slate-400 font-medium">Jatuh Tempo</p>
                                    <p class="font-semibold text-slate-700 mt-0.5">{{ $dueDate }}</p>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                                <span class="font-bold text-slate-700">Total Nominal Tagihan:</span>
                                <span class="text-base font-extrabold text-emerald-600 font-mono">{{ $formatRupiah($amount) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Card Rekening Pembayaran Resmi TEVARA --}}
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800">Rekening Tujuan Resmi</h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">Gunakan salah satu kanal resmi di bawah</p>
                            </div>
                            <span class="inline-flex items-center gap-1 text-[10.5px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                                Resmi TEFA
                            </span>
                        </div>

                        <div class="space-y-3.5">
                            {{-- Bank Mandiri Giro --}}
                            <div class="p-4 rounded-2xl bg-[#F8FAFD] border border-slate-200/80 space-y-2 hover:border-[#0B3B82] transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10.5px] font-bold px-2 py-0.5 rounded bg-blue-100 text-[#0B3B82]">BANK MANDIRI (GIRO)</span>
                                    <button type="button"
                                            @click="copyText('1420099281721', 'Rekening Mandiri')"
                                            class="text-xs font-bold text-[#0B3B82] hover:underline inline-flex items-center gap-1 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>
                                        Salin
                                    </button>
                                </div>
                                <div>
                                    <p class="text-base font-extrabold text-slate-800 font-mono tracking-wide">142-00-9928172-1</p>
                                    <p class="text-[11px] text-slate-500 font-medium">a.n. Teaching Factory Politeknik</p>
                                </div>
                            </div>

                            {{-- BCA Virtual Account --}}
                            <div class="p-4 rounded-2xl bg-[#F8FAFD] border border-slate-200/80 space-y-2 hover:border-[#0B3B82] transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10.5px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">BCA VIRTUAL ACCOUNT</span>
                                    <button type="button"
                                            @click="copyText('8872008123456789', 'No. VA BCA')"
                                            class="text-xs font-bold text-[#0B3B82] hover:underline inline-flex items-center gap-1 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>
                                        Salin
                                    </button>
                                </div>
                                <div>
                                    <p class="text-base font-extrabold text-slate-800 font-mono tracking-wide">88720-08123456789</p>
                                    <p class="text-[11px] text-slate-500 font-medium">a.n. TEVARA - {{ $userName }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Kolom Kanan (7 Kolom): Formulir Bukti Transfer --}}
                <div class="lg:col-span-7">
                    <form method="POST"
                          action="{{ route('klien.tagihan.upload-proof', ['proposal' => $proposal['id'], 'payment' => $payment['id']]) }}"
                          enctype="multipart/form-data"
                          class="bg-white rounded-3xl border border-slate-100 shadow-xs p-8 space-y-6">
                        @csrf

                        <div class="border-b border-slate-100 pb-4">
                            <h2 class="text-base font-bold text-slate-800">Formulir Konfirmasi Pembayaran</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Isi rincian transaksi dan lampirkan bukti pembayaran yang sah.</p>
                        </div>

                        {{-- Metode Pembayaran --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">
                                Metode / Kanal Pembayaran yang Digunakan <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_method" required class="w-full bg-[#F8FAFC] border border-slate-200 rounded-2xl px-4 py-3 text-xs text-slate-700 focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition">
                                <option value="BANK_TRANSFER" {{ old('payment_method') === 'BANK_TRANSFER' ? 'selected' : '' }}>
                                    Transfer Bank Mandiri Giro (142-00-9928172-1)
                                </option>
                                <option value="VIRTUAL_ACCOUNT" {{ old('payment_method') === 'VIRTUAL_ACCOUNT' ? 'selected' : '' }}>
                                    BCA Virtual Account (88720-08123456789)
                                </option>
                                <option value="CASH" {{ old('payment_method') === 'CASH' ? 'selected' : '' }}>
                                    Setor Tunai Langsung ke Kasir TEFA
                                </option>
                                <option value="OTHER" {{ old('payment_method') === 'OTHER' ? 'selected' : '' }}>
                                    Transfer Antar Bank (BI-FAST / Realtime Online)
                                </option>
                            </select>
                        </div>

                        {{-- Grid Tanggal & No Referensi --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-2">
                                    Tanggal Transfer <span class="text-red-500">*</span>
                                </label>
                                <input type="date"
                                       name="payment_date"
                                       required
                                       value="{{ old('payment_date', date('Y-m-d')) }}"
                                       class="w-full bg-[#F8FAFC] border border-slate-200 rounded-2xl px-4 py-3 text-xs text-slate-700 focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-2">
                                    Nomor Referensi / 4 Digit Rekening
                                </label>
                                <input type="text"
                                       name="transfer_reference"
                                       value="{{ old('transfer_reference') }}"
                                       placeholder="Contoh: REF98213 / Rek 4321"
                                       maxlength="255"
                                       class="w-full bg-[#F8FAFC] border border-slate-200 rounded-2xl px-4 py-3 text-xs text-slate-700 focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/10 transition">
                            </div>
                        </div>

                        {{-- File Upload Box --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">
                                Berkas Bukti Transfer (JPG, PNG, atau PDF max 5MB) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative border-2 border-dashed border-slate-200 hover:border-[#0B3B82] rounded-3xl p-7 text-center bg-[#F8FAFC] transition cursor-pointer group">
                                <input type="file"
                                       name="proof"
                                       required
                                       accept=".jpg,.jpeg,.png,.pdf"
                                       @change="handleFileChange($event)"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                
                                <div class="space-y-2 pointer-events-none">
                                    <div class="w-12 h-12 rounded-2xl bg-[#EAF3FF] text-[#0B3B82] group-hover:scale-110 transition flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                                        </svg>
                                    </div>
                                    <template x-if="!selectedFileName">
                                        <div>
                                            <p class="text-xs font-bold text-slate-700">
                                                Klik untuk memilih berkas atau seret file ke sini
                                            </p>
                                            <p class="text-[11px] text-slate-400 mt-1">
                                                Format file: JPG, PNG, atau PDF (Ukuran maksimal 5MB)
                                            </p>
                                        </div>
                                    </template>
                                    <template x-if="selectedFileName">
                                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl py-2 px-3 inline-flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                            <span class="text-xs font-bold" x-text="selectedFileName"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-2">
                                Pastikan foto / file bukti transfer menampilkan nomor rekening tujuan, tanggal, dan nominal yang jelas terbaca.
                            </p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                            <a href="{{ route('klien.tagihan') }}"
                               class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-semibold text-xs transition">
                                Batal
                            </a>
                            <button type="submit"
                                    class="px-6 py-2.5 rounded-xl bg-[#0B3B82] hover:bg-[#082a5c] text-white font-bold text-xs shadow-sm shadow-[#0B3B82]/25 transition cursor-pointer flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                                </svg>
                                <span>Kirim Bukti Pembayaran</span>
                            </button>
                        </div>

                    </form>
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
