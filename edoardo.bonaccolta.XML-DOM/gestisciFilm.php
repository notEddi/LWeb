<?php
require_once 'config.php';

// Verifica admin
if (!$is_logged || !$is_admin) {
    header('Location: index.php');
    exit();
}

// Manager XML già caricati da config.php:
// - getFilmArray()
// - eliminaFilmXML($id)
// - contaRecensioniByFilm($id)
// - contaWatchlistPerFilm($id)

// Gestione eliminazione film
$success_message = "";
$error_message = "";

if (isset($_POST['elimina_film'])) {

    $film_id = intval($_POST['film_id']);

    // Recupera dati film per eliminare poster
    $film = getFilmArrayById($film_id);

    if (!$film) {
        $error_message = "Film non trovato nel file XML.";
    } else {

        // 1) Elimina il film da film.xml
        if (eliminaFilmXML($film_id)) {

            // 2) Elimina recensioni associate
            eliminaRecensioniByFilm($film_id);

            // 3) Elimina watchlist associate
            eliminaWatchlistByFilm($film_id);

            // 4) Elimina poster fisico
            if (!empty($film['poster']) && $film['poster'] !== 'default.jpg') {
                $file_path = 'poster/' . $film['poster'];
                if (file_exists($file_path)) unlink($file_path);
            }

            $success_message = "Film eliminato con successo!";

        } else {
            $error_message = "Errore nell'eliminazione del film dal file XML.";
        }
    }
}

// Carichiamo l'intera lista film da XML
$film_list = getUltimiFilmArray();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestisci Film - Admin</title>
    <link rel="stylesheet" href="https://unpkg.com/98.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style3.css">
</head>
<body>
<div class="window">
    <div class="title-bar">
        <div class="title-bar-text">Gestisci Film - Pannello Admin</div>
    </div>

    <div class="window-body">

        <header>
            <div class="window">
                <div class="tab-container">
                    <a href="index.php" class="tab">Home</a>
                    <a href="listaFilm.php" class="tab">Lista Film</a>
                    <a href="areaUtente.php" class="tab">Pannello Admin</a>
                    <a href="logout.php" class="tab">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
                </div>
            </div>
        </header>

        <div class="window-body">

            <?php
            if ($success_message) {
                echo '<div class="window" style="margin-bottom: 15px;">
                        <div class="title-bar">
                            <div class="title-bar-text">Successo</div>
                        </div>
                        <div class="window-body">
                            <div style="color: green;">' . $success_message . '</div>
                        </div>
                      </div>';
            }
            if ($error_message) {
                echo '<div class="window" style="margin-bottom: 15px;">
                        <div class="title-bar">
                            <div class="title-bar-text">Errore</div>
                        </div>
                        <div class="window-body">
                            <div style="color: red;">' . $error_message . '</div>
                        </div>
                      </div>';
            }
            ?>

            <div class="window" style="margin-bottom: 20px;">
                <div class="title-bar">
                    <div class="title-bar-text">Lista Film (<?php echo count($film_list); ?> totali)</div>
                </div>

                <div class="window-body">

                    <?php
                    if (!empty($film_list)) {

                        foreach ($film_list as $film) {

                            $poster_path = !empty($film['poster'])
                                           ? 'poster/' . $film['poster']
                                           : 'poster/default.jpg';

                            // Ricaviamo contatori da XML
                            $num_rec = contaRecensioniByFilm($film['id']);
                            $num_wl  = contaWatchlistPerFilm($film['id']);

                            echo '
                            <div class="film-card">

                                <div class="film-header">

                                    <div class="film-info">
                                        <h4 style="margin: 0 0 5px 0;">
                                            ' . htmlspecialchars($film['titolo']) . ' (' . $film['anno'] . ')
                                        </h4>

                                        <p style="margin: 0 0 3px 0; font-size: 13px;">
                                            <strong>Regista:</strong> ' . htmlspecialchars($film['regista']) . ' |
                                            <strong>Durata:</strong> ' . $film['durata'] . ' minuti
                                        </p>

                                        <p style="margin: 0; font-size: 12px; color: #666;">
                                            ' . substr(htmlspecialchars($film['trama']), 0, 200) . '...
                                        </p>

                                        <div class="stats-grid">
                                            <div class="stat-item">
                                                <strong>Recensioni</strong><br>
                                                ' . $num_rec . '
                                            </div>
                                            <div class="stat-item">
                                                <strong>Watchlist</strong><br>
                                                ' . $num_wl . '
                                            </div>
                                            <div class="stat-item">
                                                <strong>ID Film</strong><br>
                                                #' . $film['id'] . '
                                            </div>
                                        </div>
                                    </div>

                                    <img src="' . $poster_path . '" alt="' . htmlspecialchars($film['titolo']) . '" class="film-poster-small">
                                </div>

                                <div class="film-actions">
                                    <a href="film.php?id=' . $film['id'] . '" class="button" style="font-size: 12px; padding: 2px 6px;">👁️ Vedi Film</a>

                                    <form action="gestisciFilm.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="film_id" value="' . $film['id'] . '">

                                        <button type="submit" name="elimina_film"
                                            class="button"
                                            style="font-size: 12px; padding: 2px 6px; background-color: #c00; color: white;"
                                            onclick="return confirm(\'⚠️ SEI SICURO DI VOLER ELIMINARE QUESTO FILM?\\n\\n' . addslashes($film['titolo']) . ' (' . $film['anno'] . ')\\n\\nQuesta azione eliminerà:\\n- ' . $num_rec . ' recensioni\\n- ' . $num_wl . ' elementi dalle watchlist\\n\\nL\\\'azione è IRREVERSIBILE!\')">
                                            🗑️ Elimina Film
                                        </button>
                                    </form>
                                </div>

                            </div>';
                        }

                    } else {
                        echo '
                        <div class="window">
                            <div class="title-bar">
                                <div class="title-bar-text">Info</div>
                            </div>
                            <div class="window-body">
                                <p>Nessun film presente.</p>
                                <p><a href="aggiungiFilm.php" class="button">➕ Aggiungi il primo film</a></p>
                            </div>
                        </div>';
                    }
                    ?>

                </div>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <a href="aggiungiFilm.php" class="button">➕ Aggiungi Nuovo Film</a>
                <a href="areaUtente.php" class="button">← Torna al Pannello Admin</a>
            </div>

            <div class="window" style="margin-top: 20px;">
                <div class="title-bar">
                    <div class="title-bar-text">⚠️ Avviso Importante</div>
                </div>
                <div class="window-body">
                    <p style="font-size: 13px; margin: 0;">
                        <strong>L'eliminazione di un film è un'azione permanente che cancellerà:</strong><br>
                        - Tutte le recensioni associate al film<br>
                        - Tutte le occorrenze nelle watchlist<br>
                        - Il file del poster (se presente)<br>
                        <em>Questa operazione non può essere annullata!</em>
                    </p>
                </div>
            </div>

        </div>

    </div>

</div>
</body>
</html>
