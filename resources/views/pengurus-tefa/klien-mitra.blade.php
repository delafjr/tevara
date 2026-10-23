<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Klien & Mitra - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php $user = $user ?? session('user'); $firstName = explode(' ', $user['name'] ?? 'Pengurus')[0]; @endphp
<div class="flex min-h-screen">
    <x-sidebar active="klien-mitra" />
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <header class="bg-white border-b border-slate-100 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <p class="text-xs text-slate-400 font-medium">Pengurus TEFA</p>
                <p class="text-[15px] font-semibold text-[#1E293B] leading-tight">Klien & Mitra</p>
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
                        Data Klien &amp; <span class="text-[#FFB800]">Mitra TeFa</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1.5 max-w-2xl leading-relaxed">
                        Kelola data klien dan mitra yang telah bekerja sama dengan Teaching Factory.
                    </p>
                </div>

                <div class="relative z-10 flex-shrink-0">
                    <a href="#" class="flex items-center gap-2 bg-[#FFB800] text-[#1E293B] font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-yellow-300 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah Klien
                    </a>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-4 gap-4">
                @foreach([['12','Total Klien','#0B3B82'],['8','Klien Aktif','#16A34A'],['3','Mitra Baru','#F59E0B'],['4','Projek Selesai','#6366F1']] as [$v,$l,$c])
                <div class="bg-white rounded-xl px-5 py-5 border border-slate-100 shadow-sm">
                    <p class="text-3xl font-bold text-[#1E293B] leading-none">{{ $v }}</p>
                    <p class="text-xs text-slate-400 mt-1.5 font-medium">{{ $l }}</p>
                    <div class="h-1 rounded-full mt-3" style="background:{{ $c }}30"><div class="h-full rounded-full w-3/4" style="background:{{ $c }}"></div></div>
                </div>
                @endforeach
            </div>

            {{-- Grid Klien --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-[#1E293B] text-[15px]">Daftar Klien & Mitra</p>
                        <p class="text-xs text-slate-400 mt-0.5">12 klien terdaftar</p>
                    </div>
                    <input type="text" placeholder="Cari klien..." class="text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0B3B82] w-48">
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-6 py-3">Nama Klien / Instansi</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Jenis</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Kontak PIC</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Total Projek</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Projek Aktif</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Total Nilai</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Status</th>
                                <th class="px-3 py-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @php
                            $clients = [
                                ['name'=>'PT. ABC Indonesia','type'=>'Perusahaan','pic'=>'Budi Hartanto','total'=>4,'aktif'=>2,'nilai'=>'Rp 48.000.000','status'=>'Aktif'],
                                ['name'=>'CV. Tani Maju','type'=>'UMKM','pic'=>'Sari Dewi','total'=>3,'aktif'=>2,'nilai'=>'Rp 24.500.000','status'=>'Aktif'],
                                ['name'=>'PT. DEF Solusi','type'=>'Perusahaan','pic'=>'Hendra P.','total'=>2,'aktif'=>0,'nilai'=>'Rp 18.000.000','status'=>'Selesai'],
                                ['name'=>'Pemkot Semarang','type'=>'Pemerintah','pic'=>'Ir. Suprapto','total'=>3,'aktif'=>1,'nilai'=>'Rp 62.000.000','status'=>'Aktif'],
                                ['name'=>'Koperasi Batik','type'=>'Koperasi','pic'=>'Ibu Suminah','total'=>2,'aktif'=>1,'nilai'=>'Rp 16.000.000','status'=>'Aktif'],
                                ['name'=>'Yayasan Al-Hikmah','type'=>'Yayasan','pic'=>'Ustaz Farid','total'=>1,'aktif'=>1,'nilai'=>'Rp 8.500.000','status'=>'Baru'],
                                ['name'=>'PT. Maju Sentosa','type'=>'Perusahaan','pic'=>'Andi Kurniawan','total'=>2,'aktif'=>1,'nilai'=>'Rp 32.000.000','status'=>'Aktif'],
                                ['name'=>'SMK Negeri 1','type'=>'Sekolah','pic'=>'Bapak Suharno','total'=>1,'aktif'=>1,'nilai'=>'Rp 12.000.000','status'=>'Baru'],
                            ];
                            $stStyle=['Aktif'=>'bg-green-50 text-green-700','Baru'=>'bg-blue-50 text-blue-700','Selesai'=>'bg-slate-100 text-slate-500'];
                            @endphp
                            @foreach($clients as $c)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-[#0B3B82]/10 flex items-center justify-center text-[11px] font-bold text-[#0B3B82] flex-shrink-0">{{ strtoupper(substr($c['name'],0,2)) }}</div>
                                        <p class="font-semibold text-[#1E293B] text-[13px]">{{ $c['name'] }}</p>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600">{{ $c['type'] }}</span></td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-600">{{ $c['pic'] }}</td>
                                <td class="px-3 py-3.5 text-[12px] font-semibold text-slate-700 text-center">{{ $c['total'] }}</td>
                                <td class="px-3 py-3.5 text-[12px] font-bold text-[#0B3B82] text-center">{{ $c['aktif'] }}</td>
                                <td class="px-3 py-3.5 text-[12px] font-semibold text-[#1E293B]">{{ $c['nilai'] }}</td>
                                <td class="px-3 py-3.5"><span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $stStyle[$c['status']] ?? 'bg-slate-100 text-slate-600' }}">{{ $c['status'] }}</span></td>
                                <td class="px-3 py-3.5">
                                    <button class="p-1 rounded hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm0 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm0 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z"/></svg>
                                    </button>
                                </td>
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
