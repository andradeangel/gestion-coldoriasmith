<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Error de conexión a la base de datos");
}

try {
    $db->beginTransaction();
    
    echo "🔧 Agregando datos de prueba para calificaciones y asistencias...\n\n";
    
    // Obtener IDs necesarios
    $query = "SELECT id_inscripcion, id_estudiante FROM inscripciones LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $inscripciones = $stmt->fetchAll();
    
    $query = "SELECT id_curso_materia FROM curso_materia LIMIT 4";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $curso_materias = $stmt->fetchAll();
    
    if (empty($inscripciones) || empty($curso_materias)) {
        echo "❌ No se encontraron inscripciones o curso_materias. Ejecute primero migrate_data.php\n";
        exit;
    }
    
    // Agregar calificaciones de prueba
    echo "📊 Agregando calificaciones...\n";
    $calificaciones = [];
    $periodos = ['Q1', 'Q2', 'Q3', 'Q4'];
    
    foreach ($inscripciones as $inscripcion) {
        foreach ($curso_materias as $curso_materia) {
            foreach ($periodos as $periodo) {
                $nota = rand(60, 100); // Notas entre 60 y 100
                $fecha = date('Y-m-d H:i:s', strtotime('-' . rand(1, 90) . ' days'));
                
                $query = "INSERT INTO calificaciones (id_inscripcion, id_curso_materia, periodo, nota, fecha_registro) 
                         VALUES (?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$inscripcion['id_inscripcion'], $curso_materia['id_curso_materia'], $periodo, $nota, $fecha]);
            }
        }
    }
    
    echo "✅ Calificaciones agregadas: " . (count($inscripciones) * count($curso_materias) * count($periodos)) . " registros\n";
    
    // Agregar asistencias de prueba (últimos 30 días)
    echo "📅 Agregando asistencias...\n";
    $estados = ['presente', 'presente', 'presente', 'presente', 'tardanza', 'ausente']; // Más presentes
    $total_asistencias = 0;
    
    for ($i = 0; $i < 30; $i++) {
        $fecha = date('Y-m-d', strtotime("-$i days"));
        
        // Solo agregar asistencias para días de semana (lunes a viernes)
        if (date('N', strtotime($fecha)) <= 5) {
            foreach ($inscripciones as $inscripcion) {
                $estado = $estados[array_rand($estados)];
                $curso_materia = $curso_materias[array_rand($curso_materias)];
                
                $query = "INSERT INTO asistencias (id_inscripcion, id_curso_materia, fecha, estado) 
                         VALUES (?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$inscripcion['id_inscripcion'], $curso_materia['id_curso_materia'], $fecha, $estado]);
                $total_asistencias++;
            }
        }
    }
    
    echo "✅ Asistencias agregadas: $total_asistencias registros\n";
    
    $db->commit();
    echo "\n🎉 ¡Datos de prueba agregados exitosamente!\n";
    echo "Ahora las pestañas de notas y asistencia deberían mostrar información.\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
