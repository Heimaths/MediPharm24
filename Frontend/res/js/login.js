document.getElementById('loginForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'login');  // 👈 Aktion explizit setzen!

    fetch('/Backend/logic/userHandler.php', {
        method: 'POST',
        body: formData  // Ändere `body` direkt auf `formData`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server-Antwort:', data);
        if (data.status === 'success') {
            alert(data.message);
            window.location.href = 'index.html';
        } else {
            alert('Fehler: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Fehler bei der Anfrage:', error);
        alert('Ein unerwarteter Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.');
    });
});
