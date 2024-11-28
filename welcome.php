<?php
session_start(); // Kezdjük el a munkamenetet

// Ha nincs bejelentkezett felhasználó, visszairányítjuk a login.php-ra
if (!isset($_SESSION['username']) || !isset($_SESSION['color'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username']; // A felhasználónév
$color = $_SESSION['color']; // A kedvenc szín

// Ellenőrizzük, hogy a szín hexadecimális formátumban van-e
// Ha nem, akkor egy alap színt adunk vissza
if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
    $color = '#ffffff'; // Alap szín, ha nem megfelelő a kód
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Üdvözöllek (beadando2)</title>
    <style>
        body {
            display: flex;
            justify-content: center; /* Horizontálisan középre igazít */
            align-items: center; /* Vertikálisan középre igazít */
            height: 100vh; /* Teljes magasságot lefedi */
            margin: 0; /* Alapértelmezett margó eltávolítása */
            background-color: <?php echo $color; ?>;
            font-family: Arial, sans-serif; /* Betűtípus */
        }

        h2 {
            font-size: 2em;
        }
    </style>
</head>
<body>
    <h2>Üdvözöllek, <?php echo $username; ?>!</h2>
</body>
</html>
