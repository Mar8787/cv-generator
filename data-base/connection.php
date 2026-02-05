<?php
// Aquí realizamos la conexión con la DB

$DB_HOST = "127.0.0.1";
$DB_NAME = "cv_generator";
$DB_USER = "root";
$DB_PASS = "";

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

// Usamos PDO (clase de PHP) para hablar con MySQL
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // lanza excepción siu hay error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // cuando haga fetch me da un array asociativo
];

try {
    // crea la conexion
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    exit("Error DB: " . $e->getMessage());
}
