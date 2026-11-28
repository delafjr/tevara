<?php

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Main page (Public: accessible by guest or logged-in users)
Route::get('/', fn () => view('welcome'));

// Authentication routes
$googleAuthHandler = function (Request $request, ApiService $api) {
    $validated = $request->validate([
        'id_token'   => ['nullable', 'string', 'required_without:credential'],
        'credential' => ['nullable', 'string', 'required_without:id_token'],
    ]);

    $idToken = $validated['id_token'] ?? $validated['credential'];

    try {
        $response = $api->loginWithGoogle($idToken);

        if ($response->successful() && $response->json('success')) {
            $data = $response->json('data');
            $user = $data['user'];
            $allowedRoles = ApiService::getEffectiveRoles($user);

            if (empty($allowedRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun tidak memiliki hak akses role.',
                ], 403);
            }

            session([
                'api_token'   => $data['token'],
                'user'        => $user,
                'active_role' => $allowedRoles[0],
            ]);

            return response()->json([
                'success' => true,
                'message' => $response->json('message') ?? 'Login Google berhasil',
                'data'    => [
                    'user'         => $user,
                    'token'        => $data['token'],
                    'redirect_url' => route('dashboard'),
                ],
            ])->cookie('api_token', $data['token'], 60 * 24 * 7, '/', null, false, false);
        }

        return response()->json([
            'success' => false,
            'message' => $response->json('message') ?? 'Token Google tidak valid.',
            'errors'  => $response->json('errors'),
        ], $response->status() ?: 422);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak dapat terhubung ke server backend: ' . $e->getMessage(),
        ], 500);
    }
};

