<?php
require_once "dati_generali.php";

// Connessione al DBMS (senza database)
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Creazione database
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");

// Selezione database
$conn->select_db($dbname);

$XML_DIR = __DIR__ . "/xml/";

// 2) CREAZIONE TABELLA UTENTI (se non esiste)
$sql = "CREATE TABLE IF NOT EXISTS utenti (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($sql);

$messages[] = "✔ Tabella utenti verificata/creata.";

// 3) CREAZIONE UTENTE ADMIN DEFAULT
$admin_username = "admin";
$admin_email = "admin@cinema.com";
$admin_password = password_hash("admin", PASSWORD_DEFAULT);

$sql_admin = "INSERT IGNORE INTO utenti (username, email, password, is_admin) 
              VALUES ('$admin_username', '$admin_email', '$admin_password', TRUE)";

if ($conn->query($sql_admin) === TRUE) {
    // Aggiungo il messaggio all'array come negli altri casi
    $messages[] = "✔ Utente admin creato o già presente. <strong>Credenziali: admin / admin</strong>";
} else {
    $messages[] = "❌ Errore creazione utente admin: " . $conn->error;
}

// 4) CREAZIONE CARTELLA XML SE NON ESISTE
if (!is_dir($XML_DIR)) {
    mkdir($XML_DIR, 0777, true);
    $messages[] = "✔ Cartella /xml creata.";
}

// DEFINIZIONE NOMI FILE
$FILM_XML = $XML_DIR . "film.xml";
$FILM_XSD = $XML_DIR . "film.xsd";

$REC_XML = $XML_DIR . "recensioni.xml";
$REC_XSD = $XML_DIR . "recensioni.xsd";

$WL_XML = $XML_DIR . "watchlist.xml";
$WL_XSD = $XML_DIR . "watchlist.xsd";

// 5) CREAZIONE XSD FILM
if (!file_exists($FILM_XSD)) {

$film_xsd = <<<XSD
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="film_collection">
    <xs:complexType>
      <xs:sequence>
        <xs:element name="film" maxOccurs="unbounded">
          <xs:complexType>
            <xs:sequence>
              <xs:element name="titolo" type="xs:string"/>
              <xs:element name="regista" type="xs:string"/>
              <xs:element name="anno" type="xs:int"/>
              <xs:element name="durata" type="xs:int"/>
              <xs:element name="trama" type="xs:string"/>
              <xs:element name="poster" type="xs:string"/>
            </xs:sequence>
            <xs:attribute name="id" type="xs:int" use="required"/>
          </xs:complexType>
        </xs:element>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
</xs:schema>
XSD;

file_put_contents($FILM_XSD, $film_xsd);
$messages[] = "✔ Creato film.xsd";
}

// 6) CREAZIONE XSD RECENSIONI
if (!file_exists($REC_XSD)) {

$rec_xsd = <<<XSD
<?xml version="1.0" encoding="UTF-8"?>
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">

  <xsd:element name="recensioni">
    <xsd:complexType>
      <xsd:sequence>
        <xsd:element name="recensione" minOccurs="0" maxOccurs="unbounded">
          <xsd:complexType>
            <xsd:sequence>
              <xsd:element name="voto" type="xsd:int"/>
              <xsd:element name="data" type="xsd:string"/>
              <xsd:element name="testo" type="xsd:string"/>
            </xsd:sequence>
            <xsd:attribute name="id" type="xsd:int" use="required"/>
            <xsd:attribute name="utente_id" type="xsd:int" use="required"/>
            <xsd:attribute name="film_id" type="xsd:int" use="required"/>
          </xsd:complexType>
        </xsd:element>
      </xsd:sequence>
    </xsd:complexType>
  </xsd:element>

</xsd:schema>
XSD;

file_put_contents($REC_XSD, $rec_xsd);
$messages[] = "✔ Creato recensioni.xsd";
}

// 7) CREAZIONE XSD WATCHLIST
if (!file_exists($WL_XSD)) {

$wl_xsd = <<<XSD
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">

  <xs:element name="watchlist">
    <xs:complexType>
      <xs:sequence>
        <xs:element name="item" minOccurs="0" maxOccurs="unbounded">
          <xs:complexType>
            <xs:attribute name="utente_id" type="xs:int" use="required"/>
            <xs:attribute name="film_id" type="xs:int" use="required"/>
            <xs:attribute name="data_aggiunta" type="xs:string" use="optional"/>
          </xs:complexType>
        </xs:element>
      </xs:sequence>
    </xs:complexType>
  </xs:element>

</xs:schema>

XSD;

file_put_contents($WL_XSD, $wl_xsd);
$messages[] = "✔ Creato watchlist.xsd";
}

