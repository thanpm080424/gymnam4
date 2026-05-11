<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

function runTest($name, $url, $method = 'GET', $postData = [], $cookies = '') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/monkeygymnhom2" . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true); // Get headers to capture cookies
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }
    if ($cookies) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    curl_close($ch);
    
    // Extract new cookies
    $newCookies = [];
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $header, $matches);
    foreach($matches[1] as $item) {
        $newCookies[] = $item;
    }
    
    echo "<li><strong>$name:</strong> ";
    if ($httpCode >= 200 && $httpCode < 400) {
        echo "<span style='color:green'>PASSED (HTTP $httpCode)</span>";
    } else {
        echo "<span style='color:red'>FAILED (HTTP $httpCode)</span>";
    }
    echo "</li>";
    
    return [
        'code' => $httpCode,
        'body' => $body,
        'cookies' => implode('; ', $newCookies) ?: $cookies
    ];
}

echo "<h1>SYSTEM INTEGRATION TEST REPORT</h1>";
echo "<ul>";

// 1. Test Login Admin
$res = runTest("Admin Login Page Load", "/login");
$csrf = '';
if (preg_match('/name="csrf_token" value="([^"]+)"/', $res['body'], $m)) {
    $csrf = $m[1];
}

$adminLogin = runTest("Admin Authentication", "/login", "POST", [
    'csrf_token' => $csrf,
    'email' => 'admin',
    'password' => '123'
], $res['cookies']);

$adminCookies = $adminLogin['cookies'];

$adminDash = runTest("Admin Dashboard Access", "/admin/dashboard", "GET", [], $adminCookies);

// 2. Test Member Login
$resMember = runTest("Member Login Page Load", "/login");
$csrfMember = '';
if (preg_match('/name="csrf_token" value="([^"]+)"/', $resMember['body'], $m)) {
    $csrfMember = $m[1];
}

$memberLogin = runTest("Member Authentication", "/login", "POST", [
    'csrf_token' => $csrfMember,
    'email' => 'hoivien5@gmail.com', // fallback
    'password' => '123456'
], $resMember['cookies']);

$memberCookies = $memberLogin['cookies'];

// 3. Test Member Dashboard
$memberDash = runTest("Member Dashboard Access", "/member/dashboard", "GET", [], $memberCookies);

// Extract Member's QR Code from dashboard
$ma_hoi_vien = 0;
if (preg_match('/data=MEMBER_(\d+)_/i', $memberDash['body'], $m)) {
    $ma_hoi_vien = $m[1];
    echo "<li><em>Extracted Member ID from QR: $ma_hoi_vien</em></li>";
}

// 4. Test QR Check-in via Admin API
if ($ma_hoi_vien) {
    // We need Admin CSRF token for the API
    $scanRes = runTest("Admin Scan QR Page", "/admin/quet-qr", "GET", [], $adminCookies);
    $adminApiCsrf = '';
    if (preg_match("/csrfToken:\s*'([^']+)'/", $scanRes['body'], $m)) {
        $adminApiCsrf = $m[1];
    }
    
    // Test Checkin API
    $chApi = curl_init();
    curl_setopt($chApi, CURLOPT_URL, "http://localhost/monkeygymnhom2/public/api/qr-checkin.php");
    curl_setopt($chApi, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chApi, CURLOPT_POST, true);
    curl_setopt($chApi, CURLOPT_COOKIE, $adminCookies);
    curl_setopt($chApi, CURLOPT_POSTFIELDS, json_encode([
        'qr_code' => "MEMBER_{$ma_hoi_vien}_" . time(),
        'csrf_token' => $adminApiCsrf
    ]));
    curl_setopt($chApi, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $apiRes = curl_exec($chApi);
    $apiCode = curl_getinfo($chApi, CURLINFO_HTTP_CODE);
    curl_close($chApi);
    
    $apiJson = json_decode($apiRes, true);
    echo "<li><strong>Admin QR Check-in API:</strong> ";
    if ($apiCode == 200 && isset($apiJson['success'])) {
        echo "<span style='color:green'>PASSED (Success: " . ($apiJson['success'] ? 'Yes' : 'No') . " - " . $apiJson['message'] . ")</span>";
    } else {
        echo "<span style='color:red'>FAILED (HTTP $apiCode - Response: $apiRes)</span>";
    }
    echo "</li>";
}

// 5. Test Member PT Booking Page
$memberPT = runTest("Member PT Booking Page Load", "/member/book-pt", "GET", [], $memberCookies);

// 6. Test Member Store Page
$memberStore = runTest("Member Store Page Load", "/member/store", "GET", [], $memberCookies);

echo "</ul>";
echo "<p>Test completed.</p>";
?>
