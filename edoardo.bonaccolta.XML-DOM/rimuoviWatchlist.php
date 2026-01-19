<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['film_id'])) {
    header("Location: listaFilm.php");
    exit();
}

$utente_id = $_SESSION['user_id'];
$film_id   = intval($_POST['film_id']);

rimuoviFromWatchlistXML($utente_id, $film_id);

header("Location: film.php?id=" . $film_id);
exit();
?>
