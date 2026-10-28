<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Proposal - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user');
    $proposal = is_array($proposal ?? null) ? $proposal : [];
    $error = $error ?? null;

    $pic = is_array($proposal['pic'] ?? null) ? $proposal['pic'] : [];
    $modules = is_array($proposal['modules'] ?? null) ? $proposal['modules'] : [];

    $category = (string) ($proposal['category_name'] ?? 'Other');
    $status = (string) ($proposal['status'] ?? '-');

    $categoryStyle = [
        'web'     => 'bg-blue-50 text-blue-700',
        'mobile'  => 'bg-purple-50 text-purple-700',
        'desktop' => 'bg-indigo-50 text-indigo-700',
        'iot'     => 'bg-amber-50 text-amber-700',
        'other'   => 'bg-slate-100 text-slate-600',
    ];

    $statusStyle = function (string $value): string {
        $s = strtolower($value);

        if (str_contains($s, 'revision') || str_contains($s, 'reject')) {
            return 'bg-red-50 text-red-700 border-red-100';
        }
        if (str_contains($s, 'submitted') || str_contains($s, 'review')) {
            return 'bg-amber-50 text-amber-700 border-amber-100';
        }
        if (str_contains($s, 'approved')) {
            return 'bg-green-50 text-green-700 border-green-100';
        }

        return 'bg-slate-100 text-slate-600 border-slate-200';
    };

    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $formatDate = fn ($value) => $value
        ? \Carbon\Carbon::parse($value)->translatedFormat('d M Y')
        : '—';
