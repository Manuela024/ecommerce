<?php
require_once '../includes/db.php';
require_once '../includes/fonctions.php';

if (!estConnecte() || !estAdmin()) {
    setFlash("Accès non autorisé.", "error");
    rediriger('../index.php');
}

$database = new Database();
$db = $database->getConnection();

$total_produits = $db->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$total_users    = $db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$total_commandes= $db->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
$ca_total       = $db->query("SELECT SUM(total) FROM commandes WHERE statut != 'annulee'")->fetchColumn();
$ca_total       = $ca_total ? number_format($ca_total, 0, ',', ' ') : '0';
$commandes_recentes = $db->query("SELECT c.*, u.nom FROM commandes c JOIN utilisateurs u ON c.utilisateur_id = u.id ORDER BY c.date_commande DESC LIMIT 6")->fetchAll();
$stock_faible   = $db->query("SELECT * FROM produits WHERE stock < 5 AND stock > 0 ORDER BY stock ASC LIMIT 5")->fetchAll();
$rupture        = $db->query("SELECT * FROM produits WHERE stock <= 0 LIMIT 5")->fetchAll();
$ca_mois        = $db->query("SELECT SUM(total) FROM commandes WHERE MONTH(date_commande)=MONTH(NOW()) AND YEAR(date_commande)=YEAR(NOW()) AND statut!='annulee'")->fetchColumn();
$ca_mois        = $ca_mois ? number_format($ca_mois, 0, ',', ' ') : '0';
$cmd_pending    = $db->query("SELECT COUNT(*) FROM commandes WHERE statut='en_attente'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Dashboard Admin — TechShop</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css" rel="stylesheet"/>
<style>
*,::before,::after{box-sizing:border-box;margin:0;padding:0}
html{height:100%}
body{font-family:'Inter',sans-serif;background:#FDFBF7;color:#2C2C2C;min-height:100vh;display:flex;overflow-x:hidden}
a{text-decoration:none;color:inherit}
:root{
  --gold:#D4AF37;
  --gold-light:#E5C158;
  --gold-dark:#C5A028;
  --cream:#FDFBF7;
  --cream-dark:#F9F6F0;
  --cream-light:#FFF8E7;
  --dark:#2C2C2C;
  --dark2:#3A3A3A;
  --dark3:#4A4A4A;
  --dark4:#5A5A5A;
  --border:#E8D5A3;
  --border2:#D4AF37;
  --text-muted:#888;
  --text-light:#666;
  --white:#fff;
  --green:#22c55e;
  --yellow:#eab308;
  --red:#ef4444;
  --blue:#3b82f6;
  --radius:12px;
  --radius-sm:8px;
}

/* SIDEBAR */
.sidebar{
  width:240px;min-height:100vh;flex-shrink:0;
  background:var(--cream-dark);border-right:1px solid var(--border);
  position:fixed;top:0;left:0;bottom:0;
  display:flex;flex-direction:column;z-index:100;
  overflow-y:auto;
}
.sidebar-logo{
  padding:28px 24px;border-bottom:1px solid var(--border);
  font-family:'Bebas Neue',sans-serif;font-size:1.5rem;letter-spacing:2px;color:var(--dark);
}
.sidebar-logo span{color:var(--gold)}
.sidebar-logo small{display:block;font-family:'Inter',sans-serif;font-size:.68rem;font-weight:500;letter-spacing:1.5px;color:var(--text-muted);text-transform:uppercase;margin-top:2px}
.sidebar-nav{padding:16px 12px;flex:1}
.nav-section{margin-bottom:24px}
.nav-section-label{font-size:.65rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--text-muted);padding:0 12px;margin-bottom:8px;display:block}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:var(--radius-sm);color:var(--text-muted);font-size:.83rem;font-weight:500;transition:all .2s;margin-bottom:2px;cursor:pointer}
.nav-item:hover{background:var(--cream-light);color:var(--gold)}
.nav-item.active{background:rgba(212,175,55,.12);color:var(--gold);border:1px solid rgba(212,175,55,.2)}
.nav-item i{font-size:1.1rem;flex-shrink:0}
.nav-badge{margin-left:auto;background:var(--gold);color:var(--dark);font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:50px}
.nav-badge.yellow{background:var(--yellow)}
.sidebar-user{padding:16px 24px;border-top:1px solid var(--border);display:flex;align-items:center;gap:10px}
.user-avatar{width:34px;height:34px;border-radius:50%;background:rgba(212,175,55,.12);border:1px solid rgba(212,175,55,.3);display:flex;align-items:center;justify-content:center;font-size:.8rem;color:var(--gold);font-weight:600;flex-shrink:0}
.user-name{font-size:.82rem;font-weight:600;color:var(--dark)}
.user-role{font-size:.7rem;color:var(--text-muted)}

