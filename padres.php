<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireAdministrador();

$database = new Database();
$db = $database->getConnection();

$mensaje = '';
$tipoMensaje = '';

if ($_POST) {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'crear_padre':
            $password = trim($_POST['password'] ?? '');
            $nombres = trim($_POST['nombres'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if ($password && $nombres && $apellidos) {
                try {
                    $db->beginTransaction();
                    
                    // Crear usuario
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $query = "INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES (?, ?, ?, ?, 'padre')";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$nombres, $apellidos, $email, $hash]);
                    $id_usuario = $db->lastInsertId();
                    
                    // Crear registro en tabla padres
                    $query = "INSERT INTO padres (id_usuario) VALUES (?)";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$id_usuario]);
                    
                    $db->commit();
                    $mensaje = 'Padre de familia creado correctamente';
                    $tipoMensaje = 'success';
                } catch (PDOException $e) {
                    $db->rollBack();
                    $mensaje = 'Error al crear padre: ' . ($e->errorInfo[1] == 1062 ? 'usuario ya existente' : 'verifique los datos');
                    $tipoMensaje = 'danger';
                }
            } else {
                $mensaje = 'Complete los campos obligatorios';
                $tipoMensaje = 'warning';
            }
            break;

        case 'asociar':
            $padre_id = intval($_POST['padre_id'] ?? 0);
            $estudiante_id = intval($_POST['estudiante_id'] ?? 0);
            if ($padre_id > 0 && $estudiante_id > 0) {
                try {
                    $query = "INSERT INTO relacion_padre_estudiante (id_padre, id_estudiante) VALUES (?, ?)";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$padre_id, $estudiante_id]);
                    $mensaje = 'Asociación creada/activada correctamente';
                    $tipoMensaje = 'success';
                } catch (PDOException $e) {
                    $mensaje = 'Error al asociar: verifique los datos';
                    $tipoMensaje = 'danger';
                }
            } else {
                $mensaje = 'Seleccione padre y estudiante';
                $tipoMensaje = 'warning';
            }
            break;

        case 'desasociar':
            $padre_id = intval($_POST['padre_id'] ?? 0);
            $estudiante_id = intval($_POST['estudiante_id'] ?? 0);
            if ($padre_id > 0 && $estudiante_id > 0) {
                $query = "DELETE FROM relacion_padre_estudiante WHERE id_padre = ? AND id_estudiante = ?";
                $stmt = $db->prepare($query);
                if ($stmt->execute([$padre_id, $estudiante_id])) {
                    $mensaje = 'Asociación desactivada';
                    $tipoMensaje = 'info';
                } else {
                    $mensaje = 'No se pudo desactivar la asociación';
                    $tipoMensaje = 'danger';
                }
            } else {
                $mensaje = 'Seleccione padre y estudiante';
                $tipoMensaje = 'warning';
            }
            break;
    }
}

// Filtros
$buscar = trim($_GET['buscar'] ?? '');

// Obtener lista de padres
$queryPadres = "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.creado_en, p.id_padre
                FROM usuarios u 
                JOIN padres p ON u.id_usuario = p.id_usuario
                WHERE u.rol = 'padre' AND (
                    u.nombre LIKE ? OR u.apellido LIKE ? OR u.email LIKE ?
                )
                ORDER BY u.apellido, u.nombre";
$stmt = $db->prepare($queryPadres);
$like = "%$buscar%";
$stmt->execute([$like, $like, $like]);
$padres = $stmt->fetchAll();

