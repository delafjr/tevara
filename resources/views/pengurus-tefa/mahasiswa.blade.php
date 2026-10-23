<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mahasiswa - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased">
@php $user = $user ?? session('user'); $firstName = explode(' ', $user['name'] ?? 'Pengurus')[0]; @endphp
<div class="flex min-h-screen">
    <x-sidebar active="mahasiswa" />
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
                        Mahasiswa <span class="text-[#FFB800]">Peserta TeFa</span>
                    </h1>
                    <p class="text-sm text-slate-400 mt-1.5 max-w-2xl leading-relaxed">
                        Pantau keterlibatan mahasiswa dalam setiap projek Teaching Factory.
                    </p>
                </div>

                <div class="relative z-10 flex-shrink-0">
                    <a href="#" class="flex items-center gap-2 bg-[#FFB800] text-[#1E293B] font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-yellow-300 transition-colors shadow-sm">
                        <svg class="w-4 h-4 fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah Mahasiswa
                    </a>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-4 gap-4">
                @foreach([['142','Total Mahasiswa','#0B3B82'],['118','Aktif di Projek','#16A34A'],['3','Angkatan','#F59E0B'],['24','Belum Terlibat','#EF4444']] as [$v,$l,$c])
                <div class="bg-white rounded-xl px-5 py-5 border border-slate-100 shadow-sm">
                    <p class="text-3xl font-bold text-[#1E293B] leading-none">{{ $v }}</p>
                    <p class="text-xs text-slate-400 mt-1.5 font-medium">{{ $l }}</p>
                    <div class="h-1 rounded-full mt-3" style="background:{{ $c }}30"><div class="h-full rounded-full w-3/4" style="background:{{ $c }}"></div></div>
                </div>
                @endforeach
            </div>

            {{-- Tabel Mahasiswa --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-[#1E293B] text-[15px]">Daftar Mahasiswa</p>
                        <p class="text-xs text-slate-400 mt-0.5">142 mahasiswa terdaftar</p>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" placeholder="Cari mahasiswa..." class="text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0B3B82] w-48">
                        <select class="text-xs border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0B3B82]">
                            <option>Semua Angkatan</option><option>2022</option><option>2023</option><option>2024</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-6 py-3">Nama Mahasiswa</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">NIM</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Jurusan / Prodi</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Angkatan</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Projek Saat Ini</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Peran</th>
                                <th class="text-left text-[11px] font-semibold text-slate-400 px-3 py-3">Status</th>
                                <th class="px-3 py-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @php
                            $mahasiswas = [
                                ['name'=>'Ahmad Fajar Nugroho','nim'=>'4.31.22.0.01','prodi'=>'D4 TI','angkatan'=>2022,'projek'=>'Sistem Informasi UMKM','peran'=>'Full Stack Dev','status'=>'Aktif'],
                                ['name'=>'Bella Savitri','nim'=>'4.31.22.0.02','prodi'=>'D4 TI','angkatan'=>2022,'projek'=>'Sistem Informasi UMKM','peran'=>'UI/UX Designer','status'=>'Aktif'],
                                ['name'=>'Cahyo Pramono','nim'=>'4.31.23.0.01','prodi'=>'D4 TI','angkatan'=>2023,'projek'=>'Smart Farming IoT','peran'=>'IoT Engineer','status'=>'Aktif'],
                                ['name'=>'Dewi Lestari','nim'=>'4.31.22.0.03','prodi'=>'D4 TI','angkatan'=>2022,'projek'=>'Website Company Profile','peran'=>'Front End Dev','status'=>'Selesai'],
                                ['name'=>'Eko Prasetyo','nim'=>'4.31.24.0.01','prodi'=>'D4 TI','angkatan'=>2024,'projek'=>'—','peran'=>'—','status'=>'Belum Terlibat'],
                                ['name'=>'Fani Rizki','nim'=>'4.31.23.0.02','prodi'=>'D4 Elektro','angkatan'=>2023,'projek'=>'Smart Farming IoT','peran'=>'Hardware Dev','status'=>'Aktif'],
                                ['name'=>'Gilang Satria','nim'=>'4.31.22.0.04','prodi'=>'D4 TI','angkatan'=>2022,'projek'=>'Monitoring Produksi','peran'=>'Backend Dev','status'=>'Aktif'],
                                ['name'=>'Hana Permatasari','nim'=>'4.31.24.0.02','prodi'=>'D4 TI','angkatan'=>2024,'projek'=>'—','peran'=>'—','status'=>'Belum Terlibat'],
                                ['name'=>'Irfan Maulana','nim'=>'4.31.22.0.05','prodi'=>'D4 TI','angkatan'=>2022,'projek'=>'E-Commerce UMKM Batik','peran'=>'Full Stack Dev','status'=>'Aktif'],
                                ['name'=>'Jessica Amelia','nim'=>'4.31.23.0.03','prodi'=>'D4 TI','angkatan'=>2023,'projek'=>'Sistem Parkir Cerdas','peran'=>'Embedded Dev','status'=>'Aktif'],
                            ];
                            $stStyle=['Aktif'=>'bg-blue-50 text-blue-700','Selesai'=>'bg-green-50 text-green-700','Belum Terlibat'=>'bg-slate-100 text-slate-500'];
                            @endphp
                            @foreach($mahasiswas as $m)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#6366F1]/10 flex items-center justify-center text-[11px] font-bold text-[#6366F1] flex-shrink-0">{{ strtoupper(substr($m['name'],0,1)).strtoupper(substr(explode(' ',$m['name'])[1]??'',0,1)) }}</div>
                                        <p class="font-semibold text-[#1E293B] text-[13px]">{{ $m['name'] }}</p>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-[11px] text-slate-500 font-mono">{{ $m['nim'] }}</td>
                                <td class="px-3 py-3.5"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600">{{ $m['prodi'] }}</span></td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-600">{{ $m['angkatan'] }}</td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-700 font-medium">{{ $m['projek'] }}</td>
                                <td class="px-3 py-3.5 text-[12px] text-slate-500">{{ $m['peran'] }}</td>
                                <td class="px-3 py-3.5"><span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $stStyle[$m['status']] ?? 'bg-slate-100 text-slate-600' }}">{{ $m['status'] }}</span></td>
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
                <div class="px-6 py-3.5 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-400">Menampilkan 10 dari 142 mahasiswa</p>
                    <div class="flex items-center gap-1">
                        <button class="px-3 py-1.5 text-xs rounded-lg bg-[#0B3B82] text-white">1</button>
                        <button class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-500 hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors">2</button>
                        <button class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-500 hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors">3</button>
                        <button class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-500 hover:border-[#0B3B82] hover:text-[#0B3B82] transition-colors">Next →</button>
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
