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
* Class I2CE_Module_FormStorage_DB
* 
* @access public
*/


abstract class I2CE_FormStorage_DB extends I2CE_FormStorage_Mechanism {



    protected $container_cache = array();
    
    /**
     * @var PDO The database object
     */
    protected $db;



    /**
     * Construct this module class
     * @param string $name
     * @param array $options
     */
    public function __construct($name,$options) {
        parent::__construct($name,$options);
        $this->db = I2CE::PDO();
    }




    /**
     *  Construct a query (to be used as a sub-select) to view the fields of the given form.  It always will return the id of the form as well
     *  @param string $form
     *  @param mixed $fields.  Either a string, the field, or an array of string, the fields.  Can also include the special field "last_modified" to get the last modification time on any of the fields
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
    abstract public  function getRequiredFieldsQuery($form,$fields, $id=null, $parent = false, $field_reference_callback = null, $mod_time = -1, $user=false);

    /**
     *Check to see if there is a quick field update implemented
     *@returns true if there is a method to quickly update all instances of a given field via SQL
     */
    public function hasGlobalFieldUpdateBySql() {
        return false;
    }

    /**
     * update value of each  instanceo  of a given form field by a pho function call
     * @param {I2CE_FormField} $form_field
     * @param array $where Array of where data
     * @param string $set_sql sql used to update the field
     */
    public function globalFieldUpdateBySQL($form_field, $where,$set_sql) {
        return false;
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
                $fieldObj->setFromDB($result->$ref);
            }
        }
        $ref = strtolower($form_name . '+parent'  );
        if (isset($result->$ref)) {
            $form->setParent($result->$ref);
        }
        $ref = strtolower($form_name . '+last_modified'  );
        if (isset($result->$ref)) {
            $form->setLastModified($result->$ref);
        }
        $ref = strtolower($form_name . '+created'  );
        if (isset($result->$ref)) {
            $form->setCreated($result->$ref);
        }
        return true;       
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
     *                      than one. If false, it returns it as an array.
     * @return string false on failure
     */
    public function lookupField($form,$id,$fields,$delim) {
        $lookup_fields = $fields;
        if ( ($key = array_search('parent',$lookup_fields)) !== false) {
            unset($lookup_fields[$key]);
            $parent = true;
        } else {
            $parent = false;
        }
        $qry = $this->getRequiredFieldsQuery($form,$lookup_fields,$id, $parent);
        $qry .= ' LIMIT 1';
        try { 
            $result = I2CE_PDO::getRow($qry);
            $ret = array();
            foreach ($fields as $field) {
                $field_ref = strtolower($form . '+' . $field);
                if (isset($result->$field_ref)) {
                    $ret[$field] = $result->$field_ref;
                } else {
                    $ret[$field] = '';
                }
            }
            if ($delim === false) {
                return $ret;
            } else {
                return implode($delim,$ret);
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error populating form " . $form );
            return false;
        }
    }


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
     * @return mixed.  
     *    boolean: returns false if there is no ability to generate a SQL statement to use in a sub-select on.  
     *             the columns returned will be the field names
     *    string: a SQL statement to use in a sub-select on.
     *    
     */
    public function getSubSelectFieldsQuery($form,$sel_fields,$id = null ,$mod_time = -1,  $parent =false ,$limit = false  ) {
        $callback = function($a,$b) { return "`" . $b . "`"; };
        if (!$callback) {
            I2CE::raiseError("Could not create callback reference");
            return false;
        }
        $post_qry = '';
        if ($limit === true) {
            $post_qry .= ' LIMIT 1';
        }  else if (is_int($limit) ||  (is_string($limit) && ctype_digit($limit))) {
            $post_qry .= ' LIMIT ' . $limit;
        } else if (is_array($limit) && count($limit) == 2 ) {
            $post_qry .= ' LIMIT ' . implode(' , ', $limit);
        }
        $select = $this->getRequiredFieldsQuery($form,$sel_fields,$id,$parent, $callback, $mod_time );      
        if (!$select) {
            I2CE::raiseError("Could not get required fields for {$this->form}");
            return false;
        }
        return $select . $post_qry;
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
     *                      than one. If false, it returns it as an array.
     * @return string false on failure
     */
    public function lookupDisplayField($form,$id,$fields,$delim) {
        $lookup_fields = $fields;
        if ( ($key = array_search('parent',$lookup_fields)) !== false) {
            unset($lookup_fields[$key]);
            $parent = true;
        } else {
            $parent = false;
        }
        $qry = $this->getRequiredFieldsQuery($form,$lookup_fields,$id, $parent);        
        $qry .= ' LIMIT 1';
        try {
            $result = I2CE_PDO::getRow($qry);
            if (!$result ) {
                I2CE::raiseError("Error populating form $form:\n\t$qry");
                return;
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error populating form " . $form );
            return false;
        }
       $ret = array();
        $fo = $this->getContainer($form);
        if ($fo instanceof I2CE_Form ) {
            foreach ($fields as $field) {
                $field_ref = strtolower($form . '+' . $field);
                if ($field == 'parent' || $field == 'id') {
                    $ret[$field] = $result->$field_ref;
                } else if ($fo->getField($field) instanceof I2CE_FormField ) {
                    $fo->getField($field)->setFromDB($result->$field_ref);
                    $ret[$field] = $fo->getField($field)->getDisplayValue();
                } else {
                    $ret[$field] = '';
                }
            }
            // This object is cached in $this->container_cache, if we call cleanup then it won't
            // have any fields because cleanup removes them all.
            //$fo->cleanup();
        } else {
            I2CE::raiseError("Could not instantiate $form");
        }
        unset( $fo );
        if ($delim === false) {
            return $ret;
        } else {
            return implode($delim,$ret);
        }
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
        $fieldQry = $this->getRequiredFieldsQuery($form->getName(),array($field),$form->getId());
        try {
            $result = I2CE_PDO::getRow($fieldQry);
            $ref = strtolower($form->getName() . '+' . $field );
            $entry = new I2CE_Entry( I2CE_Date::blank(), 1, 0,  $form_field->getFromDB( $result->$ref ));
            $form_field->addHistory( $entry );
            return true;
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error populating field $field of form " . $form->getName() );
            return false;
        }
    }
    
    
    protected function getContainer($form) {
        if (! array_key_exists($form,$this->container_cache)) {
            $this->container_cache[$form] = I2CE_FormFactory::instance()->createContainer($form);
        } else   if ($this->container_cache[$form] instanceof I2CE_Form) {
            $this->container_cache[$form]->resetDefaultValues();
        }
        return $this->container_cache[$form];
    }


    


    /**
     * Return an array of all the record ids for a given form.
     * @param string $form
     * @param integer $mod_time.  Defaults to  -1.  If non-negative, it is a unix timestamp and we retrieve records modified at or after the given time
     * @param string $parent Defaults to false.  If true, The parent field we want to restrict values to
     * @return array
     */
    public function getRecords( $form, $mod_time = -1,$parent =false) {        
        $formObj = $this->getContainer($form);
        if (!$formObj instanceof I2CE_Form) {
            I2CE::raiseError("Could not instantiate $form");
            return array();
        }
        $idQry = $this->getRequiredFieldsQuery($form,null,null,$parent,null,$mod_time);
        if (!is_string($idQry) || strlen(ltrim($idQry)) == 0) {
            //I2CE::raiseError("Could not get the required fields query for $form");
            return array();
        }
        try {
            $result = $this->db->query($idQry);
            $ids = array();
            $ref =   strtolower($form . '+id');
            while( $row = $result->fetch() ) {
                $ids[] = $row->$ref;
            }
            unset( $result );
            return $ids;
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error getting ids for form $form:" );
            return array();
        }
    }



    /**
     * Generates a SQL to select the required fields.
     * @param string $form The form we are select from
     * @param array $fields of string.  the fields that we want to select.  the keys are the fields names, the values are what we wish to select them as.
     * Can include the special field 'last_modified' to get the last modification time for any of the fields of that form which is returned in the format  "Y-m-d H:i:s"
     * @param boolean $parent. Defaults to false.  If true, we include the parent id as a referenced field.  
     * If it is scalar and non-boolean, it is consider to be the ID of the parent, and then we get all forms with parent the given id.
     * @param mixed $expr array or class implementing ArrayAccess, Iterator, and Countable (e.g. MagicDataNode) . the where data.  
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit. Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.  
     * @param callback $field_refernece_callback.  A callback function whose first arguement is the form, the second arguements
     * is the field and which returns the way the field value should be references as a field.  If the callback is null (the default) then
     * the reference used is "$form+$field"
     * @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *    time greater than $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.
     * @param mixed $user The user id of the user to limit the results to so it only returns results limited to that user.  This can be a single value or an array
     * @returns string the SQL Query needed to get the form/field data or false on failure.
     */
    public function getFields($form, $fields = array(), $parent, $where_data=array(), $ordering=array(), $limit = false, $field_reference_callback = null, $mod_time = -1, $user=false) {
        if (!is_array($fields) || count($fields) == 0) {
            I2CE::raiseError("No fields given");
            return false;
        }
        $formObj = $this->getContainer($form);
        if (!$formObj instanceof I2CE_Form) {
            I2CE::raiseError("Could not instantiate $form");
            return false;
        }       
        foreach ($fields as $i=>$field) {
            if (in_array($field,array('id','parent','last_modified','created'))) {
                continue;
            }
            if (!($fieldObj = $formObj->getField($field)) instanceof I2CE_FormField
                || ! $fieldObj->isInDB()
                ) {
                unset($fields[$i]);
            }
        }

        $ref_fields = array();
        if (!is_array($ordering)) {
            $ordering = array(); //just to be sage
        }

        foreach ($fields as $field=>$desired_ref) {
            if ($field_reference_callback !== null) {
                if ( !is_string($ref = call_user_func($field_reference_callback, $form,$field)) === false){ 
                    I2CE::raiseError("Invalid field reference callback function");
                    return false;
                }
            } else {
                $ref = '`' . $form . '+' . $field . '`';
            }
            $ref_fields[] = "$ref AS `$desired_ref`";
        }
        $field_select = "SELECT SQL_CALC_FOUND_ROWS " . implode(",",$ref_fields);
        foreach ($ordering as $i=>&$ord) {
            if (!is_string($ord)) {
                unset($ordering[$i]);
                continue;
            }
            $field = $ord;
            if ($ord[0] == '-') {
                $field = substr($ord,1);
                $ord = "`$form+" . $field . '` DESC';
            } else {
                $ord = "`$form+$field`  ASC";
            }
            if (!in_array($field,$fields)) {
                $fields[] = $field;
            }
        }

        if ((is_array($where_data) || ($where_data instanceof ArrayAccess && $where_data instanceof Countable && $where_data instanceof Iterator )) && count($where_data) > 0) {
            if (!I2CE_ModuleFactory::instance()->isEnabled('form-limits')) {
                I2CE::raiseError("module form-limits is not available");
                return false;
            }
            $where_qry = $formObj->generateWhereClause($where_data);
            $fields = array_unique(array_merge($fields, $this->getLimitedFields($where_data)));
        }  else {
            $where_qry = '';
        }

        $sub_qry = $this->getRequiredFieldsQuery($form,$fields, null,$parent,$field_reference_callback, $mod_time, $user);
        if (!is_string($sub_qry) || strlen($sub_qry) == 0) { //this means no valid fields.  just die here.
            //I2CE::raiseError("Cannot get the fields " . implode(',',$fields) . " from $form ");
            return false;
        }
        if (!is_string($where_qry)) {
            I2CE::raiseError("Invalid where clause for $form ");
            return false;
        } 
        $where_qry = trim($where_qry);
        if (strlen($where_qry) > 0) {
            $where_qry = ' WHERE (' . $where_qry . ')';
        }
        $post_qry = '';
        if ( count($ordering) > 0) {
            $post_qry .= ' ORDER BY ' . implode (',', $ordering);
        }
        if ($limit === true) {
            $post_qry .= ' LIMIT 1';
        }  else if (is_int($limit) ||  (is_string($limit) && ctype_digit($limit))) {
            $post_qry .= ' LIMIT ' . $limit;
        } else if (is_array($limit) && count($limit) == 2 ) {
            $post_qry .= ' LIMIT ' . implode(' , ', $limit);
        }
        return   "$field_select FROM (" . $sub_qry . ") AS `$form` "  . $where_qry  . $post_qry;
    }











    /**
     * Gets the id's for the given child for this form.
     * @param string $form
     * @param   mixed $parent_form_id the prent form and id
     * @param  array/string: an optional orderBy array of fields
     * @param array  where
     * @param integer: A limit of the number of children ids to return
     * @return array
     */
    public function getIdsAsChild($form, $parent_form_id,$order_by, $where, $limit) {
        $idQry = $this->getFields($form, array('id'=>'id'), $parent_form_id,$where, $order_by, $limit);
        //if (!is_string($idQry) || strlen(ltrim($idQry)) == 0 || stripos( $idQry, 'select null as id' ) !== false ) {
        if (!is_string($idQry) || strlen(ltrim($idQry)) == 0 ) {
            return array();
        }
        try {
            $result = $this->db->query($idQry);
            $this->queryLastListCount( $form );
            $ids = array();
            while( $row = $result->fetch() ) {
                $ids[] = $row->id;
            }
            unset( $result );
            return $ids;
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error getting ids for form $form as a child of $parent_form_id:" );
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
        $qry =  $this->getFields( $form,  array('id'=>'id'), $parent, $where_data, $ordering, $limit);
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
        if ( !$qry ) {
            // The form may not exist yet so nothing to query so return false since nothing can be found.
            if ($limit_one) {
                return false;
            } else {
                return array();
            }
        }
        if (!$qry) {
            return parent::search($form,$parent,$where_data,$ordering,$limit);
        }
        try {
            $results = $this->db->query($qry);      
            $this->queryLastListCount( $form );
            if ($limit_one) {
                if (($data = $results->fetch())) {
                    unset( $results );
                    return $data->id;
                } else {
                    unset( $results );
                    return false;
                }
            } else {
                $res = array();
                while ( $data = $results->fetch() ) {
                    $res[] = $data->id;
                }
                unset( $results );
                return $res;
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Bad query -- $qry");
            return parent::search($form,$parent,$where_data,$ordering,$limit);
        }
    }




    /**
     * @param string $form
     * @param array $fields of string. The fields we want returned. 
     * Can include the special field 'last_modified' to get the last modification time for any of the fields of that form which is returned in the format  "Y-m-d H:i:s"
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
    public  function listFields($form, $fields, $parent = false , $where_data=array(), $ordering=array(), $limit = false, $mod_time = -1) {    
        $lookup_fields = $fields;
        if ( ($key = array_search('parent',$lookup_fields)) !== false) {
            unset($lookup_fields[$key]);
            if ($parent === false) {
                $parent = true;
            }
        } else {
            if ($parent === true) {
                $lookup_fields[] = 'parent';
                $fields[] = 'parent';
            }
        }
        $select_fields = array( 'id' =>'id');
        foreach ($lookup_fields as $field) {
            $select_fields[$field]=$field;
        }
        if ($parent === true) {
            $select_fields['parent'] = 'parent';
        }
        $qry =  $this->getFields($form, $select_fields,  $parent, $where_data, $ordering, $limit, null, $mod_time);
        if (!$qry) {
            return array();
        }
        try {
            $res = $this->db->query($qry);      
            $this->queryLastListCount( $form );
            $results = array();
            while ( $data = $res->fetch() ) {
                $data_vals = array();
                foreach ($fields as $field) {
                    if (isset($data->$field)) {
                        $data_vals[$field] = $data->$field;
                    } else {
                        $data_vals[$field] = null;
                    }
                }
                $results[$data->id] = $data_vals;
            }
            unset( $res );
            return $results;
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Bad query -- $qry");
            return array();
        }
    }



    /**
     * @param string $form
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
    public  function listDisplayFields($form, $fields, $parent = false , $where_data=array(), $ordering=array(), $limit = false, $mod_time = -1, $user = false ) {    
        $lookup_fields = $fields;
        if ( ($key = array_search('parent',$lookup_fields)) !== false) {
            unset($lookup_fields[$key]);
            if ($parent === false) {
                $parent = true;
            }
        } else {
            if ($parent === true) {
                $lookup_fields[] = 'parent';
                $fields[] = 'parent';
            }
        }
        $select_fields = array( 'id' =>'id');
        foreach ($lookup_fields as $field) {
            $select_fields[$field]=$field;
        }
        if ($parent === true) {
            $select_fields['parent'] = 'parent';
        }
        $qry = $this->getFields($form, $select_fields,  $parent, $where_data, $ordering, $limit, null, $mod_time, $user);
        if (!$qry) {
            return array();
        }
        try {
            $res = $this->db->query($qry);      
            $this->queryLastListCount( $form );
            $results = array();
            $fo = $this->getContainer($form);
            if (!$fo instanceof I2CE_Form) {
                I2CE::raiseError("Could not instantiate form $form");
                return array();
            }
            while ( $data = $res->fetch() ) {
                $data_vals = array();
                foreach ($fields as $field) {
                    $fieldObj = $fo->getField($field);
                    if (!$fieldObj instanceof I2CE_FormField) {
                        I2CE::raiseError("Could not get field $field");
                        continue;
                    }
                    if (isset($data->$field)) {                    
                        $fieldObj->setFromDB($data->$field);
                        $data_vals[$field] = $fieldObj->getDisplayValue();
                    } else {
                        $data_vals[$field] = null;
                    }
                }
                $results[$data->id] = $data_vals;
            }
            unset( $res );
            return $results;
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Bad query -- $qry");
            return array();
        }
    }

    /**
     * Query the last list count and set it in FormStorage
     * @param string $form The form name to set.
     */

    protected function queryLastListCount( $form ) {
        try {
            $num_rows = I2CE_PDO::getRow( "SELECT FOUND_ROWS() AS num_rows" );
            I2CE_FormStorage::setLastListCount( $form, (int)$num_rows->num_rows );
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Couldn't get total number of results." );
        }
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
