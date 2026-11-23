@props([
    'notifications' => [],
    'unreadCount'   => 0,
])

@php
    $notifications = is_array($notifications) ? $notifications : [];
    $unreadCount = (int) $unreadCount;
    $user = session('user') ?? [];
    $currentRole = session('active_role') ?? ($user['roles'][0] ?? 'klien');

    // SVG paths (Heroicons outline stroke-width=2) — dirender server-side agar bisa masuk ke dalam <svg>
    $icons = [
        // dokumen / proposal masuk
        'inbox'             => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z"/>',
        // revisi / refresh
        'arrow-path'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>',
        // disetujui
        'check-circle'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
        // badge / verified
        'check-badge'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>',
        // ditolak / error
        'x-circle'          => '<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
        // warning / peringatan
        'exclamation-circle'=> '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>',
        // edit / revisi diperlukan
        'pencil-square'     => '<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>',
        // penugasan user
        'user-check'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>',
        // registrasi user baru
        'user-plus'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/>',
        // pembayaran / uang
        'banknotes'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0H3m-1.5 0h.75m1.5 0h16.5m-16.5 0H3m16.5 0v.75c0 .414.336.75.75.75h.75m0 0H21m1.5 0h-.75M21 6H3m18 0v12.75a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18.75V6m18 0h.75"/>',
        // bukti / receipt
        'receipt-refund'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h4.875a2.625 2.625 0 0 1 0 5.25H12M8.25 9.75 10.5 7.5M8.25 9.75 10.5 12m9-7.243V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z"/>',
        // UAT / clipboard check
        'clipboard-check'   => '<path stroke-linecap="round" stroke-linejoin="round" d="m11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75"/>',
        // BAST / handover / document check
        'document-check'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>',
        // task
        'list-bullet'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>',
        // maintenance / wrench
        'wrench'            => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.654-4.654m5.886-5.774c.337-.03.676-.03 1.013 0l.072.007c.344.03.682.085 1.013.165M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
        // default / bell
        'bell'              => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>',
    ];

    $getTypeMeta = function (string $type) use ($icons): array {
        $map = [
            'proposal.submitted'              => ['label' => 'Proposal Masuk',            'pill' => 'bg-blue-50 text-blue-700 border-blue-200',        'bg_icon' => 'bg-blue-100 text-[#0B3B82]',       'icon' => 'inbox'],
            'proposal.revision_submitted'     => ['label' => 'Revisi Masuk',              'pill' => 'bg-blue-50 text-blue-700 border-blue-200',        'bg_icon' => 'bg-blue-100 text-[#0B3B82]',       'icon' => 'arrow-path'],
            'proposal.approved'               => ['label' => 'Proposal Disetujui',        'pill' => 'bg-emerald-50 text-emerald-700 border-emerald-200','bg_icon' => 'bg-emerald-100 text-emerald-700',  'icon' => 'check-circle'],
            'proposal.rejected'               => ['label' => 'Proposal Ditolak',          'pill' => 'bg-red-50 text-red-700 border-red-200',           'bg_icon' => 'bg-red-100 text-red-700',          'icon' => 'x-circle'],
            'proposal.revision_required'      => ['label' => 'Perlu Revisi',              'pill' => 'bg-amber-50 text-amber-800 border-amber-300',     'bg_icon' => 'bg-amber-100 text-amber-700',      'icon' => 'pencil-square'],
            'proposal.pm_assigned'            => ['label' => 'Penugasan PM',              'pill' => 'bg-indigo-50 text-indigo-700 border-indigo-200',  'bg_icon' => 'bg-indigo-100 text-indigo-700',    'icon' => 'user-check'],
            'payment.created'                 => ['label' => 'Termin Pembayaran',         'pill' => 'bg-purple-50 text-purple-700 border-purple-200',  'bg_icon' => 'bg-purple-100 text-purple-700',    'icon' => 'banknotes'],
            'payment.terms_set'               => ['label' => 'Termin Ditetapkan',         'pill' => 'bg-purple-50 text-purple-700 border-purple-200',  'bg_icon' => 'bg-purple-100 text-purple-700',    'icon' => 'banknotes'],
            'payment.proof_uploaded'          => ['label' => 'Bukti Pembayaran',          'pill' => 'bg-cyan-50 text-cyan-800 border-cyan-200',        'bg_icon' => 'bg-cyan-100 text-cyan-700',        'icon' => 'receipt-refund'],
            'payment.verified'                => ['label' => 'Pembayaran Lunas',          'pill' => 'bg-emerald-50 text-emerald-700 border-emerald-200','bg_icon' => 'bg-emerald-100 text-emerald-700',  'icon' => 'check-badge'],
            'payment.rejected'                => ['label' => 'Pembayaran Ditolak',        'pill' => 'bg-red-50 text-red-700 border-red-200',           'bg_icon' => 'bg-red-100 text-red-700',          'icon' => 'exclamation-circle'],
            'client.registration_requested'   => ['label' => 'Registrasi Klien',          'pill' => 'bg-blue-50 text-blue-700 border-blue-200',        'bg_icon' => 'bg-blue-100 text-[#0B3B82]',       'icon' => 'user-plus'],
            'handover.prepared'               => ['label' => 'BAST Menunggu',             'pill' => 'bg-teal-50 text-teal-700 border-teal-200',        'bg_icon' => 'bg-teal-100 text-teal-700',        'icon' => 'document-check'],
            'handover.approved'               => ['label' => 'BAST Disetujui',            'pill' => 'bg-emerald-50 text-emerald-700 border-emerald-200','bg_icon' => 'bg-emerald-100 text-emerald-700',  'icon' => 'document-check'],
            'project.team_assigned'           => ['label' => 'Penugasan Tim',             'pill' => 'bg-indigo-50 text-indigo-700 border-indigo-200',  'bg_icon' => 'bg-indigo-100 text-indigo-700',    'icon' => 'user-check'],
            'task.assigned'                   => ['label' => 'Task Baru',                 'pill' => 'bg-violet-50 text-violet-700 border-violet-200',  'bg_icon' => 'bg-violet-100 text-violet-700',    'icon' => 'list-bullet'],
            'task.progress_submitted'         => ['label' => 'Laporan Progres',           'pill' => 'bg-sky-50 text-sky-700 border-sky-200',           'bg_icon' => 'bg-sky-100 text-sky-700',          'icon' => 'list-bullet'],
            'task.reviewed'                   => ['label' => 'Review Task',               'pill' => 'bg-indigo-50 text-indigo-700 border-indigo-200',  'bg_icon' => 'bg-indigo-100 text-indigo-700',    'icon' => 'list-bullet'],
            'uat.submitted'                   => ['label' => 'UAT Masuk',                 'pill' => 'bg-sky-50 text-sky-700 border-sky-200',           'bg_icon' => 'bg-sky-100 text-sky-700',          'icon' => 'clipboard-check'],
            'uat.reviewed'                    => ['label' => 'Review UAT',                'pill' => 'bg-indigo-50 text-indigo-700 border-indigo-200',  'bg_icon' => 'bg-indigo-100 text-indigo-700',    'icon' => 'clipboard-check'],
            'uat.retest_ready'                => ['label' => 'Siap Uji Ulang',            'pill' => 'bg-amber-50 text-amber-700 border-amber-200',     'bg_icon' => 'bg-amber-100 text-amber-700',      'icon' => 'clipboard-check'],
            'maintenance.ticket_created'      => ['label' => 'Laporan Error',             'pill' => 'bg-amber-50 text-amber-700 border-amber-200',     'bg_icon' => 'bg-amber-100 text-amber-700',      'icon' => 'wrench'],
            'maintenance.ticket_created_by_tefa'=>['label'=> 'Catatan Maintenance',       'pill' => 'bg-amber-50 text-amber-700 border-amber-200',     'bg_icon' => 'bg-amber-100 text-amber-700',      'icon' => 'wrench'],
            'maintenance.ticket_updated'      => ['label' => 'Update Tiket',              'pill' => 'bg-amber-50 text-amber-700 border-amber-200',     'bg_icon' => 'bg-amber-100 text-amber-700',      'icon' => 'wrench'],
            'maintenance.ticket_resolved'     => ['label' => 'Tiket Selesai',             'pill' => 'bg-emerald-50 text-emerald-700 border-emerald-200','bg_icon' => 'bg-emerald-100 text-emerald-700',  'icon' => 'wrench'],
            'maintenance.extension_requested' => ['label' => 'Ajuan Perpanjangan',        'pill' => 'bg-purple-50 text-purple-700 border-purple-200',  'bg_icon' => 'bg-purple-100 text-purple-700',    'icon' => 'wrench'],
            'maintenance.extension_approved'  => ['label' => 'Perpanjangan Disetujui',    'pill' => 'bg-emerald-50 text-emerald-700 border-emerald-200','bg_icon' => 'bg-emerald-100 text-emerald-700',  'icon' => 'wrench'],
            'maintenance.extension_rejected'  => ['label' => 'Perpanjangan Ditolak',      'pill' => 'bg-red-50 text-red-700 border-red-200',           'bg_icon' => 'bg-red-100 text-red-700',          'icon' => 'wrench'],
        ];

        $meta = $map[$type] ?? [
            'label'   => 'Pemberitahuan',
            'pill'    => 'bg-slate-100 text-slate-700 border-slate-200',
            'bg_icon' => 'bg-slate-100 text-slate-600',
            'icon'    => 'bell',
        ];

        $meta['icon_svg'] = $icons[$meta['icon']] ?? $icons['bell'];
        return $meta;
    };

    $getTargetUrl = function (array $notification) use ($currentRole): ?string {
        $type   = (string) ($notification['type'] ?? '');
        $propId = $notification['proposal_id'] ?? null;

        if ($currentRole === 'pengurus_tefa') {
            if (str_starts_with($type, 'maintenance.')) {
                return $propId
                    ? route('pengurus-tefa.maintenance.detail', $propId)
                    : route('pengurus-tefa.maintenance');
            }
            if (str_starts_with($type, 'uat.')) {
                return $propId
                    ? route('pengurus-tefa.uat.detail', $propId)
                    : route('pengurus-tefa.uat');
            }
            if (str_starts_with($type, 'handover.')) {
                return $propId
                    ? route('pengurus-tefa.bast.prepare.form', $propId)
                    : route('pengurus-tefa.bast');
            }
            if (str_starts_with($type, 'payment.')) {
                return route('pengurus-tefa.keuangan');
            }
            if (str_starts_with($type, 'proposal.') || str_starts_with($type, 'project.')) {
                return $propId
                    ? route('pengurus-tefa.validasi-proposal.detail', $propId)
                    : route('pengurus-tefa.validasi-proposal');
            }
            if ($type === 'client.registration_requested') {
                return route('pengurus-tefa.klien-mitra');
            }
            return route('pengurus-tefa.validasi-proposal');
        }

        if ($currentRole === 'klien') {
            if (str_starts_with($type, 'maintenance.')) {
                return $propId
                    ? route('klien.maintenance.detail', $propId)
                    : route('klien.maintenance');
            }
            if (str_starts_with($type, 'uat.')) {
                return $propId
                    ? route('klien.projek-saya.uat', $propId)
                    : route('klien.projek-saya');
            }
            if (str_starts_with($type, 'handover.')) {
                return $propId
                    ? route('klien.projek-saya.bast.review', $propId)
                    : route('klien.projek-saya');
            }
            if (str_starts_with($type, 'payment.')) {
                return route('klien.tagihan');
            }
            if (str_starts_with($type, 'proposal.')) {
                return $propId
                    ? route('klien.projek-saya.detail', $propId)
                    : route('klien.projek-saya');
            }
            return $propId ? route('klien.projek-saya.detail', $propId) : route('klien.projek-saya');
        }

        if ($currentRole === 'jurusan') {
            if (str_starts_with($type, 'payment.')) {
                return route('jurusan.tagihan');
            }
            return route('jurusan.projek-saya');
        }

        if ($currentRole === 'pm' || $currentRole === 'dosen') {
            if (str_starts_with($type, 'uat.')) {
                return $propId ? route('pm.uat.detail', $propId) : route('pm.task-timeline');
            }
            if (str_starts_with($type, 'task.') || str_starts_with($type, 'project.')) {
                return route('pm.task-timeline', $propId ? ['proposal_id' => $propId] : []);
            }
            return route('pm.project');
        }

        if ($currentRole === 'mahasiswa') {
            return route('mahasiswa.task');
        }

        return null;
    };

    $preparedNotifications = array_map(function ($item) use ($getTypeMeta, $getTargetUrl) {
        $meta = $getTypeMeta((string) ($item['type'] ?? ''));
        $targetUrl = $getTargetUrl($item);
        $timeAgo = !empty($item['created_at'])
            ? \Carbon\Carbon::parse($item['created_at'])->locale('id')->diffForHumans()
            : 'Baru saja';

        return array_merge($item, [
            'meta'       => $meta,
            'target_url' => $targetUrl,
            'time_ago'   => $timeAgo,
        ]);
    }, $notifications);
