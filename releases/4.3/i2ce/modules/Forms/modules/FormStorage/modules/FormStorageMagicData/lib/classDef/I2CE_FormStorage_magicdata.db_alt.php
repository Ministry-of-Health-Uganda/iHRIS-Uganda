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
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.0
* @since v4.0.0
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_magicdata
* 
* @access public
*/


class I2CE_FormStorage_magicdata extends I2CE_FormStorage_DB{
    
    

    /**
     * Construct this module class
     * @param string $name The name of this storage mechanism
     * @param array $options
     */
    public function __construct( $name, $options=array() ) {
        parent::__construct( $name, $options );
        $this->config = I2CE::getConfig()->traverse($this->getFormPath());
    }

    /**
     * Get the form path.
     * @param string $form.  Defaults to null.  The optional name of the form we want
     * @param string $id.  Defaults to null.  The oprional id of the form we want
     */
    protected function getFormPath($form=null,$id = null) {
        $path = "/I2CE/formsData/forms";
        if (!is_string($form) || strlen($form) == 0) {
            return $path;
        }
        $path .= '/' . $form;
        if (!$id) {
            return $path;
        }
        $path .= '/' . $id;
        return $path;
    }

    



    /**
     * Release any resourced held by this form storage mechanism for the indicated form
     * @param string $form
     */
    public function release($form) {
        parent::release($form);
        if (!is_string($form)) {
            return;
        }
        if (($config = I2CE::getConfig()->traverse($path = $this->getFormPath($form),false,false)) instanceof I2CE_MagicDataNode) {
            $config->unpopulate();
        }


    }

    /**
     *  Construct a query (to be used as a sub-select) to view the fields of the given form.  It always will return the id of the form as well
     *  @param string $form
     *  @param mixed $fields.  Either a string, the field, or an array of string, the fields.
     *  @param mixed $id.  Defaults to null.  If non-null it is the id that we wish to limit to.
     *  @param boolean $parent. Defaults to false.  If true, we include the parent id as a referenced field.  
     *  If it is scalar and non-boolean, it is consider to be the ID of the parent, and then we get all forms with parent the given id.
     *  @param callback $field_refernece_callback.  A callback function whose first arguement is the form, the second arguements
     *  is the field and which returns the way the field value should be references as a field.  If the callback is null (the default) then
     *  the reference used is "$form+$field"
     *  @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *    time greater than or equal to $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.
     * @param mixed $user The user id of the user to limit the results to so it only returns results limited to that user.  This can be a single value or an array
     *  @returns string the query or false on failed.
     */
    public  function getRequiredFieldsQuery($form,$fields, $id=null, $parent = false, $field_reference_callback = null, $mod_time = -1,$user=false) {
        // parent id  was given
        if (is_string($fields)) {
            $fields = array($fields);
        }
        if (!is_array($fields)) {
            $fields = array();
        }
        $joins = array();
        if ($field_reference_callback != null) {
            if ( !is_string($id_ref = call_user_func($field_reference_callback, $form,'id')) ) {
                I2CE::raiseError("Invalid field reference callback function");
                return false;
            }                
        } else {
            $id_ref = "`$form+id`";
        }            
        $selects = array("c.name as $id_ref");
        if ( is_array( $mod_time ) && array_key_exists('mod_time', $mod_time)) {
            $mod_time = $mod_time['mod_time'];
        }
        $get_mod_time =  true;//(is_scalar($mod_time) && $mod_time >= 0);
        $get_who = false;
        foreach ($fields as $field) {
            if ($field == 'id' || $field == 'parent') {
                continue;
            }
            if ( $field == 'last_modified') {
                $get_mod_time = true;
                continue;
            }
            if ( $field == 'who') {
                $get_who = true;
                continue;
            }
            if ($field_reference_callback !== null) {
                if ( !is_string($ref = call_user_func($field_reference_callback, $form,$field))) {
                    I2CE::raiseError("Invalid field reference callback function:\n$field --> $ref");
                    return false;
                }
            } else {
                $ref = '`' . $form . '+' . $field. '`';
            }
            $joins[] = "LEFT JOIN config_alt `joined_$field` ON `joined_$field`.parent= CONCAT(c.parent, '/' , c.name , '/fields')  AND `joined_$field`.name = '" . addslashes($field) . "'";
            $selects[] = "`joined_$field`.value as $ref";
        }
        if ($parent !== false || in_array('parent',$fields)) {
            $joins[] = "LEFT JOIN config_alt `joined_parent` ON `joined_parent`.parent = CONCAT(c.parent,'/',c.name) and `joined_parent`.name = 'parent'";
//            if ($parent === true) {
            if (true) {
                //no parent id is specified, but we want the parent id to be returned.
                if ($field_reference_callback !== null) {
                    if ( !is_string($p_ref = call_user_func($field_reference_callback, $form,'parent'))) {
                        I2CE::raiseError("Invalid parent reference callback function:\nparent --> $ref");
                        return false;
                    }
                } else {
                    $p_ref = '`' . $form . '+parent`';
                }        
                $selects[] = "`joined_parent`.value as $p_ref";
            }
        }
        $wheres =  array( "c.parent = '/I2CE/formsData/forms/" . addslashes($form) . "'"); 
        if ($id !== null) {
            //an id was given
            $wheres[] =     "c.name = '" .  addslashes($id) . "'";
        } else if ( $parent && !is_bool( $parent ) ) {
            //a parent is given
            $wheres[] = "`joined_parent`.value = '" . addslashes($parent)  ."'";
        } else {
            //no id or parent was given
            //don't need to do anything
        }
        if ($get_who ) {
            $joins[] = "LEFT JOIN config_alt who ON who.parent = CONCAT(c.parent ,'/',c.name) AND who.name = 'who' ";
            if ($field_reference_callback !== null) {
                if ( !is_string($ref = call_user_func($field_reference_callback, $form,'who'))) {
                    I2CE::raiseError("Invalid parent reference callback function:\nwho --> $ref");
                    return false;
                }
            } else {
                $ref = '`' . $form . '+who`';
            }        
            $selects[] = "who.value AS $ref";
        }
        if ($get_mod_time ) {
            $joins[] = "LEFT JOIN config_alt modified ON modified.parent = CONCAT(c.parent ,'/',c.name) AND modified.name = 'last_modified' ";
            if ($field_reference_callback !== null) {
                if ( !is_string($ref = call_user_func($field_reference_callback, $form,'last_modified'))) {
                    I2CE::raiseError("Invalid parent reference callback function:\nlast_modified --> $ref");
                    return false;
                }
            } else {
                $ref = '`' . $form . '+last_modified`';
            }        
            $selects[] = "modified.value AS $ref";
            if (is_scalar($mod_time) && $mod_time >= 0) {
                $wheres[] = "(modified.value IS NULL  OR STR_TO_DATE(modified.value,'%Y-%m-%d %H:%i:%s') > STR_TO_DATE('"  .  date('Y-m-d H:i:s' , $mod_time) . "','%Y-%m-%d %H:%i:%s'))";
            }
        }
        return  "SELECT " .implode(',',$selects) . " FROM config_alt c " . implode(' ', $joins) . ' WHERE (' . implode( ' AND ', $wheres) . ')';
    }


