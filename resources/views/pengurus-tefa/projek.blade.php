<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Projek - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php $user = $user ?? session('user'); $firstName = explode(' ', $user['name'] ?? 'Pengurus')[0]; @endphp
<div class="flex min-h-screen">
    <x-sidebar active="projek" />
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
                        Kelola Semua <span class="text-[#FFB800]">Projek TeFa</span>
                    </h1>
                    <p class="text-sm text-[#64748B] mt-1.5 max-w-2xl leading-relaxed">
                        Pantau status, progress, dan tim untuk setiap projek yang berjalan.
                    </p>
                </div>

                <div class="relative z-10 flex-shrink-0">
                    <a href="#" class="flex items-center gap-2 bg-[#0B3B82] text-white font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-[#082a5c] transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah Projek
                    </a>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-4 gap-4 items-stretch">
                @php
                $dash = $dashboard ?? [];
                $totalProjects   = (int) ($dash['total_projects'] ?? 0);
                $runningProjects = (int) ($dash['running_projects'] ?? 0);
                $inReview        = (int) ($dash['in_review_projects'] ?? 0);
                $submitted       = (int) ($dash['submitted_proposals'] ?? 0);

                $projStats = [
                    ['label' => 'Total Projek',     'value' => (string) $totalProjects,   'sub' => 'Seluruh Projek',  'color' => '#0B3B82', 'icon' => 'folder'],
                    ['label' => 'Sedang Berjalan',  'value' => (string) $runningProjects, 'sub' => 'Dalam Pengerjaan','color' => '#0B3B82', 'icon' => 'play'],
                    ['label' => 'Dalam Review',     'value' => (string) $inReview,        'sub' => 'Perlu Review',    'color' => '#F59E0B', 'icon' => 'clock-file'],
                    ['label' => 'Proposal Masuk',   'value' => (string) $submitted,       'sub' => 'Menunggu Validasi','color' => '#0B3B82', 'icon' => 'check'],
                ];
                @endphp
                @foreach($projStats as $stat)
                    <x-stat-card :label="$stat['label']" :value="$stat['value']" :sub="$stat['sub']" :color="$stat['color']" :icon="$stat['icon']" />
                @endforeach
            </div>

            {{-- Filter + Tabel --}}
            @php
            $projectsList = is_array($dash['projects'] ?? null) ? $dash['projects'] : [];
            $ss = [
                'active'   => 'bg-blue-50 text-blue-700',
                'berjalan' => 'bg-blue-50 text-blue-700',
                'testing'  => 'bg-purple-50 text-purple-700',
                'review'   => 'bg-amber-50 text-amber-700',
                'handover' => 'bg-emerald-50 text-emerald-700',
                'selesai'  => 'bg-green-50 text-green-700',
                'completed'=> 'bg-green-50 text-green-700',
                'proposal' => 'bg-purple-50 text-purple-700',
            ];
            $pc = fn($p) => $p >= 100 ? '#16A34A' : ($p >= 60 ? '#0B3B82' : ($p >= 30 ? '#F59E0B' : '#DC2626'));
            @endphp
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden" x-data="{ search: '', statusFilter: 'Semua Status' }">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-[#1E293B] text-[15px]">Daftar Projek</p>
                        <p class="text-xs text-slate-400 mt-0.5">Semua projek yang terdaftar di database</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="search" placeholder="Cari projek..." class="text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0B3B82] w-48">
                        <select x-model="statusFilter" class="text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0B3B82]">
                            <option>Semua Status</option>
                            <option>Active</option>
                            <option>Testing</option>
                            <option>Handover</option>
                            <option>Completed</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-6 py-3">Nama Projek</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Klien / Mitra</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">PM / Dosen</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Mahasiswa</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Status</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3 w-32">Progress</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Deadline</th>
                                <th class="text-center text-[11px] font-semibold text-slate-400 px-3 py-3 w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($projectsList as $p)
                            @php
                                $statusKey = strtolower($p['status'] ?? 'other');
                                $badgeClass = $ss[$statusKey] ?? 'bg-slate-100 text-slate-600';
                                $prog = (int) ($p['progress'] ?? 0);
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors"
                                x-show="(statusFilter === 'Semua Status' || statusFilter.toLowerCase() === '{{ $statusKey }}') && (!search || '{{ strtolower(addslashes($p['title'] ?? '')) }}'.includes(search.toLowerCase()) || '{{ strtolower(addslashes($p['client_name'] ?? '')) }}'.includes(search.toLowerCase()))">
                                <td class="px-6 py-3.5">
                                    <a href="{{ route('pengurus-tefa.projek.detail', $p['id']) }}" class="font-semibold text-[#1E293B] hover:text-[#0B3B82] text-[13px] leading-tight block transition-colors">
                                        {{ $p['title'] }}
                                    </a>
                                    <p class="text-[11px] text-slate-400 mt-0.5 capitalize">{{ $p['category'] ?? 'Lainnya' }}</p>
                                </td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-600">{{ $p['client_name'] ?? '—' }}</td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-600 font-medium">{{ $p['pm_name'] ?? '—' }}</td>
                                <td class="px-3 py-3.5"><span class="text-[12px] font-semibold text-[#0B3B82]">{{ $p['student_count'] ?? 0 }} mhs</span></td>
                                <td class="px-3 py-3.5"><span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $badgeClass }}">{{ $p['status'] }}</span></td>
                                <td class="px-3 py-3.5 w-32">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full" style="width:{{ $prog }}%;background:{{ $pc($prog) }}"></div>
                                        </div>
                                        <span class="text-[11px] font-semibold text-slate-500 w-7 text-right">{{ $prog }}%</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-500">{{ $p['deadline'] ?? '—' }}</td>
                                <td class="px-3 py-3.5 text-center">
                                    <a href="{{ route('pengurus-tefa.projek.detail', $p['id']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#EAF3FF] text-[#0B3B82] hover:bg-[#0B3B82] hover:text-white text-[11px] font-bold transition-all" title="Lihat Detail Projek">
                                        <span>Detail</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400 text-xs">
                                    Belum ada projek aktif yang berjalan di database.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3.5 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-400">Menampilkan {{ count($projectsList) }} projek</p>
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
