<?php
// ponytail: one-off scratch script, delete after use
require __DIR__.'/../../tevara-api/vendor/autoload.php';
$app = require __DIR__.'/../../tevara-api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$exists = App\Models\User::where('email', 'budi@tevara.dev')->first();
if ($exists) {
    echo 'EXISTS id=' . $exists->id . PHP_EOL;
    exit;
}

$u = App\Models\User::create([
    'name'     => 'Budi Santoso',
    'email'    => 'budi@tevara.dev',
    'password' => 'password123',
]);
$u->assignRole('klien');
echo 'CREATED id=' . $u->id . ' role=klien' . PHP_EOL;
