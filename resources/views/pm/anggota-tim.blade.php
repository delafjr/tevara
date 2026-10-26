<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen Anggota Tim - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen">

@php
    $user = $user ?? session('user');
    $userName = $user['name'] ?? 'Project Manager';
    $isDosen = session('active_role') === 'dosen';

    // Ikon kategori per tipe proyek
    $katIkon = [
        'IoT & Web Portal'       => 'iot-web',
        'Mobile App (Flutter)'   => 'mobile',
        'IoT & Desktop'          => 'iot-desktop',
        'Web Dashboard'          => 'web-dashboard',
        'AgriTech IoT'           => 'agritech',
    ];

    $timProyek = [
        [
            'id'          => 'PRJ-2026/014',
            'kategori'    => 'IoT & Web Portal',
            'status'      => 'Aktif',
            'status_type' => 'aktif',
            'judul'       => 'Sistem Monitoring Rantai Pasok Terpadu',
            'klien'       => 'PT. ABC Indonesia',
            'jml'         => 4,
            'expanded'    => true,
            'avatars'     => ['DP','AR','SN','KS'],
            'anggota'     => [
                ['inisial'=>'DP','nama'=>'Dimas Prasetyo', 'nim'=>'22641181999','prodi'=>'D3 Teknik Informatika','peran'=>'Backend Lead'],
                ['inisial'=>'AR','nama'=>'Ahmad Rifai',    'nim'=>'22641181645','prodi'=>'D3 Teknik Informatika','peran'=>'Fullstack Lead'],
                ['inisial'=>'SN','nama'=>'Siti Nurhaliza', 'nim'=>'22641181812','prodi'=>'D3 Teknik Informatika','peran'=>'UI/UX Designer'],
                ['inisial'=>'KS','nama'=>'Kevin Sanjaya',  'nim'=>'22641181613','prodi'=>'D3 Teknik Informatika','peran'=>'QA Tester'],
            ],
        ],
        [
            'id'          => 'PRJ-2026/009',
            'kategori'    => 'Mobile App (Flutter)',
            'status'      => 'Aktif',
            'status_type' => 'aktif',
            'judul'       => 'Aplikasi Mobile Portal Layanan Nasabah & Pembayaran Digital',
            'klien'       => 'PT. DEF Solusi Finansial',
            'jml'         => 3,
            'expanded'    => false,
            'avatars'     => ['BR','PA','DS'],
            'anggota'     => [
                ['inisial'=>'BR','nama'=>'Bagas Rahmawan',   'nim'=>'22641181701','prodi'=>'D3 Teknik Informatika','peran'=>'Mobile Dev'],
                ['inisial'=>'PA','nama'=>'Putri Ayu Lestari','nim'=>'22641181722','prodi'=>'D3 Teknik Informatika','peran'=>'UI/UX Designer'],
                ['inisial'=>'DS','nama'=>'Daffa Syahputra',  'nim'=>'22641181745','prodi'=>'D3 Teknik Informatika','peran'=>'QA Tester'],
            ],
        ],
        [
            'id'          => 'PRJ-2026/021',
            'kategori'    => 'IoT & Desktop',
            'status'      => 'Aktif',
            'status_type' => 'aktif',
            'judul'       => 'Smart Warehouse Environmental Sensor & Desktop Control Unit',
            'klien'       => 'Dinas Ketahanan Pangan',
            'jml'         => 2,
            'expanded'    => false,
            'avatars'     => ['BR','MI'],
            'anggota'     => [
                ['inisial'=>'BR','nama'=>'Budi Ramadhan',  'nim'=>'22641181810','prodi'=>'D4 Teknik Telekomunikasi','peran'=>'DevOps'],
                ['inisial'=>'MI','nama'=>'M. Irfan Hakim', 'nim'=>'22641181833','prodi'=>'D3 Teknik Informatika','peran'=>'Frontend Lead'],
            ],
        ],
        [
            'id'          => 'PRJ-2026/005',
            'kategori'    => 'Web Dashboard',
            'status'      => 'Aktif',
            'status_type' => 'aktif',
            'judul'       => 'Sistem Informasi Manajemen Inventori & Logistik Sekolah',
            'klien'       => 'Dinas Pendidikan Provinsi',
            'jml'         => 3,
            'expanded'    => false,
            'avatars'     => ['FA','NI','HW'],
            'anggota'     => [
                ['inisial'=>'FA','nama'=>'Farhan Aditya','nim'=>'22641181902','prodi'=>'D3 Teknik Informatika','peran'=>'Frontend Lead'],
                ['inisial'=>'NI','nama'=>'Nurul Izzah',  'nim'=>'22641181919','prodi'=>'D3 Teknik Informatika','peran'=>'Backend Lead'],
                ['inisial'=>'HW','nama'=>'Hendra Wijaya','nim'=>'22641181954','prodi'=>'D3 Teknik Informatika','peran'=>'DevOps'],
            ],
        ],
        [
            'id'          => 'PRJ-2026/027',
            'kategori'    => 'AgriTech IoT',
            'status'      => 'Inisiasi Tim',
            'status_type' => 'inisiasi',
            'judul'       => 'Smart Farming IoT & Irigasi Otomatis Berbasis Sensor Tanah',
            'klien'       => 'CV. Tani Maju Mandiri',
            'jml'         => 2,
            'expanded'    => false,
            'avatars'     => ['MI','MZ'],
            'anggota'     => [
                ['inisial'=>'MI','nama'=>'M. Iqbal Kurniawan','nim'=>'22641182001','prodi'=>'D4 Teknik Telekomunikasi','peran'=>'Fullstack Lead'],
                ['inisial'=>'MZ','nama'=>'M. Zaki Fadhilah',  'nim'=>'22641182035','prodi'=>'D3 Teknik Informatika','peran'=>'Backend Lead'],
            ],
        ],
    ];

    $peranColors = [
        'Fullstack Lead'  => ['bg'=>'bg-[#DBEAFE]','text'=>'text-[#1D4ED8]'],
        'UI/UX Designer'  => ['bg'=>'bg-[#EDE9FE]','text'=>'text-[#6D28D9]'],
        'Backend Lead'    => ['bg'=>'bg-[#DCFCE7]','text'=>'text-[#15803D]'],
        'QA Tester'       => ['bg'=>'bg-[#FEF3C7]','text'=>'text-[#B45309]'],
        'Frontend Lead'   => ['bg'=>'bg-[#DBEAFE]','text'=>'text-[#1D4ED8]'],
        'DevOps'          => ['bg'=>'bg-[#FCE7F3]','text'=>'text-[#9D174D]'],
        'Mobile Dev'      => ['bg'=>'bg-[#CCFBF1]','text'=>'text-[#0F766E]'],
    ];

    $katColors = [
        'IoT & Web Portal'     => ['bg'=>'bg-[#DBEAFE]','text'=>'text-[#1D4ED8]'],
        'Mobile App (Flutter)' => ['bg'=>'bg-[#EDE9FE]','text'=>'text-[#6D28D9]'],
        'IoT & Desktop'        => ['bg'=>'bg-[#CCFBF1]','text'=>'text-[#0F766E]'],
        'Web Dashboard'        => ['bg'=>'bg-[#FEF3C7]','text'=>'text-[#B45309]'],
        'AgriTech IoT'         => ['bg'=>'bg-[#DCFCE7]','text'=>'text-[#15803D]'],
    ];

    // Warna avatar berdasarkan inisial untuk variasi
    $avatarColors = ['#0B254A','#1D4ED8','#15803D','#B45309','#6D28D9','#0F766E','#9D174D','#DC2626'];
