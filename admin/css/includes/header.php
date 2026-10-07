<?php
// C:\wamp64\www\ecommerce-informatique\includes\header.php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechShop - Matériel Informatique</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="logo">
            <h1>🖥️ TechShop</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="catalogue.php">Catalogue</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="panier.php">🛒 Panier</a></li>
                    <li><a href="commandes.php">Mes commandes</a></li>
                    <li><a href="deconnexion.php">Déconnexion (<?= $_SESSION['user_nom'] ?>)</a></li>
                    <?php if($_SESSION['user_role'] == 'admin'): ?>
                        <li><a href="admin/dashboard.php">🔧 Admin</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="login.php">Connexion</a></li>
                    <li><a href="register.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>