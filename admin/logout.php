<?php
session_start();
session_destroy(); // Tüm session'ları temizle
header('Location: login.php'); // Login sayfasına yönlendir
exit;
?> 