<?php
session_start();
require_once 'connection.php';

// Verifica che l'utente sia loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verifica che il form sia stato inviato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $film_id = isset($_POST['film_id']) ? intval($_POST['film_id']) : 0;
    $utente_id = $_SESSION['user_id'];
    
    if ($film_id > 0) {
        // Rimuovi dalla watchlist
        $query_rimuovi = "DELETE FROM " . T_WATCHLIST . " WHERE id_utente = ? AND id_film = ?";
        $stmt_rimuovi = $conn->prepare($query_rimuovi);
        $stmt_rimuovi->bind_param("ii", $utente_id, $film_id);
        
        if ($stmt_rimuovi->execute()) {
            $_SESSION['successo'] = "Film rimosso dalla watchlist!";
        } else {
            $_SESSION['errore'] = "Errore durante la rimozione dalla watchlist: " . $conn->error;
        }
        $stmt_rimuovi->close();
    } else {
        $_SESSION['errore'] = "Film non valido.";
    }
    
    // Reindirizza all'area utente
    header('Location: areaUtente.php');
    exit();
} else {
    header('Location: index.php');
    exit();
}
?>