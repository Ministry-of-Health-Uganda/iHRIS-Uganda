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
*/
/**
*  I2CE_CachedForm
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_CachedForm extends I2CE_Fuzzy{


    /**
     * turn off spam
     */
    public static $spam = true;

    /**
     * @var protected string $form The form we are caching
     */
    protected $form;

    /**
     * @var protected  string $database  the database name (unquoted)
     */ 
    protected $database;
    /**
     * @var protected  string $table_name  the table name for this form.
     */ 
    protected $table_name;
    /**
     * @var protected  string $short_table_name  the table name for this form without quotes and without the databse
     */ 
    protected $short_table_name;
    /**
     * @var protected  string $last_entry_database  the database name (unquoted) where last_entry is
     */ 
    protected $last_entry_database;


    /**
     * @var protected I2CE_Form $formObj   An instance of the form object
     */
    protected $formObj;

    /**
     * @var protected I2CE_FormStorage_Mechanism $formMech   An instance of the form storage mechansim for the form
     */
    protected $formMech;


    protected static $container_cache = array();
    protected function getContainer($form) {
        if (! array_key_exists($form,self::$container_cache)) {
            self::$container_cache[$form] = I2CE_FormFactory::instance()->createContainer($form);
        } else   if (self::$container_cache[$form] instanceof I2CE_Form) {
            self::$container_cache[$form]->resetDefaultValues();
        }
        return self::$container_cache[$form];
    }
    

    /**
     * The constructor
     * @param string $form  The form we wish to cash into a table
     */
    public function __construct($form) {
        $this->form = $form;
        $factory = I2CE_FormFactory::instance();
        if (!$factory->exists($form)) {
            $msg = "Trying to cache form $form, but the form does not exist";
            I2CE::raiseError($msg);
            throw new Exception($msg);
        }
        $this->formObj = $this->getContainer($this->form);
        if (!$this->formObj instanceof I2CE_Form) {
            $msg = "Cannot instantiate {$this->form}";
            I2CE::raiseError($msg);
            throw new Exception($msg);
        }
        $this->formMech = I2CE_FormStorage::getStorageMechanism($form);
        if (!$this->formMech instanceof I2CE_FormStorage_Mechanism) {
            $msg = "Cannot get storage mechansim for form $form";
            I2CE::raiseError($msg);
            throw new Exception($msg);
        }
        $this->short_table_name = $this->getCachedTableName($form,false);
        $this->table_name = $this->getCachedTableName($form,true);
        $this->tmp_table_name = $this->getCachedTableName($form,true , 'tmp_cached_form');
    }




    /**
     * Get the id's of the cached forms.
     */
    public function getIDs() {
        $ids = array();
        $qry = "SELECT id from {$this->table_name}";
        try {
            $results =$db->query($qry);
            while($result = $results->fetch() ) {
                $ids[] = $result->id();
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Cannot access database:\n$qry");
            return $ids;
        }
        return $ids;
    }

    /**
     * Get the name of the database that the cached tables are stored in.
     * @returns string The string may be empty meaning that we are using the database for the DB connection
     */
    public static function getCacheDatabase() {
        $db_name = '';
        I2CE::getConfig()->setIfIsSet($db_name,"/modules/CachedForms/database_options/database");        
        if ( !$db_name || $db_name == "" ) {
            $db_name = I2CE_PDO::details('dbname');
        }
        return $db_name;
    }

    /**
     * Get the name of the cached table for the specfiied form.
     * @param string $form
     * @param boolean $withDB defaults to true.  If true we return the table in the form `database_name`.`table_name`.  Otherwise
     * we return simplt table_name
     * @returns string
     */
    public static function getCachedTableName($form,$withDB = true , $table_prefix = '') {        
        $db_name = '';
        if ($withDB) {
            $db_name = self::getCacheDatabase();
            if (strlen($db_name) > 0) {
                $db_name = '`' . $db_name . '`.';
            }
        }
        if (!$table_prefix) {
            $table_prefix = 'hippo_';
            $DBConfig = I2CE::getConfig()->setIfIsSet($table_prefix,"/modules/CachedForms/database_options/table_prefix");        
        }
        if (strlen($table_prefix) > 0) {
            if ($table_prefix[strlen($table_prefix)-1] !== '_') {
                $table_prefix .= '_';
            }
        }     
        if ($withDB) {
            return            $db_name . '`' .  $table_prefix . $form  . '`';
        } else {
            return $table_prefix . $form;
        }
    }



    /**
     * Get the last time that this form was chached
     */
    public function getLastCachedTime() {
        $timeConfig = I2CE::getConfig()->traverse("/modules/CachedForms/times/generation");
        if (!$timeConfig instanceof I2CE_MagicDataNode) {
            return -1;
        }
        $timeConfig->volatile(true);
        $generation_time = -1;
        $timeConfig->setIfIsSet($generation_time,$this->form);
        return $generation_time;
    }

    /**
     *Get the time the cached form is considered stale
     * @returns int the number of seconds for this form to be considered stale.  if 0 it is considered to be always stale
     */
    public function getStaleTime() {
        $timeConfig = I2CE::getConfig()->modules->CachedForms->times;
        $stale_time  = 10; 
        $timeConfig->setIfIsSet($stale_time,"stale_time");
        if (is_integer($stale_time) ||  (is_string($stale_time) && ctype_digit($stale_time))) {
            if ($stale_time <= 0) {
                return 0;
            } 
        } else {
            $stale_time = 10;
        }
        //lookup storage mechanism stale time and override if necc.
        $t_stale_time = null;
        if ($timeConfig->setIfIsSet($t_stale_time,"stale_time_by_mechanism/" . I2CE_FormStorage::getStorage($this->form))) {
            if (is_integer($t_stale_time) ||  (is_string($t_stale_time) && ctype_digit($t_stale_time))) {
                if ( $t_stale_time > 0 ) {
                    $stale_time = $t_stale_time;
                } else {
                    return 0;
                }
            }
        }
        //lookup form stale time and override if  necc.
        $t_stale_time = null;
        if ($timeConfig->setIfIsSet($t_stale_time,"stale_time_by_form/{$this->form}")) {
            if (is_integer($t_stale_time) ||  (is_string($t_stale_time) && ctype_digit($t_stale_time))) {
                if ( $t_stale_time > 0 ) {
                    $stale_time = $t_stale_time;
                } else {
                    return 0;
                }
            }
        }
        $stale_time = $stale_time * 60; //convert from minutes
        return $stale_time;
    }
    
    /**
     * Checks to see if the cached table is stale
     * @returns boolean
     */
    public function isStale() {
        $cache_status = '';
        $config =   I2CE::getConfig()->modules->CachedForms;
        $config->status->volatile(true);
        $cache_status = $config->status->{$this->form};
        if ($cache_status == 'in_progress') {
            return false;
        }
        if (!$this->tableExists()) {
            return true;
        }
        $generation_time = $this->getLastCachedTime();
        if ( $generation_time <= 0) {
            return true;
        }
        if ($generation_time > time()) { //make sure there is no time sync. problem
            I2CE::raiseError("You have a time-sync problem");
            return true;
        }
        //set/lookup default stale time for forms 
        $stale_time = $this->getStaleTime();
        if ($stale_time <= 0) {  //always considered stale
            return true;
        }
        return  (($generation_time + $stale_time) < time()); 
    }

    /**
     * Drops the existing cached table from the database
     * @returns boolean
     */
    public function dropTable()  {
        $qry = "DROP TABLE IF EXISTS " . $this->table_name ;
        $db =  I2CE::PDO();

        try {
            $result = $db->exec($qry);
        } catch( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to drop " . $this->table_name );
            return false;
        }

        $timeConfig = I2CE::getConfig()->traverse("/modules/CachedForms/times/generation/{$this->form}",false,false);
        if ($timeConfig instanceof I2CE_MagicDataNode) {
            $timeConfig->erase();
        }
        return true;
    }

    /**
     * Check to see if the cached table for this table exists and has the the proper fields for its columns.  If it is invalud, it will
     * drop the table.
     * @return boolean
     */
    public function tableExists() {
        if ($this->database) {
            $qry = "SHOW TABLES FROM " . $this->database . " LIKE '" . $this->short_table_name . "'";
        } else {
            $qry = "SHOW TABLES  LIKE '" . $this->short_table_name . "'";
        }
        $db =  I2CE::PDO();
        try {
            $result = $db->query( $qry );
            if ( $result->rowCount() == 0 ) {
                return false;
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError($e, "Failed to show tables:\n$qry" );
            return false;
        }

        try {
            $qry = "SHOW COLUMNS FROM ". $this->table_name  ;
            $results =$db->query($qry);
            $factory = I2CE_FormFactory::instance();
            $field_defs = array();
            foreach ($this->formObj as $field=>$fieldObj) {            
                $field_defs[$field] = $fieldObj->getDBType();  //we really should be checking that the column types are correct.
            }
            $special = array();
            while ( $row = $results->fetch()) {
                $field = $row->field;
                if ($field == 'id' || $field=='parent' || $field =='last_modified' || $field =='created') {
                    $special[$field] = true;
                    continue;
                }
                $fieldObj = $this->formObj->getField($field);
                if (!$fieldObj instanceof I2CE_FormField) {
                    I2CE::raiseError("The form field, {$this->form}:$field, is present in the cached table but is not a valid I2CE_FormField");
                    $this->dropTable();
                    return false;
                }
                if (!$fieldObj->isInDB()) {
                    I2CE::raiseError("The form field, {$this->form}:$field, is present in the cached table but is not supposed to be saved to the cached table");
                    $this->dropTable();
                    return false;
                }
                if (!array_key_exists($field,$field_defs)) {
                    I2CE::raiseError("Field $field present in cached table but not the form");
                    $this->dropTable();
                    return false;
                }
                unset($field_defs[$field]);
            }
            unset( $results );
        } catch( PDOException $e ) {
            I2CE::pdoError($e,"Cannot access database:\n$qry");
            return false;
        }
        if (count($special) !== 4) {
            I2CE::raiseError("Could not find id, parent, created or last_modified");
            $this->dropTable();
            return false;
        }
        foreach ($field_defs as $field=>$def) {
            $fieldObj = $this->formObj->getField($field);
            if (!$fieldObj instanceof I2CE_FormField) {
                I2CE::raiseError("The form field, {$this->form}:$field,  is not a valid I2CE_FormField");
                return false;
            }
            if (!$fieldObj->isInDB()) {
                unset($field_defs[$field]);
            }
        }
        if (count($field_defs) > 0) {
            I2CE::raiseError("The fields  "  . implode(',',array_keys($field_defs)) . " are not present in the existing cached table");
            $this->dropTable();
            return false;
        }
        return true;
    }

    /**
     * Updates the cached table if it exists with a given record.
     * @param mixed $id The id of the form to be updated in the cache.
     * @param boolean $check_mod Also only update based on the last modified time for the entire form cache.  Defaults to false.
     * @return boolean
     */
    public function updateCachedTable( $id, $check_mod=false ) {
        if ( !$this->tableExists() ) { 
            // Only update existing tables, use generateCachedTable when it's not.
            return false;
        }
        if ($check_mod) {
            $mod_time = $this->getLastCachedTime();
        } else {
            $mod_time = 0;
        }
        $sel_fields = array('id','parent','last_modified','created');
        foreach ($this->formObj as $field=>$fieldObj) {
            if (!$fieldObj->isInDB()) {
                continue;
            }
            $sel_fields[] = $field;
        }

        $sub_select = $this->formMech->getSubSelectFieldsQuery($this->form,$sel_fields,$id,$mod_time );
        if ($sub_select) {
            if (!$this->fastPopulate($sub_select,$check_mod,$id)) {
                return false;
            }
        } else {
            if (!$this->slowPopulate($mod_time, $id)) {
                return false;
            }
        }
        return true;
    }


    /**
     * Generates the cached table for the form
     * @param boolean $check_stale.  Defaults to true.  If false, it skips the staleness check
     * @param boolean $check_dirty.  Defaults to true.  If false, it skips the dirtiness check
     */
    public function generateCachedTable($check_stale = true, $check_dirty = true) {
        $time = time();        
        if ($check_stale && !$this->isStale()) {
            //I2CE::raiseMessage("Skipping cached table for {$this->form} as it is not stale" );
            return true;
        }
        if ($this->tableExists()){ 
            if ( $check_dirty && !I2CE_Module_CachedForms::formIsDirty($this->form)) {
                //I2CE::raiseMessage("Skipping cached table for {$this->form} as it is not dirty" );
                return true;            
            }
            I2CE::raiseMessage("The form {$this->form} is dirty" );
        }
        I2CE::getConfig()->modules->CachedForms->status->{$this->form} = 'in_progress';
        if (!$this->tableExists() && !$this->createCacheTable()) {
            return false;
        }
                
        if (I2CE_CachedForm::$spam) {
            I2CE::raiseError("Populating fields for {$this->form}");
        }

        if ($check_dirty) {
            $mod_time = $this->getLastCachedTime();
        } else {
            $mod_time = 0;
        }
        $fields = array('id','parent','last_modified','created');
        foreach ($this->formObj as $field=>$fieldObj) {
            if (!$fieldObj->isInDB()) {
                continue;
            }
            $fields[] = $field;
        }


        $sub_select = $this->formMech->getSubSelectFieldsQuery($this->form,$fields,null,$mod_time );
        if ($sub_select) {
            if (!$this->fastPopulate($sub_select,$mod_time)) {
                return false;
            }
        } else {
            if (!$this->slowPopulate($mod_time)) {
                return false;
            }
        }
        I2CE::raiseMessage("Populated {$this->form} at $time:" . date('r',$time) .  "\n");
        I2CE::getConfig()->modules->CachedForms->times->generation->{$this->form} = $time;
        I2CE::getConfig()->modules->CachedForms->status->{$this->form} = 'done';
        I2CE_Module_CachedForms::markFormClean($this->form,$time); //will mark the form as clean if the dirty timestamp  does not exceed time
        return true;
    }


    protected static $prepared_stash = array();
    /**
     * Method used to populate the cache table in case the form storage mechanism is  DB like
     * @param integer $mod_time.  Defaults to  -1.  If non-negative, it is a unix timestamp and we retrieve records modified at or after the given time
     * @param mixed $id The id to limit for the caching
     */
    protected function fastPopulate($subselect,$check_mod = -1, $id =null) {
        $fields = array();
        $default_values =array();
        foreach ($this->formObj as $field=>$fieldObj) {
            if (!$fieldObj->isInDB()) {
                continue;
            }
            $fields[] = $field;
            $default_values[] = $fieldObj->getDBValue();
        }
        $insert_fields = $fields;
        $insert_fields[] ='id';
        $insert_fields[] = 'parent';
        $update_fields = array();
        foreach ($insert_fields as &$field) {
            $field = '`' . $field . '`';
            $update_fields[] = "$field=values($field)";
        }
        $update_fields[] = '`last_modified`=values(`last_modified`)';


        $sel_fields = $fields;
        $sel_fields[]  = 'created';
        $sel_fields[]  = 'last_modified';

        $fields = array_diff($fields, array('parent','id'));
        $d_fields  = array();
        foreach ($fields as $field) {
            $d_fields[] = "IFNULL(`$field`, ?) as `$field`";
        }
        $select = "SELECT  concat('{$this->form}|',id) as id, parent , IFNULL(`last_modified`,'1900-01-01 00:00:00') as `last_modified`, IFNULL(`created`,'0000-00-00 00:00:00') as `created`, " . implode(',',$d_fields) . " FROM ($subselect) AS cached_table";
        $db = I2CE::PDO();
        if ($id ) {
            $select .= " WHERE cached_table.id = " . $db->quote($id);
        }
        $insertQry = 'INSERT INTO ' . $this->table_name . '(id,parent,`last_modified`,created,`' . implode( '`,`', $fields) . '`) ('  .  $select    .") ON DUPLICATE KEY UPDATE " . implode(',',$update_fields) ;
        $hash = md5($insertQry);
        if (array_key_exists($hash, self::$prepared_stash)) {
            $prp = self::$prepared_stash[$hash];
        } else {
            try {
                self::$prepared_stash[$hash] =  ($prp = $db->prepare( $insertQry ));
            } catch ( PDOException $e ) {
                try { 
                    $db = I2CE_PDO::reconnect();
                    I2CE::raiseError("Successful reconnect");
                    self::$prepared_stash[$hash] =  ($prp = $db->prepare( $insertQry ));
                } catch ( PDOException $e ) {
                    I2CE::pdoError($e, "Failed to prepare queries for fastPopulate");
                    return false;
                }
            }
        }
        if (I2CE_CachedForm::$spam) {
            I2CE::raiseError("Fast Populate Query:$insertQry\n");
        }
        try {
            $res = $prp->execute($default_values);
            if (I2CE_CachedForm::$spam) {
                I2CE::raiseError("Updated $res records for {$this->form}".($id!==null?" ($id)":""));
            }
            $prp->closeCursor();
            if ( $id || $check_mod ) {
                unset( $prp );
                unset( self::$prepared_stash[$hash] );
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Could not populate cache for {$this->form}:");
            return false;
        }

       return true;
    }


    /**
     * Method used to populate the cache table in case the form storage mechanism is not DB like
     * @param integer $mod_time.  Defaults to  -1.  If non-negative, it is a unix timestamp and we retrieve records modified at or after the given time
     * @param mixed $id The id to limit for the caching
     */
    protected function slowPopulate($mod_time = -1, $id = null) {
        $fields = array();
        $default_values = array();
        foreach ($this->formObj as $field=>$fieldObj) {
            if (!$fieldObj->isInDB()) {
                continue;
            }
            $fields[] = $field;
            $default_values[$field] = $fieldObj->getDBValue();
        }
        $default_values['last_modified'] = '1900-01-01 00:00:00';
        $default_values['created'] = '0000-00-00 00:00:00';
        $default_values['parent'] = '|';
        $insert_fields = $fields;
        $insert_fields[] = 'last_modified';
        $insert_fields[] = 'created';
        $fields[] = 'last_modified';
        $fields[] = 'created';
        $fields[] = 'parent';
        $insert_fields[] = 'parent';
        $insert_fields[] = "id";
        $update_fields = array();
        foreach ($insert_fields as &$field) {
            $field = '`' . $field . '`';
            $update_fields[] = "$field=values($field)";
        }
        unset($field);
        $insertQry = 'INSERT INTO ' . $this->table_name .   " (" . implode(',',$insert_fields) . " ) "
            ." VALUES (" . implode(',',array_fill(0,count($insert_fields),'?')) . ")" 
            ." ON DUPLICATE KEY UPDATE " . implode(',',$update_fields) ;
        if (I2CE_CachedForm::$spam) {
            I2CE::raiseError("Slow populate:\n$insertQry");
        }
        $db = I2CE::PDO();
        try {
            $prep = $db->prepare($insertQry);
            $storage = I2CE_FormStorage::getStorageMechanism($this->form);
            if (!$storage instanceof I2CE_FormStorage_Mechanism) {
                I2CE::raiseError("form $form does not have valid form storage mechanism");
                return false;
            }
            if (I2CE_CachedForm::$spam) {
                I2CE::raiseError("Mod Time =$mod_time");
            }
            if ( $id !== null ) {
                $ids = array( $id );
            } else {
                $ids = $storage->getRecords($this->form,$mod_time);
            }
            if (I2CE_CachedForm::$spam) {
                I2CE::raiseError(implode("\n", $ids));
            }
            foreach ($ids as $id) {
                $data = $storage->lookupField($this->form,$id,$fields,false);
                if (!is_array($data)) {
                    continue;
                }
                $t_data = array();
                foreach ($fields as $field) {
                    if (array_key_exists($field,$data)) {
                        $t_data[] = $data[$field];
                    } else {
                        $t_data[] = $default_values[$field];
                    }
                }
                $t_data[] = $this->form . "|" . $id;
                try {
                    $res = $prep->execute($t_data);
                    $prep->closeCursor();
                } catch ( PDOException $e ) {
                    I2CE::pdoError( $e, "Error insert into cache table:" );
                    return false;
                }            
            }
            unset( $prep );
            I2CE_FormStorage::releaseStorage($this->form);
            if (I2CE_CachedForm::$spam) {
                I2CE::raiseError("Populated " . count($ids)  . " entries for {$this->form}");
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error setting up form in the database:" );
            return false;
        }
        return true;

    }




    /**
     * setup of the queries used to create and populate the cached table
     * @returns boolean.  True on success, false on error
     */
    protected function createCacheTable() {
        I2CE::raiseError("(Re)Creating cached table schema for {$this->form} as it either does not exist or is out of date");
        $timeConfig = I2CE::getConfig()->traverse("/modules/CachedForms/times/generation/{$this->form}",false,false);
        if ($timeConfig instanceof I2CE_MagicDataNode) {
            $timeConfig->erase();
        }
        $createFields = array('`id` varchar(255) NOT NULL', 'PRIMARY KEY  (`id`)'); 
        $createFields[] = '`parent` varchar(255) default "|" ';
        $createFields[] = 'INDEX (`parent`)';
        $createFields[] = '`last_modified` datetime default \'1900-01-01 00:00:00\'' ;
        $createFields[] = '`created` datetime default \'0000-00-00 00:00:00\'' ;
        $createFields[] = 'INDEX (`last_modified`)';
        $field_defs = array();
        foreach ($this->formObj as $field=>$fieldObj) {
            if (!$fieldObj->isInDB()) {
                continue;
            }
            $createFields[] = '`' . $field . '` ' . $fieldObj->getDBType();  
            if ($fieldObj instanceof I2CE_FormField_MAPPED) {
                $createFields[] = 'INDEX (`' . $field . '`) ';
            }
        }
        $createQuery =  "CREATE TABLE  " . $this->table_name ." ( "  .  implode(',', $createFields) . ")  ENGINE=InnoDB DEFAULT CHARSET=utf8  DEFAULT COLLATE=utf8_bin";        
        I2CE::raiseError("Creating table for {$this->form} as:\n$createQuery");
        try {
            $db =  I2CE::PDO();
            $result =$db->exec($createQuery);
        } catch (PDOException $e ) {
            I2CE::pdoError($e,"Cannot create cached table for {$this->form}:\n$createQuery");
            return false;
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
