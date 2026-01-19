# README – Homework XML-DOM

**Autore:** Edoardo Bonaccolta  
**Repository GitHub:** [https://github.com/notEddi/LWeb.git](https://github.com/notEddi/LWeb.git)

---

## 1) Sintesi del Progetto
Applicazione web in stile **"Windows 98"** per la gestione e recensione di film.
L'architettura è **IBRIDA**:
* **MySQL:** Gestione Utenti, Login e Sicurezza.
* **XML (DOM):** Gestione dati Film, Recensioni e Watchlist (database documentale).

---

## 2) Riferimenti Didattici (Slide XML1 / XML2)
Il progetto implementa le tecniche discusse nel modulo XML:
* **Definizione Linguaggio (XML1):** Creazione di formati XML personalizzati e "Well-formed".
* **Validazione XSD (XML2):** Uso di XML Schema per la tipizzazione forte dei dati (es. `xs:int`), preferito alle DTD.
* **Manipolazione DOM (XML2):** Lettura/Scrittura tramite classe PHP `DOMDocument` (nodi e attributi).
* **Validazione Runtime (XML2):** Uso del metodo `$dom->schemaValidate()` per garantire la validità dei dati a ogni modifica.

---

## 3) Installazione Rapida
1.  **Configurazione:** Modificare `dati_generali.php` con le credenziali del DB locale.
2.  **Setup:** Aprire nel browser `install.php`.
    * *-> Crea automaticamente Database, Tabella Utenti e File XML/XSD iniziali.*

### Credenziali Admin (Default)
* **User:** `admin`
* **Pass:** `admin`

> **Nota:** Per il ripristino totale dell'ambiente usare lo script `resetDatabase.php`.

---

## 4) Struttura dei file

### 📂 Cartelle
* `xml/` → Contiene i file dati (`.xml`) e gli schemi di validazione (`.xsd`).
* `poster/` → Directory contenente le immagini caricate dei poster dei film.

### ⚙️ Gestione Dati (Back-end XML/SQL)
* `config.php` → File di configurazione, avvio sessioni e inclusione manager.
* `connection.php` → Connessione al database MySQL (solo per utenti).
* `dati_generali.php` → Credenziali DB.
* `xmlFilmManager.php` → Funzioni DOM per lettura/scrittura `film.xml` e validazione XSD.
* `xmlRecensioniManager.php` → Funzioni DOM per gestione recensioni.
* `xmlWatchlistManager.php` → Funzioni DOM per gestione watchlist.

### 🌐 Pagine Pubbliche e Utente
* `index.php` → Homepage con i film più votati.
* `listaFilm.php` → Elenco completo dei film (da XML).
* `film.php` → Scheda dettaglio film, recensioni e media voti.
* `login.php` → Pagina di login.
* `register.php` → Pagina di registrazione nuovo utente.
* `logout.php` → Script di disconnessione.
* `areaUtente.php` → Dashboard utente (o admin) con le proprie attività.

### 🛠️ Funzionalità Admin / Gestione Contenuti
* `aggiungiFilm.php` → Form per inserimento film e upload poster.
* `gestisciFilm.php` → Pannello admin per elenco ed eliminazione film.
* `aggiungiRecensione.php` → Script di salvataggio recensione su XML.
* `modificaRecensione.php` → Form modifica recensione.
* `rimuoviRecensione.php` → Eliminazione recensione.
* `aggiungiWatchlist.php` → Aggiunta film ai preferiti.
* `rimuoviWatchlist.php` → Rimozione film dai preferiti.

### 🔌 Utility di Sistema
* `install.php` → Script di installazione automatica (First Run).
* `resetDatabase.php` → Script per il ripristino dell'ambiente iniziale.
* `header.php` → Componente grafico comune (menu navigazione).

### 🎨 Stili
* `style.css` → Foglio di stile principale (override 98.css).
* `style2.css` → Stili per dashboard admin.
* `style3.css` → Stili per schede film.
* *(Dipendenza esterna: `98.css` via CDN per interfaccia Windows 98)*
