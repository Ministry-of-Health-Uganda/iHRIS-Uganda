CREATE TABLE IF NOT EXISTS `import_export`  (
  `record` int(10) unsigned NOT NULL,
  `site` varchar(36) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  PRIMARY KEY  (`record`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
