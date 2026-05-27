<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Militare - Login</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>DATABASE MILITARE</h1>
            <div class="subtitle">Operazioni Classificate</div>
        </div>
        
        <div class="menu-grid">
            <a href="login.php" class="menu-item">
                <div class="menu-title">LOGIN</div>
                <div class="menu-desc">Accesso personale autorizzato</div>
            </a>
        </div>
        
        <div class="security-info">
            <h3>VULNERABILITÀ IMPLEMENTATE</h3>
            <ul>
                <li>- SQL Injection in tutti i form di ricerca e login</li>
                <li>- Esposizione di chiavi private crittografiche</li>
                <li>- Nessuna sanitizzazione degli input utente</li>
                <li>- Credenziali hardcoded nel codice</li>
                <li>- Messaggi di errore dettagliati esposti</li>
                <li>- Nessun controllo di autorizzazione</li>
            </ul>
        </div>
    </div>
</body>
</html>