<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Récupérer le produit
$sql = "SELECT p.*, c.processeur, c.ram, c.stockage, c.carte_graphique 
        FROM produits p 
        LEFT JOIN caracteristiques c ON p.id = c.produit_id 
        WHERE p.id = :id";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $id]);
$produit = $stmt->fetch();

if (!$produit) {
    setFlash("Produit non trouvé.", "error");
    rediriger('catalogue.php');
}

// Récupérer les produits similaires
$similaires_sql = "SELECT * FROM produits WHERE (type = :type OR marque = :marque) AND id != :id LIMIT 4";
$similaires_stmt = $db->prepare($similaires_sql);
$similaires_stmt->execute([
    ':type' => $produit['type'],
    ':marque' => $produit['marque'],
    ':id' => $id
]);
$similaires = $similaires_stmt->fetchAll();

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
  --radius:14px;
  --radius-sm:8px;
}

.produit-detail{
  max-width:1200px;margin:100px auto 60px;padding:0 20px;
}
.produit-detail-grid{
  display:grid;grid-template-columns:1fr 1fr;gap:40px;
  background:var(--cream-light);border-radius:var(--radius);
  padding:30px;border:1px solid var(--border);
}
.produit-detail-image img{
  width:100%;border-radius:var(--radius-sm);
  border:1px solid var(--border);
}
.stock-info{
  margin-top:15px;padding:10px;border-radius:var(--radius-sm);
  text-align:center;font-weight:600;
}
.dispo{background:rgba(212,175,55,.12);color:var(--gold-dark);border:1px solid rgba(212,175,55,.3);}
.rupture{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
.produit-detail-info h1{
  font-family:'Bebas Neue',sans-serif;font-size:2.5rem;color:var(--dark);margin-bottom:10px;
}
.produit-marque-detail{
  color:var(--gold);font-weight:600;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;
}
.produit-categorie{
  color:var(--text-muted);font-size:.85rem;margin-bottom:15px;
}
.produit-prix-detail{
  font-family:'Bebas Neue',sans-serif;font-size:2.5rem;
  color:var(--gold-dark);margin:20px 0;
}
.produit-description{
  margin:20px 0;padding:15px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);
}
.produit-description h3{color:var(--dark);margin-bottom:10px;font-size:1rem;}
.produit-caracteristiques{margin:20px 0;}
.produit-caracteristiques h3{color:var(--dark);margin-bottom:15px;font-size:1rem;}
.caracteristiques-table{
  width:100%;border-collapse:collapse;background:var(--cream-dark);border-radius:var(--radius-sm);overflow:hidden;
}
.caracteristiques-table tr{border-bottom:1px solid var(--border);}
.caracteristiques-table tr:last-child{border-bottom:none;}
.caracteristiques-table td{padding:12px 15px;font-size:.85rem;}
.caracteristiques-table td:first-child{font-weight:600;color:var(--dark);width:40%;}
.quantite-selector{
  display:flex;align-items:center;gap:15px;margin:20px 0;
}
.quantite-selector label{font-weight:600;color:var(--dark);}
.quantite-selector input{
  width:70px;padding:8px;border:1px solid var(--border);border-radius:var(--radius-sm);
  background:var(--cream);color:var(--dark);font-size:.9rem;
}
.btn-ajouter-panier{
  background:var(--gold);color:var(--dark);padding:14px 28px;
  border:none;border-radius:var(--radius-sm);font-weight:600;
  font-size:1rem;cursor:pointer;transition:all .2s;width:100%;
}
.btn-ajouter-panier:hover{background:var(--gold-light);transform:translateY(-2px);}
.btn-indispo-panier{
  background:#e9ecef;color:var(--text-muted);padding:14px 28px;
  border:1px solid var(--border);border-radius:var(--radius-sm);
  font-weight:600;width:100%;cursor:not-allowed;
}
.produits-similaires{
  margin-top:60px;
}
.produits-similaires h3{
  font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:var(--dark);margin-bottom:25px;
}
.similaires-grid{
  display:grid;grid-template-columns:repeat(4,1fr);gap:20px;
}
.similaire-card{
  background:var(--cream-light);border:1px solid var(--border);border-radius:var(--radius);
  padding:16px;text-align:center;transition:all .3s;
}
.similaire-card:hover{transform:translateY(-5px);border-color:var(--gold);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.similaire-card img{
  width:100%;height:140px;object-fit:cover;border-radius:var(--radius-sm);margin-bottom:12px;
}
.similaire-card h4{font-size:.9rem;color:var(--dark);margin-bottom:8px;}
.similaire-prix{
  color:var(--gold-dark);font-weight:600;font-size:1.1rem;margin-bottom:12px;
}
.btn-similaire{
  background:var(--gold);color:var(--dark);padding:8px 20px;
  border-radius:6px;display:inline-block;text-decoration:none;
  font-size:.8rem;font-weight:600;transition:all .2s;
}
.btn-similaire:hover{background:var(--gold-light);}

@media(max-width:768px){
  .produit-detail-grid{grid-template-columns:1fr;}
  .similaires-grid{grid-template-columns:repeat(2,1fr);}
}
</style>

<div class="produit-detail">
    <div class="produit-detail-grid">
        <!-- Image -->
        <div class="produit-detail-image">
            <img src="images/<?php echo $produit['image'] ?? 'placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($produit['nom']); ?>">
            <?php if($produit['stock'] > 0): ?>
                <div class="stock-info dispo">✅ En stock (<?php echo $produit['stock']; ?> disponibles)</div>
            <?php else: ?>
                <div class="stock-info rupture">❌ Rupture de stock</div>
            <?php endif; ?>
        </div>
        
        <!-- Informations -->
        <div class="produit-detail-info">
            <h1><?php echo htmlspecialchars($produit['nom']); ?></h1>
            <p class="produit-marque-detail">Marque : <?php echo htmlspecialchars($produit['marque']); ?></p>
            <p class="produit-categorie">Catégorie : <?php echo $produit['type']; ?></p>
            <div class="produit-prix-detail"><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</div>
            
            <div class="produit-description">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($produit['description'])); ?></p>
            </div>
            
            <!-- Caractéristiques techniques -->
            <?php if($produit['processeur'] || $produit['ram'] || $produit['stockage'] || $produit['carte_graphique']): ?>
                <div class="produit-caracteristiques">
                    <h3>Caractéristiques techniques</h3>
                    <table class="caracteristiques-table">
                        <?php if($produit['processeur']): ?>
                            <tr>
                                <td>Processeur</td>
                                <td><?php echo htmlspecialchars($produit['processeur']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if($produit['ram']): ?>
                            <tr>
                                <td>RAM</td>
                                <td><?php echo htmlspecialchars($produit['ram']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if($produit['stockage']): ?>
                            <tr>
                                <td>Stockage</td>
                                <td><?php echo htmlspecialchars($produit['stockage']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if($produit['carte_graphique']): ?>
                            <tr>
                                <td>Carte graphique</td>
                                <td><?php echo htmlspecialchars($produit['carte_graphique']); ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            <?php endif; ?>
            
            <!-- Ajout au panier -->
            <?php if($produit['stock'] > 0): ?>
                <form action="panier.php" method="POST" class="ajout-panier-form">
                    <input type="hidden" name="action" value="ajouter">
                    <input type="hidden" name="id" value="<?php echo $produit['id']; ?>">
                    <div class="quantite-selector">
                        <label>Quantité :</label>
                        <input type="number" name="quantite" value="1" min="1" max="<?php echo $produit['stock']; ?>">
                    </div>
                    <button type="submit" class="btn-ajouter-panier">🛒 Ajouter au panier</button>
                </form>
            <?php else: ?>
                <button class="btn-indispo-panier" disabled>📦 Produit indisponible</button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Produits similaires -->
    <?php if(count($similaires) > 0): ?>
        <div class="produits-similaires">
            <h3>🔄 Produits similaires</h3>
            <div class="similaires-grid">
                <?php foreach($similaires as $similaire): ?>
                    <div class="similaire-card">
                        <img src="images/<?php echo $similaire['image'] ?? 'placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($similaire['nom']); ?>">
                        <h4><?php echo htmlspecialchars($similaire['nom']); ?></h4>
                        <p class="similaire-prix"><?php echo number_format($similaire['prix'], 2, ',', ' '); ?> €</p>
                        <a href="produit.php?id=<?php echo $similaire['id']; ?>" class="btn-similaire">Voir</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>