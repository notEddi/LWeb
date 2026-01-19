<?php
// xmlRecensioniManager.php
// Gestione recensioni con XML + XSD + schemaValidate (solo logging)

if (!defined('REC_XML')) {
    define('REC_XML', __DIR__ . '/xml/recensioni.xml');
}
if (!defined('REC_XSD')) {
    define('REC_XSD', __DIR__ . '/xml/recensioni.xsd');
}

/*
 * Carica il DOM e applica schemaValidate.
 * In caso di errori → log, MA NON ferma il sito.
 */
function loadRecensioniDOM() {
    if (!file_exists(REC_XML)) {
        error_log("loadRecensioniDOM: file non trovato: " . REC_XML);
        return false;
    }

    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;

    if (!$dom->load(REC_XML)) {
        error_log("loadRecensioniDOM: parse error in " . REC_XML);
        return false;
    }

    if (file_exists(REC_XSD)) {
        if (!$dom->schemaValidate(REC_XSD)) {
            error_log("loadRecensioniDOM: XML non valido rispetto a " . REC_XSD);
        }
    }

    return $dom;
}

/*
 * Ritorna tutti i nodi <recensione>
 */
function getRecensioniNodi() {
    $dom = loadRecensioniDOM();
    if ($dom === false) return [];
    return $dom->getElementsByTagName("recensione");
}

/*
 * Ritorna TUTTE le recensioni come array associativi
 */
function getRecensioniArray() {
    $nodes = getRecensioniNodi();
    $out = [];

    foreach ($nodes as $n) {
        $out[] = [
            "id"        => $n->getAttribute("id"),
            "utente_id" => $n->getAttribute("utente_id"),
            "film_id"   => $n->getAttribute("film_id"),
            "voto"      => $n->getElementsByTagName("voto")->item(0)?->textContent ?? "",
            "data"      => $n->getElementsByTagName("data")->item(0)?->textContent ?? "",
            "testo"     => $n->getElementsByTagName("testo")->item(0)?->textContent ?? ""
        ];
    }

    return $out;
}

/*
 * Ritorna le recensioni per un film
 */
function getRecensioniArrayPerFilm($film_id) {
    return array_values(array_filter(
        getRecensioniArray(),
        fn($r) => (string)$r["film_id"] === (string)$film_id
    ));
}

/*
 * Media voti di un film
 */
function getMediaVotiFilm($film_id) {
    $rec = getRecensioniArrayPerFilm($film_id);
    if (count($rec) === 0) return 0.0;

    $sum = 0;
    foreach ($rec as $r) {
        if (is_numeric($r["voto"])) {
            $sum += floatval($r["voto"]);
        }
    }

    return $sum > 0 ? ($sum / count($rec)) : 0.0;
}

/*
 * Numero recensioni film
 */
function getNumeroRecensioniFilm($film_id) {
    return count(getRecensioniArrayPerFilm($film_id));
}

/*
 Ritorna una recensione specifica SOLO se appartiene all'utente indicato.
 */
function getRecensioneByIdAndUtente($recensione_id, $utente_id) {

    $dom = loadRecensioniDOM();
    if ($dom === false) return null;

    foreach ($dom->getElementsByTagName("recensione") as $n) {

        $id_xml        = $n->getAttribute("id");
        $utente_xml    = $n->getAttribute("utente_id");

        if ((string)$id_xml === (string)$recensione_id &&
            (string)$utente_xml === (string)$utente_id) {

            // Estrazione voto
            $vnode = $n->getElementsByTagName("voto")->item(0);
            $voto = $vnode ? trim($vnode->textContent) : "";

            // Estrazione testo
            $tnode = $n->getElementsByTagName("testo")->item(0);
            $testo = $tnode ? trim($tnode->textContent) : "";

            // Estrazione data
            $dnode = $n->getElementsByTagName("data")->item(0);
            $data = $dnode ? trim($dnode->textContent) : "";

            return [
                "id"        => $id_xml,
                "utente_id" => $utente_xml,
                "film_id"   => $n->getAttribute("film_id"),
                "voto"      => $voto,
                "testo"     => $testo,
                "data"      => $data,
                "node"      => $n
            ];
        }
    }

    return null;
}


/*
 * Aggiunge una recensione
 */
