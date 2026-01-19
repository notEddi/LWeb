<?php
// reset.php — reset totale del sito (XML + SQL utenti)

session_start();

$XML_DIR = __DIR__ . "/xml/";

// File XML e XSD da cancellare
$files = [
    $XML_DIR . "film.xml",
    $XML_DIR . "recensioni.xml",
    $XML_DIR . "watchlist.xml",
    $XML_DIR . "film.xsd",
    $XML_DIR . "recensioni.xsd",
    $XML_DIR . "watchlist.xsd"
];

// Cancella file XML/XSD
foreach ($files as $f) {
    if (file_exists($f)) {
        unlink($f);
    }
}

// Reset del database SQL (solo utenti)
require_once "connection.php";

// Distrugge completamente il DB esistente
$conn->query("DROP DATABASE IF EXISTS edoardo_bonaccolta_xml_dom");

// Logout dell’utente
session_destroy();

// Dopo aver cancellato tutto → reindirizza all’installer
header("Location: install.php");
exit();
?>
