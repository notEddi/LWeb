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
        // Verifica se il film è già nella watchlist
        $query_check = "SELECT id FROM " . T_WATCHLIST . " WHERE id_utente = ? AND id_film = ?";
        $stmt_check = $conn->prepare($query_check);
        $stmt_check->bind_param("ii", $utente_id, $film_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $_SESSION['errore'] = "Film già presente nella watchlist.";
        } else {
            // Inserisci nella watchlist
            $query_inserisci = "INSERT INTO " . T_WATCHLIST . " (id_utente, id_film) VALUES (?, ?)";
            $stmt_inserisci = $conn->prepare($query_inserisci);
            $stmt_inserisci->bind_param("ii", $utente_id, $film_id);
            
            if ($stmt_inserisci->execute()) {
                $_SESSION['successo'] = "Film aggiunto alla watchlist!";
            } else {
                $_SESSION['errore'] = "Errore durante l'aggiunta alla watchlist: " . $conn->error;
            }
            $stmt_inserisci->close();
        }
        $stmt_check->close();
    } else {
        $_SESSION['errore'] = "Film non valido.";
    }
    
    // Reindirizza alla pagina del film
    header('Location: film.php?id=' . $film_id);
    exit();
} else {
    header('Location: index.php');
    exit();
}
?>