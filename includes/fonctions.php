<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
function estConnecte() {
    return isset($_SESSION['user_id']);
}

// Vérifier si l'utilisateur est admin
function estAdmin() {
    // Correction : utiliser 'user_role' au lieu de 'role'
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Récupérer le panier
function obtenirPanier() {
    return isset($_SESSION['panier']) ? $_SESSION['panier'] : [];
}

// Mettre à jour le panier
function mettreAJourPanier($panier) {
    $_SESSION['panier'] = $panier;
}

// Nettoyer une chaîne
function nettoyer($chaine) {
    return htmlspecialchars(trim($chaine), ENT_QUOTES, 'UTF-8');
}

// Rediriger
function rediriger($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

// Afficher message flash
function setFlash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Générer un token aléatoire
function genererToken() {
    return bin2hex(random_bytes(32));
}

// ===== NOUVELLE FONCTION POUR LE FCFA =====
/**
 * Convertit un prix en Euros en FCFA et le formate
 * @param float $prix_euros Prix en euros
 * @param bool $symbole Afficher "FCFA" ou non
 * @return string Prix formaté en FCFA
 */
function formatPrixFCFA($prix_euros, $symbole = true) {
    // Taux de conversion fixe (1€ = 655,957 FCFA)
    $taux_conversion = 655.957;
    
    // Conversion en FCFA
    $prix_fcfa = $prix_euros * $taux_conversion;
    
    // Arrondir à l'entier le plus proche (pas de centimes en FCFA pour l'affichage)
    $prix_fcfa = round($prix_fcfa);
    
    // Formater avec espace comme séparateur de milliers
    $prix_formate = number_format($prix_fcfa, 0, ',', ' ');
    
    // Ajouter le symbole FCFA si demandé
    if ($symbole) {
        return $prix_formate . ' FCFA';
    }
    
    return $prix_formate;
}

/**
 * Convertit un prix FCFA en Euros (utile pour les calculs)
 * @param float $prix_fcfa Prix en FCFA
 * @return float Prix en euros
 */
function convertirFCFAEnEuros($prix_fcfa) {
    $taux_conversion = 655.957;
    return $prix_fcfa / $taux_conversion;
}

/**
 * Formate un prix FCFA sans conversion (si déjà en FCFA)
 * @param float $prix_fcfa Prix en FCFA
 * @return string Prix formaté
 */
function formaterFCFA($prix_fcfa) {
    return number_format($prix_fcfa, 0, ',', ' ') . ' FCFA';
}
?>