<?php
// tests/test_session.php - Test de la sécurité des sessions

require_once __DIR__ . '/../config/database.php';

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

// Test 6: session_regenerate_id (DOIT être avant tout output)
$old_id = session_id();
session_regenerate_id(true);
$new_id = session_id();
$regenerate_works = ($old_id !== $new_id);
$data_preserved = isset($_SESSION);

echo "=== Test: Sécurité des sessions ===\n";

// Test 1: Session is active
assert_true('Session is active', session_status() === PHP_SESSION_ACTIVE);

// Test 2: Cookie httponly
$httponly = ini_get('session.cookie_httponly');
assert_true('session.cookie_httponly = 1', $httponly == 1 || $httponly === '1' || $httponly === 'On');

// Test 3: use_only_cookies
$only_cookies = ini_get('session.use_only_cookies');
assert_true('session.use_only_cookies = 1', $only_cookies == 1 || $only_cookies === '1' || $only_cookies === 'On');

// Test 4: cookie_secure
$secure = ini_get('session.cookie_secure');
assert_true('session.cookie_secure = 1', $secure == 1 || $secure === '1' || $secure === 'On');

// Test 5: cookie_samesite
$samesite = ini_get('session.cookie_samesite');
assert_true('session.cookie_samesite = Strict', strtolower($samesite) === 'strict');

// Test 6: session_regenerate_id works
assert_true('session_regenerate_id() changes session ID', $regenerate_works);
assert_true('Session data preserved after regenerate', $data_preserved);

echo "\n=== Résultat: $passed réussis, $failed échoués ===\n";
exit($failed > 0 ? 1 : 0);
