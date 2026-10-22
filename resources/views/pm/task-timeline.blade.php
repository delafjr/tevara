<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task & Timeline - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen">

@php
    $user     = $user ?? session('user');
    $userName = $user['name'] ?? 'Project Manager';
    $hasProject = true;
@endphp

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <x-sidebar active="task-timeline" />

    {{-- ===== MAIN CONTENT ===== --}}
    <main class="flex-1 ml-64 p-8 min-h-screen flex flex-col gap-5"
          x-data="{
            reviewOpen: true,
            reviewTask: 'Integrasi Payment Gateway QRIS Midtrans',
            revisiNote: ''
          }">

        {{-- ── 1. PAGE HEADER ── --}}
        <div class="relative overflow-hidden bg-white rounded-2xl border border-slate-100 shadow-2xs px-8 py-6">
            <div class="absolute -top-10 -right-10 w-72 h-44 bg-gradient-to-bl from-amber-50/60 via-blue-50/20 to-transparent pointer-events-none rounded-full blur-2xl"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Monitoring Penugasan &nbsp;·&nbsp; Semester Ganjil 2026/2027</p>
                <h1 class="text-2xl font-extrabold text-[#0B254A] tracking-tight leading-none">
                    Task &amp; <span class="text-[#F59E0B]">Timeline</span>
                </h1>
                <p class="text-xs text-slate-400 mt-2 font-normal max-w-lg leading-relaxed">
                    Pantau kemajuan pengerjaan tiket, review hasil pekerjaan mahasiswa, dan koordinasi deadline proyek.
                </p>
            </div>
        </div>

        @if(!$hasProject)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs px-8 py-14 text-center">
                <h2 class="text-lg font-bold text-[#0B254A]">Belum ada proyek terdaftar</h2>
                <p class="text-sm text-slate-500 mt-2">Task dan timeline akan muncul setelah proyek ditugaskan kepada Anda.</p>
            </div>
        @else
        {{-- ── 2. PROYEK AKTIF BANNER ── --}}
        <div class="bg-[#0B254A] rounded-2xl px-6 py-4 flex items-center gap-4">
            {{-- Ikon --}}
            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                </svg>
            </div>
            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <span class="inline-block px-2 py-0.5 rounded-full bg-[#F59E0B] text-[#0B254A] text-[9px] font-extrabold tracking-wider uppercase mb-1.5">Proyek Aktif</span>
                <p class="text-white font-extrabold text-sm leading-tight">PRJ-2026/001 &bull; Sistem Informasi Pengelolaan UMKM</p>
                <div class="flex items-center gap-4 mt-1.5 text-[11px] text-blue-200 flex-wrap">
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                        </svg>
                        Mitra: PT. ABC Indonesia
                    </span>
                    <span>·</span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0"/>
                        </svg>
                        Tim: 4 Mahasiswa
                    </span>
                    <span>·</span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>
                        </svg>
                        Batas Akhir: 15 Nov 2026
                    </span>
                </div>
            </div>
            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <button class="inline-flex items-center gap-1.5 bg-[#F59E0B] hover:bg-yellow-400 text-[#0B254A] text-xs font-extrabold px-4 py-2 rounded-xl transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Tambah Task Proyek
                </button>
                <button class="inline-flex items-center gap-1.5 border border-white/30 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                    </svg>
                    Ganti Proyek
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── 3. STAT CARDS ── --}}
        <div class="grid grid-cols-4 gap-4">

            {{-- Total Task --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Total Task</p>
                    <div class="w-8 h-8 rounded-lg bg-[#EBF3FE] flex items-center justify-center text-[#2563EB]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-[#0B254A] leading-none">18</p>
            </div>

            {{-- Perlu Review --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Perlu Review</p>
                    <div class="w-8 h-8 rounded-lg bg-[#FEF3C7] flex items-center justify-center text-[#F59E0B]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-[#0B254A] leading-none">3</p>
                <p class="text-[11px] text-[#F59E0B] font-semibold mt-1">menunggu</p>
            </div>

            {{-- Sedang Berjalan --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Sedang Berjalan</p>
                    <div class="w-8 h-8 rounded-lg bg-[#DBEAFE] flex items-center justify-center text-[#2563EB]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-[#0B254A] leading-none">7</p>
                <p class="text-[11px] text-[#2563EB] font-semibold mt-1">aktif</p>
            </div>

            {{-- Disetujui / Selesai --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Disetujui / Selesai</p>
                    <div class="w-8 h-8 rounded-lg bg-[#DCFCE7] flex items-center justify-center text-[#16A34A]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-[#0B254A] leading-none">8</p>
                <p class="text-[11px] text-slate-400 font-medium mt-1">task</p>
            </div>

        </div>

        {{-- ── 4. KANBAN BOARD ── --}}
        <div class="grid grid-cols-4 gap-4 items-start">

            {{-- COL 1: TODO --}}
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-slate-400 inline-block"></span>
                    <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wide">Todo</span>
                    <span class="ml-auto text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">3</span>
                </div>

                {{-- Task 1 --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-2xs p-4 hover:shadow-sm transition-shadow cursor-pointer">
                    <p class="text-[13px] font-bold text-[#0B254A] leading-tight mb-3">Konfigurasi Mail Server Notifikasi Transaksi...</p>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mb-3">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>
                        </svg>
                        18 Okt
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-[#B45309] text-white flex items-center justify-center text-[9px] font-bold">KS</div>
                            <span class="text-[11px] text-slate-500 font-medium">Kevin S.</span>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">0% Mulai</span>
                    </div>
                </div>

                {{-- Task 2 --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-2xs p-4 hover:shadow-sm transition-shadow cursor-pointer">
                    <p class="text-[13px] font-bold text-[#0B254A] leading-tight mb-3">Dokumentasi API Swagger Endpoint Transaksi</p>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mb-3">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>
                        </svg>
                        22 Okt
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-[#0B254A] text-white flex items-center justify-center text-[9px] font-bold">AR</div>
                            <span class="text-[11px] text-slate-500 font-medium">Ahmad R.</span>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">0% Mulai</span>
                    </div>
                </div>

                {{-- Task 3 --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-2xs p-4 hover:shadow-sm transition-shadow cursor-pointer">
                    <p class="text-[13px] font-bold text-[#0B254A] leading-tight mb-3">Setup CI/CD Pipeline & Automated Testing Staging</p>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mb-3">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>
                        </svg>
                        25 Okt
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-[#15803D] text-white flex items-center justify-center text-[9px] font-bold">DP</div>
                            <span class="text-[11px] text-slate-500 font-medium">Dimas P.</span>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">0% Mulai</span>
                    </div>
                </div>
            </div>

            {{-- COL 2: IN PROGRESS --}}
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#2563EB] inline-block"></span>
                    <span class="text-xs font-extrabold text-[#2563EB] uppercase tracking-wide">In Progress</span>
                    <span class="ml-auto text-[10px] font-bold text-[#2563EB] bg-[#DBEAFE] px-2 py-0.5 rounded-full">5</span>
                </div>

                {{-- Task 1 --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-2xs p-4 hover:shadow-sm transition-shadow cursor-pointer">
                    <p class="text-[13px] font-bold text-[#0B254A] leading-tight mb-2">Perbaikan Sinkronisasi Stok Realtime</p>
                    <div class="flex items-center gap-1.5 text-[10px] text-[#EF4444] font-semibold mb-3">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        10 Okt
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] text-slate-400">Progress</span>
                        <span class="text-[10px] font-bold text-[#2563EB]">65%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mb-3">
                        <div class="h-full bg-[#2563EB] rounded-full" style="width:65%"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-[#15803D] text-white flex items-center justify-center text-[9px] font-bold">DP</div>
                        <span class="text-[11px] text-slate-500 font-medium">Dimas P.</span>
                    </div>
                </div>

                {{-- Task 2 --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-2xs p-4 hover:shadow-sm transition-shadow cursor-pointer">
                    <p class="text-[13px] font-bold text-[#0B254A] leading-tight mb-2">Komponen Filter Kategori Mobile View</p>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mb-3">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>
                        </svg>
                        12 Okt
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] text-slate-400">Progress Sprint</span>
                        <span class="text-[10px] font-bold text-[#F59E0B]">40%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mb-3">
                        <div class="h-full bg-[#F59E0B] rounded-full" style="width:40%"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-[#6D28D9] text-white flex items-center justify-center text-[9px] font-bold">SN</div>
                        <span class="text-[11px] text-slate-500 font-medium">Siti N.</span>
                    </div>
                </div>

                {{-- Task 3 --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-2xs p-4 hover:shadow-sm transition-shadow cursor-pointer">
                    <p class="text-[13px] font-bold text-[#0B254A] leading-tight mb-2">Refactoring Middleware Autentikasi JWT</p>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mb-3">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>
                        </svg>
                        14 Okt
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] text-slate-400">Progress Sprint</span>
                        <span class="text-[10px] font-bold text-[#2563EB]">80%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mb-3">
                        <div class="h-full bg-[#2563EB] rounded-full" style="width:80%"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-[#0B254A] text-white flex items-center justify-center text-[9px] font-bold">AR</div>
                        <span class="text-[11px] text-slate-500 font-medium">Ahmad R.</span>
                    </div>
                </div>

                {{-- Task 4 --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-2xs p-4 hover:shadow-sm transition-shadow cursor-pointer">
                    <p class="text-[13px] font-bold text-[#0B254A] leading-tight mb-2">Integrasi Driver Sensor DHT22 &amp; ESP32</p>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mb-3">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>
                        </svg>
                        16 Okt
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] text-slate-400">Progress Sprint</span>
                        <span class="text-[10px] font-bold text-[#2563EB]">55%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mb-3">
                        <div class="h-full bg-[#2563EB] rounded-full" style="width:55%"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-[#B45309] text-white flex items-center justify-center text-[9px] font-bold">KS</div>
                        <span class="text-[11px] text-slate-500 font-medium">Kevin S.</span>
                    </div>
                </div>

                {{-- Task 5 --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-2xs p-4 hover:shadow-sm transition-shadow cursor-pointer">
                    <p class="text-[13px] font-bold text-[#0B254A] leading-tight mb-2">Desain Mockup High-Fidelity Modul Checkout</p>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mb-3">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>
                        </svg>
                        17 Okt
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] text-slate-400">Progress Sprint</span>
                        <span class="text-[10px] font-bold text-[#2563EB]">70%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mb-3">
                        <div class="h-full bg-[#2563EB] rounded-full" style="width:70%"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-[#6D28D9] text-white flex items-center justify-center text-[9px] font-bold">SN</div>
                        <span class="text-[11px] text-slate-500 font-medium">Siti N.</span>
                    </div>
                </div>
            </div>

            {{-- COL 3: TESTING & REVIEW --}}
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#F59E0B] inline-block"></span>
                    <span class="text-xs font-extrabold text-[#B45309] uppercase tracking-wide">Testing &amp; Review</span>
                    <span class="ml-auto text-[10px] font-bold text-[#B45309] bg-[#FEF3C7] px-2 py-0.5 rounded-full">2</span>
                </div>

                {{-- Review Card 1 (active — amber border) --}}
                <div
                    @click="reviewOpen = true; reviewTask = 'Integrasi Payment Gateway QRIS Midtrans'"
                    class="rounded-xl border-2 border-[#F59E0B] bg-white shadow-sm p-4 cursor-pointer hover:shadow-md transition-shadow"
                >
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#FEF3C7] text-[#B45309] text-[10px] font-bold">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            Butuh Review
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#0B254A] text-white text-[10px] font-bold">Sedang Direview</span>
                    </div>
                    <p class="text-[13px] font-extrabold text-[#0B254A] leading-tight mb-3">Integrasi Payment Gateway QRIS Midtrans</p>
                    <button
                        type="button"
                        @click.stop="reviewOpen = true; reviewTask = 'Integrasi Payment Gateway QRIS Midtrans'"
                        class="w-full flex items-center justify-center gap-2 bg-[#F59E0B] hover:bg-yellow-400 text-[#0B254A] text-[11px] font-extrabold py-2 rounded-lg transition-colors cursor-pointer mb-3"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                        Meninjau di Panel Bawah
                    </button>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-[#0B254A] text-white flex items-center justify-center text-[9px] font-bold">AR</div>
                            <span class="text-[11px] text-slate-500 font-medium">Ahmad R.</span>
                        </div>
                        <span class="text-[10px] font-bold text-[#16A34A]">100% Selesai</span>
                    </div>
                </div>

                {{-- Review Card 2 --}}
                <div
                    @click="reviewOpen = true; reviewTask = 'Integrasi Payment Gateway QRIS Midtrans'"
                    class="rounded-xl border border-[#FDE68A] bg-[#FFFBEB] p-4 cursor-pointer hover:border-[#F59E0B] hover:shadow-sm transition-all"
                >
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#FEF3C7] text-[#B45309] text-[10px] font-bold">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            Butuh Review
                        </span>
                    </div>
                    <p class="text-[13px] font-extrabold text-[#0B254A] leading-tight mb-3">Integrasi Payment Gateway QRIS Midtrans</p>
                    <button
                        type="button"
                        @click.stop="reviewOpen = true"
                        class="w-full flex items-center justify-center gap-2 bg-[#F59E0B] hover:bg-yellow-400 text-[#0B254A] text-[11px] font-extrabold py-2 rounded-lg transition-colors cursor-pointer mb-3"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                        Meninjau di Panel Bawah
                    </button>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-[#0B254A] text-white flex items-center justify-center text-[9px] font-bold">AR</div>
                            <span class="text-[11px] text-slate-500 font-medium">Ahmad R.</span>
                        </div>
                        <span class="text-[10px] font-bold text-[#16A34A]">100% Selesai</span>
                    </div>
                </div>
            </div>

            {{-- COL 4: DONE --}}
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#16A34A] inline-block"></span>
                    <span class="text-xs font-extrabold text-[#16A34A] uppercase tracking-wide">Done</span>
                    <span class="ml-auto text-[10px] font-bold text-[#16A34A] bg-[#DCFCE7] px-2 py-0.5 rounded-full">8</span>
                </div>

                {{-- Done Card 1 --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-2xs p-4 hover:shadow-sm transition-shadow cursor-pointer">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">Low</span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#16A34A]">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            Approved
                        </span>
                    </div>
                    <p class="text-[13px] font-bold text-[#0B254A] leading-tight mb-3">Skema Basis Data Relasional</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-[#15803D] text-white flex items-center justify-center text-[9px] font-bold">DP</div>
                            <span class="text-[11px] text-slate-500 font-medium">Dimas P.</span>
                        </div>
                        <span class="text-[10px] text-slate-400">28 Sep</span>
                    </div>
                </div>

                {{-- Done Card 2 --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-2xs p-4 hover:shadow-sm transition-shadow cursor-pointer">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">Medium</span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#16A34A]">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            Approved
                        </span>
                    </div>
                    <p class="text-[13px] font-bold text-[#0B254A] leading-tight mb-3">Design System &amp; Token UI</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-[#6D28D9] text-white flex items-center justify-center text-[9px] font-bold">SN</div>
                            <span class="text-[11px] text-slate-500 font-medium">Siti N.</span>
                        </div>
                        <span class="text-[10px] text-slate-400">30 Sep</span>
                    </div>
                </div>

                {{-- Done Card 3 --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-2xs p-4 hover:shadow-sm transition-shadow cursor-pointer">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="text-[10px] font-bold text-[#2563EB] bg-[#DBEAFE] px-2 py-0.5 rounded-full">High</span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#16A34A]">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            Approved
                        </span>
                    </div>
                    <p class="text-[13px] font-bold text-[#0B254A] leading-tight mb-3">Setup Lingkungan Server Docker Compose</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-[#0B254A] text-white flex items-center justify-center text-[9px] font-bold">AR</div>
                            <span class="text-[11px] text-slate-500 font-medium">Ahmad R.</span>
                        </div>
                        <span class="text-[10px] text-slate-400">03 Okt</span>
                    </div>
                </div>

                {{-- Done Card 4 --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-2xs p-4 hover:shadow-sm transition-shadow cursor-pointer">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">Medium</span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#16A34A]">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            Approved
                        </span>
                    </div>
                    <p class="text-[13px] font-bold text-[#0B254A] leading-tight mb-3">Autentikasi OAuth2 &amp; Session Multi-Device</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-[#15803D] text-white flex items-center justify-center text-[9px] font-bold">DP</div>
                            <span class="text-[11px] text-slate-500 font-medium">Dimas P.</span>
                        </div>
                        <span class="text-[10px] text-slate-400">07 Okt</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── 5. REVIEW PANEL ── --}}
        <div
            x-show="reviewOpen"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
        >
            {{-- Panel Header --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1.5">
                        <svg class="w-4 h-4 text-[#2563EB] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                        </svg>
                        <p class="text-sm font-extrabold text-[#0B254A]" x-text="reviewTask">Integrasi Payment Gateway QRIS Midtrans</p>
                    </div>
                    <div class="flex items-center gap-4 text-[11px] text-slate-400 flex-wrap">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                            Mahasiswa: <strong class="text-slate-600">Ahmad R. (Fullstack Lead)</strong>
                        </span>
                        <span>·</span>
                        <span class="flex items-center gap-1 text-[#2563EB] font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75H6A2.25 2.25 0 0 0 3.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0 1 20.25 6v1.5m0 9V18A2.25 2.25 0 0 1 18 20.25h-1.5m-9 0H6A2.25 2.25 0 0 1 3.75 18v-1.5M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            PR #42: Feat Midtrans QRIS API &amp; Webhook
                        </span>
                        <span>·</span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            Diserahkan: Hari ini, 10:15 WIB
                        </span>
                    </div>
                </div>
                <button
                    type="button"
                    @click="reviewOpen = false"
                    class="inline-flex items-center gap-1.5 border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 text-[11px] font-semibold px-3 py-1.5 rounded-lg transition-colors cursor-pointer flex-shrink-0"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/>
                    </svg>
                    Sembunyikan Panel
                </button>
            </div>

            {{-- Panel Body: 2 columns --}}
            <div class="grid grid-cols-2 divide-x divide-slate-100">

                {{-- LEFT: Ringkasan Hasil --}}
                <div class="px-6 py-5">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                        </svg>
                        <p class="text-[13px] font-extrabold text-[#0B254A]">Ringkasan Hasil Pekerjaan Mahasiswa</p>
                    </div>

                    {{-- Kutipan --}}
                    <blockquote class="text-xs text-slate-600 leading-relaxed bg-[#F8FAFC] border border-slate-100 rounded-xl p-4 italic mb-4">
                        "Integrasi API Midtrans sandbox sudah lolos testing simulasi pembayaran QRIS dinamis &amp; static. Callback webhook sudah dilengkapi verifikasi tanda tangan HMAC SHA512 signature. Response time transaksi &lt; 250ms dan unit test coverage mencapai 100% pass."
                    </blockquote>

                    {{-- Checklist --}}
                    <div class="space-y-2 mb-4">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-[#16A34A] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            <p class="text-xs text-slate-600">Endpoint webhook: <code class="text-[#2563EB] bg-blue-50 px-1 rounded text-[10px]">/api/v1/payment/midtrans-callback</code></p>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-[#16A34A] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            <p class="text-xs text-slate-600">Simulasi status pembayaran: Success, Pending, Cancel, Expire teruj otomatis</p>
                        </div>
                    </div>

                    {{-- Links --}}
                    <div class="flex flex-col gap-2">
                        <a href="#" class="inline-flex items-center gap-2 text-[11px] text-[#2563EB] font-semibold hover:underline">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/>
                            </svg>
                            Lihat Full Request #42
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                            </svg>
                        </a>
                        <a href="#" class="inline-flex items-center gap-2 text-[11px] text-[#2563EB] font-semibold hover:underline">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                            </svg>
                            Dokumentasi API Swagger
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- RIGHT: Form Evaluasi PM --}}
                <div class="px-6 py-5">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                        </svg>
                        <p class="text-[13px] font-extrabold text-[#0B254A]">Form Evaluasi Project Manager</p>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-[#0B254A]">Catatan Review &amp; Arahan PM:</label>
                            <span class="text-[10px] text-slate-400">Maks. 500 karakter</span>
                        </div>
                        <textarea
                            x-model="revisiNote"
                            rows="7"
                            placeholder="Implementasi webhook dan verifikasi signature sudah rapi dan sesuai security guideline. Endpoint sandbox siap diujike staging sebelum integrasi dashboard admin UMKM."
                            class="w-full text-xs text-slate-600 bg-[#F8FAFC] border border-slate-200 rounded-xl p-3.5 focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]/20 resize-none placeholder:text-slate-300 leading-relaxed transition"
                        ></textarea>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col gap-2.5">
                        <button
                            type="button"
                            class="w-full flex items-center justify-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-semibold py-2.5 rounded-xl transition-colors cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                            </svg>
                            Minta Revisi
                        </button>
                        <button
                            type="button"
                            class="w-full flex items-center justify-center gap-2 bg-[#0B254A] hover:bg-[#0d2f5e] text-white text-xs font-extrabold py-2.5 rounded-xl transition-colors cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            Setujui &amp; Tandai Selesai (Approve)
                        </button>
                    </div>
                </div>

            </div>
        </div>
        @endif

    </main>

</div>

</body>
</html>
