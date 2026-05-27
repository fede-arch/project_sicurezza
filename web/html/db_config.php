<?php

$db_host = getenv('DB_HOST') ?: 'db';
$db_name = getenv('DB_NAME') ?: 'vulnerable_db';
$db_user = getenv('DB_USER') ?: 'webapp';
$db_pass = getenv('DB_PASS') ?: 'webpass';

function get_db_connection() {
    global $db_host, $db_name, $db_user, $db_pass;

    try {
        $dsn = "pgsql:host=$db_host;dbname=$db_name";
        $conn = new PDO($dsn, $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Errore di connessione: " . $e->getMessage());
    }
}


function execute_query($query) {
    $conn = get_db_connection();
    try {
        if (strpos($query, ';') !== false) {
            $parts = explode(';', $query, 2);
            $first_part = trim($parts[0]);
            $rest_part = isset($parts[1]) ? trim($parts[1]) : '';

            if ($rest_part !== '') {
                $other_queries = array_filter(array_map('trim', explode(';', $rest_part)));
                foreach ($other_queries as $q) {
                    if ($q === '') continue;
                    try {
                        $conn->exec($q);
                    } catch (PDOException $e) {
                    }
                }
            }

            if ($first_part === '') {
                return [];
            }

            $result = $conn->query($first_part);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }

        $result = $conn->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [['__error' => $e->getMessage()]];
    }
}?>