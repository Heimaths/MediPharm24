// Globale Variablen
let currentProductId = null;
let categories = [];
let searchTimeout;
let currentImagePath = null;

// Seite initialisieren
$(document).ready(function() {
    loadCategories();
    loadProducts();
    setupEventListeners();
});

// Event Listener einrichten
function setupEventListeners() {
    $('#saveProductBtn').click(saveProduct);
    $('#productModal').on('hidden.bs.modal', resetForm);

    // Bild-Upload
    $('#productImageUpload').on('change', handleImageUpload);

    // Suchfunktion mit Verzögerung
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(loadProducts, 300);
    });

    // Kategorie-Filter
    $('#categoryFilter').change(loadProducts);

    // Suchbutton
    $('#searchButton').click(loadProducts);
}

// Kategorien laden
function loadCategories() {
    $.get('/MediPharm24/Backend/api/categories.php')
        .done(function(data) {
            categories = data;
            const select = $('#productCategory');
            const filter = $('#categoryFilter');

            // Kategorien für beide Dropdowns aktualisieren
            [select, filter].forEach(element => {
                element.empty();
                if (element.attr('id') === 'categoryFilter') {
                    element.append('<option value="">Alle Kategorien</option>');
                }
                data.forEach(category => {
                    element.append(`<option value="${category.id}">${category.name}</option>`);
                });
            });
        })
        .fail(function(error) {
            showNotification('Fehler beim Laden der Kategorien', 'danger');
        });
}

// Produkte laden
function loadProducts() {
    const searchTerm = $('#searchInput').val();
    const categoryId = $('#categoryFilter').val();

    let url = '/MediPharm24/Backend/api/productManagement.php';
    const params = [];

    if (searchTerm) {
        params.push(`search=${encodeURIComponent(searchTerm)}`);
    }
    if (categoryId) {
        params.push(`category_id=${categoryId}`);
    }

    if (params.length > 0) {
        url += '?' + params.join('&');
    }

    $.get(url)
        .done(function(response) {
            if (response.status === 'success') {
                const tbody = $('#productTableBody');
                tbody.empty();

                if (response.products.length === 0) {
                    tbody.append(`
                                <tr>
                                    <td colspan="6" class="text-center">
                                        Keine Produkte gefunden
                                    </td>
                                </tr>
                            `);
                    return;
                }

                response.products.forEach(product => {
                    const category = categories.find(c => c.id === product.kategorie_id);
                    tbody.append(`
                                <tr>
                                    <td>${product.id}</td>
                                    <td>
                                        <img src="${product.bild || '../../res/img/no-image.jpg'}" 
                                             alt="${product.name}" 
                                             style="max-width: 50px;">
                                    </td>
                                    <td>${product.name}</td>
                                    <td>${category ? category.name : 'Keine Kategorie'}</td>
                                    <td>${product.preis} €</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" 
                                                onclick="editProduct(${JSON.stringify(product).replace(/"/g, '&quot;')})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="deleteProduct(${product.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                });
            }
        })
        .fail(function(error) {
            showNotification('Fehler beim Laden der Produkte', 'danger');
        });
}

// Produkt speichern
function saveProduct() {
    const productData = {
        name: $('#productName').val(),
        beschreibung: $('#productDescription').val(),
        preis: parseFloat($('#productPrice').val()),
        kategorie_id: parseInt($('#productCategory').val()),
        bild: currentImagePath || null
    };

    if (currentProductId) {
        productData.id = currentProductId;
    }

    const method = currentProductId ? 'PUT' : 'POST';

    $.ajax({
        url: '/MediPharm24/Backend/api/productManagement.php',
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(productData)
    })
        .done(function(response) {
            if (response.status === 'success') {
                $('#productModal').modal('hide');
                showNotification(response.message);
                loadProducts();
            } else {
                showNotification(response.message, 'danger');
            }
        })
        .fail(function(error) {
            showNotification('Fehler beim Speichern des Produkts', 'danger');
        });
}

// Bild-Upload verarbeiten
function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Überprüfe Dateityp
    if (!file.type.match('image.*')) {
        showNotification('Bitte wählen Sie eine Bilddatei aus', 'danger');
        return;
    }

    // Überprüfe Dateigröße (5MB)
    if (file.size > 5 * 1024 * 1024) {
        showNotification('Die Datei ist zu groß (max. 5MB)', 'danger');
        return;
    }

    // Zeige Dateinamen an
    $('.custom-file-label').text(file.name);

    // Erstelle FormData für Upload
    const formData = new FormData();
    formData.append('image', file);

    // Lade Bild hoch
    $.ajax({
        url: '/MediPharm24/Backend/api/uploadImage.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false
    })
        .done(function(response) {
            if (response.status === 'success') {
                currentImagePath = response.image_path;
                $('#productImage').val(currentImagePath);

                // Zeige Vorschau
                const preview = $('#imagePreview');
                preview.find('img').attr('src', currentImagePath);
                preview.show();

                showNotification('Bild erfolgreich hochgeladen');
            } else {
                showNotification(response.message, 'danger');
            }
        })
        .fail(function(error) {
            showNotification('Fehler beim Hochladen des Bildes', 'danger');
        });
}

// Bild entfernen
function removeImage() {
    currentImagePath = null;
    $('#productImage').val('');
    $('#productImageUpload').val('');
    $('.custom-file-label').text('Bild auswählen...');
    $('#imagePreview').hide();
}

// Produkt bearbeiten
function editProduct(product) {
    currentProductId = product.id;
    $('#modalTitle').text('Produkt bearbeiten');
    $('#productName').val(product.name);
    $('#productDescription').val(product.beschreibung);
    $('#productPrice').val(product.preis);
    $('#productCategory').val(product.kategorie_id);

    // Bild-Handling
    if (product.bild) {
        currentImagePath = product.bild;
        $('#productImage').val(currentImagePath);
        const preview = $('#imagePreview');
        preview.find('img').attr('src', currentImagePath);
        preview.show();
        $('.custom-file-label').text('Bild ändern...');
    } else {
        removeImage();
    }

    $('#productModal').modal('show');
}

// Produkt löschen
function deleteProduct(productId) {
    if (confirm('Möchten Sie dieses Produkt wirklich löschen?')) {
        $.ajax({
            url: `/MediPharm24/Backend/api/productManagement.php?id=${productId}`,
            method: 'DELETE'
        })
            .done(function(response) {
                if (response.status === 'success') {
                    showNotification(response.message);
                    loadProducts();
                } else {
                    showNotification(response.message, 'danger');
                }
            })
            .fail(function(error) {
                showNotification('Fehler beim Löschen des Produkts', 'danger');
            });
    }
}

// Formular zurücksetzen
function resetForm() {
    currentProductId = null;
    currentImagePath = null;
    $('#modalTitle').text('Neues Produkt');
    $('#productForm')[0].reset();
    $('.custom-file-label').text('Bild auswählen...');
    $('#imagePreview').hide();
}

// Benachrichtigung anzeigen
function showNotification(message, type = 'success') {
    const notification = $(`
                <div class="alert alert-${type} alert-dismissible fade show" 
                     style="position: fixed; top: 20px; right: 20px; z-index: 1000;">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `);
    $('body').append(notification);
    setTimeout(() => notification.alert('close'), 3000);
}