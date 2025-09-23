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
        case 'crear':
            $nombres = trim($_POST['nombres'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $codigo = trim($_POST['codigo'] ?? '');
            $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
            $genero = $_POST['genero'] ?? '';
            
            if (!empty($nombres) && !empty($apellidos) && !empty($email) && !empty($codigo)) {
                try {
                    $db->beginTransaction();
                    
                    // Crear usuario
                    $query = "INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES (?, ?, ?, ?, 'estudiante')";
                    $stmt = $db->prepare($query);
                    $password_hash = password_hash('password123', PASSWORD_BCRYPT);
                    $stmt->execute([$nombres, $apellidos, $email, $password_hash]);
                    $usuario_id = $db->lastInsertId();
                    
                    // Crear estudiante
                    $query = "INSERT INTO estudiantes (id_usuario, codigo, fecha_nacimiento, genero) VALUES (?, ?, ?, ?)";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$usuario_id, $codigo, $fecha_nacimiento, $genero]);
                    
                    $db->commit();
                    $mensaje = 'Estudiante agregado exitosamente';
                    $tipoMensaje = 'success';
                } catch (Exception $e) {
                    $db->rollBack();
                    $mensaje = 'Error al agregar el estudiante';
                    $tipoMensaje = 'danger';
                }
            } else {
                $mensaje = 'Por favor, complete todos los campos obligatorios';
                $tipoMensaje = 'warning';
            }
            break;
            
        case 'editar':
            $id = intval($_POST['id'] ?? 0);
            $nombres = trim($_POST['nombres'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $codigo = trim($_POST['codigo'] ?? '');
            
            if ($id > 0 && !empty($nombres) && !empty($apellidos) && !empty($email)) {
                try {
                    $db->beginTransaction();
                    
                    // Actualizar usuario
                    $query = "UPDATE usuarios u JOIN estudiantes e ON u.id_usuario = e.id_usuario 
                             SET u.nombre = ?, u.apellido = ?, u.email = ? 
                             WHERE e.id_estudiante = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$nombres, $apellidos, $email, $id]);
                    
                    // Actualizar estudiante
                    $query = "UPDATE estudiantes SET codigo = ? WHERE id_estudiante = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$codigo, $id]);
                    
                    $db->commit();
                    $mensaje = 'Estudiante actualizado exitosamente';
                    $tipoMensaje = 'success';
                } catch (Exception $e) {
                    $db->rollBack();
                    $mensaje = 'Error al actualizar el estudiante';
                    $tipoMensaje = 'danger';
                }
            } else {
                $mensaje = 'Datos inválidos para la actualización';
                $tipoMensaje = 'warning';
            }
            break;
            
        case 'eliminar':
            $id = intval($_POST['id'] ?? 0);
            
            if ($id > 0) {
                try {
                    $db->beginTransaction();
                    
                    // Obtener id_usuario del estudiante
                    $query = "SELECT id_usuario FROM estudiantes WHERE id_estudiante = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$id]);
                    $estudiante = $stmt->fetch();
                    
                    if ($estudiante) {
                        // Eliminar estudiante (esto eliminará el usuario por CASCADE)
                        $query = "DELETE FROM estudiantes WHERE id_estudiante = ?";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$id]);
                    }
                    
                    $db->commit();
                    $mensaje = 'Estudiante eliminado exitosamente';
                    $tipoMensaje = 'success';
                } catch (Exception $e) {
                    $db->rollBack();
                    $mensaje = 'Error al eliminar el estudiante';
                    $tipoMensaje = 'danger';
                }
            } else {
                $mensaje = 'ID de estudiante inválido';
                $tipoMensaje = 'warning';
            }
            break;
    }
}

// Obtener lista de estudiantes
$query = "SELECT e.*, u.nombre, u.apellido, u.email 
          FROM estudiantes e 
          JOIN usuarios u ON e.id_usuario = u.id_usuario 
          ORDER BY u.apellido, u.nombre";
$stmt = $db->prepare($query);
$stmt->execute();
$estudiantes = $stmt->fetchAll();

