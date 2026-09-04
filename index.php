<?php
require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Build Your Trip';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
  <section class="hero p-4 p-lg-5 mb-4">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="text-uppercase small fw-bold opacity-75 mb-2">NamVoy</div>
        <h1 class="display-5 fw-bold">The marketplace where travel providers compete for your trip.</h1>
        <p class="lead opacity-75">Tell us what you want. Verified travel providers compete with offers built around your requirements.</p>
        <a href="/trip-planner.php" class="btn btn-light btn-lg">Build My Trip</a>
      </div>
    </div>
  </section>
  <div class="row g-4">
    <div class="col-md-4"><div class="card p-4 h-100"><h3>1. Tell NamVoy</h3><p class="text-secondary mb-0">Destination, dates, travelers, budget and preferences.</p></div></div>
    <div class="col-md-4"><div class="card p-4 h-100"><h3>2. Get competing offers</h3><p class="text-secondary mb-0">Providers receive your RFQ and submit their best proposal.</p></div></div>
    <div class="col-md-4"><div class="card p-4 h-100"><h3>3. Compare & choose</h3><p class="text-secondary mb-0">Compare price, inclusions and provider quality before booking.</p></div></div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