@endphp

<div x-data="{
    notifications: {{ Js::from($preparedNotifications) }},
    unreadCount: {{ $unreadCount }},
    filter: 'all',
    expanded: true,
    async markAsRead(id) {
        const item = this.notifications.find(n => n.id === id);
        if (!item || item.is_read) return;
        try {
            const res = await fetch('{{ url('/notifications') }}/' + id + '/read', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            if (res.ok) {
                item.is_read = true;
                this.unreadCount = Math.max(0, this.unreadCount - 1);
            }
        } catch (e) {
            console.error('Gagal menandai notifikasi dibaca', e);
        }
    },
    async markAllAsRead() {
        if (this.unreadCount === 0) return;
        try {
            const res = await fetch('{{ route('notifications.read-all') }}', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            if (res.ok) {
                this.notifications.forEach(n => n.is_read = true);
                this.unreadCount = 0;
            }
        } catch (e) {
            console.error('Gagal menandai semua notifikasi dibaca', e);
        }
    },
    get displayList() {
        if (this.filter === 'unread') {
            return this.notifications.filter(n => !n.is_read);
        }
        return this.notifications;
    }
}" class="relative overflow-hidden rounded-2xl bg-white border border-slate-200/80 shadow-sm transition-all">

    {{-- Aksen Garis Gradien Khas Tevara --}}
    <div class="h-1 bg-gradient-to-r from-[#0B3B82] via-[#2F6BD8] to-[#FFB800]"></div>

    {{-- Header Card Notifikasi --}}
    <div class="px-6 py-4.5 flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 bg-slate-50/50">
        <div class="flex items-center gap-3.5 min-w-0">
            {{-- Icon Lonceng Interaktif --}}
            <div class="relative w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition-all"
                 :class="unreadCount > 0 ? 'bg-amber-50 text-amber-600 ring-1 ring-amber-300/80' : 'bg-[#EAF3FF] text-[#0B3B82] ring-1 ring-[#0B3B82]/10'">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                <template x-if="unreadCount > 0">
                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500 ring-2 ring-white"></span>
                    </span>
                </template>
            </div>

            <div class="min-w-0">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-sm font-bold text-[#1E293B] tracking-tight">Notifikasi & Aktivitas Terkini</h2>
                    <template x-if="unreadCount > 0">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FEF3C7] text-amber-800 border border-amber-300 shadow-2xs" style="font-size: 11px; line-height: 1.2;">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            <span x-text="unreadCount + ' Belum Dibaca'"></span>
                        </span>
                    </template>
                    <template x-if="unreadCount === 0 && notifications.length > 0">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200" style="font-size: 11px; line-height: 1.2;">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Semua sudah dibaca
                        </span>
                    </template>
                </div>
                <p class="text-[11.5px] text-slate-400 mt-0.5 truncate">
                    <span x-show="unreadCount > 0">Ada informasi penting yang perlu Anda tinjau segera.</span>
                    <span x-show="unreadCount === 0">Pantau riwayat pembaruan status dan interaksi akun Anda secara real-time.</span>
                </p>
            </div>
        </div>

        {{-- Action Buttons & Filters --}}
        <div class="flex items-center gap-2 flex-wrap ml-auto">
            <template x-if="notifications.length > 0">
                <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-200/60 text-xs font-semibold">
                    <button type="button"
                            @click="filter = 'all'"
                            class="px-3 py-1 rounded-lg transition-all"
                            :class="filter === 'all' ? 'bg-white text-[#0B3B82] shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                        Semua (<span x-text="notifications.length"></span>)
                    </button>
                    <button type="button"
                            @click="filter = 'unread'"
                            class="px-3 py-1 rounded-lg transition-all"
                            :class="filter === 'unread' ? 'bg-white text-[#0B3B82] shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                        Belum Dibaca (<span x-text="unreadCount"></span>)
                    </button>
                </div>
            </template>

            <template x-if="unreadCount > 0">
                <button type="button"
                        @click="markAllAsRead()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5m-6 6 6 6 9-13.5" />
                    </svg>
                    Tandai Semua Dibaca
                </button>
            </template>

            <button type="button"
                    @click="expanded = !expanded"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-all"
                    :title="expanded ? 'Ciutkan notifikasi' : 'Bentangkan notifikasi'">
                <svg class="w-4 h-4 transition-transform duration-200"
                     :class="expanded ? 'rotate-180' : ''"
                     fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Konten List Notifikasi --}}
    <div x-show="expanded" x-collapse>
        <template x-if="notifications.length === 0">
            {{-- Empty State Bila Tidak Ada Notifikasi --}}
            <div class="px-6 py-8 text-center bg-white">
                <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3 ring-1 ring-slate-100">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </div>
                <p class="text-xs font-bold text-slate-700">Belum Ada Notifikasi</p>
                <p class="text-[11px] text-slate-400 mt-1 max-w-sm mx-auto">Semua pembaruan proposal, revisi, dan status proyek akan muncul langsung di sini.</p>
            </div>
        </template>

        <template x-if="notifications.length > 0 && displayList.length === 0 && filter === 'unread'">
            <div class="px-6 py-8 text-center bg-white">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 ring-1 ring-emerald-100">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                </div>
                <p class="text-xs font-bold text-slate-700">Semua Notifikasi Telah Dibaca</p>
                <p class="text-[11px] text-slate-400 mt-1">Gunakan filter "Semua" di atas untuk melihat seluruh riwayat notifikasi Anda.</p>
            </div>
        </template>

        <template x-if="displayList.length > 0">
            <div class="divide-y divide-slate-100 max-h-[380px] overflow-y-auto">
                <template x-for="item in displayList" :key="item.id">
                    <div class="p-4 sm:px-6 transition-all flex flex-col sm:flex-row sm:items-start justify-between gap-4"
                         :class="!item.is_read ? 'bg-gradient-to-r from-blue-50/50 via-white to-amber-50/20 border-l-4 border-l-[#0B3B82]' : 'bg-white hover:bg-slate-50/60'">
                        
                        <div class="flex items-start gap-3.5 min-w-0 flex-1">
                            {{-- Icon per Jenis Notifikasi � path di-inject server-side via icon_svg --}}
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 ring-1 ring-black/5 mt-0.5"
                                 :class="item.meta.bg_icon">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                     x-html="item.meta.icon_svg"></svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md border tracking-wide uppercase"
                                          :class="item.meta.pill"
                                          x-text="item.meta.label"></span>
                                    
                                    <span class="text-xs font-bold text-[#1E293B]" x-text="item.title"></span>

                                    <template x-if="!item.is_read">
                                        <span class="w-2 h-2 rounded-full bg-[#0B3B82] inline-block" title="Belum dibaca"></span>
                                    </template>

                                    <span class="text-[11px] text-slate-400 font-medium ml-auto flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        <span x-text="item.time_ago"></span>
                                    </span>
                                </div>

                                <p class="text-xs text-slate-600 leading-relaxed font-normal" x-text="item.message"></p>
                            </div>
                        </div>

                        {{-- Action Buttons per Item --}}
                        <div class="flex items-center gap-2.5 sm:self-center flex-shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                            <template x-if="!item.is_read">
                                <button type="button"
                                        @click="markAsRead(item.id)"
                                        class="text-[11px] font-semibold text-slate-500 hover:text-[#0B3B82] px-2.5 py-1 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer"
                                        title="Tandai notifikasi ini sudah dibaca">
                                    Tandai Dibaca
                                </button>
                            </template>

                            <template x-if="item.target_url">
                                <a :href="item.target_url"
                                   @click="markAsRead(item.id)"
                                   class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0B3B82] bg-[#EAF3FF] hover:bg-[#DCEBFF] px-3 py-1.5 rounded-xl transition-all cursor-pointer shadow-2xs">
                                    <span>Buka Detail</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                    </svg>
                                </a>
                            </template>
                        </div>

                    </div>
                </template>
            </div>
        </template>
    </div>

</div>
