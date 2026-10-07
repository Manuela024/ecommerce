// js/animations.js

// Attend que la page soit complètement chargée
document.addEventListener('DOMContentLoaded', function() {
    
    // Animation 1 : Hero section (le grand bandeau)
    gsap.to('.hero', {
        duration: 1,
        opacity: 1,
        y: 0,
        ease: 'power2.out',
        delay: 0.3
    });
    
    // Animation 2 : Cartes des catégories (apparition en cascade)
    gsap.to('.cat-card', {
        duration: 0.6,
        opacity: 1,
        y: 0,
        stagger: 0.1, // Chaque carte apparaît avec 0.1s de décalage
        ease: 'back.out(1.2)',
        scrollTrigger: {
            trigger: '.categories-home',
            start: 'top 80%'
        }
    });
    
    // Animation 3 : Cartes des produits (apparition au scroll)
    gsap.to('.produit-card', {
        duration: 0.8,
        opacity: 1,
        y: 0,
        stagger: 0.15,
        ease: 'power2.out',
        scrollTrigger: {
            trigger: '.catalogue-grid',
            start: 'top 85%',
            toggleActions: 'play none none reverse'
        }
    });
    
    // Animation 4 : Effet hover sur les cartes produits
    const cards = document.querySelectorAll('.produit-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card, {
                duration: 0.3,
                scale: 1.05,
                boxShadow: '0 10px 30px rgba(0,0,0,0.2)',
                ease: 'power2.out'
            });
        });
        
        card.addEventListener('mouseleave', () => {
            gsap.to(card, {
                duration: 0.3,
                scale: 1,
                boxShadow: '0 2px 10px rgba(0,0,0,0.1)',
                ease: 'power2.out'
            });
        });
    });
    
    // Animation 5 : Menu (effet au survol)
    const menuLinks = document.querySelectorAll('nav ul li a');
    menuLinks.forEach(link => {
        link.addEventListener('mouseenter', () => {
            gsap.to(link, {
                duration: 0.2,
                scale: 1.1,
                backgroundColor: '#e94560',
                ease: 'power2.out'
            });
        });
        
        link.addEventListener('mouseleave', () => {
            gsap.to(link, {
                duration: 0.2,
                scale: 1,
                backgroundColor: 'transparent',
                ease: 'power2.out'
            });
        });
    });
    
    // Animation 6 : Boutons (effet pulse au chargement)
    const btns = document.querySelectorAll('.btn');
    btns.forEach((btn, index) => {
        gsap.fromTo(btn, 
            { scale: 1 },
            { 
                duration: 0.8,
                scale: 1.05,
                repeat: -1,
                yoyo: true,
                delay: index * 0.2,
                ease: 'power1.inOut'
            }
        );
    });
});