<?php
require_once "dati_generali.php";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

define('T_UTENTI', 'utenti');
?>
