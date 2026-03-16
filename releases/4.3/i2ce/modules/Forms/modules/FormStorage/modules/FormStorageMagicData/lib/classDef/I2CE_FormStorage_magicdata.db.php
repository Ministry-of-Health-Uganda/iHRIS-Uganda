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
        $this->config = I2CE::getConfig()->I2CE->formsData;
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
        if ($id !== null) {
            //an id was given
            $selects = array("'" . addslashes($id) . "' as $id_ref");
        } else if (!is_bool($parent) && $parent !== null) {
            //a parent id was given
            $selects = array(
                "SUBSTRING_INDEX(CONCAT('/',SUBSTRING(c.path,8)), '/', -1) as $id_ref"
                );
        } else {
            //no id or parent was given
            $selects = array("SUBSTRING_INDEX(CONCAT('/',SUBSTRING(c.path,8)), '/', -1) as $id_ref");
        }
        if ( is_array( $mod_time ) && array_key_exists('mod_time', $mod_time)) {
            $mod_time = $mod_time['mod_time'];
        }
        $get_mod_time =  (is_scalar($mod_time) && $mod_time >= 0);
        foreach ($fields as $field) {
            if ($field == 'id' || $field == 'parent') {
                continue;
            }
            if ( $field == 'last_modified') {
                $get_mod_time = true;
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
            $joins[] = "LEFT JOIN config `joined_$field` on `joined_$field`.path = CONCAT(c.path,'/','fields/$field')";
            $selects[] = "`joined_$field`.value as $ref";
        }
        if ($parent !== false) {
            $joins[] = "LEFT JOIN config `joined_parent` on `joined_parent`.path = CONCAT(c.path,'/','parent')";
            if ($parent === true) {
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
        if ($id !== null) {
            //an id was given
            $wheres = array( "c.path = 'config:I2CE/formsData/forms/$form/" . addslashes($id) . "'");
        } else if ( $parent && !is_bool( $parent ) ) {
            //a parent is given
            $wheres = array(
                "'I2CE/formsData/forms/$form' = SUBSTR(c.path,8,length(c.path) - locate('/', reverse(c.path)) -7  )",
                "`joined_parent`.value = '" . addslashes($parent)  ."'");
        } else {
            //no id or parent was given
            $wheres = array( "'I2CE/formsData/forms/$form' = SUBSTR(c.path,8,length(c.path) - locate('/', reverse(c.path)) -7  )");
        }
        if ($get_mod_time ) {
            $joins[] = "LEFT JOIN config modified ON modified.path = CONCAT( c.path, '/', 'last_modified' )";
            if ($field_reference_callback !== null) {
                if ( !is_string($ref = call_user_func($field_reference_callback, $form,'last_modified'))) {
                    I2CE::raiseError("Invalid parent reference callback function:\nlast_modified --> $ref");
                    return false;
                }
            } else {
                $ref = '`' . $form . '+last_modified`';
            }        
            $selects[] = "FROM_UNIXTIME(modified.value) AS $ref";
            if (is_scalar($mod_time) && $mod_time >= 0) {
                $wheres[] = "(modified.value IS NULL OR STR_TO_DATE(modified.value,'%Y-%m-%d %H:%i:%s') > STR_TO_DATE('"  .  date('Y-m-d H:i:s' , $mod_time) . "','%Y-%m-%d %H:%i:%s'))";
            }
        }
        return  "SELECT " .implode(',',$selects) . " FROM config c " . implode(' ', $joins) . ' WHERE (' . implode( ' AND ', $wheres) . ')';
    }


// a sample query that is produced for facility fields
//
// SELECT CONCAT('facility+',SUBSTRING_INDEX(CONCAT('/',SUBSTRING(c.path,8)), '/', -1)) as `facility+id`,
//       joined_name.value as `facility+name`,
//       joined_facility_type.value as `facility+facility_type`,
//       joined_location.value as `facility+location`,
//       joined_parent.value as `facility+parent`
// FROM config c
// LEFT JOIN config joined_name on joined_name.path = CONCAT(c.path,'/','fields/name')
// LEFT JOIN config joined_facility_type on joined_facility_type.path = CONCAT(c.path,'/','fields/facility_type')
// LEFT JOIN config joined_location on joined_location.path = CONCAT(c.path,'/','fields/location')
// LEFT JOIN config joined_parent on joined_parent.path = CONCAT(c.path,'/','parent')


// WHERE  'I2CE/formsData/forms/facility' =
// SUBSTR(c.path,8,length(c.path) - locate('/', reverse(c.path)) -7  )





    /**
     * Change the id of the given form
     * @param string $form
     * @param mixed $oldid
     * @param mixed $newid
     * @returns boolean. true on success
     */
    public function changeID( $form,  $oldid, $newid) {
        if (! ($oldFormConfig = $this->getFormConfigById($form,$oldid,false)) instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("No form $form_name|$oldid exists");
            return false;
        }
        $newFormConfig = $this->getFormConfigById($form,$oldid,true); 
        foreach ($oldFormConfig as $key=>$data) {
            $newFormConfig->$key = $data;
        }
        $oldFormConfig->erase();
        return true;
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
        if ( !$id || ($this->config->is_scalar("forms/$form_name")) || (is_string($id) && strlen($id) == 0)) {
            return false;
        }
        $form_config = $this->config->traverse("forms/$form_name/$id", $create_id,false);
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
     * Gets the next unused integer id for the form.  At least 1
     * @param string $form_name
     * @returns int.  0 on failure. An integer > 0 on success.
     */
    protected function getNextAvailableId($form_name) {
        $form_path = "forms/$form_name";
        if ($this->config->is_scalar($form_path)) {
            return 0;
        }
        $ids  = $this->config->getKeys($form_path);
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
        if (!is_string($field_name) || strlen($field_name) == 0) {
            return false;
        }
        $fields_config->$field_name = $form_field->getDBValue();
        return true;
    }


    


    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