// 8) CREAZIONE FILM.XML CON 12 FILM
if (!file_exists($FILM_XML)) {

    $dom = new DOMDocument("1.0", "UTF-8");
    $dom->formatOutput = true;

    $root = $dom->createElement("film_collection");
    $dom->appendChild($root);

    $film_data = [
        [1,'Pulp Fiction','Quentin Tarantino',1994,154,'Le vicende di due sicari della mafia, un pugile, la moglie di un gangster e due banditi di diner si intrecciano in quattro storie di violenza e redenzione.','poster1.jpg'],
        [2,'Seven','David Fincher',1995,127,'Due detective, un esordiente e un veterano, cacciano un serial killer che usa i sette peccati capitali come motivazioni.','poster2.jpg'],
        [3,'Memories of Murder','Bong Joon Ho',2003,131,'Nel 1986 in una piccola provincia coreana, tre investigatori lavorano sul caso di diverse donne vittime di stupro e omicidio da un colpevole sconosciuto.','poster3.jpg'],
        [4,'Oppenheimer','Christopher Nolan',2023,181,'La storia del ruolo di J. Robert Oppenheimer nello sviluppo della bomba atomica durante la seconda guerra mondiale.','poster4.jpg'],
        [5,'The Matrix','Wachowski','1999',136,'Ambientato nel 22° secolo, Matrix racconta la storia di un hacker che si unisce a un gruppo di ribelli clandestini che combattono i grandi e potenti computer che ora governano la Terra.','poster5.jpg'],
        [6,'The Fly','David Cronenberg',1986,96,'Uno scienziato brillante ed eccentrico comincia a trasformarsi in una mostruosa creature metà uomo e metà mosca dopo che uno dei suoi esperimenti è andato storto.', 'poster6.jpg'],
        [7,'Oldboy','Park Chan-wook',2003,120,'Senza avere idea di come sia finito in prigione, drogato e torturato per 15 anni, un uomo disperato cerca vendetta sui suoi rapitori.', 'poster7.jpg'],
        [8,'Mulholland Drive','David Lynch',2001,147,'Dopo aver perso la memoria in un incidente sulla strada Mullholland Drive, una donna ed un\'aspirante attrice cercano indizi e rispose a Los Angeles in un\'intricata avventura che oscilla tra sogno e realtà.', 'poster8.jpg'],
        [9,'The Shining','Stanley Kubrick',1980,144,'Una famiglia si dirige verso un hotel isolato per l\'inverno, dove una presenza spirituale malvagia induce il padre alla violenza, mentre il figlio telepatico vede orribili presentimenti del passato e del futuro.', 'poster9.jpg'],
        [10,'Le Cercle Rouge','Jean-Pierre Melville',1970,140,'Dopo aver lasciato la prigione, il maestro ladro Corey incontra un famigerato evaso e un ex poliziotto alcolizzato. Il trio procede alla trama di un\'elaborata rapina.', 'poster10.jpg'],
        [11,'Spirited Away','Hayao Miyazaki',2001,125,'Una ragazzina, Chihiro, rimane intrappolata in uno strano, nuovo mondo di spiriti. Quando i suoi genitori subiscono una misteriosa trasformazione, deve fare appello al coraggio che non sapeva di avere per liberare la sua famiglia.','poster11.jpg'],
        [12,'Avatar','James Cameron',2009,162,'Nel 22° secolo, un marine paraplegico viene inviato sulla luna Pandora per una missione unica, ma è combattuto tra l\'esecuzione degli ordini e la protezione di una civiltà aliena.','poster12.jpg'],
    ];

    foreach ($film_data as $f) {
        $film = $dom->createElement("film");
        $film->setAttribute("id", $f[0]);

        $film->appendChild($dom->createElement("titolo", $f[1]));
        $film->appendChild($dom->createElement("regista", $f[2]));
        $film->appendChild($dom->createElement("anno", $f[3]));
        $film->appendChild($dom->createElement("durata", $f[4]));
        $film->appendChild($dom->createElement("trama", $f[5]));
        $film->appendChild($dom->createElement("poster", $f[6]));

        $root->appendChild($film);
    }

    $dom->save($FILM_XML);

    $messages[] = "✔ Creato film.xml con 10 film iniziali.";
}

// 9) CREAZIONE FILE RECENSIONI E WATCHLIST
if (!file_exists($REC_XML)) {
    file_put_contents($REC_XML, "<?xml version=\"1.0\"?><recensioni/>");
    $messages[] = "✔ Creato recensioni.xml";
}

if (!file_exists($WL_XML)) {
    file_put_contents($WL_XML, "<?xml version=\"1.0\"?><watchlist/>");
    $messages[] = "✔ Creato watchlist.xml";
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Installazione Completata</title>
    <link rel="stylesheet" href="https://unpkg.com/98.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="window" style="max-width: 500px; margin: 40px auto;">
        <div class="title-bar">
            <div class="title-bar-text">Installazione Completata</div>
        </div>
        <div class="window-body">
            <?php foreach ($messages as $m) echo "<p>$m</p>"; ?>
            <p><a href="index.php" class="button">Vai al sito</a></p>
        </div>
    </div>
</body>
</html>
