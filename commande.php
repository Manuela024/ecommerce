<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    setFlash("Veuillez vous connecter pour passer commande.", "error");
    rediriger('login.php');
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Valider la commande
if (isset($_GET['action']) && $_GET['action'] == 'valider') {
    if (empty($_SESSION['panier'])) {
        setFlash("Votre panier est vide.", "error");
        rediriger('catalogue.php');
    }
    
    // Récupérer les produits du panier
    $ids = array_keys($_SESSION['panier']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT * FROM produits WHERE id IN ($placeholders)";
    $stmt = $db->prepare($sql);
    $stmt->execute($ids);
    $produits = $stmt->fetchAll();
    
    // Calculer le total
    $total = 0;
    foreach ($produits as $produit) {
        $quantite = $_SESSION['panier'][$produit['id']];
        $total += $produit['prix'] * $quantite;
    }
    
    // Créer la commande
    $db->beginTransaction();
    
    try {
        $sql = "INSERT INTO commandes (utilisateur_id, total, statut) VALUES (?, ?, 'en_attente')";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user_id, $total]);
        $commande_id = $db->lastInsertId();
        
        // Ajouter les détails de la commande
        $sql = "INSERT INTO details_commande (commande_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        
        foreach ($produits as $produit) {
            $quantite = $_SESSION['panier'][$produit['id']];
            $stmt->execute([$commande_id, $produit['id'], $quantite, $produit['prix']]);
            
            // Mettre à jour le stock
            $update_stock = $db->prepare("UPDATE produits SET stock = stock - ? WHERE id = ?");
            $update_stock->execute([$quantite, $produit['id']]);
        }
        
        $db->commit();
        
        // Vider le panier
        $_SESSION['panier'] = [];
        
        setFlash("Commande validée avec succès ! Merci pour votre achat.", "success");
        rediriger("commande.php?id=$commande_id");
        
    } catch(Exception $e) {
        $db->rollBack();
        setFlash("Erreur lors de la validation de la commande.", "error");
        rediriger('panier.php');
    }
}

