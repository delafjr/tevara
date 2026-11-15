<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Siapkan BAST - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $project = is_array($project ?? null) ? $project : [];
    $projectCode = $project['project_code'] ?? ('Proposal #' . ($project['id'] ?? '—'));
    $deliverables = !empty($project['handover_deliverables'])
        ? $project['handover_deliverables']
        : [['name' => '', 'type' => 'digital', 'quantity' => 1, 'unit' => 'unit', 'condition' => 'not_applicable', 'notes' => '']];
@endphp
<div class="flex min-h-screen">
    <x-sidebar active="bast" />
    <div class="ml-64 flex min-h-screen flex-1 flex-col">
        <main class="flex-1 space-y-6 px-8 py-7">
            <a href="{{ route('pengurus-tefa.bast') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali ke Daftar BAST
            </a>

            @if(session('error'))
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">{{ session('error') }}</div>
            @endif
            @if(!empty($errors) && $errors->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                    <p class="font-semibold">Periksa kembali data BAST:</p>
                    <ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $errorItem)<li>{{ $errorItem }}</li>@endforeach</ul>
                </div>
            @endif

            <header class="relative overflow-hidden rounded-2xl border border-slate-100 bg-white px-8 py-6 shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60"></div>
                <div class="relative">
                    <span class="mb-2 inline-flex rounded-full bg-[#EAF3FF] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-[#0B3B82]">BAST · Persiapan Pengurus TeFa</span>
                    <h1 class="text-2xl font-bold text-[#1E293B]">Siapkan <span class="text-[#FFB800]">Serah Terima</span></h1>
                    <p class="mt-1.5 text-sm text-slate-500">Lengkapi apa saja yang diterima klien. Setelah dikirim, PIC akan meninjau dan menyetujui BAST.</p>
                </div>
            </header>

            <section class="rounded-2xl border border-slate-100 bg-white px-7 py-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">{{ $project['title'] ?? 'Proyek' }}</h2>
                        <p class="mt-1 font-mono text-xs text-slate-500">{{ $projectCode }}</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">UAT {{ $project['latest_uat_result'] ?? 'PASSED' }} · Ditandatangani klien</span>
                </div>

                <form method="POST" enctype="multipart/form-data" action="{{ route('pengurus-tefa.bast.prepare', $project['id']) }}" class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2" x-data="{ items: {{ \Illuminate\Support\Js::from($deliverables) }}, addItem() { this.items.push({ name: '', type: 'digital', quantity: 1, unit: 'unit', condition: 'not_applicable', notes: '' }) }, removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1) } }">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Nomor BAST</label>
                        <input type="text" value="{{ $project['bast_number'] ?? 'Dibuat otomatis saat disimpan' }}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm text-slate-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Tanggal serah terima resmi</label>
                        <input type="text" value="Dicatat setelah PIC menyetujui" readonly class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm text-slate-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Repository source code</label>
                        <input type="url" name="repository_url" required maxlength="255" value="{{ old('repository_url', $project['repository_url'] ?? '') }}" placeholder="https://github.com/..." class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Lampiran fisik dokumentasi API</label>
                        <input type="file" name="documentation_file" accept=".pdf,.doc,.docx" @required(empty($project['documentation_url'])) class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700">
                        <p class="mt-1 text-[11px] text-slate-400">{{ !empty($project['documentation_url']) ? 'Opsional: unggah jika ingin mengganti lampiran.' : 'Wajib diisi: PDF, DOC, atau DOCX, maksimal 10 MB.' }}</p>
                        @if(!empty($project['documentation_url']))
                            <a href="{{ route('pengurus-tefa.bast.documentation', $project['id']) }}" class="mt-1 inline-block text-xs font-semibold text-blue-700 underline">Lihat lampiran saat ini</a>
                        @endif
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">URL panduan pengguna</label>
                        <input type="url" name="user_manual_url" maxlength="255" value="{{ old('user_manual_url', $project['user_manual_url'] ?? '') }}" placeholder="https://..." class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Masa garansi (bulan)</label>
                        <input type="number" name="maintenance_warranty_months" min="0" required value="{{ old('maintenance_warranty_months', $project['maintenance']['maintenance_warranty_months'] ?? 3) }}" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Catatan serah terima</label>
                        <textarea name="handover_notes" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700">{{ old('handover_notes', $project['handover_notes'] ?? '') }}</textarea>
                    </div>

                    <section class="space-y-4 md:col-span-2" aria-labelledby="deliverables-title">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 id="deliverables-title" class="text-sm font-bold text-slate-800">Hasil/barang yang diserahkan</h2>
                                <p class="mt-1 text-xs text-slate-500">Daftar ini akan ditinjau klien sebelum menyetujui BAST.</p>
                            </div>
                            <button type="button" @click="addItem()" class="rounded-lg border border-blue-200 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">Tambah item</button>
                        </div>
                        <input type="hidden" name="handover_deliverables" :value="JSON.stringify(items)">
                        <template x-for="(item, index) in items" :key="index">
                            <fieldset class="rounded-xl border border-slate-200 p-4">
                                <legend class="px-1 text-xs font-semibold text-slate-500" x-text="`Item ${index + 1}`"></legend>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <label class="text-xs font-medium text-slate-600">Nama hasil/barang
                                        <input type="text" x-model="item.name" required maxlength="255" placeholder="Contoh: Aplikasi manajemen inventaris" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                    </label>
                                    <label class="text-xs font-medium text-slate-600">Jenis
                                        <select x-model="item.type" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                            <option value="digital">Produk digital</option><option value="physical">Barang fisik</option><option value="document">Dokumen</option><option value="other">Lainnya</option>
                                        </select>
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="text-xs font-medium text-slate-600">Jumlah
                                            <input type="number" x-model="item.quantity" min="1" max="1000000" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                        </label>
                                        <label class="text-xs font-medium text-slate-600">Satuan
                                            <input type="text" x-model="item.unit" required maxlength="50" placeholder="unit, set, file" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                        </label>
                                    </div>
                                    <label class="text-xs font-medium text-slate-600">Kondisi
                                        <select x-model="item.condition" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                            <option value="new">Baru</option><option value="good">Baik</option><option value="needs_repair">Perlu perbaikan</option><option value="not_applicable">Tidak berlaku (digital)</option>
                                        </select>
                                    </label>
                                    <label class="text-xs font-medium text-slate-600 md:col-span-2">Keterangan
                                        <textarea x-model="item.notes" rows="2" maxlength="2000" placeholder="Detail isi, spesifikasi, atau cara menerima barang/hasil" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></textarea>
                                    </label>
                                    <div class="text-right md:col-span-3">
                                        <button type="button" @click="removeItem(index)" :disabled="items.length === 1" class="text-xs font-semibold text-red-600 disabled:opacity-40">Hapus item</button>
                                    </div>
                                </div>
                            </fieldset>
                        </template>
                    </section>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Catatan pemeliharaan</label>
                        <textarea name="maintenance_notes" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700">{{ old('maintenance_notes', $project['maintenance']['maintenance_notes'] ?? '') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between md:col-span-2">
                        <p class="text-xs text-slate-500">Tanggal serah terima dan awal garansi ditetapkan sistem setelah persetujuan PIC.</p>
                        <div class="flex gap-2">
                            <a href="{{ route('pengurus-tefa.bast') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
                            <button type="submit" class="rounded-lg bg-[#0B3B82] px-5 py-2.5 text-xs font-semibold text-white hover:bg-[#082d64]">Kirim ke PIC Klien</button>
                        </div>
                    </div>
                </form>
            </section>
        </main>
    </div>
</div>
</body>
</html>
