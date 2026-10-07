<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

if (!estConnecte() || !estAdmin()) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produit ajouté - TechShop</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#FDFBF7;color:#2C2C2C;min-height:100vh;display:flex;align-items:center;justify-content:center}
:root{--gold:#D4AF37;--gold-light:#E5C158;--dark:#2C2C2C;--border:#E8D5A3;--green:#22c55e;--radius:16px}
.success-container{text-align:center;padding:40px;background:#FFF8E7;border-radius:var(--radius);border:1px solid var(--border);max-width:500px;margin:20px}
.success-icon{width:80px;height:80px;background:rgba(34,197,94,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 25px}
.success-icon i{font-size:3.5rem;color:var(--green)}
.success-title{font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--dark);margin-bottom:10px}
.success-message{color:#888;margin-bottom:30px}
.btn-group{display:flex;gap:15px;justify-content:center}
.btn{padding:12px 25px;border-radius:50px;font-weight:600;text-decoration:none;transition:all 0.3s}
.btn-primary{background:var(--gold);color:var(--dark)}
.btn-primary:hover{background:var(--gold-light);transform:translateY(-2px)}
.btn-secondary{background:transparent;border:1px solid var(--border);color:#888}
.btn-secondary:hover{border-color:var(--gold);color:var(--gold)}
.countdown{color:#888;font-size:0.8rem;margin-top:20px}
</style>
</head>
<body>
<div class="success-container">
    <div class="success-icon"><i class='bx bxs-check-circle'></i></div>
    <h1 class="success-title">Produit ajouté !</h1>
    <p class="success-message">Le produit a été enregistré avec succès dans la base de données.</p>
    <div class="btn-group">
        <a href="admin/produits.php" class="btn btn-primary"><i class='bx bx-list-ul'></i> Voir la liste</a>
        <a href="admin/produits.php" class="btn btn-secondary"><i class='bx bx-plus'></i> Ajouter un autre</a>
    </div>
    <p class="countdown">Redirection automatique dans <span id="countdown">3</span> secondes...</p>
</div>
<script>
let seconds = 3;
const timer = setInterval(() => {
    seconds--;
    document.getElementById('countdown').textContent = seconds;
    if (seconds <= 0) { clearInterval(timer); window.location.href = 'admin/produits.php'; }
}, 1000);
</script>
</body>
</html>