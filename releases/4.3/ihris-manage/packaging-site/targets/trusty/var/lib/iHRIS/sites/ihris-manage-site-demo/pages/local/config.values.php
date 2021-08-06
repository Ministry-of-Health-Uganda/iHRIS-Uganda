<?php
include_once("/etc/ihris/ihris-manage-site-demo.config-db.php");  #include deb_conf generated db access to get password
$i2ce_site_i2ce_path = "/var/lib/iHRIS/releases/4.2/i2ce";
$i2ce_site_dsn = "mysql://$dbuser:$dbpass@unix(/var/run/mysqld/mysqld.sock)/$dbname" ;
$i2ce_site_user_access_init = null;
$i2ce_site_module_config = "/var/lib/iHRIS/sites/ihris-manage-site-demo/iHRIS-Manage-Demo.xml";
