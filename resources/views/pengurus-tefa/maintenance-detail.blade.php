{{-- == ( detail maintenance - pengurus tefa ) == --}}
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
    $isWarrantyActive = ($p['has_warranty'] ?? false) && $warrantyEnd && $warrantyEnd->gte($today);

    // Cari perpanjangan maintenance berbayar yang aktif
    $projectMaintenances = collect($p['project_maintenances'] ?? []);
    $activePaidMaintenance = $projectMaintenances
        ->where('type', 'maintenance')
        ->where('status', 'active')
        ->filter(fn ($m) => !empty($m['end_date']) && Carbon::parse($m['end_date'])->gte($today))
        ->sortByDesc('end_date')
        ->first();

    $hasActiveMaintenance = !empty($activePaidMaintenance);
    $maintenanceEnd = $hasActiveMaintenance ? Carbon::parse($activePaidMaintenance['end_date']) : null;

    $isActive = $isWarrantyActive || $hasActiveMaintenance;
    $effectiveEndDate = $maintenanceEnd ?? $warrantyEnd;
    $daysLeft = $effectiveEndDate ? (int) $today->diffInDays($effectiveEndDate, false) : null;
    $deliveryType = $p['delivery_type'] ?? 'source_code';

    $pendingExtReqs = collect($extReqs)->where('status', 'pending');

    $statusColors = [
        'open'        => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',  'border' => 'border-amber-200',  'label' => 'Terbuka'],
        'in_progress' => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',   'border' => 'border-blue-200',   'label' => 'Dikerjakan'],
        'resolved'    => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700','border' => 'border-emerald-200','label' => 'Selesai'],
    ];
    $priorityColors = [
        'low'    => ['bg' => 'bg-slate-50',  'text' => 'text-slate-500',  'label' => 'Rendah'],
        'medium' => ['bg' => 'bg-amber-50',  'text' => 'text-amber-600',  'label' => 'Sedang'],
        'high'   => ['bg' => 'bg-red-50',    'text' => 'text-red-600',    'label' => 'Tinggi'],
    ];
@endphp

