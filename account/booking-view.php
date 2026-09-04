<?php
require_once __DIR__.'/../includes/bootstrap.php';
$user = require_login('traveler');
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT b.*,p.business_name,p.business_email,p.business_phone,tr.request_number,tr.destination,tr.start_date,tr.end_date FROM bookings b INNER JOIN providers p ON p.id=b.provider_id LEFT JOIN trip_requests tr ON tr.id=b.trip_request_id WHERE b.id=? AND b.user_id=? LIMIT 1');
$stmt->bind_param('ii',$id,$user['id']);$stmt->execute();$booking=$stmt->get_result()->fetch_assoc();
if(!$booking){http_response_code(404);exit('Booking not found.');}
$page_title='Booking '.$booking['booking_number'];require __DIR__.'/../includes/header.php';
?>
<div class="container py-5"><div class="d-flex justify-content-between align-items-start mb-4"><div><div class="text-secondary small">Booking confirmed for your selected offer</div><h1 class="h3 mb-1"><?=e($booking['booking_number'])?></h1><p class="text-secondary mb-0"><?=e($booking['request_number']??'')?> · <?=e($booking['destination']??'')?></p></div><span class="badge text-bg-warning p-2"><?=e($booking['booking_status'])?></span></div>
<div class="row g-4"><div class="col-lg-7"><div class="card p-4"><h2 class="h5">Trip</h2><p class="mb-1"><strong><?=e($booking['destination']??'')?></strong></p><p class="text-secondary"><?=e($booking['start_date']??'')?> → <?=e($booking['end_date']??'')?></p><hr><h2 class="h5">Selected provider</h2><p class="mb-1"><strong><?=e($booking['business_name'])?></strong></p><p class="text-secondary mb-0"><?=e($booking['business_email']??'')?><?=!empty($booking['business_phone'])?' · '.e($booking['business_phone']):''?></p></div></div><div class="col-lg-5"><div class="card p-4"><h2 class="h5">Amount</h2><div class="price mb-2"><?=e($booking['currency'])?> <?=number_format((float)$booking['total_amount'],2)?></div><div class="d-flex justify-content-between"><span>Payment</span><span><?=e($booking['payment_status'])?></span></div><div class="d-flex justify-content-between"><span>Booking</span><span><?=e($booking['booking_status'])?></span></div><p class="small text-secondary mt-3 mb-0">Payment integration is the next marketplace layer. No payment has been collected by this MVP.</p></div></div></div></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
