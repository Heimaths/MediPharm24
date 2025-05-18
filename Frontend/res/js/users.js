function loadUsers(searchTerm = '') {
    const url = searchTerm
        ? `/MediPharm24/Backend/api/allUsers.php?search=${encodeURIComponent(searchTerm)}`
        : '/MediPharm24/Backend/api/allUsers.php';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                let html = '<table class="table"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Benutzername</th><th>Admin</th><th>Status</th><th>Aktion</th></tr></thead><tbody>';
                data.users.forEach(user => {
                    html += `<tr>
                        <td>${user.id}</td>
                        <td>${user.salutation} ${user.first_name} ${user.last_name}</td>
                        <td>${user.email}</td>
                        <td>${user.username}</td>
                        <td>${user.is_admin ? 'Ja' : 'Nein'}</td>
                        <td>${user.is_active ? '<span class="badge badge-success">Aktiv</span>' : '<span class="badge badge-danger">Inaktiv</span>'}</td>
                        <td>
                            <button class="btn btn-sm btn-primary mr-2" onclick="editUser(${user.id})">Bearbeiten</button>
                            <button class="btn btn-sm btn-info" onclick="showUserOrders(${user.id}, '${user.first_name} ${user.last_name}')">Bestellungen</button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table>';
                document.getElementById('userList').innerHTML = html;
            } else {
                document.getElementById('userList').innerHTML = '<p class="text-danger">Fehler beim Laden der Nutzer.</p>';
            }
        });
}

function searchUsers() {
    const searchTerm = document.getElementById('searchInput').value.trim();
    loadUsers(searchTerm);
}

// Event-Listener für Enter-Taste in der Suchleiste
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchUsers();
    }
});

// Lade Benutzer beim Start
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
});

function editUser(userId) {
    // Userdaten laden
    fetch(`/MediPharm24/Backend/api/getUser.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const user = data.user;
                $('#editUserId').val(user.id);
                $('#edit_salutation').val(user.salutation);
                $('#edit_first_name').val(user.first_name);
                $('#edit_last_name').val(user.last_name);
                $('#edit_email').val(user.email);
                $('#edit_address').val(user.address);
                $('#edit_postal_code').val(user.postal_code);
                $('#edit_city').val(user.city);
                $('#edit_username').val(user.username);
                $('#edit_is_admin').val(user.is_admin);
                $('#edit_is_active').val(user.is_active);
                $('#edit_password').val('');
                $('#editUserModal').modal('show');
            } else {
                alert('Fehler beim Laden des Nutzers.');
            }
        });
}

$('#saveUserBtn').on('click', function() {
    const formData = new FormData(document.getElementById('editUserForm'));
    fetch('/MediPharm24/Backend/api/updateUser.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                $('#editUserModal').modal('hide');
                loadUsers();
                alert(data.message);
            } else {
                alert('Fehler beim Speichern: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Fehler bei der Anfrage:', error);
            alert('Ein unerwarteter Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.');
        });
});

function showUserOrders(userId, userName) {
    document.getElementById('orderUserName').textContent = userName;
    $('#ordersModal').modal('show');

    // Bestellungen des Users laden
    fetch(`/MediPharm24/Backend/api/userOrders.php?user_id=${userId}`)
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
                                        Bestellung #${order.id} vom ${order.datum} (${getStatusBadge(order.status)})
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
                                    <form class="order-edit-form" data-order-id="${order.id}">
                                        <div class="form-row align-items-center">
                                            <div class="col-auto">
                                                <label for="status-select-${order.id}" class="sr-only">Status</label>
                                                <select class="form-control mb-2" id="status-select-${order.id}" name="status">
                                                    <option value="offen" ${order.status === 'offen' ? 'selected' : ''}>Offen</option>
                                                    <option value="bestätigt" ${order.status === 'bestätigt' ? 'selected' : ''}>Bestätigt</option>
                                                    <option value="bezahlt" ${order.status === 'bezahlt' ? 'selected' : ''}>Bezahlt</option>
                                                    <option value="versendet" ${order.status === 'versendet' ? 'selected' : ''}>Versendet</option>
                                                    <option value="storniert" ${order.status === 'storniert' ? 'selected' : ''}>Storniert</option>
                                                </select>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-success mb-2">Speichern</button>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="text-right">
                                        <a class="btn btn-primary" href="/MediPharm24/Backend/api/generatePdf.php?order_id=${order.id}" target="_blank">
                                            Rechnung drucken
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    });
                    html += '</div>';
                }
                document.getElementById('ordersList').innerHTML = html;
            } else {
                document.getElementById('ordersList').innerHTML = '<p class="text-danger">Fehler beim Laden der Bestellungen.</p>';
            }
        });
}

