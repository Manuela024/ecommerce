<?php
echo "Le dossier courant est : " . __DIR__ . "<br>";
echo "Le chemin du fichier est : " . __FILE__ . "<br>";

if(file_exists('includes/header.php')) {
    echo "✅ includes/header.php existe !<br>";
} else {
    echo "❌ includes/header.php n'existe PAS !<br>";
    echo "Contenu du dossier includes : <br>";
    $files = scandir('includes/');
    print_r($files);
}