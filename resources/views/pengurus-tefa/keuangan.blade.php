<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keuangan - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body {
            overflow-x: hidden !important;
            max-width: 100vw;
        }
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #F8FAFC;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
    </style>
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased overflow-x-hidden">
@php
    $user = $user ?? session('user');
    $firstName = explode(' ', $user['name'] ?? 'Pengurus')[0];
    $pendingPayments = $pendingPayments ?? [];
    $allPayments = $allPayments ?? [];
    $allProposals = $allProposals ?? [];

    $verifiedPayments = collect($allPayments)->filter(fn($p) => strtoupper($p['status'] ?? '') === 'VERIFIED');
    $totalVerifiedAmount = $verifiedPayments->sum(fn($p) => (float) ($p['amount'] ?? 0));
    $paidProjectIds = $verifiedPayments->pluck('proposal_id')->filter()->unique()->count();
@endphp

<div class="flex min-h-screen overflow-x-hidden">

    <x-sidebar active="keuangan" />

    <div class="flex-1 ml-64 flex flex-col min-h-screen min-w-0 overflow-x-hidden">
        <main class="flex-1 px-8 py-7 space-y-7 min-w-0 overflow-x-hidden">

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
                    <p class="text-sm text-slate-400 mt-1.5">Verifikasi bukti transfer termin klien dan pantau seluruh transaksi penerimaan TeFa</p>
                </div>

                <div class="relative z-10 flex-shrink-0 flex items-center gap-3">
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
                    <div class="w-full overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse text-xs min-w-[880px]">
                            <thead>
                                <tr class="border-b border-slate-100 bg-[#F8FAFC] text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                                    <th class="px-5 py-3.5 whitespace-nowrap">Tanggal Bayar</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Kode & Judul Proyek</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Klien / PIC</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Termin</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Nominal</th>
                                    <th class="px-4 py-3.5 whitespace-nowrap">Metode Bayar</th>
                                    <th class="px-6 py-3.5 text-right whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($pendingPayments as $p)
                                    <tr class="hover:bg-blue-50/40 transition-colors">
                                        <td class="px-5 py-4 font-medium text-slate-600 whitespace-nowrap">
                                            {{ !empty($p['payment_date']) ? \Carbon\Carbon::parse($p['payment_date'])->translatedFormat('d M Y') : '-' }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <p class="font-mono text-[11px] font-bold text-[#0B3B82] whitespace-nowrap">{{ $p['proposal']['project_code'] ?? ('PRJ-' . ($p['proposal_id'] ?? '-')) }}</p>
                                            <p class="font-semibold text-slate-800 text-xs mt-1 truncate max-w-[210px]" title="{{ $p['proposal']['title'] ?? '-' }}">{{ $p['proposal']['title'] ?? '-' }}</p>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <p class="font-semibold text-slate-800 text-xs whitespace-nowrap">{{ $p['proposal']['client']['name'] ?? 'Klien' }}</p>
                                            <p class="text-[11px] text-slate-400 mt-0.5 truncate max-w-[170px]" title="{{ $p['proposal']['client']['email'] ?? '' }}">{{ $p['proposal']['client']['email'] ?? '' }}</p>
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
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <a href="{{ route('pengurus-tefa.payments.verifikasi', ['proposal' => $p['proposal_id'] ?? 1, 'payment' => $p['id']]) }}"
                                               class="inline-flex items-center gap-1.5 rounded-xl bg-[#0B3B82] hover:bg-[#082a5c] px-3.5 py-2 text-xs font-bold text-white shadow-xs transition cursor-pointer">
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-stretch">
                @php
                $keuStats = [
                    ['label' => 'Total Pemasukan',   'value' => 'Rp ' . number_format($totalVerifiedAmount, 0, ',', '.'), 'sub' => $verifiedPayments->count() . ' Transaksi Terverifikasi', 'color' => '#16A34A', 'icon' => 'check'],
                    ['label' => 'Target Penerimaan', 'value' => 'Rp 100.000.000', 'sub' => round(min(100, ($totalVerifiedAmount / 100000000) * 100)) . '% Tercapai',   'color' => '#0B3B82', 'icon' => 'folder'],
                    ['label' => 'Projek Terbayar',   'value' => $paidProjectIds . ' Projek', 'sub' => 'Dari database',    'color' => '#6366F1', 'icon' => 'clock-file'],
                    ['label' => 'Antrean Verifikasi','value' => count($pendingPayments) . ' Tagihan',  'sub' => 'Menunggu Review', 'color' => '#F59E0B', 'icon' => 'calendar'],
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
                            <p class="font-semibold text-[#1E293B] text-[15px]">Pemasukan Bulanan</p>
                            <p class="text-xs text-slate-400 mt-0.5">Per bulan — Tahun 2026</p>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded bg-[#0B3B82]"></div><span class="text-slate-500 font-medium">Pemasukan</span></div>
                        </div>
                    </div>
                    @php
                    $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt'];
                    $income  = [8,12,15,18,20,22,25,28,35,35];
                    $maxVal  = 40;
                    @endphp
                    <div class="flex items-end gap-2 h-44">
                        @foreach($months as $i => $m)
                        <div class="flex-1 flex items-end">
                            <div class="w-full rounded-t-sm bg-[#0B3B82] hover:bg-[#082a5c] transition-colors" style="height:{{ round($income[$i]/$maxVal*100) }}%; min-height:4px" title="Pemasukan {{ $m }}: Rp {{ $income[$i] }} Jt"></div>
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
                            ['Lunas / Verified', $verifiedPayments->count() . ' transaksi', '#16A34A'],
                            ['Menunggu Verifikasi', count($pendingPayments) . ' tagihan', '#F59E0B'],
                            ['Total Proposal', count($allProposals) . ' projek', '#6366F1'],
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
                        <p class="text-xs text-slate-400 mb-2">Target Penerimaan</p>
                        @php
                            $targetPercent = round(min(100, ($totalVerifiedAmount / 100000000) * 100));
                        @endphp
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#0B3B82] rounded-full" style="width:{{ $targetPercent }}%"></div>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-[11px] text-slate-500">Rp {{ number_format($totalVerifiedAmount, 0, ',', '.') }}</span>
                            <span class="text-[11px] font-bold text-[#0B3B82]">{{ $targetPercent }}% dari target</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Riwayat Transaksi --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-[#1E293B] text-[15px]">Riwayat Transaksi Keuangan</p>
                        <p class="text-xs text-slate-400 mt-0.5">Semua data transaksi pembayaran yang tercatat di database</p>
                    </div>
                </div>
                <div class="w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full text-sm min-w-[650px]">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 text-[11px] font-semibold">
                                <th class="text-left px-6 py-3 whitespace-nowrap">Tanggal</th>
                                <th class="text-left px-4 py-3 whitespace-nowrap">Keterangan Tagihan</th>
                                <th class="text-left px-4 py-3 whitespace-nowrap">Projek</th>
                                <th class="text-left px-4 py-3 whitespace-nowrap">Klien</th>
                                <th class="text-left px-4 py-3 whitespace-nowrap">Status</th>
                                <th class="text-right px-6 py-3 whitespace-nowrap">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($allPayments as $t)
                            @php
                                $isVerified = strtoupper($t['status'] ?? '') === 'VERIFIED';
                                $badgeClass = $isVerified ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700';
                                $amountColor = $isVerified ? 'text-green-600' : 'text-slate-600';
                                $dateStr = !empty($t['payment_date']) 
                                    ? \Carbon\Carbon::parse($t['payment_date'])->translatedFormat('d M Y') 
                                    : (!empty($t['created_at']) ? \Carbon\Carbon::parse($t['created_at'])->translatedFormat('d M Y') : '—');
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-3 text-[12px] text-slate-500 whitespace-nowrap">{{ $dateStr }}</td>
                                <td class="px-4 py-3 text-[12px] font-semibold text-[#1E293B] whitespace-nowrap">
                                    Termin {{ $t['term_sequence'] ?? 1 }}
                                    @if(!empty($t['invoice_number']))
                                        <span class="text-[10px] text-slate-400 font-normal">({{ $t['invoice_number'] }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-[12px] text-slate-600 whitespace-nowrap">{{ $t['proposal']['title'] ?? ('Projek #' . ($t['proposal_id'] ?? '-')) }}</td>
                                <td class="px-4 py-3 text-[12px] text-slate-600 whitespace-nowrap">{{ $t['proposal']['client']['name'] ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full {{ $badgeClass }}">{{ $t['status'] ?? 'PENDING' }}</span>
                                </td>
                                <td class="px-6 py-3 text-right text-[13px] font-bold {{ $amountColor }} whitespace-nowrap">
                                    +Rp {{ number_format((float) ($t['amount'] ?? 0), 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-xs text-slate-400">
                                    Belum ada transaksi pembayaran yang tercatat di database.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </main>

        <footer class="px-8 py-4 border-t border-slate-100 bg-white">
            <p class="text-[11px] text-slate-400 font-medium">© {{ date('Y') }} TEVARA · Politeknik Negeri Semarang · Teaching Factory</p>
        </footer>
    </div>

</div>
</body>
</html>
