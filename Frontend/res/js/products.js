// Produktanzeige initialisieren
document.addEventListener('DOMContentLoaded', function() {
    fetch('../backend/requestHandler.php?action=getProducts')
        .then(response => response.json())
        .then(data => {
            let productList = document.getElementById("product-list");
            data.data.forEach(product => {
                let div = document.createElement("div");
                div.classList.add("card", "p-3");
                div.innerHTML = `
                    <h3>${product.name}</h3>
                    <p>${product.beschreibung}</p>
                    <p class='text-primary font-weight-bold'>${product.preis} EUR</p>
                `;
                productList.appendChild(div);
            });
        })
        .catch(error => console.error('Fehler beim Laden der Produkte:', error));
}); 