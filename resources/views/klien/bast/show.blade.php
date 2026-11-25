<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail BAST - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F4F7FB] font-sans text-[#1E293B] antialiased">
@php
    $proposal = $handover['proposal'];
    $latestUat = $handover['latest_uat'] ?? [];
    $warrantyMonths = $proposal['warranty_months'] ?? null;
    $canApprove = !empty($handover['eligible_for_handover'])
        && !empty($handover['awaiting_client_approval'])
        && empty($proposal['client_approved']);
@endphp
<div class="flex min-h-screen">
    <x-sidebar active="bast" />

    <main class="ml-64 flex-1 space-y-6 p-8">
        <a href="{{ route('klien.bast') }}" class="text-sm font-semibold text-[#0B3B82] hover:underline">&larr; Kembali ke daftar BAST</a>

        @if($error)
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">{{ $error }}</div>
        @endif
        @if($success)
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ $success }}</div>
        @endif

        <header class="rounded-2xl border border-slate-100 bg-white px-8 py-6 shadow-sm">
            <p class="font-mono text-xs font-bold text-[#0B3B82]">{{ $proposal['project_code'] ?? $proposal['proposal_code'] ?? '' }}</p>
            <h1 class="mt-2 text-2xl font-bold">{{ $proposal['title'] ?? 'Detail BAST' }}</h1>
            <p class="mt-2 text-sm text-slate-500">Nomor BAST: {{ $proposal['bast_number'] ?? 'Belum disiapkan' }}</p>
        </header>

        <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">Validasi laporan UAT terbaru</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Hasil terbaru</p>
                    <p class="mt-1 font-bold {{ ($latestUat['overall_result'] ?? '') === 'PASSED' ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $latestUat['overall_result'] ?? 'Belum ada laporan UAT' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Disahkan pada</p>
                    <p class="mt-1 font-semibold text-slate-700">{{ $latestUat['signed_off_at'] ?? 'Belum disahkan' }}</p>
                </div>
            </div>
            @if(!$canApprove)
                <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    BAST belum dapat disetujui. Persetujuan hanya tersedia jika laporan UAT terbaru lulus, sudah disahkan, dan dokumen BAST telah disiapkan.
                </p>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">Dokumen &amp; masa garansi</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Durasi garansi dari kontrak</dt>
                    <dd class="mt-1 font-semibold text-slate-700">
                        @if(!empty($proposal['has_warranty']) && $warrantyMonths)
                            {{ $warrantyMonths }} bulan
                        @else
                            Tidak ada garansi
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Hasil/barang diserahkan</dt>
                    <dd class="mt-1 text-sm text-slate-700">
                        @forelse(($proposal['handover_deliverables'] ?? []) as $deliverable)
                            <p>{{ $deliverable['name'] ?? 'Hasil serah terima' }}{{ !empty($deliverable['condition']) ? ' — ' . $deliverable['condition'] : '' }}</p>
                        @empty
                            Belum dicatat
                        @endforelse
                    </dd>
                </div>
                @foreach(['repository_url' => 'Repositori', 'documentation_url' => 'Dokumentasi'] as $field => $label)
                    @if(!empty($proposal[$field]))
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</dt>
                            @if($field === 'documentation_url' && !filter_var($proposal[$field], FILTER_VALIDATE_URL))
                                <dd class="mt-1 text-sm"><a class="text-[#0B3B82] hover:underline" href="{{ route('klien.projek-saya.bast.documentation', $proposal['id']) }}">Unduh dokumentasi API</a></dd>
                            @else
                                <dd class="mt-1 break-all text-sm"><a class="text-[#0B3B82] hover:underline" href="{{ $proposal[$field] }}" target="_blank" rel="noopener noreferrer">{{ $proposal[$field] }}</a></dd>
                            @endif
                        </div>
                    @endif
                @endforeach
                @if(!empty($proposal['user_manual_file_url']) || !empty($proposal['user_manual_url']))
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Panduan Pengguna</dt>
                        <dd class="mt-1 text-sm"><a class="text-[#0B3B82] hover:underline" href="{{ route('klien.projek-saya.bast.user-manual', $proposal['id']) }}">Unduh panduan pengguna</a></dd>
                    </div>
                @endif
                @if(!empty($proposal['handover_notes']))
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Catatan serah terima</dt>
                        <dd class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $proposal['handover_notes'] }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        @if($canApprove)
            <form method="POST" action="{{ route('klien.bast.approve', $proposal['id']) }}" class="space-y-4 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                @csrf
                <h2 class="text-lg font-bold">Persetujuan BAST</h2>
                <div>
                    <label for="client_signoff_name" class="mb-1.5 block text-sm font-semibold text-slate-700">Nama PIC yang menyetujui</label>
                    <input id="client_signoff_name" name="client_signoff_name" type="text" required maxlength="255"
                           value="{{ old('client_signoff_name', $user['name'] ?? '') }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#0B3B82] focus:outline-none">
                    @error('client_signoff_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <label class="flex items-start gap-3 text-sm text-slate-600">
                    <input type="checkbox" name="client_approved" value="1" required class="mt-1 rounded border-slate-300 text-[#0B3B82]">
                    <span>Saya telah memeriksa hasil/barang yang diserahkan dan menyetujui BAST ini.</span>
                </label>
                @error('client_approved')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit" class="rounded-xl bg-[#0B3B82] px-5 py-3 text-sm font-bold text-white hover:bg-[#082d63]">
                    Setujui BAST
                </button>
            </form>
        @elseif(!empty($proposal['client_approved']))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                BAST telah disetujui pada {{ $proposal['bast_approved_at'] ?? $proposal['handover_date'] ?? '-' }}.
                @if(!empty($proposal['has_warranty']) && $proposal['warranty_start_date'])
                    Masa garansi {{ $proposal['warranty_months'] }} bulan berlaku {{ $proposal['warranty_start_date'] }} sampai {{ $proposal['warranty_end_date'] ?? '-' }}.
                @endif
            </div>
        @endif
    </main>
</div>
</body>
</html>
