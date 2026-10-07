<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

// Vérifier si le panier n'est pas vide
if (empty($_SESSION['panier'])) {
    setFlash("Votre panier est vide. Ajoutez des produits avant de passer commande.", "error");
    rediriger('catalogue.php');
}

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    setFlash("Veuillez vous connecter pour passer commande.", "error");
    rediriger('login.php');
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Récupérer les produits du panier
$ids = array_keys($_SESSION['panier']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "SELECT * FROM produits WHERE id IN ($placeholders)";
$stmt = $db->prepare($sql);
$stmt->execute($ids);
$produits = $stmt->fetchAll();

// Calculer le total
$total = 0;
$panier_details = [];
foreach ($produits as $produit) {
    $quantite = $_SESSION['panier'][$produit['id']];
    $sous_total = $produit['prix'] * $quantite;
    $total += $sous_total;
    $panier_details[] = [
        'produit' => $produit,
        'quantite' => $quantite,
        'sous_total' => $sous_total
    ];
}

// Récupérer l'adresse de l'utilisateur (si existante dans la session)
$adresse_livraison = isset($_SESSION['adresse_livraison']) ? $_SESSION['adresse_livraison'] : [];

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Validation de commande - TechShop</title>
<style>
:root {
    --gold: #D4AF37;
    --gold-light: #E5C158;
    --gold-dark: #C5A028;
    --cream: #FDFBF7;
    --cream-dark: #F9F6F0;
    --cream-light: #FFF8E7;
    --dark: #2C2C2C;
    --dark-soft: #3A3A3A;
    --border: #E8D5A3;
    --text-muted: #888;
    --success: #2e7d32;
    --error: #c62828;
    --shadow: 0 10px 30px rgba(0,0,0,0.05);
    --shadow-hover: 0 15px 35px rgba(0,0,0,0.1);
    --radius: 16px;
    --radius-sm: 10px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: var(--cream);
    font-family: 'Inter', sans-serif;
    color: var(--dark);
}

.checkout-container {
    max-width: 1400px;
    margin: 100px auto 60px;
    padding: 0 30px;
}

.checkout-container h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2.5rem;
    color: var(--dark);
    margin-bottom: 30px;
    letter-spacing: 1px;
    position: relative;
    display: inline-block;
}

.checkout-container h1:after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 60px;
    height: 3px;
    background: var(--gold);
    border-radius: 3px;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 30px;
}

/* Formulaire */
.checkout-form {
    background: var(--cream-light);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: 30px;
    box-shadow: var(--shadow);
}

.checkout-section {
    margin-bottom: 35px;
    padding-bottom: 25px;
    border-bottom: 1px solid var(--border);
}

.checkout-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.checkout-section h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.5rem;
    color: var(--dark);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 8px;
    letter-spacing: 0.5px;
}

.form-group input, 
.form-group select {
    width: 100%;
    padding: 12px 16px;
    background: var(--cream);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    color: var(--dark);
    font-size: 0.9rem;
    transition: all 0.2s;
}

.form-group input:focus, 
.form-group select:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,175,55,0.1);
}

/* Options grid pour livraison/paiement */
.options-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.option-card {
    cursor: pointer;
    transition: all 0.2s;
}

.option-card input {
    display: none;
}

.option-content {
    background: var(--cream);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 15px;
    text-align: center;
    transition: all 0.2s;
    cursor: pointer;
}

.option-card input:checked + .option-content {
    border-color: var(--gold);
    background: linear-gradient(135deg, var(--cream-light), var(--cream));
    box-shadow: 0 5px 15px rgba(212,175,55,0.15);
    transform: translateY(-2px);
}

.option-icon {
    font-size: 1.8rem;
    display: block;
    margin-bottom: 8px;
}

.option-title {
    font-weight: 600;
    color: var(--dark);
    display: block;
    margin-bottom: 5px;
}

.option-price {
    color: var(--gold-dark);
    font-weight: 700;
    font-size: 0.9rem;
    display: block;
    margin-bottom: 3px;
}

.option-desc {
    font-size: 0.7rem;
    color: var(--text-muted);
}

