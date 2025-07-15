<?php
// index.php

session_start();

// Las variables de entorno para MySQL de Railway serán así:
$dbHost = getenv('MYSQLHOST');
$dbPort = getenv('MYSQLPORT');
$dbName = getenv('MYSQLDATABASE');
$dbUser = getenv('MYSQLUSER');
$dbPass = getenv('MYSQLPASSWORD');

$pdo = null;
$db_status_message = "";
$notes = [];
$error_message = "";
$success_message = "";

try {
    // Solo intentamos conectar si todas las variables necesarias están presentes
    if ($dbHost && $dbPort && $dbName && $dbUser && $dbPass) {
        // *** CAMBIO CLAVE AQUÍ: Usar 'mysql' en la cadena DSN y el puerto adecuado ***
        $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db_status_message = "Conexión a la base de datos MySQL exitosa.";

        // --- Manejo de Añadir Nota (simplificado) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['note_title']) && isset($_POST['note_content'])) {
            $title = trim($_POST['note_title']);
            $content = trim($_POST['note_content']);

            if (!empty($title) && !empty($content)) {
                $stmt = $pdo->prepare("INSERT INTO notes (title, content) VALUES (:title, :content)");
                if ($stmt->execute([':title' => $title, ':content' => $content])) {
                    $success_message = "Nota añadida correctamente.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $error_message = "Error al añadir la nota.";
                }
            } else {
                $error_message = "El título y el contenido de la nota no pueden estar vacíos.";
            }
        }

        // --- Obtener Notas Existentes ---
        // La sintaxis de SELECT y ORDER BY es compatible entre PostgreSQL y MySQL para esto.
        $stmt = $pdo->query("SELECT id, title, content, created_at FROM notes ORDER BY created_at DESC");
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        $db_status_message = "Faltan variables de entorno para la base de datos MySQL.";
        $error_message = "No se pudo conectar a la base de datos. (¿Estás en Railway o configuraste las variables localmente?)";
    }
} catch (PDOException $e) {
    $db_status_message = "Error de conexión a la base de datos MySQL.";
    $error_message = "Error en la base de datos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema sencillo de Notas con Embeds</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Mi Sistema de Notas Simple</h1>
        <p>Gestiona tus recordatorios y explora recursos.</p>
    </header>

    <main>
        <section class="status-section">
            <h2>Estado del Sistema:</h2>
            <p><strong>Conexión DB:</strong> <?php echo htmlspecialchars($db_status_message); ?></p>
            <?php if ($error_message): ?>
                <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
        </section>

        <hr>

        <section class="add-note-section">
            <h2>Añadir Nueva Nota:</h2>
            <form action="" method="POST" class="note-form">
                <div class="form-group">
                    <label for="note_title">Título:</label>
                    <input type="text" id="note_title" name="note_title" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="note_content">Contenido:</label>
                    <textarea id="note_content" name="note_content" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn primary-btn">Guardar Nota</button>
            </form>
        </section>

        <hr>

        <section class="notes-list-section">
            <h2>Mis Notas:</h2>
            <?php if (!empty($notes)): ?>
                <div class="notes-grid">
                    <?php foreach ($notes as $note): ?>
                        <div class="note-card">
                            <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                            <small>Creado: <?php echo date('d/M/Y H:i', strtotime($note['created_at'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Aún no tienes notas. ¡Añade una usando el formulario de arriba!</p>
            <?php endif; ?>
        </section>

        <hr>

        <section class="resource-embeds-section">
            <h2>Recursos Relacionados (Embeds):</h2>
            <p>Aquí puedes integrar videos o mapas relevantes para tus notas o para tu proyecto.</p>
            <div class="embed-grid">
                <div class="embed-item">
                    <h3>Tutorial de PHP Básico</h3>
                    <div class="responsive-embed">
                        <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen></iframe>
                    </div>
                    <p>Un video introductorio a la programación con PHP. (Este es un ejemplo de Rick Astley para probar el embed).</p>
                </div>

                <div class="embed-item">
                    <h3>Mapa de Guadalajara</h3>
                    <div class="responsive-embed">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d119643.08051871469!2d-103.49842442491104!3d20.659698717865038!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8428b185e7d5cf59%3A0x6e9f13d8e5793e2!2sGuadalajara%2C%20Jal.!5e0!3m2!1ses!2smx!4v1700000000000!5m2!1ses!2smx" 
                                width="700" 
                                height="550" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <p>Ubicación de la hermosa ciudad de Guadalajara, Jalisco.</p>
                </div>
            </div>
        </section>

        <hr>

        <section class="external-services-section">
            <h2>Plataformas de Servicios en la Nube:</h2>
            <div class="links-grid">
                <a href="https://railway.app/" target="_blank" class="cloud-link">
                    <img src="https://railway.app/brand/logo-dark.svg" alt="Railway Logo">
                    <span>Railway</span>
                </a>
                <a href="https://github.com/" target="_blank" class="cloud-link">
                    <img src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png" alt="GitHub Logo">
                    <span>GitHub</span>
                </a>
                <a href="https://www.postgresql.org/" target="_blank" class="cloud-link">
                    <img src="https://www.postgresql.org/media/img/about/press/elephant.png" alt="PostgreSQL Logo">
                    <span>PostgreSQL</span>
                </a>
            </div>
        </section>
    </main>

    <footer>
        <p>© <?php echo date("Y"); ?> Sistema de Notas Simple. Creado para Railway.</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
