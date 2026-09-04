<?php
require_once __DIR__.'/../includes/bootstrap.php';

// Run periodically from cron. This endpoint intentionally has no UI output.
$conn=db();
$conn->begin_transaction();
try {
    $stmt=$conn->prepare("UPDATE trip_requests SET status='expired' WHERE status IN ('published','receiving_offers') AND expires_at IS NOT NULL AND expires_at < NOW()");
    $stmt->execute();
    $stmt=$conn->prepare("UPDATE provider_bids b INNER JOIN trip_requests r ON r.id=b.trip_request_id SET b.status='expired' WHERE b.status IN ('submitted','shortlisted') AND (b.valid_until IS NOT NULL AND b.valid_until < NOW() OR r.status='expired')");
    $stmt->execute();
    $conn->commit();
    header('Content-Type: application/json');
    echo json_encode(['success'=>true,'expired_requests'=>$conn->affected_rows]);
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success'=>false]);
}
