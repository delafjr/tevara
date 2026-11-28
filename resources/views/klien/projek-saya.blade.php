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
    $totalProjectCount = count($proposals);
    $activeProjectCount = count(array_filter($proposals, fn ($p) => in_array(strtolower($p['status'] ?? ''), ['active', 'berjalan', 'testing', 'handover'])));
    $averageProjectProgress = $totalProjectCount > 0
        ? round(array_sum(array_column($proposals, 'overall_progress')) / $totalProjectCount, 1)
        : 0;
    $totalModuleCount = array_sum(array_map(fn ($p) => count($p['modules'] ?? []), $proposals));
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

            {{-- == ( Filter + Tabel Projek Klien - Desain Mirip Pengurus TeFa ) == --}}
            @php
            $ss = [
                'active'      => 'bg-blue-50 text-blue-700',
                'berjalan'    => 'bg-blue-50 text-blue-700',
                'testing'     => 'bg-purple-50 text-purple-700',
                'review'      => 'bg-amber-50 text-amber-700',
                'handover'    => 'bg-emerald-50 text-emerald-700',
                'selesai'     => 'bg-green-50 text-green-700',
                'completed'   => 'bg-green-50 text-green-700',
                'proposal'    => 'bg-purple-50 text-purple-700',
                'improvement' => 'bg-amber-100 text-amber-800 border border-amber-300',
                'revisi'      => 'bg-amber-100 text-amber-800 border border-amber-300',
            ];
            $pc = fn($p) => $p >= 100 ? '#16A34A' : ($p >= 60 ? '#0B3B82' : ($p >= 30 ? '#F59E0B' : '#DC2626'));
            @endphp
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden" x-data="{ search: '', statusFilter: 'Semua Status' }">
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <p class="font-semibold text-[#1E293B] text-[15px]">Daftar Projek</p>
                        <p class="text-xs text-slate-400 mt-0.5">Semua projek yang terdaftar di database</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <input type="text" x-model="search" placeholder="Cari projek..." class="text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0B3B82] w-48">
                        <select x-model="statusFilter" class="text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0B3B82]">
                            <option>Semua Status</option>
                            <option>Active</option>
                            <option>Testing</option>
                            <option>Handover</option>
                            <option>Completed</option>
                        </select>
                        <a href="{{ route($routePrefix . '.ajukan-proyek') }}" class="flex items-center gap-1.5 bg-[#0B3B82] text-white font-semibold text-xs px-3.5 py-2 rounded-lg hover:bg-[#082a5c] transition-colors shadow-xs ml-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span>Ajukan Projek</span>
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-6 py-3">Nama Projek</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">PM / Dosen</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Mahasiswa</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Status</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3 w-32">Progress</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Deadline</th>
                                <th class="text-right text-[11px] font-semibold text-slate-400 px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($proposals as $p)
                            @php
                                $statusStr = (string) ($p['status'] ?? '-');
                                $statusKey = strtolower($statusStr);
                                $statusId = (int) ($p['status_id'] ?? 0);
                                $isRevision = $statusId === 4 || str_contains($statusKey, 'revision') || str_contains($statusKey, 'improvement');
                                $badgeClass = $isRevision
                                    ? 'bg-amber-100 text-amber-800 border border-amber-300'
                                    : ($ss[$statusKey] ?? 'bg-slate-100 text-slate-600');
                                $displayStatus = $isRevision ? 'Improvement (Revisi)' : $statusStr;
                                $prog = (int) ($p['overall_progress'] ?? 0);
                                $code = $p['project_code'] ?? $p['proposal_code'] ?? ('PRSL-' . $p['id']);
                                $category = $p['category'] ?? 'Web';
                                $pmName = $p['pm']['name'] ?? '—';
                                $studentCount = count($p['team_members'] ?? []);
                                $deadline = $p['target_completion_date'] ?? $p['desired_deadline'] ?? '—';
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors"
                                x-show="(statusFilter === 'Semua Status' || statusFilter.toLowerCase() === '{{ $statusKey }}' || (statusFilter === 'Improvement' && {{ $isRevision ? 'true' : 'false' }})) && (!search || '{{ strtolower(addslashes($p['title'] ?? '')) }}'.includes(search.toLowerCase()) || '{{ strtolower(addslashes($code)) }}'.includes(search.toLowerCase()))">
                                <td class="px-6 py-3.5">
                                    <a href="{{ route('klien.projek-saya.detail', $p['id']) }}" class="font-semibold text-[#1E293B] hover:text-[#0B3B82] text-[13px] leading-tight block transition-colors">
                                        {{ $p['title'] ?? 'Proyek Tanpa Judul' }}
                                    </a>
                                    <p class="text-[11px] text-slate-400 mt-0.5 capitalize">
                                        {{ $category }} <span class="font-mono text-slate-300">·</span> <span class="font-mono text-[10.5px] font-semibold text-[#0B3B82]">{{ $code }}</span>
                                    </p>
                                    @if($isRevision && !empty($p['review_notes']))
                                        <div class="mt-2 text-[11px] text-amber-900 bg-amber-50 rounded-lg p-2 border border-amber-200 flex items-start gap-1.5 max-w-md">
                                            <svg class="w-3.5 h-3.5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                            <p class="leading-snug"><strong>Catatan TeFa:</strong> {{ $p['review_notes'] }}</p>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-600 font-medium whitespace-nowrap">{{ $pmName }}</td>
                                <td class="px-3 py-3.5 whitespace-nowrap"><span class="text-[12px] font-semibold text-[#0B3B82]">{{ $studentCount }} mhs</span></td>
                                <td class="px-3 py-3.5 whitespace-nowrap"><span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $badgeClass }}">{{ $displayStatus }}</span></td>
                                <td class="px-3 py-3.5 w-32">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full" style="width:{{ $prog }}%;background:{{ $pc($prog) }}"></div>
                                        </div>
                                        <span class="text-[11px] font-semibold text-slate-500 w-7 text-right">{{ $prog }}%</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-500 whitespace-nowrap">{{ $deadline }}</td>
                                <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        @if(!empty($p['contract_file_url']))
                                            <a href="{{ route('dokumen.spk', $p['id']) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-[11px] font-bold transition-all" title="Unduh SPK">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                                <span>SPK</span>
                                            </a>
                                        @endif
                                        @php
                                            $rawStatusLower = strtolower($statusStr);
                                            $canAccessUat = in_array($rawStatusLower, ['testing', 'handover', 'completed'], true) 
                                                || ($rawStatusLower === 'active' && $prog >= 100)
                                                || !empty($p['bast_eligible']);
                                        @endphp
                                        @if($canAccessUat)
                                            <a href="{{ route('klien.projek-saya.uat', $p['id']) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 text-[11px] font-bold transition-all" title="Buka Lembar UAT">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                <span>UAT</span>
                                            </a>
                                        @endif
                                        <a href="{{ route('klien.projek-saya.detail', $p['id']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#EAF3FF] text-[#0B3B82] hover:bg-[#0B3B82] hover:text-white text-[11px] font-bold transition-all" title="Lihat Detail Projek">
                                            <span>Detail</span>
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-xs">
                                    Belum ada projek yang terdaftar di database.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3.5 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-400">Menampilkan {{ count($proposals) }} projek</p>
                </div>
            </div>

        </main>

    </div>

</div>

</body>
</html>
