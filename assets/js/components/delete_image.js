// Fonction qui permet d'effacer les images (admin_products_edit)

window.onload = () => {

    // Gestion des boutons "supprimer"
    let links = document.querySelectorAll("[data-delete]");
    
    // On boucle sur links
    for (link of links) {
        // On écoute le clic
        link.addEventListener("click", function(e) {
           // On empêche la navigation
            e.preventDefault()
           // On demande une confirmation
            if (confirm("Voulez-vous supprimer cette image ?")) {
                // On envoie une requête Ajax vers le href du lien avec la méthode delete
                fetch(this.getAttribute("href"), {
                    method: "DELETE",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({"_token": this.dataset.token})
                }).then(
                    // On récupère la réponse en json
                    response => response.json()
                ).then(data => {
                    if (data.success)
                        this.parentElement.parentElement.remove()
                    else
                        alert(data.error)
                }).catch(e => alert(e))
            }
        });
    }
}