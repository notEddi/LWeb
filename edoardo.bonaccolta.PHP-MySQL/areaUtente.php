<?php
session_start();
require_once 'connection.php';

// Verifica che l'utente sia loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$utente_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Verifica se l'utente è admin
$is_admin = false;
$stmt_admin = $conn->prepare("SELECT is_admin FROM " . T_UTENTI . " WHERE id = ?");
$stmt_admin->bind_param("i", $utente_id);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();
$user_data = $result_admin->fetch_assoc();
$is_admin = $user_data['is_admin'];
$stmt_admin->close();

// Query per ottenere le recensioni dell'utente
$query_recensioni = "SELECT r.*, f.titolo, f.anno 
                     FROM " . T_RECENSIONI . " r 
                     JOIN " . T_FILM . " f ON r.film_id = f.id 
                     WHERE r.utente_id = ? 
                     ORDER BY r.data_recensione DESC";
$stmt_recensioni = $conn->prepare($query_recensioni);
$stmt_recensioni->bind_param("i", $utente_id);
$stmt_recensioni->execute();
$result_recensioni = $stmt_recensioni->get_result();

// Query per ottenere la watchlist dell'utente
$query_watchlist = "SELECT w.*, f.titolo, f.anno, f.regista, f.poster 
                    FROM " . T_WATCHLIST . " w 
                    JOIN " . T_FILM . " f ON w.id_film = f.id 
                    WHERE w.id_utente = ? 
                    ORDER BY w.data_aggiunta DESC";
$stmt_watchlist = $conn->prepare($query_watchlist);
$stmt_watchlist->bind_param("i", $utente_id);
$stmt_watchlist->execute();
$result_watchlist = $stmt_watchlist->get_result();

// Query per statistiche admin
if ($is_admin) {
    $query_stats = "SELECT 
        (SELECT COUNT(*) FROM " . T_FILM . ") as totale_film,
        (SELECT COUNT(*) FROM " . T_UTENTI . ") as totale_utenti,
        (SELECT COUNT(*) FROM " . T_RECENSIONI . ") as totale_recensioni,
        (SELECT COUNT(*) FROM " . T_WATCHLIST . ") as totale_watchlist";
    
    $result_stats = $conn->query($query_stats);
    $stats = $result_stats->fetch_assoc();
    
    // Ultimi film aggiunti
    $query_ultimi_film = "SELECT * FROM " . T_FILM . " ORDER BY id DESC LIMIT 5";
    $result_ultimi_film = $conn->query($query_ultimi_film);
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
        <header>
            <div class="window">
                <div class="tab-container">
                    <a href="index.php" class="tab">Home</a>
                    <a href="listaFilm.php" class="tab">Lista Film</a>
                    <a href="areaUtente.php" class="tab active">
                        <?php echo $is_admin ? 'Pannello Admin' : 'Area Utente'; ?>
                    </a>
                    <a href="logout.php" class="tab">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
                </div>
            </div>
        </header>
        
        <div class="window-body">
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

                <!-- Ultimi film aggiunti -->
                <div class="window" style="margin-bottom: 20px;">
                    <div class="title-bar">
                        <div class="title-bar-text">Ultimi Film Aggiunti</div>
                    </div>
                    <div class="window-body">
                        <?php
                        if ($result_ultimi_film->num_rows > 0) {
                            echo '<ul style="padding-left: 0px;">';
                            while ($film = $result_ultimi_film->fetch_assoc()) {
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
                            echo '<p>Nessun film nel database.</p>';
                        }
                        ?>
                    </div>
                </div>

            <?php else: ?>
                <!-- SEZIONE UTENTE NORMALE -->
                
                <!-- Watchlist -->
                <div class="window" style="margin-bottom: 20px;">
                    <div class="title-bar">
                        <div class="title-bar-text">La Mia Watchlist</div>
                    </div>
                    <div class="window-body">
                        <?php
                        if ($result_watchlist->num_rows > 0) {
                            echo '<ul class="film-grid">';
                            while ($film_watchlist = $result_watchlist->fetch_assoc()) {
                                $poster_path = !empty($film_watchlist['poster']) ? 'poster/' . $film_watchlist['poster'] : 'poster/default.jpg';
                                
                                echo '
                                <li class="film">
                                    <a href="film.php?id=' . $film_watchlist['id_film'] . '">
                                        <img src="' . $poster_path . '" alt="' . htmlspecialchars($film_watchlist['titolo']) . '" width="150" height="225">
                                        <h4>' . htmlspecialchars($film_watchlist['titolo']) . ' (' . $film_watchlist['anno'] . ')</h4>
                                    </a>
                                    <form action="rimuoviWatchlist.php" method="POST" style="margin-top: 5px;">
                                        <input type="hidden" name="film_id" value="' . $film_watchlist['id_film'] . '">
                                        <button type="submit" class="button" onclick="return confirm(\'Rimuovere dalla watchlist?\')">Rimuovi</button>
                                    </form>
                                </li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<p>La tua watchlist è vuota. <a href="listaFilm.php">Esplora i film</a> per aggiungerne!</p>';
                        }
                        $stmt_watchlist->close();
                        ?>
                    </div>
                </div>

                <!-- Recensioni -->
                <div class="window">
                    <div class="title-bar">
                        <div class="title-bar-text">Le Mie Recensioni</div>
                    </div>
                    <div class="window-body">
                        <?php
                        if ($result_recensioni->num_rows > 0) {
                            while ($recensione = $result_recensioni->fetch_assoc()) {
                                $stelle_recensione = str_repeat('⭐', $recensione['voto']);
                                
                                echo '
                                <div class="review-box" style="margin-bottom: 15px; padding: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px;">
                                        <div style="flex-grow: 1;">
                                            <h4 style="margin: 0 0 3px 0; font-size: 17px; font-weight: bold;">
                                                ' . htmlspecialchars($recensione['titolo']) . ' (' . $recensione['anno'] . ')
                                            </h4>
                                            <div style="font-size: 12px; color: #666;">
                                                <span>' . $stelle_recensione . ' (' . $recensione['voto'] . '/5)</span>
                                                <span style="margin-left: 10px;">' . date('d/m/Y H:i', strtotime($recensione['data_recensione'])) . '</span>
                                            </div>
                                        </div>
                                        <div style="flex-shrink: 0; margin-left: 10px;">
                                            <a href="modificaRecensione.php?id=' . $recensione['id'] . '" class="button" style="font-size: 12px; padding: 2px 6px;">Modifica</a>
                                            <a href="eliminaRecensione.php?id=' . $recensione['id'] . '" class="button" style="font-size: 12px; padding: 2px 6px;" onclick="return confirm(\'Eliminare questa recensione?\')">Elimina</a>
                                        </div>
                                    </div>
                                    <div style="font-size: 13px; line-height: 1.3; margin-top: 5px;">
                                        ' . nl2br(htmlspecialchars($recensione['testo'])) . '
                                    </div>
                                </div>';
                            }
                        } else {
                            echo '<p>Non hai ancora scritto recensioni. <a href="listaFilm.php">Esplora i film</a> per iniziare!</p>';
                        }
                        $stmt_recensioni->close();
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