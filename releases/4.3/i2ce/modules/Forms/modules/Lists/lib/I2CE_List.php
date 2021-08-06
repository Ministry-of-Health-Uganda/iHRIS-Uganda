<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
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
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v1.0.0
 * @version v2.0.0
 */
/**
 * Base object for dealing with lists of data.
 * 
 * This is an abstract object with base code for many of the I2CE_Form interface methods.  It is used
 * for many of the list database objects such as {@link AcademicLevel} and {@link District}.
 * 
 * It assumes all lists have a code field and by default a description field but this can be overwritten
 * by certain objects like {@link Cadre}.
 * 
 * @package I2CE
 * @abstract
 * @access public
 */
abstract class I2CE_List extends I2CE_Form {
        
    /**
     * Check to see if a class or class name is a list
     * @param mixed $class an object or a string
     * @returns boolean
     */
    public static function isList($class) {
	if (is_string($class)) {
	    return ($class == 'I2CE_List' || in_array('I2CE_List',class_parents($class)));
	    //really want to use is_subclass_of but not supported as needed in earlier version of php (need php 5.3.9)
	} else if (is_object($class)) {
	    return $class instanceof I2CE_List;
	} else {
	    return false;
	}
    }

    /**
     *@var protected array $mapped_fields_by_form of array index by names of lists of arrays where keys are form names, values are arrays with keys field names and values field objects
     */
    protected static $mapped_fields_by_form = array();
    /**
     *Get all fields mapping to the given list type
     * @param mixed $list.  Either a string a list name or isntance of {I2CE_List}
     * @returns array keys are form names, values are arrays with keys field names and values field objects
     */
    public static function getFieldsMappingToList($list) {
        if (is_string($list)) {        
            $ff = I2CE_FormFactory::instance();
            $listObj = $ff->createForm($list);
        } else if ($list instanceof I2CE_List) {
            $listObj = $list;
        }
        if (!$listObj instanceof I2CE_List) {
            return array();
        }
        $form = $listObj->getName();
        if (!array_key_exists($form,self::$mapped_fields_by_form)) {
            $forms = array();
            $ff = I2CE_FormFactory::instance();
            $allForms = $ff->getForms();
            foreach ($allForms as $f) {
                $obj = $ff->createForm($f);
                if (!$obj instanceof I2CE_Form) {
                    continue;
                }
                $matched = false;
                foreach ($obj as  $fieldName=>$fieldObj) {
                    if (!$fieldObj instanceof I2CE_FormField_MAPPED) {
                        continue;
                    }
                    if (!in_array($form,$fieldObj->getSelectableForms())) {
                        continue;
                    }
                    if (!array_key_exists($f,$forms)) {
                        $forms[$f] = array();
                    }
                    $matched = true;
                    $forms[$f][$fieldName]=$fieldObj;
                }
                if (!$matched) {
                    $obj->cleanup();
                }
                
            }
            self::$mapped_fields_by_form[$form] = $forms;
        } 
        return self::$mapped_fields_by_form[$form];

    }

    /**
     * An array to cache lookup entries
     * 
     * Any lookups done to the database will be cached in this static array so additional
     * lookups using the same id won't have to access the database.
     * @static
     * @var array
     */
    protected static $cache = array();
        


    /**
     * returns the posible componentization of a form|id 
     * @param string $qry the query need to get the form|id
     * @param array $forms an array of string which are componentized
     * @parm string $component The component we possibly wish to componentize at.
     * @returns string. They query need to turn $qry into one componentized to $component.
     */
    public static function componentizeQuery($qry,$forms,$component) {
        if (count($forms) == 0) {
            return $qry;
        }
        //there are parent forms.  we need to examine this vlaue to
        //see if it is a componentized form.
        foreach ($forms as &$form) {
            $form = I2CE::PDO()->quote($form);
        }
        $form_qry = 'SUBSTRING(' . $qry . ",1, LOCATE('|'," . $qry . ") - 1)";        
        $l_qry = 'IF ( ' . $form_qry . ' IN (' . implode(',',$forms) . '), CONCAT( ' . $qry . "," . I2CE::PDO()->quote('@'.$component) . ")," . $qry . ')';
        return "IF ( LOCATE('|'," . $qry . ") > 0 , " .  $l_qry . ',' . $qry .")";
    }

        
    /**
     * Return the HTML file name for the form template for this form.
     * @param string $type
     * @return string
     */
    public function getHTMLTemplate($type='default') {
        if (!$type || $type == 'default') {
            return "lists_form_" . $this->getName() . ".html";
        } else {
            return "lists_form_" . $this->getName() . "_alternate_" . $type .".html";
        }
    }

