<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

// Si déjà connecté, rediriger
if (estConnecte()) {
    rediriger('index.php');
}

$erreur = '';
$succes = '';
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = nettoyer($_POST['nom']);
    $email = nettoyer($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirme_mdp = $_POST['confirme_mdp'];
    
    // Validation
    if (empty($nom) || empty($email) || empty($mot_de_passe)) {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Email invalide.";
    } elseif (strlen($mot_de_passe) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif (!preg_match('/[!@#$%^&*]/', $mot_de_passe)) {
        $erreur = "Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*)";
    } elseif ($mot_de_passe !== $confirme_mdp) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $existant = $stmt->fetch();
        
        if ($existant) {
            $erreur = "Cet email est déjà utilisé.";
        } else {
            // Créer le compte - actif = 1 directement (SANS ENVOI D'EMAIL)
            $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, role, actif, date_inscription) 
                    VALUES (?, ?, ?, 'user', 1, NOW())";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute([$nom, $email, $hashed_password])) {
                $succes = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            } else {
                $erreur = "Erreur lors de l'inscription.";
            }
        }
    }
}

include 'includes/header.php';
?>

<style>
:root{
  --gold:#D4AF37;
  --gold-light:#E5C158;
  --gold-dark:#C5A028;
  --cream:#FDFBF7;
  --cream-dark:#F9F6F0;
  --cream-light:#FFF8E7;
  --dark:#2C2C2C;
  --border:#E8D5A3;
  --text-muted:#888;
  --radius-sm:8px;
}

.form-container{
  max-width:500px;margin:100px auto;padding:30px;
  background:var(--cream-light);border-radius:12px;
  border:1px solid var(--border);
}
.form-container h2{
  font-family:'Bebas Neue',sans-serif;font-size:1.8rem;
  color:var(--dark);margin-bottom:20px;text-align:center;
}
.form-group{margin-bottom:20px;}
.form-group label{
  display:block;font-size:.8rem;font-weight:600;
  color:var(--dark);margin-bottom:5px;
}
.form-group input{
  width:100%;padding:12px;
  border:1px solid var(--border);border-radius:var(--radius-sm);
  background:var(--cream);color:var(--dark);
  font-size:.9rem;transition:all .2s;
}
.form-group input:focus{
  outline:none;border-color:var(--gold);
}
.form-group small{
  display:block;font-size:.7rem;color:var(--text-muted);margin-top:5px;
}
.btn-primary{
  width:100%;background:var(--gold);color:var(--dark);
  padding:14px;border:none;border-radius:var(--radius-sm);
  font-weight:600;font-size:1rem;cursor:pointer;
  transition:all .2s;
}
.btn-primary:hover{
  background:var(--gold-light);transform:translateY(-2px);
}
.flash-message{
  padding:12px;border-radius:var(--radius-sm);margin-bottom:20px;
  display:flex;align-items:center;gap:10px;
}
.flash-error{
  background:rgba(212,175,55,.12);
  border:1px solid rgba(212,175,55,.3);
  color:var(--gold-dark);
}
.flash-success{
  background:rgba(40,180,100,.12);
  border:1px solid rgba(40,180,100,.3);
  color:#2e7d32;
}
.password-strength{font-size:.75rem;margin-top:5px;}
.password-match{font-size:.75rem;margin-top:5px;}
</style>

<div class="form-container">
    <h2>📝 Créer un compte</h2>
    
    <?php if($erreur): ?>
        <div class="flash-message flash-error">⚠️ <?php echo $erreur; ?></div>
    <?php endif; ?>
    
    <?php if($succes): ?>
        <div class="flash-message flash-success">
            ✅ <?php echo $succes; ?>
        </div>
        <p style="text-align: center; margin-top: 1rem;">
            <a href="login.php" style="color:var(--gold);">Aller à la page de connexion →</a>
        </p>
    <?php else: ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Nom complet *</label>
                <input type="text" name="nom" required value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Mot de passe *</label>
                <input type="password" name="mot_de_passe" id="password" required>
                <small>Minimum 6 caractères dont 1 caractère spécial (!@#$%^&*)</small>
                <div id="password-strength" class="password-strength"></div>
            </div>
            
            <div class="form-group">
                <label>Confirmer le mot de passe *</label>
                <input type="password" name="confirme_mdp" id="confirm_password" required>
                <small id="password-match" class="password-match"></small>
            </div>
            
            <button type="submit" class="btn-primary">S'inscrire</button>
        </form>
        
        <p style="text-align: center; margin-top: 1rem; color:var(--text-muted);">
            Déjà un compte ? <a href="login.php" style="color:var(--gold);">Se connecter</a>
        </p>
    <?php endif; ?>
</div>

<script>
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirm_password');
const strengthDiv = document.getElementById('password-strength');
const matchSpan = document.getElementById('password-match');

password.addEventListener('input', function() {
    const val = this.value;
    let strength = 0;
    
    if (val.length >= 6) strength++;
    if (val.length >= 8) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[!@#$%^&*]/.test(val)) strength++;
    
    let strengthText = '';
    let strengthColor = '';
    
    if (val.length === 0) {
        strengthText = '';
    } else if (strength <= 2) {
        strengthText = '🔴 Faible';
        strengthColor = '#e74c3c';
    } else if (strength <= 4) {
        strengthText = '🟠 Moyen';
        strengthColor = '#f39c12';
    } else {
        strengthText = '🟢 Fort';
        strengthColor = '#27ae60';
    }
    
    strengthDiv.textContent = strengthText;
    strengthDiv.style.color = strengthColor;
    
    checkPasswordMatch();
});

function checkPasswordMatch() {
    if (confirmPassword.value.length > 0) {
        if (password.value === confirmPassword.value) {
            matchSpan.textContent = '✓ Les mots de passe correspondent';
            matchSpan.style.color = '#27ae60';
        } else {
            matchSpan.textContent = '✗ Les mots de passe ne correspondent pas';
            matchSpan.style.color = '#e74c3c';
        }
    } else {
        matchSpan.textContent = '';
    }
}

confirmPassword.addEventListener('input', checkPasswordMatch);
</script>

<?php include 'includes/footer.php'; ?>