<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task & Timeline - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen">

@php
    $user       = $user ?? session('user');
    $projects   = is_array($projects ?? null) ? $projects : [];
    $overview   = is_array($overview ?? null) ? $overview : [];
    $proposal   = is_array($overview['proposal'] ?? null) ? $overview['proposal'] : [];
    $summary    = is_array($overview['summary'] ?? null) ? $overview['summary'] : [];
    $tasks      = is_array($overview['tasks'] ?? null) ? $overview['tasks'] : [];
    $hasProject = ! empty($proposal);

    $statusMeta = function (string $status): array {
        return match (strtoupper($status)) {
            'TODO'        => ['label' => 'Todo',        'dot' => 'bg-slate-400',   'pill' => 'bg-slate-100 text-slate-600'],
            'IN_PROGRESS' => ['label' => 'In Progress', 'dot' => 'bg-[#0B3B82]',  'pill' => 'bg-[#EFF6FF] text-[#0B3B82]'],
            'TESTING'     => ['label' => 'Testing',     'dot' => 'bg-[#F59E0B]',  'pill' => 'bg-[#FEF3C7] text-[#B45309]'],
            'DONE'        => ['label' => 'Done',        'dot' => 'bg-[#16A34A]',  'pill' => 'bg-[#DCFCE7] text-[#16A34A]'],
            default       => ['label' => ucfirst(strtolower($status ?: '—')), 'dot' => 'bg-slate-400', 'pill' => 'bg-slate-100 text-slate-600'],
        };
    };

    $pmStatusMeta = function (?string $pmStatus): array {
        return match (strtoupper((string) $pmStatus)) {
            'APPROVED'           => ['label' => 'Approved',          'pill' => 'bg-[#DCFCE7] text-[#16A34A]'],
            'NEEDS_IMPROVEMENT'  => ['label' => 'Perlu Perbaikan',   'pill' => 'bg-[#FEE2E2] text-[#B91C1C]'],
            'PENDING'            => ['label' => 'Menunggu Review',   'pill' => 'bg-[#FEF3C7] text-[#B45309]'],
            default              => ['label' => 'Belum Direview',    'pill' => 'bg-slate-100 text-slate-500'],
        };
    };

    $priorityMeta = function (?string $priority): array {
        return match (strtoupper((string) $priority)) {
            'CRITICAL' => ['label' => 'Critical', 'pill' => 'bg-[#FEE2E2] text-[#B91C1C]'],
            'HIGH'     => ['label' => 'High',     'pill' => 'bg-[#FEF3C7] text-[#B45309]'],
            'LOW'      => ['label' => 'Low',      'pill' => 'bg-slate-100 text-slate-500'],
            default    => ['label' => 'Medium',   'pill' => 'bg-[#EFF6FF] text-[#0B3B82]'],
        };
    };

    $columns = [
        ['key' => 'TODO',        'label' => 'Todo',                'dot' => 'bg-slate-400',  'text' => 'text-slate-500',  'badge' => 'bg-slate-100 text-slate-500'],
        ['key' => 'IN_PROGRESS', 'label' => 'In Progress',         'dot' => 'bg-[#0B3B82]', 'text' => 'text-[#0B3B82]', 'badge' => 'bg-[#EFF6FF] text-[#0B3B82]'],
        ['key' => 'TESTING',     'label' => 'Testing & Review',    'dot' => 'bg-[#F59E0B]', 'text' => 'text-[#B45309]', 'badge' => 'bg-[#FEF3C7] text-[#B45309]'],
        ['key' => 'DONE',        'label' => 'Done',                'dot' => 'bg-[#16A34A]', 'text' => 'text-[#16A34A]', 'badge' => 'bg-[#DCFCE7] text-[#16A34A]'],
    ];

    $groupedTasks = collect($tasks)->groupBy(fn ($task) => strtoupper((string) ($task['status'] ?? 'TODO')));

    $teamMembers = collect($tasks)
        ->pluck('assignee')
        ->filter(fn ($assignee) => is_array($assignee) && ! empty($assignee['id']))
        ->unique('id')
        ->values();

    $formatDate = function (?string $date): string {
        if (! $date) {
            return '—';
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->format('d M Y');
        } catch (\Throwable) {
            return $date;
        }
    };

    $overallProgress = (int) round((float) ($summary['overall_progress'] ?? 0));
    $initialTask     = $tasks[0] ?? null;

    $currentProposalId = $proposal['id'] ?? ($proposalId ?? null);
