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
                'user_manual_file_url' => $proposal['user_manual_file_url'] ?? null,
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

            {{-- == ( hero card : judul halaman & deskripsi ) == --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
                {{-- gradient dekoratif --}}
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        Workspace Klien
                    </span>
                    <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                        Daftar <span class="text-[#FFB800]">Projek Saya</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1.5">Pantau progres eksekusi teknis, evaluasi deliverable, dan spesifikasi pengerjaan proyek</p>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div role="status" class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <p class="text-sm font-medium leading-relaxed">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error') || ($error ?? null))
                <div role="alert" class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    <p class="text-sm font-medium leading-relaxed">{{ session('error') ?? $error }}</p>
                </div>
            @endif
            @if(!empty($errors) && $errors->any())
                <div role="alert" class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                    <p class="font-semibold">Persetujuan BAST belum dapat dikirim:</p>
                    <ul class="mt-1 list-disc pl-5 space-y-0.5">
                        @foreach($errors->all() as $errItem)
                            <li>{{ $errItem }}</li>
                        @endforeach
                    </ul>
                </div>
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
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/>
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
                            <p class="text-xs text-slate-400 mt-1">Total deliverable proyek</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>

            {{-- == ( Persetujuan BAST - Posisi di bawah 3 card metrik & berdesain highlight ) == --}}
            @if($routePrefix === 'klien')
            <section class="space-y-3" aria-labelledby="bast-ready-title">
                @if(count($handoverProjects))
                    {{-- HIGHLIGHT CARD JIKA ADA BAST YANG PERLU DISETUJUI --}}
                    <div class="relative overflow-hidden rounded-2xl border-2 border-emerald-400 bg-gradient-to-br from-emerald-50/90 via-white to-blue-50/70 p-6 sm:p-7 shadow-md ring-4 ring-emerald-100/60 transition-all">
                        {{-- Aksen dekoratif latar belakang --}}
                        <div class="pointer-events-none absolute -right-12 -top-12 h-44 w-44 rounded-full bg-emerald-200/30 blur-2xl"></div>
                        <div class="pointer-events-none absolute right-1/3 -bottom-10 h-32 w-32 rounded-full bg-blue-200/30 blur-xl"></div>

                        {{-- Header Persetujuan BAST --}}
                        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-emerald-200/70 pb-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-300 bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900 shadow-2xs">
                                        <span class="h-2 w-2 rounded-full bg-amber-500 animate-ping"></span>
                                        Tindakan Diperlukan
                                    </span>
                                    <h2 id="bast-ready-title" class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Persetujuan BAST</h2>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600">Pengurus TeFa menyiapkan dokumen. Periksa dan setujui BAST di sini untuk menyelesaikan serah terima proyek.</p>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-white px-3.5 py-1.5 text-xs font-bold text-emerald-800 shadow-2xs">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    {{ count($handoverProjects) }} menunggu persetujuan
                                </span>
                            </div>
                        </div>

                        {{-- Daftar Proyek BAST Siap Disetujui --}}
                        <div class="relative z-10 mt-4 space-y-3.5">
                            @foreach($handoverProjects as $handoverProjectItem)
                                <article class="flex flex-col gap-4 rounded-xl border border-emerald-200/90 bg-white p-5 shadow-xs hover:border-emerald-400 hover:shadow-sm transition-all sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-start gap-4">
                                        <div class="hidden sm:flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 ring-1 ring-emerald-300/50">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                            </svg>
                                        </div>
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-mono text-xs font-bold text-[#0B3B82] bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-md">
                                                    {{ $handoverProjectItem['bast_number'] ?? 'Nomor BAST belum tersedia' }}
                                                </span>
                                                @if(!empty($handoverProjectItem['project_code']))
                                                    <span class="font-mono text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                                                        {{ $handoverProjectItem['project_code'] }}
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700">
                                                    ✓ Siap Disetujui PIC
                                                </span>
                                            </div>
                                            <h3 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">
                                                {{ $handoverProjectItem['title'] ?? 'Proyek' }}
                                            </h3>
                                            <p class="text-xs text-slate-500 leading-relaxed max-w-2xl">
                                                Dokumen serah terima sudah disiapkan. Silakan tinjau daftar hasil/barang sebelum menyetujui.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center sm:justify-end flex-shrink-0 pt-2 sm:pt-0">
                                        <a href="{{ route('klien.projek-saya.bast.review', $handoverProjectItem['id']) }}"
                                           class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 px-5 py-3 text-xs sm:text-sm font-bold text-white shadow-sm hover:shadow-md transition-all ring-2 ring-emerald-500/20">
                                            <span>Tinjau BAST</span>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                            </svg>
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- TAMPILAN KETIKA BELUM ADA BAST YANG MENUNGGU PERSETUJUAN --}}
                    <div class="rounded-2xl border border-slate-100 bg-white p-5 sm:p-6 shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3.5">
                            <div>
                                <h2 id="bast-ready-title" class="text-sm font-bold text-slate-800">Persetujuan BAST</h2>
                                <p class="mt-0.5 text-xs text-slate-500">Pengurus TeFa menyiapkan dokumen. Periksa dan setujui BAST di sini.</p>
                            </div>
                            <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">
                                0 menunggu persetujuan
                            </span>
                        </div>
                        <div class="pt-3.5 flex items-center gap-2.5 text-xs text-slate-500">
                            <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                            </svg>
                            <p>
                                @if($uatReadyCount > 0)
                                    Ada {{ $uatReadyCount }} proyek dengan UAT lulus yang menunggu Pengurus TeFa menyiapkan BAST.
                                @elseif($handoverProjectsAvailable)
                                    Belum ada BAST yang menunggu persetujuan PIC Klien.
                                @else
                                    Data kelayakan BAST belum dapat dimuat dari backend.
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
            </section>
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

            {{-- == ( daftar kartu proyek aktif ) == --}}
            <div class="space-y-4">

                @forelse($projectsData as $proj)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs transition hover:shadow-md overflow-hidden">

                    {{-- == CARD HEADER (COLLAPSED / EXPANDED TOP BAR) == --}}
                    <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-5">

                        <div class="flex items-start gap-4 flex-1">
                            {{-- Project Icon --}}
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 shadow-xs
                                        bg-[#EAF3FF] text-[#0B3B82]">
                                @if($proj['icon_type'] === 'iot')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                        <path d="M4.93 19.07a10 10 0 0 1 0-14.14M19.07 4.93a10 10 0 0 1 0 14.14M7.76 16.24a6 6 0 0 1 0-8.48M16.24 7.76a6 6 0 0 1 0 8.48"/>
                                        <circle cx="12" cy="12" r="2"/>
                                    </svg>
                                @elseif($proj['icon_type'] === 'mobile')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                        <rect width="14" height="20" x="5" y="2" rx="3"/>
                                        <path d="M12 18h.01"/>
                                    </svg>
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                        <path d="M4 20h16M7 20v-2a2 2 0 0 1 2-2h1.5a2 2 0 0 0 1.8-1.1l2.4-4.8A2 2 0 0 1 16.5 9h1.5M18 6l2 3-2 3M15 9h5"/>
                                    </svg>
                                @endif
                            </div>

                            {{-- Project Basic Info --}}
                            <div class="space-y-1.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-bold text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-0.5 rounded-md font-mono">
                                        {{ $proj['id'] }}
                                    </span>
                                    @if($proj['status_type'] === 'uat')
                                        <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full inline-flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $proj['status'] }}
                                        </span>
                                    @else
                                        <span class="text-xs font-semibold text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-0.5 rounded-full inline-flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                                            {{ $proj['status'] }}
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-base md:text-[17px] font-bold text-slate-800 leading-snug">
                                    {{ $proj['title'] }}
                                </h2>

                                {{-- If collapsed, show Dosen PM line --}}
                                <div x-show="!expanded['{{ $proj['id'] }}']" class="pt-0.5">
                                    <p class="text-xs text-slate-500">
                                        Dosen PM: <span class="font-medium text-slate-700">{{ $proj['dosen']['name'] }}</span>
                                    </p>
                                </div>

                                {{-- If expanded, show date & countdown badge --}}
                                <div x-show="expanded['{{ $proj['id'] }}']" class="flex items-center gap-3 pt-1 flex-wrap">
                                    <span class="text-xs text-slate-500 font-medium inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5"/>
                                        </svg>
                                        {{ $proj['dates'] }}
                                    </span>
                                    <span class="text-[11px] font-semibold text-[#0B3B82] bg-[#EAF3FF] px-2.5 py-0.5 rounded-md inline-flex items-center gap-1.5">
                                        <svg class="w-3 h-3 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                        {{ $proj['days_left'] }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Right Side Actions & Progress --}}
                        <div class="flex items-center justify-between md:justify-end gap-6 flex-shrink-0">

                            {{-- Mini Progress (Shown when collapsed) --}}
                            <div x-show="!expanded['{{ $proj['id'] }}']" class="space-y-1 text-right">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-slate-400">Progres Proyek</span>
                                    <span class="text-sm font-bold text-slate-800">{{ $proj['progress'] }}%</span>
                                </div>
                                <div class="w-32 md:w-36 h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#0B3B82] rounded-full" style="width: {{ $proj['progress'] }}%"></div>
                                </div>
                            </div>

                            @if($routePrefix === 'klien')
                                <a href="{{ route('klien.projek-saya.uat', $proj['proposal_id']) }}"
                                   class="bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold px-3.5 py-2 rounded-xl inline-flex items-center gap-1.5 transition flex-shrink-0 shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 2h7M7 16h10"/>
                                    </svg>
                                    Lembar UAT
                                </a>
                            @endif

                            {{-- Accordion Toggle Button --}}
                            <button type="button"
                                    @click="toggle('{{ $proj['id'] }}')"
                                    class="bg-[#EAF3FF] hover:bg-[#D9E9FF] text-[#0B3B82] text-xs font-semibold px-4 py-2 rounded-xl inline-flex items-center gap-1.5 transition cursor-pointer flex-shrink-0">
                                <span x-text="expanded['{{ $proj['id'] }}'] ? 'Tutup Detail' : 'Lihat Detail'"></span>
                                <svg class="w-3.5 h-3.5 transition-transform duration-200"
                                     :class="expanded['{{ $proj['id'] }}'] ? 'rotate-180' : ''"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>

                        </div>

                    </div>

                    {{-- == CARD EXPANDED BODY (DETAILED SPECS & TIM) == --}}
                    <div x-show="expanded['{{ $proj['id'] }}']"
                         x-collapse
                         class="px-6 pb-6 pt-2 border-t border-slate-100/80 space-y-6">

                        {{-- 1. Progres Eksekusi Keseluruhan --}}
                        <div class="space-y-2 pt-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/>
                                    </svg>
                                    <span>Progres Eksekusi Keseluruhan</span>
                                </div>
                                <span class="text-base font-bold text-[#0B3B82]">{{ $proj['progress'] }}%</span>
                            </div>
                            <div class="h-2.5 w-full bg-[#DDE9FA] rounded-full overflow-hidden">
                                <div class="h-full bg-[#0B3B82] rounded-full transition-all duration-500"
                                     style="width: {{ $proj['progress'] }}%"></div>
                            </div>
                        </div>

                        {{-- 2. Catatan & Spesifikasi Modul Proyek --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm font-bold text-slate-800">
                                    <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <rect width="18" height="18" x="3" y="3" rx="2"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/>
                                    </svg>
                                    <span>Catatan & Spesifikasi Modul Proyek</span>
                                </div>
                                <span class="text-xs text-slate-400 font-medium">
                                    {{ count($proj['modules']) }} Modul Terdaftar dalam TOR
                                </span>
                            </div>

                            <div class="space-y-3">
                                @foreach($proj['modules'] as $mod)
                                <div class="bg-[#F8FAFD] rounded-xl border border-slate-100 p-4 space-y-1 hover:bg-[#F2F6FC] transition">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-[#0B3B82] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                        </svg>
                                        <h3 class="text-xs md:text-sm font-bold text-slate-800">
                                            {{ $mod['title'] }}
                                        </h3>
                                    </div>
                                    <p class="text-xs text-slate-500 leading-relaxed pl-6">
                                        {{ $mod['desc'] }}
                                    </p>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- 3. Tim Pengembang Mahasiswa & Dosen Pembimbing --}}
                        <div class="bg-[#F0F5FD] rounded-2xl border border-blue-100/70 p-5 space-y-4">
                            <h4 class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">
                                TIM PENGEMBANG MAHASISWA &amp; DOSEN PEMBIMBING
                            </h4>

                            {{-- Dosen Pembimbing Card --}}
                            <div class="bg-white rounded-xl border border-blue-100/60 p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-full bg-[#0E1F40] text-white font-bold text-sm flex items-center justify-center flex-shrink-0 shadow-xs">
                                        {{ $proj['dosen']['initials'] }}
                                    </div>
                                    <div class="space-y-0.5">
                                        <p class="text-[10px] font-bold text-[#0B3B82] uppercase tracking-wide">
                                            {{ $proj['dosen']['tag'] }}
                                        </p>
                                        <h5 class="text-sm font-bold text-slate-800">
                                            {{ $proj['dosen']['name'] }}
                                        </h5>
                                        <p class="text-xs text-slate-400 font-mono">
                                            {{ $proj['dosen']['nidn'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex-shrink-0">
                                    <span class="bg-white border border-blue-200/80 text-[#0B3B82] text-xs font-semibold px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                                        <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                                        </svg>
                                        {{ $proj['dosen']['role_badge'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Students Grid (2x2) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($proj['students'] as $stu)
                                <div class="bg-white rounded-xl border border-blue-100/50 p-3.5 flex items-center gap-3.5 shadow-xs">
                                    <div class="w-8 h-8 rounded-lg bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-[#0B3B82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/>
                                        </svg>
                                    </div>
                                    <div class="space-y-0.5 min-w-0">
                                        <h6 class="text-xs font-bold text-slate-800 truncate">{{ $stu['name'] }}</h6>
                                        <p class="text-[11px] text-slate-400 font-medium truncate">{{ $stu['role'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                        </div>

                        @if($proj['status_type'] === 'uat' && $routePrefix === 'klien')
                        <section class="border-t border-slate-100 pt-5 space-y-4" aria-labelledby="bast-title-{{ $proj['id'] }}">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <h3 id="bast-title-{{ $proj['id'] }}" class="text-sm font-bold text-slate-800">Berita Acara Serah Terima (BAST)</h3>
                                    <p class="text-xs text-slate-500 mt-1">UAT telah lulus. BAST akan disiapkan oleh Pengurus TeFa dan muncul di bagian Persetujuan BAST.</p>
                                </div>
                                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    UAT Lulus
                                </span>
                            </div>
                        </section>
                        @endif
                        @if($proj['status_type'] === 'complete')
                            <section class="border-t border-slate-100 pt-5 space-y-3">
                                <h3 class="text-sm font-bold text-slate-800">Berita Acara Serah Terima</h3>
                                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                            <h4 class="text-xs font-bold text-slate-800">Hasil/barang yang diserahterimakan</h4>
                                            @if(!empty($proj['handover_deliverables']))
                                                <ul class="mt-2 divide-y divide-slate-200">
                                                    @foreach($proj['handover_deliverables'] as $deliverable)
                                                        <li class="py-2 text-xs">
                                                            <p class="font-semibold text-slate-800">{{ $deliverable['name'] ?? 'Hasil/barang' }} <span class="font-normal text-slate-500">· {{ $deliverable['quantity'] ?? 1 }} {{ $deliverable['unit'] ?? 'unit' }}</span></p>
                                                            <p class="mt-0.5 text-slate-500">{{ ['physical' => 'Barang fisik', 'digital' => 'Produk digital', 'document' => 'Dokumen', 'other' => 'Lainnya'][$deliverable['type'] ?? 'other'] ?? 'Lainnya' }} · Kondisi: {{ ['new' => 'Baru', 'good' => 'Baik', 'needs_repair' => 'Perlu perbaikan', 'not_applicable' => 'Tidak berlaku'][$deliverable['condition'] ?? 'not_applicable'] ?? 'Tidak berlaku' }}</p>
                                                            @if(!empty($deliverable['notes']))<p class="mt-1 text-slate-600">{{ $deliverable['notes'] }}</p>@endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="mt-1 text-xs text-amber-800">BAST ini sudah disetujui sebelum daftar hasil/barang dicatat. Data persetujuan tidak diubah; hubungi Pengurus TeFa untuk koreksi resmi.</p>
                                            @endif
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-700">Nomor BAST</label>
                                        <input type="text" value="{{ $proj['bast_number'] ?? '—' }}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs text-slate-600" aria-readonly="true">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-700">Tanggal serah terima resmi</label>
                                        <input type="date" value="{{ $proj['handover_date'] ?? '' }}" readonly disabled class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs text-slate-600 cursor-not-allowed" aria-readonly="true">
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-700">Masa garansi</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $proj['maintenance']['maintenance_warranty_months'] ?? 0 }} bulan · mulai {{ $proj['maintenance']['maintenance_start_date'] ?? '—' }} · berakhir {{ $proj['maintenance']['maintenance_end_date'] ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-700">Repository source code</p>
                                        @if($proj['repository_url'])
                                            <a href="{{ $proj['repository_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-1 block truncate text-xs text-blue-700 underline">{{ $proj['repository_url'] }}</a>
                                        @else
                                            <p class="mt-1 text-xs text-slate-500">Belum tercatat</p>
                                        @endif
                                        @if($proj['documentation_url'])
                                            <a href="{{ route('klien.projek-saya.bast.documentation', $proj['proposal_id']) }}" class="mt-2 block text-xs text-blue-700 underline">Unduh dokumentasi API · {{ basename($proj['documentation_url']) }}</a>
                                        @else
                                            <p class="mt-2 text-xs text-slate-500">Dokumentasi API: Belum terlampir</p>
                                        @endif
                                        @if($proj['user_manual_url'] && filter_var($proj['user_manual_url'], FILTER_VALIDATE_URL))
                                            <a href="{{ $proj['user_manual_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-2 block text-xs text-blue-700 underline">Panduan pengguna (Web)</a>
                                        @endif
                                        @if(!empty($proj['user_manual_file_url']) || (!empty($proj['user_manual_url']) && !filter_var($proj['user_manual_url'], FILTER_VALIDATE_URL)))
                                            <a href="{{ route('klien.projek-saya.bast.user-manual', $proj['proposal_id']) }}" class="mt-2 block text-xs text-blue-700 underline">Unduh panduan pengguna (Dokumen)</a>
                                        @endif
                                        @if($proj['client_signoff_name'])
                                            <p class="mt-2 text-xs text-slate-500">Disetujui oleh: {{ $proj['client_signoff_name'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </section>
                        @endif

                    </div>

                </div>
                @empty
                    <div class="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center">
                        <p class="text-sm font-semibold text-slate-700">Belum ada proyek aktif.</p>
                        <p class="mt-1 text-xs text-slate-500">Proyek akan muncul di sini setelah proposal disetujui.</p>
                    </div>
                @endforelse

            </div>

        </main>

    </div>

</div>

</body>
</html>
