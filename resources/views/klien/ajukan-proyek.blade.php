{{-- == ( halaman ajukan proyek baru - klien ) == --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Ajukan proyek baru bersama TEVARA Teaching Factory Polines. Isi data klien, rincian kebutuhan proyek, dan estimasi deadline dalam satu formulir.">
    <title>Ajukan Proyek Baru - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">

@php
    $user = $user ?? session('user', []);
    $profile = [
        'instansi' => $user['company_name'] ?? $user['name'] ?? '',
        'pic'      => $user['name'] ?? '',
        'telepon'  => $user['phone_number'] ?? '',
        'email'    => $user['email'] ?? '',
        'jabatan'  => $user['position'] ?? '',
    ];
    $deadlineMin = now()->addDays(30)->toDateString();
    $categories = [
        1 => ['name' => 'Web',     'icon' => 'web'],
        2 => ['name' => 'Mobile',  'icon' => 'mobile'],
        3 => ['name' => 'Desktop', 'icon' => 'desktop'],
        4 => ['name' => 'IoT',     'icon' => 'signal'],
        5 => ['name' => 'Other',   'icon' => 'other'],
    ];

    // Modul dikirim sebagai JSON string dari repeater Alpine (hidden input) atau sudah array (flash old()).
    $oldModul = old('modul');
    if (is_string($oldModul)) {
        $oldModul = json_decode($oldModul, true);
    }
    $modulesDefault = array_values(array_filter((array) $oldModul, fn ($m) => !empty($m['judul'])))
        ?: [['judul' => '', 'deskripsi' => '']];
@endphp

<div class="flex min-h-screen">

    {{-- == ( sidebar komponen DRY ) == --}}
    <x-sidebar active="ajukan-projek" />

    <div class="flex-1 ml-64 flex flex-col min-h-screen">

        @if (session('success'))
            <div class="mx-8 mt-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <p class="text-sm font-medium leading-relaxed">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-8 mt-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                <p class="text-sm font-medium leading-relaxed">{{ $errors->first() }}</p>
            </div>
        @endif

        {{-- == ( form utama : alpine scope untuk kategori + modul ) == --}}
        <form id="form-ajukan-proyek"
              method="POST"
              action="{{ route('klien.ajukan-proyek.store') }}"
              enctype="multipart/form-data"
              x-data="{
                  kategori: {{ (int) old('kategori_id', 1) }},
                  ringkasan: @js(old('ringkasan') ?: ''),
                  modules: @js($modulesDefault),
                  addModule() { this.modules.push({ judul: '', deskripsi: '' }) },
                  removeModule(i) { if (this.modules.length > 1) this.modules.splice(i, 1) }
              }"
              class="flex-1 flex flex-col">

            <main class="flex-1 px-8 py-7 flex flex-col">

                {{-- == ( header halaman ) == --}}
                <header class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6">
                    <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-70 pointer-events-none"></div>
                    <div class="relative z-10">
                        <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                            Ajukan Proyek <span class="text-[#FFB800]">Baru</span>
                        </h1>
                        <p class="text-sm text-slate-400 mt-1.5 max-w-2xl leading-relaxed">
                            Isi data klien, rincian kebutuhan, dan estimasi deadline proyek. Pengajuan akan diteruskan ke administrators TEVARA untuk ditinjau sebelum SPK diterbitkan.
                        </p>
                    </div>
                </header>

                @php
                    $field = 'w-full rounded-xl border bg-white px-4 py-3 text-sm text-[#1E293B] placeholder:text-slate-300 transition focus:outline-none focus:ring-2';
                    $inputOk  = $field . ' border-slate-200 focus:border-[#0B3B82] focus:ring-[#0B3B82]/15';
                    $inputErr = $field . ' border-red-300 focus:border-red-500 focus:ring-red-500/15';
                    $label = 'block text-[10.5px] font-bold tracking-wide uppercase text-slate-400 mb-1.5';
                @endphp

                <div class="space-y-6 mt-6 flex-1">

                    {{-- == ( STEP 1 : informasi klien & instansi ) == --}}
                    <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6">
                        <div class="flex items-start gap-3.5 mb-6">
                            <span class="w-8 h-8 rounded-xl bg-[#0B3B82] text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm">1</span>
                            <div>
                                <h2 class="text-[15px] font-bold text-[#1E293B] leading-tight">Informasi Klien &amp; Instansi</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Data penanggung jawab dan perusahaan yang akan partnering dengan TEVARA</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-5">
                            {{-- Nama klien / instansi --}}
                            <div class="lg:col-span-2">
                                <label for="nama_instansi" class="{{ $label }}">Nama Klien / Instansi <span class="text-red-500">*</span></label>
                                <input id="nama_instansi" name="nama_instansi" type="text" required
                                       placeholder="Contoh: PT. Maju Bersama Sentosa"
                                       value="{{ old('nama_instansi') ?: $profile['instansi'] }}"
                                       class="{{ $errors->has('nama_instansi') ? $inputErr : $inputOk }}">
                                @error('nama_instansi') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- Nama penanggung jawab --}}
                            <div>
                                <label for="pic_nama" class="{{ $label }}">Nama Penanggung Jawab (PIC) <span class="text-red-500">*</span></label>
                                <input id="pic_nama" name="pic_nama" type="text" required
                                       placeholder="Contoh: Budi Santoso"
                                       value="{{ old('pic_nama') ?: $profile['pic'] }}"
                                       class="{{ $errors->has('pic_nama') ? $inputErr : $inputOk }}">
                                @error('pic_nama') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                @if ($profile['jabatan'])
                                    <p class="mt-1.5 text-xs text-slate-400">Jabatan PIC: {{ $profile['jabatan'] }}</p>
                                @endif
                            </div>

                            {{-- Nomor kontak --}}
                            <div>
                                <label for="kontak_nomor" class="{{ $label }}">Kontak / No. WhatsApp <span class="text-slate-300 normal-case tracking-normal font-medium">(opsional)</span></label>
                                <input id="kontak_nomor" name="kontak_nomor" type="text"
                                       placeholder="Contoh: 6281234567890"
                                       value="{{ old('kontak_nomor') ?: $profile['telepon'] }}"
                                       class="{{ $errors->has('kontak_nomor') ? $inputErr : $inputOk }}">
                                @error('kontak_nomor') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- Email --}}
                            <div class="lg:col-span-2">
                                <div class="flex items-center justify-between gap-4 mb-1.5">
                                    <label for="kontak_email" class="text-[10.5px] font-bold tracking-wide uppercase text-slate-400">Kontak &mdash; Email <span class="text-red-500">*</span></label>
                                    <button type="button" id="pakai-email-profil"
                                            @click="document.getElementById('kontak_email').value = @js($profile['email'])"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0B3B82] hover:underline transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                        Gunakan email profil PIC
                                    </button>
                                </div>
                                <input id="kontak_email" name="kontak_email" type="email" required
                                       placeholder="nama@perusahaan.co.id"
                                       value="{{ old('kontak_email') ?: $profile['email'] }}"
                                       class="{{ $errors->has('kontak_email') ? $inputErr : $inputOk }}">
                                @error('kontak_email') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    {{-- == ( STEP 2 : rincian & kebutuhan proyek ) == --}}
                    <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6">
                        <div class="flex items-start gap-3.5 mb-6">
                            <span class="w-8 h-8 rounded-xl bg-[#0B3B82] text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm">2</span>
                            <div>
                                <h2 class="text-[15px] font-bold text-[#1E293B] leading-tight">Rincian &amp; Kebutuhan Proyek</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Deskripsikan kebutuhan agar tim dapat menyusun ruang lingkup kerja</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            {{-- Judul proyek --}}
                            <div>
                                <label for="judul_proyek" class="{{ $label }}">Judul Proyek <span class="text-red-500">*</span></label>
                                <input id="judul_proyek" name="judul_proyek" type="text" required
                                       placeholder="Contoh: Sistem Informasi Manajemen Proyek Teaching Factory"
                                       value="{{ old('judul_proyek') }}"
                                       class="{{ $errors->has('judul_proyek') ? $inputErr : $inputOk }}">
                                @error('judul_proyek') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- Kategori proyek --}}
                            <div>
                                <span class="{{ $label }}">Kategori Proyek <span class="text-red-500">*</span></span>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                                    @foreach ($categories as $id => $cat)
                                        <label id="kategori-{{ $cat['name'] }}"
                                               class="cursor-pointer rounded-xl border px-3 py-3 flex flex-col items-center gap-2 text-xs font-semibold transition"
                                               :class="kategori === {{ $id }}
                                                    ? 'border-[#0B3B82] bg-[#EAF3FF] text-[#0B3B82] shadow-sm'
                                                    : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300 hover:text-slate-700'">
                                            <input type="radio" name="kategori_id" value="{{ $id }}" class="sr-only" x-model.number="kategori" required>
                                            <span class="w-6 h-6" :class="kategori === {{ $id }} ? 'text-[#0B3B82]' : 'text-slate-400'">
                                                @if ($cat['icon'] === 'web')
                                                    {{-- Browser window --}}
                                                    <svg class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><rect x="3" y="4.5" width="18" height="15" rx="2.25"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 9h18"/><path stroke-linecap="round" d="M6.25 6.75h.007M8.75 6.75h.007"/></svg>
                                                @elseif ($cat['icon'] === 'mobile')
                                                    {{-- Smartphone --}}
                                                    <svg class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><rect x="7" y="2.5" width="10" height="19" rx="2.25"/><path stroke-linecap="round" d="M11 18.75h2"/></svg>
                                                @elseif ($cat['icon'] === 'desktop')
                                                    {{-- Desktop monitor --}}
                                                    <svg class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><rect x="2.75" y="4" width="18.5" height="12.5" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 20.25h6M12 16.5v3.75"/></svg>
                                                @elseif ($cat['icon'] === 'signal')
                                                    {{-- Wireless signal --}}
                                                    <svg class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 6.75a10.5 10.5 0 0 1 13.5 0M8.25 10.25a6 6 0 0 1 7.5 0M11 13.75a1.75 1.75 0 0 1 2 0M12 17.25h.007"/></svg>
                                                @else
                                                    {{-- Ellipsis --}}
                                                    <svg class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                                @endif
                                            </span>
                                            <span>{{ $cat['name'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('kategori_id') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- Ringkasan kebutuhan --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="ringkasan" class="text-[10.5px] font-bold tracking-wide uppercase text-slate-400">Ringkasan Kebutuhan Proyek <span class="text-red-500">*</span></label>
                                    <span class="text-[11px] font-semibold"
                                          :class="ringkasan.length < 100 ? 'text-amber-600' : 'text-emerald-600'"
                                          x-text="ringkasan.length + '/500 karakter minimum'">0/500 karakter minimum</span>
                                </div>
                                <textarea id="ringkasan" name="ringkasan" rows="4" required minlength="100" maxlength="500"
                                          x-model="ringkasan"
                                          placeholder="Uraikan kebutuhan utama proyek dalam 100-500 karakter, contoh: Kami membutuhkan aplikasi untuk mengelola data stok multi-outlet…"
                                          class="{{ $errors->has('ringkasan') ? $inputErr : $inputOk }}">{{ old('ringkasan') }}</textarea>
                                @error('ringkasan') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- Detail kebutuhan --}}
                            <div>
                                <label for="deskripsi" class="{{ $label }}">Detail Kebutuhan Proyek <span class="text-slate-300 normal-case tracking-normal font-medium">(opsional)</span></label>
                                <textarea id="deskripsi" name="deskripsi" rows="3"
                                          placeholder="Spesifikasi tambahan, fitur wajib, atau referensi sistem yang sudah dipakai perusahaan."
                                          class="{{ $errors->has('deskripsi') ? $inputErr : $inputOk }}">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- Target pengguna --}}
                            <div>
                                <label for="target_pengguna" class="{{ $label }}">Target Pengguna <span class="text-slate-300 normal-case tracking-normal font-medium">(opsional)</span></label>
                                <textarea id="target_pengguna" name="target_pengguna" rows="2"
                                          placeholder="Siapa yang akan memakai hasil proyek, misalnya: staff operasional, pelanggan, atau siswa."
                                          class="{{ $errors->has('target_pengguna') ? $inputErr : $inputOk }}">{{ old('target_pengguna') }}</textarea>
                                @error('target_pengguna') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    {{-- == ( STEP 3 : estimasi & dokumen pendukung ) == --}}
                    <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6">
                        <div class="flex items-start gap-3.5 mb-6">
                            <span class="w-8 h-8 rounded-xl bg-[#0B3B82] text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm">3</span>
                            <div>
                                <h2 class="text-[15px] font-bold text-[#1E293B] leading-tight">Estimasi &amp; Dokumen Pendukung</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Tentukan target waktu, anggaran, dan rincian modul yang dibutuhkan</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-5">
                            {{-- Deadline --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="deadline" class="text-[10.5px] font-bold tracking-wide uppercase text-slate-400">Estimasi Deadline <span class="text-red-500">*</span></label>
                                    <span class="text-[10.5px] font-bold text-[#0B3B82] bg-[#EAF3FF] px-2 py-0.5 rounded-full">Min H+30 hari</span>
                                </div>
                                <input id="deadline" name="deadline" type="date" required
                                       min="{{ $deadlineMin }}"
                                       value="{{ old('deadline') ?: $deadlineMin }}"
                                       class="{{ $errors->has('deadline') ? $inputErr : $inputOk }}">
                                @error('deadline') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- Anggaran --}}
                            <div>
                                <label for="anggaran" class="{{ $label }}">Estimasi Anggaran <span class="text-slate-300 normal-case tracking-normal font-medium">(opsional)</span></label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">Rp</span>
                                    <input id="anggaran" name="anggaran" type="number" min="0" step="100000"
                                           value="{{ old('anggaran') }}"
                                           placeholder="0"
                                           class="{{ $errors->has('anggaran') ? $inputErr : $inputOk }} pl-11">
                                </div>
                                <p class="mt-2 text-[11px] text-slate-400 leading-relaxed">
                                    Perkiraan budget membantu administrators TeFa menyusun estimasi biaya dan termin pembayaran.
                                </p>
                                @error('anggaran') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Modul proyek --}}
                        <div class="mt-6">
                            <div class="flex items-center justify-between gap-4 mb-3.5">
                                <span class="text-[10.5px] font-bold tracking-wide uppercase text-slate-400">Modul Proyek yang Diminta <span class="text-red-500">*</span></span>
                                <button type="button" id="tambah-modul"
                                        @click="addModule()"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-[#0B3B82] px-3 py-1.5 text-[11.5px] font-bold text-[#0B3B82] hover:bg-[#EAF3FF] transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Tambah Modul Proyek
                                </button>
                            </div>

                            <input type="hidden" name="modul" :value="JSON.stringify(modules)">

                            <div class="space-y-4">
                                <template x-for="(mod, i) in modules" :key="i">
                                    <div class="rounded-xl border border-slate-200 bg-[#F8FAFC] p-4">
                                        <div class="flex items-start gap-3">
                                            <span class="shrink-0 mt-1 text-[10px] font-extrabold tracking-wide text-[#0B3B82] bg-[#EAF3FF] px-2 py-1 rounded-md"
                                                  x-text="'MODUL ' + (i + 1)">MODUL 1</span>
                                            <div class="flex-1 min-w-0 space-y-3">
                                                <div>
                                                    <label class="block text-[11px] font-bold text-[#1E293B] mb-1.5" :for="'modul-judul-' + i">Komponen Proyek</label>
                                                    <input :id="'modul-judul-' + i" type="text" x-model="mod.judul"
                                                           placeholder="Contoh: Modul Autentikasi & Manajemen Role"
                                                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/15 transition">
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] font-bold text-[#1E293B] mb-1.5" :for="'modul-deskripsi-' + i">Deskripsi Modul</label>
                                                    <textarea :id="'modul-deskripsi-' + i" rows="2" x-model="mod.deskripsi"
                                                              placeholder="Jelaskan singkat apa yang harus dikerjakan pada modul ini."
                                                              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/15 transition"></textarea>
                                                </div>
                                            </div>
                                            <button type="button" @click="removeModule(i)"
                                                    class="shrink-0 mt-1 p-2 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition cursor-pointer"
                                                    :aria-label="'Hapus modul ' + (i + 1)">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            @error('modul') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                            @error('modul.*.judul') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </section>

                </div>

                {{-- == ( sticky action bar ) == --}}
                <div class="sticky bottom-0 -mx-8 px-8 py-4 mt-6 bg-white/90 backdrop-blur border-t border-slate-200 flex items-center justify-between gap-6">
                    <p class="flex items-center gap-2 text-xs text-slate-400">
                        <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        Pengajuan diteruskan ke administrators TEVARA untuk ditinjau
                    </p>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-full bg-[#0B3B82] px-6 py-3 text-sm font-bold text-white hover:bg-[#0a3372] transition shadow-sm shadow-[#0B3B82]/25 cursor-pointer">
                        Ajukan Proyek Sekarang
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>

            </main>
        </form>
    </div>
</div>

</body>
</html>
