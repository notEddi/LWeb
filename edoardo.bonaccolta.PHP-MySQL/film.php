<?php
require_once 'config.php';

// Verifica che l'ID del film sia stato passato
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listaFilm.php');
    exit();
}

$film_id = intval($_GET['id']);

// Query per ottenere i dettagli del film
$query_film = "SELECT * FROM " . T_FILM . " WHERE id = ?";
$stmt = $conn->prepare($query_film);
$stmt->bind_param("i", $film_id);
$stmt->execute();
$result_film = $stmt->get_result();

if ($result_film->num_rows === 0) {
    // Film non trovato
    header('Location: listaFilm.php');
    exit();
}

$film = $result_film->fetch_assoc();
$stmt->close();

// Query per ottenere le recensioni del film
$query_recensioni = "SELECT r.*, u.username 
                     FROM " . T_RECENSIONI . " r 
                     JOIN " . T_UTENTI . " u ON r.utente_id = u.id 
                     WHERE r.film_id = ? 
                     ORDER BY r.data_recensione DESC";
$stmt_recensioni = $conn->prepare($query_recensioni);
$stmt_recensioni->bind_param("i", $film_id);
$stmt_recensioni->execute();
$result_recensioni = $stmt_recensioni->get_result();

// Calcola la media dei voti
$query_media = "SELECT AVG(voto) as media_voti, COUNT(*) as totale_recensioni 
                FROM " . T_RECENSIONI . " 
                WHERE film_id = ?";
$stmt_media = $conn->prepare($query_media);
$stmt_media->bind_param("i", $film_id);
$stmt_media->execute();
$result_media = $stmt_media->get_result();
$media_data = $result_media->fetch_assoc();
$stmt_media->close();

$media_voti = $media_data['media_voti'] ? round($media_data['media_voti'], 1) : 0;
$stelle = str_repeat('⭐', round($media_voti));

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Recensioni Cinematografiche</title>
    <link rel="stylesheet" href="https://unpkg.com/98.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="window">
        <div class="title-bar">
            <div class="title-bar-text">Recensioni Cinematografiche - Login</div>
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
                <p><strong>Valutazione media:</strong> <?php echo $stelle; ?> (<?php echo $media_voti; ?>/5) da <?php echo $media_data['totale_recensioni']; ?> recensioni</p>
                <p><strong>Trama:</strong> <?php echo htmlspecialchars($film['trama']); ?></p>
                <p><strong>Regista:</strong> <?php echo htmlspecialchars($film['regista']); ?></p>
                <p><strong>Anno di uscita:</strong> <?php echo $film['anno']; ?></p>
                <p><strong>Durata:</strong> <?php echo $film['durata']; ?> minuti</p>

                    <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        // Verifica se il film è già nella watchlist
        $query_check_watchlist = "SELECT id FROM " . T_WATCHLIST . " WHERE id_utente = ? AND id_film = ?";
        $stmt_check_watchlist = $conn->prepare($query_check_watchlist);
        $stmt_check_watchlist->bind_param("ii", $_SESSION['user_id'], $film_id);
        $stmt_check_watchlist->execute();
        $result_check_watchlist = $stmt_check_watchlist->get_result();
        $in_watchlist = $result_check_watchlist->num_rows > 0;
        $stmt_check_watchlist->close();
        ?>
        
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
            <!-- Form per aggiungere una recensione -->
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

            <!-- Lista delle recensioni esistenti -->
            <div class="window">
                <div class="title-bar">
                    <div class="title-bar-text">Recensioni degli Utenti</div>
                </div>
                <div class="window-body">
                    <?php
                    if ($result_recensioni->num_rows > 0) {
                        while ($recensione = $result_recensioni->fetch_assoc()) {
                            $stelle_recensione = str_repeat('⭐', $recensione['voto']);
                            echo '
                            <div class="review-box">
                                <p><strong>' . htmlspecialchars($recensione['username']) . '</strong> - ' . $stelle_recensione . ' (' . $recensione['voto'] . '/5) - ' . date('d/m/Y H:i', strtotime($recensione['data_recensione'])) . '</p>
                                <p>' . nl2br(htmlspecialchars($recensione['testo'])) . '</p>
                            </div>';
                        }
                    } else {
                        echo '<p>Ancora nessuna recensione per questo film.</p>';
                    }
                    $stmt_recensioni->close();
                    ?>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2025 Recensioni Cinematografiche</p>
        </footer>
    </div>
</div>
</body>
</html>