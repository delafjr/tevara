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

    Route::post('/logout', function () {
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

        $viewMap = [
            'klien'         => 'klien.dashboard',
            'pengurus_tefa' => 'pengurus-tefa.dashboard',
            'pm'            => 'pm.dashboard',
            'dosen'         => 'pm.dashboard',
            'mahasiswa'     => 'mahasiswa.dashboard',
            'jurusan'       => 'jurusan.dashboard',
        ];

        $view = $viewMap[$activeRole] ?? 'dashboard';
        return view($view, ['user' => $user, 'currentRole' => $activeRole, 'dashboard' => $dashboardData]);
    })->name('dashboard');

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

    // Klien: Ajukan Proyek Baru & Projek Saya
    Route::middleware('auth.api:klien')->prefix('klien')->name('klien.')->group(function () {
        Route::get('/projek-saya', function (ApiService $api) {
            $proposals = [];
            $error = null;

            try {
                $response = $api->get('proposals/mine');
                if ($response->successful() && is_array($response->json())) {
                    $proposals = $response->json();
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
            }

            return view('klien.projek-saya', [
                'user'      => session('user'),
                'proposals' => $proposals,
                'error'     => $error,
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

        Route::post('/projek-saya/{proposal}/revisi', function (Request $request, ApiService $api, int $proposal) {
            $request->merge([
                'modul' => json_decode($request->input('modul', '[]'), true) ?: [],
            ]);

            $validated = $request->validate([
                'judul_proyek'      => ['required', 'string', 'max:255'],
                'kategori_id'       => ['required', 'integer', 'min:1'],
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

            $payload = [
                'title'            => $validated['judul_proyek'],
                'background'       => $validated['ringkasan'],
                'target_audience'  => $validated['target_pengguna'],
                'category_id'      => (int) $validated['kategori_id'],
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

        Route::get('/tagihan', function (ApiService $api) {
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
                                $totalBudget = (float) ($detail['estimated_budget'] ?? 0);
                                $totalPaid = 0;
                                $termins = [];
                                $hasOpen = false;

                                foreach ($payments as $pay) {
                                    $amount = (float) ($pay['amount'] ?? 0);
                                    $status = strtoupper($pay['status'] ?? 'PENDING');
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
                                        $totalPaid += $amount;
                                    } elseif ($status === 'REJECTED') {
                                        $statusLabel = 'DITOLAK (Upload Ulang)';
                                        $statusType = 'rejected';
                                        $canUpload = true;
                                        $hasOpen = true;
                                    } elseif ($proofUrl) {
                                        $statusLabel = 'DALAM VERIFIKASI';
                                        $statusType = 'verifying';
                                        $canUpload = false;
                                    } else {
                                        $hasOpen = true;
                                    }

                                    $termins[] = [
                                        'id'                 => $pay['id'],
                                        'proposal_id'        => $pId,
                                        'term_sequence'      => $pay['term_sequence'] ?? 1,
                                        'name'               => 'Termin ' . ($pay['term_sequence'] ?? 1),
                                        'desc'               => !empty($pay['due_date']) ? 'Jatuh tempo: ' . \Carbon\Carbon::parse($pay['due_date'])->translatedFormat('d M Y') : 'Pembayaran Termin ' . ($pay['term_sequence'] ?? 1),
                                        'nominal'            => 'Rp ' . number_format($amount, 0, ',', '.'),
                                        'amount'             => $amount,
                                        'channel'            => $pay['payment_method'] ?? 'Transfer Bank (Giro / VA)',
                                        'channel_icon'       => 'bank',
                                        'status'             => $statusType,
                                        'raw_status'         => $status,
                                        'status_label'       => $statusLabel,
                                        'due_date'           => !empty($pay['due_date']) ? 'Jatuh Tempo: ' . \Carbon\Carbon::parse($pay['due_date'])->translatedFormat('d M Y') : null,
                                        'can_download'       => $canDownload,
                                        'can_upload'         => $canUpload,
                                        'proof_url'          => $proofUrl,
                                        'verification_notes' => $pay['verification_notes'] ?? null,
                                    ];
                                }

                                $paidPercent = $totalBudget > 0 ? round(($totalPaid / $totalBudget) * 100, 1) : 0;

                                $projectsBilling[] = [
                                    'id'              => $detail['proposal_code'] ?? ('PRJ-' . $pId),
                                    'proposal_id'     => $pId,
                                    'spk'             => 'SPK/TEFA/' . ($detail['proposal_code'] ?? $pId),
                                    'spk_status'      => $detail['status']['name'] ?? $detail['status'] ?? 'Aktif',
                                    'spk_status_type' => 'success',
                                    'title'           => $detail['title'] ?? 'Proyek Tanpa Judul',
                                    'period'          => 'Target: ' . (!empty($detail['desired_deadline']) ? \Carbon\Carbon::parse($detail['desired_deadline'])->translatedFormat('d M Y') : '-'),
                                    'total_value'     => 'Rp ' . number_format($totalBudget, 0, ',', '.'),
                                    'paid_percent'    => $paidPercent,
                                    'paid_status'     => $paidPercent . '% Terbayar',
                                    'has_open'        => $hasOpen,
                                    'termins'         => $termins,
                                ];
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
            }

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

        Route::get('/tagihan-pembayaran', fn () => redirect()->route('klien.tagihan'));
        Route::get('/pembayaran', fn () => redirect()->route('klien.tagihan'));

        Route::get('/ajukan-proyek', function (ApiService $api) {
            return view('klien.ajukan-proyek', [
                'user'       => session('user'),
                'company'    => $api->getCompanyProfile(),
                'categories' => $api->getCategories(),
            ]);
        })->name('ajukan-proyek');

        Route::post('/ajukan-proyek', function (Request $request, ApiService $api) {
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
                'ringkasan'         => ['required', 'string', 'min:100', 'max:500'],
                'target_pengguna'   => ['required', 'string', 'max:1000'],
                'deadline'          => ['required', 'date', 'after_or_equal:' . now()->addDays(30)->toDateString()],
                'anggaran'          => ['required', 'numeric', 'min:0'],
                'modul'             => ['required', 'array', 'min:1'],
                'modul.*.judul'     => ['required', 'string', 'max:255'],
                'modul.*.deskripsi' => ['nullable', 'string', 'max:1000'],
            ]);

            // Modul dipisah ke field modules sesuai kontrak POST /proposals.
            $modules = collect($validated['modul'] ?? [])
                ->filter(fn ($m) => !empty($m['judul']))
                ->map(fn ($m) => [
                    'title'       => $m['judul'],
                    'description' => $m['deskripsi'] ?? null,
                ])
                ->values()
                ->all();

            $user = session('user', []);

            $response = $api->post('proposals', [
                'client_id'        => (int) ($user['id'] ?? 0),
                'title'            => $validated['judul_proyek'],
                'background'       => $validated['ringkasan'],
                'target_audience'  => $validated['target_pengguna'],
                'category_id'      => (int) $validated['kategori_id'],
                'desired_deadline' => $validated['deadline'],
                'estimated_budget' => (int) $validated['anggaran'],
                'modules'          => $modules,
            ]);

            if ($response->successful()) {
                return redirect()
                    ->route('klien.ajukan-proyek')
                    ->with('success', 'Pengajuan proyek berhasil dikirim ke administrators TEVARA. Kode proposal: ' . ($response->json('proposal_code') ?? '-'));
            }

            return back()
                ->withInput()
                ->with('error', 'Pengajuan gagal: ' . ($response->json('message') ?? 'terjadi kesalahan pada server TEVARA.'));
        })->name('ajukan-proyek.store');
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

    Route::get('/mahasiswa/project', fn () => view('mahasiswa.project', ['user' => session('user')]))->name('mahasiswa.project');
    Route::get('/mahasiswa/task', fn () => view('mahasiswa.task', ['user' => session('user')]))->name('mahasiswa.task');

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
    Route::middleware('auth.api:pengurus_tefa')->prefix('pengurus-tefa')->name('pengurus-tefa.')->group(function () {
        Route::get('/projek', fn () => view('pengurus-tefa.projek', ['user' => session('user')]))->name('projek');
        Route::get('/klien-mitra', fn () => view('pengurus-tefa.klien-mitra', ['user' => session('user')]))->name('klien-mitra');
        Route::get('/dosen', fn () => view('pengurus-tefa.dosen', ['user' => session('user')]))->name('dosen');
        Route::get('/mahasiswa', fn () => view('pengurus-tefa.mahasiswa', ['user' => session('user')]))->name('mahasiswa');
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
                    $error = $response->json('message') ?? 'Detail proposal tidak ditemukan.';
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
        Route::get('/keuangan', function (ApiService $api) {
            $pendingPayments = [];
            $allProposals = [];
            $error = null;

            try {
                $resPending = $api->getPendingPayments();
                if ($resPending->successful() && is_array($resPending->json('data'))) {
                    $pendingPayments = $resPending->json('data');
                }
            } catch (\Throwable $e) {
                $error = 'Gagal memuat antrean verifikasi pembayaran: ' . $e->getMessage();
            }

            try {
                $resAll = $api->get('proposals');
                if ($resAll->successful() && is_array($resAll->json())) {
                    $allProposals = $resAll->json();
                }
            } catch (\Throwable) {
                // Biarkan kosong jika tidak dapat dimuat
            }

            return view('pengurus-tefa.keuangan', [
                'user'            => session('user'),
                'pendingPayments' => $pendingPayments,
                'allProposals'    => $allProposals,
                'error'           => $error,
            ]);
        })->name('keuangan');

        Route::post('/proposals/{proposal}/payments', function (Request $request, ApiService $api, int $proposal) {
            $validated = $request->validate([
                'term_sequence' => ['required', 'integer', 'min:1'],
                'amount'        => ['required', 'numeric', 'min:0'],
                'due_date'      => ['nullable', 'date'],
            ], [
                'term_sequence.required' => 'Nomor termin wajib diisi.',
                'amount.required'        => 'Nominal tagihan termin wajib diisi.',
            ]);

            try {
                $response = $api->createPaymentTerm($proposal, $validated);

                if ($response->successful()) {
                    return back()->with('success', 'Termin pembayaran berhasil dibuat.');
                }

                return back()
                    ->withInput()
                    ->with('error', 'Gagal membuat termin: ' . ($response->json('message') ?? 'Terjadi kesalahan pada backend.'));
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Tidak dapat terhubung ke server backend: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->name('payments.store');

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
                    return back()->with('success', $msg);
                }

                return back()
                    ->with('error', 'Gagal memverifikasi: ' . ($response->json('message') ?? 'Terjadi kesalahan backend.'));
            } catch (\Throwable $e) {
                return back()
                    ->with('error', 'Tidak dapat terhubung ke server backend: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->whereNumber('payment')->name('payments.verify');
        Route::get('/monitoring', fn () => view('pengurus-tefa.monitoring', ['user' => session('user')]))->name('monitoring');
    });
});