    /**
     * Populate the member variables of the object from the database.
     * @param I2CE_Form $form
     */
    public function populate( $form) {
        $fields = array();
        foreach ($form as $field =>$fieldObj) {
            if (!$fieldObj->isInDB()) {
                continue;
            }
            $fields[] = $field;
        }
        $fields[] = 'last_modified';
        $fields[] = 'created';
        $populateQry = $this->getRequiredFieldsQuery($form->getName(),$fields,$form->getId(),true);
        if (!$populateQry) {
            return false;
        }      
        $populateQry .= ' LIMIT 1';
        try {
            $result = I2CE_PDO::getRow($populateQry);
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error populating form " . $form->getName() );
            return false;
        }    
        $form_name = $form->getName();
        foreach( $fields as $field) {
            $fieldObj = $form->getField($field);            
            if (!$fieldObj instanceof I2CE_FormField) {
                continue;
            }
            $ref =   strtolower($form_name . '+' . $field)  ;
            if (isset($result->$ref)) {                
                $value = $result->$ref;
                if ($fieldObj->getTypeString() =='blob') {
                    $value = base64_decode($value);
                }
                $fieldObj->setFromDB($value);
            }
        }
        $ref = strtolower($form_name . '+parent'  );
        if (isset($result->$ref)) {
            $form->setParent($result->$ref);
        }
        return true;       
    }

