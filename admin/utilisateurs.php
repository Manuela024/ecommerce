<?php
require_once '../includes/db.php';
require_once '../includes/fonctions.php';

if (!estConnecte() || !estAdmin()) {
    setFlash("Accès non autorisé.", "error");
    rediriger('../index.php');
}

$database = new Database();
$db = $database->getConnection();

// Supprimer un utilisateur
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    if ($id == $_SESSION['user_id']) {
        setFlash("Vous ne pouvez pas supprimer votre propre compte.", "error");
    } else {
        $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        setFlash("Utilisateur supprimé avec succès.", "success");
    }
    rediriger('utilisateurs.php');
}

// Changer le rôle
if (isset($_GET['role']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $role = $_GET['role'] == 'admin' ? 'admin' : 'user';
    if ($id != $_SESSION['user_id']) {
        $stmt = $db->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?");
        $stmt->execute([$role, $id]);
        setFlash("Rôle de l'utilisateur modifié.", "success");
    }
    rediriger('utilisateurs.php');
}

$utilisateurs = $db->query("SELECT * FROM utilisateurs ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Gestion des utilisateurs - TechShop Admin</title>
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
.content{padding:28px 32px;flex:1}
.card{background:var(--cream-light);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
.card-header{padding:18px 22px;border-bottom:1px solid var(--border)}
.card-header h3{font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--dark)}
.card-header h3 i{color:var(--gold);margin-right:8px}
.card-body{padding:22px}
.table-wrapper{overflow-x:auto}
.admin-table{width:100%;border-collapse:collapse}
.admin-table th{font-size:.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--text-muted);padding:12px;text-align:left;border-bottom:1px solid var(--border)}
.admin-table td{padding:12px;border-bottom:1px solid var(--border);font-size:.8rem;color:var(--dark)}
.admin-table tr:hover td{background:rgba(212,175,55,.03)}
.role-badge{display:inline-block;padding:4px 12px;border-radius:50px;font-size:.7rem;font-weight:600}
.role-badge.admin{background:rgba(212,175,55,.12);color:var(--gold)}
.role-badge.user{background:rgba(59,130,246,.12);color:var(--blue)}
.badge-you{background:rgba(34,197,94,.12);color:var(--green);font-size:.65rem;padding:2px 6px;border-radius:50px;margin-left:8px}
.actions{display:flex;gap:8px}
.btn-role{padding:5px 10px;border-radius:6px;font-size:.7rem;transition:all .2s;background:rgba(212,175,55,.12);color:var(--gold);border:1px solid rgba(212,175,55,.2)}
.btn-role:hover{background:var(--gold);color:var(--dark)}
.btn-delete{padding:5px 10px;border-radius:6px;font-size:.7rem;transition:all .2s;background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.2)}
.btn-delete:hover{background:var(--red);color:#fff}
.text-muted{color:var(--text-muted);font-size:.7rem}
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
      <a href="commandes.php" class="nav-item"><i class='bx bx-package'></i> Commandes</a>
      <a href="produits.php" class="nav-item"><i class='bx bx-store'></i> Produits</a>
      <a href="utilisateurs.php" class="nav-item active"><i class='bx bx-group'></i> Utilisateurs</a>
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
    <div class="topbar-left"><h1>Gestion des utilisateurs</h1><p>Gérer les comptes clients et administrateurs</p></div>
  </div>
  <div class="content">
    <div class="card">
      <div class="card-header"><h3><i class='bx bx-group'></i> Liste des utilisateurs</h3></div>
      <div class="card-body">
        <div class="table-wrapper">
          <table class="admin-table">
            <thead><tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Inscrit le</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach($utilisateurs as $user): ?>
              <tr>
                <td>#<?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['nom']) ?><?php if($user['id'] == $_SESSION['user_id']): ?><span class="badge-you">(Vous)</span><?php endif; ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><span class="role-badge <?= $user['role'] ?>"><?= $user['role'] == 'admin' ? '👑 Admin' : '👤 Client' ?></span></td>
                <td><?= date('d/m/Y', strtotime($user['date_inscription'])) ?></td>
                <td class="actions">
                  <?php if($user['id'] != $_SESSION['user_id']): ?>
                    <?php if($user['role'] == 'admin'): ?>
                      <a href="utilisateurs.php?role=user&id=<?= $user['id'] ?>" class="btn-role" onclick="return confirm('Retirer les droits admin ?')">⬇️ Client</a>
                    <?php else: ?>
                      <a href="utilisateurs.php?role=admin&id=<?= $user['id'] ?>" class="btn-role" onclick="return confirm('Donner les droits admin ?')">⬆️ Admin</a>
                    <?php endif; ?>
                    <a href="utilisateurs.php?supprimer=<?= $user['id'] ?>" class="btn-delete" onclick="return confirm('Supprimer cet utilisateur ?')">🗑️</a>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
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
</body>
</html>