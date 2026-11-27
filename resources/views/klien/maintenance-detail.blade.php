{{-- == ( detail garansi & maintenance - klien ) == --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Maintenance - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FB] font-sans text-[#1E293B] antialiased min-h-screen overflow-x-hidden">

@php
    use Carbon\Carbon;

    $p       = is_array($proposal ?? null) ? $proposal : [];
    $tickets = is_array($tickets  ?? null) ? $tickets  : [];
    $extReqs = is_array($extReqs  ?? null) ? $extReqs  : [];
    $error   = $error ?? null;

    $today        = Carbon::today();
    $warrantyEnd  = !empty($p['warranty_end_date']) ? Carbon::parse($p['warranty_end_date']) : null;
    $isActive     = ($p['has_warranty'] ?? false) && $warrantyEnd && $warrantyEnd->gte($today);
    $daysLeft     = $warrantyEnd ? (int) $today->diffInDays($warrantyEnd, false) : null;
    $deliveryType = $p['delivery_type'] ?? 'source_code';
    $canMaintenance = $deliveryType === 'tefa_managed';

    $hasPendingWarranty     = collect($extReqs)->where('type', 'warranty')->where('status', 'pending')->isNotEmpty();
    $hasPendingMaintenance  = collect($extReqs)->where('type', 'maintenance')->whereIn('status', ['pending', 'offered', 'waiting_payment'])->isNotEmpty();

    $offeredExt        = collect($extReqs)->firstWhere('status', 'offered');
    $waitingPaymentExt = collect($extReqs)->firstWhere('status', 'waiting_payment');

    $statusColors = [
        'open'        => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',  'border' => 'border-amber-200',  'label' => 'Terbuka'],
        'in_progress' => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',   'border' => 'border-blue-200',   'label' => 'Dikerjakan'],
        'resolved'    => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700','border' => 'border-emerald-200','label' => 'Selesai'],
    ];
    $priorityColors = [
        'low'    => 'text-slate-500',
        'medium' => 'text-amber-600',
        'high'   => 'text-red-600',
    ];
    $extStatusColors = [
        'pending'         => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',  'label' => 'Menunggu Review TeFa'],
        'offered'         => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',   'label' => 'Menunggu Persetujuan Anda'],
        'waiting_payment' => ['bg' => 'bg-purple-50',  'text' => 'text-purple-700', 'label' => 'Menunggu Pembayaran'],
        'approved'        => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700','label' => 'Disetujui'],
        'rejected'        => ['bg' => 'bg-red-50',     'text' => 'text-red-600',    'label' => 'Ditolak'],
    ];
@endphp

<div class="flex min-h-screen" x-data="{
    reportModal: false,
    extModal: false,
    extType: 'maintenance',
    extMonths: 6,
    extNotes: '',
    submittingReport: false,
    submittingExt: false,
}">
    <x-sidebar active="maintenance" />

    <div class="flex-1 ml-64 flex flex-col min-h-screen min-w-0">
        <main class="flex-1 px-8 py-7 space-y-6">

            {{-- Back --}}
            <div>
                <a href="{{ route('klien.maintenance') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-[#0B3B82] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Garansi & Maintenance
                </a>
            </div>

            {{-- Flash --}}
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error') || $error)
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') ?? $error }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc pl-4 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            @if(empty($p))
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-12 text-center text-slate-500">Data proyek tidak ditemukan.</div>
            @else

            {{-- Header card --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap gap-2 mb-2">
                            @if($isActive)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-emerald-100 text-emerald-700 rounded-full px-3 py-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Garansi Aktif
                                </span>
                            @elseif($p['has_warranty'] ?? false)
                                <span class="text-[11px] font-bold bg-red-100 text-red-600 rounded-full px-3 py-1">Garansi Berakhir</span>
                            @else
                                <span class="text-[11px] font-bold bg-slate-100 text-slate-500 rounded-full px-3 py-1">Tanpa Garansi</span>
                            @endif

                            @if($deliveryType === 'source_code')
                                <span class="text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-full px-3 py-1">Source Code Diserahkan</span>
                            @else
                                <span class="text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-3 py-1">Dikelola TEVARA</span>
                            @endif
                        </div>
                        <h1 class="text-xl font-extrabold text-slate-800">{{ $p['title'] ?? 'Proyek' }}</h1>
                        <p class="text-xs text-slate-500 mt-1 font-mono">{{ $p['project_code'] ?? $p['proposal_code'] ?? '' }}</p>
                    </div>

                    {{-- Warranty countdown --}}
                    @if($p['has_warranty'] ?? false)
                    <div class="bg-{{ $isActive ? 'emerald' : 'red' }}-50 border border-{{ $isActive ? 'emerald' : 'red' }}-200 rounded-2xl px-6 py-4 text-center min-w-[180px]">
                        @if($isActive && $daysLeft !== null)
                            <p class="text-3xl font-black text-emerald-600">{{ $daysLeft }}</p>
                            <p class="text-xs text-emerald-700 font-semibold mt-0.5">hari tersisa</p>
                        @else
                            <p class="text-sm font-bold text-red-600">Masa Berakhir</p>
                        @endif
                        @if($warrantyEnd)
                            <p class="text-[11px] text-slate-500 mt-1">s/d {{ $warrantyEnd->translatedFormat('d M Y') }}</p>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Action buttons --}}
                <div class="mt-5 flex flex-wrap gap-3">
                    @if($isActive)
                        <button @click="reportModal = true"
                            class="inline-flex items-center gap-2 bg-[#0B3B82] text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-[#082d64] transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Laporkan Error / Bug
                        </button>
                    @endif

                    {{-- Extend maintenance (Available for both source_code and tefa_managed) --}}
                    @if(!$hasPendingMaintenance)
                        <button @click="extModal = true"
                            class="inline-flex items-center gap-2 bg-[#0B3B82] text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-[#082d64] shadow-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                            Perpanjang Masa Maintenance
                        </button>
                    @elseif($offeredExt)
                        <span class="inline-flex items-center gap-2 bg-blue-50 border border-blue-200 text-blue-700 text-sm font-semibold px-5 py-2.5 rounded-xl">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                            Penawaran Harga Maintenance Tersedia (Perlu Tindakan)
                        </span>
                    @elseif($waitingPaymentExt)
                        <a href="{{ route('klien.tagihan') }}" class="inline-flex items-center gap-2 bg-purple-50 border border-purple-200 text-purple-700 text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-purple-100 transition">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            Menunggu Pembayaran Maintenance &rarr;
                        </a>
                    @else
                        <span class="inline-flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-700 text-sm font-semibold px-5 py-2.5 rounded-xl cursor-not-allowed">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Pengajuan Maintenance Menggantung (Menunggu TeFa)
                        </span>
                    @endif
                </div>
            </div>

            {{-- Banner Penawaran Harga Maintenance --}}
            @if($offeredExt)
                @php
                    $isExpired = !empty($offeredExt['payment_deadline']) && \Carbon\Carbon::parse($offeredExt['payment_deadline'])->isPast();
                    $deadlineFormatted = !empty($offeredExt['payment_deadline']) ? \Carbon\Carbon::parse($offeredExt['payment_deadline'])->translatedFormat('d M Y, H:i') : null;
                @endphp
                <div class="rounded-2xl border-2 {{ $isExpired ? 'border-red-200 bg-red-50/50' : 'border-blue-200 bg-blue-50/60' }} p-6 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $isExpired ? 'bg-red-500' : 'bg-blue-600 animate-pulse' }}"></span>
                                <h3 class="text-base font-bold text-slate-800">
                                    {{ $isExpired ? 'Penawaran Maintenance Kadaluarsa' : 'Penawaran Harga Maintenance dari Pengurus TeFa' }}
                                </h3>
                            </div>
                            <p class="text-xs text-slate-600">
                                Pengurus TeFa telah menetapkan biaya layanan untuk perpanjangan maintenance selama <strong>+{{ $offeredExt['months'] }} Bulan</strong>.
                            </p>
                            @if(!empty($offeredExt['reviewer_notes']))
                                <p class="text-xs text-slate-500 italic mt-1 bg-white/70 rounded-lg px-3 py-1.5 border border-slate-200/60">
                                    Catatan: "{{ $offeredExt['reviewer_notes'] }}"
                                </p>
                            @endif
                        </div>

                        <div class="bg-white rounded-xl border border-blue-100 p-4 min-w-[220px] text-center md:text-right shadow-2xs">
                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Biaya</span>
                            <span class="text-2xl font-black text-[#0B3B82] block">Rp {{ number_format((float)$offeredExt['price'], 0, ',', '.') }}</span>
                            @if($deadlineFormatted)
                                <span class="text-[11px] font-medium {{ $isExpired ? 'text-red-600 font-bold' : 'text-slate-500' }} block mt-1">
                                    {{ $isExpired ? 'Kadaluarsa pada:' : 'Batas bayar:' }} {{ $deadlineFormatted }}
                                </span>
                            @endif
                        </div>
                    </div>

                    @if(!$isExpired)
                        <div class="flex flex-wrap items-center justify-end gap-3 mt-4 pt-4 border-t border-blue-100/80">
                            {{-- Form Tolak --}}
                            <form action="{{ route('klien.maintenance.respond', [$p['id'] ?? 0, $offeredExt['id']]) }}" method="POST"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menolak penawaran harga ini?')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="response" value="reject">
                                <button type="submit" class="px-4 py-2 text-xs font-semibold text-red-600 hover:text-red-700 bg-white border border-red-200 rounded-xl hover:bg-red-50 transition">
                                    Tolak Penawaran
                                </button>
                            </form>

                            {{-- Form Setujui & Bayar --}}
                            <form action="{{ route('klien.maintenance.respond', [$p['id'] ?? 0, $offeredExt['id']]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="response" value="accept">
                                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Setujui & Terbitkan Tagihan
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="mt-3 text-xs text-red-600 font-medium">
                            Penawaran ini telah melewati batas waktu pembayaran. Silakan hubungi pengurus TeFa atau ajukan perpanjangan baru.
                        </div>
                    @endif
                </div>
            @endif

            {{-- Banner Menunggu Pembayaran Maintenance --}}
            @if($waitingPaymentExt)
                <div class="rounded-2xl border border-purple-200 bg-purple-50 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                            <h3 class="text-sm font-bold text-purple-900">Tagihan Maintenance Menunggu Pembayaran</h3>
                        </div>
                        <p class="text-xs text-purple-700">
                            Penawaran perpanjangan +{{ $waitingPaymentExt['months'] }} bulan telah disetujui. Tagihan sebesar <strong>Rp {{ number_format((float)$waitingPaymentExt['price'], 0, ',', '.') }}</strong> telah diterbitkan.
                        </p>
                    </div>
                    @php
                        $directUploadUrl = !empty($waitingPaymentExt['payment_id'])
                            ? route('klien.tagihan.unggah', ['proposal' => $proposal['id'], 'payment' => $waitingPaymentExt['payment_id']])
                            : route('klien.tagihan');
                    @endphp
                    <a href="{{ $directUploadUrl }}" class="inline-flex items-center justify-center gap-2 bg-purple-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl hover:bg-purple-700 transition whitespace-nowrap shadow-xs">
                        Unggah Bukti Pembayaran
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            @endif

            {{-- Main grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left: Tickets --}}
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-slate-800 mb-4">Tiket Error / Bug ({{ count($tickets) }})</h2>

                        @forelse($tickets as $ticket)
                            @php
                                $sc = $statusColors[$ticket['status'] ?? 'open'] ?? $statusColors['open'];
                                $pc = $priorityColors[$ticket['priority'] ?? 'medium'] ?? 'text-slate-500';
                            @endphp
                            <div class="border border-slate-100 rounded-xl p-4 mb-3 last:mb-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-slate-800 text-sm truncate">{{ $ticket['title'] }}</p>
                                        @if(!empty($ticket['description']))
                                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $ticket['description'] }}</p>
                                        @endif
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            <span class="text-[11px] font-semibold {{ $sc['bg'] }} {{ $sc['text'] }} border {{ $sc['border'] }} rounded-full px-2 py-0.5">{{ $sc['label'] }}</span>
                                            <span class="text-[11px] font-semibold {{ $pc }}">Prioritas: {{ ucfirst($ticket['priority'] ?? 'medium') }}</span>
                                        </div>
                                        @if(!empty($ticket['resolution_notes']))
                                            <div class="mt-3 bg-emerald-50 border border-emerald-100 rounded-lg p-3">
                                                <p class="text-[11px] font-bold text-emerald-700 mb-1">Balasan dari TEVARA:</p>
                                                <p class="text-xs text-emerald-800">{{ $ticket['resolution_notes'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-[11px] text-slate-400">Dilaporkan</p>
                                        <p class="text-[11px] font-semibold text-slate-600">{{ \Carbon\Carbon::parse($ticket['created_at'])->translatedFormat('d M Y') }}</p>
                                        @if(!empty($ticket['handler']))
                                            <p class="text-[11px] text-slate-400 mt-1">Handler</p>
                                            <p class="text-[11px] font-semibold text-slate-600">{{ $ticket['handler']['name'] ?? '-' }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-sm text-slate-400">Belum ada tiket yang dilaporkan.</p>
                                @if($isActive)
                                    <button @click="reportModal = true" class="mt-2 text-xs font-semibold text-[#0B3B82] hover:underline">Laporkan sekarang →</button>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Right: Info + Extension Requests --}}
                <div class="space-y-4">

                    {{-- Info card --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Info Garansi</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Durasi</span>
                                <span class="font-semibold text-slate-800">{{ $p['warranty_months'] ?? '-' }} bulan</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Mulai</span>
                                <span class="font-semibold text-slate-800">{{ !empty($p['warranty_start_date']) ? \Carbon\Carbon::parse($p['warranty_start_date'])->translatedFormat('d M Y') : '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Berakhir</span>
                                <span class="font-semibold {{ $isActive ? 'text-emerald-700' : 'text-red-600' }}">{{ $warrantyEnd?->translatedFormat('d M Y') ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Skema</span>
                                <span class="font-semibold text-slate-800">{{ $deliveryType === 'source_code' ? 'Source Diserahkan' : 'Kelola TEVARA' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Extension Requests --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Riwayat Perpanjangan</h3>
                        @forelse($extReqs as $req)
                            @php $esc = $extStatusColors[$req['status']] ?? $extStatusColors['pending']; @endphp
                            <div class="border border-slate-100 rounded-xl p-3 mb-2 last:mb-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-700">{{ $req['type'] === 'warranty' ? 'Garansi' : 'Maintenance' }} +{{ $req['months'] }} bln</p>
                                        <p class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($req['created_at'])->translatedFormat('d M Y') }}</p>
                                    </div>
                                    <span class="text-[11px] font-bold {{ $esc['bg'] }} {{ $esc['text'] }} rounded-full px-2 py-0.5">{{ $esc['label'] }}</span>
                                </div>
                                @if(!empty($req['price']))
                                    <p class="text-xs font-bold text-slate-800 mt-1">Rp {{ number_format((float)$req['price'], 0, ',', '.') }}</p>
                                @endif
                                @if(!empty($req['reviewer_notes']))
                                    <p class="text-[11px] text-slate-500 mt-1 italic">{{ $req['reviewer_notes'] }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-3">Belum ada riwayat perpanjangan.</p>
                        @endforelse
                    </div>

                </div>
            </div>

            @endif {{-- end if $p --}}

        </main>
    </div>

    {{-- ===== MODAL: Laporkan Error ===== --}}
    <div x-show="reportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/40" @click="reportModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-slate-800">Laporkan Error / Bug</h3>
                <button @click="reportModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('klien.maintenance.report', $p['id'] ?? 0) }}" method="POST" @submit="submittingReport = true">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Error <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required maxlength="255" placeholder="Contoh: Tombol login tidak berfungsi"
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/30 focus:border-[#0B3B82]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Detail</label>
                        <textarea name="description" rows="4" placeholder="Jelaskan langkah-langkah yang menyebabkan error..."
                                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/30 focus:border-[#0B3B82] resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Prioritas</label>
                        <select name="priority" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/30 focus:border-[#0B3B82]">
                            <option value="low">Rendah</option>
                            <option value="medium" selected>Sedang</option>
                            <option value="high">Tinggi</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" @click="reportModal = false"
                            class="flex-1 border border-slate-200 text-slate-700 text-sm font-semibold py-2.5 rounded-xl hover:bg-slate-50 transition">Batal</button>
                    <button type="submit" :disabled="submittingReport"
                            class="flex-1 bg-[#0B3B82] text-white text-sm font-semibold py-2.5 rounded-xl hover:bg-[#082d64] transition disabled:opacity-60">
                        <span x-show="!submittingReport">Kirim Laporan</span>
                        <span x-show="submittingReport">Mengirim...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL: Ajukan Perpanjangan ===== --}}
    <div x-show="extModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/40" @click="extModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-slate-800">
                    Ajukan Perpanjangan <span class="text-[#0B3B82]">Maintenance</span>
                </h3>
                <button @click="extModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('klien.maintenance.extend', $p['id'] ?? 0) }}" method="POST" @submit="submittingExt = true">
                @csrf
                <input type="hidden" name="type" value="maintenance">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">Durasi Perpanjangan <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                            @foreach([1, 3, 6, 12, 24, 36] as $mo)
                                <label class="cursor-pointer">
                                    <input type="radio" name="months" value="{{ $mo }}" x-model="extMonths" class="sr-only peer" {{ $mo === 6 ? 'checked' : '' }}>
                                    <div class="text-center border-2 rounded-xl py-2.5 text-sm font-bold transition peer-checked:border-[#0B3B82] peer-checked:bg-[#EAF3FF] peer-checked:text-[#0B3B82] border-slate-200 text-slate-500 hover:border-slate-300">
                                        {{ $mo }}<br><span class="text-[10px] font-normal">bln</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Kebutuhan SLA (opsional)</label>
                        <textarea name="notes" rows="3" x-model="extNotes" placeholder="Rincian kebutuhan pemantauan, backup, atau penyesuaian sistem..."
                                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/30 focus:border-[#0B3B82] resize-none"></textarea>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 leading-relaxed">
                        Permintaan akan berstatus <strong>Menunggu (Pending)</strong> dan dikirim ke Pengurus TeFa. Kesepakatan biaya / SLA akan dikonfirmasi sebelum masa maintenance aktif.
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" @click="extModal = false"
                            class="flex-1 border border-slate-200 text-slate-700 text-sm font-semibold py-2.5 rounded-xl hover:bg-slate-50 transition">Batal</button>
                    <button type="submit" :disabled="submittingExt"
                            class="flex-1 bg-[#0B3B82] text-white text-sm font-semibold py-2.5 rounded-xl hover:bg-[#082d64] transition disabled:opacity-60">
                        <span x-show="!submittingExt">Ajukan</span>
                        <span x-show="submittingExt">Mengajukan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
</body>
</html>
