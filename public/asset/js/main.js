// ---------- BOUTONS DE CONFIRMATION popups (pour suppression, inscription)

// On récupère tous les éléments ayant pour classe "js-popup-confirmation" (boutons avec confirmation de choix)
const confirmationButtons = document.querySelectorAll('.js-popup-confirmation');

// pour chaque bouton de "confirmation" trouvé
confirmationButtons.forEach((confirmationButton) => {
    // on ajoute un event listener "click" : au click on éxécute une fonction de callback
    confirmationButton.addEventListener('click', () => {

        // on récupère la valeur de l'attribut data-trigger-id de l'élément cliqué
        const elementId = confirmationButton.dataset.triggerId;

        // on séléctionne l'élément HTML comportant un attribut data-popup-target-id et dont la valeur est la même que l'id recherché (de l'activité, de la catégorie, du user...)
        const popup = document.querySelector(`[data-popup-target-id="${elementId}"]`);

        // on passe la popup trouvée en display block pour l'afficher
        popup.style.display = "block";
    });
})


// ---------- BOUTONS ANNULER (ferme les popups)

// On récupère tous les boutons "Annuler" ayant pour classe "js-close-popup"
const closePopupButtons = document.querySelectorAll('.js-close-popup');

// Pour chaque bouton "Annuler" trouvé
closePopupButtons.forEach((closePopupButton) => {

    // On ajoute un event listener "click" : au click on exécute une fonction de callback
    closePopupButton.addEventListener('click', () => {

        // On remonte jusqu'à la popup parente et on la masque
        const popup = closePopupButton.closest('.popup-confirmation');
        popup.style.display = "none";
    });
});