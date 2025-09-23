<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Verificar que el usuario esté logueado
requireLogin();

// Obtener información del usuario
$nombreCompleto = getNombreCompleto();
$tipoUsuario = $_SESSION['tipo_usuario'];

// Mensaje de bienvenida según el tipo de usuario
$mensajesBienvenida = [
    'admin' => [
        'titulo' => 'Panel de Administración',
        'mensaje' => 'Bienvenido al sistema de gestión del Colegio Dora Schmidt - A. Como administrador, tiene acceso completo a todas las funcionalidades del sistema.',
        'icono' => 'fas fa-user-shield',
        'color' => 'primary'
    ],
    'docente' => [
        'titulo' => 'Panel del Docente',
        'mensaje' => 'Bienvenido al sistema de gestión académica. Desde aquí puede gestionar las notas de sus estudiantes y acceder a todas las herramientas educativas.',
        'icono' => 'fas fa-chalkboard-teacher',
        'color' => 'success'
    ],
    'padre' => [
        'titulo' => 'Panel del Padre de Familia',
        'mensaje' => 'Bienvenido al portal de padres. Aquí puede consultar información sobre el rendimiento académico de su hijo/a y mantenerse informado sobre su progreso.',
        'icono' => 'fas fa-user-friends',
        'color' => 'info'
    ]
];

$infoUsuario = $mensajesBienvenida[$tipoUsuario];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Colegio Dora Schmidt - A</title>
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
        .welcome-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        .welcome-card:hover {
            transform: translateY(-5px);
        }
        .stats-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-3px);
        }
        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-logout {
            border-radius: 20px;
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>Inicio
                        </a>
                    </li>
                    <?php if (isDocente()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="notas.php">
                            <i class="fas fa-clipboard-list me-1"></i>Notas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="asistencia.php">
                            <i class="fas fa-clock me-1"></i>Asistencia
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (isPadre()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="consulta_padre.php">
                            <i class="fas fa-search me-1"></i>Consulta Académica
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (isAdministrador()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="padres.php">
                            <i class="fas fa-user-friends me-1"></i>Padres
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($nombreCompleto); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><span class="dropdown-item-text">
                                <i class="fas fa-user-tag me-2"></i>
                                <?php echo ucfirst($tipoUsuario); ?>
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
        <!-- Tarjeta de Bienvenida -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card welcome-card bg-<?php echo $infoUsuario['color']; ?> text-white">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <div class="icon-circle bg-white bg-opacity-25 mx-auto">
                                    <i class="<?php echo $infoUsuario['icono']; ?>"></i>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <h2 class="card-title mb-2"><?php echo $infoUsuario['titulo']; ?></h2>
                                <p class="card-text mb-0"><?php echo $infoUsuario['mensaje']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row">
            <?php if (isDocente()): ?>
            <div class="col-md-4 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-success text-white mx-auto mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="card-title">Estudiantes</h5>
                        <p class="card-text">Gestionar notas y calificaciones</p>
                        <a href="notas.php" class="btn btn-success">
                            <i class="fas fa-arrow-right me-1"></i>Ir a Notas
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-primary text-white mx-auto mb-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5 class="card-title">Asistencia</h5>
                        <p class="card-text">Registrar y controlar asistencia</p>
                        <a href="asistencia.php" class="btn btn-primary">
                            <i class="fas fa-arrow-right me-1"></i>Ir a Asistencia
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-info text-white mx-auto mb-3">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5 class="card-title">Reportes</h5>
                        <p class="card-text">Ver estadísticas y reportes</p>
                        <button class="btn btn-info" disabled>
                            <i class="fas fa-arrow-right me-1"></i>Próximamente
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (isPadre()): ?>
            <div class="col-md-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-info text-white mx-auto mb-3">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5 class="card-title">Consulta Académica</h5>
                        <p class="card-text">Ver notas y asistencias de su hijo/a</p>
                        <a href="consulta_padre.php" class="btn btn-info">
                            <i class="fas fa-arrow-right me-1"></i>Consultar
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-warning text-white mx-auto mb-3">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h5 class="card-title">Notificaciones</h5>
                        <p class="card-text">Recibir alertas importantes</p>
                        <button class="btn btn-warning" disabled>
                            <i class="fas fa-arrow-right me-1"></i>Próximamente
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (isAdministrador()): ?>
            <div class="col-md-4 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-success text-white mx-auto mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="card-title">Gestión de Usuarios</h5>
                        <p class="card-text">Administrar usuarios del sistema</p>
                        <button class="btn btn-success" disabled>
                            <i class="fas fa-arrow-right me-1"></i>Próximamente
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-info text-white mx-auto mb-3">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5 class="card-title">Reportes Generales</h5>
                        <p class="card-text">Ver estadísticas del colegio</p>
                        <button class="btn btn-info" disabled>
                            <i class="fas fa-arrow-right me-1"></i>Próximamente
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-warning text-white mx-auto mb-3">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h5 class="card-title">Configuración</h5>
                        <p class="card-text">Ajustes del sistema</p>
                        <button class="btn btn-warning" disabled>
                            <i class="fas fa-arrow-right me-1"></i>Próximamente
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Información del Usuario -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Información de la Sesión
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                                <p><strong>Nombre Completo:</strong> <?php echo htmlspecialchars($nombreCompleto); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Tipo de Usuario:</strong> 
                                    <span class="badge bg-<?php echo $infoUsuario['color']; ?>">
                                        <?php echo ucfirst($tipoUsuario); ?>
                                    </span>
                                </p>
                                <p><strong>Último Acceso:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
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
