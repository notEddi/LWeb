<?php
require_once 'config.php';

// Utente non loggato → redirect login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['film_id'])) {
    header("Location: listaFilm.php");
    exit();
}

// Dati
$utente_id = $_SESSION['user_id'];
$film_id   = intval($_POST['film_id']);

// Evita duplicati
if (!isFilmInWatchlist($utente_id, $film_id)) {
    aggiungiWatchlistXML($utente_id, $film_id);
}

// Torna alla pagina film
header("Location: film.php?id=" . $film_id);
exit();
?>
