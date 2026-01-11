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
    // Validazione dati
    $film_id = isset($_POST['film_id']) ? intval($_POST['film_id']) : 0;
    $voto = isset($_POST['voto']) ? intval($_POST['voto']) : 0;
    $testo = isset($_POST['testo']) ? trim($_POST['testo']) : '';
    
    // Validazione
    $errori = [];
    
    if ($film_id <= 0) {
        $errori[] = "Film non valido.";
    }
    
    if ($voto < 1 || $voto > 5) {
        $errori[] = "Voto non valido. Deve essere tra 1 e 5.";
    }
    
    if (empty($testo)) {
        $errori[] = "Il testo della recensione è obbligatorio.";
    }
    
    // Se non ci sono errori, inserisci la recensione
    if (empty($errori)) {
        $utente_id = $_SESSION['user_id'];
        
        // Verifica se l'utente ha già recensito questo film
        $query_check = "SELECT id FROM " . T_RECENSIONI . " WHERE film_id = ? AND utente_id = ?";
        $stmt_check = $conn->prepare($query_check);
        $stmt_check->bind_param("ii", $film_id, $utente_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $_SESSION['errore'] = "Hai già recensito questo film.";
        } else {
            // Inserisci la nuova recensione
            $query_inserisci = "INSERT INTO " . T_RECENSIONI . " (film_id, utente_id, voto, testo) VALUES (?, ?, ?, ?)";
            $stmt_inserisci = $conn->prepare($query_inserisci);
            $stmt_inserisci->bind_param("iiis", $film_id, $utente_id, $voto, $testo);
            
            if ($stmt_inserisci->execute()) {
                $_SESSION['successo'] = "Recensione aggiunta con successo!";
            } else {
                $_SESSION['errore'] = "Errore durante l'aggiunta della recensione: " . $conn->error;
            }
            
            $stmt_inserisci->close();
        }
        
        $stmt_check->close();
    } else {
        $_SESSION['errore'] = implode("<br>", $errori);
    }
    
    // Reindirizza alla pagina del film
    header('Location: film.php?id=' . $film_id);
    exit();
} else {
    // Se il form non è stato inviato con POST, reindirizza alla home
    header('Location: index.php');
    exit();
}
?>