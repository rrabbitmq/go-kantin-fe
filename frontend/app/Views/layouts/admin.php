<?php
/** @var string $contentView */
use App\Core\View;

$currentUser = $currentUser ?? ['name' => 'Tamu', 'avatar_letter' => 'T'];
$standMeta = $standMeta ?? [
    'nama' => 'Stand Kantin JTI',
    'lokasi' => 'Kantin JTI • Lantai 8',
    'jam' => '08.00 - 15.00 WIB',
    'status_buka' => true,
    'role_label' => 'Admin Stand',
];
$activeNav = $activeNav ?? '';
$queueBadge = (int) ($queueBadge ?? 0);
$pageTitle = $pageTitle ?? 'Go Kantin POS';
$jamOperasional = $standMeta['jam'] ?? '08.00 - 15.00 WIB';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#F5C332">
  <link rel="shortcut icon" href="/img/ui/logo.ico" type="image/x-icon">
  <link rel="stylesheet" href="/css/plugins/font-awesome.min.css">
  <link rel="stylesheet" href="/css/plugins/bootstrap.min.css">
  <link rel="stylesheet" href="/css/plugins/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="/css/style.css">
  <title><?= gk_h($pageTitle) ?></title>
</head>
<body>
  <div class="sb-app gk-app">
    <div class="sb-click-effect"></div>
    <div class="sb-load"></div>
<?php View::partial('layouts/admin-sidebar', compact('activeNav', 'currentUser', 'standMeta', 'queueBadge')); ?>
    <div class="gk-main">
<?php View::partial('layouts/admin-topbar', compact('currentUser', 'standMeta', 'jamOperasional')); ?>
      <div class="gk-content">
        <?php
          $flashOk = $flashSuccess ?? gk_flash('success');
          $flashErr = $flashError ?? gk_flash('error');
        ?>
        <?php if ($flashOk): ?>
        <div class="gk-flash gk-flash-success" role="status" style="margin:0 0 16px;padding:12px 16px;border-radius:12px;background:#e8f7ee;color:#1b6b3a;font-weight:500"><?= gk_h($flashOk) ?></div>
        <?php endif; ?>
        <?php if ($flashErr): ?>
        <div class="gk-flash gk-flash-error" role="alert" style="margin:0 0 16px;padding:12px 16px;border-radius:12px;background:#fdeaea;color:#a12a2a;font-weight:500"><?= gk_h($flashErr) ?></div>
        <?php endif; ?>
        <?php require $contentView; ?>
      </div>
    </div>
  </div>

  <div class="gk-admin-toast" id="gk-admin-toast" hidden></div>

  <script src="/js/plugins/jquery.min.js"></script>
  <script src="/js/plugins/bootstrap.bundle.min.js"></script>
  <script src="/js/plugins/jquery.dataTables.min.js"></script>
  <script src="/js/plugins/dataTables.bootstrap4.min.js"></script>
  <script src="/js/go-kantin.js"></script>
  <script src="/js/admin-datatable.js"></script>
  <script src="/js/admin.js"></script>
  <script src="/js/admin-menu.js"></script>
</body>
</html>