@endphp
<div class="flex min-h-screen">
    <x-sidebar active="validasi-proposal" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 pb-32 space-y-6">

            {{-- Breadcrumb --}}
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('pengurus-tefa.validasi-proposal') }}"
                   class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82] transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke Validasi Proposal
                </a>
                @if (!$error && !empty($proposal['proposal_code']))
                    <span class="text-[11px] font-mono font-semibold text-slate-400 bg-white border border-slate-200 px-3 py-1.5 rounded-lg">
                        {{ $proposal['proposal_code'] }}
                    </span>
                @endif
            </div>

            @if ($error)
                <div class="max-w-xl mx-auto bg-white rounded-2xl border border-slate-100 shadow-sm px-8 py-14 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-5">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                        </svg>
                    </div>
                    <h1 class="text-lg font-bold text-[#1E293B]">Detail proposal tidak dapat ditampilkan</h1>
                    <p class="text-sm text-slate-400 mt-2 leading-relaxed">{{ $error }}</p>
                    <a href="{{ route('pengurus-tefa.validasi-proposal') }}"
                       class="inline-flex items-center gap-2 mt-6 rounded-full bg-[#0B3B82] px-5 py-2.5 text-xs font-bold text-white hover:bg-[#092f6a] transition-colors">
                        Kembali ke Daftar Proposal
                    </a>
                </div>
            @else
                {{-- Hero --}}
                <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm px-8 py-7">
                    <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-60 pointer-events-none"></div>

                    <div class="relative z-10 flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg {{ $categoryStyle[strtolower($category)] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $category }}
                                </span>
                                <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full border {{ $statusStyle($status) }}">
                                    {{ $status }}
                                </span>
                            </div>
                            <h1 class="text-2xl font-bold leading-snug text-[#1E293B] max-w-3xl">
                                {{ $proposal['title'] ?? '-' }}
                            </h1>
                            <p class="text-sm text-slate-400 mt-2">
                                Diajukan oleh <span class="font-semibold text-slate-600">{{ $proposal['client_name'] ?? '-' }}</span>
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3 flex-shrink-0">
                            <div class="rounded-xl bg-white/80 border border-slate-100 px-4 py-3 min-w-[140px]">
                                <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Deadline</p>
                                <p class="text-sm font-bold text-[#1E293B] mt-1">{{ $formatDate($proposal['desired_deadline'] ?? null) }}</p>
                            </div>
                            <div class="rounded-xl bg-white/80 border border-slate-100 px-4 py-3 min-w-[150px]">
                                <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Estimasi Anggaran</p>
                                <p class="text-sm font-bold text-[#0B3B82] mt-1">{{ $formatRupiah($proposal['estimated_budget'] ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
                    {{-- Main content --}}
                    <div class="xl:col-span-2 space-y-6">
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6">
                            <div class="flex items-start gap-3.5 mb-4">
                                <span class="w-9 h-9 rounded-xl bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                    </svg>
                                </span>
                                <div>
                                    <h2 class="text-[15px] font-bold text-[#1E293B] leading-tight">Latar Belakang</h2>
                                    <p class="text-xs text-slate-400 mt-0.5">Konteks dan kebutuhan proyek dari klien</p>
                                </div>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ $proposal['background'] ?? '-' }}</p>
                        </section>

                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6">
                            <div class="flex items-start gap-3.5 mb-4">
                                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>
                                    </svg>
                                </span>
                                <div>
                                    <h2 class="text-[15px] font-bold text-[#1E293B] leading-tight">Target Pengguna</h2>
                                    <p class="text-xs text-slate-400 mt-0.5">Pengguna yang akan memakai hasil proyek</p>
                                </div>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ $proposal['target_audience'] ?? '-' }}</p>
                        </section>

                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-7 py-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div class="flex items-start gap-3.5">
                                    <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <h2 class="text-[15px] font-bold text-[#1E293B] leading-tight">Modul Proyek</h2>
                                        <p class="text-xs text-slate-400 mt-0.5">Rincian modul yang diajukan klien</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-2.5 py-1 rounded-full">
                                    {{ count($modules) }} Modul
                                </span>
                            </div>

                            <div class="space-y-3">
                                @forelse ($modules as $index => $module)
                                    <div class="flex items-start gap-3.5 rounded-xl border border-slate-100 bg-[#F8FAFC] p-4">
                                        <span class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-[#0B3B82] text-[11px] font-extrabold flex items-center justify-center flex-shrink-0">
                                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-[#1E293B] leading-tight">{{ $module['title'] ?? '-' }}</p>
                                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ !empty($module['description']) ? $module['description'] : 'Tanpa deskripsi.' }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-xl border border-dashed border-slate-200 px-5 py-8 text-center">
                                        <p class="text-xs font-semibold text-slate-500">Belum ada modul yang diajukan</p>
                                    </div>
                                @endforelse
                            </div>
                        </section>
                    </div>

                    {{-- Sidebar --}}
                    <div class="space-y-6">
                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-6">
                            <h2 class="text-[13px] font-bold text-[#1E293B]">Informasi Klien</h2>

                            <div class="mt-4 space-y-4">
                                <div class="flex items-start gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Perusahaan</p>
                                        <p class="text-xs font-semibold text-[#1E293B] mt-0.5">{{ $proposal['client_name'] ?? '-' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">PIC</p>
                                        <p class="text-xs font-semibold text-[#1E293B] mt-0.5">{{ $pic['name'] ?? '-' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">Email</p>
                                        <p class="text-xs font-semibold text-[#1E293B] mt-0.5 break-all">{{ $pic['email'] ?? '-' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold tracking-wide uppercase text-slate-400">No. HP</p>
                                        <p class="text-xs font-semibold text-[#1E293B] mt-0.5">{{ $pic['no_hp'] ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-6">
                            <h2 class="text-[13px] font-bold text-[#1E293B]">Ringkasan Proposal</h2>

                            <div class="mt-4 space-y-3">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-slate-400">Kategori</span>
                                    <span class="text-xs font-semibold text-[#1E293B] text-right">{{ $category }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-slate-400">Status</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $statusStyle($status) }}">{{ $status }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-slate-400">Deadline</span>
                                    <span class="text-xs font-semibold text-[#1E293B] text-right">{{ $formatDate($proposal['desired_deadline'] ?? null) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-slate-400">Anggaran</span>
                                    <span class="text-xs font-bold text-[#0B3B82] text-right">{{ $formatRupiah($proposal['estimated_budget'] ?? 0) }}</span>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
            @endif
        </main>

        @unless ($error)
            {{-- Review action dock --}}
            <div class="fixed bottom-0 left-64 right-0 z-30 px-8 pb-5 pointer-events-none">
                <div class="pointer-events-auto mx-auto max-w-[1500px] overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 shadow-2xl shadow-slate-900/10 backdrop-blur-md">
                    <div class="h-1 bg-gradient-to-r from-[#0B3B82] via-[#2F6BD8] to-[#FFB800]"></div>

                    <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-3.5">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-10 h-10 rounded-xl bg-[#EAF3FF] text-[#0B3B82] flex items-center justify-center flex-shrink-0 ring-1 ring-[#0B3B82]/10">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="text-xs font-bold text-[#1E293B]">Keputusan Review</p>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $statusStyle($status) }}">{{ $status }}</span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5 truncate">Pilih tindakan untuk proposal {{ $proposal['proposal_code'] ?? '-' }}.</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button"
                                    class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2.5 text-xs font-bold text-red-600 hover:bg-red-50 hover:border-red-300 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Tolak
                            </button>

                            <button type="button"
                                    class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs font-bold text-amber-700 hover:bg-amber-100 hover:border-amber-300 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                Butuh Revisi
                            </button>

                            <div class="hidden sm:block h-6 w-px bg-slate-200 mx-1"></div>

                            <a href="{{ route('pengurus-tefa.validasi-proposal.setujui', $proposal['id'] ?? 0) }}"
                               class="inline-flex items-center gap-2 rounded-xl bg-[#16A34A] px-5 py-2.5 text-xs font-bold text-white shadow-sm shadow-green-600/20 hover:bg-[#15803D] hover:shadow-md transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Setujui
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endunless
    </div>
</div>
</body>
</html>
