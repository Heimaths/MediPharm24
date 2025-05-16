
document.getElementById('registrationForm').addEventListener('submit', function (event) {
    event.preventDefault();

    let formData = new FormData(this);
    formData.append('action', 'register');  // Aktion explizit setzen!

    fetch('../../Backend/logic/userHandler.php', {
        method: 'POST',
        body: formData  // Ändere `body` direkt auf `formData`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server-Antwort:', data);
        console.log('Status-Typ:', typeof data.status);
        console.log('Status-Wert:', data.status);

        if (data.success === true) {
            alert(data.message);
            window.location.href = 'login.html';
        } else {
            alert('Fehler: ' + data.message);
            console.log('Else-Block wurde ausgeführt, weil data.status nicht exakt "success" ist');
        }

    })
    .catch(error => {
        console.error('Fehler bei der Anfrage:', error);
        alert('Ein unerwarteter Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.');
    });
});
