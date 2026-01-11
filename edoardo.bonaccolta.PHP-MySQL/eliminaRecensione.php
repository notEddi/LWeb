<?php
session_start();
require_once 'connection.php';

// Verifica che l'utente sia loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verifica che l'ID della recensione sia stato passato
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: areaUtente.php');
    exit();
}

$recensione_id = intval($_GET['id']);
$utente_id = $_SESSION['user_id'];

// Verifica che la recensione appartenga all'utente
$query_check = "SELECT film_id FROM " . T_RECENSIONI . " WHERE id = ? AND utente_id = ?";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bind_param("ii", $recensione_id, $utente_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    // Recensione non trovata o non appartiene all'utente
    header('Location: areaUtente.php');
    exit();
}

$recensione_data = $result_check->fetch_assoc();
$film_id = $recensione_data['film_id'];
$stmt_check->close();

// Elimina la recensione
$query_elimina = "DELETE FROM " . T_RECENSIONI . " WHERE id = ? AND utente_id = ?";
$stmt_elimina = $conn->prepare($query_elimina);
$stmt_elimina->bind_param("ii", $recensione_id, $utente_id);

if ($stmt_elimina->execute()) {
    $_SESSION['successo'] = "Recensione eliminata con successo!";
} else {
    $_SESSION['errore'] = "Errore durante l'eliminazione: " . $conn->error;
}

$stmt_elimina->close();

// Reindirizza all'area utente
header('Location: areaUtente.php');
exit();
?>