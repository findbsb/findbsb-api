<?php
/**
 * FindBSB API — PHP examples
 * https://findbsb.com.au/api
 */

define('FINDBSB_BASE_URL', 'https://findbsb.com.au/api');

// ── Single BSB lookup ────────────────────────────────────────────────────────

function lookup_bsb(string $bsb): array {
    $url = FINDBSB_BASE_URL . '/bsb/' . urlencode($bsb);
    $response = http_get($url);

    if ($response['status'] === 404) {
        throw new \Exception("BSB {$bsb} not found");
    }
    if ($response['status'] === 400) {
        throw new \Exception("Invalid BSB format: {$bsb}");
    }

    return $response['body'];
}

// ── Bulk validation ──────────────────────────────────────────────────────────

function validate_bsbs(array $bsbs): array {
    $response = http_post(
        FINDBSB_BASE_URL . '/validate',
        ['bsbs' => $bsbs]
    );
    return $response['body'];
}

// ── Filter by bank / state / suburb / postcode ───────────────────────────────

function filter_bsbs(array $params = []): array {
    $params = array_merge(['limit' => 100], $params);
    $url = FINDBSB_BASE_URL . '/bsb?' . http_build_query($params);
    $response = http_get($url);
    return $response['body'];
}

// ── HTTP helpers ─────────────────────────────────────────────────────────────

function http_get(string $url): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT      => 'MyApp/1.0',
        CURLOPT_TIMEOUT        => 10,
    ]);
    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'body' => json_decode($body, true)];
}

function http_post(string $url, array $data): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_USERAGENT      => 'MyApp/1.0',
        CURLOPT_TIMEOUT        => 10,
    ]);
    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'body' => json_decode($body, true)];
}

// ── Examples ─────────────────────────────────────────────────────────────────

// Single lookup
$bsb = lookup_bsb('062-000');
echo "{$bsb['bank']} — {$bsb['branch']}, {$bsb['suburb']} {$bsb['state']}\n";

// Bulk validate
$result = validate_bsbs(['062-000', '012-003', '999-999']);
echo "Valid: {$result['valid']}, Invalid: {$result['invalid']}, Closed: {$result['closed']}\n";
foreach ($result['results'] as $r) {
    $status = $r['valid'] ? '✓' : '✗';
    $closed = ($r['closed'] ?? false) ? ' (CLOSED)' : '';
    $bank   = $r['bank'] ?? 'N/A';
    echo "  {$status} {$r['bsb']} — {$bank}{$closed}\n";
}

// Filter NAB branches in QLD
$result = filter_bsbs(['bank' => 'NAB', 'state' => 'QLD', 'limit' => 5]);
echo "\nNAB QLD branches ({$result['total']} total):\n";
foreach ($result['results'] as $r) {
    echo "  {$r['bsb']} — {$r['branch']}, {$r['suburb']}\n";
}
