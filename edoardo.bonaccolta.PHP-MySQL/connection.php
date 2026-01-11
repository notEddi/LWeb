<?php
require_once "dati_generali.php";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

define('T_FILM', 'film');
define('T_UTENTI', 'utenti');
define('T_RECENSIONI', 'recensioni');
define('T_WATCHLIST', 'watchlist');
?>
