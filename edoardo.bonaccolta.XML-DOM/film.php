<?php
require_once 'config.php';


// Verifica parametro id film
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listaFilm.php');
    exit();
}

$film_id = intval($_GET['id']);

// Carichiamo il film da film.xml
$film = getFilmArrayById($film_id);

if (!$film) {
    header('Location: listaFilm.php');
    exit();
}

// Recuperiamo recensioni e media voti da recensioni.xml
$recensioni = getRecensioniArrayPerFilm($film_id);
$media_voti = getMediaVotiFilm($film_id);
$media_voti = round($media_voti, 1);

$stelle = str_repeat('⭐', round($media_voti));

// Controllo watchlist XML (solo se utente loggato)
$in_watchlist = false;

if (isset($_SESSION['user_id'])) {
    $wl = getWatchlistPerUtente($_SESSION['user_id']);
    foreach ($wl as $item) {
        if ($item['film_id'] == $film_id) {
            $in_watchlist = true;
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($film['titolo']); ?> - Recensioni Cinematografiche</title>
    <link rel="stylesheet" href="https://unpkg.com/98.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="window">
        <div class="title-bar">
            <div class="title-bar-text"><?php echo htmlspecialchars($film['titolo']); ?></div>
        </div>

        <div class="window-body">
            <?php include 'header.php'; ?>

            <div class="film-container">
                <div class="film-poster">
                    <?php
                    $poster_path = !empty($film['poster']) ? 'poster/' . $film['poster'] : 'poster/default.jpg';
                    echo '<img src="' . $poster_path . '" alt="' . htmlspecialchars($film['titolo']) . '" width="230" height="345">';
                    ?>
                </div>

                <div class="film-info">
                    <h2><?php echo htmlspecialchars($film['titolo']); ?></h2>

                    <p><strong>Valutazione media:</strong>
                        <?php echo $stelle; ?> (<?php echo $media_voti; ?>/5)
                        da <?php echo count($recensioni); ?> recensioni
                    </p>

                    <p><strong>Trama:</strong> <?php echo htmlspecialchars($film['trama']); ?></p>
                    <p><strong>Regista:</strong> <?php echo htmlspecialchars($film['regista']); ?></p>
                    <p><strong>Anno di uscita:</strong> <?php echo $film['anno']; ?></p>
                    <p><strong>Durata:</strong> <?php echo $film['durata']; ?> minuti</p>

                    <?php if (isset($_SESSION['user_id'])): ?>

                        <?php if (!$in_watchlist): ?>
                            <form action="aggiungiWatchlist.php" method="POST" style="display: inline;">
                                <input type="hidden" name="film_id" value="<?php echo $film_id; ?>">
                                <button type="submit" class="button">➕ Aggiungi alla Watchlist</button>
                            </form>
                        <?php else: ?>
                            <form action="rimuoviWatchlist.php" method="POST" style="display: inline;">
                                <input type="hidden" name="film_id" value="<?php echo $film_id; ?>">
                                <button type="submit" class="button">➖ Rimuovi dalla Watchlist</button>
                            </form>
                        <?php endif; ?>

                    <?php else: ?>
                        <p><a href="login.php">Accedi</a> per aggiungere questo film alla tua watchlist.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="window-body">

            <!-- FORM AGGIUNTA RECENSIONE -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="window" style="margin-bottom: 20px;">
                    <div class="title-bar">
                        <div class="title-bar-text">Scrivi una Recensione</div>
                    </div>
                    <div class="window-body">
                        <form action="aggiungiRecensione.php" method="POST">
                            <input type="hidden" name="film_id" value="<?php echo $film_id; ?>">

                            <label for="voto">Voto:</label>
                            <select name="voto" id="voto" required>
                                <option value="">Seleziona un voto</option>
                                <option value="5">5 - Incredible!</option>
                                <option value="4">4 - Great!</option>
                                <option value="3">3 - Pretty good</option>
                                <option value="2">2 - Not so great</option>
                                <option value="1">1 - Unfortunate</option>
                            </select>

                            <div class="field-row-stacked" style="margin-top: 10px;">
                                <label for="testo">Testo della recensione:</label>
                                <textarea id="testo" name="testo" rows="4" required class="textarea-win98"></textarea>
                            </div>

                            <button type="submit" class="button" style="margin-top: 10px;">Invia Recensione</button>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <div class="window" style="margin-bottom: 20px;">
                    <div class="title-bar">
                        <div class="title-bar-text">Scrivi una Recensione</div>
                    </div>
                    <div class="window-body">
                        <p>Devi <a href="login.php">accedere</a> per scrivere una recensione.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- LISTA RECENSIONI -->
            <div class="window">
                <div class="title-bar">
                    <div class="title-bar-text">Recensioni degli Utenti</div>
                </div>

                <div class="window-body">
                    <?php
                    if (count($recensioni) > 0) {
                        foreach ($recensioni as $rec) {

                            // Recupero username da SQL (gli utenti rimangono nel DB)
                            $username = "Utente";
                            $stmt = $conn->prepare("SELECT username FROM " . T_UTENTI . " WHERE id = ?");
                            $stmt->bind_param("i", $rec["utente_id"]);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                $username = htmlspecialchars($result->fetch_assoc()["username"]);
                            }
                            $stmt->close();

                            $stelle_rec = str_repeat("⭐", intval($rec["voto"]));

                            echo '
                            <div class="review-box">
                                <p><strong>' . $username . '</strong> - ' . $stelle_rec . ' (' . $rec["voto"] . '/5) - ' . date("d/m/Y H:i", strtotime($rec["data"])) . '</p>
                                <p>' . nl2br(htmlspecialchars($rec["testo"])) . '</p>
                            </div>';
                        }
                    } else {
                        echo "<p>Ancora nessuna recensione per questo film.</p>";
                    }
                    ?>
                </div>
            </div>

        </div>

        <footer>
            <p>&copy; 2025 Recensioni Cinematografiche</p>
        </footer>

    </div>
</body>
</html>
