document.getElementById('registrationForm').addEventListener('submit', function(event) {
    event.preventDefault();
    let formData = new FormData(this);


    if (formData.get("password") !== formData.get("confirmPassword")) {
        alert('Passwords do not match');
        return;
    }
    fetch('/Webscripting-Webproject/Backend/logic/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            window.location.href = 'login.html';
        } else {
            alert(data.message);
        }
    });
});