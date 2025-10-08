<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// 🚫 Evita que el navegador guarde en caché el formulario de login
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Si ya está logueado, mándalo al dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_POST) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role'] ?? '');
    
    if (!empty($email) && !empty($password) && !empty($role)) {
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            $query = "SELECT id_usuario, nombre, apellido, email, password, rol 
                      FROM usuarios 
                      WHERE email = ? AND rol = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$email, $role]);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                
                // Verificar contraseña
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id_usuario'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['tipo_usuario'] = $user['rol'];
                    $_SESSION['nombres'] = $user['nombre'];
                    $_SESSION['apellidos'] = $user['apellido'];
                    
                    // 🚀 Evita reenvío de formulario (PRG)
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Credenciales incorrectas';
                }
            } else {
                $error = 'Usuario o rol no válido';
            }
        } else {
            $error = 'Error de conexión a la base de datos';
        }
    } else {
        $error = 'Por favor, complete todos los campos';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Colegio Dora Schmidt - A</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #5f1818ff 0%, #1d1653ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #741d1dff 0%, #100c42ff 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #420e0eff;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #421616ff 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-container">
                    <div class="login-header">
                        <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                        <h3>Colegio Dora Schmidt - A</h3>
                        <p class="mb-0">Sistema de Gestión Escolar</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Contraseña
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="role" class="form-label">
                                    <i class="fas fa-users me-2"></i>Rol
                                </label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="">Seleccione un rol</option>
                                    <option value="admin">Administrador</option>
                                    <option value="docente">Docente</option>
                                    <option value="padre">Padre</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-login w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>                                
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const toggleIcon = document.getElementById("toggleIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>
