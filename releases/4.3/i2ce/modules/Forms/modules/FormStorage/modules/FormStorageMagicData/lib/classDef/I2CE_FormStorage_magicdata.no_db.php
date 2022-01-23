<?php 
/**
 * © Copyright 2008, 2009 IntraHealth International, Inc.
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
 */
/**
 *  I2CE_Module_FormStorageEntry
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software Foundation; either 
 * version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
 * received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
 * @version 3.2
 * @since 3.2
 * @access public
 */


class I2CE_FormStorage_magicdata extends I2CE_FormStorage_Mechanism {
    
    /**
     * Checks to see if this is writalbe
     * @returns boolean
     */
    public function isWritable() {
        return true;
    }

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
     * Populate the member variables of the object from the database.
     * @param I2CE_Form $form
     */
    public function populate( $form) {
        $form_config = $this->getFormConfig( $form );
        if ( !$form_config instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError( "No saved data found for " , $form->getName() . "|" . $form->getId() );
            return false;
        }            
        if ($form_config->is_scalar("parent" )) {
            $form->setParent( $form_config->parent );
        }
        if ($form_config->is_scalar("last_modified" )) {
            $form->setLastModified( $form_config->last_modified );
        }
        
        $fields = array();
        $form_config->setIfIsSet( $fields, "fields", true );
        if (!$form_config->is_parent('fields')) {
            //no fields have been set.
            return true;
        }
        foreach ($form_config->fields as $field =>$value) {
            if (!is_scalar($value)) {
                continue;
            }
            $fieldObj = $form->getField( $field );
            if ( $fieldObj instanceof I2CE_FormField && $fieldObj->isInDB() ) {
                $fieldObj->setFromDB( $value );
            }
        }
        return true;
    }




    /**
     * Populate the history of entries for the form field if the storage module handles history.
     * @param I2CE_FormField $form_field
     * @return boolean
     */
    public function FF_populateHistory( $form_field ) {        
        $form_config = $this->getFormConfig( $form_field );
        if ( $form_config instanceof I2CE_MagicDataNode ) {
            $field_name = $form_field->getName();
            $entry = new I2CE_Entry( I2CE_Date::blank(), 1, 0, 
                                     $form_field->getFromDB( $form_config->fields->$field_name ) );
            $form_field->addHistory( $entry );
            return true;
        } else {
            return false;
        }
    }
    



    /**
     * Return an array of all the record ids for a given form.
     * @param string $form
     * @param integer $mod_time.  Defaults to  -1.  If non-negative, it is a unix timestamp and we retrieve records modified at or after the given time
     * @param string $parent Defaults to false.  If true, The parent field we want to restrict values to
     * @return array
     */
    public function getRecords( $form, $mod_time = -1, $parent = false) {
        if (! I2CE_MagicDataNode::checkKey( $form)) {
            I2CE::raiseError("Invalid form name");
            return array();
        }
        if (!$this->config->is_parent("forms/$form")) {
            return array();
        }        
        $forms =  $this->config->traverse("forms/$form");        
        if ($mod_time < 0 && $parent == false) {
            $keys = $forms->getKeys();
            if (!is_array($keys)) {
                $keys = array();
            }
        } else {
            if ($mod_time) {
                $mod_time = I2CE_Date::now(I2CE_Date::DATE_TIME,$mod_time);
            }
            $keys = array();
            foreach ($forms as $id=>$form_config) {            
                if (!$form_config instanceof I2CE_MagicDataNode) {
                    continue;
                }
                if ($mod_time && $form_config->is_scalar('last_modified')  && $mod_time->compare(I2CE_Date::fromDB($form_config->last_modified)) == -1) {
                    continue;
                }
                if ($parent 
                    && (  !$form_config->is_scalar('parent') 
                          || !$form_connfig->parent = $parent)
                    ) {
                    continue;
                }
                $keys[] = $id;
            }
        }
        return  $keys;

    }   

