
CREATE DATABASE IF NOT EXISTS escuela CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


USE escuela;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('administrador', 'docente', 'padre') NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    nota DECIMAL(5,2) NOT NULL CHECK (nota >= 0 AND nota <= 100),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla para relacionar padres con estudiantes
CREATE TABLE padre_estudiante (
    id INT AUTO_INCREMENT PRIMARY KEY,
    padre_id INT NOT NULL,
    estudiante_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (padre_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_padre_estudiante (padre_id, estudiante_id)
);

-- Tabla para registrar asistencias
CREATE TABLE asistencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    docente_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_llegada TIME,
    hora_salida TIME,
    estado ENUM('presente', 'tardanza', 'ausente', 'justificado') DEFAULT 'presente',
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (docente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_estudiante_fecha (estudiante_id, fecha)
);

INSERT INTO usuarios (username, password, tipo_usuario, nombres, apellidos, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', 'Administrador', 'Sistema', 'admin@doriasmith.edu'),
('docente1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'docente', 'María', 'González', 'maria.gonzalez@doriasmith.edu'),
('padre1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'padre', 'Carlos', 'Rodríguez', 'carlos.rodriguez@email.com');

INSERT INTO estudiantes (nombres, apellidos, nota) VALUES
('Ana', 'Martínez', 85.5),
('Luis', 'Fernández', 92.0),
('Sofia', 'López', 78.5),
('Diego', 'García', 88.0),
('Valentina', 'Hernández', 95.5);

-- Relacionar padre con estudiantes (padre1 con Ana y Luis)
INSERT INTO padre_estudiante (padre_id, estudiante_id) VALUES
(3, 1), -- Carlos Rodríguez con Ana Martínez
(3, 2); -- Carlos Rodríguez con Luis Fernández

-- Insertar algunas asistencias de ejemplo
INSERT INTO asistencias (estudiante_id, docente_id, fecha, hora_llegada, hora_salida, estado, observaciones) VALUES
(1, 2, CURDATE(), '08:00:00', '15:30:00', 'presente', 'Asistencia normal'),
(2, 2, CURDATE(), '08:15:00', '15:45:00', 'tardanza', 'Llegó 15 minutos tarde'),
(3, 2, CURDATE(), NULL, NULL, 'ausente', 'No asistió a clases'),
(4, 2, CURDATE(), '07:45:00', '15:20:00', 'presente', 'Asistencia normal'),
(5, 2, CURDATE(), '08:05:00', '15:35:00', 'presente', 'Asistencia normal');

CREATE INDEX idx_usuarios_username ON usuarios(username);
CREATE INDEX idx_usuarios_tipo ON usuarios(tipo_usuario);
CREATE INDEX idx_estudiantes_apellidos ON estudiantes(apellidos);
CREATE INDEX idx_estudiantes_nota ON estudiantes(nota);
CREATE INDEX idx_padre_estudiante_padre ON padre_estudiante(padre_id);
CREATE INDEX idx_padre_estudiante_estudiante ON padre_estudiante(estudiante_id);
CREATE INDEX idx_asistencias_estudiante ON asistencias(estudiante_id);
CREATE INDEX idx_asistencias_docente ON asistencias(docente_id);
CREATE INDEX idx_asistencias_fecha ON asistencias(fecha);
