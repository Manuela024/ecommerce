<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

// Vérifier si l'utilisateur est admin
if (!estConnecte() || !estAdmin()) {
    header('Location: index.php');
    exit();
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Erreur - TechShop</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#FDFBF7;color:#2C2C2C;min-height:100vh;display:flex;align-items:center;justify-content:center}
:root{--gold:#D4AF37;--gold-light:#E5C158;--gold-dark:#C5A028;--cream:#FDFBF7;--cream-dark:#F9F6F0;--cream-light:#FFF8E7;--dark:#2C2C2C;--border:#E8D5A3;--red:#ef4444;--radius:16px}
.error-container{text-align:center;padding:40px;background:var(--cream-light);border-radius:var(--radius);border:1px solid var(--border);max-width:500px;margin:20px;box-shadow:0 20px 40px rgba(0,0,0,0.05)}
.error-icon{width:80px;height:80px;background:rgba(239,68,68,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 25px}
.error-icon i{font-size:3.5rem;color:var(--red)}
.error-title{font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--dark);margin-bottom:10px}
.error-message{color:var(--text-muted);margin-bottom:30px}
.btn-group{display:flex;gap:15px;justify-content:center}
.btn{padding:12px 25px;border-radius:50px;font-weight:600;text-decoration:none;transition:all 0.3s}
.btn-primary{background:var(--gold);color:var(--dark)}
.btn-primary:hover{background:var(--gold-light);transform:translateY(-2px)}
.btn-secondary{background:transparent;border:1px solid var(--border);color:var(--text-muted)}
.btn-secondary:hover{border-color:var(--gold);color:var(--gold)}
</style>
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
<div class="error-container">
    <div class="error-icon">
        <i class='bx bxs-error-circle'></i>
    </div>
    <h1 class="error-title">Une erreur est survenue !</h1>
    <p class="error-message"><?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : "L'opération n'a pas pu être effectuée. Veuillez réessayer."; ?></p>
    <div class="btn-group">
        <a href="admin/produits.php" class="btn btn-primary"><i class='bx bx-arrow-back'></i> Retour</a>
        <a href="admin/dashboard.php" class="btn btn-secondary"><i class='bx bx-home'></i> Dashboard</a>
    </div>
</div>
</body>
</html>