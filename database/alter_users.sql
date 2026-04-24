-- Hacer opcionales los campos dni_nie y fecha_nacimiento
-- para permitir registro simplificado desde la web
ALTER TABLE `users` MODIFY `dni_nie` varchar(20) DEFAULT NULL;
ALTER TABLE `users` MODIFY `fecha_nacimiento` date DEFAULT NULL;

-- Eliminar la restricción UNIQUE de dni_nie para permitir NULLs
ALTER TABLE `users` DROP INDEX `dni_nie`;
