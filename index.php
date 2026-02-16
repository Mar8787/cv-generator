<?php

// Connects to the database. DIR prevents relative path errors
require __DIR__ . "/data-base/connection.php";

session_start();

$errors = $_SESSION["errors"] ?? [];
$oldPost = $_SESSION["old_post"] ?? [];
unset($_SESSION["errors"], $_SESSION["old_post"]);

function noNumber($s) {
    if (!ctype_alpha($s)) {
        echo "Error: Solo se permiten letras.";
    }
}

/**
 * Security, normalize user input
 * Summary of normalizeInput
 * @param mixed $s
 * @return string
 */
function normalizeInput($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
}

// Returns submitted data, DB data, or empty string
function fieldValue($key, $oldPost, $old) {
    return $oldPost[$key] ?? ($old[$key] ?? "");
}

// CV versions
$stmt = $pdo->query("SELECT id, version_num, created_date FROM cv_versions ORDER BY version_num DESC");
$versions = $stmt->fetchAll();

// CV data. (PDO -> PHP Data Objects, is the connection with db)
$old = null;
$v = (int)($_GET["v"] ?? 0);

if ($v > 0) {
    $stmt = $pdo->prepare("SELECT * FROM cv_versions WHERE version_num = ?");
    $stmt->execute([$v]);
    $old = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de CV</title>

    <!-- bootstrap and css links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="style/style_index.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

</head>

<body>

    <div class="title-web container text-center mt-5">
        <h1>Generador de CV</h1>
    </div>


    <div class="container mt-4 mb-5">
        <div class="row g-4 align-items-start">

            <!-- Left, versions -->
            <aside class="col-12 col-lg-3">
                <div class="panel">
                    <h3 class="mb-2">Todos tus CV</h3>

                    <?php if (empty($versions)): ?>
                        <p class="text-muted mb-0">No hay versiones aún, guarda un CV.</p>
                    <?php else: ?>

                        <?php foreach ($versions as $version): ?>
                            <?php $isActive = ($v === (int)$version["version_num"]); ?>
                            <div class="version-item">
                                <div>
                                    <div class="<?= $isActive ? "fw-bold" : "" ?>">Versión <?= (int)$version["version_num"] ?></div>
                                    <small class="text-muted"><?= normalizeInput($version["created_date"]) ?></small>
                                </div>
                                <div class="version-actions text-end">
                                    <a class="btn btn-outline-primary" href="index.php?v=<?= (int)$version["version_num"] ?>">Cargar</a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </div>
            </aside>

            <!-- Center, form -->
            <main class="col-12 col-lg-7">
                <form method="post" action="save.php" enctype="multipart/form-data">

                    <!-- Personal Information -->
                    <div class="cnt-data mb-5">
                        <h2>Datos personales</h2>

                        <label for="name-input" class="form-label mt-3">Nombre y apellidos <span class="text-danger">*</span>
                        </label>
                        <input id="name-input" name="full_name" type="text"
                            class="form-control <?= !empty($errors["full_name"]) ? "is-invalid" : "" ?>"
                            placeholder="Ej. Josefa Sánchez Pérez" aria-describedby="name-help"
                            value="<?= normalizeInput(fieldValue("full_name", $oldPost, $old ?? [])) ?>">
                        <?php if (!empty($errors["full_name"])): ?>
                            <div class="invalid-feedback"><?= normalizeInput($errors["full_name"]) ?></div>
                        <?php else: ?>
                            <div id="name-help" class="form-text">Introduce tu nombre y apellidos completos.</div>
                        <?php endif; ?>

                        <label for="profession-input" class="form-label mt-3">Profesión <span class="text-danger">*</span></label>
                        <input id="profession-input" name="profession" type="text"
                            class="form-control <?= !empty($errors["profession"]) ? "is-invalid" : "" ?>"
                            placeholder="Ej. Developer" aria-describedby="profession-help" 
                            value="<?= normalizeInput(fieldValue("profession", $oldPost, $old ?? [])) ?>">
                        <?php if (!empty($errors["profession"])): ?>
                            <div class="invalid-feedback"><?= normalizeInput($errors["profession"]) ?></div>
                        <?php else: ?>
                            <div id="profession-help" class="form-text">Describe tu profesión en una palabra.</div>
                        <?php endif; ?>

                        <label for="tlf-input" class="form-label mt-3">Teléfono <span class="text-danger">*</span></label>
                        <input id="tlf-input" name="phone" type="tel"
                            class="form-control <?= !empty($errors["phone"]) ? "is-invalid" : "" ?>"
                            placeholder="Ej. 999 99 99 99" aria-describedby="tlf-help" 
                            value="<?= normalizeInput(fieldValue("phone", $oldPost, $old ?? [])) ?>">
                        <?php if (!empty($errors["phone"])): ?>
                            <div class="invalid-feedback"><?= normalizeInput($errors["phone"]) ?></div>
                        <?php else: ?>
                            <div id="tlf-help" class="form-text">No compartiremos tu teléfono con terceros.</div>
                        <?php endif; ?>

                        <label for="email-input" class="form-label mt-3">Correo electrónico <span class="text-danger">*</span></label>
                        <input id="email-input" name="email" type="text"
                            class="form-control <?= !empty($errors["email"]) ? "is-invalid" : "" ?>"
                            placeholder="Ej. ejemplo@gmail.com" aria-describedby="email-help" 
                            value="<?= normalizeInput(fieldValue("email", $oldPost, $old ?? [])) ?>">
                        <?php if (!empty($errors["email"])): ?>
                            <div class="invalid-feedback"><?= normalizeInput($errors["email"]) ?></div>
                        <?php else: ?>
                            <div id="email-help" class="form-text">No compartiremos tu email con terceros.</div>
                        <?php endif; ?>

                        <label for="adress-input" class="form-label mt-3">Localidad de residencia <span class="text-danger">*</span></label>
                        <input id="adress-input" name="address" type="text"
                            class="form-control <?= !empty($errors["address"]) ? "is-invalid" : "" ?>"
                            placeholder="Ej. Jerez (Cádiz)" aria-describedby="adress-help" 
                            value="<?= normalizeInput(fieldValue("address", $oldPost, $old ?? [])) ?>">
                        <?php if (!empty($errors["address"])): ?>
                            <div class="invalid-feedback"><?= normalizeInput($errors["address"]) ?></div>
                        <?php else: ?>
                            <div id="adress-help" class="form-text">Introduce tu localidad y provincia (entre paréntesis).</div>
                        <?php endif; ?>

                        <label for="about" class="form-label mt-3">Sobre ti <span class="text-danger">*</span></label>
                        <textarea id="about" name="about"
                            class="form-control <?= !empty($errors["about"]) ? "is-invalid" : "" ?>"
                            rows="5" maxlength="250" aria-describedby="about-help" 
                            placeholder="Ej. Desarrolladora de aplicaciones web..."><?= normalizeInput(fieldValue("about", $oldPost, $old ?? [])) ?></textarea>
                        <?php if (!empty($errors["about"])): ?>
                            <div class="invalid-feedback"><?= normalizeInput($errors["about"]) ?></div>
                        <?php else: ?>
                            <div id="about-help" class="form-text">Descríbete en menos de 250 caracteres.</div>
                        <?php endif; ?>
                    </div>

                    <!-- Work experience -->
                    <div class="cnt-data mb-5">
                        <h2>Experiencia laboral</h2>

                        <label for="company-input" class="form-label mt-3">Empresa <span class="text-danger">*</span>
                        </label>
                        <input id="company-input" name="company" type="text"
                            class="form-control <?= !empty($errors["company"]) ? "is-invalid" : "" ?>"
                            placeholder="Ej. Amazon" aria-describedby="company-help" 
                            value="<?= normalizeInput(fieldValue("company", $oldPost, $old ?? [])) ?>">
                        <?php if (!empty($errors["company"])): ?>
                            <div class="invalid-feedback"><?= normalizeInput($errors["company"]) ?></div>
                        <?php else: ?>
                            <div id="company-help" class="form-text">Introduce el nombre de la empresa donde trabajaste.</div>
                        <?php endif; ?>

                        <label for="position-input" class="form-label mt-3">Puesto <span class="text-danger">*</span></label>
                        <input id="position-input" name="position" type="text"
                            class="form-control <?= !empty($errors["position"]) ? "is-invalid" : "" ?>"
                            placeholder="Ej. Developer backend" aria-describedby="position-help" 
                            value="<?= normalizeInput(fieldValue("position", $oldPost, $old ?? [])) ?>">
                        <?php if (!empty($errors["position"])): ?>
                            <div class="invalid-feedback"><?= normalizeInput($errors["position"]) ?></div>
                        <?php else: ?>
                            <div id="position-help" class="form-text">Introduce puesto que desempeñabas en la empresa.</div>
                        <?php endif; ?>

                        <div id="cnt-dates-work" class="row">
                            <div class="col-md-6">
                                <label for="start-date-work" class="form-label mt-3">Fecha de inicio <span class="text-danger">*</span></label>
                                <input id="start-date-work" name="work_start" type="date"
                                    class="form-control <?= !empty($errors["work_start"]) ? "is-invalid" : "" ?>"
                                    aria-describedby="start-date-help-work" 
                                    value="<?= normalizeInput(fieldValue("work_start", $oldPost, $old ?? [])) ?>">
                                <?php if (!empty($errors["work_start"])): ?>
                                    <div class="invalid-feedback"><?= normalizeInput($errors["work_start"]) ?></div>
                                <?php else: ?>
                                    <div id="start-date-help-work" class="form-text">Introduce la fecha de inicio.</div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="end-date-work" class="form-label mt-3">Fecha de finalización</label>
                                <input id="end-date-work" name="work_end" type="date"
                                    class="form-control <?= !empty($errors["work_end"]) ? "is-invalid" : "" ?>"
                                    aria-describedby="end-date-help-work"
                                    value="<?= normalizeInput(fieldValue("work_end", $oldPost, $old ?? [])) ?>">
                                <?php if (!empty($errors["work_end"])): ?>
                                    <div class="invalid-feedback"><?= normalizeInput($errors["work_end"]) ?></div>
                                <?php else: ?>
                                    <div id="end-date-help-work" class="form-text"> Si finalizaste este empleo, introduce la fecha.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <label for="description-input-work" class="form-label mt-3">Descripción del puesto</label>
                        <textarea id="description-input-work" name="work_description" class="form-control" rows="5" maxlength="250"
                            aria-describedby="description-help-work" placeholder="Ej. Desarrollo y personalización de sitios web."><?= normalizeInput(fieldValue("work_description", $oldPost, $old ?? [])) ?></textarea>
                        <div id="description-help-work" class="form-text">Describe tu puesto de trabajo en menos de 250 caracteres.</div>
                    </div>

                    <!-- Education -->
                    <div class="cnt-data mb-5">
                        <h2>Formación académica</h2>

                        <label for="school-input" class="form-label mt-3">Nombre del centro de estudios <span class="text-danger">*</span></label>
                        <input id="school-input" name="school" type="text"
                            class="form-control <?= !empty($errors["school"]) ? "is-invalid" : "" ?>"
                            placeholder="Ej. Universidad de Cádiz" aria-describedby="school-help" 
                            value="<?= normalizeInput(fieldValue("school", $oldPost, $old ?? [])) ?>">
                        <?php if (!empty($errors["school"])): ?>
                            <div class="invalid-feedback"><?= normalizeInput($errors["school"]) ?></div>
                        <?php else: ?>
                            <div id="school-help" class="form-text">Introduce el nombre del centro donde estudiaste.</div>
                        <?php endif; ?>

                        <label for="qualification-input" class="form-label mt-3">Título <span class="text-danger">*</span></label>
                        <input id="qualification-input" name="qualification" type="text"
                            class="form-control <?= !empty($errors["qualification"]) ? "is-invalid" : "" ?>"
                            placeholder="Ej. Graduado en CC Ambientales" aria-describedby="qualification-help" 
                            value="<?= normalizeInput(fieldValue("qualification", $oldPost, $old ?? [])) ?>">
                        <?php if (!empty($errors["qualification"])): ?>
                            <div class="invalid-feedback"><?= normalizeInput($errors["qualification"]) ?></div>
                        <?php else: ?>
                            <div id="qualification-help" class="form-text">Introduce el título obtenido en estos estudios.</div>
                        <?php endif; ?>

                        <div id="cnt-dates-edu" class="row">
                            <div class="col-md-6">
                                <label for="start-date-edu" class="form-label mt-3">Fecha de inicio <span class="text-danger">*</span></label>
                                <input id="start-date-edu" name="edu_start" type="date"
                                    class="form-control <?= !empty($errors["edu_start"]) ? "is-invalid" : "" ?>"
                                    aria-describedby="start-date-help-edu" 
                                    value="<?= normalizeInput(fieldValue("edu_start", $oldPost, $old ?? [])) ?>">
                                <?php if (!empty($errors["edu_start"])): ?>
                                    <div class="invalid-feedback"><?= normalizeInput($errors["edu_start"]) ?></div>
                                <?php else: ?>
                                    <div id="start-date-help-edu" class="form-text">Introduce la fecha de inicio.</div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="end-date-edu" class="form-label mt-3">Fecha de finalización</label>
                                <input id="end-date-edu" name="edu_end" type="date"
                                    class="form-control <?= !empty($errors["edu_end"]) ? "is-invalid" : "" ?>"
                                    aria-describedby="end-date-help-edu"
                                    value="<?= normalizeInput(fieldValue("edu_end", $oldPost, $old ?? [])) ?>">
                                <?php if (!empty($errors["edu_end"])): ?>
                                    <div class="invalid-feedback"><?= normalizeInput($errors["edu_end"]) ?></div>
                                <?php else: ?>
                                    <div id="end-date-help-edu" class="form-text">Si finalizaste estos estudios, introduce la fecha.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <label for="description-input-edu" class="form-label mt-3">Aptitudes adquiridas</label>
                        <textarea id="description-input-edu" name="edu_description" class="form-control" rows="5" maxlength="250"
                            aria-describedby="description-help-edu" placeholder="Ej. Gestión y análisis de datos..."><?= normalizeInput(fieldValue("edu_description", $oldPost, $old ?? [])) ?></textarea>
                        <div id="description-help-edu" class="form-text">Describe tu formación en menos de 250 caracteres.</div>
                    </div>

                    <!-- Additional Skills -->
                    <div class="cnt-data mb-5">
                        <h2>Información adicional</h2>

                        <label for="skills-input" class="form-label mt-3">Habilidades <span class="text-danger">*</span></label>
                        <input id="skills-input" name="skills" type="text"
                            class="form-control <?= !empty($errors["skills"]) ? "is-invalid" : "" ?>"
                            placeholder="Ej. Asertiva, facilidad para el trabajo en equipo, emprendedora." aria-describedby="skills-help" 
                            value="<?= normalizeInput(fieldValue("skills", $oldPost, $old ?? [])) ?>">
                        <?php if (!empty($errors["skills"])): ?>
                            <div class="invalid-feedback"><?= normalizeInput($errors["skills"]) ?></div>
                        <?php else: ?>
                            <div id="skills-help" class="form-text">Incluye habilidades e información que pueda ser interesante.</div>
                        <?php endif; ?>

                        <label for="languages-input" class="form-label mt-3">Idiomas</label>
                        <input id="languages-input" name="languages" type="text" class="form-control"
                            placeholder="Ej. Inglés C1" aria-describedby="languages-help"
                            value="<?= normalizeInput(fieldValue("languages", $oldPost, $old ?? [])) ?>">
                        <div id="languages-help" class="form-text">Introduce los idiomas que conoces y su cualificación.</div>
                    </div>

                    <!-- Photo -->
                    <div class="cnt-data mb-3">
                        <h2>Foto</h2>

                        <?php if (!empty($old["photo_path"])): ?>
                            <p class="form-text">Foto actual:</p>
                            <img src="<?= normalizeInput($old["photo_path"]) ?>" style="max-width:120px;border-radius:10px;">
                        <?php endif; ?>

                        <label for="photo-input" class="form-label mt-3">Sube una foto</label>
                        <input id="photo-input" name="photo" type="file"
                            class="form-control <?= !empty($errors["photo"]) ? "is-invalid" : "" ?>"
                            accept="image/*" aria-describedby="photo-help">
                        <?php if (!empty($errors["photo"])): ?>
                            <div class="invalid-feedback"><?= normalizeInput($errors["photo"]) ?></div>
                        <?php else: ?>
                            <div id="photo-help" class="form-text">Opcional.</div>
                        <?php endif; ?>
                    </div>

                    <div class="text-end d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-outline-primary">Limpiar formulario</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>

                </form>
            </main>

            <!-- Rigth, button of actions -->
            <aside class="col-12 col-lg-2">
                <div class="panel">
                    <h3>Acciones</h3>

                    <?php if ($v > 0 && $old): ?>
                        <a class="btn btn-sm btn-outline-primary w-100 mb-2" href="cv.php?v=<?= (int)$v ?>" target="_blank">Abrir CV</a>

                        <a class="btn btn-sm btn-outline-danger w-100" href="delete.php?id=<?= (int)$old["id"] ?>" onclick="return confirm('¿Eliminar la versión <?= (int)$v ?>?')">Eliminar versión</a>
                    <?php else: ?>
                        <p class="text-muted mb-0">Crea o selecciona un CV para activar las acciones.</p>
                    <?php endif; ?>
                </div>
            </aside>

        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light mt-5">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="text-center">
                    <p class="mb-0">© 2026 · Generador de CV · 2ºDAW</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scroll button -->
    <button id="btn-scroll-top" class="btn btn-primary">
        <i class="bi bi-arrow-up"></i>
    </button>

    <script>
        const btnScrollTop = document.getElementById("btn-scroll-top");

        window.addEventListener("scroll", () => {
            if (window.scrollY > 300) {
                btnScrollTop.style.display = "block";
            } else {
                btnScrollTop.style.display = "none";
            }
        });

        btnScrollTop.addEventListener("click", () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    </script>

    <!-- Bootstrap js link -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>

</html>