/* MAIN */
.main{
  margin-left:240px;
  flex:1;
  display:flex;
  flex-direction:column;
  min-height:100vh;
}

/* TOP BAR */
.topbar{
  position:sticky;top:0;z-index:90;
  background:rgba(253,251,247,.95);backdrop-filter:blur(12px);
  border-bottom:1px solid var(--border);
  padding:0 32px;height:60px;
  display:flex;align-items:center;justify-content:space-between;
}
.topbar-left h1{font-family:'Bebas Neue',sans-serif;font-size:1.4rem;letter-spacing:1px;color:var(--dark)}
.topbar-left p{font-size:.75rem;color:var(--text-muted);margin-top:1px}
.topbar-right{display:flex;align-items:center;gap:12px}
.topbar-btn{
  display:flex;align-items:center;gap:7px;
  padding:8px 16px;border-radius:var(--radius-sm);
  border:1px solid var(--border);color:var(--text-muted);
  font-size:.78rem;font-weight:500;transition:all .2s;
}
.topbar-btn:hover{border-color:var(--gold);color:var(--gold)}
.topbar-btn.primary{background:var(--gold);border-color:var(--gold);color:var(--dark)}
.topbar-btn.primary:hover{background:var(--gold-light)}
.topbar-date{font-size:.75rem;color:var(--text-muted);padding:8px 12px;background:var(--cream-light);border-radius:var(--radius-sm);border:1px solid var(--border)}

/* CONTENT */
.content{
  padding:28px 32px;
  flex:1;
}

/* STATS GRID */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px}
.stat-card{
  background:var(--cream-light);border:1px solid var(--border);border-radius:var(--radius);
  padding:22px 24px;position:relative;overflow:hidden;
  transition:border-color .2s;cursor:default;
}
.stat-card:hover{border-color:var(--gold)}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px}
.stat-card.blue::before{background:var(--blue)}
.stat-card.orange::before{background:var(--gold)}
.stat-card.green::before{background:var(--green)}
.stat-card.yellow::before{background:var(--yellow)}
.stat-label{font-size:.72rem;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--text-muted);margin-bottom:14px;display:flex;align-items:center;justify-content:space-between}
.stat-icon{font-size:1rem;opacity:.6}
.stat-num{font-family:'Bebas Neue',sans-serif;font-size:2.4rem;letter-spacing:1px;color:var(--dark);line-height:1;margin-bottom:8px}
.stat-num.orange{color:var(--gold)}
.stat-trend{font-size:.72rem;color:var(--text-muted);display:flex;align-items:center;gap:4px}
.trend-up{color:var(--green)}
.trend-down{color:var(--red)}

/* GRID 2-COL */
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
.grid-3{display:grid;grid-template-columns:2fr 1fr;gap:20px}

