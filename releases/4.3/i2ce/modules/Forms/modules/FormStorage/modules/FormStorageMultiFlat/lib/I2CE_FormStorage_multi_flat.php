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
* @subpackage core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.1
* @since v4.0.1
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_multi_flat
* 
* @access public
*/


class I2CE_FormStorage_multi_flat extends I2CE_FormStorage_DB {

    /**
     * Checks to see if this storage mechansim is writable
     * @returns boolean. false
     */
    public function isWritable() {
        return false;
    }




    protected function fieldIsColumn($data) {
        $col = '';
        $data->setIfIsSet($col,'col');
        $col = trim($col);
        return (strlen($col)>0);
    }


    protected static $columnList = array();

    protected function hasColumn($col,$database, $table) {        
        if ($table[0] != '`') {
            $table = '`'  . $table . '`';
        }
        if ($database[0] != '`') {
            $database = '`'  . $database . '`';
        }
        $d_t = $database . '.' . $table;
        if (!array_key_exists($d_t,self::$columnList)) {
            self::$columnList[$d_t] = array();
            $db = I2CE::PDO();
            try {
                $res = $db->query("SHOW COLUMNS IN $table FROM $database");
                while ($row = $res->fetch()) {
                    if (!isset($row->field)) {
                        continue;
                    }
                    self::$columnList[$d_t][] = $row->field;
                }
                unset( $res->free() );
                return in_array($col,self::$columnList[$d_t]);
            } catch ( PDOException $e ) {
                I2CE::pdoError($e,"Could determine columns on $d_t");
                return false;
            }
        }
    }

    protected function getFieldData($data,$database = null, $table = null) {
        if (!$data instanceof I2CE_MagicDataNode) {
            return ' NULL ';
        }
        $col = '';
        if ($data->setIfIsSet($col,'col')) {
            $col = trim($col);
        }
        if (!$col) {
            //no column data was set.
            //check to see if a funciton is set.
            $function = '';
            $data->setIfIsSet($function,'function');
            if (strlen($function)> 0) {
                //function data was set, return it.
                return $function;
            }
            //no function data was set, so set the col data to the default column data.
            $col = $data->getName(); //the name of the magic data node is the name of the field
        }
        //no function was set.  We are using a column
        if (!is_string($col) || strlen($col) == 0) {
            //this shouldn't happen, jsut being safe.
            return ' NULL ';
        }
        if ($database && $table) {
            if (!$this->hasColumn($col,$database,$table)) {
                I2CE::raiseError("Warning, column $col not found in in $d_t.  Using NULL value");
                return ' NULL ';
            }
        }
        if ($col[0] != '`') {
            //addin back-tics if needed
            $col = '`' . $col . '`';
        }
        return $col;
    }

    /**
     * @var protected array sting.  Keys are componentities and values are the assoicated database
     */
    protected $databases;

    /**
     * @var protected array componentized_forms. The array of componentized forms
     */
    protected $componentized_forms;



