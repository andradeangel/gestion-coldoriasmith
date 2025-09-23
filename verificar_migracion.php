<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("❌ Error: No se pudo conectar a la base de datos 'col'");
}

echo "🔍 Verificando migración del sistema...\n\n";

$errores = [];
$exitos = [];

// Verificar que las tablas principales existen
$tablas_requeridas = [
    'usuarios', 'estudiantes', 'docentes', 'padres', 
    'cursos', 'materias', 'curso_materia', 'inscripciones',
    'asistencias', 'calificaciones', 'relacion_padre_estudiante'
];

foreach ($tablas_requeridas as $tabla) {
    $query = "SHOW TABLES LIKE '$tabla'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $exitos[] = "✅ Tabla '$tabla' existe";
    } else {
        $errores[] = "❌ Tabla '$tabla' no existe";
    }
}

// Verificar usuarios de prueba
$query = "SELECT COUNT(*) as total FROM usuarios";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch();

if ($result['total'] > 0) {
    $exitos[] = "✅ Se encontraron {$result['total']} usuarios en la base de datos";
} else {
    $errores[] = "❌ No se encontraron usuarios en la base de datos";
}

// Verificar roles
$query = "SELECT rol, COUNT(*) as total FROM usuarios GROUP BY rol";
$stmt = $db->prepare($query);
$stmt->execute();
$roles = $stmt->fetchAll();

$roles_esperados = ['admin', 'docente', 'padre', 'estudiante'];
foreach ($roles_esperados as $rol) {
    $encontrado = false;
    foreach ($roles as $rol_data) {
        if ($rol_data['rol'] === $rol) {
            $exitos[] = "✅ Rol '$rol': {$rol_data['total']} usuarios";
            $encontrado = true;
            break;
        }
    }
    if (!$encontrado) {
        $errores[] = "❌ No se encontraron usuarios con rol '$rol'";
    }
}

// Verificar estudiantes
$query = "SELECT COUNT(*) as total FROM estudiantes";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch();

if ($result['total'] > 0) {
    $exitos[] = "✅ Se encontraron {$result['total']} estudiantes";
} else {
    $errores[] = "❌ No se encontraron estudiantes";
}

// Verificar relaciones padre-estudiante
$query = "SELECT COUNT(*) as total FROM relacion_padre_estudiante";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch();

if ($result['total'] > 0) {
    $exitos[] = "✅ Se encontraron {$result['total']} relaciones padre-estudiante";
} else {
    $errores[] = "❌ No se encontraron relaciones padre-estudiante";
}

// Verificar cursos y materias
$query = "SELECT COUNT(*) as total FROM cursos";
$stmt = $db->prepare($query);
$stmt->execute();
$cursos = $stmt->fetch()['total'];

$query = "SELECT COUNT(*) as total FROM materias";
$stmt = $db->prepare($query);
$stmt->execute();
$materias = $stmt->fetch()['total'];

if ($cursos > 0) {
    $exitos[] = "✅ Se encontraron $cursos cursos";
} else {
    $errores[] = "❌ No se encontraron cursos";
}

if ($materias > 0) {
    $exitos[] = "✅ Se encontraron $materias materias";
} else {
    $errores[] = "❌ No se encontraron materias";
}

// Mostrar resultados
echo "📊 RESULTADOS DE LA VERIFICACIÓN:\n\n";

if (!empty($exitos)) {
    echo "✅ ÉXITOS:\n";
    foreach ($exitos as $exito) {
        echo "   $exito\n";
    }
    echo "\n";
}

if (!empty($errores)) {
    echo "❌ ERRORES:\n";
    foreach ($errores as $error) {
        echo "   $error\n";
    }
    echo "\n";
}

// Resumen final
if (empty($errores)) {
    echo "🎉 ¡MIGRACIÓN EXITOSA!\n";
    echo "El sistema está listo para usar con la nueva base de datos 'col'.\n\n";
    echo "🔑 CREDENCIALES DE ACCESO:\n";
    echo "   Admin: admin@doriasmith.edu / admin123\n";
    echo "   Docente: maria.gonzalez@doriasmith.edu / docente123\n";
    echo "   Padre: carlos.rodriguez@email.com / padre123\n\n";
    echo "🌐 Accede al sistema en: http://localhost/index.php\n";
} else {
    echo "⚠️  MIGRACIÓN INCOMPLETA\n";
    echo "Por favor, revisa los errores y ejecuta el script migrate_data.php\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
?>