@endphp

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <x-sidebar active="anggota-tim" />

    {{-- ===== MAIN CONTENT ===== --}}
    <main class="flex-1 ml-64 p-8 min-h-screen flex flex-col gap-6">

        {{-- ── 1. PAGE HEADER ── --}}
        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
            {{-- gradient dekoratif --}}
            <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                    Manajemen Anggota
                </span>
                <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                    Tim <span class="text-[#FFB800]">Mahasiswa</span>
                </h1>
                <p class="text-sm text-slate-400 mt-1.5 max-w-2xl leading-relaxed">
                    Pantau alokasi mahasiswa pelaksana Teaching Factory, distribusi role/peran, susunan struktur tim, serta status keaktifan anggota per proyek binaan Anda.
                </p>
            </div>
        </div>

        @if(empty($timProyek))
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs px-8 py-14 text-center">
                <h2 class="text-lg font-bold text-[#0B254A]">Belum ada proyek terdaftar</h2>
                <p class="text-sm text-slate-500 mt-2">Informasi tim akan muncul setelah proyek ditugaskan kepada Anda.</p>
            </div>
        @else
        {{-- ── 2. STAT CARDS ── --}}
        <div class="grid grid-cols-3 gap-5">

            {{-- Card 1: Total Mahasiswa Aktif --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Total Mahasiswa</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Aktif</p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-[#EBF3FE] flex items-center justify-center text-[#2563EB]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14v7M3 9v7a9 9 0 0 0 18 0V9"/>
                        </svg>
                    </div>
                </div>
                <p class="text-4xl font-extrabold text-[#0B254A] leading-none">18</p>
                <p class="text-xs font-semibold text-slate-500 mt-2">Mahasiswa</p>
                <div class="flex items-center gap-1.5 mt-1">
                    <svg class="w-3.5 h-3.5 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0"/>
                    </svg>
                    <p class="text-[11px] text-slate-400">Tersebar di 5 Proyek</p>
                </div>
                <p class="text-[11px] font-semibold text-[#16A34A] mt-0.5">Aktif</p>
            </div>

            {{-- Card 2: Total Tim Proyek --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Total Tim Proyek</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">&nbsp;</p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-[#DBEAFE] flex items-center justify-center text-[#2563EB]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-4xl font-extrabold text-[#0B254A] leading-none">5</p>
                <p class="text-xs font-semibold text-slate-500 mt-2">Tim Proyek</p>
                <div class="flex items-center gap-1.5 mt-1">
                    <svg class="w-3.5 h-3.5 text-[#16A34A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <p class="text-[11px] font-semibold text-[#16A34A]">100% Tim Terisi Lengkap</p>
                </div>
            </div>

            {{-- Card 3: Rata-rata Anggota / Tim --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Rata-rata Anggota /</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Tim</p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-[#EDE9FE] flex items-center justify-center text-[#6D28D9]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-4xl font-extrabold text-[#0B254A] leading-none">6</p>
                <p class="text-xs font-semibold text-slate-500 mt-2">Mahasiswa</p>
                <div class="flex items-center gap-1.5 mt-1">
                    <svg class="w-3.5 h-3.5 text-[#6D28D9]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0"/>
                    </svg>
                    <p class="text-[11px] font-semibold text-[#6D28D9]">Distribusi Tim Seimbang</p>
                </div>
            </div>

        </div>

        {{-- ── 3. SEARCH & FILTER BAR ── --}}
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Search --}}
            <div class="relative flex-1 min-w-60">
                <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                </span>
                <input
                    type="text"
                    id="search-anggota"
                    placeholder="Cari nama mahasiswa, NIM, peran, atau proyek..."
                    class="w-full pl-9 pr-4 py-2.5 text-xs text-slate-600 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]/20 placeholder:text-slate-400 transition"
                >
            </div>
            {{-- Filter Role --}}
            <div class="relative">
                <select id="filter-role" class="appearance-none pl-3 pr-8 py-2.5 text-xs text-slate-600 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-[#2563EB] cursor-pointer font-medium">
                    <option>Filter Role: Semua Role</option>
                    <option>Fullstack Lead</option>
                    <option>Backend Lead</option>
                    <option>Frontend Lead</option>
                    <option>UI/UX Designer</option>
                    <option>QA Tester</option>
                    <option>Mobile Dev</option>
                    <option>DevOps</option>
                </select>
                <span class="absolute inset-y-0 right-2.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </span>
            </div>
            {{-- Sort --}}
            <div class="relative">
                <select id="sort-anggota" class="appearance-none pl-3 pr-8 py-2.5 text-xs text-slate-600 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-[#2563EB] cursor-pointer font-medium">
                    <option>Urutkan: Jumlah Anggota Terbanyak</option>
                    <option>Urutkan: Nama Proyek A-Z</option>
                    <option>Urutkan: Status Aktif Dulu</option>
                </select>
                <span class="absolute inset-y-0 right-2.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </span>
            </div>
        </div>

        {{-- ── 4. TIM PROYEK LIST ── --}}
        <div x-data="{ expandedId: 'PRJ-2026/014' }" class="flex flex-col gap-4">

            @foreach($timProyek as $idx => $tim)
            @php
                $kc  = $katColors[$tim['kategori']] ?? ['bg'=>'bg-slate-100','text'=>'text-slate-500'];
                $isAktif = $tim['status_type'] === 'aktif';
                // Avatar background warna bergilir
                $avBg = $avatarColors[$idx % count($avatarColors)];
            @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden">

                {{-- Card Header --}}
                <div class="px-6 py-4 flex items-center gap-4">

                    {{-- Ikon Kategori --}}
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-[#F1F5F9] flex items-center justify-center text-slate-400">
                        @if(str_contains($tim['kategori'], 'IoT') && str_contains($tim['kategori'], 'Web'))
                        {{-- IoT & Web --}}
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z"/>
                        </svg>
                        @elseif(str_contains($tim['kategori'], 'Mobile'))
                        {{-- Mobile --}}
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 3h3m-3 3h3"/>
                        </svg>
                        @elseif(str_contains($tim['kategori'], 'Desktop'))
                        {{-- Desktop --}}
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/>
                        </svg>
                        @elseif(str_contains($tim['kategori'], 'Dashboard'))
                        {{-- Dashboard --}}
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                        </svg>
                        @else
                        {{-- AgriTech / lainnya --}}
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
                        </svg>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $kc['bg'] }} {{ $kc['text'] }}">{{ strtoupper($tim['kategori']) }}</span>
                            <span class="text-[10px] font-bold text-slate-400 tracking-wide">{{ $tim['id'] }}</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $isAktif ? 'bg-[#DCFCE7] text-[#15803D]' : 'bg-[#FEF3C7] text-[#B45309]' }}">
                                <span class="w-1.5 h-1.5 rounded-full inline-block {{ $isAktif ? 'bg-[#16A34A]' : 'bg-[#F59E0B]' }}"></span>
                                {{ $tim['status'] }}
                            </span>
                        </div>
                        <p class="text-sm font-extrabold text-[#0B254A] leading-tight truncate">{{ $tim['judul'] }}</p>
                        <div class="flex items-center gap-1.5 mt-1 text-[11px] text-slate-400">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0"/>
                            </svg>
                            <span>{{ $tim['jml'] }} Mahasiswa Ditugaskan</span>
                        </div>
                    </div>

                    {{-- Avatar stack + Toggle --}}
                    <div class="flex items-center gap-3 flex-shrink-0">
                        {{-- Avatar stack --}}
                        <div class="flex -space-x-2">
                            @foreach(array_slice($tim['avatars'], 0, 4) as $avi => $av)
                            @php $bg = $avatarColors[($idx + $avi) % count($avatarColors)]; @endphp
                            <div class="w-7 h-7 rounded-full border-2 border-white flex items-center justify-center text-[9px] font-bold text-white ring-1 ring-slate-100"
                                 style="background:{{ $bg }}">{{ $av }}</div>
                            @endforeach
                        </div>
                        {{-- Toggle --}}
                        <button
                            type="button"
                            @click="expandedId = (expandedId === '{{ $tim['id'] }}') ? null : '{{ $tim['id'] }}'"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-[11px] font-semibold text-slate-500 hover:bg-slate-50 transition-all cursor-pointer"
                        >
                            <span x-text="expandedId === '{{ $tim['id'] }}' ? 'Tutup Rincian Tim' : 'Lihat Rincian Tim'">Lihat Rincian Tim</span>
                            <svg class="w-3 h-3 transition-transform duration-200" :class="expandedId === '{{ $tim['id'] }}' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>
                    </div>

                </div>

                {{-- ── Expanded: Anggota Cards ── --}}
                @if(!empty($tim['anggota']))
                <div
                    x-show="expandedId === '{{ $tim['id'] }}'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="border-t border-slate-100 px-6 py-5"
                >
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($tim['anggota'] as $mi => $member)
                        @php
                            $pc  = $peranColors[$member['peran']] ?? ['bg'=>'bg-slate-100','text'=>'text-slate-600'];
                            $mbg = $avatarColors[($idx + $mi) % count($avatarColors)];
                        @endphp
                        <div class="flex items-center gap-4 p-4 rounded-2xl border border-slate-100 bg-[#F8FAFC] hover:bg-white hover:shadow-xs transition-all">
                            {{-- Avatar --}}
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-extrabold text-white flex-shrink-0"
                                 style="background:{{ $mbg }}">{{ $member['inisial'] }}</div>
                            {{-- Info --}}
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-extrabold text-[#0B254A] truncate">{{ $member['nama'] }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">NIM: {{ $member['nim'] }}</p>
                                <p class="text-[11px] text-slate-400">· {{ $member['prodi'] }}</p>
                            </div>
                            {{-- Peran badge --}}
                            <span class="flex-shrink-0 px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $pc['bg'] }} {{ $pc['text'] }}">{{ $member['peran'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
            @endforeach

            {{-- Pagination --}}
            <div class="flex items-center justify-between pt-2">
                <p class="text-xs text-slate-400">Menampilkan 1-5 dari 5 Tim Proyek Mahasiswa</p>
                <div class="flex items-center gap-1.5">
                    <button class="w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-400 flex items-center justify-center hover:bg-slate-50 cursor-pointer transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                        </svg>
                    </button>
                    <button class="w-7 h-7 rounded-lg bg-[#0B254A] text-white text-xs font-bold flex items-center justify-center cursor-pointer">1</button>
                    <button class="w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-400 flex items-center justify-center hover:bg-slate-50 cursor-pointer transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                        </svg>
                    </button>
                </div>
            </div>

        </div>
        @endif

    </main>

</div>

</body>
</html>
