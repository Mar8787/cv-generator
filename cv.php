<?php
require __DIR__ . "/data-base/connection.php";

// Read the URL and get the valor of v and turn in integer
$v = (int)($_GET["v"] ?? 0);

function normalizeInput($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8");
}

if ($v <= 0) {
    exit("Falta el parámetro v (no sabemos versión del CV");
}

$stmt = $pdo->prepare("SELECT * FROM cv_versions WHERE version_num = ?");
$stmt->execute([$v]);
$cv = $stmt->fetch();

if (!$cv) {
    http_response_code(404);
    exit("No existe la versión $v");
}

// REVISAR
// Date formating
$workStart = !empty($cv["work_start"]) ? date("d/m/Y", strtotime($cv["work_start"])) : "";
$workEnd = !empty($cv["work_end"]) ? date("d/m/Y", strtotime($cv["work_end"])) : "Actualidad";

$eduStart = !empty($cv["edu_start"]) ? date("d/m/Y", strtotime($cv["edu_start"])) : "";
$eduEnd = !empty($cv["edu_end"]) ? date("d/m/Y", strtotime($cv["edu_end"])) : "Actualidad";

// Photo
$photoSrc = !empty($cv["photo_path"]) ? $cv["photo_path"] : "assets/usuario-generico.png";

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS link -->
    <link rel="stylesheet" href="style/style_cv.css">
    <title>Curriculum Versión <?= $v ?></title>
</head>

<body>
    <div id="father">
        <div class="left-sidebar">
            <img id="photo" src= <?= $photoSrc ?> alt="foto carnet">
            <div id="contact">
                <h3 class="title-h" id="h-contact">Contacto</h3>
                <p id="tlf" class="p-left-sidebar"><img class="icon" src="assets/telefono.png" alt="icono movil"> <?= normalizeInput($cv["phone"]) ?></p>
                <p id="email" class="p-left-sidebar"><img class="icon" src="assets/mail.png" alt="icono mail"> <?= normalizeInput($cv["email"]) ?></p>
                <p id="adress" class="p-left-sidebar"><img class="icon" src="assets/casa.png" alt="icono casa"> <?= normalizeInput($cv["address"]) ?></p>
            </div>

            <hr class="hr-left-sidebar">

            <div id="education">
                <h3 class="title-h" id="h-education">Educación</h3>
                <h4 id="qualification" class="h4-left-sidebar"><?= normalizeInput($cv["qualification"]) ?></h4>
                <p id="school" class="p-left-sidebar"><?= normalizeInput($cv["school"]) ?></p>
                <p id="start-date-edu" class="p-left-sidebar"><?= normalizeInput($eduStart)  ?> | <?= normalizeInput($eduEnd) ?></p>

                <?php if (!empty($cv["edu_description"])): ?>
                    <ul class="list-left-sidebar">
                        <li><?= normalizeInput($cv["edu_description"]) ?></li>
                    </ul>
                <?php endif; ?>
            </div>

            <hr class="hr-left-sidebar">

            <div id="skills">
                <h3 id="h-skills" class="title-h">Habilidades</h3>
                <ul class="list-left-sidebar">
                    <li><?= normalizeInput($cv["skills"]) ?></li>
                </ul>

                <?php if (!empty($cv["languages"])): ?>
                    <h4 id="languages" class="h4-left-sidebar">Idiomas</h4>
                    <ul class="list-left-sidebar">
                        <li><?= normalizeInput($cv["languages"]) ?></li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div id="general">
            <div class="title-bar">
                <h1><?= normalizeInput($cv["full_name"]) ?></h1>
                <h2><?= normalizeInput($cv["profession"]) ?></h2>
            </div>

            <div id="about-me">
                <h3 class="h3-general">Sobre mí</h3>
                <p class="p-general"><?= normalizeInput($cv["about"]) ?></p>

                <hr class="hr-general">
            </div>

            <div id="work">
                <h3 class="h3-general">Experiencia laboral</h3>

                <div id="data-work">
                    <h4 class="h4-general"><?= normalizeInput($cv["position"]) ?></h4>
                    <p class="p-general"><?= normalizeInput($cv["company"]) ?> <br> <?= normalizeInput($workStart) ?> | <?= normalizeInput($workEnd) ?></p>

                    <?php if (!empty($cv["work_description"])): ?>
                        <ul id="list-general">
                            <li><?= normalizeInput($cv["work_description"]) ?></li>
                        </ul>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- Print / PDF options (browser) -->
    <div style="padding-left: 3%; padding-bottom: 3%;">
        <button class="btn" onclick="window.print()">Imprimir / Guardar como PDF</button>
    </div>
</body>

</html>