    /**
     * Populate the history of entries for the form field if the storage module handles history.
     * @param I2CE_FormField $form_field
     * @return boolean
     */
    public function FF_populateHistory( $form_field ) {        
        $field = $form_field->getName();
        $form = $form_field->getContainer();
        if (!$form instanceof I2CE_Form) {
            return false;
        }        
        if ($field == 'parent') {
            $fieldQry = $this->getRequiredFieldsQuery($form->getName(),array('last_modified','who'),$form->getId(),true);
        } else {
            $fieldQry = $this->getRequiredFieldsQuery($form->getName(),array($field,'last_modified','who'),$form->getId());
        }
        try {
            $result = I2CE_PDO::getRow($fieldQry);
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error populating field $field of form " . $form->getName() );
            return false;
        }
        $ref =   $form->getName() . '+' . $field;
        $last_ref = $form->getName() . '+'  .'last_modified';
        $who_ref = $form->getName() . '+'  .'who';
        $date = null;
        $who = 99;
        if (isset($result->$last_ref)) {
            $date = I2CE_Date::fromDB($result->$last_ref);
        }
        if (!$date instanceof I2CE_Date) {
            $date = I2CE_Date::blank();
        }
        if (isset($result->$who_ref)) {
            $who = $result->$who_ref;
        }
        if (isset($result->$ref)) {
            $entry = new I2CE_Entry( $date, $who, 0,  $form_field->getFromDB( $result->$ref ));
            $form_field->addHistory( $entry );
        }
        return true;
    }
    

    /**********************
     *
     * Writing methods
     *
     ***********************/



    /**
     * Return the magic data node for the given form.
     * @param mixed $form I2CE_Form or I2CE_FormField
     * @param boolean $create_id. Defaults to false
     * @return I2CE_MagicDataNode
     */
    protected function getFormConfig( $form , $create_id = false ) {
        if ($form instanceof I2CE_FormField) {
            $form = $form->getContainer();
        }
        if (!$form instanceof I2CE_Form) {
            return false;
        }
        return $this->getFormConfigById($form->getName(),$form->getId(),$create_id); 
    }


    /**
     * Return the magic data node for the given form name and id
     * @param string $form_name
     * @param string $id
     * @param boolean $create_id Defaults to false.  
     */
    protected function getFormConfigById($form_name,$id,$create_id=false) {
        if ( !$id || ($this->config->is_scalar("$form_name")) || (is_string($id) && strlen($id) == 0)) {
            return false;
        }
        $form_config = $this->config->traverse("$form_name/$id", $create_id,false);
        if (!$form_config instanceof I2CE_MagicDataNode) {
            return false;
        }
        if ($form_config->is_indeterminate()) {
            $form_config->set_parent();
        }
        if (!$form_config->is_parent()) {
            return  false;
        }
        return $form_config;        
    }



    /**
     * Deletes a form from the entry tables.
     * @param I2CE_Form $form
     * @param boolean $transact: a flag to use transactions or not. default: true
     * @return boolean
     */
    public function delete( $form, $transact) {
        I2CE::raiseError("MDS attempting to delete " . $form->getFormID());
        $form_config = $this->getFormConfig( $form );
        if ( $form_config instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError("MDS deleting " . $form->getFormID());
            $form_config->erase();
        }
        return true;
    }    



    /**
     * Checks to see if this is writalbe
     * @returns boolean
     */
    public function isWritable() {
        return true;
    }

    /**
     * Updates time stamp on given object
     * @param I2CE_Form $form
     * @param int $timestamp. Unix timestamp
     * @returns boolean. true on success
     */
    public function updateTimeStamp($form, $timestamp ) {
        if (! ($form_config = $this->getFormConfig($form, true))  instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Could not get magic data for form");
            return false;
        }
        $form_config->last_modified = I2CE_Date::now( I2CE_Date::DATE_TIME, $timestamp )->dbFormat();
        return true;
    }

    /**
     * Save a form object into magicdata
     * @param I2CE_Form $form
     * @param I2CE_User $user
     * @param boolean $transact
     */
    public function save( $form, $user, $transact ) {        
        $form_id = $form->getId();
        if ( !$form_id ) {
            $form_id = $this->getNextAvailableId($form->getName());
        }
        if (!$form_id) {
            return false;
        }
        $form->setId($form_id);
        $form_config = $this->getFormConfig($form, true);
        if (!$form_config instanceof I2CE_MagicDataNode) {
            return false;
        }
        $form_config->last_modified = I2CE_Date::now( I2CE_Date::DATE_TIME )->dbFormat();
        $form_config->who = $user->getId();
        $parent = $form->getParent();
        if ($parent != "") {
            /*  Does this need to be here?  the parent node may be new and doesn't exist...
            if ($form_config->is_parent('parent')) {
                return false;
            }
            */
            $form_config->parent = $parent;
        }
        return parent::save($form,$user,$transact);
    }



