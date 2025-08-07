// Gestionnaire de transitions entre les pages
document.addEventListener('DOMContentLoaded', () => {
    // Ajouter le loader à la page
    const loader = document.createElement('div');
    loader.className = 'loader hidden';
    document.body.appendChild(loader);

    // Ajouter la classe de transition à la page
    document.body.classList.add('page-transition');

    // Gestionnaire pour les liens de navigation
    document.querySelectorAll('a').forEach(link => {
        // Ignorer les liens externes et les ancres
        if (link.href && link.href.startsWith(window.location.origin) && !link.href.includes('#')) {
            link.addEventListener('click', e => {
                e.preventDefault();
                const targetHref = link.href;

                // Afficher le loader
                loader.classList.remove('hidden');

                // Ajouter l'animation de sortie
                document.body.style.opacity = '0';
                document.body.style.transform = 'translateY(-20px)';

                // Attendre la fin de l'animation avant de naviguer
                setTimeout(() => {
                    window.location.href = targetHref;
                }, 500);
            });
        }
    });

    // Cacher le loader quand la page est chargée
    window.addEventListener('load', () => {
        loader.classList.add('hidden');
    });
});
