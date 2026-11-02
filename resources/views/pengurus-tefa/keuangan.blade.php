<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keuangan - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user');
    $firstName = explode(' ', $user['name'] ?? 'Pengurus')[0];
    $pendingPayments = $pendingPayments ?? [];
    $allProposals = $allProposals ?? [];
@endphp

<div class="flex min-h-screen"
     x-data="{
         showCreateModal: false,
         createProposalId: ''
     }">

    <x-sidebar active="keuangan" />

    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 space-y-7">

            {{-- Flash Alert Messages --}}
            @if (session('success'))
                <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800 shadow-xs">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <div>
                        <p class="text-xs font-bold text-emerald-900">Berhasil</p>
                        <p class="text-xs font-medium text-emerald-700 leading-relaxed">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error') || ($error ?? null))
                <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 shadow-xs">
                    <svg class="w-5 h-5 flex-shrink-0 text-red-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    <div>
                        <p class="text-xs font-bold text-red-900">Perhatian</p>
                        <p class="text-xs font-medium text-red-700 leading-relaxed">{{ session('error') ?? $error }}</p>
                    </div>
                </div>
            @endif

            {{-- Hero Card --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6 flex items-center justify-between gap-6">
                {{-- gradient dekoratif --}}
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-bold tracking-wide uppercase px-2.5 py-1 rounded-full mb-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                        Teaching Factory · Polines
                    </span>
                    <h1 class="text-2xl font-bold leading-snug text-[#1E293B]">
                        Rekap <span class="text-[#FFB800]">Keuangan & Pembayaran</span>
                    </h1>
                    <p class="text-sm text-[#64748B] mt-1.5 max-w-2xl leading-relaxed">
                        Kelola termin pembayaran proyek, verifikasi bukti transfer klien (F-09), dan pantau seluruh transaksi TeFa.
                    </p>
                </div>

                <div class="relative z-10 flex-shrink-0 flex items-center gap-3">
                    <button type="button"
                            @click="showCreateModal = true"
                            class="flex items-center gap-2 bg-[#0B3B82] text-white font-semibold text-xs px-4 py-2.5 rounded-xl hover:bg-[#082a5c] transition-colors shadow-sm cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Buat Termin Pembayaran
                    </button>
                    <a href="#" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 font-semibold text-xs px-4 py-2.5 rounded-xl hover:bg-slate-50 transition-colors shadow-2xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Ekspor Laporan
                    </a>
                </div>
            </div>

            {{-- ANTREAN VERIFIKASI PEMBAYARAN (F-09: GET /payments/pending) --}}
            <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-blue-50/50 via-white to-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#0B3B82] text-white flex items-center justify-center shadow-xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-[#1E293B] text-base">Antrean Verifikasi Bukti Pembayaran</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold {{ count($pendingPayments) > 0 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ count($pendingPayments) }} Pembayaran
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">Bukti transfer yang telah diunggah oleh klien dan menunggu konfirmasi Pengurus TEFA</p>
                        </div>
                    </div>
                </div>

                @if(count($pendingPayments) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 bg-[#F8FAFC] text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                                    <th class="px-6 py-3.5">Tanggal Bayar</th>
                                    <th class="px-4 py-3.5">Kode & Judul Proyek</th>
                                    <th class="px-4 py-3.5">Klien / PIC</th>
                                    <th class="px-4 py-3.5">Termin</th>
                                    <th class="px-4 py-3.5">Nominal</th>
                                    <th class="px-4 py-3.5">Metode / Ref</th>
                                    <th class="px-6 py-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($pendingPayments as $p)
                                    <tr class="hover:bg-blue-50/40 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-600 whitespace-nowrap">
                                            {{ !empty($p['payment_date']) ? \Carbon\Carbon::parse($p['payment_date'])->translatedFormat('d M Y') : '-' }}
                                        </td>
                                        <td class="px-4 py-4 min-w-[200px]">
                                            <p class="font-mono text-[11px] font-bold text-[#0B3B82]">{{ $p['proposal']['project_code'] ?? ('PRJ-' . ($p['proposal_id'] ?? '-')) }}</p>
                                            <p class="font-semibold text-slate-800 line-clamp-1 mt-0.5">{{ $p['proposal']['title'] ?? '-' }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <p class="font-semibold text-slate-800">{{ $p['proposal']['client']['name'] ?? 'Klien' }}</p>
                                            <p class="text-[11px] text-slate-400">{{ $p['proposal']['client']['email'] ?? '' }}</p>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-[#EAF3FF] text-[#0B3B82]">
                                                Termin {{ $p['term_sequence'] ?? 1 }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 font-bold text-emerald-600 whitespace-nowrap text-sm">
                                            Rp {{ number_format((float) ($p['amount'] ?? 0), 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-slate-600">
                                            <p class="font-medium">{{ $p['payment_method'] ?? 'Transfer Bank' }}</p>
                                            @if(!empty($p['transfer_reference']))
                                                <p class="text-[10px] text-slate-400 font-mono">Ref: {{ $p['transfer_reference'] }}</p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            @php
                                                $propId = $p['proposal_id'] ?? ($p['proposal']['id'] ?? 1);
                                                $payId = $p['id'] ?? 1;
                                            @endphp
                                            <a href="{{ route('pengurus-tefa.payments.verifikasi', ['proposal' => $propId, 'payment' => $payId]) }}"
                                               class="inline-flex items-center gap-1.5 rounded-xl bg-[#0B3B82] hover:bg-[#082a5c] px-3.5 py-2 text-xs font-bold text-white shadow-xs transition">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                                Periksa & Verifikasi
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800">Tidak Ada Pembayaran yang Menunggu Verifikasi</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                            Semua bukti transfer klien telah diproses. Ketika klien mengunggah bukti bayar baru, data akan otomatis muncul di sini.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Stats Keuangan --}}
            <div class="grid grid-cols-4 gap-4 items-stretch">
                @php
                $keuStats = [
                    ['label' => 'Total Pemasukan', 'value' => 'Rp 218 Jt', 'sub' => '↑ 32% vs 2025',  'color' => '#16A34A', 'icon' => 'check'],
                    ['label' => 'Total Pengeluaran', 'value' => 'Rp 124 Jt', 'sub' => '↑ 18% vs 2025',  'color' => '#DC2626', 'icon' => 'clock-file'],
                    ['label' => 'Saldo Bersih',      'value' => 'Rp 94 Jt',  'sub' => 'Net Positif',    'color' => '#0B3B82', 'icon' => 'folder'],
                    ['label' => 'Antrean Verifikasi','value' => count($pendingPayments) . ' Tagihan',  'sub' => 'Butuh Tindakan', 'color' => '#F59E0B', 'icon' => 'calendar'],
                ];
                @endphp
                @foreach($keuStats as $stat)
                    <x-stat-card :label="$stat['label']" :value="$stat['value']" :sub="$stat['sub']" :color="$stat['color']" :icon="$stat['icon']" />
                @endforeach
            </div>

            {{-- 2 kolom: Chart Bar + Ringkasan --}}
            <div class="grid grid-cols-3 gap-6">

                {{-- Grafik Bar Bulanan --}}
                <div class="col-span-2 bg-white rounded-xl border border-slate-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <p class="font-semibold text-[#1E293B] text-[15px]">Pemasukan & Pengeluaran</p>
                            <p class="text-xs text-slate-400 mt-0.5">Per bulan — Tahun 2026</p>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded bg-[#0B3B82]"></div><span class="text-slate-500">Pemasukan</span></div>
                            <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded bg-[#EF4444]/30 border border-red-300"></div><span class="text-slate-500">Pengeluaran</span></div>
                        </div>
                    </div>
                    @php
                    $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt'];
                    $income  = [8,12,15,18,20,22,25,28,35,35];
                    $expense = [5,8,10,12,13,15,16,18,20,22];
                    $maxVal  = 40;
                    @endphp
                    <div class="flex items-end gap-2 h-44">
                        @foreach($months as $i => $m)
                        <div class="flex-1 flex items-end gap-0.5">
                            <div class="flex-1 rounded-t-sm bg-[#0B3B82]" style="height:{{ round($income[$i]/$maxVal*100) }}%; min-height:4px" title="Pemasukan {{ $m }}: Rp {{ $income[$i] }} Jt"></div>
                            <div class="flex-1 rounded-t-sm bg-red-200" style="height:{{ round($expense[$i]/$maxVal*100) }}%; min-height:4px" title="Pengeluaran {{ $m }}: Rp {{ $expense[$i] }} Jt"></div>
                        </div>
                        @endforeach
                    </div>
                    <div class="flex gap-2 mt-2">
                        @foreach($months as $m)
                        <div class="flex-1 text-center text-[10px] text-slate-400 font-medium">{{ $m }}</div>
                        @endforeach
                    </div>
                </div>

                {{-- Ringkasan Keuangan --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
                    <p class="font-semibold text-[#1E293B] text-[15px] mb-4">Status Tagihan Proyek</p>
                    <div class="space-y-3">
                        @foreach([
                            ['Lunas / Verified','11 projek','#16A34A'],
                            ['Menunggu Verifikasi', count($pendingPayments) . ' tagihan','#F59E0B'],
                            ['Belum Dibayar','5 projek','#EF4444'],
                            ['Gratis/CSR','2 projek','#6366F1'],
                        ] as [$s,$v,$c])
                        <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $c }}"></span>
                                <span class="text-[13px] text-slate-600 font-medium">{{ $s }}</span>
                            </div>
                            <span class="text-[12px] font-bold text-[#1E293B]">{{ $v }}</span>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-xs text-slate-400 mb-2">Target Penerimaan 2026</p>
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#0B3B82] rounded-full" style="width:72%"></div>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-[11px] text-slate-500">Rp 218 Jt</span>
                            <span class="text-[11px] font-bold text-[#0B3B82]">72% dari target</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Riwayat Transaksi --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-[#1E293B] text-[15px]">Riwayat Transaksi Keuangan</p>
                        <p class="text-xs text-slate-400 mt-0.5">Semua transaksi buku kas TeFa</p>
                    </div>
                    <select class="text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0B3B82]">
                        <option>Oktober 2026</option><option>September 2026</option><option>Agustus 2026</option>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 text-[11px] font-semibold">
                                <th class="text-left px-6 py-3">Tanggal</th>
                                <th class="text-left px-3 py-3">Keterangan</th>
                                <th class="text-left px-3 py-3">Projek</th>
                                <th class="text-left px-3 py-3">Klien</th>
                                <th class="text-left px-3 py-3">Jenis</th>
                                <th class="text-right px-6 py-3">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @php
                            $transactions = [
                                ['date'=>'18 Okt 2026','ket'=>'Pembayaran DP 50%','projek'=>'Sistem Parkir Cerdas','klien'=>'Pemkot Semarang','jenis'=>'Pemasukan','jumlah'=>'+Rp 6.000.000'],
                                ['date'=>'17 Okt 2026','ket'=>'Pembelian Komponen IoT','projek'=>'Smart Farming','klien'=>'Internal','jenis'=>'Pengeluaran','jumlah'=>'-Rp 2.400.000'],
                                ['date'=>'15 Okt 2026','ket'=>'Pembayaran Lunas','projek'=>'Website Company Profile','klien'=>'PT. DEF Solusi','jenis'=>'Pemasukan','jumlah'=>'+Rp 9.000.000'],
                                ['date'=>'14 Okt 2026','ket'=>'Langganan Hosting & Domain','projek'=>'Internal','klien'=>'—','jenis'=>'Pengeluaran','jumlah'=>'-Rp 1.200.000'],
                                ['date'=>'12 Okt 2026','ket'=>'Pembayaran Termin 2','projek'=>'Sistem Informasi UMKM','klien'=>'PT. ABC Indonesia','jenis'=>'Pemasukan','jumlah'=>'+Rp 5.000.000'],
                                ['date'=>'10 Okt 2026','ket'=>'Honor Dosen PM','projek'=>'Semua Projek','klien'=>'Internal','jenis'=>'Pengeluaran','jumlah'=>'-Rp 4.500.000'],
                            ];
                            @endphp
                            @foreach($transactions as $t)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-3 text-[12px] text-slate-500">{{ $t['date'] }}</td>
                                <td class="px-3 py-3 text-[12px] font-semibold text-[#1E293B]">{{ $t['ket'] }}</td>
                                <td class="px-3 py-3 text-[12px] text-slate-600">{{ $t['projek'] }}</td>
                                <td class="px-3 py-3 text-[12px] text-slate-600">{{ $t['klien'] }}</td>
                                <td class="px-3 py-3">
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $t['jenis']==='Pemasukan' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">{{ $t['jenis'] }}</span>
                                </td>
                                <td class="px-6 py-3 text-right text-[13px] font-bold {{ str_starts_with($t['jumlah'],'+') ? 'text-green-600' : 'text-red-500' }}">{{ $t['jumlah'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </main>

        <footer class="px-8 py-4 border-t border-slate-100 bg-white">
            <p class="text-[11px] text-slate-400 font-medium">© {{ date('Y') }} TEVARA · Politeknik Negeri Semarang · Teaching Factory</p>
        </footer>
    </div>

    {{-- MODAL BUAT TERMIN PEMBAYARAN (F-09: POST /proposals/{id}/payments) --}}
    <div x-show="showCreateModal"
         x-cloak
         style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs" @click="showCreateModal = false"></div>
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <div class="inline-block w-full max-w-lg p-6 my-8 text-left align-middle bg-white rounded-3xl shadow-2xl border border-slate-100 transform transition-all relative z-10">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Buat Record Termin Pembayaran</h3>
                        <p class="text-xs text-slate-400 mt-0.5">F-09: Tambahkan termin tagihan pada proyek yang disetujui</p>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST"
                      :action="'/pengurus-tefa/proposals/' + createProposalId + '/payments'"
                      class="space-y-4 pt-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Pilih Proyek / Proposal <span class="text-red-500">*</span>
                        </label>
                        <select x-model="createProposalId" required class="w-full bg-[#F8FAFC] border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-700 focus:outline-none focus:border-[#0B3B82]">
                            <option value="">-- Pilih Proyek --</option>
                            @foreach($allProposals as $pr)
                                <option value="{{ $pr['id'] }}">
                                    [{{ $pr['proposal_code'] ?? ('ID ' . $pr['id']) }}] {{ $pr['title'] ?? 'Proyek' }} (Klien: {{ $pr['client']['name'] ?? ($pr['client_name'] ?? '-') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Urutan Termin (Sequence) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="term_sequence" min="1" max="10" value="1" required
                                   class="w-full bg-[#F8FAFC] border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-700 focus:outline-none focus:border-[#0B3B82]">
                            <p class="text-[10px] text-slate-400 mt-1">1 = DP, 2 = Termin 2, 3 = Pelunasan</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Jatuh Tempo (Due Date)
                            </label>
                            <input type="date" name="due_date"
                                   class="w-full bg-[#F8FAFC] border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-700 focus:outline-none focus:border-[#0B3B82]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Nominal Termin (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="amount" min="1000" step="1000" placeholder="Contoh: 15000000" required
                               class="w-full bg-[#F8FAFC] border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-700 focus:outline-none focus:border-[#0B3B82]">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" :disabled="!createProposalId" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#0B3B82] hover:bg-[#082d64] disabled:opacity-50 rounded-xl shadow-xs transition cursor-pointer">
                            Simpan Termin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
</body>
</html>
