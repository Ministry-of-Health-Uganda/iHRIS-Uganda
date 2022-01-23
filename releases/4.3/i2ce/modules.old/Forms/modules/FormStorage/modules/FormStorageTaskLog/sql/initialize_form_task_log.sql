


CREATE TABLE IF NOT EXISTS `form_task_log`  (
  `form` varchar(255) collate utf8_bin NOT NULL,
  `field`   varchar(255) collate utf8_bin NOT NULL,
  `id`  varchar(255) collate utf8_bin NOT NULL,
  `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `who` varchar(255) collate utf8_bin NOT NULL,
  `change_type` tinyint(3) unsigned NOT NULL,
  `task` varchar(255) collate utf8_bin default NULL,
  `task_id` varchar(255) collate utf8_bin default NULL,
  `blob_value` longblob,
  `string_value` varchar(255) collate utf8_bin default NULL,
  `integer_value` int(11) default NULL,
  `text_value` text collate utf8_bin,
  `date_value` datetime default NULL,
  KEY `formid` (`form`,`id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



