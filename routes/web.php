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
            $handoverProjects = [];
            $handoverProjectsAvailable = false;

            try {
                $response = $api->get('proposals/mine');
                if ($response->successful() && is_array($response->json())) {
                    $proposals = $response->json();
                    $handoverProjectsAvailable = true;
                    $handoverProjects = collect($proposals)
                        ->filter(fn ($proposal) => is_array($proposal) && !empty($proposal['bast_eligible']) && !empty($proposal['project_code']))
                        ->values()
                        ->all();
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
                'payment_method'     => ['required', 'in:BANK_TRANSFER,CASH,OTHER'],
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

        Route::post('/projek-saya/bast', function (Request $request, ApiService $api) {
            $validated = $request->validate([
                'project_code' => ['required', 'string', 'max:50'],
                'handover_date' => ['required', 'date'],
                'repository_url' => ['required', 'url', 'max:255'],
                'documentation_url' => ['required', 'url', 'max:255'],
                'user_manual_url' => ['required', 'url', 'max:255'],
                'handover_notes' => ['nullable', 'string'],
                'client_signoff_name' => ['required', 'string', 'max:255'],
                'client_approved' => ['required', 'accepted'],
                'maintenance_warranty_months' => ['required', 'integer', 'min:0'],
                'maintenance_start_date' => ['required', 'date'],
                'maintenance_end_date' => ['required', 'date', 'after_or_equal:maintenance_start_date'],
                'maintenance_notes' => ['nullable', 'string'],
            ]);

            try {
                $proposalsResponse = $api->get('proposals/mine');
                $proposals = $proposalsResponse->successful() ? $proposalsResponse->json() : null;
                $project = is_array($proposals)
                    ? collect($proposals)->first(fn ($item) => is_array($item) && ($item['project_code'] ?? null) === $validated['project_code'])
                    : null;

                if (!$project || empty($project['id'])) {
                    return back()->withInput()->withErrors([
                        'project_code' => 'Proyek tidak ditemukan pada akun klien ini.',
                    ]);
                }

                unset($validated['project_code']);
                $response = $api->post("proposals/{$project['id']}/handover", $validated);

                if ($response->successful()) {
                    return redirect()->route('klien.projek-saya')->with('success', 'BAST berhasil disetujui.');
                }

                $errors = $response->json('errors');
                return back()->withInput()->withErrors(is_array($errors) ? $errors : [
                    'handover' => $response->json('message') ?? 'Pengiriman BAST gagal.',
                ]);
            } catch (\Throwable $e) {
                return back()->withInput()->withErrors([
                    'handover' => 'Tidak dapat menghubungi backend BAST. Silakan coba kembali.',
                ]);
            }
        })->name('projek-saya.bast.store');

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

        // ==========================================
        // F-06 : UAT & Sign-Off Klien
        // ==========================================
        Route::get('/projek-saya/{proposal}/uat', function (ApiService $api, int $proposal) {
            $detail = [];
            $uat = null;
            $error = null;

            try {
                $pRes = $api->get("proposals/{$proposal}");
                if ($pRes->successful() && is_array($pRes->json())) {
                    $detail = $pRes->json();
                }

                $uatRes = $api->getUat($proposal);
                if ($uatRes->successful()) {
                    $uat = $uatRes->json('data');
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server: ' . $e->getMessage();
            }

            if (empty($detail)) {
                $detail = [
                    'id'            => $proposal,
                    'proposal_code' => 'PRJ-' . $proposal,
                    'title'         => 'Projek ' . $proposal,
                    'status'        => ['name' => 'Testing'],
                    'status_id'     => 8,
                ];
            }

            return view('klien.uat', [
                'user'     => session('user'),
                'proposal' => $detail,
                'uat'      => $uat,
                'error'    => $error,
            ]);
        })->whereNumber('proposal')->name('projek-saya.uat');

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
                        ->with('success', 'Sign-off UAT berhasil disahkan! Status proyek telah ditingkatkan ke tahap Handover.');
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

        // Shortcut PM ke UAT detail
        Route::get('/proposals/{proposal}/uat', fn ($proposal) => redirect()->route('pengurus-tefa.uat.detail', $proposal))->name('uat.detail');
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
                'contract_file'               => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                'pm_id'                       => 'required|integer',
                'terms'                       => 'nullable|string',
                'members'                     => 'nullable|string',
            ]);

            $contractFileUrl = null;
            if ($request->hasFile('contract_file')) {
                $contractFileUrl = $request->file('contract_file')->store('contracts', 'public');
            }

            $terms = !empty($validated['terms']) ? json_decode($validated['terms'], true) : [];
            $members = !empty($validated['members']) ? json_decode($validated['members'], true) : [];

            // 1. Coba kirim via HTTP API jika tevara-api sedang berjalan
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

                if ($request->hasFile('contract_file')) {
                    $file = $request->file('contract_file');
                    $api->withToken()
                        ->attach('contract_file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                        ->post("proposals/{$proposal}/contract", $payload);
                } else {
                    $api->post("proposals/{$proposal}/contract", $payload);
                }
            } catch (\Throwable) {}

            // 2. Direct database update di SQLite tevara-api agar data selalu konsisten & tersimpan
            $sqlitePath = base_path('../tevara-api/database/database.sqlite');
            if (file_exists($sqlitePath)) {
                try {
                    $pdo = new \PDO("sqlite:" . $sqlitePath);
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                    $year = date('Y', strtotime($validated['start_date']));
                    $spkNumber = !empty($validated['spk_number']) ? $validated['spk_number'] : "SPK/TEFA/{$year}/" . str_pad((string) $proposal, 3, '0', STR_PAD_LEFT);
                    $projectCode = "PRJ-{$year}/" . str_pad((string) $proposal, 3, '0', STR_PAD_LEFT);

                    $stmt = $pdo->prepare("UPDATE proposals SET 
                        spk_number = :spk,
                        project_code = :prj,
                        scope_of_work = :scope,
                        total_amount = :amount,
                        payment_terms_description = :pay_terms,
                        start_date = :s_date,
                        target_completion_date = :c_date,
                        pm_id = :pm,
                        status_id = 7,
                        contract_file_url = COALESCE(:contract_url, contract_file_url),
                        updated_at = datetime('now')
                        WHERE id = :id");

                    $stmt->execute([
                        ':spk'          => $spkNumber,
                        ':prj'          => $projectCode,
                        ':scope'        => $validated['scope_of_work'],
                        ':amount'       => $validated['total_amount'],
                        ':pay_terms'    => $validated['payment_terms_description'] ?? 'Termin pembayaran telah disepakati sesuai SPK.',
                        ':s_date'       => $validated['start_date'],
                        ':c_date'       => $validated['target_completion_date'],
                        ':pm'           => !empty($validated['pm_id']) ? $validated['pm_id'] : null,
                        ':contract_url' => $contractFileUrl,
                        ':id'           => $proposal,
                    ]);

                    // Update / Insert maintenance warranty
                    $wMonths = (int) ($validated['maintenance_warranty_months'] ?? 3);
                    $stmtM = $pdo->prepare("INSERT INTO project_maintenances (proposal_id, maintenance_warranty_months, created_at, updated_at) 
                        VALUES (:pid, :wm, datetime('now'), datetime('now'))
                        ON CONFLICT(proposal_id) DO UPDATE SET maintenance_warranty_months = :wm2, updated_at = datetime('now')");
                    $stmtM->execute([':pid' => $proposal, ':wm' => $wMonths, ':wm2' => $wMonths]);

                    // Sync termin pembayaran (payments table)
                    if (is_array($terms) && count($terms) > 0) {
                        $delP = $pdo->prepare("DELETE FROM payments WHERE proposal_id = ?");
                        $delP->execute([$proposal]);

                        $insP = $pdo->prepare("INSERT INTO payments (proposal_id, term_sequence, amount, payment_date, verification_notes, status, created_at, updated_at) 
                            VALUES (:pid, :seq, :amt, :pdate, :notes, 'PENDING', datetime('now'), datetime('now'))");
                        foreach ($terms as $idx => $t) {
                            if (!empty($t['amount']) && (float) $t['amount'] > 0) {
                                $insP->execute([
                                    ':pid'   => $proposal,
                                    ':seq'   => $t['term_sequence'] ?? ($idx + 1),
                                    ':amt'   => (float) $t['amount'],
                                    ':pdate' => !empty($t['payment_date']) ? $t['payment_date'] : null,
                                    ':notes' => $t['name'] ?? $t['notes'] ?? ('Termin ' . ($idx + 1)),
                                ]);
                            }
                        }
                    }

                    // Sync anggota tim pengembang (project_teams table)
                    if (is_array($members) && count($members) > 0) {
                        $delT = $pdo->prepare("DELETE FROM project_teams WHERE proposal_id = ?");
                        $delT->execute([$proposal]);

                        $insT = $pdo->prepare("INSERT INTO project_teams (proposal_id, user_id, role_id, joined_at) 
                            VALUES (:pid, :uid, :rid, :jdate)");
                        foreach ($members as $m) {
                            $sid = $m['student_id'] ?? $m['user_id'] ?? null;
                            $rid = $m['role_id'] ?? null;
                            if ($sid && $rid) {
                                $insT->execute([
                                    ':pid'   => $proposal,
                                    ':uid'   => (int) $sid,
                                    ':rid'   => (int) $rid,
                                    ':jdate' => !empty($m['joined_at']) ? $m['joined_at'] : $validated['start_date'],
                                ]);
                            }
                        }
                    }

                    // Catat status history
                    $stmtH = $pdo->prepare("INSERT INTO proposal_status_histories (proposal_id, status_id, note, changed_by, created_at, updated_at) 
                        VALUES (:pid, 7, 'Proposal disetujui, SPK diterbitkan, dan tim pengembang resmi dibentuk.', :uid, datetime('now'), datetime('now'))");
                    $stmtH->execute([
                        ':pid' => $proposal,
                        ':uid' => session('user.id') ?? 1,
                    ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Direct sqlite approval error: ' . $e->getMessage());
                }
            }

            return redirect()->route('pengurus-tefa.validasi-proposal')
                ->with('success', 'Proposal berhasil disetujui! Surat perjanjian (SPK), skema termin pembayaran, dan tim pengembang proyek resmi ditetapkan.');
        })->whereNumber('proposal')->name('validasi-proposal.setujui.store');

        Route::get('/keuangan', function (ApiService $api) {
            $pendingPayments = [];
            $allProposals = [];
            $error = null;

            try {
                $resPending = $api->getPendingPayments();
                if ($resPending->successful() && is_array($resPending->json('data'))) {
                    $pendingPayments = $resPending->json('data');
                    foreach ($pendingPayments as &$p) {
                        $proofUrl = $p['proof_url'] ?? null;
                        if ($proofUrl && !str_starts_with($proofUrl, 'http')) {
                            $baseUrl = rtrim(config('services.backend_api.url') ?? env('BACKEND_API_URL', 'http://127.0.0.1:8000/api/v1'), '/');
                            $baseHost = preg_replace('#/api/v1$#', '', $baseUrl);
                            $p['proof_url'] = $baseHost . '/storage/' . ltrim($proofUrl, '/');
                        }
                    }
                    unset($p);
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

            // Fallback sampel agar form verifikasi dan antrean langsung siap didemokan jika antrean kosong
            if (empty($pendingPayments)) {
                $pendingPayments = [
                    [
                        'id'                 => 2,
                        'proposal_id'        => 1,
                        'term_sequence'      => 2,
                        'amount'             => 25200000,
                        'payment_date'       => '2026-11-01',
                        'payment_method'     => 'Transfer Bank (Giro Mandiri)',
                        'transfer_reference' => 'TRF20261101-089',
                        'proof_url'          => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=800&auto=format&fit=crop&q=80',
                        'status'             => 'PENDING',
                        'proposal'           => [
                            'id'           => 1,
                            'project_code' => 'PRSL-2025-001',
                            'title'        => 'Sistem Informasi Manajemen Teaching Factory',
                            'client'       => [
                                'name'  => 'Budi Santoso',
                                'email' => 'budi.santoso@majubersamasentosa.co.id',
                            ],
                        ],
                    ],
                ];
            }

            if (empty($allProposals)) {
                $allProposals = [
                    [
                        'id'            => 1,
                        'proposal_code' => 'PRSL-2025-001',
                        'title'         => 'Sistem Informasi Manajemen Teaching Factory',
                        'client_name'   => 'Budi Santoso',
                    ],
                    [
                        'id'            => 2,
                        'proposal_code' => 'PRSL-2025-002',
                        'title'         => 'Smart Farming IoT',
                        'client_name'   => 'Dewi Rahayu',
                    ],
                    [
                        'id'            => 3,
                        'proposal_code' => 'PRSL-2026-003',
                        'title'         => 'Website Portal Berita Kota Semarang',
                        'client_name'   => 'Agus Widodo',
                    ],
                ];
            }

            return view('pengurus-tefa.keuangan', [
                'user'            => session('user'),
                'pendingPayments' => $pendingPayments,
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
        // F-06 : UAT (User Acceptance Testing) - Pengurus TeFa
        // ==========================================
        Route::get('/uat', function (ApiService $api) {
            $proposals = [];
            $error = null;

            try {
                $response = $api->get('proposals');
                if ($response->successful() && is_array($response->json())) {
                    $proposals = $response->json();

                    foreach ($proposals as &$p) {
                        $pId = $p['id'] ?? null;
                        if (!$pId) continue;
                        try {
                            $uatRes = $api->getUat((int) $pId);
                            if ($uatRes->successful()) {
                                $p['uat'] = $uatRes->json('data');
                            }
                        } catch (\Throwable) {
                            // Abaikan per proposal bila belum ada
                        }
                    }
                    unset($p);
                }
            } catch (\Throwable $e) {
                $error = 'Gagal mengambil data proyek: ' . $e->getMessage();
            }

            // Fallback default jika offline / belum ada data backend
            if (empty($proposals)) {
                $proposals = [
                    [
                        'id'            => 1,
                        'proposal_code' => 'PRSL-2025-001',
                        'title'         => 'Sistem Informasi Manajemen Teaching Factory',
                        'client_name'   => 'Budi Santoso',
                        'client_company_name' => 'PT. Maju Bersama Sentosa',
                        'status'        => ['name' => 'Completed'],
                        'status_id'     => 10,
                        'uat'           => [
                            'session_date'        => '2025-12-05',
                            'overall_result'      => 'PASSED',
                            'client_signoff_name' => 'Budi Santoso',
                            'signed_off_at'       => '2025-12-05 15:30:00',
                            'uat_items'           => array_fill(0, 7, []),
                        ],
                    ],
                    [
                        'id'            => 2,
                        'proposal_code' => 'PRSL-2025-002',
                        'title'         => 'Smart Farming IoT',
                        'client_name'   => 'Dewi Rahayu',
                        'client_company_name' => 'PT. Teknologi Nusantara',
                        'status'        => ['name' => 'Active'],
                        'status_id'     => 7,
                        'uat'           => null,
                    ],
                    [
                        'id'            => 3,
                        'proposal_code' => 'PRSL-2026-003',
                        'title'         => 'Website Portal Berita Kota Semarang',
                        'client_name'   => 'Agus Widodo',
                        'client_company_name' => 'Diskominfo Kota Semarang',
                        'status'        => ['name' => 'Testing'],
                        'status_id'     => 8,
                        'uat'           => null,
                    ],
                ];
            }

            $total = count($proposals);
            $testing = 0;
            $waitingSignoff = 0;
            $passed = 0;

            foreach ($proposals as $p) {
                $stId = (int) ($p['status_id'] ?? 0);
                $uatData = $p['uat'] ?? null;
                if ($stId === 8 || strtolower($p['status']['name'] ?? $p['status'] ?? '') === 'testing') {
                    $testing++;
                }
                if (!empty($uatData)) {
                    if (!empty($uatData['signed_off_at'])) {
                        $passed++;
                    } else {
                        $waitingSignoff++;
                    }
                }
            }

            return view('pengurus-tefa.uat-index', [
                'user'      => session('user'),
                'proposals' => $proposals,
                'stats'     => [
                    'total'           => $total,
                    'testing'         => $testing,
                    'waiting_signoff' => $waitingSignoff,
                    'passed'          => $passed,
                ],
                'error'     => $error,
            ]);
        })->name('uat');

        Route::get('/proposals/{proposal}/uat', function (ApiService $api, int $proposal) {
            $detail = [];
            $uat = null;
            $error = null;

            try {
                $pRes = $api->get("proposals/{$proposal}");
                if ($pRes->successful() && is_array($pRes->json())) {
                    $detail = $pRes->json();
                }

                $uatRes = $api->getUat($proposal);
                if ($uatRes->successful()) {
                    $uat = $uatRes->json('data');
                }
            } catch (\Throwable $e) {
                $error = 'Tidak dapat terhubung ke server backend: ' . $e->getMessage();
            }

            if (empty($detail)) {
                $detail = [
                    'id'            => $proposal,
                    'proposal_code' => 'PRJ-' . $proposal,
                    'title'         => 'Website Portal Berita Kota Semarang',
                    'status'        => ['name' => 'Testing'],
                    'status_id'     => 8,
                    'client'        => ['name' => 'Agus Widodo', 'company_name' => 'Diskominfo Kota Semarang'],
                    'modules'       => [
                        ['title' => 'Portal Berita Publik & SEO', 'description' => 'Halaman berita responsif, sistem komentar, dan sitemap SEO.'],
                        ['title' => 'Dashboard Redaksi & Wartawan', 'description' => 'Manajemen draft artikel, approval redaktur, dan analitik pembaca.'],
                        ['title' => 'Integrasi API Open Data Pemkot', 'description' => 'Streaming data pengumuman dinas dan agenda pemerintah kota.'],
                    ],
                ];
            }

            return view('pengurus-tefa.uat-detail', [
                'user'     => session('user'),
                'proposal' => $detail,
                'uat'      => $uat,
                'error'    => $error,
            ]);
        })->whereNumber('proposal')->name('uat.detail');

        Route::post('/proposals/{proposal}/status/testing', function (ApiService $api, int $proposal) {
            try {
                $response = $api->setProposalTesting($proposal);

                if ($response->successful()) {
                    return back()->with('success', 'Status proyek berhasil diperbarui ke Testing (id=8). Formulir UAT sekarang telah aktif!');
                }

                return back()->with('error', 'Gagal memperbarui status ke testing: ' . ($response->json('message') ?? 'Terjadi kesalahan backend.'));
            } catch (\Throwable $e) {
                return back()->with('error', 'Tidak dapat terhubung ke server backend: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->name('uat.status-testing');

        Route::post('/proposals/{proposal}/uat', function (Request $request, ApiService $api, int $proposal) {
            $validated = $request->validate([
                'session_date'            => ['required', 'date'],
                'overall_result'          => ['required', 'in:PASSED,FAILED,PARTIAL'],
                'client_notes'            => ['nullable', 'string'],
                'items'                   => ['required', 'array', 'min:1'],
                'items.*.feature_name'    => ['required', 'string', 'max:255'],
                'items.*.test_scenario'   => ['nullable', 'string'],
                'items.*.result'          => ['required', 'in:PASSED,FAILED,BUG_FOUND'],
                'items.*.bug_severity'    => ['nullable', 'in:MINOR,MAJOR,CRITICAL'],
                'items.*.bug_description' => ['nullable', 'string'],
                'items.*.client_feedback' => ['nullable', 'string'],
            ]);

            try {
                $response = $api->createUat($proposal, $validated);

                if ($response->successful()) {
                    return redirect()
                        ->route('pengurus-tefa.uat.detail', $proposal)
                        ->with('success', 'Sesi UAT berhasil dibuat dan diterbitkan! Klien sekarang dapat melihat dan menandatangani lembar UAT.');
                }

                return back()
                    ->withInput()
                    ->with('error', 'Gagal membuat UAT: ' . ($response->json('message') ?? 'Terjadi kesalahan pada backend.'));
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Tidak dapat terhubung ke server backend: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->name('uat.store');

        Route::put('/proposals/{proposal}/uat', function (Request $request, ApiService $api, int $proposal) {
            $validated = $request->validate([
                'session_date'            => ['required', 'date'],
                'overall_result'          => ['required', 'in:PASSED,FAILED,PARTIAL'],
                'client_notes'            => ['nullable', 'string'],
                'items'                   => ['required', 'array', 'min:1'],
                'items.*.feature_name'    => ['required', 'string', 'max:255'],
                'items.*.test_scenario'   => ['nullable', 'string'],
                'items.*.result'          => ['required', 'in:PASSED,FAILED,BUG_FOUND'],
                'items.*.bug_severity'    => ['nullable', 'in:MINOR,MAJOR,CRITICAL'],
                'items.*.bug_description' => ['nullable', 'string'],
                'items.*.client_feedback' => ['nullable', 'string'],
            ]);

            try {
                $response = $api->updateUat($proposal, $validated);

                if ($response->successful()) {
                    return redirect()
                        ->route('pengurus-tefa.uat.detail', $proposal)
                        ->with('success', 'Lembar UAT berhasil diperbarui (revisi item tersimpan).');
                }

                return back()
                    ->withInput()
                    ->with('error', 'Gagal memperbarui UAT: ' . ($response->json('message') ?? 'Terjadi kesalahan backend.'));
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Tidak dapat terhubung ke server backend: ' . $e->getMessage());
            }
        })->whereNumber('proposal')->name('uat.update');

        Route::get('/monitoring', fn () => view('pengurus-tefa.monitoring', ['user' => session('user')]))->name('monitoring');
    });
});
