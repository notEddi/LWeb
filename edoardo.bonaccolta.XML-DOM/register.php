<?php
require_once 'config.php';

// Se già loggato, reindirizza alla home
if ($is_logged) {
    header('Location: index.php');
    exit();
}

// Inizializza variabili
$error_message = "";
$success_message = "";
$username = '';
$email = '';

if (isset($_POST['registrati'])) {
    // Salva i valori inseriti (per favorire la persistenza delle cose corrette)
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? '';
    $conferma_password = $_POST['conferma_password'] ?? '';

    // Validazione campi
    if (empty($username) || empty($email) || empty($password) || empty($conferma_password)) {
        $error_message = "<p style='color: red;'>Tutti i campi sono obbligatori!</p>";
    } else if ($password !== $conferma_password) {
        $error_message = "<p style='color: red;'>Le password non coincidono!</p>";
    } else {
        // Controllo se l'utente esiste già
        $stmt = $conn->prepare("SELECT id FROM " . T_UTENTI . " WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "<p style='color: red;'>Username o email già esistenti!</p>";
        } else {
            // Hash della password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Inserimento nuovo utente
            $stmt = $conn->prepare("INSERT INTO " . T_UTENTI . " (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                $success_message = "<p style='color: green;'>Registrazione completata! <a href='login.php'>Accedi ora</a></p>";
                // Pulisce i valori della form dopo il successo
                $username = '';
                $email = '';
            } else {
                $error_message = "<p style='color: red;'>Errore durante la registrazione: " . $conn->error . "</p>";
            }
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
    <title>Registrazione - Recensioni Cinematografiche</title>
    <link rel="stylesheet" href="https://unpkg.com/98.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="window">
        <div class="title-bar">
            <div class="title-bar-text">Recensioni Cinematografiche - Registrazione</div>
        </div>
        <div class="window-body">
            <?php include 'header.php'; ?>
            
            <div class="login-container">
                <div class="window login-form">
                    <div class="title-bar">
                        <div class="title-bar-text">Registrati</div>
                    </div>
                    <div class="window-body">
                        <?php 
                        echo $error_message;
                        echo $success_message;
                        ?>
                        
                        <form action="register.php" method="post">
                            <div class="field-row-stacked">
                                <label for="username">Username:</label>
                                <input type="text" id="username" name="username" required value="<?= htmlspecialchars($username) ?>">
                            </div>
                            
                            <div class="field-row-stacked" style="margin-top: 10px;">
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email) ?>">
                            </div>
                            
                            <div class="field-row-stacked" style="margin-top: 10px;">
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            
                            <div class="field-row-stacked" style="margin-top: 10px;">
                                <label for="conferma_password">Conferma Password:</label>
                                <input type="password" id="conferma_password" name="conferma_password" required>
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <input type="submit" name="registrati" value="Registrati" class="button">
                                <input type="reset" value="Reset" class="button">
                            </div>
                        </form>
                        
                        <p style="text-align: center; margin-top: 15px;">
                            Hai già un account? <a href="login.php">Accedi qui</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
