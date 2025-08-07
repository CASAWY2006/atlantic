document.addEventListener("DOMContentLoaded", function() {

    // --- ANIMATION DES CHIFFRES ---
    const counters = document.querySelectorAll('.stat-number');
    const speed = 200; // Vitesse de l'animation

    const animateCounter = (counter) => {
        const target = +counter.getAttribute('data-target');
        const count = +counter.innerText;

        const increment = target / speed;

        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(() => animateCounter(counter), 1);
        } else {
            counter.innerText = target;
        }
    };

    // Utiliser Intersection Observer pour déclencher l'animation au défilement
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target); // Pour ne l'animer qu'une seule fois
            }
        });
    }, {
        threshold: 0.5 // Déclenche quand 50% de l'élément est visible
    });

    counters.forEach(counter => {
        observer.observe(counter);
    });


    // --- BOUTON RETOUR EN HAUT ---
    const scrollToTopButton = document.querySelector('.scroll-to-top');

    scrollToTopButton.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth' // Pour un défilement fluide
        });
    });

});