<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengujian UAT Bertingkat — {{ $proposal['title'] ?? 'Proyek' }} - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user', []);
    $proposalId = $proposal['id'] ?? null;
    $proposalCode = $proposal['proposal_code'] ?? ('PRJ-' . $proposalId);
    $title = $proposal['title'] ?? 'Proyek';
    $statusName = $proposal['status']['name'] ?? 'Status tidak tersedia';
    $uat = is_array($uat ?? null) ? $uat : null;
    $hasUat = !empty($uat);
    $items = $uat['uat_items'] ?? [];
    $reviewStatus = $uat['review_status'] ?? null;
    $signedAt = $uat['signed_off_at'] ?? null;
    $isApproved = $reviewStatus === 'APPROVED';
    $isSigned = !empty($signedAt);
    $isTesting = strtolower($statusName) === 'testing' || (int) ($proposal['status_id'] ?? 0) === 8;
    $canSubmit = $isTesting && (!$hasUat || $reviewStatus === 'READY_FOR_RETEST');

    // Riset Kategori UAT Lengkap (8 Dimensi Pengujian Standar Industri)
    $uatCategories = [
        'UI' => [
            'name' => 'Tampilan & Antarmuka (UI)',
            'icon' => 'layout',
            'desc' => 'Kesesuaian tata letak, keselarasan warna, tipografi, dan responsivitas layar.',
            'scenarios' => [
                'Kesesuaian tata letak, warna, tipografi, dan ikon pada halaman modul dengan rancangan desain.',
                'Keterbacaan dan responsivitas antarmuka pada berbagai resolusi layar (desktop, tablet, mobile).',
            ],
        ],
        'Fungsionalitas' => [
            'name' => 'Fungsionalitas Utama',
            'icon' => 'cog',
            'desc' => 'Kelancaran alur proses kerja, aksi tombol, CRUD data, dan proses bisnis.',
            'scenarios' => [
                'Semua alur proses utama modul berfungsi sesuai spesifikasi kebutuhan bisnis tanpa kendala.',
                'Aksi tombol, pengisian formulir, dan navigasi data beroperasi secara tepat dan akurat.',
            ],
        ],
        'Keamanan' => [
            'name' => 'Keamanan Sistem',
            'icon' => 'shield',
            'desc' => 'Proteksi data sensitif, penanganan sesi, dan pencegahan akses ilegal.',
            'scenarios' => [
                'Fitur dan data modul terlindungi dari manipulasi tanpa hak akses yang sah.',
                'Penanganan sesi pengguna bekerja dengan aman saat keluar (logout) atau waktu sesi berakhir.',
            ],
        ],
        'Usability' => [
            'name' => 'Kegunaan & Kemudahan (Usability)',
            'icon' => 'user-check',
            'desc' => 'Kemudahan penggunaan, kejelasan petunjuk, dan navigasi intuitif.',
            'scenarios' => [
                'Antarmuka mudah dipahami dan dioperasikan oleh pengguna secara intuitif tanpa kebingungan.',
                'Pesan konfirmasi, notifikasi berhasil, dan pesan kesalahan tampil informatif dan jelas.',
            ],
        ],
        'Kompatibilitas' => [
            'name' => 'Kompatibilitas Perangkat & Browser',
            'icon' => 'globe',
            'desc' => 'Konsistensi tampilan dan performa lintas berbagai peramban web.',
            'scenarios' => [
                'Modul berjalan normal dan konsisten pada berbagai browser utama (Chrome, Firefox, Edge, Safari).',
            ],
        ],
        'Performa' => [
            'name' => 'Performa & Kecepatan Respon',
            'icon' => 'zap',
            'desc' => 'Kecepatan memuat halaman dan waktu respon eksekusi data.',
            'scenarios' => [
                'Pemuatan halaman dan eksekusi aksi berlangsung responsif (< 3 detik) tanpa jeda panjang.',
            ],
        ],
        'Validasi_Input' => [
            'name' => 'Validasi Input Data',
            'icon' => 'check-circle',
            'desc' => 'Pencegahan entri data yang kosong, tidak valid, atau salah format.',
            'scenarios' => [
                'Sistem menolak input data yang kosong atau tidak valid serta menampilkan pesan peringatan yang tepat.',
            ],
        ],
        'Hak_Akses' => [
            'name' => 'Hak Akses & Otorisasi Role',
            'icon' => 'lock',
            'desc' => 'Pemisahan wewenang akses menu dan aksi sesuai peran pengguna.',
            'scenarios' => [
                'Pengguna hanya dapat melihat dan mengeksekusi fitur sesuai peran/role yang diberikan.',
            ],
        ],
    ];

    // Pembentukan struktur form bertingkat (Project -> Modul -> Kategori -> Item Uji)
    $groupedForm = [];
    $globalIdx = 0;

    if ($canSubmit) {
        if ($hasUat && !empty($items)) {
            // Pengujian ulang (Retest): Gunakan data item sesi sebelumnya
            foreach ($items as $item) {
                $modName = $item['module_name'] ?? 'Modul Utama';
                $catKey  = $item['test_category'] ?? 'Fungsionalitas';
                $catName = $uatCategories[$catKey]['name'] ?? ($item['test_category'] ?? 'Pengujian');

                if (!isset($groupedForm[$modName])) {
                    $groupedForm[$modName] = [];
                }
                if (!isset($groupedForm[$modName][$catKey])) {
                    $groupedForm[$modName][$catKey] = [
                        'info' => $uatCategories[$catKey] ?? ['name' => $catName, 'desc' => '', 'icon' => 'check'],
                        'items' => [],
                    ];
                }

                $groupedForm[$modName][$catKey]['items'][] = [
                    'global_index'    => $globalIdx++,
                    'module_name'     => $modName,
                    'test_category'   => $catKey,
                    'feature_name'    => $item['feature_name'] ?? ($modName . ' — ' . $catName),
                    'test_scenario'   => $item['test_scenario'] ?? '',
                    'previous_result' => $item['result'] ?? null,
                    'review_notes'    => $item['review_notes'] ?? null,
                ];
            }
        } else {
            // Pengujian baru: Generate otomatis struktur nested dari modul proposal
            $modules = !empty($proposal['modules']) ? $proposal['modules'] : [
                ['title' => 'Modul Utama Sistem', 'description' => 'Fungsi dan alur kerja utama aplikasi'],
            ];

            foreach ($modules as $module) {
                $modName = $module['title'] ?? 'Modul Sistem';
                $modDesc = $module['description'] ?? '';
                $groupedForm[$modName] = [
                    '__desc' => $modDesc,
                ];

                foreach ($uatCategories as $catKey => $catInfo) {
                    $groupedForm[$modName][$catKey] = [
                        'info' => $catInfo,
                        'items' => [],
                    ];

                    foreach ($catInfo['scenarios'] as $scenarioText) {
                        $groupedForm[$modName][$catKey]['items'][] = [
                            'global_index'    => $globalIdx++,
                            'module_name'     => $modName,
                            'test_category'   => $catKey,
                            'feature_name'    => $modName . ' — ' . $catInfo['name'],
                            'test_scenario'   => $scenarioText,
                            'previous_result' => null,
                            'review_notes'    => null,
                        ];
                    }
                }
            }
        }
    }

    // Kelompokkan item sesi UAT yang sudah ada untuk visualisasi bertingkat
    $existingGrouped = [];
    if ($hasUat && !empty($items)) {
        foreach ($items as $item) {
            $mName = $item['module_name'] ?? 'Modul Proyek';
            $cKey  = $item['test_category'] ?? 'Fungsionalitas';
            if (!isset($existingGrouped[$mName])) {
                $existingGrouped[$mName] = [];
            }
            if (!isset($existingGrouped[$mName][$cKey])) {
                $existingGrouped[$mName][$cKey] = [
                    'name' => $uatCategories[$cKey]['name'] ?? ($item['test_category'] ?? 'Pengujian'),
                    'items' => [],
                ];
            }
            $existingGrouped[$mName][$cKey]['items'][] = $item;
        }
    }

    $history = $uat['uat_history'] ?? ($uat ? [$uat] : []);
