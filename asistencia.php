<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireDocente();

$database = new Database();
$db = $database->getConnection();

$mensaje = '';
$tipoMensaje = '';

if ($_POST) {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'registrar_asistencia':
            $estudiante_id = intval($_POST['estudiante_id'] ?? 0);
            $fecha = $_POST['fecha'] ?? '';
            $hora_llegada = $_POST['hora_llegada'] ?? '';
            $hora_salida = $_POST['hora_salida'] ?? '';
            $estado = $_POST['estado'] ?? 'presente';
            $observaciones = trim($_POST['observaciones'] ?? '');
            
            if ($estudiante_id > 0 && !empty($fecha)) {
                // Verificar si ya existe una asistencia para este estudiante en esta fecha
                $query = "SELECT a.id_asistencia FROM asistencias a 
                         JOIN inscripciones i ON a.id_inscripcion = i.id_inscripcion
                         WHERE i.id_estudiante = ? AND a.fecha = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$estudiante_id, $fecha]);
                
                if ($stmt->rowCount() > 0) {
                    // Actualizar asistencia existente
                    $asistencia_existente = $stmt->fetch();
                    $query = "UPDATE asistencias SET 
                             estado = ?, modificado_en = CURRENT_TIMESTAMP
                             WHERE id_asistencia = ?";
                    $stmt = $db->prepare($query);
                    if ($stmt->execute([$estado, $asistencia_existente['id_asistencia']])) {
                        $mensaje = 'Asistencia actualizada exitosamente';
                        $tipoMensaje = 'success';
                    } else {
                        $mensaje = 'Error al actualizar la asistencia';
                        $tipoMensaje = 'danger';
                    }
                } else {
                    // Obtener inscripción del estudiante
                    $query = "SELECT id_inscripcion FROM inscripciones WHERE id_estudiante = ? LIMIT 1";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$estudiante_id]);
                    $inscripcion = $stmt->fetch();
                    
                    if ($inscripcion) {
                        // Crear nueva asistencia
                        $query = "INSERT INTO asistencias (id_inscripcion, id_curso_materia, fecha, estado, creado_por) 
                                 VALUES (?, 1, ?, ?, ?)";
                        $stmt = $db->prepare($query);
                        if ($stmt->execute([$inscripcion['id_inscripcion'], $fecha, $estado, $_SESSION['user_id']])) {
                            $mensaje = 'Asistencia registrada exitosamente';
                            $tipoMensaje = 'success';
                        } else {
                            $mensaje = 'Error al registrar la asistencia';
                            $tipoMensaje = 'danger';
                        }
                    } else {
                        $mensaje = 'No se encontró inscripción para el estudiante';
                        $tipoMensaje = 'warning';
                    }
                }
            } else {
                $mensaje = 'Por favor, complete todos los campos requeridos';
                $tipoMensaje = 'warning';
            }
            break;
            
        case 'eliminar_asistencia':
            $id = intval($_POST['id'] ?? 0);
            
            if ($id > 0) {
                $query = "DELETE FROM asistencias WHERE id_asistencia = ?";
                $stmt = $db->prepare($query);
                if ($stmt->execute([$id])) {
                    $mensaje = 'Asistencia eliminada exitosamente';
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = 'Error al eliminar la asistencia';
                    $tipoMensaje = 'danger';
                }
            } else {
                $mensaje = 'ID de asistencia inválido';
                $tipoMensaje = 'warning';
            }
            break;
    }
}

// Obtener lista de estudiantes
$query = "SELECT e.*, u.nombre, u.apellido 
          FROM estudiantes e 
          JOIN usuarios u ON e.id_usuario = u.id_usuario 
          ORDER BY u.apellido, u.nombre";
$stmt = $db->prepare($query);
$stmt->execute();
$estudiantes = $stmt->fetchAll();

// Obtener asistencias del día actual por defecto
$fecha_consulta = $_GET['fecha'] ?? date('Y-m-d');
$query = "SELECT a.*, u.nombre, u.apellido 
          FROM asistencias a 
          JOIN inscripciones i ON a.id_inscripcion = i.id_inscripcion
          JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
          JOIN usuarios u ON e.id_usuario = u.id_usuario
          WHERE a.fecha = ? 
          ORDER BY u.apellido, u.nombre";
$stmt = $db->prepare($query);
$stmt->execute([$fecha_consulta]);
$asistencias = $stmt->fetchAll();

// Obtener estadísticas del día
$query = "SELECT 
    COUNT(*) as total_registros,
    SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) as presentes,
    SUM(CASE WHEN estado = 'tardanza' THEN 1 ELSE 0 END) as tardanzas,
    SUM(CASE WHEN estado = 'ausente' THEN 1 ELSE 0 END) as ausentes,
    SUM(CASE WHEN estado = 'justificado' THEN 1 ELSE 0 END) as justificados
    FROM asistencias WHERE fecha = ?";
