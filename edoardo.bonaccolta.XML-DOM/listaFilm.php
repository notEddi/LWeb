<?php
require_once 'config.php';

// IMPORT MANAGER XML
require_once 'xmlFilmManager.php';

// CARICA TUTTI I FILM DA XML
$films = getAllFilms(); // array già ordinato per id crescente
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
                if (!empty($films)) {
                    echo '<ul class="film-grid">';

                    foreach ($films as $film) {

                        // valori
                        $id     = $film['id'];
                        $titolo = $film['titolo'];
                        $anno   = $film['anno'];
                        $poster = $film['poster'];

                        // path poster
                        $poster_path = (!empty($poster))
                            ? 'poster/' . $poster
                            : 'poster/default.jpg';

                        // medie & contatori
                        $media = round(getMediaVotiFilm($id), 1);
                        $stelle = str_repeat('⭐', round($media));
                        $numRec = contaRecensioniByFilm($id);

                        echo '
                        <li class="film">
                            <a href="film.php?id=' . $id . '">
                                <img src="' . $poster_path . '" alt="' . htmlspecialchars($titolo) . '" width="150" height="225">
                                <h4>' . htmlspecialchars($titolo) . ' (' . $anno . ')</h4>
                                <p>' . $stelle . ' ' . $media . '/5</p>
                                <p><small>' . $numRec . ' recensioni</small></p>
                            </a>
                        </li>';
                    }

                    echo '</ul>';

                } else {
                    echo '
                    <div class="window" style="margin-top: 20px;">
                        <div class="title-bar">
                            <div class="title-bar-text">Info</div>
                        </div>
                        <div class="window-body">
                            <p>Nessun film trovato.</p>
                            <p><a href="aggiungi-film.php">Aggiungi un film</a></p>
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
