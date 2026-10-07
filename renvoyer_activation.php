<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';
require_once 'includes/mail.php';

$database = new Database();
$db = $database->getConnection();

$email = isset($_GET['email']) ? nettoyer($_GET['email']) : '';
$message = '';
$type = '';

if ($email) {
    $stmt = $db->prepare("SELECT id, nom, token_activation FROM utilisateurs WHERE email = ? AND actif = 0");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $lien_activation = SITE_URL . "activation.php?token=" . $user['token_activation'];
        $sujet = "Activation de votre compte TechShop";
        $body = "
            <html>
            <head><title>Activation de compte</title></head>
            <body>
                <h1>Activation de votre compte TechShop</h1>
                <p>Bonjour {$user['nom']},</p>
                <p>Pour activer votre compte, cliquez sur le lien ci-dessous :</p>
                <p><a href='$lien_activation'>Activer mon compte</a></p>
                <p>Ce lien expirera dans 24 heures.</p>
            </body>
            </html>
        ";
        
        envoyerEmail($email, $sujet, $body);
        $message = "Un nouvel email d'activation a été envoyé.";
        $type = "success";
    } else {
        $message = "Aucun compte inactif trouvé avec cet email.";
        $type = "error";
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h2>📧 Renvoyer l'activation</h2>
    
    <?php if($message): ?>
        <div class="flash-message flash-<?php echo $type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <p style="text-align: center; margin-top: 1rem;">
        <a href="login.php">← Retour à la connexion</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>