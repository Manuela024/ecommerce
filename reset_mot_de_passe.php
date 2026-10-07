<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

$database = new Database();
$db = $database->getConnection();

$token = isset($_GET['token']) ? nettoyer($_GET['token']) : '';
$message = '';
$type = '';
$user_id = null;

// Vérifier le token
if ($token) {
    $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE mot_de_passe_oublie_token = ? AND mot_de_passe_oublie_expire > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $user_id = $user['id'];
    } else {
        $message = "Lien invalide ou expiré. Veuillez refaire une demande.";
        $type = "error";
    }
}

// Réinitialiser le mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id) {
    $nouveau_mdp = $_POST['mot_de_passe'];
    $confirme_mdp = $_POST['confirme_mdp'];
    
    if (strlen($nouveau_mdp) < 6) {
        $message = "Le mot de passe doit contenir au moins 6 caractères.";
        $type = "error";
    } elseif ($nouveau_mdp !== $confirme_mdp) {
        $message = "Les mots de passe ne correspondent pas.";
        $type = "error";
    } else {
        $hashed = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE utilisateurs SET mot_de_passe = ?, mot_de_passe_oublie_token = NULL, mot_de_passe_oublie_expire = NULL WHERE id = ?");
        $update->execute([$hashed, $user_id]);
        
        $message = "Votre mot de passe a été réinitialisé avec succès ! Vous pouvez maintenant vous connecter.";
        $type = "success";
        $user_id = null; // Cacher le formulaire
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h2>🔑 Nouveau mot de passe</h2>
    
    <?php if($message): ?>
        <div class="flash-message flash-<?php echo $type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <?php if($user_id && $type != 'success'): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="mot_de_passe" required>
                <small>Minimum 6 caractères</small>
            </div>
            
            <div class="form-group">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="confirme_mdp" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Réinitialiser</button>
        </form>
    <?php elseif($type == 'success'): ?>
        <p style="text-align: center; margin-top: 1rem;">
            <a href="login.php">Aller à la page de connexion</a>
        </p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>