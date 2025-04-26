// Produktanzeige initialisieren
// Diese Datei ist für eine alternative Produktliste, nicht für die Hauptprodukte-Seite zuständig!
document.addEventListener('DOMContentLoaded', function() {
    fetch('/MediPharm24/Backend/api/products.php?category=1')
        .then(response => response.json())
        .then(data => {
            let productList = document.getElementById("product-list");
            if (!productList) return;
            productList.innerHTML = '';
            data.forEach(product => {
                let div = document.createElement("div");
                div.classList.add("card", "p-3");
                div.innerHTML = `
                    <h3>${product.name}</h3>
                    <img src="${product.bild}" style="max-width:100px;">
                    <p class='text-primary font-weight-bold'>${product.preis} EUR</p>
                    <p>Bewertung: ${product.rating}/5</p>
                `;
                productList.appendChild(div);
            });
        })
        .catch(error => console.error('Fehler beim Laden der Produkte:', error));
}); 