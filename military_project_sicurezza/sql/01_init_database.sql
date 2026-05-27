
CREATE USER webapp WITH PASSWORD 'webpass';

CREATE TABLE classificazione(
    id SERIAL PRIMARY KEY,
    nome VARCHAR(30) NOT NULL UNIQUE,
    livello INT NOT NULL,
    descrizione TEXT 
);

INSERT INTO classificazione (nome, livello, descrizione) VALUES
('NON_CLASSIFICATO', 0, 'Informazioni pubbliche'),
('RISERVATO', 1, 'Accesso ristretto a personale autorizzato'),
('SEGRETO', 2, 'Informazioni sicurezza nazionale'),
('TOP_SECRET', 3, 'Informazioni di difesa nazionale'),
('MAX_TOP_SECRET', 4, 'Informazioni di massimo livello');

CREATE TABLE grado_militare(
    id SERIAL PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    gerarchia INT NOT NULL,
    categoria VARCHAR(20) NOT NULL
);

INSERT INTO grado_militare (nome, gerarchia, categoria) VALUES
('Soldato', 1, 'TRUPPA'),
('Caporale', 2, 'TRUPPA'),
('Sergente', 3, 'SOTTOUFFICIALE'),
('Maresciallo', 4, 'SOTTOUFFICIALE'),
('Tenente', 5, 'UFFICIALE'),
('Capitano', 6, 'UFFICIALE'),
('Maggiore', 7, 'UFFICIALE'),
('Colonnello', 8, 'UFFICIALE'),
('Generale di Brigata', 9, 'GENERALE'),
('Generale di Divisione', 10, 'GENERALE');

CREATE TABLE stato_operativo (
    id SERIAL PRIMARY KEY,
    codice VARCHAR(20) NOT NULL UNIQUE,
    descrizione VARCHAR(100)
);

INSERT INTO stato_operativo (codice, descrizione) VALUES
('ATTIVO', 'Missione in corso'),
('STANDBY', 'In attesa di ordini'),
('TERMINATO', 'Missione completata'),
('COMPROMESSO', 'Sicurezza violata'),
('EMERGENZA', 'Situazione critica');

CREATE TABLE algoritmo_crittografia (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(30) NOT NULL UNIQUE,
    tipo VARCHAR(20) NOT NULL,
    lunghezza_chiave INT,
    sicurezza VARCHAR(20) NOT NULL
);

INSERT INTO algoritmo_crittografia (nome, tipo, lunghezza_chiave, sicurezza) VALUES
('AES-256', 'SIMMETRICO', 256, 'MILITARE'),
('RSA-4096', 'ASIMMETRICO', 4096, 'MILITARE'),
('RSA-2048', 'ASIMMETRICO', 2048, 'ALTA'),
('ECC-256', 'ASIMMETRICO', 256, 'MILITARE'),
('SHA-256', 'HASH', 256, 'ALTA'),
('SHA-512', 'HASH', 512, 'MILITARE');

-- TABELLE PRINCIPALI --
-- (12 colonne)
CREATE TABLE personale (
    id SERIAL PRIMARY KEY,
    matricola VARCHAR(20) NOT NULL UNIQUE,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    grado_id INT NOT NULL,
    livello_clearance INT NOT NULL,
    reparto VARCHAR(100),
    email VARCHAR(100),
    telefono_criptato VARCHAR(20),
    data_arruolamento DATE,
    password_hash VARCHAR(255),
    note_riservate TEXT,
    FOREIGN KEY (grado_id) REFERENCES grado_militare(id) ON DELETE CASCADE
);

