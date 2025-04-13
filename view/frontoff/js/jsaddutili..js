document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form"); // Sélectionne le formulaire
  
  // Configuration des champs à valider
  const fields = {
      nom: {
          element: document.querySelector("[name='nom']"),
          error: null,
          validate: value => value.trim() !== ""
      },
      prenom: {
          element: document.querySelector("[name='prenom']"),
          error: null,
          validate: value => value.trim() !== ""
      },
      email: {
          element: document.querySelector("[name='email']"),
          error: null,
          validate: value => /^\S+@\S+\.\S+$/.test(value)
      },
      mdp: {
          element: document.querySelector("[name='mdp']"),
          error: null,
          validate: value => {
              // Validation seulement si en mode création (pas en édition)
              if (!document.querySelector("[name='is_edit']") || 
                  document.querySelector("[name='is_edit']").value !== "true") {
                  return (
                      value.length >= 8 &&
                      /[a-z]/.test(value) && // Au moins une minuscule
                      /[A-Z]/.test(value) && // Au moins une majuscule
                      /[0-9]/.test(value)    // Au moins un chiffre
                  );
              }
              return true; // En mode édition, on ne valide pas le mot de passe
          }
      },
      tel: {
          element: document.querySelector("[name='tel']"),
          error: null,
          validate: value => value === "" || /^[0-9]{6,15}$/.test(value)
      },
      adresse: {
          element: document.querySelector("[name='adresse']"),
          error: null,
          validate: value => true // Optionnel
      },
      role: {
          element: document.querySelector("[name='role']"),
          error: null,
          validate: value => value !== ""
      },
      status: {
          element: document.querySelector("[name='status']"),
          error: null,
          validate: value => value !== ""
      }
  };

  // Créer et insérer les paragraphes d'erreur
  for (const key in fields) {
      if (fields[key].element) { // Vérifie si l'élément existe
          const p = document.createElement("p");
          p.className = "error-message";
          p.style.color = "#e74c3c";
          p.style.fontSize = "0.8rem";
          p.style.marginTop = "0.3rem";
          fields[key].element.parentNode.insertBefore(p, fields[key].element.nextSibling);
          fields[key].error = p;
      }
  }

  // Validation en temps réel
  for (const key in fields) {
      if (fields[key].element) {
          fields[key].element.addEventListener("input", () => {
              validateField(key);
          });
      }
  }

  // Validation à la soumission
  if (form) {
      form.addEventListener("submit", (e) => {
          let isValid = true;
          let firstInvalidField = null;

          for (const key in fields) {
              if (!validateField(key)) {
                  isValid = false;
                  if (!firstInvalidField) {
                      firstInvalidField = fields[key].element;
                  }
              }
          }

          if (!isValid) {
              e.preventDefault();
              if (firstInvalidField) {
                  firstInvalidField.focus();
              }
              
              // Animation pour attirer l'attention sur les erreurs
              document.querySelectorAll('.error-message').forEach(el => {
                  if (el.textContent) {
                      el.style.animation = 'shake 0.5s';
                      setTimeout(() => {
                          el.style.animation = '';
                      }, 500);
                  }
              });
          }
      });
  }

  function validateField(key) {
      const value = fields[key].element.value;
      if (!fields[key].validate(value)) {
          fields[key].error.textContent = getErrorMessage(key);
          fields[key].element.style.borderColor = "#e74c3c";
          return false;
      } else {
          fields[key].error.textContent = "";
          fields[key].element.style.borderColor = "#00d09c";
          return true;
      }
  }

  function getErrorMessage(field) {
      const messages = {
          nom: "Le nom est obligatoire.",
          prenom: "Le prénom est obligatoire.",
          email: "Adresse email invalide.",
          mdp: "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.",
          tel: "Numéro de téléphone invalide (6-15 chiffres).",
          role: "Veuillez sélectionner un rôle.",
          status: "Veuillez sélectionner un statut."
      };
      return messages[field] || "Champ invalide";
  }
});

// Animation CSS pour les erreurs
const style = document.createElement('style');
style.textContent = `
  @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-5px); }
      40%, 80% { transform: translateX(5px); }
  }
`;
document.head.appendChild(style);