    /**
     * Change the id of the given form
     * @param string $form
     * @param mixed $oldid
     * @param mixed $newid
     * @returns boolean. true on success
     */
    public function changeID( $form,  $oldid, $newid) {
        if (!$this->config->is_parent("forms/$form")) {
            I2CE::raiseError("No existing forms $form");
            return false;
        }
        return $this->config->forms->$form->renameChild($oldid,$newid);
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


    /***********************************
     *
     *  Searching methods
     *
     ***********************************/




    /**
     * Looks up the db value of the item based on the code.
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
        $form_config = $this->getFormConfigById($form,$id);
        if (!$form_config instanceof I2CE_MagicDataNode) {
            return false;
        }
        $field_config = $form_config->fields;
        if (!$field_config instanceof I2CE_MagicDataNode) {
            return false;
        }
        $data=array();
        foreach ($fields as $field) {
            if ($field == 'id') {
                $data['id'] = $id;
            } else if ($field == 'parent') {
                $data['parent'] = null;
                $form_config->setIfIsSet($data['parent'],'parent');
            } else if ($field == 'last_modified') {
                $data['parent'] = null;
                $form_config->setIfIsSet($data['last_modified'],'last_modified');
            } else {
                $data[$field] = null;
                $field_config->setIfIsSet($data[$field],$field);
            }
        }
        if ($delim === false) {
            return $data;
        } else {
            return implode($delim,$data);
        }
    }
    




    /**
     * Looks up the display vlaue of the item based on the code.
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
        $form_config = $this->getFormConfigById($form,$id);
        if (!$form_config instanceof I2CE_MagicDataNode) {
            return false;
        }
        $field_config = $form_config->fields;
        if (!$field_config instanceof I2CE_MagicDataNode) {
            return false;
        }
        $fo = I2CE_FormFactory::instance()->createContainer($form);        
        $data=array();
        if ($fo instanceof I2CE_Form) {
            foreach ($fields as $field) {
                if ($field == 'id') {
                    $data['id'] = $id;
                } else if ($field == 'parent') {
                    $data['parent'] = null;
                    $form_config->setIfIsSet($data['parent'],'parent');
                } else   if ($fo->getField($field) instanceof I2CE_FormField) {
                    $dbval = null;
                    if ($field_config->setIfIsSet($dbval,$field)) {
                        $fo->getField($field)->setFromDB($dbval);
                        $data[$field] = $fo->getField($field)->getDisplayValue();
                    } else {
                        $data[$field] = null;
                    }
                } else {
                    $data[$field] = null;

                }
            }
        } else {
            I2CE::raiseError("Could not instantiate $form");
        }
        if ($delim === false) {
            return $data;
        } else {
            return implode($delim,$data);
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
        if (count($ordering) == 0) {
            return $this->quickSearch($form,$parent,$where_data,$limit);
        } else {
            $limit_one = (($limit === true ) || (is_numeric($limit) && $limit == 1 ) || (is_array($limit) && $limit[1] == 1));
            $vals =array_keys($this->listFields($form, array(), $parent,$where_data,$ordering, $limit));
            if ($limit_one) {
                if (count($vals) == 1) {
                    reset($vals);
                    return current($vals);
                } else {
                    return false;
                }
            } else {
                return $vals;
            }
        }
    }

    /**
     * @param string $form  The form name.
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     * and then we get all forms with parent the given id.
     * @param mixed $where_data array or class implementing ArrayAccess, Iterator, and Countable (e.g. MagicDataNode) . the where data.  
     * @param mixed $limit. Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.  
     * @returns mixed an array of matching form ids.  However, ff $limit_one is true or 1 or 
     * array ($offset,1) then then we return either the id or false,  if none found or there was an error.
     */
    protected  function quickSearch($form, $parent=false, $where_data=array(),  $limit = false) {                                      
        $vals = array();
        $limit_fields = $this->getLimitedFields($where_data);
        $factory = I2CE_FormFactory::instance();
        $formObj = $factory->createContainer($form);
        if ($formObj instanceof I2CE_Form && $this->config->is_parent("forms/$form") ) {
            $func = $formObj->createCheckFunction($where_data);
            if ($func !== false) {
                $forms =  $this->config->traverse("forms/$form");
                foreach ($forms as $id=>$form_config) {
                    if (!$form_config instanceof I2CE_MagicDataNode) {
                        continue;
                    }
                    $data = array();
                    foreach ($limit_fields as $field) {
                        if ( $field == 'id' ) {
                            $data[$field] = $id;
                        } else if ($field == 'parent') {
                            $data['parent'] = null;
                            $form_config->setIfIsSet($data['parent'],'parent');
                        } else {
                            $data[$field] = null;
                            $form_config->setIfIsSet($data[$field],"fields/$field");
                        }
                    }
                    if ($func($data) === true) {
                        $vals[] = $id;
                    }
                }
            } else {
                I2CE::raiseError("Bad check function from: " . print_r($where_data,true));
            }

        }
        I2CE_FormStorage::setLastListCount( $form, count( $vals ) );
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
            if (count($vals) < 1) {
                $ret = false;
            } else {
                reset($vals);
                $ret =  current($vals);
            }
            return $ret;
        } else {
            if (is_numeric($limit)) {
                return array_slice($vals,0,$limit);
            } else if (is_array($limit)) {
                list($offset,$limit) = $limit;
                return array_slice($vals,$offset,$limit);
            } else {
                return $vals;
            }
        }
    }

