<?php
// tests/test_password.php - Test de la validation des mots de passe

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

function validatePassword($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    return true;
}

echo "=== Test: Validation des mots de passe ===\n";

assert_false('trop court (7 car.)', validatePassword('Abcd1'));
assert_false('pas de majuscule', validatePassword('abcdefgh1'));
assert_false('pas de chiffre', validatePassword('Abcdefgh'));
assert_false('vide', validatePassword(''));
assert_true('valide: 8 car., majuscule, chiffre', validatePassword('Abcdefg1'));
assert_true('valide: long + complexe', validatePassword('MonSuperMotDePasse42'));
assert_true('valide: chiffre au début', validatePassword('1Abcdefgh'));

echo "\n=== Résultat: $passed réussis, $failed échoués ===\n";
exit($failed > 0 ? 1 : 0);
