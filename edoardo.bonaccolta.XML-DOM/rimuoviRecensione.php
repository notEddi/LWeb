<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: areaUtente.php");
    exit();
}

$id = intval($_GET['id']);

eliminaRecensioneById($id);

header("Location: areaUtente.php");
exit();
?>
