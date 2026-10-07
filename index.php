<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

$database = new Database();
$db = $database->getConnection();

$sql = "SELECT * FROM produits ORDER BY date_ajout DESC LIMIT 6";
$stmt = $db->prepare($sql);
$stmt->execute();
$nouveaux_produits = $stmt->fetchAll();

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>TechShop — Matériel Informatique Premium</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css" rel="stylesheet"/>
<style>
/* ===== RESET & BASE ===== */
*,::before,::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:#FDFBF7;color:#2C2C2C;overflow-x:hidden;line-height:1.6}
a{text-decoration:none;color:inherit}
img{max-width:100%;display:block}
ul{list-style:none}

/* ===== CSS VARIABLES ===== */
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

/* ===== NAVBAR ===== */
.navbar{
  position:fixed;top:0;left:0;right:0;z-index:999;
  padding:0 5%;
  height:68px;
  display:flex;align-items:center;justify-content:space-between;
  background:rgba(253,251,247,0.95);
  backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);
}
.nav-logo{
  font-family:'Bebas Neue',sans-serif;
  font-size:1.7rem;letter-spacing:2px;color:var(--dark);
  display:flex;align-items:center;gap:8px;
}
.nav-logo span{color:var(--gold)}
.nav-links{display:flex;align-items:center;gap:32px}
.nav-links a{font-size:.85rem;font-weight:500;color:var(--text-muted);letter-spacing:.5px;transition:color .2s}
.nav-links a:hover{color:var(--gold)}
.nav-actions{display:flex;align-items:center;gap:12px}
.btn-nav-cart{
  display:flex;align-items:center;gap:8px;
  background:var(--gold);color:var(--dark);
  padding:9px 20px;border-radius:50px;
  font-size:.82rem;font-weight:600;letter-spacing:.5px;
  transition:background .2s,transform .15s;
}
.btn-nav-cart:hover{background:var(--gold-light);transform:translateY(-1px)}
.nav-link-btn{
  font-size:.82rem;font-weight:500;color:var(--text-muted);
  border:1px solid var(--border);padding:8px 18px;border-radius:50px;
  transition:all .2s;
}
.nav-link-btn:hover{border-color:var(--gold);color:var(--gold)}
.nav-toggle{display:none;font-size:1.5rem;cursor:pointer;color:var(--dark)}

