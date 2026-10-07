<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce');
define('DB_USER', 'root');
define('DB_PASS', '');

// URL du site (à adapter selon ton environnement)
define('SITE_URL', 'http://localhost/ecommerce/');

// Configuration des erreurs (désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration du fuseau horaire
date_default_timezone_set('Europe/Paris');

// Configuration du jeu de caractères
ini_set('default_charset', 'UTF-8');
?>