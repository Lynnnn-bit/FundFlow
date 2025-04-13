document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const inputs = form.querySelectorAll(".input");
    const checkbox = document.getElementById("terms");
  
    form.addEventListener("submit", (e) => {
      e.preventDefault(); // Stop form submission until validation passes
  
      const prenom = inputs[0].value.trim();
      const nom = inputs[1].value.trim();
      const email = inputs[2].value.trim();
      const password = inputs[3].value;
      const confirm = inputs[4].value;
  
      if (!prenom || !nom) {
        alert("Veuillez remplir votre prénom et nom.");
        return;
      }
  
      if (!validateEmail(email)) {
        alert("Veuillez entrer une adresse email valide.");
        return;
      }
  
      if (!validatePassword(password)) {
        alert("Le mot de passe ne respecte pas les critères.");
        return;
      }
  
      if (password !== confirm) {
        alert("Les mots de passe ne correspondent pas.");
        return;
      }
  
      if (!checkbox.checked) {
        alert("Vous devez accepter les conditions d'utilisation.");
        return;
      }
  
      // If all validations pass:
      alert("Formulaire envoyé avec succès !");
      form.submit(); // Remove this line if you're handling submission manually
    });
  });
  
  // Validate email with regex
  function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  }
  
  // Validate password with rules
  function validatePassword(password) {
    const lengthValid = password.length >= 8 && password.length <= 16;
    const lower = /[a-z]/.test(password);
    const upper = /[A-Z]/.test(password);
    const digit = /[0-9]/.test(password);
    const special = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    return lengthValid && lower && upper && digit && special;
  }