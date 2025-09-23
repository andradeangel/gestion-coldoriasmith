# Migración del Sistema de Colegio - De database.sql a col.sql

## Resumen de la Migración

Se ha migrado exitosamente el sistema del Colegio Dora Schmidt - A de la base de datos de prueba (`database.sql`) a la base de datos completa (`col.sql`).

## Cambios Realizados

### 1. Configuración de Base de Datos
- **Archivo**: `config/database.php`
- **Cambio**: Actualizado el nombre de la base de datos de `escuela` a `col`

### 2. Estructura de Base de Datos
- **Antes**: Base de datos simple con tablas básicas
- **Después**: Base de datos completa y normalizada con:
  - `usuarios` (con campos: id_usuario, nombre, apellido, email, password, rol)
  - `estudiantes` (con campos: id_estudiante, id_usuario, codigo, fecha_nacimiento, genero)
  - `docentes` (con campos: id_docente, id_usuario, especialidad)
  - `padres` (con campos: id_padre, id_usuario, telefono)
  - `cursos`, `materias`, `curso_materia`
  - `inscripciones`, `asistencias`, `calificaciones`
  - `relacion_padre_estudiante`, `alertas`, `reportes`

### 3. Archivos PHP Migrados

#### `index.php` (Login)
- Actualizado para usar `email` en lugar de `username`
- Cambiados los campos de la consulta SQL
- Actualizado el rol `administrador` a `admin`

#### `includes/session.php`
- Actualizado el rol `administrador` a `admin`

#### `dashboard.php`
- Actualizado el rol `administrador` a `admin`

#### `asistencia.php`
- Migrado para usar la nueva estructura de asistencias
- Actualizado para trabajar con `inscripciones` y `curso_materia`
- Simplificado el manejo de asistencias (solo estado)

#### `notas.php`
- Migrado para usar la nueva estructura de estudiantes
- Agregados campos: email, codigo, fecha_nacimiento, genero
- Actualizado para crear usuarios y estudiantes por separado

#### `consulta_padre.php`
- Migrado para usar `relacion_padre_estudiante`
- Actualizado para obtener notas desde `calificaciones`
- Actualizado para obtener asistencias con la nueva estructura

#### `padres.php`
- Migrado para usar la nueva estructura de padres
- Actualizado para usar `relacion_padre_estudiante`
- Simplificado el formulario de creación

### 4. Nuevos Archivos Creados

#### `migrate_data.php`
- Script para insertar datos de prueba en la nueva base de datos
- Crea usuarios, estudiantes, docentes, padres, cursos, materias, etc.

## Instrucciones de Instalación

### Paso 1: Crear la Base de Datos
```sql
-- Ejecutar el archivo col.sql en MySQL/MariaDB
mysql -u root -p < col.sql
```

### Paso 2: Ejecutar la Migración de Datos
```bash
php migrate_data.php
```

### Paso 3: Verificar la Instalación
1. Acceder a `http://localhost/index.php`
2. Usar las credenciales:
   - **Admin**: admin@doriasmith.edu / admin123
   - **Docente**: maria.gonzalez@doriasmith.edu / docente123
   - **Padre**: carlos.rodriguez@email.com / padre123

## Funcionalidades Disponibles

### Para Administradores
- Gestión de padres de familia
- Asociación de padres con estudiantes
- Acceso completo al sistema

### Para Docentes
- Gestión de estudiantes (crear, editar, eliminar)
- Registro de asistencias
- Visualización de estadísticas

### Para Padres
- Consulta de información académica de sus hijos
- Visualización de notas y asistencias
- Estadísticas de rendimiento

## Notas Importantes

1. **Seguridad**: Las contraseñas están hasheadas usando `PASSWORD_BCRYPT`
2. **Relaciones**: El sistema ahora usa relaciones más complejas y normalizadas
3. **Escalabilidad**: La nueva estructura permite agregar más funcionalidades como:
   - Múltiples cursos y materias
   - Sistema de calificaciones por materia
   - Alertas y reportes
   - Gestión de docentes

## Próximos Pasos Sugeridos

1. Implementar el sistema de calificaciones por materia
2. Agregar funcionalidad de alertas automáticas
3. Crear reportes estadísticos avanzados
4. Implementar sistema de notificaciones
5. Agregar funcionalidad de exportación de datos

## Soporte

Si encuentras algún problema durante la migración, verifica:
1. Que la base de datos `col` esté creada correctamente
2. Que el archivo `config/database.php` tenga la configuración correcta
3. Que todos los archivos PHP estén en el directorio correcto
4. Que el servidor web tenga permisos de lectura/escritura
