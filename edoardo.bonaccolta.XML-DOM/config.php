<?php
// config.php
session_start();

// Connessione SQL solo per utenti
require_once 'connection.php';

// Manager XML sempre disponibili
require_once 'xmlFilmManager.php';
require_once 'xmlRecensioniManager.php';
require_once 'xmlWatchlistManager.php';

// Stato login
$is_logged = isset($_SESSION['user_id']);
$is_admin = false;
$username = "";

// Se loggato recuperiamo info utente
if ($is_logged) {

    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    // Recupero ruolo admin dal DB
    if (!isset($_SESSION['is_admin'])) {

        $stmt = $conn->prepare("SELECT is_admin FROM " . T_UTENTI . " WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['is_admin'] = intval($row['is_admin']) === 1;
        } else {
            $_SESSION['is_admin'] = false;
        }

        $stmt->close();
    }

    $is_admin = $_SESSION['is_admin'];
}
?>
