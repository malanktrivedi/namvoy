<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

/**
 * Calculate a provider eligibility/match score for an RFQ.
 * Weights follow the NamVoy MVP specification:
 * destination 30%, budget 20%, category 15%, rating 15%, response 10%, cancellation 10%.
 */
function provider_match_score(array $request, array $provider): float
{
    $destination = 0.0;
    $requestDestination = strtolower(trim((string)($request['destination'] ?? '')));
    $providerDestinations = strtolower((string)($provider['service_destinations'] ?? ''));
    if ($requestDestination !== '' && ($providerDestinations === '' || str_contains($providerDestinations, $requestDestination))) {
        $destination = 100.0;
    } elseif ($requestDestination !== '' && str_contains($providerDestinations, 'vietnam')) {
        $destination = 75.0;
    }

    $budget = 100.0;
    $max = (float)($request['budget_max'] ?? 0);
    $providerMin = (float)($provider['typical_min_budget'] ?? 0);
    $providerMax = (float)($provider['typical_max_budget'] ?? 0);
    if ($max > 0 && $providerMax > 0) {
        if ($providerMin <= $max && $providerMax >= (float)($request['budget_min'] ?? 0)) {
            $budget = 100.0;
        } elseif ($providerMin <= ($max * 1.15)) {
            $budget = 70.0;
        } else {
            $budget = 30.0;
        }
    }

    $category = 100.0;
    $style = strtolower(trim((string)($request['travel_type'] ?? '')));
    $providerCategories = strtolower((string)($provider['categories'] ?? ''));
    if ($style !== '' && $providerCategories !== '' && !str_contains($providerCategories, $style)) {
        $category = 50.0;
    }

    $rating = min(100.0, max(0.0, ((float)($provider['rating'] ?? 0)) / 5 * 100));
    $response = min(100.0, max(0.0, (float)($provider['response_rate'] ?? 0)));
    $cancellation = 100.0 - min(100.0, max(0.0, (float)($provider['cancellation_rate'] ?? 0)));

    return round(
        ($destination * 0.30) + ($budget * 0.20) + ($category * 0.15) +
        ($rating * 0.15) + ($response * 0.10) + ($cancellation * 0.10),
        1
    );
}

function find_matching_providers(array $request, int $limit = 20): array
{
    $limit = max(1, min(100, $limit));
    $stmt = db()->prepare("SELECT p.*, u.first_name, u.last_name
        FROM providers p
        INNER JOIN users u ON u.id = p.user_id
        WHERE p.verification_status IN ('verified','trusted','preferred')
          AND p.status = 'active'
        ORDER BY p.rating DESC, p.response_rate DESC
        LIMIT ?");
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $matches = [];
    while ($provider = $result->fetch_assoc()) {
        $provider['match_score'] = provider_match_score($request, $provider);
        $matches[] = $provider;
    }

    usort($matches, static fn(array $a, array $b): int => $b['match_score'] <=> $a['match_score']);
    return $matches;
}

function generate_request_number(): string
{
    return 'NV' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
}

function generate_bid_number(): string
{
    return 'BID' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
}
