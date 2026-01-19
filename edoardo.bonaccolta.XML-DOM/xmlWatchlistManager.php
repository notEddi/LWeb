<?php
// xmlWatchlistManager.php

if (!defined('WATCH_XML')) {
    define('WATCH_XML', __DIR__ . '/xml/watchlist.xml');
}
if (!defined('WATCH_XSD')) {
    define('WATCH_XSD', __DIR__ . '/xml/watchlist.xsd');
}

function loadWatchlistDOM() {
    if (!file_exists(WATCH_XML)) {
        error_log("loadWatchlistDOM: file non trovato: " . WATCH_XML);
        return false;
    }

    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;

    if (!$dom->load(WATCH_XML)) {
        error_log("loadWatchlistDOM: parse error");
        return false;
    }

    if (file_exists(WATCH_XSD)) {
        if (!$dom->schemaValidate(WATCH_XSD)) {
            error_log("loadWatchlistDOM: XML non valido rispetto a " . WATCH_XSD);
        }
    }

    return $dom;
}

function isFilmInWatchlist($utente_id, $film_id) {
    $dom = loadWatchlistDOM();
    if (!$dom) return false;

    foreach ($dom->getElementsByTagName("item") as $n) {
        if ($n->getAttribute("utente_id") == $utente_id &&
            $n->getAttribute("film_id") == $film_id) {
            return true;
        }
    }
    return false;
}

function getWatchlistArray() {
    $dom = loadWatchlistDOM();
    if ($dom === false) return [];

    $out = [];

    foreach ($dom->getElementsByTagName("item") as $it) {

        $data = $it->getAttribute("data_aggiunta");
        if ($data === "" || $data === null) {
            // fallback per compatibilità
            $data = "1970-01-01 00:00";
        }

        $out[] = [
            "utente_id"     => $it->getAttribute("utente_id"),
            "film_id"       => $it->getAttribute("film_id"),
            "data_aggiunta" => $data
        ];
    }

    return $out;
}


/*
 * Watchlist di un singolo utente
 */
function getWatchlistPerUtente($utente_id) {
    return array_values(array_filter(
        getWatchlistArray(),
        fn($w) => (string)$w["utente_id"] === (string)$utente_id
    ));
}

/*
 * Conteggio utenti che hanno un film in watchlist
 */
function contaWatchlistPerFilm($film_id) {
    $count = 0;
    foreach (getWatchlistArray() as $w) {
        if ((string)$w["film_id"] === (string)$film_id) $count++;
    }
    return $count;
}

function aggiungiWatchlistXML($utente_id, $film_id) {
    $dom = loadWatchlistDOM();

    // Se il file non esiste → creiamo root
    if (!$dom) {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $root = $dom->createElement("watchlist");
        $dom->appendChild($root);
    } else {
        $root = $dom->documentElement;
    }

    // Evita duplicati (utente_id + film_id)
    foreach ($dom->getElementsByTagName("item") as $n) {
        if ($n->getAttribute("utente_id") == $utente_id &&
            $n->getAttribute("film_id") == $film_id) {
            return true; // già presente
        }
    }

    // ✨ Nodo senza ID (conforme allo XSD)
    $item = $dom->createElement("item");
    $item->setAttribute("utente_id", $utente_id);
    $item->setAttribute("film_id", $film_id);
    $item->setAttribute("data_aggiunta", date("Y-m-d H:i:s"));

    $root->appendChild($item);

    // Validazione XSD (solo logging)
    if (file_exists(WATCH_XSD)) {
        if (!$dom->schemaValidate(WATCH_XSD)) {
            error_log("aggiungiWatchlistXML: validazione XSD fallita");
        }
    }

    return $dom->save(WATCH_XML);
}


/*
 * Rimuove elemento dalla watchlist
 */
function rimuoviFromWatchlistXML($utente_id, $film_id) {
    $dom = loadWatchlistDOM();
    if ($dom === false) return false;

    foreach ($dom->getElementsByTagName("item") as $it) {
        if ($it->getAttribute("utente_id") == $utente_id &&
            $it->getAttribute("film_id") == $film_id) {

            $it->parentNode->removeChild($it);

            if (file_exists(WATCH_XSD) && !$dom->schemaValidate(WATCH_XSD)) {
                error_log("rimuoviFromWatchlistXML: validazione fallita dopo rimozione");
            }

            return $dom->save(WATCH_XML);
        }
    }

    return true;
}

/**
 * Elimina TUTTI gli item di watchlist associati a un certo film
 */
function eliminaWatchlistByFilm($film_id) {
    $dom = loadWatchlistDOM();
    if ($dom === false) return false;

    $changed = false;

    foreach ($dom->getElementsByTagName("item") as $it) {
        if ((string)$it->getAttribute("film_id") === (string)$film_id) {
            $it->parentNode->removeChild($it);
            $changed = true;
        }
    }

    if ($changed) {
        if (file_exists(WATCH_XSD) && !$dom->schemaValidate(WATCH_XSD)) {
            error_log("eliminaWatchlistByFilm: validazione XSD fallita dopo rimozione item film_id=$film_id");
        }
        return $dom->save(WATCH_XML);
    }

    // Nessuna occorrenza da rimuovere, va bene lo stesso
    return true;
}

function getTotaleWatchlistXML() {
    $dom = loadWatchlistDOM();
    if (!$dom) return 0;
    return $dom->getElementsByTagName("item")->length;
}

function getWatchlistByUtenteSorted($utente_id) {
    $items = getWatchlistPerUtente($utente_id);
    
    // Ordina per data_aggiunta decrescente
    usort($items, function($a, $b) {
        return strcmp($b['data_aggiunta'], $a['data_aggiunta']);
    });
    
    return $items;
}