/* ===== HERO ===== */
.hero{
  min-height:100vh;padding:68px 5% 0;
  display:grid;grid-template-columns:1fr 1fr;
  align-items:center;gap:60px;
  position:relative;overflow:hidden;
}
.hero::before{
  content:'';position:absolute;top:-200px;right:-200px;
  width:700px;height:700px;border-radius:50%;
  background:radial-gradient(circle,rgba(212,175,55,.15),transparent 70%);
  pointer-events:none;
}
.hero-left{padding:60px 0}
.hero-tag{
  display:inline-flex;align-items:center;gap:6px;
  background:rgba(212,175,55,.12);border:1px solid rgba(212,175,55,.3);
  color:var(--gold);font-size:.75rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;
  padding:6px 14px;border-radius:50px;margin-bottom:28px;
}
.hero-tag::before{content:'';width:6px;height:6px;border-radius:50%;background:var(--gold);animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}
.hero-title{
  font-family:'Bebas Neue',sans-serif;
  font-size:clamp(60px,8vw,110px);
  line-height:.92;letter-spacing:2px;
  color:var(--dark);margin-bottom:28px;
}
.hero-title .accent{color:var(--gold)}
.hero-title .outline{
  -webkit-text-stroke:2px var(--dark);color:transparent;
}
.hero-subtitle{
  font-size:1rem;color:var(--text-light);
  max-width:400px;line-height:1.8;margin-bottom:40px;font-weight:300;
}
.hero-ctas{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.btn-primary{
  background:var(--gold);color:var(--dark);
  padding:15px 32px;border-radius:50px;
  font-size:.9rem;font-weight:600;letter-spacing:.5px;
  transition:all .25s;border:none;cursor:pointer;
  box-shadow:0 0 30px rgba(212,175,55,.3);
}
.btn-primary:hover{background:var(--gold-light);transform:translateY(-2px);box-shadow:0 0 40px rgba(212,175,55,.45)}
.btn-ghost{
  color:var(--text-light);font-size:.88rem;font-weight:500;
  display:flex;align-items:center;gap:8px;
  border:1px solid var(--border);padding:14px 28px;border-radius:50px;
  transition:all .2s;
}
.btn-ghost:hover{border-color:var(--gold);color:var(--gold)}
.hero-stats{display:flex;gap:36px;margin-top:52px;padding-top:40px;border-top:1px solid var(--border)}
.stat{display:flex;flex-direction:column}
.stat-num{font-family:'Bebas Neue',sans-serif;font-size:2.4rem;color:var(--dark);letter-spacing:1px;line-height:1}
.stat-num span{color:var(--gold)}
.stat-label{font-size:.75rem;color:var(--text-muted);margin-top:4px;letter-spacing:.5px;text-transform:uppercase}

.hero-right{position:relative;height:100vh;display:flex;align-items:center}
.hero-img-main{
  width:100%;height:560px;object-fit:cover;
  border-radius:24px;
  box-shadow:0 40px 80px rgba(0,0,0,.15);
}
.hero-badge{
  position:absolute;bottom:80px;left:-40px;
  background:var(--cream-dark);border:1px solid var(--border);
  border-radius:var(--radius);padding:18px 22px;
  display:flex;align-items:center;gap:14px;
  min-width:230px;
  box-shadow:0 20px 40px rgba(0,0,0,.1);
  animation:floatBadge 4s ease-in-out infinite;
}
@keyframes floatBadge{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.badge-dot{width:42px;height:42px;background:var(--gold);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}
.badge-t1{font-size:.85rem;font-weight:600;color:var(--dark)}
.badge-t2{font-size:.75rem;color:var(--text-muted);margin-top:2px}
.hero-badge2{
  position:absolute;top:80px;right:-20px;
  background:var(--gold);border-radius:var(--radius);padding:16px 20px;
  text-align:center;
  animation:floatBadge 4s ease-in-out infinite .5s;
}
.badge2-num{font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--dark);line-height:1}
.badge2-text{font-size:.72rem;color:rgba(44,44,44,.8);margin-top:2px;font-weight:500}

/* ===== TICKER ===== */
.ticker{
  background:var(--cream-dark);border-top:1px solid var(--border);border-bottom:1px solid var(--border);
  padding:16px 0;overflow:hidden;
}
.ticker-inner{
  display:flex;gap:60px;white-space:nowrap;
  animation:ticker 25s linear infinite;
}
.ticker-item{display:flex;align-items:center;gap:10px;font-size:.8rem;font-weight:500;letter-spacing:1px;text-transform:uppercase;color:var(--text-muted)}
.ticker-dot{color:var(--gold);font-size:1rem}
@keyframes ticker{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}

/* ===== SECTION ===== */
.section{padding:100px 5%}
.s-tag{
  font-size:.72rem;font-weight:600;letter-spacing:2px;text-transform:uppercase;
  color:var(--gold);margin-bottom:12px;display:block;
}
.s-title{
  font-family:'Bebas Neue',sans-serif;
  font-size:clamp(38px,5vw,62px);letter-spacing:1px;
  color:var(--dark);line-height:1;
}
.s-head{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:56px;gap:20px}
.see-all{
  font-size:.82rem;font-weight:500;color:var(--text-muted);
  border:1px solid var(--border);padding:9px 22px;border-radius:50px;
  transition:all .2s;white-space:nowrap;
}
.see-all:hover{border-color:var(--gold);color:var(--gold)}

/* ===== CATEGORIES ===== */
.cats-grid{display:grid;grid-template-columns:1.8fr 1fr 1fr;grid-template-rows:260px 240px;gap:14px}
.cat-card{
  position:relative;overflow:hidden;border-radius:var(--radius);cursor:pointer;
  background:var(--cream-dark);
}
.cat-card:first-child{grid-row:1/3}
.cat-card img{width:100%;height:100%;object-fit:cover;transition:transform .6s ease;filter:brightness(.7)}
.cat-card:hover img{transform:scale(1.06);filter:brightness(.85)}
.cat-overlay{
  position:absolute;inset:0;
  background:linear-gradient(to top,rgba(44,44,44,.85) 0%,rgba(44,44,44,.1) 60%,transparent 100%);
}
.cat-info{position:absolute;bottom:0;left:0;right:0;padding:28px}
.cat-label{
  font-size:.68rem;font-weight:600;letter-spacing:2px;text-transform:uppercase;
  color:var(--gold);margin-bottom:6px;display:block;
}
.cat-title{font-family:'Bebas Neue',sans-serif;font-size:1.8rem;letter-spacing:1px;color:var(--white);line-height:1.1;margin-bottom:8px}
.cat-card:first-child .cat-title{font-size:2.8rem}
.cat-count{font-size:.8rem;color:rgba(255,255,255,.7)}
.cat-arrow{
  position:absolute;top:20px;right:20px;
  width:36px;height:36px;border-radius:50%;
  background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);
  display:flex;align-items:center;justify-content:center;
  font-size:1rem;color:var(--white);
  transform:rotate(0deg);transition:all .3s;
}
.cat-card:hover .cat-arrow{background:var(--gold);border-color:var(--gold);transform:rotate(-45deg)}

