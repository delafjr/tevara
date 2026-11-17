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
    $uat = is_array($uat ?? null) ? $uat : null;
    $items = $uat['uat_items'] ?? [];
    $members = $members ?? [];
    $reviewStatus = $uat['review_status'] ?? null;
    $canReview = $uat && $reviewStatus === 'PENDING_REVIEW';
    $failedItems = collect($items)->filter(fn ($item) => in_array($item['result'] ?? null, ['FAILED', 'BUG_FOUND'], true));
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

        <div class="flex items-center justify-between">
            <a href="{{ route('pengurus-tefa.uat') }}" class="text-sm font-semibold text-slate-500 hover:text-[#0B3B82]">← Kembali ke daftar UAT</a>
            <span class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 font-mono text-xs font-bold text-slate-500">{{ $proposal['proposal_code'] ?? '' }}</span>
        </div>

        <section class="rounded-2xl border border-slate-100 bg-white p-7 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-[#0B3B82]">Pemeriksaan hasil UAT</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $proposal['title'] ?? 'Proyek' }}</h1>
            <p class="mt-2 text-sm text-slate-600">Periksa hasil yang dikirim Klien. Jika ada fitur yang belum sesuai, buat poin tindak lanjut dan tetapkan mahasiswa yang mengerjakannya. Pengurus tidak mengubah hasil uji Klien.</p>
            <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-600">
                <span>Klien: <strong>{{ $proposal['client']['name'] ?? $proposal['client_name'] ?? '—' }}</strong></span>
                <span>Perusahaan: <strong>{{ $proposal['client']['company_name'] ?? $proposal['client_company_name'] ?? '—' }}</strong></span>
            </div>
        </section>

        @if(!$uat)
            <section class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-600">
                Belum ada hasil UAT dari Klien. Setelah Klien mengirim hasil pengujian, sesi akan muncul di halaman ini.
            </section>
        @else
            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Dikirim {{ $uat['session_date'] ?? '' }}</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Hasil keseluruhan: {{ ['PASSED' => 'Sesuai', 'PARTIAL' => 'Sebagian sesuai', 'FAILED' => 'Perlu perbaikan'][$uat['overall_result'] ?? ''] ?? '—' }}</h2>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800">{{ $reviewStatus ?? 'Menunggu pemeriksaan' }}</span>
                </div>
                @if(!empty($uat['client_notes']))
                    <p class="mt-4 whitespace-pre-line rounded-lg bg-slate-50 p-4 text-sm text-slate-700">{{ $uat['client_notes'] }}</p>
                @endif
            </section>

            <section class="space-y-4">
                @foreach($items as $item)
                    <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="font-bold text-slate-900">{{ $item['feature_name'] }}</h3>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ ['PASSED' => 'Sesuai', 'FAILED' => 'Belum sesuai', 'BUG_FOUND' => 'Ada kendala'][$item['result'] ?? ''] ?? '—' }}</span>
                        </div>
                        @if(!empty($item['test_scenario']))
                            <p class="mt-2 text-sm text-slate-600">{{ $item['test_scenario'] }}</p>
                        @endif
                        @if(!empty($item['bug_description']) || !empty($item['client_feedback']))
                            <p class="mt-2 text-sm text-slate-700">{{ $item['bug_description'] ?? $item['client_feedback'] }}</p>
                        @endif
                        @if(!empty($item['review_notes']))
                            <p class="mt-3 rounded-lg bg-amber-50 p-3 text-sm text-amber-900"><strong>Catatan tindak lanjut:</strong> {{ $item['review_notes'] }}</p>
                        @endif
                        @foreach(($item['tasks'] ?? []) as $task)
                            <p class="mt-2 text-xs text-slate-500">Checklist: {{ $task['title'] }} — {{ $task['assignee']['name'] ?? 'Belum ditugaskan' }} · {{ $task['status'] }}</p>
                        @endforeach
                    </article>
                @endforeach
            </section>

            @if($canReview)
                @if(($uat['overall_result'] ?? null) === 'PASSED')
                    <form action="{{ route('pengurus-tefa.uat.review', [$proposalId, $uat['id']]) }}" method="POST" class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
                        @csrf
                        <input type="hidden" name="decision" value="APPROVED">
                        <h2 class="text-lg font-bold text-slate-900">Setujui hasil pemeriksaan</h2>
                        <label for="review_notes" class="mt-4 block text-xs font-semibold text-slate-600">Catatan untuk Klien (opsional)</label>
                        <textarea id="review_notes" name="review_notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        <button class="mt-4 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">Setujui hasil UAT</button>
                    </form>
                @else
                    <form action="{{ route('pengurus-tefa.uat.review', [$proposalId, $uat['id']]) }}" method="POST" class="rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
                        @csrf
                        <input type="hidden" name="decision" value="NEEDS_IMPROVEMENT">
                        <h2 class="text-lg font-bold text-slate-900">Buat checklist perbaikan</h2>
                        <p class="mt-1 text-sm text-slate-600">Setiap fitur yang belum sesuai harus memiliki catatan dan mahasiswa penanggung jawab.</p>
                        @foreach($failedItems as $index => $item)
                            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50/50 p-4">
                                <input type="hidden" name="corrections[{{ $index }}][item_id]" value="{{ $item['id'] }}">
                                <h3 class="font-semibold text-slate-900">{{ $item['feature_name'] }}</h3>
                                <label for="note-{{ $index }}" class="mt-3 block text-xs font-semibold text-slate-600">Poin perbaikan <span class="text-red-600">*</span></label>
                                <textarea id="note-{{ $index }}" name="corrections[{{ $index }}][note]" required rows="2" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm" placeholder="Jelaskan apa yang perlu diperbaiki.">{{ old("corrections.$index.note") }}</textarea>
                                <label for="student-{{ $index }}" class="mt-3 block text-xs font-semibold text-slate-600">Teruskan kepada mahasiswa <span class="text-red-600">*</span></label>
                                <select id="student-{{ $index }}" name="corrections[{{ $index }}][assigned_student_id]" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                    <option value="">Pilih mahasiswa</option>
                                    @foreach($members as $member)
                                        @php($student = $member['student'] ?? [])
                                        <option value="{{ $student['id'] ?? '' }}">{{ $student['name'] ?? 'Mahasiswa' }}{{ !empty($student['nim']) ? ' · ' . $student['nim'] : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                        <label for="general_review_notes" class="mt-5 block text-xs font-semibold text-slate-600">Catatan tambahan untuk Klien (opsional)</label>
                        <textarea id="general_review_notes" name="review_notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @if($failedItems->count() === 0)
                            <p class="mt-4 text-sm text-red-700">Tidak ada fitur gagal yang dapat dijadikan checklist perbaikan.</p>
                        @elseif(count($members) === 0)
                            <p class="mt-4 text-sm text-red-700">Belum ada mahasiswa pada tim proyek ini. Lengkapi tim sebelum meneruskan perbaikan.</p>
                        @else
                            <button class="mt-5 rounded-xl bg-amber-700 px-5 py-3 text-sm font-bold text-white hover:bg-amber-800">Kirim checklist ke mahasiswa</button>
                        @endif
                    </form>
                @endif
            @elseif($reviewStatus === 'NEEDS_IMPROVEMENT')
                <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">Checklist perbaikan sedang dikerjakan. UAT ulang belum dibuka sampai seluruh tugas diselesaikan dan diverifikasi.</section>
            @elseif($reviewStatus === 'READY_FOR_RETEST')
                <section class="rounded-2xl border border-blue-200 bg-blue-50 p-5 text-sm text-blue-900">Seluruh checklist telah diverifikasi. Klien dapat membuka proyek dan mengirim hasil pengujian ulang.</section>
            @elseif($reviewStatus === 'APPROVED')
                <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">Hasil pemeriksaan disetujui. Klien dapat mengesahkan UAT sebelum proses BAST dimulai.</section>
            @endif
        @endif
    </main>
</div>
</body>
</html>
