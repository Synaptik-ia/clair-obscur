<?php
// ajax/frais_port.php - Calcul AJAX des frais de port selon le pays

require_once '../config/database.php';
require_once '../includes/functions.php';

// Initialiser la réponse
$response = ['success' => false, 'frais_port' => 0, 'pays' => ''];

// Vérifier le paramètre pays
if (!isset($_GET['pays']) || empty($_GET['pays'])) {
    echo json_encode($response);
    exit();
}

$pays = trim($_GET['pays']);

// Calculer les frais de port
$frais_port = calculerFraisPort($pays);

$response['success'] = true;
$response['frais_port'] = $frais_port;
$response['pays'] = $pays;

echo json_encode($response);
exit();
?>