    /**
     * Return the HTML file name for the view template for this form.
     * @return string
     */
    public function getViewTemplate($type='default') {
        if (!$type || $type == 'default') {
            return "view_list_" . $this->getName() . ".html";
        } else {
            return "view_list_" . $this->getName() . "_alternate_" . $type .".html";
        }
    }

    /**
     * Return the list edit type for this list.
     * 
     * The possible return values are "list," "dual," or "select." Select will display a drop
     * down of all choices and list and dual will list them all in a table.  Dual includes the
     * linked list object for the object.
     * @return string
     */
    public function getListType() { return "select"; }
        
    /**
     * Sets the field values to be displayed in the HTML template.
     * @param I2CE_Template &$template
     */
    public function setDisplayData( &$template ) {
        parent::setDisplayData( $template );
        $template->setDisplayData( "add_type", array("type"=>$this->getName()) );
        $template->setDisplayData( "type_name", $this->getDisplayName() );
    }
        


    /**
     * Return the display name for this list object.
     * 
     * This will return the same value as lookup() but from the current object instead of from the database.
     * @param string $style.. Defaults to 'default'
     * @return string
     */
    public function name( $style = 'default' ) {
        $disp_args = $this->_getDisplayFields($style);
        $disp_string = $this->_getDisplayString($style);
        $disp_vals = array();
        foreach ($disp_args as $field) {
            if (array_key_exists($field,$this->fields) && $this->fields[$field] instanceof I2CE_FormField) {
                $disp_vals[] = $this->fields[$field]->getDisplayValue();
            } else {
                $disp_vals[] = '';
            }
        }
        return vsprintf($disp_string,$disp_vals);
    }


    /**
     * Worker function to get the display fields
     * @param string $style.. Defaults to 'default'
     * @returns array of string
     */
    protected function _getDisplayFields($style = 'default') {
        return self::getDisplayFields( $this->name, $style );
    }

    /**
     * worker method to get the display string
     * @param string $style.. Defaults to 'default'
     * @returns  string
     */
    protected function _getDisplayString($style = 'default') {
        return self::getDisplayString( $this->name, $style );
    }



    /**
     * Returns display string used for displaying this list.
     * @param string $form_name
     * @param string $style.. Defaults to 'default'
     * @return string
     */
    static public function getDisplayString( $form_name, $style ='default' ) {
        $factory = I2CE_FormFactory::instance();
        $factory->loadMetaAttributes( $form_name );

        if ( !$factory->hasMetaAttribute( $form_name, "list/$style/display_string" )
                || !($disp_string = $factory->getMetaAttribute( $form_name, 
                        "list/$style/display_string" ) ) ) {
            $disp_string =  implode( " - ", array_pad( array(),
                        count( self::getDisplayFields( $form_name, $style ) ), 
                               "%s" ) );
            $factory->setMetaAttribute( $form_name, 
                    "list/$style/display_string", $disp_string );
        }
        return $disp_string;
    }

