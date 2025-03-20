document.getElementById('registerForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    if (formData.get('password') !== formData.get('confirm_password')) {
        alert('Passwords do not match');
        return;
    }
    fetch('../logic/userHandler.php', {
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