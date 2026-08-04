<?php
// tests/test_database.php - Test du singleton Database

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

echo "=== Test: Database Singleton ===\n";

// Test 1: getInstance returns same object
$db1 = Database::getInstance();
$db2 = Database::getInstance();
assert_true('getInstance() returns same instance', $db1 === $db2);

// Test 2: getConnection returns same PDO object
$conn1 = $db1->getConnection();
$conn2 = $db2->getConnection();
assert_true('getConnection() returns same PDO connection', $conn1 === $conn2);

// Test 3: new Database() still works for backward compatibility
$db3 = new Database();
assert_true('new Database() creates a different instance', $db3 !== $db1);

// Test 4: Connection is valid PDO
assert_true('Connection is PDO instance', $conn1 instanceof PDO);

echo "\n=== Résultat: $passed réussis, $failed échoués ===\n";
exit($failed > 0 ? 1 : 0);
