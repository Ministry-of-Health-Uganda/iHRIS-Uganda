<?php
/**   
 * @copyright © 2007, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */
 

/**
 * I2CE_Module_Core
 * @package I2CE
 * @todo Better Documentation
 */
class I2CE_Module_Core extends I2CE_Module{

    public static function getHooks() {
        return array(
            'locales_changed'=>'updateLocales'
            );
    }


    public function updateLocales($args) {
        $fileSearch = I2CE::getFileSearch();
        if ($fileSearch instanceof I2CE_FileSearch_Caching) {
            $fileSearch->clearCache();
        }        
    }

    
    /**
     * Method called when the module is enabled for the first time.
     * @param boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        $config_protocol = strtolower(I2CE::getRuntimeVariable('I2CE_CONFIG_PROTOCOL', 'config_alt' ));
        $old_config = null;
        switch($config_protocol) {
        case 'mongodb':
            $old_config =I2CE::getConfig()->getAsArray();
            if (! $this->createMongoDBConfig(false)) {
                return false;
            }
            break;
        default:
            if (! $this->createAltConfig(false)) { 
                return false;
            }
        }

        //Re-Add The Db As A Magic Data Storage Mechanism And Dump Everyting already stored  To The Db
        // The db_name stuff doesn't seem to exist anymore so I'm commenting it all out.  It's
        // not being re-set anywhere anyway. -lad
        //$db_name = 'XXX'; 
        
        //$old_config->setIfIsSet($db_name,"database/DB");
        return $this->setupMagicDataStorage($old_config);
    }

    /**
     *Setup the magic data storage
     * @param array $old_config_values.  If non-null set the new storage to have these magic data values
     * @returns boolean.  True on success
     */
    function setupMagicDataStorage($old_config_values = null) {
        I2CE::raiseError("Setting up magic data in I2CE Core module");
        $new_config = I2CE::setupMagicData(false,true);
        if (!$new_config instanceof I2CE_MagicData) {
            I2CE::raiseError("Could not setup magic data storage");
            return false;
        }
        if (is_array($old_config_values)) {
            // If setting from the old values it will already be stored
            // in the temporary storage caches so those need to be cleared
            // or things won't get saved in the new permanent storage so clear
            // them all out first.
            $new_config->clearCache();
            $new_config->setValue($old_config_values);
        }
        I2CE::setConfig($new_config);        
        I2CE_ModuleFactory::instance()->setStorage();
        return true;
    }


    /**
     * Upgrades the modules
     * @param string $old_vers
     * @param string $new_vers
     * @returns boolean
     */
    public function upgrade($old_vers,$new_vers) {
        I2CE::raiseError("upgrade $old_vers -- $new_vers");
        if (I2CE_Validate::checkVersion($old_vers,'<','4.1.5.1')) {
            if (! $this->updateConfigAltToLong()) {
                return false;
            }
        }
        if (I2CE_Validate::checkVersion($old_vers,'<','3.0.1')) {
            if (! $this->updateConfigStatus()) {
                return false;
            }
        }
        if (I2CE_Validate::checkVersion($old_vers,'<','3.1')) {
            if (! $this->dropOldCaches()) {
                return false;
            }
            if (! $this->updateToInnoDB()) {
                return false;
            }
        }
        if (I2CE_Validate::checkVersion($old_vers,'<','3.2')) {
            if (! $this->updateConfigProcessed()) {
                return false;
            }
            
        }
        if (I2CE_Validate::checkVersion($old_vers,'>=','3.2') && I2CE_Validate::checkVersion($old_vers,'<','3.2.5')) {
            I2CE::raiseError("Clearing magic data cache for upgrade of I2CE");
            if ( !I2CE::getConfig()->clearCache()) {
                return false;
            }
        }
        if (I2CE_Validate::checkVersion($old_vers,'<','4.0.2')) {
            if (! $this->addConfigPathIndex()) {
                return false;
            }            
        }
        if (I2CE_Validate::checkVersion($old_vers,'<','4.0.3.6')) {
            if (! $this->createAltConfig(true)) {
                return false;
            }            
        }
        $config_protocol = strtolower(I2CE::getRuntimeVariable('I2CE_CONFIG_PROTOCOL', 'config_alt' ));
        if ($config_protocol == 'mongodb' && I2CE_Validate::checkVersion($old_vers,'<','4.1.4.0')) {
            if (! $this->createMongoDBConfig(true)) {
                return false;
            }            
        }

        return true;
    }