$stmt = $db->prepare($query);
$stmt->execute([$fecha_consulta]);
$estadisticas = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Asistencia - Colegio Dora Schmidt - A</title>
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
        .btn-action {
            border-radius: 20px;
            padding: 5px 15px;
        }
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus {
            border-color: #551818ff;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .badge-presente { background-color: #28a745; }
        .badge-tardanza { background-color: #ffc107; color: #000; }
        .badge-ausente { background-color: #dc3545; }
        .badge-justificado { background-color: #17a2b8; }
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
                        <a class="nav-link" href="notas.php">
                            <i class="fas fa-clipboard-list me-1"></i>Notas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="asistencia.php">
                            <i class="fas fa-clock me-1"></i>Asistencia
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
                                <i class="fas fa-user-tag me-2"></i>Docente
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
        <!-- Mensajes -->
        <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $tipoMensaje === 'success' ? 'check-circle' : ($tipoMensaje === 'danger' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
            <?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Filtro de fecha -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-calendar me-2"></i>Consultar Asistencia por Fecha
                        </h5>
                        <form method="GET" class="d-flex">
                            <input type="date" class="form-control me-2" name="fecha" value="<?php echo htmlspecialchars($fecha_consulta); ?>" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Consultar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-plus me-2"></i>Registrar Asistencia
                        </h5>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAsistencia">
                            <i class="fas fa-plus me-2"></i>Nueva Asistencia
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas del día -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h4><?php echo $estadisticas['total_registros']; ?></h4>
                        <p class="mb-0">Total Registros</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-check fa-2x mb-2"></i>
                        <h4><?php echo $estadisticas['presentes']; ?></h4>
                        <p class="mb-0">Presentes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <h4><?php echo $estadisticas['tardanzas']; ?></h4>
                        <p class="mb-0">Tardanzas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-danger text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-times fa-2x mb-2"></i>
                        <h4><?php echo $estadisticas['ausentes']; ?></h4>
                        <p class="mb-0">Ausentes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de asistencias -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Asistencias del <?php echo date('d/m/Y', strtotime($fecha_consulta)); ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th>Estudiante</th>
                                <th>Hora Llegada</th>
                                <th>Hora Salida</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($asistencias)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No hay asistencias registradas para esta fecha</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($asistencias as $asistencia): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($asistencia['nombre'] . ' ' . $asistencia['apellido']); ?></td>
                                <td>-</td>
                                <td>-</td>
                                <td>
                                    <span class="badge badge-<?php echo $asistencia['estado']; ?>">
                                        <?php echo ucfirst($asistencia['estado']); ?>
                                    </span>
                                </td>
                                <td>-</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary btn-action me-1" 
                                            onclick="editarAsistencia(<?php echo htmlspecialchars(json_encode($asistencia)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-action" 
                                            onclick="eliminarAsistencia(<?php echo $asistencia['id_asistencia']; ?>, '<?php echo htmlspecialchars($asistencia['nombre'] . ' ' . $asistencia['apellido']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar/editar asistencia -->
    <div class="modal fade" id="modalAsistencia" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Registrar Asistencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formAsistencia">
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="registrar_asistencia">
                        <input type="hidden" name="id" id="asistenciaId">
                        
                        <div class="mb-3">
                            <label for="estudiante_id" class="form-label">Estudiante</label>
                            <select class="form-control" id="estudiante_id" name="estudiante_id" required>
                                <option value="">Seleccione un estudiante</option>
                                <?php foreach ($estudiantes as $estudiante): ?>
                                <option value="<?php echo $estudiante['id_estudiante']; ?>">
                                    <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha_consulta; ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hora_llegada" class="form-label">Hora de Llegada</label>
                                    <input type="time" class="form-control" id="hora_llegada" name="hora_llegada">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hora_salida" class="form-label">Hora de Salida</label>
                                    <input type="time" class="form-control" id="hora_salida" name="hora_salida">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-control" id="estado" name="estado" required>
                                <option value="presente">Presente</option>
                                <option value="tardanza">Tardanza</option>
                                <option value="ausente">Ausente</option>
                                <option value="justificado">Justificado</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar la asistencia de <strong id="nombreEliminar"></strong>?</p>
                    <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="accion" value="eliminar_asistencia">
                        <input type="hidden" name="id" id="idEliminar">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        function editarAsistencia(asistencia) {
            document.getElementById('modalTitulo').textContent = 'Editar Asistencia';
            document.getElementById('estudiante_id').value = asistencia.estudiante_id;
            document.getElementById('fecha').value = asistencia.fecha;
            document.getElementById('hora_llegada').value = asistencia.hora_llegada || '';
            document.getElementById('hora_salida').value = asistencia.hora_salida || '';
            document.getElementById('estado').value = asistencia.estado;
            document.getElementById('observaciones').value = asistencia.observaciones || '';
            document.getElementById('btnGuardar').textContent = 'Actualizar';
            
            new bootstrap.Modal(document.getElementById('modalAsistencia')).show();
        }
        
        function eliminarAsistencia(id, nombre) {
            document.getElementById('idEliminar').value = id;
            document.getElementById('nombreEliminar').textContent = nombre;
            
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }
        
        // Limpiar formulario cuando se cierre el modal
        document.getElementById('modalAsistencia').addEventListener('hidden.bs.modal', function () {
            document.getElementById('formAsistencia').reset();
            document.getElementById('modalTitulo').textContent = 'Registrar Asistencia';
            document.getElementById('btnGuardar').textContent = 'Guardar';
            document.getElementById('fecha').value = '<?php echo $fecha_consulta; ?>';
        });
        
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
