<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

$database = new Database();
$db = $database->getConnection();

// Récupérer les filtres
$search   = isset($_GET['search']) ? nettoyer($_GET['search']) : '';
$type     = isset($_GET['type'])   ? nettoyer($_GET['type'])   : '';
$marque   = isset($_GET['marque']) ? nettoyer($_GET['marque']) : '';
$prix_min = isset($_GET['prix_min']) ? (int)$_GET['prix_min'] : 0;
$prix_max = isset($_GET['prix_max']) ? (int)$_GET['prix_max'] : 5000;
$page     = isset($_GET['page'])   ? (int)$_GET['page']       : 1;
$par_page = 9;
$offset   = ($page - 1) * $par_page;

// Construction de la requête avec vérification
$sql = "SELECT * FROM produits WHERE 1=1";
$params = [];

if ($prix_min > 0 || $prix_max < 5000) {
    $sql .= " AND prix BETWEEN :pmin AND :pmax";
    $params[':pmin'] = $prix_min;
    $params[':pmax'] = $prix_max;
}

if(!empty($search)){
    $sql .= " AND (nom LIKE :s OR description LIKE :s OR marque LIKE :s)";
    $params[':s'] = "%$search%";
}
if(!empty($type)){
    $sql .= " AND type = :type";
    $params[':type'] = $type;
}
if(!empty($marque)){
    $sql .= " AND marque = :marque";
    $params[':marque'] = $marque;
}

$sql .= " ORDER BY date_ajout DESC LIMIT :offset, :limit";

$stmt = $db->prepare($sql);

// Liaison des paramètres
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $par_page, PDO::PARAM_INT);
$stmt->execute();
$produits = $stmt->fetchAll();

// Requête pour le comptage
$countSql = "SELECT COUNT(*) FROM produits WHERE 1=1";
if ($prix_min > 0 || $prix_max < 5000) {
    $countSql .= " AND prix BETWEEN $prix_min AND $prix_max";
}
if(!empty($search)){
    $countSql .= " AND (nom LIKE '%$search%' OR description LIKE '%$search%' OR marque LIKE '%$search%')";
}
if(!empty($type)){
    $countSql .= " AND type = '$type'";
}
if(!empty($marque)){
    $countSql .= " AND marque = '$marque'";
}
$total = $db->query($countSql)->fetchColumn();
$total_pages = ceil($total / $par_page);

// Récupérer les marques distinctes
$marques = $db->query("SELECT DISTINCT marque FROM produits WHERE marque IS NOT NULL AND marque != '' ORDER BY marque")->fetchAll(PDO::FETCH_COLUMN);

// Images par défaut
$fallback = [
    'ordinateur' => 'https://images.unsplash.com/photo-1587831990711-23ca6441447b?w=500&h=320&fit=crop&q=80',
    'peripherique' => 'https://images.unsplash.com/photo-1616763355548-1b606f439f86?w=500&h=320&fit=crop&q=80',
    'composant' => 'https://images.unsplash.com/photo-1591488322449-99f9208c2d08?w=500&h=320&fit=crop&q=80',
    'reseau' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=500&h=320&fit=crop&q=80',
];

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Catalogue — TechShop</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css" rel="stylesheet"/>
<style>
*,::before,::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:#FDFBF7;color:#2C2C2C;line-height:1.6}
a{text-decoration:none;color:inherit}
img{max-width:100%;display:block}
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
  --border:#E8D5A3;
  --text-muted:#888;
  --text-light:#666;
  --white:#fff;
  --radius:14px;
  --radius-sm:8px;
}

/* NAV */
.navbar{position:fixed;top:0;left:0;right:0;z-index:999;padding:0 5%;height:68px;display:flex;align-items:center;justify-content:space-between;background:rgba(253,251,247,.95);backdrop-filter:blur(16px);border-bottom:1px solid var(--border)}
.nav-logo{font-family:'Bebas Neue',sans-serif;font-size:1.7rem;letter-spacing:2px;color:var(--dark)}
.nav-logo span{color:var(--gold)}
.nav-links{display:flex;align-items:center;gap:28px}
.nav-links a{font-size:.83rem;font-weight:500;color:var(--text-muted);transition:color .2s}
.nav-links a:hover,.nav-links a.active{color:var(--gold)}
.nav-actions{display:flex;gap:10px;align-items:center}
.btn-nav-cart{display:flex;align-items:center;gap:8px;background:var(--gold);color:var(--dark);padding:9px 20px;border-radius:50px;font-size:.82rem;font-weight:600;transition:all .2s}
.btn-nav-cart:hover{background:var(--gold-light);transform:translateY(-1px)}
.nav-link-btn{font-size:.82rem;color:var(--text-muted);border:1px solid var(--border);padding:8px 16px;border-radius:50px;transition:all .2s}
.nav-link-btn:hover{border-color:var(--gold);color:var(--gold)}

