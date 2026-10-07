<?php
// Configuration email
function envoyerEmail($destinataire, $sujet, $message_html, $message_texte = '') {
    // Configuration - À MODIFIER selon votre hébergeur
    // Pour XAMPP/WAMP en local, vous devez utiliser une bibliothèque comme PHPMailer
    
    // Option 1: Utiliser mail() (nécessite un serveur SMTP configuré)
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: TechShop <noreply@techshop.com>\r\n";
    $headers .= "Reply-To: support@techshop.com\r\n";
    
    return mail($destinataire, $sujet, $message_html, $headers);
}
?>