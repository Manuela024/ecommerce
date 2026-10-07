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

// Récupérer tous les produits
$produits = $db->query("SELECT * FROM produits ORDER BY id DESC")->fetchAll();

// Récupérer l'ID pour édition
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$produit_edit = null;

if ($edit_id > 0) {
    $stmt = $db->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([$edit_id]);
    $produit_edit = $stmt->fetch();
}

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Gestion des produits - TechShop Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css" rel="stylesheet"/>
<style>
*,::before,::after{box-sizing:border-box;margin:0;padding:0}
html{height:100%}
body{font-family:'Inter',sans-serif;background:#FDFBF7;color:#2C2C2C;min-height:100vh;display:flex;overflow-x:hidden}
a{text-decoration:none;color:inherit}
:root{
  --gold:#D4AF37;--gold-light:#E5C158;--gold-dark:#C5A028;
  --cream:#FDFBF7;--cream-dark:#F9F6F0;--cream-light:#FFF8E7;
  --dark:#2C2C2C;--border:#E8D5A3;--text-muted:#888;
  --green:#22c55e;--yellow:#eab308;--red:#ef4444;--blue:#3b82f6;
  --radius:12px;--radius-sm:8px;
}
.sidebar{
  width:240px;min-height:100vh;flex-shrink:0;
  background:var(--cream-dark);border-right:1px solid var(--border);
  position:fixed;top:0;left:0;bottom:0;display:flex;flex-direction:column;z-index:100;overflow-y:auto;
}
.sidebar-logo{padding:28px 24px;border-bottom:1px solid var(--border);font-family:'Bebas Neue',sans-serif;font-size:1.5rem;letter-spacing:2px;color:var(--dark)}
.sidebar-logo span{color:var(--gold)}
.sidebar-logo small{display:block;font-family:'Inter',sans-serif;font-size:.68rem;font-weight:500;letter-spacing:1.5px;color:var(--text-muted);text-transform:uppercase;margin-top:2px}
.sidebar-nav{padding:16px 12px;flex:1}
.nav-section{margin-bottom:24px}
.nav-section-label{font-size:.65rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--text-muted);padding:0 12px;margin-bottom:8px;display:block}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:var(--radius-sm);color:var(--text-muted);font-size:.83rem;font-weight:500;transition:all .2s;margin-bottom:2px}
.nav-item:hover{background:var(--cream-light);color:var(--gold)}
.nav-item.active{background:rgba(212,175,55,.12);color:var(--gold);border:1px solid rgba(212,175,55,.2)}
.nav-item i{font-size:1.1rem}
.sidebar-user{padding:16px 24px;border-top:1px solid var(--border);display:flex;align-items:center;gap:10px}
.user-avatar{width:34px;height:34px;border-radius:50%;background:rgba(212,175,55,.12);border:1px solid rgba(212,175,55,.3);display:flex;align-items:center;justify-content:center;font-size:.8rem;color:var(--gold);font-weight:600}
.user-name{font-size:.82rem;font-weight:600;color:var(--dark)}
.user-role{font-size:.7rem;color:var(--text-muted)}
.main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-height:100vh}
.topbar{position:sticky;top:0;z-index:90;background:rgba(253,251,247,.95);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);padding:0 32px;height:60px;display:flex;align-items:center;justify-content:space-between}
.topbar-left h1{font-family:'Bebas Neue',sans-serif;font-size:1.4rem;letter-spacing:1px;color:var(--dark)}
.topbar-left p{font-size:.75rem;color:var(--text-muted);margin-top:1px}
.topbar-btn{padding:8px 16px;border-radius:var(--radius-sm);border:1px solid var(--border);color:var(--text-muted);font-size:.78rem;font-weight:500;transition:all .2s;background:none;cursor:pointer}
.topbar-btn:hover{border-color:var(--gold);color:var(--gold)}
.topbar-btn.primary{background:var(--gold);border-color:var(--gold);color:var(--dark)}
.topbar-btn.primary:hover{background:var(--gold-light)}
.content{padding:28px 32px;flex:1}
.card{background:var(--cream-light);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:25px;overflow:hidden}
.card-header{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-header h3{font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--dark)}
.card-header h3 i{color:var(--gold);margin-right:8px}
.card-body{padding:22px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:15px}
.form-group{margin-bottom:15px}
.form-group label{display:block;font-size:.75rem;font-weight:600;color:var(--dark);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px}
.form-group input,.form-group select,.form-group textarea{
  width:100%;padding:10px 14px;
  background:var(--cream);border:1px solid var(--border);
  border-radius:var(--radius-sm);color:var(--dark);
  font-size:.85rem;transition:all .2s;
}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{
  outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(212,175,55,.1);
}
.form-buttons{display:flex;gap:12px;margin-top:20px}
.btn-save{background:var(--gold);color:var(--dark);border:none;padding:10px 24px;border-radius:var(--radius-sm);font-weight:600;cursor:pointer}
.btn-save:hover{background:var(--gold-light)}
.btn-cancel{background:transparent;border:1px solid var(--border);color:var(--text-muted);padding:10px 24px;border-radius:var(--radius-sm);font-weight:600;cursor:pointer}
.btn-cancel:hover{border-color:var(--red);color:var(--red)}
.table-wrapper{overflow-x:auto}
.admin-table{width:100%;border-collapse:collapse}
.admin-table th{font-size:.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--text-muted);padding:12px;text-align:left;border-bottom:1px solid var(--border)}
.admin-table td{padding:12px;border-bottom:1px solid var(--border);font-size:.8rem;color:var(--dark)}
.admin-table tr:hover td{background:rgba(212,175,55,.03)}
.product-thumb{width:50px;height:50px;object-fit:cover;border-radius:8px;background:var(--cream-dark)}
.stock-badge{display:inline-block;padding:4px 10px;border-radius:50px;font-size:.7rem;font-weight:600}
.stock-high{background:rgba(34,197,94,.12);color:var(--green)}
.stock-low{background:rgba(234,179,8,.12);color:var(--yellow)}
.stock-out{background:rgba(239,68,68,.12);color:var(--red)}
.actions{display:flex;gap:8px}
.btn-edit,.btn-delete{padding:6px 10px;border-radius:6px;font-size:.75rem;transition:all .2s;display:inline-flex;align-items:center;gap:4px}
.btn-edit{background:rgba(212,175,55,.12);color:var(--gold);border:1px solid rgba(212,175,55,.2)}
.btn-edit:hover{background:var(--gold);color:var(--dark)}
.btn-delete{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.2)}
.btn-delete:hover{background:var(--red);color:#fff}
.admin-footer{background:var(--cream-dark);border-top:1px solid var(--border);padding:20px 32px;text-align:center;margin-top:auto}
.admin-footer p{font-size:.75rem;color:var(--text-muted)}
.admin-footer span{color:var(--gold)}

/* FLASH MESSAGES */
.flash-message{
    position:fixed;top:80px;right:20px;z-index:1000;
    padding:14px 20px;border-radius:8px;
    display:flex;align-items:center;gap:12px;
    animation:slideIn 0.3s ease;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
    max-width:400px;
}
@keyframes slideIn{
    from{transform:translateX(100%);opacity:0;}
    to{transform:translateX(0);opacity:1;}
}
.flash-success{background:var(--cream-light);border-left:3px solid var(--green);border:1px solid rgba(34,197,94,.2)}
.flash-error{background:var(--cream-light);border-left:3px solid var(--red);border:1px solid rgba(239,68,68,.2)}
.flash-close{background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--text-muted);margin-left:10px}
.flash-close:hover{color:var(--gold)}

