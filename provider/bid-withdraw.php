<?php
require_once __DIR__.'/../includes/bootstrap.php';
$user=require_login('provider');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method not allowed.');}
verify_csrf($_POST['csrf_token']??null);$bidId=(int)($_POST['bid_id']??0);
$stmt=db()->prepare("SELECT b.id,b.trip_request_id,b.status,tr.status AS request_status,tr.expires_at,p.user_id FROM provider_bids b JOIN trip_requests tr ON tr.id=b.trip_request_id JOIN providers p ON p.id=b.provider_id WHERE b.id=? AND p.user_id=? LIMIT 1");$stmt->bind_param('ii',$bidId,$user['id']);$stmt->execute();$bid=$stmt->get_result()->fetch_assoc();if(!$bid){http_response_code(404);exit('Offer not found.');}
if(!in_array($bid['status'],['draft','submitted','shortlisted'],true)||!in_array($bid['request_status'],['published','receiving_offers'],true)||($bid['expires_at']&&strtotime($bid['expires_at'])<=time())){set_flash('danger','This offer can no longer be withdrawn.');redirect('/provider/offers.php');}
$stmt=db()->prepare("UPDATE provider_bids SET status='withdrawn' WHERE id=? AND status IN ('draft','submitted','shortlisted')");$stmt->bind_param('i',$bidId);$stmt->execute();set_flash('success','Offer withdrawn.');redirect('/provider/offers.php');
