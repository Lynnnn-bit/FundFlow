document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
  
    form.addEventListener("submit", function (e) {
      e.preventDefault();
  
      const email = document.getElementById("email").value.trim();
      const password = document.getElementById("password").value.trim();
  
      const passwordRegex = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W_]).{6,}$/;
  
      if (!email || !password) {
        alert("Veuillez remplir tous les champs.");
        return;
      }
  
      if (!passwordRegex.test(password)) {
        alert("Le mot de passe doit contenir au moins 6 caractères, dont une lettre, un chiffre et un symbole.");
        return;
      }
  
      // Exemple de simulation
      if (email === "test@example.com" && password === "Ex@mpl3") {
        alert("Connexion réussie !");
        window.location.href = "dashboard.html";
      } else {
        alert("Email ou mot de passe incorrect.");
      }
    });
  });