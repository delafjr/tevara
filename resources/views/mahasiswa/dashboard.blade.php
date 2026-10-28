@php
    $user = $user ?? session('user', []);
    $userName = ($user['name'] ?? '') === 'Dimas Setiawan' || empty($user['name']) ? 'Alvian Reza' : $user['name'];
    $firstName = explode(' ', $userName)[0] ?? 'Alvian';

    $dash = $dashboard ?? [];
    $activeProjects = $dash['active_projects'] ?? 0;
    $pendingTasks = $dash['pending_tasks'] ?? 0;
    $completedProjects = $dash['completed_projects'] ?? 0;
    $projectsList = $dash['projects'] ?? [];
    $tasksList = $dash['tasks'] ?? [];

    $allDueDates = collect($projectsList)
        ->filter(fn($p) => strtolower($p['status'] ?? '') !== 'completed' && strtolower($p['status'] ?? '') !== 'selesai')
        ->pluck('due_date')
        ->merge(
            collect($tasksList)
                ->filter(fn($t) => strtoupper($t['status'] ?? '') !== 'DONE' && strtoupper($t['status'] ?? '') !== 'SELESAI')
                ->pluck('due_date')
        )
        ->filter()
        ->map(fn($d) => \Carbon\Carbon::parse($d))
        ->sort();
    $nearestDate = $allDueDates->first();
    $daysRemaining = $nearestDate ? now()->startOfDay()->diffInDays($nearestDate->startOfDay(), false) : null;
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
            <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[11.5px] font-semibold px-3 py-1.5 rounded-lg">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Data terbaru
            </span>
        </div>
    </div>

    {{-- ===== 4 STAT CARDS ===== --}}
    @php
        $deadlineText = $daysRemaining === null ? 'Aman' : ($daysRemaining < 0 ? 'Terlewat' : ($daysRemaining === 0 ? 'Hari ini' : $daysRemaining . ' Hari'));
        $deadlineSub = $daysRemaining === null ? 'Sisa Waktu' : ($daysRemaining < 0 ? 'Terlewat' : 'Sisa Waktu');
        $isUrgentDeadline = ($daysRemaining !== null && $daysRemaining <= 3);
        $deadlineColor = $isUrgentDeadline ? '#DC2626' : '#0B3B82';

        $studentStats = [
            ['label' => 'Project Aktif',     'value' => $activeProjects,    'sub' => 'Dalam Pengerjaan', 'color' => '#0B3B82', 'icon' => 'play'],
            ['label' => 'Menunggu Review',   'value' => $pendingTasks,      'sub' => 'Perlu Review',     'color' => '#F59E0B', 'icon' => 'clock-file'],
            ['label' => 'Project Selesai',   'value' => $completedProjects, 'sub' => 'Siap Produksi',    'color' => '#0B3B82', 'icon' => 'check'],
            ['label' => 'Deadline Terdekat', 'value' => $deadlineText,      'sub' => $deadlineSub,       'color' => $deadlineColor, 'icon' => 'calendar'],
        ];
    @endphp

    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-stretch" aria-label="Ringkasan aktivitas">
        @foreach($studentStats as $stat)
            <x-stat-card :label="$stat['label']" :value="$stat['value']" :sub="$stat['sub']" :color="$stat['color']" :icon="$stat['icon']" />
        @endforeach
    </section>

    {{-- ===== PROJECT TERBARU TABLE ===== --}}
    <section class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 py-5 flex items-center justify-between border-b border-slate-100">
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Project Terbaru</h2>
                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">{{ count($projectsList) }} Project</span>
            </div>
            <a href="{{ route('mahasiswa.project') }}" class="text-sm font-semibold text-[#0B3B82] hover:text-[#082a5c] inline-flex items-center gap-1 group transition">
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($projectsList as $proj)
                        @php
                            $status = $proj['status'] ?? 'Berjalan';
                            $isCompleted = strtolower($status) === 'completed' || strtolower($status) === 'selesai';
                            $projDueDate = !empty($proj['due_date']) ? \Carbon\Carbon::parse($proj['due_date']) : null;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-6">
                                <strong class="block text-sm font-bold text-slate-900 leading-snug">{{ $proj['title'] ?? '-' }}</strong>
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-medium">{{ $proj['client_name'] ?? '-' }}</td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $isCompleted ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isCompleted ? 'bg-emerald-600' : 'bg-blue-600' }}"></span>
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-28 bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div class="{{ $isCompleted ? 'bg-emerald-500' : 'bg-[#0B3B82]' }} h-full rounded-full" style="width: {{ $proj['progress'] ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-600">{{ $proj['progress'] ?? 0 }}%</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 {{ $projDueDate && $projDueDate->isPast() && !$isCompleted ? 'font-bold text-rose-600' : 'text-slate-600 font-medium' }}">
                                {{ $projDueDate ? $projDueDate->translatedFormat('d M Y') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-sm">Belum ada data project.</td>
                        </tr>
                    @endforelse
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
            @forelse($tasksList as $task)
                @php
                    $status = strtoupper($task['status'] ?? 'PENDING');
                    $isDone = $status === 'DONE' || $status === 'SELESAI';
                    $dueDate = !empty($task['due_date']) ? \Carbon\Carbon::parse($task['due_date']) : null;
                    $diffDays = $dueDate ? now()->startOfDay()->diffInDays($dueDate->startOfDay(), false) : null;
                    $isUrgent = !$isDone && $diffDays !== null && $diffDays <= 3;
                @endphp
                <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-13 h-14 rounded-xl {{ $isDone ? 'bg-emerald-50/70 border border-emerald-200 text-emerald-600' : ($isUrgent ? 'bg-rose-50/70 border border-rose-200 text-rose-600' : 'bg-blue-50/70 border border-blue-200 text-blue-700') }} flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-lg font-black leading-none">{{ $dueDate ? $dueDate->format('d') : '—' }}</span>
                            <span class="text-[10px] font-bold uppercase mt-1 tracking-wider">{{ $dueDate ? $dueDate->translatedFormat('M') : '' }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="inline-block px-2 py-0.5 rounded-full {{ $isDone ? 'bg-emerald-100 text-emerald-700' : ($isUrgent ? 'bg-rose-100 text-rose-600' : 'bg-blue-100 text-blue-700') }} text-[10px] font-bold">
                                @if($isDone)
                                    SELESAI
                                @elseif($diffDays === null)
                                    TIDAK ADA DEADLINE
                                @elseif($diffDays < 0)
                                    LEWAT DEADLINE
                                @elseif($diffDays === 0)
                                    HARI INI
                                @else
                                    {{ $diffDays }} HARI LAGI
                                @endif
                            </span>
                            <h3 class="text-sm font-bold text-slate-900 truncate mt-1">{{ $task['title'] ?? '-' }}</h3>
                            <p class="text-xs text-slate-400 truncate mt-0.5">{{ $task['proposal']['title'] ?? ($task['description'] ?? 'Tugas TeFa') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-6 text-center text-slate-400 text-sm bg-white border border-slate-200/80 rounded-2xl">
                    Belum ada deadline tugas mendatang.
                </div>
            @endforelse
        </div>
    </section>

</x-student-layout>