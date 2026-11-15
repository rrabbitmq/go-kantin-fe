<?php
require __DIR__ . '/config/database.php';
 
echo 'PHP versi          : ' . PHP_VERSION . PHP_EOL;
echo 'Ekstensi pdo_pgsql : ' . (extension_loaded('pdo_pgsql') ? 'AKTIF' : 'TIDAK AKTIF  <-- aktifkan di php.ini') . PHP_EOL;
 
try {
    $pdo = getConnection();
    echo 'Koneksi database   : OK' . PHP_EOL;
 
    foreach (['users', 'roles', 'user_roles', 'permissions', 'role_permissions', 'token_blacklist'] as $t) {
        $ada = $pdo->query("SELECT to_regclass('public.$t')")->fetchColumn();
        echo str_pad("Tabel $t", 19) . ': ' . ($ada ? 'ada' : 'TIDAK ADA') . PHP_EOL;
    }
 
    $kolom = $pdo->query(
        "SELECT 1 FROM information_schema.columns WHERE table_name = 'users' AND column_name = 'no_hp'"
    )->fetchColumn();
    echo 'Kolom users.no_hp  : ' . ($kolom ? 'ada' : 'TIDAK ADA (jalankan 004_add_no_hp.sql)') . PHP_EOL;
 
    echo 'Jumlah user        : ' . $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() . PHP_EOL;
} catch (Throwable $e) {
    echo 'ERROR              : ' . $e->getMessage() . PHP_EOL;
}