<?php
require_once '../includes/db.php';
require_once '../includes/fonctions.php';

if (!estConnecte() || !estAdmin()) {
    setFlash("Accès non autorisé.", "error");
    rediriger('../index.php');
}

$database = new Database();
$db = $database->getConnection();

// Mettre à jour le statut
if (isset($_POST['update_statut'])) {
    $commande_id = (int)$_POST['commande_id'];
    $statut = nettoyer($_POST['statut']);
    $stmt = $db->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
    $stmt->execute([$statut, $commande_id]);
    setFlash("Statut de la commande mis à jour.", "success");
    rediriger('commandes.php');
}

// Voir une commande
$voir_id = isset($_GET['voir']) ? (int)$_GET['voir'] : 0;
$commande_detail = null;
$details_produits = [];

if ($voir_id > 0) {
    $stmt = $db->prepare("SELECT c.*, u.nom, u.email FROM commandes c JOIN utilisateurs u ON c.utilisateur_id = u.id WHERE c.id = ?");
    $stmt->execute([$voir_id]);
    $commande_detail = $stmt->fetch();
    if ($commande_detail) {
        $stmt = $db->prepare("SELECT dc.*, p.nom, p.image FROM details_commande dc JOIN produits p ON dc.produit_id = p.id WHERE dc.commande_id = ?");
        $stmt->execute([$voir_id]);
        $details_produits = $stmt->fetchAll();
    }
}