<div class="flex min-h-screen" x-data="{
    activeTicket: null,
    replyModal: false,
    extReviewModal: false,
    extReviewId: null,
    extReviewType: '',
    extReviewMonths: 0,
    submitting: false,
    initiatePreviewImg: null,
    replyPreviewImg: null,
    previewModal: false,
    previewModalUrl: '',
    openReply(ticket) {
        this.activeTicket = ticket;
        this.replyPreviewImg = null;
        this.replyModal = true;
    },
    openExtReview(id, type, months) {
        this.extReviewId = id;
        this.extReviewType = type;
        this.extReviewMonths = months;
        this.extReviewModal = true;
    },
    openImage(url) {
        this.previewModalUrl = url;
        this.previewModal = true;
    },
    handleInitiateFile(event) {
        const file = event.target.files[0];
        this.initiatePreviewImg = file ? URL.createObjectURL(file) : null;
    },
    handleReplyFile(event) {
        const file = event.target.files[0];
        this.replyPreviewImg = file ? URL.createObjectURL(file) : null;
    }
}">
    <x-sidebar active="maintenance" />

    <div class="flex-1 ml-64 flex flex-col min-h-screen min-w-0">
        <main class="flex-1 px-8 py-7 space-y-6">

            {{-- Back --}}
            <div>
                <a href="{{ route('pengurus-tefa.maintenance') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-[#0B3B82] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Maintenance
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
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
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
                        <h1 class="text-xl font-extrabold text-slate-800">{{ $p['title'] }}</h1>
                        <p class="text-xs text-slate-500 mt-1 font-mono">{{ $p['project_code'] ?? $p['proposal_code'] ?? '' }}</p>
                        <div class="mt-3 flex flex-wrap gap-4 text-xs text-slate-500">
                            <span>Klien: <strong class="text-slate-700">{{ $p['client']['name'] ?? '-' }}</strong></span>
                            <span>Serah Terima: <strong class="text-slate-700">{{ !empty($p['handover_date']) ? \Carbon\Carbon::parse($p['handover_date'])->translatedFormat('d M Y') : '-' }}</strong></span>
                            @if($warrantyEnd)
                                <span>Garansi s/d: <strong class="{{ $isActive ? 'text-emerald-700' : 'text-red-600' }}">{{ $warrantyEnd->translatedFormat('d M Y') }}</strong></span>
                            @endif
                        </div>
                    </div>

                    @if($p['has_warranty'] ?? false)
                    <div class="bg-{{ $isActive ? 'emerald' : 'red' }}-50 border border-{{ $isActive ? 'emerald' : 'red' }}-200 rounded-2xl px-6 py-4 text-center min-w-[160px]">
                        @if($isActive && $daysLeft !== null)
                            <p class="text-3xl font-black text-emerald-600">{{ $daysLeft }}</p>
                            <p class="text-xs text-emerald-700 font-semibold mt-0.5">hari tersisa</p>
                        @else
                            <p class="text-sm font-bold text-red-600">Masa Berakhir</p>
                        @endif
                        <p class="text-[11px] text-slate-500 mt-1">{{ $warrantyEnd?->translatedFormat('d M Y') ?? '-' }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Main grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left: Tickets --}}
                <div class="lg:col-span-2 space-y-4">

                    {{-- Pending Extension Requests --}}
                    @if($pendingExtReqs->count() > 0)
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                        <h3 class="text-sm font-bold text-amber-800 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Permintaan Perpanjangan Menggantung ({{ $pendingExtReqs->count() }})
                        </h3>
                        @foreach($pendingExtReqs as $req)
                            <div class="bg-white border border-amber-100 rounded-xl p-4 mb-2 last:mb-0">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">
                                            Perpanjangan {{ $req['type'] === 'warranty' ? 'Garansi' : 'Maintenance' }} +{{ $req['months'] }} bulan
                                        </p>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            Diajukan oleh {{ $req['requester']['name'] ?? 'Klien' }} · {{ \Carbon\Carbon::parse($req['created_at'])->translatedFormat('d M Y') }}
                                        </p>
                                        @if(!empty($req['notes']))
                                            <p class="text-xs text-slate-600 mt-1 italic">{{ $req['notes'] }}</p>
                                        @endif
                                    </div>
                                    <button @click="openExtReview({{ $req['id'] }}, '{{ $req['type'] }}', {{ $req['months'] }})"
                                            class="flex-shrink-0 bg-[#0B3B82] text-white text-xs font-semibold px-4 py-2 rounded-xl hover:bg-[#082d64] transition whitespace-nowrap">
                                        Review
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Tiket list --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-slate-800 mb-4">Tiket Error / Bug ({{ count($tickets) }})</h2>

                        @forelse($tickets as $ticket)
                            @php
                                $sc = $statusColors[$ticket['status'] ?? 'open'] ?? $statusColors['open'];
                                $pc = $priorityColors[$ticket['priority'] ?? 'medium'] ?? $priorityColors['medium'];
                            @endphp
                            <div class="border border-slate-100 rounded-xl p-4 mb-3 last:mb-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-slate-800 text-sm">{{ $ticket['title'] }}</p>
                                        @if(!empty($ticket['description']))
                                            <p class="text-xs text-slate-600 mt-1 whitespace-pre-line leading-relaxed">{{ $ticket['description'] }}</p>
                                        @endif
                                        @if(!empty($ticket['image_url']))
                                            <div class="mt-2.5">
                                                <button type="button" @click="openImage('{{ $ticket['image_url'] }}')"
                                                        class="group relative inline-block rounded-xl overflow-hidden border border-slate-200 hover:border-blue-400 transition shadow-2xs">
                                                    <img src="{{ $ticket['image_url'] }}" alt="Lampiran Error" class="h-28 w-auto max-w-xs object-cover rounded-xl group-hover:scale-105 transition duration-200">
                                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-semibold gap-1.5 transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                                        <span>Lihat Gambar</span>
                                                    </div>
                                                </button>
                                            </div>
                                        @endif
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            <span class="text-[11px] font-semibold {{ $sc['bg'] }} {{ $sc['text'] }} border {{ $sc['border'] }} rounded-full px-2 py-0.5">{{ $sc['label'] }}</span>
                                            <span class="text-[11px] font-semibold {{ $pc['bg'] }} {{ $pc['text'] }} rounded-full px-2 py-0.5">{{ $pc['label'] }}</span>
                                            @if(!empty($ticket['reporter']))
                                                <span class="text-[11px] text-slate-400">oleh {{ $ticket['reporter']['name'] ?? '-' }}</span>
                                            @endif
                                        </div>
                                        @if(!empty($ticket['resolution_notes']) || !empty($ticket['resolution_image_url']))
                                            <div class="mt-3 bg-emerald-50 border border-emerald-100 rounded-lg p-3.5 space-y-2">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                                    <p class="text-[11px] font-bold text-emerald-800">Catatan Penyelesaian & Tindakan:</p>
                                                </div>
                                                @if(!empty($ticket['resolution_notes']))
                                                    <p class="text-xs text-emerald-900 whitespace-pre-line leading-relaxed">{{ $ticket['resolution_notes'] }}</p>
                                                @endif
                                                @if(!empty($ticket['resolution_image_url']))
                                                    <div class="pt-1">
                                                        <button type="button" @click="openImage('{{ $ticket['resolution_image_url'] }}')"
                                                                class="group relative inline-block rounded-lg overflow-hidden border border-emerald-200 hover:border-emerald-400 transition">
                                                            <img src="{{ $ticket['resolution_image_url'] }}" alt="Lampiran Penyelesaian" class="h-24 w-auto max-w-xs object-cover rounded-lg group-hover:scale-105 transition duration-200">
                                                            <div class="absolute inset-0 bg-emerald-900/50 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[11px] font-bold gap-1 transition">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                                <span>Perbesar</span>
                                                            </div>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                        <p class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($ticket['created_at'])->translatedFormat('d M Y') }}</p>
                                        <button @click="openReply({{ json_encode($ticket) }})"
                                                class="text-xs font-semibold bg-[#EAF3FF] text-[#0B3B82] px-3 py-1.5 rounded-lg hover:bg-[#0B3B82] hover:text-white transition">
                                            {{ $ticket['status'] === 'resolved' ? 'Edit Balasan' : 'Balas & Update' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-sm text-slate-400">Belum ada tiket yang dilaporkan klien.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Form tambah catatan maintenance inisiatif — saat garansi atau maintenance aktif --}}
                    @if($isActive)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-slate-800 mb-1">Tambah Catatan Maintenance</h2>
                        <p class="text-xs text-slate-500 mb-4">Buat tiket inisiatif dari TEVARA meski klien tidak melaporkan bug. Klien akan mendapat notifikasi.</p>
                        <form action="{{ route('pengurus-tefa.maintenance.ticket.store', $p['id']) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Judul <span class="text-red-500">*</span></label>
                                <input type="text" name="title" required maxlength="255" placeholder="Pembaruan dependensi library"
                                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/30 focus:border-[#0B3B82]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Lengkap</label>
                                <textarea name="description" rows="3" placeholder="Detail pekerjaan maintenance yang dilakukan..."
                                          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/30 focus:border-[#0B3B82]"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Lampiran Gambar (Opsional)</label>
                                <div class="flex flex-col gap-2">
                                    <label class="flex items-center gap-3 border border-slate-200 hover:border-[#0B3B82] rounded-xl px-4 py-2.5 cursor-pointer bg-slate-50/50 hover:bg-slate-50 transition group">
                                        <svg class="w-5 h-5 text-slate-400 group-hover:text-[#0B3B82] transition flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                                        <span class="text-xs text-slate-600 font-medium">Unggah screenshot / log teknis (Maks. 10MB)</span>
                                        <input type="file" name="image" accept="image/*" class="sr-only" @change="handleInitiateFile($event)">
                                    </label>
                                    <template x-if="initiatePreviewImg">
                                        <div class="relative inline-block">
                                            <img :src="initiatePreviewImg" alt="Preview" class="h-28 w-auto max-w-xs rounded-xl border border-slate-200 object-cover shadow-2xs">
                                            <button type="button" @click="initiatePreviewImg = null; $el.closest('form').querySelector('input[name=image]').value = ''"
                                                    class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full p-1 shadow hover:bg-red-700 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="flex gap-4 items-end">
                                <div class="flex-1">
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Prioritas</label>
                                    <select name="priority" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/30 focus:border-[#0B3B82]">
                                        <option value="low">Rendah</option>
                                        <option value="medium" selected>Sedang</option>
                                        <option value="high">Tinggi</option>
                                    </select>
                                </div>
                                <button type="submit" class="bg-[#0B3B82] text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-[#082d64] transition">
                                    Tambah Catatan
                                </button>
                            </div>
                        </form>
                    </div>
                    @else
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 flex items-center gap-3 text-slate-500">
                        <svg class="w-5 h-5 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-sm">Masa garansi dan layanan maintenance telah berakhir. Tiket maintenance baru tidak dapat dibuat.</p>
                    </div>
                    @endif
                </div>

                {{-- Right: Info + Ext history --}}
                <div class="space-y-4">
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

                    {{-- All extension requests --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Riwayat Perpanjangan</h3>
                        @php
                            $allExt = collect($extReqs)->all();
                            $extStatusColors = [
                                'pending'         => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',  'label' => 'Menggantung'],
                                'offered'         => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',   'label' => 'Menunggu Klien'],
                                'waiting_payment' => ['bg' => 'bg-purple-50',  'text' => 'text-purple-700', 'label' => 'Menunggu Pembayaran'],
                                'approved'        => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700','label' => 'Disetujui'],
                                'rejected'        => ['bg' => 'bg-red-50',     'text' => 'text-red-600',    'label' => 'Ditolak'],
                            ];
                        @endphp
                        @forelse($allExt as $req)
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
            @endif

        </main>
    </div>

    {{-- ===== MODAL: Balas & Update Tiket ===== --}}
    <div x-show="replyModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/40" @click="replyModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Balas & Update Tiket</h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="activeTicket?.title"></p>
                </div>
                <button @click="replyModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form :action="`{{ route('pengurus-tefa.maintenance.ticket.update', [$p['id'] ?? 0, '__ID__']) }}`.replace('__ID__', activeTicket?.id)"
                  method="POST" enctype="multipart/form-data" @submit="submitting = true">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                        <select name="status" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/30 focus:border-[#0B3B82]"
                                :value="activeTicket?.status" x-effect="$el.value = activeTicket?.status ?? 'open'">
                            <option value="open">Terbuka</option>
                            <option value="in_progress">Sedang Dikerjakan</option>
                            <option value="resolved">Selesai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Balasan / Catatan Penyelesaian Lengkap</label>
                        <textarea name="resolution_notes" rows="4"
                                  placeholder="Jelaskan apa yang sudah dilakukan atau instruksi untuk klien..."
                                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/30 focus:border-[#0B3B82]"
                                  x-effect="$el.value = activeTicket?.resolution_notes ?? ''"></textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Klien akan mendapat notifikasi ketika status atau balasan diperbarui.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lampiran Foto Bukti Penyelesaian / Perbaikan (Opsional)</label>
                        <div class="flex flex-col gap-2">
                            <label class="flex items-center gap-3 border border-slate-200 hover:border-[#0B3B82] rounded-xl px-4 py-2.5 cursor-pointer bg-slate-50/50 hover:bg-slate-50 transition group">
                                <svg class="w-5 h-5 text-slate-400 group-hover:text-[#0B3B82] transition flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                                <span class="text-xs text-slate-600 font-medium">Pilih gambar bukti penanganan (Maks. 10MB)</span>
                                <input type="file" name="resolution_image" accept="image/*" class="sr-only" @change="handleReplyFile($event)">
                            </label>
                            <template x-if="replyPreviewImg">
                                <div class="relative inline-block">
                                    <img :src="replyPreviewImg" alt="Preview Balasan" class="h-28 w-auto max-w-xs rounded-xl border border-slate-200 object-cover shadow-2xs">
                                    <button type="button" @click="replyPreviewImg = null; $el.closest('form').querySelector('input[name=resolution_image]').value = ''"
                                            class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full p-1 shadow hover:bg-red-700 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                            <template x-if="!replyPreviewImg && activeTicket?.resolution_image_url">
                                <div class="flex items-center gap-2 text-xs text-slate-500 pt-1">
                                    <span>Gambar saat ini:</span>
                                    <button type="button" @click="openImage(activeTicket.resolution_image_url)" class="text-[#0B3B82] font-semibold underline hover:text-[#082d64]">
                                        Lihat Gambar
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" @click="replyModal = false"
                            class="flex-1 border border-slate-200 text-slate-700 text-sm font-semibold py-2.5 rounded-xl hover:bg-slate-50 transition">Batal</button>
                    <button type="submit" :disabled="submitting"
                            class="flex-1 bg-[#0B3B82] text-white text-sm font-semibold py-2.5 rounded-xl hover:bg-[#082d64] transition disabled:opacity-60">
                        <span x-show="!submitting">Simpan & Kirim Notifikasi</span>
                        <span x-show="submitting">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL: Review Perpanjangan ===== --}}
    <div x-show="extReviewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/40" @click="extReviewModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Review & Tentukan Biaya</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Perpanjangan Layanan Maintenance +<span x-text="extReviewMonths" class="font-semibold text-slate-700"></span> Bulan
                    </p>
                </div>
                <button @click="extReviewModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Form Penawaran Harga --}}
            <form :action="`{{ route('pengurus-tefa.maintenance.ext.review', [$p['id'] ?? 0, '__ID__']) }}`.replace('__ID__', extReviewId)"
                  method="POST" @submit="submitting = true" x-ref="approveForm">
                @csrf
                @method('PATCH')
                <input type="hidden" name="decision" value="approved">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Nominal Biaya Maintenance <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="price" min="0" step="1000" required placeholder="3000000"
                                   class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/30 focus:border-[#0B3B82] font-semibold text-slate-800">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Nominal yang akan ditawarkan dan ditagihkan ke klien.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Batas Waktu Pembayaran (Deadline) <span class="text-red-500">*</span>
                        </label>
                        <select name="payment_deadline_hours" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/30 focus:border-[#0B3B82]">
                            <option value="24" selected>24 Jam (1 Hari)</option>
                            <option value="48">48 Jam (2 Hari)</option>
                            <option value="72">72 Jam (3 Hari)</option>
                            <option value="168">7 Hari (1 Minggu)</option>
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Batas waktu bagi klien untuk menyetujui & menyelesaikan penawaran.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Penawaran (Opsional)</label>
                        <textarea name="reviewer_notes" rows="2" placeholder="Catatan SLA atau lingkup perbaikan..."
                                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0B3B82]/30 focus:border-[#0B3B82] resize-none" x-ref="approveNotes"></textarea>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-800">
                        <p class="font-semibold text-blue-900 mb-0.5">Alur Persetujuan & Penagihan:</p>
                        <p class="text-[11px] leading-relaxed text-blue-700">
                            Penawaran harga ini akan dikirim ke klien. Begitu klien menyetujui, sistem otomatis menerbitkan invoice tagihan di menu Keuangan/Tagihan. Masa maintenance aktif setelah pembayaran lunas.
                        </p>
                    </div>
                </div>

                <div class="flex gap-3 mt-5">
                    <button type="button" @click="extReviewModal = false"
                            class="flex-1 border border-slate-200 text-slate-700 text-sm font-semibold py-2.5 rounded-xl hover:bg-slate-50 transition">Batal</button>
                    <button type="button" :disabled="submitting"
                            @click="
                                $refs.approveForm.querySelector('[name=decision]').value = 'rejected';
                                $refs.approveForm.querySelector('[name=price]').removeAttribute('required');
                                $refs.approveForm.querySelector('[name=reviewer_notes]').value = $refs.approveNotes.value;
                                submitting = true;
                                $refs.approveForm.submit();
                            "
                            class="flex-1 bg-red-500 text-white text-sm font-semibold py-2.5 rounded-xl hover:bg-red-600 transition disabled:opacity-60">
                        Tolak
                    </button>
                    <button type="submit" :disabled="submitting"
                            class="flex-1 bg-[#0B3B82] text-white text-sm font-semibold py-2.5 rounded-xl hover:bg-[#082d64] transition disabled:opacity-60">
                        <span x-show="!submitting">Kirim Penawaran</span>
                        <span x-show="submitting">Mengirim...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL: Preview Gambar ===== --}}
    <div x-show="previewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80"
         @click.self="previewModal = false"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center">
            <button @click="previewModal = false"
                    class="absolute -top-10 right-0 text-white hover:text-slate-300 transition flex items-center gap-1 text-xs font-semibold cursor-pointer">
                <span>Tutup</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <img :src="previewModalUrl" alt="Foto Tiket" class="rounded-xl max-h-[85vh] w-auto object-contain shadow-2xl">
        </div>
    </div>

</div>
</body>
</html>
