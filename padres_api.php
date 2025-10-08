<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireAdministrador();

$database = new Database();
$db = $database->getConnection();

// Obtener estudiantes para los selectores
$stmt = $db->prepare("SELECT e.id_estudiante, u.nombre, u.apellido 
                     FROM estudiantes e 
                     JOIN usuarios u ON e.id_usuario = u.id_usuario 
                     ORDER BY u.apellido, u.nombre");
$stmt->execute();
$estudiantes = $stmt->fetchAll();
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
        .loading { opacity: 0.6; pointer-events: none; }
    </style>
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
                        <a class="nav-link active" href="padres_api.php"><i class="fas fa-user-friends me-1"></i>Padres (API)</a>
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
        <!-- Alertas dinámicas -->
        <div id="alertContainer"></div>

        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-filter me-2"></i>Filtrar Padres</h5>
                        <form class="d-flex" onsubmit="filtrarPadres(event)">
                            <input type="text" class="form-control me-2" id="buscarInput" placeholder="Buscar por nombre, apellido o email">
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
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="padresTableBody">
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">Cargando padres...</p>
                                </td>
                            </tr>
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
                <form id="formNuevoPadre" onsubmit="crearPadre(event)">
                    <div class="modal-body">
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
                        <button type="submit" class="btn btn-primary" id="btnGuardarPadre">
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Padre -->
    <div class="modal fade" id="modalEditarPadre" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Padre de Familia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarPadre" onsubmit="editarPadre(event)">
                    <div class="modal-body">
                        <input type="hidden" id="editIdPadre" name="id">
                        <div class="mb-3">
                            <label class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="editNombres" name="nombres" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="editApellidos" name="apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnActualizarPadre">
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        // Variables globales
        let padresData = [];
        let estudiantesData = <?php echo json_encode($estudiantes); ?>;

        // Función para mostrar alertas
        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alertContainer');
            const alertId = 'alert-' + Date.now();
            
            const alertHtml = `
                <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'danger' ? 'exclamation-triangle' : 'info-circle')} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            alertContainer.innerHTML = alertHtml;
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }

        // Función para hacer peticiones a la API
        async function apiRequest(url, options = {}) {
            try {
                const response = await fetch(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    },
                    ...options
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Error en la petición');
                }
                
                return data;
            } catch (error) {
                console.error('Error en API:', error);
                throw error;
            }
        }

        // Cargar padres al iniciar
        async function cargarPadres(buscar = '') {
            try {
                const url = buscar ? `/api/padres?buscar=${encodeURIComponent(buscar)}` : '/api/padres';
                const response = await apiRequest(url);
                padresData = response.data;
                renderizarPadres();
            } catch (error) {
                showAlert('Error al cargar padres: ' + error.message, 'danger');
                renderizarPadres([]);
            }
        }

        // Renderizar tabla de padres
        function renderizarPadres(padres = padresData) {
            const tbody = document.getElementById('padresTableBody');
            
            if (padres.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No hay padres registrados</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = padres.map(padre => `
                <tr>
                    <td>${padre.id_padre}</td>
                    <td>${padre.nombre} ${padre.apellido}</td>
                    <td>${padre.email}</td>
                    <td>
                        ${padre.estudiantes_asociados && padre.estudiantes_asociados.length > 0 
                            ? padre.estudiantes_asociados.map(est => 
                                `<span class="badge bg-info mb-1">${est.nombre} ${est.apellido}</span>`
                              ).join(' ')
                            : '<span class="text-muted">Sin asociaciones</span>'
                        }
                    </td>
                    <td>
                        <form class="d-flex" onsubmit="asociarEstudiante(event, ${padre.id_padre})">
                            <select class="form-select me-2" name="estudiante_id" required>
                                <option value="">Seleccione estudiante</option>
                                ${estudiantesData.map(est => 
                                    `<option value="${est.id_estudiante}">${est.nombre} ${est.apellido}</option>`
                                ).join('')}
                            </select>
                            <button class="btn btn-primary btn-action me-2" type="submit">
                                <i class="fas fa-link"></i>
                            </button>
                        </form>
                        ${padre.estudiantes_asociados && padre.estudiantes_asociados.length > 0 
                            ? `<form class="d-inline" onsubmit="desasociarEstudiante(event, ${padre.id_padre})">
                                <select class="form-select d-inline-block w-auto me-2" name="estudiante_id" required>
                                    <option value="">Quitar estudiante</option>
                                    ${padre.estudiantes_asociados.map(est => 
                                        `<option value="${est.id_estudiante}">${est.nombre} ${est.apellido}</option>`
                                    ).join('')}
                                </select>
                                <button class="btn btn-outline-danger btn-action" type="submit">
                                    <i class="fas fa-unlink"></i>
                                </button>
                            </form>`
                            : ''
                        }
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editarPadreModal(${padre.id_padre})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarPadre(${padre.id_padre}, '${padre.nombre} ${padre.apellido}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Crear nuevo padre
        async function crearPadre(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const btn = document.getElementById('btnGuardarPadre');
            const spinner = btn.querySelector('.spinner-border');
            
            try {
                btn.disabled = true;
                spinner.classList.remove('d-none');
                
                await apiRequest('/api/padres', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                
                showAlert('Padre creado correctamente', 'success');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('modalNuevoPadre')).hide();
                cargarPadres();
                
            } catch (error) {
                showAlert('Error al crear padre: ' + error.message, 'danger');
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        // Editar padre (modal)
        function editarPadreModal(id) {
            const padre = padresData.find(p => p.id_padre == id);
            if (!padre) return;
            
            document.getElementById('editIdPadre').value = padre.id_padre;
            document.getElementById('editNombres').value = padre.nombre;
            document.getElementById('editApellidos').value = padre.apellido;
            document.getElementById('editEmail').value = padre.email;
            
            new bootstrap.Modal(document.getElementById('modalEditarPadre')).show();
        }

        // Editar padre (enviar)
        async function editarPadre(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            const id = data.id;
            delete data.id;
            
            const btn = document.getElementById('btnActualizarPadre');
            const spinner = btn.querySelector('.spinner-border');
            
            try {
                btn.disabled = true;
                spinner.classList.remove('d-none');
                
                await apiRequest(`/api/padres/${id}`, {
                    method: 'PUT',
                    body: JSON.stringify(data)
                });
                
                showAlert('Padre actualizado correctamente', 'success');
                bootstrap.Modal.getInstance(document.getElementById('modalEditarPadre')).hide();
                cargarPadres();
                
            } catch (error) {
                showAlert('Error al actualizar padre: ' + error.message, 'danger');
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        // Eliminar padre
        async function eliminarPadre(id, nombre) {
            if (!confirm(`¿Está seguro de eliminar al padre ${nombre}?`)) {
                return;
            }
            
            try {
                await apiRequest(`/api/padres/${id}`, {
                    method: 'DELETE'
                });
                
                showAlert('Padre eliminado correctamente', 'success');
                cargarPadres();
                
            } catch (error) {
                showAlert('Error al eliminar padre: ' + error.message, 'danger');
            }
        }

        // Asociar estudiante
        async function asociarEstudiante(event, padreId) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const estudianteId = formData.get('estudiante_id');
            
            try {
                await apiRequest(`/api/padres/${padreId}/asociar`, {
                    method: 'POST',
                    body: JSON.stringify({ estudiante_id: parseInt(estudianteId) })
                });
                
                showAlert('Estudiante asociado correctamente', 'success');
                form.reset();
                cargarPadres();
                
            } catch (error) {
                showAlert('Error al asociar estudiante: ' + error.message, 'danger');
            }
        }

        // Desasociar estudiante
        async function desasociarEstudiante(event, padreId) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const estudianteId = formData.get('estudiante_id');
            
            try {
                await apiRequest(`/api/padres/${padreId}/desasociar`, {
                    method: 'DELETE',
                    body: JSON.stringify({ estudiante_id: parseInt(estudianteId) })
                });
                
                showAlert('Estudiante desasociado correctamente', 'success');
                form.reset();
                cargarPadres();
                
            } catch (error) {
                showAlert('Error al desasociar estudiante: ' + error.message, 'danger');
            }
        }

        // Filtrar padres
        function filtrarPadres(event) {
            event.preventDefault();
            const buscar = document.getElementById('buscarInput').value;
            cargarPadres(buscar);
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            cargarPadres();
        });
    </script>
</body>
</html>
