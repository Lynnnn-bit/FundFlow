document.addEventListener("DOMContentLoaded", function () {
    // Slider functionality
    const montantSlider = document.getElementById("montant_demande");
    const dureeSlider = document.getElementById("duree_remboursement");
    const montantValue = document.getElementById("montantValue");
    const dureeValue = document.getElementById("dureeValue");
  
    function formatNumber(num) {
      return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
  
    montantSlider.addEventListener("input", function() {
      montantValue.textContent = formatNumber(this.value) + " €";
    });
  
    dureeSlider.addEventListener("input", function() {
      dureeValue.textContent = this.value + " mois";
    });
  
    // Form submission
    const form = document.getElementById("demandeForm");
    const message = document.getElementById("demandeMessage");
  
    form.addEventListener("submit", function (e) {
      e.preventDefault();
  
      const nomProjet = document.getElementById("nom_projet").value;
      const description = document.getElementById("description_projet").value;
      const montant = montantSlider.value;
      const duree = dureeSlider.value;
      const confirmation = document.getElementById("confirmation").checked;
  
      if (!confirmation) {
        message.textContent = "Veuillez confirmer que vous avez les documents nécessaires.";
        message.style.color = "red";
        return;
      }
  
      console.log({
        nomProjet,
        description,
        montant,
        duree,
        confirmation,
      });
  
      message.textContent = "Votre demande a été soumise avec succès !";
      message.style.color = "#10b981";
  
      form.reset();
      // Reset sliders to initial values
      montantSlider.value = 50000;
      dureeSlider.value = 24;
      montantValue.textContent = "50,000 €";
      dureeValue.textContent = "24 mois";
    });
  });