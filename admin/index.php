<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_login('admin');
$counts=[];
foreach(['users','providers','trip_requests','provider_bids','bookings'] as $table){$result=db()->query("SELECT COUNT(*) c FROM `$table`");$counts[$table]=(int)$result->fetch_assoc()['c'];}
$page_title='Admin Dashboard';require __DIR__.'/../includes/header.php';
?><div class="container py-5"><h1 class="h3 mb-4">NamVoy Admin</h1><div class="row g-3"><?php foreach($counts as $name=>$count):?><div class="col-md"><div class="card p-4 h-100"><div class="text-secondary text-capitalize"><?=e(str_replace('_',' ',$name))?></div><div class="display-6 fw-semibold"><?=$count?></div></div></div><?php endforeach;?></div><div class="card p-4 mt-4"><h2 class="h5">MVP controls</h2><p class="text-secondary mb-0">Provider verification and marketplace operations will be expanded here in the next phase.</p></div></div><?php require __DIR__.'/../includes/footer.php'; ?>
