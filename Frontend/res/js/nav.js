document.addEventListener("DOMContentLoaded", function () {
    fetch("../Backend/logic/auth.php")
        .then(response => response.json())
        .then(data => {
            let navBar = document.querySelector("#navbarNav ul"); // Wähle das UL-Element

            

            if (data.logged_in) {
                // User ist eingeloggt
                if (data.is_admin) {
                    navBar.innerHTML = `
                        
                            <li class="nav-item"><a class="nav-link" href="sites/cart.html">Warenkorb</a></li>
                            <li class="nav-item"><a class="nav-link" href="sites/imprint.html">Impressum</a></li>
                            <li class="nav-item"><a class="nav-link" href="sites/terms.html">AGB</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/Backend/logic/logout.php">Logout</a></li>
                        
                    `;
                } else {
                    navBar.innerHTML = `
                        
                            <li class="nav-item"><a class="nav-link" href="sites/cart.html">Warenkorb</a></li>
                            <li class="nav-item"><a class="nav-link" href="sites/imprint.html">Impressum</a></li>
                            <li class="nav-item"><a class="nav-link" href="sites/terms.html">AGB</a></li>
                            <li class="nav-item"><a class="nav-link" href="/MediPharm24/Backend/logic/logout.php">Logout</a></li>
                        
                    `;
                }
            } else {
                // Nicht eingeloggt
                navBar.innerHTML = `
                    
                            <li class="nav-item"><a class="nav-link" href="sites/cart.html">Warenkorb</a></li>
                            <li class="nav-item"><a class="nav-link" href="sites/imprint.html">Impressum</a></li>
                            <li class="nav-item"><a class="nav-link" href="sites/terms.html">AGB</a></li>
                            <li class="nav-item"><a class="nav-link" href="sites/register.html">Registrieren</a></li>
                            <li class="nav-item"><a class="nav-link" href="sites/login.html">Login</a></li>
                        
                `;
            }
        })
        .catch(error => console.error("Fehler beim Laden der Navigation:", error));
});
