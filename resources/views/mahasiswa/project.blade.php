@php
    $user = $user ?? session('user', []);
    $dash = $dashboard ?? [];
    $projectsList = is_array($dash['projects'] ?? null) ? $dash['projects'] : [];
    $totalCount = count($projectsList);
    $runningCount = count(array_filter($projectsList, fn($p) => in_array(strtolower($p['status'] ?? ''), ['active', 'berjalan', 'testing'])));
    $reviewCount = count(array_filter($projectsList, fn($p) => in_array(strtolower($p['status'] ?? ''), ['review', 'under review'])));
    $completedCount = count(array_filter($projectsList, fn($p) => in_array(strtolower($p['status'] ?? ''), ['completed', 'selesai', 'handover'])));
    $nearestDl = collect($projectsList)->pluck('deadline')->filter()->sort()->first();
    $nearestDlText = !empty($nearestDl) ? date('d M Y', strtotime($nearestDl)) : '—';
@endphp

<x-student-layout active="project" title="Project Saya">

    {{-- ===== HEADER CARD ===== --}}
    <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
        {{-- gradient dekoratif --}}
        <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                Workspace Mahasiswa
            </span>
            <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                Project <span class="text-[#FFB800]">Saya</span>
            </h1>
            <p class="text-sm text-slate-400 mt-1.5">Daftar proyek Teaching Factory industri yang sedang dan telah Anda kerjakan bersama tim</p>
        </div>
    </div>

    {{-- ===== 4 STAT CARDS ===== --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" aria-label="Ringkasan project">
        {{-- Card 1: Total Proyek --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold tracking-wider uppercase">
                <span>Total Proyek</span>
                <span class="w-9 h-9 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </span>
            </div>
            <div>
                <strong class="text-3xl font-extrabold text-[#0F172A] tracking-tight block">{{ $totalCount }}</strong>
                <span class="text-xs text-slate-400 mt-1 block">Proyek Terdaftar</span>
            </div>
        </div>

        {{-- Card 2: Sedang Berjalan --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold tracking-wider uppercase">
                <span>Sedang Berjalan</span>
                <span class="w-9 h-9 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </span>
            </div>
            <div>
                <strong class="text-3xl font-extrabold text-[#0F172A] tracking-tight block">{{ $runningCount }}</strong>
                <span class="text-xs text-slate-400 mt-1 block">Proyek Aktif Sprint</span>
            </div>
        </div>

        {{-- Card 3: Dalam Review --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold tracking-wider uppercase">
                <span>Dalam Review</span>
                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </div>
            <div>
                <strong class="text-3xl font-extrabold text-[#0F172A] tracking-tight block">{{ $reviewCount }}</strong>
                <span class="text-xs text-slate-400 mt-1 block">Menunggu Verifikasi PM</span>
            </div>
        </div>

        {{-- Card 4: Deadline Terdekat --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold tracking-wider uppercase">
                <span>Deadline Terdekat</span>
                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div>
                <strong class="text-2xl font-extrabold text-[#0F172A] tracking-tight block">{{ $nearestDlText }}</strong>
                <span class="text-xs text-slate-400 mt-1 block">Target Milestone</span>
            </div>
        </div>
    </section>

    {{-- ===== FILTER & PROJECT LIST WRAPPER ===== --}}
    <div x-data="{ currentFilter: 'all', sortBy: 'terbaru' }" class="space-y-5">

        {{-- Filter toolbar --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2.5">
                <button
                    type="button"
                    @click="currentFilter = 'all'"
                    :class="currentFilter === 'all' ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                >
                    Semua ({{ $totalCount }})
                </button>
                <button
                    type="button"
                    @click="currentFilter = 'running'"
                    :class="currentFilter === 'running' ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                >
                    Sedang Berjalan ({{ $runningCount }})
                </button>
                <button
                    type="button"
                    @click="currentFilter = 'review'"
                    :class="currentFilter === 'review' ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                >
                    Dalam Review ({{ $reviewCount }})
                </button>
                <button
                    type="button"
                    @click="currentFilter = 'done'"
                    :class="currentFilter === 'done' ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                >
                    Selesai ({{ $completedCount }})
                </button>
            </div>
        </div>

        {{-- Project Cards List --}}
        <div class="space-y-4">
            @forelse($projectsList as $idx => $p)
            @php
                $st = strtolower($p['status'] ?? 'berjalan');
                $isDone = in_array($st, ['completed', 'selesai', 'handover']);
                $isReview = in_array($st, ['review', 'under review', 'testing']);
                $filterKey = $isDone ? 'done' : ($isReview ? 'review' : 'running');
                $code = 'PRJ-' . str_pad($p['id'] ?? ($idx + 1), 3, '0', STR_PAD_LEFT);
                $prog = (int) ($p['progress'] ?? 0);
                $dl = !empty($p['deadline']) ? date('d M Y', strtotime($p['deadline'])) : '—';
            @endphp
            <article
                x-data="{ open: true }"
                x-show="currentFilter === 'all' || currentFilter === '{{ $filterKey }}'"
                class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs transition"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-[#0B2A5E] text-white flex items-center justify-center flex-shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-bold text-slate-400">{{ $code }}</span>
                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-[#2563EB] font-bold text-[11px]">{{ $p['role'] ?? 'Anggota Tim' }}</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $isDone ? 'bg-green-50 text-green-700' : ($isReview ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isDone ? 'bg-green-600' : ($isReview ? 'bg-amber-500' : 'bg-blue-600') }}"></span>
                                    {{ $p['status'] ?? 'Berjalan' }}
                                </span>
                            </div>
                            <h2 class="text-lg font-bold text-slate-900 tracking-tight mt-1.5">
                                {{ $p['title'] }}
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $p['client_name'] ?? 'Mitra Industri' }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('mahasiswa.task', ['proposal_id' => $p['id']]) }}" class="px-3.5 py-1.5 rounded-xl border border-blue-200 text-[#2563EB] bg-white hover:bg-blue-50 text-xs font-semibold transition flex items-center gap-1.5">
                        Lihat Task & Progress →
                    </a>
                </div>

                {{-- Progress Bar Row --}}
                <div class="mt-5 flex items-center justify-between gap-4">
                    <span class="text-xs font-semibold text-slate-500 whitespace-nowrap">Progress Proyek</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-[#0B3B82] h-full rounded-full transition-all duration-300" style="width: {{ $prog }}%"></div>
                    </div>
                    <span class="text-xs font-bold text-slate-700 whitespace-nowrap">{{ $prog }}%</span>
                    <div class="text-right whitespace-nowrap border-l border-slate-200/80 pl-4 ml-2">
                        <span class="text-[10px] text-slate-400 block uppercase font-medium">Target Selesai</span>
                        <strong class="text-xs font-bold text-slate-800">{{ $dl }}</strong>
                    </div>
                </div>
            </article>
            @empty
            <div class="bg-white border border-slate-200/80 rounded-2xl p-10 text-center text-slate-400 text-xs">
                Anda belum terdaftar dalam proyek Teaching Factory aktif di database.
            </div>
            @endforelse        </div>

    </div>

</x-student-layout>
