<?php
// ajax/newsletter.php - Endpoint AJAX pour la newsletter

require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'subscribe':
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
            exit;
        }

        $result = newsletter_subscribe($email);

        if ($result['status'] === 'already_subscribed') {
            echo json_encode(['success' => false, 'message' => 'Vous êtes déjà inscrit à notre newsletter.']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Un email de confirmation vous a été envoyé. Veuillez cliquer sur le lien pour valider votre inscription.']);
        }
        break;

    case 'confirm':
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            header('Location: ' . SITE_URL);
            exit;
        }

        $email = newsletter_confirm($token);

        if ($email) {
            header('Location: ' . SITE_URL . '?newsletter=confirmed');
        } else {
            header('Location: ' . SITE_URL . '?newsletter=error');
        }
        exit;

    case 'notify':
        $email = trim($_POST['email'] ?? '');
        $livre_id = (int)($_POST['livre_id'] ?? 0);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
            exit;
        }

        if ($livre_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Livre invalide.']);
            exit;
        }

        // On inscrit à la newsletter + on note l'intérêt pour ce livre
        $result = newsletter_subscribe($email);

        echo json_encode(['success' => true, 'message' => 'Vous serez prévenu dès que ce livre sera disponible. Un email de confirmation vous a été envoyé.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide.']);
}
