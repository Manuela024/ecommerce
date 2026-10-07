<?php
require_once 'includes/fonctions.php';

// Détruire la session
$_SESSION = array();
session_destroy();

setFlash("Vous avez été déconnecté.", "success");
rediriger('login.php');
?>