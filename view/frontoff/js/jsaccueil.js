// transition.js

// Appliquer une transition à tous les liens internes
document.addEventListener("DOMContentLoaded", () => {
    const links = document.querySelectorAll('a[href]');
  
    links.forEach(link => {
      const href = link.getAttribute('href');
      
      // Vérifie si le lien n'est pas externe ni une ancre
      if (href && !href.startsWith('http') && !href.startsWith('#')) {
        link.addEventListener('click', function (e) {
          e.preventDefault();
          document.body.classList.add('fade-out');
  
          setTimeout(() => {
            window.location.href = href;
          }, 500); // durée en millisecondes
        });
      }
    });
  
    // Ajouter la classe pour le fade-in
    document.body.classList.add('loaded');
  });
  