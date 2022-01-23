<?php
/**
 * @copyright © 2012, 2013 Intrahealth International, Inc.
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
 * @since v4.1.5
 * @version v4.1.5
 */
/**
 * Base object for dealing with data trees from I2CE_List objects.
 * 
 * @package I2CE
 * @access public
 */
class I2CE_DataTree {
        
    /**
     * @var string The report alias to use for fields in the getSQLField Callback
     */
    protected static $curr_alias;

    /**
     * Create a data tree of the selectable forms.  Deisgned to be fed into tree select
     * @param array $fields an ordered array E.g array('village+county','county','district,'region+country','country').
     * it is an "bottom up" array of string where strings are of the form "$form" or "$form+$link_field".  In the case of
     * the former type, then $link_field is assumed to be the next form.  So for example, "county" has link field "district".
     * If a "$form(+$link_field)" is surrounded by brackets [ ] , it is not displayed.
     * @param array $forms An unorderd array of form names whose values we allow to be selected
     * @param array $limits An array with keys form names and value limit data
     * @param array $orders An array with keys form names and values array of field orders for that form.  
     *                      If the form name has no orders, we use default ordering for that form based on its displayed firelds
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.  Defaults to 0
     * @param array $report A report name to use for the query instead of building it from form cache or directly.
     * @return array
     */
    public static function buildDataTree($fields, $forms,$limits,$orders=array(),$show_hidden = 0, $report=null, $style ='default' )  {
         //$forms = array(village, county) -- order does not matter.
        //$fields -- order matters.
        //Example0:
        //$fields == array('village','county','[district+cssc_region],'cssc_region+cssc_country','country')
        //Example1:
        //$fields == array('village+county','county','district,'region+country','country')  -- you could just as easily have used 'region'
        //Example2:
        //$fields == array('village','county');        

        //build the tree top down.  top  = country in Example 0,1 and top = county in Example 2
        if (!is_array($forms) || !is_array($fields)  || count($fields) == 0) {
            return array();
        }
        $data = array();
        $displayed = array();
        $last_form = false;
        $fields = array_reverse($fields);
        foreach ($fields as &$field) {
            //start0: $form = country $link_field = false   $last_form = false
            //next0: $form = cssc_region $link_field = country $last_form = country 
            //start1: $form = country $link_field = false   $last_form = false
            //next0: $form = region $link_field = country $last_form = country 
            //start2: $form = county $link_field = false   $last_form = false
            //next2: $form = village $link_field = county  $last_form = county  
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
        unset($field);
        $styles = array();
        
        $ff = I2CE_FormFactory::instance();
        if ($last_form) {
            $avail_styles= array($last_form => $style);
            $curr_style = $style;
            foreach (array_reverse($fields) as $formfield) {
                list ($form, $link_field) = $formfield;
                if (!$form
                    || !  ($formObj = $ff->createContainer($form)) instanceof I2CE_Form
                    ) {
                    break;
                }
                if (array_key_exists($form,$avail_styles) && is_string($avail_styles[$form])) {
                    $curr_style = $avail_styles[$form];
                } else {
                    $curr_style = 'default';
                }
                $styles["$form+$link_field"] = $curr_style;
                if (!$link_field
                    || ! ( $fieldObj = $formObj->getField($link_field)) instanceof I2CE_FormField
                    ) {
                    break;
                }
                $avail_styles = I2CE_List::getDisplayFieldStyles($form,$style);
            }
        }
        if ( is_array($report) ) {
            $results = self::buildReportTree( $fields, $forms, $displayed, $limits, $orders, $show_hidden, $report );
            if ( count($results) > 0 ) {
                return $results;
            } else {
                I2CE::raiseError( "buildReportTree returned no results so defaulting to regular display.  If there is data then something went wrong so it should be fixed." );
            }
        }
        
        $use_cache = true;
        if (  I2CE_ModuleFactory::instance()->isEnabled( "CachedForms" ) ) {
            $fs = I2CE_FormStorage::getMechanismByStorage( "cached" );
            if ( $fs instanceof I2CE_FormStorage_cached ) {
                try {
                    return $fs->buildDataTree( $fields, $forms, $displayed, $limits, $orders, $show_hidden,$style );
                } catch (Exception $e) {
                    $use_cache = false;
                    I2CE::raiseError("Could not cache $form");
                }
            }
        }


        $phonebook = array(); //indexed by "$form|$id" with values (by reference) the arrays at which contains the 'children' sub-array  for $form|$id node
        $parent_links = array(); //indexed by "$form|$id" with values "$pform|$pid" which is the form/id that "$form|$id" is linked against
        $display_string = array();
        foreach ($fields as $formfield) {            
            list ($form, $link_field) = $formfield;
            if ( array_key_exists( "$form+$link_field", $limits ) ) {
                $limit = $limits["$form+$link_field"];
            } elseif (array_key_exists($form,$limits)) {
                $limit = $limits[$form];
            } else {
                $limit = array();
            }
            if (!($formObj = $ff->createContainer($form)) instanceof I2CE_Form) {
                continue;
            }
            $style ='default';
            if (array_key_exists("$form+$link_field",$styles)) {
                $style = $styles["$form+$link_field"];
            }
            //if we dont show the hidden list memmber we need to include the limit where i2ce_disabled is false
            $limit = self::showHiddenLimit($limit,$show_hidden);

            $disp_fields = I2CE_List::getDisplayFields($form,$style);
            $disp_str = I2CE_List::getDisplayString($form,$style);
            //start0:  $form = country, $fields = (name)
            //next0:  $form = cssc_region, $fields = (name, cssc_country)
            //next0:  $form =  district $fields = (name, cssc_region)
            //next0: $form = county $field = (name,distrcit)
            //end0: $form = villate $field = (name,county)
            //start1:  $form = country, $fields = (name)
            //next1:  $form = region, $fields = (name, country)
            //next1:  $form =  district $fields = (name, region)
            //etc.
            if (array_key_exists($form,$orders)) {
                $order = $orders[$form];
            } else {
                $order = I2CE_List::getSortFields($form,$style);
            }
            ksort($order);

            if ($link_field) {
                $field_datas = I2CE_FormStorage::listFields( $form, $link_field, false,
                        $limit, $order, false, -1, $use_cache);
            } else {
                $field_datas = I2CE_FormStorage::listFields( $form, 'id', false,
                        $limit, $order, false, -1, $use_cache );
            }
            $display_datas = I2CE_FormStorage::listFields( $form, $disp_fields, 
                    false, $limit,$order, false, -1, $use_cache );
            $link_id = false;
            $last_link = false;
            $selectable = in_array($form,$forms);
            foreach ($field_datas as $id=>$field_data) {
                $formid =$form . '|'. $id;
                if (!$link_field) { //this should only be the case for the top form
                    $parent = &$data;
                } else {
                    //we are not at the top.
                    $link = $field_data[$link_field];
                    unset($field_data[$link_field]);
                    if ($last_link != $link) {
                        if (!array_key_exists($link,$phonebook)) {   
                            //don't know where to put this as a child of the previous one so skip it
                            continue;
                        }
                        $last_link = $link;
                        if (!array_key_exists('children',$phonebook[$link])) {
                            $phonebook[$link]['children'] = array();
                        }
                        $parent = &$phonebook[$link]['children'];
                        //example: $diplayed == array(country=>true, region=>false,  district=>true, county=>true village => true)
                        //we have $form = district, $formid = district|30, $parent_link= region|40
                        end($displayed);
                        $disp_form = key($displayed);
                        while ( $disp_form !== false && $disp_form !== $form) {
                            prev($displayed);
                            $disp_form = key($displayed);
                        }
                        //we end here either before the beginning of the array or where $disp_form == $form.                    
                        prev($displayed); //we are now at the one before the $form.  if the current form was district, we are now at region
                        $parent_link = $link;
                    } else {
                        if (!array_key_exists($link,$phonebook) || !$phonebook[$link]) {
                            //don't know where to put this as a child of the previous one so skip it
                            continue;
                        }
                    }
                    $parent_links[$formid] = $link;
                }
                if (!array_key_exists($id,$display_datas)) {
                    continue;
                }
                $disp_array  =array();
                foreach ($disp_fields as $field) {
                    if (array_key_exists($field,$display_datas[$id])
                        && ($fieldObj = $formObj->getField($field)) instanceof I2CE_FormField
                        ) {
                        $fieldObj->setFromDB($display_datas[$id][$field]);
                        $disp_array[] = $fieldObj->getDisplayValue(false,$style);
                    } else {
                        $disp_array[] = '';
                    }
                }
                $display = vsprintf( $disp_str, $disp_array );
                $child_data =  array('display'=>$display,'show'=>$displayed[$form]);
                if ($selectable) {
                    $child_data['value'] = $formid;
                }
                $parent[] = $child_data;
                end($parent);
                $phonebook[$formid] = &$parent[key($parent)];
            }
        }
        self::removeNotShownNodes( $data );
        return $data;
    }


    /**
     * Remove any nodes from buildDataTree that shouldn't be shown
     * and move the children to the previous node.
     * @param array $data
     */
    protected static function removeNotShownNodes( &$data ) {
        foreach( $data as $key => &$node ) {
            if ( array_key_exists( 'children', $node ) && is_array( $node['children'] ) ) {
                if ( !array_key_exists( 'show', $node ) || !$node['show'] ) {
                    foreach( $node['children'] as $child ) {
                        $data[] = $child;
                    }       
                    unset( $data[$key] );
                }       
                self::removeNotShownNodes( $node['children'] );
            }       
            unset( $node['show'] );
        }       
    }       






    /**
     * Flatten the data tree into a single array of results
     * @param array $data
     * @param boolean $keyvalue Return an array as key value pairs.
     * @param boolean $reverse If using keyvalue pairs, then reverse the array 
     *                         so the value is the key.  
     *                         Any duplicates will be overwritten!
     * @return array
     */
    public static function flattenDataTree($data, $keyvalue=false, $reverse=false) {
        $list = array();
        self::_flattenDataTree($data,$list,$keyvalue,$reverse);
        return $list;
    }


    /**
     * Recursive method to flatten the data tree into a single array of results
     * @param array $data
     * @param array &$list
     * @param boolean $keyvalue Return an array as key value pairs.
     * @param boolean $reverse If using keyvalue pairs, then reverse the array 
     *                         so the value is the key.  
     *                         Any duplicates will be overwritten!
      * @return array
     */
    protected static function _flattenDataTree($data,&$list,$keyvalue=false,$reverse=false) {
        foreach ($data as $d) {
            if (!is_array($d) || !array_key_exists('display', $d)) {
                continue;
            }
            if (array_key_exists('value',$d) && $d['value'] && $d['value'] != '|' && array_key_exists('display',$d) && is_string($d['display']) && strlen($d['display']) > 0) {
                if ( $keyvalue ) {
                    if ( $reverse ) {
                        $list[$d['display']] = $d['value'];
                    } else {
                        $list[$d['value']] = $d['display'];
                    }
                } else {
                    $list[] = array('value'=>$d['value'], 'display'=>$d['display']);
                }
            }
            if (array_key_exists('children',$d) && is_array($d['children'])) {
                self::_flattenDataTree($d['children'],$list,$keyvalue,$reverse);
            }
        }        
    }

    /**
     *  Modifies a where clause to limit to hidden fields as neccesary
     * @param array $where
     * @param int $show_hideden 0=non-hidden, 1=All, 2=hidden only.    
     * @returns array()
     */    
    public static function showHiddenLimit($where,$show_hidden) {
        $show_hidden = (int) $show_hidden;
        if ($show_hidden < 0 || $show_hidden > 2) {
            $show_hidden = 0;
        }
        if ($show_hidden != 1) {
            if ($show_hidden == 0) {
                $where_hidden = array(
                    'operator'=>'OR',
                    'operand'=>array(
                        array(
                            'operator'=>'FIELD_LIMIT',
                            'field'=>'i2ce_hidden',
                            'style'=>'no',
                            'data'=>array( )
                            ),
                        array(
                            'operator'=>'FIELD_LIMIT',
                            'field'=>'i2ce_hidden',
                            'style'=>'null',
                            'data'=>array( )
                            )

                        )
                    );
            } else {
                // $show_hidden =2, 
                $where_hidden = array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'i2ce_hidden',
                    'style'=>'yes',
                    'data'=>array( )
                    );
            }
            if (count($where) == 0) {
                $where = $where_hidden;
            } else { 
                $where = array(
                    'operator'=>'AND',
                    'operand'=>array($where, $where_hidden)
                    );
            }
        }
        return $where;
    }

