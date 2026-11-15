<?php
$pageTitle = $pageTitle ?? 'Go Kantin';
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
  <link rel="stylesheet" href="/css/style.css">
  <title><?= gk_h($pageTitle) ?></title>
  <style>
    .gk-auth-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 16px;
      background:
        radial-gradient(circle at top left, rgba(245, 195, 50, 0.28), transparent 40%),
        linear-gradient(160deg, #fffdf6 0%, #f7f3ea 45%, #efe8d8 100%);
    }
    .gk-auth-card {
      width: 100%;
      max-width: 440px;
      background: #fff;
      border-radius: 18px;
      padding: 32px 28px;
      box-shadow: 0 18px 50px rgba(40, 30, 10, 0.08);
    }
    .gk-auth-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
      text-decoration: none;
      color: inherit;
    }
    .gk-auth-brand img {
      width: 42px;
      height: 42px;
    }
    .gk-auth-brand strong {
      font-size: 1.25rem;
    }
    .gk-auth-card h1 {
      font-size: 1.6rem;
      margin-bottom: 8px;
    }
    .gk-auth-card .gk-auth-sub {
      color: #6f6f87;
      margin-bottom: 24px;
    }
    .gk-auth-alert {
      padding: 12px 14px;
      border-radius: 10px;
      margin-bottom: 16px;
      font-size: 0.92rem;
    }
    .gk-auth-alert-error {
      background: #fdecea;
      color: #a33b2b;
    }
    .gk-auth-alert-success {
      background: #eaf8ef;
      color: #1f7a43;
    }
    .gk-auth-field {
      margin-bottom: 16px;
    }
    .gk-auth-field label {
      display: block;
      margin-bottom: 6px;
      font-weight: 600;
      font-size: 0.92rem;
    }
    .gk-auth-field input {
      width: 100%;
      border: 1px solid #e4dfd3;
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 1rem;
      background: #fff;
    }
    .gk-auth-actions {
      margin-top: 22px;
      display: grid;
      gap: 12px;
    }
    .gk-auth-actions .sb-btn {
      justify-content: center;
      width: 100%;
    }
    .gk-auth-links {
      margin-top: 18px;
      text-align: center;
      font-size: 0.92rem;
      color: #6f6f87;
    }
    .gk-auth-links a {
      color: #1f1f2e;
      font-weight: 600;
      text-decoration: none;
    }
    .gk-auth-switch {
      margin-top: 16px;
      padding-top: 16px;
      border-top: 1px solid #efe9dc;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="gk-auth-page">
    <?php require $contentView; ?>
  </div>
</body>
</html>
