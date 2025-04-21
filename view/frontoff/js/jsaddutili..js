document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form"); // Sélectionne le formulaire
    
    // Configuration des champs à valider
    const fields = {
        nom: {
            element: document.querySelector("[name='nom']"),
            error: null,
            validate: value => {
                const trimmed = value.trim();
                return trimmed !== "" && /^[a-zA-ZÀ-ÿ\s\-']+$/.test(trimmed);
            },
            required: true
        },
        prenom: {
            element: document.querySelector("[name='prenom']"),
            error: null,
            validate: value => {
                const trimmed = value.trim();
                return trimmed !== "" && /^[a-zA-ZÀ-ÿ\s\-']+$/.test(trimmed);
            },
            required: true
        },
        email: {
            element: document.querySelector("[name='email']"),
            error: null,
            validate: value => {
                const trimmed = value.trim();
                return trimmed !== "" && /^\S+@\S+\.\S+$/.test(trimmed) && 
                       trimmed.includes('@') && trimmed.includes('.') &&
                       trimmed.indexOf('@') < trimmed.lastIndexOf('.');
            },
            required: true
        },
        mdp: {
            element: document.querySelector("[name='mdp']"),
            error: null,
            validate: value => {
                // Validation seulement si en mode création (pas en édition)
                if (!document.querySelector("[name='is_edit']") || 
                    document.querySelector("[name='is_edit']").value !== "true") {
                    return 
                        value.length >= 8 &&
                        /[a-z]/.test(value) && // Au moins une minuscule
                        /[A-Z]/.test(value) && // Au moins une majuscule
                        /[0-9]/.test(value)    // Au moins un chiffre
                }
                return true; // En mode édition, on ne valide pas le mot de passe
            },
            required: !(document.querySelector("[name='is_edit']") && 
                      document.querySelector("[name='is_edit']").value === "true")
        },
        tel: {
            element: document.querySelector("[name='tel']"),
            error: null,
            validate: value => {
                const trimmed = value.trim();
                // Si le champ est vide et non obligatoire, c'est valide
                if (!this.required && trimmed === "") return true;
                return /^[0-9]{8}$/.test(trimmed);
            },
            required: true // Modifiez à false si le téléphone n'est pas obligatoire
        },
        adresse: {
            element: document.querySelector("[name='adresse']"),
            error: null,
            validate: value => {
                const trimmed = value.trim();
                return trimmed !== ""; // Adresse obligatoire
            },
            required: true
        },
        role: {
            element: document.querySelector("[name='role']"),
            error: null,
            validate: value => value !== "",
            required: true
        },
        status: {
            element: document.querySelector("[name='status']"),
            error: null,
            validate: value => value !== "",
            required: true
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
            
            // Ajout d'un événement blur pour valider quand on quitte le champ
            fields[key].element.addEventListener("blur", () => {
                validateField(key);
            });
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
        const field = fields[key];
        const value = field.element.value;
        const isEmpty = value.trim() === "";
        
        // Si le champ est obligatoire et vide
        if (field.required && isEmpty) {
            field.error.textContent = getErrorMessage(key, true);
            field.element.style.borderColor = "#e74c3c";
            return false;
        }
        // Si le champ n'est pas vide mais invalide
        else if (!isEmpty && !field.validate(value)) {
            field.error.textContent = getErrorMessage(key, false);
            field.element.style.borderColor = "#e74c3c";
            return false;
        }
        // Si le champ est optionnel et vide
        else if (!field.required && isEmpty) {
            field.error.textContent = "";
            field.element.style.borderColor = "#00d09c";
            return true;
        }
        // Champ valide
        else {
            field.error.textContent = "";
            field.element.style.borderColor = "#00d09c";
            return true;
        }
    }
  
    function getErrorMessage(field, isEmpty) {
        const messages = {
            nom: {
                empty: "Le nom est obligatoire.",
                invalid: "Le nom ne doit contenir que des lettres."
            },
            prenom: {
                empty: "Le prénom est obligatoire.",
                invalid: "Le prénom ne doit contenir que des lettres."
            },
            email: {
                empty: "L'email est obligatoire.",
                invalid: "Adresse email invalide (doit contenir @ et .)."
            },
            mdp: {
                empty: "Le mot de passe est obligatoire.",
                invalid: "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre."
            },
            tel: {
                empty: "Le téléphone est obligatoire.",
                invalid: "Numéro de téléphone invalide (exactement 8 chiffres)."
            },
            adresse: {
                empty: "L'adresse est obligatoire.",
                invalid: "Adresse invalide."
            },
            role: {
                empty: "Le rôle est obligatoire.",
                invalid: ""
            },
            status: {
                empty: "Le statut est obligatoire.",
                invalid: ""
            }
        };
        
        return messages[field] ? (isEmpty ? messages[field].empty : messages[field].invalid) : "Champ invalide";
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
    
    .error-message {
        color: #e74c3c;
        font-size: 0.8rem;
        margin-top: 0.3rem;
    }
    
    input:invalid, select:invalid {
        border-color: #e74c3c;
    }
    
    input:valid, select:valid {
        border-color: #00d09c;
    }
  `;
  document.head.appendChild(style);