/* ===== PRODUCTS ===== */
.prods-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.prod-card{
  background:var(--cream-light);border:1px solid var(--border);
  border-radius:var(--radius);overflow:hidden;
  transition:all .3s;cursor:pointer;position:relative;
}
.prod-card:hover{transform:translateY(-6px);border-color:var(--gold);box-shadow:0 24px 48px rgba(0,0,0,.1)}
.prod-img-wrap{height:230px;overflow:hidden;background:var(--cream-dark);position:relative}
.prod-img-wrap img{width:100%;height:100%;object-fit:cover;transition:transform .5s}
.prod-card:hover .prod-img-wrap img{transform:scale(1.07)}
.prod-badge-new{
  position:absolute;top:14px;left:14px;
  background:var(--gold);color:var(--dark);
  font-size:.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
  padding:4px 10px;border-radius:4px;
}
.prod-badge-out{
  position:absolute;top:14px;left:14px;
  background:rgba(0,0,0,.7);color:var(--text-muted);
  font-size:.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
  padding:4px 10px;border-radius:4px;border:1px solid var(--border);
}
.prod-wish{
  position:absolute;top:14px;right:14px;
  width:34px;height:34px;border-radius:50%;
  background:rgba(0,0,0,.5);border:1px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  font-size:.95rem;color:var(--text-muted);
  transition:all .2s;cursor:pointer;
}
.prod-wish:hover{background:var(--gold);border-color:var(--gold);color:var(--dark)}
.prod-info{padding:20px}
.prod-brand{font-size:.72rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.prod-name{font-size:.95rem;font-weight:500;color:var(--dark);margin-bottom:12px;line-height:1.4}
.prod-bottom{display:flex;align-items:center;justify-content:space-between;margin-top:16px;padding-top:16px;border-top:1px solid var(--border)}
.prod-price{font-family:'Bebas Neue',sans-serif;font-size:1.6rem;letter-spacing:1px;color:var(--gold-dark)}
.prod-price sup{font-family:'Inter',sans-serif;font-size:.75rem;font-weight:600;vertical-align:super}
.prod-actions{display:flex;gap:8px}
.btn-detail-sm{
  padding:8px 14px;border-radius:var(--radius-sm);
  border:1px solid var(--border);color:var(--text-muted);
  font-size:.78rem;font-weight:500;transition:all .2s;
}
.btn-detail-sm:hover{border-color:var(--gold);color:var(--gold)}
.btn-add-sm{
  padding:8px 16px;border-radius:var(--radius-sm);
  background:var(--gold);color:var(--dark);border:none;
  font-size:.78rem;font-weight:600;cursor:pointer;transition:all .2s;
}
.btn-add-sm:hover{background:var(--gold-light);transform:scale(1.04)}
.btn-out-sm{
  padding:8px 14px;border-radius:var(--radius-sm);
  background:transparent;border:1px solid var(--border);
  color:var(--text-muted);font-size:.78rem;cursor:not-allowed;
}
.prods-cta{text-align:center;margin-top:52px}

/* ===== ADVANTAGES ===== */
.adv-section{background:var(--cream-dark);border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
.adv-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0}
.adv-card{
  padding:52px 36px;
  border-right:1px solid var(--border);
  transition:background .3s;
}
.adv-card:last-child{border-right:none}
.adv-card:hover{background:var(--cream-light)}
.adv-icon{
  width:52px;height:52px;border-radius:12px;
  background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.2);
  display:flex;align-items:center;justify-content:center;
  font-size:1.4rem;margin-bottom:20px;
}
.adv-title{font-size:1rem;font-weight:600;color:var(--dark);margin-bottom:8px}
.adv-text{font-size:.83rem;color:var(--text-muted);line-height:1.7}

