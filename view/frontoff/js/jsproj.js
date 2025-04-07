const projets = [
    {
      nom: 'EcoRide',
      categorie: 'Mobilité Verte',
      objectif: 500000,
      leve: 345000,
      jours: 15
    },
    {
      nom: 'NutriScan',
      categorie: 'Santé & Bien-être',
      objectif: 300000,
      leve: 230000,
      jours: 8
    },
    {
      nom: 'AquaPure',
      categorie: 'Cleantech',
      objectif: 600000,
      leve: 520000,
      jours: 23
    }
  ];
  
  // Affichage de la liste
  function afficherProjets() {
    const tbody = document.getElementById("project-table-body");
    tbody.innerHTML = "";
  
    projets.forEach((proj, i) => {
      const pourcentage = Math.round((proj.leve / proj.objectif) * 100);
      const tr = document.createElement("tr");
  
      tr.innerHTML = `
        <td>${proj.nom}</td>
        <td><span class="category ${proj.categorie.split(" ")[0]}">${proj.categorie}</span></td>
        <td>${proj.objectif.toLocaleString()}€</td>
        <td>${proj.leve.toLocaleString()}€</td>
        <td>
          <div class="progress-bar">
            <div class="progress-bar-fill" style="width: ${pourcentage}%;"></div>
          </div>
          <small>${pourcentage}%</small>
        </td>
        <td>${proj.jours}</td>
        <td>
          <button class="action-btn" title="Modifier">✏️</button>
          <button class="action-btn" title="Supprimer" onclick="supprimerProjet(${i})">🗑️</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }
  
  // Suppression d’un projet
  function supprimerProjet(index) {
    if (confirm("Voulez-vous supprimer ce projet ?")) {
      projets.splice(index, 1);
      afficherProjets();
    }
  }
  
  // Navigation entre les onglets
  document.getElementById("tab-liste").addEventListener("click", () => {
    document.getElementById("content-liste").style.display = "block";
    document.getElementById("content-creer").style.display = "none";
    document.getElementById("tab-liste").classList.add("active");
    document.getElementById("tab-creer").classList.remove("active");
  });
  
  document.getElementById("tab-creer").addEventListener("click", () => {
    document.getElementById("content-liste").style.display = "none";
    document.getElementById("content-creer").style.display = "block";
    document.getElementById("tab-creer").classList.add("active");
    document.getElementById("tab-liste").classList.remove("active");
  });
  
  // Création de projet
  document.getElementById("form-projet").addEventListener("submit", function (e) {
    e.preventDefault();
  
    const nom = document.getElementById("nom").value;
    const categorie = document.getElementById("categorie").value;
    const objectif = parseInt(document.getElementById("objectif").value);
    const leve = parseInt(document.getElementById("leve").value);
    const jours = parseInt(document.getElementById("jours").value);
  
    projets.push({ nom, categorie, objectif, leve, jours });
    afficherProjets();
  
    alert("Projet ajouté avec succès !");
    this.reset();
  
    document.getElementById("tab-liste").click(); // retour à la liste
  });
  
  // Initialisation
  afficherProjets();