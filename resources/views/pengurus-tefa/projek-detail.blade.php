<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Projek - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user');
    $proposal = is_array($proposal ?? null) ? $proposal : [];
    $uat = is_array($uat ?? null) ? $uat : null;
    $error = $error ?? null;

    $proposalId = (int) ($proposal['id'] ?? 0);
    $proposalCode = $proposal['proposal_code'] ?? 'PRSL-' . $proposalId;

    $pic = is_array($proposal['client'] ?? null) ? $proposal['client'] : [];
    $modules = is_array($proposal['modules'] ?? null) ? $proposal['modules'] : [];
    $payments = is_array($proposal['payments'] ?? null) ? $proposal['payments'] : [];

    $category = (string) ($proposal['category']['name'] ?? ($proposal['category_name'] ?? 'Other'));
    $status = (string) ($proposal['status']['name'] ?? ($proposal['status'] ?? '-'));
    $statusLower = strtolower($status);

    $isCompletedStatus = in_array($statusLower, ['completed', 'handover'], true);
    $overallProgress = isset($proposal['overall_progress'])
        ? (int) round((float) $proposal['overall_progress'])
        : ($isCompletedStatus ? 100 : 0);
    $overallProgress = max(0, min(100, $overallProgress));

    // PM Data
    $pm = is_array($proposal['pm'] ?? null) ? $proposal['pm'] : [];
    $pmName = trim((string) ($pm['name'] ?? ''));
    $pmEmail = trim((string) ($pm['email'] ?? ''));

    // Team Members (Mahasiswa)
    $teamMembers = is_array($proposal['project_teams'] ?? null) ? $proposal['project_teams'] : [];

    $teamInitials = function (string $name): string {
        return collect(explode(' ', trim($name)))
            ->filter()
            ->take(2)
            ->map(fn ($word) => mb_substr($word, 0, 1))
            ->implode('');
    };

    $categoryStyle = [
        'web'     => 'bg-blue-50 text-blue-700',
        'mobile'  => 'bg-purple-50 text-purple-700',
        'desktop' => 'bg-indigo-50 text-indigo-700',
        'iot'     => 'bg-amber-50 text-amber-700',
        'other'   => 'bg-slate-100 text-slate-600',
    ];

    $statusStyle = function (string $value): string {
        $s = strtolower($value);
        if (str_contains($s, 'active') || str_contains($s, 'berjalan')) {
            return 'bg-blue-50 text-blue-700 border-blue-200';
        }
        if (str_contains($s, 'testing')) {
            return 'bg-purple-50 text-purple-700 border-purple-200';
        }
        if (str_contains($s, 'handover')) {
            return 'bg-amber-50 text-amber-700 border-amber-200';
        }
        if (str_contains($s, 'completed') || str_contains($s, 'selesai')) {
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        }
        if (str_contains($s, 'reject')) {
            return 'bg-red-50 text-red-700 border-red-200';
        }
        return 'bg-slate-100 text-slate-700 border-slate-200';
    };

    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $formatDate = fn ($value) => $value
        ? \Carbon\Carbon::parse($value)->translatedFormat('d M Y')
        : '—';

    $deadlineToUse = $proposal['target_completion_date'] ?? ($proposal['desired_deadline'] ?? null);
@endphp

