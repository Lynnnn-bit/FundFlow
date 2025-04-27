document.addEventListener('DOMContentLoaded', function() {
    // Filtrage des projets
    const searchInput = document.querySelector('.search-box input');
    const categoryFilter = document.querySelector('.category-filter');
    const projectCards = document.querySelectorAll('.project-card');

    function filterProjects() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value;

        projectCards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const category = card.querySelector('.category-badge').textContent.toLowerCase();
            const categoryId = card.querySelector('.category-badge').dataset.categoryId;
            
            const matchesSearch = title.includes(searchTerm);
            const matchesCategory = selectedCategory === '' || categoryId === selectedCategory;
            
            if (matchesSearch && matchesCategory) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterProjects);
    categoryFilter.addEventListener('change', filterProjects);
});