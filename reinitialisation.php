<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

// Si déjà connecté, rediriger
if (estConnecte()) {
    rediriger('index.php');
}

$database = new Database();
$db = $database->getConnection();
$erreur = '';

// Récupérer le token depuis l'URL
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    setFlash("Lien invalide.", "error");
    rediriger('mot_de_passe_oublie.php');
}

// Vérifier le token (pas expiré)
$sql = "SELECT id, nom, email FROM utilisateurs WHERE mot_de_passe_oublie_token = ? AND mot_de_passe_oublie_expire > NOW()";
$stmt = $db->prepare($sql);
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    setFlash("Lien invalide ou expiré. Veuillez refaire une demande.", "error");
    rediriger('mot_de_passe_oublie.php');
}

// Traitement du nouveau mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveau_mdp = $_POST['mot_de_passe'];
    $confirme_mdp = $_POST['confirme_mot_de_passe'];
    
    if (empty($nouveau_mdp)) {
        $erreur = "Veuillez entrer un nouveau mot de passe.";
    } elseif (strlen($nouveau_mdp) < 8) {
        $erreur = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($nouveau_mdp !== $confirme_mdp) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else {
        // Mettre à jour le mot de passe
        $hashed_password = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
        $sql = "UPDATE utilisateurs SET mot_de_passe = ?, mot_de_passe_oublie_token = NULL, mot_de_passe_oublie_expire = NULL WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$hashed_password, $user['id']]);
        
        setFlash("✅ Votre mot de passe a été réinitialisé avec succès ! Vous pouvez maintenant vous connecter.", "success");
        rediriger('login.php');
    }
}

include 'includes/header.php';
?>

<div class="form-container" style="max-width: 500px; margin: 50px auto;">
    <h2>🔐 Nouveau mot de passe</h2>
    
    <p>Bonjour <strong><?php echo htmlspecialchars($user['nom']); ?></strong>, veuillez créer votre nouveau mot de passe.</p>
    
    <?php if($erreur): ?>
        <div class="flash-message flash-error"><?php echo $erreur; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Nouveau mot de passe (minimum 8 caractères)</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required minlength="8">
        </div>
        
        <div class="form-group">
            <label>Confirmer le mot de passe</label>
            <input type="password" name="confirme_mot_de_passe" id="confirme_mot_de_passe" required>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">✅ Réinitialiser le mot de passe</button>
    </form>
    
    <p style="text-align: center; margin-top: 1.5rem;">
        <a href="login.php">← Retour à la connexion</a>
    </p>
</div>

<script>
// Validation en temps réel de la correspondance des mots de passe
const motDePasse = document.getElementById('mot_de_passe');
const confirmation = document.getElementById('confirme_mot_de_passe');

function validerCorrespondance() {
    if (motDePasse.value !== confirmation.value) {
        confirmation.setCustomValidity('Les mots de passe ne correspondent pas');
    } else {
        confirmation.setCustomValidity('');
    }
}

motDePasse.addEventListener('change', validerCorrespondance);
motDePasse.addEventListener('keyup', validerCorrespondance);
confirmation.addEventListener('keyup', validerCorrespondance);
</script>

<?php include 'includes/footer.php'; ?>