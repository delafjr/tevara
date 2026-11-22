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
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-stretch" aria-label="Ringkasan project">
        <x-stat-card label="Total Proyek" :value="$totalCount" sub="Proyek terdaftar" color="#0B3B82" icon="folder" />
        <x-stat-card label="Sedang Berjalan" :value="$runningCount" sub="Proyek aktif sprint" color="#16A34A" icon="play" />
        <x-stat-card label="Dalam Review" :value="$reviewCount" sub="Menunggu verifikasi PM" color="#F59E0B" icon="clock-file" />
        <x-stat-card label="Deadline Terdekat" :value="$nearestDlText" sub="Target milestone" color="#6366F1" icon="calendar" />
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
