<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Revisi Proposal - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user', []);
    $proposal = is_array($proposal ?? null) ? $proposal : [];
    $error = $error ?? null;
    $proposalId = $proposal['id'] ?? null;
    $proposalCode = $proposal['proposal_code'] ?? '-';
    $clientName = $proposal['client_name'] ?? $proposal['pic']['name'] ?? '-';
    $projectTitle = $proposal['title'] ?? '-';
    $existingNotes = $proposal['review_notes'] ?? '';
    $currentStatus = $proposal['status'] ?? '-';

    $statusIsRevision = str_contains(strtolower($currentStatus), 'revision');
    $statusLabel = $statusIsRevision ? 'Improvement (Revisi)' : $currentStatus;
@endphp

<div class="flex min-h-screen">
    <x-sidebar active="validasi-proposal" />

    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 pb-32 space-y-6">

            {{-- Breadcrumb --}}
            <div class="flex items-center justify-between gap-4">
                <a href="{{ $proposalId ? route('pengurus-tefa.validasi-proposal.detail', $proposalId) : route('pengurus-tefa.validasi-proposal') }}"
                   class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82] transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke Detail Proposal
                </a>
                @if ($proposalCode !== '-')
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-mono font-semibold text-slate-400 bg-white border border-slate-200 px-3 py-1.5 rounded-lg">
                            {{ $proposalCode }}
                        </span>
                        <span class="text-[11px] font-bold px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200">
                            {{ $statusLabel }}
                        </span>
                    </div>
                @endif
            </div>

            @if (session('error') || $error)
                <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                    <p class="text-sm font-medium leading-relaxed">{{ session('error') ?? $error }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                    <p class="text-sm font-medium leading-relaxed">{{ $errors->first() }}</p>
                </div>
            @endif

            {{-- Hero Header --}}
            <header class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6">
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-amber-50/60 to-orange-50/80 pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-800 text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2 border border-amber-200">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                            </svg>
                            Sisi Pengurus TEFA &mdash; Form Revisi
                        </div>
                        <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                            Permintaan Revisi <span class="text-amber-600">Proposal Proyek</span>
                        </h1>
                        <p class="text-sm text-slate-400 mt-1 max-w-2xl leading-relaxed">
                            Kirimkan catatan perbaikan atau penyesuaian (misalnya harga/anggaran atau ruang lingkup) kepada klien. Klien akan menerima status <strong>Improvement</strong> dan dapat mengubah field yang diminta.
                        </p>
                    </div>
                </div>
            </header>

            {{-- Form Permintaan Revisi --}}
            <form method="POST"
                  action="{{ route('pengurus-tefa.validasi-proposal.revisi.store', $proposalId) }}"
                  class="space-y-6">
                @csrf

                {{-- SECTION 1: NAMA PENGURUS TEFA (READ-ONLY DARI SESSION) --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-7">
                    <div class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-[#0B3B82] text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                                1
                            </span>
                            <div>
                                <h2 class="text-sm font-bold text-[#1E293B]">Identitas Pengurus TEFA (Reviewer)</h2>
                                <p class="text-xs text-slate-400">Nama pengurus terisi otomatis dari session login yang aktif</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                            </svg>
                            Read-Only (Session Aktif)
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Nama Pengurus TEFA --}}
                        <div>
                            <label for="pengurus_name" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                Nama Pengurus TEFA <span class="text-slate-300">(Otomatis dari Session)</span>
                            </label>
                            <div class="relative">
                                <input id="pengurus_name"
                                       name="pengurus_name"
                                       type="text"
                                       value="{{ $user['name'] ?? session('user.name', 'Pengurus TEFA') }}"
                                       readonly
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm font-semibold text-slate-700 cursor-not-allowed focus:outline-none select-none">
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Nama peninjau ini akan tercatat dalam riwayat revisi proposal.</p>
                        </div>

                        {{-- Role / Email Pengurus --}}
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                Akun Peninjau
                            </label>
                            <input type="text"
                                   value="{{ $user['email'] ?? session('user.email', 'pengurus@tevara.polines.ac.id') }} &bull; Pengurus TEFA"
                                   readonly
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-600 cursor-not-allowed focus:outline-none select-none">
                            <p class="text-[11px] text-slate-400 mt-1">Hak akses validasi proposal Teaching Factory Polines.</p>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: FORM KECIL (NAMA KLIEN/PROJEK & CATATAN REVISI) --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-7 space-y-5">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                        <span class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                            2
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-[#1E293B]">Form Revisi Proyek</h2>
                            <p class="text-xs text-slate-400">Rincian proposal dan catatan spesifik apa yang harus diperbaiki oleh klien</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Nama Klien / Mitra --}}
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                Nama Klien / Instansi
                            </label>
                            <input type="text"
                                   value="{{ $clientName }}"
                                   readonly
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-800 cursor-default focus:outline-none">
                        </div>

                        {{-- Nama / Judul Projek --}}
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                Nama Projek / Judul Proposal
                            </label>
                            <input type="text"
                                   value="{{ $projectTitle }}"
                                   readonly
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-800 cursor-default focus:outline-none">
                        </div>
                    </div>

                    {{-- Catatan apa yang perlu direvisi klien --}}
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <label for="review_notes" class="block text-[11px] font-bold uppercase tracking-wider text-slate-600">
                                Catatan Apa yang Perlu Direvisi Klien <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[11px] font-medium text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                                Wajib diisi jelas untuk klien
                            </span>
                        </div>

                        <textarea id="review_notes"
                                  name="review_notes"
                                  rows="6"
                                  required
                                  minlength="5"
                                  maxlength="2000"
                                  placeholder="Tuliskan catatan detail mengenai apa yang harus direvisi oleh pihak klien.&#10;&#10;Contoh:&#10;1. Mohon sesuaikan estimasi harga/anggaran proyek menjadi maksimal Rp 25.000.000 sesuai plafon kategori Web.&#10;2. Rincian deskripsi pada Modul Pembayaran harap diperjelas integrasinya.&#10;3. Target pengguna mohon dilengkapi."
                                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#1E293B] placeholder:text-slate-300 focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/15 transition leading-relaxed">{{ old('review_notes', $existingNotes) }}</textarea>

                        @error('review_notes')
                            <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror

                        <div class="mt-2.5 flex items-start gap-2.5 text-xs text-slate-500 bg-[#F8FAFC] border border-slate-200 rounded-xl p-3">
                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                            </svg>
                            <p class="leading-relaxed">
                                Catatan ini akan otomatis ditampilkan kepada klien saat membuka menu <strong>Detail Projek Saya</strong>. Status proposal akan berubah menjadi <strong>Improvement</strong> dan klien dapat langsung mengedit bagian yang diminta (seperti harga, modul, dsb.) lalu mengirimkannya kembali ke pengurus TEFA.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                    <a href="{{ $proposalId ? route('pengurus-tefa.validasi-proposal.detail', $proposalId) : route('pengurus-tefa.validasi-proposal') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                        Batal
                    </a>

                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-amber-600 hover:bg-amber-700 px-6 py-3 text-xs font-bold text-white shadow-sm shadow-amber-600/30 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                        </svg>
                        Kirim Permintaan Revisi ke Klien
                    </button>
                </div>
            </form>

        </main>
    </div>
</div>
</body>
</html>

