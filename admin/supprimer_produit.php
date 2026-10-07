<?php
require_once '../includes/db.php';
require_once '../includes/fonctions.php';

// Vérifier si l'utilisateur est admin
if (!estConnecte() || !estAdmin()) {
    header('Location: ../index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer l'ID du produit
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: produits.php');
    exit();
}

// Récupérer les informations du produit
$stmt = $db->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch();

if (!$produit) {
    header('Location: produits.php');
    exit();
}

// Traitement de la suppression
if (isset($_POST['confirmer']) && $_POST['confirmer'] === 'oui') {
    try {
        // Supprimer l'image si elle existe
        if (!empty($produit['image']) && file_exists('../images/' . $produit['image'])) {
            unlink('../images/' . $produit['image']);
        }
        
        // Supprimer le produit
        $stmt = $db->prepare("DELETE FROM produits WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: ../produit_supprime.php');
        exit();
        
    } catch (Exception $e) {
        header('Location: produits.php?error=1');
        exit();
    }
}

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Supprimer un produit - TechShop Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#FDFBF7;color:#2C2C2C;min-height:100vh}
:root{--gold:#D4AF37;--gold-light:#E5C158;--gold-dark:#C5A028;--cream:#FDFBF7;--cream-dark:#F9F6F0;--cream-light:#FFF8E7;--dark:#2C2C2C;--border:#E8D5A3;--text-muted:#888;--red:#ef4444;--red-dark:#dc2626;--radius:12px;--radius-sm:8px}
.container{max-width:600px;margin:100px auto;padding:0 20px}
.delete-card{background:var(--cream-light);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.05)}
.delete-header{background:linear-gradient(135deg,#fff5f5,#fee);padding:30px;text-align:center;border-bottom:1px solid var(--border)}
.delete-header i{font-size:4rem;color:var(--red);margin-bottom:15px}
.delete-header h1{font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--red);margin-bottom:10px}
.delete-header p{color:var(--text-muted);font-size:0.9rem}
.delete-body{padding:30px}
.product-info{background:var(--cream-dark);border-radius:var(--radius-sm);padding:20px;margin-bottom:25px;display:flex;gap:20px;align-items:center}
.product-info img{width:100px;height:100px;object-fit:cover;border-radius:8px;border:1px solid var(--border)}
.product-details{flex:1}
.product-name{font-size:1.2rem;font-weight:600;color:var(--dark);margin-bottom:5px}
.product-brand{color:var(--gold);font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px}
.product-price{font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--gold-dark)}
.product-id{color:var(--text-muted);font-size:0.7rem;margin-top:5px}
.warning-box{background:rgba(239,68,68,0.08);border-left:3px solid var(--red);padding:15px 20px;border-radius:var(--radius-sm);margin-bottom:30px}
.warning-box p{color:var(--red-dark);font-size:0.85rem;display:flex;align-items:center;gap:10px}
.warning-box i{font-size:1.2rem}
.form-buttons{display:flex;gap:15px;justify-content:center}
.btn{padding:12px 32px;border-radius:50px;font-weight:600;font-size:0.9rem;cursor:pointer;transition:all 0.2s;text-decoration:none;display:inline-flex;align-items:center;gap:8px;border:none}
.btn-danger{background:var(--red);color:white;box-shadow:0 2px 10px rgba(239,68,68,0.3)}
.btn-danger:hover{background:var(--red-dark);transform:translateY(-2px)}
.btn-secondary{background:transparent;border:1px solid var(--border);color:var(--text-muted)}
.btn-secondary:hover{border-color:var(--gold);color:var(--gold);transform:translateY(-2px)}
@keyframes fadeIn{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
.delete-card{animation:fadeIn 0.3s ease}
@media(max-width:480px){.product-info{flex-direction:column;text-align:center}.product-info img{width:80px;height:80px}.form-buttons{flex-direction:column}.btn{justify-content:center}}
</style>
</head>
<body>

<div class="container">
    <div class="delete-card">
        <div class="delete-header">
            <i class='bx bxs-trash-alt'></i>
            <h1>Supprimer un produit</h1>
            <p>Cette action est irréversible</p>
        </div>
        
        <div class="delete-body">
            <div class="product-info">
                <?php 
                $imagePath = (!empty($produit['image']) && file_exists('../images/' . $produit['image'])) 
                    ? '../images/' . $produit['image'] 
                    : 'https://images.unsplash.com/photo-1587831990711-23ca6441447b?w=100&h=100&fit=crop';
                ?>
                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                <div class="product-details">
                    <div class="product-brand"><?= htmlspecialchars($produit['marque']) ?></div>
                    <div class="product-name"><?= htmlspecialchars($produit['nom']) ?></div>
                    <div class="product-price"><?= number_format($produit['prix'], 2, ',', ' ') ?> €</div>
                    <div class="product-id">Référence: #<?= $produit['id'] ?></div>
                </div>
            </div>
            
            <div class="warning-box">
                <p><i class='bx bx-error-circle'></i> Êtes-vous sûr de vouloir supprimer ce produit ? Cette action supprimera également l'image associée et ne pourra pas être annulée.</p>
            </div>
            
            <form method="POST" class="form-buttons">
                <input type="hidden" name="confirmer" value="oui">
                <button type="submit" class="btn btn-danger"><i class='bx bxs-trash'></i> Oui, supprimer</button>
                <a href="produits.php" class="btn btn-secondary"><i class='bx bx-x'></i> Annuler</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>