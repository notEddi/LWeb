README – Homework XHTML/CSS

Autore:
- Edoardo Bonaccolta

Repository GitHub:
- https://github.com/notEddi/LWeb.git

1) Descrizione dell’esercizio

Il progetto consiste nella realizzazione di un sito di recensioni cinematografiche
in XHTML 1.0 e CSS, con estetica ispirata a Windows 98.

Il sito è composto da:
- homepage stile desktop (index.xhtml)
- lista dei film in formato griglia (listaFilm.xhtml)
- schede dedicate ai singoli film con trama e recensioni (film1.xhtml … film6.xhtml)
- pagine di login e registrazione stile Win98 (login.xhtml, register.xhtml)

La presentazione è ottenuta tramite la libreria 98.css e uno stylesheet
personalizzato (style.css).

2) Collegamento agli esercizi delle slide

Il progetto riprende i concetti richiesti dagli esercizi di laboratorio:

- XHTML-1/2/3 → sintassi corretta, struttura ben formata, validazione W3C  
- XHTML-5/6/8/9/12 → uso di più pagine, link, attributi, entità e formattazione semantica  
- XHTML-CSS-1/2/3 → distinzione block/inline e documento valido  
- CSS-1/3/5/7/9/10 → box model, organizzazione degli elementi, liste, link e posizionamento  
- CSS-12/13 → principi di layout e composizione della pagina

La realizzazione del sito integra questi obiettivi in modo unitario senza
limitarsi ai singoli esercizi.

3) Caratteristiche XHTML del progetto

- XHTML 1.0 Strict con sintassi rigorosa e tag ben annidati  
- Pagine strutturate e collegate tra loro tramite link  
- Uso dei principali elementi XHTML: titoli, paragrafi, liste, immagini, form e input  
- Assenza di elementi deprecati  
- Utilizzo corretto di attributi (`alt`, `href`, `title`, ecc.)

4) Caratteristiche CSS del progetto

- Foglio di stile esterno + importazione tramite @import  
- Combinazione tra 98.css e regole personalizzate  
- Uso del box model per finestre, pulsanti e contenitori  
- Layout tramite `display: grid` (lista film) e `display: flex` (schede film)  
- Pseudo-classi per l’interazione (`:hover`, `:active`)  
- Stile coerente con l’interfaccia Windows 98

5) Struttura dei file

index.xhtml            		→ homepage  
listaFilm.xhtml        		→ lista dei film  
film1.xhtml … film6.xhtml  	→ schede film  
login.xhtml            		→ login  
register.xhtml         		→ registrazione  
style.css              		→ foglio di stile  
