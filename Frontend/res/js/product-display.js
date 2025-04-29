$(document).ready(function() {
    // Warenkorb initialisieren
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    updateCartCount();

    // Kategorien laden
    loadCategories();

    // Drag & Drop Event Handler
    document.getElementById('cartIcon').addEventListener('dragover', function(e) {
        e.preventDefault();
    });

    document.getElementById('cartIcon').addEventListener('drop', function(e) {
        e.preventDefault();
        const productId = e.dataTransfer.getData('text/plain');
        addToCart(productId);
    });
});

function loadCategories() {
    $.ajax({
        url: '/MediPharm24/Backend/api/categories.php',
        method: 'GET',
        success: function(categories) {
            const categoryList = $('#categoryList');
            categoryList.empty();
            categories.forEach((category) => {
                categoryList.append(`
                    <a href="#" class="list-group-item list-group-item-action" 
                       data-category-id="${category.id}">
                        ${category.name}
                    </a>
                `);
            });
            // Erste Kategorie initial aktivieren und laden
            if (categories.length > 0) {
                $('#categoryList a').first().addClass('active');
                loadProducts(categories[0].id);
            }
        }
    });
}

// Event-Delegation für Kategorie-Klicks
$(document).on('click', '#categoryList a', function(e) {
    e.preventDefault();
    $('#categoryList a').removeClass('active');
    $(this).addClass('active');
    const categoryId = $(this).data('category-id');
    loadProducts(categoryId);
});

function loadProducts(categoryId) {
    const searchTerm = $('#searchInput').val();
    $.ajax({
        url: `/MediPharm24/Backend/api/products.php?category=${categoryId}&search=${encodeURIComponent(searchTerm)}`,
        method: 'GET',
        success: function(products) {
            const productGrid = $('#productGrid');
            productGrid.empty();
            if (products.length === 0) {
                productGrid.html('<div class="col-12"><p class="text-center">Keine Produkte gefunden.</p></div>');
                return;
            }
            products.forEach(product => {
                productGrid.append(`
                    <div class="col-md-4 mb-4">
                        <div class="card product-card" draggable="true" 
                             ondragstart="drag(event, ${product.id})">
                            <img src="${product.bild}" class="card-img-top" alt="${product.name}">
                            <div class="card-body">
                                <h5 class="card-title">${product.name}</h5>
                                <p class="card-text">${product.preis} €</p>
                                <p class="card-text">
                                    <small class="text-muted">Bewertung: ${product.rating}/5</small>
                                </p>
                                <button class="btn btn-primary" onclick="addToCart(${product.id})">
                                    In den Warenkorb
                                </button>
                            </div>
                        </div>
                    </div>
                `);
            });
        }
    });
}

function drag(event, productId) {
    event.dataTransfer.setData('text/plain', productId);
}

function addToCart(productId) {
    // Prüfen, ob der Benutzer eingeloggt ist
    fetch('/MediPharm24/Backend/logic/auth.php')
        .then(response => response.json())
        .then(data => {
            if (!data.logged_in) {
                showNotification('Bitte melden Sie sich an, um Produkte in den Warenkorb zu legen', 'warning');
                return;
            }

            // Wenn eingeloggt, Produkt zum Warenkorb hinzufügen
            $.ajax({
                url: '/MediPharm24/Backend/api/cart.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    action: 'add',
                    productId: productId
                }),
                success: function(response) {
                    if (response.error) {
                        showNotification('Fehler: ' + response.error, 'danger');
                        return;
                    }
                    updateCartCount();
                    showNotification('Produkt wurde zum Warenkorb hinzugefügt');
                },
                error: function(xhr, status, error) {
                    console.error('Fehler beim Hinzufügen zum Warenkorb:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    showNotification('Fehler beim Hinzufügen zum Warenkorb: ' + error, 'danger');
                }
            });
        })
        .catch(error => {
            console.error('Fehler beim Überprüfen des Login-Status:', error);
            showNotification('Ein Fehler ist aufgetreten', 'danger');
        });
}

function updateCartCount() {
    $.ajax({
        url: '/MediPharm24/Backend/api/cart.php',
        method: 'GET',
        success: function(response) {
            $('#cartCount').text(response.count);
        }
    });
}

// Erweiterte Benachrichtigungsfunktion mit Typ
function showNotification(message, type = 'success') {
    const notification = $(`
        <div class="alert alert-${type}" style="position: fixed; top: 20px; right: 20px; z-index: 1000;">
            ${message}
        </div>
    `);
    $('body').append(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Suchfeld Event-Listener
let searchTimeout;
$('#searchInput').on('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const activeCategory = $('#categoryList a.active');
        const categoryId = activeCategory.length ? activeCategory.data('category-id') : 0;
        loadProducts(categoryId);
    }, 300); // 300ms Verzögerung
}); 