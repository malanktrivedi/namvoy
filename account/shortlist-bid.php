<?php
require_once __DIR__.'/../includes/bootstrap.php';
$user=require_login('traveler');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method not allowed.');}
verify_csrf($_POST['csrf_token']??null);$bidId=(int)($_POST['bid_id']??0);
$stmt=db()->prepare("SELECT b.id,b.trip_request_id,b.status,tr.status AS request_status,tr.expires_at FROM provider_bids b JOIN trip_requests tr ON tr.id=b.trip_request_id WHERE b.id=? AND tr.user_id=? LIMIT 1");$stmt->bind_param('ii',$bidId,$user['id']);$stmt->execute();$bid=$stmt->get_result()->fetch_assoc();if(!$bid){http_response_code(404);exit('Offer not found.');}
if(!in_array($bid['status'],['submitted','shortlisted'],true)||!in_array($bid['request_status'],['published','receiving_offers'],true)||($bid['expires_at']&&strtotime($bid['expires_at'])<=time())){set_flash('danger','This offer cannot be shortlisted.');redirect('/account/request-view.php?id='.$bid['trip_request_id']);}
$stmt=db()->prepare("UPDATE provider_bids SET status='shortlisted' WHERE id=? AND status='submitted'");$stmt->bind_param('i',$bidId);$stmt->execute();set_flash('success','Offer shortlisted. You can continue comparing it with other offers.');redirect('/account/request-view.php?id='.$bid['trip_request_id']);