/* ===== FOOTER ===== */
.footer{
  background:var(--cream-dark);border-top:1px solid var(--border);
  padding:64px 5% 32px;
}
.footer-top{display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:48px;margin-bottom:56px}
.footer-brand{}
.footer-logo{font-family:'Bebas Neue',sans-serif;font-size:2rem;letter-spacing:2px;color:var(--dark);margin-bottom:14px}
.footer-logo span{color:var(--gold)}
.footer-desc{font-size:.83rem;color:var(--text-muted);line-height:1.8;max-width:260px;margin-bottom:24px}
.footer-socials{display:flex;gap:10px}
.social-btn{
  width:36px;height:36px;border-radius:50%;
  border:1px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  color:var(--text-muted);font-size:1rem;
  transition:all .2s;
}
.social-btn:hover{border-color:var(--gold);color:var(--gold)}
.footer-col h4{font-size:.78rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--dark);margin-bottom:20px}
.footer-col ul{display:flex;flex-direction:column;gap:10px}
.footer-col ul li a{font-size:.83rem;color:var(--text-muted);transition:color .2s}
.footer-col ul li a:hover{color:var(--gold)}
.footer-bottom{
  padding-top:28px;border-top:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
}
.footer-copy{font-size:.78rem;color:var(--text-muted)}
.footer-copy span{color:var(--gold)}

/* ===== NOTIFICATION ===== */
.notif{
  position:fixed;top:82px;right:24px;z-index:9999;
  background:var(--cream-light);border:1px solid var(--gold);
  border-left:3px solid var(--gold);
  border-radius:var(--radius-sm);
  padding:16px 20px;
  display:flex;align-items:center;gap:12px;
  min-width:280px;max-width:360px;
  box-shadow:0 16px 40px rgba(0,0,0,.15);
  animation:slideNotif .3s ease;
}
@keyframes slideNotif{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}
.notif-icon{font-size:1.2rem;flex-shrink:0}
.notif-msg{font-size:.83rem;color:var(--dark);flex:1;line-height:1.4}
.notif-close{background:none;border:none;color:var(--text-muted);font-size:1.1rem;cursor:pointer;padding:0 4px;transition:color .2s}
.notif-close:hover{color:var(--gold)}

