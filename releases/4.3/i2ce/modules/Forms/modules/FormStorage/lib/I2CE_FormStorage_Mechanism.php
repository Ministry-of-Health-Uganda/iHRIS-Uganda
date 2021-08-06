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
* Class I2CE_Module_FormStorage_Mechanism
* 
* @access public
*/


abstract class I2CE_FormStorage_Mechanism extends I2CE_Fuzzy{

    /** 
     * Constant signifying an initial entry.
     */ 
    const CHANGE_INITIAL = 1;
    /**
     * Constant signifying a verified entry.
     */
    const CHANGE_VERIFY = 2;
    /**
     * Constant signifying a corrected entry.
     */
    const CHANGE_CORRECTION = 3;
    /**
     * Constant signifying an updated entry.
     */
    const CHANGE_UPDATE = 4;



    /**
     * The short name for this storage mechanism
     * @var protected string $name
     */
    protected $name;

    /*
     * @var protected array $global_options. The array of options that are 
     * the same across all forms which share a commone storage mechanisms
     */
    protected $global_options;
    /**
     * The constructor for the storage mechanism
     * @param string $name
     * @param array $global_options. Default to empty array. The array of options that are 
     * the same across all all forms which share a commone storage mechanisms
     */
    public function __construct($name, $global_options=array()) {
        $this->name = $name;
        $this->global_options = $global_options;
    }


    /**
     * update value of each  instanceo  of a given form field by a pho function call
     * @param I2CE_FormField $form_field
     * @param array $where Array of where data
     * @param callable $set_func
     */
    public function globalFieldUpdateByFunction($form_field, $where,$set_func) {
        $formObj = $form_field->getContainer();
        if (!$formObj instanceof I2CE_Form) {
            I2CE::raiseError("Field has no container");
            return false;
        }
        $form = $formObj->getName();
        $field = $form_field->getName();
        $ff = I2CE_FormFactory::instance();
        $ids = I2CE_FormStorage::search($form,true,$where);
        $user = new I2CE_User();
        foreach ($ids as $id) {
            if (! ($obj = $ff->createForm($form . '|'  . $id)) instanceof I2CE_Form) {
                continue;
            }
            $obj->populate();
            $data = array();
            foreach ($obj as $fieldName=>$fieldObj) {
                if (!$fieldObj  instanceof I2CE_FormField) {
                    continue;
                }
                $data[$fieldName] = $fieldObj->getDBValue();
            }
            $data['parent'] = $obj->getParent();
            $fieldObj->setFromDB($set_func($data[$form_field->getName()]));
            $obj->save($user);
            $obj->cleanup();
        }
        return true;
    }
    /**
     * Gets the storage options for the given form.
     * @param string $form
     * @returns mixed I2CE_MagicDataNode of flat storage options on success, false on failure.
     */
    protected function getStorageOptions($form) {
        if (!is_scalar($form)) {
            I2CE::raiseError("Bad call to get storage options for $form");
            return false;
        }
        if (!array_key_exists($form,$this->storage_options_cache) || !$this->storage_options_cache[$form] instanceof I2CE_MagicDataNode) {
            if (!is_string($form) || strlen($form)==0) {
                I2CE::raiseError("Invalid form");
                return false;
            }
            if (!I2CE::getConfig()->setIfIsSet($storage_mech,"/modules/forms/forms/$form/storage")) {
                I2CE::raiseError("Storage mechanism for $form is not set.");
                return false;
            }
            if ($storage_mech !== $this->name) {
                I2CE::raiseError("Storage mechansim registered for $form is not {$this->name}: $storage_mech");
                return false;
            }
            if (I2CE::getConfig()->is_scalar("/modules/forms/forms/$form/storage_options/{$this->name}")) {
                I2CE::raiseError("Invalid {$this->name} storage options stored for $form");
                return false;
            }
            $this->storage_options_cache[$form] = I2CE::getConfig()->traverse("/modules/forms/forms/$form/storage_options/{$this->name}",true);
        }
        return $this->storage_options_cache[$form];
    }


    /**
     * @var protected array $storage_options_cache of I2CE_MagicDataNodes.  The keys are the names of forms which have flat storage.
     */
    protected $storage_options_cache= array();