$commandes = $db->query("SELECT c.*, u.nom FROM commandes c JOIN utilisateurs u ON c.utilisateur_id = u.id ORDER BY c.date_commande DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Gestion des commandes - TechShop Admin</title>
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
.topbar-right a{display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:var(--radius-sm);border:1px solid var(--border);color:var(--text-muted);font-size:.78rem;font-weight:500;transition:all .2s}
.topbar-right a:hover{border-color:var(--gold);color:var(--gold)}
.content{padding:28px 32px;flex:1}
.card{background:var(--cream-light);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:25px;overflow:hidden}
.card-header{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-header h3{font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--dark)}
.card-header h3 i{color:var(--gold);margin-right:8px}
.card-body{padding:22px}
.btn-back{padding:8px 16px;border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text-muted);font-size:.75rem;transition:all .2s}
.btn-back:hover{border-color:var(--gold);color:var(--gold)}
.commande-info{background:var(--cream-dark);border-radius:var(--radius-sm);padding:20px;margin-bottom:20px}
.commande-info p{margin:8px 0;font-size:.85rem}
.statut-form{display:flex;align-items:center;gap:12px;margin-top:15px}
.statut-form select{padding:10px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--cream);color:var(--dark)}
.btn-update{padding:10px 20px;background:var(--gold);color:var(--dark);border:none;border-radius:var(--radius-sm);font-weight:600;cursor:pointer}
.btn-update:hover{background:var(--gold-light)}
.table-wrapper{overflow-x:auto}
.admin-table{width:100%;border-collapse:collapse}
.admin-table th{font-size:.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--text-muted);padding:12px;text-align:left;border-bottom:1px solid var(--border)}
.admin-table td{padding:12px;border-bottom:1px solid var(--border);font-size:.8rem;color:var(--dark)}
.admin-table tr:hover td{background:rgba(212,175,55,.03)}
.product-thumb{width:40px;height:40px;object-fit:cover;border-radius:6px}
.statut-badge{display:inline-block;padding:4px 12px;border-radius:50px;font-size:.7rem;font-weight:600}
.statut-badge.en_attente{background:rgba(234,179,8,.12);color:var(--yellow)}
.statut-badge.validee{background:rgba(34,197,94,.12);color:var(--green)}
.statut-badge.livree{background:rgba(59,130,246,.12);color:var(--blue)}
.statut-badge.annulee{background:rgba(239,68,68,.12);color:var(--red)}
.btn-small{padding:5px 12px;border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text-muted);font-size:.7rem;transition:all .2s}
.btn-small:hover{border-color:var(--gold);color:var(--gold)}
.admin-footer{background:var(--cream-dark);border-top:1px solid var(--border);padding:20px 32px;text-align:center;margin-top:auto}
.admin-footer p{font-size:.75rem;color:var(--text-muted)}
.admin-footer span{color:var(--gold)}
@media(max-width:768px){.sidebar{transform:translateX(-100%);position:fixed}.main{margin-left:0}.content{padding:16px}}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">Tech<span>Shop</span><small>Espace Admin</small></div>
  <nav class="sidebar-nav">
    <div class="nav-section"><span class="nav-section-label">Principal</span>
      <a href="dashboard.php" class="nav-item"><i class='bx bx-grid-alt'></i> Dashboard</a>
      <a href="commandes.php" class="nav-item active"><i class='bx bx-package'></i> Commandes</a>
      <a href="produits.php" class="nav-item"><i class='bx bx-store'></i> Produits</a>
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
    <div class="topbar-left"><h1>Gestion des commandes</h1><p>Suivre et modifier le statut des commandes</p></div>
    <div class="topbar-right"><a href="../index.php"><i class='bx bx-store'></i> Voir la boutique</a></div>
  </div>
  <div class="content">

    <?php if ($commande_detail): ?>
      <div class="card">
        <div class="card-header">
          <h3><i class='bx bx-detail'></i> Détail de la commande #<?= $commande_detail['id'] ?></h3>
          <a href="commandes.php" class="btn-back">← Retour</a>
        </div>
        <div class="card-body">
          <div class="commande-info">
            <p><strong>👤 Client :</strong> <?= htmlspecialchars($commande_detail['nom']) ?> (<?= $commande_detail['email'] ?>)</p>
            <p><strong>📅 Date :</strong> <?= date('d/m/Y H:i', strtotime($commande_detail['date_commande'])) ?></p>
            <p><strong>💰 Total :</strong> <span style="color:var(--gold-dark);font-weight:700"><?= number_format($commande_detail['total'], 2, ',', ' ') ?> €</span></p>
            <form method="POST" class="statut-form">
              <input type="hidden" name="commande_id" value="<?= $commande_detail['id'] ?>">
              <label><strong>Statut :</strong></label>
              <select name="statut">
                <option value="en_attente" <?= $commande_detail['statut'] == 'en_attente' ? 'selected' : '' ?>>⏳ En attente</option>
                <option value="validee" <?= $commande_detail['statut'] == 'validee' ? 'selected' : '' ?>>✅ Validée</option>
                <option value="livree" <?= $commande_detail['statut'] == 'livree' ? 'selected' : '' ?>>🚚 Livrée</option>
                <option value="annulee" <?= $commande_detail['statut'] == 'annulee' ? 'selected' : '' ?>>❌ Annulée</option>
              </select>
              <button type="submit" name="update_statut" class="btn-update">Mettre à jour</button>
            </form>
          </div>
          <h4 style="margin-bottom:15px">🛍️ Produits commandés</h4>
          <div class="table-wrapper">
            <table class="admin-table">
              <thead><tr><th>Image</th><th>Produit</th><th>Prix unitaire</th><th>Quantité</th><th>Total</th></tr></thead>
              <tbody>
                <?php foreach($details_produits as $item): ?>
                <tr>
                  <td><img src="../images/<?= $item['image'] ?? 'placeholder.jpg' ?>" class="product-thumb"></td>
                  <td><strong><?= htmlspecialchars($item['nom']) ?></strong></td>
                  <td><?= number_format($item['prix_unitaire'], 2, ',', ' ') ?> €</td>
                  <td><?= $item['quantite'] ?></td>
                  <td style="color:var(--gold-dark);font-weight:600"><?= number_format($item['prix_unitaire'] * $item['quantite'], 2, ',', ' ') ?> €</td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="card">
        <div class="card-header"><h3><i class='bx bx-list-ul'></i> Toutes les commandes</h3></div>
        <div class="card-body">
          <div class="table-wrapper">
            <table class="admin-table">
              <thead><tr><th>N°</th><th>Client</th><th>Date</th><th>Total</th><th>Statut</th><th>Action</th></tr></thead>
              <tbody>
                <?php foreach($commandes as $commande): ?>
                <tr>
                  <td>#<?= $commande['id'] ?></td>
                  <td><strong><?= htmlspecialchars($commande['nom']) ?></strong></td>
                  <td><?= date('d/m/Y', strtotime($commande['date_commande'])) ?></td>
                 <td style="color:var(--gold-dark);font-weight:600"><?= formatPrixFCFA($commande['total']) ?>FCFA</td>
                  <td><span class="statut-badge <?= $commande['statut'] ?>"><?= $commande['statut'] ?></span></td>
                  <td><a href="commandes.php?voir=<?= $commande['id'] ?>" class="btn-small">Voir détail</a></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <footer class="admin-footer"><p>© 2026 <span>TechShop</span> — Tous droits réservés — Administration</p></footer>
</div>
</body>
</html>