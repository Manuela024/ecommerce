<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

// Si déjà connecté, rediriger
if (estConnecte()) {
    rediriger('index.php');
}

$database = new Database();
$db = $database->getConnection();
$message = '';
$erreur = '';

// Vérifier si le formulaire a été soumis ET que l'email existe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier que le champ email existe dans la requête POST
    if (isset($_POST['email'])) {
        $email = nettoyer($_POST['email']);
        
        if (empty($email)) {
            $erreur = "Veuillez entrer votre email.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = "Email invalide.";
        } else {
            // Vérifier si l'email existe
            $sql = "SELECT id, nom FROM utilisateurs WHERE email = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Supprimer les anciens tokens expirés
                $sql = "UPDATE utilisateurs SET mot_de_passe_oublie_token = NULL, mot_de_passe_oublie_expire = NULL WHERE mot_de_passe_oublie_expire < NOW()";
                $db->exec($sql);
                
                // Générer un token sécurisé
                $token = bin2hex(random_bytes(32));
                $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Sauvegarder le token
                $sql = "UPDATE utilisateurs SET mot_de_passe_oublie_token = ?, mot_de_passe_oublie_expire = ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$token, $expire, $user['id']]);
                
                // Lien sécurisé
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/ecommerce/reinitialisation.php?token=" . $token;
                
                // Stocker le lien en session
                $_SESSION['temp_reset_link'] = $reset_link;
                
                // Message de succès
                $message = "✅ Un lien de réinitialisation a été généré.<br>";
                $message .= "<small>⚠️ Lien sécurisé - Valable 1 heure.</small>";
                
                // En développement, afficher le lien (à désactiver en production)
                if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
                    $message .= "<br><br><div style='background: #F9F6F0; padding: 15px; border-radius: 8px; margin-top: 10px; border: 1px solid #E8D5A3;'>";
                    $message .= "<strong>🔧 Mode développement (lien visible uniquement pour les tests) :</strong><br>";
                    $message .= "<a href='$reset_link' target='_blank' style='color: #D4AF37; font-weight: bold;'>Cliquez ici pour réinitialiser votre mot de passe</a>";
                    $message .= "<br><small style='color: #888;'>En production, ce lien serait envoyé par email.</small>";
                    $message .= "</div>";
                }
            } else {
                $message = "📧 Si votre email est enregistré, vous recevrez un lien de réinitialisation.";
            }
        }
    } else {
        $erreur = "Veuillez remplir le formulaire correctement.";
    }
}

include 'includes/header.php';
?>

<style>
:root{
    --gold: #D4AF37;
    --gold-light: #E5C158;
    --gold-dark: #C5A028;
    --cream: #FDFBF7;
    --cream-dark: #F9F6F0;
    --cream-light: #FFF8E7;
    --dark: #2C2C2C;
    --border: #E8D5A3;
    --text-muted: #888;
    --radius-sm: 8px;
}

.form-container{
    max-width: 500px;
    margin: 100px auto;
    padding: 35px;
    background: var(--cream-light);
    border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}
.form-container h2{
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2rem;
    color: var(--dark);
    text-align: center;
    margin-bottom: 10px;
}
.form-container > p{
    text-align: center;
    color: var(--text-muted);
    margin-bottom: 25px;
    font-size: .9rem;
}
.form-group{
    margin-bottom: 20px;
}
.form-group label{
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--dark);
    font-size: .85rem;
}
.form-group input{
    width: 100%;
    padding: 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--cream);
    color: var(--dark);
    font-size: .9rem;
    transition: all .2s;
}
.form-group input:focus{
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,175,55,0.1);
}
.btn-primary{
    width: 100%;
    padding: 12px;
    background: var(--gold);
    color: var(--dark);
    border: none;
    border-radius: var(--radius-sm);
    font-size: .9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.btn-primary:hover{
    background: var(--gold-light);
    transform: translateY(-2px);
}
.flash-message{
    padding: 12px;
    border-radius: var(--radius-sm);
    margin-bottom: 20px;
    font-size: .85rem;
}
.flash-error{
    background: rgba(212,175,55,.12);
    border: 1px solid rgba(212,175,55,.3);
    color: var(--gold-dark);
}
.flash-success{
    background: rgba(40,180,100,.12);
    border: 1px solid rgba(40,180,100,.3);
    color: #2e7d32;
}
.back-link{
    text-align: center;
    margin-top: 1.5rem;
}
.back-link a{
    color: var(--gold);
    text-decoration: none;
    transition: color .2s;
}
.back-link a:hover{
    color: var(--gold-dark);
    text-decoration: underline;
}
</style>

<div class="form-container">
    <h2>🔑 Mot de passe oublié</h2>
    <p>Entrez votre email pour réinitialiser votre mot de passe</p>
    
    <?php if(!empty($erreur)): ?>
        <div class="flash-message flash-error">
            ⚠️ <?php echo $erreur; ?>
        </div>
    <?php endif; ?>
    
    <?php if(!empty($message)): ?>
        <div class="flash-message flash-success">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <?php if(empty($message) && empty($_POST)): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       required 
                       placeholder="exemple@email.com">
            </div>
            <button type="submit" class="btn-primary">
                📧 Envoyer le lien de réinitialisation
            </button>
        </form>
    <?php endif; ?>
    
    <div class="back-link">
        <a href="login.php">← Retour à la connexion</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>