    /**
     * Returns a list of fields used for displaying this list.
     * @param string $form_name
     * @param string $style.. Defaults to 'default'
     * @return array
     */
    static public function getDisplayFields( $form_name, $style ='default' ) {
        $factory = I2CE_FormFactory::instance();
        $factory->loadMetaAttributes( $form_name );
        $disp_args =array();
        if ( !$factory->hasMetaAttribute( $form_name, "list/$style/display_args" )
                || !is_array( $disp_args = $factory->getMetaAttribute( $form_name,
                        "list/$style/display_args" ) ) ) {
            $disp_args = array( 'name' );
            $factory->setMetaAttribute( $form_name, 
                    "list/$style/display_args", $disp_args );
        }
        ksort($disp_args);
        return $disp_args;
    }


    /**
     * Returns a list of fields used for displaying this list.
     * @param string $form_name
     * @param string $style.. Defaults to 'default'
     * @return array
     */
    static public function getDisplayFieldStyles( $form_name, $style ='default' ) {
        $factory = I2CE_FormFactory::instance();
        $factory->loadMetaAttributes( $form_name );

        if ( !$factory->hasMetaAttribute( $form_name, "list/$style/display_arg_styles" )
                || !is_array( $disp_arg_styles = $factory->getMetaAttribute( $form_name,
                        "list/$style/display_arg_styles" ) ) ) {
            $disp_arg_styles = array();
            $factory->setMetaAttribute( $form_name, 
                    "list/$style/display_arg_styles", $disp_arg_styles );
        }
        return $disp_arg_styles;
    }


    /**
     * Returns a list of fields used for sorting this list.
     * @param string $form_name
     * @param string $style.. Defaults to 'default'
     * @return array
     */
    public static function getSortFields($form_name,$style = 'default') {
        $factory = I2CE_FormFactory::instance();
        $factory->loadMetaAttributes( $form_name );
        $sort_fields = array();
        if ( !$factory->hasMetaAttribute( $form_name, "list/$style/sort_fields" )
                || !is_array( $sort_fields = $factory->getMetaAttribute( $form_name,
                        "list/$style/sort_fields" ) ) ) {
            $sort_fields = self::getDisplayFields( $form_name, $style );
            $factory->setMetaAttribute( $form_name, 
                    "list/$style/sort_fields", $sort_fields );
        }
        ksort($sort_fields);
        return $sort_fields;
    }

    /**
     * Worker function to get the display fields
     * @param string $style.. Defaults to 'default'
     * @returns array of string
     */
    protected function _getSortFields($style = 'default') {
        return self::getSortFields( $this->name, $style );
    }

        
    /**
     * Return the list of options for this list as an array.
     * 
     * @param string $form_name The form being listed.  This needs to be a subclass of I2CE_List
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.  Defaults to 0
     * @param $select_fields An array of field objects of the form type that that are in this form that we want to limit values by.  Defaults to empty array
     * @param string $opt_field An optional field to further limit the list of choices 
     * @param integer $opt_value If the $opt_field is used then this is the value to limit it by.
     * @param array $sub_fields of string.  If $op_value is is set, it is an array of  linked ($form+)$field's to 
     * include results under the optional value.  e.g. if $opt_value was 'country|10' and $sub_fields was
     * array(county+district,district+region,[region],country) we would display all counties and district who
     * are under country|10.  In this case we display the extended version of the option

     * @return array
     */

