<?php
require_once 'includes/db.php';

$database = new Database();
$db = $database->getConnection();

// Nouveau mot de passe
$nouveau_mdp = "admin123";  // Changez ici

$hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);

$sql = "UPDATE utilisateurs SET mot_de_passe = :mdp WHERE email = :email";
$stmt = $db->prepare($sql);
$stmt->execute([
    ':mdp' => $hash,
    ':email' => 'admin@techshop.com'
]);

if ($stmt->rowCount() > 0) {
    echo "✅ Mot de passe admin modifié avec succès !<br>";
    echo "Email : admin@techshop.com<br>";
    echo "Mot de passe : " . $nouveau_mdp . "<br>";
    echo "<a href='login.php'>Aller à la page de connexion</a>";
} else {
    echo "❌ Aucun admin trouvé. Création d'un nouveau compte admin...<br>";
    
    // Créer un nouveau compte admin
    $sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, role, actif) VALUES 
            ('Administrateur', 'admin@techshop.com', :mdp, 'admin', 1)";
    $stmt = $db->prepare($sql);
    $stmt->execute([':mdp' => $hash]);
    
    echo "✅ Compte admin créé !<br>";
    echo "Email : admin@techshop.com<br>";
    echo "Mot de passe : " . $nouveau_mdp . "<br>";
    echo "<a href='login.php'>Aller à la page de connexion</a>";
}
?>