/* ===== RESPONSIVE ===== */
@media(max-width:1024px){
  .hero{grid-template-columns:1fr;gap:40px;min-height:auto;padding-top:120px;padding-bottom:60px}
  .hero-right{height:400px}
  .cats-grid{grid-template-columns:1fr 1fr;grid-template-rows:auto}
  .cat-card:first-child{grid-column:1/3;grid-row:auto;height:300px}
  .prods-grid{grid-template-columns:repeat(2,1fr)}
  .adv-grid{grid-template-columns:repeat(2,1fr)}
  .adv-card:nth-child(2){border-right:none}
  .adv-card:nth-child(3){border-right:1px solid var(--border)}
  .adv-card{border-bottom:1px solid var(--border)}
  .footer-top{grid-template-columns:1fr 1fr}
}
@media(max-width:768px){
  .navbar{padding:0 4%}
  .nav-links{display:none}
  .nav-toggle{display:block}
  .hero{padding:100px 4% 60px}
  .hero-title{font-size:clamp(50px,14vw,80px)}
  .hero-badge{display:none}
  .hero-badge2{display:none}
  .section{padding:70px 4%}
  .cats-grid{grid-template-columns:1fr;grid-template-rows:auto}
  .cat-card:first-child{grid-column:auto;grid-row:auto;height:260px}
  .cat-card{height:200px}
  .prods-grid{grid-template-columns:1fr}
  .adv-grid{grid-template-columns:1fr}
  .adv-card{border-right:none}
  .footer-top{grid-template-columns:1fr}
  .footer-bottom{flex-direction:column;gap:12px;text-align:center}
  .hero-stats{gap:24px}
  .s-head{flex-direction:column;align-items:flex-start}
}
</style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
  <a href="index.php" class="nav-logo">Tech<span>Shop</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Accueil</a></li>
    <li><a href="catalogue.php">Catalogue</a></li>
    <li><a href="catalogue.php?type=ordinateur">Ordinateurs</a></li>
    <li><a href="catalogue.php?type=composant">Composants</a></li>
  </ul>
  <div class="nav-actions">
    <?php if(estConnecte()): ?>
      <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <a href="admin/dashboard.php" class="nav-link-btn">Admin</a>
      <?php endif; ?>
      <a href="commande.php" class="nav-link-btn">Mes commandes</a>
      <a href="deconnexion.php" class="nav-link-btn">Déconnexion</a>
    <?php else: ?>
      <a href="login.php" class="nav-link-btn">Connexion</a>
    <?php endif; ?>
    <a href="panier.php" class="btn-nav-cart">
      <i class='bx bx-cart'></i> Panier
      <?php 
        $nb = isset($_SESSION['panier']) ? array_sum($_SESSION['panier']) : 0;
        if($nb > 0) echo "<span class='panier-count' style='background:rgba(0,0,0,.1);border-radius:50%;padding:0 5px;margin-left:2px;font-size:.75rem;'>$nb</span>";
      ?>
    </a>
  </div>
  <div class="nav-toggle" id="nav-toggle"><i class='bx bx-menu'></i></div>
</nav>

<!-- ===== HERO ===== -->
<section class="hero" id="home">
  <div class="hero-left">
    <div class="hero-tag">
      <span>Nouveau stock 2026</span>
    </div>
    <h1 class="hero-title">
      LE MEILLEUR<br>
      <span class="outline">MATÉRIEL</span><br>
      <span class="accent">INFORMATIQUE</span>
    </h1>
    <p class="hero-subtitle">Composants haute performance, ordinateurs gaming et périphériques pro — tout pour équiper votre setup de rêve.</p>
    <div class="hero-ctas">
      <a href="catalogue.php" class="btn-primary">Découvrir le catalogue →</a>
      <a href="catalogue.php?type=ordinateur" class="btn-ghost"><i class='bx bx-laptop'></i> Voir les PCs</a>
    </div>
    <div class="hero-stats">
      <div class="stat">
        <span class="stat-num">500<span>+</span></span>
        <span class="stat-label">Produits</span>
      </div>
      <div class="stat">
        <span class="stat-num">48<span>h</span></span>
        <span class="stat-label">Livraison</span>
      </div>
      <div class="stat">
        <span class="stat-num">2<span>ans</span></span>
        <span class="stat-label">Garantie</span>
      </div>
    </div>
  </div>
  <div class="hero-right">
    <img class="hero-img-main"
     src="images/ok.jpg"
      alt="Setup Gaming Premium"/>
    <!-- <div class="hero-badge">
      <div class="badge-dot">🔥</div>
      <div>
        <div class="badge-t1">Meilleure vente</div>
        <div class="badge-t2">RTX 4090 — En stock</div>
      </div>
    </div> -->
    <!-- <div class="hero-badge2">
      <div class="badge2-num">4.9★</div>
      <div class="badge2-text">Note clients</div>
    </div> -->
  </div>
