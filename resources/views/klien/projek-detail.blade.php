{{-- == ( halaman detail projek saya - klien & form perbaikan / revisi ) == --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Projek Saya - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">

@php
    $user = $user ?? session('user', []);
    $proposal = is_array($proposal ?? null) ? $proposal : [];
    $company = is_array($company ?? null) ? $company : [];
    $categories = is_array($categories ?? null) ? $categories : [];

    if (empty($categories)) {
        $categories = [
            ['id' => 1, 'name' => 'Web'],
            ['id' => 2, 'name' => 'Mobile'],
            ['id' => 3, 'name' => 'Desktop'],
            ['id' => 4, 'name' => 'IoT'],
            ['id' => 5, 'name' => 'Other'],
        ];
    }

    $proposalId = $proposal['id'] ?? null;
    $proposalCode = $proposal['proposal_code'] ?? 'PRSL-' . ($proposalId ?? '000');
    $title = $proposal['title'] ?? '';
    $background = $proposal['background'] ?? '';
    $targetAudience = $proposal['target_audience'] ?? '';
    $desiredDeadline = !empty($proposal['desired_deadline'])
        ? \Carbon\Carbon::parse($proposal['desired_deadline'])->toDateString()
        : now()->addDays(30)->toDateString();
    $estimatedBudget = (int) round((float) ($proposal['estimated_budget'] ?? 0));
    $categoryId = $proposal['category_id'] ?? ($proposal['category']['id'] ?? 1);
    $categoryName = $proposal['category']['name'] ?? ($proposal['category_name'] ?? 'Web');

    $rawStatus = (string) ($proposal['status']['name'] ?? ($proposal['status'] ?? ''));
    $statusId = (int) ($proposal['status_id'] ?? 0);

    $deliveryType = (string) ($proposal['delivery_type'] ?? 'source_code');
    $hasWarranty = !empty($proposal['has_warranty']) || !empty($proposal['warranty']['has_warranty']);
    $warrantyMonths = (int) ($proposal['warranty_months'] ?? ($proposal['warranty']['warranty_months'] ?? 3));

    // Deteksi apakah proposal berstatus Improvement / Revisi (status_id = 4 atau nama berisi revision/improvement)
    $isRevision = $statusId === 4 || str_contains(strtolower($rawStatus), 'revision') || str_contains(strtolower($rawStatus), 'improvement');
    $reviewNotes = (string) ($proposal['review_notes'] ?? '');
    $reviewerName = (string) ($proposal['reviewer']['name'] ?? 'Pengurus TEFA');

    // Info pelaksanaan proyek: hanya ditampilkan saat proyek sudah berjalan / selesai.
    $executionStatuses = ['active', 'testing', 'completed', 'handover'];
    $showExecutionInfo = in_array(strtolower($rawStatus), $executionStatuses, true);
    $isCompletedStatus = in_array(strtolower($rawStatus), ['completed', 'handover'], true);
    $overallProgress = isset($proposal['overall_progress'])
        ? (int) round((float) $proposal['overall_progress'])
        : ($isCompletedStatus ? 100 : 0);
    $overallProgress = max(0, min(100, $overallProgress));

    $pm                 = is_array($proposal['pm'] ?? null) ? $proposal['pm'] : [];
    $pmName             = trim((string) ($pm['name'] ?? ''));
    $teamMembers        = is_array($proposal['project_teams'] ?? null) ? $proposal['project_teams'] : [];
    $teamInitials       = function (string $name): string {
        return collect(explode(' ', trim($name)))
            ->filter()
            ->take(2)
            ->map(fn ($word) => mb_substr($word, 0, 1))
            ->implode('');
    };

    // Modules untuk Alpine
    $rawModules = is_array($proposal['modules'] ?? null) ? $proposal['modules'] : [];
    $modulesDefault = [];
    foreach ($rawModules as $mod) {
        $modulesDefault[] = [
            'judul'     => $mod['title'] ?? ($mod['judul'] ?? ''),
            'deskripsi' => $mod['description'] ?? ($mod['deskripsi'] ?? ''),
        ];
    }
    if (empty($modulesDefault)) {
        $modulesDefault = [['judul' => '', 'deskripsi' => '']];
    }

    // Profil PIC / Perusahaan
    $profile = [
        'instansi' => $proposal['client_company_name'] ?? $company['company_name'] ?? $user['company_name'] ?? $user['name'] ?? '',
        'pic'      => $proposal['client']['name'] ?? $company['name'] ?? $user['name'] ?? '',
        'telepon'  => $proposal['client']['phone_number'] ?? $company['no_hp'] ?? $user['phone_number'] ?? '',
        'email'    => $proposal['client']['email'] ?? $company['email'] ?? $user['email'] ?? '',
        'jabatan'  => $company['position'] ?? $user['position'] ?? 'PIC Klien',
    ];

    $fieldStyle = 'w-full rounded-xl border bg-white px-4 py-3 text-sm text-[#1E293B] placeholder:text-slate-300 transition focus:outline-none focus:ring-2';
    $inputOk  = $fieldStyle . ' border-slate-200 focus:border-[#0B3B82] focus:ring-[#0B3B82]/15';
    $inputErr = $fieldStyle . ' border-red-300 focus:border-red-500 focus:ring-red-500/15';
    $labelStyle = 'block text-[10.5px] font-bold tracking-wide uppercase text-slate-400 mb-1.5';

    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $formatDate = fn ($value) => $value
        ? \Carbon\Carbon::parse($value)->translatedFormat('d M Y')
        : '-';
@endphp

<div class="flex min-h-screen">

    <x-sidebar active="projek-saya" />

    <div class="flex-1 ml-64 flex flex-col min-h-screen">

        {{-- Flash message --}}
        @if (session('success'))
            <div class="mx-8 mt-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <p class="text-sm font-medium leading-relaxed">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error') || ($error ?? null))
            <div class="mx-8 mt-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                <p class="text-sm font-medium leading-relaxed">{{ session('error') ?? $error }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-8 mt-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                <p class="text-sm font-medium leading-relaxed">{{ $errors->first() }}</p>
            </div>
        @endif

        @if ($isRevision)
            {{-- == ( JIKA STATUS IMPROVEMENT / REVISI: TAMPILKAN FORM MIRIP AJUKAN PROJEK ) == --}}
            <form id="form-revisi-proyek"
                  method="POST"
                  action="{{ route('klien.projek-saya.revisi.store', $proposalId) }}"
                  x-data="{
                      ringkasan: @js(old('ringkasan', $background)),
                      deliveryType: @js(old('delivery_type', $deliveryType)),
                      hasWarranty: '1',
                      warrantyMonths: @js((string) old('warranty_months', (string) ($warrantyMonths ?: 3))),
                      modules: @js(old('modul') ? (is_string(old('modul')) ? json_decode(old('modul'), true) : old('modul')) : $modulesDefault),
                      addModule() { this.modules.push({ judul: '', deskripsi: '' }) },
                      removeModule(i) { if (this.modules.length > 1) this.modules.splice(i, 1) }
                  }"
                  class="flex-1 flex flex-col">
                @csrf

                <main class="flex-1 px-8 py-7 flex flex-col space-y-6">

                    {{-- Breadcrumb --}}
                    <div class="flex items-center justify-between gap-4">
                        <a href="{{ route('klien.projek-saya') }}"
                           class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82] transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                            </svg>
                            Kembali ke Daftar Projek Saya
                        </a>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-mono font-semibold text-slate-400 bg-white border border-slate-200 px-3 py-1.5 rounded-lg">
                                {{ $proposalCode }}
                            </span>
                            <span class="text-[11px] font-bold px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                Status: Improvement (Revisi)
                            </span>
                        </div>
                    </div>

                    {{-- ALERT BOX: CATATAN DARI PENGURUS TEFA --}}
                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50/50 border-2 border-amber-300 p-6 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <h3 class="text-base font-bold text-amber-950">Catatan Perbaikan dari Pengurus TEFA</h3>
                                    <span class="text-[11px] font-semibold text-amber-800 bg-amber-100/80 px-2.5 py-0.5 rounded-full border border-amber-200">
                                        Reviewer: {{ $reviewerName }}
                                    </span>
                                </div>
                                <p class="text-xs text-amber-900/80 mb-3 leading-relaxed">
                                    Pengurus TEFA meminta penyesuaian pada proposal Anda. Silakan ubah bagian/field yang diminta pada form berikut (misalnya <strong>harga / estimasi anggaran</strong> atau ruang lingkup modul), kemudian klik <strong>Kirim Perbaikan ke Pengurus TEFA</strong> untuk dicek kembali.
                                </p>
                                <div class="bg-white/95 rounded-xl border border-amber-200/90 p-4 text-sm text-[#1E293B] font-medium leading-relaxed shadow-xs">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.502 49.188 49.188 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
                                        </svg>
                                        <div class="whitespace-pre-line text-slate-800">{{ $reviewNotes ?: 'Silakan perbarui estimasi anggaran / rincian modul sesuai arahan pengurus.' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FORMULIR MIRIP "AJUKAN PROJEK" --}}
                    <div class="space-y-6">

                        {{-- STEP 1 : Informasi Klien & Instansi (Read-only context) --}}
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6">
                            <div class="flex items-start gap-3.5 mb-6">
                                <span class="w-8 h-8 rounded-xl bg-[#0B3B82] text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm">1</span>
                                <div>
                                    <h2 class="text-[15px] font-bold text-[#1E293B] leading-tight">Informasi Klien &amp; Instansi</h2>
                                    <p class="text-xs text-slate-400 mt-0.5">Data identitas penanggung jawab dan instansi Anda di TEVARA</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-5">
                                <div class="lg:col-span-2">
                                    <label class="{{ $labelStyle }}">Nama Perusahaan / Instansi</label>
                                    <input type="text" readonly value="{{ $profile['instansi'] }}"
                                           class="{{ $inputOk }} bg-slate-50 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="{{ $labelStyle }}">Nama Penanggung Jawab (PIC)</label>
                                    <input type="text" readonly value="{{ $profile['pic'] }}"
                                           class="{{ $inputOk }} bg-slate-50 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="{{ $labelStyle }}">Kontak / No. WhatsApp</label>
                                    <input type="text" readonly value="{{ $profile['telepon'] ?: '-' }}"
                                           class="{{ $inputOk }} bg-slate-50 cursor-not-allowed">
                                </div>
                                <div class="lg:col-span-2">
                                    <label class="{{ $labelStyle }}">Email PIC</label>
                                    <input type="email" readonly value="{{ $profile['email'] }}"
                                           class="{{ $inputOk }} bg-slate-50 cursor-not-allowed">
                                </div>
                            </div>
                        </section>

                        {{-- STEP 2 : Rincian & Kebutuhan Proyek (Bisa diedit) --}}
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6">
                            <div class="flex items-start gap-3.5 mb-6">
                                <span class="w-8 h-8 rounded-xl bg-[#0B3B82] text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm">2</span>
                                <div>
                                    <h2 class="text-[15px] font-bold text-[#1E293B] leading-tight">Rincian &amp; Kebutuhan Proyek</h2>
                                    <p class="text-xs text-slate-400 mt-0.5">Ubah judul, ringkasan, atau target pengguna bila diminta oleh pengurus TEFA</p>
                                </div>
                            </div>

                            <div class="space-y-5">
                                {{-- Judul proyek --}}
                                <div>
                                    <label for="judul_proyek" class="{{ $labelStyle }}">Judul Proyek <span class="text-red-500">*</span></label>
                                    <input id="judul_proyek" name="judul_proyek" type="text" required
                                           placeholder="Contoh: Sistem Informasi Manajemen Proyek Teaching Factory"
                                           value="{{ old('judul_proyek', $title) }}"
                                           class="{{ $errors->has('judul_proyek') ? $inputErr : $inputOk }}">
                                    @error('judul_proyek') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Kategori proyek --}}
                                <div>
                                    <label for="kategori_id" class="{{ $labelStyle }}">Kategori Proyek <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select id="kategori_id" name="kategori_id" required
                                                class="{{ $errors->has('kategori_id') ? $inputErr : $inputOk }} appearance-none pr-10 cursor-pointer">
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat['id'] }}" @selected((string) old('kategori_id', $categoryId) === (string) $cat['id'])>
                                                    {{ $cat['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <svg class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                                             fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                                        </svg>
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
                                              placeholder="Uraikan kebutuhan utama proyek dalam 100-500 karakter..."
                                              class="{{ $errors->has('ringkasan') ? $inputErr : $inputOk }}">{{ old('ringkasan', $background) }}</textarea>
                                    @error('ringkasan') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Target pengguna --}}
                                <div>
                                    <label for="target_pengguna" class="{{ $labelStyle }}">Target Pengguna <span class="text-red-500">*</span></label>
                                    <textarea id="target_pengguna" name="target_pengguna" rows="2" required
                                              placeholder="Siapa yang akan memakai hasil proyek..."
                                              class="{{ $errors->has('target_pengguna') ? $inputErr : $inputOk }}">{{ old('target_pengguna', $targetAudience) }}</textarea>
                                    @error('target_pengguna') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </section>

                        {{-- STEP 3 : Skema Pengelolaan & Garansi Pemeliharaan --}}
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6">
                            <div class="flex items-start gap-3.5 mb-6">
                                <span class="w-8 h-8 rounded-xl bg-[#0B3B82] text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm">3</span>
                                <div>
                                    <h2 class="text-[15px] font-bold text-[#1E293B] leading-tight">Skema Pengelolaan &amp; Garansi Pemeliharaan</h2>
                                    <p class="text-xs text-slate-400 mt-0.5">Tentukan kepemilikan operasional dan layanan pemeliharaan (maintenance) pasca serah terima</p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                {{-- Pertanyaan 3: Skema Pengelolaan (Delivery Type) --}}
                                <div>
                                    <label class="{{ $labelStyle }}">Skema Penyerahan &amp; Pengelolaan Produk <span class="text-red-500">*</span></label>
                                    <p class="text-[11px] text-slate-400 mb-3">Pilih apakah sistem akan diserahkan mandiri ke klien atau dikelola penuh oleh infrastruktur TEVARA.</p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <label class="relative flex items-start gap-3.5 p-4 rounded-xl border cursor-pointer transition"
                                               :class="deliveryType === 'source_code' ? 'border-[#0B3B82] bg-[#EAF3FF]/40 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                            <input type="radio" name="delivery_type" value="source_code" x-model="deliveryType" class="mt-1 text-[#0B3B82] focus:ring-[#0B3B82]">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-[#1E293B]">Diserahkan ke Klien (Source Code)</p>
                                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                                    Seluruh source code, aset, dan hak operasional diserahkan mandiri kepada pihak klien. Tim TEVARA tetap bertanggung jawab memberikan garansi pemeliharaan &amp; perbaikan bug selama masa garansi.
                                                </p>
                                            </div>
                                        </label>

                                        <label class="relative flex items-start gap-3.5 p-4 rounded-xl border cursor-pointer transition"
                                               :class="deliveryType === 'tefa_managed' ? 'border-[#0B3B82] bg-[#EAF3FF]/40 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                            <input type="radio" name="delivery_type" value="tefa_managed" x-model="deliveryType" class="mt-1 text-[#0B3B82] focus:ring-[#0B3B82]">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-[#1E293B]">Dikelola Penuh oleh TEVARA</p>
                                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                                    Infrastruktur server, deployment, pemantauan performa, dan pengelolaan hosting tetap dipegang tim TEVARA Polines.
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                    @error('delivery_type') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Garansi Pemeliharaan (Maintenance) - Mandatory --}}
                                <div class="pt-5 border-t border-slate-100 space-y-4">
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <label for="warranty_months" class="{{ $labelStyle }}">Garansi Pemeliharaan (Maintenance) <span class="text-red-500">*</span></label>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#EAF3FF] text-[#0B3B82] border border-[#0B3B82]/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                                                Wajib Disertakan
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-400 mb-3">Dukungan perbaikan bug, asistensi teknis, dan pemeliharaan berkala setelah serah terima proyek (BAST) untuk seluruh skema.</p>

                                        <input type="hidden" name="has_warranty" value="1">

                                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                                            <label for="warranty_months" class="block text-xs font-bold text-[#1E293B]">Durasi Garansi Pemeliharaan <span class="text-red-500">*</span></label>
                                            <div class="relative max-w-md">
                                                <select id="warranty_months" name="warranty_months"
                                                        x-model="warrantyMonths"
                                                        required
                                                        class="{{ $errors->has('warranty_months') ? $inputErr : $inputOk }} appearance-none pr-10 cursor-pointer bg-white">
                                                    <option value="1">1 Bulan</option>
                                                    <option value="3">3 Bulan (Standar Rekomendasi)</option>
                                                    <option value="6">6 Bulan</option>
                                                    <option value="12">12 Bulan (1 Tahun)</option>
                                                </select>
                                                <svg class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                                                     fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                                                </svg>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                                Masa garansi pemeliharaan aktif otomatis setelah Berita Acara Serah Terima (BAST) disetujui bersama. Tim TEVARA tetap bertanggung jawab memberikan perbaikan bug dan stabilitas sistem selama periode garansi berlangsung.
                                            </p>
                                            @error('warranty_months') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </section>

                    {{-- STEP 4 : Estimasi & Dokumen Pendukung (HARGA / ESTIMASI ANGGARAN & MODUL) --}}
                    <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6">
                        <div class="flex items-start gap-3.5 mb-6">
                            <span class="w-8 h-8 rounded-xl bg-[#0B3B82] text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm">4</span>
                            <div>
                                <h2 class="text-[15px] font-bold text-[#1E293B] leading-tight">Estimasi Anggaran &amp; Modul Proyek</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Ubah harga atau modul sesuai catatan pengurus TEFA</p>
                            </div>
                        </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-5">
                                {{-- Deadline --}}
                                <div>
                                    <label for="deadline" class="{{ $labelStyle }}">Estimasi Deadline <span class="text-red-500">*</span></label>
                                    <input id="deadline" name="deadline" type="date" required
                                           value="{{ old('deadline', $desiredDeadline) }}"
                                           class="{{ $errors->has('deadline') ? $inputErr : $inputOk }}">
                                    @error('deadline') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Anggaran / Harga (HIGHLIGHTED) --}}
                                <div class="relative bg-amber-50/50 p-3 rounded-2xl border border-amber-200">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="anggaran" class="text-[10.5px] font-bold tracking-wide uppercase text-amber-900">
                                            Estimasi Anggaran / Harga <span class="text-red-500">*</span>
                                        </label>
                                        <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-md">
                                            Field Revisi Utama
                                        </span>
                                    </div>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-500">Rp</span>
                                        <input id="anggaran" name="anggaran" type="number" min="0" step="100000" required
                                               value="{{ old('anggaran', $estimatedBudget) }}"
                                               placeholder="0"
                                               class="{{ $errors->has('anggaran') ? $inputErr : $inputOk }} pl-11 bg-white font-bold text-base text-slate-900 focus:border-amber-500 focus:ring-amber-500/20">
                                    </div>
                                    <p class="mt-2 text-[11px] text-amber-800 leading-relaxed">
                                        &bull; Klien tinggal menyesuaikan nominal harga di sini sesuai arahan pengurus TEFA.
                                    </p>
                                    @error('anggaran') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Modul proyek (Repeater Alpine) --}}
                            <div class="mt-6">
                                <div class="flex items-center justify-between gap-4 mb-3.5">
                                    <div>
                                        <span class="text-[10.5px] font-bold tracking-wide uppercase text-slate-400">Modul Proyek yang Diminta</span>
                                        <p class="text-[11px] text-slate-400">Tambahkan atau sesuaikan rincian modul proyek</p>
                                    </div>
                                    <button type="button"
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
                                                        class="shrink-0 mt-1 p-2 rounded-lg text-red-500 hover:text-red-600 hover:bg-red-50 transition cursor-pointer"
                                                        :aria-label="'Hapus modul ' + (i + 1)">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </section>

                    </div>

                    {{-- Sticky Action Bar --}}
                    <div class="sticky bottom-0 -mx-8 px-8 py-4 mt-6 bg-white/95 backdrop-blur border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 z-20 shadow-lg">
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Setelah diisi, data otomatis balik lagi ke pengurus TEFA untuk dicek kembali.</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('klien.projek-saya') }}"
                               class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                                Batal
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-[#0B3B82] hover:bg-[#082a5c] px-6 py-2.5 text-xs font-bold text-white shadow-sm shadow-[#0B3B82]/25 transition cursor-pointer">
                                Kirim Perbaikan ke Pengurus TEFA
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            </button>
                        </div>
                    </div>

                </main>
            </form>
        @else
            {{-- == ( JIKA BUKAN REVISI: TAMPILKAN DETAIL PROJEK READ-ONLY LENGKAP ) == --}}
            <main class="flex-1 px-8 py-7 space-y-6">

                {{-- Breadcrumb --}}
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('klien.projek-saya') }}"
                       class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82] transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                        </svg>
                        Kembali ke Daftar Projek Saya
                    </a>
                    <span class="text-[11px] font-mono font-semibold text-slate-400 bg-white border border-slate-200 px-3 py-1.5 rounded-lg">
                        {{ $proposalCode }}
                    </span>
                </div>

                {{-- Hero Detail --}}
                <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6">
                    <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                    <div class="relative z-10 flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700">
                                    {{ $categoryName }}
                                </span>
                                <span class="text-[11px] font-bold px-3 py-1 rounded-full border bg-slate-100 text-slate-700 border-slate-200">
                                    {{ $rawStatus ?: 'Submitted' }}
                                </span>
                            </div>
                            <h1 class="text-2xl font-bold text-[#1E293B] leading-snug">{{ $title ?: 'Detail Projek' }}</h1>
                            <p class="text-xs text-slate-400 mt-1">Diajukan oleh: <span class="font-semibold text-slate-600">{{ $profile['instansi'] }}</span> &bull; PIC: {{ $profile['pic'] }}</p>
                        </div>

                        <div class="flex items-center gap-4 bg-slate-50 rounded-2xl p-4 border border-slate-100 flex-shrink-0">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Estimasi Anggaran</p>
                                <p class="text-lg font-bold text-[#0B3B82] mt-0.5">{{ $formatRupiah($estimatedBudget) }}</p>
                            </div>
                            <div class="h-8 w-px bg-slate-200"></div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Target Deadline</p>
                                <p class="text-sm font-bold text-slate-700 mt-0.5">{{ $formatDate($desiredDeadline) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Deskripsi & Rincian --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
                            <h2 class="text-sm font-bold text-[#1E293B]">Ringkasan Kebutuhan Proyek</h2>
                            <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ $background ?: '-' }}</p>
                        </section>

                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
                            <h2 class="text-sm font-bold text-[#1E293B]">Target Pengguna</h2>
                            <p class="text-sm text-slate-600 leading-relaxed">{{ $targetAudience ?: '-' }}</p>
                        </section>

                        {{-- Skema Penyerahan & Garansi Pemeliharaan --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-start gap-3">
                                <span class="w-8 h-8 rounded-lg bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.75 5.1a3 3 0 0 1 2.4-1.1h7.7a3 3 0 0 1 2.4 1.1l2.1 3.45a4.5 4.5 0 0 1 .9 2.7"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Skema Penyerahan</p>
                                    <p class="text-xs font-bold text-slate-800 mt-0.5">
                                        {{ $deliveryType === 'tefa_managed' ? 'Dikelola Penuh TEVARA' : 'Diserahkan ke Klien (Source Code)' }}
                                    </p>
                                </div>
                            </div>
                            <div class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-start gap-3">
                                <span class="w-8 h-8 rounded-lg bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Garansi Pemeliharaan</p>
                                    <p class="text-xs font-bold text-slate-800 mt-0.5">
                                        @if ($hasWarranty)
                                            <span class="text-emerald-700">Ya, Garansi {{ $warrantyMonths }} Bulan</span>
                                        @else
                                            <span class="text-slate-500">Tanpa Garansi Maintenance</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Modul-modul --}}
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-sm font-bold text-[#1E293B]">Modul yang Diminta</h2>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                    {{ count($rawModules) }} Modul
                                </span>
                            </div>

                            <div class="space-y-3">
                                @forelse ($rawModules as $idx => $m)
                                    <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                                        <div class="flex items-start gap-3">
                                            <span class="w-6 h-6 rounded-lg bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                                                {{ $idx + 1 }}
                                            </span>
                                            <div>
                                                <h3 class="text-sm font-bold text-slate-800">{{ $m['title'] ?? '-' }}</h3>
                                                @if (!empty($m['description']))
                                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $m['description'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400">Tidak ada modul yang tercatat.</p>
                                @endforelse
                            </div>
                        </section>
                    </div>

                    {{-- Sidebar Info --}}
                    <div class="space-y-6">
                        @if ($showExecutionInfo)
                            <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h2 class="text-sm font-bold text-[#1E293B]">Progres Pengerjaan</h2>
                                    <span class="text-xs font-bold text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-1 rounded-full">{{ $overallProgress }}%</span>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        <span>Overall Progress</span>
                                        <span class="text-[#0B3B82]">{{ $overallProgress }}%</span>
                                    </div>
                                    <div class="h-2.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-r from-[#0B3B82] to-[#2563EB] transition-all"
                                             style="width: {{ $overallProgress }}%"></div>
                                    </div>
                                </div>

                                <div class="border-t border-slate-100 pt-4 space-y-4">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Project Manager</p>
                                        @if ($pmName)
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-full bg-[#0B3B82] text-white flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                                                    {{ $teamInitials($pmName) ?: 'PM' }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-xs font-semibold text-slate-700 truncate">{{ $pmName }}</p>
                                                    <p class="text-[10px] text-slate-400">Penanggung Jawab Proyek</p>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-xs text-slate-400">Belum ditentukan</p>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tim Pelaksana</p>
                                            <span class="text-[10px] font-semibold text-slate-400">{{ count($teamMembers) }} Orang</span>
                                        </div>
                                        @forelse ($teamMembers as $member)
                                            @php
                                                $memberName = trim((string) ($member['user']['name'] ?? ''));
                                                $memberRole = trim((string) ($member['role']['name'] ?? 'Anggota'));
                                            @endphp
                                            @if ($memberName)
                                                <div class="flex items-center gap-2.5 py-1.5">
                                                    <div class="w-8 h-8 rounded-full bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                                                        {{ $teamInitials($memberName) ?: '—' }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-semibold text-slate-700 truncate">{{ $memberName }}</p>
                                                        <p class="text-[10px] text-slate-400">{{ $memberRole ?: 'Anggota' }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        @empty
                                            <p class="text-xs text-slate-400">Belum ada anggota tim yang ditugaskan.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </section>
                        @endif

                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                            <h2 class="text-sm font-bold text-[#1E293B]">Status Validasi</h2>
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                                <p class="text-xs text-slate-400">Status Saat Ini</p>
                                <p class="text-sm font-bold text-slate-800 mt-1">{{ $rawStatus ?: 'Submitted' }}</p>
                                <p class="text-[11px] text-slate-400 mt-1.5 leading-relaxed">
                                    Proposal sedang dalam alur peninjauan TEVARA Teaching Factory.
                                </p>
                            </div>
                            {{-- UAT Box --}}
                            @php
                                $rawStatusLower = strtolower($rawStatus);
                                $canAccessUat = in_array($rawStatusLower, ['testing', 'handover', 'completed'], true) 
                                    || ($rawStatusLower === 'active' && $overallProgress >= 100)
                                    || !empty($proposal['bast_eligible']);
                            @endphp
                            @if($canAccessUat)
                            <div class="p-3.5 rounded-xl bg-[#EAF3FF] border border-blue-200 space-y-2.5">
                                <div class="flex items-center gap-2 text-[#0B3B82]">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 2h7M7 16h10"/>
                                    </svg>
                                    <span class="text-xs font-bold uppercase tracking-wider">UAT &amp; Pengujian</span>
                                </div>
                                <p class="text-[11px] text-[#0B3B82]/80 leading-relaxed">
                                    Tinjau lembar pengujian penerimaan pengguna dan lakukan sign-off digital hasil UAT.
                                </p>
                                <a href="{{ route('klien.projek-saya.uat', $proposalId) }}"
                                   class="inline-flex items-center justify-center w-full gap-2 px-3 py-2 rounded-lg bg-[#0B3B82] hover:bg-[#082a5c] text-white text-xs font-bold transition shadow-xs">
                                    Buka Lembar UAT
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                    </svg>
                                </a>
                            </div>
                            @endif
                            @if(!empty($proposal['client_approved']))
                                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 space-y-2.5">
                                    <div class="flex items-center gap-2 text-emerald-800">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                        <span class="text-xs font-bold uppercase tracking-wider">Dokumen BAST Resmi</span>
                                    </div>
                                    <p class="text-[11px] text-emerald-800/80 leading-relaxed">
                                        Serah terima telah disetujui ({{ $proposal['bast_number'] ?? 'BAST' }}). PDF berita acara siap diunduh.
                                    </p>
                                    <a href="{{ route('klien.projek-saya.bast.pdf', $proposalId) }}"
                                       class="inline-flex items-center justify-center w-full gap-2 px-3 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                        </svg>
                                        Unduh PDF BAST
                                    </a>
                                </div>
                            @endif
                        </section>

                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
                            <h2 class="text-sm font-bold text-[#1E293B]">Kontak PIC</h2>
                            <div class="space-y-2 text-xs">
                                <div>
                                    <p class="text-slate-400">Nama PIC</p>
                                    <p class="font-semibold text-slate-700">{{ $profile['pic'] }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-400">Email</p>
                                    <p class="font-semibold text-slate-700">{{ $profile['email'] }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-400">No. Telepon / WA</p>
                                    <p class="font-semibold text-slate-700">{{ $profile['telepon'] ?: '-' }}</p>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

            </main>
        @endif

    </div>

</div>

</body>
</html>

