CREATE TABLE IF NOT EXISTS `usuarios` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`nombre` VARCHAR(255) NOT NULL,
	`apellido` VARCHAR(255) NOT NULL,
	`foto_perfil` VARCHAR(255),
	`correo` VARCHAR(50) NOT NULL UNIQUE,
	`password` BLOB NOT NULL,
	`fecha_registro` DATETIME,
	`codigo` VARCHAR(6),
	`cantidad` INT NOT NULL,
	`precio` FLOAT NOT NULL,
	PRIMARY KEY (`id`)
);