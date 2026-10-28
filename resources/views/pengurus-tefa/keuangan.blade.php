<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keuangan - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php $user = $user ?? session('user'); $firstName = explode(' ', $user['name'] ?? 'Pengurus')[0]; @endphp
<div class="flex min-h-screen">
    <x-sidebar active="keuangan" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 space-y-7">

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
                        Rekap <span class="text-[#FFB800]">Keuangan TeFa</span>
                    </h1>
                    <p class="text-sm text-[#64748B] mt-1.5 max-w-2xl leading-relaxed">
                        Pantau pemasukan, pengeluaran, dan laporan keuangan seluruh projek Teaching Factory.
                    </p>
                </div>

                <div class="relative z-10 flex-shrink-0">
                    <a href="#" class="flex items-center gap-2 bg-[#0B3B82] text-white font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-[#082a5c] transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Ekspor Laporan
                    </a>
                </div>
            </div>

            {{-- Stats Keuangan --}}
            <div class="grid grid-cols-4 gap-4 items-stretch">
                @php
                $keuStats = [
                    ['label' => 'Total Pemasukan', 'value' => 'Rp 218 Jt', 'sub' => '↑ 32% vs 2025',  'color' => '#16A34A', 'icon' => 'check'],
                    ['label' => 'Total Pengeluaran', 'value' => 'Rp 124 Jt', 'sub' => '↑ 18% vs 2025',  'color' => '#DC2626', 'icon' => 'clock-file'],
                    ['label' => 'Saldo Bersih',      'value' => 'Rp 94 Jt',  'sub' => 'Net Positif',    'color' => '#0B3B82', 'icon' => 'folder'],
                    ['label' => 'Piutang Belum Cair','value' => 'Rp 46 Jt',  'sub' => '5 Projek',       'color' => '#F59E0B', 'icon' => 'calendar'],
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
                    <p class="font-semibold text-[#1E293B] text-[15px] mb-4">Status Tagihan</p>
                    <div class="space-y-3">
                        @foreach([
                            ['Lunas','11 projek','#16A34A'],
                            ['Sebagian','5 projek','#F59E0B'],
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

            {{-- Tabel Transaksi --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-[#1E293B] text-[15px]">Riwayat Transaksi</p>
                        <p class="text-xs text-slate-400 mt-0.5">Semua transaksi keuangan TeFa</p>
                    </div>
                    <select class="text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0B3B82]">
                        <option>Oktober 2026</option><option>September 2026</option><option>Agustus 2026</option>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-6 py-3">Tanggal</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Keterangan</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Projek</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Klien</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Jenis</th>
                                <th class="text-right text-[11px] font-semibold text-slate-400 px-6 py-3">Jumlah</th>
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
</div>
</body>
</html>
