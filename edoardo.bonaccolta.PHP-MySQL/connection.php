<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "edoardo_bonaccolta_php_mysql";

// Creare connessione
$conn = new mysqli($servername, $username, $password);

// Verificare connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Creare database se non esiste
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // Selezionare il database
    $conn->select_db($dbname);
} else {
    die("Errore creazione database: " . $conn->error);
}
?>