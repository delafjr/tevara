<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ringkasan UAT — {{ $proposal['title'] ?? 'Proyek' }} - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $proposalId = $proposal['id'] ?? null;
    $proposalCode = $proposal['proposal_code'] ?? ('PRJ-' . $proposalId);
    $title = $proposal['title'] ?? 'Proyek';
    $uat = is_array($uat ?? null) ? $uat : null;
    $hasUat = !empty($uat);
    $items = $uat['uat_items'] ?? [];
    $reviewStatus = $uat['review_status'] ?? null;
    $signedAt = $uat['signed_off_at'] ?? null;
    $isSigned = !empty($signedAt);
    $history = $uat['uat_history'] ?? ($uat ? [$uat] : []);
@endphp

<div class="flex min-h-screen">
    <x-sidebar active="project" />
    <main class="ml-64 flex-1 space-y-6 px-8 py-7">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('pm.project') }}" class="text-sm font-semibold text-slate-500 hover:text-[#0B3B82]">← Kembali ke proyek</a>
            <span class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 font-mono text-xs font-bold text-slate-500">{{ $proposalCode }}</span>
        </div>

        <section class="rounded-2xl border border-slate-100 bg-white p-7 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-[#0B3B82]">UAT · Ringkasan Pengujian Penerimaan Pengguna</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $title }}</h1>
            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
                Halaman ini menampilkan catatan pengujian dari klien serta tindak lanjut yang diberikan oleh Pengurus TeFa.
            </p>
        </section>

        @if(!$hasUat)
            <section class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-600">
                Klien belum mengirimkan hasil pengujian UAT.
            </section>
        @else
            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sesi terbaru · {{ !empty($uat['session_date']) ? \Carbon\Carbon::parse($uat['session_date'])->translatedFormat('d M Y') : 'Tanggal tidak tersedia' }}</p>
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
                    <div class="mt-4">
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 mb-1">Catatan Umum</p>
                        <p class="whitespace-pre-line text-sm text-slate-600 bg-slate-50 rounded-lg px-4 py-3 border border-slate-100">{{ $uat['client_notes'] }}</p>
                    </div>
                @endif

                <div class="mt-5 overflow-hidden rounded-xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    <th class="w-12 px-4 py-3 text-center">No</th>
                                    <th class="w-2/5 min-w-[240px] px-4 py-3">Fitur &amp; Skenario</th>
                                    <th class="w-36 px-4 py-3 text-center">Hasil Pengujian</th>
                                    <th class="px-4 py-3">Catatan / Feedback</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @php
                                    $resultMap = [
                                        'PASSED'    => ['label' => 'Sesuai', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                        'FAILED'    => ['label' => 'Belum sesuai', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                        'BUG_FOUND' => ['label' => 'Ada kendala', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                    ];
                                @endphp
                                @foreach($items as $index => $item)
                                    @php
                                        $res = $resultMap[$item['result'] ?? ''] ?? ['label' => 'Belum dinilai', 'class' => 'bg-slate-50 text-slate-600 border-slate-200'];
                                        $note = $item['bug_description'] ?? $item['client_feedback'] ?? null;
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 transition-colors align-top">
                                        <td class="px-4 py-3.5 text-center font-semibold text-xs text-slate-400">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <p class="font-bold text-slate-900 leading-snug">{{ $item['feature_name'] }}</p>
                                            @if(!empty($item['test_scenario']))
                                                <p class="mt-1 text-xs text-slate-500 leading-relaxed">{{ $item['test_scenario'] }}</p>
                                            @endif
                                            @if(!empty($item['tasks']))
                                                <div class="mt-2 space-y-1">
                                                    @foreach($item['tasks'] as $task)
                                                        <div class="inline-flex items-center gap-1.5 text-[11px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                                                            <span class="w-1.5 h-1.5 rounded-full {{ $task['status'] === 'DONE' ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                                            <span>{{ $task['title'] }} — {{ $task['assignee']['name'] ?? 'Mahasiswa' }} · {{ $task['status'] === 'DONE' ? 'Selesai' : 'Sedang dikerjakan' }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                            <span class="inline-block text-xs font-bold px-2.5 py-1 rounded-full border {{ $res['class'] }}">
                                                {{ $res['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            @if(!empty($note))
                                                <p class="text-xs text-slate-700 leading-relaxed">{{ $note }}</p>
                                            @else
                                                <span class="text-xs text-slate-300 italic">— Tidak ada catatan —</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            @if($isSigned)
                <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">
                    Hasil UAT telah disetujui oleh <strong>{{ $uat['client_signoff_name'] }}</strong> pada {{ \Carbon\Carbon::parse($signedAt)->translatedFormat('d F Y, H:i') }}.
                </section>
            @endif

            @if(count($history) > 1)
                <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="font-bold text-slate-900">Riwayat Pengujian</h2>
                    <ol class="mt-3 space-y-2 text-sm text-slate-600">
                        @foreach($history as $session)
                            @php
                                $histResult = ['PASSED' => 'Sesuai', 'PARTIAL' => 'Sebagian sesuai', 'FAILED' => 'Perlu perbaikan'][$session['overall_result'] ?? ''] ?? ($session['overall_result'] ?? '—');
                                $histStatus = ['PENDING_REVIEW' => 'Menunggu pemeriksaan', 'NEEDS_IMPROVEMENT' => 'Perlu perbaikan', 'READY_FOR_RETEST' => 'Siap diuji ulang', 'APPROVED' => 'Disetujui'][$session['review_status'] ?? ''] ?? ($session['review_status'] ?? '—');
                            @endphp
                            <li>Sesi {{ $session['id'] }} · {{ !empty($session['session_date']) ? \Carbon\Carbon::parse($session['session_date'])->translatedFormat('d M Y') : '—' }} · {{ $histResult }} · {{ $histStatus }}</li>
                        @endforeach
                    </ol>
                </section>
            @endif
        @endif
    </main>
</div>
</body>
</html>