.payment-details {
    background: var(--cream-dark);
    border-radius: var(--radius-sm);
    padding: 20px;
    margin-top: 20px;
    border: 1px solid var(--border);
    animation: fadeIn 0.3s ease;
}

.payment-details h4 {
    font-size: 0.9rem;
    margin-bottom: 15px;
    color: var(--gold-dark);
}

.info-text {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 10px;
}

.btn-valider-final {
    width: 100%;
    background: var(--gold);
    color: var(--dark);
    border: none;
    padding: 16px;
    border-radius: var(--radius-sm);
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-valider-final:hover {
    background: var(--gold-light);
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(212,175,55,0.3);
}

/* Récapitulatif */
.checkout-summary {
    background: var(--cream-light);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: 25px;
    position: sticky;
    top: 100px;
    height: fit-content;
    box-shadow: var(--shadow);
}

.checkout-summary h3 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.4rem;
    color: var(--dark);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border);
}

.summary-products {
    max-height: 400px;
    overflow-y: auto;
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
}

.summary-item img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    background: var(--cream-dark);
}

.summary-item-info {
    flex: 1;
}

.summary-item-name {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--dark);
    margin-bottom: 3px;
}

.summary-item-qty {
    font-size: 0.7rem;
    color: var(--text-muted);
}

.summary-item-price {
    font-weight: 600;
    color: var(--gold-dark);
}

.summary-total {
    background: var(--cream-dark);
    border-radius: var(--radius-sm);
    padding: 15px;
    margin: 15px 0;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 0.9rem;
    color: var(--dark-soft);
}

.summary-line.total {
    border-top: 1px solid var(--border);
    margin-top: 8px;
    padding-top: 12px;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--dark);
}

.summary-line.total span:last-child {
    color: var(--gold-dark);
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.4rem;
}

.summary-security {
    text-align: center;
    font-size: 0.7rem;
    color: var(--text-muted);
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid var(--border);
}

.summary-security p {
    margin: 5px 0;
}

