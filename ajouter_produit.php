<?php
require_once 'includes/db.php';
require_once 'includes/fonctions.php';

// Vérifier si l'utilisateur est admin
if (!estConnecte() || !estAdmin()) {
    header('Location: index.php');
    exit();
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ajouter un produit - TechShop Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: #FDFBF7;
    color: #2C2C2C;
    min-height: 100vh;
}

:root {
    --gold: #D4AF37;
    --gold-light: #E5C158;
    --gold-dark: #C5A028;
    --cream: #FDFBF7;
    --cream-dark: #F9F6F0;
    --cream-light: #FFF8E7;
    --dark: #2C2C2C;
    --border: #E8D5A3;
    --text-muted: #888;
    --green: #22c55e;
    --red: #ef4444;
    --radius: 12px;
    --radius-sm: 8px;
}

.container {
    max-width: 800px;
    margin: 80px auto;
    padding: 0 20px;
}

.add-card {
    background: var(--cream-light);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.add-header {
    background: linear-gradient(135deg, var(--cream-dark), var(--cream-light));
    padding: 25px 30px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 15px;
}

.add-header i {
    font-size: 2.5rem;
    color: var(--gold);
}

.add-header h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.8rem;
    color: var(--dark);
    margin-bottom: 5px;
}

.add-header p {
    color: var(--text-muted);
    font-size: 0.8rem;
}

.add-body {
    padding: 30px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-group label i {
    margin-right: 5px;
    color: var(--gold);
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    background: var(--cream);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    color: var(--dark);
    font-size: 0.9rem;
    transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.image-preview {
    margin-top: 10px;
    display: none;
}

.image-preview img {
    max-width: 150px;
    border-radius: 8px;
    border: 1px solid var(--border);
}

.form-buttons {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.btn {
    padding: 12px 28px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
}

.btn-primary {
    background: var(--gold);
    color: var(--dark);
}

.btn-primary:hover {
    background: var(--gold-light);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
}

.btn-secondary {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text-muted);
}

.btn-secondary:hover {
    border-color: var(--gold);
    color: var(--gold);
    transform: translateY(-2px);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.add-card {
    animation: fadeIn 0.3s ease;
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .add-header {
        flex-direction: column;
        text-align: center;
    }
    
    .add-body {
        padding: 20px;
    }
    
    .form-buttons {
        flex-direction: column;
    }
    
    .btn {
        justify-content: center;
    }
}
</style>
</head>
<body>

<div class="container">
    <div class="add-card">
        <div class="add-header">
            <i class='bx bx-plus-circle'></i>
            <div>
                <h1>Ajouter un produit</h1>
                <p>Ajoutez un nouveau produit à votre catalogue</p>
            </div>
        </div>
        
        <div class="add-body">
            <form method="POST" action="traiter_produit.php" enctype="multipart/form-data" id="addForm">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class='bx bxs-package'></i> Nom du produit *</label>
                        <input type="text" name="nom" required placeholder="Ex: Ordinateur Gaming Xtreme">
                    </div>
                    <div class="form-group">
                        <label><i class='bx bxs-building'></i> Marque *</label>
                        <input type="text" name="marque" required placeholder="Ex: ASUS, Dell, HP">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class='bx bxs-purchase-tag'></i> Prix (FCFA) *</label>
                        <input type="number" step="0.01" name="prix" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label><i class='bx bxs-box'></i> Stock *</label>
                        <input type="number" name="stock" required placeholder="Quantité en stock">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class='bx bxs-category'></i> Catégorie</label>
                        <select name="type">
                            <option value="ordinateur">💻 Ordinateur</option>
                            <option value="peripherique">🖱️ Périphérique</option>
                            <option value="composant">⚙️ Composant</option>
                            <option value="reseau">🌐 Réseau</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class='bx bxs-image'></i> Image</label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" id="imageInput">
                        <small style="color: var(--text-muted); display: block; margin-top: 5px;">
                            Formats acceptés : JPG, PNG, GIF, WEBP
                        </small>
                        <div id="imagePreview" class="image-preview"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class='bx bxs-detail'></i> Description</label>
                    <textarea name="description" placeholder="Description détaillée du produit..."></textarea>
                </div>
                
                <div class="form-buttons">
                    <a href="admin/produits.php" class="btn btn-secondary">
                        <i class='bx bx-x'></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-save'></i> Ajouter le produit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Aperçu de l'image avant upload
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');

imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            imagePreview.innerHTML = `
                <img src="${event.target.result}" alt="Aperçu">
                <small style="display: block; margin-top: 5px; color: var(--green);">
                    ✓ Image sélectionnée
                </small>
            `;
            imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        imagePreview.style.display = 'none';
        imagePreview.innerHTML = '';
    }
});
</script>

</body>
</html>