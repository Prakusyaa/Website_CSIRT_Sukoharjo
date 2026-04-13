<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

echo "=== SESSION CONFIG ===" . PHP_EOL;
$s = config('session');
echo "driver: " . $s['driver'] . PHP_EOL;
echo "domain: " . var_export($s['domain'], true) . PHP_EOL;
echo "secure: " . var_export($s['secure'], true) . PHP_EOL;
echo "same_site: " . var_export($s['same_site'], true) . PHP_EOL;
echo "http_only: " . var_export($s['http_only'], true) . PHP_EOL;
echo "cookie: " . $s['cookie'] . PHP_EOL;
echo "path: " . $s['path'] . PHP_EOL;

echo PHP_EOL . "=== SESSIONS TABLE ===" . PHP_EOL;
echo "exists: " . (Schema::hasTable('sessions') ? 'YES' : 'NO') . PHP_EOL;
if (Schema::hasTable('sessions')) {
    echo "count: " . DB::table('sessions')->count() . PHP_EOL;
    $cols = Schema::getColumnListing('sessions');
    echo "columns: " . implode(', ', $cols) . PHP_EOL;
}

echo PHP_EOL . "=== USERS ===" . PHP_EOL;
$users = User::with('role')->get();
foreach ($users as $u) {
    echo "ID={$u->id} | user={$u->username} | email={$u->email} | active=" . var_export($u->is_active, true)
        . " | role=" . ($u->role ? $u->role->name . "(L{$u->role->level})" : 'NONE')
        . " | remember_token=" . (empty($u->remember_token) ? 'NULL' : 'SET')
        . PHP_EOL;
}

echo PHP_EOL . "=== PASSWORD CHECK ===" . PHP_EOL;
$firstUser = User::first();
if ($firstUser) {
    echo "User: {$firstUser->username}" . PHP_EOL;
    echo "Password hash: " . substr($firstUser->password, 0, 20) . "..." . PHP_EOL;
    echo "Hash starts with \$2y\$: " . (str_starts_with($firstUser->password, '$2y$') ? 'YES' : 'NO') . PHP_EOL;
    echo "Hash starts with \$2a\$: " . (str_starts_with($firstUser->password, '$2a$') ? 'YES' : 'NO') . PHP_EOL;
    echo "Test 'password': " . (password_verify('password', $firstUser->password) ? 'MATCH' : 'NO MATCH') . PHP_EOL;
    echo "Test 'admin': " . (password_verify('admin', $firstUser->password) ? 'MATCH' : 'NO MATCH') . PHP_EOL;
    echo "Test 'admin123': " . (password_verify('admin123', $firstUser->password) ? 'MATCH' : 'NO MATCH') . PHP_EOL;
}
