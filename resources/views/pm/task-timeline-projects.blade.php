<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pilih Proyek - Task & Timeline - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen">

@php
    $user = $user ?? session('user');
    $projects = is_array($projects ?? null) ? $projects : [];
    $usingDemoData = (bool) ($usingDemoData ?? false);
    $totalCount = count($projects);
    $runningCount = count(array_filter($projects, fn($p) => in_array(strtolower($p['status'] ?? ''), ['active', 'berjalan', 'testing'])));
    $completedCount = count(array_filter($projects, fn($p) => in_array(strtolower($p['status'] ?? ''), ['completed', 'selesai', 'handover'])));
    $avgProgress = $totalCount > 0 ? (int) round(collect($projects)->avg('progress') ?? 0) : 0;
@endphp

<div class="flex min-h-screen">
    {{-- == Sidebar == --}}
    <x-sidebar active="task-timeline" />

    {{-- == Konten Utama == --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen min-w-0">
        <main class="flex-1 px-8 py-7 space-y-6">

            {{-- 1. Header Hero Card --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        Workspace Project Manager
                    </span>
                    <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                        Task &amp; <span class="text-[#FFB800]">Timeline Proyek</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1.5">Pilih proyek yang Anda kelola untuk memantau papan Kanban, timeline, dan validasi task tim</p>
                </div>
            </div>

            @if($usingDemoData)
                <div role="status" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800">
                    Preview data demo — data proyek ini tidak tersimpan ke backend.
                </div>
            @endif

            {{-- 2. 4 Stat Cards --}}
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-stretch" aria-label="Statistik Proyek">
                <x-stat-card label="Total Proyek Dikelola" :value="$totalCount" sub="Proyek aktif di bawah PM" color="#0B3B82" icon="folder" />
                <x-stat-card label="Sedang Berjalan" :value="$runningCount" sub="Fase pengerjaan aktif" color="#16A34A" icon="play" />
                <x-stat-card label="Rata-rata Progress" :value="$avgProgress . '%'" sub="Capaian seluruh proyek" color="#6366F1" icon="trend-up" />
                <x-stat-card label="Proyek Selesai" :value="$completedCount" sub="Tahap serah terima / selesai" color="#F59E0B" icon="check" />
            </section>

            {{-- 3. Filter & Project List Wrapper --}}
            <div x-data="{ currentFilter: 'all', search: '' }" class="space-y-5">

                {{-- Toolbar Filter & Search --}}
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            @click="currentFilter = 'all'"
                            :class="currentFilter === 'all' ? 'bg-[#0B3B82] text-white shadow-xs' : 'bg-white border border-slate-200/80 text-slate-600 hover:bg-slate-50'"
                            class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                        >
                            Semua Proyek ({{ $totalCount }})
                        </button>
                        <button
                            type="button"
                            @click="currentFilter = 'running'"
                            :class="currentFilter === 'running' ? 'bg-[#0B3B82] text-white shadow-xs' : 'bg-white border border-slate-200/80 text-slate-600 hover:bg-slate-50'"
                            class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                        >
                            Sedang Berjalan ({{ $runningCount }})
                        </button>
                        <button
                            type="button"
                            @click="currentFilter = 'done'"
                            :class="currentFilter === 'done' ? 'bg-[#0B3B82] text-white shadow-xs' : 'bg-white border border-slate-200/80 text-slate-600 hover:bg-slate-50'"
                            class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                        >
                            Selesai ({{ $completedCount }})
                        </button>
                    </div>

                    {{-- Search Input --}}
                    <div class="relative w-full sm:w-64">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                        </svg>
                        <input
                            type="text"
                            x-model="search"
                            placeholder="Cari nama atau klien proyek..."
                            class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200/80 rounded-xl text-xs text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#0B3B82] transition shadow-2xs"
                        >
                    </div>
                </div>

                {{-- Daftar Proyek Cards --}}
                @if(empty($projects))
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center shadow-xs">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-[#0B254A]">Belum Ada Proyek yang Dikelola</h3>
                        <p class="text-xs text-slate-500 mt-1">Anda belum ditugaskan sebagai Project Manager pada proyek manapun saat ini.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($projects as $idx => $p)
                            @php
                                $pId = $p['id'] ?? null;
                                $st = strtolower($p['status'] ?? 'berjalan');
                                $isDone = in_array($st, ['completed', 'selesai', 'handover']);
                                $isTesting = in_array($st, ['testing']);
                                $filterKey = $isDone ? 'done' : 'running';
                                $code = $p['project_code'] ?? $p['proposal_code'] ?? ('PRJ-' . str_pad($pId ?? ($idx + 1), 3, '0', STR_PAD_LEFT));
                                $prog = (int) ($p['progress'] ?? 0);
                                $dl = !empty($p['deadline']) ? date('d M Y', strtotime($p['deadline'])) : '—';
                                $title = $p['title'] ?? 'Proyek Tanpa Judul';
                                $clientName = $p['client_name'] ?? 'Mitra Industri';
                                $teamCount = $p['student_count'] ?? count($p['team'] ?? []);
                                $category = $p['category'] ?? 'Teaching Factory';
                            @endphp
                            <article
                                x-show="(currentFilter === 'all' || currentFilter === '{{ $filterKey }}') && ('{{ strtolower(addslashes($title . ' ' . $clientName . ' ' . $code)) }}'.includes(search.toLowerCase()))"
                                class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs hover:border-[#0B3B82]/30 hover:shadow-md transition-all group"
                            >
                                <div class="flex flex-col md:flex-row md:items-start justify-between gap-5">
                                    {{-- Kolom Kiri: Info Proyek --}}
                                    <div class="flex items-start gap-4 flex-1 min-w-0">
                                        <div class="w-12 h-12 rounded-xl bg-[#0B254A] text-white flex items-center justify-center flex-shrink-0 shadow-xs group-hover:bg-[#0B3B82] transition-colors">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2 text-xs mb-1.5">
                                                <span class="font-extrabold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md text-[11px]">{{ $code }}</span>
                                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-[#0B3B82] font-bold text-[11px] uppercase tracking-wider">{{ $category }}</span>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $isDone ? 'bg-emerald-50 text-emerald-700' : ($isTesting ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-[#0B3B82]') }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $isDone ? 'bg-emerald-600' : ($isTesting ? 'bg-amber-500' : 'bg-[#0B3B82]') }}"></span>
                                                    {{ $p['status'] ?? 'Berjalan' }}
                                                </span>
                                            </div>
                                            <h2 class="text-base font-bold text-[#0B254A] leading-snug group-hover:text-[#0B3B82] transition-colors">
                                                {{ $title }}
                                            </h2>
                                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 mt-1.5">
                                                <span class="flex items-center gap-1 text-slate-500">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                    </svg>
                                                    {{ $clientName }}
                                                </span>
                                                <span>•</span>
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                                                    </svg>
                                                    {{ $teamCount }} Mahasiswa Tim
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Kolom Kanan: Tombol Masuk Detail --}}
                                    <div class="flex items-center md:self-center">
                                        <a
                                            href="{{ route('pm.task-timeline', ['proposal_id' => $pId]) }}"
                                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#0B3B82] hover:bg-[#082a5c] text-white text-xs font-bold transition shadow-xs group-hover:shadow-md cursor-pointer whitespace-nowrap"
                                        >
                                            <span>Kelola Task &amp; Timeline</span>
                                            <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>

                                {{-- Progress Bar Row --}}
                                <div class="mt-5 pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex-1 flex items-center gap-3">
                                        <span class="text-xs font-semibold text-slate-500 whitespace-nowrap">Progress Overall</span>
                                        <div class="flex-1 bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                            <div
                                                class="h-full rounded-full transition-all duration-500 {{ $prog >= 100 ? 'bg-[#16A34A]' : 'bg-[#0B3B82]' }}"
                                                style="width: {{ $prog }}%"
                                            ></div>
                                        </div>
                                        <span class="text-xs font-extrabold text-[#0B254A] whitespace-nowrap">{{ $prog }}%</span>
                                    </div>

                                    <div class="flex items-center gap-4 text-xs sm:border-l sm:border-slate-200/80 sm:pl-4">
                                        <div>
                                            <span class="text-[10px] text-slate-400 block uppercase font-medium">Target Selesai</span>
                                            <span class="font-bold text-slate-700">{{ $dl }}</span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

        </main>
    </div>
</div>

</body>
</html>
