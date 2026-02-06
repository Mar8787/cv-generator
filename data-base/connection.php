<?php
// Here we establish the database connection.

$DB_HOST = "127.0.0.1";
$DB_NAME = "cv_generator";
$DB_USER = "root";
$DB_PASS = "";

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

// We use PDO (a PHP class) to communicate with MySQL
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // It throws an exception if an error occurs
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // When calling fetch it returns an associative array
];

try {
    // Create the connection
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    exit("Error DB: " . $e->getMessage());
}