    protected function  createMongoDBConfig($update_from_config_alt) {
        require_once("I2CE_MagicDataStorageMongoDB.php");
        if (! ($db_name = I2CE::dbName() )) {
            I2CE::raiseError("No database to connect to MongoDB");
            return false;
        }
        if (! ($db_password = I2CE_PDO::details( 'pass' ) )) {
            I2CE::raiseError("No password to connect to MongoDB");
            return false;
        }
        if ( !($db_user = I2CE_PDO::details( 'user' ) )) {
            I2CE::raiseError("No user to connect to MongoDB");
            return false;
        }
        $conn_string ="mongodb://localhost";
        try {
            $m = new Mongo($conn_string);
        }
        catch (Exception $e) {
            I2CE::raiseError("Could not connect to mongodb using: " . $conn_string);
            return false;
        }
        if ( !is_array ($dbs = $m->listDBs()) || !array_key_exists('databases',$dbs) || !is_array($dbs['databases']) ) {
            I2CE::raiseError("MongoDB: $db_name needs to be created");
            //this is done automatically when we select below
        } else {
            $found = false;
            foreach ($dbs['databases'] as $info) {
                if (!is_array($info) || !array_key_exists('name',$info) || !$info['name'] = $db_name) {
                    continue;
                }
                $found = true;
            }
            if (!$found) {
                I2CE::raiseError("MongoDB: The database $db_name needs to be created:" . print_r($dbs,true));
            }
        }

        $new_coll = false;
        if (! ($mdb = $m->selectDB($db_name)) instanceof MongoDB) {
            I2CE::raiseError("Cannont connect to mongo database $db_name");
            return false;
        }    
        if ( !is_array($mcolls = $mdb->listCollections()) || count($mcolls) == 0 || !in_array($db_name . ".config",$mcolls)) {
            I2CE::raiseError("MongoDB: collection $db_name.config needs to be created");            
            //this is done automatically when we select below
            $new_coll = true;
        }        
        if (! ($mcoll = $mdb->selectCollection('config')) instanceof MongoCollection) {
            I2CE::raiseError("Cannot connect to config collection of mongo database $db_name");
            return false;
        }                            
        if (! (I2CE_MagicDataStorageMongoDB::setupIndices($mcoll))) {
            I2CE::raiseError("Could not setup indices");
            return false;
        }

        $db = I2CE::PDO();
        $qry = 'SHOW TABLES LIKE "config_alt"';
        try {
            $result = $db->query($qry);
            if ($result->rowCount() < 1) {
                $update_from_config_alt = false;
            }         
            unset( $result );
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Cannot access database");
            return false;
        }
        if ($update_from_config_alt && $new_coll ) {
            $qry_nodes = "SELECT IF(  LENGTH(p.parent) > 1, concat(p.parent,'/',p.name),IF (LENGTH(p.name) > 1, CONCAT('/',p.name), '')) as path, p.value as value, p.type as type, 
GROUP_CONCAT(c.name SEPARATOR '/') AS children
from config_alt AS p LEFT JOIN
 config_alt c 
ON c.parent  =  IF( 
 LENGTH(p.parent) > 1,
  concat(p.parent,'/',p.name),
 IF( LENGTH(p.name) > 1, CONCAT('/',p.name), CONCAT('/',p.name))
) 
GROUP BY path";
            try {
                $res = $db->query($qry_nodes);
                I2CE::raiseError("Populating mongodb from config_alt table");
                while (($row = $res->fetch(PDO::FETCH_ASSOC))) {
                    if ($row['type'] == '0') {
                        $children = preg_split('/\\//',$row['children'],-1,PREG_SPLIT_NO_EMPTY);
                    } else {
                        $children = null;
                    }
                    $data = array( 
                            I2CE_MagicDataStorageMongoDB::PATH  => $row['path'],
                            I2CE_MagicDataStorageMongoDB::TYPE => $row['type'],
                            I2CE_MagicDataStorageMongoDB::VALUE => $row['value'],
                            I2CE_MagicDataStorageMongoDB::CHILDREN => $children
                            );                
                    if (! is_array( $r = $mcoll->insert($data, array("safe" => true))) || (array_key_exists('err',$r) && $r['err'])) {
                        I2CE::raiseError("Could not inset new node:" . print_r($r,true));
                        return false;
                    }
                }
                unset( $res );
            } catch( PDOException $e ) {
                I2CE::pdoError($e, "Could not query existing config_alt");
                return false;
            }

        }
        if (!$this->setupMagicDataStorage()) {
            I2CE::raiseError("Could not setup magic data storage");
        }
        return true;
            
    }


