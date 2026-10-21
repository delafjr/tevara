<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitoring - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php $user = $user ?? session('user'); $firstName = explode(' ', $user['name'] ?? 'Pengurus')[0]; @endphp
<div class="flex min-h-screen">
    <x-sidebar active="monitoring" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <header class="bg-white border-b border-slate-100 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <p class="text-xs text-slate-400 font-medium">Pengurus TEFA</p>
                <p class="text-[15px] font-semibold text-[#1E293B] leading-tight">Monitoring Projek</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400">{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
                <div class="w-px h-4 bg-slate-200"></div>
                <form action="{{ route('logout') }}" method="POST">@csrf
                    <button type="submit" class="text-xs text-slate-500 hover:text-[#0B3B82] font-medium transition-colors">Logout</button>
                </form>
            </div>
        </header>
        <main class="flex-1 px-8 py-7 space-y-7">

            {{-- Hero --}}
            <div class="relative overflow-hidden rounded-2xl bg-[#0B3B82] px-8 py-7">
                <div class="absolute -right-16 -top-16 w-72 h-72 rounded-full bg-white/5"></div>
                <div class="absolute right-24 -bottom-10 w-40 h-40 rounded-full bg-[#FFB800]/10"></div>
                <div class="relative z-10 max-w-lg">
                    <p class="text-[#FFB800] text-xs font-semibold tracking-wide mb-2">Teaching Factory · Polines</p>
                    <h1 class="text-white text-2xl font-bold leading-snug">Monitoring Progress Projek</h1>
                    <p class="text-blue-200 text-sm mt-2 leading-relaxed">Pantau perkembangan real-time semua projek — dari timeline hingga beban tim.</p>
                </div>
            </div>

            {{-- Stat Cards --}}
            <div class="grid grid-cols-4 gap-4">
                @foreach([
                    ['17','Projek Berjalan','#0B3B82','On track'],
                    ['4','Perlu Perhatian','#F59E0B','Progress lambat'],
                    ['2','Terlambat','#EF4444','Deadline terlewat'],
                    ['78%','Rata-rata Progress','#16A34A','Dari 17 projek'],
                ] as [$v,$l,$c,$sub])
                <div class="bg-white rounded-xl px-5 py-5 border border-slate-100 shadow-sm">
                    <p class="text-3xl font-bold leading-none" style="color:{{ $c }}">{{ $v }}</p>
                    <p class="text-xs text-slate-400 mt-1.5 font-medium">{{ $l }}</p>
                    <p class="text-[11px] mt-1 text-slate-500">{{ $sub }}</p>
                </div>
                @endforeach
            </div>

            {{-- Monitoring Cards Grid --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <p class="font-semibold text-[#1E293B] text-[15px]">Status Semua Projek</p>
                    <div class="flex gap-2">
                        <button class="text-xs px-3 py-1.5 rounded-lg bg-[#0B3B82] text-white font-semibold">Semua</button>
                        <button class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 text-slate-500 hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors">Bermasalah</button>
                        <button class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 text-slate-500 hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors">On Track</button>
                    </div>
                </div>
                <div class="space-y-3">
                    @php
                    $monitorings = [
                        ['name'=>'Sistem Informasi UMKM','pm'=>'Dimas Setiawan','klien'=>'PT. ABC Indonesia','prog'=>75,'sprint'=>3,'mhs'=>8,'status'=>'On Track','deadline'=>'12 Okt 2026','catatan'=>'Sprint 3 selesai tepat waktu, deliverable sudah disubmit.'],
                        ['name'=>'Smart Farming IoT','pm'=>'Rizky Pratama','klien'=>'CV. Tani Maju','prog'=>60,'sprint'=>2,'mhs'=>6,'status'=>'On Track','deadline'=>'5 Nov 2026','catatan'=>'Hardware integration selesai, lanjut ke tahap pengujian.'],
                        ['name'=>'Monitoring Produksi','pm'=>'Dimas Setiawan','klien'=>'CV. Tani Maju','prog'=>40,'sprint'=>2,'mhs'=>7,'status'=>'Perhatian','deadline'=>'25 Nov 2026','catatan'=>'Sprint 2 mundur 1 minggu, perlu evaluasi tim.'],
                        ['name'=>'Sistem Parkir Cerdas','pm'=>'Ari Wibowo','klien'=>'Pemkot Semarang','prog'=>55,'sprint'=>3,'mhs'=>9,'status'=>'On Track','deadline'=>'1 Des 2026','catatan'=>'Integrasi sensor selesai, lanjut ke UI dashboard.'],
                        ['name'=>'E-Commerce UMKM Batik','pm'=>'Siti Rahayu','klien'=>'Koperasi Batik','prog'=>80,'sprint'=>4,'mhs'=>6,'status'=>'On Track','deadline'=>'10 Nov 2026','catatan'=>'Hampir selesai, tinggal tahap QA dan testing akhir.'],
                        ['name'=>'Aplikasi Inventori','pm'=>'Thomas Elang','klien'=>'PT. Maju Sentosa','prog'=>25,'sprint'=>1,'mhs'=>5,'status'=>'Perhatian','deadline'=>'18 Nov 2026','catatan'=>'Kickoff selesai, desain UI masih dalam review klien.'],
                        ['name'=>'Absensi Digital','pm'=>'Budi Prasetyo','klien'=>'SMK Negeri 1','prog'=>30,'sprint'=>1,'mhs'=>5,'status'=>'Terlambat','deadline'=>'20 Des 2026','catatan'=>'Terlambat 2 minggu dari jadwal. Perlu meeting evaluasi.'],
                    ];
                    $stColor=['On Track'=>['dot'=>'#16A34A','badge'=>'bg-green-50 text-green-700'],'Perhatian'=>['dot'=>'#F59E0B','badge'=>'bg-amber-50 text-amber-700'],'Terlambat'=>['dot'=>'#EF4444','badge'=>'bg-red-50 text-red-600']];
                    $pc=fn($p)=>$p>=70?'#16A34A':($p>=40?'#0B3B82':($p>=20?'#F59E0B':'#EF4444'));
                    @endphp
                    @foreach($monitorings as $m)
                    @php $sc = $stColor[$m['status']]; @endphp
                    <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-6 py-5 hover:border-slate-200 hover:shadow-md transition-all">
                        <div class="flex items-start gap-5">
                            <div class="flex-1">
                                <div class="flex items-center gap-2.5 mb-1.5">
                                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $sc['dot'] }}"></span>
                                    <p class="font-bold text-[#1E293B] text-[14px]">{{ $m['name'] }}</p>
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $sc['badge'] }}">{{ $m['status'] }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-[12px] text-slate-500 mb-3">
                                    <span>👤 {{ $m['pm'] }}</span>
                                    <span>🏢 {{ $m['klien'] }}</span>
                                    <span>🎓 {{ $m['mhs'] }} mahasiswa</span>
                                    <span>📅 Deadline: {{ $m['deadline'] }}</span>
                                    <span>📌 Sprint {{ $m['sprint'] }}</span>
                                </div>
                                <p class="text-[12px] text-slate-500 italic">{{ $m['catatan'] }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-2 flex-shrink-0 min-w-[140px]">
                                <div class="text-right">
                                    <p class="text-2xl font-black" style="color:{{ $pc($m['prog']) }}">{{ $m['prog'] }}%</p>
                                    <p class="text-[11px] text-slate-400">progress</p>
                                </div>
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width:{{ $m['prog'] }}%;background:{{ $pc($m['prog']) }}"></div>
                                </div>
                                <a href="#" class="text-[11px] font-semibold text-[#0B3B82] hover:underline">Detail →</a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Timeline & Beban Tim --}}
            <div class="grid grid-cols-2 gap-6">
                {{-- Deadline Terdekat --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
                    <p class="font-semibold text-[#1E293B] text-[15px] mb-4">Deadline Terdekat</p>
                    <div class="space-y-3">
                        @foreach([
                            ['Website Company Profile','10 Nov 2026','3 hari lagi','#EF4444'],
                            ['Smart Farming IoT','5 Nov 2026','16 hari lagi','#F59E0B'],
                            ['E-Commerce UMKM Batik','10 Nov 2026','21 hari lagi','#F59E0B'],
                            ['Aplikasi Inventori','18 Nov 2026','29 hari lagi','#16A34A'],
                            ['Monitoring Produksi','25 Nov 2026','36 hari lagi','#16A34A'],
                        ] as [$proj,$dl,$sisa,$c])
                        <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
                            <div>
                                <p class="text-[13px] font-semibold text-[#1E293B] leading-tight">{{ $proj }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $dl }}</p>
                            </div>
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full" style="background:{{ $c }}15;color:{{ $c }}">{{ $sisa }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Beban PM --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
                    <p class="font-semibold text-[#1E293B] text-[15px] mb-4">Beban Project Manager</p>
                    <div class="space-y-4">
                        @foreach([
                            ['Dimas Setiawan',2,'15 mhs'],
                            ['Rizky Pratama',1,'6 mhs'],
                            ['Ari Wibowo',1,'9 mhs'],
                            ['Siti Rahayu',1,'6 mhs'],
                            ['Thomas Elang',1,'5 mhs'],
                            ['Budi Prasetyo',1,'5 mhs'],
                            ['Nadia Putri',1,'4 mhs'],
                        ] as [$pm,$projek,$mhs])
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-[#0B3B82]/10 flex items-center justify-center text-[9px] font-bold text-[#0B3B82] flex-shrink-0">{{ strtoupper(substr($pm,0,1)).strtoupper(substr(explode(' ',$pm)[1]??'',0,1)) }}</div>
                                    <span class="text-[12px] font-semibold text-[#1E293B]">{{ $pm }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[11px] font-bold text-[#0B3B82]">{{ $projek }} projek</span>
                                    <span class="text-[11px] text-slate-400 ml-1.5">· {{ $mhs }}</span>
                                </div>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-[#0B3B82]" style="width:{{ min($projek*50,100) }}%"></div>
                            </div>
                        </div>
                        @endforeach
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
