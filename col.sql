-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2025 at 02:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `col`
--

-- --------------------------------------------------------

--
-- Table structure for table `alertas`
--

CREATE TABLE `alertas` (
  `id_alerta` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `estado` varchar(20) NOT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `asistencias`
--

CREATE TABLE `asistencias` (
  `id_asistencia` int(11) NOT NULL,
  `id_inscripcion` int(11) NOT NULL,
  `id_curso_materia` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` varchar(20) NOT NULL COMMENT 'Presente, Ausente, Retraso',
  `creado_en` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asistencias`
--

INSERT INTO `asistencias` (`id_asistencia`, `id_inscripcion`, `id_curso_materia`, `fecha`, `estado`, `creado_en`, `creado_por`, `modificado_en`, `modificado_por`) VALUES
(109, 2, 1, '2025-10-07', 'justificado', '2025-10-07 10:01:00', 2, '2025-10-07 18:31:00', NULL),
(112, 1, 1, '2025-10-08', 'tardanza', '2025-10-08 10:30:00', 2, '2025-10-08 12:29:00', NULL),
(114, 7, 1, '2025-10-07', 'ausente', '2025-10-07 12:00:00', 2, '2025-10-07 18:00:00', NULL),
(115, 3, 1, '2025-10-07', 'presente', '2025-10-07 12:00:00', 2, '2025-10-07 15:00:00', NULL),
(116, 7, 1, '2025-10-08', 'presente', '2025-10-08 11:00:00', 2, '2025-10-08 19:00:00', NULL),
(117, 8, 1, '2025-10-07', 'tardanza', '2025-10-07 20:00:00', 2, '2025-10-07 22:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `calificaciones`
--

CREATE TABLE `calificaciones` (
  `id_calificacion` int(11) NOT NULL,
  `id_inscripcion` int(11) NOT NULL,
  `id_curso_materia` int(11) NOT NULL,
  `periodo` varchar(20) NOT NULL,
  `nota` decimal(5,2) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `creado_en` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calificaciones`
--

INSERT INTO `calificaciones` (`id_calificacion`, `id_inscripcion`, `id_curso_materia`, `periodo`, `nota`, `fecha_registro`, `creado_en`, `creado_por`, `modificado_en`, `modificado_por`) VALUES
(6, 1, 2, 'Q2', 99.90, '2025-10-07 18:18:27', '2025-10-04 11:58:03', NULL, '2025-10-07 18:18:27', NULL),
(8, 2, 2, 'Q4', 100.00, '2025-10-07 18:18:40', '2025-10-04 11:58:03', NULL, '2025-10-07 18:18:40', NULL),
(9, 3, 3, 'Q1', 51.00, '2025-10-07 18:18:34', '2025-10-04 11:58:03', NULL, '2025-10-07 18:18:34', NULL),
(82, 7, 1, 'Q1', 56.00, '2025-10-07 18:19:49', '2025-10-07 18:19:49', NULL, '2025-10-07 18:19:49', NULL),
(83, 8, 1, 'Q1', 55.00, '2025-10-07 19:46:49', '2025-10-07 19:46:49', NULL, '2025-10-07 19:46:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cursos`
--

CREATE TABLE `cursos` (
  `id_curso` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `nivel` varchar(50) NOT NULL,
  `gestion` int(11) NOT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cursos`
--

INSERT INTO `cursos` (`id_curso`, `nombre`, `nivel`, `gestion`, `creado_en`, `creado_por`, `modificado_en`, `modificado_por`) VALUES
(1, 'Sexto de Secundaria', 'Secundaria', 2024, '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `curso_materia`
--

CREATE TABLE `curso_materia` (
  `id_curso_materia` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `id_docente` int(11) NOT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `curso_materia`
--

INSERT INTO `curso_materia` (`id_curso_materia`, `id_curso`, `id_materia`, `id_docente`, `creado_en`, `creado_por`, `modificado_en`, `modificado_por`) VALUES
(1, 1, 1, 1, '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL),
(2, 1, 2, 1, '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL),
(3, 1, 3, 1, '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL),
(4, 1, 4, 1, '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `docentes`
--

CREATE TABLE `docentes` (
  `id_docente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `docentes`
--

INSERT INTO `docentes` (`id_docente`, `id_usuario`, `especialidad`, `creado_en`, `creado_por`, `modificado_en`, `modificado_por`) VALUES
(1, 2, 'Matemáticas', '2025-10-04 11:22:08', NULL, '2025-10-04 11:22:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id_estudiante` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` varchar(10) NOT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `estudiantes`
--

INSERT INTO `estudiantes` (`id_estudiante`, `id_usuario`, `codigo`, `fecha_nacimiento`, `genero`, `creado_en`, `creado_por`, `modificado_en`, `modificado_por`) VALUES
(1, 4, 'EST001', '2005-03-15', 'F', '2025-10-04 11:22:08', NULL, '2025-10-04 11:22:08', NULL),
(2, 5, 'EST002', '2005-07-22', 'M', '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL),
(3, 6, 'EST003', '2005-11-08', 'F', '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL),
(9, 15, 'EST004', '2002-01-10', 'M', '2025-10-07 18:19:49', NULL, '2025-10-07 18:19:49', NULL),
(10, 16, 'EST005', '2000-10-10', 'M', '2025-10-07 19:46:49', NULL, '2025-10-07 19:46:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id_inscripcion` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `fecha_inscripcion` date NOT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inscripciones`
--

INSERT INTO `inscripciones` (`id_inscripcion`, `id_estudiante`, `id_curso`, `fecha_inscripcion`, `creado_en`, `creado_por`, `modificado_en`, `modificado_por`) VALUES
(1, 1, 1, '2025-10-04', '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL),
(2, 2, 1, '2025-10-04', '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL),
(3, 3, 1, '2025-10-04', '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL),
(7, 9, 1, '2025-10-07', '2025-10-07 18:19:49', NULL, '2025-10-07 18:19:49', NULL),
(8, 10, 1, '2025-10-07', '2025-10-07 19:46:49', NULL, '2025-10-07 19:46:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `materias`
--

CREATE TABLE `materias` (
  `id_materia` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materias`
--

INSERT INTO `materias` (`id_materia`, `nombre`, `descripcion`, `creado_en`, `creado_por`, `modificado_en`, `modificado_por`) VALUES
(1, 'Matemáticas', 'Álgebra, geometría y cálculo', '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL),
(2, 'Lenguaje', 'Gramática, literatura y redacción', '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL),
(3, 'Ciencias Naturales', 'Biología, química y física', '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL),
(4, 'Historia', 'Historia universal y nacional', '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `padres`
--

CREATE TABLE `padres` (
  `id_padre` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `padres`
--

INSERT INTO `padres` (`id_padre`, `id_usuario`, `telefono`, `creado_en`, `creado_por`, `modificado_en`, `modificado_por`) VALUES
(1, 3, '555-1234', '2025-10-04 11:22:08', NULL, '2025-10-04 11:22:08', NULL),
(4, 13, '555888000', '2025-10-07 17:25:18', NULL, '2025-10-07 17:42:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `relacion_padre_estudiante`
--

CREATE TABLE `relacion_padre_estudiante` (
  `id_relacion` int(11) NOT NULL,
  `id_padre` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `relacion_padre_estudiante`
--

INSERT INTO `relacion_padre_estudiante` (`id_relacion`, `id_padre`, `id_estudiante`, `creado_en`, `creado_por`, `modificado_en`, `modificado_por`) VALUES
(2, 1, 2, '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL),
(4, 4, 3, '2025-10-07 17:25:45', NULL, '2025-10-07 17:25:45', NULL),
(5, 4, 9, '2025-10-07 19:22:04', NULL, '2025-10-07 19:22:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reportes`
--

CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `contenido` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `creado_en` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(50) NOT NULL COMMENT 'admin, docente, estudiante, padre',
  `creado_en` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `email`, `password`, `rol`, `creado_en`, `creado_por`, `modificado_en`, `modificado_por`) VALUES
(1, 'Angel', 'Smith', 'admin@doriasmith.edu', '$2y$10$rAcKoqvgMukN9S1gb0zXKuPcacgv.EzeQvyznoZkndApJdDpW6vT6', 'admin', '2025-10-04 11:22:08', NULL, '2025-10-07 18:00:44', NULL),
(2, 'Laura', 'González', 'maria.gonzalez@doriasmith.edu', '$2y$10$phoR659VFA8ymot5FTIuSuV01T6FyHhAqO00vbYF.GxWYgRihBEWa', 'docente', '2025-10-04 11:22:08', NULL, '2025-10-07 18:01:48', NULL),
(3, 'Carlo', 'Rodríguez', 'carlos.rodriguez@email.com', '$2y$10$Rmqw5L6WbkkfXkPMZ77qE.w4jalbQr0YsmajyxBThuyFWceoySkYa', 'padre', '2025-10-04 11:22:08', NULL, '2025-10-07 17:43:17', NULL),
(4, 'Ana', 'Martínez', 'ana.martinez@estudiante.edu', '$2y$10$o./nmLZ68Ket74r4n7t36uPIS2gKDgUNAkHJb50Frhf15tinVAadK', 'estudiante', '2025-10-04 11:22:08', NULL, '2025-10-04 11:22:08', NULL),
(5, 'Sergio', 'Fernández', 'luis.fernandez@estudiante.edu', '$2y$10$fLkDoe9wuiYG5/IVOpq2P.TL3KRU8MHUWyv6.3S2FdshM5CT5eFwq', 'estudiante', '2025-10-04 11:22:09', NULL, '2025-10-07 19:44:15', NULL),
(6, 'Sofia', 'López', 'sofia.lopez@estudiante.edu', '$2y$10$p.yXHtJePvQvlxl.2TLUbukIyuLwGPNcWnv.mbkNTfFdBVIzKOjru', 'estudiante', '2025-10-04 11:22:09', NULL, '2025-10-04 11:22:09', NULL),
(13, 'Limbert', 'Ponce', 'lgomez@mail.com', '$2y$10$Dfgj8N3DEWqr2GT4Z2brR.NU4is9u4gIjvDjGrgEKfyu8epdWeenq', 'padre', '2025-10-07 17:25:18', NULL, '2025-10-07 18:01:33', NULL),
(15, 'Adan', 'Romero', 'romero@mail.com', '$2y$10$TLCKCKf4PIrS1IA/k7ExDOuTZ7oH9AvvOU6vzjJnyR6KrugQxtVAu', 'estudiante', '2025-10-07 18:19:49', NULL, '2025-10-07 18:19:49', NULL),
(16, 'Juan', 'Rodriguez', 'rodriguez@mail.com', '$2y$10$/eOp9it8SGkHgbVSv.icU.dZ.K8yKq9EDJRdzdCK7K2O2CvYGXRz6', 'estudiante', '2025-10-07 19:46:49', NULL, '2025-10-07 19:46:49', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alertas`
--
ALTER TABLE `alertas`
  ADD PRIMARY KEY (`id_alerta`),
  ADD KEY `idx_alertas_estudiante_fecha` (`id_estudiante`,`fecha`);

--
-- Indexes for table `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD KEY `id_curso_materia` (`id_curso_materia`),
  ADD KEY `idx_asistencias_inscripcion_fecha` (`id_inscripcion`,`fecha`);

--
-- Indexes for table `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD PRIMARY KEY (`id_calificacion`),
  ADD KEY `id_curso_materia` (`id_curso_materia`),
  ADD KEY `idx_calificaciones_inscripcion` (`id_inscripcion`);

--
-- Indexes for table `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id_curso`);

--
-- Indexes for table `curso_materia`
--
ALTER TABLE `curso_materia`
  ADD PRIMARY KEY (`id_curso_materia`),
  ADD KEY `id_curso` (`id_curso`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `id_docente` (`id_docente`);

--
-- Indexes for table `docentes`
--
ALTER TABLE `docentes`
  ADD PRIMARY KEY (`id_docente`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id_estudiante`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_estudiantes_codigo` (`codigo`);

--
-- Indexes for table `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id_inscripcion`),
  ADD KEY `id_curso` (`id_curso`),
  ADD KEY `idx_inscripciones_estudiante_curso` (`id_estudiante`,`id_curso`);

--
-- Indexes for table `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id_materia`);

--
-- Indexes for table `padres`
--
ALTER TABLE `padres`
  ADD PRIMARY KEY (`id_padre`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `relacion_padre_estudiante`
--
ALTER TABLE `relacion_padre_estudiante`
  ADD PRIMARY KEY (`id_relacion`),
  ADD KEY `id_padre` (`id_padre`),
  ADD KEY `id_estudiante` (`id_estudiante`);

--
-- Indexes for table `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `id_estudiante` (`id_estudiante`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_usuarios_email` (`email`),
  ADD KEY `idx_usuarios_rol` (`rol`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alertas`
--
ALTER TABLE `alertas`
  MODIFY `id_alerta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `calificaciones`
--
ALTER TABLE `calificaciones`
  MODIFY `id_calificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id_curso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `curso_materia`
--
ALTER TABLE `curso_materia`
  MODIFY `id_curso_materia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `docentes`
--
ALTER TABLE `docentes`
  MODIFY `id_docente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id_estudiante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id_inscripcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `materias`
--
ALTER TABLE `materias`
  MODIFY `id_materia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `padres`
--
ALTER TABLE `padres`
  MODIFY `id_padre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `relacion_padre_estudiante`
--
ALTER TABLE `relacion_padre_estudiante`
  MODIFY `id_relacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alertas`
--
ALTER TABLE `alertas`
  ADD CONSTRAINT `alertas_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`) ON DELETE CASCADE;

--
-- Constraints for table `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`id_inscripcion`) REFERENCES `inscripciones` (`id_inscripcion`) ON DELETE CASCADE,
  ADD CONSTRAINT `asistencias_ibfk_2` FOREIGN KEY (`id_curso_materia`) REFERENCES `curso_materia` (`id_curso_materia`) ON DELETE CASCADE;

--
-- Constraints for table `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`id_inscripcion`) REFERENCES `inscripciones` (`id_inscripcion`) ON DELETE CASCADE,
  ADD CONSTRAINT `calificaciones_ibfk_2` FOREIGN KEY (`id_curso_materia`) REFERENCES `curso_materia` (`id_curso_materia`) ON DELETE CASCADE;

--
-- Constraints for table `curso_materia`
--
ALTER TABLE `curso_materia`
  ADD CONSTRAINT `curso_materia_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE,
  ADD CONSTRAINT `curso_materia_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE,
  ADD CONSTRAINT `curso_materia_ibfk_3` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON DELETE CASCADE;

--
-- Constraints for table `docentes`
--
ALTER TABLE `docentes`
  ADD CONSTRAINT `docentes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Constraints for table `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Constraints for table `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscripciones_ibfk_2` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE;

--
-- Constraints for table `padres`
--
ALTER TABLE `padres`
  ADD CONSTRAINT `padres_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Constraints for table `relacion_padre_estudiante`
--
ALTER TABLE `relacion_padre_estudiante`
  ADD CONSTRAINT `relacion_padre_estudiante_ibfk_1` FOREIGN KEY (`id_padre`) REFERENCES `padres` (`id_padre`) ON DELETE CASCADE,
  ADD CONSTRAINT `relacion_padre_estudiante_ibfk_2` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`) ON DELETE CASCADE;

--
-- Constraints for table `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