    /**
     * @param string $form.  THe form name
     * @param array $fields of string. The fields we want returned
     * Can include the special field 'last_modified' to get the last modification time for any of the fields of that form which is returned in the format  "Y-m-d H:i:s"
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     * and then we get all forms with parent the given id. If true, we return the parent as one of the fields.
     * @param array $where_data.  contains the  where clause information about this form or a nested      
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.
     * @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *  time greater than or equal to  $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.  
     * @returns mixed an array with key id's and value and array of values.  the array of values has as keys the fields with their corresponding value.
     */
    public  function listFields($form, $fields, $parent = false, $where_data=array(), $ordering=array(), $limit = false,$mod_time = -1) { 
        $vals = array();
        $data_fields = $fields;
        if ( ($key = array_search('parent',$data_fields)) !== false) {
            unset($data_fields[$key]);
            if ($parent === false) {
                $parent = true;
            }
        }
        $order_fields=array();
        $order_func = false;
        if (count($ordering) > 0) {
            foreach ($ordering as $order) {
                if (!is_string($order) || strlen($order) == 0) {                    
                    continue;
                }
                if ($order[0] == '-') {
                    $order = substr($order,1);
                    $order_fields[] = $order;                    
                } else {
                    $order_fields[] = $order;
                }                
            }            
            $order_func = function($a,$b) use ($ordering) {
                foreach( $ordering as $order ) {
                    if (!is_string($order) || strlen($order) == 0) {                    
                        continue;
                    }
                    if ($order[0] == '-') {
                        $order = substr($order,1);
                        if ( array_key_exists($order,$a) && array_key_exists($order,$b)) { 
                            if ($a[$order] < $b[$order]) return 1; 
                            if ($a[$order] > $b[$order]) return -1;
                        }
                    } else {
                        if ( array_key_exists($order,$a) && array_key_exists($order,$b)) { 
                            if ($a[$order] < $b[$order]) return -1; 
                            if ($a[$order] > $b[$order]) return 1;
                        }
                    }
                }
                return 0;
            };
        }
        $all_fields = array_unique(array_merge($data_fields,$this->getLimitedFields($where_data), $order_fields));
        $new_fields = array_diff($all_fields,$data_fields);
        $factory = I2CE_FormFactory::instance();
        $formObj = $factory->createContainer($form);
        if (!$formObj instanceof I2CE_Form && $this->config->is_parent("forms/$form")) {
            I2CE::raiseError("No data for form $form is stored in magic data");
            return array();
        }        
        $func = $formObj->createCheckFunction($where_data);
        if ($func === false) {
            I2CE::raiseError("Bad limit data");
            return array();
        }
        if ( is_array( $mod_time ) && array_key_exists('mod_time',$mod_time)) {
            $mod_time = $mod_time['mod_time'];
        }  
        if (is_scalar($mod_time) &&  ($mod_time >= 0)) {
            $mod_time = I2CE_Date::now(I2CE_Date::DATE_TIME,$mod_time);
        } else {
            $mod_time = false;
        }
        if (!$this->config->is_parent("forms/$form")) {
            return array();
        }
        $forms =  $this->config->traverse("forms/$form");        
        if (count($order_fields) == 0) {
            foreach ($forms as $id=>$form_config) {            
                if (!$form_config instanceof I2CE_MagicDataNode) {
                    continue;
                }
                if ($mod_time && $form_config->is_scalar('last_modified')  && $mod_time->compare(I2CE_Date::fromDB($form_config->last_modified)) == -1) {
                    continue;
                }
                $data = array();
                foreach ($all_fields as $field) {
                    if ( $field == "id" ) {
                        $data[$field] = $id;
                    } else if ($field == 'parent') {
                        $data['parent'] = null;
                        $form_config->setIfIsSet($data['parent'],'parent');
                    } else if ($field == 'last_modified') {
                        $data['last_modified'] = date("Y-m-d H:i:s",0);
                        $form_config->setIfIsSet($data['last_modified'],'last_modified');                        
                    } else {
                        $data[$field] = null;
                        $form_config->setIfIsSet($data[$field],"fields/$field");
                    }
                }
                if ($func && $func($data) !== true) {
                    continue;
                }
                foreach ($new_fields as $field) {
                    unset($data[$field]);
                }
                if ($parent === true) {
                    $data['parent'] = 0;
                    $form_config->setIfIsSet($data['parent'],'parent');
                }                    
                $vals[$id] = $data;
            }
        } else {
            foreach ($forms as $id=>$form_config) {            
                if (!$form_config instanceof I2CE_MagicDataNode) {
                    continue;
                }
                if ($mod_time && $form_config->is_scalar('last_modified')  && $mod_time->before(I2CE_Date::now(I2CE_Date::DATE_TIME, $form_config->last_modified))) {
                    continue;
                }
                $data = array();
                foreach ($all_fields as $field) {
                     if ( $field == "id" ) {
                        $data[$field] = $id;
                     } else if ($field == 'parent') {
                        $data['parent'] = null;
                        $form_config->setIfIsSet($data['parent'],'parent');
                    } else if ($field == 'last_modified') {
                        $data['last_modified'] = date("Y-m-d H:i:s",0);
                        $form_config->setIfIsSet($data['last_modified'],'last_modified');                        
                     } else {
                        $data[$field] = null;
                        $form_config->setIfIsSet($data[$field],"fields/$field");
                     }

                }
                if ($func && $func($data) !== true) {
                    continue;
                }
                if ($parent === true) {
                    $data['parent'] = 0;
                    $form_config->setIfIsSet($data['parent'],'parent');
                }                    
                $vals[$id] = $data;
            }
            if ($order_func) {
                uasort($vals,$order_func);
            }
            if (count($new_fields) > 0) {
                foreach ($vals as &$data) {
                    foreach ($new_fields as $field) {
                        unset($data[$field]);
                    }
                }                
            }
        }
        I2CE_FormStorage::setLastListCount( $form, count( $vals ) );
        if ($limit === true) {
            return array_slice($vals,0,1);
        } else  if (is_numeric($limit)) {
            return array_slice($vals,0,$limit);
        } else if (is_array($limit)) {
            list($offset,$limit) = $limit;
            return array_slice($vals,$offset,$limit);
        } else {
            return $vals;
        }
    }






