
CREATE TABLE IF NOT EXISTS `csd_cache`  (
  `record` varchar(255) collate utf8_bin NOT NULL,
  `relationship` varchar(255) collate utf8_bin NOT NULL,
  `transform` varchar(255) collate utf8_bin NOT NULL,
  `last_modified` datetime NOT NULL,
  `xml_entry` longblob,
  UNIQUE  KEY `unique` (`record`,`relationship`,`transform`),
  INDEX `last_modified_export` (`relationship`,`transform`,`last_modified`),
  INDEX `record_export` (`relationship`,`transform`,`record`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
