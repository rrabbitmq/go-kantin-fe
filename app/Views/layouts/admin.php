<?php

use App\Core\View;

$currentUser = $currentUser ?? ['name' => 'Tamu', 'avatar_letter' => 'T'];
$standMeta = $standMeta ?? [
    'nama' => 'Stand Kantin JTI',
    'lokasi' => 'Kantin JTI • Lantai 8',
    'jam' => '08.00 - 15.00 WIB',
    'status_buka' => true,
    'role_label' => 'Kasir Stand Terpadu',
];
$activeNav = $activeNav ?? '';
$queueBadge = (int) ($queueBadge ?? 0);
$pageTitle = $pageTitle ?? 'Go Kantin POS';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#F97316">
  <link rel="shortcut icon" href="/img/ui/logo.ico" type="image/x-icon">
  <link rel="stylesheet" href="/css/plugins/font-awesome.min.css">
  <link rel="stylesheet" href="/css/plugins/bootstrap.min.css">
  <link rel="stylesheet" href="/css/plugins/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="/css/admin.css">
  <title><?= gk_h($pageTitle) ?></title>
</head>
<body class="gk-admin-body">
  <div class="gk-admin-app" id="gk-admin-app">
<?php View::partial('layouts/admin-sidebar', compact('activeNav', 'currentUser', 'standMeta', 'queueBadge')); ?>
    <div class="gk-admin-main">
<?php View::partial('layouts/admin-topbar', compact('currentUser', 'standMeta')); ?>
      <main class="gk-admin-content">
        <?php require $contentView; ?>
      </main>
    </div>
  </div>

  <div class="gk-admin-toast" id="gk-admin-toast" hidden></div>

  <script src="/js/plugins/jquery.min.js"></script>
  <script src="/js/plugins/bootstrap.bundle.min.js"></script>
  <script src="/js/plugins/jquery.dataTables.min.js"></script>
  <script src="/js/plugins/dataTables.bootstrap4.min.js"></script>
  <script src="/js/admin-datatable.js"></script>
  <script src="/js/admin.js"></script>
</body>
</html>
