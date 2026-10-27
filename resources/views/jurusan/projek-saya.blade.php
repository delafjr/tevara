<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Projek Saya - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#F8F9FB] font-['Inter'] text-[#1E293B] antialiased">

@php
    $user = $user ?? session('user');
    $userName = $user['name'];
@endphp

<div class="flex min-h-screen">

    {{-- Pemanggilan Sidebar (otomatis menyorot menu Projek Saya) --}}
    <x-sidebar active="projek" />

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen p-8 gap-6">

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

    </div>
</div>

</body>
</html>