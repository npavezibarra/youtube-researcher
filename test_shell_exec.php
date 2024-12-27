<?php
echo "<h2>shell_exec Function Status</h2>";

if (function_exists('shell_exec')) {
    echo "<p><strong>shell_exec está habilitado.</strong></p>";
    
    // Execute the command to get Python version
    $python_version = shell_exec("python3 --version 2>&1"); // Redirect stderr to stdout
    
    if ($python_version) {
        echo "<p>Python Version:</p>";
        echo "<pre>" . htmlspecialchars($python_version) . "</pre>";
    } else {
        echo "<p>No se pudo obtener la versión de Python. Asegúrate de que Python 3 esté instalado y accesible.</p>";
    }
} else {
    echo "<p><strong>shell_exec está deshabilitado.</strong></p>";
    echo "<p>Para que el plugin funcione correctamente, habilita la función <code>shell_exec</code> en tu configuración de PHP.</p>";
}
?>
