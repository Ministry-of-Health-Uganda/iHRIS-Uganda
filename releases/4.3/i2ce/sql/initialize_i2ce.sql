
CREATE TABLE  IF NOT EXISTS `config`  (
  `hash` char(32) NOT NULL,
  `path` text NOT NULL,
  `type` tinyint(4) NOT NULL,
  `value` text CHARACTER SET utf8 default NULL,
  `children` text default NULL,
  PRIMARY KEY  (`hash`),
  KEY `path` ( `path` ( 130 ) )
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


