<?php
// config.php
session_start();
require_once 'connection.php';

// Controllo se l'utente è loggato e se è admin
$is_logged = isset($_SESSION['user_id']);
$is_admin = false;
$username = '';

if ($is_logged) {
    $username = $_SESSION['username'];
    
    // Controlla se è admin (solo se non l'abbiamo già fatto in questa sessione)
    if (!isset($_SESSION['is_admin'])) {
        $stmt = $conn->prepare("SELECT is_admin FROM " . T_UTENTI . " WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $_SESSION['is_admin'] = $user_data['is_admin'];
        $stmt->close();
    }
    
    $is_admin = $_SESSION['is_admin'];
}
?>