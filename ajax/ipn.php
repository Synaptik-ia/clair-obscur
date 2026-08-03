<?php
// paiement/ipn.php - Instant Payment Notification (IPN) pour PayPal

// Ne pas inclure header/footer pour ce fichier - c'est un endpoint API
require_once '../config/database.php';

// Configuration PayPal
$paypal_url = (PAYPAL_MODE == 'live') ? 'https://www.paypal.com/cgi-bin/webscr' : 'https://www.sandbox.paypal.com/cgi-bin/webscr';

// Lire les données POST
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();

foreach ($raw_post_array as $keyval) {
    $keyval = explode ('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// Ajouter cmd=_notify-validate pour vérification
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
}

// Vérifier la transaction avec PayPal
$ch = curl_init($paypal_url);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

$res = curl_exec($ch);
curl_close($ch);

// Journalisation des IPN (pour débogage)
function logIpn($message) {
    $log_file = __DIR__ . '/../logs/ipn.log';
    $dir = dirname($log_file);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

logIpn("IPN reçu: " . print_r($myPost, true));

// Vérifier la réponse PayPal
if (strcmp($res, "VERIFIED") == 0) {
    logIpn("IPN VERIFIED");
    
    // Récupérer les données de la transaction
    $payment_status = $myPost['payment_status'] ?? '';
    $txn_id = $myPost['txn_id'] ?? '';
    $receiver_email = $myPost['receiver_email'] ?? '';
    $payer_email = $myPost['payer_email'] ?? '';
    $custom = $myPost['custom'] ?? ''; // Contient l'ID de commande
    $mc_gross = $myPost['mc_gross'] ?? 0;
    $mc_currency = $myPost['mc_currency'] ?? '';
    
    // Vérifier que le paiement est complet
    if ($payment_status == 'Completed') {
        // Extraire l'ID de commande du champ custom
        $commande_id = (int)$custom;
        
        if ($commande_id > 0) {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Vérifier que la commande existe et est en attente
            $sql_check = "SELECT id, reference, statut, montant_total FROM commandes WHERE id = :id";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->execute([':id' => $commande_id]);
            $commande = $stmt_check->fetch();
            
            if ($commande && $commande['statut'] == 'en_attente') {
                // Vérifier que le montant correspond
                if (abs($commande['montant_total'] - $mc_gross) < 0.01) {
                    // Mettre à jour le statut de la commande
                    $sql_update = "UPDATE commandes SET statut = 'paye' WHERE id = :id";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->execute([':id' => $commande_id]);
                    
                    // Enregistrer la transaction PayPal
                    $sql_trans = "INSERT INTO paypal_transactions (commande_id, txn_id, payer_email, payment_status, mc_gross, mc_currency, payment_date) 
                                  VALUES (:commande_id, :txn_id, :payer_email, :payment_status, :mc_gross, :mc_currency, NOW())";
                    $stmt_trans = $conn->prepare($sql_trans);
                    $stmt_trans->execute([
                        ':commande_id' => $commande_id,
                        ':txn_id' => $txn_id,
                        ':payer_email' => $payer_email,
                        ':payment_status' => $payment_status,
                        ':mc_gross' => $mc_gross,
                        ':mc_currency' => $mc_currency
                    ]);
                    
                    // Générer le lien de téléchargement si c'est un ebook
                    $sql_type = "SELECT type_commande FROM commandes WHERE id = :id";
                    $stmt_type = $conn->prepare($sql_type);
                    $stmt_type->execute([':id' => $commande_id]);
                    $type_info = $stmt_type->fetch();
                    
                    if ($type_info && $type_info['type_commande'] == 'ebook') {
                        genererLienTelechargement($commande_id, 0);
                    }
                    
                    logIpn("Commande #$commande_id mise à jour : payée");
                } else {
                    logIpn("ERREUR: Montant incorrect pour commande #$commande_id. Attendu: {$commande['montant_total']}, Reçu: $mc_gross");
                }
            } else {
                logIpn("ERREUR: Commande #$commande_id non trouvée ou déjà traitée");
            }
        } else {
            logIpn("ERREUR: ID commande invalide dans custom: $custom");
        }
    } else {
        logIpn("Paiement non complété. Statut: $payment_status");
    }
} else {
    logIpn("IPN INVALID - Transaction suspecte");
    // Optionnel: envoyer une alerte email
}

// Toujours répondre OK à PayPal
header('HTTP/1.1 200 OK');
?>