// Obtener estadísticas
$query = "SELECT 
    COUNT(*) as total_estudiantes,
    AVG(c.nota) as promedio_notas,
    MAX(c.nota) as nota_maxima,
    MIN(c.nota) as nota_minima
    FROM estudiantes e 
    JOIN usuarios u ON e.id_usuario = u.id_usuario
    LEFT JOIN inscripciones i ON e.id_estudiante = i.id_estudiante
    LEFT JOIN calificaciones c ON i.id_inscripcion = c.id_inscripcion";
$stmt = $db->prepare($query);
$stmt->execute();
$estadisticas = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Notas - Colegio Dora Schmidt - A</title>
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
                        <a class="nav-link active" href="notas.php">
                            <i class="fas fa-clipboard-list me-1"></i>Notas
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

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h4><?php echo $estadisticas['total_estudiantes']; ?></h4>
                        <p class="mb-0">Total Estudiantes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                        <h4><?php echo number_format($estadisticas['promedio_notas'], 1); ?></h4>
                        <p class="mb-0">Promedio General</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-arrow-up fa-2x mb-2"></i>
                        <h4><?php echo $estadisticas['nota_maxima']; ?></h4>
                        <p class="mb-0">Nota Máxima</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-arrow-down fa-2x mb-2"></i>
                        <h4><?php echo $estadisticas['nota_minima']; ?></h4>
                        <p class="mb-0">Nota Mínima</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón para agregar estudiante -->
        <div class="row mb-3">
            <div class="col-12">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalEstudiante">
                    <i class="fas fa-plus me-2"></i>Agregar Estudiante
                </button>
            </div>
        </div>

        <!-- Tabla de estudiantes -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Lista de Estudiantes
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Email</th>
                                <th>Código</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($estudiantes)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No hay estudiantes registrados</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($estudiantes as $estudiante): ?>
                            <tr>
                                <td><?php echo $estudiante['id_estudiante']; ?></td>
                                <td><?php echo htmlspecialchars($estudiante['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['email']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['codigo']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary btn-action me-1" 
                                            onclick="editarEstudiante(<?php echo htmlspecialchars(json_encode($estudiante)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-action" 
                                            onclick="eliminarEstudiante(<?php echo $estudiante['id_estudiante']; ?>, '<?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?>')">
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

    <!-- Modal para agregar/editar estudiante -->
    <div class="modal fade" id="modalEstudiante" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Agregar Estudiante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formEstudiante">
                    <div class="modal-body">
                        <input type="hidden" name="accion" id="accion" value="crear">
                        <input type="hidden" name="id" id="estudianteId">
                        
                        <div class="mb-3">
                            <label for="nombres" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código de Estudiante</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                        </div>
                        
                        <div class="mb-3">
                            <label for="genero" class="form-label">Género</label>
                            <select class="form-control" id="genero" name="genero">
                                <option value="">Seleccione</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
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
                    <p>¿Está seguro de que desea eliminar al estudiante <strong id="nombreEliminar"></strong>?</p>
                    <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" id="idEliminar">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        function editarEstudiante(estudiante) {
            document.getElementById('modalTitulo').textContent = 'Editar Estudiante';
            document.getElementById('accion').value = 'editar';
            document.getElementById('estudianteId').value = estudiante.id_estudiante;
            document.getElementById('nombres').value = estudiante.nombre;
            document.getElementById('apellidos').value = estudiante.apellido;
            document.getElementById('email').value = estudiante.email;
            document.getElementById('codigo').value = estudiante.codigo;
            document.getElementById('fecha_nacimiento').value = estudiante.fecha_nacimiento;
            document.getElementById('genero').value = estudiante.genero;
            document.getElementById('btnGuardar').textContent = 'Actualizar';
            
            new bootstrap.Modal(document.getElementById('modalEstudiante')).show();
        }
        
        function eliminarEstudiante(id, nombre) {
            document.getElementById('idEliminar').value = id;
            document.getElementById('nombreEliminar').textContent = nombre;
            
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }
        
        // Limpiar formulario cuando se cierre el modal
        document.getElementById('modalEstudiante').addEventListener('hidden.bs.modal', function () {
            document.getElementById('formEstudiante').reset();
            document.getElementById('modalTitulo').textContent = 'Agregar Estudiante';
            document.getElementById('accion').value = 'crear';
            document.getElementById('btnGuardar').textContent = 'Guardar';
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
