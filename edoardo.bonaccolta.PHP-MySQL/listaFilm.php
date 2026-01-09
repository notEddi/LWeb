<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Film - Recensioni Cinematografiche</title>
    <link rel="stylesheet" href="https://unpkg.com/98.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="window">
    <div class="title-bar">
        <div class="title-bar-text">Lista Film - Recensioni Cinematografiche</div>
    </div>
    <div class="window-body">
        <?php include 'header.php'; ?>
        <main>
            <section id="film-list">
                <h2>Lista Film</h2>
                <?php
                // Query per ottenere tutti i film con informazioni sulle recensioni, ordinati per ID
                $query = "SELECT f.id, f.titolo, f.regista, f.anno, f.durata, f.poster,
                         AVG(r.voto) as media_voti, COUNT(r.id) as numero_recensioni
                         FROM film f 
                         LEFT JOIN recensioni r ON f.id = r.film_id 
                         GROUP BY f.id 
                         ORDER BY f.id ASC"; // Ordinati per ID
                
                $result = $conn->query($query);
                
                if ($result && $result->num_rows > 0) {
                    echo '<ul class="film-grid">';
                    
                    while ($film = $result->fetch_assoc()) {
                        $poster_path = !empty($film['poster']) ? 'poster/' . $film['poster'] : 'poster/default.jpg';
                        $media_voti = $film['media_voti'] ? round($film['media_voti'], 2) : 0;
                        $stelle = str_repeat('⭐', round($media_voti));
                        
                        echo '
                        <li class="film">
                            <a href="film.php?id=' . $film['id'] . '">
                                <img src="' . $poster_path . '" alt="' . htmlspecialchars($film['titolo']) . '" width="150" height="225">
                                <h4>' . htmlspecialchars($film['titolo']) . ' (' . $film['anno'] . ')</h4>
                                <p>' . $stelle . ' ' . $media_voti . '/5</p>
                                <p><small>' . $film['numero_recensioni'] . ' recensioni</small></p>
                            </a>
                        </li>';
                    }
                    
                    echo '</ul>';
                } else {
                    echo '<div class="window" style="margin-top: 20px;">
                            <div class="title-bar">
                                <div class="title-bar-text">Info</div>
                            </div>
                            <div class="window-body">
                                <p>Nessun film trovato nel database.</p>
                                <p><a href="install.php">Esegui l\'installazione</a> per popolare il database.</p>
                            </div>
                          </div>';
                }
                ?>
            </section>
        </main>
        
        <footer>
            <p>&copy; 2025 Recensioni Cinematografiche</p>
        </footer>
    </div>
</div>
</body>
</html>