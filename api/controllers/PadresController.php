<?php
// Controlador de la API para Padres
class PadresController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        if (!$this->db) {
            $this->sendResponse(['error' => 'Error de conexión a la base de datos'], 500);
        }
    }
    
    // GET /api/padres - Obtener todos los padres
    public function getAll() {
        try {
            $buscar = $_GET['buscar'] ?? '';
            
            $query = "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.creado_en, p.id_padre
                      FROM usuarios u 
                      JOIN padres p ON u.id_usuario = p.id_usuario
                      WHERE u.rol = 'padre'";
            
            $params = [];
            if (!empty($buscar)) {
                $query .= " AND (u.nombre LIKE ? OR u.apellido LIKE ? OR u.email LIKE ?)";
                $like = "%$buscar%";
                $params = [$like, $like, $like];
            }
            
            $query .= " ORDER BY u.apellido, u.nombre";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $padres = $stmt->fetchAll();
            
            // Obtener asociaciones para cada padre
            foreach ($padres as &$padre) {
                $padre['estudiantes_asociados'] = $this->getEstudiantesAsociados($padre['id_padre']);
            }
            
            $this->sendResponse([
                'success' => true,
                'data' => $padres,
                'total' => count($padres)
            ]);
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Error al obtener padres: ' . $e->getMessage()], 500);
        }
    }
    
    // GET /api/padres/{id} - Obtener un padre por ID
    public function getById($id) {
        try {
            $query = "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.creado_en, p.id_padre
                      FROM usuarios u 
                      JOIN padres p ON u.id_usuario = p.id_usuario
                      WHERE p.id_padre = ? AND u.rol = 'padre'";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            $padre = $stmt->fetch();
            
            if (!$padre) {
                $this->sendResponse(['error' => 'Padre no encontrado'], 404);
            }
            
            $padre['estudiantes_asociados'] = $this->getEstudiantesAsociados($id);
            
            $this->sendResponse([
                'success' => true,
                'data' => $padre
            ]);
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Error al obtener padre: ' . $e->getMessage()], 500);
        }
    }
    
    // POST /api/padres - Crear un nuevo padre
    public function create() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $this->sendResponse(['error' => 'Datos JSON inválidos'], 400);
            }
            
            $nombres = trim($input['nombres'] ?? '');
            $apellidos = trim($input['apellidos'] ?? '');
            $email = trim($input['email'] ?? '');
            $password = trim($input['password'] ?? '');
            
            if (empty($nombres) || empty($apellidos) || empty($email) || empty($password)) {
                $this->sendResponse(['error' => 'Faltan campos obligatorios'], 400);
            }
            
            $this->db->beginTransaction();
            
            // Crear usuario
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES (?, ?, ?, ?, 'padre')";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$nombres, $apellidos, $email, $hash]);
            $id_usuario = $this->db->lastInsertId();
            
            // Crear registro en tabla padres
            $query = "INSERT INTO padres (id_usuario) VALUES (?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id_usuario]);
            $id_padre = $this->db->lastInsertId();
            
            $this->db->commit();
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Padre creado correctamente',
                'data' => [
                    'id_padre' => $id_padre,
                    'id_usuario' => $id_usuario,
                    'nombre' => $nombres,
                    'apellido' => $apellidos,
                    'email' => $email
                ]
            ], 201);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->sendResponse(['error' => 'Error al crear padre: ' . $e->getMessage()], 500);
        }
    }
    
    // PUT /api/padres/{id} - Actualizar un padre
    public function update($id) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $this->sendResponse(['error' => 'Datos JSON inválidos'], 400);
            }
            
            $nombres = trim($input['nombres'] ?? '');
            $apellidos = trim($input['apellidos'] ?? '');
            $email = trim($input['email'] ?? '');
            
            if (empty($nombres) || empty($apellidos) || empty($email)) {
                $this->sendResponse(['error' => 'Faltan campos obligatorios'], 400);
            }
            
            // Verificar que el padre existe
            $query = "SELECT u.id_usuario FROM usuarios u 
                      JOIN padres p ON u.id_usuario = p.id_usuario 
                      WHERE p.id_padre = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            $padre = $stmt->fetch();
            
            if (!$padre) {
                $this->sendResponse(['error' => 'Padre no encontrado'], 404);
            }
            
            // Actualizar usuario
            $query = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ? WHERE id_usuario = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$nombres, $apellidos, $email, $padre['id_usuario']]);
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Padre actualizado correctamente'
            ]);
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Error al actualizar padre: ' . $e->getMessage()], 500);
        }
    }
    
    // DELETE /api/padres/{id} - Eliminar un padre
    public function delete($id) {
        try {
            // Verificar que el padre existe
            $query = "SELECT u.id_usuario FROM usuarios u 
                      JOIN padres p ON u.id_usuario = p.id_usuario 
                      WHERE p.id_padre = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            $padre = $stmt->fetch();
            
            if (!$padre) {
                $this->sendResponse(['error' => 'Padre no encontrado'], 404);
            }
            
            $this->db->beginTransaction();
            
            // Eliminar asociaciones primero
            $query = "DELETE FROM relacion_padre_estudiante WHERE id_padre = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            
            // Eliminar registro de padre
            $query = "DELETE FROM padres WHERE id_padre = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            
            // Eliminar usuario
            $query = "DELETE FROM usuarios WHERE id_usuario = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$padre['id_usuario']]);
            
            $this->db->commit();
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Padre eliminado correctamente'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->sendResponse(['error' => 'Error al eliminar padre: ' . $e->getMessage()], 500);
        }
    }
    
    // POST /api/padres/{id}/asociar - Asociar estudiante a padre
    public function asociarEstudiante($id) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['estudiante_id'])) {
                $this->sendResponse(['error' => 'ID de estudiante requerido'], 400);
            }
            
            $estudiante_id = intval($input['estudiante_id']);
            
            if ($estudiante_id <= 0) {
                $this->sendResponse(['error' => 'ID de estudiante inválido'], 400);
            }
            
            // Verificar que el padre existe
            $query = "SELECT id_padre FROM padres WHERE id_padre = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            
            if (!$stmt->fetch()) {
                $this->sendResponse(['error' => 'Padre no encontrado'], 404);
            }
            
            // Verificar que el estudiante existe
            $query = "SELECT id_estudiante FROM estudiantes WHERE id_estudiante = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$estudiante_id]);
            
            if (!$stmt->fetch()) {
                $this->sendResponse(['error' => 'Estudiante no encontrado'], 404);
            }
            
            // Crear asociación
            $query = "INSERT INTO relacion_padre_estudiante (id_padre, id_estudiante) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id, $estudiante_id]);
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Asociación creada correctamente'
            ], 201);
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Error al asociar estudiante: ' . $e->getMessage()], 500);
        }
    }
    
    // DELETE /api/padres/{id}/desasociar - Desasociar estudiante de padre
    public function desasociarEstudiante($id) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['estudiante_id'])) {
                $this->sendResponse(['error' => 'ID de estudiante requerido'], 400);
            }
            
            $estudiante_id = intval($input['estudiante_id']);
            
            if ($estudiante_id <= 0) {
                $this->sendResponse(['error' => 'ID de estudiante inválido'], 400);
            }
            
            // Eliminar asociación
            $query = "DELETE FROM relacion_padre_estudiante WHERE id_padre = ? AND id_estudiante = ?";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$id, $estudiante_id]);
            
            if ($stmt->rowCount() === 0) {
                $this->sendResponse(['error' => 'Asociación no encontrada'], 404);
            }
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Asociación eliminada correctamente'
            ]);
            
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Error al desasociar estudiante: ' . $e->getMessage()], 500);
        }
    }
    
    // Método auxiliar para obtener estudiantes asociados
    private function getEstudiantesAsociados($id_padre) {
        $query = "SELECT e.id_estudiante, u.nombre, u.apellido
                  FROM relacion_padre_estudiante rpe
                  JOIN estudiantes e ON rpe.id_estudiante = e.id_estudiante
                  JOIN usuarios u ON e.id_usuario = u.id_usuario
                  WHERE rpe.id_padre = ?
                  ORDER BY u.apellido, u.nombre";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id_padre]);
        return $stmt->fetchAll();
    }
    
    // Método auxiliar para enviar respuestas JSON
    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
?>