    /**
     * Change the id of the given form
     * @param string $form
     * @param mixed $oldid
     * @param mixed $newid
     * @returns boolean. true on success
     */
    public function changeID( $form,  $oldid, $newid) {
        if (!$this->config->is_parent($form)) {
            I2CE::raiseError("No existing forms $form");
            return false;
        }
        return $this->config->$form->renameChild($oldid,$newid);
    }


    /**
     * Checks if the given record exists.
     * @param string $form_name
     * @param string $form_id
     * @return array
     */
    public function hasRecord($form_name,$form_id) {
        return ($this->getFormConfigById($form_name,$form_id,false) instanceof I2CE_MagicDataNode);
    }




    /**
     * Gets the next unused integer id for the form.  At least 1
     * @param string $form_name
     * @returns int.  0 on failure. An integer > 0 on success.
     */
    protected function getNextAvailableId($form_name) {
        if ($this->config->is_scalar($form_name)) {
            return 0;
        }
        $ids  = $this->config->getKeys($form_name);
        if (!is_array($ids)) {
            return 0;
        }
        $max = 0;
        foreach ($ids as $id) {
            if (!is_int($id) && ! (is_string($id) && ctype_digit($id))) {
                continue;
            }
            $max = max($id,$max);
        }
        $max++;
        return $max;
    }






    /**
     * Save the FormField to the database.
     * @param I2CE_FormField $form_field
     * @param  boolean $do_check : A flag to determine if a check should be made for the same value being saved.
     * @param  I2CE_User $user: The user saving this data.
     * @return boolean
     */
    public function FF_save( $form_field, $do_check, $user ) {
        if ( !$form_field->isInDB() ) {
            return true;
        }
        if ($form_field->getDBValue() != "" && !$form_field->isValid()) {
            return true;
        }
        $form_config = $this->getFormConfig($form_field, true);
        if (!$form_config instanceof I2CE_MagicDataNode) {
            return false;
        }
        $fields_config = $form_config->fields;
        if (!$fields_config instanceof I2CE_MagicDataNode) {
            return false;
        }
        $field_name = $form_field->getName();
        if (!I2CE_MagicDataNode::checkKey($field_name)) {
            return false;
        }
        $field_config = $fields_config->traverse($field_name,true,false);
        if (!$field_config instanceof I2CE_MagicDataNode) {
            return false;
        }
        if ($form_field->getTypeString() =='blob') {
            $field_config->setAttribute('binary',1);
            $field_config->setAttribute('encoding','base64');
            $field_config->setValue(base64_encode($form_field->getDBValue()));
        } else {
            $field_config->setValue($form_field->getDBValue());
        }
        
        return true;
    }


    /**
     *Check to see if there is a quick field update implemented
     *@returns true if there is a method to quickly update all instances of a given field via SQL
     */
    public function hasGlobalFieldUpdateBySQL() {
        return true;
    }


    /**
     * update value of each  instance  of a given form field by a sql  function call
     * @param I2CE_FormField $form_field
     * @param array $where Array of where data
     * @param string $set_sql sql used to update the field
     */
    public function globalFieldUpdateBySQL($form_field, $where,$set_sql) {
        if (!$form_field instanceof I2CE_FormField) {
            I2CE::raiseError("Not passed form_field");
            return false;
        }
        $formObj = $form_field->getContainer();
        if (!$formObj instanceof I2CE_Form) {
            I2CE::raiseError("No form as a container for the form field");
            return false;
        }
        $form = $formObj->getName();
        if (!$set_sql) {
            I2CE::raiseError("No SQL provided to update $form+$field");
            return false;
        }
        $sub_qry =  $this->getRequiredFieldsQuery($form,array($form_field->getName()));
        $where_qry = $formObj->generateWhereClause($where);
        if (!$where_qry) {
            I2CE::raiseError("Could not gernerate where clasue for $form by\n" . print_r($where,true));
            return false;
        }

        $qry = "UPDATE config_alt JOIN ($sub_qry) AS data  "
            ."ON  parent = CONCAT( '/I2CE/formsData/forms/$form/', `$form+id` , '/fields' ) "
            ."SET value = $set_sql "
            ." WHERE (name = '" . $form_field->getName() . "' AND ($where_qry))";
        //I2CE::raiseError("Updating by $qry");
        
        try {
            $res = $this->db->exec($qry);
            I2CE::getConfig()->clearCache(false);  //since we did a write to the DB, need to clear cache
        } catch ( PDOException $e ) {
            I2CE::pdoError($e, "Cannot update by:\n$qry");
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
