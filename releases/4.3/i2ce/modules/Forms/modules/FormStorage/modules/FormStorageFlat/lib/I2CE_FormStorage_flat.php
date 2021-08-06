<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
* it under the terms of the GNU General Public License as published by 
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
* @version v3.2.0
* @since v3.2.0
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_Flat
* 
* @access public
*/


class I2CE_FormStorage_Flat extends I2CE_FormStorage_DB {





    /**
     * Checks to see if this storage mechansim is writable
     * @returns boolean. false
     */
    public function isWritable() {
        return true;
    }

    /**
     * @protected array $saveStmts An array of prepared statements for saving indexed by the form name
     */
    protected $saveStmts = array();

    /**
     * @var protected $saveCols.  An array, indexed by form name, of arrays of pairs fieldnames=>column names
     */
    protected $saveCols = array();

    /**
     * Get the columsn which can save for the specidied form
     * @param I2CE_Form $form
     * @returns array keys are the field names and values are the column names
     */
    protected function getSaveColumns($form) {
        $formName = $form->getName();
        if (array_key_exists($formName,$this->saveCols)) {
            return $this->saveCols[$formName];
        }
        $table = $this->getTable($formName);
        if (!$table) {
            I2CE::raiseError("No table specified for $form");
            $this->saveCols[$formName] = array();
            return array();
        }        
        $writable = false;
        $options = $this->getStorageOptions($formName);
        $options->setIfIsSet($writable,'writable');
        if (!$writable) {
            I2CE::raiseError("Trying to save non-writable form");
            $this->saveCols[$formName] = array();
            return array();
        }
        if (!$options instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Invalid storage options for $formName");
            $this->saveCols[$formName] = array();
            return array();
        }
        $form_prepended = true;
        $options->setIfIsSet($form_prepended,'id/form_prepended');
        if ($form_prepended) {
            $this->saveCols[$formName] = array();
            return array();
        }
        $id_col  = 'id';
        if (!$options->setIfIsSet($id_col,'id/col') || !$id_col) {
            if ($options->is_scalar('id/function') && $options->id->function) {
                //the id is being read in as a function.  and the col is not overwritten
                //so cannot save to a function value
                $this->saveCols[$formName] = array();
                return array();
            }
        }
        try {
            $res = $this->db->query("SHOW FULL COLUMNS FROM $table WHERE Field='$id_col'");            
            $rows = $res->fetchAll();
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to get id column" );
            $rows = array();
        }
        if(!is_array($rows) || count($rows) != 1) { //the id column was not there
            $this->saveCols[$formName] = array();
            return array();
        }
        $cols['id']= $id_col;
        $parent_col = false;
        $parent_enabled = true;
        $options->setIfIsSet($parent_enabled,"parent/enabled");
        if ($parent_enabled) {
            if (!$options->setIfIsSet($parent_col,'parent/col') || !$parent_col) {
                if ($options->is_scalar('parent/function') && $options->parent->function) {
                    //the parent is being read in as a function.  and the col is not overwritten
                    //so cannot save to a function value
                    //do nothing. can't save the parent id.
                } else {
                    $cols['parent']= $parent_col;
                }
            } else { 
                $cols['parent']= $parent_col;
            }
        }
        $fields = $form->getFieldNames();
        foreach ($fields as $field) {
            $field_col = $field;
            $field_enabled = true;
            $options->setIfIsSet($field_enabled,"fields/$field/enabled");
            if ($field_enabled) {
                if (!$options->setIfIsSet($field_col,"fields/$field/col") || !$field_col) {
                    if ($options->is_scalar("fields/$field/function") && $options->fields->$field->function) {
                        //the parent is being read in as a function.  and the col is not overwritten
                        //so cannot save to a function value
                        //do nothing. can't save the parent id.
                    } else {
                        $cols[$field]= $field_col;
                    }
                } else { 
                    $cols[$field]= $field_col;
                }
            }
            //now we need to make sure that the col is "atomic"
            try {
                $res = $this->db->query("SHOW FULL COLUMNS FROM $table WHERE Field='{$cols[$field]}'");            
                $rows = $res->fetchAll();
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to get field column" );
                $rows = array();
            }
            if(!is_array($rows) || count($rows) != 1) { //the column was not there
                unset($cols[$field]);
            }
            $row = current($rows);
            if ($row->null == 'NO' && !$row->default) {  //it is not allowed to be null and there is no default value set.  non-atomic
                unset($cols[$field]);
            }
        }
        $this->saveCols[$formName] = $cols;
        return $cols;
    }

