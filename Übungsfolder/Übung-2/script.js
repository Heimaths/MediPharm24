// Warten, bis das gesamte HTML geladen wurde
document.addEventListener('DOMContentLoaded', function() {
  
    // 1. Spielername erfragen und anzeigen
    let playerName = prompt("Bitte geben Sie Ihren Namen ein:");
    if (!playerName) {
      playerName = "Unbekannt"; // Fallback, falls der Nutzer keinen Namen eingibt
    }
    document.getElementById('playerName').textContent = playerName;
    
    // 2. Variablen für Timer und Versuche initialisieren
    let timeElapsed = 0;          // Sekunden, die seit Spielstart vergangen sind
    let attempts = 0;             // Anzahl der Versuche (Aufdecken von 2 Karten)
    let timerInterval = null;     // Variable für den Timer
  
    // Starten des Timers: Jede Sekunde wird timeElapsed erhöht und im HTML aktualisiert
    timerInterval = setInterval(function() {
      timeElapsed++;
      document.getElementById('time').textContent = timeElapsed;
    }, 1000);
    
    // 3. Array der Kartenbilder erstellen (8 Paare)
    // Hier gehen wir davon aus, dass sich die Bilder im Ordner "pics" befinden.
    // Für jedes Paar verwenden wir z. B. "card1.png" bis "card8.png"
    let cardImages = [
      "card1.png",
      "card2.png",
      "card3.png",
      "card4.png",
      "card5.png",
      "card6.png",
      "card7.png",
      "card8.png"
    ];
    
    // Jedes Bild duplizieren, sodass wir 16 Karten haben
    let cardsArray = cardImages.concat(cardImages);
    
    // 4. Array mischen (Fisher-Yates-Algorithmus)
    function shuffle(array) {
      for (let i = array.length - 1; i > 0; i--) {
        let j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]]; // Tausch der Elemente
      }
      return array;
    }
    cardsArray = shuffle(cardsArray);
    
    // 5. Spielvariablen für die Logik der Karten
    let flippedCards = [];    // Hier werden aktuell umgedrehte Karten gespeichert
    let lockBoard = false;    // Verhindert, dass während des Überprüfens weitere Karten angeklickt werden
    let matchedCount = 0;     // Anzahl der gefundenen Paare
  
    // 6. Erstellen der Karten und Hinzufügen zum Spielbereich
    const spielbereich = document.getElementById('spielbereich');
    
    // Für jede Karte im gemischten Array:
    cardsArray.forEach(function(cardImage, index) {
      // Erstellen eines div-Elements für die Karte
      let card = document.createElement('div');
      card.classList.add('karte'); // CSS-Klasse aus der style.css (definiert Größe und das verdeckte Bild)
      
      // Speichern des Bildnamens in einem benutzerdefinierten Datenelement
      card.dataset.card = cardImage;
      
      // Setzen einer eindeutigen ID (optional, aber hilfreich zur Fehlersuche)
      card.id = "card-" + index;
      
      // Hinzufügen eines Click-Event-Listeners
      card.addEventListener('click', flipCard);
      
      // Hinzufügen der Karte in den Spielbereich
      spielbereich.appendChild(card);
    });
    
    // 7. Funktion, die aufgerufen wird, wenn eine Karte angeklickt wird
    function flipCard() {
      // Wenn das Board gesperrt ist (z.B. während zwei Karten überprüft werden), oder die Karte bereits umgedreht wurde, wird nichts getan.
      if (lockBoard || this.classList.contains('flipped') || this.classList.contains('matched')) return;
      
      // Karte "umdrehen": Hintergrundbild ändern auf das zugewiesene Kartenbild
      // Hier nehmen wir an, dass die Bilder im Ordner "pics" liegen.
      this.style.backgroundImage = "url('pics/" + this.dataset.card + "')";
      this.classList.add('flipped');
      
      // Speichern der umgedrehten Karte
      flippedCards.push(this);
      
      // Wenn zwei Karten umgedreht wurden, muss überprüft werden, ob sie übereinstimmen.
      if (flippedCards.length === 2) {
        lockBoard = true; // Weitere Klicks werden blockiert
        
        // Versuchs-Zähler erhöhen
        attempts++;
        document.getElementById('attempts').textContent = attempts;
        
        // Überprüfen, ob beide Karten das gleiche Bild haben
        if (flippedCards[0].dataset.card === flippedCards[1].dataset.card) {
          // Karten passen zusammen: Nach kurzer Verzögerung wird das "Match"-Design gesetzt
          setTimeout(function() {
            flippedCards.forEach(function(card) {
              // Setzen eines speziellen Hintergrundbildes, das anzeigt, dass das Paar gefunden wurde
              card.style.backgroundImage = "url('pics/memoryBG1.png')";
              card.classList.add('matched');
              // Entfernen des Click-Listeners, damit die Karte nicht erneut angeklickt werden kann
              card.removeEventListener('click', flipCard);
            });
            // Zurücksetzen der flippedCards und Freigeben des Boards
            flippedCards = [];
            lockBoard = false;
            
            // Erhöhen des Zählers für gefundene Paare
            matchedCount++;
            
            // Wenn alle 8 Paare gefunden wurden, ist das Spiel beendet
            if (matchedCount === cardImages.length) {
              clearInterval(timerInterval); // Timer stoppen
              alert("Herzlichen Glückwunsch, " + playerName + "! Sie haben das Spiel in " + timeElapsed + " Sekunden und " + attempts + " Versuchen beendet.");
            }
          }, 1000); // 1 Sekunde Verzögerung, damit der Spieler die beiden Karten sehen kann
        } else {
          // Karten passen nicht zusammen: Nach kurzer Verzögerung werden beide Karten wieder umgedreht
          setTimeout(function() {
            flippedCards.forEach(function(card) {
              // Rücksetzen auf das verdeckte Bild (definiert in der CSS-Klasse "karte")
              card.style.backgroundImage = "url('pics/memoryBg.png')";
              card.classList.remove('flipped');
            });
            // Zurücksetzen der flippedCards und Freigeben des Boards
            flippedCards = [];
            lockBoard = false;
          }, 1000); // 1 Sekunde Verzögerung
        }
      }
    }
  });
  