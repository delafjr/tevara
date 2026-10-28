<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Jurusan - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#F8F9FB] font-['Inter'] text-[#1E293B] antialiased">

@php
    $user = $user ?? session('user');
    $userName = $user['name'];
@endphp

<div class="flex min-h-screen">

    {{-- Reusable Sidebar Component --}}
    <x-sidebar active="dashboard" />

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen p-8 gap-6">

        {{-- HEADER / HERO SECTION --}}
        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
            {{-- gradient dekoratif --}}
            <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                    Workspace Jurusan
                </span>
                <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                    Selamat Datang, <span class="text-[#FFB800]">{{ $userName }}</span>
                </h1>
                <p class="text-sm text-slate-400 mt-1.5">Pantau perkembangan proyek dan kolaborasi bersama TEVARA secara terpadu</p>
            </div>

            <div class="relative z-10 flex flex-col items-end gap-2 flex-shrink-0">
                <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[11.5px] font-semibold px-3 py-1.5 rounded-lg">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    Data terbaru
                </span>
            </div>
        </div>

        {{-- STAT CARDS --}}
        @php
            $jurusanStats = [
                ['label' => 'Total Projek',         'value' => 28,  'sub' => 'Inisiatif Terdaftar', 'color' => '#0B3B82', 'icon' => 'folder'],
                ['label' => 'Projek Berjalan',      'value' => 142, 'sub' => 'Dalam Pengerjaan',    'color' => '#0B3B82', 'icon' => 'play'],
                ['label' => 'Projek Selesai',       'value' => 16,  'sub' => 'Siap Produksi',       'color' => '#0B3B82', 'icon' => 'check'],
                ['label' => 'Menunggu Persetujuan', 'value' => 7,   'sub' => 'Perlu Review',        'color' => '#F59E0B', 'icon' => 'clock-file'],
            ];
        @endphp

        <div class="grid grid-cols-4 gap-4">
            @foreach($jurusanStats as $stat)
                <x-stat-card :label="$stat['label']" :value="$stat['value']" :sub="$stat['sub']" :color="$stat['color']" :icon="$stat['icon']" />
            @endforeach
        </div>

        {{-- ACTIONS BAR --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button class="bg-[#0B3B82] hover:bg-[#082a5c] text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Ajukan Projek Baru
                </button>
                <button class="bg-white hover:bg-slate-50 text-[#1E293B] border border-slate-200 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    Lihat Semua Projek
                </button>
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
                    <a href="#" class="text-[#0B3B82] text-sm font-semibold hover:underline flex items-center gap-1">
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
                            {{-- Row 1 --}}
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
                            {{-- Row 2 --}}
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
                            {{-- Row 3 --}}
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
                            {{-- Row 4 --}}
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

    </div>
</div>

</body>
</html>