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
 *  I2CE_FormStorage_entry
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org> / Luke Duncan <lduncan@intrahealth.org>
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


class I2CE_FormStorage_entry extends I2CE_FormStorage_DB {

    /**
     * @var array A list of all prepared statements for working with entry data.
     */
    static protected $prepared;

    /**
     * @var array Keys are form names values are arrays with keys fields 
     * and values an array for the form field id and details
     */
    protected $form_field_data_cache;

    /**
     * @var array  Keys are form names values are their id's
     */
    protected $form_id_cache;
    /**
     * @var array  Keys are field names values are their id's
     */
    protected $field_id_cache;



    public function  __construct($name,$options) {
        parent::__construct($name,$options);
        if ( !is_array( self::$prepared ) ) {
            self::$prepared = array();
        }
        $this->form_field_data_cache = array();
        $this->form_id_cache = array();
        $this->field_id_cache = array();
    }

    /**
     * Checks to see if this storage mechansim implements the writing methods.
     */
    public function isWritable() {
        return true;
    }

    
    /**
     *  Construct a query (to be used as a sub-select) to view the fields of the given form.  It always will return the id of the form as well
     *  @param string $form
     *  @param mixed $fields.  Either a string, the field, or an array of string, the fields. Can also include the special field "last_modified" to get the last modification time on any of the fields
     *  @param mixed $id.  Defaults to null.  If non-null it is the id that we wish to limit to.
     *  @param boolean $parent. Defaults to false.  If true, we include the parent id as a referenced field
     *     If it is scalar and non-boolean, it is consider to be the ID of the parent, and then we get all forms with parent the given id.
     *  @param callback $field_refernece_callback.  A callback function whose first arguement is the form, the second arguements
     *     is the field and which returns the way the field value should be references as a field.  If the callback is null (the default) then
     *     the reference used is "$form+$field"
     *  @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *     time greater than or equal to $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.  
     * @param mixed $user The user id of the user to limit the results to so it only returns results limited to that user.  This can be a single value or an array
     *  @returns string the query or false on failed.
     */
    public function getRequiredFieldsQuery($form,$fields, $id = null, $parent = false, $field_reference_callback = null, $mod_time = -1, $user=false) {
        return $this->_getRequiredFieldsQuery($form,$fields,$id,$parent,$field_reference_callback,$mod_time,true,$user);
    }
    /**
     *  Worker method to construct a query (to be used as a sub-select) to view the fields of the given form.  It always will return the id of the form as well
     *  @param string $form
     *  @param mixed $fields.  Either a string, the field, or an array of string, the fields. Can also include the special field "last_modified" to get the last modification time on any of the fields
     *  @param mixed $id.  Defaults to null.  If non-null it is the id that we wish to limit to.
     *  @param boolean $parent. Defaults to false.  If true, we include the parent id as a referenced field
     *     If it is scalar and non-boolean, it is consider to be the ID of the parent, and then we get all forms with parent the given id.
     *  @param callback $field_refernece_callback.  A callback function whose first arguement is the form, the second arguements
     *     is the field and which returns the way the field value should be references as a field.  If the callback is null (the default) then
     *     the reference used is "$form+$field"
     *  @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *     time greater than or equal to $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.  
     *  @param boolean $last_entry.  Detaults to true.  Set to false to construct query on entry table
     * @param mixed $user The user id of the user to limit the results to so it only returns results limited to that user.  This can be a single value or an array
     *  @returns string the query or false on failed.
     */
    protected function _getRequiredFieldsQuery($form,$fields, $id = null, $parent = false, $field_reference_callback = null, $mod_time = -1, $last_entry = true, $user=false) {
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
        if ($field_reference_callback !== null) {
            if ( !is_string($ref = call_user_func($field_reference_callback, $form,'id'))) {
                I2CE::raiseError("Invalid id reference  callback function:\nid --> $ref");
                return false;
            }
        } else {
            $ref = '`' . $form . '+id`';
        }                
        $form_id = $this->getFormId($form, true);
        if ($form_id == 0) {
            //this form has not been created want to select NULL for everything
            $select_list= array("NULL as $ref");
            $joins = array( 'record r' );
            $wheres = array( 'r.form = 0' );
        } else {
            $select_list = array( "r.id AS $ref" );
            $joins = array('record r');
            $wheres = array("( r.form = $form_id )");
        }

        if ($parent !== false || in_array('parent',$fields)) {
            //if (true) {
            if ($field_reference_callback !== null) {
                if ( !is_string($ref = call_user_func($field_reference_callback, $form,'parent'))) {
                    I2CE::raiseError("Invalid parent reference callback function:\nparent --> $ref");
                    return false;
                }
            } else {
                $ref = '`' . $form . '+parent`';
            }        
            if ($form_id == 0) {
                $select_list[] = "NULL as $ref";
            } else {
                $select_list[] = "CONCAT(r.parent_form,'|',r.parent_id)  AS $ref";
                if (!is_bool($parent) && is_scalar($parent)) {
                    list($parent_form, $parent_id) = array_pad( explode('|',$parent,2),2,'');
                    $wheres[] = "(r.parent_form = ". $this->db->quote($parent_form) . " AND r.parent_id = " . $this->db->quote($parent_id) . ")";
                    //$wheres[] = "(CONCAT(r.parent_form,'|',r.parent_id) = '$parent')";
                }
            }
        }
        //we will construct the select, e.g.:  select id from e.record as id from last_entry e JOIN last_entry e_name ON e_name.record = e.record ...
        if ( is_array( $mod_time ) && array_key_exists('mod_time',$mod_time) ) {
            $mod_time['mod_time'] = date('Y-m-d H:i:s' , $mod_time['mod_time'] );
            $get_mod_time = true;
        } elseif (is_scalar($mod_time) && $mod_time >= 0) {
            $mod_time = date('Y-m-d H:i:s' , $mod_time);
            $get_mod_time = true;
        } else {
            $mod_time = false;
            $get_mod_time = true;
        }
        $time_wheres = array();
        $get_created = false;
        foreach ($fields as $field) {
            if ($field == 'id' || $field =='parent') {
                continue;
            }
            if ( $field == 'last_modified' ) {
                $get_mod_time = true;
                continue;
            }
            if ( $field == 'created' ) {
                $get_created = true;
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
            $tbl = '`e_' . $field .'`';
            $details = $this->getFormFieldIdAndType($form,$field);
            if ($details == null || $form_id == 0) {
                //this can happen if the form field has never been saved.
                if ($ref !== 'NULL') {
                    $select_list[] = ' NULL AS ' . $ref . ' ' ;
                }
            } else {
                $select_list[] = ' ' . $tbl . '.`' . $details['type'] . '_value` AS ' . $ref . ' ' ;
                if ( is_array( $mod_time ) && in_array( $field, $mod_time['fields'] ) ) {
                    $time_wheres[] = '(' . $tbl . ".`date` >= '" . $mod_time['mod_time'] . "' )";
                }
                if ($last_entry) {
                    $joins[] =  ' last_entry ' .  $tbl .' ON ' . $tbl . ".record = r.id AND $tbl.form_field = {$details['id']} ";
                } else {
                    $joins[] =  ' entry ' .  $tbl .' ON ' . $tbl . ".record = r.id AND $tbl.form_field = {$details['id']} ";
                }
            }
        }
        if ($get_mod_time) {
            if ($field_reference_callback !== null) {
                if ( !is_string($ref = call_user_func($field_reference_callback, $form,'last_modified'))) {
                    I2CE::raiseError("Invalid parent reference callback function:\nlast_modified --> $ref");
                    return false;
                }
            } else {
                $ref = '`' . $form . '+last_modified`';
            }        
            if ($form_id != 0) {
                $select_list[] = "r.last_modified AS $ref";
            } else {
                $select_list[] = "NULL AS $ref";
            }
        }
        if ($get_created) {
            if ($field_reference_callback !== null) {
                if ( !is_string($ref = call_user_func($field_reference_callback, $form,'created'))) {
                    I2CE::raiseError("Invalid parent reference callback function:\ncreated --> $ref");
                    return false;
                }
            } else {
                $ref = '`' . $form . '+created`';
            }        
            if ($form_id != 0) {
                $select_list[] = "r.created AS $ref";
            } else {
                $select_list[] = "NULL AS $ref";
            }
        }

        $qry = ' SELECT '.  implode( ", ", $select_list );
        if (count($joins) > 0) {
            $qry .= " FROM "  . implode( " LEFT JOIN ", $joins ) ;
        } else {
            $qry .= " FROM last_entry "; // we are sellecting NULL for all fields.  this isn't really happening in last_entry table, but we need  table name so the qry won't fail
        }
        if (is_scalar($id) && $form_id !=0) {
            if (!is_int($id) && ! (is_string($id) && strlen($id) >0 && ctype_digit($id))) {
                I2CE::raiseError("Invalid $id which is invalid for record table");
                return false;
            }
            $wheres[] = "( r.id = $id )";
        }
        $user_time_check = "";
        if ( $form_id !=0 && $mod_time && !is_array( $mod_time ) ) {
            $time_wheres[] = "r.last_modified >= '$mod_time'";
            $user_time_check = " AND last_modified >= '$mod_time' ";
        }
        if (count($time_wheres) > 0) {  //should be the same as if ($mod_time).
            $wheres[] = '(' . implode( ' OR ' , $time_wheres) . ')';
        }
        if ( $form_id != 0 &&  $user !== false ) {
            if ( is_array($user) ) {
                $wheres[] = " r.id IN ( SELECT record FROM last_entry WHERE record = r.id $user_time_check AND who IN ( " . implode( ',', $user ) . " ) ) ";
            } else {
                $wheres[] = " r.id IN ( SELECT record FROM last_entry WHERE record = r.id $user_time_check AND who = $user ) ";
            }
        }
        if (count($wheres) > 0) {
            $qry .=  " WHERE (" . implode(" AND ", $wheres) . ")"  ;
        }
        return $qry;
    }





    /**
     * Generates a SQL to select the required fields.
     * @param string $form The form we are select from
     * @param array $fields of string.  the fields that we want to select.  the keys are the fields names, the values are what we wish to select them as.
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
        $ref_fields = array();
        $ord_ref_fields = array();
        if (!is_array($ordering)) {
            $ordering = array(); //just to be sage
        }
        $limit_fields = $this->getLimitedFields($where_data);
        $orders = array();
        foreach ($ordering as $ord) {
            if (!is_string($ord) || strlen($ord) == 0) {
                continue;
            }
            if ($ord[0] == '-') {
                $orders[ substr($ord,1)] = false;
            } else {
                $orders[$ord] = true;
            }
        }        
        $all_fields = array_unique(array_merge(array_keys($fields),$limit_fields, array_keys($orders)));
        foreach ($all_fields as $field) {
            if ($field_reference_callback !== null) {
                if ( !is_string($ref = call_user_func($field_reference_callback, $form,$field)) === false){ 
                    I2CE::raiseError("Invalid field reference callback function");
                    return false;
                }
            } else {
                $ref = '`' . $form . '+' . $field . '`';
            }

            if ($field == 'id') {
                if (array_key_exists($field,$fields)) {
                    $ref_fields[] = "id AS `{$fields[$field]}`";
                }
                if (array_key_exists('id',$orders)) {
                    $ord_ref_fields['id'] = "`id`";
                }
            } else if ($field == 'parent') {
                if (array_key_exists($field,$fields)) {
                    $ref_fields[] = "parent AS `{$fields[$field]}`";
                }
                if (array_key_exists('parent',$orders)) {
                    $ord_ref_fields['parent'] = "`parent`";
                }
            } else if ($field == 'last_modified') {
                if ( array_key_exists( $field, $fields ) ) {
                    $ref_fields[] = "last_modified AS `{$fields[$field]}`";
                }
                if (array_key_exists('last_modified',$orders)) {
                    $ord_ref_fields['last_modified'] = "`last_modified`";
                }
            } else if ($field == 'created') {
                if ( array_key_exists( $field, $fields ) ) {
                    $ref_fields[] = "created AS `{$fields[$field]}`";
                }
                if (array_key_exists('created',$orders)) {
                    $ord_ref_fields['created'] = "`created`";
                }
            } else {
                $details = $this->getFormFieldIdAndType($form,$field);
                if (!is_array($details) || !array_key_exists('type',$details)) {
                    // This should be rare but could happen if no data has been saved yet.
                    if (array_key_exists($field,$fields)) {
                        $ref_fields[] = "NULL AS `{$fields[$field]}`";
                    }                     
                } else {
                    if (array_key_exists($field,$fields)) {
                        //$ref_fields[] = " `e_$field`.`{$details['type']}_value` AS `{$fields[$field]}`";
                        $ref_fields[] = " `{$fields[$field]}`";
                    }                     
                    if (array_key_exists($field,$orders)) {
                        $ord_ref_fields[$field] = "`e_$field`.`{$details['type']}_value`"; 
                    }
                }
            }
        }
        if ( ! ($internal_reference_callback =$this->generateReferenceCallback_1($form,$all_fields))) {
            return false;
        }        
        if ( ! ($internal_reference_callback2 =$this->generateReferenceCallback_2($form,$all_fields))) {
            return false;
        }        



        $ordered = array();
        foreach ($orders as $field=>$asc) {
            if (!array_key_exists($field,$ord_ref_fields)) {
                continue;
            }
            if ($asc) {
                //$ordered[] = $ord_ref_fields[$field]. ' ASC';
                $ordered[] = $field. ' ASC';
            } else {
                //$ordered[] = $ord_ref_fields[$field]. ' DESC';
                $ordered[] = $field. ' DESC';
            }
        }

        $field_select = "SELECT SQL_CALC_FOUND_ROWS " . implode(",",$ref_fields);
        if ((is_array($where_data) || ($where_data instanceof ArrayAccess && $where_data instanceof Countable && $where_data instanceof Iterator )) && count($where_data) > 0) {
            if (!I2CE_ModuleFactory::instance()->isEnabled('form-limits')) {
                I2CE::raiseError("module form-limits is not available");
                return false;
            }
            $formObj = I2CE_FormFactory::instance()->createContainer($form);
            if (!$formObj instanceof I2CE_Form) {
                I2CE::raiseError("Could not instantiate $form");
                return false;
            }       
            $where_qry = $formObj->generateWhereClause($where_data, $internal_reference_callback2);
            $fields = $all_fields;
            $formObj->cleanup();
            unset( $formObj );
        }  else {
            $where_qry = '';
        }
        $sub_qry = $this->getRequiredFieldsQuery($form,$all_fields, null,$parent,$internal_reference_callback, $mod_time, $user);
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
            if ( stripos( $sub_qry, 'WHERE' ) !== false ) {
                $where_add = ' AND ';
            } else {
                $where_add = ' WHERE ';
            }
            $where_qry = $where_add . '(' . $where_qry . ') ';
        }
        $ord_qry = '';
        if ( count($ordered) > 0) {
            $ord_qry .= ' ORDER BY ' . implode (',', $ordered);
        }
        $limit_qry = '';
        if ($limit === true) {
            $limit_qry .= ' LIMIT 1';
        }  else if (is_int($limit) ||  (is_string($limit) && ctype_digit($limit))) {
            $limit_qry .= ' LIMIT ' . $limit;
        } else if (is_array($limit) && count($limit) == 2 ) {
            $limit_qry .= ' LIMIT ' . implode(' , ', $limit);
        }
        return   "$field_select FROM (" . $sub_qry . $where_qry . ") AS `$form` "  .   $ord_qry . $limit_qry;
    }




    protected $get_form_id_qry = null;


    /**
     * Returns the id from the form table in the database.
     * 
     * If the form doesn't currently exist then it will be created and the new id will be returned.
     * @param string $form The name of the form.
     * @param boolean $nocreate Set to true if the form shouldn't be created if it doesn't exist.
     * @return integer
     */
    protected function getFormId( $form, $nocreate = false ) {
        if (!$this->get_form_id_qry) {
            try {
                $this->get_form_id_qry = $this->db->prepare( "SELECT id FROM form WHERE name = ?" ); 
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to prepare form ID query." );
                return 0;
            }
        }
        if (array_key_exists($form,$this->form_id_cache)) {
            return $this->form_id_cache[$form];
        }
        try {
            $this->get_form_id_qry->execute(array( $form ));
            $row = $this->get_form_id_qry->fetch();
            $this->get_form_id_qry->closeCursor();
            if ( $row !== false ) {
                $this->form_id_cache[$form] = $row->id;
                return $row->id;
            } elseif ( !$nocreate ) {
                $field_values = array( $form_id, $form );
                if ( !$this->prepareFormIDGetStatement() ) {
                    I2CE::raiseError( "Unable to setup form id insert query!" );
                }

                self::$prepared['formIDGet']->execute($field_values);
                $form_id = $this->db->lastInsertId();
                $this->form_id_cache[$form] = $form_id;
                return $form_id;
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error getting form id." );
        }
        return 0;
    }

    protected $get_field_id_prep = null;
        
    /**
     * Return the id for the field in the field table.
     *
     * If the field doesn't currently exist then it will be created.
     * @param string $name The name of this field.
     * @param string $type The FormField type of this field.
     * @return integer
     */
    protected function getFieldId( $name, $type ) {
        if (array_key_exists($name,$this->field_id_cache)) {
            return $this->field_id_cache[$name];
        }
        try {
            $row = I2CE_PDO::getRow( "SELECT id FROM field WHERE name = ? AND type = ?", array( $name, $type ) ); 
            if ( $row !== false ) {
                $this->field_id_cache[$name] = $row->id;
                return $row->id;
            } else {
                $field_values = array( $name, $type );
                I2CE_PDO::execParam( "INSERT INTO field ( id, name, type ) VALUES ( 0, ?, ? )", $field_values );
                $field_id = $this->db->lastInsertId();
                I2CE::raiseMessage("Adding field id for $name $type is $field_id");
                $this->field_id_cache[$name] = $field_id;
                return $field_id;
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error getting field id:" );
        }
        I2CE::raiseError("Failed getting field id for field $name"); 
        return null;
    }




    /**
     * Release any resourced held by this form storage mechanism for the indicated form
     * @param string $form
     */
    public function release($form) {
        if (!is_string($form)) {
            return;
        }
        /* 
         * Commenting this out because it causes this data to be re-queried when the form is created and released
         * frequently.  This isn't much data per form so there shouldn't be any memory issues and there are
         * database issues when this is done frequently for data import or migrations.
         *
        if (array_key_exists($form,$this->form_field_data_cache)) {
            unset($this->form_field_data_cache[$form]);
        }        
        if (array_key_exists($form,$this->form_id_cache)) {
            unset($this->form_id_cache[$form]);
        }        
*/

        parent::release($form);

    }

    protected $ff_id_type_prep =null;

    /**
     * Return the form field id for the given form and field and the data type for that field.
     * @param string $form The name of the form.
     * @param string $field The name of the field.
     * @return array or null on failure
     */
    protected function getFormFieldIdAndType( $form_name, $field_name ) {
        if (!array_key_exists($form_name, $this->form_field_data_cache) || !is_array($this->form_field_data_cache[$form_name])) {
            $this->form_field_data_cache[$form_name] = array();
        }
        if (array_key_exists($field_name, $this->form_field_data_cache[$form_name]) && is_array($this->form_field_data_cache[$form_name][$field_name])) {
            return $this->form_field_data_cache[$form_name][$field_name];
        }
        if ($this->ff_id_type_prep === null) {
            try {
                $this->ff_id_type_prep =                    
                    $this->db->prepare(             
                        "SELECT ff.id AS id,field.type AS type FROM form_field ff JOIN field ON field.id = ff.field JOIN form ON form.id = ff.form WHERE form.name = ? AND field.name = ?"
                        );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Could not prepare ff id and type" );
                $this->ff_id_type_prep = null;
                return null;
            }
        }
//        $row = $this->db->getRow( "SELECT ff.id,field.type FROM form_field ff JOIN field ON field.id = ff.field JOIN form ON form.id = ff.form WHERE form.name = ? AND field.name = ?",
//                array('integer', 'text'), array( $form_name, $field_name ), array( 'text', 'text' ) );

        try {
            $this->ff_id_type_prep->execute(array( $form_name, $field_name ));
            $data = $this->ff_id_type_prep->fetch(PDO::FETCH_ASSOC);
            $this->ff_id_type_prep->closeCursor();
        
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error executing query to get field id and type:" );
            return null;
        }

        $this->form_field_data_cache[$form_name][$field_name] = $data;
        return $data;
    }


    /**  
     * Return an array of DB details for a given field to be used to build queries
     * for that field.
     *
     * @param string $form The form.
     * @param string $field The field being looked up.
     * @param integer $field_count If you're building a query with multiple fields
     *                             this is the count for the table alias.
     * @param string $table_prefix The table alias prefix to go along with the field count.
     * @param string $table The table to do the lookups on.  This will most often be last_entry
     *                      but in some cases you may want to use entry instead.
     * @param string $join_field The join field to use for the table's record field to join on.
     *                           By default it will use the first last_entry (e1) record field.
     * @return array
     */
    protected function fieldDetails( $form, $field, $field_count = 1, 
        $table_prefix = "e", $table = "last_entry", $join_field = null ) {

        $details = $this->getFormFieldIdAndType( $form, $field );
        if ( $details == null ) {
            // This should be rare but could happen if no data has been saved yet.
            I2CE::raiseError( "Invalid form and field. $form $field", E_USER_ERROR );
            return null;
        } else {
            $sql = array();
            $e = $table_prefix . $field_count;
            $formObj = I2CE_FormFactory::instance()->createContainer( $form );
            $sql['field_type'] = $details['type'];
            $sql['field'] =  $e . "." . $details['type'] . "_value";
            $sql['where'] = $e . ".form_field = ?";
            $sql['param'] = $details['id'];
            $sql['type'] = 'integer';
            if ( $field_count > 1 || $join_field !== null ) {
                if ( $join_field === null ) {
                    $join_field = $table_prefix . "1.record";
                }
                $sql['from'] = "$table $e ON $e.record = " . $join_field;
            } else {
                $sql['from'] = "$table $e";
            }
            return $sql;
        }
    }    





    /**
     * Populates the form and field ids for all the fields for this form.
     *
     * If the form or fields don't exist yet they will be created.
     * @param I2CE_Form $form
     */
    protected function setupForm( $form ) {
        if ( $form->hasStaticAttribute( "DBEntry_form_id" ) && $form->getStaticAttribute( "DBEntry_form_id" ) > 0 ) {
            return;
        }

        $form_id = $this->getFormId( $form->getName() );
        $form->setStaticAttribute( "DBEntry_form_id", $form_id );

        $form_fields = array();
        if ( !$this->prepareSetupFormStatement( "select" ) ) {
            I2CE::raiseError( "Unable to setup form select query!" );
        }
        try {
            self::$prepared['setupForm']['select']->execute( array( $form_id ) );
            while( $row = self::$prepared['setupForm']['select']->fetch() ) {
                $form_fields[ $row->field ] = $row->id;
            }
            self::$prepared['setupForm']['select']->closeCursor();
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error setting up form in the database:" );
            return;
        }

        foreach( $form as $key => $field ) {
            if (!$field instanceof I2CE_FormField) {
                I2CE::raiseError("Bad field $key in form " . $form->getName());
                continue;
            }
            if ( !$field->isInDB() ) {
                continue;
            }
            $field_id = $this->getFieldId( $field->getName(), $field->getTypeString() );
            if ($field_id == null) {
                I2CE::raiseError("Bad field id for $field");
                continue;
            }
            $form_field_id = 0;
            if ( array_key_exists( $field_id, $form_fields ) ) {
                $form_field_id = $form_fields[$field_id];
            } else {
                /*
                 * There can only be one field name assigned to a given form.  If through an upgrade the
                 * field type changes this can cause issues with the form_field table.
                 * So, we delete any other rows in form_field that have the same field name as the one
                 * we're adding before adding the new one.
                 */
                if ( !$this->prepareSetupFormStatement( "delete" ) ) {
                    I2CE::raiseError( "Unable to setup form delete query!" );
                }
                if ( !$this->prepareFormIDInsertStatement() ) {
                    I2CE::raiseError( "Unable to setup form id insert query!" );
                }

                try {
                    self::$prepared['setupForm']['delete']->execute( array( $form_id, $field->getName() ) );
                    $deleted = self::$prepared['setupForm']['delete']->rowCount();
                    if ( is_numeric( $deleted ) && $deleted > 0 ) {
                        I2CE::raiseError( "Deleted extra $deleted row(s) from form_field to block field name collisions. Form: $form_id Field: " . $field->getName() . " Field Type: " . $field->getTypeString() . " Field Class: " . get_class( $field ) );
                    } 
                } catch ( PDOException $e ) {
                    I2CE::pdoError( $e, "Tried to delete invalid rows from form_field:" );
                }
                
                $field_values = array( $form_id, $field_id );
                
                try {
                    self::$prepared['formIDInsert']->execute($field_values);
                    $form_field_id = $this->db->lastInsertId();
                } catch ( PDOException $e ) {
                    I2CE::pdoError( $e, "Error setting up form field in the database:" );
                }
            }
            $field->setStaticAttribute( "DBEntry_field_id", $field_id );
            $field->setStaticAttribute( "DBEntry_form_field_id", $form_field_id );
        }
    }
 

    /**
     * Prepare the save statement for the given field type.
     *
     * @param string $field_type_db
     * @param string $field_type_string
     * @return boolean
     */
    protected function prepareSaveStatement( $field_type_db, $field_type_string ) {
        if ( !array_key_exists( 'save', self::$prepared ) || !is_array( self::$prepared['save'] ) ) {
            self::$prepared['save'] = array();
        }
        if ( !isset( self::$prepared['save'][$field_type_db][$field_type_string] ) ) {
            try {
                if ( $field_type_db == "save_entry" ) {
                    self::$prepared['save'][$field_type_db][$field_type_string] = $this->db->prepare( "INSERT INTO entry SELECT * FROM last_entry WHERE record = ? AND form_field = ?" ); 
                } else {
                    self::$prepared['save'][$field_type_db][$field_type_string] = $this->db->prepare( "REPLACE INTO last_entry ( record, form_field, date, who, change_type, {$field_type_string}_value ) VALUES ( ?, ?, NOW(), ?, ?, ? )" );
                }
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error preparing entry insert of type ({$field_type_db}/{$field_type_string}):" );
                return false;
            }
        }
        return true;
    }

    /**
     * Prepare the record table save statements for insert or update.
     *
     * @param string $type
     * @return boolean
     */
    protected function prepareRecordStatement( $type ) {
        if ( !array_key_exists( 'record', self::$prepared ) || !is_array( self::$prepared['record'] ) ) {
            self::$prepared['record'] = array();
        }
        if ( !isset( self::$prepared['record'][$type] ) ) {
            try {
                if ( $type == "insert" ) {
                    self::$prepared['record'][$type] = $this->db->prepare( "INSERT INTO record ( id, last_modified, form, parent_form, parent_id,created ) VALUES ( ?, NOW(), ?, ?, ?, NOW() )" );
                } elseif ($type == "lookupdbform") {
                    self::$prepared['record'][$type] = 
                        $this->db->prepare( "SELECT form FROM record WHERE id = ?" );

                } elseif ( $type == "update" ) {
                    self::$prepared['record'][$type] = $this->db->prepare( 
                            "INSERT INTO record (parent_form,parent_id,last_modified,created,id,form) VALUES (?,?,NOW(),NOW(),?,?) "
                            . "ON DUPLICATE KEY UPDATE "
                            . "  parent_form = VALUES(parent_form)   "
                            . "  ,parent_id = VALUES(parent_id)   "
                            . "  ,parent_id = VALUES(parent_id)   "
                            . "  ,last_modified = VALUES(last_modified)   " );
                } elseif ( $type == "updatetime" ) { 
                    self::$prepared['record'][$type] = $this->db->prepare( "UPDATE record SET last_modified = ? WHERE id = ?" );
                }

            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error in preparing save record $type statement:" );
                return false;
            }
        }
        return true;
    }

    /**
     * Prepare the setupForm statements for select and delete.
     *
     * @param string $type
     * @return boolean
     */
    protected function prepareFormIDInsertStatement( ) {
        if ( !array_key_exists( 'formIDInsert', self::$prepared ) || !isset( self::$prepared['formIDInsert'] ) ) {
            try {
                self::$prepared['formIDInsert'] = $this->db->prepare( "INSERT INTO form_field ( id,  form,  field ) VALUES ( 0, ?, ? )" );
            } catch  ( PDOException $e ) {
                I2CE::pdoError( $e, "Error in preparing form id insert statement:" );
                return false;
            }
        }
        return true;
    }

    /**
     * Prepare the setupForm statements for select and delete.
     *
     * @param string $type
     * @return boolean
     */
    protected function prepareFormIDGetStatement( ) {


        if ( !array_key_exists( 'formIDGet', self::$prepared ) || !isset( self::$prepared['formIDGet'] ) ) {
            try {
                self::$prepared['formIDGet'] = $this->db->prepare( "INSERT INTO form ( id,  name ) VALUES ( ?,? )" );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error in preparing form id get statement:" );
                return false;
            }
        }
        return true;
    }

    /**
     * Prepare the setupForm statements for select and delete.
     *
     * @param string $type
     * @return boolean
     */
    protected function prepareSetupFormStatement( $type ) {                
        if ( !array_key_exists( 'setupForm', self::$prepared ) || !is_array( self::$prepared['setupForm'] ) ) {
            self::$prepared['setupForm'] = array();
        }
        if ( !isset( self::$prepared['setupForm'][$type] ) ) {
            try {
                if ( $type == "select" ) {
                    self::$prepared['setupForm'][$type] = $this->db->prepare( 
                            "SELECT id,field FROM form_field WHERE form = ?" );
                } elseif ( $type == "delete" ) {
                    self::$prepared['setupForm'][$type] = $this->db->prepare( 
                            "DELETE FROM form_field WHERE form = ? AND field IN (SELECT ID FROM field WHERE name = ? )" );
                }

            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error in preparing setupForm $type statement:" );
                return false;
            }
        }
        return true;
    }




    /**
     * Prepare the check statements for the given field type
     *
     * @param string $type_string
     * @return boolean
     */
    protected function prepareCheckStatement($type_string) {
        if ( !array_key_exists( 'check', self::$prepared ) || !is_array( self::$prepared['check'] ) ) {
            self::$prepared['check'] = array();
        }
        if ( !isset( self::$prepared['check'][$type_string] ) ) {
            try {
                self::$prepared['check'][$type_string] = $this->db->prepare( "SELECT " . $type_string 
                    . "_value AS value FROM last_entry WHERE record = ? AND form_field = ?" );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error preparing string duplicate check:" );
                return false;
            }
        }
        return true;
    }

    /**
     * Prepare the delete statements for records and entries.
     *
     * @param $delete_type The type of delete statement to prepare.
     * @return boolean
     */
    protected function prepareDeleteStatement( $delete_type ) {
        if ( !array_key_exists( 'delete', self::$prepared ) || !is_array( self::$prepared['delete'] ) ) {
            self::$prepared['delete'] = array();
        }
        if ( !isset( self::$prepared['delete'][$delete_type] ) ) {
            try {
                switch( $delete_type ) {
                    case "record" :
                        self::$prepared['delete'][$delete_type] = $this->db->prepare( "DELETE FROM record WHERE id = ?" ); 
                        break;
                    case "entry" :
                    case "last_entry" :
                        self::$prepared['delete'][$delete_type] = $this->db->prepare( "DELETE FROM $delete_type WHERE record = ?" );
                        break;
                    default :
                        I2CE::raiseMessage("Unable to prepare delete statement for $delete_type.");
                        return false;
                }
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error preparing delete statement $delete_type:" );
                return false;
            }
        }
        return true;
    }






    /**
     * Populates the history of each field given to the method.
     * @param I2CE_Form $form
     */
    public function populateHistory( $form ) {
        $this->setupForm( $form );
        parent::populateHistory($form);
    }


    /**
     * Updates time stamp on given object
     * @param I2CE_Form $form
     * @param int $timestamp. Unix timestamp
     * @returns boolean. true on success
     */
    public function updateTimeStamp($form, $timestamp ) {
        if ( !$this->prepareRecordStatement( "updatetime" ) ) {
            return false;
        }
        $datetime = I2CE_Date::now( I2CE_Date::DATE_TIME, $timestamp )->dbFormat();
        try {
            self::$prepared['record']['updatetime']->execute( array($datetime,$form->getId()));
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error updating record timestamp:" );
            return false;
        }
        return true;
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
        }
        if ( $transact && !$this->db->inTransaction() ) {
            try {
                $this->db->beginTransaction();
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Tried to create transaction, but not supported." );
                $transact = false;
            }
        } else {
            $transact = false;
        }
        $this->setupForm( $form );

        $do_check = false;
        if ( $form->getId() == '0' ) {
            if ( !$this->prepareRecordStatement( "insert" ) ) {
                if ( $transact && $this->db->inTransaction() ) { 
                    $this->db->rollback();
                }
                return false;
            }
            try {
                self::$prepared['record']['insert']->execute( 
                array( 0, $form->getStaticAttribute( "DBEntry_form_id" ), 
                    $form->getParentForm(), $form->getParentID() ) );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error saving record:" );
                if ($transact && $this->db->inTransaction() ) { 
                    $this->db->rollback();
                }
                return false;
            }
            $new_id = $this->db->lastInsertId();
            $form->setId( $new_id );
            $form->setChangeType( I2CE_FormStorage_Mechanism::CHANGE_INITIAL );
        } else {
            $db_form_id = $form->getStaticAttribute( "DBEntry_form_id" );
            if ( !$this->prepareRecordStatement( "lookupdbform" ) ) {
                if ( $transact && $this->db->inTransaction() ) { 
                    $this->db->rollback();
                }
                return false;
            }            
            try {
                self::$prepared['record']['lookupdbform']->execute( array(  $form->getId() ));
                if ( is_array($row = self::$prepared['record']['lookupdbform']->fetch(PDO::FETCH_ASSOC))
                        && array_key_exists('form',$row)
                        && $row['form']
                        && $db_form_id != $row['form']) {
                    I2CE::raiseError("Internal consitency error.  Record " . $form->getID() . " has mismatch of form references: " . $db_form_id . " != " . $row['form']);
                    return false;
                }
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error in lookup of record form type:" );
                if ($transact && $this->db->inTransaction()) { 
                    $this->db->rollback();
                }
                return false;
            }
            if ( !$this->prepareRecordStatement( "update" ) ) {
                if ( $transact && $this->db->inTransaction() ) { 
                    $this->db->rollback();
                }
                return false;
            }

            try {
                self::$prepared['record']['update']->execute( 
                        array( $form->getParentForm() , $form->getParentID(), 
                            $form->getId() , $db_form_id ));
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error updating record:" );
                if ($transact && $this->db->inTransaction()) { 
                    $this->db->rollback();
                }
                return false;
            }
            $do_check = true;
        }       
        foreach( $form as $key => $field ) {
            if ( !$field->isInDB() ) {
                continue;
            }
            $change_type = $form->getAttribute( "change_type_default" );
            if ( $form->hasAttribute( "change_type_" . $key ) ) {
                $change_type = $form->getAttribute( "change_type_" . $key );
            }
            $field->setStaticAttribute( "DBEntry_change_type", $change_type );
            if ( !$field->save( $do_check, $user ) ) {
                if ($transact && $this->db->inTransaction()) { 
                    $this->db->rollback();
                }
                return false;
            }
        }

        if ( $transact && $this->db->inTransaction() ) {
            $res = $this->db->commit();
            if ( $res ) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Deletes a list of records from the database from a list of child form ids and
     * a list of form ids.
     * Any records that are deleted will also delete all child forms.
     * For example, you can pass a list of demographic forms with a parent of person and it will 
     * delete the person records and all children.  You can also simply pass the list of 
     * person records.  The ids shouldn't have the form name included.
     * @param array $records The list of records to delete
     * @param array $children The list of children records to delete the parents
     * @param boolean $calculate Only calculate how many records to delete, but don't delete anything
     * @param boolean $transact a flag to use transacations or not
     * @return mixed
     */
    public static function massDelete( $records, $children, $calculate=true, $transact=true ) {
        $db = I2CE::PDO();
        $record_table = "record_list_" . uniqid();
        $child_table = "child_list_" . uniqid();

        $parents = 0;
        $directs = 0;
        if ( count($children) > 0 ) {
            try {
                $parents = $db->exec( "CREATE TEMPORARY TABLE IF NOT EXISTS $record_table (id INT PRIMARY KEY) SELECT parent_id AS id FROM record WHERE id IN ( " . implode( ',', $children ) . ")" );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error massDelete: " );
                return false;
            }
        }
        if ( count($records) > 0 ) {
            try {
                $directs = $db->exec( "CREATE TEMPORARY TABLE IF NOT EXISTS $record_table (id INT PRIMARY KEY) SELECT id FROM record WHERE id IN ( " . implode( ',', $records ) . " )");
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error massDelete: " );
                return false;
            }
        }

        $result = $directs + $parents;
        if ( $result == 0 ) {
            I2CE::raiseError("Nothing to delete for mass delete.");
            return true;
        }
        $failsafe = 0;
        while ( $result > 0 ) {
            $failsafe++;
            if ($failsafe > 50 ) {
                I2CE::raiseError( "Failsafe for mass deletion hit 50 so that's way too deep so ending!");
                return false;
            }

            try {
                $result = $db->exec( "CREATE TEMPORARY TABLE IF NOT EXISTS $child_table (id INT PRIMARY KEY) IGNORE SELECT id FROM record WHERE parent_id IN (SELECT id FROM $record_table)");
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error massDelete: " );
                return false;
            }
            try {
                $result = $db->exec( "INSERT IGNORE INTO $record_table SELECT * from $child_table" );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error massDelete: " );
                return false;
            }
        }
        try {
            $result = I2CE_PDO::getRow("SELECT COUNT(*) AS total FROM $record_table");
            if ( $calculate ) {
                return $result->total;
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError($e, "Error getting count to delete: " );
            return false;
        }

        // Now we have all the necessary records to be deleted in $record_table so back them up and delete them.
        if ( $transact && !$db->inTransaction() ) {
            try {
                $db->beginTransaction();
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Tried to create transaction, but not supported." );
                $transact = false;
            }
        } else {
            $transact = false;
        }
        try {
            $result = $db->exec("CREATE TABLE IF NOT EXISTS deleted_entry IGNORE SELECT * FROM entry WHERE record IN (SELECT id FROM $record_table)" );
            $result = $db->exec("CREATE TABLE IF NOT EXISTS deleted_last_entry IGNORE SELECT * FROM last_entry WHERE record IN (SELECT id FROM $record_table)" );
            $result = $db->exec("INSERT IGNORE INTO deleted_record SELECT * FROM record WHERE id IN (SELECT id FROM $record_table)" );
            $deleted_records = $db->exec("DELETE FROM record WHERE id IN (SELECT id FROM $record_table)");
            $result = $db->exec("DELETE FROM last_entry WHERE record IN (SELECT id FROM $record_table)");
            $result = $db->exec("DELETE FROM entry WHERE record IN (SELECT id FROM $record_table)");
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error on mass delete: " );
            if ( $transact && $db->inTransaction() ) {
                $db->rollback();
            }
            return false;
        }

        if ( $transact && $db->inTransaction() ) {
            $res = $db->commit();
            if ( $res ) {
                I2CE::raiseError("$deleted_records have been mass deleted!");
                return $deleted_records;
            } else {
                return false;
            }
        }
        I2CE::raiseError("$deleted_records records have been mass deleted!");
        return $deleted_records;

    }


    /**
     * Deletes a form from the entry tables.
     * @param I2CE_Form $form
     * @param boolean $transact: a flag to use transactions or not. default: true
     * @return boolean
     */
    public function delete( $form, $transact) {
        if ( $form->getId() == '0' ) {
            return false;
        }
        if ( $transact && !$this->db->inTranscation() ) {
            try {
                $this->db->beginTransaction();
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Tried to create transaction, but not supported." );
                $transact = false;
            }
        } else {
            $transact = false;
        }

        if ( !$this->prepareDeleteStatement('record') ) {
            if ( $transact && $this->db->inTransaction() ) {
                $this->db->rollback();
            }
            return false;
        }
        try {
            self::$prepared['delete']['record']->execute( array( $form->getId() ) );
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Delete failed from record for " . $form->getId() );
            if ( $transact && $this->db->inTransaction() ) {
                $this->db->rollback();
            }
            return false;
        }

        foreach( array( 'entry', 'last_entry' ) as $table ) {
            
            if ( !$this->prepareDeleteStatement( $table ) ) {
                if ( $transact && $this->db->inTransaction() ) {
                    $this->db->rollback();
                }
                return false;
            }
            try {
                self::$prepared['delete'][$table]->execute( array( $form->getId() ) );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Delete failed from $table for " . $form->getId() );
                if ( $transact && $this->db->inTransaction() ) {
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
     * Checks to see if the value of the form field is the same as in the db
     * @param I2CE_FormField $form_field
     * @param boolean $do_check
     * @return boolean 
     *
     */
    protected function FF_isSameValue( $form_field, $do_check) {
        if ( $do_check ) {
            if ( !$this->prepareCheckStatement( $form_field->getTypeString() ) ) {
                return false;
            }
            try {
                self::$prepared['check'][$form_field->getTypeString()]->execute( 
                        array( $form_field->getContainer()->getId(), $form_field->getStaticAttribute( "DBEntry_form_field_id" ) ) );
                $check = self::$prepared['check'][$form_field->getTypeString()]->fetch();
                self::$prepared['check'][$form_field->getTypeString()]->closeCursor();
                if ( !$check ) {
                    return false;
                }
                if ( $form_field->isSameValue( $check->value ) ) {
                    return true;
                }
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error checking duplicate value:" );
            }
        }
        return false;
    }


    /**
     * Save the FormField to the database.
     * @param I2CE_FormField $form_field
     * @param  boolean $do_check A flag to determine if a check should be made for the same value being saved.
     * @param  I2CE_User $user: The user saving this data.
     * @return boolean
     */
    public function FF_save( $form_field, $do_check, $user ) {
        if ( $this->FF_isSameValue( $form_field, $do_check ) ) {
            return true;
        }
        if ($form_field->getDBValue() != "" && !$form_field->isValid()) {
            return true;
        }
        $this->setupForm( $form_field->getContainer() );
        if ( !$this->prepareSaveStatement( $form_field->getDBType(), $form_field->getTypeString() ) 
             || !$this->prepareSaveStatement( "save_entry", "save_entry" ) ) {
            return false;
        }
        try {
            self::$prepared['save'][$form_field->getDBType()][$form_field->getTypeString()]->execute( 
                    array( $form_field->getContainer()->getId(),  //record 
                        $form_field->getStaticAttribute( "DBEntry_form_field_id" ),  //form_field 
                        $user->getId(), //who 
                        $form_field->getStaticAttribute( "DBEntry_change_type" ),  //change_type 
                        $form_field->getDBValue() //db_value
                        ) );
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error inserting new value for " . $form_field->getName() . " (Make sure triggers are removed.");
            /* save can fail if you have upgraded from version 2 and
               haven't removed the trigger.  To fix this problem, drop
               the trigger as a mysql admin user. */
            return false;
        }
        try {
            self::$prepared['save']["save_entry"]["save_entry"]->execute( 
                    array( $form_field->getContainer()->getId(), $form_field->getStaticAttribute( "DBEntry_form_field_id" ) ) );
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error copying to entry table: " );
            return false;
        }
        return true;

    }



 
    /**
     * Set the value of this field to the next sequence for the form field.
     * @param I2CE_FormField $form_field
     */
    public function FF_IG_setSequence( $form_field) {
        $form_field_id = $form_field->getStaticAttribute( "DBEntry_form_field_id" );
        $update_query = "INSERT INTO field_sequence (form_field, sequence) SELECT "
            . $form_field_id . ", LAST_INSERT_ID( IFNULL( MAX(sequence), 0) +1 ) FROM (SELECT MAX(integer_value) AS sequence FROM last_entry WHERE form_field = " . $form_field_id . " UNION SELECT sequence FROM field_sequence WHERE form_field = " . $form_field_id . ") as next_sequence ON DUPLICATE KEY UPDATE sequence = values(sequence)";
        $select_query = "SELECT LAST_INSERT_ID() AS sequence";
        I2CE::raiseError( $update_query );
        try {
            $this->db->exec( $update_query );
            $res = I2CE_PDO::getRow( $select_query );
            $form_field->setValue( $res->sequence );
            $form_field->setGenerate( false );
            unset( $res );
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error setting new field sequence:" );
        }
    }
 

    /**
     * Populate the history of entries for the form field if the storage module handles history.
     * @param I2CE_FormField $form_field
     * @return boolean
     */
    public function FF_populateHistory( $form_field) {
        if ($form_field->getName() == 'parent') {
            try {
                $result = $this->db->prepare( "SELECT last_modified AS date,0 AS who,0 AS change_type, CONCAT(parent_form,'|',parent_id) as value FROM record WHERE id = ? " );
                $result->execute( array( $form_field->getContainer()->getId()));
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error preparing to populate history:" );
                return false;
            }
        } else {
            if ( !($formObj =$form_field->getContainer()) instanceof I2CE_Form) {
                return false;
            }
            $this->setupForm( $formObj );
            try {
                $result = $this->db->prepare( "SELECT date,who,change_type," . $form_field->getTypeString() 
                                        . "_value as value FROM entry WHERE record = ? AND form_field = ? ORDER BY date" );
                $result->execute( array( $form_field->getContainer()->getId(),
                            $form_field->getStaticAttribute( "DBEntry_form_field_id" ) ) );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error executing populate history: " );
                return false;
            }
        }
        $has_been_set = false;
        while ( $data = $result->fetch() ) {
            if (!$has_been_set && !isset($data->value)) {  
                continue;
            }
            $has_been_set = true;
            $entry = new I2CE_Entry(
                I2CE_Date::fromDB( $data->date ),
                $data->who,
                $data->change_type,
                $form_field->getFromDB( $data->value )
                );
            $form_field->addHistory( $entry );
        }
        $result->closeCursor();
        unset( $result );
        return true;
    }



 
    /**
     * Change the id of the given form
     * @param string $form
     * @param mixed $oldid
     * @param mixed $newid
     * @returns boolean. true on success
     */
    public function changeID( $form,  $oldid, $newid) {
        //form storage check that $form|$oldid exists and $form|$newid does not.
        //but as entry can have $otherform|$newid we need to do an addiional check here/
        $qry = "SELECT COUNT(record.id) AS hasrecord from record WHERE record.id = " . $newid;
        try {
            $res = $this->db->query($qry); 
            if ( !$row = $res->fetch() ) {
                I2CE::raiseError("Badness checking for validity of new $newid");
                return false;
            }
            unset ( $res );
            if ($row->hasrecord == 1) {
                I2CE::raiseError("Cannot change $oldid to $newid -- $newid is already in use");
                return false;
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error checking for validity of new id $form|$newid" );
            return false;
        }
        //now we need to change the record, entry and last_entry tables
        $qrys = array(
            "UPDATE record SET id = $newid WHERE id = $oldid"=>"Could not update $oldid to $newid in record table",
            "UPDATE entry set record = $newid WHERE record = $oldid"=>"Could not update $oldid to $newid in entry table",
            "UPDATE last_entry set record = $newid WHERE record = $oldid"=>"Could not update $oldid to $newid in last_entry table"
            );
        foreach ($qrys as $qry=>$msg) {
            try {
                $res = $this->db->exec($qry);
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, $msg );
                return false;
            }
        }
        return true;
    }


    /**
     * Checks if the given record exists.
     * @param string $form_name
     * @param string $form_id
     * @return array
     */
    public function hasRecord($form_name,$form_id) {
        $int_form_id = $this->getFormId($form_name, true);
        if ($int_form_id == 0) {
            //I2CE::raiseError("No form/unable to create $form_name");
            return false;
        }
        $qry = "SELECT COUNT(record.id) AS hasrecord from record WHERE form = " . $int_form_id . ' AND record.id = ' . $form_id;
        try {
            $res = $this->db->query($qry);
            if ( !$row = $res->fetch() ) {
                return false;
            }
            unset( $res );
            return $row->hasrecord == 1;
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error checking for formid $form_name|$form_id" );
            return false;
        }
    }



    
    /**
     * Generate the reference callback for the given form and field (Type 1)
     *
     *  id=>id
     * parent=>parent
     * last_modified=>last_modified
     * $field => `$field`  (or NULL)
     *
     * @param string $form
     * @param string $field
     * @returns callable
     */
    protected function generateReferenceCallback_1($form,$fields) {
        if (is_string($fields)) {
            $fields = array($fields);
        }
        $deets = array();
        foreach ($fields as $field) {
            $deets["$form+$field"] = $this->getFormFieldIdAndType($form,$field);
        }

        return function($form,$field) use($deets) {
            switch($field) {
                case 'id': return 'id' ;
                case 'parent': return 'parent';
                case 'last_modified': return 'last_modified';
                case 'created': return 'created';
            }
            if ( array_key_exists("$form+$field", $deets ) ) {
                $details = $deets["$form+$field"];
                if (!is_array($details) || !array_key_exists('type',$details)) {
                    return 'NULL';
                } else {
                    return "`$field`";
                }
            } else {
                return "'BAD_FIELD_REFERENCE FOR_$field'";
            }
        };
    }

    /**
     * Generate the reference callback for the given form and field (Type 2)
     *
     * id=>recod.id
     * parent => CONCAT(record.parent_form , '|', record.parent_id)
     * last_modified => record.last_modified
     * $field => last_entry.{$fieldtype}_value  (e.g. last_entry.string_value) or NULL
     *
     * @param string $form
     * @param string $field
     * @returns callable
     */
    protected function generateReferenceCallback_2($form,$fields) {
        if (is_string($fields)) {
            $fields = array($fields);
        }
        foreach ($fields as $field) {
            $deets["$form+$field"] = $this->getFormFieldIdAndType($form,$field);
        }

        return function ($form,$field) use ($deets) {
            switch($field) {
                case 'id': return 'id' ;
                case 'parent': return "CONCAT(r.parent_form,'|',r.parent_id)";
                case 'last_modified': return 'r.last_modified';
                case 'created': return 'r.created';
            }
            if ( array_key_exists("$form+$field", $deets ) ) {
                $details = $deets["$form+$field"];
                if (!is_array($details) || !array_key_exists('type',$details)) {
                    return 'NULL';
                } else {
                    return "`e_$field`.`{$details['type']}_value`";
                }
            } else {
                return "`BAD_FIELD_REFERENCE2_FOR_$field`";
            }
        };

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
        $where_qry = $formObj->generateWhereClause($where);
        if (!$where_qry) {
            I2CE::raiseError("Could not gernerate where clasue for $form by\n" . print_r($where,true));
            return false;
        }

        $this->setupForm( $formObj );
        $referenceCallback  = $this->generateReferenceCallback_2($form,$form_field->getName());
        if (!$referenceCallback) {            
            return false;
        }
        
        if ($form_field->getName() == 'parent') {
            $formID = $this->getFormId( $form, true);
            if (!$formID) {
                //form has not been saved to yet
                return false;
            }
            $last_entry_fields_qry = $this->_getRequiredFieldsQuery($form,array($form_field->getName()), null,false,null,-1,true);
            if (!$last_entry_fields_qry) {
                I2CE::raiseError("Could not generage field query for " . $form_field->getName());
                return false;
            }

            $qry = "UPDATE record JOIN ($last_entry_fields_qry ) AS data ON record.id = data.`$form+id` SET  ".
                "record.parent_form = SUBSTR($set_sql,1,LOCATE('|',$set_sql)-1)," .
                "record.parent_id = CONVERT(SUBSTR($set_sql,LOCATE('|',$set_sql)+1),SIGNED INTEGER) WHERE ( (record.form = $formID  )AND ($where_qry))" ;
            //I2CE::raiseError("Updating by $qry");
            try {
                $res = $this->db->exec($qry);
            } catch ( PDOException $e ) {
                I2CE::pdoError($e, "Cannot update by:\n$qry");
                return false;
            }            
        } else {
            $last_entry_fields_qry = $this->_getRequiredFieldsQuery($form,array($form_field->getName()), null,false,null,-1,true);
            $entry_fields_qry = $this->_getRequiredFieldsQuery($form,array($form_field->getName()), null,false,null,-1,false);
            if (!$last_entry_fields_qry) {
                I2CE::raiseError("Could not generage field query for " . $form_field->getName());
                return false;
            }

            if (!$entry_fields_qry) {
                I2CE::raiseError("Could not generage field query for " . $form_field->getName());
                return false;
            }

            $details = $this->getFormFieldIdAndType($form,$form_field->getName());
            if (!is_array($details) || !array_key_exists('type',$details)) {
                // This shoueld be rare but could happen if no data has been saved yet.  Interpret as nothing to update
                return true;
            }            
            $qry = "UPDATE last_entry JOIN ($last_entry_fields_qry ) AS data ON last_entry.record = data.`$form+id` SET  ".
                'last_entry.`' . $details['type'] . '_value`  = ' . $set_sql  
                . ' WHERE (last_entry.form_field = ' . $details['id']  ." AND ($where_qry))";
            //I2CE::raiseError("Updating by $qry");
            try {
                $res = $this->db->exec($qry);
            } catch ( PDOException $e ) {
                I2CE::pdoError($e, "Cannot update by:\n$qry");
                return false;
            }
            $qry = "UPDATE entry  JOIN ($entry_fields_qry ) AS data ON entry.record = data.`$form+id` SET  ".
                'entry.`' . $details['type'] . '_value`  = ' . $set_sql  
                . ' WHERE (entry.form_field = ' . $details['id']  ." AND ($where_qry) )";
            //I2CE::raiseError("Updating by $qry");
            try {
                $res = $this->db->exec($qry);
            } catch ( PDOException $e ) {
                I2CE::pdoError($e, "Cannot update by:\n$qry");
                return false;
            }
        }
        return true;
    }
//should produce something like ($set_sql = CONCAT(`person+residence`,'@2')
// UPDATE 
//     last_entry update JOIN ( SELECT r.id as `person+id` , e_res.string_value as `person+residence`
// FROM
// record r JOIN last_entry e_res ON e_res.record = r.id AND e_res.form_field = 312
// WHERE 
//                                ((r.form = 48) AND 
//                                 (e_res.string_value LIKE 'country|TF'))

//         ) as data 
// ON  update.record = data.`person+id`

//     SET update.string_value = CONCAT(`person+residence` , '@2')
// WHERE et.form_field = 312 

 
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