<div class="flex min-h-screen">
    <x-sidebar active="projek" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 pb-32 space-y-6">

            {{-- Breadcrumb --}}
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('pengurus-tefa.projek') }}"
                   class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82] transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke Daftar Projek
                </a>
                @if (!$error && !empty($proposalCode))
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-mono font-semibold text-slate-400 bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-2xs">
                            {{ $proposalCode }}
                        </span>
                        <span class="text-[11px] font-bold px-3 py-1.5 rounded-lg border {{ $statusStyle($status) }}">
                            Status: {{ $status }}
                        </span>
                    </div>
                @endif
            </div>

            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-semibold text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if ($error)
                <div class="max-w-xl mx-auto bg-white rounded-2xl border border-slate-100 shadow-sm px-8 py-14 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-5">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                        </svg>
                    </div>
                    <h1 class="text-lg font-bold text-[#1E293B]">Detail projek tidak dapat ditampilkan</h1>
                    <p class="text-sm text-slate-400 mt-2 leading-relaxed">{{ $error }}</p>
                    <a href="{{ route('pengurus-tefa.projek') }}"
                       class="inline-flex items-center gap-2 mt-6 rounded-full bg-[#0B3B82] px-5 py-2.5 text-xs font-bold text-white hover:bg-[#092f6a] transition-colors">
                        Kembali ke Daftar Projek
                    </a>
                </div>
            @else
                {{-- Hero Detail --}}
                <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6">
                    <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                    <div class="relative z-10 flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg {{ $categoryStyle[strtolower($category)] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $category }}
                                </span>
                                <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full border {{ $statusStyle($status) }}">
                                    {{ $status }}
                                </span>
                            </div>
                            <h1 class="text-2xl font-bold leading-snug text-[#1E293B] max-w-3xl">
                                {{ $proposal['title'] ?? '-' }}
                            </h1>
                            <p class="text-sm text-slate-400 mt-2">
                                Klien / Mitra: <span class="font-semibold text-slate-600">{{ $proposal['client_company_name'] ?? ($proposal['client_name'] ?? '-') }}</span>
                                @if(!empty($pic['name']))
                                    &bull; PIC: <span class="font-medium text-slate-600">{{ $pic['name'] }}</span>
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3 flex-shrink-0">
                            <div class="rounded-xl bg-white/80 border border-slate-100 px-4 py-3 min-w-[130px]">
                                <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Progres</p>
                                <p class="text-base font-bold text-[#0B3B82] mt-1">{{ $overallProgress }}%</p>
                            </div>
                            <div class="rounded-xl bg-white/80 border border-slate-100 px-4 py-3 min-w-[140px]">
                                <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Deadline</p>
                                <p class="text-sm font-bold text-[#1E293B] mt-1">{{ $formatDate($deadlineToUse) }}</p>
                            </div>
                            <div class="rounded-xl bg-white/80 border border-slate-100 px-4 py-3 min-w-[150px]">
                                <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Anggaran</p>
                                <p class="text-sm font-bold text-[#0B3B82] mt-1">{{ $formatRupiah($proposal['estimated_budget'] ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Grid Konten --}}
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">

                    {{-- Left Column (2 Cols) --}}
                    <div class="xl:col-span-2 space-y-6">

                        {{-- Card: Progress Pengerjaan & Tim Pelaksana --}}
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6 space-y-6">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-xl bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <h2 class="text-[15px] font-bold text-[#1E293B]">Progres &amp; Tim Pelaksana Projek</h2>
                                        <p class="text-xs text-slate-400">Rincian kemajuan pengerjaan, Project Manager, dan tim mahasiswa</p>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-[#0B3B82] bg-[#EAF3FF] px-3 py-1 rounded-full">
                                    {{ $overallProgress }}% Selesai
                                </span>
                            </div>

                            {{-- Progress Bar --}}
                            <div>
                                <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">
                                    <span>Penyelesaian Keseluruhan</span>
                                    <span class="text-[#0B3B82]">{{ $overallProgress }}%</span>
                                </div>
                                <div class="h-3 w-full rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-[#0B3B82] via-[#2563EB] to-[#16A34A] transition-all duration-500"
                                         style="width: {{ $overallProgress }}%"></div>
                                </div>
                            </div>

                            {{-- PM & Mahasiswa Container --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                                {{-- Project Manager Card --}}
                                <div class="rounded-xl border border-slate-100 bg-[#F8FAFC] p-4.5">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                                        Project Manager (Dosen)
                                    </p>
                                    @if ($pmName)
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-[#0B3B82] text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm">
                                                {{ $teamInitials($pmName) ?: 'PM' }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-[#1E293B] leading-tight truncate">{{ $pmName }}</p>
                                                @if ($pmEmail)
                                                    <p class="text-xs text-slate-400 truncate mt-0.5">{{ $pmEmail }}</p>
                                                @endif
                                                <span class="inline-block text-[10px] font-semibold text-[#0B3B82] bg-white border border-blue-100 px-2 py-0.5 rounded-md mt-1.5">
                                                    Penanggung Jawab Proyek
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-xs text-slate-400 italic">Dosen PM belum ditentukan.</p>
                                    @endif
                                </div>

                                {{-- Mahasiswa Count Summary --}}
                                <div class="rounded-xl border border-slate-100 bg-[#F8FAFC] p-4.5">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Kapasitas Tim Mahasiswa
                                    </p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-2xl font-bold text-[#1E293B]">{{ count($teamMembers) }}</span>
                                        <span class="text-xs text-slate-500 font-medium">Mahasiswa Terdaftar</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1.5 leading-relaxed">
                                        Mahasiswa aktif yang mengerjakan modul dan task projek ini.
                                    </p>
                                </div>
                            </div>

                            {{-- Mahasiswa Member List --}}
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Daftar Anggota Mahasiswa</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @forelse ($teamMembers as $member)
                                        @php
                                            $memberName = trim((string) ($member['user']['name'] ?? ''));
                                            $memberRole = trim((string) ($member['role']['name'] ?? 'Mahasiswa'));
                                            $studentNim = trim((string) ($member['user']['student_profile']['nim'] ?? ''));
                                        @endphp
                                        @if ($memberName)
                                            <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 bg-white hover:border-slate-200 transition-colors">
                                                <div class="w-9 h-9 rounded-xl bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center text-xs font-bold flex-shrink-0">
                                                    {{ $teamInitials($memberName) ?: 'MH' }}
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-xs font-bold text-slate-800 truncate">{{ $memberName }}</p>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <span class="text-[10px] font-semibold text-[#0B3B82] bg-blue-50 px-2 py-0.5 rounded">
                                                            {{ $memberRole }}
                                                        </span>
                                                        @if ($studentNim)
                                                            <span class="text-[10px] text-slate-400 font-mono">{{ $studentNim }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @empty
                                        <div class="col-span-2 rounded-xl border border-dashed border-slate-200 p-5 text-center text-xs text-slate-400">
                                            Belum ada mahasiswa yang ditugaskan pada projek ini.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </section>

                        {{-- Card: Sesi & Hasil UAT --}}
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6 space-y-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 2h7M7 16h10"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <h2 class="text-[15px] font-bold text-[#1E293B]">Hasil Pengujian Klien (UAT)</h2>
                                        <p class="text-xs text-slate-400">Penerimaan fitur dan evaluasi sistem oleh Klien/Mitra</p>
                                    </div>
                                </div>
                                @if ($uat)
                                    <a href="{{ route('pengurus-tefa.uat.detail', $proposalId) }}"
                                       class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0B3B82] hover:underline">
                                        Lihat Pemeriksaan UAT Lengkap &rarr;
                                    </a>
                                @endif
                            </div>

                            @if ($uat)
                                @php
                                    $overallRes = strtoupper((string) ($uat['overall_result'] ?? '—'));
                                    $reviewStat = strtoupper((string) ($uat['review_status'] ?? 'PENDING_REVIEW'));
                                    $sessionDate = $uat['session_date'] ?? null;
                                    $uatItems = is_array($uat['uat_items'] ?? null) ? $uat['uat_items'] : [];

                                    $resClass = match($overallRes) {
                                        'PASSED' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'FAILED' => 'bg-red-50 text-red-700 border-red-200',
                                        'PARTIAL' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };

                                    $reviewClass = match($reviewStat) {
                                        'APPROVED' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'NEEDS_IMPROVEMENT' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        default => 'bg-blue-50 text-blue-700 border-blue-200',
                                    };
                                @endphp

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="rounded-xl border border-slate-100 bg-[#F8FAFC] p-4">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal Pengujian</p>
                                        <p class="text-sm font-bold text-[#1E293B] mt-1">{{ $formatDate($sessionDate) }}</p>
                                    </div>
                                    <div class="rounded-xl border border-slate-100 bg-[#F8FAFC] p-4">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Hasil Keseluruhan</p>
                                        <span class="inline-block text-xs font-bold px-2.5 py-1 rounded-lg border mt-1 {{ $resClass }}">
                                            {{ $overallRes }}
                                        </span>
                                    </div>
                                    <div class="rounded-xl border border-slate-100 bg-[#F8FAFC] p-4">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status Review TeFa</p>
                                        <span class="inline-block text-xs font-bold px-2.5 py-1 rounded-lg border mt-1 {{ $reviewClass }}">
                                            {{ $reviewStat === 'NEEDS_IMPROVEMENT' ? 'Perlu Perbaikan' : ($reviewStat === 'APPROVED' ? 'Disetujui' : 'Menunggu Review') }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Signoff Info --}}
                                @if (!empty($uat['client_signoff_name']))
                                    <div class="flex items-center gap-3 p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/70 text-emerald-800 text-xs font-medium">
                                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                        <div>
                                            <span>Telah ditandatangani secara digital oleh PIC Klien: <strong>{{ $uat['client_signoff_name'] }}</strong></span>
                                            @if(!empty($uat['signed_off_at']))
                                                <span class="text-emerald-700/80"> pada {{ \Carbon\Carbon::parse($uat['signed_off_at'])->translatedFormat('d M Y H:i') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Catatan Klien jika ada --}}
                                @if (!empty($uat['client_notes']))
                                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-xs text-slate-600">
                                        <p class="font-bold text-slate-700 mb-1">Catatan dari Klien:</p>
                                        <p class="leading-relaxed">{{ $uat['client_notes'] }}</p>
                                    </div>
                                @endif

                                {{-- Rincian Fitur UAT --}}
                                @if (count($uatItems) > 0)
                                    <div>
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2.5">Fitur yang Diuji ({{ count($uatItems) }})</h4>
                                        <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden bg-white text-xs">
                                            @foreach ($uatItems as $item)
                                                @php
                                                    $itemRes = strtoupper((string) ($item['result'] ?? ''));
                                                    $itemBadge = match($itemRes) {
                                                        'PASSED' => 'bg-emerald-50 text-emerald-700',
                                                        'FAILED' => 'bg-red-50 text-red-700',
                                                        default  => 'bg-amber-50 text-amber-700',
                                                    };
                                                @endphp
                                                <div class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 hover:bg-slate-50/50">
                                                    <div>
                                                        <p class="font-bold text-slate-800">{{ $item['feature_name'] ?? 'Fitur' }}</p>
                                                        @if (!empty($item['test_scenario']))
                                                            <p class="text-slate-500 text-[11px] mt-0.5">{{ $item['test_scenario'] }}</p>
                                                        @endif
                                                        @if (!empty($item['bug_description']))
                                                            <p class="text-red-600 text-[11px] mt-1">Bug: {{ $item['bug_description'] }}</p>
                                                        @endif
                                                    </div>
                                                    <span class="inline-flex self-start sm:self-center px-2.5 py-1 rounded-md font-bold text-[10px] {{ $itemBadge }}">
                                                        {{ $itemRes }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="rounded-xl border border-dashed border-slate-200 p-6 text-center">
                                    <p class="text-xs font-semibold text-slate-500">Belum ada data sesi UAT yang tercatat.</p>
                                    <p class="text-[11px] text-slate-400 mt-1 max-w-md mx-auto">
                                        Saat proyek masuk fase pengujian (Testing), Klien akan mengisi checklist UAT dan hasilnya ditampilkan di bagian ini.
                                    </p>
                                    @if ($statusLower === 'active')
                                        <form action="{{ route('pengurus-tefa.uat.status-testing', $proposalId) }}" method="POST" class="mt-4">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center gap-2 rounded-xl bg-[#0B3B82] px-4 py-2 text-xs font-bold text-white hover:bg-[#082a5c] transition-colors shadow-2xs">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/>
                                                </svg>
                                                Tandai Siap Diuji (Testing)
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </section>

                        {{-- Card: Latar Belakang --}}
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6">
                            <div class="flex items-start gap-3.5 mb-4">
                                <span class="w-9 h-9 rounded-xl bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                    </svg>
                                </span>
                                <div>
                                    <h2 class="text-[15px] font-bold text-[#1E293B] leading-tight">Latar Belakang</h2>
                                    <p class="text-xs text-slate-400 mt-0.5">Konteks dan kebutuhan proyek dari klien</p>
                                </div>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ $proposal['background'] ?? '-' }}</p>
                        </section>

                        {{-- Card: Target Pengguna --}}
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6">
                            <div class="flex items-start gap-3.5 mb-4">
                                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>
                                    </svg>
                                </span>
                                <div>
                                    <h2 class="text-[15px] font-bold text-[#1E293B] leading-tight">Target Pengguna</h2>
                                    <p class="text-xs text-slate-400 mt-0.5">Pengguna yang akan memakai hasil proyek</p>
                                </div>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ $proposal['target_audience'] ?? '-' }}</p>
                        </section>

                        {{-- Card: Modul Proyek --}}
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div class="flex items-start gap-3.5">
                                    <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <h2 class="text-[15px] font-bold text-[#1E293B] leading-tight">Modul Proyek</h2>
                                        <p class="text-xs text-slate-400 mt-0.5">Rincian modul yang disetujui dalam kontrak</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-2.5 py-1 rounded-full">
                                    {{ count($modules) }} Modul
                                </span>
                            </div>

                            <div class="space-y-3">
                                @forelse ($modules as $index => $module)
                                    <div class="flex items-start gap-3.5 rounded-xl border border-slate-100 bg-[#F8FAFC] p-4">
                                        <span class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-[#0B3B82] text-[11px] font-extrabold flex items-center justify-center flex-shrink-0">
                                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-[#1E293B] leading-tight">{{ $module['title'] ?? '-' }}</p>
                                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ !empty($module['description']) ? $module['description'] : 'Tanpa deskripsi.' }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-xl border border-dashed border-slate-200 px-5 py-8 text-center">
                                        <p class="text-xs font-semibold text-slate-500">Belum ada modul yang tercatat</p>
                                    </div>
                                @endforelse
                            </div>
                        </section>

                    </div>

                    {{-- Right Column (1 Col) --}}
                    <div class="space-y-6">

                        {{-- Card: Informasi Klien --}}
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-6">
                            <h2 class="text-[13px] font-bold text-[#1E293B]">Informasi Klien / Mitra</h2>

                            <div class="mt-4 space-y-4">
                                <div class="flex items-start gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Instansi / Perusahaan</p>
                                        <p class="text-xs font-semibold text-[#1E293B] mt-0.5">{{ $proposal['client_company_name'] ?? ($proposal['client_name'] ?? '-') }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Penanggung Jawab (PIC)</p>
                                        <p class="text-xs font-semibold text-[#1E293B] mt-0.5">{{ $pic['name'] ?? '-' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Email</p>
                                        <p class="text-xs font-semibold text-[#1E293B] mt-0.5 break-all">{{ $pic['email'] ?? '-' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">No. HP / Telepon</p>
                                        <p class="text-xs font-semibold text-[#1E293B] mt-0.5">{{ $pic['phone_number'] ?? ($pic['no_hp'] ?? '-') }}</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {{-- Card: Ringkasan Kontrak & Administrasi --}}
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-6">
                            <h2 class="text-[13px] font-bold text-[#1E293B]">Administrasi &amp; Kontrak</h2>

                            <div class="mt-4 space-y-3">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-slate-400">Status Projek</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $statusStyle($status) }}">{{ $status }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-slate-400">Kategori</span>
                                    <span class="text-xs font-semibold text-[#1E293B] text-right">{{ $category }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-slate-400">Target Deadline</span>
                                    <span class="text-xs font-semibold text-[#1E293B] text-right">{{ $formatDate($deadlineToUse) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-slate-400">Nilai Kontrak</span>
                                    <span class="text-xs font-bold text-[#0B3B82] text-right">{{ $formatRupiah($proposal['estimated_budget'] ?? 0) }}</span>
                                </div>

                                @if (!empty($proposal['contract_file_url']))
                                    <div class="pt-3 border-t border-slate-100">
                                        <a href="{{ route('dokumen.spk', $proposalId) }}"
                                           class="inline-flex items-center justify-center w-full gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                            </svg>
                                            Unduh Dokumen SPK
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </section>

                        {{-- Card: Termin Pembayaran --}}
                        @if (count($payments) > 0)
                            <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h2 class="text-[13px] font-bold text-[#1E293B]">Termin Pembayaran</h2>
                                    <span class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-0.5 rounded-full">
                                        {{ count($payments) }} Termin
                                    </span>
                                </div>

                                <div class="space-y-2.5">
                                    @foreach ($payments as $pay)
                                        @php
                                            $payStatus = strtolower((string) ($pay['status'] ?? 'pending'));
                                            $payBadge = match($payStatus) {
                                                'paid' => 'bg-emerald-50 text-emerald-700',
                                                'verified' => 'bg-emerald-50 text-emerald-700',
                                                'pending' => 'bg-amber-50 text-amber-700',
                                                default => 'bg-slate-100 text-slate-600',
                                            };
                                        @endphp
                                        <div class="p-3 rounded-xl border border-slate-100 bg-[#F8FAFC] flex items-center justify-between gap-2">
                                            <div>
                                                <p class="text-xs font-bold text-slate-800">{{ $pay['term_name'] ?? ('Termin ' . ($pay['term_sequence'] ?? 1)) }}</p>
                                                <p class="text-[11px] text-[#0B3B82] font-semibold mt-0.5">{{ $formatRupiah($pay['amount'] ?? 0) }}</p>
                                            </div>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $payBadge }} uppercase">
                                                {{ $pay['status'] ?? 'PENDING' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                    </div>

                </div>

            @endif
        </main>

        @unless ($error)
            {{-- Action Dock Bottom --}}
            <div class="fixed bottom-0 left-64 right-0 z-30 px-8 pb-5 pointer-events-none">
                <div class="pointer-events-auto mx-auto max-w-[1500px] overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 shadow-2xl shadow-slate-900/10 backdrop-blur-md">
                    <div class="h-1 bg-gradient-to-r from-[#0B3B82] via-[#2F6BD8] to-[#FFB800]"></div>

                    <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-3.5">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-10 h-10 rounded-xl bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0 ring-1 ring-[#0B3B82]/10">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-[#1E293B]">Detail Projek Aktif</p>
                                <p class="text-[11px] text-slate-400 mt-0.5 truncate">{{ $proposal['title'] ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5">
                            @if (!empty($proposal['contract_file_url']))
                                <a href="{{ route('dokumen.spk', $proposalId) }}"
                                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                    </svg>
                                    Unduh SPK
                                </a>
                            @endif

                            <a href="{{ route('pengurus-tefa.uat.detail', $proposalId) }}"
                               class="inline-flex items-center gap-2 rounded-xl border border-purple-200 bg-purple-50 px-4 py-2 text-xs font-bold text-purple-700 hover:bg-purple-100 transition-colors">
                                <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 2h7M7 16h10"/>
                                </svg>
                                Halaman UAT
                            </a>

                            @if ($statusLower === 'handover' || $statusLower === 'completed')
                                <a href="{{ route('pengurus-tefa.bast.prepare', $proposalId) }}"
                                   class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition-colors shadow-2xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                    Persiapkan BAST
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endunless
    </div>
</div>
</body>
</html>
