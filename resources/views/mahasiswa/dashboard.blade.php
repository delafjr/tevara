@php
    $user = $user ?? session('user', []);
    $userName = ($user['name'] ?? '') === 'Dimas Setiawan' || empty($user['name']) ? 'Alvian Reza' : $user['name'];
    $firstName = explode(' ', $userName)[0] ?? 'Alvian';
@endphp

<x-student-layout active="dashboard" title="Dashboard Mahasiswa">

    {{-- ===== WELCOME HERO BANNER ===== --}}
    <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
        {{-- gradient dekoratif --}}
        <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                Workspace Mahasiswa
            </span>
            <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                Selamat Datang kembali, <span class="text-[#FFB800]">{{ $firstName }}!</span>
            </h1>
            <p class="text-sm text-slate-400 mt-1.5">Terus berkontribusi dan wujudkan solusi nyata bersama Teaching Factory untuk transformasi digital industri.</p>
        </div>

        <div class="relative z-10 flex flex-col items-end gap-2 flex-shrink-0">
            <span class="inline-flex items-center gap-2 bg-white border border-slate-200 shadow-sm text-[12.5px] font-semibold text-[#1E293B] px-3.5 py-2 rounded-lg">
                <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                {{ now()->isoFormat('dddd, D MMMM Y') }}
            </span>
            <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[11.5px] font-semibold px-3 py-1.5 rounded-lg">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Data terbaru
            </span>
        </div>
    </div>

    {{-- ===== 4 STAT CARDS ===== --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" aria-label="Ringkasan aktivitas">
        {{-- Card 1: Project Aktif --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 text-sm font-medium">
                <span>Project Aktif</span>
                <span class="w-9 h-9 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                </span>
            </div>
            <strong class="text-3xl font-extrabold text-[#0F172A] mt-4 tracking-tight">08</strong>
        </div>

        {{-- Card 2: Menunggu Review --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 text-sm font-medium">
                <span>Menunggu Review</span>
                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </span>
            </div>
            <strong class="text-3xl font-extrabold text-[#0F172A] mt-4 tracking-tight">03</strong>
        </div>

        {{-- Card 3: Project Selesai --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 text-sm font-medium">
                <span>Project Selesai</span>
                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <strong class="text-3xl font-extrabold text-[#0F172A] mt-4 tracking-tight">12</strong>
        </div>

        {{-- Card 4: Deadline Terdekat --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 text-sm font-medium">
                <span>Deadline Terdekat</span>
                <span class="w-9 h-9 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
            </div>
            <strong class="text-3xl font-extrabold text-rose-600 mt-4 tracking-tight">2 hari</strong>
        </div>
    </section>

    {{-- ===== PROJECT TERBARU TABLE ===== --}}
    <section class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 py-5 flex items-center justify-between border-b border-slate-100">
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Project Terbaru</h2>
                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">4 Aktif</span>
            </div>
            <a href="{{ route('mahasiswa.project') }}" class="text-sm font-semibold text-[#2563EB] hover:text-blue-700 inline-flex items-center gap-1 group transition">
                <span>Lihat Semua</span>
                <span class="group-hover:translate-x-0.5 transition-transform">→</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-white">
                        <th class="py-3.5 px-6">Nama Project</th>
                        <th class="py-3.5 px-6">Mitra / Klien</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6">Progress</th>
                        <th class="py-3.5 px-6">Deadline</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    {{-- Row 1: Sistem Informasi UMKM --}}
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-4 px-6">
                            <strong class="block text-sm font-bold text-slate-900 leading-snug">Sistem Informasi UMKM</strong>
                            <span class="block text-xs text-slate-400 mt-0.5">Pengembangan sistem transaksi &amp; inventaris digital</span>
                        </td>
                        <td class="py-4 px-6 text-slate-600 font-medium">PT. ABC Indonesia</td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                Berjalan
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-28 bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-[#0B3B82] h-full rounded-full" style="width: 75%"></div>
                                </div>
                                <span class="text-xs font-bold text-slate-600">75%</span>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-slate-600 font-medium">12 Okt 2026</td>
                        <td class="py-4 px-4 text-center">
                            <button type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition" title="Menu Opsi">
                                <svg class="w-4 h-4 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <circle cx="10" cy="4" r="2"/>
                                    <circle cx="10" cy="10" r="2"/>
                                    <circle cx="10" cy="16" r="2"/>
                                </svg>
                            </button>
                        </td>
                    </tr>

                    {{-- Row 2: Smart Farming IoT --}}
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-4 px-6">
                            <strong class="block text-sm font-bold text-slate-900 leading-snug">Smart Farming IoT</strong>
                            <span class="block text-xs text-slate-400 mt-0.5">Implementasi telemetri tanah dan irigasi otomatis</span>
                        </td>
                        <td class="py-4 px-6 text-slate-600 font-medium">CV. Tani Maju</td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                Berjalan
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-28 bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-[#0B3B82] h-full rounded-full" style="width: 60%"></div>
                                </div>
                                <span class="text-xs font-bold text-slate-600">60%</span>
                            </div>
                        </td>
                        <td class="py-4 px-6 font-bold text-rose-600">5 Okt 2026</td>
                        <td class="py-4 px-4 text-center">
                            <button type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition" title="Menu Opsi">
                                <svg class="w-4 h-4 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <circle cx="10" cy="4" r="2"/>
                                    <circle cx="10" cy="10" r="2"/>
                                    <circle cx="10" cy="16" r="2"/>
                                </svg>
                            </button>
                        </td>
                    </tr>

                    {{-- Row 3: Website Profil Perusahaan --}}
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-4 px-6">
                            <strong class="block text-sm font-bold text-slate-900 leading-snug">Website Profil Perusahaan</strong>
                            <span class="block text-xs text-slate-400 mt-0.5">Desain portal korporat modern dan CMS kustom</span>
                        </td>
                        <td class="py-4 px-6 text-slate-600 font-medium">PT. DEF Solusi</td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                Selesai
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-28 bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-emerald-500 h-full rounded-full" style="width: 100%"></div>
                                </div>
                                <span class="text-xs font-bold text-slate-600">100%</span>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-slate-400 font-medium">—</td>
                        <td class="py-4 px-4 text-center">
                            <button type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition" title="Menu Opsi">
                                <svg class="w-4 h-4 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <circle cx="10" cy="4" r="2"/>
                                    <circle cx="10" cy="10" r="2"/>
                                    <circle cx="10" cy="16" r="2"/>
                                </svg>
                            </button>
                        </td>
                    </tr>

                    {{-- Row 4: Aplikasi Manajemen Inventori --}}
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-4 px-6">
                            <strong class="block text-sm font-bold text-slate-900 leading-snug">Aplikasi Manajemen Inventori</strong>
                            <span class="block text-xs text-slate-400 mt-0.5">Pelacakan logistik pergudangan unit pendidikan</span>
                        </td>
                        <td class="py-4 px-6 text-slate-600 font-medium">Dinas Pendidikan</td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                Berjalan
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-28 bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-[#0B3B82] h-full rounded-full" style="width: 25%"></div>
                                </div>
                                <span class="text-xs font-bold text-slate-600">25%</span>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-slate-600 font-medium">18 Okt 2026</td>
                        <td class="py-4 px-4 text-center">
                            <button type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition" title="Menu Opsi">
                                <svg class="w-4 h-4 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <circle cx="10" cy="4" r="2"/>
                                    <circle cx="10" cy="10" r="2"/>
                                    <circle cx="10" cy="16" r="2"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- ===== DEADLINE MENDATANG ===== --}}
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Deadline Mendatang</h2>
                <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 text-[10px] font-extrabold tracking-wider border border-rose-100">
                    URGENT
                </span>
            </div>
            <a href="{{ route('mahasiswa.task') }}" class="text-sm font-semibold text-[#2563EB] hover:text-blue-700 inline-flex items-center gap-1.5 transition">
                <span>Lihat Kalender</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect width="18" height="18" x="3" y="4" rx="2"/>
                    <path d="M16 2v4"/>
                    <path d="M8 2v4"/>
                    <path d="M3 10h18"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Card 1: Smart Farming IoT --}}
            <a href="{{ route('mahasiswa.task') }}" class="bg-white border border-slate-200/80 hover:border-rose-300 rounded-2xl p-4 shadow-xs flex items-center justify-between gap-4 transition-all duration-150 hover:shadow-sm group">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-13 h-14 rounded-xl bg-rose-50/70 border border-rose-200 flex flex-col items-center justify-center flex-shrink-0">
                        <span class="text-lg font-black text-rose-600 leading-none">5</span>
                        <span class="text-[10px] font-bold text-rose-500 uppercase mt-1 tracking-wider">OKT</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="inline-block px-2 py-0.5 rounded-full bg-rose-100 text-rose-600 text-[10px] font-bold">2 HARI LAGI</span>
                        <h3 class="text-sm font-bold text-slate-900 truncate mt-1">Smart Farming IoT</h3>
                        <p class="text-xs text-slate-400 truncate mt-0.5">Pengumpulan revisi propo...</p>
                    </div>
                </div>
                <span class="text-slate-300 text-lg group-hover:text-rose-500 group-hover:translate-x-0.5 transition-all">›</span>
            </a>

            {{-- Card 2: Sistem Informasi UMKM --}}
            <a href="{{ route('mahasiswa.task') }}" class="bg-white border border-slate-200/80 hover:border-blue-300 rounded-2xl p-4 shadow-xs flex items-center justify-between gap-4 transition-all duration-150 hover:shadow-sm group">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-13 h-14 rounded-xl bg-blue-50/70 border border-blue-200 flex flex-col items-center justify-center flex-shrink-0">
                        <span class="text-lg font-black text-blue-700 leading-none">12</span>
                        <span class="text-[10px] font-bold text-blue-600 uppercase mt-1 tracking-wider">OKT</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="inline-block px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold">9 HARI LAGI</span>
                        <h3 class="text-sm font-bold text-slate-900 truncate mt-1">Sistem Informasi UMKM</h3>
                        <p class="text-xs text-slate-400 truncate mt-0.5">Presentasi milestone 2 · P...</p>
                    </div>
                </div>
                <span class="text-slate-300 text-lg group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all">›</span>
            </a>

            {{-- Card 3: Aplikasi Manajemen Inventori --}}
            <a href="{{ route('mahasiswa.task') }}" class="bg-white border border-slate-200/80 hover:border-slate-300 rounded-2xl p-4 shadow-xs flex items-center justify-between gap-4 transition-all duration-150 hover:shadow-sm group">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-13 h-14 rounded-xl bg-slate-50 border border-slate-200 flex flex-col items-center justify-center flex-shrink-0">
                        <span class="text-lg font-black text-slate-700 leading-none">18</span>
                        <span class="text-[10px] font-bold text-slate-500 uppercase mt-1 tracking-wider">OKT</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="inline-block px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold">15 HARI LAGI</span>
                        <h3 class="text-sm font-bold text-slate-900 truncate mt-1">Aplikasi Manajemen I...</h3>
                        <p class="text-xs text-slate-400 truncate mt-0.5">Upload dokumen akhir · D...</p>
                    </div>
                </div>
                <span class="text-slate-300 text-lg group-hover:text-slate-600 group-hover:translate-x-0.5 transition-all">›</span>
            </a>
        </div>
    </section>

</x-student-layout>
