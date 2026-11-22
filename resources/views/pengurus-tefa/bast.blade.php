<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Serah Terima &amp; BAST - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user');
    $projects = is_array($projects ?? null) ? $projects : [];
    $eligibleProjects = collect($projects)->filter(fn ($project) => !empty($project['bast_eligible']) && empty($project['bast_prepared']))->count();
    $pendingApprovals = collect($projects)->filter(fn ($project) => !empty($project['bast_prepared']))->count();
    $completedHandovers = collect($projects)->filter(fn ($project) => !empty($project['client_approved']))->count();
@endphp
<div class="flex min-h-screen">
    <x-sidebar active="bast" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 space-y-7" x-data="{ searchQuery: '' }">
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
                {{-- gradient dekoratif --}}
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        Teaching Factory · Polines
                    </span>
                    <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                        Serah Terima &amp; <span class="text-[#FFB800]">BAST</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1.5">Kelola persiapan dokumen serah terima proyek dan persetujuan BAST bersama klien</p>
                </div>
            </div>

            @if(session('success'))
                <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(!empty($errors) && $errors->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                    <p class="font-semibold">BAST belum berhasil disimpan:</p>
                    <ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $errorItem)<li>{{ $errorItem }}</li>@endforeach</ul>
                </div>
            @endif
            @if(session('error'))
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">{{ session('error') }}</div>
            @endif
            @if(!empty($error))
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">{{ $error }}</div>
            @endif

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3" aria-label="Ringkasan BAST">
                <x-stat-card label="Siap Disiapkan" :value="$eligibleProjects" sub="UAT terakhir lulus" color="#0B3B82" icon="play" />
                <x-stat-card label="Menunggu PIC Klien" :value="$pendingApprovals" sub="BAST sudah disiapkan" color="#F59E0B" icon="clock-file" />
                <x-stat-card label="Serah Terima Selesai" :value="$completedHandovers" sub="Telah disetujui klien" color="#16A34A" icon="check" />
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-[15px] font-semibold text-[#1E293B]">Daftar Proyek</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Status UAT dan persetujuan BAST tersinkron dengan database.</p>
                    </div>
                    <label class="block w-full sm:w-80">
                        <span class="sr-only">Cari proyek, klien, atau kode proyek</span>
                        <input type="search" x-model.debounce.150ms="searchQuery" placeholder="Cari proyek atau klien"
                               class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[#0B3B82] focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10">
                    </label>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="px-6 py-3 text-left text-[11px] font-semibold text-slate-400">Proyek</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400">Klien</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400">UAT terakhir</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400">Status BAST</th>
                                <th class="px-6 py-3 text-right text-[11px] font-semibold text-slate-400">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($projects as $project)
                                @php
                                    $projectCode = $project['project_code'] ?? ('Proposal #' . ($project['id'] ?? '—'));
                                    $searchable = strtolower($projectCode . ' ' . ($project['title'] ?? '') . ' ' . ($project['client_name'] ?? ''));
                                    $isApproved = !empty($project['client_approved']);
                                    $isPrepared = !empty($project['bast_prepared']);
                                    $isEligible = !empty($project['bast_eligible']);
                                @endphp
                                <tr data-search="{{ $searchable }}" x-show="!searchQuery || $el.dataset.search.includes(searchQuery.toLowerCase().trim())">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-[#1E293B]">{{ $project['title'] ?? 'Proyek tanpa judul' }}</p>
                                        <p class="mt-1 font-mono text-[11px] text-slate-400">{{ $projectCode }}</p>
                                    </td>
                                    <td class="px-3 py-4 text-xs text-slate-600">{{ $project['client_name'] ?? '—' }}</td>
                                    <td class="px-3 py-4 text-xs text-slate-600">{{ $project['latest_uat_result'] ?? 'Belum ada hasil' }}</td>
                                    <td class="px-3 py-4">
                                        @if($isApproved)
                                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Disetujui PIC</span>
                                        @elseif($isPrepared)
                                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">Menunggu PIC</span>
                                        @elseif($isEligible)
                                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700">Siap disiapkan</span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">Menunggu UAT lulus</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if(!$isApproved && $isEligible)
                                            <a href="{{ route('pengurus-tefa.bast.prepare.form', $project['id']) }}" class="inline-flex rounded-lg bg-[#0B3B82] px-3.5 py-2 text-xs font-semibold text-white hover:bg-[#082d64]">
                                                {{ $isPrepared ? 'Perbarui BAST' : 'Siapkan BAST' }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada data proyek di database.</td></tr>
                            @endforelse
                            <tr x-cloak x-show="searchQuery && Array.from($el.parentElement.querySelectorAll('tr[data-search]')).every(row => !row.dataset.search.includes(searchQuery.toLowerCase().trim()))">
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Tidak ada proyek yang sesuai dengan pencarian.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
        <footer class="border-t border-slate-100 bg-white px-8 py-4">
            <p class="text-[11px] font-medium text-slate-400">© {{ date('Y') }} TEVARA · Politeknik Negeri Semarang · Teaching Factory</p>
        </footer>
    </div>
</div>
</body>
</html>
