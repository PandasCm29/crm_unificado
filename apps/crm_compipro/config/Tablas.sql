CHARACTER SET 'utf8' COLLATE 'utf8_spanish_ci' NULL DEFAULT NULL ;
ALTER TABLE rgarcia_cmr_actual.clientes 
ADD COLUMN notion_page_id VARCHAR(36) NULL AFTER accionescliente,
ADD COLUMN last_sync_time DATETIME NULL AFTER notion_page_id,
ADD COLUMN sync_source VARCHAR(10) NULL AFTER last_sync_time;

ALTER TABLE rgarcia_cmr_actual.usuarios 
 ADD COLUMN notion_page_id VARCHAR(36) NULL AFTER correo,
 ADD COLUMN last_sync_time DATETIME NULL AFTER notion_page_id,
 ADD COLUMN sync_source VARCHAR(10) NULL AFTER last_sync_time;

ALTER TABLE `clientes` ADD `fecha_de_actualizacion` DATETIME NOT NULL DEFAULT 'CURRENT_TIMESTAMP' AFTER `sync_source`;
ALTER TABLE `clientes` ADD `creadopor` INT(11) NOT NULL AFTER `fecha_de_actualizacion`;