@endphp

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <x-sidebar active="task-timeline" />

    {{-- ===== MAIN CONTENT ===== --}}
    <main class="flex-1 ml-64 p-8 min-h-screen flex flex-col gap-5"
          x-data="{
            selectedTask: @js($initialTask),
            note: @js($initialTask['pm_notes'] ?? ''),
            reviewOpen: true,
            taskModal: false,
            projectMenu: false,
            selectTask(task) {
                this.selectedTask = task;
                this.note = task.pm_notes ?? '';
                this.reviewOpen = true;
            },
            formatDate(value) {
                if (!value) return '—';
                const d = new Date(value);
                if (isNaN(d.getTime())) return value;
                return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            }
          }">

        {{-- ── 1. PAGE HEADER ── --}}
        @if(session('success') || session('error'))
            <div class="rounded-xl border {{ session('success') ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700' }} px-4 py-3 text-xs font-semibold" x-data="{ visible: true }" x-show="visible">
                <div class="flex items-center justify-between gap-3">
                    <span>{{ session('success') ?? session('error') }}</span>
                    <button type="button" @click="visible = false" class="text-current/60 hover:text-current">&times;</button>
                </div>
            </div>
        @endif

        @if(!empty($error))
            <div class="rounded-xl border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3 text-xs font-semibold">
                {{ $error }}
            </div>
        @endif

        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
            {{-- gradient dekoratif --}}
            <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                    Monitoring Penugasan
                </span>
                <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                    Task &amp; <span class="text-[#FFB800]">Timeline</span>
                </h1>
                <p class="text-sm text-slate-400 mt-1.5 max-w-2xl leading-relaxed">
                    Pantau kemajuan pengerjaan task, review hasil pekerjaan mahasiswa, dan koordinasi deadline proyek.
                </p>
            </div>
        </div>

        @if(!$hasProject)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs px-8 py-14 text-center">
                <div class="w-12 h-12 rounded-full bg-[#F8FAFC] text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-[#0B254A]">Belum ada proyek terdaftar</h2>
                <p class="text-sm text-slate-500 mt-2">Task dan timeline akan muncul setelah proyek ditugaskan kepada Anda sebagai Dosen PM.</p>
            </div>
        @else
        {{-- ── 2. PROYEK AKTIF BANNER ── --}}
        <div class="bg-[#0B254A] rounded-2xl px-6 py-5 flex flex-col xl:flex-row xl:items-center gap-5">
            {{-- Ikon --}}
            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                </svg>
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1.5">
                    <span class="inline-block px-2 py-0.5 rounded-full bg-[#F59E0B] text-[#0B254A] text-[9px] font-extrabold tracking-wider uppercase">Proyek PM</span>
                    <span class="text-[10px] font-semibold text-blue-200">{{ $proposal['proposal_code'] ?? '—' }}</span>
                </div>
                <p class="text-white font-extrabold text-sm leading-tight">{{ $proposal['title'] ?? '—' }}</p>
                <div class="flex items-center gap-4 mt-2 text-[11px] text-blue-200 flex-wrap">
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>
                        </svg>
                        {{ $proposal['category'] ?? 'Tanpa kategori' }}
                    </span>
                    <span>·</span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>
                        </svg>
                        {{ $formatDate($proposal['start_date'] ?? null) }} — {{ $formatDate($proposal['target_completion_date'] ?? null) }}
                    </span>
                    <span>·</span>
                    <span class="flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FFB800]"></span>
                        Status: {{ $proposal['status'] ?? '—' }}
                    </span>
                </div>
            </div>

            {{-- Progress ringkas --}}
            <div class="w-full xl:w-48 flex-shrink-0">
                <div class="flex items-center justify-between text-[10px] text-blue-200 mb-1.5">
                    <span class="uppercase tracking-wide">Progress Keseluruhan</span>
                    <span class="font-bold text-white">{{ $overallProgress }}%</span>
                </div>
                <div class="h-2 bg-white/15 rounded-full overflow-hidden">
                    <div class="h-full bg-[#FFB800] rounded-full transition-all duration-500" style="width: {{ $overallProgress }}%"></div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0 relative">
                <button type="button" @click="taskModal = true" class="inline-flex items-center gap-1.5 bg-[#F59E0B] hover:bg-yellow-400 text-[#0B254A] text-xs font-extrabold px-4 py-2 rounded-xl transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Tambah Task
                </button>
                <button type="button" @click="projectMenu = !projectMenu" class="inline-flex items-center gap-1.5 border border-white/30 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                    </svg>
                    Ganti Proyek
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>

                <div x-show="projectMenu" x-cloak @click.outside="projectMenu = false" class="absolute top-full mt-2 right-0 z-30 w-80 rounded-xl bg-white p-2 shadow-xl ring-1 ring-slate-200">
                    <p class="px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Pilih Proyek</p>
                    @foreach($projects as $project)
                        <a href="{{ route('pm.task-timeline', ['proposal_id' => $project['id'] ?? '']) }}"
                           class="block rounded-lg px-2.5 py-2 hover:bg-[#F8FAFC] transition-colors {{ (string) ($project['id'] ?? '') === (string) $currentProposalId ? 'bg-[#EAF3FF]' : '' }}">
                            <p class="text-xs font-semibold text-[#1E293B] leading-snug truncate">{{ $project['title'] ?? '—' }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5 truncate">
                                {{ $project['client_name'] ?? '—' }} · {{ $project['status'] ?? '—' }} · {{ (int) ($project['progress'] ?? 0) }}%
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── 3. STAT CARDS (dari summary backend) ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Total Task</p>
                    <div class="w-8 h-8 rounded-lg bg-[#EBF3FE] flex items-center justify-center text-[#2563EB]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-[#0B254A] leading-none">{{ (int) ($summary['total'] ?? count($tasks)) }}</p>
                <p class="text-[11px] text-slate-400 font-medium mt-1">seluruh task proyek</p>
            </div>

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
                <p class="text-3xl font-extrabold text-[#0B254A] leading-none">{{ (int) ($summary['pending_review'] ?? 0) }}</p>
                <p class="text-[11px] text-[#F59E0B] font-semibold mt-1">menunggu keputusan PM</p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Sedang Berjalan</p>
                    <div class="w-8 h-8 rounded-lg bg-[#DBEAFE] flex items-center justify-center text-[#2563EB]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-[#0B254A] leading-none">{{ (int) ($summary['in_progress'] ?? 0) }}</p>
                <p class="text-[11px] text-[#2563EB] font-semibold mt-1">{{ (int) ($summary['testing'] ?? 0) }} sedang testing</p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Disetujui / Selesai</p>
                    <div class="w-8 h-8 rounded-lg bg-[#DCFCE7] flex items-center justify-center text-[#16A34A]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-[#0B254A] leading-none">{{ (int) ($summary['approved'] ?? 0) }}</p>
                <p class="text-[11px] text-slate-400 font-medium mt-1">{{ (int) ($summary['done'] ?? 0) }} task done</p>
            </div>
        </div>

        {{-- ── 4. KANBAN BOARD ── --}}
        @if(empty($tasks))
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs px-8 py-14 text-center">
                <div class="w-12 h-12 rounded-full bg-[#F8FAFC] text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-[#0B254A]">Belum ada task pada proyek ini</h2>
                <p class="text-sm text-slate-500 mt-2">Tambahkan task pertama agar timeline pengerjaan tim mulai terbentuk.</p>
                <button type="button" @click="taskModal = true" class="mt-4 inline-flex items-center gap-1.5 bg-[#0B3B82] text-white text-xs font-semibold px-4 py-2.5 rounded-xl hover:bg-[#082a5c] transition-colors">
                    Tambah Task Pertama
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 items-start">
                @foreach($columns as $column)
                    @php $columnTasks = $groupedTasks->get($column['key'], collect()); @endphp
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $column['dot'] }} inline-block"></span>
                            <span class="text-xs font-extrabold {{ $column['text'] }} uppercase tracking-wide">{{ $column['label'] }}</span>
                            <span class="ml-auto text-[10px] font-bold {{ $column['badge'] }} px-2 py-0.5 rounded-full">{{ $columnTasks->count() }}</span>
                        </div>

                        @forelse($columnTasks as $task)
                            @php
                                $taskStatus  = $statusMeta((string) ($task['status'] ?? 'TODO'));
                                $pmStatus    = $pmStatusMeta($task['pm_status'] ?? null);
                                $priority    = $priorityMeta($task['priority'] ?? null);
                                $taskProgress = (int) round((float) ($task['progress_percentage'] ?? 0));
                                $assignee    = is_array($task['assignee'] ?? null) ? $task['assignee'] : null;
                                $initials    = $assignee
                                    ? collect(explode(' ', trim((string) ($assignee['name'] ?? 'U'))))->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('')
                                    : '—';
                            @endphp
                            <button type="button"
                                    @click="selectTask(@js($task))"
                                    :class="selectedTask?.id === {{ (int) ($task['id'] ?? 0) }} ? 'border-[#0B3B82] ring-1 ring-[#0B3B82]' : 'border-slate-100 hover:border-slate-200'"
                                    class="w-full text-left bg-white rounded-xl border shadow-2xs p-4 hover:shadow-sm transition-all cursor-pointer">
                                <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $priority['pill'] }}">{{ $priority['label'] }}</span>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $pmStatus['pill'] }}">{{ $pmStatus['label'] }}</span>
                                </div>

                                <p class="text-[13px] font-bold text-[#0B254A] leading-tight mb-2.5">{{ $task['title'] ?? '—' }}</p>

                                <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mb-2.5">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>
                                    </svg>
                                    {{ $formatDate($task['due_date'] ?? null) }}
                                </div>

                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[10px] text-slate-400">Progress</span>
                                    <span class="text-[10px] font-bold {{ $taskProgress >= 100 ? 'text-[#16A34A]' : 'text-[#2563EB]' }}">{{ $taskProgress }}%</span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mb-3">
                                    <div class="h-full {{ $taskProgress >= 100 ? 'bg-[#16A34A]' : 'bg-[#2563EB]' }} rounded-full" style="width: {{ max(0, min(100, $taskProgress)) }}%"></div>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-6 h-6 rounded-full bg-[#0B254A] text-white flex items-center justify-center text-[9px] font-bold flex-shrink-0">
                                            {{ mb_strtoupper($initials) }}
                                        </div>
                                        <span class="text-[11px] text-slate-500 font-medium truncate">{{ $assignee['name'] ?? 'Belum ditugaskan' }}</span>
                                    </div>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $taskStatus['pill'] }}">{{ $taskStatus['label'] }}</span>
                                </div>
                            </button>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 bg-white/60 px-4 py-8 text-center">
                                <p class="text-[11px] text-slate-400">Tidak ada task</p>
                            </div>
                        @endforelse
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ── 5. TASK DETAIL & REVIEW PANEL ── --}}
        <div
            x-show="selectedTask && reviewOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden"
        >
            <div class="px-6 py-4 border-b border-slate-100 flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1.5">
                        <svg class="w-4 h-4 text-[#2563EB] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                        </svg>
                        <p class="text-sm font-extrabold text-[#0B254A] truncate" x-text="selectedTask?.title ?? 'Pilih task'"></p>
                    </div>
                    <div class="flex items-center gap-4 text-[11px] text-slate-400 flex-wrap">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                            Mahasiswa: <strong class="text-slate-600" x-text="selectedTask?.assignee?.name ?? 'Belum ditugaskan'"></strong>
                            <span x-show="selectedTask?.assignee?.nim" x-text="'(' + (selectedTask?.assignee?.nim ?? '') + ')'"></span>
                        </span>
                        <span>·</span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>
                            </svg>
                            Deadline: <strong class="text-slate-600" x-text="formatDate(selectedTask?.due_date)"></strong>
                        </span>
                        <span>·</span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            Direview: <strong class="text-slate-600" x-text="formatDate(selectedTask?.pm_reviewed_at)"></strong>
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

            <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-slate-100">
                {{-- LEFT: Detail Task --}}
                <div class="px-6 py-5 space-y-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                        </svg>
                        <p class="text-[13px] font-extrabold text-[#0B254A]">Laporan Pekerjaan Mahasiswa</p>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Progress</p>
                            <p class="text-xl font-extrabold text-[#0B254A]" x-text="(selectedTask?.progress_percentage ?? 0) + '%'"></p>
                        </div>
                        <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-[#EFF6FF] text-[#0B3B82]" x-text="selectedTask?.status ?? '—'"></span>
                    </div>

                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-[#2563EB] rounded-full transition-all duration-500" :style="'width: ' + Math.max(0, Math.min(100, selectedTask?.progress_percentage ?? 0)) + '%'"></div>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">Ringkasan Capaian</p>
                        <p class="text-xs text-slate-600 leading-relaxed bg-[#F8FAFC] border border-slate-100 rounded-xl p-3.5" x-text="selectedTask?.achieved_summary || 'Mahasiswa belum mengisi ringkasan capaian.'"></p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">Kendala</p>
                        <p class="text-xs text-slate-600 leading-relaxed bg-[#F8FAFC] border border-slate-100 rounded-xl p-3.5" x-text="selectedTask?.blockers_summary || 'Tidak ada kendala yang dilaporkan.'"></p>
                    </div>

                    <div x-show="selectedTask?.report_attachment_url">
                        <a :href="selectedTask?.report_attachment_url" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[11px] text-[#2563EB] font-semibold hover:underline">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                            </svg>
                            Lihat lampiran laporan
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

                    <form method="POST"
                          :action="'{{ url('/pm/proposals/' . ($currentProposalId ?? 0) . '/tasks') }}/' + (selectedTask?.id ?? 0) + '/review'"
                          class="space-y-4">
                        @csrf

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="pm-note" class="text-xs font-bold text-[#0B254A]">Catatan Review &amp; Arahan PM:</label>
                                <span class="text-[10px] text-slate-400">Maks. 500 karakter</span>
                            </div>
                            <textarea
                                id="pm-note"
                                name="note"
                                x-model="note"
                                rows="6"
                                maxlength="500"
                                placeholder="Tulis arahan perbaikan atau catatan persetujuan untuk mahasiswa..."
                                class="w-full text-xs text-slate-600 bg-[#F8FAFC] border border-slate-200 rounded-xl p-3.5 focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]/20 resize-none placeholder:text-slate-300 leading-relaxed transition"
                            ></textarea>
                        </div>

                        <div class="flex flex-col gap-2.5">
                            <button
                                type="submit"
                                name="decision"
                                value="revision"
                                class="w-full flex items-center justify-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-semibold py-2.5 rounded-xl transition-colors cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                                Minta Revisi
                            </button>
                            <button
                                type="submit"
                                name="decision"
                                value="approve"
                                class="w-full flex items-center justify-center gap-2 bg-[#0B254A] hover:bg-[#0d2f5e] text-white text-xs font-extrabold py-2.5 rounded-xl transition-colors cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                Setujui Task (Approve)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── 6. MODAL TAMBAH TASK ── --}}
        <div x-show="taskModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-[#0B254A]/30 p-4" @keydown.escape.window="taskModal = false">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl" @click.outside="taskModal = false">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#2563EB]">Task baru</p>
                        <h2 class="text-lg font-extrabold text-[#0B254A]">Tambah Task Proyek</h2>
                    </div>
                    <button type="button" @click="taskModal = false" class="rounded-lg p-2 text-slate-400 hover:bg-slate-50">&times;</button>
                </div>
                <form method="POST" action="{{ route('pm.task.store', ['proposal' => $currentProposalId ?? 0]) }}" class="space-y-4">
                    @csrf
                    <label class="block text-xs font-bold text-slate-600">Nama task
                        <input name="title" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-[#2563EB] focus:outline-none" placeholder="Contoh: Integrasi API pembayaran">
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block text-xs font-bold text-slate-600">Deadline
                            <input type="date" name="due_date" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-[#2563EB] focus:outline-none">
                        </label>
                        <label class="block text-xs font-bold text-slate-600">Prioritas
                            <select name="priority" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-[#2563EB] focus:outline-none">
                                <option value="LOW">Low</option>
                                <option value="MEDIUM" selected>Medium</option>
                                <option value="HIGH">High</option>
                                <option value="CRITICAL">Critical</option>
                            </select>
                        </label>
                    </div>
                    <label class="block text-xs font-bold text-slate-600">PIC Mahasiswa
                        <select name="assigned_student_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-[#2563EB] focus:outline-none">
                            <option value="">Belum ditentukan</option>
                            @foreach($teamMembers as $member)
                                <option value="{{ $member['id'] }}">{{ $member['name'] }}{{ !empty($member['nim']) ? ' · ' . $member['nim'] : '' }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="taskModal = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-[#0B254A] px-4 py-2.5 text-xs font-extrabold text-white hover:bg-[#123b6e]">Simpan Task</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

    </main>

</div>

</body>
</html>
