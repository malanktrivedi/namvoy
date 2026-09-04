<?php
$flash = get_flash();
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title ?? APP_NAME) ?> | <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/">NamVoy</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="/trip-planner.php">Build My Trip</a></li>
        <?php if ($user): ?>
          <?php if ($user['role'] === 'traveler'): ?><li class="nav-item"><a class="nav-link" href="/account/dashboard.php">Dashboard</a></li><li class="nav-item"><a class="nav-link" href="/account/bookings.php">Bookings</a></li><?php endif; ?>
          <?php if ($user['role'] === 'provider'): ?><li class="nav-item"><a class="nav-link" href="/provider/dashboard.php">Provider Dashboard</a></li><li class="nav-item"><a class="nav-link" href="/provider/bookings.php">Bookings</a></li><?php endif; ?>
          <?php if ($user['role'] === 'admin'): ?><li class="nav-item"><a class="nav-link" href="/admin/index.php">Admin</a></li><?php endif; ?>
          <li class="nav-item"><a class="btn btn-dark btn-sm" href="/logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/login.php">Login</a></li>
          <li class="nav-item"><a class="btn btn-dark btn-sm" href="/register.php">Get Started</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<main>
<?php if ($flash): ?><div class="container pt-3"><div class="alert alert-<?= e($flash['type']) ?> mb-0"><?= e($flash['message']) ?></div></div><?php endif; ?>
