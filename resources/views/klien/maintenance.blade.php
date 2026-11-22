{{-- == ( halaman garansi & maintenance - klien ) == --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Garansi & Maintenance - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen">

@php
    use Carbon\Carbon;

    $projects = is_array($projects ?? null) ? $projects : [];
    $error    = $error ?? null;

    $today = Carbon::today();

    $mapped = collect($projects)->map(function ($p) use ($today) {
        $warrantyEnd  = !empty($p['warranty_end_date']) ? Carbon::parse($p['warranty_end_date']) : null;
        $warrantyActive = $p['has_warranty'] && $warrantyEnd && $warrantyEnd->gte($today);
        $daysLeft       = $warrantyEnd ? $today->diffInDays($warrantyEnd, false) : null;

        return array_merge($p, [
            'warranty_active'    => $warrantyActive,
            'warranty_days_left' => $daysLeft,
            'warranty_end_fmt'   => $warrantyEnd?->translatedFormat('d M Y'),
        ]);
    })->all();

    $activeCount   = collect($mapped)->where('warranty_active', true)->count();
    $expiredCount  = collect($mapped)->where('warranty_active', false)->count();
@endphp

<div class="flex min-h-screen">
    <x-sidebar active="maintenance" />

    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 space-y-6">

            {{-- Header --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#EFF6FF] via-[#F3F7FD] to-[#F0FDF4] border border-blue-100/70 px-8 py-7 shadow-xs">
                <div class="relative z-10 max-w-4xl">
                    <h1 class="text-2xl lg:text-[28px] font-extrabold text-[#1E293B] tracking-tight">
                        Garansi & <span class="text-[#0B3B82]">Maintenance</span>
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-2 leading-relaxed">
                        Pantau status garansi proyek, laporkan error, dan ajukan perpanjangan masa garansi atau maintenance.
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400 mb-2">Total Proyek</p>
                    <p class="text-2xl font-bold text-slate-800">{{ count($mapped) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400 mb-2">Garansi Aktif</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ $activeCount }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6">
                    <p class="text-[10.5px] font-bold tracking-wider uppercase text-slate-400 mb-2">Masa Berakhir</p>
                    <p class="text-2xl font-bold text-slate-500">{{ $expiredCount }}</p>
                </div>
            </div>

            {{-- Project List --}}
            <div class="space-y-4">
                @forelse($mapped as $p)
                    @php
                        $isActive     = $p['warranty_active'];
                        $daysLeft     = $p['warranty_days_left'];
                        $deliveryType = $p['delivery_type'] ?? 'source_code';
                        $hasWarranty  = $p['has_warranty'];
                        $projectCode  = $p['project_code'] ?? $p['proposal_code'];
                        $pid          = $p['id'];
                    @endphp
                    <a href="{{ route('klien.maintenance.detail', $pid) }}"
                       class="block bg-white rounded-2xl border border-slate-100 shadow-xs p-6 hover:shadow-md hover:border-blue-200 transition-all duration-150 group">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                            {{-- Left: title + meta --}}
                            <div class="flex items-start gap-4 min-w-0">
                                <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center {{ $isActive ? 'bg-emerald-100' : 'bg-slate-100' }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 {{ $isActive ? 'text-emerald-600' : 'text-slate-400' }}">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-800 group-hover:text-[#0B3B82] transition-colors truncate">{{ $p['title'] }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5 font-mono">{{ $projectCode }}</p>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        {{-- Delivery type badge --}}
                                        @if($deliveryType === 'source_code')
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-full px-2.5 py-0.5">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                                Source Code Diserahkan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2.5 py-0.5">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                Dikelola TEVARA
                                            </span>
                                        @endif

                                        {{-- Warranty status badge --}}
                                        @if(!$hasWarranty)
                                            <span class="text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200 rounded-full px-2.5 py-0.5">Tanpa Garansi</span>
                                        @elseif($isActive)
                                            <span class="text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-2.5 py-0.5">
                                                Garansi Aktif · {{ $daysLeft !== null ? ($daysLeft >= 0 ? 'Sisa '.$daysLeft.' hari' : 'Berakhir') : '' }}
                                            </span>
                                        @else
                                            <span class="text-[11px] font-semibold bg-red-50 text-red-600 border border-red-200 rounded-full px-2.5 py-0.5">Garansi Berakhir</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Right: dates + arrow --}}
                            <div class="flex-shrink-0 flex items-center gap-4">
                                <div class="text-right hidden sm:block">
                                    @if($hasWarranty && !empty($p['warranty_end_fmt']))
                                        <p class="text-[11px] text-slate-400">Berakhir</p>
                                        <p class="text-sm font-semibold text-slate-700">{{ $p['warranty_end_fmt'] }}</p>
                                    @endif
                                    @if(!empty($p['handover_date']))
                                        <p class="text-[11px] text-slate-400 mt-1">Serah Terima</p>
                                        <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($p['handover_date'])->translatedFormat('d M Y') }}</p>
                                    @endif
                                </div>
                                <svg class="w-5 h-5 text-slate-300 group-hover:text-[#0B3B82] transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>

                        </div>
                    </a>
                @empty
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-12 text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                            </svg>
                        </div>
                        <p class="text-slate-500 text-sm">Belum ada proyek yang telah melewati serah terima (BAST).</p>
                        <a href="{{ route('klien.projek-saya') }}" class="inline-block mt-3 text-xs font-semibold text-[#0B3B82] hover:underline">Lihat Projek Saya →</a>
                    </div>
                @endforelse
            </div>

        </main>
    </div>
</div>
</body>
</html>
