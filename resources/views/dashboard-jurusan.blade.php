<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Workspace Jurusan - TEVARA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#F8F9FB] font-['Inter'] text-[#1E293B] antialiased">

@php
    $user = $user ?? session('user');
    $userName = $user['name'] ?? 'Kuwat Santoso, S.T., M.Kom.';
    
    // Menentukan Halaman Aktif (Default: dashboard)
    $activePage = request()->get('page', 'dashboard');
@endphp

<div class="flex min-h-screen">

    {{-- ===== SIDEBAR LANGSUNG DI DALAM FILE ===== --}}
    <aside class="w-64 bg-white border-r border-slate-100 flex flex-col justify-between fixed h-full z-30">
        <div>
            <!-- Logo Brand TEVARA -->
            <x-sidebar :active="$activePage" :role="$currentRole" />

            <!-- Menu Navigasi Samping -->
            <nav class="p-4 space-y-1.5">
                <a href="{{ url()->current() }}?page=dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all {{ $activePage === 'dashboard' ? 'bg-blue-50 text-[#0B3B82]' : 'text-slate-600 hover:bg-slate-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>

                <a href="{{ url()->current() }}?page=ajukan" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all {{ $activePage === 'ajukan' ? 'bg-blue-50 text-[#0B3B82]' : 'text-slate-600 hover:bg-slate-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Ajukan Projek
                </a>

                <a href="{{ url()->current() }}?page=projek" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all {{ $activePage === 'projek' ? 'bg-blue-50 text-[#0B3B82]' : 'text-slate-600 hover:bg-slate-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                    Projek Saya
                </a>

                <a href="{{ url()->current() }}?page=tagihan" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all {{ $activePage === 'tagihan' ? 'bg-blue-50 text-[#0B3B82]' : 'text-slate-600 hover:bg-slate-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Tagihan & Pembayaran
                </a>
            </nav>
        </div>

        <!-- Profil Pengguna Samping -->
        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 shadow-sm cursor-pointer hover:bg-slate-100 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#0B3B82] text-white flex items-center justify-center font-bold text-xs tracking-wider">
                        KU
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-[13px] font-bold text-[#1E293B] truncate w-32">{{ $userName }}</p>
                        <span class="text-[10px] text-slate-500 font-medium">Jurusan</span>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen p-8 gap-6">

        {{-- ========================================================= --}}
        {{-- 1. HALAMAN DASHBOARD                                     --}}
        {{-- ========================================================= --}}
        @if($activePage === 'dashboard')
            {{-- HEADER / HERO SECTION --}}
            <div class="bg-gradient-to-r from-white to-[#FDF9F1] rounded-2xl p-8 shadow-sm border border-slate-100 flex justify-between items-start relative overflow-hidden">
                <div class="relative z-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50/80 text-[#0B3B82] text-xs font-semibold tracking-wide mb-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        WORKSPACE JURUSAN
                    </div>
                    <h1 class="text-[28px] font-bold text-[#1E293B] leading-tight mb-2">
                        Selamat Datang, <span class="text-[#F59E0B]">{{ explode(',', $userName)[0] }}</span>
                    </h1>
                    <p class="text-slate-500 text-sm">
                        Pantau perkembangan proyek dan kolaborasi bersama TEVARA secara terpadu
                    </p>
                </div>
                
                <div class="flex flex-col items-end gap-3 relative z-10">
                    <div class="flex items-center gap-2 bg-white/60 backdrop-blur-sm px-4 py-2 rounded-xl text-sm font-medium text-slate-700 shadow-sm border border-slate-100">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Rabu, 30 September 2026
                    </div>
                    <div class="flex items-center gap-1.5 bg-blue-50 text-[#0B3B82] px-3 py-1.5 rounded-lg text-xs font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Data terbaru
                    </div>
                </div>
            </div>

            {{-- STAT CARDS --}}
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div class="flex items-start justify-between mb-4">
                        <p class="text-xs font-semibold text-slate-500 tracking-wide">TOTAL PROJEK</p>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-[#0B3B82]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h2 class="text-4xl font-bold text-[#1E293B]">28</h2>
                        <p class="text-xs text-slate-500 font-medium">Inisiatif Terdaftar</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div class="flex items-start justify-between mb-4">
                        <p class="text-xs font-semibold text-slate-500 tracking-wide">PROJEK BERJALAN</p>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-[#0B3B82]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h2 class="text-4xl font-bold text-[#0B3B82]">142</h2>
                        <p class="text-xs text-slate-500 font-medium">Dalam Pengerjaan</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div class="flex items-start justify-between mb-4">
                        <p class="text-xs font-semibold text-slate-500 tracking-wide">PROJEK SELESAI</p>
                        <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h2 class="text-4xl font-bold text-[#1E293B]">16</h2>
                        <p class="text-xs text-slate-500 font-medium">Siap Produksi</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div class="flex items-start justify-between mb-4">
                        <p class="text-xs font-semibold text-slate-500 tracking-wide">MENUNGGU PERSETUJUAN</p>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h2 class="text-4xl font-bold text-[#F59E0B]">7</h2>
                        <p class="text-xs text-amber-600 font-medium">Perlu Review</p>
                    </div>
                </div>
            </div>

            {{-- ACTIONS BAR --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="?page=ajukan" class="bg-[#0B3B82] hover:bg-[#082a5c] text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Ajukan Projek Baru
                    </a>
                    <a href="?page=projek" class="bg-white hover:bg-slate-50 text-[#1E293B] border border-slate-200 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        Lihat Semua Projek
                    </a>
                </div>
                <div class="relative w-72">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" class="w-full bg-white border border-slate-200 text-sm rounded-xl focus:ring-[#0B3B82] focus:border-[#0B3B82] block pl-10 p-2.5 py-2 shadow-sm" placeholder="Cari nama projek">
                </div>
            </div>

            {{-- BOTTOM SECTION (Table & Chart) --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-8">
                
                {{-- Table Projek Saya --}}
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-[#1E293B]">Projek Saya</h3>
                            <p class="text-sm text-slate-500 mt-1">Semua project yang ditugaskan ke Anda</p>
                        </div>
                        <a href="?page=projek" class="text-[#0B3B82] text-sm font-semibold hover:underline flex items-center gap-1">
                            Lihat Semua 
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 font-semibold bg-slate-50/50 uppercase tracking-wider">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Nama Project</th>
                                    <th scope="col" class="px-6 py-4">Klien / Mitra</th>
                                    <th scope="col" class="px-6 py-4">Status</th>
                                    <th scope="col" class="px-6 py-4">Progress</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-4 font-semibold text-[#1E293B]">Sistem Informasi UMKM</td>
                                    <td class="px-6 py-4 text-slate-500">PT. ABC Indonesia</td>
                                    <td class="px-6 py-4">
                                        <span class="bg-blue-50 text-[#0B3B82] text-xs font-semibold px-3 py-1 rounded-full">Berjalan</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-full bg-slate-100 rounded-full h-1.5 flex-1">
                                                <div class="bg-[#0B3B82] h-1.5 rounded-full" style="width: 75%"></div>
                                            </div>
                                            <span class="text-sm font-medium text-slate-600 w-8">75%</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-4 font-semibold text-[#1E293B]">Smart Farming IoT</td>
                                    <td class="px-6 py-4 text-slate-500">CV. Tani Maju</td>
                                    <td class="px-6 py-4">
                                        <span class="bg-amber-50 text-amber-600 text-xs font-semibold px-3 py-1 rounded-full">Review</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-full bg-slate-100 rounded-full h-1.5 flex-1">
                                                <div class="bg-amber-500 h-1.5 rounded-full" style="width: 45%"></div>
                                            </div>
                                            <span class="text-sm font-medium text-slate-600 w-8">45%</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-4 font-semibold text-[#1E293B]">Website Company Profile</td>
                                    <td class="px-6 py-4 text-slate-500">PT. DEF Solusi</td>
                                    <td class="px-6 py-4">
                                        <span class="bg-green-50 text-green-600 text-xs font-semibold px-3 py-1 rounded-full">Selesai</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-full bg-slate-100 rounded-full h-1.5 flex-1">
                                                <div class="bg-green-500 h-1.5 rounded-full" style="width: 100%"></div>
                                            </div>
                                            <span class="text-sm font-medium text-slate-600 w-8">100%</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-4 font-semibold text-[#1E293B]">Aplikasi Manajemen Inventori</td>
                                    <td class="px-6 py-4 text-slate-500">PT. Logistik Nusantara</td>
                                    <td class="px-6 py-4">
                                        <span class="bg-blue-50 text-[#0B3B82] text-xs font-semibold px-3 py-1 rounded-full">Berjalan</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-full bg-slate-100 rounded-full h-1.5 flex-1">
                                                <div class="bg-[#0B3B82] h-1.5 rounded-full" style="width: 30%"></div>
                                            </div>
                                            <span class="text-sm font-medium text-slate-600 w-8">30%</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-slate-100 flex items-center justify-between text-sm text-slate-500">
                        <p>Menampilkan 1-4 dari 10 proyek</p>
                        <div class="flex items-center gap-1">
                            <button class="p-1 hover:bg-slate-100 rounded text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>
                            <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-blue-50 text-[#0B3B82] font-semibold">1</button>
                            <button class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-50 text-slate-600">2</button>
                            <button class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-50 text-slate-600">3</button>
                            <button class="p-1 hover:bg-slate-100 rounded text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>
                        </div>
                    </div>
                </div>

                {{-- Chart Kategori --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col">
                    <div class="flex items-start justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-bold text-[#1E293B]">Distribusi Kategori</h3>
                            <p class="text-sm text-slate-500 mt-1">Klasifikasi seluruh portofolio</p>
                        </div>
                        <button class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                        </button>
                    </div>
                    
                    <div class="flex-1 flex flex-col items-center justify-center">
                        {{-- CSS Donut Chart representation --}}
                        <div class="relative w-48 h-48 rounded-full flex items-center justify-center" style="background: conic-gradient(#0B3B82 0% 40%, #F59E0B 40% 66%, #cbd5e1 66% 80%, #3b82f6 80% 100%);">
                            <div class="absolute w-32 h-32 bg-white rounded-full flex flex-col items-center justify-center">
                                <span class="text-3xl font-bold text-[#1E293B]">15</span>
                                <span class="text-[10px] font-semibold tracking-widest text-slate-500 uppercase mt-1">Project</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-y-4 gap-x-2 mt-8 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#0B3B82]"></span>
                            <span class="text-slate-600">Website <span class="font-semibold text-[#1E293B]">(6)</span></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#F59E0B]"></span>
                            <span class="text-slate-600">IoT & Emb. <span class="font-semibold text-[#1E293B]">(4)</span></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                            <span class="text-slate-600">Aplikasi <span class="font-semibold text-[#1E293B]">(3)</span></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-slate-300"></span>
                            <span class="text-slate-600">Sistem <span class="font-semibold text-[#1E293B]">(2)</span></span>
                        </div>
                    </div>
                </div>

            </div>

        {{-- ========================================================= --}}
        {{-- 2. HALAMAN AJUKAN PROJEK                                --}}
        {{-- ========================================================= --}}
        @elseif($activePage === 'ajukan')
            {{-- Banner Header --}}
            <div class="bg-gradient-to-r from-white to-blue-50/30 rounded-2xl p-8 shadow-sm border border-slate-100">
                <h1 class="text-2xl font-bold text-[#1E293B]">Ajukan Projek <span class="text-[#F59E0B]">Baru</span></h1>
                <p class="text-slate-500 text-sm mt-1">Silakan lengkapi spesifikasi proyek Anda di bawah ini. Tim analis dan engineer TEVARA akan meninjau estimasi kebutuhan serta menghubungi Anda dalam waktu 1x24 jam kerja.</p>
            </div>

            <form class="space-y-6">
                <!-- Step 1: Informasi Klien -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-[#0B3B82] text-white flex items-center justify-center text-xs font-bold">1</span>
                        <div>
                            <h2 class="font-bold text-[#1E293B]">Informasi Klien & Instansi</h2>
                            <p class="text-xs text-slate-400">Identitas pemohon dan penanggung jawab proyek</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">NAMA KLIEN / INSTANSI *</label>
                            <input type="text" value="{{ $userName }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:bg-white focus:ring-[#0B3B82]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">NAMA PERUSAHAAN (OPSIONAL)</label>
                            <input type="text" placeholder="Jika instansi/badan usaha" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">KONTAK — NAMA PIC *</label>
                            <input type="text" value="{{ $userName }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">KONTAK — NO. WHATSAPP / HP *</label>
                            <input type="text" value="+62 812-3456-7890" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:bg-white">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Rincian Proyek -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-[#0B3B82] text-white flex items-center justify-center text-xs font-bold">2</span>
                        <div>
                            <h2 class="font-bold text-[#1E293B]">Rincian & Kebutuhan Proyek</h2>
                            <p class="text-xs text-slate-400">Deskripsikan cakupan dan sasaran pengembangan sistem</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">JUDUL PROYEK *</label>
                            <input type="text" placeholder="Contoh: Pengembangan Smart Monitoring Dashboard IoT Terpadu" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">KATEGORI PROYEK *</label>
                            <div class="grid grid-cols-5 gap-3">
                                <button type="button" class="p-3 border-2 border-[#0B3B82] bg-blue-50/50 text-[#0B3B82] rounded-xl font-semibold text-xs flex flex-col items-center gap-1">🌐 Web</button>
                                <button type="button" class="p-3 border border-slate-200 text-slate-600 rounded-xl font-semibold text-xs flex flex-col items-center gap-1 hover:bg-slate-50">📱 Mobile</button>
                                <button type="button" class="p-3 border border-slate-200 text-slate-600 rounded-xl font-semibold text-xs flex flex-col items-center gap-1 hover:bg-slate-50">💻 Desktop</button>
                                <button type="button" class="p-3 border border-slate-200 text-slate-600 rounded-xl font-semibold text-xs flex flex-col items-center gap-1 hover:bg-slate-50">⚙️ IoT</button>
                                <button type="button" class="p-3 border border-slate-200 text-slate-600 rounded-xl font-semibold text-xs flex flex-col items-center gap-1 hover:bg-slate-50">••• Other</button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">LATAR BELAKANG & KEBUTUHAN PROYEK *</label>
                            <textarea rows="4" placeholder="Uraikan problem yang ingin diselesaikan, fitur prioritas, flow bisnis utama, serta integrasi apa saja yang dibutuhkan..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:bg-white"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer Submit -->
                <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-100">
                    <span class="text-xs text-slate-400">🛡️ Data terlindungi dengan enkripsi keamanan standar TEVARA.</span>
                    <button type="button" class="bg-[#0B3B82] text-white px-6 py-3 rounded-xl font-semibold text-sm hover:bg-[#082a5c]">Ajukan Proyek Sekarang →</button>
                </div>
            </form>

        {{-- ========================================================= --}}
        {{-- 3. HALAMAN PROJEK SAYA                                  --}}
        {{-- ========================================================= --}}
        @elseif($activePage === 'projek')
            <div class="space-y-6">
                <div>
                    <h1 class="text-2xl font-bold text-[#1E293B]">Daftar Projek Saya</h1>
                    <p class="text-slate-500 text-sm">Pantau progres eksekusi teknis, evaluasi berkas deliverable, dan spesifikasi pengerjaan proyek Teaching Factory secara terpadu.</p>
                </div>

                {{-- STATS --}}
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl p-6 border border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400">TOTAL PROYEK PORTOFOLIO</p>
                            <h2 class="text-3xl font-bold text-[#1E293B] mt-1">3 Proyek <span class="text-xs bg-blue-50 text-[#0B3B82] px-2 py-0.5 rounded font-medium">Aktif Berjalan</span></h2>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-6 border border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400">RATA-RATA PROGRES</p>
                            <h2 class="text-3xl font-bold text-[#0B3B82] mt-1">68.4%</h2>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-6 border border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400">DISTRIBUSI FASE PENGERJAAN</p>
                            <h2 class="text-3xl font-bold text-[#1E293B] mt-1">3 Milestone</h2>
                        </div>
                    </div>
                </div>

                {{-- ITEM DETAIL CARD --}}
                <div class="bg-white rounded-2xl border border-slate-100 p-6 space-y-6">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold text-[#0B3B82] bg-blue-50 px-2 py-0.5 rounded">PRJ-2026/014</span>
                                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-full">Dalam Pengerjaan</span>
                            </div>
                            <h2 class="text-xl font-bold text-[#1E293B]">Sistem Monitoring Rantai Pasok Terpadu (IoT & Web)</h2>
                            <p class="text-xs text-slate-400">📅 15 Agt 2026 - 25 Nov 2026 · ⌛ Sisa 56 Hari Kalender</p>
                        </div>
                        <button class="text-xs text-slate-500 font-semibold border border-slate-200 px-3 py-1.5 rounded-lg hover:bg-slate-50">Tutup Detail ^</button>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between text-xs font-semibold">
                            <span class="text-slate-500">Progres Eksekusi Keseluruhan</span>
                            <span class="text-[#0B3B82]">72%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-[#0B3B82] h-2 rounded-full" style="width: 72%"></div>
                        </div>
                    </div>

                    {{-- Tim Pengembang --}}
                    <div class="bg-slate-50/50 rounded-xl p-4 border border-slate-100">
                        <p class="text-xs font-bold text-slate-400 tracking-wider mb-3">TIM PENGEMBANG MAHASISWA & DOSEN PEMBIMBING</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white p-3 rounded-xl border border-slate-100 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#0B3B82] text-white flex items-center justify-center font-bold text-xs">HK</div>
                                <div>
                                    <p class="text-xs font-bold text-[#1E293B]">Dr. Hendra Kusuma, M.T.</p>
                                    <p class="text-[10px] text-slate-400">DOSEN PEMBIMBING / PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        {{-- ========================================================= --}}
        {{-- 4. HALAMAN TAGIHAN & PEMBAYARAN                         --}}
        {{-- ========================================================= --}}
        @elseif($activePage === 'tagihan')
            <div class="space-y-6">
                <div>
                    <h1 class="text-2xl font-bold text-[#1E293B]">Tagihan & Pembayaran Proyek</h1>
                    <p class="text-slate-500 text-sm">Pantau status penagihan termin proyek (SPK), riwayat pembayaran terverifikasi, dan unggah bukti transfer termin secara terintegrasi dengan akuntansi Politeknik.</p>
                </div>

                {{-- Alert Banner --}}
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold">🔔</div>
                        <div>
                            <p class="text-xs font-bold text-amber-800">PERLU TINDAKAN KLIEN · Jatuh Tempo: 15 Okt 2026</p>
                            <h3 class="font-bold text-amber-900">Unggah Bukti Pembayaran Termin Berjalan (Termin 2)</h3>
                        </div>
                    </div>
                    <button class="bg-[#0B3B82] text-white px-5 py-2.5 rounded-xl font-semibold text-sm hover:bg-[#082a5c]">Unggah Bukti Bayar Sekarang</button>
                </div>

                {{-- Section Rekening --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 space-y-2">
                        <span class="text-xs font-semibold text-slate-400">BANK MANDIRI (GIRO)</span>
                        <p class="text-lg font-bold text-[#1E293B]">142-00-9928172-1</p>
                        <p class="text-xs text-slate-500">a.n. Teaching Factory Politeknik</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 space-y-2">
                        <span class="text-xs font-semibold text-slate-400">BCA VIRTUAL ACCOUNT</span>
                        <p class="text-lg font-bold text-[#1E293B]">88720-08123456789</p>
                        <p class="text-xs text-slate-500">a.n. TEVARA - {{ $userName }}</p>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

</body>
</html>