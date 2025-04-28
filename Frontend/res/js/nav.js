document.addEventListener("DOMContentLoaded", function () {
    fetch("/MediPharm24/Backend/logic/auth.php")
        .then(response => response.json())
        .then(data => {
            let navBar = document.querySelector("#navbarNav ul"); // Wähle das UL-Element

            

            if (data.logged_in) {
                // User ist eingeloggt
                if (data.is_admin == true) { 
                    navBar.innerHTML = `

                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/frontend/index.html">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/frontend/sites/products.html">Produkte bearbeiten</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/frontend/sites/users.html">Kunden bearbeiten</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/frontend/sites/vouchers.html">Gutscheinverwaltung</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/Backend/logic/logout.php">Logout</a></li>
                        
                    `;
                } else {
                    navBar.innerHTML = `
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/frontend/index.html">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/frontend/sites/products.html">Produkte</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/frontend/sites/cart.html">Warenkorb</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/frontend/sites/profile.html">Mein Konto</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/Backend/logic/logout.php">Logout</a></li>
                    `;
                }
            } 
            else {
                // Nicht eingeloggt
                navBar.innerHTML = `
                    
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/frontend/index.html">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/frontend/sites/products.html">Produkte</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/frontend/sites/cart.html">Warenkorb</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/frontend/sites/register.html">Registrieren</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/frontend/sites/login.html">Login</a></li>
                        
                `;
            }
        })
        .catch(error => console.error("Fehler beim Laden der Navigation:", error));
});
