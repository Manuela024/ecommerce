<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

$database = new Database();
$db = $database->getConnection();

// Initialiser le panier si inexistant
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Ajouter au panier avec message de confirmation
if (isset($_POST['action']) && $_POST['action'] == 'ajouter') {
    $id = (int)$_POST['id'];
    $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;
    
    // Récupérer le nom du produit pour le message
    $stmt = $db->prepare("SELECT nom FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    $produit = $stmt->fetch();
    $nom_produit = $produit ? $produit['nom'] : 'Produit';
    
    if ($quantite > 0) {
        if (isset($_SESSION['panier'][$id])) {
            $_SESSION['panier'][$id] += $quantite;
            $message = "✅ Quantité augmentée : $nom_produit (x$quantite)";
        } else {
            $_SESSION['panier'][$id] = $quantite;
            $message = "✅ $nom_produit a été ajouté au panier !";
        }
        setFlash($message, "success");
    }
    
    // Rediriger vers la page précédente ou le panier
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'panier.php';
    header("Location: $redirect");
    exit();
}

// Modifier la quantité
if (isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id']) && isset($_GET['qte'])) {
    $id = (int)$_GET['id'];
    $qte = (int)$_GET['qte'];
    
    if ($qte > 0) {
        $_SESSION['panier'][$id] = $qte;
        setFlash("✅ Quantité mise à jour.", "success");
    } else {
        unset($_SESSION['panier'][$id]);
        setFlash("🗑️ Produit supprimé du panier.", "success");
    }
    rediriger('panier.php');
}

// Supprimer un produit
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    unset($_SESSION['panier'][$id]);
    setFlash("🗑️ Produit supprimé du panier.", "success");
    rediriger('panier.php');
}

// Vider le panier
if (isset($_GET['action']) && $_GET['action'] == 'vider') {
    $_SESSION['panier'] = [];
    setFlash("🛒 Panier vidé.", "success");
    rediriger('panier.php');
}

// Récupérer les détails des produits dans le panier
$panier_details = [];
$total = 0;