@endphp

<div class="flex min-h-screen">
    <x-sidebar active="projek-saya" />
    <main class="ml-64 flex-1 space-y-6 px-8 py-7">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800 shadow-xs">
                <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error') || ($error ?? null) || (isset($errors) && $errors->any()))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800 shadow-xs">
                <div class="font-bold text-red-900">{{ session('error') ?? ($error ?? $errors->first()) }}</div>
                @if(isset($errors) && $errors->any())
                    <ul class="mt-2 list-inside list-disc space-y-1 text-xs">
                        @foreach($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        {{-- Navigasi Balik & Breadcrumb --}}
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('klien.projek-saya.detail', $proposalId) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-[#0B3B82] transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
                Kembali ke Detail Proyek
            </a>
            <span class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 font-mono text-xs font-bold text-slate-600 shadow-2xs">
                {{ $proposalCode }}
            </span>
        </div>

        {{-- Header Proyek & Hirarki UAT --}}
        <section class="rounded-2xl border border-slate-100 bg-white p-7 shadow-xs">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        User Acceptance Testing (UAT) Bertingkat
                    </span>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
                    <p class="mt-1.5 max-w-3xl text-sm leading-relaxed text-slate-600">
                        Pengujian penerimaan dilakukan secara terstruktur bertingkat: <strong>Proyek &rarr; Modul &rarr; Kategori Pengujian (UI, Fungsionalitas, Keamanan, Usability, Kompatibilitas, Performa, Validasi Input, Hak Akses) &rarr; Skenario Uji</strong>.
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold {{ $isTesting ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-700' }}">
                        <span class="w-2 h-2 rounded-full {{ $isTesting ? 'bg-amber-500 animate-pulse' : 'bg-slate-400' }}"></span>
                        Status Proyek: {{ $statusName }}
                    </span>
                </div>
            </div>
        </section>

        {{-- Alert Bila Belum Masuk Fase Testing --}}
        @if(!$hasUat && !$canSubmit)
            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900 shadow-xs">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                    <div>
                        <h3 class="font-bold text-amber-900">Pengujian Belum Dapat Dimulai</h3>
                        <p class="mt-1 text-xs text-amber-800 leading-relaxed">
                            Proyek saat ini masih berstatus <strong>{{ $statusName }}</strong>. Berdasarkan aturan TeFa, tahap UAT hanya dibuka saat pengerjaan task telah mencapai <strong>100%</strong> dan ditandai masuk fase Testing oleh Pengurus/Dosen PM.
                        </p>
                    </div>
                </div>
            </section>
        @endif

        {{-- FORMULIR PENGUJIAN BERTINGKAT (Bila Proyek Testing & Siap Diuji) --}}
        @if($canSubmit)
            <form action="{{ route('klien.projek-saya.uat.submit', $proposalId) }}" method="POST" class="space-y-6">
                @csrf

                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            {{ $hasUat ? 'Pengujian Ulang Sesi UAT' : 'Formulir Lembar Pengujian UAT Bertingkat' }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Silakan lakukan uji coba per modul dan kategori di bawah ini. Pilih status <strong>Sesuai</strong> bila telah memenuhi kriteria, atau <strong>Belum Sesuai / Ada Kendala</strong> disertai penjelasan.
                        </p>
                    </div>
                    <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200">
                        Total {{ $globalIdx }} Butir Skenario Uji
                    </span>
                </div>

                {{-- HIERARKIS: LOOP LEVEL 1 - MODUL --}}
                @foreach($groupedForm as $moduleName => $categories)
                    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-xs">
                        {{-- Header Modul --}}
                        <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white px-6 py-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center font-bold text-xs text-amber-300">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tingkat 1 · Modul Proyek</div>
                                    <h3 class="text-base font-bold text-white">{{ $moduleName }}</h3>
                                    @if(!empty($categories['__desc']))
                                        <p class="text-xs text-slate-300 mt-0.5">{{ $categories['__desc'] }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="text-xs font-medium text-slate-300 bg-white/10 px-2.5 py-1 rounded-lg">
                                {{ count(array_filter(array_keys($categories), fn($k) => $k !== '__desc')) }} Kategori Pengujian
                            </span>
                        </div>

                        {{-- Body Modul: LOOP LEVEL 2 - KATEGORI PENGUJIAN --}}
                        <div class="p-6 space-y-6">
                            @foreach($categories as $categoryKey => $catData)
                                @if($categoryKey === '__desc') @continue @endif
                                @php
                                    $catInfo = $catData['info'];
                                    $scenarioItems = $catData['items'];
                                @endphp
                                <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5 space-y-4">
                                    {{-- Header Kategori --}}
                                    <div class="flex items-start justify-between border-b border-slate-200/80 pb-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-[#0B3B82]"></span>
                                                <h4 class="font-bold text-slate-900 text-sm">
                                                    {{ $catInfo['name'] }}
                                                </h4>
                                                <span class="text-[11px] font-mono text-slate-400 bg-slate-200/70 px-1.5 py-0.5 rounded">
                                                    {{ $categoryKey }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $catInfo['desc'] }}</p>
                                        </div>
                                    </div>

                                    {{-- LOOP LEVEL 3 - BUTIR SKENARIO UJI --}}
                                    <div class="space-y-4">
                                        @foreach($scenarioItems as $item)
                                            @php $idx = $item['global_index']; @endphp
                                            <div class="rounded-xl border border-slate-200 bg-white p-4.5 space-y-3 shadow-2xs">
                                                {{-- Hidden Inputs untuk menjaga relasi bertingkat ke backend --}}
                                                <input type="hidden" name="items[{{ $idx }}][module_name]" value="{{ $item['module_name'] }}">
                                                <input type="hidden" name="items[{{ $idx }}][test_category]" value="{{ $item['test_category'] }}">
                                                <input type="hidden" name="items[{{ $idx }}][feature_name]" value="{{ $item['feature_name'] }}">
                                                <input type="hidden" name="items[{{ $idx }}][test_scenario]" value="{{ $item['test_scenario'] }}">
                                                <input type="hidden" name="items[{{ $idx }}][bug_severity]" value="MINOR">

                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="space-y-1">
                                                        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">
                                                            Skenario Uji #{{ $idx + 1 }}
                                                        </div>
                                                        <p class="text-sm font-medium text-slate-800 leading-snug">
                                                            {{ $item['test_scenario'] }}
                                                        </p>
                                                    </div>
                                                </div>

                                                {{-- Pilihan Hasil & Catatan --}}
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2">
                                                    <div class="md:col-span-1">
                                                        <label for="result-{{ $idx }}" class="block text-xs font-bold text-slate-700 mb-1">
                                                            Hasil Pengujian <span class="text-red-600">*</span>
                                                        </label>
                                                        <select id="result-{{ $idx }}"
                                                                name="items[{{ $idx }}][result]"
                                                                required
                                                                class="w-full text-xs font-medium rounded-xl border border-slate-300 bg-white px-3 py-2.5 focus:border-[#0B3B82] focus:ring-1 focus:ring-[#0B3B82]">
                                                            <option value="">-- Pilih Hasil Uji --</option>
                                                            <option value="PASSED" {{ old("items.{$idx}.result", $item['previous_result']) === 'PASSED' ? 'selected' : '' }}>
                                                                ✓ Sesuai (Lulus)
                                                            </option>
                                                            <option value="FAILED" {{ old("items.{$idx}.result", $item['previous_result']) === 'FAILED' ? 'selected' : '' }}>
                                                                ✕ Belum Sesuai
                                                            </option>
                                                            <option value="BUG_FOUND" {{ old("items.{$idx}.result", $item['previous_result']) === 'BUG_FOUND' ? 'selected' : '' }}>
                                                                ⚠ Ada Kendala / Bug
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="md:col-span-2">
                                                        <label for="feedback-{{ $idx }}" class="block text-xs font-bold text-slate-700 mb-1">
                                                            Catatan / Keterangan Temuan
                                                        </label>
                                                        <input type="text"
                                                               id="feedback-{{ $idx }}"
                                                               name="items[{{ $idx }}][client_feedback]"
                                                               value="{{ old("items.{$idx}.client_feedback") }}"
                                                               placeholder="Tuliskan catatan hasil uji atau rincian kendala bila belum sesuai..."
                                                               class="w-full text-xs rounded-xl border border-slate-300 bg-white px-3 py-2.5 focus:border-[#0B3B82] focus:ring-1 focus:ring-[#0B3B82]">
                                                    </div>
                                                </div>

                                                @if(!empty($item['review_notes']))
                                                    <div class="rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-900">
                                                        <strong>Catatan Tindak Lanjut Pengurus:</strong> {{ $item['review_notes'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Catatan Umum & Tombol Kirim --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 space-y-4 shadow-xs">
                    <div>
                        <label for="client_notes" class="block text-xs font-bold text-slate-800 mb-1">
                            Catatan Keseluruhan Pengujian UAT (Opsional)
                        </label>
                        <textarea id="client_notes"
                                  name="client_notes"
                                  rows="3"
                                  class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:border-[#0B3B82] focus:ring-1 focus:ring-[#0B3B82]"
                                  placeholder="Tuliskan kesimpulan umum atau pesan tambahan untuk Pengurus TeFa dan Tim Pengembang...">{{ old('client_notes') }}</textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <p class="text-xs text-slate-500">
                            Setelah dikirim, notifikasi otomatis akan diteruskan ke <strong>Pengurus TeFa</strong> dan <strong>Dosen PM</strong> proyek ini.
                        </p>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-[#0B3B82] px-6 py-3 text-xs font-bold text-white hover:bg-[#082a5c] shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                            </svg>
                            <span>Kirim hasil untuk diperiksa</span>
                        </button>
                    </div>
                </div>
            </form>
        @endif

        {{-- VISUALISASI HASIL SESI UAT TERAKHIR (BERTINGKAT) --}}
        @if($hasUat)
            <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-xs space-y-6 p-6">
                <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Sesi Terkini · {{ $uat['session_date'] ?? '—' }}
                            </span>
                        </div>
                        <h2 class="mt-1 text-xl font-bold text-slate-900">
                            Hasil Pengujian:
                            @if(($uat['overall_result'] ?? '') === 'PASSED')
                                <span class="text-emerald-600">Seluruh Skenario Sesuai (Lulus)</span>
                            @elseif(($uat['overall_result'] ?? '') === 'PARTIAL')
                                <span class="text-amber-600">Sebagian Sesuai (Perlu Tindak Lanjut)</span>
                            @else
                                <span class="text-rose-600">Perlu Perbaikan (Belum Lulus)</span>
                            @endif
                        </h2>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="rounded-xl px-3 py-1.5 text-xs font-bold {{ $reviewStatus === 'APPROVED' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($reviewStatus === 'NEEDS_IMPROVEMENT' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-blue-50 text-blue-800 border border-blue-200') }}">
                            {{ ['PENDING_REVIEW' => 'Menunggu Pemeriksaan Pengurus', 'NEEDS_IMPROVEMENT' => 'Perlu Perbaikan Tim', 'READY_FOR_RETEST' => 'Siap Diuji Ulang', 'APPROVED' => 'Disetujui Pengurus'][$reviewStatus] ?? 'Dalam Proses' }}
                        </span>
                    </div>
                </div>

                @if(!empty($uat['review_notes']))
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-xs text-slate-700">
                        <strong class="font-bold text-slate-900">Catatan Pengurus TeFa:</strong> {{ $uat['review_notes'] }}
                    </div>
                @endif

                @if(!empty($uat['client_notes']))
                    <div class="text-xs text-slate-600 bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                        <strong class="font-bold text-slate-800">Catatan Klien:</strong>
                        <p class="mt-1 whitespace-pre-line">{{ $uat['client_notes'] }}</p>
                    </div>
                @endif

                {{-- Tampilan Rincian Bertingkat: Modul -> Kategori -> Skenario --}}
                <div class="space-y-5">
                    <h3 class="text-sm font-bold text-slate-900">Rincian Pengujian Bertingkat</h3>

                    @foreach($existingGrouped as $modName => $categories)
                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-5 space-y-4">
                            <div class="flex items-center gap-2 border-b border-slate-200 pb-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#0B3B82]"></span>
                                <h4 class="font-bold text-slate-900 text-sm">Modul: {{ $modName }}</h4>
                            </div>

                            <div class="space-y-4">
                                @foreach($categories as $catKey => $catData)
                                    <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h5 class="text-xs font-bold text-slate-800">{{ $catData['name'] }}</h5>
                                            <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-2 py-0.5 rounded">{{ $catKey }}</span>
                                        </div>

                                        <div class="divide-y divide-slate-100">
                                            @foreach($catData['items'] as $item)
                                                <div class="py-2.5 first:pt-0 last:pb-0 flex flex-col md:flex-row md:items-center justify-between gap-2">
                                                    <div class="space-y-0.5">
                                                        <p class="text-xs font-medium text-slate-800">{{ $item['test_scenario'] ?? $item['feature_name'] }}</p>
                                                        @if(!empty($item['client_feedback']) || !empty($item['bug_description']))
                                                            <p class="text-[11px] text-slate-500 italic">"{{ $item['bug_description'] ?? $item['client_feedback'] }}"</p>
                                                        @endif
                                                        @if(!empty($item['review_notes']))
                                                            <p class="text-[11px] text-amber-700 font-medium">Tindak lanjut: {{ $item['review_notes'] }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        @if(($item['result'] ?? '') === 'PASSED')
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                                ✓ Sesuai
                                                            </span>
                                                        @elseif(($item['result'] ?? '') === 'FAILED')
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                                ✕ Belum Sesuai
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                                ⚠ Kendala
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Sign-off Section (Jika Disetujui Pengurus) --}}
                @if($isApproved && !$isSigned && ($uat['overall_result'] ?? null) === 'PASSED')
                    <form action="{{ route('klien.projek-saya.uat.signoff', $proposalId) }}" method="POST" class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-6 space-y-4">
                        @csrf
                        <div>
                            <h3 class="text-base font-bold text-emerald-950">Persetujuan &amp; Pengesahan UAT (Sign-off)</h3>
                            <p class="text-xs text-emerald-800 mt-1 leading-relaxed">
                                Pengurus TeFa telah memeriksa dan menyetujui bahwa seluruh fitur telah sesuai. Pengesahan ini menandai diterimanya hasil proyek dan melanjutkan status proyek ke tahap <strong>Handover (BAST)</strong>.
                            </p>
                        </div>
                        <div>
                            <label for="client_signoff_name" class="block text-xs font-bold text-slate-800 mb-1">
                                Nama PIC Klien yang Mengesahkan
                            </label>
                            <input id="client_signoff_name"
                                   name="client_signoff_name"
                                   required
                                   value="{{ old('client_signoff_name', $user['name'] ?? '') }}"
                                   class="w-full max-w-md text-xs rounded-xl border border-slate-300 bg-white px-3 py-2.5">
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-5 py-2.5 text-xs font-bold text-white hover:bg-emerald-800 transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                            <span>Sahkan &amp; Tandatangani UAT</span>
                        </button>
                    </form>
                @elseif($isSigned)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold">✓</div>
                        <p class="text-xs text-emerald-900 font-medium">
                            Hasil UAT telah disahkan secara resmi oleh <strong>{{ $uat['client_signoff_name'] }}</strong> pada {{ $signedAt }}. Proyek berlanjut ke tahap Handover.
                        </p>
                    </div>
                @endif
            </section>
        @endif

    </main>
</div>
</body>
</html>