    /**
     * Gets the table for the specified form
     * @param string $form
     * @returns string
     */
    protected function getTable($form) {
        $options = $this->getStorageOptions($form);
        if (!$options instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Invalid storage options for $form");
            return false;
        }
        $table = '';
        $options->setIfIsSet($table,'table');
        $table = trim($table);
        if (strlen($table) == 0) {
            if (array_key_exists('table_prefix', $this->global_options) 
                && is_string($this->global_options['table_prefix']) 
                && strlen(trim($this->global_options['table_prefix'])) > 0
                )  {
                $table_prefix = trim($this->global_options['table_prefix']);
            } else {
                $table_prefix  = 'hippo_';
            }
            $table = '`' . $table_prefix . $form . '`';
        }
        if (strlen($table) == 0) {
            I2CE::raiseError("No table specified for $form");
            return false;
        }
        return $table;
    }

    /**
     * Ensures that a row exists in the given tablet
     * @param I2CE_Form $form
     * @param string $col
     * @param mixed $parent_col.  If a string it is the parent col to save the parent id in
     */
    protected function ensureFormId($form,$col,$parent_col) {
        $table = $this->getTable($form->getName());
        if (!$table) {
            I2CE::raiseError("No table specified for $form");
            return '0';
        }        
        $id = $form->getId();
        if ($id == '0') { 
            $stmt = "INSERT INTO $table SET `$col` = '" . $form->getName() . "|$id'";
            if (is_string($parent_col)) {
                $stmt .= ", `{$parent_col}` = '" . $form->getParent() ."'";
            }
            try {
                $this->db->exec($stmt);
                $new_id = $this->db->lastInsertId();
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error inserting form " . $form->getName() . ": " );
                return '0'; 
            }
            $form->setId( $new_id );
            $form->setChangeType( I2CE_FormStorage_Mechanism::CHANGE_INITIAL );
            return $new_id;
        } else {
            $stmt = "INSERT IGNORE INTO $table SET `$col` = '$id'";
            if (is_string($parent_col)) {
                $stmt .= ", `$parent_col` = '" . $form->getParent() ."'";
            }
            try {
                $this->db->exec($stmt);
                return $id;
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error inserting form " . $form->getName() . ": " );
                return '0'; 
            }
        }
    }

    /**
     * Save a form object into entry tables.
     * @param I2CE_Form $form
     * @param I2CE_User $user
     * @param boolean $transact
     */
    public function save( $form, $user,$transact) {        
        if ( $user === null ) {
            I2CE::raiseError( "Invalid arguments passed to I2CE_Form::save. ");
            return false;
        }
        $options = $this->getStorageOptions($form->getName());
        if (!$options instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Invalid storage options for " . $form->getName());
            return false;
        }
        $cols = $this->getSaveColumns($form);
        //var_dump($cols); 
        if (count($cols) == 0) {
            I2CE::raiseError("No fields can be  saved");
            return true;
        }
        if ( $transact ) {
            try {
                $this->db->beginTransaction();
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to start transaction!" );
            }
        }        
        if (array_key_exists('parent',$cols)) {
            $parent_col = $cols['parent'];
        } else {
            $parent_col = false;
        }
        $formId = $this->ensureFormId($form, $cols['id'],$parent_col);
        if ($formId == '0') {
            I2CE::raiseError("Could not create a new row for the form $form");
            if ( $transact && $this->db->inTransaction() ) { 
                $this->db->rollback();
            }
            return false;
        }
        $do_check = false;
        foreach ($form as $field=>$fieldObj) {
            if (!array_key_exists($field,$cols)) {
                continue;
            }
            if ( !$fieldObj->isInDB() ) {
                continue;
            }
            if ( !$fieldObj->save( $do_check, $user ) ) {
                if ($transact && $this->db->inTransaction()) { 
                    $this->db->rollback();
                }
                return false;
            }
        }       
        if ( $transact && $this->db->inTransaction() ) {
            return $this->db->commit();
        }
        return true;

    }

