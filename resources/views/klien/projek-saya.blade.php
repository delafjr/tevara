{{-- == ( halaman daftar projek saya - klien ) == --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Daftar Projek Saya - Pantau progres eksekusi teknis, evaluasi berkas deliverable, dan spesifikasi pengerjaan proyek Teaching Factory secara terpadu.">
    <title>Daftar Projek Saya - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen"
      x-data="{
          searchQuery: '',
          activeCategory: 'Semua',
          expanded: {
              'PRJ-2026/014': true,
              'PRJ-2026/008': false,
              'PRJ-2026/021': false
          },
          handoverModalOpen: false,
          handoverProject: '',
          handoverMessage: '',
          toggle(id) {
              this.expanded[id] = !this.expanded[id];
          },
          openHandoverForm(projectId) {
              this.handoverProject = projectId;
              this.handoverModalOpen = true;
          },
          matches(p) {
              const matchCat = this.activeCategory === 'Semua' ||
                  (this.activeCategory === 'Web Platform' && (p.category === 'Web' || p.category_badge.includes('Web') || p.title.includes('Web'))) ||
                  (this.activeCategory === 'Mobile' && (p.category === 'Mobile' || p.title.includes('Mobile'))) ||
                  (this.activeCategory === 'IoT & Hardware' && (p.category === 'IoT & Hardware' || p.title.includes('IoT') || p.title.includes('Sensor')));

              const q = this.searchQuery.toLowerCase().trim();
              if (!q) return matchCat;

              const matchQuery = p.id.toLowerCase().includes(q) ||
                  p.title.toLowerCase().includes(q) ||
                  p.dosen.name.toLowerCase().includes(q) ||
                  p.status.toLowerCase().includes(q);

              return matchCat && matchQuery;
          }
      }">

@php
    $user = $user ?? session('user', []);
    $userName = $user['name'] ?? 'Budi Santoso';
    $handoverProjects = $handoverProjects ?? [];
    $handoverProjectsAvailable = $handoverProjectsAvailable ?? false;

    $projectsData = [
        [
            'id'               => 'PRJ-2026/014',
            'category'         => 'IoT & Hardware',
            'category_badge'   => 'IoT & Web',
            'status'           => 'Dalam Pengerjaan',
            'status_type'      => 'running',
            'title'            => 'Sistem Monitoring Rantai Pasok Terpadu (IoT & Web)',
            'icon_type'        => 'iot',
            'dates'            => '15 Agt 2026 — 25 Nov 2026',
            'days_left'        => 'Sisa 56 Hari Kalender',
            'progress'         => 72,
            'dosen' => [
                'initials'     => 'HK',
                'name'         => 'Dr. Hendra Kusuma, M.T.',
                'tag'          => 'DOSEN PEMBIMBING / PM',
                'nidn'         => 'NIDN: 0012058401 • Lab Rekayasa Lunak',
                'role_badge'   => 'Penanggung Jawab Teknis',
            ],
            'modules' => [
                [
                    'icon'     => 'lock',
                    'title'    => 'Modul 1: Autentikasi & RBAC',
                    'desc'     => 'Spesifikasi Single Sign-On (OAuth2), manajemen hak akses multi-role (Klien, Admin, Operator Gudang), enkripsi token sesi, dan pencatatan audit log terpusat.',
                ],
                [
                    'icon'     => 'signal',
                    'title'    => 'Modul 2: Telemetri IoT Stream',
                    'desc'     => 'Integrasi broker MQTT broker data logger, websocket real-time feed ke dashboard monitoring kondisi kontainer, dan visualisasi heatmap temperatur armada.',
                ],
                [
                    'icon'     => 'file',
                    'title'    => 'Modul 3: Laporan & Export Data',
                    'desc'     => 'Format otomasi laporan kepatuhan suhu & kelembapan berkala, filter rentang tanggal, serta engine unduhan format PDF resmi dan spreadsheet Excel.',
                ],
            ],
            'students' => [
                [
                    'icon'     => 'code',
                    'name'     => 'Ahmad Rifai',
                    'role'     => 'Fullstack Lead',
                ],
                [
                    'icon'     => 'palette',
                    'name'     => 'Siti Nurhaliza',
                    'role'     => 'UI/UX Designer',
                ],
                [
                    'icon'     => 'server',
                    'name'     => 'Dimas Prasetyo',
                    'role'     => 'Backend Engineer',
                ],
                [
                    'icon'     => 'shield',
                    'name'     => 'Kevin Sanjaya',
                    'role'     => 'QA & Verification',
                ],
            ],
        ],
        [
            'id'               => 'PRJ-2026/008',
            'category'         => 'Mobile',
            'category_badge'   => 'Mobile Platform',
            'status'           => 'Tahap UAT & Validasi',
            'status_type'      => 'uat',
            'title'            => 'Aplikasi Mobile Portal Layanan Nasabah & Pembayaran Digital',
            'icon_type'        => 'mobile',
            'dates'            => '01 Jul 2026 — 30 Okt 2026',
            'days_left'        => 'Sisa 12 Hari Kalender',
            'progress'         => 88,
            'dosen' => [
                'initials'     => 'IM',
                'name'         => 'Irfan Maulana, S.T., M.Kom.',
                'tag'          => 'DOSEN PEMBIMBING / PM',
                'nidn'         => 'NIDN: 0019088203 • Lab Mobile Computing',
                'role_badge'   => 'Penanggung Jawab Teknis',
            ],
            'modules' => [
                [
                    'icon'     => 'lock',
                    'title'    => 'Modul 1: Digital KYC & Biometrik',
                    'desc'     => 'Verifikasi identitas nasabah, pemindaian e-KTP dan integrasi biometrik face recognition terenkripsi sesuai standar OJK.',
                ],
                [
                    'icon'     => 'signal',
                    'title'    => 'Modul 2: Payment Gateway & QRIS Dinamis',
                    'desc'     => 'Integrasi multi-channel pembayaran perbankan, mutasi transaksi real-time, dan otomasi settlement rekening escrow.',
                ],
                [
                    'icon'     => 'file',
                    'title'    => 'Modul 3: Push Notification & Audit Trailing',
                    'desc'     => 'Notifikasi instan via FCM/APNs, ringkasan transaksi bulanan, serta pencatatan audit log forensik transaksi perbankan.',
                ],
            ],
            'students' => [
                [
                    'icon'     => 'code',
                    'name'     => 'Fajar Nugroho',
                    'role'     => 'Mobile Flutter Lead',
                ],
                [
                    'icon'     => 'palette',
                    'name'     => 'Nadia Amanda',
                    'role'     => 'UI/UX Specialist',
                ],
                [
                    'icon'     => 'server',
                    'name'     => 'Rizky Pratama',
                    'role'     => 'API & Backend Specialist',
                ],
                [
                    'icon'     => 'shield',
                    'name'     => 'Dewi Lestari',
                    'role'     => 'Security & QA Engineer',
                ],
            ],
        ],
        [
            'id'               => 'PRJ-2026/021',
            'category'         => 'IoT & Hardware',
            'category_badge'   => 'Hardware & IoT',
            'status'           => 'Dalam Pengerjaan',
            'status_type'      => 'running',
            'title'            => 'Smart Warehouse Environmental Sensor & Desktop Control Unit',
            'icon_type'        => 'robot',
            'dates'            => '10 Sep 2026 — 20 Des 2026',
            'days_left'        => 'Sisa 68 Hari Kalender',
            'progress'         => 88,
            'dosen' => [
                'initials'     => 'BW',
                'name'         => 'Bambang Wijaya, Ph.D.',
                'tag'          => 'DOSEN PEMBIMBING / PM',
                'nidn'         => 'NIDN: 0028047501 • Lab Mekatronika & IoT',
                'role_badge'   => 'Penanggung Jawab Teknis',
            ],
            'modules' => [
                [
                    'icon'     => 'signal',
                    'title'    => 'Modul 1: Firmware Sensor ESP32 & LoRa',
                    'desc'     => 'Pembacaan multi-sensor lingkungan gudang (suhu, kelembapan, gas) transmisi nirkabel jarak jauh via protokol LoRaWAN.',
                ],
                [
                    'icon'     => 'lock',
                    'title'    => 'Modul 2: Desktop Control App (Tauri / Electron)',
                    'desc'     => 'Antarmuka visualisasi layout denah gudang interaktif dengan pemicu alarm otomatis saat melewati ambang batas toleransi.',
                ],
                [
                    'icon'     => 'file',
                    'title'    => 'Modul 3: Sinkronisasi Cloud & Failover Cache',
                    'desc'     => 'Penyimpanan lokal SQLite offline-first dengan pipeline sinkronisasi otomatis terenkripsi saat koneksi cloud online kembali.',
                ],
            ],
            'students' => [
                [
                    'icon'     => 'code',
                    'name'     => 'Bayu Setiawan',
                    'role'     => 'Embedded System Lead',
                ],
                [
                    'icon'     => 'palette',
                    'name'     => 'Anisa Rahmawati',
                    'role'     => 'Desktop UI Engineer',
                ],
                [
                    'icon'     => 'server',
                    'name'     => 'Galih Wicaksono',
                    'role'     => 'Hardware Architect',
                ],
                [
                    'icon'     => 'shield',
                    'name'     => 'Rian Saputra',
                    'role'     => 'Field Tester & QA',
                ],
            ],
        ],
    ];
@endphp

<div class="flex min-h-screen">

    {{-- == ( sidebar komponen DRY ) == --}}
    <x-sidebar active="projek-saya" />

    {{-- == ( konten utama ) == --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen">

        <main class="flex-1 px-8 py-7 space-y-6">

            {{-- == ( header card : judul halaman & deskripsi ) == --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#EFF6FF] via-[#F3F7FD] to-[#FCF8EC] border border-blue-100/70 px-8 py-7 shadow-xs">
                <div class="relative z-10 max-w-4xl">
                    <h1 class="text-2xl lg:text-[28px] font-extrabold text-[#1E293B] tracking-tight">
                        Daftar <span class="text-[#FFB800]">Projek Saya</span>
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-2 leading-relaxed font-normal">
                        Pantau progres eksekusi teknis, evaluasi berkas deliverable, dan spesifikasi pengerjaan proyek Teaching Factory secara terpadu.
                    </p>
                </div>
            </div>

            @if(session('success'))
                <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold">BAST belum dapat dikirim:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="border-b border-slate-200 pb-5" aria-labelledby="bast-ready-title">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-2">
                    <div>
                        <h2 id="bast-ready-title" class="text-sm font-bold text-slate-800">Proyek siap serah terima</h2>
                        <p class="mt-1 text-xs text-slate-500">Daftar ini mengikuti hasil UAT terbaru dari backend.</p>
                    </div>
                    <span class="text-xs font-medium text-slate-500">{{ count($handoverProjects) }} proyek siap</span>
                </div>
                @if(count($handoverProjects))
                    <div class="mt-3 divide-y divide-slate-100 border-y border-slate-100">
                        @foreach($handoverProjects as $handoverProjectItem)
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 py-3">
                                <div>
                                    <p class="text-xs font-bold text-slate-800">{{ $handoverProjectItem['title'] }}</p>
                                    <p class="mt-1 font-mono text-[11px] text-slate-500">{{ $handoverProjectItem['project_code'] }}</p>
                                </div>
                                <button type="button" @click="openHandoverForm(@js($handoverProjectItem['project_code']))"
                                        class="inline-flex w-fit items-center gap-2 rounded-lg bg-[#0B3B82] px-3.5 py-2 text-xs font-semibold text-white hover:bg-[#082d64]">
                                    Isi formulir BAST
                                </button>
                            </div>
                        @endforeach
                    </div>
                @elseif($handoverProjectsAvailable)
                    <p class="mt-3 text-xs text-slate-500">Belum ada proyek dengan hasil UAT terakhir PASSED.</p>
                @else
                    <p class="mt-3 text-xs text-amber-800">Data kelayakan BAST belum dapat dimuat dari backend.</p>
                @endif
            </section>

            {{-- == ( 3 kartu ringkasan metrik statistik ) == --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                {{-- Kartu 1: Total Proyek Portofolio --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6 flex flex-col justify-between hover:shadow-md transition">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400 mb-3">TOTAL PROYEK PORTOFOLIO</p>
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl font-bold text-slate-800">3 Proyek</span>
                            <span class="text-[11px] font-semibold text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-0.5 rounded-full inline-flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                                Aktif Berjalan
                            </span>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                            {{-- Bento Grid Icon --}}
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                                <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Kartu 2: Rata - Rata Progres --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6 flex flex-col justify-between hover:shadow-md transition">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400 mb-3">RATA - RATA PROGRES</p>
                    <div class="flex items-center justify-between gap-3">
                        <div class="space-y-1.5">
                            <span class="text-2xl font-bold text-slate-800">68.4%</span>
                            <div class="w-28 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#0B3B82] rounded-full" style="width: 68.4%"></div>
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                            {{-- Trending Up Icon --}}
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Kartu 3: Distribusi Fase Pengerjaan --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6 flex flex-col justify-between hover:shadow-md transition">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400 mb-3">DISTRIBUSI FASE PENGERJAAN</p>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <span class="text-2xl font-bold text-slate-800">3 Milestone</span>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                            {{-- Clipboard / Checklist Icon --}}
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>

            {{-- == ( filter & search bar ) == --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-2.5 flex flex-col md:flex-row items-center justify-between gap-3">

                {{-- Search Box --}}
                <div class="relative flex-1 w-full">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                        </svg>
                    </span>
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Cari kode proyek, judul deliverable, nama dosen..."
                           class="w-full bg-[#F8FAFC] border border-slate-200/70 rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#0B3B82] focus:bg-white transition">
                </div>

                {{-- Filter Pills --}}
                <div class="flex items-center gap-1.5 flex-shrink-0 flex-wrap">
                    @php
                        $categories = [
                            'Semua'          => 'Semua (3)',
                            'Web Platform'   => 'Web Platform',
                            'Mobile'         => 'Mobile',
                            'IoT & Hardware' => 'IoT & Hardware',
                        ];
                    @endphp

                    @foreach($categories as $catKey => $catLabel)
                        <button type="button"
                                @click="activeCategory = '{{ $catKey }}'"
                                :class="activeCategory === '{{ $catKey }}'
                                    ? 'bg-[#0B3B82] text-white shadow-xs font-semibold'
                                    : 'bg-transparent text-slate-600 hover:bg-slate-100 font-medium'"
                                class="px-4 py-2 rounded-xl text-xs transition cursor-pointer">
                            {{ $catLabel }}
                        </button>
                    @endforeach
                </div>

            </div>

            {{-- == ( daftar kartu proyek ) == --}}
            <div class="space-y-4">

                @foreach($projectsData as $proj)
                <div x-show="matches(@js($proj))"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white rounded-2xl border border-slate-100 shadow-xs transition hover:shadow-md overflow-hidden">

                    {{-- == CARD HEADER (COLLAPSED / EXPANDED TOP BAR) == --}}
                    <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-5">

                        <div class="flex items-start gap-4 flex-1">
                            {{-- Project Icon --}}
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 shadow-xs
                                        {{ $proj['id'] === 'PRJ-2026/014' ? 'bg-[#0B3B82] text-white' : 'bg-[#EAF3FF] text-[#0B3B82]' }}">
                                @if($proj['icon_type'] === 'iot')
                                    {{-- IoT / Broadcast Waves Icon --}}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                        <path d="M4.93 19.07a10 10 0 0 1 0-14.14M19.07 4.93a10 10 0 0 1 0 14.14M7.76 16.24a6 6 0 0 1 0-8.48M16.24 7.76a6 6 0 0 1 0 8.48"/>
                                        <circle cx="12" cy="12" r="2"/>
                                    </svg>
                                @elseif($proj['icon_type'] === 'mobile')
                                    {{-- Mobile Smartphone Icon --}}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                        <rect width="14" height="20" x="5" y="2" rx="3"/>
                                        <path d="M12 18h.01"/>
                                    </svg>
                                @else
                                    {{-- Robotic Arm / Mechanical Hardware Icon --}}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                        <path d="M4 20h16M7 20v-2a2 2 0 0 1 2-2h1.5a2 2 0 0 0 1.8-1.1l2.4-4.8A2 2 0 0 1 16.5 9h1.5M18 6l2 3-2 3M15 9h5"/>
                                    </svg>
                                @endif
                            </div>

                            {{-- Project Basic Info --}}
                            <div class="space-y-1.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-bold text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-0.5 rounded-md font-mono">
                                        {{ $proj['id'] }}
                                    </span>
                                    @if($proj['status_type'] === 'uat')
                                        <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full inline-flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $proj['status'] }}
                                        </span>
                                    @else
                                        <span class="text-xs font-semibold text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-0.5 rounded-full inline-flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                                            {{ $proj['status'] }}
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-base md:text-[17px] font-bold text-slate-800 leading-snug">
                                    {{ $proj['title'] }}
                                </h2>

                                {{-- If collapsed, show Dosen PM line --}}
                                <div x-show="!expanded['{{ $proj['id'] }}']" class="pt-0.5">
                                    <p class="text-xs text-slate-500">
                                        Dosen PM: <span class="font-medium text-slate-700">{{ $proj['dosen']['name'] }}</span>
                                    </p>
                                </div>

                                {{-- If expanded, show date & countdown badge --}}
                                <div x-show="expanded['{{ $proj['id'] }}']" class="flex items-center gap-3 pt-1 flex-wrap">
                                    <span class="text-xs text-slate-500 font-medium inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5"/>
                                        </svg>
                                        {{ $proj['dates'] }}
                                    </span>
                                    <span class="text-[11px] font-semibold text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-0.5 rounded-md inline-flex items-center gap-1.5">
                                        <svg class="w-3 h-3 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                        {{ $proj['days_left'] }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Right Side Actions & Progress --}}
                        <div class="flex items-center justify-between md:justify-end gap-6 flex-shrink-0">

                            {{-- Mini Progress (Shown when collapsed) --}}
                            <div x-show="!expanded['{{ $proj['id'] }}']" class="space-y-1 text-right">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-slate-400">Progres Proyek</span>
                                    <span class="text-sm font-bold text-slate-800">{{ $proj['progress'] }}%</span>
                                </div>
                                <div class="w-32 md:w-36 h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#0B3B82] rounded-full" style="width: {{ $proj['progress'] }}%"></div>
                                </div>
                            </div>

                            {{-- Accordion Toggle Button --}}
                            <button type="button"
                                    @click="toggle('{{ $proj['id'] }}')"
                                    class="bg-[#EAF3FF] hover:bg-[#D9E9FF] text-[#0B3B82] text-xs font-semibold px-4 py-2 rounded-xl inline-flex items-center gap-1.5 transition cursor-pointer flex-shrink-0">
                                <span x-text="expanded['{{ $proj['id'] }}'] ? 'Tutup Detail' : 'Lihat Detail'"></span>
                                <svg class="w-3.5 h-3.5 transition-transform duration-200"
                                     :class="expanded['{{ $proj['id'] }}'] ? 'rotate-180' : ''"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>

                        </div>

                    </div>

                    {{-- == CARD EXPANDED BODY (DETAILED SPECS & TIM) == --}}
                    <div x-show="expanded['{{ $proj['id'] }}']"
                         x-collapse
                         class="px-6 pb-6 pt-2 border-t border-slate-100/80 space-y-6">

                        {{-- 1. Progres Eksekusi Keseluruhan --}}
                        <div class="space-y-2 pt-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/>
                                    </svg>
                                    <span>Progres Eksekusi Keseluruhan</span>
                                </div>
                                <span class="text-base font-bold text-[#0B3B82]">{{ $proj['progress'] }}%</span>
                            </div>
                            <div class="h-2.5 w-full bg-[#DDE9FA] rounded-full overflow-hidden">
                                <div class="h-full bg-[#0B3B82] rounded-full transition-all duration-500"
                                     style="width: {{ $proj['progress'] }}%"></div>
                            </div>
                        </div>

                        {{-- 2. Catatan & Spesifikasi Modul Proyek --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm font-bold text-slate-800">
                                    <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <rect width="18" height="18" x="3" y="3" rx="2"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/>
                                    </svg>
                                    <span>Catatan & Spesifikasi Modul Proyek</span>
                                </div>
                                <span class="text-xs text-slate-400 font-medium">
                                    {{ count($proj['modules']) }} Modul Terdaftar dalam TOR
                                </span>
                            </div>

                            <div class="space-y-3">
                                @foreach($proj['modules'] as $mod)
                                <div class="bg-[#F8FAFD] rounded-xl border border-slate-100 p-4 space-y-1 hover:bg-[#F2F6FC] transition">
                                    <div class="flex items-center gap-2">
                                        @if($mod['icon'] === 'lock')
                                            <svg class="w-4 h-4 text-[#0B3B82] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                                            </svg>
                                        @elseif($mod['icon'] === 'signal')
                                            <svg class="w-4 h-4 text-[#0B3B82] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-[#0B3B82] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                            </svg>
                                        @endif
                                        <h3 class="text-xs md:text-sm font-bold text-slate-800">
                                            {{ $mod['title'] }}
                                        </h3>
                                    </div>
                                    <p class="text-xs text-slate-500 leading-relaxed pl-6">
                                        {{ $mod['desc'] }}
                                    </p>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- 3. Tim Pengembang Mahasiswa & Dosen Pembimbing --}}
                        <div class="bg-[#F0F5FD] rounded-2xl border border-blue-100/70 p-5 space-y-4">
                            <h4 class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">
                                TIM PENGEMBANG MAHASISWA & DOSEN PEMBIMBING
                            </h4>

                            {{-- Dosen Pembimbing Card --}}
                            <div class="bg-white rounded-xl border border-blue-100/60 p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-full bg-[#0E1F40] text-white font-bold text-sm flex items-center justify-center flex-shrink-0 shadow-xs">
                                        {{ $proj['dosen']['initials'] }}
                                    </div>
                                    <div class="space-y-0.5">
                                        <p class="text-[10px] font-bold text-[#0B3B82] uppercase tracking-wide">
                                            {{ $proj['dosen']['tag'] }}
                                        </p>
                                        <h5 class="text-sm font-bold text-slate-800">
                                            {{ $proj['dosen']['name'] }}
                                        </h5>
                                        <p class="text-xs text-slate-400 font-mono">
                                            {{ $proj['dosen']['nidn'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex-shrink-0">
                                    <span class="bg-white border border-blue-200/80 text-[#0B3B82] text-xs font-semibold px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                                        <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                                        </svg>
                                        {{ $proj['dosen']['role_badge'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Students Grid (2x2) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($proj['students'] as $stu)
                                <div class="bg-white rounded-xl border border-blue-100/50 p-3.5 flex items-center gap-3.5 shadow-xs">
                                    <div class="w-8 h-8 rounded-lg bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                                        @if($stu['icon'] === 'code')
                                            {{-- Code / Dev Icon --}}
                                            <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/>
                                            </svg>
                                        @elseif($stu['icon'] === 'palette')
                                            {{-- UI/UX Designer Icon --}}
                                            <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-.778.099-1.533.284-2.253"/>
                                            </svg>
                                        @elseif($stu['icon'] === 'server')
                                            {{-- Server / Backend Icon --}}
                                            <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.75 5.1a3 3 0 0 1 2.4-1.35h7.7a3 3 0 0 1 2.4 1.35l1.6 3.45a4.5 4.5 0 0 1 .9 2.7"/>
                                            </svg>
                                        @else
                                            {{-- Shield / QA Verification Icon --}}
                                            <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="space-y-0.5 min-w-0">
                                        <h6 class="text-xs font-bold text-slate-800 truncate">{{ $stu['name'] }}</h6>
                                        <p class="text-[11px] text-slate-400 font-medium truncate">{{ $stu['role'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                        </div>

                        @if($proj['status_type'] === 'uat')
                        <section class="border-t border-slate-100 pt-5 space-y-4" aria-labelledby="bast-title-{{ $proj['id'] }}">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <h3 id="bast-title-{{ $proj['id'] }}" class="text-sm font-bold text-slate-800">Berita Acara Serah Terima (BAST)</h3>
                                    <p class="text-xs text-slate-500 mt-1">BAST dapat disetujui setelah hasil UAT berstatus PASSED.</p>
                                </div>
                                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Menunggu persetujuan UAT
                                </span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                <div class="rounded-lg border border-slate-100 bg-[#F8FAFD] p-3">
                                    <p class="font-semibold text-slate-700">Serah terima produk</p>
                                    <p class="mt-1 text-slate-500">Repositori source code, dokumentasi API, dan panduan pengguna</p>
                                </div>
                                <div class="rounded-lg border border-slate-100 bg-[#F8FAFD] p-3">
                                    <p class="font-semibold text-slate-700">Masa pemeliharaan</p>
                                    <p class="mt-1 text-slate-500">Lama garansi, tanggal mulai, tanggal selesai, dan catatan pemeliharaan</p>
                                </div>
                            </div>
                            <button type="button" disabled
                                    class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg border border-slate-200 bg-slate-100 px-3.5 py-2 text-xs font-semibold text-slate-400"
                                    title="Gunakan daftar proyek siap serah terima setelah UAT PASSED">
                                Menunggu hasil UAT PASSED
                            </button>
                        </section>
                        @endif

                    </div>

                </div>
                @endforeach

                {{-- Empty Search / Filter State --}}
                <div x-show="!$el.previousElementSibling || searchQuery && [ 'PRJ-2026/014', 'PRJ-2026/008', 'PRJ-2026/021' ].every(id => !matches(projectsData.find(p => p.id === id)))"
                     style="display: none;"
                     class="bg-white rounded-2xl border border-slate-100 p-12 text-center shadow-xs">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Tidak ada proyek ditemukan</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Coba gunakan kata kunci pencarian lain atau pilih kategori yang berbeda.
                    </p>
                </div>

            </div>

        </main>

    </div>

</div>

<div x-show="handoverModalOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="handover-modal-title">
    <div class="fixed inset-0 bg-slate-900/50" @click="handoverModalOpen = false"></div>
    <div class="min-h-screen px-4 py-8 flex items-center justify-center">
        <div class="relative z-10 w-full max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h2 id="handover-modal-title" class="text-base font-bold text-slate-800">Formulir Berita Acara Serah Terima</h2>
                    <p class="mt-1 text-xs text-slate-500">Proyek <span x-text="handoverProject"></span></p>
                </div>
                <button type="button" @click="handoverModalOpen = false" aria-label="Tutup formulir BAST" class="text-xl leading-none text-slate-500 hover:text-slate-800">&times;</button>
            </div>
            <form method="POST" action="{{ route('klien.projek-saya.bast.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
                @csrf
                <input type="hidden" name="project_code" :value="handoverProject">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Nomor BAST</label>
                    <input type="text" value="Diterbitkan sistem setelah UAT disetujui" disabled class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs text-slate-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Tanggal serah terima</label>
                    <input type="date" name="handover_date" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Tautan repositori</label>
                    <input type="url" name="repository_url" placeholder="https://" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Tautan dokumentasi</label>
                    <input type="url" name="documentation_url" placeholder="https://" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Tautan panduan pengguna</label>
                    <input type="url" name="user_manual_url" placeholder="https://" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Nama penandatangan klien</label>
                    <input type="text" name="client_signoff_name" required maxlength="255" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Masa garansi (bulan)</label>
                    <input type="number" name="maintenance_warranty_months" min="0" value="3" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Tanggal mulai pemeliharaan</label>
                    <input type="date" name="maintenance_start_date" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Tanggal selesai pemeliharaan</label>
                    <input type="date" name="maintenance_end_date" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">
                </div>
                <label class="flex items-center gap-2 text-xs font-medium text-slate-700">
                    <input type="checkbox" name="client_approved" value="1" required class="rounded border-slate-300 text-[#0B3B82]">
                    Saya menyetujui serah terima produk
                </label>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Catatan serah terima / pemeliharaan</label>
                    <textarea name="handover_notes" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700"></textarea>
                </div>
                <div class="md:col-span-2 flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-t border-slate-100 pt-4">
                    <p class="text-xs text-amber-800" role="status">Pengiriman hanya berhasil jika hasil UAT terakhir berstatus PASSED.</p>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="handoverModalOpen = false" class="rounded-lg px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="rounded-lg bg-[#0B3B82] px-4 py-2 text-xs font-semibold text-white hover:bg-[#082d64]">Kirim persetujuan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
