-- Spalte is_active zur Kunden-Tabelle hinzufügen
ALTER TABLE kunden ADD COLUMN is_active BOOLEAN DEFAULT TRUE;

-- Spalte is_visible zur Bestellungsdetails-Tabelle hinzufügen
ALTER TABLE bestellungsdetails ADD COLUMN is_visible BOOLEAN DEFAULT TRUE;

-- Index für is_active erstellen
CREATE INDEX idx_kunden_is_active ON kunden(is_active);

-- Index für is_visible erstellen
CREATE INDEX idx_bestellungsdetails_is_visible ON bestellungsdetails(is_visible); 