// Voir une commande spécifique
if (isset($_GET['id'])) {
    $commande_id = (int)$_GET['id'];
    
    // Récupérer la commande
    $sql = "SELECT * FROM commandes WHERE id = ? AND utilisateur_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$commande_id, $user_id]);
    $commande = $stmt->fetch();
    
    if (!$commande) {
        setFlash("Commande non trouvée.", "error");
        rediriger('commande.php');
    }
    
    // Récupérer les détails
    $sql = "SELECT dc.*, p.nom, p.image 
            FROM details_commande dc 
            JOIN produits p ON dc.produit_id = p.id 
            WHERE dc.commande_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$commande_id]);
    $details = $stmt->fetchAll();
    
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
    .commande-detail{max-width:900px;margin:100px auto;padding:0 20px;}
    .commande-detail h1{font-family:'Bebas Neue',sans-serif;color:var(--dark);margin-bottom:20px;}
    .commande-info{background:var(--cream-light);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:30px;}
    .commande-info p{margin:8px 0;}
    .statut{display:inline-block;padding:4px 12px;border-radius:50px;font-size:.75rem;font-weight:600;}
    .statut.en_attente{background:rgba(212,175,55,.12);color:var(--gold-dark);}
    .statut.validee{background:rgba(40,180,100,.12);color:#2e7d32;}
    .statut.livree{background:rgba(33,150,243,.12);color:#1565c0;}
    .statut.annulee{background:rgba(244,67,54,.12);color:#c62828;}
    .commande-table{width:100%;border-collapse:collapse;background:var(--cream-light);border-radius:12px;overflow:hidden;}
    .commande-table th,.commande-table td{padding:12px;text-align:left;border-bottom:1px solid var(--border);}
    .commande-table th{background:var(--cream-dark);color:var(--gold);font-weight:600;font-size:.75rem;text-transform:uppercase;}
    .commande-produit img{width:40px;height:40px;object-fit:cover;border-radius:6px;margin-right:10px;vertical-align:middle;}
    .btn-retour{display:inline-block;margin-top:20px;padding:10px 20px;border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text-muted);transition:all .2s;}
    .btn-retour:hover{border-color:var(--gold);color:var(--gold);}
    </style>
    
    <div class="commande-detail">
        <h1>📋 Détail de la commande #<?php echo $commande['id']; ?></h1>
        
        <div class="commande-info">
            <p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?></p>
            <p><strong>Statut :</strong> 
                <span class="statut <?php echo $commande['statut']; ?>">
                    <?php 
                        switch($commande['statut']) {
                            case 'en_attente': echo '⏳ En attente'; break;
                            case 'validee': echo '✅ Validée'; break;
                            case 'livree': echo '🚚 Livrée'; break;
                            case 'annulee': echo '❌ Annulée'; break;
                            default: echo $commande['statut'];
                        }
                    ?>
                </span>
            </p>
            <p><strong>Total :</strong> <?php echo number_format($commande['total'], 2, ',', ' '); ?> €</p>
        </div>
        
        <h3 style="margin-bottom:15px;color:var(--dark);">Produits commandés</h3>
        <table class="commande-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Total</th>
                 </tr>
            </thead>
            <tbody>
                <?php foreach($details as $item): ?>
                    <tr>
                        <td class="commande-produit">
                            <img src="images/<?php echo $item['image'] ?? 'placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($item['nom']); ?>">
                            <?php echo htmlspecialchars($item['nom']); ?>
                         </td>
                        <td><?php echo number_format($item['prix_unitaire'], 2, ',', ' '); ?> €</td>
                        <td><?php echo $item['quantite']; ?></td>
                        <td><?php echo number_format($item['prix_unitaire'] * $item['quantite'], 2, ',', ' '); ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <a href="commande.php" class="btn-retour">← Retour à mes commandes</a>
    </div>
    
    <?php
    include 'includes/footer.php';
    exit();
}

// Lister toutes les commandes de l'utilisateur
$sql = "SELECT * FROM commandes WHERE utilisateur_id = ? ORDER BY date_commande DESC";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);
$commandes = $stmt->fetchAll();

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
.commandes-container{max-width:1000px;margin:100px auto;padding:0 20px;}
.commandes-container h1{font-family:'Bebas Neue',sans-serif;color:var(--dark);margin-bottom:30px;}
.commandes-table{width:100%;border-collapse:collapse;background:var(--cream-light);border-radius:12px;overflow:hidden;}
.commandes-table th,.commandes-table td{padding:15px;text-align:left;border-bottom:1px solid var(--border);}
.commandes-table th{background:var(--cream-dark);color:var(--gold);font-weight:600;font-size:.75rem;text-transform:uppercase;}
.statut-badge{display:inline-block;padding:4px 12px;border-radius:50px;font-size:.7rem;font-weight:600;}
.statut-badge.en_attente{background:rgba(212,175,55,.12);color:var(--gold-dark);}
.statut-badge.validee{background:rgba(40,180,100,.12);color:#2e7d32;}
.statut-badge.livree{background:rgba(33,150,243,.12);color:#1565c0;}
.statut-badge.annulee{background:rgba(244,67,54,.12);color:#c62828;}
.btn-detail-commande{padding:6px 12px;border:1px solid var(--border);border-radius:6px;color:var(--text-muted);font-size:.75rem;transition:all .2s;}
.btn-detail-commande:hover{border-color:var(--gold);color:var(--gold);}
.aucune-commande{text-align:center;padding:60px 20px;background:var(--cream-light);border-radius:12px;border:1px solid var(--border);}
.aucune-commande-icon{font-size:4rem;margin-bottom:20px;}
.aucune-commande h2{color:var(--dark);margin-bottom:10px;}
.aucune-commande p{color:var(--text-muted);margin-bottom:25px;}
.btn-decouvrir{display:inline-block;padding:12px 25px;background:var(--gold);color:var(--dark);border-radius:50px;font-weight:600;transition:all .2s;}
.btn-decouvrir:hover{background:var(--gold-light);transform:translateY(-2px);}
</style>

<div class="commandes-container">
    <h1>📋 Mes commandes</h1>
    
    <?php if(count($commandes) > 0): ?>
        <div class="commandes-liste">
            <table class="commandes-table">
                <thead>
                    <tr>
                        <th>N° commande</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($commandes as $commande): ?>
                        <tr>
                            <td>#<?php echo $commande['id']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($commande['date_commande'])); ?></td>
                            <td><?php echo number_format($commande['total'], 2, ',', ' '); ?> €</td>
                            <td>
                                <span class="statut-badge <?php echo $commande['statut']; ?>">
                                    <?php echo $commande['statut']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="commande.php?id=<?php echo $commande['id']; ?>" class="btn-detail-commande">Voir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="aucune-commande">
            <div class="aucune-commande-icon">📦</div>
            <h2>Vous n'avez pas encore passé de commande</h2>
            <p>Découvrez nos produits et faites votre première commande !</p>
            <a href="catalogue.php" class="btn-decouvrir">🛍️ Découvrir nos produits</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>