/* Notifications */
.flash-message {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 1000;
    padding: 12px 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideIn 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive */
@media (max-width: 1024px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    .checkout-summary {
        position: static;
        order: -1;
    }
}

@media (max-width: 768px) {
    .checkout-container {
        padding: 0 20px;
        margin: 80px auto 40px;
    }
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    .options-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .checkout-form {
        padding: 20px;
    }
}
</style>

<div class="checkout-container">
    <h1>📋 Validation de commande</h1>
    
    <div class="checkout-grid">
        <!-- Colonne de gauche : Formulaire -->
        <div class="checkout-form">
            <!-- Étape 1 : Adresse de livraison -->
            <div class="checkout-section">
                <h2>📍 Adresse de livraison</h2>
                <form id="adresseForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Prénom *</label>
                            <input type="text" id="prenom" placeholder="Jean" required value="<?php echo htmlspecialchars($adresse_livraison['prenom'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Nom *</label>
                            <input type="text" id="nom" placeholder="Dupont" required value="<?php echo htmlspecialchars($adresse_livraison['nom'] ?? $_SESSION['user_nom'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Adresse *</label>
                        <input type="text" id="adresse" placeholder="Numéro et rue" required value="<?php echo htmlspecialchars($adresse_livraison['adresse'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Code postal *</label>
                            <input type="text" id="cp" placeholder="75001" required value="<?php echo htmlspecialchars($adresse_livraison['cp'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Ville *</label>
                            <input type="text" id="ville" placeholder="Paris" required value="<?php echo htmlspecialchars($adresse_livraison['ville'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="tel" id="telephone" placeholder="01 23 45 67 89" value="<?php echo htmlspecialchars($adresse_livraison['telephone'] ?? ''); ?>">
                    </div>
                </form>
            </div>
            
            <!-- Étape 2 : Mode de livraison -->
            <div class="checkout-section">
                <h2>🚚 Mode de livraison</h2>
                <div class="options-grid">
                    <label class="option-card">
                        <input type="radio" name="livraison" value="standard" checked>
                        <div class="option-content">
                            <span class="option-icon">📦</span>
                            <span class="option-title">Livraison standard</span>
                            <span class="option-price">Gratuite</span>
                            <span class="option-desc">Sous 3-5 jours ouvrés</span>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="livraison" value="express">
                        <div class="option-content">
                            <span class="option-icon">⚡</span>
                            <span class="option-title">Livraison express</span>
                            <span class="option-price">+5,99€</span>
                            <span class="option-desc">Sous 24h</span>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="livraison" value="relais">
                        <div class="option-content">
                            <span class="option-icon">🏪</span>
                            <span class="option-title">Point relais</span>
                            <span class="option-price">2,99€</span>
                            <span class="option-desc">Sous 2-3 jours</span>
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- Étape 3 : Mode de paiement -->
            <div class="checkout-section">
                <h2>💳 Mode de paiement</h2>
                <div class="options-grid">
                    <label class="option-card">
                        <input type="radio" name="paiement" value="carte" checked>
                        <div class="option-content">
                            <span class="option-icon">💳</span>
                            <span class="option-title">Carte bancaire</span>
                            <span class="option-desc">Visa, Mastercard</span>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="paiement" value="mobile_money">
                        <div class="option-content">
                            <span class="option-icon">📱</span>
                            <span class="option-title">Mobile Money</span>
                            <span class="option-desc">Orange Money, MTN</span>
                        </div>
                    </label>
                    <label class="option-card">
                        <input type="radio" name="paiement" value="paypal">
                        <div class="option-content">
                            <span class="option-icon">💙</span>
                            <span class="option-title">PayPal</span>
                            <span class="option-desc">Paiement sécurisé</span>
                        </div>
                    </label>
                </div>
                
                <!-- Détails carte bancaire -->
                <div id="carteDetails" class="payment-details">
                    <h4>Informations carte bancaire</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Numéro de carte</label>
                            <input type="text" placeholder="1234 5678 9012 3456">
                        </div>
                        <div class="form-group">
                            <label>Date d'expiration</label>
                            <input type="text" placeholder="MM/AA">
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" placeholder="123">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nom sur la carte</label>
                        <input type="text" placeholder="Nom du titulaire">
                    </div>
                </div>
                
                <!-- Détails Mobile Money -->
                <div id="mobileMoneyDetails" class="payment-details" style="display:none;">
                    <h4>Informations Mobile Money</h4>
                    <div class="form-group">
                        <label>Opérateur</label>
                        <select>
                            <option value="orange">Orange Money</option>
                            <option value="mtn">MTN Mobile Money</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Numéro de téléphone</label>
                        <input type="tel" placeholder="6X XX XX XX">
                    </div>
                </div>
                
                <!-- Détails PayPal -->
                <div id="paypalDetails" class="payment-details" style="display:none;">
                    <h4>Connexion PayPal</h4>
                    <div class="form-group">
                        <label>Email PayPal</label>
                        <input type="email" placeholder="exemple@email.com">
                    </div>
                    <p class="info-text">Vous serez redirigé vers PayPal après validation.</p>
                </div>
            </div>
            
            <button onclick="validerCommande()" class="btn-valider-final">✅ Confirmer et payer</button>
        </div>
        
        <!-- Colonne de droite : Récapitulatif -->
        <div class="checkout-summary">
            <h3>🛒 Votre commande</h3>
            
            <div class="summary-products">
                <?php foreach($panier_details as $item): ?>
                    <div class="summary-item">
                        <img src="images/<?php echo $item['produit']['image'] ?? 'placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($item['produit']['nom']); ?>">
                        <div class="summary-item-info">
                            <p class="summary-item-name"><?php echo htmlspecialchars($item['produit']['nom']); ?></p>
                            <p class="summary-item-qty">Quantité: x<?php echo $item['quantite']; ?></p>
                        </div>
                        <div class="summary-item-price"><?php echo number_format($item['sous_total'], 2, ',', ' '); ?> €</div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="summary-total">
                <div class="summary-line">
                    <span>Sous-total</span>
                    <span><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                </div>
                <div class="summary-line" id="fraisLivraisonLine" style="display:none;">
                    <span>Livraison</span>
                    <span id="fraisLivraison">+0,00 €</span>
                </div>
                <div class="summary-line total">
                    <span>Total TTC</span>
                    <span id="totalFinal"><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                </div>
            </div>
            
            <div class="summary-security">
                <p>🔒 Paiement 100% sécurisé</p>
                <p>🔐 Vos données sont cryptées</p>
            </div>
        </div>
    </div>
</div>

<script>
// Afficher/masquer les détails de paiement avec animation
document.querySelectorAll('input[name="paiement"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const carteDiv = document.getElementById('carteDetails');
        const mobileDiv = document.getElementById('mobileMoneyDetails');
        const paypalDiv = document.getElementById('paypalDetails');
        
        [carteDiv, mobileDiv, paypalDiv].forEach(div => {
            div.style.display = 'none';
        });
        
        if (this.value === 'carte') {
            carteDiv.style.display = 'block';
        } else if (this.value === 'mobile_money') {
            mobileDiv.style.display = 'block';
        } else if (this.value === 'paypal') {
            paypalDiv.style.display = 'block';
        }
    });
});