</section>

<!-- ===== TICKER ===== -->
<div class="ticker">
  <div class="ticker-inner">
    <span class="ticker-item"><span class="ticker-dot">●</span> Livraison gratuite dès 100€</span>
    <span class="ticker-item"><span class="ticker-dot">●</span> Garantie 2 ans sur tous les produits</span>
    <span class="ticker-item"><span class="ticker-dot">●</span> Paiement sécurisé</span>
    <span class="ticker-item"><span class="ticker-dot">●</span> Support technique 7j/7</span>
    <span class="ticker-item"><span class="ticker-dot">●</span> Retours gratuits sous 30 jours</span>
    <span class="ticker-item"><span class="ticker-dot">●</span> Livraison gratuite dès 100€</span>
    <span class="ticker-item"><span class="ticker-dot">●</span> Garantie 2 ans sur tous les produits</span>
    <span class="ticker-item"><span class="ticker-dot">●</span> Paiement sécurisé</span>
    <span class="ticker-item"><span class="ticker-dot">●</span> Support technique 7j/7</span>
    <span class="ticker-item"><span class="ticker-dot">●</span> Retours gratuits sous 30 jours</span>
  </div>
</div>

<!-- ===== CATÉGORIES ===== -->
<section class="section" id="categories">
  <div class="s-head">
    <div>
      <span class="s-tag">Collections</span>
      <h2 class="s-title">NOS CATÉGORIES</h2>
    </div>
    <a href="catalogue.php" class="see-all">Tout voir →</a>
  </div>
  <div class="cats-grid">

    <a href="catalogue.php?type=ordinateur" class="cat-card">
      <img src="images/yeh.jpg"/>
      <div class="cat-overlay"></div>
      <div class="cat-info">
        <span class="cat-label">Catégorie</span>
        <h3 class="cat-title">Ordinateurs<br>& Laptops</h3>
        <p class="cat-count">PC de bureau, portables gaming, workstations</p>
      </div>
      <div class="cat-arrow">→</div>
    </a>

    <a href="catalogue.php?type=composant" class="cat-card">
      <img  src="images/eeeh.jpg" alt="Composants"/>
      <div class="cat-overlay"></div>
      <div class="cat-info">
        <span class="cat-label">Catégorie</span>
        <h3 class="cat-title">Composants</h3>
        <p class="cat-count">GPU, CPU, RAM, SSD</p>
      </div>
      <div class="cat-arrow">→</div>
    </a>

    <a href="catalogue.php?type=peripherique" class="cat-card">
      <img src="https://images.unsplash.com/photo-1616763355548-1b606f439f86?w=500&h=300&fit=crop&q=80" alt="Périphériques"/>
      <div class="cat-overlay"></div>
      <div class="cat-info">
        <span class="cat-label">Catégorie</span>
        <h3 class="cat-title">Périphériques</h3>
        <p class="cat-count">Claviers, souris, écrans</p>
      </div>
      <div class="cat-arrow">→</div>
    </a>

    <a href="catalogue.php?type=reseau" class="cat-card">
      <img src="https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=500&h=300&fit=crop&q=80" alt="Réseau"/>
      <div class="cat-overlay"></div>
      <div class="cat-info">
        <span class="cat-label">Catégorie</span>
        <h3 class="cat-title">Réseau</h3>
        <p class="cat-count">Routeurs, câbles, switchs</p>
      </div>
      <div class="cat-arrow">→</div>
    </a>

  </div>
</section>