/* PAGE HEADER */
.page-header{
  padding:120px 5% 48px;
  border-bottom:1px solid var(--border);
  background:linear-gradient(to bottom,#F9F6F0 0%,var(--cream) 100%);
}
.page-header-inner{display:flex;align-items:flex-end;justify-content:space-between;gap:20px}
.ph-tag{font-size:.72rem;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:10px;display:block}
.ph-title{font-family:'Bebas Neue',sans-serif;font-size:clamp(42px,6vw,72px);letter-spacing:1px;color:var(--dark);line-height:1}
.ph-count{font-size:.85rem;color:var(--text-muted);align-self:flex-end;padding-bottom:8px}
.ph-count strong{color:var(--gold);font-family:'Bebas Neue',sans-serif;font-size:1.4rem;letter-spacing:1px}

/* LAYOUT */
.catalogue-wrap{display:grid;grid-template-columns:280px 1fr;gap:32px;padding:40px 5% 80px;max-width:1400px;margin:0 auto}

/* SIDEBAR */
.sidebar{position:sticky;top:88px;height:fit-content}
.sidebar-card{background:var(--cream-dark);border:1px solid var(--border);border-radius:var(--radius);padding:24px;margin-bottom:16px}
.sidebar-title{font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:18px;display:flex;align-items:center;gap:8px}
.filter-group{margin-bottom:22px}
.filter-group:last-child{margin-bottom:0}
.filter-label{font-size:.78rem;font-weight:600;color:var(--dark);margin-bottom:8px;display:block}
.filter-input{
  width:100%;background:var(--cream-light);border:1px solid var(--border);
  border-radius:var(--radius-sm);padding:10px 14px;
  color:var(--dark);font-size:.83rem;font-family:'Inter',sans-serif;
  transition:border-color .2s;outline:none;
}
.filter-input:focus{border-color:var(--gold)}
.filter-input::placeholder{color:var(--text-muted)}
.filter-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23888'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:calc(100% - 12px) center;padding-right:32px}
.prix-row{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:8px}
.prix-sep{color:var(--text-muted);font-size:.8rem;text-align:center}
.btn-filter{width:100%;background:var(--gold);color:var(--dark);border:none;padding:12px;border-radius:var(--radius-sm);font-size:.85rem;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:background .2s;margin-bottom:10px}
.btn-filter:hover{background:var(--gold-light)}
.btn-reset{display:block;width:100%;text-align:center;padding:10px;border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text-muted);font-size:.82rem;transition:all .2s}
.btn-reset:hover{border-color:var(--gold);color:var(--gold)}
.active-filters{display:flex;flex-wrap:wrap;gap:6px;margin-top:14px}
.filter-chip{
  display:inline-flex;align-items:center;gap:6px;
  background:rgba(212,175,55,.12);border:1px solid rgba(212,175,55,.3);
  color:var(--gold);font-size:.72rem;font-weight:500;
  padding:4px 10px;border-radius:50px;
}
.filter-chip a{color:var(--gold);font-size:.8rem;margin-left:2px;opacity:.7}
.filter-chip a:hover{opacity:1}

/* GRID */
.content-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.sort-select{background:var(--cream-dark);border:1px solid var(--border);color:var(--dark);padding:9px 14px;border-radius:var(--radius-sm);font-size:.82rem;font-family:'Inter',sans-serif;outline:none}
.prods-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}