    /**
     * Public set the more storage options for a given form
     * @param string $form
     * @param mixed $storageOptions.  Either a I2CE_MagicDataNode or an array.
     */
    public function setStorageOptions($form,$storageOptions) {
        if (array_key_exists($form,$this->storage_options_cache)) {
            unset($this->storage_options_cache[$form]);
        }
        if (!is_string($form) || strlen($form) == 0) {
            I2CE::raiseError("Invlaid form");
            return false;
        }
        if (is_array($storageOptions)) {
            $tmp_storageOptions = I2CE_MagicData::instance( "temp_form_storage_options_" . $form );
            $tmp_storageOptions->setValue($storageOptions);
            $this->storage_options_cache[$form] = $tmp_storageOptions;
        } else  if ($storageOptions instanceof I2CE_MagicDataNode) {
            $this->storage_options_cache[$form] = $storageOptions;
        } else {
            I2CE::raiseError("Invalid storage options");
            return false;
        }       
        return true;
    }

    /**
     * Release any resourced held by this form storage mechanism for the indicated form
     * @param string $form
     */
    public function release($form) {
        if (!is_string($form)) {
            return;
        }
        if (array_key_exists($form,$this->storage_options_cache)) {
            unset($this->storage_options_cache[$form]);
        }        
    }

    /*********************
     *
     *  Abstract Reading 
     *
     *********************/


    /**
     * Populate the member variables of the object from the database.
     * @param I2CE_Form $form
     */
    abstract public function populate( $form);
    

    
    /**
     * Return an array of all the record ids for a given form.
     * @param string $form
     * @param integer $mod_time.  Defaults to  -1.  If non-negative, it is a unix timestamp and we retrieve records modified at or after the given time
     * @param string $parent Defaults to false.  If true, The parent field we want to restrict values to
     * @return array
     */
    abstract public function getRecords( $form, $mod_time = -1, $parent =false);

    /**
     * Checks to see if this storage mechansim implements the writing methods.
     * You need to override this in a subclass that implements writable
     * @returns boolean
     */
    abstract public function isWritable();


    


    /*********************
     *
     *  Reading  -- Can be reimplemented for speed
     *
     *********************/

    
    /**
     * @param string $form.  the form name
     * @param array $sel_fields.  an array of field names you want the values for
     * @param mixed $id.  Defaults to null.  If non-null it is the id that we wish to limit to.
     * @param integer $mod_time. Defaults to -1.  
     *    If non-negative, we only list the requested fields for an 
     *    id if at least one of them has a modification
     *    time greater than or equal to $mod_time.  If the form storage has no way of tracking modifucation time, 
     *    all entries are listed.
     *  @param boolean $parent. Defaults to false.  If true, we include the parent id as a referenced field.  
     *  @param mixed $limit. Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *    If it is as an array of two integers, it is the offset and then number of results to limit to.  
     *
     * @returns mixed.  
     *    boolean: returns false if there is no ability to generate a SQL statement to use in a sub-select on.  
     *             the columns returned will be the field names
     *    string: a SQL statement to use in a sub-select on.
     *    
     */
    public function getSubSelectFieldsQuery($form,$sel_fields,$id = null ,$mod_time = -1,  $parent =false ,$limit = false  ) {        
        return false;
    }



    /**
     * Checks if the given record exists.
     * @param string $form_name
     * @param string $form_id
     * @return array
     */
    public function hasRecord( $form_name, $form_id) {
        return in_array($form_id,$this->getRecords($form_name));
    }

