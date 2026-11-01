<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validasi Proposal - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user');
    $firstName = explode(' ', $user['name'] ?? 'Pengurus')[0];

    $proposals = is_array($proposals ?? null) ? $proposals : [];
    $allProposals = is_array($allProposals ?? null) ? $allProposals : [];

    $pendingCount = count($proposals);
    $statusCounts = collect($allProposals)->countBy(fn ($p) => strtolower((string) ($p['status'] ?? '')));
    $approvedCount = $statusCounts->get('approved', 0);
    $revisionCount = $statusCounts->get('revision required', 0);
    $totalProposalCount = count($allProposals) ?: $pendingCount;

    $categoryStyle = [
        'web'     => 'bg-blue-50 text-blue-700',
        'mobile'  => 'bg-purple-50 text-purple-700',
        'desktop' => 'bg-indigo-50 text-indigo-700',
        'iot'     => 'bg-amber-50 text-amber-700',
        'other'   => 'bg-slate-100 text-slate-600',
    ];

    $statusStyle = function (string $status): string {
        $s = strtolower($status);

        if (str_contains($s, 'revision') || str_contains($s, 'reject')) {
            return 'bg-red-50 text-red-700';
        }
        if (str_contains($s, 'submitted') || str_contains($s, 'review')) {
            return 'bg-amber-50 text-amber-700';
        }
        if (str_contains($s, 'approved')) {
            return 'bg-green-50 text-green-700';
        }

        return 'bg-slate-100 text-slate-600';
    };

    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $formatDate = fn ($value) => $value
        ? \Carbon\Carbon::parse($value)->translatedFormat('d M Y')
        : '—';
@endphp
<div class="flex min-h-screen">
    <x-sidebar active="validasi-proposal" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 space-y-7">

            {{-- Flash Alert --}}
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-xs text-emerald-800 shadow-xs">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold">Berhasil!</p>
                        <p class="text-emerald-700 mt-0.5">{{ session('success') }}</p>
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
                        Validasi <span class="text-[#FFB800]">Proposal Projek</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1.5 max-w-2xl leading-relaxed">
                        Tinjau, setujui, atau tolak proposal projek yang masuk dari klien dan tim internal.
                    </p>
                </div>

                <div class="relative z-10 flex-shrink-0">
                    <div class="flex items-center gap-2 bg-[#FFF9ED] border border-[#FFE8A3] rounded-xl px-5 py-2.5">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-[#FFB800]">{{ $pendingCount }}</p>
                            <p class="text-slate-500 text-xs font-medium">Menunggu Validasi</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-4 gap-4">
                @foreach([[ $pendingCount, 'Menunggu Validasi', '#F59E0B'], [ $approvedCount, 'Disetujui', '#16A34A'], [ $revisionCount, 'Dikembalikan', '#EF4444'], [ $totalProposalCount, 'Total Proposal', '#0B3B82']] as [$v, $l, $c])
                <div class="bg-white rounded-xl px-5 py-5 border border-slate-100 shadow-sm">
                    <p class="text-3xl font-bold text-[#1E293B] leading-none">{{ $v }}</p>
                    <p class="text-xs text-slate-400 mt-1.5 font-medium">{{ $l }}</p>
                    <div class="h-1 rounded-full mt-3" style="background:{{ $c }}30"><div class="h-full rounded-full w-3/4" style="background:{{ $c }}"></div></div>
                </div>
                @endforeach
            </div>

            {{-- Proposal Cards - Menunggu Validasi --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <p class="font-semibold text-[#1E293B] text-[15px]">Proposal Menunggu Validasi</p>
                    <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">{{ $pendingCount }} Proposal</span>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    @forelse($proposals as $p)
                    @php
                        $category = (string) ($p['category_name'] ?? 'Other');
                        $status = (string) ($p['status'] ?? '-');
                    @endphp
                    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden hover:border-slate-200 hover:shadow-md transition-all">
                        <div class="px-5 py-4 border-b border-slate-100">
                            <div class="flex items-center justify-between gap-3 mb-2.5">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg {{ $categoryStyle[strtolower($category)] ?? 'bg-slate-100 text-slate-600' }}">{{ $category }}</span>
                                <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full {{ $statusStyle($status) }}">{{ $status }}</span>
                            </div>
                            <h3 class="font-bold text-[#1E293B] text-[13px] leading-tight mb-1">{{ $p['title'] ?? '-' }}</h3>
                            <p class="text-[10px] text-slate-400 font-mono">{{ $p['proposal_code'] ?? '-' }}</p>
                        </div>
                        <div class="px-5 py-4 border-b border-slate-100 grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400 mb-1">Latar Belakang</p>
                                <p class="text-[11.5px] text-slate-600 leading-relaxed whitespace-pre-line">{{ $p['background'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400 mb-1">Target Pengguna</p>
                                <p class="text-[11.5px] text-slate-600 leading-relaxed whitespace-pre-line">{{ $p['target_audience'] ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="px-5 py-3.5 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/></svg>
                                    <span class="text-[11px] text-slate-600">{{ $p['client_name'] ?? '-' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                    <span class="text-[11px] text-slate-600">Deadline {{ $formatDate($p['desired_deadline'] ?? null) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    <span class="text-[11px] font-bold text-[#0B3B82]">{{ $formatRupiah($p['estimated_budget'] ?? 0) }}</span>
                                </div>
                            </div>
                            <a href="{{ route('pengurus-tefa.validasi-proposal.detail', $p['id'] ?? 0) }}"
                               class="inline-flex items-center gap-2 rounded-full bg-[#0B3B82] px-4 py-2 text-[11.5px] font-bold text-white shadow-sm shadow-[#0B3B82]/20 hover:bg-[#092f6a] hover:shadow-md transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                </svg>
                                Lihat Detail
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-1 bg-white rounded-xl border border-dashed border-slate-200 px-6 py-12 text-center">
                        <p class="text-sm font-semibold text-slate-600">Belum ada proposal yang menunggu validasi</p>
                        <p class="text-xs text-slate-400 mt-1">Proposal baru dari klien akan muncul di sini.</p>
                    </div>
                    @endforelse
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
