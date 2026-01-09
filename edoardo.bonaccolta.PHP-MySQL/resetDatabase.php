<?php
require_once 'connection.php';

// Eliminare le tabelle in ordine corretto (per vincoli di foreign key)
$tables = ['recensioni', 'watchlist', 'film', 'utenti'];

foreach ($tables as $table) {
    $sql = "DROP TABLE IF EXISTS $table";
    if ($conn->query($sql)) {
        echo "Tabella $table eliminata.<br>";
    } else {
        echo "Errore eliminazione tabella $table: " . $conn->error . "<br>";
    }
}

echo "<h3>Database resettato!</h3>";
echo "<p><a href='install.php'>Esegui di nuovo l'installazione</a></p>";

$conn->close();
?>