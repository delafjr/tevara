<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tinjau BAST - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $project = is_array($project ?? null) ? $project : [];
    $maintenance = is_array($project['maintenance'] ?? null) ? $project['maintenance'] : [];
    $deliverables = is_array($project['handover_deliverables'] ?? null) ? $project['handover_deliverables'] : [];
    $typeLabels = ['physical' => 'Barang fisik', 'digital' => 'Produk digital', 'document' => 'Dokumen', 'other' => 'Lainnya'];
    $conditionLabels = ['new' => 'Baru', 'good' => 'Baik', 'needs_repair' => 'Perlu perbaikan', 'not_applicable' => 'Tidak berlaku'];
@endphp
<div class="flex min-h-screen">
    <x-sidebar active="projek-saya" />
    <div class="ml-64 flex min-h-screen flex-1 flex-col">
        <main class="flex-1 space-y-6 px-8 py-7">
            <a href="{{ route('klien.projek-saya') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali ke Projek Saya
            </a>
            @if(!empty($error))
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">{{ $error }}</div>
            @endif
            @if(!empty($errors) && $errors->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                    <p class="font-semibold">Persetujuan belum berhasil dikirim:</p>
                    <ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $errorItem)<li>{{ $errorItem }}</li>@endforeach</ul>
                </div>
            @endif
            <header class="relative overflow-hidden rounded-2xl border border-slate-100 bg-white px-8 py-6 shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#E9F9EE] opacity-70"></div>
                <div class="relative">
                    <span class="mb-2 inline-flex rounded-full bg-[#EAF3FF] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-[#0B3B82]">BAST · Tinjauan Klien</span>
                    <h1 class="text-2xl font-bold text-[#1E293B]">Tinjau <span class="text-[#16A34A]">Serah Terima</span></h1>
                    <p class="mt-1.5 text-sm text-slate-500">Pastikan daftar hasil/barang dan dokumen sudah sesuai sebelum menyetujui BAST.</p>
                </div>
            </header>

            <section class="rounded-2xl border border-slate-100 bg-white px-7 py-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">{{ $project['title'] ?? 'Proyek' }}</h2>
                        <p class="mt-1 font-mono text-xs text-slate-500">{{ $project['project_code'] ?? '' }}</p>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Menunggu persetujuan PIC</span>
                </div>
                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Nomor BAST</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $project['bast_number'] ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Tanggal serah terima resmi</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">Dicatat setelah disetujui</p>
                    </div>
                </div>

                <section class="mt-6" aria-labelledby="deliverables-review-title">
                    <h3 id="deliverables-review-title" class="text-sm font-bold text-slate-800">Hasil/barang yang akan diterima</h3>
                    <div class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200">
                        @foreach($deliverables as $deliverable)
                            <article class="grid gap-2 p-4 sm:grid-cols-[1fr_auto]">
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-800">{{ $deliverable['name'] ?? 'Hasil/barang' }}</h4>
                                    @if(!empty($deliverable['notes']))<p class="mt-1 text-xs text-slate-500">{{ $deliverable['notes'] }}</p>@endif
                                </div>
                                <div class="text-xs text-slate-600 sm:text-right">
                                    <p>{{ $typeLabels[$deliverable['type'] ?? 'other'] ?? 'Lainnya' }} · {{ $deliverable['quantity'] ?? 1 }} {{ $deliverable['unit'] ?? 'unit' }}</p>
                                    <p class="mt-1">Kondisi: {{ $conditionLabels[$deliverable['condition'] ?? 'not_applicable'] ?? 'Tidak berlaku' }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                @if(!empty($project['handover_notes']))
                    <section class="mt-5 rounded-xl border border-slate-200 p-4">
                        <h3 class="text-xs font-bold text-slate-700">Catatan serah terima</h3>
                        <p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $project['handover_notes'] }}</p>
                    </section>
                @endif

                <section class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <h3 class="text-xs font-bold text-slate-700">Source code &amp; dokumentasi</h3>
                        @if(!empty($project['repository_url']))<a href="{{ $project['repository_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-2 block break-all text-xs font-semibold text-blue-700 underline">Buka repository source code</a>@endif
                        @if(!empty($project['id']) && !empty($project['documentation_url']))<a href="{{ route('klien.projek-saya.bast.documentation', $project['id']) }}" class="mt-2 block text-xs font-semibold text-blue-700 underline">Unduh dokumentasi API</a>@endif
                        @if(!empty($project['user_manual_url']))<a href="{{ $project['user_manual_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-2 block break-all text-xs font-semibold text-blue-700 underline">Buka panduan pengguna</a>@endif
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <h3 class="text-xs font-bold text-slate-700">Masa garansi</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $maintenance['maintenance_warranty_months'] ?? 0 }} bulan</p>
                        @if(!empty($maintenance['maintenance_notes']))<p class="mt-1 text-xs text-slate-500">{{ $maintenance['maintenance_notes'] }}</p>@endif
                        <p class="mt-2 text-xs text-slate-500">Garansi dimulai sehari setelah persetujuan dan tanggal serah terima resmi.</p>
                    </div>
                </section>

                <form method="POST" action="{{ route('klien.projek-saya.bast.approve', $project['id']) }}" class="mt-6 border-t border-slate-100 pt-5">
                    @csrf
                    @method('PATCH')
                    <label class="block max-w-md text-xs font-semibold text-slate-700">Nama PIC yang menyetujui
                        <input type="text" name="client_signoff_name" required maxlength="255" value="{{ old('client_signoff_name', session('user.name')) }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal text-slate-700">
                    </label>
                    <label class="mt-4 flex items-start gap-2 text-xs text-slate-600">
                        <input type="checkbox" name="client_approved" value="1" required class="mt-0.5 rounded border-slate-300 text-[#0B3B82]">
                        Saya telah memeriksa daftar hasil/barang dan menyetujui serah terima ini.
                    </label>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="{{ route('klien.projek-saya') }}" class="rounded-lg border border-slate-200 px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Kembali</a>
                        <button type="submit" class="rounded-lg bg-[#16A34A] px-5 py-2.5 text-xs font-semibold text-white hover:bg-[#15803D]">Setujui BAST</button>
                    </div>
                </form>
            </section>
        </main>
    </div>
</div>
</body>
</html>
