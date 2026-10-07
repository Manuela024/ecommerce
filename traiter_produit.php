<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

// Vérifier si l'utilisateur est admin
if (!estConnecte() || !estAdmin()) {
    header('Location: index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
$nom = nettoyer($_POST['nom']);
$description = nettoyer($_POST['description']);
$prix = (float)$_POST['prix'];
$marque = nettoyer($_POST['marque']);
$stock = (int)$_POST['stock'];
$type = nettoyer($_POST['type']);

// Gestion de l'image
$image_name = '';

if ($edit_id > 0) {
    $stmt = $db->prepare("SELECT image FROM produits WHERE id = ?");
    $stmt->execute([$edit_id]);
    $old_image = $stmt->fetchColumn();
    $image_name = $old_image;
}

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0 && $_FILES['image']['size'] > 0) {
    $target_dir = "images/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $image_name = time() . '_' . uniqid() . '.' . $extension;
    $target_file = $target_dir . $image_name;
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
    $file_type = $_FILES['image']['type'];
    
    if (in_array($file_type, $allowed_types)) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            if ($edit_id > 0 && !empty($old_image) && file_exists($target_dir . $old_image)) {
                unlink($target_dir . $old_image);
            }
        }
    }
}

if ($edit_id > 0) {
    // Modification
    $sql = "UPDATE produits SET nom=?, description=?, prix=?, marque=?, stock=?, type=?, image=? WHERE id=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$nom, $description, $prix, $marque, $stock, $type, $image_name, $edit_id]);
    header('Location: produit_modifie.php');
    exit();
} else {
    // Ajout
    $sql = "INSERT INTO produits (nom, description, prix, marque, stock, type, image, date_ajout) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    $stmt->execute([$nom, $description, $prix, $marque, $stock, $type, $image_name]);
    header('Location: produit_enregistre.php');
    exit();
}
?>