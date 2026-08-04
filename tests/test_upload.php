<?php
// tests/test_upload.php - Test de la validation des extensions d'upload

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

echo "=== Test: Validation des extensions d'upload ===\n";

// Simuler la validation d'extension (même logique que dans les fichiers admin)
$allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

function validateExtension($filename, $allowed) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowed);
}

// Tests extensions valides
assert_true('jpg accepté', validateExtension('photo.jpg', $allowed_extensions));
assert_true('jpeg accepté', validateExtension('photo.jpeg', $allowed_extensions));
assert_true('png accepté', validateExtension('photo.png', $allowed_extensions));
assert_true('webp accepté', validateExtension('photo.webp', $allowed_extensions));
assert_true('gif accepté', validateExtension('photo.gif', $allowed_extensions));
assert_true('JPG majuscule accepté', validateExtension('photo.JPG', $allowed_extensions));
assert_true('PNG majuscule accepté', validateExtension('photo.PNG', $allowed_extensions));

// Tests extensions rejetées
assert_false('php rejeté', validateExtension('shell.php', $allowed_extensions));
assert_false('phtml rejeté', validateExtension('shell.phtml', $allowed_extensions));
assert_false('exe rejeté', validateExtension('virus.exe', $allowed_extensions));
assert_false('html rejeté', validateExtension('page.html', $allowed_extensions));
assert_false('js rejeté', validateExtension('script.js', $allowed_extensions));
assert_false('sans extension rejeté', validateExtension('noextension', $allowed_extensions));
assert_false('double extension rejetée', validateExtension('photo.jpg.php', $allowed_extensions));

echo "\n=== Résultat: $passed réussis, $failed échoués ===\n";
exit($failed > 0 ? 1 : 0);