    /**
     * Looks up the description of the item based on the code.
     * 
     * This is the default method that most implementations of {@link lookup()} use.  It finds the description of
     * the object based on the code and saves it in the {@link cache} and returns it.
     * @param string $form The name of the form in the database.
     * @param integer $id The code of the entry to lookup.
     * @param array $fields A list of fields to look up and return.
     * @param string $delim The delimiter to put between returned fields if there are more 
     *                      than one.  If false, returns the values as an array.
     * @return string false on failure
     */
    public function lookupField($form,$id,$fields,$delim) {
        $formObj = I2CE_FormFactory::instance()->createContainer($form.'|'.$id);
        if (!$formObj instanceof I2CE_Form) {
            return false;
        }
        $formObj->populate();
        $ret = array();;
        foreach ($fields as $field) {
            if ($field == 'id') {
                $ret['id'] = $formObj->getId();
            } else if ($field == 'parent') {
                $ret['parent'] = $formObj->getParent();
            } else  if ( $formObj->getField($field) instanceof I2CE_FormField) {
                $ret[$field] = $formObj->getField($field)->getDBValue();
            } else {
                $ret[$field] = null;
            }
        }
        if ($delim === false) {
            return $ret;
        } else {
            return implode($delim,$fields);
        }
    }



    /**
     * Looks up the description of the item based on the code.
     * 
     * This is the default method that most implementations of {@link lookup()} use.  It finds the description of
     * the object based on the code and saves it in the {@link cache} and returns it.
     * @param string $form The name of the form in the database.
     * @param integer $id The code of the entry to lookup.
     * @param array $fields A list of fields to look up and return.
     * @param string $delim The delimiter to put between returned fields if there are more 
     *                      than one.  If false, returns the values as an array.
     * @return string false on failure
     */
    public function lookupDisplayField($form,$id,$fields,$delim) {
        $formObj = I2CE_FormFactory::instance()->createContainer($form.'|'.$id);
        if (!$formObj instanceof I2CE_Form) {
            return false;
        }
        $formObj->populate();
        $ret = array();;
        foreach ($fields as $field) {
            if ($field == 'id') {
                $ret['id'] = $formObj->getId();
            } else if ($field == 'parent') {
                $ret['parent'] = $formObj->getParent();
            } else  if ( $formObj->getField($field) instanceof I2CE_FormField) {
                $ret[$field] = $formObj->getField($field)->getDisplayValue();
            } else {
                $ret[$field] = null;
            }
        }
        if ($delim === false) {
            return $ret;
        } else {
            return implode($delim,$ret);
        }
    }


    /**
     * Populate the member variables of the object from the database.
     * @param I2CE_Form $form
     */
    public function populateHistory( $form) {
        foreach( $form as $field => $field_obj ) {
            if ( $field_obj && $field_obj instanceof I2CE_FormField ) {
                $field_obj->populateHistory();
            }
        }
        if ( ($parentField = $form->getField('parent')) instanceof I2CE_FormField) {
            $parentField->populateHistory();
        }
    }


    /**
     * Gets the required fields.
     * @param string $form The form we are select from
     * @param boolean $parent. Defaults to false.  If true, we include the parent id as a referenced field.  
     * If it is scalar and non-boolean, it is consider to be the ID of the parent, and then we get all forms with parent the given id.
     * @param mixed $where_data array or class implementing ArrayAccess, Iterator, and Countable (e.g. MagicDataNode) . the where data.  
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit. Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.  
     * @param callback $field_refernece_callback.  A callback function whose first arguement is the form, the second arguements
     * is the field and which returns the way the field value should be references as a field.  If the callback is null (the default) then
     * the reference used is "$form+$field"
     * @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *    time greater than or equal to $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.
     * @returns array with keys id's and values the I2CE_Form instance.
     */
    public function getFormsById($form,  $parent, $where_data=array(), $ordering=array(), $limit = false, $mod_time = -1) {
        $vals = array();
        $ids = $this->getRecords($form,$mod_time,$parent);
        $factory = I2CE_FormFactory::instance();
        $form_obj = $factory->createContainer($form);
        if (!$form_obj instanceof I2CE_Form) {
            return array();
        }
        if ((is_array($where_data) || ($where_data instanceof ArrayAccess && $where_data instanceof Countable && $where_data instanceof Iterator )) 
            && count($where_data) >0) {
            if (!I2CE_ModuleFactory::instance()->isEnabled('form-limits')) {
                I2CE::raiseError("form-limits module is not enabled");
                return array();
            }
            $check_limit = true;
        }  else {
            $check_limit =    false;
        }
        $limit_offset = 0;
        $limit_amount = false;
        if ($limit !== false) {
            if (is_array($limit) && count($limit) == 2) {
                list($limit_offset, $limit_amount) = $limit;
            } else if (is_scalar($limit)) {
                $limit_amount = $limit;
            }
        }            
        foreach ($ids as $id) {
            $child_form = $factory->createContainer($form.'|'.$id);
            if (!$child_form instanceof I2CE_Form) {
                continue;
            }
            $child_form->populate();
            if ($check_limit && !$child_form->checkWhereClause($where_data)) {
                $child_form->cleanup();
                continue;
            }
            $vals[$id] = $child_form;
        }
        if (is_array($ordering) && count($ordering) > 0) {
            $this->ordering = $ordering;
            uasort($vals,array($this,'compareFormsByFields'));
        }
        if (is_numeric($limit_amount)) {
            return array_slice($vals,$limit_offset,$limit_amount,true);
        } else {
            return $vals;
        }
    }





