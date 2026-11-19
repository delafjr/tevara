{{-- == ( halaman daftar projek saya - klien ) == --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Daftar Projek Saya - Pantau progres eksekusi teknis, evaluasi berkas deliverable, dan spesifikasi pengerjaan proyek Teaching Factory secara terpadu.">
    <title>Daftar Projek Saya - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen"
      x-data="{
          expanded: {},
          toggle(id) {
              this.expanded[id] = !this.expanded[id];
          }
      }">

@php
    $proposals = is_array($proposals ?? null) ? $proposals : [];
    $handoverProjects = $handoverProjects ?? [];
    $handoverProjectsAvailable = $handoverProjectsAvailable ?? false;
    $error = $error ?? null;
    $routePrefix = request()->routeIs('jurusan.*') ? 'jurusan' : 'klien';
    $uatReadyCount = collect($proposals)->filter(fn ($proposal) => is_array($proposal)
        && !empty($proposal['bast_eligible'])
        && empty($proposal['bast_prepared'])
        && empty($proposal['client_approved']))->count();

    $formatProjectDate = static fn ($value) => $value
        ? \Carbon\Carbon::parse($value)->translatedFormat('d M Y')
        : 'Belum ditentukan';
    $projectsData = collect($proposals)
        ->filter(fn ($proposal) => is_array($proposal) && !empty($proposal['project_code']))
        ->map(function ($proposal) use ($formatProjectDate) {
            $projectCode = (string) $proposal['project_code'];
            $targetDate = $proposal['target_completion_date'] ?? null;
            $daysRemaining = $targetDate
                ? \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($targetDate)->startOfDay(), false)
                : null;
            $pm = $proposal['pm'] ?? [];
            $pmName = $pm['name'] ?? 'Belum ditetapkan';
            $pmInitials = collect(explode(' ', trim($pmName)))
                ->filter()
                ->take(2)
                ->map(fn ($part) => mb_substr($part, 0, 1))
                ->implode('');
            $category = $proposal['category'] ?? 'Proyek';
            $categoryAndTitle = strtolower($category . ' ' . ($proposal['title'] ?? ''));
            $statusType = !empty($proposal['client_approved'])
                ? 'complete'
                : (!empty($proposal['bast_prepared'])
                    ? 'pending_approval'
                    : (!empty($proposal['bast_eligible']) ? 'uat' : 'running'));
            $modules = collect($proposal['modules'] ?? [])
                ->filter(fn ($module) => is_array($module))
                ->map(fn ($module) => [
                    'icon' => 'file',
                    'title' => $module['title'] ?? 'Modul',
                    'desc' => $module['description'] ?? 'Tidak ada deskripsi modul.',
                ])
                ->values()
                ->all();
            $students = collect($proposal['team_members'] ?? [])
                ->filter(fn ($member) => is_array($member))
                ->map(fn ($member) => [
                    'icon' => 'code',
                    'name' => $member['name'] ?? 'Mahasiswa',
                    'role' => $member['role'] ?? 'Anggota Tim',
                ])
                ->values()
                ->all();
            $displayStatus = $statusType === 'uat'
                ? 'Menunggu Pengurus menyiapkan BAST'
                : ($statusType === 'pending_approval'
                    ? 'Menunggu persetujuan PIC Klien'
                    : ($statusType === 'complete' ? 'Serah Terima Selesai' : ($proposal['status'] ?? 'Berjalan')));

            return [
                'id' => $projectCode,
                'proposal_id' => $proposal['id'],
                'category' => $category,
                'status' => $displayStatus,
                'status_type' => $statusType,
                'title' => $proposal['title'] ?? 'Proyek TEVARA',
                'icon_type' => str_contains($categoryAndTitle, 'mobile') ? 'mobile' : (str_contains($categoryAndTitle, 'iot') ? 'iot' : 'robot'),
                'dates' => $formatProjectDate($proposal['start_date'] ?? null) . ' — ' . $formatProjectDate($targetDate),
                'days_left' => $daysRemaining === null
                    ? 'Target belum ditentukan'
                    : ($daysRemaining >= 0 ? 'Sisa ' . $daysRemaining . ' Hari Kalender' : 'Terlambat ' . abs($daysRemaining) . ' Hari Kalender'),
                'progress' => max(0, min(100, (int) ($proposal['overall_progress'] ?? 0))),
                'bast_number' => $proposal['bast_number'] ?? null,
                'handover_date' => $proposal['handover_date'] ?? null,
                'repository_url' => $proposal['repository_url'] ?? null,
                'documentation_url' => $proposal['documentation_url'] ?? null,
                'user_manual_url' => $proposal['user_manual_url'] ?? null,
                'handover_notes' => $proposal['handover_notes'] ?? null,
                'handover_deliverables' => $proposal['handover_deliverables'] ?? [],
                'client_signoff_name' => $proposal['client_signoff_name'] ?? null,
                'maintenance' => $proposal['maintenance'] ?? [],
                'dosen' => [
                    'initials' => $pmInitials !== '' ? strtoupper($pmInitials) : 'PM',
                    'name' => $pmName,
                    'tag' => 'DOSEN PEMBIMBING / PM',
                    'nidn' => 'NIDN: ' . ($pm['nidn'] ?? '—') . ' • ' . ($pm['department'] ?? 'Jurusan belum diisi'),
                    'role_badge' => 'Penanggung Jawab Teknis',
                ],
                'modules' => $modules,
                'students' => $students,
            ];
        })
        ->values()
        ->all();
    $totalProjectCount = count($projectsData);
    $activeProjectCount = count(array_filter($projectsData, fn ($project) => $project['status_type'] === 'running'));
    $averageProjectProgress = $totalProjectCount > 0
        ? round(array_sum(array_column($projectsData, 'progress')) / $totalProjectCount, 1)
        : 0;
    $totalModuleCount = array_sum(array_map(fn ($project) => count($project['modules']), $projectsData));