    //return array with keys formid and values the value the displayed value
    static public function listOptions ( $form_name, $show_hidden = 0,$select_fields = array()) {

        $where = array(); 
        if (!is_array($select_fields)) {
            $select_fields = array($select_fields);
        }
        foreach ($select_fields as $select_field) {
            if (!$select_field instanceof I2CE_FormField_MAPPED 
                || ! $select_field->isSetValue() 
                || !$select_field->isValid() 
                || ! ($select_form = $select_field->getContainer()) instanceof I2CE_Form 
                || $select_form->getName() != $form_name) {
                continue;
            }            
            $where[] = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>$select_field->getName(),
                'style'=>'equals',
                'data'=>array(
                    'value'=>$select_field->getDBValue()
                    )
                );
        }
        $add_limits = array();
        foreach ( I2CE_ModuleFactory::callHooks("get_limit_add_form_{$form_name}_id") as $add_limit) {
            if (!is_array($add_limit) || !array_key_exists($form_name,$add_limit) || !is_array($add_limit[$form_name])) {
                continue;
            }
            $add_limits[] = $add_limit;
        }
        $where = array_merge($where,$add_limits);
        if (count($where) > 0) {
            $where = array( 'operator'=>'AND',  'operand'=>$where);
        }
        $forms = array($form_name);
        $limits = array($form_name=>$where);
        $limits = array($form_name=>$where);
        $orders = array($form_name=>self::getSortFields($form_name));       
        $fields = array($form_name);
        $data = I2CE_DataTree::buildDataTree( $fields,$forms,$limits, $orders, $show_hidden);        
        $data = I2CE_DataTree::flattenDataTree( $data );
        return $data;
    }
    
    /**
     *  Modifies a where clause to limit to hidden fields as neccesary
     * @param array $where
     * @param int $show_hideden.  0=non-hidden, 1=All, 2=hidden only
     * @returns array()
     */    
    public static function showHiddenLimit($where,$show_hidden) {
        return I2CE_DataTree::showHiddenLimit( $where, $show_hidden );
    }


    /**
     * Walk up a form/linked  field path until. Return the id of the top form.
     * $param mixed. Either an array of string or a colon-sepearted string.  (imploded) values is a string of the form:
     *    form1(+field1):...:formM(+fieldM):..:fieldN     
     *    there needs to be at least one colon/array needs to be at least two in length
     * @param string $formid  string of the form "$form|$id".  the starting value.  $form should be one of formX above (e.g. the dbvalue of a map field)
     * @returns mixed. False on failure, string of the form "$form|$id" on success
     */
    public static function walkupFieldPath($field_path,$formid) {
        if (is_string($field_path)) {
            $field_path = explode(':',$field_path);
        }
        if (!is_array($field_path) || count($field_path) < 2) {
            return false;
        }
        list($form,$form_id) = array_pad(explode('|',$formid,2),2,'');
        if (strlen($form_id) == 0 || strlen($form_id) == 0) {
            return false;
        }
        $form_path  = array();
        $link_field_path  = array();
        $len = count($field_path);
        for ($i = $len -1; $i >= 0; $i--) {
            $data = explode('+',$field_path[$i],2);
            if (count($data) == 2) {
                list($form,$link_field) = $data;
            } else {
                $link_field = $form;
                $form = $field_path[$i];
            }
            $form_path[$i] = $form;
            $link_field_path[$i] = $link_field;
        }

        //ksort($form_path);
        //ksort($link_field_path);
        //now we need to see if we can find the form.
        if (($form_index = array_search($form,$form_path)) === false) {
            //the resitrcted form is not among the forms in the form path.
            return false;
        }

        //now we need to walk up the form path until we get to the id of the top most form (e.g. country) for the resitrcted field
        //example:  formid = country|10.  stop this is the top
        //example:  formid = region|10
        //          get dbvalue country field in region|10.  this is the top
        //example:  formid = distrct|10
        //          get dbvalue region field in district|10.  suppose it is refgion|10
        //          get dbvalue country field in region|10.  this is the top
        for ($i= $form_index; $i < $len-1; $i++) {
            //if (($form_id = I2CE_FormStorage::search($form_path[$i],false,$where,array(),1)) === false) {
            if (($formid = I2CE_FormStorage::lookupField($form_path[$i],$form_id,$link_field_path[$i])) === false) {
                //could not walk up the path.
                return false;
            }
            list($form,$form_id) = array_pad(explode('|',$formid,2),2,'');
            if ($form != $link_field_path[$i] || strlen($form_id) == 0 || strlen($form_id) == 0) {
                //be extra sutre that no invalid id received
                return false;
            }
        }
        //$formid is now the id of the top form.
        return $formid;
    }


        
    /**
     * Looks up the description of the item based on the code.
     * 
     * This is the default method that most implementations of {@link lookup()} use.  It finds the description of
     * the object based on the code and saves it in the {@link cache} and returns it.
     * @param integer $id The code of the entry to lookup.
     * @param string $form_name The name of the form in the database.
     * @return string
     */
    static public function lookup( $id, $form_name ) {
        if ( !$id || $id == "" ) return "";
        if ( !self::isCached( $form_name, $id ) ) {
            $value_arr = I2CE_FormStorage::lookupDisplayField( $form_name, $id, self::getDisplayFields( $form_name ), false ) ;
            if (count($value_arr) > 0) {
                $value = @vsprintf(self::getDisplayString( $form_name ) , $value_arr );
            } else {
                $value = false;
            }
            if ( $value ) {
                self::addCache( $form_name, $id, $value );
                return $value;
            }

        }
        return self::getCache( $form_name, $id );
    }
    
    
    
    /**
     * Checks to see if the {@link id} number is cached.
     * 
     * Checks the {@link cache} to see if {@link code} has been cached or not.
     * @param string $table_name The name of the table in the database.
     * @param integer $code The code of the entry to lookup.
     * @return boolean
     */
    static final protected function isCached( $table_name, $id ) {
        if ( array_key_exists( $table_name, self::$cache ) ) {
            return array_key_exists( $id, self::$cache[ $table_name ] );
        } else {
            return false;
        }
    }
    /**
     * Adds the {@link id} number to the {@link cache}.
     * 
     * @param string $table_name The name of the table in the database.
     * @param integer $code The code of the entry to add.
     * @param string $value The value to add to the cache
     */
    static final protected function addCache( $table_name, $id, $value ) {
        if ( !array_key_exists( $table_name, self::$cache ) ) {
            self::$cache[ $table_name ] = array();
        }
        self::$cache[ $table_name ][ $id ] = $value;       
    }
    /** 
     * Return the {@link cache cached} entry for the {@link id} number.
     * 
     * @param string $table_name The name of the table in the database.
     * @param integer $code The code of the entry to lookup.
     * @return string The value of the cached code.
     */
    static final protected function getCache( $table_name, $id ) {
        if ( self::isCached( $table_name, $id ) ) {
            return self::$cache[ $table_name ][ $id ];
        } else {
            return "";
        }
    }




    /**
     * Create a data tree of the selectable forms.  Deisgned to be fed into tree select
     * @param array $fields an ordered array E.g array('village+county','county','district,'region+country','country').
     *                      it is an "bottom up" array of string where strings are of the form "$form" or "$form+$link_field".  
     *                      In the case of the former type, then $link_field is assumed to be the next form.  So for example, 
     *                      "county" has link field "district".  If a "$form(+$link_field)" is surrounded by brackets [ ] , 
     *                      it is not displayed.
     * @param array $forms An unorderd array of form names whose values we allow to be selected
     * @param array $limits An array with keys form names and value limit data
     * @param array $orders An array with keys form names and values array of field orders for that form.  If the form 
     *                      name has no orders, we use default
     * ordering for that form based on its displayed firelds
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.  Defaults to 0
     * @return array
     */
    public static function buildDataTree($fields, $forms,$limits,$orders=array(),$show_hidden = 0)  {
        return I2CE_DataTree::buildDataTree($fields, $forms, $limits, $orders, $show_hidden );
    }

    public static function flattenDataTree($data) {
        return I2CE_DataTree::flattenDataTree($data);
    }


    /**
     * Internal method to find all the ids for forms that are below
     * the given matched form id.  This will go through the
     * list of fields until the form that is being matched
     * is found.
     * @param string $match The form id to match at the top.
     * @param array $fields The getDisplayedFields() for the field object.
     * @param array $forms The getSelectableForms() for the field object.
     * @param array $displayed A list of forms that should be included in the results.
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.  Defaults to 0
     * @return array
     */
    protected static function _findLowerMatches( $match_form, $match_ids, $fields, $forms, $displayed, $show_hidden = 0 ) {
        $formfield = array_shift( $fields );
        list( $form, $link_field ) = $formfield;
        if ( $form != $match_form ) {
            return self::_findLowerMatches( $match_form, $match_ids, 
                    $fields, $forms, $displayed, $show_hidden );
        } else { 
            if ( $displayed[$form] ) {
                return array_merge( $match_ids, self::_searchLowerMatches( 
                            $match_form, $match_ids, $fields, $forms, 
                            $displayed, $show_hidden ) );
            } else {
                return self::_searchLowerMatches( $match_form, $match_ids, 
                        $fields, $forms, $displayed, $show_hidden );
            }
        }
    }
    /**
     * Internal method to search all the ids for forms that are below
     * the given matched form id.
     * @param string $match The form id to match at the top.
     * @param array $fields The getDisplayedFields() for the field object.
     * @param array $forms The getSelectableFroms() for the field object.
     * @param array $displayed A list of forms that should be included in the results.
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.
     * @return array
     */
     protected static function _searchLowerMatches( $match_form, $match_ids,
            $fields, $forms, $displayed, $show_hidden ) {
        $formfield = array_shift( $fields );
        if ( !is_array( $formfield ) ) {
            return array();
        }
        list( $form, $link_field ) = $formfield;

        $limit = array( 
                'operator' => 'FIELD_LIMIT', 
                'field' => $link_field, 
                'style' => 'in', 
                'data' => array( 
                    'value' => $match_ids
                    )
                ); 
        $limit = I2CE_DataTree::showHiddenLimit( $limit, $show_hidden ); 
        $order = I2CE_List::getSortFields( $form ); 
        $field_datas = I2CE_FormStorage::listFields( $form, 'id',
                false, $limit, $order );
        $matched = array();
        foreach( array_keys( $field_datas ) as $matched_id ) {
            $matched[] = $form . "|" . $matched_id;
        }
        if ( $displayed[$form] ) {
            return array_merge( $matched, self::_searchLowerMatches( $form, 
                    $matched, $fields, $forms, $displayed, $show_hidden ) );
        } else {
            return self::_searchLowerMatches( $form, $matched, $fields, 
                    $forms, $displayed, $show_hidden );
        }
    }

    /**
     * For the list of displayed fields for a field, find all
     * the ids for forms that are below the given matched form id.
     * For example if a location has the displayed fields of:
     * array( 'county', 'district', '[region]', 'country' )
     * with selectable fields:
     * array( 'county', 'district' )
     * if you want to match the district: district|10
     * this method will return all the county ids that are
     * in district|10.  This will also return 'district|10' in the list
     * to have a complete list of valid selectable forms.
     * @param string $match The form id to match at the top.
     * @param array $fields The getDisplayedFields() for the field object.
     * @param array $forms The getSelectableFroms() for the field object.
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.  Defaults to 0
     * @return array
     */
    public static function findLowerMatches($match, $fields, $forms,
            $show_hidden = 0 )  {
        if (!is_array($forms) || !is_array($fields)  || count($fields) == 0) {
            return array();
        }
        list( $match_form, $match_id ) = explode( '|', $match );
        $displayed = array();
        $last_form = false;
        $fields = array_reverse($fields);
        foreach ($fields as &$field) {
            if (!is_string($field)) {
                return array();
            }
            $len = strlen($field);
            if ($len >= 2 && $field[0] == '[' && $field[$len-1] == ']') {
                $field = substr($field,1,$len-2);
                $display = false;
            } else {
                $display = true;
            }
            if ( ($pos = strpos($field,'+')) !== false) {
                list($form,$link_field) = explode('+',$field,2);
                if ($last_form == false) { //throw away junk linked field data on the top level form
                    $link_field =false;
                }
            } else {
                $form = $field;
                $link_field = false;
            }
            if (!$form) {
                return array();
            }
            if (!$link_field) {
                $link_field = $last_form;
            }
            $field = array($form,$link_field);
            $displayed[$form] = $display;
            $last_form = $form;
        }

        return self::_findLowerMatches( $match_form, array( $match ),
                $fields, $forms, $displayed, $show_hidden );

    }


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