    protected $ordering;
    /**
     * Compares two forms to see which is greater based on the field ordering set in $this->ordering
     * @param $form1
     * @param $form2
     * @returns int
     */
    protected function compareFormsByFields($form1,$form2) {
        if (!is_array($this->ordering)) { //bad ordering data.  consider things equal
            return 0;
        }
        foreach ($this->ordering as $field) {            
            if (!is_string($field) || strlen($field) < 1) {
                continue;
            }
            if ($field[0] == '-') {
                $negate = true;
                $field = substr($field,1);
            } else {
                $negate = false;
            }
            $field1 = $form1->getField($field);
            $field2 = $form2->getField($field);
            if (!$field1 instanceof I2CE_FormField) {
                continue;
            }
            if (!$field2 instanceof I2CE_FormField) {
                continue;
            }
            $cmp = $field1->compare($field2);
            if ($cmp == 0) {
                continue;
            }
            if ($cmp  < 0) { //then field1 < field2
                if ($negate) {
                    return 1;
                } else {
                    return -1;
                } 
            } else {
                if ($negate) {
                    return -1;
                } else {
                    return 1;
                } 
            }            
        }
        return 0;  //no fields compared against another.  consider them equal
    }






    /**
     * Gets the id's for the given child for this form.
     * @param string $form_name
     * @param   mixed $parent_form_id the prent form id
     * @param  array/string: an optional orderBy array of fields
     * @param array  where
     * @param integer: A limit of the number of children ids to return
     * @return array
     */
    public function getIdsAsChild($form_name, $parent_form_id,$order_by, $where, $limit) { 
        $forms = $this->getFormsById($form_name, $parent_form_id, $where,$order_by,$limit);
        $ids = array();
        foreach ($forms as $form) {
            if (!$form instanceof I2CE_Form) {
                continue;
            }
            $ids[] = $form->getId();
            $form->cleanup();
        }
        return $ids;
    }


    
    /**
     * @param string $form.  THe form name
     * @param array $fields of string. The fields we want returned.  Can include the special field 'last_modified' to get the last modification time for any of the fields of that form which is returned in the format  "Y-m-d H:i:s"
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     * and then we get all forms with parent the given id. If true, we return the parent as one of the fields.
      *@param array $where_data.  contains the  where clause information about this form or a nested      
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.
     * @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *    time greater than or equal to $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.
     * @returns mixed an array with key id's and value and array of values.  the array of values has as keys the fields with their corresponding value.
     */
    public  function listFields($form, $fields, $parent = false, $where_data=array(), $ordering=array(), $limit = false, $mod_time = -1) { 
        $forms = $this->getFormsById($form,$parent, $where_data,$ordering,$limit, $mod_time);
        if ($parent === true) {
            if (!in_array('parent',$fields)) {
                $fields[] = 'parent';
            }
        }
        $vals = array();
        foreach ($forms as $id=>$form) {
            if (!$form instanceof I2CE_Form) {
                continue;
            }
            $data = array();
            foreach ($fields as $field) {
                if ($field == 'parent') {
                    $data['parent'] = $form->getParent();
                } else if ($field == 'last_modified') {
                    $data['last_modified'] = date("Y-m-d H:i:s",0);
                } else {
                    $fieldObj = $form->getField($field);
                    if ($fieldObj instanceof I2CE_FormField) {
                        $data[$field] = $fieldObj->getDBValue();
                    } else {
                        $data[$field] = null;
                    }               
                } 
            }
            $vals[$id] = $data;
            $form->cleanup();
        }
        return  $vals;
    }