@endphp

<div class="flex min-h-screen">

    {{-- == ( sidebar komponen DRY ) == --}}
    <x-sidebar active="projek-saya" />

    {{-- == ( konten utama ) == --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen">

        <main class="flex-1 px-8 py-7 space-y-6">

            {{-- == ( header card : judul halaman & deskripsi ) == --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#EFF6FF] via-[#F3F7FD] to-[#FCF8EC] border border-blue-100/70 px-8 py-7 shadow-xs">
                <div class="relative z-10 max-w-4xl">
                    <h1 class="text-2xl lg:text-[28px] font-extrabold text-[#1E293B] tracking-tight">
                        Daftar <span class="text-[#FFB800]">Projek Saya</span>
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-2 leading-relaxed font-normal">
                        Pantau progres eksekusi teknis, evaluasi berkas deliverable, dan spesifikasi pengerjaan proyek Teaching Factory secara terpadu.
                    </p>
                </div>
            </div>

            @if(session('success'))
                <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(!empty($errors) && $errors->any())
                <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold">Persetujuan BAST belum dapat dikirim:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach($errors->all() as $errItem)
                            <li>{{ $errItem }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($routePrefix === 'klien')
            <section class="border-b border-slate-200 pb-5" aria-labelledby="bast-ready-title">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-2">
                    <div>
                        <h2 id="bast-ready-title" class="text-sm font-bold text-slate-800">Persetujuan BAST</h2>
                        <p class="mt-1 text-xs text-slate-500">Pengurus TeFa menyiapkan dokumen. Periksa dan setujui BAST di sini.</p>
                    </div>
                    <span class="text-xs font-medium text-slate-500">{{ count($handoverProjects) }} menunggu persetujuan</span>
                </div>
                @if(count($handoverProjects))
                    <div class="mt-3 divide-y divide-slate-100 border-y border-slate-100">
                        @foreach($handoverProjects as $handoverProjectItem)
                            <article class="flex flex-col gap-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-800">{{ $handoverProjectItem['title'] ?? 'Proyek' }}</p>
                                    <p class="mt-1 font-mono text-[11px] text-slate-500">{{ $handoverProjectItem['bast_number'] ?? 'Nomor BAST belum tersedia' }} · {{ $handoverProjectItem['project_code'] ?? '' }}</p>
                                    <p class="mt-2 text-xs text-slate-500">Dokumen serah terima sudah disiapkan. Silakan tinjau daftar hasil/barang sebelum menyetujui.</p>
                                </div>
                                <a href="{{ route('klien.projek-saya.bast.review', $handoverProjectItem['id']) }}" class="inline-flex shrink-0 justify-center rounded-lg bg-[#0B3B82] px-4 py-2.5 text-xs font-semibold text-white hover:bg-[#082d64]">Tinjau BAST</a>
                            </article>
                        @endforeach
                    </div>
                @elseif($handoverProjectsAvailable)
                    <p class="mt-3 text-xs text-slate-500">
                        @if($uatReadyCount > 0)
                            Ada {{ $uatReadyCount }} proyek dengan UAT lulus yang menunggu Pengurus TeFa menyiapkan BAST.
                        @else
                            Belum ada BAST yang menunggu persetujuan PIC Klien.
                        @endif
                    </p>
                @else
                    <p class="mt-3 text-xs text-amber-800">Data kelayakan BAST belum dapat dimuat dari backend.</p>
                @endif
            </section>
            @endif

            {{-- == ( 3 kartu ringkasan metrik statistik ) == --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                {{-- Kartu 1: Total Proyek Portofolio --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6 flex flex-col justify-between hover:shadow-md transition">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400 mb-3">TOTAL PROYEK PORTOFOLIO</p>
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl font-bold text-slate-800">{{ $totalProjectCount }} Proyek</span>
                            <span class="text-[11px] font-semibold text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-0.5 rounded-full inline-flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                                {{ $activeProjectCount }} Aktif
                            </span>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                            {{-- Bento Grid Icon --}}
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                                <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Kartu 2: Rata - Rata Progres --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6 flex flex-col justify-between hover:shadow-md transition">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400 mb-3">RATA - RATA PROGRES</p>
                    <div class="flex items-center justify-between gap-3">
                        <div class="space-y-1.5">
                            <span class="text-2xl font-bold text-slate-800">{{ number_format($averageProjectProgress, 1) }}%</span>
                            <div class="w-28 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#0B3B82] rounded-full" style="width: {{ $averageProjectProgress }}%"></div>
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                            {{-- Trending Up Icon --}}
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Kartu 3: Distribusi Fase Pengerjaan --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6 flex flex-col justify-between hover:shadow-md transition">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400 mb-3">DISTRIBUSI FASE PENGERJAAN</p>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <span class="text-2xl font-bold text-slate-800">{{ $totalModuleCount }} Modul</span>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                            {{-- Clipboard / Checklist Icon --}}
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <p class="text-sm font-medium leading-relaxed">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error') || ($error ?? null))
                <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                    <p class="text-sm font-medium leading-relaxed">{{ session('error') ?? $error }}</p>
                </div>
            @endif

            {{-- == ( Proposal & Pengajuan Proyek Klien dari Backend ) == --}}
            @if(!empty($proposals))
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#0B3B82]"></span>
                            <h2 class="text-sm font-bold text-slate-800">Proposal &amp; Pengajuan Proyek Anda</h2>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-[#EAF3FF] text-[#0B3B82]">{{ count($proposals) }}</span>
                        </div>
                        <a href="{{ route($routePrefix . '.ajukan-proyek') }}" class="text-xs font-semibold text-[#0B3B82] hover:underline inline-flex items-center gap-1">
                            + Ajukan Proyek Baru
                        </a>
                    </div>

                    @foreach($proposals as $prop)
                        @php
                            $propStatus = (string) ($prop['status'] ?? '-');
                            $propStatusId = (int) ($prop['status_id'] ?? 0);
                            $isPropRevision = $propStatusId === 4 || str_contains(strtolower($propStatus), 'revision') || str_contains(strtolower($propStatus), 'improvement');
                            $formatBudget = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
                        @endphp
                        <div class="bg-white rounded-2xl border {{ $isPropRevision ? 'border-amber-300 ring-2 ring-amber-200/50' : 'border-slate-100' }} shadow-xs p-6 space-y-4">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="space-y-1 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-bold text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-0.5 rounded-md font-mono">
                                            {{ $prop['proposal_code'] ?? 'PRSL-' . $prop['id'] }}
                                        </span>
                                        <span class="text-[11px] font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-md">
                                            {{ $prop['category'] ?? 'Web' }}
                                        </span>
                                        @if($isPropRevision)
                                            <span class="text-xs font-bold text-amber-800 bg-amber-100 px-3 py-0.5 rounded-full border border-amber-300 inline-flex items-center gap-1.5 animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                                Improvement (Revisi)
                                            </span>
                                        @else
                                            <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">
                                                {{ $propStatus }}
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-base font-bold text-slate-800 leading-snug">
                                        {{ $prop['title'] ?? '-' }}
                                    </h3>
                                    <div class="flex items-center gap-4 text-xs text-slate-400 pt-1 flex-wrap">
                                        <span>Estimasi Anggaran: <strong class="text-slate-700">{{ $formatBudget($prop['estimated_budget'] ?? 0) }}</strong></span>
                                        <span>&bull;</span>
                                        <span>Deadline: <strong class="text-slate-700">{{ $prop['desired_deadline'] ?? '-' }}</strong></span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 flex-shrink-0">
                                    @if(!empty($prop['contract_file_url']))
                                        <a href="{{ route('dokumen.spk', $prop['id']) }}"
                                           class="inline-flex items-center gap-2 rounded-xl border border-[#0B3B82]/20 bg-[#EAF3FF] px-4 py-2.5 text-xs font-bold text-[#0B3B82] hover:bg-[#DBEAFE] transition"
                                           title="Unduh dokumen SPK/kontrak proyek">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                            </svg>
                                            Unduh SPK
                                        </a>
                                    @endif
                                    <a href="{{ route('klien.projek-saya.detail', $prop['id']) }}"
                                       class="inline-flex items-center gap-2 rounded-xl {{ $isPropRevision ? 'bg-amber-600 hover:bg-amber-700 text-white shadow-sm shadow-amber-600/30' : 'bg-[#0B3B82] hover:bg-[#082a5c] text-white' }} px-5 py-2.5 text-xs font-bold transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                        </svg>
                                        Detail Projek Saya {{ $isPropRevision ? '(Perlu Revisi)' : '' }}
                                    </a>
                                </div>
                            </div>

                            @if($isPropRevision && !empty($prop['review_notes']))
                                <div class="bg-amber-50/90 border border-amber-200 rounded-xl p-3.5 text-xs text-amber-950 flex items-start gap-3">
                                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                                    </svg>
                                    <div class="space-y-1 flex-1">
                                        <p class="font-bold text-amber-900">Catatan Revisi dari Pengurus TEFA:</p>
                                        <p class="leading-relaxed text-slate-800">{{ $prop['review_notes'] }}</p>
                                        <p class="text-[11px] text-amber-800/80 pt-1 font-medium">Klik tombol <strong>Detail Projek Saya</strong> di atas untuk mengubah harga/data yang diminta.</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        </main>

    </div>

</div>

</body>
</html>
