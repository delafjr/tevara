<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TEVARA · Teaching Factory Politeknik Negeri Semarang</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8FAFC] font-sans text-[#1E293B] antialiased selection:bg-[#0B3B82] selection:text-white">

@php
    $user = session('user');
    $isLoggedIn = session('api_token') && $user;
@endphp

{{-- Navigation --}}
<header x-data="{ mobileOpen: false, scrolled: false }"
        @scroll.window="scrolled = (window.pageYOffset > 20)"
        :class="scrolled ? 'bg-white/90 backdrop-blur-md shadow-sm border-b border-slate-200/80' : 'bg-transparent border-b border-slate-200/40'"
        class="sticky top-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        {{-- Logo --}}
        <a href="{{ url('/') }}" class="flex items-center gap-3 group">
            <img src="{{ asset('images/tevara-logo.png') }}" alt="TEVARA Logo" class="h-9 w-auto max-w-[170px] object-contain transition-transform group-hover:scale-105">
            <span class="hidden sm:inline-flex items-center text-[10px] font-extrabold uppercase tracking-widest text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-1 rounded-full border border-[#0B3B82]/15">
                Teaching Factory
            </span>
        </a>

        {{-- Desktop Navigation Links --}}
        <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
            <a href="#tentang" class="hover:text-[#0B3B82] transition-colors">Tentang TeFa</a>
            <a href="#alur-kerja" class="hover:text-[#0B3B82] transition-colors">Alur Proyek</a>
            <a href="#peran" class="hover:text-[#0B3B82] transition-colors">Ekosistem</a>
            <a href="#simulasi" class="hover:text-[#0B3B82] transition-colors flex items-center gap-1.5">
                <span>Simulasi</span>
                <span class="text-[9px] font-bold bg-[#FFB800] text-[#1E293B] px-1.5 py-0.5 rounded-md">Interaktif</span>
            </a>
            <a href="#faq" class="hover:text-[#0B3B82] transition-colors">FAQ</a>
        </nav>

        {{-- Auth Actions --}}
        <div class="hidden md:flex items-center gap-3">
            @if ($isLoggedIn)
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-[#0B3B82] px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-[#0B3B82]/20 hover:bg-[#092f6a] hover:shadow-lg transition-all">
                    <span>Dashboard</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center px-4 py-2 text-xs font-bold text-slate-600 hover:text-[#0B3B82] transition-colors">
                    Masuk
                </a>
                <a href="{{ route('klien.ajukan-proyek') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-[#0B3B82] px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-[#0B3B82]/20 hover:bg-[#092f6a] hover:shadow-lg transition-all">
                    <span>Ajukan Proyek</span>
                    <svg class="w-3.5 h-3.5 text-[#FFB800]" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            @endif
        </div>

        {{-- Mobile Hamburger --}}
        <button @click="mobileOpen = !mobileOpen"
                class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors"
                aria-label="Toggle menu">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Mobile Menu Dropdown --}}
    <div x-show="mobileOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden bg-white border-b border-slate-200 px-6 py-5 space-y-4 shadow-xl">
        <a @click="mobileOpen = false" href="#tentang" class="block text-sm font-semibold text-slate-600 hover:text-[#0B3B82]">Tentang TeFa</a>
        <a @click="mobileOpen = false" href="#alur-kerja" class="block text-sm font-semibold text-slate-600 hover:text-[#0B3B82]">Alur Proyek</a>
        <a @click="mobileOpen = false" href="#peran" class="block text-sm font-semibold text-slate-600 hover:text-[#0B3B82]">Ekosistem</a>
        <a @click="mobileOpen = false" href="#simulasi" class="block text-sm font-semibold text-slate-600 hover:text-[#0B3B82]">Simulasi Proyek</a>
        <a @click="mobileOpen = false" href="#faq" class="block text-sm font-semibold text-slate-600 hover:text-[#0B3B82]">FAQ</a>

        <div class="pt-4 border-t border-slate-100 flex flex-col gap-2">
            @if ($isLoggedIn)
                <a href="{{ route('dashboard') }}"
                   class="w-full text-center rounded-xl bg-[#0B3B82] py-2.5 text-xs font-bold text-white shadow-sm">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="w-full text-center rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-700">
                    Masuk
                </a>
                <a href="{{ route('klien.ajukan-proyek') }}"
                   class="w-full text-center rounded-xl bg-[#0B3B82] py-2.5 text-xs font-bold text-white shadow-sm">
                    Ajukan Proyek
                </a>
            @endif
        </div>
    </div>
</header>

{{-- Hero Section --}}
<section class="relative overflow-hidden pt-8 pb-20 lg:pt-14 lg:pb-28">
    {{-- Decorative Background Glows --}}
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-[#EAF3FF] rounded-full blur-3xl opacity-80 pointer-events-none -z-10"></div>
    <div class="absolute top-1/2 -right-40 w-[30rem] h-[30rem] bg-[#FFF6E0] rounded-full blur-3xl opacity-70 pointer-events-none -z-10"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            {{-- Left: Headline & Actions --}}
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 rounded-full bg-[#EAF3FF] border border-[#0B3B82]/20 px-4 py-1.5 text-xs font-bold text-[#0B3B82]">
                    <span class="w-2 h-2 rounded-full bg-[#FFB800] animate-pulse"></span>
                    <span>Politeknik Negeri Semarang · Knowledge into Action</span>
                </div>

                {{-- Headline --}}
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-[#1E293B] tracking-tight leading-[1.12]">
                    Solusi Digital Nyata dari <span class="bg-gradient-to-r from-[#0B3B82] via-[#1E4E9C] to-[#2563EB] bg-clip-text text-transparent">Kolaborasi Industri</span> & <span class="relative inline-block text-[#0B3B82]">Talenta Polines<span class="absolute bottom-1 left-0 w-full h-3 bg-[#FFB800]/30 -z-10 rounded"></span></span>
                </h1>

                {{-- Subtitle --}}
                <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    TEVARA menjembatani kebutuhan proyek riil mitra industri dengan tim pengerjaan profesional yang dipimpin Dosen Project Manager serta mahasiswa vokasi terbaik.
                </p>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-2">
                    <a href="{{ $isLoggedIn ? route('dashboard') : route('klien.ajukan-proyek') }}"
                       class="inline-flex items-center gap-2.5 rounded-xl bg-[#0B3B82] px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-[#0B3B82]/25 hover:bg-[#092f6a] hover:shadow-xl hover:-translate-y-0.5 transition-all">
                        <span>{{ $isLoggedIn ? 'Buka Dashboard Anda' : 'Ajukan Proyek Sekarang' }}</span>
                        <svg class="w-4 h-4 text-[#FFB800]" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>

                    <a href="#alur-kerja"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition-all shadow-xs">
                        <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15.91 11.672-6.284-3.928A.75.75 0 0 0 8.5 8.384v7.232a.75.75 0 0 0 1.126.64l6.284-3.928a.75.75 0 0 0 0-1.28Z"/>
                        </svg>
                        <span>Lihat Alur Kerja</span>
                    </a>
                </div>

                {{-- Trust Metrics --}}
                <div class="pt-6 border-t border-slate-200/80 grid grid-cols-3 gap-4 max-w-md mx-auto lg:mx-0">
                    <div>
                        <p class="text-2xl font-extrabold text-[#0B3B82]">100%</p>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Proyek Industri Riil</p>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-[#0B3B82]">4+</p>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Bidang Teknologi</p>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-[#FFB800]">Multi-Role</p>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Ekosistem Terintegrasi</p>
                    </div>
                </div>
            </div>

            {{-- Right: Interactive Role Showcase Card (Alpine.js) --}}
            <div class="lg:col-span-5"
                 x-data="{
                    activeRole: 'klien',
                    roles: {
                        klien: {
                            name: 'Mitra Industri / Klien',
                            badge: 'Pemilik Kebutuhan',
                            badgeColor: 'bg-blue-100 text-[#0B3B82]',
                            title: 'Pengajuan Proposal Cepat & Transparan',
                            desc: 'Klien mendaftarkan kebutuhan modul, deadline, dan anggaran secara mandiri. Monitor progres setiap modul tanpa perantara berbelit.',
                            highlight: 'Pantau status proposal dari Submitted, Validasi TeFa, hingga Approved.'
                        },
                        pm: {
                            name: 'Dosen Project Manager',
                            badge: 'Lead & Supervisor',
                            badgeColor: 'bg-amber-100 text-amber-800',
                            title: 'Manajemen Task & Bimbingan Teknis',
                            desc: 'Dosen memimpin arsitektur sistem, membagi task ke mahasiswa, mengontrol timeline sprint, dan memastikan kualitas deliverables.',
                            highlight: 'Pengawasan langsung memastikan standar industri terpenuhi.'
                        },
                        mahasiswa: {
                            name: 'Mahasiswa Talenta',
                            badge: 'Eksekutor Proyek',
                            badgeColor: 'bg-purple-100 text-purple-800',
                            title: 'Pengalaman Nyata Berbasis Portofolio',
                            desc: 'Mahasiswa mengerjakan modul riil, submit tugas sesuai deadline, berkolaborasi dengan git, dan membangun portofolio bersertifikasi.',
                            highlight: 'Pembelajaran berbasis Teaching Factory yang siap kerja.'
                        },
                        tefa: {
                            name: 'Pengurus TeFa',
                            badge: 'Reviewer & Tata Kelola',
                            badgeColor: 'bg-emerald-100 text-emerald-800',
                            title: 'Validasi Proposal & Alokasi Sumber Daya',
                            desc: 'Pengurus TeFa memvalidasi kelayakan anggaran, menyetujui proposal, dan mendistribusikan proyek ke jurusan terkait.',
                            highlight: 'Penjamin mutu dan tata kelola program Teaching Factory.'
                        }
                    }
                 }">
                <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-7 shadow-xl shadow-slate-900/5 relative">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-400"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                            <span class="ml-2 text-[11px] font-mono font-semibold text-slate-400">tevara.live/preview</span>
                        </div>
                        <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">Live Preview</span>
                    </div>

                    {{-- Role Selector Tabs --}}
                    <div class="grid grid-cols-4 gap-1.5 p-1 bg-slate-100 rounded-xl mt-4">
                        <button type="button"
                                @click="activeRole = 'klien'"
                                :class="activeRole === 'klien' ? 'bg-white text-[#0B3B82] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                class="py-2 text-[11px] font-bold rounded-lg transition-all text-center">
                            Klien
                        </button>
                        <button type="button"
                                @click="activeRole = 'pm'"
                                :class="activeRole === 'pm' ? 'bg-white text-[#0B3B82] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                class="py-2 text-[11px] font-bold rounded-lg transition-all text-center">
                            Dosen PM
                        </button>
                        <button type="button"
                                @click="activeRole = 'mahasiswa'"
                                :class="activeRole === 'mahasiswa' ? 'bg-white text-[#0B3B82] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                class="py-2 text-[11px] font-bold rounded-lg transition-all text-center">
                            Mahasiswa
                        </button>
                        <button type="button"
                                @click="activeRole = 'tefa'"
                                :class="activeRole === 'tefa' ? 'bg-white text-[#0B3B82] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                class="py-2 text-[11px] font-bold rounded-lg transition-all text-center">
                            TeFa
                        </button>
                    </div>

                    {{-- Dynamic Role Card Preview --}}
                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-bold px-3 py-1 rounded-full"
                                  :class="roles[activeRole].badgeColor"
                                  x-text="roles[activeRole].badge"></span>
                            <span class="text-[11px] text-slate-400 font-medium" x-text="roles[activeRole].name"></span>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-[#1E293B]" x-text="roles[activeRole].title"></h3>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed" x-text="roles[activeRole].desc"></p>
                        </div>

                        {{-- Simulated Dashboard Widget --}}
                        <div class="rounded-2xl border border-slate-100 bg-[#F8FAFC] p-4 space-y-3">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="text-slate-500">Highlight Fitur</span>
                                <span class="text-emerald-600 flex items-center gap-1 font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            </div>
                            <p class="text-xs font-medium text-[#0B3B82] bg-white p-3 rounded-xl border border-slate-200/80 leading-relaxed"
                               x-text="roles[activeRole].highlight"></p>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">Klik tab di atas untuk berganti peran</span>
                        <a href="{{ route('login') }}" class="text-xs font-bold text-[#0B3B82] hover:underline flex items-center gap-1">
                            <span>Eksplorasi</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Section: Tentang TeFa Polines --}}
<section id="tentang" class="py-16 bg-white border-y border-slate-200/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <span class="text-xs font-extrabold uppercase tracking-widest text-[#0B3B82] bg-[#EAF3FF] px-3 py-1 rounded-full border border-[#0B3B82]/15">
                Konsep Teaching Factory
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-[#1E293B]">
                Membawa Standar Industri ke Dalam Ekosistem Pendidikan
            </h2>
            <p class="text-sm sm:text-base text-slate-500 leading-relaxed">
                Teaching Factory (TeFa) Polines menyinergikan kurikulum vokasi dengan kebutuhan riil dunia usaha dan dunia industri (DUDI), menghasilkan produk teknologi yang siap pakai.
            </p>
        </div>

        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-[#F8FAFC] rounded-2xl p-7 border border-slate-200/80 hover:border-[#0B3B82]/30 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-xl bg-[#0B3B82] text-white flex items-center justify-center font-bold text-lg mb-5 shadow-sm shadow-[#0B3B82]/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-[#1E293B]">Kecepatan & Fleksibilitas</h3>
                <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                    Alur pengajuan proyek ringkas tanpa birokrasi berbelit. Evaluasi modul dan estimasi timeline ditentukan bersama secara transparan.
                </p>
            </div>

            <div class="bg-[#F8FAFC] rounded-2xl p-7 border border-slate-200/80 hover:border-[#0B3B82]/30 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-xl bg-[#FFB800] text-[#1E293B] flex items-center justify-center font-bold text-lg mb-5 shadow-sm shadow-[#FFB800]/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-[#1E293B]">Kualitas Terjamin Dosen Ahli</h3>
                <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                    Setiap pengerjaan diawasi langsung oleh Dosen Project Manager berkualifikasi industri yang bertanggung jawab atas arsitektur dan kualitas kode.
                </p>
            </div>

            <div class="bg-[#F8FAFC] rounded-2xl p-7 border border-slate-200/80 hover:border-[#0B3B82]/30 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-xl bg-purple-700 text-white flex items-center justify-center font-bold text-lg mb-5 shadow-sm shadow-purple-600/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-[#1E293B]">Efisiensi Biaya Nyata</h3>
                <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                    Model kemitraan vokasi yang saling menguntungkan. Industri mendapatkan solusi bernilai tinggi dan mahasiswa mendapatkan portofolio riil.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Section: Interactive Workflow / Alur Kerja (Alpine.js) --}}
<section id="alur-kerja" class="py-20 bg-[#F8FAFC]"
         x-data="{
            activeStep: 1,
            steps: [
                {
                    num: 1,
                    title: 'Pengajuan Proposal Klien',
                    actor: 'Mitra Industri / Klien',
                    desc: 'Klien mengisi formulir pengajuan berisi latar belakang, target pengguna, modul fitur, batas deadline, serta estimasi anggaran.',
                    deliverable: 'Draft Proposal & Daftar Modul Proyek',
                    iconBg: 'bg-blue-50 text-[#0B3B82]'
                },
                {
                    num: 2,
                    title: 'Validasi & Penilaian TeFa',
                    actor: 'Pengurus TeFa Polines',
                    desc: 'Tim pengurus meninjau kelayakan teknis, relevansi kurikulum, dan kesesuaian anggaran sebelum disetujui atau dimintakan revisi.',
                    deliverable: 'Status Validasi: Disetujui / Butuh Revisi',
                    iconBg: 'bg-amber-50 text-amber-700'
                },
                {
                    num: 3,
                    title: 'Penunjukan Dosen & Tim Mahasiswa',
                    actor: 'Dosen PM & Mahasiswa',
                    desc: 'Dosen PM ditugaskan untuk membentuk tim mahasiswa bertalenta sesuai spesialisasi teknologi yang dibutuhkan proyek.',
                    deliverable: 'Susunan Anggota Tim & Timeline Sprint',
                    iconBg: 'bg-indigo-50 text-indigo-700'
                },
                {
                    num: 4,
                    title: 'Pengerjaan Modul & Sprint',
                    actor: 'Seluruh Tim Proyek',
                    desc: 'Eksekusi pengerjaan berbasis modul dengan update progress task berkala, code review mingguan, dan demo milestone.',
                    deliverable: 'Source Code, Task Completed & Progress %',
                    iconBg: 'bg-emerald-50 text-emerald-700'
                },
                {
                    num: 5,
                    title: 'Uji Fungsi & Serah Terima',
                    actor: 'Klien, PM & TeFa',
                    desc: 'Uji penerimaan pengguna (UAT), serah terima hak cipta dan dokumentasi, serta evaluasi kepuasan mitra industri.',
                    deliverable: 'Berita Acara Serah Terima & Invoice Selesai',
                    iconBg: 'bg-slate-100 text-slate-700'
                }
            ]
         }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <span class="text-xs font-extrabold uppercase tracking-widest text-[#0B3B82] bg-[#EAF3FF] px-3 py-1 rounded-full border border-[#0B3B82]/15">
                Alur Pengerjaan
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-[#1E293B]">
                5 Tahap Menuju Keberhasilan Proyek Anda
            </h2>
            <p class="text-sm sm:text-base text-slate-500 leading-relaxed">
                Klik tahapan di bawah untuk melihat rincian proses, peran yang terlibat, dan output yang dihasilkan di setiap langkah.
            </p>
        </div>

        {{-- Steps Navigation Bar --}}
        <div class="mt-12 grid grid-cols-2 sm:grid-cols-5 gap-3">
            <template x-for="s in steps" :key="s.num">
                <button type="button"
                        @click="activeStep = s.num"
                        :class="activeStep === s.num
                            ? 'bg-[#0B3B82] text-white shadow-lg shadow-[#0B3B82]/20 border-[#0B3B82]'
                            : 'bg-white text-slate-600 border-slate-200/80 hover:bg-slate-50 hover:border-slate-300'"
                        class="p-4 rounded-2xl border text-left transition-all cursor-pointer flex flex-col justify-between min-h-[100px]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold font-mono"
                              :class="activeStep === s.num ? 'text-[#FFB800]' : 'text-slate-400'"
                              x-text="'0' + s.num"></span>
                        <span x-show="activeStep === s.num" class="w-2 h-2 rounded-full bg-[#FFB800]"></span>
                    </div>
                    <p class="text-xs font-bold leading-snug mt-2" x-text="s.title"></p>
                </button>
            </template>
        </div>

        {{-- Active Step Detail Showcase Card --}}
        <div class="mt-8 bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-8 space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-[#EAF3FF] text-[#0B3B82]"
                              x-text="'Tahap ' + activeStep + ' dari 5'"></span>
                        <span class="text-xs font-semibold text-slate-500"
                              x-text="'Pelaku: ' + steps[activeStep - 1].actor"></span>
                    </div>

                    <h3 class="text-2xl font-bold text-[#1E293B]" x-text="steps[activeStep - 1].title"></h3>
                    <p class="text-sm text-slate-600 leading-relaxed max-w-2xl" x-text="steps[activeStep - 1].desc"></p>

                    <div class="pt-4 border-t border-slate-100 flex items-center gap-3">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Output / Deliverable:</span>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200"
                              x-text="steps[activeStep - 1].deliverable"></span>
                    </div>
                </div>

                <div class="lg:col-span-4 flex flex-col gap-3">
                    <div class="bg-[#F8FAFC] p-5 rounded-2xl border border-slate-100 space-y-2">
                        <p class="text-xs font-bold text-[#0B3B82] uppercase tracking-wider">Transparansi Penuh</p>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Setiap fase terekam di sistem TEVARA sehingga Anda selalu tahu siapa yang sedang bekerja dan apa progressnya.
                        </p>
                    </div>

                    <a href="{{ route('klien.ajukan-proyek') }}"
                       class="w-full text-center rounded-xl bg-[#0B3B82] py-3 text-xs font-bold text-white shadow-sm hover:bg-[#092f6a] transition-all">
                        Mulai dari Tahap 1 Sekarang →
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Section: Interactive Scope / Category Calculator (Interaktif & Ga Ngebosenin) --}}
<section id="simulasi" class="py-20 bg-white border-y border-slate-200/70"
         x-data="{
            category: 'web',
            scale: 'medium',
            specs: {
                web: {
                    name: 'Aplikasi Web & SaaS',
                    icon: '🌐',
                    desc: 'Portal sistem internal, dashboard analytics, e-commerce, atau platform layanan berbasis web responsif.',
                    small: { duration: '1 - 2 Bulan', team: '1 PM + 2 Developer', estBudget: 'Rp 15.000.000 - Rp 25.000.000' },
                    medium: { duration: '2 - 4 Bulan', team: '1 PM + 3 Developer + 1 UI/UX', estBudget: 'Rp 30.000.000 - Rp 50.000.000' },
                    large: { duration: '4 - 6 Bulan', team: '1 PM + 4-5 Dev + 1 QA', estBudget: 'Rp 60.000.000+' }
                },
                mobile: {
                    name: 'Aplikasi Mobile (Android/iOS)',
                    icon: '📱',
                    desc: 'Aplikasi native atau cross-platform dengan antarmuka modern, push notification, dan integrasi backend API.',
                    small: { duration: '1.5 - 2 Bulan', team: '1 PM + 2 Mobile Dev', estBudget: 'Rp 20.000.000 - Rp 35.000.000' },
                    medium: { duration: '3 - 4 Bulan', team: '1 PM + 3 Dev + 1 UI/UX', estBudget: 'Rp 40.000.000 - Rp 65.000.000' },
                    large: { duration: '5 - 6 Bulan', team: '1 PM + 4 Mobile Dev + 1 QA', estBudget: 'Rp 75.000.000+' }
                },
                iot: {
                    name: 'IoT & Perangkat Keras Cerdas',
                    icon: '⚡',
                    desc: 'Sistem sensor, otomasi pabrik, telemetry tracking, dan integrasi hardware dengan dashboard monitoring real-time.',
                    small: { duration: '2 - 3 Bulan', team: '1 PM + 2 Hardware/Firmware', estBudget: 'Rp 25.000.000 - Rp 40.000.000' },
                    medium: { duration: '3 - 5 Bulan', team: '1 PM + 3 IoT + 1 Cloud Dev', estBudget: 'Rp 45.000.000 - Rp 70.000.000' },
                    large: { duration: '5 - 7 Bulan', team: '1 PM + 4 Tim Gabungan Hardware/Cloud', estBudget: 'Rp 80.000.000+' }
                },
                desktop: {
                    name: 'Aplikasi Desktop & Sistem Internal',
                    icon: '🖥️',
                    desc: 'Software offline-first, kontrol inventaris kasir (POS), atau utilitas komputasi khusus untuk lingkungan industri.',
                    small: { duration: '1 - 2 Bulan', team: '1 PM + 2 Dev', estBudget: 'Rp 15.000.000 - Rp 30.000.000' },
                    medium: { duration: '2 - 4 Bulan', team: '1 PM + 3 Dev', estBudget: 'Rp 35.000.000 - Rp 55.000.000' },
                    large: { duration: '4 - 6 Bulan', team: '1 PM + 4 Dev', estBudget: 'Rp 60.000.000+' }
                }
            }
         }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <span class="text-xs font-extrabold uppercase tracking-widest text-[#FFB800] bg-[#FFF9ED] px-3 py-1 rounded-full border border-[#FFB800]/30">
                Kalkulator Interaktif
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-[#1E293B]">
                Simulasikan Kebutuhan Proyek Anda
            </h2>
            <p class="text-sm sm:text-base text-slate-500 leading-relaxed">
                Pilih kategori teknologi dan skala kebutuhan untuk melihat gambaran komposisi tim serta estimasi durasi pengerjaan di TeFa Polines.
            </p>
        </div>

        <div class="mt-12 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Controls --}}
            <div class="lg:col-span-7 space-y-6">
                {{-- Category selection --}}
                <div class="space-y-3">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-400">1. Pilih Kategori Proyek</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button"
                                @click="category = 'web'"
                                :class="category === 'web' ? 'border-[#0B3B82] bg-[#EAF3FF] text-[#0B3B82] ring-1 ring-[#0B3B82]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                                class="p-4 rounded-2xl border text-left transition-all flex items-center gap-3">
                            <span class="text-2xl">🌐</span>
                            <div>
                                <p class="text-xs font-bold">Web & SaaS</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Portal & Dashboard</p>
                            </div>
                        </button>

                        <button type="button"
                                @click="category = 'mobile'"
                                :class="category === 'mobile' ? 'border-[#0B3B82] bg-[#EAF3FF] text-[#0B3B82] ring-1 ring-[#0B3B82]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                                class="p-4 rounded-2xl border text-left transition-all flex items-center gap-3">
                            <span class="text-2xl">📱</span>
                            <div>
                                <p class="text-xs font-bold">Mobile App</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Android & iOS</p>
                            </div>
                        </button>

                        <button type="button"
                                @click="category = 'iot'"
                                :class="category === 'iot' ? 'border-[#0B3B82] bg-[#EAF3FF] text-[#0B3B82] ring-1 ring-[#0B3B82]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                                class="p-4 rounded-2xl border text-left transition-all flex items-center gap-3">
                            <span class="text-2xl">⚡</span>
                            <div>
                                <p class="text-xs font-bold">IoT & Hardware</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Sensor & Otomasi</p>
                            </div>
                        </button>

                        <button type="button"
                                @click="category = 'desktop'"
                                :class="category === 'desktop' ? 'border-[#0B3B82] bg-[#EAF3FF] text-[#0B3B82] ring-1 ring-[#0B3B82]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                                class="p-4 rounded-2xl border text-left transition-all flex items-center gap-3">
                            <span class="text-2xl">🖥️</span>
                            <div>
                                <p class="text-xs font-bold">Desktop App</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Offline-first POS</p>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- Scale selection --}}
                <div class="space-y-3">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-400">2. Pilih Skala Kebutuhan</label>
                    <div class="grid grid-cols-3 gap-3">
                        <button type="button"
                                @click="scale = 'small'"
                                :class="scale === 'small' ? 'border-[#FFB800] bg-[#FFF9ED] text-[#1E293B] ring-1 ring-[#FFB800]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                                class="p-3.5 rounded-xl border text-center transition-all">
                            <p class="text-xs font-bold">MVP / Ringan</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">1-3 Modul Utama</p>
                        </button>

                        <button type="button"
                                @click="scale = 'medium'"
                                :class="scale === 'medium' ? 'border-[#FFB800] bg-[#FFF9ED] text-[#1E293B] ring-1 ring-[#FFB800]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                                class="p-3.5 rounded-xl border text-center transition-all">
                            <p class="text-xs font-bold">Standar Industri</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">4-7 Modul Lengkap</p>
                        </button>

                        <button type="button"
                                @click="scale = 'large'"
                                :class="scale === 'large' ? 'border-[#FFB800] bg-[#FFF9ED] text-[#1E293B] ring-1 ring-[#FFB800]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                                class="p-3.5 rounded-xl border text-center transition-all">
                            <p class="text-xs font-bold">Kompleks / Custom</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">8+ Modul Integrasi</p>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Simulation Result Card --}}
            <div class="lg:col-span-5 bg-gradient-to-br from-[#0B3B82] to-[#142B57] text-white p-7 rounded-3xl shadow-xl relative overflow-hidden">
                <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-[#FFB800]/20 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 space-y-6">
                    <div class="flex items-center justify-between border-b border-white/15 pb-4">
                        <span class="text-xs uppercase font-extrabold tracking-wider text-[#FFB800]">Hasil Simulasi</span>
                        <span class="text-2xl" x-text="specs[category].icon"></span>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold" x-text="specs[category].name"></h3>
                        <p class="text-xs text-white/70 mt-1 leading-relaxed" x-text="specs[category].desc"></p>
                    </div>

                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between text-xs bg-white/10 p-3 rounded-xl backdrop-blur-xs">
                            <span class="text-white/70">Estimasi Durasi</span>
                            <span class="font-bold text-white" x-text="specs[category][scale].duration"></span>
                        </div>

                        <div class="flex items-center justify-between text-xs bg-white/10 p-3 rounded-xl backdrop-blur-xs">
                            <span class="text-white/70">Struktur Tim</span>
                            <span class="font-bold text-white text-right" x-text="specs[category][scale].team"></span>
                        </div>

                        <div class="flex items-center justify-between text-xs bg-white/10 p-3 rounded-xl backdrop-blur-xs">
                            <span class="text-white/70">Kisaran Nilai TeFa</span>
                            <span class="font-bold text-[#FFB800] text-right" x-text="specs[category][scale].estBudget"></span>
                        </div>
                    </div>

                    <a href="{{ route('klien.ajukan-proyek') }}"
                       class="block w-full text-center rounded-xl bg-[#FFB800] py-3 text-xs font-bold text-[#1E293B] shadow-md hover:bg-[#e6a600] transition-all">
                        Ajukan Proposal Kategori Ini →
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Section: Ekosistem Multi-Peran --}}
<section id="peran" class="py-20 bg-[#F8FAFC]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <span class="text-xs font-extrabold uppercase tracking-widest text-[#0B3B82] bg-[#EAF3FF] px-3 py-1 rounded-full border border-[#0B3B82]/15">
                Kolaborasi 4 Peran
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-[#1E293B]">
                Siapa Saja yang Berkolaborasi di TEVARA?
            </h2>
            <p class="text-sm sm:text-base text-slate-500 leading-relaxed">
                Setiap peran memiliki dashboard spesifik yang terhubung langsung ke backend untuk menjamin akuntabilitas dan efisiensi.
            </p>
        </div>

        <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Card 1: Klien --}}
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all">
                <span class="inline-block text-xs font-bold px-3 py-1 rounded-lg bg-blue-50 text-[#0B3B82] mb-4">Mitra Klien</span>
                <h3 class="text-base font-bold text-[#1E293B]">Industri & Start-up</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Mengajukan kebutuhan, memantau modul dan timeline, mengunduh invoice tagihan, dan memberikan umpan balik evaluasi.
                </p>
            </div>

            {{-- Card 2: Dosen PM --}}
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all">
                <span class="inline-block text-xs font-bold px-3 py-1 rounded-lg bg-amber-50 text-amber-700 mb-4">Dosen PM</span>
                <h3 class="text-base font-bold text-[#1E293B]">Project Manager</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Mengatur tim pengerjaan, memecah backlog modul menjadi task terukur, memantau sprint, dan memastikan kualitas output.
                </p>
            </div>

            {{-- Card 3: Mahasiswa --}}
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all">
                <span class="inline-block text-xs font-bold px-3 py-1 rounded-lg bg-purple-50 text-purple-700 mb-4">Mahasiswa</span>
                <h3 class="text-base font-bold text-[#1E293B]">Talenta Vokasi</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Mengerjakan task riil, mengunggah commit/deliverable, mencatat log aktivitas pengerjaan, dan mengasah portofolio kerja.
                </p>
            </div>

            {{-- Card 4: TeFa & Jurusan --}}
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all">
                <span class="inline-block text-xs font-bold px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 mb-4">Pengurus & Jurusan</span>
                <h3 class="text-base font-bold text-[#1E293B]">Tata Kelola & Validasi</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Memvalidasi kelayakan anggaran proposal, memantau statistik proyek aktif se-Polines, dan menerbitkan laporan capaian.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Section: FAQ Accordion (Alpine.js) --}}
