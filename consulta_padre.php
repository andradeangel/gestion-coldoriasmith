<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Verificar que el usuario esté logueado y sea padre
requireLogin();
if (!isPadre()) {
    header('Location: dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener los estudiantes asociados al padre
$query = "SELECT e.*, u.nombre, u.apellido, rpe.creado_en as fecha_relacion
          FROM estudiantes e 
          JOIN usuarios u ON e.id_usuario = u.id_usuario
          JOIN relacion_padre_estudiante rpe ON e.id_estudiante = rpe.id_estudiante 
          WHERE rpe.id_padre = ? 
          ORDER BY u.apellido, u.nombre";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$estudiantes = $stmt->fetchAll();

$estudiante_seleccionado = null;
$notas_estudiante = [];
$asistencias_estudiante = [];
$estadisticas_estudiante = [];

if ($_GET['estudiante_id']) {
    $estudiante_id = intval($_GET['estudiante_id']);
    
    // Verificar que el estudiante pertenece al padre
    $query = "SELECT e.*, u.nombre, u.apellido FROM estudiantes e 
              JOIN usuarios u ON e.id_usuario = u.id_usuario
              JOIN relacion_padre_estudiante rpe ON e.id_estudiante = rpe.id_estudiante 
              WHERE e.id_estudiante = ? AND rpe.id_padre = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$estudiante_id, $_SESSION['user_id']]);
    $estudiante_seleccionado = $stmt->fetch();
    
    if ($estudiante_seleccionado) {
        // Obtener notas del estudiante
        $query = "SELECT AVG(c.nota) as promedio_nota, MAX(c.fecha_registro) as ultima_nota
                  FROM calificaciones c
                  JOIN inscripciones i ON c.id_inscripcion = i.id_inscripcion
                  WHERE i.id_estudiante = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$estudiante_id]);
        $notas_estudiante = $stmt->fetch();
        
        // Obtener asistencias del estudiante (últimos 30 días)
        $query = "SELECT a.*, u.nombre as docente_nombre, u.apellido as docente_apellido
                  FROM asistencias a 
                  JOIN inscripciones i ON a.id_inscripcion = i.id_inscripcion
                  JOIN curso_materia cm ON a.id_curso_materia = cm.id_curso_materia
                  JOIN docentes d ON cm.id_docente = d.id_docente
                  JOIN usuarios u ON d.id_usuario = u.id_usuario
                  WHERE i.id_estudiante = ? 
                  AND a.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                  ORDER BY a.fecha DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$estudiante_id]);
        $asistencias_estudiante = $stmt->fetchAll();
        
        // Obtener estadísticas de asistencia
        $query = "SELECT 
            COUNT(*) as total_dias,
            SUM(CASE WHEN a.estado = 'Presente' THEN 1 ELSE 0 END) as presentes,
            SUM(CASE WHEN a.estado = 'Retraso' THEN 1 ELSE 0 END) as tardanzas,
            SUM(CASE WHEN a.estado = 'Ausente' THEN 1 ELSE 0 END) as ausentes
            FROM asistencias a
            JOIN inscripciones i ON a.id_inscripcion = i.id_inscripcion
            WHERE i.id_estudiante = ? AND a.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        $stmt = $db->prepare($query);
        $stmt->execute([$estudiante_id]);
        $estadisticas_estudiante = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Académica - Colegio Dora Schmidt - A</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .stats-card {
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-3px);
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .badge-presente { background-color: #28a745; }
        .badge-tardanza { background-color: #ffc107; color: #000; }
        .badge-ausente { background-color: #dc3545; }
        .badge-justificado { background-color: #17a2b8; }
        .student-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .student-card.selected {
            border: 3px solid #007bff;
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>
                Colegio Dora Schmidt - A
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="consulta_padre.php">
                            <i class="fas fa-search me-1"></i>Consulta Académica
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars(getNombreCompleto()); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><span class="dropdown-item-text">
                                <i class="fas fa-user-tag me-2"></i>Padre de Familia
                            </span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container mt-4">
        <!-- Selección de estudiante -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>Seleccione un Estudiante
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($estudiantes)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <p class="text-muted">No tiene estudiantes asociados a su cuenta.</p>
                            <p class="text-muted">Contacte al administrador para asociar estudiantes.</p>
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <?php foreach ($estudiantes as $estudiante): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card student-card <?php echo ($estudiante_seleccionado && $estudiante_seleccionado['id_estudiante'] == $estudiante['id_estudiante']) ? 'selected' : ''; ?>" 
                                     onclick="seleccionarEstudiante(<?php echo $estudiante['id_estudiante']; ?>)">
                                    <div class="card-body text-center">
                                        <i class="fas fa-user-graduate fa-3x text-primary mb-3"></i>
                                        <h6 class="card-title"><?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></h6>
                                        <p class="card-text text-muted">
                                            <small>Asociado desde: <?php echo date('d/m/Y', strtotime($estudiante['fecha_relacion'])); ?></small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($estudiante_seleccionado): ?>
        <!-- Información del estudiante seleccionado -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <i class="fas fa-user-graduate fa-4x"></i>
                            </div>
                            <div class="col-md-10">
                                <h3 class="mb-1"><?php echo htmlspecialchars($estudiante_seleccionado['nombre'] . ' ' . $estudiante_seleccionado['apellido']); ?></h3>
                                <p class="mb-0">Información académica y de asistencia</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas del estudiante -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                        <h4><?php echo $notas_estudiante['promedio_nota'] ? number_format($notas_estudiante['promedio_nota'], 1) : 'N/A'; ?></h4>
                        <p class="mb-0">Nota Actual</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-check fa-2x mb-2"></i>
                        <h4><?php echo $estadisticas_estudiante['presentes']; ?></h4>
                        <p class="mb-0">Días Presentes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <h4><?php echo $estadisticas_estudiante['tardanzas']; ?></h4>
                        <p class="mb-0">Tardanzas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-danger text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-times fa-2x mb-2"></i>
                        <h4><?php echo $estadisticas_estudiante['ausentes']; ?></h4>
                        <p class="mb-0">Ausencias</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de notas -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>Información de Notas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <p><strong>Nota Actual:</strong></p>
                                <h3 class="text-<?php echo $notas_estudiante['promedio_nota'] >= 80 ? 'success' : ($notas_estudiante['promedio_nota'] >= 60 ? 'warning' : 'danger'); ?>">
                                    <?php echo $notas_estudiante['promedio_nota'] ? number_format($notas_estudiante['promedio_nota'], 1) : 'N/A'; ?>
                                </h3>
                            </div>
                            <div class="col-6">
                                <p><strong>Estado:</strong></p>
                                <span class="badge bg-<?php echo $notas_estudiante['promedio_nota'] >= 80 ? 'success' : ($notas_estudiante['promedio_nota'] >= 60 ? 'warning' : 'danger'); ?> fs-6">
                                    <?php echo $notas_estudiante['promedio_nota'] >= 80 ? 'Excelente' : ($notas_estudiante['promedio_nota'] >= 60 ? 'Aprobado' : 'Reprobado'); ?>
                                </span>
                            </div>
                        </div>
                        <hr>
                        <p><strong>Última actualización:</strong> <?php echo $notas_estudiante['ultima_nota'] ? date('d/m/Y H:i', strtotime($notas_estudiante['ultima_nota'])) : 'N/A'; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Resumen de asistencia -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Resumen de Asistencia (30 días)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-3">
                                <h5 class="text-success"><?php echo $estadisticas_estudiante['presentes']; ?></h5>
                                <small>Presentes</small>
                            </div>
                            <div class="col-3">
                                <h5 class="text-warning"><?php echo $estadisticas_estudiante['tardanzas']; ?></h5>
                                <small>Tardanzas</small>
                            </div>
                            <div class="col-3">
                                <h5 class="text-danger"><?php echo $estadisticas_estudiante['ausentes']; ?></h5>
                                <small>Ausentes</small>
                            </div>
                            <div class="col-3">
                                <h5 class="text-info"><?php echo $estadisticas_estudiante['justificados']; ?></h5>
                                <small>Justificados</small>
                            </div>
                        </div>
                        <hr>
                        <p class="mb-0"><strong>Total de días registrados:</strong> <?php echo $estadisticas_estudiante['total_dias']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de asistencias -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Historial de Asistencias (Últimos 30 días)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Hora Llegada</th>
                                        <th>Hora Salida</th>
                                        <th>Estado</th>
                                        <th>Observaciones</th>
                                        <th>Docente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($asistencias_estudiante)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No hay registros de asistencia en los últimos 30 días</p>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($asistencias_estudiante as $asistencia): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($asistencia['fecha'])); ?></td>
                                        <td><?php echo $asistencia['hora_llegada'] ? date('H:i', strtotime($asistencia['hora_llegada'])) : '-'; ?></td>
                                        <td><?php echo $asistencia['hora_salida'] ? date('H:i', strtotime($asistencia['hora_salida'])) : '-'; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $asistencia['estado']; ?>">
                                                <?php echo ucfirst($asistencia['estado']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($asistencia['observaciones']); ?></td>
                                        <td><?php echo htmlspecialchars($asistencia['docente_nombre'] . ' ' . $asistencia['docente_apellido']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        function seleccionarEstudiante(estudianteId) {
            window.location.href = 'consulta_padre.php?estudiante_id=' + estudianteId;
        }
        
        // Prevenir navegación hacia atrás después del logout
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function () {
            window.history.pushState(null, null, window.location.href);
        };
        
        // Limpiar caché del navegador al cargar la página
        if (window.history && window.history.pushState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
