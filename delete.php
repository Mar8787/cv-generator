<?php
require __DIR__ . "/data-base/connection.php";

// Read and validate the id
$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit("Error en id.");
}

// Get the photo path to delete it from the disk
$stmt = $pdo->prepare("SELECT photo_path FROM cv_versions WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    exit("No existe ese CV.");
}

$photoPath = $row["photo_path"] ?? null;

// Delete the record at db
$stmt = $pdo->prepare("DELETE FROM cv_versions WHERE id = ?");
$stmt->execute([$id]);

// If there is a photo, delete the file from the uploads/
if (!empty($photoPath) && str_starts_with($photoPath, "uploads/")) {
    $fullPath = __DIR__ . "/" . $photoPath;
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

// Go back to the empty form
header("Location: index.php");
exit;
