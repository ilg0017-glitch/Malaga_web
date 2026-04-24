-- Migración: campos opcionales en users + tabla favoritos
-- Ejecutar en phpMyAdmin sobre la BD malaga_cf_db

-- 1. Hacer opcionales dni_nie y fecha_nacimiento para registro simplificado
ALTER TABLE `users` MODIFY `dni_nie` varchar(20) DEFAULT NULL;
ALTER TABLE `users` MODIFY `fecha_nacimiento` date DEFAULT NULL;

-- 2. Crear tabla de favoritos (usuarios <-> productos)
CREATE TABLE IF NOT EXISTS `user_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fav_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fav_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
