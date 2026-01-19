<?php
require_once 'config.php';

// Se l'utente non è loggato → redirect
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$utente_id = $_SESSION['user_id'];
$username  = $_SESSION['username'];

// Manager XML già disponibili
// - getFilmArray()
// - getFilmArrayById()
// - getRecensioniByUtente($utente_id)
// - getWatchlistByUser($utente_id)
// - contaRecensioniByFilm($film_id)
// - countWatchlistByFilm($film_id)

// CARICAMENTO DATI UTENTE
$recensioni_utente = getRecensioniByUtente($utente_id);
$watchlist_utente  = getWatchlistByUtenteSorted($utente_id);


function getTotaleUtentiSQL($conn) {
    $q = $conn->query("SELECT COUNT(*) AS n FROM " . T_UTENTI . "");
    return $q ? intval($q->fetch_assoc()['n']) : 0;
}


// PER SEZIONE ADMIN
if ($is_admin) {

    // Lista completa film
    $film_list = getFilmArray();

    // Statistiche XML
    $stats = [
        "totale_film"        => count($film_list),
        "totale_utenti"      => getTotaleUtentiSQL($conn), // solo SQL
        "totale_recensioni"  => getTotaleRecensioniXML(),
        "totale_watchlist"   => getTotaleWatchlistXML()
    ];

    // Ultimi 5 film aggiunti (ordinati per id desc)
    usort($film_list, fn($a,$b) => $b['id'] <=> $a['id']);
    $ultimi_film = array_slice($film_list, 0, 5);
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_admin ? 'Pannello Admin' : 'Area Utente'; ?> - Recensioni Cinematografiche</title>
    <link rel="stylesheet" href="https://unpkg.com/98.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style2.css">
</head>
<body>

<div class="window">
    <div class="title-bar">
        <div class="title-bar-text">
            <?php echo $is_admin ? 'Pannello Admin' : 'Area Utente'; ?> - <?php echo htmlspecialchars($username); ?>
        </div>
    </div>

    <div class="window-body">
        <?php include 'header.php'; ?>  

        <?php if ($is_admin): ?>

            <!-- SEZIONE ADMIN -->

            <!-- Statistiche -->
            <div class="window" style="margin-bottom: 20px;">
                <div class="title-bar">
                    <div class="title-bar-text">Statistiche Sistema</div>
                </div>
                <div class="window-body">
                    <div class="admin-stats">

                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['totale_film']; ?></div>
                            <div>Film</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['totale_utenti']; ?></div>
                            <div>Utenti</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['totale_recensioni']; ?></div>
                            <div>Recensioni</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['totale_watchlist']; ?></div>
                            <div>Watchlist</div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Azioni Admin -->
            <div class="window" style="margin-bottom: 20px;">
                <div class="title-bar">
                    <div class="title-bar-text">Gestione Sistema</div>
                </div>
                <div class="window-body">
                    <div class="admin-actions">
                        <a href="aggiungiFilm.php" class="button">➕ Aggiungi Film</a>
                        <a href="gestisciFilm.php" class="button">🗑️ Gestisci Film</a>
                    </div>
                </div>
            </div>

            <!-- Ultimi Film Aggiunti -->
            <div class="window" style="margin-bottom: 20px;">
                <div class="title-bar">
                    <div class="title-bar-text">Ultimi Film Aggiunti</div>
                </div>
                <div class="window-body">
                    <?php
                    if (!empty($ultimi_film)) {
                        echo '<ul style="padding-left: 0px;">';
                        foreach ($ultimi_film as $film) {
                            echo '<li style="margin-bottom: 8px; display: flex; justify-content: space-between;">
                                    <div>
                                        <strong>' . htmlspecialchars($film['titolo']) . '</strong> (' . $film['anno'] . ')
                                    </div>
                                    <div>
                                        <a href="film.php?id=' . $film['id'] . '" class="button" style="font-size: 12px; padding: 2px 6px;">Vedi</a>
                                    </div>
                                  </li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>Nessun film presente.</p>';
                    }
                    ?>
                </div>
            </div>

        <?php else: ?>

            <!-- SEZIONE UTENTE NORMALE -->

            <!-- WATCHLIST -->
            <div class="window" style="margin-bottom: 20px;">
                <div class="title-bar">
                    <div class="title-bar-text">La Mia Watchlist</div>
                </div>
                <div class="window-body">
                <?php
                if (!empty($watchlist_utente)) {
                    echo '<ul class="film-grid">';
                    foreach ($watchlist_utente as $item) {

                        $film = getFilmArrayById($item['film_id']);
                        if (!$film) continue;

                        $poster = !empty($film['poster']) ? 'poster/' . $film['poster'] : 'poster/default.jpg';

                        echo '
                        <li class="film">
                            <a href="film.php?id=' . $film['id'] . '">
                                <img src="' . $poster . '" alt="' . htmlspecialchars($film['titolo']) . '" width="150" height="225">
                                <h4>' . htmlspecialchars($film['titolo']) . ' (' . $film['anno'] . ')</h4>
                            </a>

                            <form action="rimuoviWatchlist.php" method="POST" style="margin-top: 5px;">
                                <input type="hidden" name="film_id" value="' . $film['id'] . '">
                                <button type="submit" class="button" onclick="return confirm(\'Rimuovere dalla watchlist?\')">Rimuovi</button>
                            </form>
                        </li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>La tua watchlist è vuota. <a href="listaFilm.php">Esplora i film</a> per aggiungerne!</p>';
                }
                ?>
                </div>
            </div>

            <!-- RECENSIONI UTENTE -->
            <div class="window">
                <div class="title-bar">
                    <div class="title-bar-text">Le Mie Recensioni</div>
                </div>
                <div class="window-body">

                <?php
                if (!empty($recensioni_utente)) {

                    foreach ($recensioni_utente as $rec) {

                        $film = getFilmArrayById($rec['film_id']);
                        if (!$film) continue;

                        $stelle = str_repeat('⭐', intval($rec['voto']));

                        echo '
                        <div class="review-box" style="margin-bottom: 15px; padding: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px;">
                                
                                <div style="flex-grow: 1;">
                                    <h4 style="margin: 0 0 3px 0; font-size: 17px; font-weight: bold;">
                                        ' . htmlspecialchars($film['titolo']) . ' (' . $film['anno'] . ')
                                    </h4>
                                    <div style="font-size: 12px; color: #666;">
                                        <span>' . $stelle . ' (' . $rec['voto'] . '/5)</span>
                                        <span style="margin-left: 10px;">' . htmlspecialchars($rec['data']) . '</span>
                                    </div>
                                </div>

                                <div style="flex-shrink: 0; margin-left: 10px;">
                                    <a href="modificaRecensione.php?id=' . $rec['id'] . '" class="button" style="font-size: 12px; padding: 2px 6px;">Modifica</a>
                                    <a href="rimuoviRecensione.php?id=' . $rec['id'] . '" class="button" style="font-size: 12px; padding: 2px 6px;" onclick="return confirm(\'Eliminare questa recensione?\')">Elimina</a>
                                </div>

                            </div>

                            <div style="font-size: 13px; line-height: 1.3; margin-top: 5px;">
                                ' . nl2br(htmlspecialchars($rec['testo'])) . '
                            </div>
                        </div>';
                    }

                } else {
                    echo '<p>Non hai ancora scritto recensioni. <a href="listaFilm.php">Esplora i film</a> per iniziare!</p>';
                }
                ?>
                </div>
            </div>
        <?php endif; ?>
        </div>
        <footer>
            <p>&copy; 2025 Recensioni Cinematografiche</p>
        </footer>
    </div>
</div>
</body>
</html>