Route::prefix('auth')->group(function () use ($googleAuthHandler) {
    Route::get('/login', function () {
        if (session('api_token') && session('user')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (Request $request, ApiService $api) {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        try {
            $response = $api->login($request->email, $request->password);

            if ($response->successful() && $response->json('success')) {
                $data = $response->json('data');
                $user = $data['user'];
                $allowedRoles = ApiService::getEffectiveRoles($user);

                if (empty($allowedRoles)) {
                    return back()->withErrors(['email' => 'Akun tidak memiliki hak akses role.'])->withInput();
                }

                session([
                    'api_token'   => $data['token'],
                    'user'        => $user,
                    'active_role' => $allowedRoles[0],
                ]);

                return redirect()->route('dashboard');
            }

            $message = $response->json('message') ?? 'Email atau password salah.';
            return back()->withErrors(['email' => $message])->withInput();
        } catch (\Throwable $e) {
            return back()->withErrors(['email' => 'Tidak dapat terhubung ke server backend: ' . $e->getMessage()])->withInput();
        }
    });

    Route::post('/google', $googleAuthHandler)->name('auth.google');

    Route::get('/register', fn () => view('auth.register'))->name('register');
    Route::post('/register', function (Request $request) {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:8',
        ]);
        return redirect('/dashboard');
    });

    Route::match(['get', 'post'], '/logout', function (Request $request) {
        if ($request->isMethod('get')) {
            return redirect()->route('login');
        }
        session()->forget(['api_token', 'user', 'active_role']);
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});

// Alias endpoint API v1 for Google Auth
Route::post('/api/v1/auth/google', $googleAuthHandler)->name('api.auth.google');

// Protected routes (require valid session api_token and user)
Route::middleware('auth.api')->group(function () {

    // Main dashboard router
    Route::get('/dashboard', function (ApiService $api) {
        $user = session('user');
        $allowedRoles = ApiService::getEffectiveRoles($user);

        if (empty($allowedRoles)) {
            abort(403, 'Akun Anda tidak memiliki hak akses role.');
        }

        $activeRole = session('active_role');
        if (!$activeRole || !in_array($activeRole, $allowedRoles, true)) {
            $activeRole = $allowedRoles[0];
            session(['active_role' => $activeRole]);
        }

        // Endpoint dashboard per role. Dosen & PM memakai endpoint dosen/dashboard.
        $dashboardEndpoint = match ($activeRole) {
            'klien'         => 'klien/dashboard',
            'mahasiswa'     => 'mahasiswa/dashboard',
            'pm'            => 'dosen/dashboard',
            'dosen'         => 'dosen/dashboard',
            'pengurus_tefa' => 'tefa/dashboard',
            'jurusan'       => 'jurusan/dashboard',
            default         => null,
        };

        $dashboardData = [];
        if ($dashboardEndpoint) {
            try {
                $res = $api->get($dashboardEndpoint);
                if ($res->successful()) {
                    $dashboardData = $res->json('data', []);
                }
            } catch (\Throwable $e) {
                // Biarkan halaman tetap tampil dengan nilai default bila backend tidak tersedia.
                $dashboardData = [];
            }
        }

        $notifications = [];
        $unreadCount = 0;
        try {
            $notifRes = $api->get('notifications');
            if ($notifRes->successful() && is_array($notifRes->json('data'))) {
                $notifications = $notifRes->json('data');
                $unreadCount = collect($notifications)->where('is_read', false)->count();
            }
        } catch (\Throwable) {
            $notifications = [];
            $unreadCount = 0;
        }

        $viewMap = [
            'klien'         => 'klien.dashboard',
            'pengurus_tefa' => 'pengurus-tefa.dashboard',
            'pm'            => 'pm.dashboard',
            'dosen'          => 'pm.dashboard',
            'mahasiswa'     => 'mahasiswa.dashboard',
            'jurusan'       => 'jurusan.dashboard',
        ];

        $view = $viewMap[$activeRole] ?? 'dashboard';
        return view($view, [
            'user'          => $user,
            'currentRole'   => $activeRole,
            'dashboard'     => $dashboardData,
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    })->name('dashboard');

    // ── Notifikasi in-app web actions ────────────────
    Route::match(['patch', 'post'], '/notifications/read-all', function (Request $request, ApiService $api) {
        try {
            $response = $api->patch('notifications/read-all');
            if ($request->wantsJson()) {
                return response()->json($response->json(), $response->status());
            }
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Gagal menandai notifikasi.'], 500);
            }
        }
        return back();
    })->name('notifications.read-all');

    Route::match(['patch', 'post'], '/notifications/{id}/read', function (Request $request, ApiService $api, int|string $id) {
        try {
            $response = $api->patch("notifications/{$id}/read");
            if ($request->wantsJson()) {
                return response()->json($response->json(), $response->status());
            }
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Gagal menandai notifikasi.'], 500);
            }
        }
        return back();
    })->name('notifications.read');

    // ── Unduh dokumen SPK/kontrak (proxy ke backend, otorisasi di API) ──
    Route::get('/dokumen/spk/{proposal}', function (ApiService $api, int $proposal) {
        try {
            $response = $api->get("proposals/{$proposal}/contract/file");

            if ($response->failed()) {
                return back()->with('error', $response->json('message') ?? 'Dokumen SPK belum dapat diunduh.');
            }

            return response($response->body(), 200, [
                'Content-Type'        => $response->header('Content-Type') ?? 'application/octet-stream',
                'Content-Disposition' => $response->header('Content-Disposition') ?? 'attachment; filename="spk-' . $proposal . '.pdf"',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengunduh dokumen SPK: ' . $e->getMessage());

            return back()->with('error', 'Dokumen SPK tidak dapat diunduh dari server TEVARA.');
        }
    })->whereNumber('proposal')->name('dokumen.spk');

    // Switch Role handlers (Strictly validated against allowed roles)
    Route::get('/switch-role/{role}', function ($role) {
        $user = session('user');
        $allowedRoles = ApiService::getEffectiveRoles($user);

        if (!in_array($role, $allowedRoles, true)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki role ' . $role);
        }

        session(['active_role' => $role]);
        return redirect()->route('dashboard');
    })->name('switch-role.get');

    Route::post('/switch-role', function (Request $request) {
        $role = $request->input('role');
        $user = session('user');
        $allowedRoles = ApiService::getEffectiveRoles($user);

        if (!in_array($role, $allowedRoles, true)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki role ' . $role);
        }

        session(['active_role' => $role]);
        return redirect()->route('dashboard');
    })->name('switch-role');

    // Halaman Profil Pengguna untuk semua role
    Route::get('/profile', function (ApiService $api) {
        $user = session('user') ?? [];
        try {
            $response = $api->get('auth/me');
            if ($response->successful()) {
                $userData = $response->json('data') ?? $response->json('user');
                if (is_array($userData)) {
                    $user = array_merge($user, $userData);
                    session(['user' => $user]);
                }
            }
        } catch (\Throwable) {
            // Gunakan session user bila endpoint backend tidak merespons
        }

        $allRoles = ApiService::getUserRoles($user);
        $effectiveRoles = ApiService::getEffectiveRoles($user);
        $activeRole = session('active_role') ?? ($effectiveRoles[0] ?? 'klien');

        return view('profile', [
            'user'           => $user,
            'allRoles'       => $allRoles,
            'effectiveRoles' => $effectiveRoles,
            'activeRole'     => $activeRole,
        ]);
    })->name('profile');

    // Handler update password profil pengguna
    Route::match(['post', 'put'], '/profile/password', function (Request $request, ApiService $api) {
        $request->validate([
            'current_password'      => ['nullable', 'string'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ], [
            'password.required'              => 'Password baru wajib diisi.',
            'password.min'                   => 'Password baru minimal 8 karakter.',
            'password.confirmed'             => 'Konfirmasi password tidak cocok dengan password baru.',
            'password_confirmation.required' => 'Ulangi password baru wajib diisi.',
        ]);

        try {
            $payload = [
                'password'              => $request->input('password'),
                'password_confirmation' => $request->input('password_confirmation'),
            ];
            if ($request->filled('current_password')) {
                $payload['current_password'] = $request->input('current_password');
            }

            $response = $api->put('auth/password', $payload);

            // Fallback bila endpoint auth/password tidak tersedia
            if ($response->status() === 404) {
                $userId = session('user.id');
                if ($userId) {
                    $response = $api->put("users/{$userId}", [
                        'password'              => $request->input('password'),
                        'password_confirmation' => $request->input('password_confirmation'),
                    ]);
                }
            }

            if (!$response->successful()) {
                $backendErrors = $response->json('errors') ?? [];
                $message = $response->json('message') ?? 'Gagal mengubah password.';
                return back()->withErrors($backendErrors)->with('error', $message);
            }

            return back()->with('success', 'Password akun Anda berhasil diubah.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat mengubah password: ' . $e->getMessage());
        }
    })->name('profile.password.update');

    $storeProposal = function (Request $request, ApiService $api, string $redirectRoute) {
        // Modul dikirim sebagai JSON string dari repeater Alpine, decode dulu agar rule array berlaku.
        $request->merge([
            'modul' => json_decode($request->input('modul', '[]'), true) ?: [],
        ]);

        $validated = $request->validate([
            'nama_instansi'     => ['required', 'string', 'max:255'],
            'pic_nama'          => ['required', 'string', 'max:100'],
            'kontak_nomor'      => ['nullable', 'string', 'max:30'],
            'kontak_email'      => ['required', 'email', 'max:255'],
            'judul_proyek'      => ['required', 'string', 'max:255'],
            // ID kategori mengikuti master data /categories; validitas akhir dicek backend.
            'kategori_id'       => ['required', 'integer', 'min:1'],
            'delivery_type'      => ['nullable', 'in:source_code,tefa_managed'],
            'has_warranty'       => ['nullable', 'in:0,1,true,false'],
            'warranty_months'    => ['nullable', 'integer', 'min:1', 'max:36'],
            'maintenance_months' => ['nullable', 'integer', 'min:0', 'max:36'],
            'ringkasan'          => ['required', 'string', 'max:500'],
            'target_pengguna'   => ['required', 'string', 'max:1000'],
            'deadline'          => ['required', 'date', 'after_or_equal:' . now()->addDays(30)->toDateString()],
            'anggaran'          => ['required', 'numeric', 'min:0'],
            'modul'             => ['required', 'array', 'min:1'],
            'modul.*.judul'     => ['required', 'string', 'max:255'],
            'modul.*.deskripsi' => ['nullable', 'string', 'max:1000'],
        ]);

        $modules = collect($validated['modul'] ?? [])
            ->filter(fn ($module) => !empty($module['judul']))
            ->map(fn ($module) => [
                'title'       => $module['judul'],
                'description' => $module['deskripsi'] ?? null,
            ])
            ->values()
            ->all();

        $deliveryType = $validated['delivery_type'] ?? 'source_code';
        $hasWarranty = true;
        $warrantyMonths = max(1, (int) ($validated['warranty_months'] ?? 3));
        $maintenanceMonths = max(0, min(36, (int) ($validated['maintenance_months'] ?? 0)));

        $user = session('user', []);
        $response = $api->post('proposals', [
            'client_id'          => (int) ($user['id'] ?? 0),
            'title'              => $validated['judul_proyek'],
            'background'         => $validated['ringkasan'],
            'target_audience'    => $validated['target_pengguna'],
            'category_id'        => (int) $validated['kategori_id'],
            'delivery_type'      => $deliveryType,
            'has_warranty'       => $hasWarranty,
            'warranty_months'    => $warrantyMonths,
            'maintenance_months' => $maintenanceMonths,
            'desired_deadline'   => $validated['deadline'],
            'estimated_budget'   => (int) $validated['anggaran'],
            'modules'            => $modules,
        ]);

        if ($response->successful()) {
            return redirect()
                ->route($redirectRoute)
                ->with('success', 'Pengajuan proyek berhasil dikirim ke administrators TEVARA. Kode proposal: ' . ($response->json('proposal_code') ?? '-'));
        }

        return back()
            ->withInput()
            ->with('error', 'Pengajuan gagal: ' . ($response->json('message') ?? 'terjadi kesalahan pada server TEVARA.'));
    };

    $loadBilling = function (ApiService $api): array {
        $proposals = [];
        $projectsBilling = [];
        $error = null;

        try {
            $response = $api->get('proposals/mine');
            if ($response->successful() && is_array($response->json())) {
                $proposals = $response->json();

                foreach ($proposals as $p) {
                    $pId = $p['id'] ?? null;
                    if (!$pId) continue;
                    $detailRes = $api->get("proposals/{$pId}");
                    if ($detailRes->successful() && is_array($detailRes->json())) {
                        $detail = $detailRes->json();
                        $payments = $detail['payments'] ?? [];
                        if (!empty($payments)) {
                            $totalBudget = (float) ($detail['total_amount'] ?? $detail['estimated_budget'] ?? 0);
                            $totalPaid = 0;
                            $termins = [];
                            $hasOpen = false;
                            $hasOpenMaintenance = false;
                            $hasOpenProject = false;

                            foreach ($payments as $pay) {
                                $amount = (float) ($pay['amount'] ?? 0);
                                $status = strtoupper($pay['status'] ?? 'PENDING');
                                $isMaintenance = ($pay['payment_type'] ?? '') === 'maintenance';
                                $proofUrl = $pay['proof_url'] ?? null;
                                if ($proofUrl && !str_starts_with($proofUrl, 'http')) {
                                    $baseUrl = rtrim(config('services.backend_api.url') ?? env('BACKEND_API_URL', 'http://127.0.0.1:8000/api/v1'), '/');
                                    $baseHost = preg_replace('#/api/v1$#', '', $baseUrl);
                                    $proofUrl = $baseHost . '/storage/' . ltrim($proofUrl, '/');
                                }

                                $statusLabel = 'MENUNGGU PEMBAYARAN';
                                $statusType = 'unpaid';
                                $canUpload = true;
                                $canDownload = false;

                                if ($status === 'VERIFIED') {
                                    $statusLabel = 'VERIFIED (Lunas)';
                                    $statusType = 'verified';
                                    $canUpload = false;
                                    $canDownload = true;
                                    if (!$isMaintenance) {
                                        $totalPaid += $amount;
                                    }
                                } elseif ($status === 'REJECTED') {
                                    $statusLabel = 'DITOLAK (Upload Ulang)';
                                    $statusType = 'rejected';
                                    $canUpload = true;
                                    $hasOpen = true;
                                    if ($isMaintenance) {
                                        $hasOpenMaintenance = true;
                                    } else {
                                        $hasOpenProject = true;
                                    }
                                } elseif ($proofUrl) {
                                    $statusLabel = 'DALAM VERIFIKASI';
                                    $statusType = 'verifying';
                                    $canUpload = false;
                                } else {
                                    $hasOpen = true;
                                    if ($isMaintenance) {
                                        $hasOpenMaintenance = true;
                                    } else {
                                        $hasOpenProject = true;
                                    }
                                }

                                $rawDueDate = $pay['due_date'] ?? $pay['payment_date'] ?? null;
                                $formattedDueDate = !empty($rawDueDate)
                                    ? \Carbon\Carbon::parse($rawDueDate)->translatedFormat('d M Y')
                                    : null;
                                $invoiceNumber = !empty($pay['invoice_number'])
                                    ? $pay['invoice_number']
                                    : (!empty($pay['id'])
                                        ? sprintf('INV/%s/%06d', !empty($pay['created_at']) ? \Carbon\Carbon::parse($pay['created_at'])->format('Y') : date('Y'), (int) $pay['id'])
                                        : 'Belum diterbitkan');
                                $isMaintenance = ($pay['payment_type'] ?? '') === 'maintenance';
                                $termName = $isMaintenance ? 'Layanan Maintenance' : ('Termin ' . ($pay['term_sequence'] ?? 1));
                                $termDesc = $pay['description']
                                    ?? ((!empty($pay['verification_notes']) && $status !== 'REJECTED')
                                        ? $pay['verification_notes']
                                        : 'Pembayaran ' . $termName);

                                $termins[] = [
                                    'id'                 => $pay['id'],
                                    'proposal_id'        => $pId,
                                    'invoice_number'     => $invoiceNumber,
                                    'term_sequence'      => $pay['term_sequence'] ?? 1,
                                    'name'               => $termName,
                                    'desc'               => $termDesc,
                                    'payment_type'       => $pay['payment_type'] ?? 'project',
                                    'is_maintenance'     => $isMaintenance,
                                    'project_title'      => $detail['title'] ?? 'Proyek Tanpa Judul',
                                    'project_code'       => $detail['project_code'] ?? ($detail['proposal_code'] ?? ('PRJ-' . $pId)),
                                    'spk_number'         => $detail['spk_number'] ?? '-',
                                    'nominal'            => 'Rp ' . number_format($amount, 0, ',', '.'),
                                    'amount'             => $amount,
                                    'channel'            => match ($pay['payment_method'] ?? null) {
                                        'BANK_TRANSFER' => 'Transfer Bank',
                                        'VIRTUAL_ACCOUNT' => 'Virtual Account',
                                        'CASH' => 'Tunai',
                                        'OTHER' => 'E-Wallet / Lainnya',
                                        default => 'Belum dipilih',
                                    },
                                    'channel_icon'       => 'bank',
                                    'status'             => $statusType,
                                    'raw_status'         => $status,
                                    'status_label'       => $statusLabel,
                                    'due_date'           => $formattedDueDate,
                                    'can_download'       => $canDownload,
                                    'can_upload'         => $canUpload,
                                    'proof_url'          => $proofUrl,
                                    'verification_notes' => $pay['verification_notes'] ?? null,
                                ];
                            }

                            $paidPercent = $totalBudget > 0 ? round(($totalPaid / $totalBudget) * 100, 1) : 0;

                            $projectsBilling[] = [
                                'id'                   => $detail['proposal_code'] ?? ('PRJ-' . $pId),
                                'proposal_id'          => $pId,
                                'spk'                  => $detail['spk_number'] ?? 'SPK belum tercatat',
                                'spk_status'           => $detail['status']['name'] ?? $detail['status'] ?? 'Aktif',
                                'spk_status_type'      => 'success',
                                'title'                => $detail['title'] ?? 'Proyek Tanpa Judul',
                                'period'               => 'Target: ' . (!empty($detail['desired_deadline']) ? \Carbon\Carbon::parse($detail['desired_deadline'])->translatedFormat('d M Y') : '-'),
                                'total_value'          => 'Rp ' . number_format($totalBudget, 0, ',', '.'),
                                'paid_percent'         => $paidPercent,
                                'paid_status'          => $paidPercent . '% Terbayar',
                                'has_open'             => $hasOpen,
                                'has_open_maintenance' => $hasOpenMaintenance,
                                'has_open_project'     => $hasOpenProject,
                                'termins'              => $termins,
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
        }

        return [$proposals, $projectsBilling, $error];
    };

    // Klien: Ajukan Proyek Baru & Projek Saya
    Route::middleware('auth.api:klien')->prefix('klien')->name('klien.')->group(function () use ($storeProposal, $loadBilling) {
        Route::get('/bast', function (ApiService $api) {
            try {
                $response = $api->get('proposals/mine');
                if (!$response->successful()) {
                    return view('klien.bast.index', [
                        'user' => session('user'),
                        'projects' => [],
                        'loadError' => $response->json('message') ?? 'Gagal memuat daftar proyek.',
                    ]);
                }

                $projects = $response->json('data') ?? $response->json();
                if (!is_array($projects)) {
                    return view('klien.bast.index', [
                        'user' => session('user'),
                        'projects' => [],
                        'loadError' => 'Format daftar proyek dari server tidak valid.',
                    ]);
                }

                return view('klien.bast.index', [
                    'user' => session('user'),
                    'projects' => $projects,
                    'loadError' => null,
                ]);
            } catch (\Throwable $e) {
                return view('klien.bast.index', [
                    'user' => session('user'),
                    'projects' => [],
                    'loadError' => 'Tidak dapat memuat daftar proyek: ' . $e->getMessage(),
                ]);
            }
        })->name('bast');

        Route::get('/bast/{id}', function (int $id, ApiService $api) {
            try {
                $response = $api->get("proposals/{$id}/handover");
            } catch (\Throwable $e) {
                return redirect()
                    ->route('klien.bast')
                    ->with('error', 'Tidak dapat memuat detail BAST: ' . $e->getMessage());
            }

            if (!$response->successful()) {
                return redirect()
                    ->route('klien.bast')
                    ->with('error', $response->json('message') ?? 'Gagal memuat detail BAST.');
            }

            $handover = $response->json('data');
            if (!is_array($handover) || !is_array($handover['proposal'] ?? null)) {
                return redirect()
                    ->route('klien.bast')
                    ->with('error', 'Format detail BAST dari server tidak valid.');
            }

            return view('klien.bast.show', [
                'user' => session('user'),
                'handover' => $handover,
                'error' => session('error'),
                'success' => session('success'),
            ]);
        })->whereNumber('id')->name('bast.show');

        Route::post('/bast/{id}/approve', function (int $id, Request $request, ApiService $api) {
            $validated = $request->validate([
                'client_signoff_name' => ['required', 'string', 'max:255'],
                'client_approved' => ['required', 'accepted'],
            ]);

            try {
                $response = $api->patch("proposals/{$id}/handover/approve", [
                    'client_signoff_name' => $validated['client_signoff_name'],
                    'client_approved' => true,
                ]);
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Tidak dapat mengirim persetujuan BAST: ' . $e->getMessage());
            }

            if (!$response->successful()) {
                return back()
                    ->withInput()
                    ->with('error', $response->json('message') ?? 'Persetujuan BAST gagal.');
            }

            return redirect()
                ->route('klien.bast.show', $id)
                ->with('success', $response->json('message') ?? 'BAST berhasil disetujui.');
        })->whereNumber('id')->name('bast.approve');

        Route::get('/projek-saya', function (ApiService $api) {
            $proposals = [];
            $error = null;
            $handoverProjects = [];
            $handoverProjectsAvailable = false;

            try {
                $response = $api->get('proposals/mine');
                $payload = $response->successful()
                    ? (is_array($response->json('data')) ? $response->json('data') : $response->json())
                    : null;
                if (is_array($payload)) {
                    $proposals = $payload;
                    $handoverProjectsAvailable = true;
                    $handoverProjects = collect($payload)
                        ->filter(fn ($proposal) => is_array($proposal) && !empty($proposal['bast_prepared']) && empty($proposal['client_approved']))
                        ->values()
                        ->all();
                } else {
                    $error = $response->json('message') ?? 'Data projek tidak dapat dimuat.';
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
                $handoverProjects = [];
            }

            return view('klien.projek-saya', [
                'user'                      => session('user'),
                'proposals'                 => $proposals,
                'handoverProjects'          => $handoverProjects,
                'handoverProjectsAvailable' => $handoverProjectsAvailable,
                'error'                     => $error,
            ]);
        })->name('projek-saya');
        Route::get('/proyek-saya', fn () => redirect()->route('klien.projek-saya'));

        Route::get('/projek-saya/{proposal}', function (ApiService $api, int $proposal) {
            $detail = [];
            $error = null;

            try {
                $response = $api->get("proposals/{$proposal}");
                if ($response->successful() && is_array($response->json())) {
                    $detail = $response->json();
                } else {
                    $error = $response->json('message') ?? 'Projek tidak ditemukan.';
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
            }

            return view('klien.projek-detail', [
                'user'       => session('user'),
                'proposal'   => $detail,
                'company'    => $api->getCompanyProfile(),
                'categories' => $api->getCategories(),
                'error'      => $error,
            ]);
        })->whereNumber('proposal')->name('projek-saya.detail');
        Route::get('/proyek-saya/{proposal}', fn ($proposal) => redirect()->route('klien.projek-saya.detail', $proposal));

        Route::get('/projek-saya/{proposal}/bast', function (ApiService $api, int $proposal) {
            try {
                $response = $api->get('proposals/mine');
                $payload = $response->successful()
                    ? (is_array($response->json('data')) ? $response->json('data') : $response->json())
                    : null;
                $project = is_array($payload)
                    ? collect($payload)->firstWhere('id', $proposal)
                    : null;

                if (!is_array($project)) {
                    return redirect()->route('klien.projek-saya')->with('error', 'BAST proyek tidak ditemukan.');
                }

                if (empty($project['bast_prepared']) || !empty($project['client_approved'])) {
                    return redirect()->route('klien.projek-saya')->with('error', 'BAST ini tidak sedang menunggu persetujuan.');
                }

                if (empty($project['handover_deliverables'])) {
                    return redirect()->route('klien.projek-saya')->with('error', 'Daftar hasil/barang BAST belum dilengkapi Pengurus TeFa.');
                }

                return view('klien.bast-review', [
                    'user' => session('user'),
                    'project' => $project,
                ]);
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::error('Gagal memuat tinjauan BAST klien: ' . $exception->getMessage(), [
                    'proposal_id' => $proposal,
                ]);

                return redirect()->route('klien.projek-saya')->with('error', 'Detail BAST tidak dapat dimuat. Silakan coba kembali.');
            }
        })->whereNumber('proposal')->name('projek-saya.bast.review');

        Route::post('/projek-saya/{proposal}/revisi', function (Request $request, ApiService $api, int $proposal) {
            $request->merge([
                'modul' => json_decode($request->input('modul', '[]'), true) ?: [],
            ]);

            $validated = $request->validate([
                'judul_proyek'      => ['required', 'string', 'max:255'],
                'kategori_id'       => ['required', 'integer', 'min:1'],
                'delivery_type'     => ['nullable', 'in:source_code,tefa_managed'],
                'has_warranty'      => ['nullable', 'in:0,1,true,false'],
                'warranty_months'   => ['nullable', 'integer', 'min:1'],
                'ringkasan'         => ['required', 'string', 'min:100', 'max:500'],
                'target_pengguna'   => ['required', 'string', 'max:1000'],
                'deadline'          => ['required', 'date'],
                'anggaran'          => ['required', 'numeric', 'min:0'],
                'modul'             => ['nullable', 'array'],
                'modul.*.judul'     => ['required_with:modul', 'string', 'max:255'],
                'modul.*.deskripsi' => ['nullable', 'string', 'max:1000'],
            ], [
                'ringkasan.min'     => 'Ringkasan kebutuhan proyek minimal 100 karakter.',
                'anggaran.required' => 'Estimasi anggaran/harga wajib diisi.',
            ]);

            $modules = collect($validated['modul'] ?? [])
                ->filter(fn ($m) => !empty($m['judul']))
                ->map(fn ($m) => [
                    'title'       => $m['judul'],
                    'description' => $m['deskripsi'] ?? null,
                ])
                ->values()
                ->all();

            $deliveryType = $validated['delivery_type'] ?? 'source_code';
            $hasWarranty = true;
            $warrantyMonths = max(1, (int) ($validated['warranty_months'] ?? 3));

            $payload = [
                'title'            => $validated['judul_proyek'],
                'background'       => $validated['ringkasan'],
                'target_audience'  => $validated['target_pengguna'],
                'category_id'      => (int) $validated['kategori_id'],
                'delivery_type'    => $deliveryType,
                'has_warranty'     => $hasWarranty,
                'warranty_months'  => $warrantyMonths,
                'desired_deadline' => $validated['deadline'],
                'estimated_budget' => (float) $validated['anggaran'],
                'modules'          => $modules,
            ];

            try {
                $response = $api->put("proposals/{$proposal}", $payload);

                if ($response->successful()) {
                    return redirect()
                        ->route('klien.projek-saya')
                        ->with('success', 'Data perbaikan proyek berhasil dikirim kembali ke Pengurus TEFA untuk dicek.');
                }

                return back()
                    ->withInput()
                    ->with('error', 'Gagal menyimpan revisi: ' . ($response->json('message') ?? 'Terjadi kesalahan pada server.'));
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Tidak dapat terhubung ke server: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->name('projek-saya.revisi.store');

        Route::get('/tagihan', function (ApiService $api) use ($loadBilling) {
            [$proposals, $projectsBilling, $error] = $loadBilling($api);

            return view('klien.tagihan', [
                'user'            => session('user'),
                'projectsBilling' => $projectsBilling,
                'proposals'       => $proposals,
                'error'           => $error,
            ]);
        })->name('tagihan');

        Route::get('/tagihan/{proposal}/payments/{payment}/unggah', function (ApiService $api, int $proposal, int $payment) {
            $proposalDetail = [];
            $targetPayment = null;
            $error = null;

            try {
                $response = $api->get("proposals/{$proposal}");
                if ($response->successful() && is_array($response->json())) {
                    $proposalDetail = $response->json();
                    $payments = $proposalDetail['payments'] ?? [];
                    foreach ($payments as $pay) {
                        if ((int) ($pay['id'] ?? 0) === $payment) {
                            $targetPayment = $pay;
                            break;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
            }

            // Fallback default jika sedang testing dengan mockup project
            if (!$targetPayment) {
                $targetPayment = [
                    'id'            => $payment,
                    'proposal_id'   => $proposal,
                    'term_sequence' => 2,
                    'amount'        => 30000000,
                    'due_date'      => '2026-10-15',
                    'status'        => 'PENDING',
                ];
                $proposalDetail = [
                    'id'               => $proposal,
                    'proposal_code'    => 'PRJ-2026/009',
                    'title'            => 'Aplikasi Mobile Portal Layanan Nasabah & Pembayaran Digital',
                    'estimated_budget' => 60000000,
                ];
            }

            return view('klien.unggah-bukti', [
                'user'     => session('user'),
                'proposal' => $proposalDetail,
                'payment'  => $targetPayment,
                'error'    => $error,
            ]);
        })->whereNumber('proposal')->whereNumber('payment')->name('tagihan.unggah');

        Route::post('/tagihan/{proposal}/payments/{payment}/proof', function (Request $request, ApiService $api, int $proposal, int $payment) {
            $validated = $request->validate([
                'payment_date'       => ['required', 'date'],
                'payment_method'     => ['required', 'in:BANK_TRANSFER,VIRTUAL_ACCOUNT,CASH,OTHER'],
                'transfer_reference' => ['nullable', 'string', 'max:255'],
                'proof'              => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            ], [
                'proof.required' => 'Berkas bukti pembayaran wajib diunggah.',
                'proof.max'      => 'Ukuran berkas maksimal 5MB.',
                'proof.mimes'    => 'Format berkas harus JPG, PNG, atau PDF.',
            ]);

            try {
                $response = $api->uploadPaymentProof($proposal, $payment, [
                    'payment_date'       => $validated['payment_date'],
                    'payment_method'     => $validated['payment_method'],
                    'transfer_reference' => $validated['transfer_reference'] ?? null,
                ], $request->file('proof'));

                if ($response->successful()) {
                    return redirect()
                        ->route('klien.tagihan')
                        ->with('success', 'Bukti pembayaran berhasil diunggah! Pembayaran sedang dalam antrean verifikasi Pengurus TEFA.');
                }

                return back()
                    ->withInput()
                    ->with('error', 'Gagal mengunggah bukti: ' . ($response->json('message') ?? 'Terjadi kesalahan pada backend.'));
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Gagal terhubung ke backend: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->whereNumber('payment')->name('tagihan.upload-proof');

        Route::get('/tagihan/payments/{payment}/invoice', function (Request $request, ApiService $api, int $payment) {
            try {
                $channel = $request->query('channel');
                $query = $channel ? ('?channel=' . urlencode((string) $channel)) : '';
                $response = $api->get("payments/{$payment}/invoice{$query}");
                if ($response->failed()) {
                    return back()->with('error', $response->json('message') ?? 'Invoice belum dapat diunduh.');
                }

                return response($response->body(), 200, [
                    'Content-Type' => $response->header('Content-Type') ?? 'application/pdf',
                    'Content-Disposition' => $response->header('Content-Disposition') ?? 'attachment; filename="invoice-' . $payment . '.pdf"',
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Gagal mengunduh invoice: ' . $e->getMessage());
                return back()->with('error', 'Invoice tidak dapat diunduh dari server TEVARA.');
            }
        })->whereNumber('payment')->name('tagihan.invoice');

        Route::get('/tagihan-pembayaran', fn () => redirect()->route('klien.tagihan'));
        Route::get('/pembayaran', fn () => redirect()->route('klien.tagihan'));

        Route::patch('/projek-saya/{proposal}/bast/approve', function (Request $request, ApiService $api, int $proposal) {
            if ($request->hasAny(['nomor_bast', 'bast_number', 'tanggal_serah_terima', 'handover_date'])) {
                return back()->withInput()->withErrors([
                    'nomor_bast' => 'Nomor BAST dan tanggal serah terima tidak boleh dimanipulasi manual. Nilai ini digenerate murni di sisi server.',
                ]);
            }

            $validated = $request->validate([
                'client_signoff_name' => ['required', 'string', 'max:255'],
                'client_approved' => ['required', 'accepted'],
                'maintenance_warranty_months' => ['nullable', 'integer', 'min:0'],
            ]);

            try {
                $response = $api->patch("proposals/{$proposal}/handover/approve", $validated);

                if ($response->successful()) {
                    return redirect()->route('klien.projek-saya')->with('success', 'Persetujuan BAST berhasil dikirim.');
                }

                $errors = $response->json('errors');
                return back()->withInput()->withErrors(is_array($errors) ? $errors : [
                    'handover' => $response->json('message') ?? 'Pengiriman BAST gagal.',
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Gagal mengirim persetujuan BAST: ' . $e->getMessage(), [
                    'proposal_id' => $proposal,
                ]);
                return back()->withInput()->withErrors([
                    'handover' => 'Tidak dapat menghubungi backend BAST. Silakan coba kembali.',
                ]);
            }
        })->whereNumber('proposal')->name('projek-saya.bast.approve');

        Route::get('/projek-saya/{proposal}/bast/pdf', function (ApiService $api, int $proposal) {
            try {
                $response = $api->get("proposals/{$proposal}/handover/bast");
                if ($response->failed()) {
                    return back()->with('error', $response->json('message') ?? 'PDF BAST belum tersedia.');
                }

                return response($response->body(), 200, [
                    'Content-Type' => $response->header('Content-Type') ?? 'application/pdf',
                    'Content-Disposition' => $response->header('Content-Disposition') ?? 'attachment; filename="bast-' . $proposal . '.pdf"',
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Gagal mengunduh PDF BAST klien: ' . $e->getMessage(), [
                    'proposal_id' => $proposal,
                ]);
                return back()->with('error', 'PDF BAST tidak dapat diunduh dari server TEVARA.');
            }
        })->whereNumber('proposal')->name('projek-saya.bast.pdf');

        Route::get('/projek-saya/{proposal}/bast/documentation', function (ApiService $api, int $proposal) {
            try {
                $response = $api->get("proposals/{$proposal}/handover/documentation");
                if (!$response->successful()) {
                    return back()->with('error', $response->json('message') ?? 'Dokumentasi BAST tidak dapat diunduh.');
                }

                return response($response->body(), 200, [
                    'Content-Type' => $response->header('Content-Type') ?? 'application/octet-stream',
                    'Content-Disposition' => $response->header('Content-Disposition') ?? 'attachment; filename="dokumentasi-api"',
                ]);
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::error('Gagal mengunduh dokumentasi BAST: ' . $exception->getMessage(), [
                    'proposal_id' => $proposal,
                ]);
                return back()->with('error', 'Dokumentasi BAST tidak dapat diunduh dari server.');
            }
        })->whereNumber('proposal')->name('projek-saya.bast.documentation');

        Route::get('/projek-saya/{proposal}/bast/user-manual', function (ApiService $api, int $proposal) {
            try {
                $response = $api->get("proposals/{$proposal}/handover/user-manual");
                if (!$response->successful()) {
                    return back()->with('error', $response->json('message') ?? 'Panduan pengguna tidak dapat diunduh.');
                }

                return response($response->body(), 200, [
                    'Content-Type' => $response->header('Content-Type') ?? 'application/octet-stream',
                    'Content-Disposition' => $response->header('Content-Disposition') ?? 'attachment; filename="panduan-pengguna"',
                ]);
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::error('Gagal mengunduh panduan pengguna BAST: ' . $exception->getMessage(), [
                    'proposal_id' => $proposal,
                ]);
                return back()->with('error', 'Panduan pengguna tidak dapat diunduh dari server.');
            }
        })->whereNumber('proposal')->name('projek-saya.bast.user-manual');

        Route::get('/ajukan-proyek', function (ApiService $api) {
            return view('klien.ajukan-proyek', [
                'user'       => session('user'),
                'company'    => $api->getCompanyProfile(),
                'categories' => $api->getCategories(),
            ]);
        })->name('ajukan-proyek');

        Route::post('/ajukan-proyek', fn (Request $request, ApiService $api) => $storeProposal($request, $api, 'klien.ajukan-proyek'))
            ->name('ajukan-proyek.store');

        // ==========================================
        // UAT & Sign-Off Klien
        // ==========================================
        Route::get('/projek-saya/{proposal}/uat', function (ApiService $api, int $proposal) {
            $proposalResponse = $api->get("proposals/{$proposal}");
            if (! $proposalResponse->successful() || ! is_array($proposalResponse->json())) {
                abort($proposalResponse->status() ?: 502, $proposalResponse->json('message') ?? 'Data proyek tidak dapat dimuat.');
            }
            $detail = $proposalResponse->json();
            $uat = null;
            $error = null;

            try {
                $uatRes = $api->getUat($proposal);
                if ($uatRes->successful()) {
                    $uat = $uatRes->json('data');
                } elseif ($uatRes->status() !== 404) {
                    abort($uatRes->status(), $uatRes->json('message') ?? 'Data UAT tidak dapat dimuat.');
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server: ' . $e->getMessage();
            }

            return view('klien.uat-workflow', [
                'user'     => session('user'),
                'proposal' => $detail,
                'uat'      => $uat,
                'error'    => $error,
            ]);
        })->whereNumber('proposal')->name('projek-saya.uat');

        Route::post('/projek-saya/{proposal}/uat', function (Request $request, ApiService $api, int $proposal) {
            $validated = $request->validate([
                'client_notes' => ['nullable', 'string'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.module_name' => ['nullable', 'string', 'max:255'],
                'items.*.test_category' => ['nullable', 'string', 'max:100'],
                'items.*.feature_name' => ['required', 'string', 'max:255'],
                'items.*.test_scenario' => ['nullable', 'string'],
                'items.*.result' => ['required', 'in:PASSED,FAILED,BUG_FOUND'],
                'items.*.bug_severity' => ['nullable', 'in:MINOR,MAJOR,CRITICAL'],
                'items.*.bug_description' => ['nullable', 'string'],
                'items.*.client_feedback' => ['nullable', 'string'],
            ]);

            try {
                $response = $api->createUat($proposal, $validated);
                if ($response->successful()) {
                    return redirect()
                        ->route('klien.projek-saya.uat', $proposal)
                        ->with('success', 'Hasil pengujian berhasil dikirim kepada Pengurus TeFa untuk diperiksa.');
                }

                return back()->withInput()->with('error', $response->json('message') ?? 'Hasil UAT tidak dapat dikirim.');
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Tidak dapat terhubung ke server backend: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->name('projek-saya.uat.submit');

        Route::post('/projek-saya/{proposal}/uat/signoff', function (Request $request, ApiService $api, int $proposal) {
            $validated = $request->validate([
                'client_signoff_name' => ['required', 'string', 'max:255'],
            ], [
                'client_signoff_name.required' => 'Nama lengkap penandatangan wajib diisi.',
            ]);

            try {
                $response = $api->signoffUat($proposal, $validated);

                if ($response->successful()) {
                    return redirect()
                        ->route('klien.projek-saya.uat', $proposal)
                        ->with('success', 'Persetujuan UAT berhasil dicatat. Proyek dapat dilanjutkan ke tahap BAST.');
                }

                return back()
                    ->withInput()
                    ->with('error', 'Gagal melakukan sign-off: ' . ($response->json('message') ?? 'Terjadi kesalahan backend.'));
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Tidak dapat terhubung ke server backend: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->name('projek-saya.uat.signoff');

        // ── Maintenance & Garansi (Klien) ─────────────────────────
        Route::get('/maintenance', function (ApiService $api) {
            $projects = [];
            $error    = null;

            try {
                $response = $api->get('proposals/mine');
                $payload  = $response->successful()
                    ? (is_array($response->json('data')) ? $response->json('data') : $response->json())
                    : null;

                if (is_array($payload)) {
                    // Filter only handed-over (client_approved) proposals
                    $projects = collect($payload)
                        ->filter(fn ($p) => is_array($p) && !empty($p['client_approved']))
                        ->map(function ($p) {
                            // warranty_end_date ada di nested $p['warranty']['warranty_end_date']
                            $warranty    = is_array($p['warranty'] ?? null) ? $p['warranty'] : [];
                            $endDateStr  = $warranty['warranty_end_date'] ?? $p['warranty_end_date'] ?? null;
                            $hasWarranty = $warranty['has_warranty'] ?? $p['has_warranty'] ?? false;
                            $warrantyEnd = !empty($endDateStr) ? \Carbon\Carbon::parse($endDateStr) : null;
                            $isWarrantyActive = $hasWarranty && $warrantyEnd && $warrantyEnd->gte(\Carbon\Carbon::today());

                            // Check active paid maintenance
                            $maintenances = collect($p['project_maintenances'] ?? []);
                            $activeMaintenance = $maintenances
                                ->where('type', 'maintenance')
                                ->where('status', 'active')
                                ->filter(fn ($m) => !empty($m['end_date']) && \Carbon\Carbon::parse($m['end_date'])->gte(\Carbon\Carbon::today()))
                                ->sortByDesc('end_date')
                                ->first();

                            $hasActiveMaintenance = !empty($activeMaintenance);
                            $effectiveEnd = $hasActiveMaintenance ? \Carbon\Carbon::parse($activeMaintenance['end_date']) : $warrantyEnd;
                            $isActive = $isWarrantyActive || $hasActiveMaintenance;
                            $daysLeft = $effectiveEnd ? (int) \Carbon\Carbon::today()->diffInDays($effectiveEnd, false) : null;

                            return array_merge($p, [
                                'has_warranty'           => $hasWarranty,
                                'warranty_end_date'      => $endDateStr,
                                'warranty_start_date'    => $warranty['warranty_start_date'] ?? $p['warranty_start_date'] ?? null,
                                'warranty_months'        => $warranty['warranty_months'] ?? $p['warranty_months'] ?? null,
                                'warranty_active'        => $isActive,
                                'warranty_days_left'     => $daysLeft,
                                'has_active_maintenance' => $hasActiveMaintenance,
                                'maintenance_end_date'   => $activeMaintenance['end_date'] ?? null,
                            ]);
                        })
                        ->values()
                        ->all();
                } else {
                    $error = $response->json('message') ?? 'Data tidak dapat dimuat.';
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
            }

            return view('klien.maintenance', [
                'user'     => session('user'),
                'projects' => $projects,
                'error'    => $error,
            ]);
        })->name('maintenance');

        Route::get('/maintenance/{proposal}', function (ApiService $api, int $proposal) {
            $proposalData = [];
            $tickets      = [];
            $extReqs      = [];
            $error        = null;

            try {
                $pRes = $api->get("proposals/{$proposal}");
                if ($pRes->successful() && is_array($pRes->json())) {
                    $proposalData = $pRes->json();
                } else {
                    $error = $pRes->json('message') ?? 'Data proyek tidak ditemukan.';
                }

                if (!$error) {
                    $tRes = $api->get("proposals/{$proposal}/maintenance-tickets");
                    if ($tRes->successful()) {
                        $raw = $tRes->json('data') ?? $tRes->json();
                        $tickets = is_array($raw) ? array_values(array_filter($raw, 'is_array')) : [];
                    }

                    $eRes = $api->get("proposals/{$proposal}/warranty-extensions");
                    if ($eRes->successful()) {
                        $raw = $eRes->json('data') ?? $eRes->json();
                        $extReqs = is_array($raw) ? array_values(array_filter($raw, 'is_array')) : [];
                    }
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
            }

            return view('klien.maintenance-detail', [
                'user'     => session('user'),
                'proposal' => $proposalData,
                'tickets'  => $tickets,
                'extReqs'  => $extReqs,
                'error'    => $error,
            ]);
        })->whereNumber('proposal')->name('maintenance.detail');

        Route::post('/maintenance/{proposal}/report', function (Request $request, ApiService $api, int $proposal) {
            $validated = $request->validate([
                'title'       => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'priority'    => ['nullable', 'in:low,medium,high'],
                'image'       => ['nullable', 'image', 'max:10240'],
            ]);

            try {
                $payload = [
                    'title'       => $validated['title'],
                    'description' => $validated['description'] ?? null,
                    'priority'    => $validated['priority'] ?? 'medium',
                ];

                if ($request->hasFile('image')) {
                    $response = $api->sendWithFile(
                        "proposals/{$proposal}/maintenance-tickets",
                        'image',
                        $request->file('image'),
                        $payload
                    );
                } else {
                    $response = $api->post("proposals/{$proposal}/maintenance-tickets", $payload);
                }

                if ($response->successful()) {
                    return redirect()
                        ->route('klien.maintenance.detail', $proposal)
                        ->with('success', 'Laporan error berhasil dikirim. Tim TEVARA akan segera menangani.');
                }

                return back()->withInput()->with('error', $response->json('message') ?? 'Gagal mengirim laporan.');
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Tidak dapat terhubung ke server: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->name('maintenance.report');

        Route::post('/maintenance/{proposal}/extend', function (Request $request, ApiService $api, int $proposal) {
            $validated = $request->validate([
                'type'   => ['required', 'in:warranty,maintenance'],
                'months' => ['required', 'integer', 'in:1,3,6,12,24,36'],
                'notes'  => ['nullable', 'string', 'max:1000'],
            ]);

            try {
                $response = $api->post("proposals/{$proposal}/warranty-extensions", $validated);

                if ($response->successful()) {
                    return redirect()
                        ->route('klien.maintenance.detail', $proposal)
                        ->with('success', 'Permintaan perpanjangan berhasil diajukan. Menunggu persetujuan Pengurus TeFa.');
                }

                return back()->withInput()->with('error', $response->json('message') ?? 'Gagal mengajukan perpanjangan.');
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Tidak dapat terhubung ke server: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->name('maintenance.extend');

        Route::patch('/maintenance/{proposal}/extensions/{extReq}/respond', function (Request $request, ApiService $api, int $proposal, int $extReq) {
            $validated = $request->validate([
                'response' => ['required', 'in:accept,reject'],
            ]);

            try {
                $res = $api->patch("proposals/{$proposal}/warranty-extensions/{$extReq}/respond", $validated);

                if ($res->successful()) {
                    if ($validated['response'] === 'accept') {
                        return redirect()
                            ->route('klien.tagihan')
                            ->with('success', 'Penawaran disetujui! Tagihan maintenance telah terbit. Silakan lakukan pembayaran.');
                    }
                    return redirect()
                        ->route('klien.maintenance.detail', $proposal)
                        ->with('success', 'Penawaran harga maintenance telah ditolak.');
                }

                return back()->with('error', $res->json('message') ?? 'Gagal merespons penawaran.');
            } catch (\Throwable $e) {
                return back()->with('error', 'Tidak dapat terhubung ke server: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->whereNumber('extReq')->name('maintenance.respond');
    });

    // Role-specific dashboard direct URLs
    Route::get('/dashboard-pengurus', function () {
        $user = session('user');
        if (!ApiService::hasRole($user, 'pengurus_tefa')) {
            abort(403, 'Akses ditolak: Anda bukan Pengurus TeFa.');
        }
        session(['active_role' => 'pengurus_tefa']);
        return redirect()->route('dashboard');
    })->name('dashboard-pengurus');

    Route::get('/dashboard-pm', function () {
        $user = session('user');
        if (!ApiService::hasRole($user, 'pm')) {
            abort(403, 'Akses ditolak: Anda bukan Project Manager (PM).');
        }
        session(['active_role' => 'pm']);
        return redirect()->route('dashboard');
    })->name('dashboard-pm');

    Route::get('/dashboard-mahasiswa', function () {
        $user = session('user');
        if (!ApiService::hasRole($user, 'mahasiswa')) {
            abort(403, 'Akses ditolak: Anda bukan Mahasiswa.');
        }
        session(['active_role' => 'mahasiswa']);
        return redirect()->route('dashboard');
    })->name('dashboard-mahasiswa');

    Route::get('/mahasiswa/project', function (ApiService $api) {
        $dashboardData = [];
        try {
            $response = $api->get('mahasiswa/dashboard');
            if ($response->successful()) {
                $dashboardData = $response->json('data', []);
            }
        } catch (\Throwable) {
            $dashboardData = [];
        }

        return view('mahasiswa.project', [
            'user'      => session('user'),
            'dashboard' => $dashboardData,
        ]);
    })->name('mahasiswa.project');

    Route::get('/mahasiswa/task', function (Request $request, ApiService $api) {
        $projects = [];

        try {
            $response = $api->get('mahasiswa/dashboard');
            if ($response->successful()) {
                $projects = $response->json('data.projects', []);
            }
        } catch (\Throwable) {
            $projects = [];
        }

        $projects = is_array($projects) ? $projects : [];

        // Proposal yang dipilih lewat ?proposal_id=..., fallback ke proyek pertama.
        $requestedId = $request->query('proposal_id')
            ?? $request->query('proposal')
            ?? $request->query('project');
        $proposalId = null;
        $selectedProject = null;

        foreach ($projects as $project) {
            if ($requestedId !== null && (string) ($project['id'] ?? '') === (string) $requestedId) {
                $proposalId = $project['id'];
                $selectedProject = $project;
                break;
            }
        }

        // Jika user belum memilih proyek (tidak ada param proposal_id / proposal / project),
        // tampilkan halaman daftar proyek overview terlebih dahulu
        if ($requestedId === null) {
            return view('mahasiswa.task-projects', [
                'user'     => session('user'),
                'projects' => $projects,
            ]);
        }

        if ($proposalId === null && !empty($projects)) {
            $proposalId = $projects[0]['id'] ?? null;
            $selectedProject = $projects[0] ?? null;
        }

        $tasks = [];
        $summary = [];
        $error = null;

        if ($proposalId !== null) {
            try {
                $tasksRes = $api->get('proposals/' . rawurlencode((string) $proposalId) . '/tasks');
                if ($tasksRes->successful()) {
                    $tasks = $tasksRes->json('data', []);
                } else {
                    $error = $tasksRes->json('message') ?? 'Gagal mengambil data task mahasiswa.';
                }

                $summaryRes = $api->get('proposals/' . rawurlencode((string) $proposalId) . '/tasks/summary');
                if ($summaryRes->successful()) {
                    $summary = $summaryRes->json('data', []);
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
            }
        }

        return view('mahasiswa.task', [
            'user'            => session('user'),
            'projects'        => $projects,
            'selectedProject' => $selectedProject,
            'proposalId'      => $proposalId,
            'tasks'           => is_array($tasks) ? $tasks : [],
            'summary'         => is_array($summary) ? $summary : [],
            'error'           => $error,
        ]);
    })->name('mahasiswa.task');

    Route::post('/mahasiswa/proposals/{proposal}/tasks/{task}/progress', function (Request $request, ApiService $api, string $proposal, string $task) {
        $validated = $request->validate([
            'progress_percentage'   => ['required', 'numeric', 'min:0', 'max:100'],
            'status'                => ['required', 'string', 'in:TODO,IN_PROGRESS,TESTING,DONE'],
            'achieved_summary'      => ['required', 'string'],
            'blockers_summary'      => ['nullable', 'string'],
            'report_attachment_url' => ['nullable', 'string', 'max:500'],
            'report_attachment'     => ['nullable', 'file', 'max:10240'],
        ]);

        $progress = (float) $validated['progress_percentage'];
        $status = $validated['status'];

        if ($status === 'TODO' || $progress <= 0) {
            $status = 'TODO';
            $progress = 0;
        } elseif ($progress >= 100 || $status === 'TESTING') {
            $status = 'TESTING';
            $progress = 100;
        } else {
            $status = 'IN_PROGRESS';
            $progress = max(1, min(99, $progress));
        }

        $payload = [
            'progress_percentage'   => $progress,
            'status'                => $status,
            'achieved_summary'      => $validated['achieved_summary'],
            'blockers_summary'      => $validated['blockers_summary'] ?? null,
            'report_attachment_url' => $validated['report_attachment_url'] ?? null,
        ];

        try {
            if ($request->hasFile('report_attachment')) {
                $response = $api->sendWithFile(
                    'proposals/' . rawurlencode($proposal) . '/tasks/' . rawurlencode($task) . '/progress',
                    'report_attachment',
                    $request->file('report_attachment'),
                    $payload
                );
            } else {
                $response = $api->post(
                    'proposals/' . rawurlencode($proposal) . '/tasks/' . rawurlencode($task) . '/progress',
                    $payload
                );
            }

            return $response->successful()
                ? back()->with('success', 'Laporan progres berhasil dikirim dan menunggu review PM.')
                : back()->with('error', $response->json('message') ?? 'Gagal mengirim laporan progres.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Backend tidak dapat dihubungi: ' . $e->getMessage());
        }
    })->name('mahasiswa.task.progress');

    // PM sub-pages (Protected by role pm)
    Route::middleware('auth.api:pm|dosen')->prefix('pm')->name('pm.')->group(function () {
        Route::get('/project', function (ApiService $api) {
            $projectData = [];

            try {
                $response = $api->get('dosen/dashboard');
                if ($response->successful()) {
                    $projectData = $response->json('data', []);
                }
            } catch (\Throwable) {
                $projectData = [];
            }

            return view('pm.project', [
                'user'        => session('user'),
                'projectData' => is_array($projectData) ? $projectData : [],
            ]);
        })->name('project');
        Route::get('/anggota-tim', function (ApiService $api) {
            $dashboard = [];

            try {
                $response = $api->get('dosen/dashboard');
                if ($response->successful()) {
                    $dashboard = $response->json('data', []);
                }
            } catch (\Throwable) {
                $dashboard = [];
            }

            return view('pm.anggota-tim', [
                'user'      => session('user'),
                'dashboard' => is_array($dashboard) ? $dashboard : [],
            ]);
        })->name('anggota-tim');
        Route::get('/task-timeline', function (Request $request, ApiService $api) {
            $projects = [];

            try {
                $response = $api->get('dosen/dashboard');
                if ($response->successful()) {
                    $projects = $response->json('data.projects', []);
                }
            } catch (\Throwable) {
                $projects = [];
            }

            $projects = is_array($projects) ? $projects : [];

            // Proposal yang dipilih lewat ?proposal_id=..., fallback ke proyek pertama.
            $requestedId = $request->query('proposal_id')
                ?? $request->query('proposal')
                ?? $request->query('project');
            $proposalId = null;

            foreach ($projects as $project) {
                if ($requestedId !== null && (string) ($project['id'] ?? '') === (string) $requestedId) {
                    $proposalId = $project['id'];
                    break;
                }
            }

            // Jika user belum memilih proyek (tidak ada param proposal_id / proposal / project),
            // tampilkan halaman daftar proyek overview terlebih dahulu
            if ($requestedId === null) {
                return view('pm.task-timeline-projects', [
                    'user'     => session('user'),
                    'projects' => $projects,
                ]);
            }

            if ($proposalId === null && !empty($projects)) {
                $proposalId = $projects[0]['id'] ?? null;
            }

            $overview = [];
            $error = null;

            if ($proposalId !== null) {
                try {
                    $response = $api->get('proposals/' . rawurlencode((string) $proposalId) . '/tasks/overview');

                    if ($response->successful()) {
                        $overview = $response->json('data', []);
                    } else {
                        $error = $response->json('message') ?? 'Gagal mengambil data task dari backend.';
                    }
                } catch (\Throwable $e) {
                    $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
                }

                if (empty($overview['team_members'])) {
                    try {
                        $teamRes = $api->get('proposals/' . rawurlencode((string) $proposalId) . '/team');
                        if ($teamRes->successful() && is_array($teamRes->json())) {
                            $teamData = $teamRes->json();
                            $members = isset($teamData['data']) && is_array($teamData['data']) ? $teamData['data'] : $teamData;
                            $overview['team_members'] = collect($members)->map(function ($item) {
                                return [
                                    'id'            => $item['student']['id'] ?? $item['user_id'] ?? null,
                                    'name'          => $item['student']['name'] ?? null,
                                    'email'         => $item['student']['email'] ?? null,
                                    'nim'           => $item['student']['nim'] ?? null,
                                    'study_program' => $item['student']['study_program'] ?? null,
                                    'role'          => $item['role']['name'] ?? null,
                                ];
                            })->filter(fn ($m) => !empty($m['id']))->values()->toArray();
                        }
                    } catch (\Throwable) {
                        // ignore fallback error
                    }
                }
            }

            return view('pm.task-timeline', [
                'user'       => session('user'),
                'projects'   => $projects,
                'proposalId' => $proposalId,
                'overview'   => is_array($overview) ? $overview : [],
                'error'      => $error,
            ]);
        })->name('task-timeline');

        Route::post('/proposals/{proposal}/tasks', function (Request $request, ApiService $api, string $proposal) {
            $validated = $request->validate([
                'title'               => ['required', 'string', 'max:255'],
                'priority'            => ['nullable', 'in:LOW,MEDIUM,HIGH,CRITICAL'],
                'due_date'            => ['nullable', 'date'],
                'assigned_student_id' => ['nullable', 'integer'],
            ]);

            try {
                $response = $api->post('proposals/' . rawurlencode($proposal) . '/tasks', $validated);

                return $response->successful()
                    ? back()->with('success', 'Task baru berhasil ditambahkan.')
                    : back()->with('error', $response->json('message') ?? 'Task gagal disimpan ke backend.');
            } catch (\Throwable $e) {
                return back()->with('error', 'Backend task tidak dapat dihubungi: ' . $e->getMessage());
            }
        })->name('task.store');

        Route::post('/proposals/{proposal}/tasks/{task}/review', function (Request $request, ApiService $api, string $proposal, string $task) {
            $validated = $request->validate([
                'decision' => ['required', 'in:revision,approve'],
                'note'     => ['nullable', 'string', 'max:500'],
            ]);

            $payload = [
                'pm_status' => $validated['decision'] === 'approve' ? 'APPROVED' : 'NEEDS_IMPROVEMENT',
                'pm_notes'  => $validated['note'] ?? null,
            ];

            try {
                $response = $api->patch('proposals/' . rawurlencode($proposal) . '/tasks/' . rawurlencode($task) . '/review', $payload);

                return $response->successful()
                    ? back()->with('success', $validated['decision'] === 'approve'
                        ? 'Task berhasil disetujui.'
                        : 'Permintaan revisi berhasil dikirim.')
                    : back()->with('error', $response->json('message') ?? 'Review task gagal disimpan ke backend.');
            } catch (\Throwable $e) {
                return back()->with('error', 'Backend review tidak dapat dihubungi: ' . $e->getMessage());
            }
        })->name('task.review');

        Route::get('/proposals/{proposal}/uat', function (ApiService $api, int $proposal) {
            $projectResponse = $api->get("proposals/{$proposal}");
            if (! $projectResponse->successful() || ! is_array($projectResponse->json())) {
                abort($projectResponse->status() ?: 502, $projectResponse->json('message') ?? 'Data proyek tidak dapat dimuat.');
            }

            $uatResponse = $api->getUat($proposal);
            if ($uatResponse->failed() && $uatResponse->status() !== 404) {
                abort($uatResponse->status(), $uatResponse->json('message') ?? 'Data UAT tidak dapat dimuat.');
            }

            return view('pm.uat-show', [
                'proposal' => $projectResponse->json(),
                'uat' => $uatResponse->successful() ? $uatResponse->json('data') : null,
            ]);
        })->whereNumber('proposal')->name('uat.detail');
    });

    // Jurusan uses the same proposal workflow and backend master data as clients.
    Route::middleware('auth.api:jurusan')->prefix('jurusan')->name('jurusan.')->group(function () use ($storeProposal, $loadBilling) {
        Route::get('/ajukan-proyek', function (ApiService $api) {
            return view('klien.ajukan-proyek', [
                'user'       => session('user'),
                'company'    => $api->getCompanyProfile(),
                'categories' => $api->getCategories(),
            ]);
        })->name('ajukan-proyek');
        Route::post('/ajukan-proyek', fn (Request $request, ApiService $api) => $storeProposal($request, $api, 'jurusan.ajukan-proyek'))
            ->name('ajukan-proyek.store');

        Route::get('/projek-saya', function (ApiService $api) {
            $proposals = [];
            $error = null;
            $handoverProjects = [];
            $handoverProjectsAvailable = false;

            try {
                $response = $api->get('proposals/mine');
                $payload = $response->successful()
                    ? (is_array($response->json('data')) ? $response->json('data') : $response->json())
                    : null;
                if (is_array($payload)) {
                    $proposals = $payload;
                    $handoverProjectsAvailable = true;
                    $handoverProjects = collect($payload)
                        ->filter(fn ($proposal) => is_array($proposal) && !empty($proposal['bast_eligible']) && !empty($proposal['project_code']))
                        ->values()
                        ->all();
                } else {
                    $error = $response->json('message') ?? 'Data projek tidak dapat dimuat.';
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
            }

            return view('klien.projek-saya', [
                'user'                      => session('user'),
                'proposals'                 => $proposals,
                'handoverProjects'          => $handoverProjects,
                'handoverProjectsAvailable' => $handoverProjectsAvailable,
                'error'                     => $error,
            ]);
        })->name('projek-saya');
        Route::get('/tagihan', function (ApiService $api) use ($loadBilling) {
            [$proposals, $projectsBilling, $error] = $loadBilling($api);

            return view('klien.tagihan', [
                'user'            => session('user'),
                'projectsBilling' => $projectsBilling,
                'proposals'       => $proposals,
                'error'           => $error,
            ]);
        })->name('tagihan');
    });

    Route::get('/dashboard-jurusan', function () {
        $user = session('user');
        if (!ApiService::hasRole($user, 'jurusan')) {
            abort(403, 'Akses ditolak: Anda bukan Pihak Jurusan.');
        }
        session(['active_role' => 'jurusan']);
        return redirect()->route('dashboard');
    })->name('dashboard-jurusan');

    // Pengurus TEFA sub-pages (Protected by role pengurus_tefa)
    $readApiList = static function ($response): array {
        if (!$response->successful()) {
            return [];
        }

        $data = $response->json('data');
        if (is_array($data)) {
            return array_values(array_filter($data, 'is_array'));
        }

        $data = $response->json();
        return is_array($data) ? array_values(array_filter($data, 'is_array')) : [];
    };

    Route::middleware('auth.api:pengurus_tefa')->prefix('pengurus-tefa')->name('pengurus-tefa.')->group(function () use ($readApiList) {
        Route::get('/projek', function (ApiService $api) {
            $dashboardData = [];
            $error = null;

            try {
                $response = $api->get('tefa/dashboard');
                if ($response->successful()) {
                    $dashboardData = $response->json('data', []);
                } else {
                    $error = $response->json('message') ?? 'Gagal memuat data projek.';
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke backend: ' . $e->getMessage();
            }

            return view('pengurus-tefa.projek', [
                'user'      => session('user'),
                'dashboard' => $dashboardData,
                'error'     => $error,
            ]);
        })->name('projek');

        Route::get('/projek/{proposal}', function (ApiService $api, int $proposal) {
            $proposalData = [];
            $uatData = null;
            $error = null;

            try {
                $pRes = $api->get("proposals/{$proposal}");
                if ($pRes->successful()) {
                    $proposalData = $pRes->json();
                } else {
                    $error = $pRes->json('message') ?? 'Data projek tidak dapat dimuat.';
                }

                $uatRes = $api->getUat($proposal);
                if ($uatRes->successful()) {
                    $uatData = $uatRes->json('data');
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
            }

            return view('pengurus-tefa.projek-detail', [
                'user'     => session('user'),
                'proposal' => $proposalData,
                'uat'      => $uatData,
                'error'    => $error,
            ]);
        })->whereNumber('proposal')->name('projek.detail');

        Route::get('/bast', function (ApiService $api) use ($readApiList) {
            $projects = [];
            $error = null;

            try {
                $response = $api->get('proposals/handover');
                $projects = $readApiList($response);
                if (!$response->successful()) {
                    $error = $response->json('message') ?? 'Data proyek untuk BAST belum dapat dimuat.';
                }
            } catch (\Throwable $exception) {
                $error = 'Tidak dapat memuat proyek untuk BAST: ' . $exception->getMessage();
            }

            return view('pengurus-tefa.bast', [
                'user' => session('user'),
                'projects' => $projects,
                'error' => $error,
            ]);
        })->name('bast');

        Route::get('/bast/{proposal}/prepare', function (ApiService $api, int $proposal) use ($readApiList) {
            try {
                $response = $api->get('proposals/handover');
                $project = collect($readApiList($response))->firstWhere('id', $proposal);

                if (!$response->successful() || !is_array($project)) {
                    return redirect()->route('pengurus-tefa.bast')->with('error', 'Proyek BAST tidak ditemukan atau tidak lagi memenuhi syarat.');
                }

                if (!empty($project['client_approved'])) {
                    return redirect()->route('pengurus-tefa.bast')->with('error', 'BAST yang sudah disetujui PIC tidak dapat diubah.');
                }

                return view('pengurus-tefa.bast-prepare', [
                    'user' => session('user'),
                    'project' => $project,
                ]);
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::error('Gagal memuat detail persiapan BAST: ' . $exception->getMessage(), [
                    'proposal_id' => $proposal,
                ]);

                return redirect()->route('pengurus-tefa.bast')->with('error', 'Data BAST tidak dapat dimuat. Silakan coba kembali.');
            }
        })->whereNumber('proposal')->name('bast.prepare.form');

        Route::get('/proposals/{proposal}/bast', fn (int $proposal) => redirect()->route('pengurus-tefa.bast.prepare.form', $proposal))->whereNumber('proposal');

        Route::post('/proposals/{proposal}/bast', function (Request $request, ApiService $api, int $proposal) {
            if ($request->hasAny(['nomor_bast', 'bast_number', 'tanggal_serah_terima', 'handover_date'])) {
                return back()->withInput()->withErrors([
                    'nomor_bast' => 'Nomor BAST dan tanggal serah terima tidak boleh dimanipulasi manual. Nilai ini digenerate murni di sisi server.',
                ]);
            }

            $deliveryType = $request->input('delivery_type', 'source_code');
            $isSourceCode = $deliveryType === 'source_code';

            $rules = [
                'documentation_file' => ['nullable', 'file', 'mimes:pdf,docx,zip,doc', 'max:10240'],
                'user_manual_file' => ['nullable', 'file', 'mimes:pdf,docx,doc,zip', 'max:10240'],
                'handover_notes' => ['nullable', 'string'],
                'handover_deliverables' => ['required', 'json'],
                'maintenance_warranty_months' => ['nullable', 'integer', 'min:0'],
                'maintenance_notes' => ['nullable', 'string'],
                'repository_url' => ['nullable', 'url', 'max:255'],
                'access_email' => ['nullable', 'string', 'max:255'],
                'access_password' => ['nullable', 'string', 'max:255'],
            ];

            if ($isSourceCode) {
                $rules['app_url'] = ['nullable', 'url', 'max:255'];
            } else {
                $rules['app_url'] = ['required', 'url', 'max:255'];
            }

            $validated = $request->validate($rules, [
                'app_url.required' => 'URL Akses Aplikasi Sistem wajib diisi karena klien memilih kelola penuh oleh TeFa.',
                'documentation_file.mimes' => 'Berkas lampiran dokumentasi API harus berformat aman: .pdf, .docx, atau .zip.',
                'documentation_file.max' => 'Ukuran berkas lampiran dokumentasi API maksimal 10 MB.',
                'user_manual_file.mimes' => 'Berkas panduan pengguna harus berformat: PDF, DOCX, DOC, atau ZIP.',
                'user_manual_file.max' => 'Ukuran berkas panduan pengguna maksimal 10 MB.',
            ]);

            try {
                $payload = $validated;
                unset($payload['documentation_file'], $payload['user_manual_file']);

                $files = [];
                if ($request->hasFile('documentation_file')) {
                    $files['documentation_file'] = $request->file('documentation_file');
                }
                if ($request->hasFile('user_manual_file')) {
                    $files['user_manual_file'] = $request->file('user_manual_file');
                }

                if ($files !== []) {
                    $response = $api->sendWithFiles(
                        "proposals/{$proposal}/handover/prepare",
                        $files,
                        $payload
                    );
                } else {
                    $response = $api->post("proposals/{$proposal}/handover/prepare", $payload);
                }

                if ($response->successful()) {
                    return redirect()->route('pengurus-tefa.bast')->with('success', 'BAST berhasil disiapkan untuk persetujuan PIC Klien.');
                }

                $errors = $response->json('errors');
                return back()->withInput()->withErrors(is_array($errors) ? $errors : [
                    'handover' => $response->json('message') ?? 'BAST gagal disiapkan.',
                ]);
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::error('Gagal menyiapkan BAST: ' . $exception->getMessage(), [
                    'proposal_id' => $proposal,
                ]);
                return back()->withInput()->withErrors([
                    'handover' => 'Tidak dapat menghubungi backend BAST. Silakan coba kembali.',
                ]);
            }
        })->whereNumber('proposal')->name('bast.prepare');

        Route::get('/proposals/{proposal}/bast/pdf', function (ApiService $api, int $proposal) {
            try {
                $response = $api->get("proposals/{$proposal}/handover/bast");
                if ($response->failed()) {
                    return back()->with('error', $response->json('message') ?? 'PDF BAST belum tersedia.');
                }

                return response($response->body(), 200, [
                    'Content-Type' => $response->header('Content-Type') ?? 'application/pdf',
                    'Content-Disposition' => $response->header('Content-Disposition') ?? 'attachment; filename="bast-' . $proposal . '.pdf"',
                ]);
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::error('Gagal mengunduh PDF BAST: ' . $exception->getMessage(), [
                    'proposal_id' => $proposal,
                ]);
                return back()->with('error', 'PDF BAST tidak dapat diunduh dari server.');
            }
        })->whereNumber('proposal')->name('bast.pdf');

        Route::get('/proposals/{proposal}/bast/documentation', function (ApiService $api, int $proposal) {
            try {
                $response = $api->get("proposals/{$proposal}/handover/documentation");
                if (!$response->successful()) {
                    return back()->with('error', $response->json('message') ?? 'Dokumentasi BAST tidak dapat diunduh.');
                }

                return response($response->body(), 200, [
                    'Content-Type' => $response->header('Content-Type') ?? 'application/octet-stream',
                    'Content-Disposition' => $response->header('Content-Disposition') ?? 'attachment; filename="dokumentasi-api"',
                ]);
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::error('Gagal mengunduh dokumentasi BAST: ' . $exception->getMessage(), [
                    'proposal_id' => $proposal,
                ]);
                return back()->with('error', 'Dokumentasi BAST tidak dapat diunduh dari server.');
            }
        })->whereNumber('proposal')->name('bast.documentation');

        Route::get('/proposals/{proposal}/bast/user-manual', function (ApiService $api, int $proposal) {
            try {
                $response = $api->get("proposals/{$proposal}/handover/user-manual");
                if (!$response->successful()) {
                    return back()->with('error', $response->json('message') ?? 'Panduan pengguna tidak dapat diunduh.');
                }

                return response($response->body(), 200, [
                    'Content-Type' => $response->header('Content-Type') ?? 'application/octet-stream',
                    'Content-Disposition' => $response->header('Content-Disposition') ?? 'attachment; filename="panduan-pengguna"',
                ]);
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::error('Gagal mengunduh panduan pengguna BAST: ' . $exception->getMessage(), [
                    'proposal_id' => $proposal,
                ]);
                return back()->with('error', 'Panduan pengguna tidak dapat diunduh dari server.');
            }
        })->whereNumber('proposal')->name('bast.user-manual');
        Route::get('/klien-mitra', function (ApiService $api) use ($readApiList) {
            $clients = [];
            $error = null;

            try {
                $clientResponse = $api->get('users', ['role' => 'klien']);
                $proposalResponse = $api->get('proposals');
                $clientUsers = $readApiList($clientResponse);
                $proposals = $readApiList($proposalResponse);

                if (!$clientResponse->successful() || !$proposalResponse->successful()) {
                    $error = 'Data klien dan proyek belum dapat dimuat dari database.';
                }

                $projectCountsByEmail = collect($proposals)
                    ->filter(fn ($proposal) => !empty($proposal['client_company_email']))
                    ->countBy(fn ($proposal) => strtolower(trim((string) $proposal['client_company_email'])));
                $projectCountsByProfile = collect($proposals)
                    ->filter(fn ($proposal) => !empty($proposal['client_profile_id']))
                    ->countBy(fn ($proposal) => (string) $proposal['client_profile_id']);

                $clients = collect($clientUsers)
                    ->filter(fn ($client) => is_array($client) && !empty($client['id']))
                    ->groupBy(function ($client) {
                        $email = strtolower(trim((string) ($client['company_email'] ?? '')));
                        return $email !== '' ? 'email:' . $email : 'profile:' . ($client['client_profile_id'] ?? $client['id']);
                    })
                    ->map(function ($companyUsers) use ($projectCountsByEmail, $projectCountsByProfile) {
                        $company = $companyUsers->first();
                        $email = strtolower(trim((string) ($company['company_email'] ?? '')));
                        $profileId = (string) ($company['client_profile_id'] ?? '');
                        $contacts = $companyUsers
                            ->map(fn ($pic) => trim((string) ($pic['name'] ?? '') . (!empty($pic['email']) ? ' · ' . $pic['email'] : '')))
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();

                        return [
                            'name'          => $company['company_name'] ?? $company['name'] ?? '—',
                            'contacts'      => $contacts,
                            'total_projects' => $email !== ''
                                ? (int) ($projectCountsByEmail[$email] ?? 0)
                                : (int) ($projectCountsByProfile[$profileId] ?? 0),
                        ];
                    })
                    ->values()
                    ->all();
            } catch (\Throwable $e) {
                $error = 'Tidak dapat memuat data klien dari backend: ' . $e->getMessage();
            }

            return view('pengurus-tefa.klien-mitra', [
                'user'    => session('user'),
                'clients' => $clients,
                'error'   => $error,
            ]);
        })->name('klien-mitra');

        Route::get('/klien-mitra/registrasi', function () {
            return view('pengurus-tefa.klien-mitra-create', [
                'user' => session('user'),
            ]);
        })->name('klien-mitra.registrasi');

        Route::post('/klien-mitra/registrasi', function (Request $request, ApiService $api) {
            $validated = $request->validate([
                'name'                  => ['required', 'string', 'max:150'],
                'email'                 => ['required', 'email'],
                'password'              => ['required', 'string', 'min:8', 'confirmed'],
                'password_confirmation' => ['required', 'string', 'min:8'],
                'phone_number'          => ['nullable', 'string', 'max:20'],
                'address'               => ['nullable', 'string', 'max:500'],
                'client_type'           => ['nullable', 'string', 'in:EXTERNAL,INTERNAL'],
                'pic_name'              => ['nullable', 'string', 'max:100'],
                'pic_email'             => ['nullable', 'email'],
                'pic_phone'             => ['nullable', 'string', 'max:20'],
                'pic_position'          => ['nullable', 'string', 'max:100'],
                'pic_password'          => ['nullable', 'string', 'min:8', 'confirmed'],
                'pic_password_confirmation' => ['nullable', 'string', 'min:8'],
            ], [
                'name.required'         => 'Nama perusahaan wajib diisi.',
                'email.required'        => 'Email perusahaan wajib diisi.',
                'email.email'           => 'Format email perusahaan tidak valid.',
                'password.required'     => 'Password akun wajib diisi.',
                'password.min'          => 'Password minimal 8 karakter.',
                'password.confirmed'    => 'Konfirmasi password tidak cocok.',
                'pic_password.min'       => 'Password PIC minimal 8 karakter.',
                'pic_password.confirmed' => 'Konfirmasi password PIC tidak cocok.',
            ]);

            try {
                $response = $api->post('users/company', $validated);

                if ($response->successful()) {
                    return redirect()
                        ->route('pengurus-tefa.klien-mitra')
                        ->with('success', "Perusahaan '{$validated['name']}' berhasil didaftarkan.");
                }

                $errors = $response->json('errors');
                if (is_array($errors) && !empty($errors)) {
                    return back()->withInput()->withErrors($errors);
                }

                $message = $response->json('message') ?? 'Gagal mendaftarkan perusahaan. Silakan coba kembali.';
                return back()->withInput()->with('error', $message);
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Terjadi kesalahan saat menghubungi server: ' . $e->getMessage());
            }
        })->name('klien-mitra.registrasi.store');

        Route::get('/klien-mitra/registrasi-pic', function (ApiService $api) use ($readApiList) {
            $companies = [];
            try {
                $response = $api->get('companies');
                $companies = $readApiList($response);
            } catch (\Throwable) {
                $companies = [];
            }

            return view('pengurus-tefa.klien-mitra-pic-create', [
                'user'      => session('user'),
                'companies' => $companies,
            ]);
        })->name('klien-mitra.pic.registrasi');

        Route::post('/klien-mitra/registrasi-pic', function (Request $request, ApiService $api) {
            $validated = $request->validate([
                'client_profile_id'     => ['required', 'integer'],
                'name'                  => ['required', 'string', 'max:100'],
                'position'              => ['required', 'string', 'max:100'],
                'email'                 => ['required', 'email'],
                'phone_number'          => ['nullable', 'string', 'max:20'],
                'password'              => ['required', 'string', 'min:8', 'confirmed'],
                'password_confirmation' => ['required', 'string', 'min:8'],
            ], [
                'client_profile_id.required' => 'Perusahaan wajib dipilih.',
                'name.required'              => 'Nama PIC wajib diisi.',
                'position.required'          => 'Posisi/jabatan PIC wajib diisi.',
                'email.required'             => 'Email PIC wajib diisi.',
                'email.email'                => 'Format email PIC tidak valid.',
                'password.required'          => 'Password akun wajib diisi.',
                'password.min'               => 'Password minimal 8 karakter.',
                'password.confirmed'         => 'Konfirmasi password tidak cocok.',
            ]);

            try {
                $response = $api->post('users/pic', $validated);

                if ($response->successful()) {
                    return redirect()
                        ->route('pengurus-tefa.klien-mitra')
                        ->with('success', "PIC '{$validated['name']}' berhasil didaftarkan.");
                }

                $errors = $response->json('errors');
                if (is_array($errors) && !empty($errors)) {
                    return back()->withInput()->withErrors($errors);
                }

                $message = $response->json('message') ?? 'Gagal mendaftarkan PIC. Silakan coba kembali.';
                return back()->withInput()->with('error', $message);
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Terjadi kesalahan saat menghubungi server: ' . $e->getMessage());
            }
        })->name('klien-mitra.pic.registrasi.store');
        Route::get('/dosen', function (ApiService $api) use ($readApiList) {
            $dosens = [];
            $error = null;

            try {
                $response = $api->get('users', ['role' => 'dosen']);
                $dosens = $readApiList($response);
                if (!$response->successful()) {
                    $error = $response->json('message') ?? 'Data dosen belum dapat dimuat dari database.';
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat memuat data dosen dari backend: ' . $e->getMessage();
            }

            return view('pengurus-tefa.dosen', ['user' => session('user'), 'dosens' => $dosens, 'error' => $error]);
        })->name('dosen');
        Route::get('/mahasiswa', function (ApiService $api) use ($readApiList) {
            $mahasiswas = [];
            $error = null;

            try {
                $response = $api->get('users', ['role' => 'mahasiswa']);
                $mahasiswas = $readApiList($response);
                if (!$response->successful()) {
                    $error = $response->json('message') ?? 'Data mahasiswa belum dapat dimuat dari database.';
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat memuat data mahasiswa dari backend: ' . $e->getMessage();
            }

            return view('pengurus-tefa.mahasiswa', ['user' => session('user'), 'mahasiswas' => $mahasiswas, 'error' => $error]);
        })->name('mahasiswa');

        Route::get('/validasi-proposal', function (ApiService $api) {
            $pendingProposals = [];
            $allProposals = [];

            try {
                $pendingResponse = $api->get('proposals/pending');
                if ($pendingResponse->successful() && is_array($pendingResponse->json())) {
                    $pendingProposals = $pendingResponse->json();
                }
            } catch (\Throwable) {
                // Biarkan kosong; halaman tetap dirender dengan empty state.
            }

            try {
                $allResponse = $api->get('proposals');
                if ($allResponse->successful() && is_array($allResponse->json())) {
                    $allProposals = $allResponse->json();
                }
            } catch (\Throwable) {
                // Statistik memakai data yang tersedia saja.
            }

            return view('pengurus-tefa.validasi-proposal', [
                'user'         => session('user'),
                'proposals'    => $pendingProposals,
                'allProposals' => $allProposals,
            ]);
        })->name('validasi-proposal');
        Route::get('/validasi-proposal/{proposal}', function (ApiService $api, int $proposal) {
            $detail = [];
            $error = null;

            try {
                $response = $api->get("proposals/{$proposal}/pending");
                $data = $response->json();

                if ($response->successful() && is_array($data)) {
                    $detail = $data;
                } else {
                    $altResponse = $api->get("proposals/{$proposal}");
                    if ($altResponse->successful() && is_array($altResponse->json())) {
                        $raw = $altResponse->json();
                        $detail = array_merge($raw, [
                            'category_name' => $raw['category']['name'] ?? $raw['category_name'] ?? 'Other',
                            'status'        => $raw['status']['name'] ?? $raw['status'] ?? '-',
                            'client_name'   => $raw['client_company_name'] ?? $raw['client']['name'] ?? '-',
                            'pic'           => [
                                'name'  => $raw['client']['name'] ?? '-',
                                'email' => $raw['client']['email'] ?? '-',
                                'no_hp' => $raw['client']['phone_number'] ?? '-',
                            ],
                        ]);
                    } else {
                        $error = $response->json('message') ?? 'Detail proposal tidak ditemukan.';
                    }
                }
            } catch (\Throwable) {
                $error = 'Gagal mengambil detail proposal dari server TEVARA.';
            }

            return view('pengurus-tefa.validasi-proposal-detail', [
                'user'     => session('user'),
                'proposal' => $detail,
                'error'    => $error,
            ]);
        })->whereNumber('proposal')->name('validasi-proposal.detail');

        Route::get('/validasi-proposal/{proposal}/revisi', function (ApiService $api, int $proposal) {
            $detail = [];
            $error = null;

            try {
                $response = $api->get("proposals/{$proposal}/pending");
                $data = $response->json();

                if ($response->successful() && is_array($data)) {
                    $detail = $data;
                } else {
                    $altResponse = $api->get("proposals/{$proposal}");
                    if ($altResponse->successful() && is_array($altResponse->json())) {
                        $alt = $altResponse->json();
                        $detail = [
                            'id'               => $alt['id'],
                            'proposal_code'    => $alt['proposal_code'] ?? '-',
                            'title'            => $alt['title'] ?? '-',
                            'client_name'      => $alt['client']['name'] ?? $alt['client_name'] ?? '-',
                            'review_notes'     => $alt['review_notes'] ?? '',
                            'status'           => $alt['status']['name'] ?? $alt['status'] ?? '-',
                            'category_name'    => $alt['category']['name'] ?? $alt['category_name'] ?? 'Other',
                            'estimated_budget' => $alt['estimated_budget'] ?? 0,
                            'desired_deadline' => $alt['desired_deadline'] ?? null,
                        ];
                    } else {
                        $error = $response->json('message') ?? 'Detail proposal tidak ditemukan.';
                    }
                }
            } catch (\Throwable) {
                $error = 'Gagal mengambil detail proposal dari server TEVARA.';
            }

            return view('pengurus-tefa.revisi-proposal', [
                'user'     => session('user'),
                'proposal' => $detail,
                'error'    => $error,
            ]);
        })->whereNumber('proposal')->name('validasi-proposal.revisi');

        Route::post('/validasi-proposal/{proposal}/revisi', function (Request $request, ApiService $api, int $proposal) {
            $validated = $request->validate([
                'review_notes' => ['required', 'string', 'min:5', 'max:2000'],
            ], [
                'review_notes.required' => 'Catatan apa yang perlu direvisi klien wajib diisi.',
                'review_notes.min'      => 'Catatan revisi minimal 5 karakter.',
            ]);

            try {
                $response = $api->patch("proposals/{$proposal}/review", [
                    'decision'     => 'revision_required',
                    'review_notes' => $validated['review_notes'],
                ]);

                if ($response->successful()) {
                    return redirect()
                        ->route('pengurus-tefa.validasi-proposal')
                        ->with('success', 'Permintaan revisi berhasil dikirim ke klien. Status proposal berubah menjadi Improvement (Revisi).');
                }

                return back()
                    ->withInput()
                    ->with('error', 'Gagal mengirim revisi: ' . ($response->json('message') ?? 'Terjadi kesalahan pada backend.'));
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Tidak dapat terhubung ke backend: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->name('validasi-proposal.revisi.store');

        Route::post('/validasi-proposal/{proposal}/tolak', function (Request $request, ApiService $api, int $proposal) {
            $validated = $request->validate([
                'review_notes' => ['required', 'string', 'min:5', 'max:2000'],
            ], [
                'review_notes.required' => 'Alasan penolakan proposal wajib diisi.',
                'review_notes.min'      => 'Alasan penolakan minimal 5 karakter.',
                'review_notes.max'      => 'Alasan penolakan maksimal 2000 karakter.',
            ]);

            try {
                $response = $api->patch("proposals/{$proposal}/review", [
                    'decision'     => 'rejected',
                    'review_notes' => $validated['review_notes'],
                ]);

                if ($response->successful()) {
                    return redirect()
                        ->route('pengurus-tefa.validasi-proposal')
                        ->with('success', 'Proposal berhasil ditolak dan pemberitahuan telah dikirimkan ke PIC klien.');
                }

                return back()
                    ->withInput()
                    ->with('error', 'Gagal menolak proposal: ' . ($response->json('message') ?? 'Terjadi kesalahan pada backend.'));
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Tidak dapat terhubung ke backend: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->name('validasi-proposal.tolak');

        // Form Lanjutan Persetujuan Proposal (SPK, Termin Biaya & Tim Pengembang)
        Route::get('/validasi-proposal/{proposal}/setujui', function (ApiService $api, int $proposal) {
            $detail = [];
            $sqlitePath = base_path('../tevara-api/database/database.sqlite');

            // 1. Ambil detail proposal
            try {
                $response = $api->get("proposals/{$proposal}/pending");
                $data = $response->json();
                if ($response->successful() && is_array($data)) {
                    $detail = $data;
                }
            } catch (\Throwable) {}

            if (empty($detail)) {
                try {
                    $response = $api->get("proposals/{$proposal}");
                    $data = $response->json('data') ?? $response->json();
                    if ($response->successful() && is_array($data)) {
                        $detail = $data;
                    }
                } catch (\Throwable) {}
            }

            if (empty($detail) && file_exists($sqlitePath)) {
                try {
                    $pdo = new \PDO("sqlite:" . $sqlitePath);
                    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, u.name as client_name, cp.company_name FROM proposals p LEFT JOIN proposal_categories c ON p.category_id = c.id LEFT JOIN users u ON p.client_id = u.id LEFT JOIN client_profiles cp ON u.id = cp.user_id WHERE p.id = ?");
                    $stmt->execute([$proposal]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    if ($row) {
                        $detail = $row;
                        $detail['client_name'] = $row['company_name'] ?? $row['client_name'] ?? 'Mitra Klien';
                    }
                } catch (\Throwable) {}
            }

            if (empty($detail)) {
                return redirect()->route('pengurus-tefa.validasi-proposal')->with('error', 'Proposal tidak ditemukan.');
            }

            // 2. Ambil data Dosen (calon PM)
            $dosens = [];
            try {
                $resDosen = $api->get('users', ['role' => 'dosen']);
                if ($resDosen->successful() && is_array($resDosen->json('data'))) {
                    $dosens = $resDosen->json('data');
                }
            } catch (\Throwable) {}

            if (empty($dosens) && file_exists($sqlitePath)) {
                try {
                    $pdo = $pdo ?? new \PDO("sqlite:" . $sqlitePath);
                    $stmt = $pdo->query("SELECT u.id, u.name, u.email, u.phone_number, lp.nidn FROM users u JOIN model_has_roles mhr ON u.id = mhr.model_id JOIN roles r ON mhr.role_id = r.id LEFT JOIN lecturer_profiles lp ON u.id = lp.user_id WHERE r.name = 'dosen' ORDER BY u.name ASC");
                    $dosens = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                } catch (\Throwable) {}
            }

            // 3. Ambil data Mahasiswa (calon Tim Pengembang)
            $mahasiswas = [];
            try {
                $resMhs = $api->get('users', ['role' => 'mahasiswa']);
                if ($resMhs->successful() && is_array($resMhs->json('data'))) {
                    $mahasiswas = $resMhs->json('data');
                }
            } catch (\Throwable) {}

            if (empty($mahasiswas) && file_exists($sqlitePath)) {
                try {
                    $pdo = $pdo ?? new \PDO("sqlite:" . $sqlitePath);
                    $stmt = $pdo->query("SELECT u.id, u.name, u.email, sp.nim, sp.study_program FROM users u JOIN model_has_roles mhr ON u.id = mhr.model_id JOIN roles r ON mhr.role_id = r.id LEFT JOIN student_profiles sp ON u.id = sp.user_id WHERE r.name = 'mahasiswa' ORDER BY u.name ASC");
                    $mahasiswas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                } catch (\Throwable) {}
            }

            // 4. Ambil Project Roles
            $projectRoles = [];
            try {
                $resRoles = $api->get('master/project-roles');
                if ($resRoles->successful() && is_array($resRoles->json('data'))) {
                    $projectRoles = $resRoles->json('data');
                }
            } catch (\Throwable) {}

            if (empty($projectRoles) && file_exists($sqlitePath)) {
                try {
                    $pdo = $pdo ?? new \PDO("sqlite:" . $sqlitePath);
                    $stmt = $pdo->query("SELECT id, name FROM project_roles ORDER BY id ASC");
                    $projectRoles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                } catch (\Throwable) {}
            }

            if (empty($projectRoles)) {
                $projectRoles = [
                    ['id' => 1, 'name' => 'UI/UX'],
                    ['id' => 2, 'name' => 'Frontend'],
                    ['id' => 3, 'name' => 'Backend'],
                    ['id' => 4, 'name' => 'Fullstack'],
                    ['id' => 5, 'name' => 'QA Tester'],
                ];
            }

            return view('pengurus-tefa.validasi-proposal-setujui', [
                'user'         => session('user'),
                'proposal'     => $detail,
                'dosens'       => $dosens,
                'mahasiswas'   => $mahasiswas,
                'projectRoles' => $projectRoles,
            ]);
        })->whereNumber('proposal')->name('validasi-proposal.setujui');

        // Simpan Persetujuan & Pembentukan Proyek
        Route::post('/validasi-proposal/{proposal}/setujui', function (Request $request, ApiService $api, int $proposal) {
            $validated = $request->validate([
                'spk_number'                  => 'nullable|string|max:100',
                'scope_of_work'               => 'required|string',
                'total_amount'                => 'required|numeric|min:0',
                'payment_terms_description'   => 'nullable|string',
                'maintenance_warranty_months' => 'nullable|integer|min:0',
                'start_date'                  => 'required|date',
                'target_completion_date'      => 'required|date|after_or_equal:start_date',
                'pm_id'                       => 'required|integer',
                'terms'                       => 'nullable',
                'members'                     => 'nullable',
            ]);

            $rawTerms = $request->input('terms');
            $terms = is_string($rawTerms) ? (json_decode($rawTerms, true) ?? []) : (is_array($rawTerms) ? $rawTerms : []);

            $rawMembers = $request->input('members');
            $members = is_string($rawMembers) ? (json_decode($rawMembers, true) ?? []) : (is_array($rawMembers) ? $rawMembers : []);

            // Persetujuan, SPK, termin, dan tim pengembang sepenuhnya ditangani backend API.
            try {
                $payload = [
                    'spk_number'                  => $validated['spk_number'],
                    'scope_of_work'               => $validated['scope_of_work'],
                    'total_amount'                => $validated['total_amount'],
                    'payment_terms_description'   => $validated['payment_terms_description'],
                    'maintenance_warranty_months' => $validated['maintenance_warranty_months'] ?? 3,
                    'start_date'                  => $validated['start_date'],
                    'target_completion_date'      => $validated['target_completion_date'],
                    'pm_id'                       => $validated['pm_id'],
                    'terms'                       => $terms,
                    'members'                     => $members,
                ];

                $response = $api->post("proposals/{$proposal}/contract", $payload);

                if ($response->failed()) {
                    return back()->withInput()->with('error', 'Gagal menyetujui proposal: ' . ($response->json('message') ?? 'Terjadi kesalahan pada backend API.'));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Gagal menghubungi backend API untuk persetujuan proposal: ' . $e->getMessage());

                return back()->withInput()->with('error', 'Tidak dapat terhubung ke backend: ' . $e->getMessage());
            }

            return redirect()->route('pengurus-tefa.validasi-proposal')
                ->with('success', 'Proposal berhasil disetujui! Surat perjanjian (SPK), skema termin pembayaran, dan tim pengembang proyek resmi ditetapkan.');
        })->whereNumber('proposal')->name('validasi-proposal.setujui.store');

        Route::get('/keuangan', function (ApiService $api) {
            $pendingPayments = [];
            $allPayments = [];
            $allProposals = [];
            $error = null;

            $normalizeProofUrl = static function (?string $url): ?string {
                if (!$url) {
                    return null;
                }
                if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                    return $url;
                }
                $baseUrl = rtrim(config('services.backend_api.url') ?? env('BACKEND_API_URL', 'http://127.0.0.1:8000/api/v1'), '/');
                $baseHost = preg_replace('#/api/v1$#', '', $baseUrl);
                return $baseHost . '/storage/' . ltrim($url, '/');
            };

            try {
                $resPending = $api->getPendingPayments();
                if ($resPending->successful() && is_array($resPending->json('data'))) {
                    $pendingPayments = $resPending->json('data');
                    foreach ($pendingPayments as &$p) {
                        $p['proof_url'] = $normalizeProofUrl($p['proof_url'] ?? null);
                    }
                    unset($p);
                }
            } catch (\Throwable $e) {
                $error = 'Gagal memuat antrean verifikasi pembayaran: ' . $e->getMessage();
            }

            try {
                $resPayments = $api->getAllPayments();
                if ($resPayments->successful() && is_array($resPayments->json('data'))) {
                    $allPayments = $resPayments->json('data');
                    foreach ($allPayments as &$p) {
                        $p['proof_url'] = $normalizeProofUrl($p['proof_url'] ?? null);
                    }
                    unset($p);
                }
            } catch (\Throwable) {
                $allPayments = [];
            }

            try {
                $resAll = $api->getProposals();
                if ($resAll->successful() && is_array($resAll->json())) {
                    $allProposals = $resAll->json();
                }
            } catch (\Throwable) {
                $allProposals = [];
            }

            return view('pengurus-tefa.keuangan', [
                'user'            => session('user'),
                'pendingPayments' => $pendingPayments,
                'allPayments'     => $allPayments,
                'allProposals'    => $allProposals,
                'error'           => $error,
            ]);
        })->name('keuangan');

        Route::get('/proposals/{proposal}/payments/{payment}/verifikasi', function (ApiService $api, int $proposal, int $payment) {
            $targetPayment = null;
            $proposalDetail = null;
            $error = null;

            // 1. Coba ambil dari antrean pending payments
            try {
                $resPending = $api->getPendingPayments();
                if ($resPending->successful() && is_array($resPending->json('data'))) {
                    foreach ($resPending->json('data') as $p) {
                        if ((int) ($p['id'] ?? 0) === $payment) {
                            $targetPayment = $p;
                            $proposalDetail = $p['proposal'] ?? null;
                            break;
                        }
                    }
                }
            } catch (\Throwable) {
                // Lanjut coba ambil detail proposal
            }

            // 2. Coba ambil detail proposal untuk data pendukung atau jika payment belum ditemukan
            try {
                $resProp = $api->get("proposals/{$proposal}");
                if ($resProp->successful() && is_array($resProp->json())) {
                    $propData = $resProp->json();
                    if (!$proposalDetail) {
                        $proposalDetail = $propData;
                    } else {
                        $proposalDetail = array_merge($propData, $proposalDetail);
                    }

                    if (!$targetPayment && !empty($propData['payments']) && is_array($propData['payments'])) {
                        foreach ($propData['payments'] as $pay) {
                            if ((int) ($pay['id'] ?? 0) === $payment) {
                                $targetPayment = $pay;
                                $targetPayment['proposal'] = $proposalDetail;
                                break;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                if (!$targetPayment) {
                    $error = 'Gagal memuat rincian pembayaran dari server: ' . $e->getMessage();
                }
            }

            // 3. Fallback sampel jika backend offline atau simulasi data demo
            if (!$targetPayment) {
                $targetPayment = [
                    'id'                 => $payment,
                    'proposal_id'        => $proposal,
                    'term_sequence'      => 2,
                    'amount'             => 25200000,
                    'payment_date'       => '2026-11-01',
                    'payment_method'     => 'Transfer Bank (Giro Mandiri)',
                    'transfer_reference' => 'TRF20261101-089',
                    'proof_url'          => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=800&auto=format&fit=crop&q=80',
                    'status'             => 'PENDING',
                    'verification_notes' => null,
                    'created_at'         => '2026-11-01 14:32:00',
                    'proposal'           => [
                        'id'           => $proposal,
                        'project_code' => 'PRSL-2025-001',
                        'title'        => 'Sistem Informasi Manajemen Teaching Factory',
                        'client'       => [
                            'name'         => 'Budi Santoso',
                            'email'        => 'budi.santoso@majubersamasentosa.co.id',
                            'company_name' => 'PT Maju Bersama Sentosa',
                            'phone'        => '0812-3456-7890',
                        ],
                    ],
                ];
                $proposalDetail = $targetPayment['proposal'];
            }

            // Normalisasi URL berkas bukti
            $proofUrl = $targetPayment['proof_url'] ?? null;
            if ($proofUrl && !str_starts_with($proofUrl, 'http')) {
                $baseUrl = rtrim(config('services.backend_api.url') ?? env('BACKEND_API_URL', 'http://127.0.0.1:8000/api/v1'), '/');
                $baseHost = preg_replace('#/api/v1$#', '', $baseUrl);
                $targetPayment['proof_url'] = $baseHost . '/storage/' . ltrim($proofUrl, '/');
            }

            return view('pengurus-tefa.verifikasi-bukti', [
                'user'     => session('user'),
                'proposal' => $proposalDetail ?? ($targetPayment['proposal'] ?? []),
                'payment'  => $targetPayment,
                'error'    => $error,
            ]);
        })->whereNumber('proposal')->whereNumber('payment')->name('payments.verifikasi');

        Route::post('/proposals/{proposal}/payments', function (Request $request, ApiService $api, int $proposal) {
            $validated = $request->validate([
                'term_sequence' => ['required', 'integer', 'min:1'],
                'amount'        => ['required', 'numeric', 'min:0'],
                'due_date'      => ['nullable', 'date'],
            ], [
                'term_sequence.required' => 'Nomor termin wajib diisi.',
                'amount.required'        => 'Nominal tagihan termin wajib diisi.',
            ]);

            // 1. Coba ambil dari antrean pending payments
            try {
                $resPending = $api->getPendingPayments();
                if ($resPending->successful() && is_array($resPending->json('data'))) {
                    foreach ($resPending->json('data') as $p) {
                        if ((int) ($p['id'] ?? 0) === $payment) {
                            $targetPayment = $p;
                            $proposalDetail = $p['proposal'] ?? null;
                            break;
                        }
                    }
                }
            } catch (\Throwable) {
                // Lanjut coba ambil detail proposal
            }

            // 2. Coba ambil detail proposal untuk data pendukung atau jika payment belum ditemukan
            try {
                $resProp = $api->get("proposals/{$proposal}");
                if ($resProp->successful() && is_array($resProp->json())) {
                    $propData = $resProp->json();
                    if (!$proposalDetail) {
                        $proposalDetail = $propData;
                    } else {
                        $proposalDetail = array_merge($propData, $proposalDetail);
                    }

                    if (!$targetPayment && !empty($propData['payments']) && is_array($propData['payments'])) {
                        foreach ($propData['payments'] as $pay) {
                            if ((int) ($pay['id'] ?? 0) === $payment) {
                                $targetPayment = $pay;
                                $targetPayment['proposal'] = $proposalDetail;
                                break;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                if (!$targetPayment) {
                    $error = 'Gagal memuat rincian pembayaran dari server: ' . $e->getMessage();
                }
            }

            // 3. Fallback sampel jika backend offline atau simulasi data demo
            if (!$targetPayment) {
                $targetPayment = [
                    'id'                 => $payment,
                    'proposal_id'        => $proposal,
                    'term_sequence'      => 2,
                    'amount'             => 25200000,
                    'payment_date'       => '2026-11-01',
                    'payment_method'     => 'Transfer Bank (Giro Mandiri)',
                    'transfer_reference' => 'TRF20261101-089',
                    'proof_url'          => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=800&auto=format&fit=crop&q=80',
                    'status'             => 'PENDING',
                    'verification_notes' => null,
                    'created_at'         => '2026-11-01 14:32:00',
                    'proposal'           => [
                        'id'           => $proposal,
                        'project_code' => 'PRSL-2025-001',
                        'title'        => 'Sistem Informasi Manajemen Teaching Factory',
                        'client'       => [
                            'name'         => 'Budi Santoso',
                            'email'        => 'budi.santoso@majubersamasentosa.co.id',
                            'company_name' => 'PT Maju Bersama Sentosa',
                            'phone'        => '0812-3456-7890',
                        ],
                    ],
                ];
                $proposalDetail = $targetPayment['proposal'];
            }

            // Normalisasi URL berkas bukti
            $proofUrl = $targetPayment['proof_url'] ?? null;
            if ($proofUrl && !str_starts_with($proofUrl, 'http')) {
                $baseUrl = rtrim(config('services.backend_api.url') ?? env('BACKEND_API_URL', 'http://127.0.0.1:8000/api/v1'), '/');
                $baseHost = preg_replace('#/api/v1$#', '', $baseUrl);
                $targetPayment['proof_url'] = $baseHost . '/storage/' . ltrim($proofUrl, '/');
            }

            return view('pengurus-tefa.verifikasi-bukti', [
                'user'     => session('user'),
                'proposal' => $proposalDetail ?? ($targetPayment['proposal'] ?? []),
                'payment'  => $targetPayment,
                'error'    => $error,
            ]);
        })->whereNumber('proposal')->whereNumber('payment')->name('payments.verifikasi');

        Route::post('/proposals/{proposal}/payments/{payment}/verify', function (Request $request, ApiService $api, int $proposal, int $payment) {
            $validated = $request->validate([
                'status'             => ['required', 'in:VERIFIED,REJECTED'],
                'verification_notes' => ['nullable', 'string', 'max:1000'],
            ]);

            try {
                $response = $api->verifyPayment($proposal, $payment, $validated);

                if ($response->successful()) {
                    $msg = $validated['status'] === 'VERIFIED'
                        ? 'Pembayaran berhasil diverifikasi (Status Lunas).'
                        : 'Bukti pembayaran telah ditolak dengan catatan.';
                    return redirect()->route('pengurus-tefa.keuangan')->with('success', $msg);
                }

                return back()
                    ->withInput()
                    ->with('error', 'Gagal memverifikasi: ' . ($response->json('message') ?? 'Terjadi kesalahan backend.'));
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Tidak dapat terhubung ke server backend: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->whereNumber('payment')->name('payments.verify');

        // ==========================================
        // UAT (User Acceptance Testing) - Pengurus TeFa
        // ==========================================
        Route::get('/uat', function (ApiService $api) {
            $proposals = [];
            $error = null;

            try {
                $response = $api->getUatProposals();
                if ($response->successful()) {
                    $proposals = $response->json('data') ?? $response->json() ?? [];
                } else {
                    $error = $response->json('message') ?? 'Daftar proyek UAT tidak dapat dimuat.';
                }
            } catch (\Throwable $e) {
                $error = 'Gagal mengambil data proyek: ' . $e->getMessage();
            }


            $total = count($proposals);
            $testing = 0;
            $waitingReview = 0;
            $needsImprovement = 0;

            foreach ($proposals as $p) {
                $stId = (int) ($p['status_id'] ?? 0);
                $uatData = $p['uat'] ?? null;
                if ($stId === 8 || strtolower($p['status']['name'] ?? $p['status'] ?? '') === 'testing') {
                    $testing++;
                }
                if (($uatData['review_status'] ?? null) === 'PENDING_REVIEW') {
                    $waitingReview++;
                }
                if (($uatData['review_status'] ?? null) === 'NEEDS_IMPROVEMENT') {
                    $needsImprovement++;
                }
            }

            return view('pengurus-tefa.uat-index', [
                'user'      => session('user'),
                'proposals' => $proposals,
                'stats'     => [
                    'total'           => $total,
                    'testing'         => $testing,
                    'waiting_review'  => $waitingReview,
                    'needs_improvement' => $needsImprovement,
                ],
                'error'     => $error,
            ]);
        })->name('uat');

        Route::get('/proposals/{proposal}/uat', function (ApiService $api, int $proposal) {
            $detail = [];
            $uat = null;
            $members = [];
            $error = null;

            try {
                $pRes = $api->get("proposals/{$proposal}");
                if ($pRes->successful() && is_array($pRes->json())) {
                    $detail = $pRes->json();
                } else {
                    $error = $pRes->json('message') ?? 'Data proyek tidak dapat dimuat.';
                }

                $uatRes = $api->getUat($proposal);
                if ($uatRes->successful()) {
                    $uat = $uatRes->json('data');
                } elseif ($uatRes->status() !== 404) {
                    $error = $uatRes->json('message') ?? 'Data UAT tidak dapat dimuat.';
                }

                $teamRes = $api->get("proposals/{$proposal}/team");
                if ($teamRes->successful() && is_array($teamRes->json())) {
                    $members = $teamRes->json();
                } elseif ($teamRes->failed()) {
                    $error = $teamRes->json('message') ?? 'Anggota tim proyek tidak dapat dimuat.';
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
            }

            return view('pengurus-tefa.uat-review', [
                'user'     => session('user'),
                'proposal' => $detail,
                'uat'      => $uat,
                'members'  => $members,
                'error'    => $error,
            ]);
        })->whereNumber('proposal')->name('uat.detail');

        Route::post('/proposals/{proposal}/uat/{uat}/review', function (Request $request, ApiService $api, int $proposal, int $uat) {
            $validated = $request->validate([
                'decision' => ['required', 'in:APPROVED,NEEDS_IMPROVEMENT'],
                'review_notes' => ['nullable', 'string'],
                'corrections' => ['required_if:decision,NEEDS_IMPROVEMENT', 'array', 'min:1'],
                'corrections.*.item_id' => ['required', 'integer', 'distinct'],
                'corrections.*.note' => ['required', 'string', 'max:5000'],
                'corrections.*.assigned_student_id' => ['required', 'integer'],
                'corrections.*.due_date' => ['nullable', 'date'],
            ]);

            try {
                $response = $api->reviewUat($proposal, $uat, $validated);
                if ($response->successful()) {
                    return redirect()
                        ->route('pengurus-tefa.uat.detail', $proposal)
                        ->with('success', $validated['decision'] === 'APPROVED'
                            ? 'Hasil UAT disetujui. Klien dapat mengesahkan hasilnya.'
                            : 'Checklist perbaikan telah diteruskan kepada mahasiswa.');
                }

                return back()->withInput()->with('error', $response->json('message') ?? 'Pemeriksaan UAT gagal disimpan.');
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Tidak dapat terhubung ke server backend: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->whereNumber('uat')->name('uat.review');

        Route::post('/proposals/{proposal}/status/testing', function (Request $request, ApiService $api, int $proposal) {
            try {
                $response = $api->setProposalTesting($proposal);

                if ($response->successful()) {
                    if ($request->input('next') === 'create_uat') {
                        return redirect()
                            ->route('pengurus-tefa.uat.detail', $proposal)
                            ->with('success', 'Proyek siap diuji. Klien dapat mengirim hasil UAT dari halaman proyek.');
                    }

                    return back()->with('success', 'Proyek telah ditandai siap diuji oleh Klien.');
                }

                return back()->with('error', 'Gagal memperbarui status ke testing: ' . ($response->json('message') ?? 'Terjadi kesalahan backend.'));
            } catch (\Throwable $e) {
                return back()->with('error', 'Tidak dapat terhubung ke server backend: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->name('uat.status-testing');

        Route::get('/monitoring', function (ApiService $api) {
            $dashboardData = [];
            $error = null;

            try {
                $response = $api->get('tefa/dashboard');
                if ($response->successful()) {
                    $dashboardData = $response->json('data', []);
                } else {
                    $error = $response->json('message') ?? 'Gagal memuat data monitoring.';
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke backend: ' . $e->getMessage();
            }

            return view('pengurus-tefa.monitoring', [
                'user'      => session('user'),
                'dashboard' => $dashboardData,
                'error'     => $error,
            ]);
        })->name('monitoring');

        // ── Maintenance & Garansi (Pengurus TeFa) ─────────────────
        Route::get('/maintenance', function (ApiService $api) {
            $projects = [];
            $error    = null;

            try {
                $response = $api->get('maintenance/proposals');
                $raw      = $response->json('data') ?? $response->json();
                if ($response->successful() && is_array($raw)) {
                    $projects = array_values(array_filter($raw, 'is_array'));
                } else {
                    $error = $response->json('message') ?? 'Data maintenance tidak dapat dimuat.';
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke backend: ' . $e->getMessage();
            }

            return view('pengurus-tefa.maintenance', [
                'user'     => session('user'),
                'projects' => $projects,
                'error'    => $error,
            ]);
        })->name('maintenance');

        Route::get('/maintenance/{proposal}', function (ApiService $api, int $proposal) {
            $proposalData = [];
            $tickets      = [];
            $extReqs      = [];
            $error        = null;

            try {
                $pRes = $api->get("proposals/{$proposal}");
                if ($pRes->successful() && is_array($pRes->json())) {
                    $proposalData = $pRes->json();
                } else {
                    $error = $pRes->json('message') ?? 'Data proyek tidak ditemukan.';
                }

                if (!$error) {
                    $tRes = $api->get("proposals/{$proposal}/maintenance-tickets");
                    if ($tRes->successful()) {
                        $raw = $tRes->json('data') ?? $tRes->json();
                        $tickets = is_array($raw) ? array_values(array_filter($raw, 'is_array')) : [];
                    }

                    $eRes = $api->get("proposals/{$proposal}/warranty-extensions");
                    if ($eRes->successful()) {
                        $raw = $eRes->json('data') ?? $eRes->json();
                        $extReqs = is_array($raw) ? array_values(array_filter($raw, 'is_array')) : [];
                    }
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke backend: ' . $e->getMessage();
            }

            return view('pengurus-tefa.maintenance-detail', [
                'user'     => session('user'),
                'proposal' => $proposalData,
                'tickets'  => $tickets,
                'extReqs'  => $extReqs,
                'error'    => $error,
            ]);
        })->whereNumber('proposal')->name('maintenance.detail');

        // Tiket inisiatif dari tefa (store)
        Route::post('/maintenance/{proposal}/tickets', function (Request $request, ApiService $api, int $proposal) {
            $validated = $request->validate([
                'title'       => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'priority'    => ['nullable', 'in:low,medium,high'],
                'image'       => ['nullable', 'image', 'max:10240'],
            ]);

            try {
                $payload = [
                    'title'       => $validated['title'],
                    'description' => $validated['description'] ?? null,
                    'priority'    => $validated['priority'] ?? 'medium',
                ];

                if ($request->hasFile('image')) {
                    $response = $api->sendWithFile(
                        "proposals/{$proposal}/maintenance-tickets",
                        'image',
                        $request->file('image'),
                        $payload
                    );
                } else {
                    $response = $api->post("proposals/{$proposal}/maintenance-tickets", $payload);
                }

                if ($response->successful()) {
                    return redirect()
                        ->route('pengurus-tefa.maintenance.detail', $proposal)
                        ->with('success', 'Catatan maintenance berhasil ditambahkan. Klien telah dinotifikasi.');
                }

                return back()->withInput()->with('error', $response->json('message') ?? 'Gagal menambahkan catatan.');
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Tidak dapat terhubung ke server: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->name('maintenance.ticket.store');

        // Update tiket (balas + status)
        Route::patch('/maintenance/{proposal}/tickets/{ticket}', function (Request $request, ApiService $api, int $proposal, int $ticket) {
            $validated = $request->validate([
                'status'           => ['nullable', 'in:open,in_progress,resolved'],
                'resolution_notes' => ['nullable', 'string'],
                'priority'         => ['nullable', 'in:low,medium,high'],
                'resolution_image' => ['nullable', 'image', 'max:10240'],
            ]);

            try {
                $payload = [
                    'status'           => $validated['status'] ?? null,
                    'resolution_notes' => $validated['resolution_notes'] ?? null,
                    'priority'         => $validated['priority'] ?? null,
                ];

                if ($request->hasFile('resolution_image')) {
                    $response = $api->sendWithFile(
                        "proposals/{$proposal}/maintenance-tickets/{$ticket}",
                        'resolution_image',
                        $request->file('resolution_image'),
                        $payload,
                        'PATCH'
                    );
                } else {
                    $response = $api->patch("proposals/{$proposal}/maintenance-tickets/{$ticket}", $payload);
                }

                if ($response->successful()) {
                    return redirect()
                        ->route('pengurus-tefa.maintenance.detail', $proposal)
                        ->with('success', 'Tiket berhasil diperbarui dan klien telah dinotifikasi.');
                }

                return back()->with('error', $response->json('message') ?? 'Gagal memperbarui tiket.');
            } catch (\Throwable $e) {
                return back()->with('error', 'Tidak dapat terhubung ke server: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->whereNumber('ticket')->name('maintenance.ticket.update');

        // Review extension request
        Route::patch('/maintenance/{proposal}/extensions/{extReq}/review', function (Request $request, ApiService $api, int $proposal, int $extReq) {
            $validated = $request->validate([
                'decision'               => ['required', 'in:approved,rejected'],
                'price'                  => ['nullable', 'numeric', 'min:0'],
                'payment_deadline_hours' => ['nullable', 'integer', 'min:1'],
                'reviewer_notes'         => ['nullable', 'string', 'max:1000'],
            ]);

            try {
                $response = $api->patch("proposals/{$proposal}/warranty-extensions/{$extReq}/review", $validated);

                if ($response->successful()) {
                    $msg = $validated['decision'] === 'approved'
                        ? 'Penawaran harga maintenance berhasil dikirim ke klien. Menunggu persetujuan & pembayaran klien.'
                        : 'Permintaan perpanjangan ditolak. Klien telah dinotifikasi.';
                    return redirect()
                        ->route('pengurus-tefa.maintenance.detail', $proposal)
                        ->with('success', $msg);
                }

                return back()->with('error', $response->json('message') ?? 'Gagal memproses review.');
            } catch (\Throwable $e) {
                return back()->with('error', 'Tidak dapat terhubung ke server: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->whereNumber('extReq')->name('maintenance.ext.review');

    });
});
