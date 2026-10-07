<?php
// Configuration email (à adapter selon votre environnement)
function envoyerEmail($destinataire, $sujet, $message) {
    // Pour l'environnement local, on simule l'envoi
    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
        // En développement, on enregistre dans un fichier log
        $log = date('Y-m-d H:i:s') . " - Destinataire: $destinataire - Sujet: $sujet\n";
        $log .= "Message: $message\n\n";
        file_put_contents(__DIR__ . '/../logs/emails.log', $log, FILE_APPEND);
        return true;
    }
    
    // En production, utilisez PHPMailer ou mail()
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: TechShop <noreply@techshop.ci>\r\n";
    
    return mail($destinataire, $sujet, $message, $headers);
}

// Fonction pour générer un token de réinitialisation (si besoin spécifique)
// Note: La fonction genererToken() est déjà dans fonctions.php
// Donc on ne la redéclare pas ici
?>