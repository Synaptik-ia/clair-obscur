<?php
// tests/runtests.php - Lance tous les tests

$test_files = [
    'test_env.php',
    'test_csrf.php',
    'test_session.php',
    'test_upload.php',
    'test_cleanxss.php',
    'test_password.php',
    'test_database.php',
];

$total_passed = 0;
$total_failed = 0;
$results = [];

echo "========================================\n";
echo "  Clair-Obscur — Tests P0/P1/P2\n";
echo "========================================\n\n";

foreach ($test_files as $file) {
    echo "--- $file ---\n";
    $output = [];
    $exit_code = 0;
    exec('php ' . __DIR__ . '/' . $file . ' 2>&1', $output, $exit_code);
    echo implode("\n", $output) . "\n";

    // Parse results
    foreach ($output as $line) {
        if (preg_match('/(\d+) réussis, (\d+) échoués/', $line, $m)) {
            $total_passed += (int)$m[1];
            $total_failed += (int)$m[2];
        }
    }

    if ($exit_code !== 0) {
        $results[$file] = 'ÉCHEC';
    } else {
        $results[$file] = 'OK';
    }
    echo "\n";
}

echo "========================================\n";
echo "  Résumé\n";
echo "========================================\n";
foreach ($results as $file => $status) {
    $icon = $status === 'OK' ? '[PASS]' : '[FAIL]';
    echo "  $icon $file\n";
}
echo "\nTotal: $total_passed réussis, $total_failed échoués\n";
exit($total_failed > 0 ? 1 : 0);