    /**
     * The constructor for the storage mechanism
     * @param string $name
     * @param array $global_options. Default to empty array. The array of options that are 
     * the same across all all forms which share a commone storage mechanisms
     */
    public function __construct($name, $global_options=array()) {
        parent::__construct($name,$global_options);
        $this->storage_options_cache[]  = array();
        $this->componentized_forms = I2CE_FormStorage::getComponentizedForms();
        if (!array_key_exists('components',$this->global_options) || !is_array($this->global_options['components'])) {
            I2CE::raiseError("No components specifed for multi-flat storage");
            return false;
        }
        $this->databases = array();
        foreach ($this->global_options['components'] as $component=>$data) {
            if (!is_array($data) || !array_key_exists('database',$data) || !is_string($data['database']) || strlen($data['database']) < 1) {
                continue;
            }
            $this->databases[$component] = $data['database'];
        }
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
    public  function getRequiredFieldsQuery($form,$fields, $id=null, $parent = false, $field_reference_callback = null, $mod_time = -1,$user=false)  {
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
        if ( !($options = $this->getStorageOptions($form)) ) {            
            I2CE::raiseError("No valid multi_flat storage options for $form");
            return false;
        }
        if (!is_array($this->databases) || count($this->databases) == 0) {
            I2CE::raiseError("No databases specified");
            return false;
        }        
        $formObj = $this->getFormObj($form);
        if (!$formObj instanceof I2CE_Form) {
            I2CE::raiseError("Could not instantiate form $form");
            return false;
        }
        if (!in_array($form,$this->componentized_forms)) {
            I2CE::raiseError("Form $form is not specified as being componentized.  Valid componentized forms:" . print_r($this->componentized_forms,true));
            return false;
        }
        $table = '';
        $options->setIfIsSet($table,'table');
        $table = trim($table);
        if (strlen($table) == 0) {
            if (array_key_exists('table_prefix', $this->global_options) && is_string($this->global_options['table_prefix']) && strlen(trim($this->global_options['table_prefix'])) > 0) {
                
                $table_prefix = trim($this->global_options['table_prefix']);
            } else {
                $table_prefix  = 'hippo_';
            }
            $table =  $table_prefix . $form;
        }
        if (strlen($table) == 0) {
            I2CE::raiseError("No table specified for $form");
            return false;
        }
        $id_ref = false;
        if ($field_reference_callback != null) {
            if ( !is_string($id_ref = call_user_func($field_reference_callback, $form,'id')) ) {
                I2CE::raiseError("Invalid field reference callback function");
                return false;
            }                
        } else {
            $id_ref = "`$form+id`";
        }
        $id_qry = 'id';
        if ($options->is_parent('id')) {
            $id_qry = $this->getFieldData($options->id);
        }
        $form_prepended = true;
        $options->setIfIsSet($form_prepended,"id/form_prepended");
        $unions = array();
        $p_componentized = false;
        if (is_scalar($id)) {
            if ( ($pos = strrpos($id,'@')) === false) { //get the last @ sign
                I2CE::raiseError("No component specified in id: $id for form $form");
                return false;
            }
            $component = substr($id,$pos+1);
            $id_no_comp = substr($id,0,$pos);
            if ( strlen($component) == 0) {
                I2CE::raiseError("zero length component specified in id:$id");
                return false;
            }
            if (!array_key_exists($component,$this->databases)) {
                I2CE::raiseError("Component $component is not associated to a database in id $id for form $form" );
                return false;
            }
            $databases = array($component => $this->databases[$component]);
            $componentParentForms = array_intersect(I2CE_Form::getAllowedParentForms($form), $this->componentized_forms);        
        } else if ( !is_bool($parent) && is_scalar($parent)) {
            list($p_form,$p_id) = explode('|',$parent,2);
            if (strlen($p_form) == 0) {
                I2CE::raiseError("sNo parent form given in $parent");
                return false;
            }
            if (strlen($p_id) == 0) {
                I2CE::raiseError("No parent id given in $parent");
                return false;
            }
            if (in_array($p_form,$this->componentized_forms)) {
                $p_componentized = true;
                if ( ($pos = strrpos($p_id,'@')) === false) { //get the last @ sign
                    I2CE::raiseError("No component specified in parent id: $parent");
                    return false;
                }
                $component = substr($p_id,$pos+1);
                $p_id_no_comp = substr($p_id,0,$pos);
                if ( strlen($component) == 0) {
                    I2CE::raiseError("zero length component specified in parent id:$parent");
                    return false;
                }
                if (!array_key_exists($component,$this->databases)) {
                    I2CE::raiseError("Component $component is not associated to a database in parent id: $parent");
                    return false;
                }
                $databases = array($component => $this->databases[$component]);
                $componentParentForms = array($p_form);        
            } else {
                $componentParentForms = array_intersect(I2CE_Form::getAllowedParentForms($form), $this->componentized_forms);                        
            }
            
        } else{ 
            $databases= $this->databases;
            $componentParentForms = array_intersect(I2CE_Form::getAllowedParentForms($form), $this->componentized_forms);        
        }

        //now verify that the databases and associated table are indeed present:
        $db = I2CE::PDO();
        
        foreach ($databases as $component=>$database) {
            $check_qry = "SELECT null FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . addslashes($database) . "'"
                . " AND TABLE_NAME = '" . addslashes($table) . "'";
            try {
                $result = $db->query($check_qry);
                if ($result->rowCount() > 0) {
                    //the table exists.
                    unset( $result );
                    continue;                
                }
                unset( $result );
                unset($databases[$component]);
            } catch ( PDOException $e ) {
                I2CE::pdoError($e,"Cannot execute  query:\n$check_qry");
                return false;
            }
        }
        if (count($databases) == 0) {
            I2CE::raiseError("No databases defined for multi-flat formstorage on table $table");
            return false;
        }

        foreach ($databases as $component=>$database) {
            $select_list = array();
            if ($form_prepended) {
                $select_list[] =   "CONCAT( SUBSTRING(" . $id_qry . "," .  (strlen($form) + 2) . "),'@'," . $db->quote($component)  . ") AS $id_ref";
            } else {
                $select_list[] =   "CONCAT( " . $id_qry . ",'@'," . $db->quote($component)  . ") AS $id_ref";
            }
            foreach ($formObj as $field=>$fieldObj) {
                if (!in_array($field,$fields)) {
                    continue;
                }
                if (!$fieldObj->isInDB()) {
                    continue;
                }
                if ($options->is_parent("fields/$field")) {
                    $data = $options->traverse("fields/$field");
                    if ($data->is_scalar('enabled') && !$data->enabled) {
                        continue; 
                    }
                    $f_qry = $this->getFieldData($data,$database,$table);
                } else {
                    if (!$this->hasColumn($field,$database,$table)) {
                        $f_qry = ' NULL ';
                    } else {
                        $f_qry = "`$field`"; //default to the field name
                    }
                }
                if ($fieldObj instanceof I2CE_FormField_MAPPED) {
                    $comp_map_forms = array_intersect($fieldObj->getSelectableForms(),$this->componentized_forms);
                    $f_qry = $fieldObj->getSQLComponentization($f_qry,$comp_map_forms,$component);
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
            if ( in_array('created',$fields)) {
                if ($options->is_scalar('created/enabled') && ! $options->created->enabled) {
                    $crt_qry = "NULL" ;
                } else if (  $options->is_scalar('created/col') && $options->created->col ) {
                    if ($this->hasColumn($options->created->col,$database,$table)) {
                        $crt_qry = $options->created->col ;
                    } else {
                        $crt_qry = 'NULL';
                    }
                } else if (  $options->is_scalar('created/function') && $options->created->function ) {
                    $crt_qry = $options->created->function ;
                } else {
                    if ($this->hasColumn('created',$database,$table)) {
                        $crt_qry = " created " ;
                    }  else {
                        //perhaps we are using a hippo_XXX table which does not have a created column
                        $crt_qry = 'NULL';
                    }
                }
                if ($field_reference_callback !== null) {
                    if ( !is_string($crt_ref = call_user_func($field_reference_callback, $form,'created'))) {
                        I2CE::raiseError("Invalid parent reference callback function:\ncreated --> $crt_ref");
                        return false;
                    }
                } else {
                    $crt_ref = '`' . $form . '+created`';
                }        
                $select_list[] = "$crt_qry AS $crt_ref";

            }
            if ( is_array( $mod_time ) && array_key_exists('mod_time', $mod_time)) {
                $mod_time = $mod_time['mod_time'];
            }
            $get_mod_time =  ((is_scalar($mod_time) && $mod_time >= 0) || in_array('last_modified',$fields));
            if ($get_mod_time) {
                if ($options->is_scalar('last_modified/enabled') && ! $options->last_modified->enabled) {
                    $mod_qry = "NULL" ;
                } else if (  $options->is_scalar('last_modified/col') && $options->last_modified->col ) {
                    if ($this->hasColumn($options->last_modified->col,$database,$table)) {
                        $mod_qry = $options->last_modified->col ;
                    } else {
                        $mod_qry = 'NULL';
                    }
                } else if (  $options->is_scalar('last_modified/function') && $options->last_modified->function ) {
                    $mod_qry = $options->last_modified->function ;
                } else {
                    if ($this->hasColumn('last_modified',$database,$table)) {
                        $mod_qry = " last_modified " ;
                    }  else {
                        //perhaps we are using a hippo_XXX table which does not have a last_modified column
                        $mod_qry = 'NULL';
                    }
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

            if ($parent !== false|| in_array($parent,$fields)) {
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
                    if ($p_qry === false)  {
                        $p_qry = 'parent';
                    }
                } else {
                    $p_qry = '0';
                }                
                $select_list[] = I2CE_List::componentizeQuery($p_qry,$componentParentForms,$component). " AS $p_ref";
                if (!is_bool($parent) && is_scalar($parent)) {
                    if (!$p_componentized) {
                        $wheres[] = " ( " . I2CE_List::componentizeQuery($p_qry,$componentParentForms,$component) . " = " . $db->quote($parent) . " ) ";
                    } else {
                        $wheres[] = " ( $p_qry = " . $db->quote($p_form . '|' . $p_id_no_comp) . ") ";
                    }
                }            
            }
            
            //want either select id as `form+id`, name as `form+name` from table
            //or select id as `form+id`, name as `form+name` from (select * from table where id=5)
            //select  substr(my_id,1,4) as `form+id`, surname as `form+name` from (select * from table where id=5)
            //or with a function..
            //select id as `form+id`, substr(surname,1,3) as `form+name` from table
            //select  id as `form+id`, substr(surname,1,3) as `form+name` from (select * from table where id=5)
            //select  substr(my_id,1,4) as `form+id`, surname as `form+name` from table wher `form+id`
            $qry = 'SELECT ' . implode(',', $select_list) . " FROM `$database`.`$table`";
            if (is_scalar($id)) {
                if ($form_prepended) {
                    $wheres[] = " ($id_qry =" . $db->quote($form . '|' . $id_no_comp) . ") ";
                } else {
                    $wheres[] = " ($id_qry =" . $db->quote($id_no_comp) . ") ";
                }
            }
            if (count($wheres) >  0 ) {
                $qry .= ' WHERE (' . implode("AND", $wheres) . ")";
            }
            $unions[] = $qry;
        }
        if (count($databases) > 1) {
            foreach ($unions as &$union) {
                $union = '(' . $union . ')';
            }
            //I2CE::raiseError("QRY:" . implode('UNION' ,$unions));
            return  implode('UNION', $unions);
        } else {
            reset($unions);
            //I2CE::raiseError("QRY:" . current($unions));
            return current($unions);
        }
        
    }
    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
