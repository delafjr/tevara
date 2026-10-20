<?php
// ponytail: one-off scratch script, delete after use
require __DIR__.'/../../tevara-api/vendor/autoload.php';
$app = require __DIR__.'/../../tevara-api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (DB::table('users')->select('id','name','email')->get() as $u) {
    $roles = DB::table('model_has_roles')
        ->where('model_id', $u->id)
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->pluck('roles.name')->implode(',');
    echo $u->id . ' | ' . $u->email . ' | ' . $roles . PHP_EOL;
}
