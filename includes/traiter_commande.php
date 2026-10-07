<?php
session_start();
require_once 'includes/config.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit();
}

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit();
}

// Vérifier le panier
if (empty($_SESSION['panier'])) {
    echo json_encode(['success' => false, 'message' => 'Panier vide']);
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $user_id = $_SESSION['user_id'];
    
    // Calculer le total
    $ids = array_keys($_SESSION['panier']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $produits = $stmt->fetchAll();
    
    $total = 0;
    foreach ($produits as $produit) {
        $quantite = $_SESSION['panier'][$produit['id']];
        $total += $produit['prix'] * $quantite;
    }
    
    // Ajouter les frais de livraison
    $total += $data['frais_livraison'];
    
    // Sauvegarder l'adresse en session (optionnel)
    $_SESSION['adresse_livraison'] = [
        'prenom' => $data['prenom'],
        'nom' => $data['nom'],
        'adresse' => $data['adresse'],
        'cp' => $data['cp'],
        'ville' => $data['ville'],
        'telephone' => $data['telephone']
    ];
    $_SESSION['methode_paiement'] = $data['paiement'];
    $_SESSION['mode_livraison'] = $data['livraison'];
    $_SESSION['frais_livraison'] = $data['frais_livraison'];
    
    // Créer la commande
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("INSERT INTO commandes (utilisateur_id, total, statut) VALUES (?, ?, 'en_attente')");
    $stmt->execute([$user_id, $total]);
    $commande_id = $pdo->lastInsertId();
    
    // Ajouter les détails
    $stmt = $pdo->prepare("INSERT INTO details_commande (commande_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
    foreach ($produits as $produit) {
        $quantite = $_SESSION['panier'][$produit['id']];
        $stmt->execute([$commande_id, $produit['id'], $quantite, $produit['prix']]);
        
        // Mettre à jour le stock
        $update = $pdo->prepare("UPDATE produits SET stock = stock - ? WHERE id = ?");
        $update->execute([$quantite, $produit['id']]);
    }
    
    $pdo->commit();
    
    // Vider le panier
    $_SESSION['panier'] = [];
    
    echo json_encode(['success' => true, 'commande_id' => $commande_id]);
    
} catch(Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>