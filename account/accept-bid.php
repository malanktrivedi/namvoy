<?php
require_once __DIR__.'/../includes/bootstrap.php';
$user = require_login('traveler');
$bidId = (int)($_POST['bid_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed.'); }
verify_csrf();
if ($bidId < 1) { http_response_code(400); exit('Invalid offer.'); }

$db = db();
$bid = null;
try {
    $db->begin_transaction();

    $stmt = $db->prepare('SELECT b.*, tr.user_id AS traveler_id, tr.status AS request_status, tr.expires_at FROM provider_bids b INNER JOIN trip_requests tr ON tr.id=b.trip_request_id WHERE b.id=? AND tr.user_id=? LIMIT 1 FOR UPDATE');
    $stmt->bind_param('ii', $bidId, $user['id']);
    $stmt->execute();
    $bid = $stmt->get_result()->fetch_assoc();
    if (!$bid) { throw new RuntimeException('Offer not found.'); }
    if (!in_array($bid['status'], ['submitted','shortlisted'], true)) { throw new RuntimeException('This offer is no longer available.'); }
    if (!in_array($bid['request_status'], ['receiving_offers','published'], true)) { throw new RuntimeException('This request is no longer accepting an offer.'); }
    if (!empty($bid['expires_at']) && strtotime($bid['expires_at']) < time()) { throw new RuntimeException('This request has expired.'); }

    $stmt = $db->prepare('SELECT id FROM bookings WHERE bid_id=? LIMIT 1 FOR UPDATE');
    $stmt->bind_param('i', $bidId);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) { throw new RuntimeException('A booking already exists for this offer.'); }

    $bookingNumber = 'NVBOOK' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
    $subtotal = (float)$bid['total_amount'];
    $stmt = $db->prepare("INSERT INTO bookings (booking_number,user_id,provider_id,trip_request_id,bid_id,subtotal,total_amount,currency,payment_status,booking_status) VALUES (?,?,?,?,?,?,?,?,'pending','pending')");
    $stmt->bind_param('siiiidds', $bookingNumber, $user['id'], $bid['provider_id'], $bid['trip_request_id'], $bidId, $subtotal, $subtotal, $bid['currency']);
    $stmt->execute();
    $bookingId = $db->insert_id;

    $stmt = $db->prepare("UPDATE provider_bids SET status=CASE WHEN id=? THEN 'accepted' ELSE 'rejected' END WHERE trip_request_id=? AND status IN ('submitted','shortlisted')");
    $stmt->bind_param('ii', $bidId, $bid['trip_request_id']);
    $stmt->execute();

    $stmt = $db->prepare("UPDATE trip_requests SET status='booked' WHERE id=? AND user_id=?");
    $stmt->bind_param('ii', $bid['trip_request_id'], $user['id']);
    $stmt->execute();

    $stmt = $db->prepare("INSERT INTO notifications (user_id,type,title,message) SELECT p.user_id,'booking_created','Offer accepted',CONCAT('Your offer ',b.bid_number,' was accepted for booking ',?) FROM provider_bids b INNER JOIN providers p ON p.id=b.provider_id WHERE b.id=?");
    $stmt->bind_param('si', $bookingNumber, $bidId);
    $stmt->execute();

    $db->commit();
    redirect('/account/booking-view.php?id=' . $bookingId);
} catch (Throwable $e) {
    $db->rollback();
    set_flash('danger', $e->getMessage());
    redirect('/account/request-view.php?id=' . (int)($bid['trip_request_id'] ?? 0));
}