<section id="faq" class="py-20 bg-white border-t border-slate-200/70"
         x-data="{
            openFaq: null,
            toggle(i) {
                this.openFaq = (this.openFaq === i) ? null : i;
            }
         }">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center space-y-3">
            <span class="text-xs font-extrabold uppercase tracking-widest text-[#0B3B82] bg-[#EAF3FF] px-3 py-1 rounded-full border border-[#0B3B82]/15">
                Pertanyaan Umum
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-[#1E293B]">
                Frequently Asked Questions
            </h2>
            <p class="text-sm text-slate-500">
                Segala hal yang sering ditanyakan mengenai platform dan kemitraan Teaching Factory Polines.
            </p>
        </div>

        <div class="mt-10 space-y-3">
            {{-- FAQ 1 --}}
            <div class="rounded-2xl border border-slate-200 overflow-hidden transition-all">
                <button type="button"
                        @click="toggle(1)"
                        class="w-full px-6 py-4.5 text-left flex items-center justify-between font-bold text-sm text-[#1E293B] hover:bg-slate-50 cursor-pointer">
                    <span>Bagaimana cara mitra industri mengajukan proyek ke TEVARA?</span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                         :class="openFaq === 1 ? 'rotate-180 text-[#0B3B82]' : ''"
                         fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
                <div x-show="openFaq === 1" x-cloak class="px-6 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    Mitra dapat mendaftar atau login sebagai klien, lalu mengisi formulir <span class="font-semibold text-[#0B3B82]">Ajukan Proyek</span> yang mencakup judul, latar belakang, modul kebutuhan, deadline target, dan estimasi anggaran. Proposal akan langsung masuk ke antrean validasi pengurus TeFa.
                </div>
            </div>

            {{-- FAQ 2 --}}
            <div class="rounded-2xl border border-slate-200 overflow-hidden transition-all">
                <button type="button"
                        @click="toggle(2)"
                        class="w-full px-6 py-4.5 text-left flex items-center justify-between font-bold text-sm text-[#1E293B] hover:bg-slate-50 cursor-pointer">
                    <span>Apakah kerahasiaan data dan source code proyek terlindungi (NDA)?</span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                         :class="openFaq === 2 ? 'rotate-180 text-[#0B3B82]' : ''"
                         fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
                <div x-show="openFaq === 2" x-cloak class="px-6 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    Ya. Setiap kerja sama didukung oleh Memorandum of Understanding (MoU) dan Non-Disclosure Agreement (NDA) resmi institusi Politeknik Negeri Semarang untuk melindungi hak kekayaan intelektual (HAKI) dan data rahasia industri.
                </div>
            </div>

            {{-- FAQ 3 --}}
            <div class="rounded-2xl border border-slate-200 overflow-hidden transition-all">
                <button type="button"
                        @click="toggle(3)"
                        class="w-full px-6 py-4.5 text-left flex items-center justify-between font-bold text-sm text-[#1E293B] hover:bg-slate-50 cursor-pointer">
                    <span>Siapa yang mengawasi kualitas hasil pengerjaan mahasiswa?</span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                         :class="openFaq === 3 ? 'rotate-180 text-[#0B3B82]' : ''"
                         fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
                <div x-show="openFaq === 3" x-cloak class="px-6 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    Setiap tim dipimpin oleh Dosen Project Manager yang bertindak sebagai lead architect dan quality control. Dosen memeriksa modul pengerjaan, mengontrol standarisasi pengkodean, dan memverifikasi fungsionalitas sebelum diserahkan ke klien.
                </div>
            </div>

            {{-- FAQ 4 --}}
            <div class="rounded-2xl border border-slate-200 overflow-hidden transition-all">
                <button type="button"
                        @click="toggle(4)"
                        class="w-full px-6 py-4.5 text-left flex items-center justify-between font-bold text-sm text-[#1E293B] hover:bg-slate-50 cursor-pointer">
                    <span>Bagaimana mekanisme validasi proposal oleh Pengurus TeFa?</span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                         :class="openFaq === 4 ? 'rotate-180 text-[#0B3B82]' : ''"
                         fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
                <div x-show="openFaq === 4" x-cloak class="px-6 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                    Pengurus TeFa memeriksa rincian proposal pada menu Validasi Proposal. Mereka dapat meninjau latar belakang, modul, target pengguna, dan anggaran, lalu memberikan keputusan: Setujui, Butuh Revisi (dengan catatan perbaikan), atau Tolak.
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Call to Action Card --}}
<section class="py-16 bg-[#F8FAFC]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-gradient-to-r from-[#0B3B82] via-[#16438D] to-[#0B3B82] text-white p-8 sm:p-14 text-center relative overflow-hidden shadow-2xl">
            <div class="absolute inset-0 bg-[radial-gradient(#FFB800_1px,transparent_1px)] [background-size:20px_20px] opacity-15 pointer-events-none"></div>

            <div class="relative z-10 max-w-2xl mx-auto space-y-6">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-xs font-bold text-[#FFB800] border border-white/15">
                    Mulai Kolaborasi Anda
                </span>

                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-snug">
                    Siap Mengembangkan Proyek Digital Anda Bersama Polines?
                </h2>

                <p class="text-sm text-white/80 leading-relaxed font-normal">
                    Bergabunglah dengan puluhan mitra industri yang telah mempercayakan pengembangan solusi teknologi mereka melalui Teaching Factory TEVARA.
                </p>

                <div class="pt-2 flex flex-wrap items-center justify-center gap-4">
                    <a href="{{ $isLoggedIn ? route('dashboard') : route('klien.ajukan-proyek') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-[#FFB800] px-6 py-3.5 text-xs font-extrabold text-[#1E293B] shadow-lg hover:bg-[#e6a600] transition-all">
                        <span>{{ $isLoggedIn ? 'Buka Dashboard' : 'Ajukan Proyek Sekarang' }}</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>

                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 px-5 py-3.5 text-xs font-bold text-white hover:bg-white/20 transition-all">
                        <span>Masuk ke Akun</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer class="bg-white border-t border-slate-200 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="md:col-span-2 space-y-4">
                <img src="{{ asset('images/tevara-logo.png') }}" alt="TEVARA Logo" class="h-8 w-auto max-w-[170px] object-contain">
                <p class="text-xs text-slate-500 max-w-sm leading-relaxed">
                    TEVARA (Teaching Factory Polines) adalah sistem integrasi proyek industri dan vokasi akademik untuk mencetak produk digital berkualitas dan talenta unggul.
                </p>
                <p class="text-[11px] text-slate-400">
                    Politeknik Negeri Semarang · Jl. Prof. H. Soedarto, S.H., Tembalang, Kota Semarang, Jawa Tengah 50275
                </p>
            </div>

            <div class="space-y-3">
                <p class="text-xs font-bold uppercase tracking-wider text-[#1E293B]">Navigasi Cepat</p>
                <ul class="space-y-2 text-xs text-slate-500">
                    <li><a href="#tentang" class="hover:text-[#0B3B82] transition-colors">Tentang TeFa</a></li>
                    <li><a href="#alur-kerja" class="hover:text-[#0B3B82] transition-colors">Alur Kerja Proyek</a></li>
                    <li><a href="#simulasi" class="hover:text-[#0B3B82] transition-colors">Simulasi Kebutuhan</a></li>
                    <li><a href="#peran" class="hover:text-[#0B3B82] transition-colors">Ekosistem Peran</a></li>
                    <li><a href="#faq" class="hover:text-[#0B3B82] transition-colors">Tanya Jawab (FAQ)</a></li>
                </ul>
            </div>

            <div class="space-y-3">
                <p class="text-xs font-bold uppercase tracking-wider text-[#1E293B]">Akses Layanan</p>
                <ul class="space-y-2 text-xs text-slate-500">
                    <li><a href="{{ route('login') }}" class="hover:text-[#0B3B82] transition-colors">Login Akun</a></li>
                    <li><a href="{{ route('klien.ajukan-proyek') }}" class="hover:text-[#0B3B82] transition-colors">Ajukan Proyek Baru</a></li>
                    <li><a href="{{ route('dashboard') }}" class="hover:text-[#0B3B82] transition-colors">Dashboard Sistem</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-400">
            <p>© {{ date('Y') }} TEVARA · Teaching Factory Politeknik Negeri Semarang. Hak Cipta Dilindungi.</p>
            <p>Knowledge into Action</p>
        </div>
    </div>
</footer>

</body>
</html>
