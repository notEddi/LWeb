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
                // Query per ottenere i film più votati
                $query = "SELECT f.id, f.titolo, f.anno, f.regista, 
                         AVG(r.voto) as media_voti, COUNT(r.id) as numero_recensioni
                         FROM " . T_FILM . " f 
                         LEFT JOIN " . T_RECENSIONI . " r ON f.id = r.film_id 
                         GROUP BY f.id 
                         ORDER BY media_voti DESC 
                         LIMIT 3";
                
                $result = $conn->query($query);
                
                if ($result && $result->num_rows > 0) {
                    echo '<ul>';
                    while ($row = $result->fetch_assoc()) {
                        $media_voti_formattata = number_format($row['media_voti'], 2, ',', '');
                        $stelle_intere = floor($row['media_voti']);
                        $stelle_mezza = ($row['media_voti'] - $stelle_intere) >= 0.5;
                        
                        $stelle = str_repeat('⭐', $stelle_intere);
                        $stelle .= $stelle_mezza ? '½' : '';
                        
                        echo "<li><strong>{$row['titolo']}</strong> ({$row['anno']}) - {$stelle} 
                              ({$media_voti_formattata}/5 da {$row['numero_recensioni']} recensioni)</li>";
                    }
                    echo '</ul>';
                } else {
                    echo '<p>Ancora nessuna recensione.</p>';
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