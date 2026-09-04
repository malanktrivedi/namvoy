<?php
require_once __DIR__.'/../includes/bootstrap.php';
$user=require_login();
$stmt=db()->prepare('SELECT COUNT(*) AS unread FROM notifications WHERE user_id=? AND is_read=0');$stmt->bind_param('i',$user['id']);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();header('Content-Type: application/json');echo json_encode(['unread'=>(int)$row['unread']]);
