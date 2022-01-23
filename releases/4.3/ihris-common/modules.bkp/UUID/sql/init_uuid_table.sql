



CREATE TABLE IF NOT EXISTS `uuid_map`  (
  `uuid` char(36)  NOT NULL,
  `formid` varchar(255) NULL,
  `last_modified` timestamp  NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`uuid`),
  KEY `formid` (`formid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


