<?php
// xmlFilmManager.php
// Gestione film tramite XML + XSD, con schemaValidate e solo logging

if (!defined('FILM_XML')) {
    define('FILM_XML', __DIR__ . '/xml/film.xml');
}
if (!defined('FILM_XSD')) {
    define('FILM_XSD', __DIR__ . '/xml/film.xsd');
}

/*
 * Carica e valida il DOM. Logging in caso di errore, MA NON si ferma l'esecuzione.
 */
function loadFilmDOM() {
    if (!file_exists(FILM_XML)) {
        error_log("loadFilmDOM: file non trovato: " . FILM_XML);
        return false;
    }

    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;

    if (!$dom->load(FILM_XML)) {
        error_log("loadFilmDOM: parse error in " . FILM_XML);
        return false;
    }

    if (file_exists(FILM_XSD)) {
        if (!$dom->schemaValidate(FILM_XSD)) {
            error_log("loadFilmDOM: XML non valido rispetto allo schema " . FILM_XSD);
            // NON fermiamo il sito
        }
    }

    return $dom;
}

/*
 * Ritorna DOMNodeList di <film>
 */
function getFilmNodi() {
    $dom = loadFilmDOM();
    if ($dom === false) return [];
    return $dom->getElementsByTagName("film");
}

/*
 * Ritorna TUTTI i film come array associativi
 */
function getFilmArray() {
    $nodes = getFilmNodi();
    $out = [];

    foreach ($nodes as $f) {
        $id = $f->getAttribute("id");

        $titolo  = $f->getElementsByTagName("titolo")->item(0)?->textContent ?? "";
        $regista = $f->getElementsByTagName("regista")->item(0)?->textContent ?? "";
        $anno    = $f->getElementsByTagName("anno")->item(0)?->textContent ?? "";
        $durata  = $f->getElementsByTagName("durata")->item(0)?->textContent ?? "";
        $trama   = $f->getElementsByTagName("trama")->item(0)?->textContent ?? "";
        $poster  = $f->getElementsByTagName("poster")->item(0)?->textContent ?? "";

        $out[] = [
            "id"      => $id,
            "titolo"  => $titolo,
            "regista" => $regista,
            "anno"    => $anno,
            "durata"  => $durata,
            "trama"   => $trama,
            "poster"  => $poster
        ];
    }

    return $out;
}

/*
 * Ritorna tutti i film ordinati per ID crescente (usato da listaFilm.php)
 */
function getAllFilms() {
    $arr = getFilmArray();
    usort($arr, fn($a, $b) => intval($a['id']) - intval($b['id']));
    return $arr;
}


/*
 * Ritorna un film tramite id
 */
function getFilmArrayById($id_cercato) {
    foreach (getFilmArray() as $f) {
        if ((string)$f["id"] === (string)$id_cercato) {
            return $f;
        }
    }
    return null;
}

/*
 * Ritorna ultimi N film ordinati per id discendente
 */
function getUltimiFilmArray() {
    $all = getFilmArray();
    usort($all, fn($a, $b) => intval($b["id"]) - intval($a["id"]));
    return array_slice($all, 0);
}

/*
 * Aggiunge un film a film.xml
 */
function aggiungiFilmXML($titolo, $regista, $anno, $durata, $trama, $poster) {
    $dom = loadFilmDOM();
    if ($dom === false) return false;

    $root = $dom->getElementsByTagName("film_collection")->item(0);
    if (!$root) return false;

    $maxId = 0;
    foreach ($dom->getElementsByTagName("film") as $f) {
        $id = intval($f->getAttribute("id"));
        if ($id > $maxId) $maxId = $id;
    }
    $newId = $maxId + 1;

    $film = $dom->createElement("film");
    $film->setAttribute("id", $newId);

    $film->appendChild($dom->createElement("titolo", $titolo));
    $film->appendChild($dom->createElement("regista", $regista));
    $film->appendChild($dom->createElement("anno", $anno));
    $film->appendChild($dom->createElement("durata", $durata));
    $film->appendChild($dom->createElement("trama", $trama));
    $film->appendChild($dom->createElement("poster", $poster));

    $root->appendChild($film);

    // Validazione finale prima del salvataggio
    if (file_exists(FILM_XSD) && !$dom->schemaValidate(FILM_XSD)) {
        error_log("aggiungiFilmXML: validazione XSD fallita PRIMA del salvataggio (" . FILM_XSD . ")");
        // continuiamo comunque
    }

    return $dom->save(FILM_XML);
}

/*
 * Elimina un film tramite id
 */
function eliminaFilmXML($id_cercato) {
    $dom = loadFilmDOM();
    if ($dom === false) return false;

    foreach ($dom->getElementsByTagName("film") as $f) {
        if ((string)$f->getAttribute("id") === (string)$id_cercato) {
            $f->parentNode->removeChild($f);

            if (file_exists(FILM_XSD) && !$dom->schemaValidate(FILM_XSD)) {
                error_log("eliminaFilmXML: validazione fallita dopo rimozione");
            }

            return $dom->save(FILM_XML);
        }
    }

    return false;
}