    /**
     * @param string $form.  THe form name
     * @param array $fields of string. The fields we want returned
     * Can include the special field 'last_modified' to get the last modification time for any of the fields of that form which is returned in the format  "Y-m-d H:i:s"
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     * and then we get all forms with parent the given id. If true, we return the parent as one of the fields.
      *@param array $where_data.  contains the  where clause information about this form or a nested      
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.
     * @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *    time greater than or equal to $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.
     * @param mixed $user The user id of the user to limit the results to so it only returns results limited to that user.  This can be a single value or an array
     * @returns mixed an array with key id's and value and array of values.  the array of values has as keys the fields with their corresponding value.
     */
    public  function listDisplayFields($form, $fields, $parent = false, $where_data=array(), $ordering=array(), $limit = false, $mod_time = -1, $user=false) { 
        $forms = $this->getFormsById($form,$parent, $where_data,$ordering,$limit, $mod_time);
        if ($parent === true) {
            if (!in_array('parent',$fields)) {
                $fields[] = 'parent';
            }
        }
        $vals = array();
        foreach ($forms as $id=>$form) {
            if (!$form instanceof I2CE_Form) {
                continue;
            }
            $form->populate();
            $data = array();
            foreach ($fields as $field) {
                if ($field == 'parent') {
                    $data['parent'] = $form->getParent();
                } else if ($field == 'last_modified') {
                    $data['last_modified'] = $form->getField('last_modified')->getDisplayValue();
                } else {
                    $fieldObj = $form->getField($field);
                    if ($fieldObj instanceof I2CE_FormField) {       
                        $data[$field] = $fieldObj->getDisplayValue();
                    } else {
                        $data[$field] = null;
                    }               
                } 
            }
            $vals[$id] = $data;
            $form->cleanup();
        }
        return  $vals;
    }


    /*
     * Walks down the where clause data from which  the WHERE query it defined to look for the fields used.
     * @param array $expr the where data.  
     * @returns array() of strings, the field names
     */
    protected function getLimitedFields($expr) {
        if (!is_array($expr)) {
            return array();
        }
        if (!array_key_exists('operator',$expr) || !is_string($expr['operator'])) {
            return array();
        }
        if ($expr['operator'] == 'FIELD_LIMIT') {
            if (array_key_exists('field',$expr) && is_string($expr['field'])) {
                return array($expr['field']);
            } else {
                return array();
            }
        } else if (array_key_exists('operand',$expr) && is_array($expr['operand'])) {            
            $fields = array();
            foreach ($expr['operand'] as $operand) {
                $t_fields = $this->getLimitedFields($operand);
                if (is_array($t_fields)) {
                    $fields = array_unique(array_merge($fields, $t_fields));
                }
            }
            return $fields;
        } else{
            return array();
        }
    }

    
    /**
     * @param string $form  The form name.
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     * and then we get all forms with parent the given id.
     * @param mixed $where_data array or class implementing ArrayAccess, Iterator, and Countable (e.g. MagicDataNode) . the where data.  
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit. Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.  
     * @returns mixed an array of matching form ids.  However, ff $limit_one is true or 1 or 
     * array ($offset,1) then then we return either the id or false,  if none found or there was an error.
     */
    public  function search($form, $parent=false, $where_data=array(), $ordering=array(), $limit = false) {                                      
        $vals = $this->getFormsById($form,$parent, $where_data,$ordering,$limit);
        $limit_one = false;
        if (is_array($limit)) {
            if (count($limit) == 2) {
                end($limit);
                if (current($limit) == 1) {
                    $limit_one = true;
                }
            }
        } else {
            $limit_one = (($limit === true ) || (is_numeric($limit) && $limit == 1 ));
        }
        if ($limit_one === true ) {
            if (count($vals) !== 1) {
                $ret = false;
            } else {
                reset($vals);
                $ret =  key($vals);
            }
            foreach ($vals as $form) {
                if (!$form instanceof I2CE_Form) {
                    continue;
                }
                $form->cleanup();
            }
            return $ret;
        } else {
            $ids = array();
            foreach ($vals as $form) {
                if (!$form instanceof I2CE_Form) {
                    continue;
                }
                $ids[] = $form->getId();
                $form->cleanup();
            }
            return $ids;
        }
    }



