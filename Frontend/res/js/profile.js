function showSection(sectionId) {
    // Alle Sektionen ausblenden
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });

    // Gewählte Sektion anzeigen
    document.getElementById(sectionId + '-section').classList.add('active');

    // Wenn Bestellungen gewählt wurden, diese neu laden
    if (sectionId === 'orders') {
        loadOrders();
    }
}

$(document).ready(function() {
    // Profildaten laden
    fetch('/MediPharm24/Backend/logic/userHandler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=getProfile'
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const user = data.user;
                $('#address').val(user.address);
                $('#email').val(user.email);
                $('#payment_info').val(user.payment_info);
                $('#postal_code').val(user.postal_code);
                $('#city').val(user.city);
            } else {
                alert('Fehler beim Laden der Profildaten: ' + data.message);
            }
        })
        .catch(error => {
            alert('Fehler beim Laden der Profildaten.');
            console.error(error);
        });

    // Profil speichern
    $('#profileForm').on('submit', function(event) {
        event.preventDefault();
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();
        if (password && password !== confirmPassword) {
            alert('Die Passwörter stimmen nicht überein!');
            return;
        }
        const formData = new FormData(this);
        formData.append('action', 'updateProfile');
        fetch('/MediPharm24/Backend/logic/userHandler.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Profil erfolgreich aktualisiert!');
                } else {
                    alert('Fehler beim Speichern: ' + data.message);
                }
            })
            .catch(error => {
                alert('Fehler beim Speichern des Profils.');
                console.error(error);
            });
    });
});

// Bestellungen laden und anzeigen
function loadOrders() {
    fetch('/MediPharm24/Backend/api/userOrders.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const orders = data.orders;
                let html = '';
                if (orders.length === 0) {
                    html = '<p>Keine Bestellungen vorhanden.</p>';
                } else {
                    html = '<div class="accordion" id="ordersAccordion">';
                    orders.forEach((order, idx) => {
                        html += `
                        <div class="card">
                            <div class="card-header" id="heading${idx}">
                                <h2 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse${idx}" aria-expanded="true" aria-controls="collapse${idx}">
                                        Bestellung #${order.id} vom ${order.datum} (${order.status})
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse${idx}" class="collapse" aria-labelledby="heading${idx}" data-parent="#ordersAccordion">
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Produkt</th>
                                                <th>Menge</th>
                                                <th>Einzelpreis</th>
                                                <th>Gesamt</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                        order.produkte.forEach(prod => {
                            html += `
                                            <tr>
                                                <td>${prod.name}</td>
                                                <td>${prod.menge}</td>
                                                <td>${Number(prod.einzelpreis).toFixed(2)} €</td>
                                                <td>${(prod.menge * prod.einzelpreis).toFixed(2)} €</td>
                                            </tr>`;
                        });
                        html += `
                                        </tbody>
                                    </table>
                                    ${order.gutschein_code ? `<div class='mb-2'><span class='badge badge-info'>Gutschein verwendet: ${order.gutschein_code} (Rabatt: -${Number(order.gutschein_rabatt).toFixed(2)} €)</span></div>` : ''}
                                    <div class="text-right">
                                        ${order.status === 'bezahlt' || order.status === 'versendet' ? `<button class="btn btn-primary" onclick="window.open('/MediPharm24/Backend/api/generatePdf.php?order_id=${order.id}', '_blank')">Rechnung drucken</button>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    });
                    html += '</div>';
                }
                document.getElementById('orderList').innerHTML = html;
            } else {
                document.getElementById('orderList').innerHTML = '<p class="text-danger">Fehler beim Laden der Bestellungen.</p>';
            }
        });
}