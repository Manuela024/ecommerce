<?php 
/* ========== LOGIN.PHP (version améliorée avec traitement PHP) ========== */
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

// Si déjà connecté, rediriger
if (estConnecte()) {
    // Si déjà connecté, rediriger selon le rôle
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        rediriger('admin/dashboard.php');
    } else {
        rediriger('index.php');
    }
}

$erreur = '';
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = nettoyer($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    
    if (empty($email) || empty($mot_de_passe)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        // Vérifier les identifiants
        $sql = "SELECT id, nom, email, mot_de_passe, role, actif FROM utilisateurs WHERE email = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            if ($user['actif'] == 0) {
                $erreur = "Votre compte n'est pas encore activé. Vérifiez vos emails.";
            } else {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                setFlash("Bienvenue " . $user['nom'] . " !", "success");
                
                // Rediriger selon le rôle
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                    exit();
                } else {
                    header("Location: index.php");
                    exit();
                }
            }
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Connexion — TechShop</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
*,::before,::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#FDFBF7;color:#2C2C2C;min-height:100vh;display:grid;grid-template-columns:1fr 1fr}
:root{--gold:#D4AF37;--gold-dark:#C5A028;--gold-light:#E5C158;--cream:#FDFBF7;--cream-dark:#F9F6F0;--cream-light:#FFF8E7;--dark:#2C2C2C;--dark2:#3A3A3A;--dark3:#4A4A4A;--border:#E8D5A3;--text-muted:#888;--white:#fff}

/* LEFT PANEL */
.auth-left{
  position:relative;overflow:hidden;
  background:#2C2C2C;
  display:flex;flex-direction:column;justify-content:flex-end;padding:48px;
  min-height:100vh;
}
.auth-left-bg{position:absolute;inset:0;background-image:url('https://images.unsplash.com/photo-1593640408182-31c228a3b4c7?w=900&h=1200&fit=crop&q=80');background-size:cover;background-position:center;filter:brightness(.35)}
.auth-left-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(44,44,44,.95) 0%,rgba(44,44,44,.3) 60%,transparent 100%)}
.auth-left-content{position:relative;z-index:1}
.auth-logo{font-family:'Bebas Neue',sans-serif;font-size:2rem;letter-spacing:2px;color:var(--white);margin-bottom:32px;display:block}
.auth-logo span{color:var(--gold)}
.auth-tagline{font-family:'Bebas Neue',sans-serif;font-size:clamp(36px,5vw,56px);letter-spacing:1px;color:var(--white);line-height:1.1;margin-bottom:16px}
.auth-tagline em{color:var(--gold);font-style:normal}
.auth-desc{font-size:.88rem;color:rgba(255,255,255,.55);line-height:1.7;max-width:320px;margin-bottom:36px}
.auth-features{display:flex;flex-direction:column;gap:12px}
.auth-feat{display:flex;align-items:center;gap:12px;font-size:.82rem;color:rgba(255,255,255,.6)}
.auth-feat-dot{width:6px;height:6px;border-radius:50%;background:var(--gold);flex-shrink:0}

/* RIGHT PANEL */
.auth-right{
  display:flex;flex-direction:column;justify-content:center;align-items:center;
  padding:60px 48px;background:var(--cream);min-height:100vh;
}
.auth-form-wrap{width:100%;max-width:400px}
.auth-form-title{font-family:'Bebas Neue',sans-serif;font-size:2.2rem;letter-spacing:1px;color:var(--dark);margin-bottom:6px}
.auth-form-sub{font-size:.85rem;color:var(--text-muted);margin-bottom:36px}
.auth-form-sub a{color:var(--gold)}
.auth-form-sub a:hover{text-decoration:underline}

.form-group{margin-bottom:20px}
.form-label{display:block;font-size:.78rem;font-weight:600;letter-spacing:.5px;color:rgba(44,44,44,.6);margin-bottom:8px;text-transform:uppercase}
.form-input{
  width:100%;background:var(--cream-light);border:1px solid var(--border);
  border-radius:10px;padding:14px 16px;
  color:var(--dark);font-size:.9rem;font-family:'Inter',sans-serif;
  transition:border-color .2s;outline:none;
}
.form-input:focus{border-color:var(--gold)}
.form-input::placeholder{color:var(--text-muted)}
.form-input-icon{position:relative}
.form-input-icon .form-input{padding-right:44px}
.input-icon{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:1.1rem;cursor:pointer;user-select:none}

