<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    setFlash("Veuillez vous connecter pour passer commande.", "error");
    rediriger('login.php');
}

// Vérifier si le panier n'est pas vide
if (empty($_SESSION['panier'])) {
    setFlash("Votre panier est vide.", "error");
    rediriger('catalogue.php');
}

// Vérifier que le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    rediriger('checkout.php');
}

// Récupérer les données du formulaire (utile pour les statistiques même sans les colonnes)
$nom_complet = trim($_POST['nom_complet'] ?? '');
$adresse = trim($_POST['adresse'] ?? '');
$code_postal = trim($_POST['code_postal'] ?? '');
$ville = trim($_POST['ville'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$livraison = $_POST['livraison'] ?? 'standard';
$paiement = $_POST['paiement'] ?? 'carte';

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
foreach ($produits as $produit) {
    $quantite = $_SESSION['panier'][$produit['id']];
    $total += $produit['prix'] * $quantite;
}

// Ajouter les frais de livraison
$frais_livraison = 0;
if ($livraison === 'express') {
    $frais_livraison = 9.90;
}
$total_final = $total + $frais_livraison;

try {
    $db->beginTransaction();
    
    // Insérer la commande (version sans les colonnes supplémentaires)
    $sql = "INSERT INTO commandes (utilisateur_id, total, statut) VALUES (?, ?, 'en_attente')";
    $stmt = $db->prepare($sql);
    $stmt->execute([$user_id, $total_final]);
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
    
    // Stocker les infos de livraison en session si nécessaire (optionnel)
    $_SESSION['derniere_commande'] = [
        'nom_complet' => $nom_complet,
        'adresse' => $adresse,
        'ville' => $ville,
        'livraison' => $livraison,
        'paiement' => $paiement
    ];
    
    setFlash("✅ Votre commande #$commande_id a été enregistrée avec succès ! Merci pour votre achat.", "success");
    rediriger("commande.php?id=$commande_id");
    
} catch(Exception $e) {
    $db->rollBack();
    error_log("Erreur commande : " . $e->getMessage());
    setFlash("❌ Une erreur est survenue lors de l'enregistrement de votre commande.", "error");
    rediriger('checkout.php');
}
?>