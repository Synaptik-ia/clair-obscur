<?php
// tests/test_env.php - Test du chargement des variables d'environnement

require_once __DIR__ . '/../config/env.php';

$passed = 0;
$failed = 0;

function assert_eq($label, $expected, $actual) {
    global $passed, $failed;
    if ($expected === $actual) {
        echo "  [PASS] $label\n";
        $passed++;
    } else {
        echo "  [FAIL] $label — expected: '$expected', got: '$actual'\n";
        $failed++;
    }
}

function assert_not_empty($label, $value) {
    global $passed, $failed;
    if (!empty($value)) {
        echo "  [PASS] $label\n";
        $passed++;
    } else {
        echo "  [FAIL] $label — value is empty\n";
        $failed++;
    }
}

echo "=== Test: Chargement .env ===\n";

assert_not_empty('DB_HOST defined', env('DB_HOST'));
assert_not_empty('DB_NAME defined', env('DB_NAME'));
assert_not_empty('DB_USER defined', env('DB_USER'));
assert_not_empty('SITE_NAME defined', env('SITE_NAME'));
assert_not_empty('SITE_URL defined', env('SITE_URL'));
assert_not_empty('ADMIN_EMAIL defined', env('ADMIN_EMAIL'));
assert_not_empty('PAYPAL_MODE defined', env('PAYPAL_MODE'));

// Test fallback
assert_eq('env() fallback works', 'default_value', env('NONEXISTENT_KEY', 'default_value'));

echo "\n=== Résultat: $passed réussis, $failed échoués ===\n";
exit($failed > 0 ? 1 : 0);