    protected function updateConfigAltToLong() {
        $qry = "alter table config_alt modify COLUMN value longtext;";
        $db = I2CE::PDO();
        try {
            $db->exec($qry);
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Cannot alter value table of config_alt table to long");
            return false;
        } 
        return true;
    }

    protected function  createAltConfig($update_from_config) {
        $hash = '2245023265ae4cf87d02c8b6ba991139'; //the hash of the root node
        //first test to see if the alt config table is there for some reason.
        $db = I2CE::PDO();
        $qry = 'SHOW TABLES LIKE "config_alt"';

        try {
            $result = $db->query($qry);
            if ($result->rowCount() > 0) {
                I2CE::raiseError("Alt Config table has already been created");
                $qry = "SELECT count(*) as count FROM `config_alt` WHERE `path_hash` = '$hash'";
                try {
                    $result = $db->query($qry);
                    if ( ($row = $result->fetch())) {
                        if ($row->count == 1) {
                            unset( $result );
                            I2CE::raiseError("Alt config has been created and seeded");
                            return true;
                        }
                    }
                    unset( $result );
                } catch( PDOException $e ) {
                    I2CE::pdoError($e,"Cannot access config_alt");
                    return false;
                }
            }        
            unset( $result );
        } catch( PDOException $e ) {
            I2CE::pdoError($e,"Cannot access database");
            return false;
        }
        if ($update_from_config) {
            //now see if the tmp alt config table is there and if so drop it.
            $qry = 'SHOW TABLES LIKE "config_alt_tmp"';
            try {
                $result = $db->query($qry);
                if ($result->rowCount() > 0) {
                    I2CE::raiseError("Temporary Alt Config table has already been created -- Dropping it");
                    $qry = 'DROP TABLE  `config_alt_tmp`';
                    try {
                        $db->exec($qry);
                    } catch( PDOException $e ) {
                        I2CE::pdoError( $e, "Cannot drop temporary alternative config table");
                        return false;
                    }               
                }
                unset( $result );
            } catch( PDOException $e ) {
                I2CE::pdoError($e,"Cannot access database");
                return false;
            }
            $create = 'config_alt_tmp';
        } else {
            $create = 'config_alt';
        }
        $qrs = array(
            "CREATE TABLE IF NOT EXISTS  `$create`  (
  `path_hash` char(32) NOT NULL,
  `parent` text NOT NULL,
  `name` text  NOT NULL,
  `type` tinyint(4) NOT NULL,
  `value` longtext CHARACTER SET utf8 default NULL,
  PRIMARY KEY  (`path_hash`),
  KEY  (`parent` (130) ),
  KEY `path` ( `parent` ( 130 ), `name` (30) )
) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            "INSERT INTO `$create` (`path_hash`,`parent`,`name`,`type`,`value`) VALUE ('$hash','','',0,NULL)" //seed the table with the root node
            );
        foreach ($qrs as $qry) {
            try {
                $db->exec($qry);
            } catch( PDOException $e ) {
                I2CE::pdoError($e,"Cannot execute  query:\n$qry");
                I2CE::raiseError("Could not initialize temporary alternate config table");
                return false;
            }        
        }
        if (!$update_from_config) {
            return true;
        } 
        $qry = 'SHOW TABLES LIKE "config"';
        try { 
            $result = $db->query($qry);
            if ($result->rowCount() == 0) {
                I2CE::raiseError("Attempting to update to alternative config table from existing config table, but the config table does not exist");
                unset( $result );
                return false;
            }
            unset( $result );
        } catch( PDOException $e ) {
            I2CE::pdoError($e,"Cannot access database");
            return false;
        }
        I2CE::longExecution();
        I2CE::raiseError("Inserting entries into config_alt table from config table");
        $qry = "REPLACE  INTO config_alt_tmp(path_hash,parent,name,type,value) SELECT 
            hash AS path_hash,
            IF ( locate('/',path) > 0 , CONCAT('/',SUBSTR(path,8,length(path) -7 - locate('/', reverse(path)))) , IF (locate(':',path), '/','' ) ) as parent,
            IF ( locate('/',path) > 0 , SUBSTR(path,length(path) - locate('/', reverse(path)) +2  ), IF (locate(':',path),SUBSTR(path,locate(':',path)+1),'') ) as name
            ,type,value
from config WHERE ( locate(':',path) > 0 OR path = 'config' )";
        try {
            $db->exec($qry);
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e,"Cannot update alternative config table from existing table");
            return false;
        }        
        $qry = "RENAME TABLE `config_alt_tmp` TO `config_alt`";
        try {
            $db->exec($qry);
        } catch( PDOException $e ) {
            I2CE::pdoError($e,"Cannot rename temporary alternative config table");
            return false;
        }      
        return $this->setupMagicDataStorage();
    }


    /** 
     * Post Update this module if necessar
     * @param string $old_vers
     * @param string $new_vers
     * @return boolean
     */
    public function post_update($old_vers,$new_vers) {
        //had do to this, as well as in the install, b/c there was a bug where i2ce version was not being saved into magic data.
        // if (I2CE_Validate::checkVersion($old_vers,'<','4.0.2')  ) {
        //     if (! $this->addConfigPathIndex()) {
        //         return false;
        //     }            
        // }
        return true;
    }

    protected function addConfigPathIndex() {        
//             AVG(length(path))     count(distinct(substr(path,1,120))) / count(distinct(path)) * 100
//             72.7333     97.9721
//             AVG(length(path))     count(distinct(substr(path,1,130))) / count(distinct(path)) * 100
//             72.7333     99.9036
//             AVG(length(path))     count(distinct(substr(path,1,140))) / count(distinct(path)) * 100
//            72.7333     99.9825 
        $db = I2CE::PDO();
        $check_qry = "SELECT * FROM information_schema.statistics WHERE table_schema = '" . addslashes(I2CE::dbName()) . "'"
            . " AND table_name = 'config' and index_name = 'path' ";
        try {
            $result = $db->query($check_qry);
            if ($result->rowCount() > 0) {
                //the index has already been created.
                I2CE::raiseError("Index on config.path has already been created");
                return true;
            }
            unset( $result );
        } catch( PDOException $e ) {
            I2CE::pdoError($e,"Cannot execute  query:\n$check_qry");
            return false;
        }
        //create the index
        ini_set('max_execution_time',60000);
        I2CE::raiseError("Creating index 'path' on config.path");
        $qry = "ALTER TABLE config ADD INDEX `path` ( `path` ( 130 ) )";
        try {
            $db->exec($qry);
        } catch( PDOException $e ) {
            I2CE::pdoError($e,"Cannot execute  query:\n$qry");
            return false;
        }
        return true;
    }

    protected function updateConfigProcessed() {
        foreach (I2CE::getConfig()->traverse("/config/status/processed") as $shortname=>$processed) {
            if (!$shortname) {
                continue;
            }
            if (is_scalar($processed) && $processed) {
                $vers = null;
                I2CE::getConfig()->setIfIsSet($vers,"/config/data/$shortname/version");
                if ($vers === null) {
                    continue;
                }
                I2CE::getConfig()->__set("/config/status/config_processed/$shortname",$vers);
            }
        }
        return true;
    }

    private static function _runStatementList($statements = null) {
        $db = I2CE::PDO();
        foreach($statements as $stmt) {
            try {
                $db->exec($stmt);
                I2CE::raiseError("Executed: $stmt");
            } catch( PDOException $e ) {
                I2CE::pdoError($e, "Problem executing statement");
                return false;
            }
        }
        return true;
    }


    protected function dropOldCaches() {
        $db = I2CE::PDO();
        I2CE::raiseError("Dropping old cache tables");
        try {
            $result = $db->query("SHOW TABLES LIKE 'cache_%'");
            $tables = $result->fetchAll( PDO::FETCH_COLUMN, 0 );
            unset( $result );
            $s = array();

            foreach($tables as $table) {$s[] = "DROP TABLE $table";}

            return self::_runStatementList($s);
        } catch( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to get cached table list" );
            return false;
        }
    }

    protected function updateToInnoDB() {
        $db = I2CE::PDO();
        I2CE::raiseError("Changing MyISAM tables to InnoDB and UTF-8");
        try {
            $result = $db->query("SHOW TABLES");
            $tables = $result->fetchAll( PDO::FETCH_COLUMN, 0 );
            unset( $result );
            $s = array();

            foreach($tables as $table) {$s[] = "ALTER TABLE $table ENGINE=InnoDB CHARSET=utf8";}
        
            return self::_runStatementList($s);
        } catch( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to get table list to alter" );
            return false;
        }
    }

    protected function updateConfigStatus() {
        $status = I2CE::getConfig()->traverse("config/status/processed",true,false);
        if (!$status instanceof I2CE_MagicDataNode) { 
            I2CE::raiseError("Cannot get magic data at /config/status/processed");
            return false;
        }
        $factory = I2CE_ModuleFactory::instance();
        $enabled = $factory->getEnabled();
        foreach ($enabled as $e) {
            $status->$e = 1;
        }
        return true;
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
