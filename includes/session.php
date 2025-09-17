<?php
// Manejo de sesiones para el sistema del Colegio "Dora Schmidt - A"

session_start();

// Headers para prevenir caché del navegador
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['tipo_usuario']);
}

// Función para verificar si el usuario es docente
function isDocente() {
    return isLoggedIn() && $_SESSION['tipo_usuario'] === 'docente';
}

// Función para verificar si el usuario es administrador
function isAdministrador() {
    return isLoggedIn() && $_SESSION['tipo_usuario'] === 'administrador';
}

// Función para verificar si el usuario es padre
function isPadre() {
    return isLoggedIn() && $_SESSION['tipo_usuario'] === 'padre';
}

// Función para redirigir si no está logueado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

// Función para redirigir si no es docente (para la sección de notas)
function requireDocente() {
    requireLogin();
    if (!isDocente()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Función para redirigir si no es administrador
function requireAdministrador() {
    requireLogin();
    if (!isAdministrador()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Función para redirigir si no es padre (para la sección de consulta)
function requirePadre() {
    requireLogin();
    if (!isPadre()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Función para obtener el nombre completo del usuario
function getNombreCompleto() {
    if (isLoggedIn()) {
        return $_SESSION['nombres'] . ' ' . $_SESSION['apellidos'];
    }
    return '';
}

// Función para cerrar sesión
function logout() {
    // Limpiar todas las variables de sesión
    $_SESSION = array();
    
    // Destruir la cookie de sesión si existe
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir la sesión
    session_destroy();
    
    // Redirigir con headers para prevenir caché
    header('Location: index.php');
    exit();
}
?>
