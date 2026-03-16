





CREATE TABLE IF NOT EXISTS `entry`  (
  `record` int(10) unsigned NOT NULL,
  `form_field` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `who` int(10) unsigned NOT NULL,
  `change_type` tinyint(3) unsigned NOT NULL,
  `string_value` varchar(255) collate utf8_bin default NULL,
  `integer_value` int(11) default NULL,
  `text_value` text collate utf8_bin,
  `date_value` datetime default NULL,
  `blob_value` longblob,
  KEY `record` (`record`),
  KEY `date` (`date`),
  KEY `form_field` (`form_field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;




CREATE TABLE IF NOT EXISTS `field`  (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) collate utf8_bin NOT NULL,
  `type` varchar(16) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name_type` (`name`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE IF NOT EXISTS `field_sequence`  (
  `form_field` int(11) NOT NULL,
  `sequence` int(11) NOT NULL,
  PRIMARY KEY  (`form_field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `form`  (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) collate utf8_bin NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



CREATE TABLE  IF NOT EXISTS `form_field` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `form` int(10) unsigned NOT NULL,
  `field` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `form` (`form`,`field`)
) ENGINE=InnoDB AUTO_INCREMENT=255 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



CREATE TABLE IF NOT EXISTS `last_entry`  (
  `record` int(10) unsigned NOT NULL,
  `form_field` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `who` int(10) unsigned NOT NULL,
  `change_type` tinyint(3) unsigned NOT NULL,
  `string_value` varchar(255) collate utf8_bin default NULL,
  `integer_value` int(11) default NULL,
  `text_value` text collate utf8_bin,
  `date_value` datetime default NULL,
  `blob_value` longblob,
  PRIMARY KEY  (`record`,`form_field`),
  KEY `record` (`record`),
  KEY `form_field` (`form_field`),
  KEY `ff_string_value` (`form_field`,`string_value`),
  KEY `ff_integer_value` (`form_field`,`integer_value`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



CREATE TABLE IF NOT EXISTS `record`  (
  `id` int(10) unsigned NOT NULL auto_increment,
  `last_modified` datetime NOT NULL,
  `created` datetime,
  `form` int(10) unsigned NOT NULL,
  `parent_form` varchar(255)  default NULL,
  `parent_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  INDEX (`parent_form`,`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10367 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE IF NOT EXISTS `deleted_record`  (
  `id` int(10) unsigned NOT NULL auto_increment,
  `last_modified` datetime NOT NULL,
  `created` datetime,
  `form` int(10) unsigned NOT NULL,
  `parent_form` varchar(255)  default NULL,
  `parent_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  INDEX (`parent_form`,`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10367 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
