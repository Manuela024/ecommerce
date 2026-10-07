<?php
// Calculer le nombre d'articles dans le panier pour l'affichage
$nb_articles_panier = 0;
if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])) {
    $nb_articles_panier = array_sum($_SESSION['panier']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechShop - Matériel Informatique</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css" rel="stylesheet">
    <style>
        :root{
            --gold:#D4AF37;
            --gold-light:#E5C158;
            --gold-dark:#C5A028;
            --cream:#FDFBF7;
            --cream-dark:#F9F6F0;
            --cream-light:#FFF8E7;
            --dark:#2C2C2C;
            --dark2:#3A3A3A;
            --border:#E8D5A3;
            --text-muted:#888;
            --text-light:#666;
            --white:#fff;
            --radius:14px;
            --radius-sm:8px;
        }
        
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:var(--cream);color:var(--dark);}
        a{text-decoration:none;}
        
        /* NAVBAR */
        .navbar{
            position:fixed;top:0;left:0;right:0;z-index:999;
            padding:0 5%;height:68px;
            display:flex;align-items:center;justify-content:space-between;
            background:rgba(253,251,247,0.95);
            backdrop-filter:blur(16px);
            border-bottom:1px solid var(--border);
        }
        .logo a{
            font-family:'Bebas Neue',sans-serif;
            font-size:1.7rem;letter-spacing:2px;color:var(--dark);
        }
        .logo a span{color:var(--gold);}
        .nav-links{display:flex;align-items:center;gap:32px;list-style:none;}
        .nav-links li a{
            font-size:.85rem;font-weight:500;color:var(--text-muted);
            letter-spacing:.5px;transition:color .2s;
        }
        .nav-links li a:hover{color:var(--gold);}
        .panier-link{display:flex;align-items:center;gap:5px;}
        .panier-count{
            background:var(--gold);color:var(--dark);
            border-radius:50%;padding:2px 6px;font-size:.7rem;font-weight:600;
        }
        .user-name{color:var(--gold);font-weight:500;}
        .admin-link{color:var(--gold-dark) !important;}
        
        /* FLASH MESSAGES */
        .flash-message{
            position:fixed;top:80px;right:20px;z-index:1000;
            padding:12px 20px;border-radius:8px;
            display:flex;align-items:center;gap:10px;
            animation:slideIn 0.3s ease;
            box-shadow:0 4px 15px rgba(0,0,0,.1);
        }
        @keyframes slideIn{
            from{transform:translateX(100%);opacity:0;}
            to{transform:translateX(0);opacity:1;}
        }
        .flash-success{background:var(--cream-light);border-left:3px solid #27ae60;color:var(--dark);}
        .flash-error{background:var(--cream-light);border-left:3px solid #e74c3c;color:var(--dark);}
        .flash-close{
            background:none;border:none;font-size:1rem;
            cursor:pointer;color:var(--text-muted);margin-left:10px;
        }
        .flash-close:hover{color:var(--gold);}
        
        main{margin-top:68px;min-height:calc(100vh - 68px);}
        
        @media(max-width:768px){
            .navbar{padding:0 4%;}
            .nav-links{display:none;}
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>"><span>🖥️</span> Tech<span>Shop</span></a>
            </div>
            <ul class="nav-links">
                <li><a href="<?php echo SITE_URL; ?>catalogue.php">📦 Catalogue</a></li>
                <li class="panier-nav">
                    <a href="<?php echo SITE_URL; ?>panier.php" class="panier-link">
                        🛒 Panier
                        <?php if($nb_articles_panier > 0): ?>
                            <span class="panier-count"><?php echo $nb_articles_panier; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if(estConnecte()): ?>
                    <li><a href="<?php echo SITE_URL; ?>commande.php">📋 Mes commandes</a></li>
                    <li><span class="user-name">👋 <?php echo nettoyer($_SESSION['user_nom']); ?></span></li>
                    <?php if(estAdmin()): ?>
                        <li><a href="<?php echo SITE_URL; ?>admin/dashboard.php" class="admin-link">⚙️ Admin</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo SITE_URL; ?>deconnexion.php">🚪 Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="<?php echo SITE_URL; ?>login.php">🔑 Connexion</a></li>
                    <li><a href="<?php echo SITE_URL; ?>register.php">📝 Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <?php $flash = getFlash(); ?>
        <?php if($flash): ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?> flash-notification">
                <span class="flash-icon"><?php echo $flash['type'] == 'success' ? '✅' : '⚠️'; ?></span>
                <?php echo $flash['message']; ?>
                <button class="flash-close" onclick="this.parentElement.style.display='none'">✕</button>
            </div>
        <?php endif; ?>