   /** 
     * Update the limits array to add the necessary form
     * if it's an id field because that is needed for 
     * buildDataTree since ids always include the form for 
     * joining.
     * @param string $form The form to use
     * @param array &$limit The limit to modify
     */
    protected static function addFormIdToLimit( $form, &$limit ) { 
        if ( array_key_exists( 'field', $limit ) && $limit['field'] == 'id' ) { 
            if ( array_key_exists( 'data', $limit ) && array_key_exists( 'value', $limit['data'] ) ) {
                if ( is_array( $limit['data']['value'] ) ) { 
                    foreach( $limit['data']['value'] as &$value ) { 
                        $value = "$form|" . $value;
                    }   
                } else {
                    $limit['data']['value'] = "$form|" . $limit['data']['value'];
                }   
            }   
        } elseif( array_key_exists( 'operand', $limit ) ) { 
            foreach( $limit['operand'] as &$sublimit ) { 
                self::addFormIdToLimit( $form, $sublimit );
            }   
        }   
    }   


    /**
     * Return the appropriate sql field for limits for the report tree.
     * @param string $form
     * @param string $field
     * @return string
     */
    public static function getSQLField( $form, $field ) {
        return "`" . self::$curr_alias . "+$field`";
    }

    /**
     * Create a data tree from a report of the selectable forms.  Deisgned to be fed into tree select
     * @param array $fields 
     * @param array $forms An unorderd array of form names whose values we allow to be selected
     * @param array $displayed The displayed forms for the tree
     * @param array $limits An array with keys form names and value limit data
     * @param array $orders An array with keys form names and values array of field orders for that form.  
     *                      If the form name has no orders, we use default ordering for that form based on its displayed firelds
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.  Defaults to 0
     * @param string $report A report name to use for the query instead of building it from form cache or directly.
     * @return array
     */
    public static function buildReportTree($fields, $forms, $displayed, $limits, $orders=array(),
            $show_hidden = 0, $report=null )  {
        if ( !is_array($report) || !array_key_exists( 'report', $report )  ) {
            return array();
        }
        $report_table = I2CE_CustomReport::getCachedTableName( $report['report'] );
        if ( !$report_table ) {
            return array();
        }
        $map = array();
        if ( array_key_exists( 'map', $report ) ) {
            $map = $report['map'];
        }
        $where = array();
        $displays = array();
        $report_alias = array();
        $formObjs = array();
        $use_link = '';
        foreach( $fields as $formfield ) {
            list( $form, $link_field ) = $formfield;
            $ff = $form . ($link_field ? "+$link_field" : "" );
            if ( array_key_exists( $ff, $map ) ) {
                $report_alias[$ff] = $map[$ff];
            } elseif ( array_key_exists( $form, $map ) ) {
                $report_alias[$form] = $map[$form];
                $report_alias[$ff] = $map[$form];
            } else {
                $report_alias[$form] = $form;
                $report_alias[$ff] = $form;
            }

            //if ( array_key_exists( $ff, $limits ) ) {
                //$limit = $limits[$ff];
            //} else
            if ( array_key_exists( $report_alias[$ff], $limits ) ) {
                $limit = $limits[ $report_alias[$ff] ];
            } elseif ( array_key_exists( $form, $limits ) ) {
                $limit = $limits[$form];
            } else {
                $limit = array();
            }

            self::addFormIdToLimit( $form, $limit );

            $limit = self::showHiddenLimit( $limit, $show_hidden );

            /*
            if ( !$show_hidden ) {
                $hidden = $report_alias[$ff] . '+i2ce_hidden';
                $where[] = "( `$hidden` = 0 OR ISNULL( `$hidden` ) )";
            }
            */
            $disp_fields = I2CE_List::getDisplayFields( $form );
            if ( array_key_exists( $form, $orders ) ) {
                $order = $orders[$form];
            } else {
                $order = I2CE_List::getSortFields( $form );
            }
            $sort_list = array();
            //if ( array_key_exists( $form, $displayed ) && $displayed[$form] ) {
            if ( array_key_exists( $form, $displayed ) ) {
                $alias_form = $report_alias[ $ff ];
                if ( $displayed[$form] ) {
                    $displays[ $alias_form ]['form'] = $form;
                    if ( $use_link == '' ) {
                        $displays[ $alias_form ]['link_field'] = ($link_field == '' ? $link_field : "$alias_form+$link_field" );
                    } else {
                        $displays[ $alias_form ]['link_field'] = $use_link;
                        $use_link = '';
                    }
                    foreach( $disp_fields as $disp ) {
                        $displays[ $alias_form ]['fields'][$disp] = $alias_form . "+$disp";
                    }
                    foreach( $order as $i => $ord ) {
                        if ( !is_string( $ord ) ) {
                            unset( $order[$i] );
                            continue;
                        }
                        if ( $ord[0] == '-' ) {
                            $field = substr( $ord, 1 );
                            $all_orders[] = "`$alias_form+$field` DESC";
                        } else {
                            $field = $ord;
                            $all_orders[] = "`$alias_form+$field` ASC";
                        }
                    }
                } elseif ( $use_link == '' ) {
                    $use_link = ($link_field == '' ? $link_field : "$alias_form+$link_field" );
                }
            }
            if ( !array_key_exists( $form, $formObjs ) ) {
                $formObjs[$form] = I2CE_FormFactory::instance()->createContainer( $form );
                if ( !$formObjs[$form] instanceof I2CE_Form ) {
                    I2CE::raiseError( "Could not instantiate $form" );
                    return array();
                }
            }
            self::$curr_alias = $report_alias[$ff];
            $where[] = $formObjs[$form]->generateWhereClause( $limit, array( "I2CE_DataTree", "getSQLField" ) );
        }
        $where_clause = "";
        $order_by = "";
        if ( count($where) > 0 ) {
            $where_clause = " WHERE " . implode( ' AND ', $where );
        }
        if ( count( $all_orders ) > 0 ) {
            $order_by = " ORDER BY " . implode( ',', $all_orders );
        }
        $qry = "SELECT * FROM $report_table $where_clause $order_by";
        $db = I2CE::PDO();
        I2CE::raiseMessage($qry);
        try {
            $res = $db->query( $qry );
            $phonebook = array();
            $results = array();
            $display_string = array();
            $display_copy = $displays;
            while ( $data = $res->fetch() ) {
                foreach( $displays as $alias => $disp_data ) {
                    $id_field = strtolower( $alias . "+id" );
                    if ( $data->$id_field == null || array_key_exists( $data->$id_field, $phonebook ) ) {
                        continue;
                    }

                    $curr = array();
                    $add_this = false;
                    if ( in_array( $disp_data['form'], $forms ) ) {
                        $curr['value'] = $data->$id_field;
                        $add_this = true;
                    }

                    if ( !$add_this ) {
                        $check_ok = false;
                        foreach( $display_copy as $alias_copy => $disp_data_copy ) {
                            if ( $alias_copy == $alias ) {
                                $check_ok = true;
                                continue;
                            }
                            if ( !$check_ok ) {
                                continue;
                            }
                            if ( array_key_exists( 'link_field', $disp_data_copy )
                                    && $disp_data_copy['link_field'] != '' ) {
                                $link_field_copy = $disp_data_copy['link_field'];
                                if ( $data->$id_field == $data->$link_field_copy ) {
                                    $add_this = true;
                                    break;
                                }
                            }
                        }
                    }
                    if ( !$add_this ) {
                        continue;
                    }


                    if ( !array_key_exists( $disp_data['form'], $display_string ) ) {
                        $display_string[ $disp_data['form'] ] = I2CE_List::getDisplayString( $disp_data['form'] );
                    }
                    $disp_array = array();
                    $disp_str = $display_string[ $disp_data['form'] ];
                    $disp_str_arr = explode( '%s', $disp_str );
                    $fo = $formObjs[ $disp_data['form'] ];

                    $disp_count = 0;
                    foreach( $disp_data['fields'] as $field => $dbfield ) {
                        $disp_count++;
                        if ( $dbfield == $disp_data['link_field'] ) {
                            // Don't include the data from the link field since it will already be there.
                            if ( $disp_count == 1 ) {
                                unset( $disp_str_arr[$disp_count] );
                            } else {
                                unset( $disp_str_arr[$disp_count-1] );
                            }
                            continue;
                        }
                        $dbfield = strtolower( $dbfield );
                        $fieldObj = $fo->getField($field);
                        if ( !$fieldObj instanceof I2CE_FormField ) {
                            I2CE::raiseError( "Could not get field $field" );
                            continue;
                        }
                        if ( isset( $data->$dbfield ) ) {
                            $fieldObj->setFromDB( $data->$dbfield );
                            $disp_array[$field] = $fieldObj->getDisplayValue();
                        } else {
                            $disp_array[$field] = null;
                        }
                    }
                    $disp_str = implode( '%s', $disp_str_arr );
                    $display = vsprintf( $disp_str, $disp_array );
                    $curr['display'] = $display;

                    $phonebook[ $data->$id_field ] = &$curr;

                    if ( $disp_data['link_field'] != '' ) {
                        $link_field = $disp_data['link_field'];
                        if ( array_key_exists( $data->$link_field, $phonebook ) ) {
                            $add_to = &$phonebook[ $data->$link_field ];
                            if ( !array_key_exists( 'children', $phonebook[ $data->$link_field ] ) ) {
                                $phonebook[ $data->$link_field ][ 'children' ] = array();
                            }
                            $phonebook[ $data->$link_field ]['children'][] = &$curr;
                        } else {
                            //I2CE::raiseMessage( "Couldn't find $link_field " . $data->$link_field . " in phonebook " );
                        }
                    } else {
                        $results[] = &$curr;
                    }
                    unset( $curr );

                }
            }
        } catch ( PDOException $e ) {
            I2CE::pdoError( $res, "Invalid report data tree query: " );
            return array();
        }
        return $results;
    }
 

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