    /**
     * Save the FormField to the database.
     * @param I2CE_FormField $fieldObj
     * @param boolean $do_check
     *        A flag to determine if a check should be made for the same value being saved.
     * @param I2CE_User $user
     *        The user saving this data. 
     * @returns boolean 
     */
    public function FF_save($fieldObj,$do_check,$user) {
        if ($form_field->getDBValue() != "" && !$fieldObj->isValid()) {
            I2CE::raiseError("no valid:" . $fieldObj->getDBValue() . "\n[" . $fieldObj->getInvalid() . ']');
            return true;
        }        
        $stmt = $this->getFieldSave($fieldObj);       
        if (!$stmt) {
            I2CE::raiseError("Trying to save invalid field " . $fieldObj->getName());
            return false;
        }
        try {
            $stmt->execute(array($fieldObj->getDBValue(), $fieldObj->getContainer()->getId()));
            return true;
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Could not save " . $fieldObj->getName() . " from form " . $fieldObj->getContainer()->getName());
            return false;
        }
    }

    /**
     * @var protected $fieldSaves.  Array indexed by form name of array, indexed by field name, of prepared save statements for a field
     */    
    protected $fieldSaves = array();

    /**
     * Get/prepare the prepared statement for the given field obj
     * @param I2CE_FormField $fieldObj
     * @returns mixed.  false om failure.  a PDOStatement object on success
     */
    protected function getFieldSave($fieldObj) {
        $formName = $fieldObj->getContainer()->getName();
        $fieldName = $fieldObj->getName();
        if (!array_key_exists($formName,$this->fieldSaves)) {
            $this->fieldSaves[$formName] = array();
        }
        if (!array_key_exists($fieldName,$this->fieldSaves[$formName])) {
            $cols = $this->getSaveColumns($fieldObj->getContainer());
            $table = $this->getTable($formName);
            if (!$table || !array_key_exists($fieldName,$cols)) {                                 
                $this->fieldSaves[$formName][$fieldName] = false;
                return false; 
            }            
            $stmt = "UPDATE $table SET `{$cols[$fieldName]}` = ? WHERE `{$cols['id']}` = ?";
            try {
                $prepStmt =  $this->db->prepare( $stmt );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error preparing save statemnt for " . $fieldObj->getName() . "\n" . $stmt );
                $prepStmt = false;
            }
            $this->fieldSaves[$formName][$fieldName] = $prepStmt;
        }        
        return $this->fieldSaves[$formName][$fieldName];
    }

    /**
     * Check if the given data is the data for a column 
     * @param I2CE_MagicDataNode $data
     * @returns boolean
     */
    protected function fieldIsColumn($data) {
        $col = '';
        $data->setIfIsSet($col,'col');
        $col = trim($col);
        return (strlen($col)>0);
    }


    /**
     * Get how the given data should be queried from the db
     * @param I2CE_MagicDataNode $data
     * @returns mixed.  fasle on failture. string on success
     */ 
    protected function getFieldData($data) { 
        $col = '';
        $data->setIfIsSet($col,'col');
        $col = trim($col);
        if (strlen($col)>0) {
            return $col;
        }
        $function = '';
        $data->setIfIsSet($function,'function');
        if (strlen($function)> 0) {
            return $function;
        }
        return false;
    }


    /**
     * @var protected array $formObjs.  Keys are form names, values are instanceof I2CE_Form
     */ 
    protected $formObjs = array();

    /**
     * Get a (cached) form object for the given form
     * @param string $form
     * @returns I2CE_Form or false on failure.
     */ 
    protected function getFormObj($form) {
        if (!array_key_exists($form,$this->formObjs) ) {
            $formObj = I2CE_FormFactory::instance()->createContainer($form);
            if (!$formObj instanceof I2CE_Form) {
                $formObj = false;
            }
            $this->formObjs[$form] = $formObj;
        }
        return $this->formObjs[$form];
    }


