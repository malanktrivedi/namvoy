<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login('traveler');
$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf($_POST['csrf_token']??null);
 $origin=trim($_POST['origin']??''); $destination=trim($_POST['destination']??''); $start=$_POST['start_date']??null; $end=$_POST['end_date']??null;
 $adults=max(1,(int)($_POST['adults']??1)); $children=max(0,(int)($_POST['children']??0)); $min=(float)($_POST['budget_min']??0); $max=(float)($_POST['budget_max']??0); $type=trim($_POST['travel_type']??''); $desc=trim($_POST['description']??'');
 if($destination===''||!$start||!$end||$end<$start||$max<=0){$error='Please enter a destination, valid dates and a budget.';}else{
  $requestNo='NV'.date('ymd').strtoupper(bin2hex(random_bytes(3)));
  $status='receiving_offers'; $expires=date('Y-m-d H:i:s',strtotime('+7 days'));
  $stmt=db()->prepare('INSERT INTO trip_requests (user_id,request_number,origin,destination,start_date,end_date,adults,children,budget_min,budget_max,travel_type,description,status,expires_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
  $uid=current_user()['id']; $stmt->bind_param('isssssiiddssss',$uid,$requestNo,$origin,$destination,$start,$end,$adults,$children,$min,$max,$type,$desc,$status,$expires); $stmt->execute();
  flash('success','Your travel request '.$requestNo.' is now receiving provider offers.'); redirect('/account/request-view.php?id='.db()->insert_id);
 }
}
$page_title='Build My Trip';require __DIR__.'/includes/header.php';
?>
<div class="container py-5" style="max-width:900px"><div class="card p-4 p-lg-5"><h1 class="h3">Build My Trip</h1><p class="text-secondary">Tell verified providers what you need. They compete with offers for your trip.</p><?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Origin</label><input class="form-control" name="origin" placeholder="Mumbai"></div><div class="col-md-6"><label class="form-label">Destination *</label><input class="form-control" name="destination" placeholder="Vietnam" required></div>
<div class="col-md-6"><label class="form-label">Start date *</label><input class="form-control" type="date" name="start_date" required></div><div class="col-md-6"><label class="form-label">End date *</label><input class="form-control" type="date" name="end_date" required></div>
<div class="col-md-3"><label class="form-label">Adults</label><input class="form-control" type="number" min="1" name="adults" value="2"></div><div class="col-md-3"><label class="form-label">Children</label><input class="form-control" type="number" min="0" name="children" value="0"></div><div class="col-md-3"><label class="form-label">Min budget</label><input class="form-control" type="number" min="0" name="budget_min" value="0"></div><div class="col-md-3"><label class="form-label">Max budget *</label><input class="form-control" type="number" min="1" name="budget_max" required></div>
<div class="col-md-6"><label class="form-label">Travel style</label><select class="form-select" name="travel_type"><option value="">Choose</option><option>Honeymoon</option><option>Family</option><option>Adventure</option><option>Leisure</option><option>Food & Culture</option><option>Business</option></select></div><div class="col-12"><label class="form-label">What do you want?</label><textarea class="form-control" name="description" rows="5" placeholder="4-star hotel, breakfast, airport transfers, cruise, sightseeing..."></textarea></div></div><button class="btn btn-dark btn-lg mt-4">Get Competing Offers</button></form></div></div>
<?php require __DIR__.'/includes/footer.php'; ?>
