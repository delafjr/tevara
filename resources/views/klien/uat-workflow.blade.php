<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengujian UAT — {{ $proposal['title'] ?? 'Proyek' }} - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user', []);
    $proposalId = $proposal['id'] ?? null;
    $proposalCode = $proposal['proposal_code'] ?? ('PRJ-' . $proposalId);
    $title = $proposal['title'] ?? 'Proyek';
    $statusName = $proposal['status']['name'] ?? 'Status tidak tersedia';
    $uat = is_array($uat ?? null) ? $uat : null;
    $hasUat = !empty($uat);
    $items = $uat['uat_items'] ?? [];
    $reviewStatus = $uat['review_status'] ?? null;
    $signedAt = $uat['signed_off_at'] ?? null;
    $isApproved = $reviewStatus === 'APPROVED';
    $isSigned = !empty($signedAt);
    $isTesting = strtolower($statusName) === 'testing' || (int) ($proposal['status_id'] ?? 0) === 8;
    $canSubmit = $isTesting && (!$hasUat || $reviewStatus === 'READY_FOR_RETEST');
    $formFeatures = [];

    if ($canSubmit && $hasUat) {
        foreach ($items as $item) {
            $formFeatures[] = [
                'feature_name' => $item['feature_name'] ?? '',
                'test_scenario' => $item['test_scenario'] ?? '',
            ];
        }
    } elseif ($canSubmit) {
        foreach (($proposal['modules'] ?? []) as $module) {
            $formFeatures[] = [
                'feature_name' => $module['title'] ?? '',
                'test_scenario' => $module['description'] ?? '',
            ];
        }
    }

    $history = $uat['uat_history'] ?? ($uat ? [$uat] : []);
@endphp

