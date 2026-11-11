<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Serah Terima &amp; BAST - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user');
    $projects = is_array($projects ?? null) ? $projects : [];
    $eligibleProjects = collect($projects)->filter(fn ($project) => !empty($project['bast_eligible']) && empty($project['bast_prepared']))->count();
    $pendingApprovals = collect($projects)->filter(fn ($project) => !empty($project['bast_prepared']))->count();
    $completedHandovers = collect($projects)->filter(fn ($project) => !empty($project['client_approved']))->count();
@endphp
<div class="flex min-h-screen">
    <x-sidebar active="bast" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 space-y-7" x-data="{ searchQuery: '' }">
            <header class="rounded-2xl border border-slate-100 bg-white px-8 py-6 shadow-sm">
                <span class="mb-2.5 inline-flex items-center gap-1.5 rounded-full bg-[#EAF3FF] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-[#0B3B82]">Teaching Factory · Polines</span>
                <h1 class="text-2xl font-bold text-[#1E293B]">Serah Terima &amp; <span class="text-[#FFB800]">BAST</span></h1>
                <p class="mt-1.5 text-sm text-slate-500">Pengurus TeFa menyiapkan dokumen serah terima setelah UAT lulus. PIC Klien meninjau dan menyetujuinya.</p>
            </header>

            @if(session('success'))
                <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(!empty($errors) && $errors->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                    <p class="font-semibold">BAST belum berhasil disimpan:</p>
                    <ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $errorItem)<li>{{ $errorItem }}</li>@endforeach</ul>
                </div>
            @endif
            @if(session('error'))
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">{{ session('error') }}</div>
            @endif
            @if(!empty($error))
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">{{ $error }}</div>
            @endif

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3" aria-label="Ringkasan BAST">
                <article class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Siap disiapkan</p>
                    <p class="mt-2 text-2xl font-bold text-[#0B3B82]">{{ $eligibleProjects }}</p>
                    <p class="mt-1 text-xs text-slate-500">UAT terakhir lulus</p>
                </article>
                <article class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Menunggu PIC Klien</p>
                    <p class="mt-2 text-2xl font-bold text-amber-600">{{ $pendingApprovals }}</p>
                    <p class="mt-1 text-xs text-slate-500">BAST sudah disiapkan</p>
                </article>
                <article class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Serah terima selesai</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $completedHandovers }}</p>
                    <p class="mt-1 text-xs text-slate-500">Telah disetujui klien</p>
                </article>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-[15px] font-semibold text-[#1E293B]">Daftar Proyek</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Status UAT dan persetujuan BAST tersinkron dengan database.</p>
                    </div>
                    <label class="block w-full sm:w-80">
                        <span class="sr-only">Cari proyek, klien, atau kode proyek</span>
                        <input type="search" x-model.debounce.150ms="searchQuery" placeholder="Cari proyek atau klien"
                               class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[#0B3B82] focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10">
                    </label>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="px-6 py-3 text-left text-[11px] font-semibold text-slate-400">Proyek</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400">Klien</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400">UAT terakhir</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400">Status BAST</th>
                                <th class="px-6 py-3 text-right text-[11px] font-semibold text-slate-400">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($projects as $project)
                                @php
                                    $projectCode = $project['project_code'] ?? ('Proposal #' . ($project['id'] ?? '—'));
                                    $searchable = strtolower($projectCode . ' ' . ($project['title'] ?? '') . ' ' . ($project['client_name'] ?? ''));
                                    $isApproved = !empty($project['client_approved']);
                                    $isPrepared = !empty($project['bast_prepared']);
                                    $isEligible = !empty($project['bast_eligible']);
                                @endphp
                                <tr data-search="{{ $searchable }}" x-show="!searchQuery || $el.dataset.search.includes(searchQuery.toLowerCase().trim())">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-[#1E293B]">{{ $project['title'] ?? 'Proyek tanpa judul' }}</p>
                                        <p class="mt-1 font-mono text-[11px] text-slate-400">{{ $projectCode }}</p>
                                    </td>
                                    <td class="px-3 py-4 text-xs text-slate-600">{{ $project['client_name'] ?? '—' }}</td>
                                    <td class="px-3 py-4 text-xs text-slate-600">{{ $project['latest_uat_result'] ?? 'Belum ada hasil' }}</td>
                                    <td class="px-3 py-4">
                                        @if($isApproved)
                                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Disetujui PIC</span>
                                        @elseif($isPrepared)
                                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">Menunggu PIC</span>
                                        @elseif($isEligible)
                                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700">Siap disiapkan</span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">Menunggu UAT lulus</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if(!$isApproved && $isEligible)
                                            <details class="inline-block text-left">
                                                <summary class="cursor-pointer list-none rounded-lg bg-[#0B3B82] px-3.5 py-2 text-xs font-semibold text-white hover:bg-[#082d64]">
                                                    {{ $isPrepared ? 'Perbarui BAST' : 'Siapkan BAST' }}
                                                </summary>
                                                <div class="absolute right-8 z-20 mt-2 w-[min(42rem,calc(100vw-20rem))] rounded-xl border border-slate-200 bg-white p-5 text-left shadow-xl">
                                                    <form method="POST" enctype="multipart/form-data" action="{{ route('pengurus-tefa.bast.prepare', $project['id']) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                        @csrf
                                                        <div class="md:col-span-2">
                                                            <h3 class="text-sm font-bold text-slate-800">Data serah terima · {{ $projectCode }}</h3>
                                                            <p class="mt-1 text-xs text-slate-500">BAST akan dikirim ke PIC Klien untuk diperiksa dan disetujui.</p>
                                                        </div>
                                                        <div>
                                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Nomor BAST</label>
                                                            <input type="text" value="{{ $project['bast_number'] ?? 'Dibuat otomatis saat disimpan' }}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs text-slate-500">
                                                        </div>
                                                        <div>
                                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Tanggal serah terima resmi</label>
                                                            <input type="text" value="Dicatat setelah PIC menyetujui" readonly class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs text-slate-500">
                                                        </div>
                                                        <div>
                                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Repository source code</label>
                                                            <input type="url" name="repository_url" required maxlength="255" value="{{ old('repository_url', $project['repository_url'] ?? '') }}" placeholder="https://github.com/..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">
                                                        </div>
                                                        <div>
                                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Lampiran fisik dokumentasi API</label>
                                                            <input type="file" name="documentation_file" accept=".pdf,.doc,.docx" @required(empty($project['documentation_url'])) class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">
                                                            <p class="mt-1 text-[11px] text-slate-400">{{ $isPrepared ? 'Unggah file baru jika dokumentasi perlu diperbarui.' : 'Wajib diisi: PDF, DOC, atau DOCX, maksimal 10 MB.' }}</p>
                                                            @if(!empty($project['documentation_url']))
                                                                <a href="{{ route('pengurus-tefa.bast.documentation', $project['id']) }}" class="mt-1 inline-block text-[11px] font-semibold text-blue-700 underline">Lihat lampiran saat ini</a>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <label class="mb-1 block text-xs font-semibold text-slate-700">URL panduan pengguna</label>
                                                            <input type="url" name="user_manual_url" maxlength="255" value="{{ old('user_manual_url', $project['user_manual_url'] ?? '') }}" placeholder="https://..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">
                                                        </div>
                                                        <div>
                                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Masa garansi (bulan)</label>
                                                            <input type="number" name="maintenance_warranty_months" min="0" required value="{{ old('maintenance_warranty_months', $project['maintenance']['maintenance_warranty_months'] ?? 3) }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">
                                                        </div>
                                                        <div class="md:col-span-2">
                                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Catatan serah terima</label>
                                                            <textarea name="handover_notes" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">{{ old('handover_notes', $project['handover_notes'] ?? '') }}</textarea>
                                                        </div>
                                                        <div class="md:col-span-2" x-data="{ items: {{ \Illuminate\Support\Js::from(!empty($project['handover_deliverables']) ? $project['handover_deliverables'] : [['name' => '', 'type' => 'digital', 'quantity' => 1, 'unit' => 'unit', 'condition' => 'not_applicable', 'notes' => '']]) }}, addItem() { this.items.push({ name: '', type: 'digital', quantity: 1, unit: 'unit', condition: 'not_applicable', notes: '' }) }, removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1) } }">
                                                            <div class="flex items-center justify-between gap-3">
                                                                <div>
                                                                    <label class="block text-xs font-semibold text-slate-700">Hasil/barang yang diserahkan</label>
                                                                    <p class="mt-1 text-[11px] text-slate-500">Cantumkan produk digital maupun barang fisik agar PIC tahu apa yang diterima.</p>
                                                                </div>
                                                                <button type="button" @click="addItem()" class="shrink-0 rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">Tambah item</button>
                                                            </div>
                                                            <input type="hidden" name="handover_deliverables" :value="JSON.stringify(items)">
                                                            <div class="mt-3 space-y-3">
                                                                <template x-for="(item, index) in items" :key="index">
                                                                    <fieldset class="rounded-lg border border-slate-200 p-3">
                                                                        <legend class="px-1 text-[11px] font-semibold text-slate-500" x-text="`Item ${index + 1}`"></legend>
                                                                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                                                            <label class="text-[11px] font-medium text-slate-600">Nama hasil/barang
                                                                                <input type="text" x-model="item.name" required maxlength="255" placeholder="Contoh: Aplikasi manajemen inventaris" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs">
                                                                            </label>
                                                                            <label class="text-[11px] font-medium text-slate-600">Jenis
                                                                                <select x-model="item.type" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs">
                                                                                    <option value="digital">Produk digital</option>
                                                                                    <option value="physical">Barang fisik</option>
                                                                                    <option value="document">Dokumen</option>
                                                                                    <option value="other">Lainnya</option>
                                                                                </select>
                                                                            </label>
                                                                            <div class="grid grid-cols-2 gap-2">
                                                                                <label class="text-[11px] font-medium text-slate-600">Jumlah
                                                                                    <input type="number" x-model="item.quantity" min="1" max="1000000" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs">
                                                                                </label>
                                                                                <label class="text-[11px] font-medium text-slate-600">Satuan
                                                                                    <input type="text" x-model="item.unit" required maxlength="50" placeholder="unit, set, file" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs">
                                                                                </label>
                                                                            </div>
                                                                            <label class="text-[11px] font-medium text-slate-600">Kondisi
                                                                                <select x-model="item.condition" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs">
                                                                                    <option value="new">Baru</option>
                                                                                    <option value="good">Baik</option>
                                                                                    <option value="needs_repair">Perlu perbaikan</option>
                                                                                    <option value="not_applicable">Tidak berlaku (produk digital)</option>
                                                                                </select>
                                                                            </label>
                                                                            <label class="text-[11px] font-medium text-slate-600 md:col-span-2">Keterangan
                                                                                <textarea x-model="item.notes" rows="2" maxlength="2000" placeholder="Detail isi, spesifikasi, atau cara menerima barang/hasil" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs"></textarea>
                                                                            </label>
                                                                            <div class="md:col-span-2 text-right">
                                                                                <button type="button" @click="removeItem(index)" :disabled="items.length === 1" class="text-[11px] font-semibold text-red-600 disabled:cursor-not-allowed disabled:opacity-40">Hapus item</button>
                                                                            </div>
                                                                        </div>
                                                                    </fieldset>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="md:col-span-2">
                                                            <label class="mb-1 block text-xs font-semibold text-slate-700">Catatan pemeliharaan</label>
                                                            <textarea name="maintenance_notes" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700">{{ old('maintenance_notes', $project['maintenance']['maintenance_notes'] ?? '') }}</textarea>
                                                        </div>
                                                        <div class="md:col-span-2 flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                                                            <p class="text-[11px] text-slate-500">Tanggal serah terima dan awal garansi ditetapkan sistem setelah persetujuan PIC.</p>
                                                            <button type="submit" class="shrink-0 rounded-lg bg-[#0B3B82] px-4 py-2 text-xs font-semibold text-white hover:bg-[#082d64]">Kirim ke PIC Klien</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </details>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada data proyek di database.</td></tr>
                            @endforelse
                            <tr x-cloak x-show="searchQuery && Array.from($el.parentElement.querySelectorAll('tr[data-search]')).every(row => !row.dataset.search.includes(searchQuery.toLowerCase().trim()))">
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Tidak ada proyek yang sesuai dengan pencarian.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
        <footer class="border-t border-slate-100 bg-white px-8 py-4">
            <p class="text-[11px] font-medium text-slate-400">© {{ date('Y') }} TEVARA · Politeknik Negeri Semarang · Teaching Factory</p>
        </footer>
    </div>
</div>
</body>
</html>