    /**
     * @param string $form.  THe form name
     * @param array $fields of string. The fields we want returned
     * Can include the special field 'last_modified' to get the last modification time for any of the fields of that form which is returned in the format  "Y-m-d H:i:s"
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     * and then we get all forms with parent the given id. If true, we return the parent as one of the fields.
     * @param array $where_data.  contains the  where clause information about this form or a nested      
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.
     * @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *  time greater than or equal to  $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.  
     * @returns mixed an array with key id's and value and array of values.  the array of values has as keys the fields with their corresponding value.
     */
    public  function listDisplayFields($form, $fields, $parent = false, $where_data=array(), $ordering=array(), $limit = false,$mod_time = -1) { 
        $vals = array();
        $data_fields = $fields;
        if ( ($key = array_search('parent',$data_fields)) !== false) {
            unset($data_fields[$key]);
            if ($parent === false) {
                $parent = true;
            }
        }
        $order_fields=array();
        $order_func = false;
        if (count($ordering) > 0) {
            foreach ($ordering as $order) {
                if (!is_string($order) || strlen($order) == 0) {                    
                    continue;
                }
                if ($order[0] == '-') {
                    $order = substr($order,1);
                    $order_fields[] = $order;                    
                } else {
                    $order_fields[] = $order;
                }                
            }            
            $order_func = function($a,$b) use ($ordering) {
                foreach( $ordering as $order ) {
                    if (!is_string($order) || strlen($order) == 0) {                    
                        continue;
                    }
                    if ($order[0] == '-') {
                        $order = substr($order,1);
                        if ( array_key_exists($order,$a) && array_key_exists($order,$b)) { 
                            if ($a[$order] < $b[$order]) return 1; 
                            if ($a[$order] > $b[$order]) return -1;
                        }
                    } else {
                        if ( array_key_exists($order,$a) && array_key_exists($order,$b)) { 
                            if ($a[$order] < $b[$order]) return -1; 
                            if ($a[$order] > $b[$order]) return 1;
                        }
                    }
                }
                return 0;
            };
 }
        $all_fields = array_unique(array_merge($data_fields,$this->getLimitedFields($where_data), $order_fields));
        $new_fields = array_diff($all_fields,$data_fields);
        $factory = I2CE_FormFactory::instance();
        $formObj = $factory->createContainer($form);
        if (!$formObj instanceof I2CE_Form && $this->config->is_parent("forms/$form")) {
            I2CE::raiseError("No data for form $form is stored in magic data");
            return array();
        }        
        $func = $formObj->createCheckFunction($where_data);
        if ($func === false) {
            I2CE::raiseError("Bad limit data");
            return array();
        }
        if ( is_array( $mod_time ) && array_key_exists('mod_time',$mod_time)) {
            $mod_time = $mod_time['mod_time'];
        }
        if (is_scalar($mod_time) && $mod_time >= 0) {
            $mod_time = I2CE_Date::now(I2CE_Date::DATE_TIME,$mod_time);
        } else {
            $mod_time = false;
        }
        if (!$this->config->is_parent("forms/$form")) {
            return array();
        }
        $forms =  $this->config->traverse("forms/$form");        
        if (count($order_fields) == 0) {
            foreach ($forms as $id=>$form_config) {            
                if (!$form_config instanceof I2CE_MagicDataNode) {
                    continue;
                }
                if ($mod_time && $form_config->is_scalar('last_modified')  && $mod_time->compare(I2CE_Date::fromDB($form_config->last_modified)) == -1) {
                    continue;
                }
                $data = array();
                foreach ($all_fields as $field) {
                    if ( $field == "id" ) {
                        $data[$field] = $id;
                    } else if ($field == 'parent') {
                        $data['parent'] = null;
                        $form_config->setIfIsSet($data['parent'],'parent');
                    } else if ($field == 'last_modified') {
                        $data['last_modified'] = date("Y-m-d H:i:s",0);
                        $form_config->setIfIsSet($data['last_modified'],'last_modified');                        
                    } else {
                        $data[$field] = null;
                        $form_config->setIfIsSet($data[$field],"fields/$field");
                    }
                }
                if ($func && $func($data) !== true) {
                    continue;
                }
                foreach ($new_fields as $field) {
                    unset($data[$field]);
                }
                if ($parent === true) {
                    $data['parent'] = 0;
                    $form_config->setIfIsSet($data['parent'],'parent');
                }                    
                $vals[$id] = $data;
            }
        } else {
            foreach ($forms as $id=>$form_config) {            
                if (!$form_config instanceof I2CE_MagicDataNode) {
                    continue;
                }
                if ($mod_time && $form_config->is_scalar('last_modified')  && $mod_time->before(I2CE_Date::now(I2CE_Date::DATE_TIME, $form_config->last_modified))) {
                    continue;
                }
                $data = array();
                $displays = array();
                foreach ($all_fields as $field) {
                    if ( $field == "id" ) {
                        $data[$field] = $id;
                    } else if ($field == 'parent') {
                        $data['parent'] = null;
                        $form_config->setIfIsSet($data['parent'],'parent');
                    } else if ($field == 'last_modified') {
                        $data['last_modified'] = date("Y-m-d H:i:s",0);
                        $form_config->setIfIsSet($data['last_modified'],'last_modified');                        
                    } else {
                        $fieldObj = $formObj->getField($field);
                        $data[$field] = null;
                        if ($fieldObj instanceof I2CE_FormField && $form_config->setIfIsSet($dbval,"fields/$field")) {
                            $fieldObj->setFromDB($dbval);
                            $data[$field] = $dbval;
                            $displays[$field] = $fieldObj->getDisplayValue();
                        } else {
                            $data[$field] = null;
                        }
                     }

                }
                if ($func && $func($data) !== true) {
                    continue;
                }
                if ($parent === true) {
                    $data['parent'] = 0;
                    $form_config->setIfIsSet($data['parent'],'parent');
                }                    
                foreach ($displays as $field=>$disp) {
                    $data[$field] = $disp;
                }
                $vals[$id] = $data;
            }
            if ($order_func) {
                uasort($vals,$order_func);
            }
            if (count($new_fields) > 0) {
                foreach ($vals as &$data) {
                    foreach ($new_fields as $field) {
                        unset($data[$field]);
                    }
                }                
            }
        }
        I2CE_FormStorage::setLastListCount( $form, count( $vals ) );
        if ($limit === true) {
            return array_slice($vals,0,1);
        } else  if (is_numeric($limit)) {
            return array_slice($vals,0,$limit);
        } else if (is_array($limit)) {
            list($offset,$limit) = $limit;
            return array_slice($vals,$offset,$limit);
        } else {
            return $vals;
        }
    }



    public static function orderByFields($vals,$ordering) {
    }


    /**********************
     *
     * Writing methods
     *
     ***********************/

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