if (!empty($_SESSION['panier'])) {
    $ids = array_keys($_SESSION['panier']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT * FROM produits WHERE id IN ($placeholders)";
    $stmt = $db->prepare($sql);
    $stmt->execute($ids);
    $produits = $stmt->fetchAll();
    
    foreach ($produits as $produit) {
        $id = $produit['id'];
        $quantite = $_SESSION['panier'][$id];
        $sous_total = $produit['prix'] * $quantite;
        $panier_details[] = [
            'produit' => $produit,
            'quantite' => $quantite,
            'sous_total' => $sous_total
        ];
        // Pour les totaux (déjà en euros dans la BDD)
$total_fcfa = formatPrixFCFA($total);
    }
}

// Calculer le nombre total d'articles
$nb_articles = array_sum($_SESSION['panier']);

include 'includes/header.php';
?>

<style>
:root{
  --gold:#D4AF37;
  --gold-light:#E5C158;
  --gold-dark:#C5A028;
  --cream:#FDFBF7;
  --cream-dark:#F9F6F0;
  --cream-light:#FFF8E7;
  --dark:#2C2C2C;
  --border:#E8D5A3;
  --text-muted:#888;
  --radius-sm:8px;
}
.panier-container{max-width:1200px;margin:100px auto;padding:0 20px;}
.panier-container h1{font-family:'Bebas Neue',sans-serif;color:var(--dark);margin-bottom:25px;}
.badge-panier{background:var(--gold);color:var(--dark);padding:5px 12px;border-radius:50px;font-size:.8rem;margin-left:12px;}
.panier-vide{text-align:center;padding:80px 20px;background:var(--cream-light);border-radius:12px;border:1px solid var(--border);}
.panier-vide-icon{font-size:4rem;margin-bottom:20px;}
.panier-vide h2{color:var(--dark);margin-bottom:10px;}
.panier-vide p{color:var(--text-muted);margin-bottom:25px;}
.btn-continuer-achat{display:inline-block;padding:12px 25px;background:var(--gold);color:var(--dark);border-radius:50px;font-weight:600;transition:all .2s;}
.btn-continuer-achat:hover{background:var(--gold-light);transform:translateY(-2px);}
.panier-contenu{display:grid;grid-template-columns:1fr 320px;gap:30px;}
.panier-table{width:100%;border-collapse:collapse;background:var(--cream-light);border-radius:12px;overflow:hidden;}
.panier-table th,.panier-table td{padding:15px;text-align:left;border-bottom:1px solid var(--border);}
.panier-table th{background:var(--cream-dark);color:var(--gold);font-weight:600;font-size:.75rem;text-transform:uppercase;}
.panier-produit{display:flex;align-items:center;gap:15px;}
.panier-produit img{width:60px;height:60px;object-fit:cover;border-radius:8px;}
.produit-marque{font-size:.7rem;color:var(--text-muted);margin-top:3px;}
.quantite-controle{display:flex;align-items:center;gap:8px;}
.qte-btn{display:inline-block;width:28px;height:28px;background:var(--cream-dark);border:1px solid var(--border);border-radius:6px;text-align:center;line-height:26px;color:var(--dark);font-weight:600;transition:all .2s;}
.qte-btn:hover{background:var(--gold);border-color:var(--gold);color:var(--dark);}
.quantite-valeur{min-width:30px;text-align:center;}
.btn-supprimer{color:var(--text-muted);font-size:1rem;transition:color .2s;}
.btn-supprimer:hover{color:#e74c3c;}
.total-ligne td{font-weight:700;border-bottom:none;}
.total-montant{font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:var(--gold-dark);}
.panier-actions-bas{display:flex;justify-content:space-between;margin-top:20px;}
.btn-continuer,.btn-vider{padding:10px 20px;border-radius:50px;font-size:.8rem;transition:all .2s;}
.btn-continuer{border:1px solid var(--border);color:var(--text-muted);}
.btn-continuer:hover{border-color:var(--gold);color:var(--gold);}
.btn-vider{border:1px solid var(--border);color:#e74c3c;}
.btn-vider:hover{background:#e74c3c;border-color:#e74c3c;color:#fff;}
.panier-resume{background:var(--cream-light);border:1px solid var(--border);border-radius:12px;padding:25px;height:fit-content;position:sticky;top:100px;}
.panier-resume h3{font-family:'Bebas Neue',sans-serif;color:var(--dark);margin-bottom:20px;}
.resume-ligne{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border);}
.resume-ligne.total{border-bottom:none;font-weight:700;font-size:1.1rem;margin-top:10px;padding-top:15px;}
.gratuit{color:#27ae60;}
.btn-valider-commande{display:block;width:100%;margin-top:20px;padding:14px;background:var(--gold);color:var(--dark);border:none;border-radius:50px;font-weight:600;font-size:1rem;cursor:pointer;text-align:center;transition:all .2s;}
.btn-valider-commande:hover{background:var(--gold-light);transform:translateY(-2px);}
.login-pour-commander{text-align:center;margin-top:20px;}
.login-pour-commander p{margin-bottom:12px;font-size:.85rem;color:var(--text-muted);}
.btn-connexion-commande,.btn-inscription-commande{display:inline-block;padding:8px 16px;border-radius:50px;font-size:.8rem;margin:0 5px;}
.btn-connexion-commande{background:var(--gold);color:var(--dark);}
.btn-inscription-commande{border:1px solid var(--border);color:var(--text-muted);}
.btn-inscription-commande:hover{border-color:var(--gold);color:var(--gold);}
@media(max-width:768px){
  .panier-contenu{grid-template-columns:1fr;}
  .panier-table thead{display:none;}
  .panier-table tr{display:block;margin-bottom:20px;border:1px solid var(--border);border-radius:12px;}
  .panier-table td{display:block;text-align:right;padding:10px;}
  .panier-table td:before{content:attr(data-label);float:left;font-weight:600;}
}
</style>

<div class="panier-container">
    <h1>🛒 Mon panier <span class="badge-panier"><?php echo $nb_articles; ?> article(s)</span></h1>
    
    <?php if(empty($panier_details)): ?>
        <div class="panier-vide">
            <div class="panier-vide-icon">🛒</div>
            <h2>Votre panier est vide</h2>
            <p>Découvrez nos produits et ajoutez-les à votre panier.</p>
            <a href="catalogue.php" class="btn-continuer-achat">🛍️ Découvrir nos produits</a>
        </div>
    <?php else: ?>
        <div class="panier-contenu">
            <div class="panier-produits">
                <table class="panier-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix unitaire</th>
                            <th>Quantité</th>
                            <th>Sous-total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($panier_details as $item): ?>
                            <tr class="panier-item">
                                <td data-label="Produit" class="panier-produit">
                                    <img src="images/<?php echo $item['produit']['image'] ?? 'placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($item['produit']['nom']); ?>">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['produit']['nom']); ?></strong>
                                        <p class="produit-marque"><?php echo htmlspecialchars($item['produit']['marque']); ?></p>
                                    </div>
                                </td>
                                <td data-label="Prix unitaire" class="prix-unitaire"><?php echo number_format($item['produit']['prix'], 2, ',', ' '); ?> €</td>
                                <td data-label="Quantité" class="quantite-cell">
                                    <div class="quantite-controle">
                                        <a href="panier.php?action=modifier&id=<?php echo $item['produit']['id']; ?>&qte=<?php echo $item['quantite'] - 1; ?>" class="qte-btn">-</a>
                                        <span class="quantite-valeur"><?php echo $item['quantite']; ?></span>
                                        <a href="panier.php?action=modifier&id=<?php echo $item['produit']['id']; ?>&qte=<?php echo $item['quantite'] + 1; ?>" class="qte-btn">+</a>
                                    </div>
                                </td>
                                <td data-label="Sous-total" class="sous-total"><?php echo number_format($item['sous_total'], 2, ',', ' '); ?> €</td>
                                <td data-label="Action">
                                    <a href="panier.php?action=supprimer&id=<?php echo $item['produit']['id']; ?>" class="btn-supprimer" onclick="return confirm('Supprimer ce produit ?')">🗑️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-ligne">
                            <td colspan="3" class="text-right"><strong>Total :</strong></td>
                            <td colspan="2"><strong class="total-montant"><?php echo number_format($total, 2, ',', ' '); ?> €</strong></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="panier-actions-bas">
                    <a href="catalogue.php" class="btn-continuer">← Continuer mes achats</a>
                    <a href="panier.php?action=vider" class="btn-vider" onclick="return confirm('Vider tout le panier ?')">🗑️ Vider le panier</a>
                </div>
            </div>
            
            <div class="panier-resume">
                <h3>Récapitulatif</h3>
                
                <div class="resume-ligne">
                    <span>📦 Articles :</span>
                    <span><?php echo $nb_articles; ?></span>
                </div>
                <div class="resume-ligne">
                    <span>Sous-total</span>
                    <span><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                </div>
                <div class="resume-ligne">
                    <span>🚚 Livraison</span>
                    <span class="gratuit">Gratuite</span>
                </div>
                <div class="resume-ligne total">
                    <span>💰 Total TTC</span>
                    <span class="total-montant"><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                </div>
                
                <?php if(estConnecte()): ?>
                    <a href="checkout.php" class="btn-valider-commande">✅ Passer commande</a>
                <?php else: ?>
                    <div class="login-pour-commander">
                        <p>🔒 Connectez-vous pour passer commande</p>
                        <a href="login.php" class="btn-connexion-commande">Se connecter</a>
                        <a href="register.php" class="btn-inscription-commande">Créer un compte</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>