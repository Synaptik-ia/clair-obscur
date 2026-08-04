<?php
// tests/test_csrf.php - Test du système CSRF

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';

$passed = 0;
$failed = 0;

function assert_true($label, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] $label\n";
        $passed++;
    } else {
        echo "  [FAIL] $label\n";
        $failed++;
    }
}

function assert_false($label, $condition) {
    global $passed, $failed;
    if (!$condition) {
        echo "  [PASS] $label\n";
        $passed++;
    } else {
        echo "  [FAIL] $label\n";
        $failed++;
    }
}

echo "=== Test: CSRF ===\n";

// Test 1: generateCSRFToken creates a token
$token = generateCSRFToken();
assert_true('generateCSRFToken() returns non-empty string', !empty($token));
assert_true('Token is 64 chars (hex of 32 bytes)', strlen($token) === 64);

// Test 2: verifyCSRFToken with valid token
assert_true('verifyCSRFToken() accepts valid token', verifyCSRFToken($token));

// Test 3: verifyCSRFToken with invalid token
assert_false('verifyCSRFToken() rejects invalid token', verifyCSRFToken('invalid_token'));

// Test 4: verifyCSRFToken with empty token
assert_false('verifyCSRFToken() rejects empty token', verifyCSRFToken(''));

// Test 5: Token is stable across calls
$token2 = generateCSRFToken();
assert_true('generateCSRFToken() returns same token', $token === $token2);

// Test 6: CSRF token survives session (simulate)
$_SESSION['csrf_token'] = 'test_token_123';
assert_true('verifyCSRFToken() with direct session set', verifyCSRFToken('test_token_123'));
assert_false('verifyCSRFToken() rejects wrong token after set', verifyCSRFToken('wrong'));

echo "\n=== Résultat: $passed réussis, $failed échoués ===\n";
exit($failed > 0 ? 1 : 0);
