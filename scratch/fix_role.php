<?php
// ponytail: one-off scratch; give seeded budi@tevara.dev the klien role, delete after use
require __DIR__.'/../../tevara-api/vendor/autoload.php';
$app = require __DIR__.'/../../tevara-api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\User::where('email', 'budi@tevara.dev')->first();
$u->assignRole('klien');
echo 'ROLE klien attached to user ' . $u->id . PHP_EOL;
