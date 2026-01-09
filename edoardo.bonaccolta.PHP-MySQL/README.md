# README – Homework PHP-MySQL

## Autore
- Edoardo Bonaccolta

## Repository GitHub
- https://github.com/notEddi/LWeb.git

---

## 1) Descrizione dell’esercizio

Il progetto consiste nella realizzazione di una applicazione web per la gestione  
e recensione di film, sviluppata in **PHP e MySQL**, come estensione significativa  
del primo homework **XHTML/CSS**.

L’applicazione permette:
- la visualizzazione di una lista di film memorizzati in un database MySQL
- la visualizzazione della scheda di dettaglio di ciascun film
- la registrazione e il login degli utenti
- l’inserimento di recensioni e voti da parte degli utenti autenticati
- il controllo che impedisce a uno stesso utente di recensire più volte lo stesso film
- la gestione di un’area amministrativa che consente all’amministratore  
  di aggiungere ed eliminare film dal database

Il progetto integra la parte dinamica lato server con PHP e la persistenza  
dei dati tramite database relazionale MySQL.

---

## 2) Collegamento agli esercizi delle slide

Il progetto riprende ed estende gli esercizi proposti nella parte finale delle  
slides **PHP2**, in particolare:

- gestione di form HTML/XHTML tramite PHP
- accesso a database MySQL con `mysqli`
- esecuzione di query `SELECT` e `INSERT`
- gestione delle sessioni (variabile `$_SESSION`) per l’autenticazione degli utenti
- separazione tra logica applicativa e accesso ai dati
- utilizzo di file comuni inclusi in più pagine (ad esempio `connection.php` e `header.php`)  
  per la gestione centralizzata della connessione al database e degli elementi condivisi  
  dell’interfaccia

L’esercizio non si limita a un singolo esempio delle slide, ma integra più concetti  
in un’unica applicazione completa.

---

## 3) Accesso al database e sicurezza

Per l’accesso al database MySQL sono stati utilizzati i **prepared statements**  
(metodi `prepare`, `bind_param` ed `execute` della libreria `mysqli`).

Questa scelta rappresenta una estensione rispetto agli esempi base mostrati  
nelle slide del corso ed è stata adottata per migliorare la sicurezza  
dell’applicazione, in particolare per prevenire vulnerabilità di tipo  
**SQL Injection**.

L’utilizzo dei prepared statements rimane pienamente coerente con i principi  
illustrati a lezione sull’uso di PHP e MySQL.

---

## 4) Installazione e configurazione

Il progetto include uno script di installazione (`install.php`) che consente  
di creare e popolare il database necessario al funzionamento  
dell’applicazione.

Per installare l’applicazione su un altro server è sufficiente:
- copiare la directory del progetto nel document root del web server
- configurare i parametri di accesso a MySQL nel file `connection.php`
- accedere via browser allo script `install.php`

Eventuali operazioni di reset del database possono essere effettuate  
tramite lo script `resetDatabase.php`.

Non vengono utilizzati percorsi assoluti, in modo da garantire la portabilità  
dell’applicazione su sistemi diversi.

---

## 5) Struttura dei file

- `poster/` → directory contenente le immagini dei poster dei film

- `aggiungiFilm.php` → inserimento di nuovi film nel database  
- `aggiungiRecensione.php` → inserimento di una recensione da parte di un utente  
- `aggiungiWatchlist.php` → aggiunta di un film alla watchlist dell’utente  
- `areaUtente.php` → area personale dell’utente autenticato  

- `config.php` → file di configurazione generale dell’applicazione  
- `connection.php` → gestione della connessione al database MySQL  

- `eliminaRecensione.php` → eliminazione di una recensione  
- `modificaRecensione.php` → modifica di una recensione esistente  

- `film.php` → scheda di dettaglio del film con recensioni  
- `gestisciFilm.php` → gestione dei film  

- `header.php` → intestazione comune alle pagine  

- `index.php` → homepage dell’applicazione  
- `listaFilm.php` → elenco dei film presenti nel database  

- `login.php` → login utente  
- `logout.php` → logout e chiusura della sessione  
- `register.php` → registrazione di un nuovo utente  

- `install.php` → script di installazione del database  
- `resetDatabase.php` → reset e ripopolamento del database  

- `rimuoviWatchlist.php` → rimozione di un film dalla watchlist  

- `style.css` → foglio di stile principale  
- `style2.css` → foglio di stile aggiuntivo  
- `style3.css` → foglio di stile aggiuntivo

