{{-- == ( Lembar UAT & Sign-off Klien - F-06 ) == --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lembar UAT &amp; Sign-off — {{ $proposal['title'] ?? 'Projek' }} - Tevara</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user', []);
    $userName = $user['name'] ?? 'Klien Mitra';

    $proposal = is_array($proposal ?? null) ? $proposal : [];
    $proposalId = $proposal['id'] ?? null;
    $proposalCode = $proposal['proposal_code'] ?? ('PRJ-' . $proposalId);
    $title = $proposal['title'] ?? 'Projek Tanpa Judul';
    $statusName = $proposal['status']['name'] ?? $proposal['status'] ?? 'Active';

    $uat = is_array($uat ?? null) ? $uat : null;
    $hasUat = !empty($uat);

    $isSigned = !empty($uat['signed_off_at']);
    $signerName = $uat['client_signoff_name'] ?? null;
    $signedAt = $uat['signed_off_at'] ?? null;

    $sessionDate = $uat['session_date'] ?? null;
    $overallResult = $uat['overall_result'] ?? 'PASSED';
    $clientNotes = $uat['client_notes'] ?? '';

    $items = $uat['uat_items'] ?? $uat['items'] ?? [];

    $passedCount = 0;
    $bugCount = 0;
    $failedCount = 0;

    foreach ($items as $it) {
        $res = $it['result'] ?? '';
        if ($res === 'PASSED') $passedCount++;
        elseif ($res === 'BUG_FOUND') $bugCount++;
        elseif ($res === 'FAILED') $failedCount++;
    }

    $totalItems = count($items);
    $passRate = $totalItems > 0 ? round(($passedCount / $totalItems) * 100) : 0;
@endphp

<div class="flex min-h-screen"
     x-data="{
         agreedTerms: false,
         signerName: '{{ addslashes($signerName ?? $userName) }}',
         showSignModal: false
     }">

    <x-sidebar active="projek-saya" />

    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <main class="flex-1 px-8 py-7 space-y-6">

            {{-- Flash Alert Messages --}}
            @if (session('success'))
                <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800 shadow-xs">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-bold text-emerald-900">Sign-off Berhasil</p>
                        <p class="text-xs font-medium text-emerald-700 leading-relaxed">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error') || ($error ?? null))
                <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 shadow-xs">
                    <svg class="w-5 h-5 flex-shrink-0 text-red-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-bold text-red-900">Perhatian</p>
                        <p class="text-xs font-medium text-red-700 leading-relaxed">{{ session('error') ?? $error }}</p>
                    </div>
                </div>
            @endif

            {{-- Breadcrumb --}}
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('klien.projek-saya.detail', $proposalId) }}"
                   class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#0B3B82] transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke Detail Projek
                </a>

                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-mono font-bold text-slate-400 bg-white border border-slate-200 px-3 py-1.5 rounded-lg">
                        {{ $proposalCode }}
                    </span>
                    <span class="text-[11px] font-bold px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $statusName }}
                    </span>
                </div>
            </div>

            {{-- Hero Document Card --}}
            <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-100 shadow-sm p-7">
                <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-white via-[#EAF3FF] to-[#FFF6E0] opacity-50 pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <span class="inline-flex items-center gap-1.5 bg-[#EAF3FF] text-[#0B3B82] text-[10px] font-extrabold tracking-wide uppercase px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0B3B82]"></span>
                            Berita Acara Pengujian Pengguna (UAT)
                        </span>
                        <h1 class="text-2xl font-bold text-[#1E293B]">{{ $title }}</h1>
                        <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                            Lembar verifikasi penerimaan pengguna untuk memastikan bahwa setiap komponen perangkat lunak yang dibangun oleh Teaching Factory Polines telah beroperasi sesuai kebutuhan Anda.
                        </p>
                    </div>

                    @if($hasUat && !$isSigned)
                        <div class="flex-shrink-0">
                            <button type="button"
                                    @click="showSignModal = true"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#0B3B82] hover:bg-[#082a5c] text-white text-xs font-bold shadow-md shadow-[#0B3B82]/25 transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                                </svg>
                                Sign-off &amp; Sahkan Hasil UAT
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            @if(!$hasUat)
                {{-- Empty state: UAT not yet available --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-[#0B3B82] flex items-center justify-center mx-auto shadow-inner">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 2h7M7 16h10"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Lembar UAT Belum Tersedia</h2>
                        <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 leading-relaxed">
                            Tim pengembang dan pengurus TeFa sedang mempersiapkan sesi pengujian dan input butir verifikasi fitur untuk proyek ini. Lembar UAT akan tampil di sini begitu sesi diterbitkan.
                        </p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('klien.projek-saya.detail', $proposalId) }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                            Kembali ke Detail Projek
                        </a>
                    </div>
                </div>
            @else

                {{-- STATUS / SIGN-OFF BADGE BANNER --}}
                @if($isSigned)
                    <div class="rounded-2xl border-2 border-emerald-300 bg-gradient-to-r from-emerald-50 via-teal-50/70 to-emerald-50 p-6 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-extrabold uppercase tracking-wide bg-emerald-200 text-emerald-900 px-2 py-0.5 rounded-full">
                                            Dokumen Sah
                                        </span>
                                        <span class="text-xs text-emerald-800 font-medium">
                                            Disahkan pada {{ \Carbon\Carbon::parse($signedAt)->translatedFormat('d F Y, H:i') }} WIB
                                        </span>
                                    </div>
                                    <h2 class="text-base font-bold text-emerald-950">
                                        UAT Telah Ditandatangani Secara Digital oleh {{ $signerName }}
                                    </h2>
                                    <p class="text-xs text-emerald-800 leading-relaxed">
                                        Terima kasih. Hasil pengujian telah sah dan status proyek Anda telah ditingkatkan ke tahap Handover (Serah Terima Produk).
                                    </p>
                                </div>
                            </div>

                            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-emerald-300 text-emerald-800 hover:bg-emerald-100 text-xs font-bold transition shadow-2xs flex-shrink-0 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.072-1.272-2.962M20.25 15.75H3.75A2.25 2.25 0 0 0 1.5 18v1.5a2.25 2.25 0 0 0 2.25 2.25h16.5A2.25 2.25 0 0 0 22.5 19.5V18a2.25 2.25 0 0 0-2.25-2.25Z"/>
                                </svg>
                                Cetak Salinan UAT
                            </button>
                        </div>
                    </div>
                @else
                    {{-- Banner waiting signoff --}}
                    <div class="rounded-2xl border border-blue-200 bg-blue-50/60 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#0B3B82] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-[#0B3B82]">Menunggu Pengesahan (Sign-off) Anda</h3>
                                <p class="text-xs text-slate-600">Silakan tinjau seluruh butir hasil pengujian di bawah ini, lalu lakukan konfirmasi sign-off.</p>
                            </div>
                        </div>

                        <button type="button"
                                @click="showSignModal = true"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#0B3B82] hover:bg-[#082a5c] text-white text-xs font-bold shadow-sm transition flex-shrink-0 cursor-pointer">
                            Tanda Tangani UAT Sekarang
                        </button>
                    </div>
                @endif

                {{-- Stats & Session Overview --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Sesi Date --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Sesi</p>
                        <p class="text-lg font-bold text-[#0B254A] mt-1">
                            {{ $sessionDate ? \Carbon\Carbon::parse($sessionDate)->translatedFormat('d M Y') : '-' }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Pelaksanaan demo pengujian</p>
                    </div>

                    {{-- Overall Result --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hasil Keseluruhan</p>
                        <div class="mt-1">
                            @if($overallResult === 'PASSED')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                    PASSED
                                </span>
                            @elseif($overallResult === 'PARTIAL')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                                    PARTIAL
                                </span>
                            @elseif($overallResult === 'FAILED')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                    FAILED
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Tingkat kelulusan: {{ $passRate }}%</p>
                    </div>

                    {{-- Total Butir --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Fitur Lulus</p>
                        <p class="text-lg font-bold text-emerald-600 mt-1">
                            {{ $passedCount }} <span class="text-xs font-medium text-slate-400">/ {{ $totalItems }} Fitur</span>
                        </p>
                        <p class="text-[11px] text-emerald-600/80 mt-0.5">Memenuhi kriteria uji</p>
                    </div>

                    {{-- Temuan Bug --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs p-5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Temuan Masalah</p>
                        <p class="text-lg font-bold {{ $bugCount > 0 ? 'text-amber-600' : 'text-slate-700' }} mt-1">
                            {{ $bugCount }} <span class="text-xs font-medium text-slate-400">Catatan Bug</span>
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $failedCount }} fitur failed</p>
                    </div>
                </div>

                {{-- Client Notes if any --}}
                @if(!empty($clientNotes))
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Catatan Pelaksanaan UAT</h3>
                        <p class="text-sm text-slate-700 leading-relaxed italic bg-slate-50 p-4 rounded-xl border border-slate-100">
                            "{{ $clientNotes }}"
                        </p>
                    </div>
                @endif

                {{-- Table of Test Items --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-[#1E293B] text-base">Rincian Butir Pengujian Fitur</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Daftar skenario dan hasil uji per modul</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-100">
                                    <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-6 py-3 w-12 text-center">No</th>
                                    <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Nama Fitur</th>
                                    <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Skenario Uji</th>
                                    <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Hasil Evaluasi</th>
                                    <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Catatan Bug / Temuan</th>
                                    <th class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-6 py-3">Umpan Balik Anda</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($items as $idx => $it)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-3.5 text-center text-xs font-bold text-slate-400">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-3.5 font-bold text-slate-900 text-xs">{{ $it['feature_name'] }}</td>
                                        <td class="px-4 py-3.5 text-xs text-slate-600 max-w-xs">{{ $it['test_scenario'] ?: '-' }}</td>
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            @if($it['result'] === 'PASSED')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    ✓ PASSED
                                                </span>
                                            @elseif($it['result'] === 'BUG_FOUND')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    ⚠ BUG DITEMUKAN
                                                </span>
                                            @elseif($it['result'] === 'FAILED')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    ✕ FAILED
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-xs">
                                            @if($it['result'] === 'BUG_FOUND' || $it['result'] === 'FAILED')
                                                <div class="space-y-1">
                                                    @if(!empty($it['bug_severity']))
                                                        <span class="inline-block text-[9.5px] font-extrabold uppercase tracking-wide px-2 py-0.5 rounded {{ $it['bug_severity'] === 'CRITICAL' ? 'bg-rose-100 text-rose-800' : ($it['bug_severity'] === 'MAJOR' ? 'bg-orange-100 text-orange-800' : 'bg-slate-100 text-slate-700') }}">
                                                            {{ $it['bug_severity'] }}
                                                        </span>
                                                    @endif
                                                    <p class="text-slate-700 leading-snug">{{ $it['bug_description'] ?: '-' }}</p>
                                                </div>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5 text-xs text-slate-600 leading-snug max-w-xs">
                                            {{ $it['client_feedback'] ?: '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-xs text-slate-400">Tidak ada butir pengujian.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Sign-off Section Card (Interactive when not signed) --}}
                @if(!$isSigned)
                    <div class="bg-white rounded-2xl border border-blue-100 shadow-md p-7 space-y-6">
                        <div class="border-b border-slate-100 pb-4">
                            <h3 class="text-base font-bold text-[#0B3B82]">Pengesahan Hasil UAT (Sign-Off)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Dengan menandatangani lembar ini, Anda menyatakan menerima hasil pengujian perangkat lunak di atas.
                            </p>
                        </div>

                        <form action="{{ route('klien.projek-saya.uat.signoff', $proposalId) }}" method="POST" class="space-y-5">
                            @csrf
                            <div>
                                <label for="client_signoff_name" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">
                                    Nama Lengkap Penandatangan (PIC Klien) <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="client_signoff_name"
                                       name="client_signoff_name"
                                       required
                                       x-model="signerName"
                                       placeholder="Nama Lengkap Anda..."
                                       class="w-full max-w-lg text-sm font-semibold border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#0B3B82] focus:ring-2 focus:ring-[#0B3B82]/15 bg-slate-50/50">
                                <p class="text-[10.5px] text-slate-400 mt-1">Nama ini akan tercatat secara resmi sebagai pihak yang menandatangani UAT.</p>
                            </div>

                            <label class="flex items-start gap-3 p-4 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer select-none">
                                <input type="checkbox"
                                       x-model="agreedTerms"
                                       required
                                       class="mt-1 rounded text-[#0B3B82] focus:ring-[#0B3B82]">
                                <span class="text-xs text-slate-700 leading-relaxed">
                                    Saya telah meninjau seluruh butir pengujian dan mengonfirmasi bahwa fungsionalitas sistem telah diuji bersama Teaching Factory Polines. Saya menyetujui sign-off hasil UAT ini.
                                </span>
                            </label>

                            <div>
                                <button type="submit"
                                        :disabled="!agreedTerms || !signerName.trim()"
                                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#0B3B82] hover:bg-[#082a5c] disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold shadow-md shadow-[#0B3B82]/25 transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                    </svg>
                                    Konfirmasi &amp; Tandatangani Sign-off UAT
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

            @endif

        </main>
    </div>

    {{-- MODAL SIGN-OFF POPUP --}}
    @if($hasUat && !$isSigned)
        <div x-show="showSignModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5"
                 @click.outside="showSignModal = false">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#0B3B82] flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Sign-off Hasil UAT Proyek</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Konfirmasi persetujuan berita acara pengujian</p>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-600 space-y-2">
                    <p><strong>Projek:</strong> {{ $title }}</p>
                    <p><strong>Hasil Keseluruhan:</strong>
                        <span class="font-bold text-emerald-700">{{ $overallResult }}</span>
                    </p>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Jika hasil UAT berstatus <strong>PASSED</strong>, sign-off ini akan otomatis menaikkan status proyek ke tahap <strong>Handover (id=9)</strong>.
                    </p>
                </div>

                <form action="{{ route('klien.projek-saya.uat.signoff', $proposalId) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="modal_signer_name" class="block text-[10.5px] font-bold text-slate-500 uppercase tracking-wide mb-1.5">
                            Nama Lengkap Penandatangan <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="modal_signer_name"
                               name="client_signoff_name"
                               required
                               x-model="signerName"
                               class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-[#0B3B82] bg-white">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button"
                                @click="showSignModal = false"
                                class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="!signerName.trim()"
                                class="px-5 py-2 rounded-xl bg-[#0B3B82] hover:bg-[#082a5c] text-white text-xs font-bold shadow-md shadow-[#0B3B82]/25 transition cursor-pointer">
                            Sahkan &amp; Tandatangani (Sign-off)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>

</body>
</html>
