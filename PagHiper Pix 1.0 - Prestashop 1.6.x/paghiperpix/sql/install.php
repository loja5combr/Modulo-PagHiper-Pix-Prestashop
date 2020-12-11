<?php
$sql = array();

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."paghiperpix` (
	`id` INT(15) NOT NULL AUTO_INCREMENT,
	`id_cliente` INT(15) NOT NULL DEFAULT '0',
    `fiscal` VARCHAR(25) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
)
ENGINE=InnoDB;";

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."paghiperpix_pedidos` (
	`id` INT(15) NOT NULL AUTO_INCREMENT,
	`id_pedido` INT(15) NOT NULL DEFAULT '0',
	`id_carrinho` INT(15) NOT NULL DEFAULT '0',
	`transacao` VARCHAR(50) NOT NULL DEFAULT '',
	`pagador` VARCHAR(50) NOT NULL DEFAULT '',
	`status` VARCHAR(20) NOT NULL DEFAULT '',
	`valor` FLOAT(10,2) NOT NULL DEFAULT '0.00',
	`emv` TEXT NOT NULL,
	`qrcode` TEXT NOT NULL,
	`url` TEXT NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."loja5_logs` (
	`id` INT(15) NOT NULL AUTO_INCREMENT,
	`modulo` CHAR(50) NULL DEFAULT NULL,
	`url` VARCHAR(255) NULL DEFAULT NULL,
	`metodo` CHAR(10) NULL DEFAULT NULL,
	`http_status` CHAR(10) NULL DEFAULT NULL,
	`enviado` TEXT NULL,
	`recebido` TEXT NULL,
	`data` TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB;";

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
