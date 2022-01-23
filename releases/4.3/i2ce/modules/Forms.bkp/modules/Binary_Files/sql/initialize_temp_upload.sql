
CREATE TABLE IF NOT EXISTS `temp_upload`  (
  `key` varchar(32) NOT NULL,
  `value` longblob,
  PRIMARY KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

