<?php
session_start();
session_destroy();
header('Location: /MediPharm24/Frontend/index.html');
exit();
?>

