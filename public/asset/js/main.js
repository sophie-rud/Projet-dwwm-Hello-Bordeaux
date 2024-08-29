// ---------- BOUTONS SUPPRIMER popups

// On récupère tous les éléments ayant pour classe "js-admin-activity-delete" (boutons de suppression)
const deleteActivityButtons = document.querySelectorAll('.js-admin-activity-delete');

// pour chaque bouton de suppression trouvé
deleteActivityButtons.forEach((deleteActivityButton) => {
    // on ajoute un event listener "click" : au click on éxécute une fonction de callback
    deleteActivityButton.addEventListener('click', () => {

        // on récupère la valeur de l'attribut data-activity-trigger-id de l'élément cliqué
        const activityId = deleteActivityButton.dataset.activityTriggerId;

        // on séléctionne l'élément HTML comportant un attribut data-activity-popup-target-id et dont la valeur est la même que l'id de l'activité
        const popup = document.querySelector(`[data-activity-popup-target-id="${activityId}"]`);

        // on passe la popup trouvée en display block pour l'afficher
        popup.style.display = "block";
    });
})


// ---------- BOUTONS ANNULER des suppressions (ferme les popups)

// On récupère tous les boutons "Annuler" ayant pour classe "js-close-popup"
const closePopupButtons = document.querySelectorAll('.js-close-popup');

// Pour chaque bouton "Annuler" trouvé
closePopupButtons.forEach((closePopupButton) => {

    // On ajoute un event listener "click" : au click on exécute une fonction de callback
    closePopupButton.addEventListener('click', () => {

        // On remonte jusqu'à la popup parente et on la masque
        const popup = closePopupButton.closest('.admin-activity-popup-delete');
        popup.style.display = "none";
    });
});