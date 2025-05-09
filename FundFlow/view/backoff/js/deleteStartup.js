// delete.js

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", () => {
            const id = button.dataset.id;

            if (confirm("Êtes-vous sûr de vouloir supprimer cette startup ?")) {
                // Redirection vers le script PHP avec l'ID en GET
                window.location.href = `delete.php?id=${encodeURIComponent(id)}`;
            }
        });
    });
});