<!-- ===== NOUVEAUTÉS ===== -->
<section class="section" id="products" style="padding-top:0">
  <div class="s-head">
    <div>
      <span class="s-tag">Dernières arrivées</span>
      <h2 class="s-title">NOUVEAUTÉS</h2>
    </div>
    <a href="catalogue.php" class="see-all">Voir tout →</a>
  </div>

  <div class="prods-grid">
    <?php
    $fallback = [
      'ordinateur' => 'https://images.unsplash.com/photo-1587831990711-23ca6441447b?w=600&h=400&fit=crop&q=80',
      'peripherique' => 'https://images.unsplash.com/photo-1616763355548-1b606f439f86?w=600&h=400&fit=crop&q=80',
      'composant' => 'https://images.unsplash.com/photo-1591488322449-99f9208c2d08?w=600&h=400&fit=crop&q=80',
      'reseau' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=600&h=400&fit=crop&q=80',
    ];
    if(count($nouveaux_produits) > 0):
      foreach($nouveaux_produits as $i => $p):
        $img = (!empty($p['image']) && file_exists('images/'.$p['image']))
               ? 'images/'.$p['image']
               : ($fallback[$p['type']] ?? $fallback['ordinateur']);
        $en_stock = $p['stock'] > 0;
    ?>
    <div class="prod-card">
      <div class="prod-img-wrap">
        <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['nom']) ?>"/>
        <?php if($i === 0): ?><span class="prod-badge-new">Nouveau</span><?php endif; ?>
        <?php if(!$en_stock): ?><span class="prod-badge-out">Rupture</span><?php endif; ?>
        <span class="prod-wish"><i class='bx bx-heart'></i></span>
      </div>
      <div class="prod-info">
        <div class="prod-brand"><?= htmlspecialchars($p['marque']) ?></div>
        <div class="prod-name"><?= htmlspecialchars($p['nom']) ?></div>
        <div class="prod-bottom">
          <div class="prod-price"><sup>FCFA </sup><?= number_format($p['prix'], 0, ',', ' ') ?></div>
          <div class="prod-actions">
            <a href="produit.php?id=<?= $p['id'] ?>" class="btn-detail-sm">Détails</a>
            <?php if($en_stock): ?>
              <button onclick="ajouterAuPanier(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['nom'])) ?>')" class="btn-add-sm">+ Panier</button>
            <?php else: ?>
              <button class="btn-out-sm" disabled>Indispo</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; else: ?>
    <p style="color:var(--text-muted);grid-column:1/-1;text-align:center;padding:40px 0">Aucun produit disponible.</p>
    <?php endif; ?>
  </div>

  <div class="prods-cta">
    <a href="catalogue.php" class="btn-primary">Voir tous les produits →</a>
  </div>
</section>

