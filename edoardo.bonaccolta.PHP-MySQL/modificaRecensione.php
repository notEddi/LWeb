<?php
session_start();
require_once 'connection.php';

// Verifica che l'utente sia loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verifica che l'ID della recensione sia stato passato
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: areaUtente.php');
    exit();
}

$recensione_id = intval($_GET['id']);
$utente_id = $_SESSION['user_id'];

// Ottieni i dati della recensione
$query_recensione = "SELECT r.*, f.titolo, f.id as film_id 
                     FROM " . T_RECENSIONI . " r 
                     JOIN " . T_FILM . " f ON r.film_id = f.id 
                     WHERE r.id = ? AND r.utente_id = ?";
$stmt = $conn->prepare($query_recensione);
$stmt->bind_param("ii", $recensione_id, $utente_id);
$stmt->execute();
$result_recensione = $stmt->get_result();

if ($result_recensione->num_rows === 0) {
    // Recensione non trovata o non appartiene all'utente
    header('Location: areaUtente.php');
    exit();
}

$recensione = $result_recensione->fetch_assoc();
$stmt->close();

// Gestione dell'aggiornamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voto = isset($_POST['voto']) ? intval($_POST['voto']) : 0;
    $testo = isset($_POST['testo']) ? trim($_POST['testo']) : '';
    
    // Validazione
    $errori = [];
    
    if ($voto < 1 || $voto > 5) {
        $errori[] = "Voto non valido. Deve essere tra 1 e 5.";
    }
    
    if (empty($testo)) {
        $errori[] = "Il testo della recensione è obbligatorio.";
    }
    
    if (empty($errori)) {
        $testo = $conn->real_escape_string($testo);
        
        // Aggiorna la recensione
        $query_aggiorna = "UPDATE " . T_RECENSIONI . " SET voto = ?, testo = ?, data_recensione = NOW() WHERE id = ? AND utente_id = ?";
        $stmt_aggiorna = $conn->prepare($query_aggiorna);
        $stmt_aggiorna->bind_param("isii", $voto, $testo, $recensione_id, $utente_id);
        
        if ($stmt_aggiorna->execute()) {
            $_SESSION['successo'] = "Recensione aggiornata con successo!";
            header('Location: areaUtente.php');
            exit();
        } else {
            $errori[] = "Errore durante l'aggiornamento: " . $conn->error;
        }
        $stmt_aggiorna->close();
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
        <div class="title-bar-text">Modifica Recensione - <?php echo htmlspecialchars($recensione['titolo']); ?></div>
    </div>
    <div class="window-body">
        <header>
            <div class="window">
                <div class="tab-container">
                    <a href="index.php" class="tab">Home</a>
                    <a href="listaFilm.php" class="tab">Lista Film</a>
                    <a href="areaUtente.php" class="tab">Area Utente</a>
                    <a href="logout.php" class="tab">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
                </div>
            </div>
        </header>
        
        <div class="window-body">
            <div class="window">
                <div class="title-bar">
                    <div class="title-bar-text">Modifica la tua recensione</div>
                </div>
                <div class="window-body">
                    <?php
                    if (!empty($errori)) {
                        echo '<div style="color: red; margin-bottom: 10px;">';
                        foreach ($errori as $errore) {
                            echo '<p>' . $errore . '</p>';
                        }
                        echo '</div>';
                    }
                    ?>
                    
                    <form action="modificaRecensione.php?id=<?php echo $recensione_id; ?>" method="POST">
                        <label for="voto">Voto:</label>
                        <select name="voto" id="voto" required>
                            <option value="">Seleziona un voto</option>
                            <option value="5" <?php echo $recensione['voto'] == 5 ? 'selected' : ''; ?>>5 - Incredible!</option>
                            <option value="4" <?php echo $recensione['voto'] == 4 ? 'selected' : ''; ?>>4 - Great!</option>
                            <option value="3" <?php echo $recensione['voto'] == 3 ? 'selected' : ''; ?>>3 - Pretty good</option>
                            <option value="2" <?php echo $recensione['voto'] == 2 ? 'selected' : ''; ?>>2 - Not so great</option>
                            <option value="1" <?php echo $recensione['voto'] == 1 ? 'selected' : ''; ?>>1 - Unfortunate</option>
                        </select>
                        
                        <div class="field-row-stacked" style="margin-top: 10px;">
                            <label for="testo">Testo della recensione:</label>
                            <textarea id="testo" name="testo" rows="6" required class="textarea-win98"><?php echo htmlspecialchars($recensione['testo']); ?></textarea>
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