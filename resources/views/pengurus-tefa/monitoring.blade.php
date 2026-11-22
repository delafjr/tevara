<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitoring - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php $user = $user ?? session('user'); $firstName = explode(' ', $user['name'] ?? 'Pengurus')[0]; @endphp
<div class="flex min-h-screen">
    <x-sidebar active="monitoring" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 space-y-7">

            {{-- Hero Card --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
                {{-- gradient dekoratif --}}
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        Teaching Factory · Polines
                    </span>
                    <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                        Monitoring <span class="text-[#FFB800]">Progress Projek</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1.5">Pantau perkembangan real-time semua projek — dari timeline hingga beban kerja tim</p>
                </div>
            </div>

            {{-- Stat Cards --}}
            @php
            $dash = $dashboard ?? [];
            $projectsList = is_array($dash['projects'] ?? null) ? $dash['projects'] : [];
            $totalActive = count($projectsList);
            $avgProgress = $totalActive > 0 ? (int) round(array_sum(array_column($projectsList, 'progress')) / $totalActive) : 0;
            $needsAttention = count(array_filter($projectsList, fn($p) => ($p['progress'] ?? 0) < 50));
            $delayed = count(array_filter($projectsList, fn($p) => !empty($p['deadline']) && strtotime($p['deadline']) < time() && ($p['progress'] ?? 0) < 100));

            $monitoringStats = [
                ['value' => (string) $totalActive, 'label' => 'Projek Berjalan', 'color' => '#0B3B82', 'sub' => 'Dari database'],
                ['value' => (string) $needsAttention, 'label' => 'Perlu Perhatian', 'color' => '#F59E0B', 'sub' => 'Progress < 50%'],
                ['value' => (string) $delayed, 'label' => 'Terlambat', 'color' => '#EF4444', 'sub' => 'Lewat target deadline'],
                ['value' => $avgProgress . '%', 'label' => 'Rata-rata Progress', 'color' => '#16A34A', 'sub' => 'Dari ' . $totalActive . ' projek'],
            ];
            @endphp
            <div class="grid grid-cols-4 gap-4">
                @foreach($monitoringStats as $stat)
                <div class="bg-white rounded-xl px-5 py-5 border border-slate-100 shadow-sm">
                    <p class="text-3xl font-bold leading-none" style="color:{{ $stat['color'] }}">{{ $stat['value'] }}</p>
                    <p class="text-xs text-slate-400 mt-1.5 font-medium">{{ $stat['label'] }}</p>
                    <p class="text-[11px] mt-1 text-slate-500">{{ $stat['sub'] }}</p>
                </div>
                @endforeach
            </div>

            {{-- Monitoring Cards Grid --}}
            <div x-data="{ filter: 'all' }">
                <div class="flex items-center justify-between mb-4">
                    <p class="font-semibold text-[#1E293B] text-[15px]">Status Semua Projek</p>
                    <div class="flex gap-2">
                        <button type="button" @click="filter = 'all'" :class="filter === 'all' ? 'bg-[#0B3B82] text-white' : 'border border-slate-200 text-slate-500'" class="text-xs px-3 py-1.5 rounded-lg font-semibold transition-colors">Semua</button>
                        <button type="button" @click="filter = 'warning'" :class="filter === 'warning' ? 'bg-[#0B3B82] text-white' : 'border border-slate-200 text-slate-500'" class="text-xs px-3 py-1.5 rounded-lg font-semibold transition-colors">Perlu Perhatian</button>
                        <button type="button" @click="filter = 'ontrack'" :class="filter === 'ontrack' ? 'bg-[#0B3B82] text-white' : 'border border-slate-200 text-slate-500'" class="text-xs px-3 py-1.5 rounded-lg font-semibold transition-colors">On Track</button>
                    </div>
                </div>
                <div class="space-y-3">
                    @php
                    $pc = fn($p) => $p >= 70 ? '#16A34A' : ($p >= 40 ? '#0B3B82' : ($p >= 20 ? '#F59E0B' : '#EF4444'));
                    @endphp
                    @forelse($projectsList as $m)
                    @php
                        $prog = (int) ($m['progress'] ?? 0);
                        $isWarning = $prog < 50;
                        $statusLabel = $prog >= 70 ? 'On Track' : ($prog >= 40 ? 'Progres Stabil' : 'Perlu Perhatian');
                        $badgeBg = $prog >= 70 ? 'bg-green-50 text-green-700' : ($prog >= 40 ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700');
                        $dotColor = $prog >= 70 ? '#16A34A' : ($prog >= 40 ? '#0B3B82' : '#F59E0B');
                    @endphp
                    <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-6 py-5 hover:border-slate-200 hover:shadow-md transition-all"
                         x-show="filter === 'all' || (filter === 'warning' && {{ $isWarning ? 'true' : 'false' }}) || (filter === 'ontrack' && {{ !$isWarning ? 'true' : 'false' }})">
                        <div class="flex items-start gap-5">
                            <div class="flex-1">
                                <div class="flex items-center gap-2.5 mb-1.5">
                                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $dotColor }}"></span>
                                    <p class="font-bold text-[#1E293B] text-[14px]">{{ $m['title'] }}</p>
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $badgeBg }}">{{ $statusLabel }}</span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-0.5 rounded border border-slate-100">{{ $m['status'] }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-[12px] text-slate-500 mb-2">
                                    <span>👤 PM: <strong>{{ $m['pm_name'] ?? '—' }}</strong></span>
                                    <span>🏢 {{ $m['client_name'] ?? '—' }}</span>
                                    <span>🎓 {{ $m['student_count'] ?? 0 }} mahasiswa</span>
                                    <span>📅 Target: {{ $m['deadline'] ?? '—' }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2 flex-shrink-0 min-w-[140px]">
                                <div class="text-right">
                                    <p class="text-2xl font-black" style="color:{{ $pc($prog) }}">{{ $prog }}%</p>
                                    <p class="text-[11px] text-slate-400">progress tim</p>
                                </div>
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width:{{ $prog }}%;background:{{ $pc($prog) }}"></div>
                                </div>
                                <a href="{{ route('pengurus-tefa.validasi-proposal.detail', $m['id']) }}" class="text-[11px] font-semibold text-[#0B3B82] hover:underline">Detail Projek →</a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="bg-white rounded-xl border border-slate-100 p-8 text-center text-slate-400 text-xs">
                        Tidak ada projek yang sedang berjalan untuk dimonitor.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Timeline & Beban Tim --}}
            <div class="grid grid-cols-2 gap-6">
                {{-- Deadline Terdekat --}}
                @php
                $deadlines = collect($projectsList)
                    ->filter(fn($p) => !empty($p['deadline']))
                    ->sortBy('deadline')
                    ->take(5);
                @endphp
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
                    <p class="font-semibold text-[#1E293B] text-[15px] mb-4">Target Deadline Terdekat</p>
                    <div class="space-y-3">
                        @forelse($deadlines as $dl)
                        @php
                            $diff = round((strtotime($dl['deadline']) - time()) / 86400);
                            $isOverdue = $diff < 0;
                            $badgeColor = $isOverdue ? '#EF4444' : ($diff <= 14 ? '#F59E0B' : '#16A34A');
                            $sisaText = $isOverdue ? abs($diff) . ' hari terlewat' : ($diff == 0 ? 'Hari ini' : $diff . ' hari lagi');
                        @endphp
                        <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
                            <div>
                                <p class="text-[13px] font-semibold text-[#1E293B] leading-tight">{{ $dl['title'] }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ date('d M Y', strtotime($dl['deadline'])) }}</p>
                            </div>
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full" style="background:{{ $badgeColor }}15;color:{{ $badgeColor }}">{{ $sisaText }}</span>
                        </div>
                        @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Tidak ada target deadline tercatat.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Beban PM --}}
                @php
                $pmWorkload = collect($projectsList)
                    ->filter(fn($p) => !empty($p['pm_name']) && $p['pm_name'] !== '—')
                    ->groupBy('pm_name')
                    ->map(fn($group, $pm) => [
                        'pm'      => $pm,
                        'projek'  => $group->count(),
                        'mhs'     => $group->sum('student_count') . ' mhs',
                    ])
                    ->values();
                @endphp
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
                    <p class="font-semibold text-[#1E293B] text-[15px] mb-4">Beban Project Manager</p>
                    <div class="space-y-4">
                        @forelse($pmWorkload as $item)
                        @php
                            $parts = explode(' ', $item['pm']);
                            $initials = strtoupper(substr($parts[0] ?? 'P', 0, 1)) . strtoupper(substr($parts[1] ?? '', 0, 1));
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-[#0B3B82]/10 flex items-center justify-center text-[9px] font-bold text-[#0B3B82] flex-shrink-0">{{ $initials }}</div>
                                    <span class="text-[12px] font-semibold text-[#1E293B]">{{ $item['pm'] }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[11px] font-bold text-[#0B3B82]">{{ $item['projek'] }} projek</span>
                                    <span class="text-[11px] text-slate-400 ml-1.5">· {{ $item['mhs'] }}</span>
                                </div>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-[#0B3B82]" style="width:{{ min($item['projek'] * 50, 100) }}%"></div>
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Belum ada PM yang ditugaskan pada projek berjalan.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </main>
        <footer class="px-8 py-4 border-t border-slate-100 bg-white">
            <p class="text-[11px] text-slate-400 font-medium">© {{ date('Y') }} TEVARA · Politeknik Negeri Semarang · Teaching Factory</p>
        </footer>
    </div>
</div>
</body>
</html>
