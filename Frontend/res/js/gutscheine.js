$(document).ready(function() {
    // Alle Gutscheine laden und anzeigen
    function loadCoupons() {
        $.getJSON('../../Backend/api/get_coupons.php', function(data) {
            let body = '';
            const today = new Date().toISOString().split('T')[0];
            data.forEach(c => {
                let status = 'Aktiv';
                if (c.eingeloest == 1) status = 'Eingelöst';
                else if (c.gueltig_bis < today) status = 'Abgelaufen';
                body += `<tr>
                    <td>${c.id}</td>
                    <td>${c.code}</td>
                    <td>${parseFloat(c.rabatt).toFixed(2)} €</td>
                    <td>${c.gueltig_bis}</td>
                    <td>${status}</td>
                </tr>`;
            });
            $('#coupon-table-body').html(body);
        });
    }

    loadCoupons();

    // Neues Coupon‑Formular abschicken
    $('#create-coupon-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.post('../../Backend/api/create_coupon.php', formData, function(resp) {
            if (resp.success) {
                loadCoupons();
                $('#rabatt, #gueltig_bis').val('');
            } else {
                alert('Fehler: ' + resp.error);
            }
        }, 'json');
    });
});
