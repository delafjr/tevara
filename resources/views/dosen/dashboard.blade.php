<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen">

@php
    $user = $user ?? session('user');
    $userName = $user['name'] ?? 'Dosen';
@endphp

<div class="flex min-h-screen">

    {{-- Reusable Sidebar Component --}}
    <x-sidebar active="dashboard" />

    {{-- ===== MAIN CONTENT ===== --}}
    <main class="flex-1 ml-64 p-8 min-h-screen flex flex-col gap-6">

        {{-- ── 1. GREETING BANNER ── --}}
        <div class="relative overflow-hidden bg-white rounded-2xl border border-slate-100 shadow-2xs px-8 py-7 flex items-center justify-between">
            <!-- Subtle soft warm background tint on the top-right -->
            <div class="absolute -top-12 -right-12 w-80 h-48 bg-gradient-to-bl from-amber-50/50 via-amber-50/20 to-transparent pointer-events-none rounded-full blur-2xl"></div>

            <div class="relative z-10">
                <h1 class="text-2xl font-bold text-[#0B254A] tracking-tight">
                    Selamat <span class="text-[#F59E0B]">Datang</span> , {{ $userName }}
                </h1>
                <p class="text-xs text-slate-400 mt-2 font-normal">
                    Pantau progres mahasiswa, review laporan sprint, dan pastikan milestone tercapai tepat waktu
                </p>
            </div>

            <div class="relative z-10 flex flex-col items-end gap-1.5 flex-shrink-0">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-white border border-slate-200/80 rounded-xl text-xs font-medium text-slate-600 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="18" x="3" y="4" rx="2"/>
                        <path d="M16 2v4"/>
                        <path d="M8 2v4"/>
                        <path d="M3 10h18"/>
                    </svg>
                    <span>Rabu, 30 September 2026</span>
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#10B981] inline-block"></span>
                    <span>Data terbaru ter-sinkron</span>
                </div>
            </div>
        </div>

        {{-- ── 2. 4 STAT CARDS ── --}}
        <div class="grid grid-cols-4 gap-5">

            <!-- Card 1: Total Project -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-[#EBF3FE] flex items-center justify-center text-[#2563EB]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-[#DCFCE7] text-[#16A34A] text-xs font-semibold">
                        <span>↑</span> +3
                    </span>
                </div>
                <p class="text-3xl font-bold text-[#0B254A] mt-4 leading-none">15</p>
                <p class="text-xs font-bold text-[#1E293B] mt-2">Total Project</p>
                <p class="text-[11px] text-slate-400 mt-1">+3 dari bulan lalu</p>
            </div>

            <!-- Card 2: Project Berjalan -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-[#FEF3C7] flex items-center justify-center text-[#F59E0B]">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <path d="M8 5.14v14l11-7-11-7z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-[#DCFCE7] text-[#16A34A] text-xs font-semibold">
                        <span>↑</span> +2
                    </span>
                </div>
                <p class="text-3xl font-bold text-[#0B254A] mt-4 leading-none">9</p>
                <p class="text-xs font-bold text-[#1E293B] mt-2">Project Berjalan</p>
                <p class="text-[11px] text-slate-400 mt-1">+2 dari bulan lalu</p>
            </div>

            <!-- Card 3: Project Selesai -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-[#DCFCE7] flex items-center justify-center text-[#16A34A]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-[#DCFCE7] text-[#16A34A] text-xs font-semibold">
                        <span>↑</span> +1
                    </span>
                </div>
                <p class="text-3xl font-bold text-[#0B254A] mt-4 leading-none">4</p>
                <p class="text-xs font-bold text-[#1E293B] mt-2">Project Selesai</p>
                <p class="text-[11px] text-slate-400 mt-1">+1 dari bulan lalu</p>
            </div>

            <!-- Card 4: Menunggu Review -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-[#FEE2E2] flex items-center justify-center text-[#EF4444]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-[#FEE2E2] text-[#EF4444] text-xs font-semibold">
                        <span>↓</span> -1
                    </span>
                </div>
                <p class="text-3xl font-bold text-[#0B254A] mt-4 leading-none">3</p>
                <p class="text-xs font-bold text-[#1E293B] mt-2">Menunggu Review</p>
                <p class="text-[11px] text-slate-400 mt-1">-1 dari bulan lalu</p>
            </div>

        </div>

        {{-- ── 3. BOTTOM SECTION: Table + Right Column ── --}}
        <div class="flex flex-col lg:flex-row gap-6 items-start">

            {{-- LEFT: Project yang Saya Kelola --}}
            <div class="flex-1 min-w-0 bg-white rounded-2xl border border-slate-100 shadow-2xs p-6">
                <div class="flex items-center justify-between pb-4">
                    <div>
                        <h2 class="font-bold text-[#1E293B] text-[15px]">Project yang Saya Kelola</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Semua project yang ditugaskan ke Anda</p>
                    </div>
                    <a href="#" class="text-xs font-semibold text-[#2563EB] hover:text-[#1D4ED8] inline-flex items-center gap-1 transition-colors">
                        Lihat Semua
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                </div>

                <div class="w-full">
                    {{-- Table Header --}}
                    <div class="bg-[#F8FAFC] rounded-xl px-5 py-3 grid grid-cols-12 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        <div class="col-span-5">NAMA PROJECT</div>
                        <div class="col-span-3">KLIEN / MITRA</div>
                        <div class="col-span-2">STATUS</div>
                        <div class="col-span-2">PROGRESS</div>
                    </div>

                    {{-- Table Rows --}}
                    <div class="divide-y divide-slate-100/80">

                        <!-- Row 1 -->
                        <div class="px-5 py-4 grid grid-cols-12 items-center hover:bg-slate-50/50 transition-colors">
                            <div class="col-span-5 pr-4">
                                <p class="font-bold text-[#1E293B] text-[13px] leading-tight">Sistem Informasi UMKM</p>
                                <p class="text-[11px] text-slate-400 mt-1 truncate">Pengembangan sistem informasi...</p>
                            </div>
                            <div class="col-span-3">
                                <span class="text-[12px] font-medium text-slate-600">PT. ABC Indonesia</span>
                            </div>
                            <div class="col-span-2">
                                <span class="bg-[#EFF6FF] text-[#2563EB] text-[11px] font-semibold px-3 py-1 rounded-full inline-block">
                                    Berjalan
                                </span>
                            </div>
                            <div class="col-span-2 flex items-center gap-3">
                                <div class="w-20 h-2 bg-[#EFF6FF] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#0B3B82] rounded-full" style="width: 75%"></div>
                                </div>
                                <span class="text-xs font-medium text-slate-500">75%</span>
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="px-5 py-4 grid grid-cols-12 items-center hover:bg-slate-50/50 transition-colors">
                            <div class="col-span-5 pr-4">
                                <p class="font-bold text-[#1E293B] text-[13px] leading-tight">Smart Farming IoT</p>
                                <p class="text-[11px] text-slate-400 mt-1 truncate">Implementasi IoT untuk pertanian...</p>
                            </div>
                            <div class="col-span-3">
                                <span class="text-[12px] font-medium text-slate-600">CV. Tani Maju</span>
                            </div>
                            <div class="col-span-2">
                                <span class="bg-[#FEF3C7] text-[#D97706] text-[11px] font-semibold px-3 py-1 rounded-full inline-block">
                                    Review
                                </span>
                            </div>
                            <div class="col-span-2 flex items-center gap-3">
                                <div class="w-20 h-2 bg-[#EFF6FF] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#D97706] rounded-full" style="width: 60%"></div>
                                </div>
                                <span class="text-xs font-medium text-slate-500">60%</span>
                            </div>
                        </div>

                        <!-- Row 3 -->
                        <div class="px-5 py-4 grid grid-cols-12 items-center hover:bg-slate-50/50 transition-colors">
                            <div class="col-span-5 pr-4">
                                <p class="font-bold text-[#1E293B] text-[13px] leading-tight">Website Company Profile</p>
                                <p class="text-[11px] text-slate-400 mt-1 truncate">Pembuatan website profil...</p>
                            </div>
                            <div class="col-span-3">
                                <span class="text-[12px] font-medium text-slate-600">PT. DEF Solusi</span>
                            </div>
                            <div class="col-span-2">
                                <span class="bg-[#DCFCE7] text-[#16A34A] text-[11px] font-semibold px-3 py-1 rounded-full inline-block">
                                    Selesai
                                </span>
                            </div>
                            <div class="col-span-2 flex items-center gap-3">
                                <div class="w-20 h-2 bg-[#EFF6FF] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#16A34A] rounded-full" style="width: 100%"></div>
                                </div>
                                <span class="text-xs font-medium text-slate-500">100%</span>
                            </div>
                        </div>

                        <!-- Row 4 -->
                        <div class="px-5 py-4 grid grid-cols-12 items-center hover:bg-slate-50/50 transition-colors">
                            <div class="col-span-5 pr-4">
                                <p class="font-bold text-[#1E293B] text-[13px] leading-tight">Aplikasi Manajemen Inventori</p>
                                <p class="text-[11px] text-slate-400 mt-1 truncate">Sistem inventori berbasis web...</p>
                            </div>
                            <div class="col-span-3">
                                <span class="text-[12px] font-medium text-slate-600">Dinas Pendidikan</span>
                            </div>
                            <div class="col-span-2">
                                <span class="bg-[#EFF6FF] text-[#2563EB] text-[11px] font-semibold px-3 py-1 rounded-full inline-block">
                                    Berjalan
                                </span>
                            </div>
                            <div class="col-span-2 flex items-center gap-3">
                                <div class="w-20 h-2 bg-[#EFF6FF] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#0B3B82] rounded-full" style="width: 40%"></div>
                                </div>
                                <span class="text-xs font-medium text-slate-500">40%</span>
                            </div>
                        </div>

                        <!-- Row 5 -->
                        <div class="px-5 py-4 grid grid-cols-12 items-center hover:bg-slate-50/50 transition-colors">
                            <div class="col-span-5 pr-4">
                                <p class="font-bold text-[#1E293B] text-[13px] leading-tight">IoT Monitoring Pertanian</p>
                                <p class="text-[11px] text-slate-400 mt-1 truncate">Monitoring lingkungan pertanian...</p>
                            </div>
                            <div class="col-span-3">
                                <span class="text-[12px] font-medium text-slate-600">CV. Tani Maju</span>
                            </div>
                            <div class="col-span-2">
                                <span class="bg-[#FFE4E6] text-[#E11D48] text-[11px] font-semibold px-3 py-1 rounded-full inline-block">
                                    Proposal
                                </span>
                            </div>
                            <div class="col-span-2 flex items-center gap-3">
                                <div class="w-20 h-2 bg-[#EFF6FF] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#E11D48] rounded-full" style="width: 20%"></div>
                                </div>
                                <span class="text-xs font-medium text-slate-500">20%</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- RIGHT: Distribusi Kategori & Status Project --}}
            <div class="w-full lg:w-80 xl:w-[320px] flex-shrink-0 flex flex-col gap-6">

                {{-- Card 1: Distribusi Kategori --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-6">
                    <div>
                        <h2 class="font-bold text-[#1E293B] text-[15px]">Distribusi Kategori</h2>
                        <p class="text-xs text-[#2563EB] font-medium mt-0.5">Klasifikasi Seluruh Portofolio</p>
                    </div>

                    {{-- Donut Chart SVG --}}
                    <div class="flex items-center justify-center my-5">
                        <div class="relative w-36 h-36">
                            <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                                <!-- Background circle -->
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#F8FAFC" stroke-width="14" />
                                
                                <!-- Website: 40% (100.53) -->
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#0B3B82" stroke-width="14"
                                    stroke-dasharray="100.53 150.8" stroke-dashoffset="0" />
                                
                                <!-- IoT & Embedded: 26.67% (67.02) -->
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#F59E0B" stroke-width="14"
                                    stroke-dasharray="67.02 184.3" stroke-dashoffset="-100.53" />
                                
                                <!-- Aplikasi: 20% (50.27) -->
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#2563EB" stroke-width="14"
                                    stroke-dasharray="50.27 201.06" stroke-dashoffset="-167.55" />
                                
                                <!-- Pengembangan Sis.: 13.33% (33.51) -->
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#94A3B8" stroke-width="14"
                                    stroke-dasharray="33.51 217.82" stroke-dashoffset="-217.82" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-bold text-[#0B254A] leading-none">15</span>
                                <span class="text-[9px] font-bold text-slate-400 tracking-wider mt-1">PROJECT</span>
                            </div>
                        </div>
                    </div>

                    {{-- Legend List --}}
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-[#0B3B82] flex-shrink-0"></span>
                                <span class="text-slate-600 font-medium">Website</span>
                            </div>
                            <span class="font-bold text-[#1E293B]">6</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-[#F59E0B] flex-shrink-0"></span>
                                <span class="text-slate-600 font-medium">IoT & Embedded</span>
                            </div>
                            <span class="font-bold text-[#1E293B]">4</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-[#2563EB] flex-shrink-0"></span>
                                <span class="text-slate-600 font-medium">Aplikasi</span>
                            </div>
                            <span class="font-bold text-[#1E293B]">3</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-[#94A3B8] flex-shrink-0"></span>
                                <span class="text-slate-600 font-medium">Pengembangan Sis.</span>
                            </div>
                            <span class="font-bold text-[#1E293B]">2</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-[#CBD5E1] flex-shrink-0"></span>
                                <span class="text-slate-600 font-medium">Lainnya</span>
                            </div>
                            <span class="font-bold text-[#1E293B]">0</span>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Status Project --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-6">
                    <div class="mb-4">
                        <h2 class="font-bold text-[#1E293B] text-[15px]">Status Project</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Distribusi berdasarkan status</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Berjalan -->
                        <div>
                            <div class="flex items-center justify-between text-xs mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#0B3B82] flex-shrink-0"></span>
                                    <span class="font-semibold text-[#1E293B]">Berjalan</span>
                                </div>
                                <span class="font-bold text-[#1E293B]">9</span>
                            </div>
                            <div class="h-2 bg-[#EFF6FF] rounded-full overflow-hidden">
                                <div class="h-full bg-[#0B3B82] rounded-full" style="width: 65%"></div>
                            </div>
                        </div>

                        <!-- Selesai -->
                        <div>
                            <div class="flex items-center justify-between text-xs mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#16A34A] flex-shrink-0"></span>
                                    <span class="font-semibold text-[#1E293B]">Selesai</span>
                                </div>
                                <span class="font-bold text-[#1E293B]">4</span>
                            </div>
                            <div class="h-2 bg-[#EFF6FF] rounded-full overflow-hidden">
                                <div class="h-full bg-[#16A34A] rounded-full" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </main>

</div>

</body>
</html>