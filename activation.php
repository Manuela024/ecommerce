<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$type = 'error';

// Récupérer le token
$token = isset($_GET['token']) ? nettoyer($_GET['token']) : '';

if (empty($token)) {
    $message = "Token d'activation invalide.";
} else {
    // Vérifier le token
    $stmt = $db->prepare("SELECT id, nom, email FROM utilisateurs WHERE token_activation = ? AND actif = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Activer le compte
        $update = $db->prepare("UPDATE utilisateurs SET actif = 1, token_activation = NULL WHERE id = ?");
        $update->execute([$user['id']]);
        
        $message = "Votre compte a été activé avec succès ! Vous pouvez maintenant vous connecter.";
        $type = 'success';
    } else {
        // Vérifier si le compte est déjà activé
        $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE token_activation = ? AND actif = 1");
        $stmt->execute([$token]);
        if ($stmt->fetch()) {
            $message = "Votre compte est déjà activé. <a href='login.php'>Connectez-vous</a>";
            $type = 'info';
        } else {
            $message = "Token d'activation invalide ou expiré.";
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <div class="flash-message flash-<?php echo $type; ?>">
        <?php echo $message; ?>
    </div>
    <p style="text-align: center; margin-top: 1rem;">
        <a href="login.php">Aller à la page de connexion</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>