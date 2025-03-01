// Diese externe JavaScript-Datei steuert den Taschenrechner.

// Wir warten, bis das gesamte HTML-Dokument geladen wurde,
// damit alle Elemente verfügbar sind, bevor wir darauf zugreifen.
document.addEventListener('DOMContentLoaded', function() {

    // Zugriff auf die HTML-Elemente (Eingabefelder, Ergebnisanzeige, Buttons, Historienliste)
    const zahl1Input = document.getElementById('zahl1'); // Erstes Eingabefeld für die erste Zahl
    const zahl2Input = document.getElementById('zahl2'); // Zweites Eingabefeld für die zweite Zahl
    const ergebnisDiv = document.getElementById('ergebnis'); // Div-Element zur Anzeige des Ergebnisses
    const historieListe = document.getElementById('historie'); // Liste zur Anzeige der Historie der Berechnungen

    // Zugriff auf die Buttons für die Rechenoperationen
    const addButton = document.getElementById('addieren');
    const subtractButton = document.getElementById('subtrahieren');
    const multiplyButton = document.getElementById('multiplizieren');
    const divideButton = document.getElementById('dividieren');

    // Diese Funktion führt die Berechnung aus und aktualisiert die Anzeige.
    // Der Parameter 'operator' bestimmt, welche Rechenart ausgeführt wird.
    function berechne(operator) {
        // 1. Auslesen der Texte aus den Eingabefeldern
        const zahl1Text = zahl1Input.value;
        const zahl2Text = zahl2Input.value;

        // 2. Umwandlung der Texte in Zahlen
        // parseFloat() konvertiert den Text in eine Fließkommazahl.
        const zahl1 = parseFloat(zahl1Text);
        const zahl2 = parseFloat(zahl2Text);

        // 3. Überprüfung, ob beide Eingaben gültige Zahlen sind.
        if (isNaN(zahl1) || isNaN(zahl2)) {
            ergebnisDiv.textContent = "Bitte geben Sie gültige Zahlen ein!";
            return; // Falls eine Eingabe ungültig ist, wird die Funktion hier beendet.
        }

        // 4. Durchführung der Berechnung anhand des übergebenen Operators.
        let ergebnis;
        switch (operator) {
            case '+':
                ergebnis = zahl1 + zahl2;
                break;
            case '-':
                ergebnis = zahl1 - zahl2;
                break;
            case '*':
                ergebnis = zahl1 * zahl2;
                break;
            case '/':
                // Überprüfung auf Division durch 0, da dies nicht erlaubt ist.
                if (zahl2 === 0) {
                    ergebnisDiv.textContent = "Division durch 0 ist nicht erlaubt!";
                    return; // Beendet die Funktion, wenn eine Division durch 0 versucht wird.
                }
                ergebnis = zahl1 / zahl2;
                break;
            default:
                // Sollte ein unbekannter Operator übergeben werden, wird eine Fehlermeldung angezeigt.
                ergebnisDiv.textContent = "Unbekannter Operator!";
                return;
        }

        // 5. Anzeige des Ergebnisses im Ergebnis-Div.
        ergebnisDiv.textContent = "Ergebnis: " + ergebnis;

        // 6. Erstellen eines Textes, der die Berechnung beschreibt.
        // Beispiel: "1 + 1 = 2"
        const berechnungsText = zahl1 + " " + operator + " " + zahl2 + " = " + ergebnis;

        // 7. Erstellen eines neuen Listenelements (li) für die Historienliste.
        const li = document.createElement('li');
        li.textContent = berechnungsText; // Setzt den Text des Listenelements

        // 8. Hinzufügen des neuen Listenelements zur Historienliste, damit die Berechnung gespeichert wird.
        historieListe.appendChild(li);
    }

    // Hinzufügen von Event-Listenern zu den Buttons.
    // Jeder Button löst beim Klicken die Funktion 'berechne' mit dem jeweiligen Operator aus.
    addButton.addEventListener('click', function() {
        berechne('+');
    });

    subtractButton.addEventListener('click', function() {
        berechne('-');
    });

    multiplyButton.addEventListener('click', function() {
        berechne('*');
    });

    divideButton.addEventListener('click', function() {
        berechne('/');
    });
});

//end
