<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pemeriksaan UAT — {{ $proposal['title'] ?? 'Proyek' }} - Tevara</title>
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
    $members = $members ?? [];
    $reviewStatus = $uat['review_status'] ?? null;
    $signedAt = $uat['signed_off_at'] ?? null;
    $isSigned = !empty($signedAt);
    $canReview = $uat && $reviewStatus === 'PENDING_REVIEW' && ($uat['overall_result'] ?? null) === 'PASSED';
    $failedItems = collect($items)->filter(fn ($item) => in_array($item['result'] ?? null, ['FAILED', 'BUG_FOUND'], true));
    $history = $uat['uat_history'] ?? ($uat ? [$uat] : []);
@endphp
<div class="flex min-h-screen">
    <x-sidebar active="uat" />
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
            <a href="{{ route('pengurus-tefa.uat') }}" class="text-sm font-semibold text-slate-500 hover:text-[#0B3B82]">← Kembali ke daftar UAT</a>
            <span class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 font-mono text-xs font-bold text-slate-500">{{ $proposalCode }}</span>
        </div>

        <section class="rounded-2xl border border-slate-100 bg-white p-7 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-[#0B3B82]">UAT · Pemeriksaan Pengujian Penerimaan Pengguna</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $title }}</h1>
            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
                Periksa hasil yang dikirim Klien. Jika ada fitur yang belum sesuai, buat poin tindak lanjut dan tetapkan mahasiswa yang mengerjakannya. Pengurus tidak mengubah hasil uji Klien.
            </p>
            <div class="mt-4 flex flex-wrap gap-4 text-xs font-medium text-slate-500">
                <span>Klien: <strong class="text-slate-800">{{ $proposal['client']['name'] ?? $proposal['client_name'] ?? '—' }}</strong></span>
                <span>Perusahaan: <strong class="text-slate-800">{{ $proposal['client']['company_name'] ?? $proposal['client_company_name'] ?? '—' }}</strong></span>
            </div>
        </section>

        @if(!$hasUat)
            <section class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-600">
                Belum ada hasil UAT dari Klien. Setelah Klien mengirim hasil pengujian, sesi akan muncul di halaman ini.
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
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 mb-1">Catatan Umum Klien</p>
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
                                    <th class="px-4 py-3">Catatan / Feedback Klien</th>
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

            @if($canReview)
                @if(($uat['overall_result'] ?? null) === 'PASSED')
                    <form action="{{ route('pengurus-tefa.uat.review', [$proposalId, $uat['id']]) }}" method="POST" class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
                        @csrf
                        <input type="hidden" name="decision" value="APPROVED">
                        <h2 class="text-lg font-bold text-slate-900">Setujui Hasil Pemeriksaan</h2>
                        <p class="mt-1 text-sm text-slate-600">Seluruh fitur dinyatakan sesuai oleh klien. Persetujuan ini akan membuka tahapan sign-off untuk klien.</p>
                        <label for="review_notes" class="mt-4 block text-xs font-semibold text-slate-600">Catatan untuk Klien (opsional)</label>
                        <textarea id="review_notes" name="review_notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/30 focus:border-[#0B3B82]"></textarea>
                        <button class="mt-4 rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-800 transition">Setujui Hasil UAT</button>
                    </form>
                @endif
            @elseif($reviewStatus === 'PENDING_REVIEW')
                <section class="rounded-2xl border border-blue-200 bg-blue-50 p-5 text-sm text-blue-900">
                    Klien telah mengirim hasil pengujian UAT. Tinjauan dan penerusan tindak lanjut perbaikan kepada mahasiswa dilakukan oleh Project Manager (PM).
                </section>
            @elseif($reviewStatus === 'NEEDS_IMPROVEMENT')
                <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                    Checklist perbaikan sedang dikerjakan. UAT ulang belum dibuka sampai seluruh tugas diselesaikan dan diverifikasi.
                </section>
            @elseif($reviewStatus === 'READY_FOR_RETEST')
                <section class="rounded-2xl border border-blue-200 bg-blue-50 p-6 text-sm text-blue-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-600 text-white">Siap Uji Ulang</span>
                            <span class="text-xs text-blue-700 font-semibold">Tindak lanjut selesai diverifikasi</span>
                        </div>
                        <p class="text-slate-600">Seluruh tugas perbaikan telah diselesaikan oleh mahasiswa dan disetujui Dosen PM. Buka sesi pengujian ulang agar Klien menerima notifikasi dan dapat mengisi lembar UAT terbaru.</p>
                    </div>
                    @if(strtolower($proposal['status']['name'] ?? '') !== 'testing' && (int) ($proposal['status_id'] ?? 0) !== 8)
                        <form action="{{ route('pengurus-tefa.uat.status-testing', $proposalId) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-[#0B3B82] text-white hover:bg-[#082a5c] shadow-sm transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                Buka Sesi Uji Ulang &amp; Beritahu Klien
                            </button>
                        </form>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-bold flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Fase Testing Aktif (Menunggu Uji Klien)
                        </span>
                    @endif
                </section>
            @elseif($reviewStatus === 'APPROVED')
                <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">
                    @if($isSigned)
                        Hasil UAT telah disetujui Pengurus dan telah disahkan secara resmi oleh <strong>{{ $uat['client_signoff_name'] }}</strong> pada {{ \Carbon\Carbon::parse($signedAt)->translatedFormat('d F Y, H:i') }}.
                    @else
                        Hasil pemeriksaan telah disetujui oleh Pengurus. Saat ini menunggu pengesahan (sign-off) resmi dari pihak Klien sebelum proses BAST dimulai.
                    @endif
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
