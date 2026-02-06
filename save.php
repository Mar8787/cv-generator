<?php

// Connection with data base
require __DIR__ . "/data-base/connection.php";

/**
 * Summary of post.
 * @param mixed $key Table field (email, phone...)
 * @param mixed $necesary When true, the field is required
 * @return string
 */
function post($key, $necesary = true)
{
    $val = trim((string)($_POST[$key] ?? ""));

    if ($necesary && $val === "") {
        http_response_code(400);
        exit("Falta el campo: $key");
    }

    return $val;
}

// Create a associative array with the CV versions data
$info = [
    "full_name" => post("full_name"),
    "profession" => post("profession"),
    "phone" => post("phone"),
    "email" => post("email"),
    "address" => post("address"),
    "about" => post("about"),

    "company" => post("company"),
    "position" => post("position"),
    "work_start" => post("work_start"),
    "work_end" => trim((string)($_POST["work_end"] ?? "")) ?: null, // Because it is not a required field, if it is empty, null is stored.
    "work_description" => post("work_description"),

    "school" => post("school"),
    "qualification" => post("qualification"),
    "edu_start" => post("edu_start"),
    "edu_end" => trim((string)($_POST["edu_end"] ?? "")) ?: null,
    "edu_description" => post("edu_description"),

    "skills" => post("skills"),
    "languages" => trim((string)($_POST["languages"] ?? "")) ?: null,
];

// Next version
$stmt = $pdo->query("SELECT COALESCE(MAX(version_num), 0) AS max_version FROM cv_versions");
$versionNum = (int)$stmt->fetch()["max_version"] + 1;

// Photo opcional
$photoPath = null;

if (!empty($_FILES["photo"]["name"]) && is_uploaded_file($_FILES["photo"]["tmp_name"])) {

    // Array of allowed file extensions
    $allowedType = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"];
    $extensionType = mime_content_type($_FILES["photo"]["tmp_name"]);

    // Check if it exists
    if (!isset($allowedType[$extensionType])) {
        http_response_code(400);
        exit("Formato de foto no permitido. Usa JPG/PNG/WEBP.");
    }

    $uploadDir = __DIR__ . "/uploads";

    // Create the directory if it does not exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $extension = $allowedType[$extensionType];

    // Create a unique name for photo
    $fileName = "version{$versionNum}_" . bin2hex(random_bytes(6)) . "." . $extension;
    $destination = $uploadDir . "/" . $fileName;

    // Move photo to upload folder since temp
    if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $destination)) {
        http_response_code(500);
        exit("No se pudo guardar la foto.");
    }

    // Store the relative file path
    $photoPath = "uploads/" . $fileName;
}

// SQL statement that inserts a CV into the table
$sql = "INSERT INTO cv_versions (
  version_num,
  full_name, profession, phone, email, address, about,
  company, position, work_start, work_end, work_description,
  school, qualification, edu_start, edu_end, edu_description,
  skills, languages, photo_path
) VALUES (
  :version_num,
  :full_name, :profession, :phone, :email, :address, :about,
  :company, :position, :work_start, :work_end, :work_description,
  :school, :qualification, :edu_start, :edu_end, :edu_description,
  :skills, :languages, :photo_path
)";

// Prepare the statement for execution
$stmt = $pdo->prepare($sql);

// Execute the query
$stmt->execute([
    ":version_num" => $versionNum,

    ":full_name" => $info["full_name"],
    ":profession" => $info["profession"],
    ":phone" => $info["phone"],
    ":email" => $info["email"],
    ":address" => $info["address"],
    ":about" => $info["about"],

    ":company" => $info["company"],
    ":position" => $info["position"],
    ":work_start" => $info["work_start"],
    ":work_end" => $info["work_end"],
    ":work_description" => $info["work_description"],

    ":school" => $info["school"],
    ":qualification" => $info["qualification"],
    ":edu_start" => $info["edu_start"],
    ":edu_end" => $info["edu_end"],
    ":edu_description" => $info["edu_description"],

    ":skills" => $info["skills"],
    ":languages" => $info["languages"],
    ":photo_path" => $photoPath,
]);
exit;
