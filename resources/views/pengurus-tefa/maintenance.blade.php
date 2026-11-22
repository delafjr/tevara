{{-- == ( halaman maintenance - pengurus tefa ) == --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance & Garansi - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen overflow-x-hidden">

@php
    use Carbon\Carbon;

    $projects = is_array($projects ?? null) ? $projects : [];
    $error    = $error ?? null;
    $today    = Carbon::today();

    $activeCount  = collect($projects)->where('warranty_active', true)->count();
    $expiredCount = collect($projects)->where('warranty_active', false)->where('has_warranty', true)->count();
    $totalTickets = collect($projects)->sum('maintenance_tickets_count');
@endphp

<div class="flex min-h-screen">
    <x-sidebar active="maintenance" />

    <div class="flex-1 ml-64 flex flex-col min-h-screen min-w-0">
        <main class="flex-1 px-8 py-7 space-y-6">

            {{-- Header --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#EFF6FF] via-[#F3F7FD] to-[#F0FDF4] border border-blue-100/70 px-8 py-7 shadow-xs">
                <div class="relative z-10 max-w-4xl">
                    <h1 class="text-2xl lg:text-[28px] font-extrabold text-[#1E293B] tracking-tight">
                        Maintenance & <span class="text-[#0B3B82]">Garansi</span>
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-2 leading-relaxed">
                        Kelola semua proyek pasca serah terima — pantau tiket error, perpanjangan garansi, dan status maintenance.
                    </p>
                </div>
            </div>

            {{-- Flash --}}
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error') || $error)
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') ?? $error }}</div>
            @endif

            {{-- Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-5">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400 mb-2">Total Proyek</p>
                    <p class="text-2xl font-bold text-slate-800">{{ count($projects) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-5">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400 mb-2">Garansi Aktif</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ $activeCount }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-5">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400 mb-2">Masa Berakhir</p>
                    <p class="text-2xl font-bold text-red-500">{{ $expiredCount }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-5">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400 mb-2">Total Tiket</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $totalTickets }}</p>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Daftar Proyek Pasca Serah Terima</h2>
                </div>

                @if(count($projects))
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="text-left px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Proyek</th>
                                    <th class="text-left px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Klien</th>
                                    <th class="text-left px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Skema</th>
                                    <th class="text-left px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Status Garansi</th>
                                    <th class="text-left px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tiket</th>
                                    <th class="text-left px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Serah Terima</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($projects as $p)
                                    @php
                                        $isActive    = $p['warranty_active'] ?? false;
                                        $hasWarranty = $p['has_warranty'] ?? false;
                                        $daysLeft    = $p['warranty_days_left'] ?? null;
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-slate-800">{{ $p['title'] }}</p>
                                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $p['project_code'] ?? $p['proposal_code'] }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <p class="text-sm text-slate-700">{{ $p['client']['name'] ?? '-' }}</p>
                                            <p class="text-[11px] text-slate-400">{{ $p['client']['email'] ?? '' }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            @if(($p['delivery_type'] ?? '') === 'source_code')
                                                <span class="text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-full px-2 py-0.5">Source Code</span>
                                            @else
                                                <span class="text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2 py-0.5">TEVARA Manage</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            @if(!$hasWarranty)
                                                <span class="text-[11px] font-semibold bg-slate-100 text-slate-500 rounded-full px-2 py-0.5">Tanpa Garansi</span>
                                            @elseif($isActive)
                                                <div>
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-2 py-0.5">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                                    </span>
                                                    @if($daysLeft !== null)
                                                        <p class="text-[11px] text-slate-400 mt-0.5">Sisa {{ $daysLeft }} hari</p>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-[11px] font-semibold bg-red-50 text-red-600 border border-red-200 rounded-full px-2 py-0.5">Berakhir</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="text-sm font-bold text-slate-800">{{ $p['maintenance_tickets_count'] ?? 0 }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <p class="text-sm text-slate-600">{{ !empty($p['handover_date']) ? \Carbon\Carbon::parse($p['handover_date'])->translatedFormat('d M Y') : '-' }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <a href="{{ route('pengurus-tefa.maintenance.detail', $p['id']) }}"
                                               class="inline-flex items-center gap-1 text-xs font-semibold text-[#0B3B82] hover:underline whitespace-nowrap">
                                                Detail
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-12 text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-500">Belum ada proyek yang telah melewati serah terima.</p>
                    </div>
                @endif
            </div>

        </main>
    </div>
</div>
</body>
</html>