INSERT INTO personale (matricola, nome, cognome, grado_id, livello_clearance, reparto, email, password_hash, note_riservate) VALUES
('M001', 'Luca', 'Bianchi', 1, 1, 'Truppa Operativa', 'luca.bianchi@mil.it', 'pass1', 'Soldato semplice'),
('M002', 'Marco', 'Rossi', 2, 1, 'Truppa Operativa', 'marco.rossi@mil.it', 'pass2', 'Caporale squadra'),
('M003', 'Giulia', 'Verdi', 3, 2, 'Sottufficiali', 'giulia.verdi@mil.it', 'pass3', 'Sergente reparto logistica'),
('M004', 'Francesco', 'Neri', 4, 2, 'Sottufficiali', 'francesco.neri@mil.it', 'pass4', 'Maresciallo supervisione'),
('M005', 'Sara', 'Galli', 5, 3, 'Ufficiali', 'sara.galli@mil.it', 'pass5', 'Tenente operazioni'),
('M006', 'Alessandro', 'Ferrari', 6, 3, 'Ufficiali', 'alessandro.ferrari@mil.it', 'pass6', 'Capitano comando unità'),
('M007', 'Chiara', 'Russo', 7, 3, 'Ufficiali', 'chiara.russo@mil.it', 'pass7', 'Maggiore pianificazione'),
('M008', 'Davide', 'Conti', 8, 4, 'Ufficiali', 'davide.conti@mil.it', 'pass8', 'Colonnello coordinamento'),
('M009', 'Elena', 'Marini', 9, 4, 'Generali', 'elena.marini@mil.it', 'pass9', 'Generale di Brigata'),
('M010', 'Stefano', 'De Luca', 10, 4, 'Generali', 'stefano.deluca@mil.it', 'pass10', 'Generale di Divisione');

CREATE TABLE chiavi_crittografiche (
    id SERIAL PRIMARY KEY,
    identificativo VARCHAR(50) NOT NULL UNIQUE,
    proprietario_id INT NOT NULL,
    algoritmo_id INT NOT NULL,
    chiave_pubblica TEXT NOT NULL,
    chiave_privata TEXT NOT NULL,
    passphrase VARCHAR(255),
    data_generazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_scadenza TIMESTAMP,
    revocata BOOLEAN DEFAULT FALSE,
    classificazione_id INT NOT NULL,
    FOREIGN KEY (proprietario_id) REFERENCES personale(id) ON DELETE CASCADE,
    FOREIGN KEY (algoritmo_id) REFERENCES algoritmo_crittografia(id) ON DELETE CASCADE,
    FOREIGN KEY (classificazione_id) REFERENCES classificazione(id) ON DELETE CASCADE
);

-- Inserimento chiavi di test
INSERT INTO chiavi_crittografiche (identificativo, proprietario_id, algoritmo_id, chiave_pubblica, chiave_privata, passphrase, data_scadenza, classificazione_id) VALUES
('KEY-RSA-001', 1, 2, 
'-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAtest...
-----END PUBLIC KEY-----',
'-----BEGIN RSA PRIVATE KEY-----
MIIJKAIBAAKCAgEAtest_private_key_data_here...
-----END RSA PRIVATE KEY-----',
'TopSecret2024!', '2025-12-31', 4),

('KEY-AES-002', 2, 1,
'AES256-PUBLIC-MOCK-KEY',
'AES256-PRIVATE-SECRET-KEY-DO-NOT-SHARE',
'CryptoPass2024', '2025-12-31', 3);

CREATE TABLE operazioni_intercettazione (
    id SERIAL PRIMARY KEY,
    codice_operazione VARCHAR(50) NOT NULL UNIQUE,
    nome_operazione VARCHAR(200) NOT NULL,
    comandante_id INT NOT NULL,
    stato_id INT NOT NULL,
    classificazione_id INT NOT NULL,
    data_inizio TIMESTAMP,
    data_fine TIMESTAMP,
    obiettivo TEXT,
    descrizione TEXT,
    budget_stanziato DECIMAL(15,2),
    note_operative TEXT,
    FOREIGN KEY (comandante_id) REFERENCES personale(id) ON DELETE CASCADE,
    FOREIGN KEY (stato_id) REFERENCES stato_operativo(id) ON DELETE CASCADE,
    FOREIGN KEY (classificazione_id) REFERENCES classificazione(id) ON DELETE CASCADE
);

INSERT INTO operazioni_intercettazione (codice_operazione, nome_operazione, comandante_id, stato_id, classificazione_id, data_inizio, obiettivo, descrizione, note_operative) VALUES
('OP-FALCON-2024', 'Operazione Falcon Eye', 1, 1, 4, '2024-01-15', 
'Intercettazione cellula terroristica internazionale', 
'Monitoraggio comunicazioni gruppo sospetto', 
'Richiede massima discrezione'),

('OP-SHADOW-2024', 'Operazione Shadow Net', 1, 1, 3, '2024-03-01',
'Controspionaggio industriale',
'Identificazione attività spionaggio economico',
'Collaborazione con agenzie partner');

