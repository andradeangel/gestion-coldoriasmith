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
        case 'crear_usuario':
            $nombres = trim($_POST['nombres'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $rol = trim($_POST['rol'] ?? '');
            
            if (!empty($nombres) && !empty($apellidos) && !empty($email) && !empty($password) && !empty($rol)) {
                try {
                    $db->beginTransaction();
                    
                    // Crear usuario
                    $query = "INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $db->prepare($query);
                    $password_hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt->execute([$nombres, $apellidos, $email, $password_hash, $rol]);
                    $usuario_id = $db->lastInsertId();
                    
                    // Crear registro específico según el rol
                    if ($rol === 'docente') {
                        $query = "INSERT INTO docentes (id_usuario, especialidad) VALUES (?, ?)";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$usuario_id, $_POST['especialidad'] ?? 'General']);
                    } elseif ($rol === 'padre') {
                        $query = "INSERT INTO padres (id_usuario, telefono) VALUES (?, ?)";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$usuario_id, $_POST['telefono'] ?? '']);
                    } elseif ($rol === 'estudiante') {
                        $query = "INSERT INTO estudiantes (id_usuario, codigo, fecha_nacimiento, genero) VALUES (?, ?, ?, ?)";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$usuario_id, $_POST['codigo'] ?? '', $_POST['fecha_nacimiento'] ?? null, $_POST['genero'] ?? '']);
                    }
                    
                    $db->commit();
                    $mensaje = 'Usuario creado exitosamente';
                    $tipoMensaje = 'success';
                } catch (Exception $e) {
                    $db->rollBack();
                    $mensaje = 'Error al crear el usuario: ' . $e->getMessage();
                    $tipoMensaje = 'danger';
                }
            } else {
                $mensaje = 'Por favor, complete todos los campos obligatorios';
                $tipoMensaje = 'warning';
            }
            break;
            
        case 'editar_usuario':
            $id = intval($_POST['id'] ?? 0);
            $nombres = trim($_POST['nombres'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $rol = trim($_POST['rol'] ?? '');
            
            if ($id > 0 && !empty($nombres) && !empty($apellidos) && !empty($email) && !empty($rol)) {
                try {
                    $query = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, rol = ? WHERE id_usuario = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$nombres, $apellidos, $email, $rol, $id]);
                    
                    $mensaje = 'Usuario actualizado exitosamente';
                    $tipoMensaje = 'success';
                } catch (Exception $e) {
                    $mensaje = 'Error al actualizar el usuario';
                    $tipoMensaje = 'danger';
                }
            } else {
                $mensaje = 'Datos inválidos para la actualización';
                $tipoMensaje = 'warning';
            }
            break;
            
        case 'eliminar_usuario':
            $id = intval($_POST['id'] ?? 0);
            
            if ($id > 0) {
                try {
                    $query = "DELETE FROM usuarios WHERE id_usuario = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$id]);
                    
                    $mensaje = 'Usuario eliminado exitosamente';
                    $tipoMensaje = 'success';
                } catch (Exception $e) {
                    $mensaje = 'Error al eliminar el usuario';
                    $tipoMensaje = 'danger';
                }
            } else {
                $mensaje = 'ID de usuario inválido';
                $tipoMensaje = 'warning';
            }
            break;
    }
}

// Obtener lista de usuarios
$query = "SELECT * FROM usuarios ORDER BY apellido, nombre";
$stmt = $db->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll();

// Obtener estadísticas
$query = "SELECT 
    COUNT(*) as total_usuarios,
    SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as administradores,
    SUM(CASE WHEN rol = 'docente' THEN 1 ELSE 0 END) as docentes,
    SUM(CASE WHEN rol = 'padre' THEN 1 ELSE 0 END) as padres,
    SUM(CASE WHEN rol = 'estudiante' THEN 1 ELSE 0 END) as estudiantes
    FROM usuarios";