.btn-auth{
  width:100%;background:var(--gold);color:var(--dark);border:none;
  padding:15px;border-radius:10px;font-size:.92rem;font-weight:600;
  cursor:pointer;font-family:'Inter',sans-serif;
  transition:all .25s;
  box-shadow:0 0 24px rgba(212,175,55,.25);
  margin-top:8px;
}
.btn-auth:hover{background:var(--gold-light);transform:translateY(-1px);box-shadow:0 0 36px rgba(212,175,55,.4)}

.form-divider{display:flex;align-items:center;gap:12px;margin:24px 0}
.form-divider span{font-size:.75rem;color:var(--text-muted);white-space:nowrap}
.form-divider::before,.form-divider::after{content:'';flex:1;height:1px;background:var(--border)}

.forgot-link{display:block;text-align:right;font-size:.78rem;color:var(--text-muted);margin-top:-10px;margin-bottom:20px;transition:color .2s}
.forgot-link:hover{color:var(--gold)}
.forgot-link a{color:inherit;text-decoration:none}

.alert-box{
  padding:14px 16px;border-radius:10px;font-size:.83rem;margin-bottom:20px;
  display:flex;align-items:flex-start;gap:10px;line-height:1.5;
}
.alert-error{background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.3);color:#C5A028}
.alert-success{background:rgba(40,180,100,.1);border:1px solid rgba(40,180,100,.3);color:#5fdd9e}

@media(max-width:768px){body{grid-template-columns:1fr}.auth-left{display:none}.auth-right{padding:40px 24px}}
</style>
</head>
<body>

<div class="auth-left">
  <div class="auth-left-bg"></div>
  <div class="auth-left-overlay"></div>
  <div class="auth-left-content">
    <a href="index.php" class="auth-logo">Tech<span>Shop</span></a>
    <h2 class="auth-tagline">Le matériel<br>qu'il vous<br><em>faut vraiment</em></h2>
    <p class="auth-desc">Composants premium, ordinateurs gaming et périphériques pro livrés chez vous.</p>
    <div class="auth-features">
      <div class="auth-feat"><span class="auth-feat-dot"></span>Garantie 2 ans sur tous les produits</div>
      <div class="auth-feat"><span class="auth-feat-dot"></span>Livraison rapide 24-48h</div>
      <div class="auth-feat"><span class="auth-feat-dot"></span>Paiement 100% sécurisé</div>
      <div class="auth-feat"><span class="auth-feat-dot"></span>Support technique 7j/7</div>
    </div>
  </div>
</div>

<div class="auth-right">
  <div class="auth-form-wrap">

    <!-- ===== CONNEXION ===== -->
    <h1 class="auth-form-title">Connexion</h1>
    <p class="auth-form-sub">Pas encore de compte ? <a href="register.php">Créer un compte →</a></p>

    <?php if(!empty($erreur)): ?>
      <div class="alert-box alert-error">⚠️ <?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label class="form-label">Adresse email</label>
        <input class="form-input" type="email" name="email" placeholder="vous@exemple.com" required
               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"/>
      </div>
      <div class="form-group">
        <label class="form-label">Mot de passe</label>
        <div class="form-input-icon">
          <input class="form-input" type="password" name="mot_de_passe" id="pwd" placeholder="••••••••" required/>
          <span class="input-icon" onclick="togglePwd('pwd',this)">👁</span>
        </div>
      </div>
      <div class="forgot-link"><a href="mot_de_passe_oublie.php">Mot de passe oublié ?</a></div>
      <button class="btn-auth" type="submit">Se connecter →</button>
    </form>

  </div>
</div>

<script>
function togglePwd(id,icon){
  const i=document.getElementById(id);
  i.type=i.type==='password'?'text':'password';
  icon.textContent=i.type==='password'?'👁':'🙈';
}
</script>
</body>
</html>