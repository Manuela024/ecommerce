<?php
session_start();

// Configuration de la base de données
$host = 'localhost';
$dbname = 'ecommerce';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    exit();
}

// Initialiser le panier
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Récupérer les données
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID produit invalide']);
    exit();
}

// Vérifier si le produit existe
$stmt = $pdo->prepare("SELECT stock, nom FROM produits WHERE id = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch();

if (!$produit) {
    echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
    exit();
}

if ($produit['stock'] < $quantite) {
    echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
    exit();
}

// Ajouter au panier
if (isset($_SESSION['panier'][$id])) {
    $_SESSION['panier'][$id] += $quantite;
} else {
    $_SESSION['panier'][$id] = $quantite;
}

// Calculer le nombre total d'articles
$nb_articles = array_sum($_SESSION['panier']);

// Retourner la réponse JSON
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Produit ajouté au panier',
    'nb_articles' => $nb_articles
]);
?>