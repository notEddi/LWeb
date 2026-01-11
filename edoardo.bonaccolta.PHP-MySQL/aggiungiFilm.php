<?php
require_once 'config.php';

// Verifica che l'utente sia admin
if (!$is_logged || !$is_admin) {
    header('Location: index.php');
    exit();
}

$success_message = "";
$error_message = "";

if (isset($_POST['aggiungi_film'])) {
    $titolo = trim($_POST['titolo']);
    $regista = trim($_POST['regista']);
    $anno = intval($_POST['anno']);
    $durata = intval($_POST['durata']);
    $trama = trim($_POST['trama']);
    
    // Gestione upload poster
    $poster_name = "";
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['poster']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $extension = pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION);
            $poster_name = uniqid() . '.' . $extension;
            $upload_path = 'poster/' . $poster_name;
            
            if (!move_uploaded_file($_FILES['poster']['tmp_name'], $upload_path)) {
                $error_message = "Errore nel caricamento del poster.";
            }
        } else {
            $error_message = "Formato file non supportato. Usa JPG, PNG o GIF.";
        }
    }
    
    if (empty($error_message)) {
        $stmt = $conn->prepare("INSERT INTO " . T_FILM . " (titolo, regista, anno, durata, trama, poster) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiiss", $titolo, $regista, $anno, $durata, $trama, $poster_name);
        
        if ($stmt->execute()) {
            $success_message = "Film aggiunto con successo!";
            // Reset form
            $_POST = array();
        } else {
            $error_message = "Errore nell'aggiunta del film: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Film - Admin</title>
    <link rel="stylesheet" href="https://unpkg.com/98.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="window">
    <div class="title-bar">
        <div class="title-bar-text">Aggiungi Film - Pannello Admin</div>
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
        
        <div class="window" style="margin: 20px auto; max-width: 600px;">
            <div class="title-bar">
                <div class="title-bar-text">Aggiungi Nuovo Film</div>
            </div>
            <div class="window-body">
                <?php
                if ($success_message) {
                    echo '<div style="color: green; margin-bottom: 15px;">' . $success_message . '</div>';
                }
                if ($error_message) {
                    echo '<div style="color: red; margin-bottom: 15px;">' . $error_message . '</div>';
                }
                ?>
                
                <form action="aggiungiFilm.php" method="POST" enctype="multipart/form-data">
                    <div class="field-row-stacked">
                        <label for="titolo">Titolo:</label>
                        <input type="text" id="titolo" name="titolo" value="<?php echo htmlspecialchars($_POST['titolo'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="field-row-stacked" style="margin-top: 10px;">
                        <label for="regista">Regista:</label>
                        <input type="text" id="regista" name="regista" value="<?php echo htmlspecialchars($_POST['regista'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="field-row" style="margin-top: 10px;">
                        <div class="field-row-stacked" style="flex: 1;">
                            <label for="anno">Anno:</label>
                            <input type="number" id="anno" name="anno" min="1900" max="2030" value="<?php echo htmlspecialchars($_POST['anno'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="field-row-stacked" style="flex: 1; margin-left: 10px; margin-top: 0px;">
                            <label for="durata">Durata (minuti):</label>
                            <input type="number" id="durata" name="durata" min="1" value="<?php echo htmlspecialchars($_POST['durata'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="field-row-stacked" style="margin-top: 10px;">
                        <label for="trama">Trama:</label>
                        <textarea id="trama" name="trama" rows="4" required class="textarea-win98"><?php echo htmlspecialchars($_POST['trama'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="field-row-stacked" style="margin-top: 10px;">
                        <label for="poster">Poster (opzionale):</label>
                        <input type="file" id="poster" name="poster" accept="image/jpeg,image/jpg,image/png,image/gif">
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <button type="submit" name="aggiungi_film" class="button">Aggiungi Film</button>
                        <button type="reset" class="button">Reset</button>
                        <a href="areaUtente.php" class="button">Torna al Pannello Admin</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>