// Obtener estudiantes activos
$stmt = $db->prepare("SELECT e.id_estudiante, u.nombre, u.apellido 
                     FROM estudiantes e 
                     JOIN usuarios u ON e.id_usuario = u.id_usuario 
                     ORDER BY u.apellido, u.nombre");
$stmt->execute();
$estudiantes = $stmt->fetchAll();

// Mapa de asociaciones por padre
$asociaciones = [];
if (!empty($padres)) {
    $padreIds = array_column($padres, 'id_padre');
    $in  = str_repeat('?,', count($padreIds) - 1) . '?';
    $sql = "SELECT rpe.id_padre, e.id_estudiante, u.nombre, u.apellido
            FROM relacion_padre_estudiante rpe
            JOIN estudiantes e ON rpe.id_estudiante = e.id_estudiante
            JOIN usuarios u ON e.id_usuario = u.id_usuario
            WHERE rpe.id_padre IN ($in)
            ORDER BY u.apellido, u.nombre";
    $stmt = $db->prepare($sql);
    $stmt->execute($padreIds);
    foreach ($stmt->fetchAll() as $row) {
        $asociaciones[$row['id_padre']][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Padres - Colegio Dora Schmidt - A</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .btn-action { border-radius: 20px; padding: 5px 12px; }
        .badge-inactivo { background: #6c757d; }
    </style>
<?php /* No scripts adicionales en <head> */ ?>
</head>
<body>
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
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-1"></i>Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="padres.php"><i class="fas fa-user-friends me-1"></i>Padres</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars(getNombreCompleto()); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><span class="dropdown-item-text"><i class="fas fa-user-shield me-2"></i>Administrador</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $tipoMensaje === 'success' ? 'check-circle' : ($tipoMensaje === 'danger' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
            <?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-filter me-2"></i>Filtrar Padres</h5>
                        <form class="d-flex" method="GET">
                            <input type="text" class="form-control me-2" name="buscar" placeholder="Buscar por nombre, apellido o email" value="<?php echo htmlspecialchars($buscar); ?>">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i>Buscar</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-user-plus me-2"></i>Nuevo Padre</h5>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoPadre"><i class="fas fa-plus me-1"></i>Crear</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-friends me-2"></i>Listado de Padres</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Estudiantes Asociados</th>
                                <th>Asociar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($padres)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><p class="text-muted mb-0">No hay padres registrados</p></td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($padres as $padre): ?>
                            <tr>
                                <td><?php echo $padre['id_padre']; ?></td>
                                <td><?php echo htmlspecialchars($padre['nombre'] . ' ' . $padre['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($padre['email']); ?></td>
                                <td>
                                    <?php if (!empty($asociaciones[$padre['id_padre']])): ?>
                                        <?php foreach ($asociaciones[$padre['id_padre']] as $rel): ?>
                                            <span class="badge bg-info mb-1">
                                                <?php echo htmlspecialchars($rel['nombre'] . ' ' . $rel['apellido']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Sin asociaciones</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form class="d-flex" method="POST">
                                        <input type="hidden" name="accion" value="asociar">
                                        <input type="hidden" name="padre_id" value="<?php echo $padre['id_padre']; ?>">
                                        <select class="form-select me-2" name="estudiante_id" required>
                                            <option value="">Seleccione estudiante</option>
                                            <?php foreach ($estudiantes as $est): ?>
                                            <option value="<?php echo $est['id_estudiante']; ?>"><?php echo htmlspecialchars($est['nombre'] . ' ' . $est['apellido']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-primary btn-action me-2" type="submit"><i class="fas fa-link"></i></button>
                                    </form>
                                    <?php if (!empty($asociaciones[$padre['id_padre']])): ?>
                                    <form class="d-inline" method="POST">
                                        <input type="hidden" name="accion" value="desasociar">
                                        <input type="hidden" name="padre_id" value="<?php echo $padre['id_padre']; ?>">
                                        <select class="form-select d-inline-block w-auto me-2" name="estudiante_id" required>
                                            <option value="">Quitar estudiante</option>
                                            <?php foreach ($asociaciones[$padre['id_padre']] as $rel): ?>
                                            <option value="<?php echo $rel['id_estudiante']; ?>"><?php echo htmlspecialchars($rel['nombre'] . ' ' . $rel['apellido']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-outline-danger btn-action" type="submit"><i class="fas fa-unlink"></i></button>
                                    </form>
                                    <?php endif; ?>
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

    <!-- Modal Nuevo Padre -->
    <div class="modal fade" id="modalNuevoPadre" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Padre de Familia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="crear_padre">
                        <div class="mb-3">
                            <label class="form-label">Nombres</label>
                            <input type="text" class="form-control" name="nombres" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        // Prevenir navegación hacia atrás después del logout
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function () { window.history.pushState(null, null, window.location.href); };
        if (window.history && window.history.pushState) { window.history.replaceState(null, null, window.location.href); }
    </script>
</body>
</html>


