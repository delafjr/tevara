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
            $handoverProjects = [];
            $handoverProjectsAvailable = false;

            try {
                $response = $api->get('proposals/mine');
                $proposals = $response->successful() ? $response->json() : null;
                if (is_array($proposals)) {
                    $handoverProjectsAvailable = true;
                    $handoverProjects = collect($proposals)
                        ->filter(fn ($proposal) => is_array($proposal) && !empty($proposal['bast_eligible']) && !empty($proposal['project_code']))
                        ->values()
                        ->all();
                }
            } catch (\Throwable) {
                $handoverProjects = [];
            }

            return view('klien.projek-saya', [
                'user' => session('user'),
                'handoverProjects' => $handoverProjects,
                'handoverProjectsAvailable' => $handoverProjectsAvailable,
            ]);
        })->name('projek-saya');
        Route::get('/proyek-saya', fn () => redirect()->route('klien.projek-saya'));

        Route::get('/tagihan', fn () => view('klien.tagihan', ['user' => session('user')]))
            ->name('tagihan');
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
        Route::get('/keuangan', fn () => view('pengurus-tefa.keuangan', ['user' => session('user')]))->name('keuangan');
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
        Route::get('/monitoring', fn () => view('pengurus-tefa.monitoring', ['user' => session('user')]))->name('monitoring');
    });
});