$stmt = $db->prepare($query);
$stmt->execute();
$estadisticas = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Colegio Dora Schmidt - A</title>
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
                        <a class="nav-link" href="padres.php">
                            <i class="fas fa-user-friends me-1"></i>Padres
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="usuarios.php">
                            <i class="fas fa-users me-1"></i>Usuarios
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
                                <i class="fas fa-user-shield me-2"></i>Administrador
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
                        <h4><?php echo $estadisticas['total_usuarios']; ?></h4>
                        <p class="mb-0">Total Usuarios</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                        <h4><?php echo $estadisticas['docentes']; ?></h4>
                        <p class="mb-0">Docentes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-user-friends fa-2x mb-2"></i>
                        <h4><?php echo $estadisticas['padres']; ?></h4>
                        <p class="mb-0">Padres</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-user-graduate fa-2x mb-2"></i>
                        <h4><?php echo $estadisticas['estudiantes']; ?></h4>
                        <p class="mb-0">Estudiantes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón para agregar usuario -->
        <div class="row mb-3">
            <div class="col-12">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                    <i class="fas fa-plus me-2"></i>Agregar Usuario
                </button>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Lista de Usuarios
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
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No hay usuarios registrados</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo $usuario['id_usuario']; ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $usuario['rol'] === 'admin' ? 'danger' : 
                                            ($usuario['rol'] === 'docente' ? 'success' : 
                                            ($usuario['rol'] === 'padre' ? 'info' : 'warning')); 
                                    ?>">
                                        <?php echo ucfirst($usuario['rol']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary btn-action me-1" 
                                            onclick="editarUsuario(<?php echo htmlspecialchars(json_encode($usuario)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-action" 
                                            onclick="eliminarUsuario(<?php echo $usuario['id_usuario']; ?>, '<?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>')">
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

    <!-- Modal para agregar/editar usuario -->
    <div class="modal fade" id="modalUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Agregar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formUsuario">
                    <div class="modal-body">
                        <input type="hidden" name="accion" id="accion" value="crear_usuario">
                        <input type="hidden" name="id" id="usuarioId">
                        
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
                        
                        <div class="mb-3" id="divPassword">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        
                        <div class="mb-3">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-control" id="rol" name="rol" required>
                                <option value="">Seleccione un rol</option>
                                <option value="admin">Administrador</option>
                                <option value="docente">Docente</option>
                                <option value="padre">Padre</option>
                                <option value="estudiante">Estudiante</option>
                            </select>
                        </div>
                        
                        <!-- Campos adicionales según el rol -->
                        <div id="camposAdicionales" style="display: none;">
                            <div class="mb-3" id="campoEspecialidad" style="display: none;">
                                <label for="especialidad" class="form-label">Especialidad</label>
                                <input type="text" class="form-control" id="especialidad" name="especialidad">
                            </div>
                            
                            <div class="mb-3" id="campoTelefono" style="display: none;">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono">
                            </div>
                            
                            <div class="mb-3" id="campoCodigo" style="display: none;">
                                <label for="codigo" class="form-label">Código de Estudiante</label>
                                <input type="text" class="form-control" id="codigo" name="codigo">
                            </div>
                            
                            <div class="mb-3" id="campoFechaNacimiento" style="display: none;">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                            </div>
                            
                            <div class="mb-3" id="campoGenero" style="display: none;">
                                <label for="genero" class="form-label">Género</label>
                                <select class="form-control" id="genero" name="genero">
                                    <option value="">Seleccione</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
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
                    <p>¿Está seguro de que desea eliminar al usuario <strong id="nombreEliminar"></strong>?</p>
                    <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="accion" value="eliminar_usuario">
                        <input type="hidden" name="id" id="idEliminar">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        function editarUsuario(usuario) {
            document.getElementById('modalTitulo').textContent = 'Editar Usuario';
            document.getElementById('accion').value = 'editar_usuario';
            document.getElementById('usuarioId').value = usuario.id_usuario;
            document.getElementById('nombres').value = usuario.nombre;
            document.getElementById('apellidos').value = usuario.apellido;
            document.getElementById('email').value = usuario.email;
            document.getElementById('rol').value = usuario.rol;
            document.getElementById('password').required = false;
            document.getElementById('btnGuardar').textContent = 'Actualizar';

            // Ocultar campos no editables en edición
            document.getElementById('divPassword').style.display = 'none';
            document.getElementById('camposAdicionales').style.display = 'none';

            new bootstrap.Modal(document.getElementById('modalUsuario')).show();
        }
        
        function eliminarUsuario(id, nombre) {
            document.getElementById('idEliminar').value = id;
            document.getElementById('nombreEliminar').textContent = nombre;
            
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }
        
        function mostrarCamposAdicionales(rol) {
            // Ocultar todos los campos adicionales
            document.getElementById('camposAdicionales').style.display = 'none';
            document.getElementById('campoEspecialidad').style.display = 'none';
            document.getElementById('campoTelefono').style.display = 'none';
            document.getElementById('campoCodigo').style.display = 'none';
            document.getElementById('campoFechaNacimiento').style.display = 'none';
            document.getElementById('campoGenero').style.display = 'none';
            
            // Mostrar campos según el rol
            if (rol === 'docente') {
                document.getElementById('camposAdicionales').style.display = 'block';
                document.getElementById('campoEspecialidad').style.display = 'block';
            } else if (rol === 'padre') {
                document.getElementById('camposAdicionales').style.display = 'block';
                document.getElementById('campoTelefono').style.display = 'block';
            } else if (rol === 'estudiante') {
                document.getElementById('camposAdicionales').style.display = 'block';
                document.getElementById('campoCodigo').style.display = 'block';
                document.getElementById('campoFechaNacimiento').style.display = 'block';
                document.getElementById('campoGenero').style.display = 'block';
            }
        }
        
        // Event listener para el cambio de rol
        document.getElementById('rol').addEventListener('change', function() {
            mostrarCamposAdicionales(this.value);
        });
        
        // Limpiar formulario cuando se cierre el modal
        document.getElementById('modalUsuario').addEventListener('hidden.bs.modal', function () {
            document.getElementById('formUsuario').reset();
            document.getElementById('modalTitulo').textContent = 'Agregar Usuario';
            document.getElementById('accion').value = 'crear_usuario';
            document.getElementById('btnGuardar').textContent = 'Guardar';
            document.getElementById('password').required = true;
            document.getElementById('camposAdicionales').style.display = 'none';
            // Mostrar campos ocultos en edición
            document.getElementById('divPassword').style.display = 'block';
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
