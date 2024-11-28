<?php
session_start(); // Kezdd el a munkamenetet

// Hibaüzenetek megjelenítése
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Adatbázis kapcsolat
$conn = new mysqli("localhost", "root", "", "adatok");

// Ellenőrizzük, hogy a kapcsolat létrejött-e
if ($conn->connect_error) {
    die("Csatlakozási hiba: " . $conn->connect_error);
}

// Dekódoló függvény
function decode($text, $key) {
    $decoded = '';
    $keyLength = count($key);

    for ($i = 0, $len = strlen($text); $i < $len; $i++) {
        $decoded .= chr(ord($text[$i]) - $key[$i % $keyLength]);
    }

    return $decoded;
}

// Hibák tárolása
$errorMessage = '';

// Form adatainak feldolgozása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    // Password.txt fájl beolvasása és dekódolása
    $passwordFile = 'password.txt';
    $lines = file($passwordFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $key = [5, -14, 31, -9, 3];
    $decodedCredentials = [];

    foreach ($lines as $line) {
        $decodedLine = decode($line, $key);
        list($username, $password) = explode('*', $decodedLine);
        $decodedCredentials[$username] = $password;
    }

    // Felhasználó ellenőrzése
    if (!array_key_exists($inputUsername, $decodedCredentials)) {
        $errorMessage = "Nincs ilyen felhasználó!";
    } elseif ($decodedCredentials[$inputUsername] !== $inputPassword) {
        $errorMessage = "Hibás jelszó!";
        header("refresh:1;url=https://www.police.hu");
        exit;
    } else {
        // Felhasználó sikeresen bejelentkezett, átirányítjuk a welcome.php oldalra
        // Kedvenc szín lekérdezése
        $stmt = $conn->prepare("SELECT color FROM users WHERE username = ?");
        $stmt->bind_param("s", $inputUsername);
        $stmt->execute();
        $stmt->bind_result($color);
        $stmt->fetch();

        if ($color) {
            // Beállítjuk a színt a session-be, hogy átadjuk a welcome.php-nak
            $_SESSION['username'] = $inputUsername;
            $_SESSION['color'] = $color;

            // Átirányítás a welcome.php oldalra
            header("Location: welcome.php");
            exit;
        } else {
            echo "Nem sikerült betölteni a kedvenc színt.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Bejelentkezés (Beadando2)</title>
    <style>
        body {
            font-family: Arial, sans-serif; /* Betűtípus beállítása */
            background-color: #f0f0f0; /* Háttér színe */
            margin: 0; /* Alapértelmezett margó eltávolítása */
            height: 100vh; /* Az egész oldal megtölti a képernyőt */
            display: flex;
            justify-content: center; /* Horizontálisan középre igazít */
            align-items: center; /* Vertikálisan középre igazít */
            position: relative; /* Ahhoz, hogy a jobb felső sarokba elhelyezhessük a szöveget */
        }

        /* Jobb felső sarokban elhelyezkedő szöveg */
        .username {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 1.1em;
            font-weight: bold;
        }

        form {
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px; /* Lekerekített sarkok */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Árnyék hozzáadása */
            text-align: center;
            width: 300px; /* Form szélesség */
        }

        label {
            display: block;
            margin: 10px 0;
            font-size: 1.1em;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; /* A padding nem növeli a szélességet */
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 1.1em;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Hibaüzenet stílusa */
        .error-message {
            color: red;
            font-size: 1.1em;
            margin-top: 150px;
            text-align: center; /* Középre igazítja a hibaüzenetet */
            position: fixed;
        }

        /* Form mezők közötti távolság növelése */
        .form-group {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<!-- Jobb felső sarokban elhelyezkedő szöveg -->
<div class="username">dr. Csonka Norbert (E0SQS2)</div>

<form action="login.php" method="post">
    <div class="form-group">
        <label for="username">Felhasználónév:</label>
        <input type="text" id="username" name="username" required><br><br>
    </div>

    <div class="form-group">
        <label for="password">Jelszó:</label>
        <input type="password" id="password" name="password" required><br><br>
    </div>

    <input type="submit" value="Bejelentkezés">
</form>

<!-- Hibaüzenet megjelenítése a form alatt -->
<?php if ($errorMessage): ?>
    <div class="error-message"><?php echo $errorMessage; ?></div>
<?php endif; ?>

</body>
</html>
