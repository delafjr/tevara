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
    $uat = is_array($uat ?? null) ? $uat : null;
    $items = $uat['uat_items'] ?? [];
    $history = $uat['uat_history'] ?? ($uat ? [$uat] : []);
@endphp
<div class="flex min-h-screen">
    <x-sidebar active="project" />
    <main class="ml-64 flex-1 space-y-6 px-8 py-7">
        <a href="{{ route('pm.project') }}" class="text-sm font-semibold text-slate-500 hover:text-[#0B3B82]">← Kembali ke proyek</a>
        <section class="rounded-2xl border border-slate-100 bg-white p-7 shadow-sm mt-4">
            <p class="text-xs font-bold uppercase tracking-wide text-[#0B3B82]">Ringkasan UAT · hanya untuk dilihat</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $proposal['title'] ?? 'Proyek' }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $proposal['proposal_code'] ?? '' }}</p>
        </section>

        @if(!$uat)
            <section class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-600">Klien belum mengirim hasil pengujian.</section>
        @else
            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs text-slate-400">Sesi terbaru · {{ $uat['session_date'] ?? '' }}</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Hasil {{ $uat['overall_result'] ?? '—' }}</h2>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800">{{ $uat['review_status'] ?? 'Menunggu pemeriksaan' }}</span>
                </div>
                @if(!empty($uat['review_notes']))
                    <p class="mt-4 rounded-lg bg-slate-50 p-4 text-sm text-slate-700"><strong>Catatan Pengurus:</strong> {{ $uat['review_notes'] }}</p>
                @endif
                <div class="mt-5 space-y-3">
                    @foreach($items as $item)
                        <article class="rounded-xl border border-slate-200 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="font-semibold text-slate-900">{{ $item['feature_name'] }}</h3>
                                <span class="text-xs font-bold text-slate-600">{{ $item['result'] }}</span>
                            </div>
                            @if(!empty($item['test_scenario']))
                                <p class="mt-2 text-sm text-slate-600">{{ $item['test_scenario'] }}</p>
                            @endif
                            @if(!empty($item['client_feedback']) || !empty($item['bug_description']))
                                <p class="mt-2 text-sm text-slate-600">{{ $item['bug_description'] ?? $item['client_feedback'] }}</p>
                            @endif
                            @if(!empty($item['review_notes']))
                                <p class="mt-2 rounded-lg bg-amber-50 p-3 text-sm text-amber-900"><strong>Tindak lanjut:</strong> {{ $item['review_notes'] }}</p>
                            @endif
                            @foreach(($item['tasks'] ?? []) as $task)
                                <p class="mt-2 text-xs text-slate-500">{{ $task['title'] }} — {{ $task['assignee']['name'] ?? 'Belum ditugaskan' }} · {{ $task['status'] }}</p>
                            @endforeach
                        </article>
                    @endforeach
                </div>
            </section>
            @if(count($history) > 1)
                <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="font-bold text-slate-900">Riwayat sesi UAT</h2>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        @foreach($history as $session)
                            <li>Sesi {{ $session['id'] }} · {{ $session['session_date'] }} · {{ $session['overall_result'] }} · {{ $session['review_status'] ?? '—' }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @endif
    </main>
</div>
</body>
</html>