<div class="flex min-h-screen">
    <x-sidebar active="projek-saya" />
    <main class="ml-64 flex-1 space-y-6 px-8 py-7">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('error') || ($error ?? null) || (isset($errors) && $errors->any()))
            <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                {{ session('error') ?? ($error ?? $errors->first()) }}
                @if(isset($errors) && $errors->any())
                    <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
                @endif
            </div>
        @endif

        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('klien.projek-saya.detail', $proposalId) }}" class="text-sm font-semibold text-slate-500 hover:text-[#0B3B82]">← Kembali ke detail proyek</a>
            <span class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 font-mono text-xs font-bold text-slate-500">{{ $proposalCode }}</span>
        </div>

        <section class="rounded-2xl border border-slate-100 bg-white p-7 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-[#0B3B82]">UAT · Pengujian Penerimaan Pengguna</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $title }}</h1>
            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
                Coba setiap fitur sesuai skenario, pilih hasil yang paling sesuai, dan tuliskan kendala bila ada. Pengurus TeFa akan memeriksa hasil Anda sebelum proyek dinyatakan lulus atau dikirim untuk perbaikan.
            </p>
        </section>

        @if(!$hasUat && !$canSubmit)
            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900">
                Pengujian belum dapat dimulai. Proyek saat ini berstatus <strong>{{ $statusName }}</strong>; formulir akan tersedia setelah proyek ditandai siap diuji.
            </section>
        @endif

        @if($canSubmit)
            @if(count($formFeatures))
                <form action="{{ route('klien.projek-saya.uat.submit', $proposalId) }}" method="POST" class="space-y-5">
                    @csrf
                    <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-slate-900">{{ $hasUat ? 'Pengujian ulang' : 'Daftar fitur yang perlu diuji' }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Hasil keseluruhan dihitung otomatis dari pilihan pada setiap fitur.</p>
                        <div class="mt-5 space-y-4">
                            @foreach($formFeatures as $index => $feature)
                                <article class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                                    <input type="hidden" name="items[{{ $index }}][feature_name]" value="{{ $feature['feature_name'] }}">
                                    <input type="hidden" name="items[{{ $index }}][test_scenario]" value="{{ $feature['test_scenario'] }}">
                                    <h3 class="font-semibold text-slate-900">{{ $feature['feature_name'] }}</h3>
                                    @if($feature['test_scenario'])
                                        <p class="mt-1 text-sm text-slate-600">{{ $feature['test_scenario'] }}</p>
                                    @endif
                                    <label class="mt-4 block text-xs font-semibold text-slate-600" for="result-{{ $index }}">Hasil pengujian <span class="text-red-600">*</span></label>
                                    <select id="result-{{ $index }}" name="items[{{ $index }}][result]" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                        <option value="">Pilih hasil</option>
                                        <option value="PASSED">Sesuai</option>
                                        <option value="FAILED">Belum sesuai</option>
                                        <option value="BUG_FOUND">Ada kendala</option>
                                    </select>
                                    <label class="mt-3 block text-xs font-semibold text-slate-600" for="feedback-{{ $index }}">Catatan atau kendala</label>
                                    <textarea id="feedback-{{ $index }}" name="items[{{ $index }}][client_feedback]" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm" placeholder="Jelaskan bagian yang sudah sesuai atau apa yang terjadi saat dicoba."></textarea>
                                    <input type="hidden" name="items[{{ $index }}][bug_severity]" value="MINOR">
                                </article>
                            @endforeach
                        </div>
                        <label for="client_notes" class="mt-5 block text-xs font-semibold text-slate-600">Catatan umum (opsional)</label>
                        <textarea id="client_notes" name="client_notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm" placeholder="Catatan tambahan untuk seluruh pengujian.">{{ old('client_notes') }}</textarea>
                        <button type="submit" class="mt-5 rounded-xl bg-[#0B3B82] px-5 py-3 text-sm font-bold text-white hover:bg-[#082a5c]">
                            Kirim hasil untuk diperiksa
                        </button>
                    </section>
                </form>
            @else
                <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900">
                    Daftar fitur proyek belum tersedia, sehingga formulir UAT belum bisa dibuat. Hubungi Pengurus TeFa untuk melengkapi modul proyek.
                </section>
            @endif
        @endif

        @if($hasUat)
            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sesi terbaru · {{ $uat['session_date'] ?? 'Tanggal tidak tersedia' }}</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Hasil: {{ ['PASSED' => 'Sesuai', 'PARTIAL' => 'Sebagian sesuai', 'FAILED' => 'Perlu perbaikan'][$uat['overall_result'] ?? ''] ?? 'Belum dinilai' }}</h2>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800">
                        {{ ['PENDING_REVIEW' => 'Menunggu pemeriksaan', 'NEEDS_IMPROVEMENT' => 'Perlu perbaikan', 'READY_FOR_RETEST' => 'Siap diuji ulang', 'APPROVED' => 'Disetujui'][$reviewStatus] ?? 'Dalam proses' }}
                    </span>
                </div>
                @if(!empty($uat['review_notes']))
                    <div class="mt-4 rounded-lg bg-slate-50 p-4 text-sm text-slate-700">
                        <strong>Catatan Pengurus:</strong> {{ $uat['review_notes'] }}
                    </div>
                @endif
                @if(!empty($uat['client_notes']))
                    <p class="mt-4 whitespace-pre-line text-sm text-slate-600">{{ $uat['client_notes'] }}</p>
                @endif

                <div class="mt-5 space-y-3">
                    @foreach($items as $item)
                        <article class="rounded-xl border border-slate-200 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="font-semibold text-slate-900">{{ $item['feature_name'] }}</h3>
                                <span class="text-xs font-bold text-slate-600">{{ ['PASSED' => 'Sesuai', 'FAILED' => 'Belum sesuai', 'BUG_FOUND' => 'Ada kendala'][$item['result'] ?? ''] ?? 'Belum dinilai' }}</span>
                            </div>
                            @if(!empty($item['client_feedback']) || !empty($item['bug_description']))
                                <p class="mt-2 text-sm text-slate-600">{{ $item['bug_description'] ?? $item['client_feedback'] }}</p>
                            @endif
                            @if(!empty($item['review_notes']))
                                <p class="mt-2 rounded-lg bg-amber-50 p-3 text-sm text-amber-900"><strong>Tindak lanjut:</strong> {{ $item['review_notes'] }}</p>
                            @endif
                            @foreach(($item['tasks'] ?? []) as $task)
                                <p class="mt-2 text-xs text-slate-500">Checklist mahasiswa: {{ $task['title'] }} — {{ $task['status'] === 'DONE' ? 'Selesai' : 'Belum selesai' }}</p>
                            @endforeach
                        </article>
                    @endforeach
                </div>
            </section>

            @if($isApproved && !$isSigned && ($uat['overall_result'] ?? null) === 'PASSED')
                <form action="{{ route('klien.projek-saya.uat.signoff', $proposalId) }}" method="POST" class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
                    @csrf
                    <h2 class="text-lg font-bold text-slate-900">Setujui hasil pengujian</h2>
                    <p class="mt-1 text-sm text-slate-600">Pengurus telah memeriksa bahwa seluruh fitur sesuai. Persetujuan ini melanjutkan proyek ke tahap BAST.</p>
                    <label for="client_signoff_name" class="mt-4 block text-xs font-semibold text-slate-600">Nama PIC yang menyetujui</label>
                    <input id="client_signoff_name" name="client_signoff_name" required value="{{ old('client_signoff_name', $user['name'] ?? '') }}" class="mt-1 w-full max-w-md rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <button type="submit" class="mt-4 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">Setujui hasil UAT</button>
                </form>
            @elseif($isSigned)
                <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">
                    Hasil UAT telah disetujui oleh <strong>{{ $uat['client_signoff_name'] }}</strong> pada {{ $signedAt }}.
                </section>
            @endif

            @if(count($history) > 1)
                <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="font-bold text-slate-900">Riwayat pengujian</h2>
                    <ol class="mt-3 space-y-2 text-sm text-slate-600">
                        @foreach($history as $session)
                            <li>Sesi {{ $session['id'] }} · {{ $session['session_date'] }} · {{ $session['overall_result'] }} · {{ $session['review_status'] ?? 'PENDING_REVIEW' }}</li>
                        @endforeach
                    </ol>
                </section>
            @endif
        @endif
    </main>
</div>
</body>
</html>
