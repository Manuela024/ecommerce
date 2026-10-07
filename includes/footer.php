    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>TechShop</h3>
                <p>Votre boutique en ligne de matériel informatique depuis 2026.</p><br>
            </div>
            <div class="footer-section">
                <h3>Liens utiles</h3>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>catalogue.php">Catalogue</a></li>
                    <li><a href="#">Conditions générales</a></li>
                    <li><a href="#">Politique de confidentialité</a></li><br>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>📧 contact@techshop.com</p>
                <p>📞 +225 07070707</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 TechShop - Tous droits réservés</p>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>js/main.js"></script>

    <script>
// Animation du compteur panier
document.addEventListener('DOMContentLoaded', function() {
    const panierCount = document.querySelector('.panier-count');
    if (panierCount) {
        panierCount.classList.add('updated');
        setTimeout(() => {
            panierCount.classList.remove('updated');
        }, 300);
    }
    
    // Auto fermeture des notifications après 3 secondes
    const flashMessages = document.querySelectorAll('.flash-notification');
    flashMessages.forEach(msg => {
        setTimeout(() => {
            msg.style.display = 'none';
        }, 3000);
    });
});
</script>
</body>
</html>