    /*****************************
     * 
     *  Writing -- can be re-implemented to make writing abailable
     *
     *****************************/
    

    /**
     * Change the id of the given form
     * @param string $form
     * @param mixed $oldid
     * @param mixed $newid
     * @returns boolean. true on success
     */
    public function changeID( $form,  $oldid, $newid) {
        $factory = I2CE_FormFactory::instance();
        $oldFormObj = $factory->createContainer($form . '|' . $oldid);
        if (!$oldFormObj instanceof I2CE_Form) {
            I2CE::raiseError("Could not instantiate " .$form . '|' . $oldid);
            return false;
        }
        $newFormObj = $factory->createContainer($form . '|' . $newid);
        if (!$newFormObj instanceof I2CE_Form) {
            I2CE::raiseError("Could not instantiate " .$form . '|' . $newid);
            return false;
        }        
        foreach ($oldFormObj as $fieldName=>$fieldObj) {
            if (!$fieldObj instanceof I2CE_FormField) {
                continue;
            }
            $newFieldObj =   $newFormObj->getField($fieldName);
            if (!$newFieldObj instanceof I2CE_FormField) {
                I2CE::raiseError("COuld not get gield $fieldName from " . $form);
                continue;
            }
            $newFieldObj->setDBValue($fieldObj->getDBValue());
        }
        if (!$newFormObj->save()) {
            I2CE::raiseError("Could not save new form " . $form->getName() . '|' . $newid);
            return false;
        }
        return $form->delete();        
    }

    /**
     * Updates time stamp on given object
     * @param I2CE_Form $form
     * @param int $timestamp. Unix timestamp
     * @returns boolean. true on success
     */
    public function updateTimeStamp($form, $timestamp ) {
        //by default does nothing.  Needs to be overwritten for various writable form storage mechanisms
        return true;
    }
        
    /**
     * Save a form object 
     * If this functio is over-written, it should include the fuzzy method call
     * foreach ($form as $field) {
     *      $field->save(true/false, $user)
     * }
     * @param I2CE_Form $form
     * @param I2CE_User $user
     * @param boolean $transact
     */
    public function save( $form, $user, $transact ) {
        if (!$this->isWritable()) {
            return true;
        }                
        foreach ($form as $field) {
            if ($field->getDBValue() != "" && !$field->isValid()) {
                continue;
            }
            if (! $field->save(true,$user)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Save the FormField to the database.
     * @param I2CE_FormField $form_field
     * @param  boolean $do_check : A flag to determine if a check should be made for the same value being saved.
     * @param  I2CE_User $user: The user saving this data.
     * @return boolean
     */
    public function FF_save( $form_field, $do_check, $user ) {
        return true;
    }


    /**
     * Set FormField_INT_GENERATE sequence
     * @param I2CE_FormField_INT_GENERATE $form_field
     * @return boolean
     */
    public function FF_IG_setSequence( $form_field) {
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
        $field_name = $form_field->getName();
        $fields = $this->lookupField($form->getName(),$form->getId(),array($field_name),false);
        if (!is_array($fields) || !array_key_exists($field_name,$fields)) {
            //no data to populate
            return true;
        }
        $last_modified = I2CE_Date::blank();
        $entry = new I2CE_Entry( $last_modified, 1, 0,  $form_field->getFromDB( $fields[$field_name]));
        $form_field->addHistory( $entry );
        return true;
    }


    /**
     * Deletes a form from the entry tables.
     * @param I2CE_Form $form
     * @param boolean $transact: a flag to use transactions or not. default: true
     * @return boolean
     */
    public function delete( $form, $transact ) {
        I2CE::raiseError("Delete not implemented");
        return false;
    }






}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