/* CARD */
.card{background:var(--cream-light);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
.card-header{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:.82rem;font-weight:600;color:var(--dark);display:flex;align-items:center;gap:8px}
.card-title i{color:var(--gold);font-size:1rem}
.card-action{font-size:.75rem;color:var(--text-muted);transition:color .2s}
.card-action:hover{color:var(--gold)}
.card-body{padding:20px 22px}

/* TABLE */
.data-table{width:100%;border-collapse:collapse}
.data-table th{font-size:.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--text-muted);padding:0 12px 12px;text-align:left;border-bottom:1px solid var(--border)}
.data-table td{padding:13px 12px;border-bottom:1px solid var(--border);font-size:.82rem;color:var(--text-muted)}
.data-table tr:last-child td{border-bottom:none}
.data-table tr:hover td{background:rgba(212,175,55,.05);color:var(--dark)}
.td-name{color:var(--dark);font-weight:500}
.td-id{color:var(--text-muted);font-family:'Bebas Neue',sans-serif;letter-spacing:1px;font-size:.9rem}

.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:50px;font-size:.68rem;font-weight:600;letter-spacing:.5px}
.badge-pending{background:rgba(234,179,8,.12);color:var(--yellow);border:1px solid rgba(234,179,8,.2)}
.badge-processing{background:rgba(59,130,246,.12);color:var(--blue);border:1px solid rgba(59,130,246,.2)}
.badge-shipped{background:rgba(99,102,241,.12);color:#818cf8;border:1px solid rgba(99,102,241,.2)}
.badge-delivered{background:rgba(34,197,94,.12);color:var(--green);border:1px solid rgba(34,197,94,.2)}
.badge-cancelled{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.2)}

.btn-sm{display:inline-flex;align-items:center;gap:4px;padding:5px 11px;border-radius:6px;font-size:.72rem;font-weight:500;border:1px solid var(--border);color:var(--text-muted);transition:all .2s}
.btn-sm:hover{border-color:var(--gold);color:var(--gold)}

/* STOCK ITEMS */
.stock-item{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border)}
.stock-item:last-child{border-bottom:none}
.stock-name{font-size:.82rem;color:var(--dark);font-weight:500;margin-bottom:2px}
.stock-num{font-size:.72rem}
.stock-bar-wrap{width:80px;height:4px;background:var(--cream-dark);border-radius:2px;overflow:hidden}
.stock-bar{height:100%;border-radius:2px}
.stock-bar.low{background:var(--yellow)}
.stock-bar.out{background:var(--red)}

/* RUPTURE */
.rupture-item{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)}
.rupture-item:last-child{border-bottom:none}
.rupture-dot{width:8px;height:8px;border-radius:50%;background:var(--red);flex-shrink:0}
.rupture-name{font-size:.82rem;color:var(--text-muted);flex:1}
.rupture-tag{font-size:.68rem;color:var(--red);background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);padding:2px 8px;border-radius:50px}

.empty-msg{text-align:center;padding:32px;color:var(--text-muted);font-size:.83rem}
.empty-icon{font-size:1.8rem;margin-bottom:8px;display:block}

/* ADMIN FOOTER */
.admin-footer{
  background:var(--cream-dark);
  border-top:1px solid var(--border);
  padding:20px 32px;
  text-align:center;
  margin-top:auto;
}
.admin-footer p{
  font-size:.75rem;
  color:var(--text-muted);
}
.admin-footer span{
  color:var(--gold);
}

