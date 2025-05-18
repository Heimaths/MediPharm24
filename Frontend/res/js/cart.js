$(document).ready(function() {
    loadCart();
});

let appliedCoupon = null;
let rabattWert = 0;

$(document).on('click', '#applyCouponBtn', function() {
    const code = $('#couponCode').val().trim();
    if (!code) return;
    if (appliedCoupon) return;
    $.ajax({
        url: '/MediPharm24/Backend/api/validateCoupon.php',
        method: 'POST',
        data: { code },
        success: function(response) {
            if (response.status === 'success') {
                appliedCoupon = response.code;
                rabattWert = parseFloat(response.rabatt);
                $('#couponFeedback').html(`<span class='text-success'>Gutschein akzeptiert: -${rabattWert.toFixed(2)} €</span>`);
                $('#couponCode').prop('disabled', true);
                $('#applyCouponBtn').prop('disabled', true);
                updateTotalWithCoupon();
            } else {
                $('#couponFeedback').html(`<span class='text-danger'>${response.message}</span>`);
            }
        },
        error: function() {
            $('#couponFeedback').html('<span class="text-danger">Fehler bei der Gutscheinprüfung.</span>');
        }
    });
});

function updateTotalWithCoupon() {
    let total = 0;
    $('#cartItems .card').each(function() {
        const priceText = $(this).find('.card-text').text().replace('€','').replace(',','.');
        const price = parseFloat(priceText);
        const quantity = parseInt($(this).find('input[type="text"]').val());
        total += price * quantity;
    });
    if (rabattWert > 0) {
        total = Math.max(0, total - rabattWert);
    }
    $('#totalAmount').text(total.toFixed(2) + ' €');
}

function loadCart() {
    $.ajax({
        url: '/MediPharm24/Backend/api/cart.php',
        method: 'GET',
        success: function(response) {
            const cartItems = $('#cartItems');
            cartItems.empty();

            if (response.items.length === 0) {
                cartItems.html('<p>Ihr Warenkorb ist leer.</p>');
                $('#totalAmount').text('0,00 €');
                return;
            }

            response.items.forEach(item => {
                cartItems.append(`
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="card-title">${item.name}</h5>
                                        <p class="card-text">${Number(item.price).toFixed(2)} €</p>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <div class="input-group mb-3" style="max-width: 200px; margin-left: auto;">
                                            <div class="input-group-prepend">
                                                <button class="btn btn-outline-secondary" type="button" 
                                                        onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                                            </div>
                                            <input type="text" class="form-control text-center" 
                                                   value="${item.quantity}" readonly>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" 
                                                        onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                                            </div>
                                        </div>
                                        <button class="btn btn-danger" onclick="removeFromCart(${item.id})">
                                            Entfernen
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
            });

            updateTotalWithCoupon();
        },
        error: function(xhr, status, error) {
            console.error('Fehler beim Laden des Warenkorbs:', error);
            $('#cartItems').html('<p class="text-danger">Fehler beim Laden des Warenkorbs.</p>');
        }
    });
}

function updateQuantity(productId, newQuantity) {
    if (newQuantity < 1) return;

    $.ajax({
        url: '/MediPharm24/Backend/api/cart.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'update',
            productId: productId,
            quantity: newQuantity
        }),
        success: function() {
            loadCart();
        },
        error: function(xhr, status, error) {
            console.error('Fehler beim Aktualisieren der Menge:', error);
            alert('Fehler beim Aktualisieren der Menge');
        }
    });
}

function removeFromCart(productId) {
    $.ajax({
        url: '/MediPharm24/Backend/api/cart.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'remove',
            productId: productId
        }),
        success: function() {
            loadCart();
        },
        error: function(xhr, status, error) {
            console.error('Fehler beim Entfernen des Produkts:', error);
            alert('Fehler beim Entfernen des Produkts');
        }
    });
}

function checkout() {
    const coupon = appliedCoupon ? appliedCoupon : null;
    fetch('/MediPharm24/Backend/api/order.php', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ coupon: coupon })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Bestellung erfolgreich angelegt! Bestellnummer: ' + data.order_id);
                // Optional: Warenkorb leeren oder zur Bestellübersicht weiterleiten
                location.reload();
            } else {
                alert('Fehler beim Anlegen der Bestellung: ' + data.message);
            }
        })
        .catch(error => {
            alert('Fehler beim Anlegen der Bestellung.');
            console.error(error);
        });
}
