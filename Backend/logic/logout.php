<?php
session_start();

// Lösche die Remember-Cookies
setcookie('remember_data', '', time() - 3600, '/', '', true, true);
setcookie('remember_key', '', time() - 3600, '/', '', true, true);

// Session zerstören
session_destroy();

// Zurück zur Startseite
header('Location: /MediPharm24/Frontend/index.html');
exit();
?>

