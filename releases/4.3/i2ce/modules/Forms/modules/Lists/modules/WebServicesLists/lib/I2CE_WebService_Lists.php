<?php
/**
 * @copyright © 2014 Intrahealth International, Inc.
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
 * @since v4.2.0
 * @version v4.2.0
 */
/**
 * Handles web service actions for lists.
 * 
 * @package I2CE
 * @access public
 */
if (! class_exists('I2CE_WebService_Lists',false) ) {
    class I2CE_WebService_Lists extends I2CE_WebService {

        /**
         * Combine two sets of limits that are associated to a form into one 
         * limit/where array.
         * @param array $limits
         * @param array $add_limits
         * @param string $operator How to combine the limits, defaults to AND
         * @return array
         */
        public static function combineFormLimits( $limits, $add_limits, $operator='AND' ) {
            if ( is_array( $add_limits ) && count( $add_limits ) > 0 ) {
                if ( !is_array( $limits ) || count( $limits ) == 0 ) {
                    $limits = $add_limits;
                } else {
                    foreach( $add_limits as $form => $form_limits ) {
                        if ( !array_key_exists( $form, $limits ) || !is_array( $limits[$form] )
                                || count( $limits[$form] ) == 0 ) {
                            $limits[$form] = $form_limits;
                        } else {
                            $limits[$form] = array(
                                    'operator' => $operator,
                                    'operand' => array( $limits[$form], $form_limits ) 
                                    );
                        }
                    }
                }
            }
            return $limits;
        }

        /**
         * Remove the hidden signifier for displayed fields, e.g. [region], from the array
         * @param string &$value
         * @param mixed $key
         */
        protected static function removeHidden( &$value, $key ) {
            $len = strlen( $value );
            if ( $value[0] == '[' && $value[$len-1] == ']' ) {
                $value = substr( $value, 1, $len-2 );
            }
        }

        /**
         * Add or combine the given limit to the given limit array
         * @param array &$limits
         * @param string $key
         * @param string $field
         * @param string $value
         * @param string $style defaults to equals
         * @param string $operator How to combine limits if needed, defaults to 'AND'
         */
        protected static function addOrCombineFieldLimit( &$limits, $key, $field, $value, $style='equals', $operator='AND' ) {
            $limit = array(
                    'operator' => 'FIELD_LIMIT',
                    'field' => $field,
                    'style' => $style,
                    'data' => array(
                        'value' => $value,
                        ),
                    );
            if ( array_key_exists( $key, $limits ) ) {
                $new_limit = array();
                $new_limit[$key] = $limit;
                $limits = self::combineFormLimits( $limits, $new_limit, $operator );
            } else {
                $limits[$key] = $limit;
            }
        }
        
        /**
         * Split the given formfield on the last + for use in limits to match the form from getDisplayFields
         * @param string $formfield
         * @return array return $form and $field as an array.
         */
        protected static function explodeLimitField( $formfield ) {
            $arr = explode( '+', $formfield );
            $field = array_pop( $arr );
            $form = implode( '+', $arr );
            return array( $form, $field );
        }

        /**
         * Get the limits to be used for the current list based on any potentially "lower" limits to avoid
         * excess choices with nothing below.
         * @param array $form_limits
         * @param string $list
         * @param array $form_fields
         * @return array
         */
        protected function getCurrentListLimits( $form_limits, $list, $form_fields ) {

            if ( count( $form_limits ) == 1 && array_key_exists( $list, $form_limits ) ) {
                return $form_limits;
            }

            $ff = I2CE_FormFactory::instance();
            //foreach( $form_fields as $form_field ) {
            $max_idx = count($form_fields)-1;
            for( $i = 0; $i < $max_idx; $i++ ) {
                $form_field = str_replace( '[', '', str_replace( ']', '', $form_fields[$i] ) );
                if ( strpos( $form_field, '+' ) !== false ) {
                    list( $form, $field ) = explode( '+', $form_field, 2 );
                } else {
                    $form = $form_field;
                    $next_ff = str_replace( '[', '', str_replace( ']', '', $form_fields[$i+1] ) );
                    if ( strpos( $next_ff, '+' ) !== false ) {
                        list( $field, $notused ) = explode( '+', $next_ff, 2 );
                    } else {
                        $field = $next_ff;
                    }
                }
                if ( $list == $form ) {
                    break;
                }
                if ( array_key_exists( $form, $form_limits ) ) {
                    $matched = I2CE_FormStorage::listFields( $form, array( $field ), false, $form_limits[$form] );
                    $in = array();
                    foreach( $matched as $match ) {
                        if ( array_key_exists( $field, $match ) && strpos( $match[$field], '|' ) !== false ) {
                            list( $mf, $mid ) = explode( '|', $match[$field], 2 );
                            $in[] = $mid;
                        }
                    }
                    if ( count($in) > 0 ) {
                        self::addOrCombineFieldLimit( $form_limits, $field, "id", $in, "in" );
                    }
                }
            }

            return $form_limits;
        }
 
        /**
         * Perform any actions
         * 
         * @return boolean  true on sucess
         */
        protected function action() {
            /*
             * XX lists/form/position/facility+location/county/district?district%2Bregion=region|1
             * XX lists/field/person_position+facility/default/??
             * lists/LIST/[FORM[+FIELD]/[STYLE]]?[limit={JSON}&FORM[+FIELD]=VALUE
             * lists/district/position+facility?country=country|TF
             * lists/facility?facility+location=country|TF
             */
            $disp_list = array_shift( $this->request_remainder );
            if ( ($pluspos = strpos( $disp_list, '+' )) !== false ) {
                $list = substr( $disp_list, 0, $pluspos );
            } else {
                $list = $disp_list;
            }

            $is_public = false;
            I2CE::getConfig()->setIfIsSet( $is_public, "/modules/forms/forms/$list/meta/list/is_public" );
            if ( !$is_public ) {
                $task = "can_view_database_list_$list";
                if ( !$this->hasPermission("task($task)" ) ) {
                    $this->setError( 'noaccess_page' );
                    return false;
                }
            }
            $listObj = I2CE_FormFactory::instance()->createContainer( $list );
            if ( !$listObj instanceof I2CE_List ) {
                $this->setError( 'invalid_list', array( $list ) );
            }
            $style = 'default';
            $fields = array();
            $forms = array( $list );
            $where = array();
            $report = null;
            $get = $this->request();
            if ( array_key_exists( 'limit', $get ) ) {
                $where = json_decode( $get['limit'] );
                unset( $get['limit'] );
            }
            $enclose_array = false;
            if ( array_key_exists( 'array', $get ) ) {
                if ( $get['array'] ) {
                    $enclose_array = true;
                }
                unset( $get['array'] );
            }
            if ( count($this->request_remainder) > 0 ) {
                $formfield = array_shift( $this->request_remainder );

                if ( $formfield[0] == '[' && $formfield[strlen($formfield)-1] == ']' ) {
                    $formfield = substr( $formfield, 1, strlen($formfield)-2 );
                    if ( strpos( $formfield, '+' ) === false ) {
                        $this->setError( 'invalid_field', array( $formfield, $list ) );
                        return true;
                    } else {
                        list( $form, $field ) = explode( '+', $formfield, 2 );
                        $init_options = array(
                                'root_path' => '/modules/CustomReports/relationships',
                                'root_type' => 'FormRelationships',
                                );
                        $form_rel = new I2CE_FormRelationship( $form );
                        $func = $form_rel->getFunctionDetails( $field );
                        if ( array_key_exists( $field, $func ) && array_key_exists( 'field', $func[$field] ) ) {
                            $fieldObj = $func[$field]['field'];
                        } else {
                            I2CE::raiseError("Unable to get function field for $field on $form" );
                            $this->setError( 'invalid_field', array( $field, $form ) );
                            return true;
                        }
                    }
                } else {
                    if ( strpos( $formfield, '+' ) === false ) {
                        $form = $formfield;
                        $field = $list;
                    } else {
                        list( $form, $field ) = explode( '+', $formfield, 2 );
                    }
                    $formObj = I2CE_FormFactory::instance()->createContainer( $form );
                    if ( !$formObj instanceof I2CE_Form ) {
                        $this->setError( 'invalid_form', array( $form ) );
                    }
                    if ( !$formObj->hasField( $field ) ) {
                        $this->setError( 'invalid_field', array( $field, $form ) );
                        return true;
                    }
                    $fieldObj = $formObj->getField( $field );
                }
                if ( !$fieldObj instanceof I2CE_FormField_MAPPED ) {
                    $this->setError( 'invalid_field', array( $field, $form ) );
                }
                if ( count($this->request_remainder) > 0 ) {
                    $style = array_shift( $this->request_remainder );
                }
                $form_limits = $fieldObj->getFormLimits( $style );
                $form_fields = $fieldObj->getDisplayedFields( $style );
                $report = $fieldObj->getDisplayReport( $style );
                $form_limits = $this->getCurrentListLimits( $form_limits, $list, $form_fields );
                if ( array_key_exists( $disp_list, $form_limits ) ) {
                    $form_limits[$list] = $form_limits[$disp_list];
                }
                array_walk( $form_fields, 'self::removeHidden' );
                $add_where = array();
                foreach( $get as $formfield => $limit ) {
                    if ( ( $form_key = array_search( $formfield, $form_fields ) ) !== false ) {
                        if ( !is_array( $limit ) ) {
                            $limit = array( 'equals' => $limit );
                        }
                        unset( $get[$formfield] );
                        if ( $form_key == 0 ) {
                            if ( strpos( $formfield, '+' ) !== false ) {
                                list( $form, $field ) = self::explodeLimitField( $formfield );
                                foreach( $limit as $limit_style => $value ) {
                                    self::addOrCombineFieldLimit( $add_where, $form, $field, $value, $limit_style );
                                }
                            }
                        } else {
                            $prev_key = $form_key-1;
                            $prev_form = $form_fields[$prev_key];
                            $curr_form = $form_fields[$form_key];
                            $new_limit = array();
                            if ( strpos( $prev_form, '+' ) === false ) {
                                if ( ($curr_pos = strpos( $curr_form, '+' ) ) !== false ) {
                                    $curr_form = substr( $curr_form, 0, $curr_pos );
                                }
                                foreach( $limit as $limit_style => $value ) {
                                    self::addOrCombineFieldLimit( $add_where, $prev_form, $curr_form, $value, $limit_style );
                                }
                            } else {
                                list( $form, $field ) = self::explodeLimitField( $prev_form );
                                foreach( $limit as $limit_style => $value ) {
                                    self::addOrCombineFieldLimit( $add_where, $form, $field, $value, $limit_style );
                                }
                            }
                        }
                    }
                }
                $form_limits = self::combineFormLimits( $form_limits, $add_where );
                if ( !array_key_exists( $list, $form_limits ) ) {
                    $skip = true;
                    foreach( $form_fields as $ff ) {
                        if ( ($pluspos = strpos( $ff, '+' )) !== false ) {
                            $ff_form = substr( $ff, 0, $pluspos );
                        } else {
                            $ff_form = $ff;
                        }
                        $ff = str_replace( '[', '', str_replace( ']', '', $ff ) );
                        if ( $ff_form == $list ) {
                            $skip = false;
                        }
                        if ( $skip ) {
                            continue;
                        }
                        $fields[] = $ff;
                        if ( array_key_exists( $ff_form, $form_limits ) ) {
                            break;
                        }
                    }
                } else {
                    $fields[] = $list;
                }
                $where = self::combineFormLimits( $where, $form_limits );
            } else {
                $fields[] = $list;
            }
            if ( count( $get ) > 0 ) {
                $add_where = array();
                foreach( $get as $formfield => $limit ) {
                    if ( !is_array( $limit ) ) {
                        $limit = array( 'equals' => $limit );
                    }
                    if ( strpos( $formfield, '+' ) !== false ) {
                        list( $form, $field ) = self::explodeLimitField( $formfield );
                        foreach( $limit as $limit_style => $value ) {
                            self::addOrCombineFieldLimit( $add_where, $form, $field, $value, $limit_style );
                        }
                    } else {
                        foreach( $limit as $limit_style => $value ) {
                            self::addOrCombineFieldLimit( $add_where, $list, $formfield, $value, $limit_style );
                        }
                     }
                }
                $where = self::combineFormLimits( $where, $add_where );
            }
            foreach( $where as $w_form => $w_limits ) {
                if ( !in_array( $w_form, $fields ) ) {
                    unset( $where[$w_form] );
                }
            }
            $this->data = array( 'list' => $list );
            //$this->data['data'] = I2CE_List::listOptions( $list );
            //$results = I2CE_DataTree::flattenDataTree( I2CE_DataTree::buildDataTree( array( 'position', 'facility+location', 'county', 'district' ), array( 'position' ), $where ), true );
            //I2CE::raiseMessage("building tree for " . print_r($fields,true).print_r($forms,true).print_r($where,true).print_r($report,true));
            $results = I2CE_DataTree::flattenDataTree( I2CE_DataTree::buildDataTree( $fields, $forms, $where, array(), 0, $report, $style ), true );
            $this->data['length'] = count( $results );
            $this->data['data'] = $results;
            if ( $enclose_array ) {
                $this->data = array( $this->data );
            }

            return true;
        }

        /**
         * Perform the field action.
         * @return boolean
         */
        protected function action_field() {
            $style = array_shift( $this->request_remainder );
           
 
        }

        /**
         * Main display method for web interface
         * @param boolean $supress_output  defaults to false.  set to true to supress the output of a webpage
         */
        /*
        protected function displayWeb($supress_output = false) {
            $i2ce_config = I2CE::getConfig()->I2CE;
            if (!$this->initPage()) {
                if ( !$this->user->logged_in() ) {
                    $this->setError('noaccess'); //defined in I2CE
                }
                return;
            }
            $permission = 'role(' . implode(",",$this->access) . ')';
            if (array_key_exists('tasks',$this->args) && is_array($this->args['tasks']) && count($this->args['tasks'])>0) {
                $permission .= ' | task(' . implode(',',$this->args['tasks']) . ')';
            }
            if ($this->hasPermission($permission)) {
                if ($this->action() === false) {
                    if ( !$this->hasError() ) {
                        $this->setError( 'unknown_error' );
                    }
                }
            } else{
                if ( $this->user->logged_in()) {
                    $this->setError( 'noaccess_page', array( $this->page ) );
                }
                if ( !$this->hasError() ) {
                    if (!$this->user->logged_in()) {                            
                        $this->setError( 'noaccess' );
                    }
                }
            }                   
        }
        */
    
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
