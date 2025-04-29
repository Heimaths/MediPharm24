$(document).ready(function() {
    // Kundenliste laden
    loadCustomers();

    // Event-Handler für Kundenstatus ändern
    $(document).on('click', '.toggle-status', function() {
        const customerId = $(this).data('customer-id');
        const newStatus = $(this).data('new-status');
        
        $.ajax({
            url: '../api/admin/update_customer_status.php',
            method: 'POST',
            data: {
                customer_id: customerId,
                is_active: newStatus
            },
            success: function(response) {
                if (response.success) {
                    loadCustomers();
                } else {
                    alert('Fehler beim Aktualisieren des Kundenstatus');
                }
            }
        });
    });

    // Event-Handler für Bestelldetails anzeigen
    $(document).on('click', '.view-orders', function() {
        const customerId = $(this).data('customer-id');
        
        $.ajax({
            url: '../api/admin/get_customer_orders.php',
            method: 'GET',
            data: { customer_id: customerId },
            success: function(response) {
                if (response.success) {
                    displayOrderDetails(response.orders);
                    $('#orderDetailsModal').modal('show');
                } else {
                    alert('Fehler beim Laden der Bestelldetails');
                }
            }
        });
    });

    // Event-Handler für Produkt aus Bestellung entfernen
    $(document).on('click', '.remove-product', function() {
        const orderItemId = $(this).data('order-item-id');
        
        if (confirm('Möchten Sie dieses Produkt wirklich aus der Bestellung entfernen?')) {
            $.ajax({
                url: '../api/admin/remove_order_item.php',
                method: 'POST',
                data: { order_item_id: orderItemId },
                success: function(response) {
                    if (response.success) {
                        loadCustomers();
                    } else {
                        alert('Fehler beim Entfernen des Produkts');
                    }
                }
            });
        }
    });
});

function loadCustomers() {
    $.ajax({
        url: '../api/admin/get_customers.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const tbody = $('#customersTable tbody');
                tbody.empty();
                
                response.customers.forEach(function(customer) {
                    const row = `
                        <tr>
                            <td>${customer.id}</td>
                            <td>${customer.name}</td>
                            <td>${customer.email}</td>
                            <td>${customer.is_active ? 'Aktiv' : 'Inaktiv'}</td>
                            <td>
                                <button class="btn btn-info btn-sm view-orders" 
                                        data-customer-id="${customer.id}">
                                    Bestellungen anzeigen
                                </button>
                            </td>
                            <td>
                                <button class="btn ${customer.is_active ? 'btn-warning' : 'btn-success'} btn-sm toggle-status"
                                        data-customer-id="${customer.id}"
                                        data-new-status="${!customer.is_active}">
                                    ${customer.is_active ? 'Deaktivieren' : 'Aktivieren'}
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            }
        }
    });
}

function displayOrderDetails(orders) {
    const content = $('#orderDetailsContent');
    content.empty();
    
    orders.forEach(function(order) {
        const orderHtml = `
            <div class="order-details mb-4">
                <h4>Bestellung #${order.id}</h4>
                <p>Datum: ${order.order_date}</p>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Menge</th>
                            <th>Preis</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${order.items.map(item => `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>${item.quantity}</td>
                                <td>${item.price} €</td>
                                <td>
                                    <button class="btn btn-danger btn-sm remove-product"
                                            data-order-item-id="${item.id}">
                                        Entfernen
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
        content.append(orderHtml);
    });
} 