@media(max-width:768px){
    .sidebar{transform:translateX(-100%);position:fixed}
    .main{margin-left:0}
    .content{padding:16px}
    .flash-message{top:70px;right:10px;left:10px;max-width:none}
}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">Tech<span>Shop</span><small>Espace Admin</small></div>
  <nav class="sidebar-nav">
    <div class="nav-section"><span class="nav-section-label">Principal</span>
      <a href="dashboard.php" class="nav-item"><i class='bx bx-grid-alt'></i> Dashboard</a>
      <a href="commandes.php" class="nav-item"><i class='bx bx-package'></i> Commandes</a>
      <a href="produits.php" class="nav-item active"><i class='bx bx-store'></i> Produits</a>
      <a href="utilisateurs.php" class="nav-item"><i class='bx bx-group'></i> Utilisateurs</a>
    </div>
    <div class="nav-section"><span class="nav-section-label">Accès rapide</span>
      <a href="../index.php" class="nav-item"><i class='bx bx-link-external'></i> Voir le site</a>
      <a href="../deconnexion.php" class="nav-item"><i class='bx bx-log-out'></i> Déconnexion</a>
    </div>
  </nav>
  <div class="sidebar-user">
    <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_nom'],0,2)) ?></div>
    <div><div class="user-name"><?= htmlspecialchars($_SESSION['user_nom']) ?></div><div class="user-role">Administrateur</div></div>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <div class="topbar-left"><h1>Gestion des produits</h1><p>Ajouter, modifier ou supprimer des produits</p></div>
    <div class="topbar-right">
      <a href="../ajouter_produit.php" class="topbar-btn primary"><i class='bx bx-plus'></i> Nouveau produit</a>
    </div>
  </div>
  <div class="content">

    <!-- AFFICHAGE DES MESSAGES FLASH -->
    <?php $flash = getFlash(); ?>
    <?php if($flash): ?>
    <div class="flash-message flash-<?= $flash['type'] ?>">
        <span><?= $flash['type'] == 'success' ? '✅' : '⚠️' ?></span>
        <span style="flex:1"><?= htmlspecialchars($flash['message']) ?></span>
        <button class="flash-close" onclick="this.parentElement.remove()">✕</button>
    </div>
    <script>setTimeout(()=>{const m=document.querySelector('.flash-message');if(m)m.remove();},5000);</script>
    <?php endif; ?>

    <!-- Formulaire d'ajout/modification -->
    <div id="productForm" class="card" style="display: <?php echo $edit_id ? 'block' : 'none'; ?>;">
      <div class="card-header">
        <h3><i class='bx <?php echo $edit_id ? 'bx-edit-alt' : 'bx-plus-circle'; ?>'></i> <?php echo $edit_id ? 'Modifier le produit' : 'Ajouter un produit'; ?></h3>
        <button onclick="cancelForm()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--text-muted)">✕</button>
      </div>
      <div class="card-body">
        <form method="POST" action="../traiter_produit.php" enctype="multipart/form-data">
          <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
          <div class="form-row">
            <div class="form-group"><label>Nom du produit *</label><input type="text" name="nom" required value="<?php echo $produit_edit['nom'] ?? ''; ?>"></div>
            <div class="form-group"><label>Marque *</label><input type="text" name="marque" required value="<?php echo $produit_edit['marque'] ?? ''; ?>"></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label>Prix (€) *</label><input type="number" step="0.01" name="prix" required value="<?php echo $produit_edit['prix'] ?? ''; ?>"></div>
            <div class="form-group"><label>Stock *</label><input type="number" name="stock" required value="<?php echo $produit_edit['stock'] ?? 0; ?>"></div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Catégorie</label>
              <select name="type">
                <option value="ordinateur" <?php echo (isset($produit_edit['type']) && $produit_edit['type'] == 'ordinateur') ? 'selected' : ''; ?>>💻 Ordinateur</option>
                <option value="peripherique" <?php echo (isset($produit_edit['type']) && $produit_edit['type'] == 'peripherique') ? 'selected' : ''; ?>>🖱️ Périphérique</option>
                <option value="composant" <?php echo (isset($produit_edit['type']) && $produit_edit['type'] == 'composant') ? 'selected' : ''; ?>>⚙️ Composant</option>
                <option value="reseau" <?php echo (isset($produit_edit['type']) && $produit_edit['type'] == 'reseau') ? 'selected' : ''; ?>>🌐 Réseau</option>
              </select>
            </div>
            <div class="form-group">
              <label>Image</label>
              <input type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
              <?php if(!empty($produit_edit['image'])): ?>
                <small style="display:block;margin-top:5px">Image actuelle: <?php echo $produit_edit['image']; ?></small>
                <img src="../images/<?php echo $produit_edit['image']; ?>" style="width:50px;height:50px;object-fit:cover;margin-top:5px;border-radius:6px">
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group"><label>Description</label><textarea name="description" rows="4"><?php echo $produit_edit['description'] ?? ''; ?></textarea></div>
          <div class="form-buttons">
            <button type="submit" class="btn-save">💾 Enregistrer</button>
            <button type="button" class="btn-cancel" onclick="cancelForm()">Annuler</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Liste des produits -->
    <div class="card">
      <div class="card-header"><h3><i class='bx bx-list-ul'></i> Liste des produits</h3></div>
      <div class="card-body">
        <div class="table-wrapper">
          <table class="admin-table">
            <thead><tr><th>ID</th><th>Image</th><th>Nom</th><th>Marque</th><th>Prix</th><th>Stock</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach($produits as $produit): 
                $stockClass = $produit['stock'] <= 0 ? 'stock-out' : ($produit['stock'] < 5 ? 'stock-low' : 'stock-high');
                $stockText = $produit['stock'] <= 0 ? 'Rupture' : ($produit['stock'] < 5 ? 'Stock faible' : 'En stock');
                $imagePath = (!empty($produit['image']) && file_exists('../images/' . $produit['image'])) 
                    ? '../images/' . $produit['image'] 
                    : 'https://images.unsplash.com/photo-1587831990711-23ca6441447b?w=50&h=50&fit=crop';
              ?>
              <tr>
                <td>#<?= $produit['id'] ?></td>
                <td><img src="<?= $imagePath ?>" class="product-thumb"></td>
                <td><strong><?= htmlspecialchars($produit['nom']); ?></strong></td>
                <td><?= htmlspecialchars($produit['marque']); ?></td>
                <td style="color:var(--gold-dark);font-weight:600"><?= formatPrixFCFA($produit['prix']) ?></td>
                <td><span class="stock-badge <?= $stockClass ?>"><?= $stockText ?> (<?= $produit['stock'] ?>)</span></td>
                <td class="actions">
                  <a href="modifier_produit.php?id=<?= $produit['id']; ?>" class="btn-edit"><i class='bx bx-edit'></i></a>
                  <a href="supprimer_produit.php?id=<?= $produit['id']; ?>" class="btn-delete"><i class='bx bx-trash'></i></a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <footer class="admin-footer"><p>© 2026 <span>TechShop</span> — Tous droits réservés — Administration</p></footer>
</div>

<script>
function toggleForm() {
  const form = document.getElementById('productForm');
  form.style.display = form.style.display === 'none' ? 'block' : 'none';
  if (form.style.display === 'block' && window.location.href.includes('edit')) {
    window.location.href = 'produits.php';
  }
}
function cancelForm() {
  window.location.href = 'produits.php';
}
</script>
</body>
</html>