<!-- ===== AVANTAGES ===== -->
<section class="section adv-section" style="padding:0">
  <div class="adv-grid">
    <div class="adv-card">
      <div class="adv-icon">🚚</div>
      <h3 class="adv-title">Livraison rapide</h3>
      <p class="adv-text">Expédition sous 24h. Livraison gratuite dès 100€ d'achat sur toute la Côte d'Ivoire.</p>
    </div>
    <div class="adv-card">
      <div class="adv-icon">🛡️</div>
      <h3 class="adv-title">Garantie 2 ans</h3>
      <p class="adv-text">Tous nos produits sont garantis 2 ans constructeur. Service après-vente réactif.</p>
    </div>
    <div class="adv-card">
      <div class="adv-icon">💳</div>
      <h3 class="adv-title">Paiement sécurisé</h3>
      <p class="adv-text">CB, PayPal, Orange Money, MTN Mobile Money — paiement 100% sécurisé et crypté.</p>
    </div>
    <div class="adv-card">
      <div class="adv-icon">💬</div>
      <h3 class="adv-title">Support 7j/7</h3>
      <p class="adv-text">Notre équipe d'experts est disponible tous les jours pour vous conseiller et vous aider.</p>
    </div>
  </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="footer">
  <div class="footer-top">
    <div class="footer-brand">
      <div class="footer-logo">Tech<span>Shop</span></div>
      <p class="footer-desc">Votre spécialiste du matériel informatique en Côte d'Ivoire. Qualité premium, prix compétitifs.</p>
      <div class="footer-socials">
        <a href="#" class="social-btn"><i class='bx bxl-facebook'></i></a>
        <a href="#" class="social-btn"><i class='bx bxl-instagram'></i></a>
        <a href="#" class="social-btn"><i class='bx bxl-twitter'></i></a>
        <a href="#" class="social-btn"><i class='bx bxl-linkedin'></i></a>
      </div>
    </div>
    <div class="footer-col">
      <h4>Catalogue</h4>
      <ul>
        <li><a href="catalogue.php?type=ordinateur">Ordinateurs</a></li>
        <li><a href="catalogue.php?type=composant">Composants</a></li>
        <li><a href="catalogue.php?type=peripherique">Périphériques</a></li>
        <li><a href="catalogue.php?type=reseau">Réseau</a></li>
        <li><a href="catalogue.php">Tout le catalogue</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Mon compte</h4>
      <ul>
        <li><a href="login.php">Connexion</a></li>
        <li><a href="register.php">Inscription</a></li>
        <li><a href="commande.php">Mes commandes</a></li>
        <li><a href="panier.php">Mon panier</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Aide</h4>
      <ul>
        <li><a href="#">Livraison & retours</a></li>
        <li><a href="#">Garantie</a></li>
        <li><a href="#">FAQ</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p class="footer-copy">© 2026 <span>TechShop</span> — Tous droits réservés</p>
    <p class="footer-copy" style="font-size:.72rem;color:#AAA">Conçu par Manuel'a — Projet L3 Génie Logiciel</p>
  </div>
</footer>

<!-- ===== NOTIFICATION ===== -->
<div id="notif-container"></div>

<script>
/* ===== NAV TOGGLE ===== */
document.getElementById('nav-toggle').addEventListener('click',function(){
  const links = document.querySelector('.nav-links');
  links.style.display = links.style.display === 'flex' ? 'none' : 'flex';
  links.style.flexDirection = 'column';
  links.style.position = 'fixed';
  links.style.top = '68px';
  links.style.left = '0';
  links.style.right = '0';
  links.style.background = '#F9F6F0';
  links.style.padding = '20px 5%';
  links.style.borderBottom = '1px solid #E8D5A3';
  links.style.gap = '16px';
  links.style.zIndex = '998';
});

/* ===== AJAX PANIER ===== */
function ajouterAuPanier(id, nom) {
  fetch('ajax_ajouter_panier.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'id='+id+'&quantite=1'
  })
  .then(r=>r.json())
  .then(data=>{
    if(data.success){
      showNotif('✅', nom + ' ajouté au panier !');
      const cnt = document.querySelector('.panier-count');
      if(cnt && data.nb_articles !== undefined) cnt.textContent = data.nb_articles;
    } else {
      showNotif('⚠️', data.message || 'Erreur lors de l\'ajout');
    }
  })
  .catch(()=>showNotif('❌','Erreur de connexion'));
}

function showNotif(icon, msg) {
  const c = document.getElementById('notif-container');
  c.querySelectorAll('.notif').forEach(n=>n.remove());
  const n = document.createElement('div');
  n.className = 'notif';
  n.innerHTML = `<span class="notif-icon">${icon}</span><span class="notif-msg">${msg}</span><button class="notif-close" onclick="this.parentElement.remove()">✕</button>`;
  c.appendChild(n);
  setTimeout(()=>{if(n.parentElement) n.remove()},3500);
}

/* ===== SCROLL REVEAL ===== */
const observer = new IntersectionObserver((entries)=>{
  entries.forEach(e=>{
    if(e.isIntersecting){
      e.target.style.opacity='1';
      e.target.style.transform='translateY(0)';
    }
  });
},{threshold:0.1});

document.querySelectorAll('.prod-card,.cat-card,.adv-card').forEach(el=>{
  el.style.opacity='0';
  el.style.transform='translateY(30px)';
  el.style.transition='opacity .6s ease, transform .6s ease';
  observer.observe(el);
});
</script>
</body>
</html>