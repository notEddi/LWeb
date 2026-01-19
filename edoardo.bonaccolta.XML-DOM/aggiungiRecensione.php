<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['film_id'], $_POST['voto'], $_POST['testo'])) {
    header("Location: listaFilm.php");
    exit();
}

$utente_id = $_SESSION['user_id'];
$film_id   = intval($_POST['film_id']);
$voto      = intval($_POST['voto']);
$testo     = trim($_POST['testo']);
$data      = date("Y-m-d H:i");

// Salva
addRecensioneXML($utente_id, $film_id, $voto, $testo, $data);

// Torna alla scheda film
header("Location: film.php?id=" . $film_id);
exit();
?>
