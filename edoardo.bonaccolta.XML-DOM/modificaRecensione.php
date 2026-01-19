<?php
require_once 'config.php';

// Utente non loggato → redirect
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$utente_id = $_SESSION['user_id'];

// ID recensione mancante o non numerico
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: areaUtente.php');
    exit();
}

$recensione_id = intval($_GET['id']);

// RECUPERO RECENSIONE DA XML
$rec = getRecensioneByIdAndUtente($recensione_id, $utente_id);

if (!$rec) {
    // Recensione non trovata o non appartiene all’utente
    header('Location: areaUtente.php');
    exit();
}

// Recupera info film
$film = getFilmArrayById($rec['film_id']);
$titolo_film = $film ? $film['titolo'] : "Film";

$errori = [];

// AGGIORNAMENTO RECENSIONE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $voto = isset($_POST['voto']) ? intval($_POST['voto']) : 0;
    $testo = trim($_POST['testo']);

    // Validazione
    if ($voto < 1 || $voto > 5) {
        $errori[] = "Voto non valido. Deve essere tra 1 e 5.";
    }

    if ($testo === "") {
        $errori[] = "Il testo della recensione non può essere vuoto.";
    }

    // Se tutto ok → aggiorno nel file XML
    if (empty($errori)) {

        if (aggiornaRecensioneXML($recensione_id, $voto, $testo)) {
            $_SESSION['successo'] = "Recensione aggiornata con successo!";
            header('Location: areaUtente.php');
            exit();
        } else {
            $errori[] = "Errore durante il salvataggio nel file XML.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Recensione - Recensioni Cinematografiche</title>
    <link rel="stylesheet" href="https://unpkg.com/98.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="window">
    <div class="title-bar">
        <div class="title-bar-text">Modifica Recensione - <?php echo htmlspecialchars($titolo_film); ?></div>
    </div>

    <div class="window-body">
        <?php include 'header.php'; ?>
        <div class="window-body">

            <div class="window">
                <div class="title-bar">
                    <div class="title-bar-text">Modifica la tua recensione</div>
                </div>

                <div class="window-body">

                    <?php if (!empty($errori)): ?>
                        <div style="color:red; margin-bottom:10px;">
                            <?php foreach($errori as $e) echo "<p>$e</p>"; ?>
                        </div>
                    <?php endif; ?>

                    <form action="modificaRecensione.php?id=<?php echo $recensione_id; ?>" method="POST">

                        <label for="voto">Voto:</label>
                        <select name="voto" id="voto" required>
                            <option value="">Seleziona un voto</option>
                            <option value="5" <?= $rec['voto']==5?'selected':''; ?>>5 - Incredible!</option>
                            <option value="4" <?= $rec['voto']==4?'selected':''; ?>>4 - Great!</option>
                            <option value="3" <?= $rec['voto']==3?'selected':''; ?>>3 - Pretty good</option>
                            <option value="2" <?= $rec['voto']==2?'selected':''; ?>>2 - Not so great</option>
                            <option value="1" <?= $rec['voto']==1?'selected':''; ?>>1 - Unfortunate</option>
                        </select>

                        <div class="field-row-stacked" style="margin-top: 10px;">
                            <label for="testo">Testo della recensione:</label>
                            <textarea id="testo" name="testo" rows="6" required class="textarea-win98"><?=
                                htmlspecialchars($rec['testo'])
                            ?></textarea>
                        </div>

                        <div style="margin-top: 15px;">
                            <button type="submit" class="button">Aggiorna Recensione</button>
                            <a href="areaUtente.php" class="button">Annulla</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
