<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dosen - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php
    $user = $user ?? session('user');
    $dosens = is_array($dosens ?? null) ? $dosens : [];
@endphp
<div class="flex min-h-screen">
    <x-sidebar active="dosen" />
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
                        Dosen Pembimbing &amp; <span class="text-[#FFB800]">PM</span>
                    </h1>
                    <p class="text-sm text-[#64748B] mt-1.5 max-w-2xl leading-relaxed">
                        Kelola data dosen yang berperan sebagai pembimbing dan project manager di TeFa.
                    </p>
                </div>

            </div>

            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-[#1E293B] text-[15px]">Daftar Dosen</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ count($dosens) }} dosen terdaftar di Teaching Factory</p>
                    </div>
                </div>
                @if (!empty($error))
                    <div role="alert" class="border-b border-red-100 bg-red-50 px-6 py-3 text-sm text-red-700">{{ $error }}</div>
                @endif
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-6 py-3">Nama Dosen</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">NIDN</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Jurusan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($dosens as $d)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#0B3B82]/10 flex items-center justify-center text-[11px] font-bold text-[#0B3B82] flex-shrink-0">{{ strtoupper(substr($d['name'],0,1)).strtoupper(substr(explode(' ',$d['name'])[1]??'',0,1)) }}</div>
                                        <p class="font-semibold text-[#1E293B] text-[13px] leading-tight">{{ $d['name'] }}</p>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-[11px] text-slate-500 font-mono">{{ $d['nidn'] ?? '—' }}</td>
                                <td class="px-3 py-3.5"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600">{{ $d['department'] ?? $d['jurusan'] ?? '—' }}</span></td>
                            </tr>
                            @empty
                                <tr><td colspan="3" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada data dosen di database.</td></tr>
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
