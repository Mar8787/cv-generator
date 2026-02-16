<?php

// Connection with data base
require __DIR__ . "/data-base/connection.php";

session_start();

/**
 * Summary of post.
 * @param mixed $key Table field (email, phone...)
 * @param mixed $necesary When true, the field is required
 * @return string
 */
function post($key, $necesary = true) {
    $val = trim((string)($_POST[$key] ?? ""));

    if ($necesary && $val === "") {
        return null;
    }

    return $val;
}

/**
 * Allows letters and symbols in the input fields.
 * Summary of onlyText
 * @param mixed $s
 * @return bool
 */
function onlyText($s) {
    return (bool)preg_match('/^[\p{L}\s\.\,\-\(\)\@\#\/\&\:\;\!\%]+$/u', $s);
}

/**
 * Validate the email input.
 * Summary of validEmail
 * @param mixed $s
 * @return bool
 */
function validEmail($s) {
    return filter_var($s, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate the phone input.
 * Summary of validPhone
 * @param mixed $s
 * @return bool
 */
function validPhone($s) {
    return (bool)preg_match('/^[0-9+\s-]{7,20}$/', $s);
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
    "work_description" => trim((string)($_POST["work_description"] ?? "")) ?: null,

    "school" => post("school"),
    "qualification" => post("qualification"),
    "edu_start" => post("edu_start"),
    "edu_end" => trim((string)($_POST["edu_end"] ?? "")) ?: null,
    "edu_description" => trim((string)($_POST["edu_description"] ?? "")) ?: null,

    "skills" => post("skills"),
    "languages" => trim((string)($_POST["languages"] ?? "")) ?: null,
];

$errors = [];

// Required fields validation
$requiredKeys = ["full_name", "profession", "phone", "email", "address", "about", "company", "position", "work_start", "school", "qualification", "edu_start", "skills"];

foreach ($requiredKeys as $k) {
    if ($info[$k] === null) {
        $errors[$k] = "Este campo es obligatorio.";
    }
}

// Format validation
$letterFields = ["full_name", "profession", "address", "qualification"];

foreach ($letterFields as $field) {
    if ($info[$field] !== null && !onlyText($info[$field])) {
        $errors[$field] = "Formato no válido, no se permiten números.";
    }
}

if ($info["email"] !== null && !validEmail($info["email"])) {
    $errors["email"] = "El correo no tiene un formato válido.";
}

if ($info["phone"] !== null && !validPhone($info["phone"])) {
    $errors["phone"] = "Teléfono no válido. Usa números, +, espacios o guiones.";
}

// Dates validation
if ($info["work_start"] !== null && $info["work_end"] !== null) {
    if ($info["work_end"] < $info["work_start"]) {
        $errors["work_end"] = "La fecha de finalización no puede ser anterior a la fecha de inicio.";
    }
}

if ($info["edu_start"] !== null && $info["edu_end"] !== null) {
    if ($info["edu_end"] < $info["edu_start"]) {
        $errors["edu_end"] = "La fecha de finalización no puede ser anterior a la fecha de inicio.";
    }
}

// If there are errors, save them in the SESSION and return to the form
if (!empty($errors)) {
    $_SESSION["errors"] = $errors;
    $_SESSION["old_post"] = $info;
    header("Location: index.php?v=0");
    exit;
}

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
        $_SESSION["errors"] = ["photo" => "Formato de foto no permitido. Usa JPG/PNG/WEBP."];
        $_SESSION["old_post"] = $info;
        header("Location: index.php?v=0");
        exit;
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

// Redirect to the main page after saving
header("Location: index.php?v=0");
exit;
