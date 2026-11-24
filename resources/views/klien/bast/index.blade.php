<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BAST & Serah Terima - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F4F7FB] font-sans text-[#1E293B] antialiased">
<div class="flex min-h-screen">
    <x-sidebar active="bast" />

    <main class="ml-64 flex-1 space-y-6 p-8">
        <header class="rounded-2xl border border-slate-100 bg-white px-8 py-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-[#0B3B82]">Proses Serah Terima</p>
            <h1 class="mt-2 text-2xl font-bold">BAST &amp; Serah Terima</h1>
            <p class="mt-2 text-sm text-slate-500">
                Persetujuan hanya tersedia setelah laporan UAT terbaru lulus dan telah disahkan.
            </p>
        </header>

        @if(session('error'))
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if($loadError)
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                {{ $loadError }}
            </div>
        @elseif(empty($projects))
            <div class="rounded-2xl border border-slate-100 bg-white px-8 py-12 text-center shadow-sm">
                <h2 class="font-bold text-slate-800">Belum ada proyek</h2>
                <p class="mt-2 text-sm text-slate-500">Proyek milik Anda akan muncul di sini.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($projects as $project)
                    @php
                        $latestResult = strtoupper((string) ($project['latest_uat_result'] ?? ''));
                        $isApproved = (bool) ($project['client_approved'] ?? false);
                        $isEligible = (bool) ($project['bast_eligible'] ?? false);
                        $isPrepared = (bool) ($project['bast_prepared'] ?? false);
                        $warranty = $project['warranty'] ?? [];
                    @endphp
                    <article class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="font-mono text-xs font-bold text-[#0B3B82]">
                                    {{ $project['project_code'] ?? $project['proposal_code'] ?? 'Proyek' }}
                                </p>
                                <h2 class="mt-1 text-lg font-bold text-slate-800">{{ $project['title'] ?? 'Proyek tanpa judul' }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ $project['status'] ?? 'Status belum tersedia' }}</p>
                            </div>

                            @if($isApproved)
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">BAST disetujui</span>
                            @elseif($isPrepared)
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">Menunggu persetujuan BAST</span>
                            @elseif($isEligible)
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">UAT terbaru lulus &amp; disahkan</span>
                            @else
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">Belum memenuhi syarat BAST</span>
                            @endif
                        </div>

                        <dl class="mt-5 grid gap-4 border-t border-slate-100 pt-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Hasil UAT terbaru</dt>
                                <dd class="mt-1 text-sm font-semibold {{ $latestResult === 'PASSED' ? 'text-emerald-700' : 'text-slate-700' }}">
                                    {{ $latestResult !== '' ? $latestResult : 'Belum ada laporan UAT' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Masa garansi</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-700">
                                    @if(!empty($warranty['has_warranty']))
                                        {{ $warranty['warranty_months'] ?? $project['warranty_months'] ?? '-' }} bulan
                                    @else
                                        Tidak ada garansi
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        @if($isPrepared && !$isApproved)
                            <a href="{{ route('klien.bast.show', $project['id']) }}"
                               class="mt-5 inline-flex rounded-xl bg-[#0B3B82] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#082d63]">
                                Lihat detail &amp; setujui BAST
                            </a>
                        @elseif(!$isApproved && !$isEligible)
                            <p class="mt-4 text-sm text-slate-500">
                                @if($latestResult !== 'PASSED')
                                    BAST belum dapat disetujui karena laporan UAT terbaru belum lulus.
                                @elseif(empty($project['uat_signed_off_at']))
                                    BAST menunggu pengesahan hasil UAT terbaru.
                                @else
                                    BAST belum dapat disetujui berdasarkan status proyek saat ini.
                                @endif
                            </p>
                        @elseif(!$isApproved && !$isPrepared)
                            <p class="mt-4 text-sm text-slate-500">UAT sudah lulus; menunggu Pengurus TeFa menyiapkan dokumen BAST.</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </main>
</div>
</body>
</html>