.prod-card{background:var(--cream-light);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;transition:all .3s;cursor:pointer}
.prod-card:hover{transform:translateY(-5px);border-color:var(--gold);box-shadow:0 20px 40px rgba(0,0,0,.1)}
.prod-img{height:210px;overflow:hidden;background:var(--cream-dark);position:relative}
.prod-img img{width:100%;height:100%;object-fit:cover;transition:transform .5s}
.prod-card:hover .prod-img img{transform:scale(1.07)}
.badge-new{position:absolute;top:12px;left:12px;background:var(--gold);color:var(--dark);font-size:.65rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:3px 9px;border-radius:4px}
.badge-low{position:absolute;top:12px;left:12px;background:rgba(212,175,55,.15);border:1px solid rgba(212,175,55,.4);color:var(--gold-dark);font-size:.65rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:3px 9px;border-radius:4px}
.badge-out{position:absolute;top:12px;left:12px;background:rgba(0,0,0,.6);border:1px solid var(--border);color:var(--text-muted);font-size:.65rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:3px 9px;border-radius:4px}
.prod-info{padding:18px}
.prod-brand{font-size:.7rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:5px}
.prod-name{font-size:.9rem;font-weight:500;color:var(--dark);margin-bottom:4px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.prod-bottom{display:flex;align-items:center;justify-content:space-between;margin-top:14px;padding-top:14px;border-top:1px solid var(--border)}
.prod-price{font-family:'Bebas Neue',sans-serif;font-size:1.5rem;letter-spacing:1px;color:var(--gold-dark)}
.prod-price sup{font-family:'Inter',sans-serif;font-size:.72rem;font-weight:600}
.prod-btns{display:flex;gap:7px}
.btn-see{padding:7px 13px;border:1px solid var(--border);border-radius:6px;color:var(--text-muted);font-size:.75rem;font-weight:500;transition:all .2s}
.btn-see:hover{border-color:var(--gold);color:var(--gold)}
.btn-add{padding:7px 14px;background:var(--gold);color:var(--dark);border:none;border-radius:6px;font-size:.75rem;font-weight:600;cursor:pointer;transition:all .2s}
.btn-add:hover{background:var(--gold-light)}
.btn-unavail{padding:7px 12px;background:transparent;border:1px solid var(--border);color:var(--text-muted);border-radius:6px;font-size:.75rem;cursor:not-allowed}

/* EMPTY */
.empty-state{grid-column:1/-1;text-align:center;padding:80px 20px}
.empty-icon{font-size:3rem;margin-bottom:16px;display:block}
.empty-title{font-family:'Bebas Neue',sans-serif;font-size:1.8rem;letter-spacing:1px;color:var(--dark);margin-bottom:8px}
.empty-text{color:var(--text-muted);font-size:.9rem;margin-bottom:24px}

/* PAGINATION */
.pagination{display:flex;justify-content:center;align-items:center;gap:8px;margin-top:48px;padding-top:32px;border-top:1px solid var(--border)}
.pagination a,.pagination span{
  display:flex;align-items:center;justify-content:center;
  width:38px;height:38px;border-radius:8px;
  font-size:.83rem;font-weight:500;
  border:1px solid var(--border);color:var(--text-muted);
  transition:all .2s;
}
.pagination a:hover{border-color:var(--gold);color:var(--gold)}
.pagination .active{background:var(--gold);border-color:var(--gold);color:var(--dark);font-weight:700}
.pagination .prev-next{width:auto;padding:0 16px;gap:6px}

/* NOTIFICATION */
.notif{position:fixed;top:82px;right:24px;z-index:9999;background:var(--cream-light);border:1px solid var(--gold);border-left:3px solid var(--gold);border-radius:var(--radius-sm);padding:14px 18px;display:flex;align-items:center;gap:10px;min-width:260px;box-shadow:0 16px 40px rgba(0,0,0,.15);animation:slideN .3s ease}
@keyframes slideN{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}
.notif-msg{font-size:.82rem;color:var(--dark);flex:1}
.notif-close{background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1rem;padding:0 2px}
.notif-close:hover{color:var(--gold)}

/* FOOTER */
.footer{background:var(--cream-dark);border-top:1px solid var(--border);padding:40px 5%;text-align:center}
.footer-logo{font-family:'Bebas Neue',sans-serif;font-size:1.6rem;letter-spacing:2px;color:var(--dark);margin-bottom:8px}
.footer-logo span{color:var(--gold)}
.footer-copy{font-size:.78rem;color:var(--text-muted)}

@media(max-width:1024px){.catalogue-wrap{grid-template-columns:240px 1fr}.prods-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:768px){.catalogue-wrap{grid-template-columns:1fr}.sidebar{position:static}.prods-grid{grid-template-columns:1fr}.page-header-inner{flex-direction:column;align-items:flex-start}}
</style>
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="nav-logo">Tech<span>Shop</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Accueil</a></li>
    <li><a href="catalogue.php" class="active">Catalogue</a></li>
    <li><a href="catalogue.php?type=ordinateur">Ordinateurs</a></li>
    <li><a href="catalogue.php?type=composant">Composants</a></li>
  </ul>
  <div class="nav-actions">
    <?php if(estConnecte()): ?>
      <a href="commande.php" class="nav-link-btn">Mes commandes</a>
      <a href="deconnexion.php" class="nav-link-btn">Déconnexion</a>
    <?php else: ?>
      <a href="login.php" class="nav-link-btn">Connexion</a>
    <?php endif; ?>
    <a href="panier.php" class="btn-nav-cart"><i class='bx bx-cart'></i> Panier
      <?php $nb=isset($_SESSION['panier'])?array_sum($_SESSION['panier']):0; if($nb>0) echo "<span>($nb)</span>"; ?>
    </a>
  </div>
</nav>

<div class="page-header">
  <div class="page-header-inner">
    <div>
      <span class="ph-tag">Notre sélection</span>
      <h1 class="ph-title">CATALOGUE COMPLET</h1>
    </div>
    <p class="ph-count">
      <strong><?= $total ?></strong> produit<?= $total > 1 ? 's' : '' ?> trouvé<?= $total > 1 ? 's' : '' ?>
    </p>
  </div>
</div>

<div class="catalogue-wrap">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-card">
      <div class="sidebar-title"><i class='bx bx-filter'></i> Filtres</div>
      <form method="GET" action="">
        <div class="filter-group">
          <label class="filter-label">Recherche</label>
          <input class="filter-input" type="text" name="search" placeholder="Nom, marque..." value="<?= htmlspecialchars($search) ?>"/>
        </div>
        <div class="filter-group">
          <label class="filter-label">Catégorie</label>
          <select class="filter-input filter-select" name="type">
            <option value="">Toutes</option>
            <option value="ordinateur" <?= $type=='ordinateur'?'selected':'' ?>>💻 Ordinateurs</option>
            <option value="peripherique" <?= $type=='peripherique'?'selected':'' ?>>🖱️ Périphériques</option>
            <option value="composant" <?= $type=='composant'?'selected':'' ?>>⚙️ Composants</option>
            <option value="reseau" <?= $type=='reseau'?'selected':'' ?>>🌐 Réseau</option>
          </select>
        </div>
        <div class="filter-group">
          <label class="filter-label">Marque</label>
          <select class="filter-input filter-select" name="marque">
            <option value="">Toutes les marques</option>
            <?php foreach($marques as $m): ?>
            <option value="<?= htmlspecialchars($m) ?>" <?= $marque==$m?'selected':'' ?>><?= htmlspecialchars($m) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group">
          <label class="filter-label">Prix (FCFA)</label>
          <div class="prix-row">
            <input class="filter-input" type="number" name="prix_min" placeholder="Min" value="<?= $prix_min ?>"/>
            <span class="prix-sep">—</span>
            <input class="filter-input" type="number" name="prix_max" placeholder="Max" value="<?= $prix_max ?>"/>
          </div>
        </div>
        <button class="btn-filter" type="submit">Appliquer les filtres</button>
        <a href="catalogue.php" class="btn-reset">Réinitialiser</a>
      </form>

      <?php if($search || $type || $marque): ?>
      <div class="active-filters" style="margin-top:14px">
        <?php if($search): ?><span class="filter-chip">🔍 <?= htmlspecialchars($search) ?> <a href="?<?= http_build_query(array_filter(array_merge($_GET,['search'=>'']),fn($v)=>$v!=='',ARRAY_FILTER_USE_BOTH)) ?>">✕</a></span><?php endif; ?>
        <?php if($type): ?><span class="filter-chip">📁 <?= $type ?> <a href="?<?= http_build_query(array_filter(array_merge($_GET,['type'=>'']),fn($v)=>$v!=='',ARRAY_FILTER_USE_BOTH)) ?>">✕</a></span><?php endif; ?>
        <?php if($marque): ?><span class="filter-chip">🏷️ <?= htmlspecialchars($marque) ?> <a href="?<?= http_build_query(array_filter(array_merge($_GET,['marque'=>'']),fn($v)=>$v!=='',ARRAY_FILTER_USE_BOTH)) ?>">✕</a></span><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </aside>

  <!-- CONTENT -->
  <div>
    <div class="prods-grid">
      <?php if(count($produits) > 0): foreach($produits as $p):
        // CORRECTION : Vérifier les deux chemins possibles pour l'image
        $imagePath = '';
        if (!empty($p['image']) && file_exists('images/'.$p['image'])) {
            $imagePath = 'images/'.$p['image'];
        } elseif (!empty($p['image']) && file_exists('../images/'.$p['image'])) {
            $imagePath = '../images/'.$p['image'];
        } else {
            $imagePath = $fallback[$p['type']] ?? $fallback['ordinateur'];
        }
        
        $en_stock = $p['stock'] > 0;
        $stock_bas = $p['stock'] > 0 && $p['stock'] < 5;
      ?>
      <div class="prod-card">
        <div class="prod-img">
          <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($p['nom']) ?>"/>
          <?php if(!$en_stock): ?><span class="badge-out">Rupture</span>
          <?php elseif($stock_bas): ?><span class="badge-low">Plus que <?= $p['stock'] ?></span>
          <?php else: ?><span class="badge-new">En stock</span><?php endif; ?>
        </div>
        <div class="prod-info">
          <div class="prod-brand"><?= htmlspecialchars($p['marque']) ?></div>
          <div class="prod-name"><?= htmlspecialchars($p['nom']) ?></div>
          <div class="prod-bottom">
            <div class="prod-price"><sup>fcfa</sup><?= number_format($p['prix'],0,',',' ') ?></div>
            <div class="prod-btns">
              <a href="produit.php?id=<?= $p['id'] ?>" class="btn-see">Détails</a>
              <?php if($en_stock): ?>
                <button onclick="addToCart(<?= $p['id'] ?>,'<?= addslashes(htmlspecialchars($p['nom'])) ?>')" class="btn-add">+ Panier</button>
              <?php else: ?>
                <button class="btn-unavail" disabled>Indispo</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; else: ?>
      <div class="empty-state">
        <span class="empty-icon">🔍</span>
        <h3 class="empty-title">Aucun résultat</h3>
        <p class="empty-text">Aucun produit ne correspond à vos critères de recherche.</p>
        <a href="catalogue.php" class="btn-filter" style="display:inline-block;text-decoration:none;width:auto;padding:12px 28px">Voir tout le catalogue</a>
      </div>
      <?php endif; ?>
    </div>

    <?php if($total_pages > 1): ?>
    <div class="pagination">
      <?php if($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&<?= http_build_query(array_filter($_GET,fn($k)=>$k!='page',ARRAY_FILTER_USE_KEY)) ?>" class="prev-next">← Préc.</a>
      <?php endif; ?>
      <?php for($i=1;$i<=$total_pages;$i++): ?>
        <a href="?page=<?= $i ?>&<?= http_build_query(array_filter($_GET,fn($k)=>$k!='page',ARRAY_FILTER_USE_KEY)) ?>" class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if($page < $total_pages): ?>
        <a href="?page=<?= $page+1 ?>&<?= http_build_query(array_filter($_GET,fn($k)=>$k!='page',ARRAY_FILTER_USE_KEY)) ?>" class="prev-next">Suiv. →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<footer class="footer">
  <div class="footer-logo">Tech<span>Shop</span></div>
  <p class="footer-copy">© 2026 TechShop — Tous droits réservés</p>
</footer>

<div id="notif-container"></div>

<script>
function addToCart(id, nom) {
  fetch('ajax_ajouter_panier.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id='+id+'&quantite=1'})
  .then(r=>r.json()).then(d=>{
    const c=document.getElementById('notif-container');
    c.querySelectorAll('.notif').forEach(n=>n.remove());
    const n=document.createElement('div');n.className='notif';
    n.innerHTML=`<span>${d.success?'✅':'⚠️'}</span><span class="notif-msg">${d.success?nom+' ajouté au panier !':d.message}</span><button class="notif-close" onclick="this.parentElement.remove()">✕</button>`;
    c.appendChild(n);setTimeout(()=>{if(n.parentElement)n.remove()},3500);
  });
}

// Force le rafraîchissement pour éviter le cache
window.addEventListener('pageshow', function(event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        location.reload();
    }
});
</script>
</body>
</html>
<?php include 'includes/footer.php'; ?>