function getStatusBadge(status) {
    const badges = {
        'offen': 'badge-secondary',
        'bestätigt': 'badge-primary',
        'bezahlt': 'badge-info',
        'versendet': 'badge-success',
        'storniert': 'badge-danger'
    };
    const labels = {
        'offen': 'Offen',
        'bestätigt': 'Bestätigt',
        'bezahlt': 'Bezahlt',
        'versendet': 'Versendet',
        'storniert': 'Storniert'
    };
    return `<span class="badge ${badges[status] || 'badge-secondary'}">${labels[status] || status}</span>`;
}

function showOrderDetails(orderId) {
    $('#ordersModal').modal('hide');
    $('#orderDetailsModal').modal('show');

    // Bestelldetails laden
    fetch(`/MediPharm24/Backend/api/orderDetails.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const order = data.order;
                $('#orderId').val(order.id);
                $('#orderStatus').val(order.status);
                $('#orderTracking').val(order.tracking_number || '');
                $('#orderNotes').val(order.notes || '');

                // Bestellte Artikel anzeigen
                let itemsHtml = '';
                order.items.forEach(item => {
                    itemsHtml += `<tr>
                        <td>${item.product_name}</td>
                        <td>${item.quantity}</td>
                        <td>${parseFloat(item.price).toFixed(2)} €</td>
                        <td>${(item.quantity * parseFloat(item.price)).toFixed(2)} €</td>
                    </tr>`;
                });
                document.getElementById('orderItemsList').innerHTML = itemsHtml;

                let gutscheinHtml = '';
                if (order.gutschein_code) {
                    gutscheinHtml = `<div class='mb-2'><span class='badge badge-info'>Gutschein verwendet: ${order.gutschein_code} (Rabatt: -${Number(order.gutschein_rabatt).toFixed(2)} €)</span></div>`;
                }
                document.getElementById('orderItemsList').insertAdjacentHTML('afterend', gutscheinHtml);
            } else {
                alert('Fehler beim Laden der Bestelldetails.');
            }
        });
}

function saveOrderDetails() {
    const formData = new FormData(document.getElementById('orderDetailsForm'));
    fetch('/MediPharm24/Backend/api/updateOrder.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                $('#orderDetailsModal').modal('hide');
                // Bestellungsliste aktualisieren
                const userId = new URLSearchParams(window.location.search).get('user_id');
                if (userId) {
                    showUserOrders(userId);
                }
                alert('Bestellung wurde aktualisiert.');
            } else {
                alert('Fehler beim Speichern: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Fehler bei der Anfrage:', error);
            alert('Ein unerwarteter Fehler ist aufgetreten.');
        });
}

// Event-Delegation für das Bearbeiten-Formular
$(document).on('submit', '.order-edit-form', function(e) {
    e.preventDefault();
    const orderId = $(this).data('order-id');
    const status = $(this).find('select[name="status"]').val();
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', status);
    fetch('/MediPharm24/Backend/api/updateOrder.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Bestellung aktualisiert!');
                // Optional: Accordion neu laden
                const userId = $('#orderUserName').data('user-id');
                if (userId) showUserOrders(userId, $('#orderUserName').text());
            } else {
                alert('Fehler beim Speichern: ' + data.message);
            }
        })
        .catch(error => {
            alert('Fehler beim Speichern.');
            console.error(error);
        });
});

