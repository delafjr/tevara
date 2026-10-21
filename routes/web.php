<?php

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Main page (Public: accessible by guest or logged-in users)
Route::get('/', function (ApiService $api) {
    $apiUrl = $api->getBaseUrl() . '/users';
    $initialData = null;
    $initialError = null;

    try {
        if (session('api_token')) {
            $response = $api->get('/users');
            if ($response->successful()) {
                $initialData = $response->json();
            }
        }
    } catch (\Throwable $e) {
        $initialError = $e->getMessage();
    }

    return view('welcome', [
        'apiUrl'       => $apiUrl,
        'initialData'  => $initialData,
        'initialError' => $initialError,
    ]);
});

// Authentication routes
Route::prefix('auth')->group(function () {
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
                $allowedRoles = ApiService::getUserRoles($user);

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

// Protected routes (require valid session api_token and user)
Route::middleware('auth.api')->group(function () {

    // Main dashboard router
    Route::get('/dashboard', function () {
        $user = session('user');
        $allowedRoles = ApiService::getUserRoles($user);

        if (empty($allowedRoles)) {
            abort(403, 'Akun Anda tidak memiliki hak akses role.');
        }

        $activeRole = session('active_role');
        if (!$activeRole || !in_array($activeRole, $allowedRoles, true)) {
            $activeRole = $allowedRoles[0];
            session(['active_role' => $activeRole]);
        }

        $viewMap = [
            'klien'         => 'klien.dashboard',
            'pengurus_tefa' => 'pengurus-tefa.dashboard',
            'pm'            => 'pm.dashboard',
            'dosen'         => 'dosen.dashboard',
            'mahasiswa'     => 'mahasiswa.dashboard',
            'jurusan'       => 'jurusan.dashboard',
        ];

        $view = $viewMap[$activeRole] ?? 'dashboard';
        return view($view, ['user' => $user, 'currentRole' => $activeRole]);
    })->name('dashboard');

    // Switch Role handlers (Strictly validated against allowed roles)
    Route::get('/switch-role/{role}', function ($role) {
        $user = session('user');
        $allowedRoles = ApiService::getUserRoles($user);

        if (!in_array($role, $allowedRoles, true)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki role ' . $role);
        }

        session(['active_role' => $role]);
        return redirect()->route('dashboard');
    })->name('switch-role.get');

    Route::post('/switch-role', function (Request $request) {
        $role = $request->input('role');
        $user = session('user');
        $allowedRoles = ApiService::getUserRoles($user);

        if (!in_array($role, $allowedRoles, true)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki role ' . $role);
        }

        session(['active_role' => $role]);
        return redirect()->route('dashboard');
    })->name('switch-role');

    // Klien: Ajukan Proyek Baru
    Route::middleware('auth.api:klien')->prefix('klien')->name('klien.')->group(function () {
        Route::get('/ajukan-proyek', fn () => view('klien.ajukan-proyek', ['user' => session('user')]))
            ->name('ajukan-proyek');

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
                // ponytail: id kategori di-hardcode karena backend belum menyediakan endpoint master data.
                // Ganti dengan fetch dari /master/proposal-categories saat endpoint tersedia.
                'kategori_id'       => ['required', 'integer', 'in:1,2,3,4,5'],
                'ringkasan'         => ['required', 'string', 'min:100', 'max:500'],
                'deskripsi'         => ['nullable', 'string', 'max:2000'],
                'target_pengguna'   => ['nullable', 'string', 'max:1000'],
                'deadline'          => ['required', 'date', 'after_or_equal:' . now()->addDays(30)->toDateString()],
                'anggaran'          => ['nullable', 'numeric', 'min:0'],
                'modul'             => ['required', 'array', 'min:1'],
                'modul.*.judul'     => ['required', 'string', 'max:255'],
                'modul.*.deskripsi' => ['nullable', 'string', 'max:1000'],
            ]);

            // ponytail: backend POST /proposals hanya menyimpan field resmi (title/background/destination).
            // Data kontak & modul digabung ke background supaya tidak hilang; pisahkan ke kolom sendiri
            // (scope_of_work / tabel modules) begitu endpoint modul & profil klien tersedia di backend.
            $modul = collect($validated['modul'] ?? [])
                ->filter(fn ($m) => !empty($m['judul']))
                ->values();

            $background = sprintf(
                "Kontak: %s | PIC: %s | %s%s\n\n%s",
                $validated['nama_instansi'],
                $validated['pic_nama'],
                $validated['kontak_email'],
                !empty($validated['kontak_nomor']) ? ' | ' . $validated['kontak_nomor'] : '',
                $validated['ringkasan']
            );

            if (!empty($validated['deskripsi'])) {
                $background .= "\n\nDetail kebutuhan:\n" . $validated['deskripsi'];
            }

            if ($modul->isNotEmpty()) {
                $background .= "\n\nModul yang diminta:\n" . $modul
                    ->map(fn ($m, $i) => ($i + 1) . '. ' . $m['judul'] . (!empty($m['deskripsi']) ? ' — ' . $m['deskripsi'] : ''))
                    ->implode("\n");
            }

            $response = $api->post('proposals', [
                'title'            => $validated['judul_proyek'],
                'background'       => $background,
                'category_id'      => (int) $validated['kategori_id'],
                'desired_deadline' => $validated['deadline'],
                'target_audience'  => $validated['target_pengguna'] ?? null,
                'estimated_budget' => $validated['anggaran'] ?? null,
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
        Route::get('/project', fn () => view('pm.project', ['user' => session('user')]))->name('project');
        Route::get('/anggota-tim', fn () => view('pm.anggota-tim', ['user' => session('user')]))->name('anggota-tim');
        Route::get('/task-timeline', fn () => view('pm.task-timeline', ['user' => session('user')]))->name('task-timeline');
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
        Route::get('/validasi-proposal', fn () => view('pengurus-tefa.validasi-proposal', ['user' => session('user')]))->name('validasi-proposal');
        Route::get('/keuangan', fn () => view('pengurus-tefa.keuangan', ['user' => session('user')]))->name('keuangan');
        Route::get('/monitoring', fn () => view('pengurus-tefa.monitoring', ['user' => session('user')]))->name('monitoring');
    });
});