CREATE TABLE bersagli (
    id SERIAL PRIMARY KEY,
    codice_bersaglio VARCHAR(50) NOT NULL UNIQUE,
    nome_copertura VARCHAR(100),
    nome_reale VARCHAR(100),
    nazionalita VARCHAR(50),
    organizzazione VARCHAR(200),
    livello_minaccia VARCHAR(20) NOT NULL,
    telefono VARCHAR(50),
    email VARCHAR(100),
    indirizzo TEXT,
    coordinate_gps VARCHAR(100),
    note_intelligence TEXT,
    classificazione_id INT NOT NULL,
    FOREIGN KEY (classificazione_id) REFERENCES classificazione(id) ON DELETE CASCADE
);

INSERT INTO bersagli (codice_bersaglio, nome_copertura, nome_reale, nazionalita, organizzazione, livello_minaccia, telefono, email, note_intelligence, classificazione_id) VALUES
('TGT-001-ALFA', 'Alessandro Bruno', 'Ahmad Al-Rashid', 'Sconosciuta', 'Cellula Red Dawn', 'CRITICO', '+39-XXX-XXXXX', 'bruno@email.fake', 'Sospetto coordinatore rete', 4),
('TGT-002-BETA', 'Marco Colombo', 'Igor Volkov', 'Russia', 'Gruppo Phantom', 'ALTO', '+39-YYY-YYYYY', 'colombo@mail.fake', 'Possibile agente straniero', 3),
('TGT-003-GAMMA', 'Lucia Romano', 'Elena Petrova', 'Ucraina', 'Organizzazione sconosciuta', 'MEDIO', '+39-ZZZ-ZZZZZ', 'romano@posta.fake', 'Sotto osservazione', 2);

CREATE TABLE operazione_bersaglio (
    operazione_id INT NOT NULL,
    bersaglio_id INT NOT NULL,
    data_assegnazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    priorita INT DEFAULT 5,
    PRIMARY KEY (operazione_id, bersaglio_id),
    FOREIGN KEY (operazione_id) REFERENCES operazioni_intercettazione(id) ON DELETE CASCADE,
    FOREIGN KEY (bersaglio_id) REFERENCES bersagli(id) ON DELETE CASCADE
);

INSERT INTO operazione_bersaglio (operazione_id, bersaglio_id, priorita) VALUES
(1, 1, 10),
(1, 2, 8),
(2, 2, 9),
(2, 3, 6);

CREATE TABLE comunicazioni_intercettate (
    id SERIAL PRIMARY KEY,
    operazione_id INT NOT NULL,
    bersaglio_mittente_id INT,
    bersaglio_destinatario_id INT,
    timestamp_intercettazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    contenuto_criptato TEXT,
    contenuto_decriptato TEXT,
    chiave_decrittazione_id INT,
    analista_id INT,
    FOREIGN KEY (operazione_id) REFERENCES operazioni_intercettazione(id) ON DELETE CASCADE,
    FOREIGN KEY (bersaglio_mittente_id) REFERENCES bersagli(id) ON DELETE CASCADE,
    FOREIGN KEY (bersaglio_destinatario_id) REFERENCES bersagli(id) ON DELETE CASCADE,
    FOREIGN KEY (chiave_decrittazione_id) REFERENCES chiavi_crittografiche(id) ON DELETE CASCADE,
    FOREIGN KEY (analista_id) REFERENCES personale(id) ON DELETE CASCADE
);

INSERT INTO comunicazioni_intercettate (operazione_id, bersaglio_mittente_id, bersaglio_destinatario_id, timestamp_intercettazione, contenuto_criptato, contenuto_decriptato, chiave_decrittazione_id, analista_id) VALUES
(1, 1, 2, '2025-10-08 14:32:10', '3f9a8c4b57e1d9f0a2c9', 'Incontro previsto per le 22 vicino al ponte vecchio.', 2, 7),
(1, 2, 1, '2025-10-08 21:47:53', 'ae91b4cf3d17e89c', '[Parziale] Coordinate trasmesse: 41.9028, 12.4964', 2, 7),
(2, 2, 3, '2025-10-07 09:13:41', 'd9e2a3b4c7f8a1b2c3d4e5f6', NULL, 1, 8), 
(2, 3, 2, '2025-10-08 16:04:25', '91a7f3e5b2c8d9a1', 'Richiesta di aggiornamento sul canale secondario in corso.', 1, 9),
(1, 1, 3, '2025-10-09 00:23:11', '7b2e45f9a3d17e6f', 'Messaggio di controllo riuscito. Nessuna anomalia riscontrata.', 1, 6);

GRANT ALL PRIVILEGES ON DATABASE vulnerable_db TO webapp;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO webapp;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO webapp;