@media(max-width:1024px){
  .stats-grid{grid-template-columns:repeat(2,1fr)}
  .grid-2{grid-template-columns:1fr}
  .grid-3{grid-template-columns:1fr}
}
@media(max-width:768px){
  .sidebar{transform:translateX(-100%);position:fixed}
  .main{margin-left:0}
  .content{padding:16px}
  .topbar{padding:0 16px}
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    Tech<span>Shop</span>
    <small>Espace Admin</small>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">
      <span class="nav-section-label">Principal</span>
      <a href="dashboard.php" class="nav-item active"><i class='bx bx-grid-alt'></i> Dashboard</a>
      <a href="commandes.php" class="nav-item">
        <i class='bx bx-package'></i> Commandes
        <?php if($cmd_pending > 0): ?><span class="nav-badge"><?= $cmd_pending ?></span><?php endif; ?>
      </a>
      <a href="produits.php" class="nav-item"><i class='bx bx-store'></i> Produits</a>
      <a href="utilisateurs.php" class="nav-item"><i class='bx bx-group'></i> Utilisateurs</a>
    </div>
    <div class="nav-section">
      <span class="nav-section-label">Gestion stock</span>
      <?php if(count($stock_faible) > 0): ?>
      <a href="produits.php?filter=low_stock" class="nav-item">
        <i class='bx bx-error'></i> Stock faible
        <span class="nav-badge yellow"><?= count($stock_faible) ?></span>
      </a>
      <?php endif; ?>
      <?php if(count($rupture) > 0): ?>
      <a href="produits.php?filter=out_of_stock" class="nav-item">
        <i class='bx bx-x-circle'></i> Ruptures
        <span class="nav-badge"><?= count($rupture) ?></span>
      </a>
      <?php endif; ?>
    </div>
    <div class="nav-section">
      <span class="nav-section-label">Accès rapide</span>
      <a href="../index.php" class="nav-item"><i class='bx bx-link-external'></i> Voir le site</a>
      <a href="../deconnexion.php" class="nav-item"><i class='bx bx-log-out'></i> Déconnexion</a>
    </div>
  </nav>
  <div class="sidebar-user">
    <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_nom'],0,2)) ?></div>
    <div>
      <div class="user-name"><?= htmlspecialchars($_SESSION['user_nom']) ?></div>
      <div class="user-role">Administrateur</div>
    </div>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div class="topbar-left">
      <h1>Tableau de bord</h1>
      <p>Vue d'ensemble de TechShop</p>
    </div>
    <div class="topbar-right">
      <span class="topbar-date">📅 <?= date('d/m/Y — H:i') ?></span>
      <a href="produits.php?action=ajouter" class="topbar-btn primary"><i class='bx bx-plus'></i> Nouveau produit</a>
    </div>
  </div>

  <div class="content">

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card orange">
        <div class="stat-label">Chiffre d'affaires <span class="stat-icon">💰</span></div>
        <div class="stat-num orange"><?= $ca_total ?>FCFA</div>
        <div class="stat-trend">CA du mois : <strong style="color:var(--dark);margin-left:4px"><?= $ca_mois ?>FCFA</strong></div>
      </div>
      <div class="stat-card blue">
        <div class="stat-label">Commandes <span class="stat-icon">📦</span></div>
        <div class="stat-num"><?= $total_commandes ?></div>
        <?php if($cmd_pending > 0): ?>
        <div class="stat-trend"><span class="trend-up">●</span> <?= $cmd_pending ?> en attente</div>
        <?php else: ?>
        <div class="stat-trend" style="color:var(--green)">● Tout traité</div>
        <?php endif; ?>
      </div>
      <div class="stat-card green">
        <div class="stat-label">Produits <span class="stat-icon">🛍️</span></div>
        <div class="stat-num"><?= $total_produits ?></div>
        <?php $alert_count = count($stock_faible) + count($rupture); ?>
        <div class="stat-trend">
          <?php if($alert_count > 0): ?>
          <span class="trend-down">⚠️</span> <?= $alert_count ?> alerte<?= $alert_count > 1 ? 's' : '' ?> stock
          <?php else: ?>
          <span class="trend-up">●</span> Tous en stock
          <?php endif; ?>
        </div>
      </div>
      <div class="stat-card yellow">
        <div class="stat-label">Utilisateurs <span class="stat-icon">👥</span></div>
        <div class="stat-num"><?= $total_users ?></div>
        <div class="stat-trend">Membres inscrits</div>
      </div>
    </div>

    <!-- COMMANDES + STOCK -->
    <div class="grid-3">

      <!-- Commandes récentes -->
      <div class="card">
        <div class="card-header">
          <span class="card-title"><i class='bx bx-list-ul'></i> Commandes récentes</span>
          <a href="commandes.php" class="card-action">Voir toutes →</a>
        </div>
        <div class="card-body" style="padding:0">
          <?php if(count($commandes_recentes) > 0): ?>
          <table class="data-table">
            <thead><tr>
              <th>N°</th><th>Client</th><th>Montant</th><th>Statut</th><th></th>
            </tr></thead>
            <tbody>
              <?php foreach($commandes_recentes as $c):
                $badge = match($c['statut']){
                  'en_attente'=>'badge-pending',
                  'en_cours'=>'badge-processing',
                  'expediee'=>'badge-shipped',
                  'livree'=>'badge-delivered',
                  'annulee'=>'badge-cancelled',
                  default=>'badge-pending'
                };
                $label = match($c['statut']){
                  'en_attente'=>'En attente','en_cours'=>'En cours',
                  'expediee'=>'Expédiée','livree'=>'Livrée','annulee'=>'Annulée',default=>$c['statut']
                };
              ?>
              <tr>
                <td class="td-id">#<?= $c['id'] ?></td>
                <td class="td-name"><?= htmlspecialchars($c['nom']) ?></td>
                <td style="font-weight:600;color:var(--gold-dark)"><?= number_format($c['total'],0,',',' ') ?>FCFA</td>
                <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
                <td><a href="commandes.php?voir=<?= $c['id'] ?>" class="btn-sm"><i class='bx bx-show'></i></a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php else: ?>
          <div class="empty-msg"><span class="empty-icon">📭</span>Aucune commande</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Alertes Stock -->
      <div style="display:flex;flex-direction:column;gap:20px">
        <div class="card">
          <div class="card-header">
            <span class="card-title"><i class='bx bx-error-circle'></i> Stock faible</span>
            <a href="produits.php" class="card-action">Gérer →</a>
          </div>
          <div class="card-body">
            <?php if(count($stock_faible) > 0): foreach($stock_faible as $p): $pct = min(100,($p['stock']/10)*100); ?>
            <div class="stock-item">
              <div>
                <div class="stock-name"><?= htmlspecialchars(substr($p['nom'],0,22)).(strlen($p['nom'])>22?'…':'') ?></div>
                <div class="stock-num" style="color:var(--yellow)">⚠️ <?= $p['stock'] ?> restant<?= $p['stock']>1?'s':'' ?></div>
              </div>
              <div>
                <div class="stock-bar-wrap"><div class="stock-bar low" style="width:<?= $pct ?>%"></div></div>
              </div>
            </div>
            <?php endforeach; else: ?>
            <div class="empty-msg" style="padding:16px"><span style="color:var(--green)">✅</span> Aucun stock critique</div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title"><i class='bx bx-x-circle'></i> Ruptures de stock</span>
          </div>
          <div class="card-body">
            <?php if(count($rupture) > 0): foreach($rupture as $p): ?>
            <div class="rupture-item">
              <span class="rupture-dot"></span>
              <span class="rupture-name"><?= htmlspecialchars(substr($p['nom'],0,26)).(strlen($p['nom'])>26?'…':'') ?></span>
              <a href="produits.php?edit=<?= $p['id'] ?>" class="rupture-tag">Réappro.</a>
            </div>
            <?php endforeach; else: ?>
            <div class="empty-msg" style="padding:16px"><span style="color:var(--green)">✅</span> Tous en stock</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div><!-- /grid-3 -->
  </div><!-- /content -->

  <!-- ADMIN FOOTER (maintenant correctement positionné) -->
  <footer class="admin-footer">
    <p>© 2026 <span>TechShop</span> — Tous droits réservés — Administration</p>
  </footer>

</div><!-- /main -->

</body>
</html>