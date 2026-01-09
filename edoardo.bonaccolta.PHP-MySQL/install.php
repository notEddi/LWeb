<?php
require_once 'connection.php';

// Creare tabella utenti
$sql_utenti = "CREATE TABLE IF NOT EXISTS utenti (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_utenti) === TRUE) {
    echo "Tabella utenti creata o già esistente.<br>";
} else {
    echo "Errore creazione tabella utenti: " . $conn->error . "<br>";
}

// Creare tabella film
$sql_film = "CREATE TABLE IF NOT EXISTS film (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(100) NOT NULL,
    regista VARCHAR(100) NOT NULL,
    anno INT(4) NOT NULL,
    durata INT(4) NOT NULL,
    trama TEXT NOT NULL,
    poster VARCHAR(255)
)";

if ($conn->query($sql_film) === TRUE) {
    echo "Tabella film creata o già esistente.<br>";
} else {
    echo "Errore creazione tabella film: " . $conn->error . "<br>";
}

// Creare tabella recensioni - CORRETTA con film_id
$sql_recensioni = "CREATE TABLE IF NOT EXISTS recensioni (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    film_id INT(6) UNSIGNED NOT NULL,
    utente_id INT(6) UNSIGNED NOT NULL,
    voto INT(1) NOT NULL CHECK (voto BETWEEN 1 AND 5),
    testo TEXT NOT NULL,
    data_recensione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (film_id) REFERENCES film(id) ON DELETE CASCADE,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE
)";

if ($conn->query($sql_recensioni) === TRUE) {
    echo "Tabella recensioni creata o già esistente.<br>";
} else {
    echo "Errore creazione tabella recensioni: " . $conn->error . "<br>";
}

// Creare tabella watchlist
$sql_watchlist = "CREATE TABLE IF NOT EXISTS watchlist (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_utente INT(6) UNSIGNED NOT NULL,
    id_film INT(6) UNSIGNED NOT NULL,
    data_aggiunta DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (id_film) REFERENCES film(id) ON DELETE CASCADE,
    UNIQUE (id_utente, id_film)
)";

if ($conn->query($sql_watchlist) === TRUE) {
    echo "Tabella watchlist creata o già esistente.<br>";
} else {
    echo "Errore creazione tabella watchlist: " . $conn->error . "<br>";
}

// Inserire alcuni film di esempio con i poster corretti
$films = [
    ["Pulp Fiction", "Quentin Tarantino", 1994, 154, "Le vicende di due sicari della mafia, un pugile, la moglie di un gangster e due banditi di diner si intrecciano in quattro storie di violenza e redenzione.", "poster1.jpg"],
    ["Seven", "David Fincher", 1995, 127, "Due detective, un esordiente e un veterano, cacciano un serial killer che usa i sette peccati capitali come motivazioni.", "poster2.jpg"],
    ["Memories of Murder", "Bong Joon Ho", 2003, 131, "Nel 1986 in una piccola provincia coreana, tre investigatori lavorano sul caso di diverse donne vittime di stupro e omicidio da un colpevole sconosciuto.", "poster3.jpg"],
    ["Oppenheimer", "Christopher Nolan", 2023, 181, "La storia dello scienziato americano J. Robert Oppenheimer e del suo ruolo nello sviluppo della bomba atomica.", "poster4.jpg"],
    ["The Matrix", "Lana e Lilly Wachowski", 1999, 136, "Grazie a misteriosi ribelli, un hacker scopre la vera natura della sua realtà e il suo ruolo nella guerra contro i suoi controllori.", "poster5.jpg"],
    ["The Fly", "David Cronenberg", 1986, 96, "Uno scienziato brillante ed eccentrico comincia a trasformarsi in una mostruosa creature metà uomo e metà mosca dopo che uno dei suoi esperimenti è andato storto.", "poster6.jpg"]
];

// Creare un utente admin di default
$admin_username = "admin";
$admin_email = "admin@cinema.com";
$admin_password = password_hash("admin", PASSWORD_DEFAULT);

$sql_admin = "INSERT IGNORE INTO utenti (username, email, password, is_admin) 
              VALUES ('$admin_username', '$admin_email', '$admin_password', TRUE)";

if ($conn->query($sql_admin) === TRUE) {
    echo "Utente admin creato o già presente.<br>";
    echo "<strong>Credenziali admin: username: admin, password: admin</strong><br>";
} else {
    echo "Errore creazione utente admin: " . $conn->error . "<br>";
}

foreach ($films as $film) {
    $titolo = $conn->real_escape_string($film[0]);
    $regista = $conn->real_escape_string($film[1]);
    $anno = $film[2];
    $durata = $film[3];
    $trama = $conn->real_escape_string($film[4]);
    $poster = $conn->real_escape_string($film[5]);
    
    $sql = "INSERT IGNORE INTO film (titolo, regista, anno, durata, trama, poster) 
            VALUES ('$titolo', '$regista', $anno, $durata, '$trama', '$poster')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Film '$titolo' inserito o già presente.<br>";
    } else {
        echo "Errore inserimento film '$titolo': " . $conn->error . "<br>";
    }
}

echo "<h3>Installazione completata!</h3>";
echo "<p><a href='index.php'>Torna alla homepage</a></p>";

$conn->close();
?>