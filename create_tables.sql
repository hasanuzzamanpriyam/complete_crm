CREATE TABLE IF NOT EXISTS `tbl_server_types` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `tbl_hosting_plans` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`id`)
);
