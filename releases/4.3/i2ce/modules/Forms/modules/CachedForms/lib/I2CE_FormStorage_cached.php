<?php
/**
* © Copyright 2011, 2012 IntraHealth International, Inc.
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
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.1.1
* @since v4.1.1
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_cached
* 
* @access public
*/


class I2CE_FormStorage_cached extends I2CE_FormStorage_flat {

    /**
     * @var boolean Determines if ids should show up in the getFields query.
     */
    protected $preserve_ids;


    /**
     * Gets the storage options for the given form.
     * Since this is the cached version for this form then
     * we don't want to use any options set for that and 
     * use what's needed for the cached tables.
     * @param string $form
     * @return I2CE_MagicDataNode
     */
    protected function getStorageOptions($form) {
        if ( !is_scalar( $form ) ) {
            I2CE::raiseError( "Bad call to get storage options for $form");
            return false;
        }
        $config = I2CE_MagicData::instance( "temp_Module_CachedForm_storage_options" );
        $config->table = I2CE_CachedForm::getCachedTableName( $form );
        if ( $this->preserve_ids ) {
            $config->id->form_prepended = 0;
        } else {
            $config->id->form_prepended = 1;
        }
        return $config;
    }

    /**
     * Generates the SQL to select the required fields.
     * This makes sure the form is cached and up to date.
     * See I2CE_FormStoage_DB for additional comments on parameters.
     * @see I2CE_FormStorage_DB::getRecords
     */
    public function getFields($form, $fields = array(), $parent,
            $where_data = array(), $ordering = array(), $limit = false,
            $field_reference_callback = null, $mod_time = -1, $user=false ) {
        try {
            $cachedForm = new I2CE_CachedForm( $form );
        } catch(Exception $e) {
            I2CE::raiseError("Could not cache form $form");
            return false;
        }
        $cachedForm->generateCachedTable();
        unset( $cachedForm );
        return parent::getFields( $form, $fields, $parent, $where_data,
                $ordering, $limit, $field_reference_callback, $mod_time, $user );
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
     * Return the field name with backticks for SQL queries
     * given the form and field.
     * @param string $form The form name.
     * @param string $field The field name.
     * @return string
     */
    public static function getSQLField( $form, $field ) {
        return "`$field`"; 
    }

    /**
     * Build the data tree for the given list of fields 
     * and limits.  This is called by I2CE_List::buildDataTree.
     * See that for more details
     * @see I2CE_List::buildDataTree
     * @param array $fields The fields to build the tree
     * @param array $forms The selectable forms
     * @param array $displayed The displayed forms for the tree
     * @param array $limits The list of limits for each form.
     * @param array $orders The order fields for each given form
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.  Defaults to 0
     * @return array The ordered list of all entries in the tree.
     */
    public function buildDataTree( $fields, $forms, $displayed,
                                   $limits, $orders, $show_hidden = 0 ,$style = 'default') { 

        $prev_form = false;
        $form_aliases = array();
        $selects = array();
        $displays = array();
        $all_orders = array();
        $formObjs = array();
        $skip_links = array();
        $skip_link_fields = array();
        $this->preserve_ids = true;


        $last_form = false;
        $styles = array();
        end($fields);
        $formfield  = current($fields);
        if (is_array($formfield)) {
            list($last_form,$link_field) = $formfield;        
        }
        if ($last_form) {
            $ff = I2CE_FormFactory::instance();
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

        foreach( $fields as $formfield ) {
            list( $form, $link_field ) = $formfield;
            $cachedForm = new I2CE_CachedForm( $form );
            $cachedForm->generateCachedTable();
            unset( $cachedForm );

            $alias_form = $form . ($link_field ? "+$link_field" : "" );
            if ( array_key_exists( $alias_form, $limits ) ) {
                $limit = $limits[$alias_form];
            } elseif ( array_key_exists( $form, $limits ) ) {
                $limit = $limits[$form];
            } else {
                $limit = array();
            }
            self::addFormIdToLimit( $form, $limit );

            $limit = I2CE_List::showHiddenLimit( $limit, $show_hidden );

            $style ='default';
            if (array_key_exists($form. '+' . $link_field,$styles)) {
                $style = $styles[$form . '+' . $link_field];
            }

            $disp_fields = I2CE_List::getDisplayFields( $form,$style );
            $field_list = $disp_fields;

            $field_list[] = 'id';
            if ( $link_field && !in_array( $link_field, $field_list ) ) {
                $field_list[] = $link_field;
            }

            if ( array_key_exists( $form, $orders ) ) {
                $order = $orders[$form];
            } else {
                $order = I2CE_List::getSortFields( $form ,$style);
            }

            $sort_list = array();
            if ( array_key_exists( $form, $displayed ) 
                    && $displayed[$form] ) {
                $displays[$alias_form]['form'] = $form;
                $displays[$alias_form]['link_field'] = $link_field;
                foreach( $disp_fields as $disp ) {
                    $displays[$alias_form]['fields'][$disp] = "$alias_form+$disp";
                }
                foreach( $order as $i => $ord ) {
                    if ( !is_string( $ord ) ) {
                        unset( $order[$i] );
                        continue;
                    }
                    if ( $ord[0] == '-' ) {
                        $field = substr( $ord, 1 );
                        $all_orders[] = "`$alias_form`.`$field` DESC";
                    } else {
                        $field = $ord;
                        $all_orders[] = "`$alias_form`.`$field` ASC";
                    }
                    if ( !in_array( $field, $field_list ) ) {
                        $sort_list[] = $field;
                    }
                }
            } else {
                $skip_link_fields[$alias_form] = $link_field;
            }

            if ( !array_key_exists( $form, $formObjs ) ) {
                $formObjs[$form] = $this->getContainer( $form );
                if ( !$formObjs[$form] instanceof I2CE_Form ) {
                    I2CE::raiseError( "Could not instantiate $form" );
                    return array();
                }
            }
    

            $where =false;
            if (is_array($limit) && count($limit) > 0) {
                $where = $formObjs[$form]->generateWhereClause( $limit, array( "I2CE_FormStorage_cached", "getSQLField" ) );
            }
            $query = $this->getRequiredFieldsQuery( $form, 
                    array_merge( $field_list, $sort_list ), 
                    null, false, array( "I2CE_FormStorage_cached", "getSQLField" ) );

            if ( is_array($prev_form) 
                    && array_key_exists( 'form', $prev_form ) ) {
                if ( $prev_form['form'] == $form ) {
                    $froms[$alias_form] = " LEFT JOIN ";
                } else {
                    $froms[$alias_form] = " LEFT JOIN ";
                }
            } else {
                $froms[$alias_form] = "";
            }
            $froms[$alias_form] .= "($query" . ($where ? " WHERE " . $where : "" ) . ") AS `$alias_form`";
            if ($link_field) {
                $join_ons = array();
                $ff = $formObjs[$form]->getField($link_field);
                if ( $ff instanceof I2CE_FormField_MAPPED ) {
                    foreach( $ff->getSelectableForms() as $link_form ) {
                        if ( array_key_exists( $link_form, $form_aliases ) ) {
                            foreach( $form_aliases[$link_form] as $link_alias ) {
                                $join_ons[] = "`$alias_form`.`$link_field` = `$link_alias`.`id`";
                                if ( !array_key_exists( $link_alias, $displays ) && array_key_exists( $link_alias, $skip_link_fields ) ) {
                                    $skip_links[ $alias_form . '+' . $link_field ] = $link_alias . '+' . $skip_link_fields[$link_alias];
                                }
                            }
                        }
                    }
                } else {
                    I2CE::raiseMessage( "Not sure what to link for $form $link_field so guessing." );
                    $join_ons[] = "`$alias_form`.`$link_field` = `" . $prev_form['alias'] . "`.`id`";
                }
                if ( count( $join_ons ) > 0 ) {
                    $froms[$alias_form] .= " ON (" 
                        . implode( ' OR ', $join_ons ) . ") ";
                } else {
                    I2CE::raiseError("Don't know how to join on $form $link_field");
                    return array();
                }
            }
            foreach( $field_list as $field ) {
                $selects[] = "`$alias_form`.`$field` AS `$alias_form+$field`";
            }
            

            $prev_form = array( 'form' => $form, 'alias' => $alias_form );
            $form_aliases[$form][] = $alias_form;
            $last_form = $form;
        }

        $or_wheres = array();
        foreach( $forms as $selectable ) {
            if( array_key_exists( $selectable, $form_aliases ) ) {
                foreach( $form_aliases[$selectable] as $required_form ) {
                    $or_wheres[] = "`$required_form`.`id` IS NOT NULL";
                }
            }
        }
        $join_query = "SELECT " . implode(',', $selects ) 
            . " FROM " . implode( '', $froms ) 
            . (count($or_wheres) > 0 ? " WHERE "
                    . implode( ' OR ', $or_wheres ) : "" )
            . (count($all_orders) > 0 ? " ORDER BY " 
                    . implode( ',', $all_orders ) : "" );
        //I2CE::raiseMessage( $join_query );

        try {
            $res = $this->db->query( $join_query );
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Bad query -- $join_query" );
            return array();
        }
        $prev_ids = array();
        $results = array();
        $phonebook = array();
        $display_string = array();
        foreach( $displays as $fix_link_alias => &$fix_link_disp_data ) {
            if ( array_key_exists( 'link_field', $fix_link_disp_data ) 
                    && $fix_link_disp_data['link_field'] != '' ) {
                $full_link_field = strtolower( $fix_link_alias . "+" 
                        . $fix_link_disp_data['link_field'] );
                while( array_key_exists( $full_link_field, $skip_links ) ) {
                    $full_link_field = $skip_links[$full_link_field];
                }
                $fix_link_disp_data['link_field'] = $full_link_field;
            }
        }
        $display_copy = $displays;
        while( $data = $res->fetch() ) {
            unset( $prev_disp );
            foreach( $displays as $alias => $disp_data ) {
                $id_field = strtolower($alias . "+id");
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
                $style ='default';
                if (array_key_exists($disp_data['form'] . '+' . $disp_data['link_field'],$styles)) {
                    $style = $styles[$disp_data['form'] . '+' . $disp_data['link_field']];
                }


                if ( !array_key_exists( $disp_data['form'], $display_string ) ) {
                    $display_string[ $disp_data['form'] ] = I2CE_List::getDisplayString( $disp_data['form'] , $style);
                }
                $disp_array = array();
                $disp_str = $display_string[ $disp_data['form'] ];
                $disp_str_arr = explode( "%s", $disp_str );
                $fo = $formObjs[$disp_data['form']];


                $disp_count = 0;
                foreach( $disp_data['fields'] as $field => $dbfield ) {
                    $disp_count++;
                    if ( $field == $disp_data['link_field'] ) {
                        // Don't include the data from the link field since it will already
                        // be in the tree above it.
                        if ( $disp_count == 1 ) {
                            unset( $disp_str_arr[$disp_count] );
                        } else {
                            unset( $disp_str_arr[$disp_count-1] );
                        }
                        continue;
                    }
                    $dbfield = strtolower($dbfield);
                    $fieldObj = $fo->getField($field);
                    if ( !$fieldObj instanceof I2CE_FormField ) {
                        I2CE::raiseError( "Could not get field $field" );
                        continue;
                    }
                    if ( isset( $data->$dbfield ) ) {
                        $fieldObj->setFromDB( $data->$dbfield );
                        $disp_array[$field] = $fieldObj->getDisplayValue(false,$style);
                    } else {
                        $disp_array[$field] = null;
                    }
                }
                $disp_str = implode( '%s', $disp_str_arr );
                $display = vsprintf( $disp_str, $disp_array );
 
                //$display = $data->$disp_data['fields']['name'];
                $curr['display'] = $display;
 
                $phonebook[ $data->$id_field ] = &$curr;

                if ( $disp_data['link_field'] != '' ) {
                    $link_field = $disp_data['link_field'];
                    if ( array_key_exists( $data->$link_field, $phonebook ) ) {
                        $add_to = &$phonebook[ $data->$link_field ];
                        if ( !array_key_exists( 'children', $phonebook[ $data->$link_field ] ) ) {
                            $phonebook[ $data->$link_field ]['children'] = array();
                        }
                        $phonebook[ $data->$link_field ]['children'][] = &$curr;
                    } else {
                        //I2CE::raiseMessage("Couldn't find $link_field " . $data->$link_field . " in phonebook");
                    }
                } else {
                    $results[] = &$curr;
                }
                unset( $curr );

            }
        }

        $this->preserve_ids = false;
        return $results;

    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:

