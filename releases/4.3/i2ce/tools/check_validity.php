#!/usr/bin/php
<?php

$path_to_i2ce_root = '..';


$path_to_i2ce_root = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR. $path_to_i2ce_root  ) . DIRECTORY_SEPARATOR;
require_once $path_to_i2ce_root . 'lib' .DIRECTORY_SEPARATOR . 'I2CE.php';
require_once $path_to_i2ce_root .  'lib'.DIRECTORY_SEPARATOR.'I2CE_Configurator.php';
require_once $path_to_i2ce_root .  'lib'.DIRECTORY_SEPARATOR.'I2CE_MagicData.php'; 
require_once $path_to_i2ce_root .  'lib'.DIRECTORY_SEPARATOR.'I2CE_FileSearch.php'; 


require_once ("Console/Getopt.php"); 
$cg = new Console_Getopt(); 
$args = $cg->readPHPArgv();
$dir = getcwd();
array_shift($args);

$config = I2CE_MagicData::instance( "check_validity" );
I2CE::setConfig($config);


foreach ($args as $file) {
    $file = realpath($file);
    echo "Checking the validity of $file\n";
    chdir($path_to_i2ce_root . 'lib');
    $config = I2CE_MagicData::instance( "config" );


    $configurator = new I2CE_Configurator($config);
    if ($configurator->processConfigFile(I2CE_FileSearch::absolut($file),true,true)) {
        echo "\tThe file $file is valid\n";
    }
    chdir($dir);
}




# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