// Mettre à jour le total selon la livraison
let fraisLivraison = 0;
const totalBase = <?php echo $total; ?>;

document.querySelectorAll('input[name="livraison"]').forEach(radio => {
    radio.addEventListener('change', function() {
        switch(this.value) {
            case 'express':
                fraisLivraison = 5.99;
                break;
            case 'relais':
                fraisLivraison = 2.99;
                break;
            default:
                fraisLivraison = 0;
        }
        
        const fraisLine = document.getElementById('fraisLivraisonLine');
        const fraisSpan = document.getElementById('fraisLivraison');
        const totalFinalSpan = document.getElementById('totalFinal');
        
        if (fraisLivraison > 0) {
            fraisLine.style.display = 'flex';
            fraisSpan.innerText = '+ ' + fraisLivraison.toFixed(2).replace('.', ',') + ' €';
        } else {
            fraisLine.style.display = 'none';
        }
        
        const totalFinal = totalBase + fraisLivraison;
        totalFinalSpan.innerText = totalFinal.toFixed(2).replace('.', ',') + ' €';
    });
});

// Valider la commande
function validerCommande() {
    // Récupérer et valider l'adresse
    const prenom = document.getElementById('prenom').value.trim();
    const nom = document.getElementById('nom').value.trim();
    const adresse = document.getElementById('adresse').value.trim();
    const cp = document.getElementById('cp').value.trim();
    const ville = document.getElementById('ville').value.trim();
    
    if (!prenom || !nom || !adresse || !cp || !ville) {
        alert('Veuillez remplir tous les champs de l\'adresse de livraison.');
        return;
    }
    
    // Récupérer les options sélectionnées
    const livraisonRadio = document.querySelector('input[name="livraison"]:checked');
    const paiementRadio = document.querySelector('input[name="paiement"]:checked');
    
    if (!livraisonRadio || !paiementRadio) {
        alert('Veuillez sélectionner un mode de livraison et de paiement.');
        return;
    }
    
    const livraison = livraisonRadio.value;
    const paiement = paiementRadio.value;
    
    // Calculer les frais
    let frais = 0;
    switch(livraison) {
        case 'express': frais = 5.99; break;
        case 'relais': frais = 2.99; break;
        default: frais = 0;
    }
    
    // Préparer les données
    const data = {
        prenom: prenom,
        nom: nom,
        adresse: adresse,
        cp: cp,
        ville: ville,
        telephone: document.getElementById('telephone').value.trim(),
        livraison: livraison,
        frais_livraison: frais,
        paiement: paiement
    };
    
    // Désactiver le bouton pour éviter double soumission
    const btn = document.querySelector('.btn-valider-final');
    btn.disabled = true;
    btn.innerHTML = '⏳ Traitement en cours...';
    
    fetch('traiter_commande.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            window.location.href = 'confirmation.php?id=' + result.commande_id;
        } else {
            alert('Erreur: ' + result.message);
            btn.disabled = false;
            btn.innerHTML = '✅ Confirmer et payer';
        }
    })
    .catch(error => {
        alert('Erreur lors de la commande. Veuillez réessayer.');
        btn.disabled = false;
        btn.innerHTML = '✅ Confirmer et payer';
    });
}
</script>

<?php include 'includes/footer.php'; ?>