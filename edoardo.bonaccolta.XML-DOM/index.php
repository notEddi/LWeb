<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recensioni Cinematografiche</title>
    <link rel="stylesheet" href="https://unpkg.com/98.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="window">
    <div class="title-bar">
        <div class="title-bar-text">Recensioni Cinematografiche</div>
    </div>

    <div class="window-body">
        <?php include 'header.php'; ?>

        <main>
            <section id="intro">
                <h2>Benvenuti su Recensioni Cinematografiche</h2>
                <p>Scopri le recensioni sui tuoi film preferiti e condividi le tue opinioni!</p>

                <?php if ($is_logged): ?>
                    <p>Ciao, <strong><?php echo htmlspecialchars($username); ?></strong>! 
                    <a href="listaFilm.php">Esplora i film</a> o <a href="logout.php">esci</a>.</p>
                <?php else: ?>
                    <p><a href="login.php">Accedi</a> o <a href="register.php">registrati</a> per aggiungere recensioni.</p>
                <?php endif; ?>
            </section>

            <section style="margin-top: 20px;">
                <h3>Film più votati</h3>
                <?php
                // --- RECUPERO FILM DA XML ---
                $film_list = getFilmArray();

                // Costruisco un array arricchito con media voti e numero recensioni
                $film_votati = [];

                foreach ($film_list as $f) {
                    $media = getMediaVotiFilm($f['id']);
                    $count = getNumeroRecensioniFilm($f['id']);

                    // Salva solo se ha almeno 1 recensione
                    if ($count > 0) {
                        $film_votati[] = [
                            "titolo" => $f["titolo"],
                            "anno"   => $f["anno"],
                            "id"     => $f["id"],
                            "regista"=> $f["regista"],
                            "media"  => $media,
                            "count"  => $count
                        ];
                    }
                }

                // Ordina per media voti decrescente
                usort($film_votati, fn($a, $b) => $b["media"] <=> $a["media"]);

                // Prende i primi 3
                $top3 = array_slice($film_votati, 0, 3);

                if (!empty($top3)):
                    echo "<ul>";
                    foreach ($top3 as $f) {

                        $media_format = number_format($f['media'], 2, ',', '');
                        $stelle_intere = floor($f['media']);
                        $stelle_mezza  = ($f['media'] - $stelle_intere) >= 0.5;

                        $stelle = str_repeat('⭐', $stelle_intere);
                        if ($stelle_mezza) $stelle .= '½';

                        echo "<li><strong>{$f['titolo']}</strong> ({$f['anno']}) - {$stelle} 
                              ({$media_format}/5 da {$f['count']} recensioni)</li>";
                    }
                    echo "</ul>";
                else:
                    echo "<p>Ancora nessuna recensione.</p>";
                endif;
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
