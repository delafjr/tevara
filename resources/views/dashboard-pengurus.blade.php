<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Pengurus - Tevara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        * { font-family: "Inter", sans-serif; box-sizing: border-box; }
        .stat-card { transition: box-shadow 0.2s, transform 0.2s; }
        .stat-card:hover { box-shadow: 0 8px 24px rgba(11,59,130,0.10); transform: translateY(-2px); }
        .action-btn-primary { background: #1a2e6e; transition: background 0.2s, box-shadow 0.2s; }
        .action-btn-primary:hover { background: #0d2155; box-shadow: 0 4px 16px rgba(26,46,110,0.25); }
        .action-btn-outline { background: white; border: 1.5px solid #e2e8f0; transition: border-color 0.2s, box-shadow 0.2s; }
        .action-btn-outline:hover { border-color: #1a2e6e; }
        .tabel-row:hover { background: #f8faff; }
        .progress-bar { transition: width 0.6s ease; }
        .badge-berjalan { background: #dbeafe; color: #1d4ed8; }
        .badge-review   { background: #fef3c7; color: #d97706; }
        .badge-selesai  { background: #d1fae5; color: #059669; }
        .tindakan-card { border: 1.5px solid #e2e8f0; border-radius: 14px; background: white; transition: box-shadow 0.2s; }
        .tindakan-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    </style>
</head>
<body style="background:#F1F5F9; color:#1E293B; margin:0;">

@php
    $user = $user ?? session('user');
    $userName = $user['name'] ?? 'Pengurus TEFA';
@endphp

<div style="display:flex; min-height:100vh;">
    <x-sidebar active="dashboard" />
    <div style="flex:1; margin-left:256px; display:flex; flex-direction:column; min-height:100vh;">

        <!-- HEADER -->
        <header style="background:white; border-bottom:1px solid #e2e8f0; padding:14px 32px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:20;">
            <div>
                <p style="font-size:11px; color:#94a3b8; font-weight:500; letter-spacing:0.04em; margin:0;">WORKSPACE / PENGURUS TEFA</p>
                <p style="font-size:15px; font-weight:700; color:#0f172a; margin:2px 0 0;">Dashboard Pengurus TEFA</p>
            </div>
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="display:flex; align-items:center; gap:6px; background:#f8faff; border:1px solid #e2e8f0; border-radius:8px; padding:6px 12px;">
                    <svg width="13" height="13" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    <span style="font-size:12px; color:#475569; font-weight:500;">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" style="font-size:12px; color:#64748b; font-weight:500; background:none; border:none; cursor:pointer; padding:6px 10px; border-radius:6px;">Logout</button>
                </form>
            </div>
        </header>

        <main style="flex:1; padding:28px 32px; display:flex; flex-direction:column; gap:22px;">

            <!-- WELCOME BANNER -->
            <div style="background:linear-gradient(135deg,#eef4ff 0%,#f0f5ff 60%,#eef4ff 100%); border-radius:16px; padding:28px 32px; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:space-between; border:1px solid #dde8ff; box-shadow:0 1px 4px rgba(11,59,130,0.06);">
                <div style="position:absolute; right:160px; top:50%; transform:translateY(-50%); width:180px; height:180px; border-radius:50%; background:radial-gradient(circle,#fef3c7 0%,transparent 70%); opacity:0.6;"></div>
                <div style="position:relative; z-index:1;">
                    <div style="display:flex; align-items:center; gap:6px; margin-bottom:12px;">
                        <span style="width:7px; height:7px; border-radius:50%; background:#1a2e6e; display:inline-block;"></span>
                        <p style="font-size:11px; color:#64748b; font-weight:600; letter-spacing:0.06em; margin:0;">WORKSPACE PENGURUS TEFA</p>
                    </div>
                    <h1 style="font-size:24px; font-weight:800; color:#0f172a; line-height:1.3; margin:0 0 8px;">Selamat Datang, {{ $userName }}</h1>
                    <p style="font-size:13px; color:#64748b; line-height:1.65; margin:0; max-width:520px;">Kelola inisiatif Teaching Factory, sinkronisasi kemitraan industri, validasi proposal, dan tata kelola akademik dalam satu kendali.</p>
                </div>
                <div style="position:relative; z-index:1; display:flex; flex-direction:column; gap:10px; flex-shrink:0; margin-left:24px; align-items:flex-end;">
                    <div style="display:flex; align-items:center; gap:8px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:8px 16px;">
                        <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        <span style="font-size:13px; color:#0f172a; font-weight:600;">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px; padding:6px 14px; background:#fef9c3; border-radius:8px;">
                        <span style="width:6px; height:6px; border-radius:50%; background:#ca8a04; display:inline-block;"></span>
                        <span style="font-size:11.5px; color:#92400e; font-weight:600;">Data terpusat TEFA &bull; Realtime</span>
                    </div>
                </div>
            </div>

            <!-- 4 STAT CARDS -->
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px;">
                @php
                $stats = [
                    ['label'=>'Total Project',       'value'=>'24', 'sub'=>'Aktif Tahun Ini',        'color'=>'#1a2e6e','bg'=>'#eef2ff','icon'=>'folder'],
                    ['label'=>'Project Berjalan',    'value'=>'14', 'sub'=>'Dalam Penyelesaian',      'color'=>'#059669','bg'=>'#d1fae5','icon'=>'play'],
                    ['label'=>'Validasi Proposal',   'value'=>'05', 'sub'=>'Menunggu Valid TEFA',     'color'=>'#d97706','bg'=>'#fef3c7','icon'=>'clipboard'],
                    ['label'=>'Mitra & Klien Aktif', 'value'=>'18', 'sub'=>'Industri, OWN Tahun Ini','color'=>'#7c3aed','bg'=>'#f3e8ff','icon'=>'users'],
                ];
                @endphp
                @foreach($stats as $s)
                <div class="stat-card" style="background:white; border-radius:14px; padding:20px; border:1px solid #e2e8f0; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:14px;">
                        <div style="width:40px; height:40px; border-radius:10px; background:{{ $s['bg'] }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            @if($s['icon']==='folder')
                                <svg width="18" height="18" fill="none" stroke="{{ $s['color'] }}" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                            @elseif($s['icon']==='play')
                                <svg width="18" height="18" fill="none" stroke="{{ $s['color'] }}" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M10 8l6 4-6 4V8z"/></svg>
                            @elseif($s['icon']==='clipboard')
                                <svg width="18" height="18" fill="none" stroke="{{ $s['color'] }}" stroke-width="2" viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="m9 14 2 2 4-4"/></svg>
                            @elseif($s['icon']==='users')
                                <svg width="18" height="18" fill="none" stroke="{{ $s['color'] }}" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            @endif
                        </div>
                        <span style="font-size:10px; font-weight:700; color:{{ $s['color'] }}; background:{{ $s['bg'] }}; padding:3px 8px; border-radius:20px;">&#x2191;</span>
                    </div>
                    <p style="font-size:34px; font-weight:800; color:#0f172a; line-height:1; margin:0;">{{ $s['value'] }}</p>
                    <p style="font-size:12px; font-weight:600; color:#475569; margin:5px 0 2px;">{{ $s['label'] }}</p>
                    <p style="font-size:11px; color:{{ $s['color'] }}; margin:0; font-weight:500;">{{ $s['sub'] }}</p>
                </div>
                @endforeach
            </div>

            <!-- QUICK ACTIONS -->
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                <a href="{{ route('pengurus.validasi-proposal') }}" class="action-btn-primary" style="display:flex; align-items:center; gap:12px; padding:14px 20px; border-radius:12px; text-decoration:none; color:white; font-size:13.5px; font-weight:700;">
                    <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span>Review Proposal</span>
                    <span style="margin-left:auto; background:#fbbf24; color:#1a2e6e; font-size:10px; font-weight:800; padding:3px 8px; border-radius:20px;">5+</span>
                    <svg width="14" height="14" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('pengurus.klien-mitra') }}" class="action-btn-outline" style="display:flex; align-items:center; gap:12px; padding:14px 20px; border-radius:12px; text-decoration:none; color:#1E293B; font-size:13.5px; font-weight:600;">
                    <svg width="18" height="18" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Kelola Klien / Mitra
                </a>
                <a href="{{ route('pengurus.dosen') }}" class="action-btn-outline" style="display:flex; align-items:center; gap:12px; padding:14px 20px; border-radius:12px; text-decoration:none; color:#1E293B; font-size:13.5px; font-weight:600;">
                    <svg width="18" height="18" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                    Kelola Dosen / PM
                </a>
            </div>

            <!-- PROJECT TERBARU -->
            <div style="background:white; border-radius:16px; border:1px solid #e2e8f0; box-shadow:0 1px 4px rgba(0,0,0,0.04); overflow:hidden;">
                <div style="padding:18px 24px 14px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <p style="font-size:15px; font-weight:700; color:#0f172a; margin:0;">Project Terbaru</p>
                        <p style="font-size:11.5px; color:#94a3b8; margin:3px 0 0;">5 data &middot; Semua project yang terdaftar</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="display:flex; align-items:center; gap:8px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:7px 12px;">
                            <svg width="13" height="13" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            <input type="text" placeholder="Cari project..." style="font-size:12px; color:#475569; border:none; background:transparent; outline:none; width:140px;">
                        </div>
                        <a href="{{ route('pengurus.projek') }}" style="font-size:12px; font-weight:600; color:#1a2e6e; text-decoration:none; display:flex; align-items:center; gap:4px; white-space:nowrap;">
                            Lihat Semua <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%; font-size:13px; border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <th style="text-align:left; font-size:11px; font-weight:600; color:#94a3b8; padding:10px 24px; letter-spacing:0.04em;">KODE &amp; NAMA PROJECT</th>
                                <th style="text-align:left; font-size:11px; font-weight:600; color:#94a3b8; padding:10px 14px;">KLIEN / MITRA</th>
                                <th style="text-align:left; font-size:11px; font-weight:600; color:#94a3b8; padding:10px 14px;">STATUS</th>
                                <th style="text-align:left; font-size:11px; font-weight:600; color:#94a3b8; padding:10px 14px; width:160px;">PROGRESS</th>
                                <th style="text-align:left; font-size:11px; font-weight:600; color:#94a3b8; padding:10px 14px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $projects = [
                                ['code'=>'PR-2024/08','name'=>'Smart Farming Peroni & Cold Storage',  'client'=>'PT. ABC Ciri Logistik',    'status'=>'Berjalan',        'progress'=>63,'btn'=>'Detail',  'btnC'=>'#1a2e6e','btnB'=>'#eef2ff'],
                                ['code'=>'PR-2426/08','name'=>'Smart Farming IoT & Urgent Channels', 'client'=>'CV. Tani Pelajar',         'status'=>'Berjalan',        'progress'=>45,'btn'=>'Detail',  'btnC'=>'#1a2e6e','btnB'=>'#eef2ff'],
                                ['code'=>'PR-2024/09','name'=>'Profil & Pembakuan Inventori UMKM',   'client'=>'Dinas Koperasi & Lingkar', 'status'=>'Review Proposal', 'progress'=>20,'btn'=>'Validasi','btnC'=>'#d97706','btnB'=>'#fef3c7'],
                                ['code'=>'PR-2024/14','name'=>'Sistem Manajemen Inventori Gudang',   'client'=>'PT. SDT Solar Mega',       'status'=>'Berjalan',        'progress'=>60,'btn'=>'Detail',  'btnC'=>'#1a2e6e','btnB'=>'#eef2ff'],
                                ['code'=>'PR-2024/20','name'=>'Aplikasi Perancangan Micro-Savings',  'client'=>'BPR Santana Bahagia',      'status'=>'Berjalan',        'progress'=>40,'btn'=>'Detail',  'btnC'=>'#1a2e6e','btnB'=>'#eef2ff'],
                            ];
                            @endphp
                            @foreach($projects as $pr)
                            <tr class="tabel-row" style="border-bottom:1px solid #f8fafc;">
                                <td style="padding:14px 24px;">
                                    <p style="font-size:10.5px; color:#94a3b8; font-weight:600; letter-spacing:0.03em; margin:0;">{{ $pr['code'] }}</p>
                                    <p style="font-size:13px; font-weight:600; color:#0f172a; margin:2px 0 0;">{{ $pr['name'] }}</p>
                                </td>
                                <td style="padding:14px; font-size:12.5px; color:#475569;">{{ $pr['client'] }}</td>
                                <td style="padding:14px;">
                                    <span class="{{ $pr['status']==='Berjalan' ? 'badge-berjalan' : ($pr['status']==='Review Proposal' ? 'badge-review' : 'badge-selesai') }}" style="font-size:11px; font-weight:600; padding:4px 10px; border-radius:20px; white-space:nowrap; display:inline-block;">
                                        {{ $pr['status'] }}
                                    </span>
                                </td>
                                <td style="padding:14px; width:160px;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div style="flex:1; height:6px; background:#f1f5f9; border-radius:99px; overflow:hidden;">
                                            <div class="progress-bar" style="height:100%; border-radius:99px; background:{{ $pr['progress']>=60 ? '#1a2e6e' : ($pr['progress']>=30 ? '#d97706' : '#dc2626') }}; width:{{ $pr['progress'] }}%;"></div>
                                        </div>
                                        <span style="font-size:11px; font-weight:700; color:#475569; width:32px; text-align:right;">{{ $pr['progress'] }}%</span>
                                    </div>
                                </td>
                                <td style="padding:14px;">
                                    <a href="#" style="font-size:11.5px; font-weight:600; color:{{ $pr['btnC'] }}; background:{{ $pr['btnB'] }}; padding:5px 14px; border-radius:8px; text-decoration:none;">{{ $pr['btn'] }}</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:12px 24px; border-top:1px solid #f8fafc; display:flex; align-items:center; justify-content:space-between;">
                    <p style="font-size:11.5px; color:#94a3b8; margin:0;">Menampilkan 1&ndash;5 dari 24 project</p>
                    <div style="display:flex; gap:4px;">
                        @foreach([1,2,3] as $pg)
                        <button style="width:28px; height:28px; border-radius:6px; font-size:12px; font-weight:600; border:1px solid {{ $pg===1 ? '#1a2e6e' : '#e2e8f0' }}; background:{{ $pg===1 ? '#1a2e6e' : 'white' }}; color:{{ $pg===1 ? 'white' : '#64748b' }}; cursor:pointer;">{{ $pg }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- MENUNGGU TINDAKAN -->
            <div>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                    <div>
                        <p style="font-size:15px; font-weight:700; color:#0f172a; margin:0;">&#x23F3; Menunggu Tindakan</p>
                        <p style="font-size:11.5px; color:#94a3b8; margin:3px 0 0;">Tugas yang memerlukan persetujuan atau tindakan dari Pengurus TEFA</p>
                    </div>
                    <span style="font-size:11px; font-weight:700; color:#d97706; background:#fef3c7; padding:4px 12px; border-radius:20px;">7 Perlu Review</span>
                </div>
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">
                    @php
                    $tindakan = [
                        ['title'=>'Pengajuan BAST Baru','count'=>'3 Items','color'=>'#d97706','bg'=>'#fef3c7',
                         'items'=>[['pr'=>'PR-2024/09','name'=>'Smart Farming IoT','date'=>'14 Okt 2026'],['pr'=>'PR-2024/11','name'=>'Website Company Profile','date'=>'15 Okt 2026']],
                         'action'=>'Tinjau Sekarang'],
                        ['title'=>'Review Proposal Masuk','count'=>'1 Item','color'=>'#7c3aed','bg'=>'#f3e8ff',
                         'items'=>[['pr'=>'PR-2024/22','name'=>'Perancangan Sistem ERP','date'=>'Tenggat: 3 hari']],
                         'action'=>'Validasi'],
                        ['title'=>'Persetujuan Dosen','count'=>'3 Items','color'=>'#1a2e6e','bg'=>'#eef2ff',
                         'items'=>[['pr'=>'PR-2024/18','name'=>'Inventori Gudang Digital','date'=>'Irfan Maulana, S.T.'],['pr'=>'PR-2024/20','name'=>'Micro-Savings Nasabah','date'=>'Dr. Hendra Kusuma']],
                         'action'=>'Tinjau'],
                    ];
                    @endphp
                    @foreach($tindakan as $t)
                    <div class="tindakan-card" style="padding:18px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                            <p style="font-size:13.5px; font-weight:700; color:#0f172a; margin:0;">{{ $t['title'] }}</p>
                            <span style="font-size:10.5px; font-weight:700; color:{{ $t['color'] }}; background:{{ $t['bg'] }}; padding:3px 10px; border-radius:20px;">{{ $t['count'] }}</span>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:14px;">
                            @foreach($t['items'] as $item)
                            <div style="background:#f8fafc; border-radius:8px; padding:10px 12px;">
                                <p style="font-size:10px; color:#94a3b8; font-weight:600; margin:0;">{{ $item['pr'] }}</p>
                                <p style="font-size:12.5px; font-weight:600; color:#0f172a; margin:2px 0 1px;">{{ $item['name'] }}</p>
                                <p style="font-size:11px; color:#64748b; margin:0;">{{ $item['date'] }}</p>
                            </div>
                            @endforeach
                        </div>
                        <a href="#" style="display:flex; align-items:center; justify-content:center; gap:6px; width:100%; padding:9px; border-radius:9px; background:{{ $t['bg'] }}; color:{{ $t['color'] }}; font-size:12.5px; font-weight:700; text-decoration:none;">
                            {{ $t['action'] }}
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- BOTTOM: DISTRIBUSI + STATUS -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

                <!-- Distribusi Kategori -->
                <div style="background:white; border-radius:16px; border:1px solid #e2e8f0; padding:22px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
                    <p style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 4px;">Distribusi Kategori</p>
                    <p style="font-size:11.5px; color:#94a3b8; margin:0 0 18px;">24 Total Project</p>
                    <div style="display:flex; align-items:center; gap:24px;">
                        <div style="position:relative; width:120px; height:120px; flex-shrink:0;"
                             x-data="{segs:[{pct:29.2,color:'#1a2e6e'},{pct:20.8,color:'#FFB800'},{pct:16.7,color:'#7c3aed'},{pct:12.5,color:'#059669'},{pct:12.5,color:'#ec4899'},{pct:8.3,color:'#94a3b8'}],get arcs(){const c=251.2;let off=0;return this.segs.map(s=>{const da=(s.pct/100)*c;const o=-(off/100)*c;off+=s.pct;return{...s,da,o};})}}">
                            <svg viewBox="0 0 100 100" style="width:100%;height:100%;transform:rotate(-90deg);">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="18"/>
                                <template x-for="a in arcs" :key="a.color">
                                    <circle cx="50" cy="50" r="40" fill="none" :stroke="a.color" stroke-width="18" :stroke-dasharray="`${a.da} ${251.2-a.da}`" :stroke-dashoffset="a.o"/>
                                </template>
                            </svg>
                            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                                <p style="font-size:24px;font-weight:800;color:#0f172a;line-height:1;margin:0;">24</p>
                                <p style="font-size:10px;color:#94a3b8;font-weight:500;margin:0;">Project</p>
                            </div>
                        </div>
                        <div style="flex:1; display:flex; flex-direction:column; gap:8px;">
                            @php $cats=[['label'=>'Web & Sistem Info','count'=>7,'pct'=>'29.2%','color'=>'#1a2e6e'],['label'=>'IoT & Embedded','count'=>5,'pct'=>'20.8%','color'=>'#FFB800'],['label'=>'Aplikasi Mobile','count'=>4,'pct'=>'16.7%','color'=>'#7c3aed'],['label'=>'Desain & Kreatif','count'=>3,'pct'=>'12.5%','color'=>'#059669'],['label'=>'Riset & Analitik','count'=>3,'pct'=>'12.5%','color'=>'#ec4899'],['label'=>'Lainnya','count'=>2,'pct'=>'8.3%','color'=>'#94a3b8']]; @endphp
                            @foreach($cats as $c)
                            <div style="display:flex; align-items:center; justify-content:space-between;">
                                <div style="display:flex; align-items:center; gap:7px;">
                                    <span style="width:10px;height:10px;border-radius:3px;background:{{ $c['color'] }};flex-shrink:0;display:inline-block;"></span>
                                    <span style="font-size:11.5px; color:#475569;">{{ $c['label'] }}</span>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-size:11px; color:#94a3b8;">{{ $c['pct'] }}</span>
                                    <span style="font-size:12px; font-weight:700; color:#0f172a;">{{ $c['count'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Status Project -->
                <div style="background:white; border-radius:16px; border:1px solid #e2e8f0; padding:22px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
                    <p style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 4px;">Status Project</p>
                    <p style="font-size:11.5px; color:#94a3b8; margin:0 0 18px;">14 Total Status</p>
                    @php $statuses=[['label'=>'Belum Berjalan','count'=>'14 Project (60%)','pct'=>60,'color'=>'#1a2e6e'],['label'=>'Sedang Berjalan','count'=>'5 Project (22%)','pct'=>22,'color'=>'#059669'],['label'=>'SELESAI / DITOLAK','count'=>'3 Project (13%)','pct'=>13,'color'=>'#FFB800'],['label'=>'Website & Penelitian','count'=>'3 Project (13%)','pct'=>13,'color'=>'#7c3aed'],['label'=>'Mobile & Formulasi','count'=>'2 Project (8%)','pct'=>8,'color'=>'#ec4899']]; @endphp
                    <div style="display:flex; flex-direction:column; gap:14px;">
                        @foreach($statuses as $s)
                        <div>
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:5px;">
                                <div style="display:flex; align-items:center; gap:7px;">
                                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $s['color'] }};flex-shrink:0;display:inline-block;"></span>
                                    <span style="font-size:12px; color:#475569; font-weight:500;">{{ $s['label'] }}</span>
                                </div>
                                <span style="font-size:11.5px; font-weight:700; color:#0f172a;">{{ $s['count'] }}</span>
                            </div>
                            <div style="height:7px; background:#f1f5f9; border-radius:99px; overflow:hidden;">
                                <div class="progress-bar" style="height:100%; border-radius:99px; background:{{ $s['color'] }}; width:{{ $s['pct'] }}%;"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </main>

        <footer style="padding:14px 32px; border-top:1px solid #e2e8f0; background:white;">
            <p style="font-size:11px; color:#94a3b8; margin:0;">&#169; {{ date('Y') }} TEVARA &middot; Politeknik Negeri Semarang &middot; <em>Knowledge into Action</em></p>
        </footer>
    </div>
</div>

</body>
</html>