function addRecensioneXML($utente_id, $film_id, $voto, $testo) {
    $dom = loadRecensioniDOM();
    if ($dom === false) return false;

    $root = $dom->getElementsByTagName("recensioni")->item(0);
    if (!$root) return false;

    // Calcola nuovo id
    $maxId = 0;
    foreach ($dom->getElementsByTagName("recensione") as $r) {
        $id = intval($r->getAttribute("id"));
        if ($id > $maxId) $maxId = $id;
    }
    $newId = $maxId + 1;

    // Creazione nodo
    $rec = $dom->createElement("recensione");
    $rec->setAttribute("id", $newId);
    $rec->setAttribute("utente_id", $utente_id);
    $rec->setAttribute("film_id", $film_id);

    $rec->appendChild($dom->createElement("voto", $voto));
    $rec->appendChild($dom->createElement("data", date("Y-m-d H:i:s")));
    $rec->appendChild($dom->createElement("testo", $testo));

    $root->appendChild($rec);

    // Validazione dopo la modifica
    if (file_exists(REC_XSD) && !$dom->schemaValidate(REC_XSD)) {
        error_log("addRecensioneXML: validazione XSD fallita prima del salvataggio");
    }

    return $dom->save(REC_XML);
}

/*
 * Modifica una recensione
 */
function aggiornaRecensioneXML($id, $voto, $testo) {
    $dom = loadRecensioniDOM();
    if ($dom === false) return false;

    foreach ($dom->getElementsByTagName("recensione") as $n) {
        if ((string)$n->getAttribute("id") === (string)$id) {

            $n->getElementsByTagName("voto")->item(0)->textContent = $voto;
            $n->getElementsByTagName("testo")->item(0)->textContent = $testo;
            $n->getElementsByTagName("data")->item(0)->textContent = date("Y-m-d H:i:s");

            if (file_exists(REC_XSD) && !$dom->schemaValidate(REC_XSD)) {
                error_log("aggiornaRecensioneXML: validazione XSD fallita dopo modifica");
            }

            return $dom->save(REC_XML);
        }
    }

    return false;
}

/*
 * Elimina una recensione tramite id
 */
function eliminaRecensioneById($id) {
    $dom = loadRecensioniDOM();
    if ($dom === false) return false;

    foreach ($dom->getElementsByTagName("recensione") as $n) {
        if ((string)$n->getAttribute("id") === (string)$id) {

            $n->parentNode->removeChild($n);

            if (file_exists(REC_XSD) && !$dom->schemaValidate(REC_XSD)) {
                error_log("eliminaRecensioneById: validazione XSD fallita dopo rimozione");
            }

            return $dom->save(REC_XML);
        }
    }

    return false;
}

function getRecensioniByUtente($utente_id) {
    $dom = loadRecensioniDOM(false);
    if (!$dom) return [];

    $out = [];
    foreach ($dom->getElementsByTagName("recensione") as $n) {
        if ($n->getAttribute("utente_id") == $utente_id) {

            $id = $n->getAttribute("id") ?: "";
            $film_id = $n->getAttribute("film_id") ?: "";

            $voto = $n->getElementsByTagName("voto")->item(0)?->textContent ?? "";
            $data = $n->getElementsByTagName("data")->item(0)?->textContent ?? "";
            $testo = $n->getElementsByTagName("testo")->item(0)?->textContent ?? "";

            $out[] = [
                "id"       => $id,
                "film_id"  => $film_id,
                "voto"     => $voto,
                "data"     => $data,
                "testo"    => $testo,
                "node"     => $n
            ];
        }
    }

    // 🔥 ORDINIAMO PER DATA DESC (più recente prima)
    usort($out, function($a, $b) {
        return strtotime($b["data"]) - strtotime($a["data"]);
    });

    return $out;
}


/**
 * Alias comodo: conta le recensioni di un film (usato in gestisciFilm.php)
 */
function contaRecensioniByFilm($film_id) {
    return getNumeroRecensioniFilm($film_id);
}

/**
 * Elimina tutte le recensioni associate a un certo film
 */
function eliminaRecensioniByFilm($film_id) {
    $dom = loadRecensioniDOM();
    if ($dom === false) return false;

    $changed = false;

    foreach ($dom->getElementsByTagName("recensione") as $n) {
        if ((string)$n->getAttribute("film_id") === (string)$film_id) {
            $n->parentNode->removeChild($n);
            $changed = true;
        }
    }

    if ($changed) {
        if (file_exists(REC_XSD) && !$dom->schemaValidate(REC_XSD)) {
            error_log("eliminaRecensioniByFilm: validazione XSD fallita dopo rimozione recensioni film_id=$film_id");
        }
        return $dom->save(REC_XML);
    }

    // Nessuna recensione da rimuovere, ma non è un errore
    return true;
}

function getTotaleRecensioniXML() {
    $dom = loadRecensioniDOM(false);
    if (!$dom) return 0;
    return $dom->getElementsByTagName("recensione")->length;
}
