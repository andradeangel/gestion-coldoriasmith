<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Error de conexión a la base de datos");
}

try {
    $db->beginTransaction();
    
    // Insertar usuarios de prueba
    $usuarios = [
        ['admin', 'admin', 'admin@doriasmith.edu', password_hash('admin123', PASSWORD_BCRYPT), 'admin'],
        ['María', 'González', 'maria.gonzalez@doriasmith.edu', password_hash('docente123', PASSWORD_BCRYPT), 'docente'],
        ['Carlos', 'Rodríguez', 'carlos.rodriguez@email.com', password_hash('padre123', PASSWORD_BCRYPT), 'padre']
    ];
    
    $query = "INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    foreach ($usuarios as $usuario) {
        $stmt->execute($usuario);
    }
    
    // Obtener IDs de usuarios creados
    $query = "SELECT id_usuario, rol FROM usuarios WHERE email IN ('admin@doriasmith.edu', 'maria.gonzalez@doriasmith.edu', 'carlos.rodriguez@email.com')";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $usuarios_creados = $stmt->fetchAll();
    
    $admin_id = null;
    $docente_id = null;
    $padre_id = null;
    
    foreach ($usuarios_creados as $usuario) {
        if ($usuario['rol'] === 'admin') $admin_id = $usuario['id_usuario'];
        if ($usuario['rol'] === 'docente') $docente_id = $usuario['id_usuario'];
        if ($usuario['rol'] === 'padre') $padre_id = $usuario['id_usuario'];
    }
    
    // Crear docente
    if ($docente_id) {
        $query = "INSERT INTO docentes (id_usuario, especialidad) VALUES (?, 'Matemáticas')";
        $stmt = $db->prepare($query);
        $stmt->execute([$docente_id]);
        $docente_table_id = $db->lastInsertId();
    }
    
    // Crear padre
    if ($padre_id) {
        $query = "INSERT INTO padres (id_usuario, telefono) VALUES (?, '555-1234')";
        $stmt = $db->prepare($query);
        $stmt->execute([$padre_id]);
        $padre_table_id = $db->lastInsertId();
    }
    
    // Crear estudiantes
    $estudiantes_data = [
        ['Ana', 'Martínez', 'ana.martinez@estudiante.edu', 'EST001', '2005-03-15', 'F'],
        ['Luis', 'Fernández', 'luis.fernandez@estudiante.edu', 'EST002', '2005-07-22', 'M'],
        ['Sofia', 'López', 'sofia.lopez@estudiante.edu', 'EST003', '2005-11-08', 'F'],
        ['Diego', 'García', 'diego.garcia@estudiante.edu', 'EST004', '2005-01-30', 'M'],
        ['Valentina', 'Hernández', 'valentina.hernandez@estudiante.edu', 'EST005', '2005-09-12', 'F']
    ];
    
    $query = "INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES (?, ?, ?, ?, 'estudiante')";
    $stmt = $db->prepare($query);
    
    $estudiante_ids = [];
    foreach ($estudiantes_data as $estudiante) {
        $password_hash = password_hash('estudiante123', PASSWORD_BCRYPT);
        $stmt->execute([$estudiante[0], $estudiante[1], $estudiante[2], $password_hash]);
        $usuario_id = $db->lastInsertId();
        
        // Crear registro en tabla estudiantes
        $query2 = "INSERT INTO estudiantes (id_usuario, codigo, fecha_nacimiento, genero) VALUES (?, ?, ?, ?)";
        $stmt2 = $db->prepare($query2);
        $stmt2->execute([$usuario_id, $estudiante[3], $estudiante[4], $estudiante[5]]);
        $estudiante_ids[] = $db->lastInsertId();
    }
    
    // Crear curso
    $query = "INSERT INTO cursos (nombre, nivel, gestion) VALUES ('Sexto de Secundaria', 'Secundaria', 2024)";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $curso_id = $db->lastInsertId();
    
    // Crear materias
    $materias = [
        ['Matemáticas', 'Álgebra, geometría y cálculo'],
        ['Lenguaje', 'Gramática, literatura y redacción'],
        ['Ciencias Naturales', 'Biología, química y física'],
        ['Historia', 'Historia universal y nacional']
    ];
    
    $query = "INSERT INTO materias (nombre, descripcion) VALUES (?, ?)";
    $stmt = $db->prepare($query);
    
    $materia_ids = [];
    foreach ($materias as $materia) {
        $stmt->execute($materia);
        $materia_ids[] = $db->lastInsertId();
    }
    
    // Crear curso_materia
    $query = "INSERT INTO curso_materia (id_curso, id_materia, id_docente) VALUES (?, ?, ?)";
    $stmt = $db->prepare($query);
    
    foreach ($materia_ids as $materia_id) {
        $stmt->execute([$curso_id, $materia_id, $docente_table_id]);
    }
    
    // Crear inscripciones
    $query = "INSERT INTO inscripciones (id_estudiante, id_curso, fecha_inscripcion) VALUES (?, ?, CURDATE())";
    $stmt = $db->prepare($query);
    
    foreach ($estudiante_ids as $estudiante_id) {
        $stmt->execute([$estudiante_id, $curso_id]);
    }
    
    // Asociar padre con estudiantes
    if ($padre_table_id && count($estudiante_ids) >= 2) {
        $query = "INSERT INTO relacion_padre_estudiante (id_padre, id_estudiante) VALUES (?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$padre_table_id, $estudiante_ids[0]]); // Ana
        $stmt->execute([$padre_table_id, $estudiante_ids[1]]); // Luis
    }
    
    $db->commit();
    echo "Datos de prueba insertados correctamente.\n";
    echo "Usuarios creados:\n";
    echo "- Admin: admin@doriasmith.edu / admin123\n";
    echo "- Docente: maria.gonzalez@doriasmith.edu / docente123\n";
    echo "- Padre: carlos.rodriguez@email.com / padre123\n";
    echo "- Estudiantes: [email]@estudiante.edu / estudiante123\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