    /**
     * The constructor for the storage mechanism
     * @param string $name
     * @param array $global_options. Default to empty array. The array of options that are 
     * the same across all all forms which share a commone storage mechanisms
     */
    public function __construct($name, $global_options=array()) {
        parent::__construct($name,$global_options);
        $this->storage_options_cache  = array();
    }

    /**
     *  Construct a query (to be used as a sub-select) to view the fields of the given form.  It always will return the id of the form as well
     *  @param string $form
     *  @param mixed $fields.  Either a string, the field, or an array of string, the fields.
     *  @param mixed $id.  Defaults to null.  If non-null it is the id that we wish to limit to.
     *  @param boolean $parent. Defaults to false.  If true, we include the parent id as a referenced field
     *  If it is scalar and non-boolean, it is consider to be the ID of the parent, and then we get all forms with parent the given id.
     *  @param callback $field_refernece_callback.  A callback function whose first arguement is the form, the second arguements
     *  is the field and which returns the way the field value should be references as a field.  If the callback is null (the default) then
     *  the reference used is "$form+$field"
     *  @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *    time greater than or equal to $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.
     * @param mixed $user The user id of the user to limit the results to so it only returns results limited to that user.  This can be a single value or an array
     *  @returns string the query or false on failed.
     */
    public function getRequiredFieldsQuery($form,$fields, $id = null, $parent = false, $field_reference_callback = null, $mod_time = -1, $user=false) {
        if ($fields === null) {
            $fields = array();
        } 
        if (is_string($fields)) {
            $fields = array($fields);
        }
        if (!is_array($fields)) {
            I2CE::raiseError("Invalid fields");
            return false;
        }
        $formObj = $this->getFormObj($form);
        if (!$formObj instanceof I2CE_Form) {
            I2CE::raiseError("Could not instantiate form $form");
            return false;
        }
        if ( !($options = $this->getStorageOptions($form)) ) {            
            I2CE::raiseError("No valid multi_flat storage options for $form");
            return false;
        }     
        $table = $this->getTable($form);
        if (!$table) {
            I2CE::raiseError("No table specified for $form");
            return false;
        }
        $select_list = array();
        $id_ref = false;
        if ($field_reference_callback != null) {
            if ( !is_string($id_ref = call_user_func($field_reference_callback, $form,'id')) ) {
                I2CE::raiseError("Invalid field reference callback function");
                return false;
            }                
        } else {
            $id_ref = "`$form+id`";
        }
        if (!$options->is_parent('id') || ($id_qry = $this->getFieldData($options->id)) === false) {
            $id_qry = 'id'; //default to column 'id'
        }
        $form_prepended = true;
        $options->setIfIsSet($form_prepended,"id/form_prepended");
        if ($form_prepended) {
            $select_list[] = "SUBSTRING($id_qry," . (strlen($form) + 2)  .  ") AS $id_ref";
        } else {
            $select_list[] = "$id_qry AS $id_ref";
        }
        foreach ($formObj as $field=>$fieldObj) {
            if (!in_array($field,$fields)) {
                continue;
            }
            if (!$fieldObj->isInDB()) {
                continue;
            }
            $f_qry = false;
            if ($options->is_parent("fields/$field")) {
                $data = $options->traverse("fields/$field");
                if ($data->is_scalar('enabled') && !$data->enabled) {
                    continue; 
                }
                $f_qry = $this->getFieldData($data);
            }
            if ($f_qry === false) {
                $f_qry = "`$field`"; //default to the field name
            }
            $f_ref = false;
            if ($field_reference_callback != null) {
                if ( !is_string($f_ref = call_user_func($field_reference_callback, $form,$field)) ) {
                    I2CE::raiseError("Invalid field reference callback function");
                    return false;
                }                
            } else {
                $f_ref = "`$form+$field`";
            }
            $select_list[] = "$f_qry AS $f_ref";                
        }
        $wheres = array();
        if ( is_array( $mod_time ) && array_key_exists('mod_time', $mod_time)) {
            $mod_time = $mod_time['mod_time'];
        }
        $get_mod_time =  ((is_scalar($mod_time) && $mod_time >= 0) || in_array('last_modified',$fields));
        if ($get_mod_time) {
            if ($options->is_scalar('last_modified/enabled') && ! $options->last_modified->enabled) {
                $mod_qry = "NULL" ;
            } else if (  $options->is_scalar('last_modified/col') && $options->last_modified->col ) {
                 $mod_qry = $options->last_modified->col ;
            } else if (  $options->is_scalar('last_modified/function') && $options->last_modified->function ) {
                 $mod_qry = $options->last_modified->function ;
            } else {
                $mod_qry = " last_modified " ;
            }
            if ($field_reference_callback !== null) {
                if ( !is_string($mod_ref = call_user_func($field_reference_callback, $form,'last_modified'))) {
                    I2CE::raiseError("Invalid parent reference callback function:\nlast_modified --> $mod_ref");
                    return false;
                }
            } else {
                $mod_ref = '`' . $form . '+last_modified`';
            }        
            $select_list[] = "$mod_qry AS $mod_ref";
            if (is_scalar($mod_time) && $mod_time >= 0) {
                $wheres[] = "($mod_qry IS NULL OR $mod_qry > FROM_UNIXTIME(" .  $mod_time . "))";
            }
        }
        if ($parent !== false || in_array('parent', $fields)) {
            $p_qry = false;
            $p_ref = false;
            if ($field_reference_callback != null) {
                if ( !is_string($p_ref = call_user_func($field_reference_callback, $form,'parent')) ) {
                    I2CE::raiseError("Invalid field reference callback function");
                    return false;
                }                
            } else {
                $p_ref = "`$form+parent`";
            }
            if (!$options->is_scalar('parent/enabled') || $options->parent->enabled ) {
                if ($options->is_parent('parent')) {
                    $p_qry = $this->getFieldData($options->parent);
                }
                if ($p_qry === false) {
                    $p_qry = 'parent';
                }
            } else {
                $p_qry = '0';
            }
            $select_list[] = $p_qry . " AS $p_ref";
            if (!is_bool($parent) && is_scalar($parent)) {
                $wheres[] = " ( $p_qry = " . $this->db->quote($parent) . " ) ";
            }            
        }
        if (in_array('created', $fields)) {
            $c_qry = false;
            $c_ref = false;
            if ($field_reference_callback != null) {
                if ( !is_string($c_ref = call_user_func($field_reference_callback, $form,'created')) ) {
                    I2CE::raiseError("Invalid field reference callback function");
                    return false;
                }                
            } else {
                $c_ref = "`$form+created`";
            }
            if (!$options->is_scalar('created/enabled') || $options->created->enabled ) {
                if ($options->is_parent('created')) {
                    $c_qry = $this->getFieldData($options->created);
                }
                if ($c_qry === false) {
                    $c_qry = 'created';
                }
            } else {
                $c_qry = '0';
            }
            $select_list[] = $c_qry . " AS $c_ref";
        }


    
        //want either select id as `form+id`, name as `form+name` from table
        //or select id as `form+id`, name as `form+name` from (select * from table where id=5)
        //select  substr(my_id,1,4) as `form+id`, surname as `form+name` from (select * from table where id=5)
        //or with a function..
        //select id as `form+id`, substr(surname,1,3) as `form+name` from table
        //select  id as `form+id`, substr(surname,1,3) as `form+name` from (select * from table where id=5)
        //select  substr(my_id,1,4) as `form+id`, surname as `form+name` from table wher `form+id`
        $qry = 'SELECT ' . implode(',', $select_list) . " FROM $table";
        if (is_scalar($id)) {
            if ($form_prepended) {
                $wheres[] = " ($id_qry =" . $this->db->quote($form . '|' . $id) . ") ";
            } else {
                $wheres[] = " ($id_qry =" . $this->db->quote($id) . ") ";
            }
        }
        if (count($wheres) >  0 ) {
            $qry .= ' WHERE (' . implode("AND", $wheres) . ")";
        }
        return $qry;
        
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
