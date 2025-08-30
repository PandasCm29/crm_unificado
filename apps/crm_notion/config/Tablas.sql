-- CHARACTER SET 'utf8' COLLATE 'utf8_spanish_ci' NULL DEFAULT NULL ;

ALTER TABLE `clientes`
ADD COLUMN `notion_page_id` VARCHAR(36) NULL AFTER `accionescliente`,
ADD COLUMN `last_sync_time` DATETIME NULL AFTER `notion_page_id`,
ADD COLUMN `sync_source` VARCHAR(10) NULL AFTER `last_sync_time`,
ADD COLUMN `creadopor` INT(11) NOT NULL AFTER `sync_source`,
ADD COLUMN `fecha_de_actualizacion` DATETIME NULL AFTER `creadopor`,
ADD COLUMN `account` TEXT NULL AFTER `fecha_de_actualizacion`,
;

ALTER TABLE `usuarios`
 ADD COLUMN `notion_page_id` VARCHAR(36) NULL AFTER `correo`,
 ADD COLUMN `last_sync_time` DATETIME NULL AFTER `notion_page_id`,
 ADD COLUMN `sync_source` VARCHAR(10) NULL AFTER `last_sync_time`,
 ADD COLUMN `fecha_creacion` DATETIME NULL AFTER `sync_source`,
 ADD COLUMN `puesto` VARCHAR(45) NOT NULL AFTER `area`
;


INSERT INTO acciones (id_tabla, valor, categoria, accion)
VALUES (0, 8, 'detalle_origen', 'CAMPAÑA');

-- delete from `usuarios`  where idusuario > 513;
-- delete from `clientes`  where idcliente > 15196;
-- delete from `groupemail` where tabla = "clientes" and idtabla > 15196;

-- CREATE TABLE sync_metadata (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     entity_type VARCHAR(50) NOT NULL,  -- 'clientes', 'usuarios', etc.
--     entity_id INT NOT NULL,            -- ID del registro en la tabla original
--     sync_source VARCHAR(50),
--     notion_page_id VARCHAR(100),
--     last_sync_time DATETIME,
--     UNIQUE (entity_type, entity_id)
-- );
