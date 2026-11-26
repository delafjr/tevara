<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keuangan & Pembayaran - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body {
            overflow-x: hidden !important;
            max-width: 100vw;
        }
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
            width: 6px;
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
    $pendingPayments = is_array($pendingPayments ?? null) ? $pendingPayments : [];
    $allPayments = is_array($allPayments ?? null) ? $allPayments : [];
    $allProposals = is_array($allProposals ?? null) ? $allProposals : [];

    $paymentsCol = collect($allPayments);
    $verifiedPayments = $paymentsCol->filter(fn($p) => strtoupper((string) ($p['status'] ?? '')) === 'VERIFIED');
    $rejectedPayments = $paymentsCol->filter(fn($p) => strtoupper((string) ($p['status'] ?? '')) === 'REJECTED');
    $pendingPaymentsAll = $paymentsCol->filter(fn($p) => strtoupper((string) ($p['status'] ?? '')) === 'PENDING');

    $totalVerifiedAmount = $verifiedPayments->sum(fn($p) => (float) ($p['amount'] ?? 0));
    $paidProjectIds = $verifiedPayments->pluck('proposal_id')->filter()->unique()->count();

    // Target penerimaan dinamis dari total termin pembayaran / estimasi anggaran proyek
    $totalTargetAmount = $paymentsCol->sum(fn($p) => (float) ($p['amount'] ?? 0));
    if ($totalTargetAmount <= 0) {
        $totalTargetAmount = collect($allProposals)->sum(fn($p) => (float) ($p['estimated_budget'] ?? 0));
    }
    if ($totalTargetAmount <= 0) {
        $totalTargetAmount = 100000000; // baseline 100jt jika data baru/kosong
    }
    $targetPercent = $totalTargetAmount > 0 ? (int) round(min(100, ($totalVerifiedAmount / $totalTargetAmount) * 100)) : 0;

    // Data Pemasukan Bulanan Riil dari transaksi terverifikasi tahun berjalan
    $currentYear = (int) date('Y');
    $monthLabels = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    $monthlyIncome = array_fill(1, 12, 0.0);
    foreach ($verifiedPayments as $vp) {
        $dateRaw = $vp['payment_date'] ?? $vp['verified_at'] ?? $vp['created_at'] ?? null;
        if ($dateRaw) {
            try {
                $cDate = \Carbon\Carbon::parse($dateRaw);
                if ((int) $cDate->format('Y') === $currentYear) {
                    $mIdx = (int) $cDate->format('n');
                    $monthlyIncome[$mIdx] += (float) ($vp['amount'] ?? 0);
                }
            } catch (\Throwable) {}
        }
    }
    $maxMonthlyIncome = max(array_values($monthlyIncome));

    $methodLabelMap = [
        'BANK_TRANSFER'   => 'Transfer Bank',
        'VIRTUAL_ACCOUNT' => 'Virtual Account',
        'CASH'            => 'Tunai (Cash)',
        'OTHER'           => 'Lainnya',
    ];
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
                    <p class="text-sm text-slate-400 mt-1.5">Verifikasi bukti transfer termin klien dan pantau seluruh transaksi penerimaan TeFa secara real-time</p>
                </div>

                <div class="relative z-10 flex-shrink-0 flex items-center gap-3">
                    <a href="#tabel-riwayat-transaksi" class="flex items-center gap-2 bg-[#0B3B82] text-white font-semibold text-xs px-4 py-2.5 rounded-xl hover:bg-[#082a5c] transition-colors shadow-2xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/>
                        </svg>
                        Lihat Riwayat Transaksi
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
                                    <th class="px-6 py-3.5 whitespace-nowrap">Tanggal Bayar</th>
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
                                    @php
                                        $pMethod = $p['payment_method'] ?? 'Transfer Bank';
                                        $pMethodFormatted = $methodLabelMap[$pMethod] ?? $pMethod;
                                    @endphp
                                    <tr class="hover:bg-blue-50/40 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-600 whitespace-nowrap">
                                            {{ !empty($p['payment_date']) ? \Carbon\Carbon::parse($p['payment_date'])->translatedFormat('d M Y') : '-' }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <p class="font-mono text-[11px] font-bold text-[#0B3B82] whitespace-nowrap">{{ $p['proposal']['project_code'] ?? ($p['proposal']['proposal_code'] ?? ('PRJ-' . ($p['proposal_id'] ?? '-'))) }}</p>
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
                                        <td class="px-4 py-4 font-bold text-emerald-600 whitespace-nowrap text-sm font-mono">
                                            Rp {{ number_format((float) ($p['amount'] ?? 0), 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-slate-600">
                                            <p class="font-medium text-xs">{{ $pMethodFormatted }}</p>
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

            {{-- Stats Keuangan (Mengambil Data Nyata API) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-stretch">
                @php
                $keuStats = [
                    [
                        'label' => 'Total Pemasukan',
                        'value' => 'Rp ' . number_format($totalVerifiedAmount, 0, ',', '.'),
                        'sub'   => $verifiedPayments->count() . ' Transaksi Terverifikasi',
                        'color' => '#16A34A',
                        'icon'  => 'check'
                    ],
                    [
                        'label' => 'Target Penerimaan',
                        'value' => 'Rp ' . number_format($totalTargetAmount, 0, ',', '.'),
                        'sub'   => $targetPercent . '% Tercapai',
                        'color' => '#0B3B82',
                        'icon'  => 'folder'
                    ],
                    [
                        'label' => 'Projek Terbayar',
                        'value' => $paidProjectIds . ' Projek',
                        'sub'   => 'Dari ' . count($allProposals) . ' total proposal',
                        'color' => '#6366F1',
                        'icon'  => 'clock-file'
                    ],
                    [
                        'label' => 'Antrean Verifikasi',
                        'value' => count($pendingPayments) . ' Tagihan',
                        'sub'   => 'Menunggu Review',
                        'color' => '#F59E0B',
                        'icon'  => 'calendar'
                    ],
                ];
                @endphp
                @foreach($keuStats as $stat)
                    <x-stat-card :label="$stat['label']" :value="$stat['value']" :sub="$stat['sub']" :color="$stat['color']" :icon="$stat['icon']" />
                @endforeach
            </div>

            {{-- 2 kolom: Chart Bar Bulanan Dinamis + Ringkasan --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Grafik Bar Bulanan Berdasarkan Transaksi Terverifikasi API --}}
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <p class="font-bold text-[#1E293B] text-base">Pemasukan Bulanan</p>
                                <p class="text-xs text-slate-400 mt-0.5">Rekap penerimaan terverifikasi — Tahun {{ $currentYear }}</p>
                            </div>
                            <div class="flex items-center gap-3 text-xs">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-3 h-3 rounded bg-[#0B3B82]"></div>
                                    <span class="text-slate-600 font-medium text-xs">Pemasukan Lunas</span>
                                </div>
                            </div>
                        </div>

                        {{-- Batang Grafik --}}
                        <div class="flex items-end gap-2.5 h-44 pt-4 border-b border-slate-100">
                            @foreach($monthLabels as $mIndex => $mLabel)
                                @php
                                    $val = $monthlyIncome[$mIndex] ?? 0;
                                    $heightPct = $maxMonthlyIncome > 0 ? round(($val / $maxMonthlyIncome) * 100) : 0;
                                @endphp
                                <div class="flex-1 flex flex-col items-center h-full justify-end group relative cursor-pointer">
                                    {{-- Tooltip hover --}}
                                    <div class="absolute bottom-full mb-2 hidden group-hover:flex flex-col items-center z-20 pointer-events-none">
                                        <div class="bg-slate-900 text-white text-[11px] rounded-lg py-1 px-2.5 shadow-lg whitespace-nowrap font-mono font-medium">
                                            {{ $mLabel }} {{ $currentYear }}: Rp {{ number_format($val, 0, ',', '.') }}
                                        </div>
                                        <div class="w-2 h-1 bg-slate-900 clip-triangle"></div>
                                    </div>

                                    <div class="w-full rounded-t-md transition-all duration-300 {{ $val > 0 ? 'bg-[#0B3B82] group-hover:bg-[#082a5c]' : 'bg-slate-100 group-hover:bg-slate-200' }}"
                                         style="height: {{ max(4, $heightPct) }}%"
                                         title="{{ $mLabel }}: Rp {{ number_format($val, 0, ',', '.') }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Label Bulan --}}
                        <div class="flex gap-2.5 mt-2.5">
                            @foreach($monthLabels as $mLabel)
                                <div class="flex-1 text-center text-[11px] text-slate-500 font-semibold">{{ $mLabel }}</div>
                            @endforeach
                        </div>
                    </div>

                    @if($maxMonthlyIncome <= 0)
                        <p class="text-center text-xs text-slate-400 mt-4 italic">Belum ada transaksi pembayaran berstatus terverifikasi pada tahun {{ $currentYear }}.</p>
                    @endif
                </div>

                {{-- Ringkasan Keuangan Proyek --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <p class="font-bold text-[#1E293B] text-base mb-4">Status Tagihan Proyek</p>
                        <div class="space-y-3.5">
                            <div class="flex items-center justify-between py-2 border-b border-slate-50">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <span class="text-xs text-slate-700 font-medium">Lunas / Terverifikasi</span>
                                </div>
                                <span class="text-xs font-bold text-emerald-700 font-mono">{{ $verifiedPayments->count() }} transaksi</span>
                            </div>

                            <div class="flex items-center justify-between py-2 border-b border-slate-50">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                    <span class="text-xs text-slate-700 font-medium">Menunggu Verifikasi</span>
                                </div>
                                <span class="text-xs font-bold text-amber-700 font-mono">{{ count($pendingPayments) }} tagihan</span>
                            </div>

                            <div class="flex items-center justify-between py-2 border-b border-slate-50">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                    <span class="text-xs text-slate-700 font-medium">Ditolak / Perlu Perbaikan</span>
                                </div>
                                <span class="text-xs font-bold text-rose-700 font-mono">{{ $rejectedPayments->count() }} tagihan</span>
                            </div>

                            <div class="flex items-center justify-between py-2">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                    <span class="text-xs text-slate-700 font-medium">Total Proposal Terdaftar</span>
                                </div>
                                <span class="text-xs font-bold text-indigo-700 font-mono">{{ count($allProposals) }} proyek</span>
                            </div>
                        </div>
                    </div>

                    {{-- Target Bar --}}
                    <div class="mt-5 pt-5 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-700">Realisasi Target Keuangan</span>
                            <span class="text-xs font-extrabold text-[#0B3B82]">{{ $targetPercent }}%</span>
                        </div>
                        <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#0B3B82] rounded-full transition-all duration-500" style="width: {{ $targetPercent }}%"></div>
                        </div>
                        <div class="flex justify-between items-center mt-2 text-[11px] text-slate-500 font-mono">
                            <span>Terkumpul: Rp {{ number_format($totalVerifiedAmount, 0, ',', '.') }}</span>
                            <span>Target: Rp {{ number_format($totalTargetAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABEL RIWAYAT TRANSAKSI KEUANGAN (REDESIGNED: CLEAN, PROFESSIONAL, SENIOR LEVEL) --}}
            <div id="tabel-riwayat-transaksi"
                 class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden"
                 x-data="{
                     searchQuery: '',
                     statusFilter: 'all',
                     currentPage: 1,
                     perPage: 10,
                     transactions: @js(array_values($allPayments)),
                     get filteredTransactions() {
                         const q = this.searchQuery.toLowerCase().trim();
                         const filter = this.statusFilter.toUpperCase();

                         return this.transactions.filter(t => {
                             const status = (t.status || '').toUpperCase();
                             if (filter !== 'ALL' && status !== filter) return false;

                             if (!q) return true;

                             const invoice = (t.invoice_number || '').toLowerCase();
                             const ref = (t.transfer_reference || '').toLowerCase();
                             const method = (t.payment_method || '').toLowerCase();
                             const projectCode = (t.proposal && (t.proposal.project_code || t.proposal.proposal_code) ? (t.proposal.project_code || t.proposal.proposal_code) : '').toLowerCase();
                             const projectTitle = (t.proposal && t.proposal.title ? t.proposal.title : '').toLowerCase();
                             const clientName = (t.proposal && t.proposal.client && t.proposal.client.name ? t.proposal.client.name : '').toLowerCase();
                             const companyName = (t.proposal && t.proposal.client && t.proposal.client.company_name ? t.proposal.client.company_name : '').toLowerCase();

                             return invoice.includes(q)
                                 || ref.includes(q)
                                 || method.includes(q)
                                 || projectCode.includes(q)
                                 || projectTitle.includes(q)
                                 || clientName.includes(q)
                                 || companyName.includes(q);
                         });
                     },
                     get totalPages() {
                         return Math.ceil(this.filteredTransactions.length / this.perPage) || 1;
                     },
                     get paginatedTransactions() {
                         const start = (this.currentPage - 1) * this.perPage;
                         return this.filteredTransactions.slice(start, start + this.perPage);
                     },
                     resetPagination() {
                         this.currentPage = 1;
                     },
                     formatRupiah(num) {
                         return 'Rp ' + (new Intl.NumberFormat('id-ID').format(num || 0));
                     },
                     formatDate(dateStr) {
                         if (!dateStr) return '—';
                         try {
                             const d = new Date(dateStr);
                             return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                         } catch (e) {
                             return dateStr;
                         }
                     }
                 }">

                {{-- Toolbar Header: Search & Filter Status --}}
                <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="font-bold text-[#1E293B] text-base">Riwayat Transaksi Keuangan</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#EAF3FF] text-[#0B3B82] border border-blue-100"
                                  x-text="transactions.length + ' Transaksi'">
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Semua data riwayat termin transaksi pembayaran yang tercatat pada sistem database TeFa</p>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap">
                        {{-- Input Pencarian --}}
                        <div class="relative min-w-[240px]">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </div>
                            <input type="text"
                                   x-model="searchQuery"
                                   @input="resetPagination()"
                                   placeholder="Cari transaksi, invoice, klien..."
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3.5 text-xs font-medium text-slate-800 placeholder-slate-400 transition focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10" />
                        </div>

                        {{-- Filter Status Dropdown --}}
                        <div class="flex items-center gap-2">
                            <select x-model="statusFilter"
                                    @change="resetPagination()"
                                    class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-700 transition focus:border-[#0B3B82] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/10 cursor-pointer">
                                <option value="all">Semua Status</option>
                                <option value="VERIFIED">Terverifikasi (Lunas)</option>
                                <option value="PENDING">Menunggu Verifikasi</option>
                                <option value="REJECTED">Ditolak</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Status Count Bar --}}
                <div class="px-6 py-2.5 bg-slate-50/60 border-b border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <p>
                        Menampilkan <span class="font-bold text-slate-800" x-text="paginatedTransactions.length"></span> dari
                        <span class="font-bold text-slate-800" x-text="filteredTransactions.length"></span> transaksi
                    </p>
                    <template x-if="searchQuery || statusFilter !== 'all'">
                        <button type="button"
                                @click="searchQuery = ''; statusFilter = 'all'; resetPagination()"
                                class="font-bold text-[#0B3B82] hover:underline cursor-pointer">
                            Reset Filter
                        </button>
                    </template>
                </div>

                {{-- Tabel Konten Responsif & Bersih --}}
                <div class="w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-xs min-w-[960px]">
                        <thead>
                            <tr class="border-b border-slate-100 bg-[#F8FAFC] text-slate-500 font-bold uppercase text-[11px] tracking-wider">
                                <th class="px-6 py-4 whitespace-nowrap">Invoice & Termin</th>
                                <th class="px-5 py-4 whitespace-nowrap">Proyek & Klien</th>
                                <th class="px-5 py-4 whitespace-nowrap">Tanggal & Metode</th>
                                <th class="px-5 py-4 whitespace-nowrap">Nominal Tagihan</th>
                                <th class="px-5 py-4 text-center whitespace-nowrap">Status</th>
                                <th class="px-6 py-4 text-right whitespace-nowrap">Aksi / Berkas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            {{-- Server-rendered fallback / SEO / Test readability --}}
                            <template x-if="paginatedTransactions.length === 0">
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                        </div>
                                        <p class="font-bold text-slate-700 text-sm">Tidak Ada Transaksi Ditemukan</p>
                                        <p class="text-xs text-slate-400 mt-1">Tidak ada transaksi yang cocok dengan kriteria pencarian Anda.</p>
                                    </td>
                                </tr>
                            </template>

                            <template x-for="t in paginatedTransactions" :key="t.id">
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    {{-- Kolom 1: Invoice & Termin --}}
                                    <td class="px-6 py-4.5 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-[#EAF3FF] text-[#0B3B82]"
                                                  x-text="'Termin ' + (t.term_sequence || 1)">
                                            </span>
                                        </div>
                                        <p class="font-mono text-xs font-bold text-slate-800 mt-1.5"
                                           x-text="t.invoice_number || ('INV/' + (new Date(t.created_at || Date.now()).getFullYear()) + '/' + String(t.id).padStart(6, '0'))">
                                        </p>
                                        <template x-if="t.transfer_reference">
                                            <p class="text-[11px] text-slate-400 font-mono mt-0.5 truncate max-w-[200px]"
                                               :title="'No. Referensi: ' + t.transfer_reference"
                                               x-text="'Ref: ' + t.transfer_reference">
                                            </p>
                                        </template>
                                    </td>

                                    {{-- Kolom 2: Proyek & Klien --}}
                                    <td class="px-5 py-4.5 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono text-[10.5px] font-bold text-[#0B3B82] bg-blue-50/80 px-2 py-0.5 rounded"
                                                  x-text="t.proposal && (t.proposal.project_code || t.proposal.proposal_code) ? (t.proposal.project_code || t.proposal.proposal_code) : ('PRJ-' + (t.proposal_id || '-'))">
                                            </span>
                                        </div>
                                        <p class="text-xs font-bold text-slate-800 truncate max-w-[260px] mt-1"
                                           :title="t.proposal ? t.proposal.title : 'Proyek'"
                                           x-text="t.proposal && t.proposal.title ? t.proposal.title : ('Proyek #' + (t.proposal_id || '-'))">
                                        </p>
                                        <p class="text-xs text-slate-500 mt-0.5 truncate max-w-[260px]"
                                           :title="(t.proposal && t.proposal.client && t.proposal.client.name ? t.proposal.client.name : '—') + (t.proposal && t.proposal.client && t.proposal.client.company_name ? ' · ' + t.proposal.client.company_name : '')">
                                            <span x-text="t.proposal && t.proposal.client && t.proposal.client.name ? t.proposal.client.name : '—'"></span>
                                            <template x-if="t.proposal && t.proposal.client && t.proposal.client.company_name">
                                                <span class="text-slate-400" x-text="' · ' + t.proposal.client.company_name"></span>
                                            </template>
                                        </p>
                                    </td>

                                    {{-- Kolom 3: Tanggal & Metode --}}
                                    <td class="px-5 py-4.5 whitespace-nowrap">
                                        <p class="text-xs font-semibold text-slate-700"
                                           x-text="formatDate(t.payment_date || t.created_at)">
                                        </p>
                                        <p class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                            </svg>
                                            <span x-text="t.payment_method ? (t.payment_method === 'BANK_TRANSFER' ? 'Transfer Bank' : (t.payment_method === 'VIRTUAL_ACCOUNT' ? 'Virtual Account' : (t.payment_method === 'CASH' ? 'Tunai' : t.payment_method))) : 'Transfer Bank'"></span>
                                        </p>
                                    </td>

                                    {{-- Kolom 4: Nominal --}}
                                    <td class="px-5 py-4.5 whitespace-nowrap">
                                        <p class="text-sm font-extrabold font-mono"
                                           :class="(t.status || '').toUpperCase() === 'VERIFIED' ? 'text-emerald-600' : 'text-slate-800'"
                                           x-text="formatRupiah(t.amount)">
                                        </p>
                                    </td>

                                    {{-- Kolom 5: Status --}}
                                    <td class="px-5 py-4.5 text-center whitespace-nowrap">
                                        <template x-if="(t.status || '').toUpperCase() === 'VERIFIED'">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Terverifikasi
                                            </span>
                                        </template>

                                        <template x-if="(t.status || '').toUpperCase() === 'PENDING'">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Menunggu
                                            </span>
                                        </template>

                                        <template x-if="(t.status || '').toUpperCase() === 'REJECTED'">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Ditolak
                                            </span>
                                        </template>
                                    </td>

                                    {{-- Kolom 6: Aksi / Berkas --}}
                                    <td class="px-6 py-4.5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-2 justify-end">
                                            {{-- Jika Pending: Tombol Cepat Verifikasi --}}
                                            <template x-if="(t.status || '').toUpperCase() === 'PENDING'">
                                                <a :href="'/pengurus-tefa/proposals/' + (t.proposal_id || 1) + '/payments/' + t.id + '/verifikasi'"
                                                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#0B3B82] hover:bg-[#082a5c] px-3.5 py-2 text-xs font-bold text-white shadow-xs transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                    Verifikasi
                                                </a>
                                            </template>

                                            {{-- Tombol Lihat Bukti --}}
                                            <template x-if="t.proof_url">
                                                <a :href="t.proof_url"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 text-xs font-bold transition"
                                                   title="Lihat Berkas Bukti Transfer">
                                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                    </svg>
                                                    <span>Bukti</span>
                                                </a>
                                            </template>

                                            <template x-if="!t.proof_url && (t.status || '').toUpperCase() !== 'PENDING'">
                                                <span class="text-[11px] text-slate-400 italic">Tidak ada berkas</span>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Navigasi --}}
                <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3"
                     x-show="filteredTransactions.length > 0">
                    <div class="text-xs text-slate-500">
                        Halaman <span class="font-bold text-slate-800" x-text="currentPage"></span> dari <span class="font-bold text-slate-800" x-text="totalPages"></span>
                    </div>

                    <div class="flex items-center gap-2" x-show="totalPages > 1">
                        <button type="button"
                                @click="currentPage > 1 ? currentPage-- : null"
                                :disabled="currentPage === 1"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                            Sebelumnya
                        </button>

                        <div class="flex items-center gap-1">
                            <template x-for="p in totalPages" :key="p">
                                <button type="button"
                                        @click="currentPage = p"
                                        class="h-7 w-7 rounded-lg text-xs font-bold transition flex items-center justify-center cursor-pointer"
                                        :class="currentPage === p ? 'bg-[#0B3B82] text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'"
                                        x-text="p">
                                </button>
                            </template>
                        </div>

                        <button type="button"
                                @click="currentPage < totalPages ? currentPage++ : null"
                                :disabled="currentPage === totalPages"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                            Selanjutnya
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </button>
                    </div>
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
