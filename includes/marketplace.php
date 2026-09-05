<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function provider_match_score(array $request, array $provider): float
{
    $destination = (float)($provider['destination_match'] ?? 0);
    $category = (float)($provider['category_match'] ?? 0);
    $budget = 100.0;
    $max = (float)($request['budget_max'] ?? 0);
    $min = (float)($request['budget_min'] ?? 0);
    $providerMin = (float)($provider['typical_min_budget'] ?? 0);
    $providerMax = (float)($provider['typical_max_budget'] ?? 0);
    if ($max > 0 && ($providerMin > 0 || $providerMax > 0)) {
        $providerMin = $providerMin > 0 ? $providerMin : $providerMax;
        $providerMax = $providerMax > 0 ? $providerMax : $providerMin;
        $budget = ($providerMin <= $max && $providerMax >= $min) ? 100.0 : (($providerMin <= ($max * 1.15) || $providerMax >= ($min * 0.85)) ? 70.0 : 30.0);
    }
    $rating = min(100.0, max(0.0, ((float)($provider['rating'] ?? 0)) / 5 * 100));
    $response = min(100.0, max(0.0, (float)($provider['response_rate'] ?? 0)));
    $cancellation = 100.0 - min(100.0, max(0.0, (float)($provider['cancellation_rate'] ?? 0)));
    return round(($destination * .30) + ($budget * .20) + ($category * .15) + ($rating * .15) + ($response * .10) + ($cancellation * .10), 1);
}

function calculate_provider_namvoy_score(array $provider): float
{
    $rating = min(100.0, max(0.0, ((float)($provider['rating'] ?? 0)) / 5 * 100));
    $response = min(100.0, max(0.0, (float)($provider['response_rate'] ?? 0)));
    $cancellation = 100.0 - min(100.0, max(0.0, (float)($provider['cancellation_rate'] ?? 0)));
    $completed = max(0, (int)($provider['completed_bookings'] ?? 0));
    $successful = max(0, (int)($provider['successful_bookings'] ?? 0));
    $fulfillment = $completed > 0 ? min(100.0, ($successful / $completed) * 100) : 70.0;
    $verified = in_array($provider['verification_status'] ?? '', ['verified','trusted','preferred'], true) ? 100.0 : 0.0;
    return round(($rating * .35) + ($response * .20) + ($cancellation * .20) + ($fulfillment * .15) + ($verified * .10), 2);
}

function provider_average_response_hours(int $providerId): ?float
{
    $stmt = db()->prepare("SELECT AVG(TIMESTAMPDIFF(MINUTE, tr.created_at, pb.created_at)) AS avg_minutes FROM provider_bids pb INNER JOIN trip_requests tr ON tr.id=pb.trip_request_id WHERE pb.provider_id=? AND pb.status <> 'draft'");
    $stmt->bind_param('i', $providerId);
    $stmt->execute();
    $value = $stmt->get_result()->fetch_assoc()['avg_minutes'] ?? null;
    return $value === null ? null : round(((float)$value) / 60, 1);
}

function record_provider_request_view(int $providerId, int $requestId): void
{
    $stmt = db()->prepare('INSERT INTO provider_request_views (provider_id,trip_request_id) VALUES (?,?) ON DUPLICATE KEY UPDATE last_viewed_at=CURRENT_TIMESTAMP,view_count=view_count+1');
    $stmt->bind_param('ii', $providerId, $requestId);
    $stmt->execute();
}

function load_provider_matching_profile(int $providerId): array
{
    $profile = ['destination_match'=>0.0,'category_match'=>0.0,'destination_count'=>0,'expert_destination_count'=>0,'categories'=>[]];
    $stmt = db()->prepare("SELECT COUNT(*) AS total, COALESCE(SUM(expertise_level='expert'),0) AS experts FROM provider_destinations WHERE provider_id=?");
    $stmt->bind_param('i', $providerId);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();
    $profile['destination_count']=(int)($row['total']??0);$profile['expert_destination_count']=(int)($row['experts']??0);
    $stmt=db()->prepare('SELECT category FROM provider_categories WHERE provider_id=? ORDER BY category');$stmt->bind_param('i',$providerId);$stmt->execute();$result=$stmt->get_result();
    while($row=$result->fetch_assoc())$profile['categories'][]=mb_strtolower(trim($row['category']));
    return $profile;
}

function enrich_provider_match(array $request, array $provider, ?array $profile = null): array
{
    $profile ??= load_provider_matching_profile((int)$provider['id']);
    $destinationName=mb_strtolower(trim((string)($request['destination']??'')));$travelType=mb_strtolower(trim((string)($request['travel_type']??'')));
    $destinationMatch=0.0;
    $stmt=db()->prepare("SELECT pd.expertise_level FROM provider_destinations pd INNER JOIN destinations d ON d.id=pd.destination_id WHERE pd.provider_id=? AND LOWER(d.name)=? AND d.status='active' LIMIT 1");$stmt->bind_param('is',$provider['id'],$destinationName);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();
    if($row)$destinationMatch=$row['expertise_level']==='expert'?100.0:90.0;
    $categoryMatch=0.0;if($travelType!==''&&in_array($travelType,$profile['categories'],true))$categoryMatch=100.0;elseif($travelType===''&&$profile['categories'])$categoryMatch=75.0;
    $provider['destination_match']=$destinationMatch;$provider['category_match']=$categoryMatch;$provider['match_score']=provider_match_score($request,$provider);$provider['match_label']=$provider['match_score']>=90?'Best Match':($provider['match_score']>=75?'Strong Match':($provider['match_score']>=60?'Good Match':'Limited Match'));
    return $provider;
}

function find_matching_providers(array $request, int $limit = 20): array
{
    $limit=max(1,min(100,$limit));$stmt=db()->prepare("SELECT p.*,u.first_name,u.last_name FROM providers p INNER JOIN users u ON u.id=p.user_id WHERE p.verification_status IN ('verified','trusted','preferred') AND u.status='active' ORDER BY p.namvoy_score DESC,p.rating DESC,p.response_rate DESC LIMIT ?");$stmt->bind_param('i',$limit);$stmt->execute();$result=$stmt->get_result();$matches=[];
    while($provider=$result->fetch_assoc())$matches[]=enrich_provider_match($request,$provider);
    usort($matches,static fn(array $a,array $b):int=>$b['match_score']<=>$a['match_score']);return $matches;
}
function generate_request_number():string{return 'NV'.date('ymd').strtoupper(bin2hex(random_bytes(3)));}
function generate_bid_number():string{return 'BID'.date('ymd').strtoupper(bin2hex(random_bytes(3)));}
