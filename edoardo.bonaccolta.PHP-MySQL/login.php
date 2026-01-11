<?php
session_start();
require_once 'connection.php';

$error_message = "";
$username = "";

if (isset($_POST['invio'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = "<p style='color: red;'>Dati mancanti!!!</p>";
    } else {
        // Preparare la query per evitare SQL injection
        $stmt = $conn->prepare("SELECT id, username, password FROM " . T_UTENTI . " WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verificare la password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['data_login'] = time();
                $_SESSION['accesso_permesso'] = true;

                // Controlla se è admin
                $stmt_admin = $conn->prepare("SELECT is_admin FROM " . T_UTENTI . " WHERE id = ?");
                $stmt_admin->bind_param("i", $user['id']);
                $stmt_admin->execute();
                $result_admin = $stmt_admin->get_result();
                $admin_data = $result_admin->fetch_assoc();
                $_SESSION['is_admin'] = $admin_data['is_admin'];
                $stmt_admin->close();

                // Reindirizzamento alla homepage
                header("Location: index.php");
                exit();
            } else {
                $error_message = "<p style='color: red;'>Password errata!</p>";
            }
        } else {
            $error_message = "<p style='color: red;'>Utente non trovato!</p>";
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
            <header>
                <div class="window">
                    <div class="tab-container">
                        <a href="index.php" class="tab">Home</a>
                        <a href="listaFilm.php" class="tab">Lista Film</a>
                        <a href="login.php" class="tab active">Login/Registrazione</a>
                    </div>
                </div>
            </header>

            <div class="login-container">
                <div class="window login-form">
                    <div class="title-bar">
                        <div class="title-bar-text">Accedi</div>
                    </div>
                    <div class="window-body">
                        <form action="login.php" method="post">
                            <div class="field-row-stacked">
                                <label for="username">Username:</label>
                                <input type="text" id="username" name="username" required value="<?= htmlspecialchars($username) ?>">
                            </div>

                            <div class="field-row-stacked" style="margin-top: 10px;">
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password" required>
                            </div>

                            <?php echo $error_message; ?>

                            <div style="margin-top: 15px;">
                                <input type="submit" name="invio" value="Accedi" class="button">
                                <input type="reset" value="Reset" class="button">
                            </div>
                        </form>
                        <p style="text-align: center; margin-top: 15px;">
                            Non hai un account? <a href="register.php">Registrati qui</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
