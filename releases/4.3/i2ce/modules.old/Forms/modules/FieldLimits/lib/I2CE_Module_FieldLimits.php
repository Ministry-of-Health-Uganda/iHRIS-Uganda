<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
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
*  I2CE_Module_FormRelationship
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Module_FieldLimits extends I2CE_Module {



    /**
     * The 'fuzzy' methods that this module implements.
     * @returns an associative array.
     */
    public static function getMethods() {
        $ret =   array(

            'I2CE_FormField->getLimitStyles'=>'getFieldLimitStyles',
            'I2CE_FormField->generateLimit'=>'generateFieldLimit',

            'I2CE_FormField->describeLimit'=>'describeFieldLimit',

            'I2CE_FormField->generateLimit_null'=>'generateLimit_null',
            'I2CE_FormField->generateLimit_not_null'=>'generateLimit_not_null',
            'I2CE_FormField->generateLimit_null_not_null'=>'generateLimit_null_not_null',
            'I2CE_FormField->checkLimit_null'=>'checkLimit_null',
            'I2CE_FormField->checkLimit_not_null'=>'checkLimit_not_null',
            'I2CE_FormField->checkLimit_null_not_null'=>'checkLimit_null_not_null',
            'I2CE_FormField->checkLimitString_null'=>'checkLimitString_null',
            'I2CE_FormField->checkLimitString_not_null'=>'checkLimitString_not_null',
            'I2CE_FormField->checkLimitString_null_not_null'=>'checkLimitString_null_not_null',
            'I2CE_FormField->checkLimitFunction_null'=>'checkLimitFunction_null',
            'I2CE_FormField->checkLimitFunction_not_null'=>'checkLimitFunction_not_null',
            'I2CE_FormField->checkLimitFunction_null_not_null'=>'checkLimitFunction_null_not_null',
            'I2CE_FormField->getLimitMenu_null'=>'I2CE_FormField_DISPLAYFIELDSTYLE_null',
            'I2CE_FormField->getLimitMenu_not_null'=>'I2CE_FormField_DISPLAYFIELDSTYLE_not_null',
            'I2CE_FormField->getLimitMenu_null_not_null'=>'I2CE_FormField_DISPLAYFIELDSTYLE_null_not_null',
            'I2CE_FormField->processLimitMenu_null'=>'I2CE_FormField_PROCESSFIELDSTYLE_null',
            'I2CE_FormField->processLimitMenu_not_null'=>'I2CE_FormField_PROCESSFIELDSTYLE_not_null',
            'I2CE_FormField->processLimitMenu_null_not_null'=>'I2CE_FormField_PROCESSFIELDSTYLE_null_not_null',
            'I2CE_FormField_DB_DATE->generateLimit_null'=>'generateLimit_DB_DATE_null',
            'I2CE_FormField_DB_DATE->generateLimit_not_null'=>'generateLimit_DB_DATE_not_null',
            'I2CE_FormField_DB_DATE->generateLimit_null_not_null'=>'generateLimit_DB_DATE_null_not_null',
            'I2CE_FormField_DB_DATE->checkLimit_null'=>'checkLimit_DB_DATE_null',
            'I2CE_FormField_DB_DATE->checkLimit_not_null'=>'checkLimit_DB_DATE_not_null',
            'I2CE_FormField_DB_DATE->checkLimit_null_not_null'=>'checkLimit_DB_DATE_null_not_null',
            'I2CE_FormField_DB_DATE->checkLimitString_null'=>'checkLimitString_DB_DATE_null',
            'I2CE_FormField_DB_DATE->checkLimitString_not_null'=>'checkLimitString_DB_DATE_not_null',
            'I2CE_FormField_DB_DATE->checkLimitString_null_not_null'=>'checkLimitString_DB_DATE_null_not_null',
            'I2CE_FormField_DB_DATE->checkLimitFunction_null'=>'checkLimitFunction_DB_DATE_null',
            'I2CE_FormField_DB_DATE->checkLimitFunction_not_null'=>'checkLimitFunction_DB_DATE_not_null',
            'I2CE_FormField_DB_DATE->checkLimitFunction_null_not_null'=>'checkLimitFunction_DB_DATE_null_not_null',

            'I2CE_FormField->generateLimit_max_parent'=>'generateLimit_max_parent',
            'I2CE_FormField->generateLimit_min_parent'=>'generateLimit_min_parent',
            'I2CE_FormField->generateLimit_max_parent_form'=>'generateLimit_max_parent_form',
            'I2CE_FormField->generateLimit_min_parent_form'=>'generateLimit_min_parent_form',
            'I2CE_FormField->getLimitMenu_max_parent'=>'I2CE_FormField_DISPLAYFIELDSTYLE_max_parent',
            'I2CE_FormField->getLimitMenu_min_parent'=>'I2CE_FormField_DISPLAYFIELDSTYLE_min_parent',
            'I2CE_FormField->getLimitMenu_max_parent_form'=>'I2CE_FormField_DISPLAYFIELDSTYLE_max_parent_form',
            'I2CE_FormField->getLimitMenu_min_parent_form'=>'I2CE_FormField_DISPLAYFIELDSTYLE_min_parent_form',
            'I2CE_FormField->processLimitMenu_max_parent'=>'I2CE_FormField_PROCESSFIELDSTYLE_max_parent',
            'I2CE_FormField->processLimitMenu_min_parent'=>'I2CE_FormField_PROCESSFIELDSTYLE_min_parent',
            'I2CE_FormField->processLimitMenu_max_parent_form'=>'I2CE_FormField_PROCESSFIELDSTYLE_max_parent_form',
            'I2CE_FormField->processLimitMenu_min_parent_form'=>'I2CE_FormField_PROCESSFIELDSTYLE_min_parent_form',



            'I2CE_FormField_BOOL->generateLimit_truefalse'=>'generateLimit_BOOL_truefalse',
            'I2CE_FormField_BOOL->generateLimit_true'=>'generateLimit_BOOL_true',
            'I2CE_FormField_BOOL->generateLimit_false'=>'generateLimit_BOOL_false',

            'I2CE_FormField_YESNO->generateLimit_yesno'=>'generateLimit_YESNO_yesno',
            'I2CE_FormField_YESNO->generateLimit_yes'=>'generateLimit_YESNO_yes',
            'I2CE_FormField_YESNO->generateLimit_no'=>'generateLimit_YESNO_no',


            'I2CE_FormField_DB_INT->generateLimit_in'=>'generateLimit_DB_INT_in',
            'I2CE_FormField_DB_FLOAT->generateLimit_in'=>'generateLimit_DB_FLOAT_in',
            'I2CE_FormField_MAP_MULT->generateLimit_in'=>'generateLimit_MAP_MULT_in',
            'I2CE_FormField_DB_STRING->generateLimit_in'=>'generateLimit_DB_STRING_in',
            'I2CE_FormField_DB_TEXT->generateLimit_in'=>'generateLimit_DB_TEXT_in',
            'I2CE_FormField_DB_DATE->generateLimit_in'=>'generateLimit_DB_DATE_in',

            'I2CE_FormField_DB_INT->generateLimit_equals'=>'generateLimit_DB_INT_equals',
            'I2CE_FormField_DB_FLOAT->generateLimit_equals'=>'generateLimit_DB_FLOAT_equals',
            'I2CE_FormField_MAP_MULT->generateLimit_equals'=>'generateLimit_MAP_MULT_equals',
            'I2CE_FormField_DB_STRING->generateLimit_equals'=>'generateLimit_DB_STRING_equals',
            'I2CE_FormField_DB_TEXT->generateLimit_equals'=>'generateLimit_DB_TEXT_equals',
            'I2CE_FormField_MAP->generateLimit_within'=>'generateLimit_MAP_within',



            'I2CE_FormField_DB_INT->generateLimit_greaterthan'=>'generateLimit_DB_INT_greaterthan',
            'I2CE_FormField_DB_FLOAT->generateLimit_greaterthan'=>'generateLimit_DB_FLOAT_greaterthan',
            'I2CE_FormField_DB_STRING->generateLimit_greaterthan'=>'generateLimit_DB_STRING_greaterthan',
            'I2CE_FormField_DB_TEXT->generateLimit_greaterthan'=>'generateLimit_DB_TEXT_greaterthan',

            'I2CE_FormField_DB_INT->generateLimit_lessthan'=>'generateLimit_DB_INT_lessthan',
            'I2CE_FormField_DB_FLOAT->generateLimit_lessthan'=>'generateLimit_DB_FLOAT_lessthan',
            'I2CE_FormField_DB_STRING->generateLimit_lessthan'=>'generateLimit_DB_STRING_lessthan',
            'I2CE_FormField_DB_TEXT->generateLimit_lessthan'=>'generateLimit_DB_TEXT_lessthan',

            'I2CE_FormField_DB_INT->generateLimit_greaterthan_equals'=>'generateLimit_DB_INT_greaterthan_equals',
            'I2CE_FormField_DB_FLOAT->generateLimit_greaterthan_equals'=>'generateLimit_DB_FLOAT_greaterthan_equals',
            'I2CE_FormField_DB_STRING->generateLimit_greaterthan_equals'=>'generateLimit_DB_STRING_greaterthan_equals',
            'I2CE_FormField_DB_TEXT->generateLimit_greaterthan_equals'=>'generateLimit_DB_TEXT_greaterthan_equals',

            'I2CE_FormField_DB_INT->generateLimit_lessthan_equals'=>'generateLimit_DB_INT_lessthan_equals',
            'I2CE_FormField_DB_FLOAT->generateLimit_lessthan_equals'=>'generateLimit_DB_FLOAT_lessthan_equals',
            'I2CE_FormField_DB_STRING->generateLimit_lessthan_equals'=>'generateLimit_DB_STRING_lessthan_equals',
            'I2CE_FormField_DB_TEXT->generateLimit_lessthan_equals'=>'generateLimit_DB_TEXT_lessthan_equals',


            'I2CE_FormField_DB_INT->generateLimit_between'=>'generateLimit_DB_INT_between',
            'I2CE_FormField_DB_FLOAT->generateLimit_between'=>'generateLimit_DB_FLOAT_between',
            'I2CE_FormField_DB_STRING->generateLimit_between'=>'generateLimit_DB_STRING_between',
            'I2CE_FormField_DB_TEXT->generateLimit_between'=>'generateLimit_DB_TEXT_between',



            'I2CE_FormField_DB_DATE->generateLimit_greaterthan_now'=>'generateLimit_greaterthan_now',
            'I2CE_FormField_DB_DATE->generateLimit_lessthan_now'=>'generateLimit_lessthan_now',

            'I2CE_FormField_DB_DATE->generateLimit_greaterthan_equals_now'=>'generateLimit_greaterthan_equals_now',
            'I2CE_FormField_DB_DATE->generateLimit_lessthan_equals_now'=>'generateLimit_lessthan_equals_now',

            'I2CE_FormField_DB_STRING->generateLimit_like'=>'generateLimit_DB_STRING_like',
            'I2CE_FormField_DB_TEXT->generateLimit_like'=>'generateLimit_DB_TEXT_like',

            'I2CE_FormField_DB_STRING->generateLimit_lowerlike'=>'generateLimit_DB_STRING_lowerlike',
            'I2CE_FormField_DB_TEXT->generateLimit_lowerlike'=>'generateLimit_DB_TEXT_lowerlike',

            'I2CE_FormField_DB_STRING->generateLimit_lowerequals'=>'generateLimit_DB_STRING_lowerequals',
            'I2CE_FormField_DB_TEXT->generateLimit_lowerequals'=>'generateLimit_DB_TEXT_lowerequals',


            'I2CE_FormField_DB_STRING->generateLimit_contains'=>'generateLimit_DB_STRING_contains',
            'I2CE_FormField_DB_TEXT->generateLimit_contains'=>'generateLimit_DB_TEXT_contains',

            'I2CE_FormField_DB_STRING->generateLimit_startswith'=>'generateLimit_DB_STRING_startswith',
            'I2CE_FormField_DB_TEXT->generateLimit_startswith'=>'generateLimit_DB_TEXT_startswith',
            );
        //now begins some laziness for the displaying and processing of the limit menus as well as checkLimit
        $t_ret = array();
        foreach ($ret as $fuzzy=>$method) {
            if (!preg_match('/^I2CE_FormField_([A-Za-z0-9_]+)->generateLimit_([a-zA-Z_0-9]+)$/',$fuzzy,$matches)) {
                continue;
            }
            list($all,$shortclass,$style) = $matches;
            $class = 'I2CE_FormField_' . $shortclass;
            $t_ret[$class . '->' . 'checkLimit_'  . $style] = 'checkLimit_' . $shortclass . '_' . $style;
            $t_ret[$class . '->' . 'checkLimitString_'  . $style] = 'checkLimitString_' . $shortclass . '_' . $style;
            $t_ret[$class . '->' . 'checkLimitFunction_'  . $style] = 'checkLimitFunction_' . $shortclass . '_' . $style;

            $t_ret[$class . '->' . 'getLimitMenu_'  . $style] = $class . '_DISPLAYFIELDSTYLE_' . $style;
            $t_ret[$class . '->' . 'processLimitMenu_'  . $style] = $class . '_PROCESSFIELDSTYLE_' . $style;
        }
        $ret = array_merge($ret,$t_ret);
        //add in the dates limits with one selection
        foreach (self::$dateOperatorMaps as $key1=>$val1) {
            foreach (self::$dateTypes as $key2=>$vals2) {
                $ret["I2CE_FormField_{$key2}->generateLimit_{$key1}"] = "DATE_generateLimit_{$key2}_{$key1}";
                $ret["I2CE_FormField_{$key2}->checkLimit_{$key1}"] = "DATE_checkLimit_{$key2}_{$key1}";
                $ret["I2CE_FormField_{$key2}->checkLimitString_{$key1}"] = "DATE_checkLimit_{$key2}_{$key1}";
                $ret["I2CE_FormField_{$key2}->checkLimitFunction_{$key1}"] = "DATE_checkLimitFunction_{$key2}_{$key1}";
                $ret["I2CE_FormField_{$key2}->getLimitMenu_{$key1}"] = "DATE_getLimitMenu_{$key2}_{$key1}";
                $ret["I2CE_FormField_{$key2}->processLimitMenu_{$key1}"] = "DATE_processLimitMenu_{$key2}_{$key1}";
            }
        }
        //now do the between date which is not handled by the above nested foreach b/c it has two values
        foreach (self::$dateTypes as $type=>$vals) {
            $ret['I2CE_FormField_' . $type . '->getLimitMenu_between']='DATE_getLimitMenu_' . $type . '_between';
            $ret['I2CE_FormField_' . $type . '->generateLimit_between']='DATE_generateLimit_' . $type . '_between';
            $ret['I2CE_FormField_' . $type . '->checkLimit_between']='DATE_checkLimit_' . $type . '_between';
            $ret['I2CE_FormField_' . $type . '->checkLimitString_between']='DATE_checkLimitString_' . $type . '_between';
            $ret['I2CE_FormField_' . $type . '->checkLimitFunction_between']='DATE_checkLimitFunction_' . $type . '_between';
            $ret['I2CE_FormField_' . $type . '->processLimitMenu_between']='DATE_processLimitMenu_' . $type . '_between';
        }
        return $ret;
    }

    protected static $dateTypes = array(
        'DATE_YMD'=>I2CE_Date::DATE,
        'DATE_MD'=>I2CE_Date::MONTH_DAY,
        'DATE_YM'=>I2CE_Date::YEAR_MONTH,
        'DATE_Y'=>I2CE_Date::YEAR_ONLY,
        'DATE_HMS'=>I2CE_Date::TIME_ONLY,
        'DATE_TIME'=>I2CE_Date::DATE_TIME
        );
    protected static $dateActions = array(
        'getLimitMenu'=>'getDateLimitMenu',
        'processLimitMenu'=>'processDateLimitMenu',
        'checkLimit'=>'checkDateLimit',
        'generateLimit'=>'generateDateLimit');
    protected static $dateOperatorMaps = array('equals'=>'=','greaterthan'=>'>','lessthan'=>'<','greaterthan_equals'=>'>=', 'lessthan_equals'=>'<=');



    public function _hasMethod($method,$getFuzzy = false,$returnErrors = false) {  //this is b/c of the laziness above
        //examples: I2CE_FormField_DB_STRING_DISPLAYFIELDSTYLE_in
        if (preg_match('/^([0-9a-zA-Z_]+)_([a-zA-Z]+)FIELDSTYLE_([0-9a-zA-Z_]+)$/',$method,$matches)) {
            //we will never have $getFuzzy = true since we overide __call() as well
            return true;
        } else if (preg_match('/^DATE_([a-zA-Z]+)_DATE_([a-zA-Z]+)_([a-zA-Z_]+?)$/',$method,$matches)) {
            if (array_key_exists($matches[1],self::$dateActions) && (array_key_exists('DATE_' . $matches[2],self::$dateTypes))){
                if ($matches[3] == 'between' ) {
                    return true;
                } else if  (array_key_exists($matches[3],self::$dateOperatorMaps)) {
                    return true;
                }
            }
        }
        return parent::_hasMethod($method,$getFuzzy,$returnErrors);
    }




    public function __call($method,$params) {  //this is b/c of the laziness above
        //examples: I2CE_FormField_DB_STRING_DISPLAYFIELDSTYLE_in
        if (preg_match('/^([0-9a-zA-Z_]+)_([a-zA-Z]+)FIELDSTYLE_([0-9a-zA-Z_]+)$/',$method,$matches)) {
            //I2CE_FormField_XXX->getLimitMenu_YYY($template,$vals,$reportformfield)
            //becomes a vall to the method: $this->XXXX_DISPLAYFIELDSTYLE_YYY($fieldObj,$template,$vals)
            if ($matches[2] !== 'PROCESS' && $matches[2] !== 'DISPLAY') {
                return parent::__call($method,$params);
            }
            $m = $matches[2] .  '_generic';  //this is for example DISPLAY_generic
            array_unshift($params, $matches[3]); //put the style at the begining
            return call_user_func_array(array($this,$m),$params);
        } else if (preg_match('/^DATE_([a-zA-Z]+)_DATE_([a-zA-Z]+)_([a-zA-Z_]+?)$/',$method,$matches)) {
            if (array_key_exists($matches[1],self::$dateActions) && (array_key_exists('DATE_' .$matches[2],self::$dateTypes))){
            //we have something like DATE_generateLimit_{$key2}_{$key1} or  DATE_getLimitMenu_{$key2}_{$key1}
                if ($matches[3] == 'between' ) {
                    $action = 'DATE_between_' . $matches[1];
                    array_unshift($params,self::$dateTypes['DATE_' . $matches[2]]); //the first argument will be the I2CE_Date type
                    //the third arguement will be the field object, the rest will be the calling arguments in order
                    return call_user_func_array(array($this,$action),$params);
                } else  if ( array_key_exists($matches[3],self::$dateOperatorMaps)) {
                    $action = self::$dateActions[$matches[1]];
                    array_unshift($params,self::$dateTypes['DATE_' .  $matches[2]]); //the second argument will be the I2CE_Date type
                    array_unshift($params,$matches[3]); //the first arguement will be the opertator
                    //the third arguement will be the field object, the rest will be the calling arguments in order
                    return call_user_func_array(array($this,$action),$params);
                }
            }
        }
        return parent::__call($method,$params);
    }



    protected function DATE_between_getLimitMenu($type,$fieldObj,$template, $name='', $vals=array()) {
        if (!is_array($vals)) {
            $vals = array();
        }
        $menuNode = $template->loadFile('limit_date_choice_between.html');
        if (!$menuNode instanceof DOMNode) {
            I2CE::raiseError("Could not load limit_date_choice_between.html");
            return;
        }
        $minNode = $template->query('.//*[@id="min_val"]',$menuNode);
        $maxNode = $template->query('.//*[@id="max_val"]',$menuNode);
        if ($minNode->length != 1 || $maxNode->length != 1) {
            I2CE::raiseError("Could not find where to put min and max nodes");
            return false;
        }
        $minNode = $minNode->item(0);
        $maxNode = $maxNode->item(0);
        if (array_key_exists('min',$vals)) {
            $min = I2CE_Date::now($type, $vals['min'],'blank');
        } else {
            $min = I2CE_Date::blank($type);
        }
        if (array_key_exists('max',$vals)) {
            $max = I2CE_Date::now($type, $vals['max'],'blank');
        } else {
            $max = I2CE_Date::blank($type);
        }
        if ($name) {
            $minName = $name .':min';
            $maxName = $name .':max';
        } else {
            $minName = 'min';
            $maxName = 'max';
        }
        self::addDateTimeElements($fieldObj,$template,$min,$minName,$minNode);
        self::addDateTimeElements($fieldObj,$template,$max,$maxName,$maxNode);
        return $menuNode;
    }

    protected function addDateTimeElements($fieldObj,$template,$date,$name,$node) {
        switch ($date->getType()) {
        case I2CE_Date::YEAR_ONLY:
            I2CE_Date::addYearElement( $template, $name, $date, false, $node, $fieldObj->getYearRange(), false, true );
            break;
        case I2CE_Date::YEAR_MONTH:
            I2CE_Date::addYearMonthElement( $template, $name, $date, false, $node, $fieldObj->getYearRange(), false, true );
            break;
        case I2CE_Date::DATE:
            $date_db = $date->dbFormat();
            $element= $template->createElement( "input",
                    array( "class" => "datepicker_ymd", "name" => $name . ":value",
                        "type" => "text", "value" => $date_db ) );
            $node->appendChild( $element );
            $args = array( "format" => "F j, Y", "inputOutputFormat" => "Y-m-d",
                    "allowEmpty" => true, "startView" => "decades" );
            $add_args = I2CE::getConfig()->getAsArray( "/modules/DatePicker/options" );
            if ( is_array( $add_args ) ) {
                $args = array_merge( $args, $add_args );
            }
            $template->addDatePicker( "datepicker_ymd", $args );
            /*
            I2CE_Date::addMonthDayElement( $template, $name, $date, false, $node );
            I2CE_Date::addYearElement( $template, $name, $date, false, $node, array(1900,2100), false, true );
            */
            break;
        case I2CE_Date::MONTH_DAY:
            I2CE_Date::addMonthDayElement( $template, $name, $date, false, $node );
            break;
        case I2CE_Date::DATE_TIME:
            $date_db = $date->dbFormat();
            $element= $template->createElement( "input",
                    array( "class" => "datepicker_ymd_hms", "name" => $name . ":value",
                        "type" => "text", "value" => $date_db ) );
            $node->appendChild( $element );
            $args = array( "format" => "F j, Y @ H:i:s", "inputOutputFormat" => "Y-m-d H:i:s",
                           "allowEmpty" => true, "startView" => "decades", 'timePicker'=>true );
            $add_args = I2CE::getConfig()->getAsArray( "/modules/DatePicker/options" );
            if ( is_array( $add_args ) ) {
                $args = array_merge( $args, $add_args );
            }
            $add_args = I2CE::getConfig()->getAsArray( "/modules/DatePicker/options_datetime" );
            if ( is_array( $add_args ) ) {
                $args = array_merge( $args, $add_args );
            }
            $template->addDatePicker( "datepicker_ymd_hms", $args );
            /*
            I2CE_Date::addMonthDayElement( $template, $name, $date, false, $node );
            I2CE_Date::addYearElement( $template, $name, $date, false, $node, array(1900,2100), false, true );
            $node->appendChild($template->createElement( "br" ));
            I2CE_Date::addTimeElement( $template, $name , $date, false, $node );
            */
            break;
        case I2CE_Date::TIME_ONLY:
            I2CE_Date::addTimeElement( $template, $name , $date, false, $node );
            break;
        }

    }


    protected function DATE_between_processLimitMenu($type,$fieldObj,$vals=array(), $strict = true) {
        $fields = array('max','min');
        $data = array();
        foreach ($fields as $field) {
            if (!array_key_exists($field,$vals)) {
                if ($strict) {
                    I2CE::raiseError("Value $field was not set");
                    return false;
                } else {
                    continue;
                }
            }
            if ($strict) {
                $date = I2CE_Date::now($type,$vals[$field],true);
                if ( ! $date instanceof I2CE_Date) {
                    I2CE::raiseError("Value $field was not set");
                    return false;
                }
            } else {
                $date = I2CE_Date::now($type,$vals[$field],'blank');
            }
            $data[$field] = $date->getValues();
        }
        return $data;
    }


    protected function DATE_between_generateLimit($type,$fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return '';
        }
        if (is_array($vals['min'])) {
            $min =  I2CE_Date::now($type,$vals['min'],true);
        } else if (is_string($vals['min'])) {
            $min = I2CE_Date::fromDB($vals['min']);
        } else {
            return '';
        }
        if (! ($min instanceof I2CE_Date)) {
            return '';
        }
        if (is_array($vals['max'])) {
            $max =  I2CE_Date::now($type,$vals['max'],true);
        } else if (is_string($vals['max'])) {
            $max = I2CE_Date::fromDB($vals['max']);
        } else {
            return '';
        }
        if (! $max instanceof I2CE_Date) {
            return '';
        }
        $min = $min->dbFormat();
        $max = $max->dbFormat();
        $post_ref = '';
        switch ($type) {
        case I2CE_Date::YEAR_ONLY:
            $pre = 'YEAR(';
            $post = ')';
            break;
        case I2CE_Date::YEAR_MONTH:
            $pre = 'MONTH(';
            $post = ')';
            break;
        case I2CE_Date::DATE:
            $pre = 'DATE(';
            $post = ')';
            break;
        case I2CE_Date::DATE_TIME:
            $pre = '';
            $post = '';
            break;
        case I2CE_Date::MONTH_DAY:
            $pre = 'DAYOFYEAR(';
            $post = ')';
            break;
        case I2CE_Date::TIME_ONLY:
            $pre = 'TIME(';
            $post_ref = "+''";
            $post = ')';
            break;
        }
        return $pre . $ref . $post_ref . $post . ' BETWEEN ' .
            $pre . I2CE::PDO()->quote($min) . $post . ' AND ' .
            $pre . I2CE::PDO()->quote($max) . $post ;
    }

    protected function DATE_between_checkLimitString($type,$fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return '';
        }
        if (is_array($vals['min'])) {
            $min =  I2CE_Date::now($type,$vals['min'],true);
        } else if (is_string($vals['min'])) {
            $min = I2CE_Date::fromDB($vals['min']);
        } else {
            return '';
        }
        if (! ($min instanceof I2CE_Date)) {
            return '';
        }
        if (is_array($vals['max'])) {
            $max =  I2CE_Date::now($type,$vals['max'],true);
        } else if (is_string($vals['max'])) {
            $max = I2CE_Date::fromDB($vals['max']);
        } else {
            return '';
        }
        if (! $max instanceof I2CE_Date) {
            return '';
        }
        $min = $min->dbFormat();
        $max = $max->dbFormat();
        return  '((I2CE_Date::fromDB(\'' .$min .'\')->before(I2CE_Date::fromDB(' . $ref . ',' . $type . ' )))'.
            ' && (I2CE_Date::fromDB(' . $ref . ', ' . $type .')->before(I2CE_Date::fromDB(\'' . $max  .'\'))))';
    }
    protected function DATE_between_checkLimitFunction($type,$fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return true;
        }
        if (is_array($vals['min'])) {
            $min =  I2CE_Date::now($type,$vals['min'],true);
        } else if (is_string($vals['min'])) {
            $min = I2CE_Date::fromDB($vals['min']);
        } else {
            return true;
        }
        if (! ($min instanceof I2CE_Date)) {
            return true;
        }
        if (is_array($vals['max'])) {
            $max =  I2CE_Date::now($type,$vals['max'],true);
        } else if (is_string($vals['max'])) {
            $max = I2CE_Date::fromDB($vals['max']);
        } else {
            return true;
        }
        if (! $max instanceof I2CE_Date) {
            return true;
        }
        $min = $min->dbFormat();
        $max = $max->dbFormat();
        return function($data) use($min,$max,$ref,$type) {
            return  ((I2CE_Date::fromDB($min)->before(I2CE_Date::fromDB($data[$ref],$type ))) 
                    && (I2CE_Date::fromDB($data[$ref], $type)->before(I2CE_Date::fromDB($max))));
        };
    }


    protected function DATE_between_checkLimit($type,$fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return null;
        }
        if (is_array($vals['min'])) {
            $min = I2CE_Date::now($type,$vals['min'],true);
        } else if (is_string($vals['min'])) {
            $min = I2CE_Date::fromDB($vals['min']);
        } else {
            return null;
        }
        if (is_array($vals['max'])) {
            $max = I2CE_Date::now($type,$vals['max'],true);
        } else if (is_string($vals['max'])) {
            $max = I2CE_Date::fromDB($vals['max']);
        } else {
            return null;
        }
        $f_date = $fieldObj->getValue();
        if (!($min instanceof I2CE_Date && $max  instanceof I2CE_Date && $f_date instanceof I2CE_Date)) {
            return null;
        }
        return ($min->before($f_date) && $f_date->before($max));
    }





    protected function generateDateLimit($operator, $type,$fieldObj,$vals, $ref) {
        $operator=    self::$dateOperatorMaps[$operator];
        $date = I2CE_Date::now($type,$vals,true);
        if (!$date instanceof I2CE_Date) {
            return '';
        }
        $post_ref = '';
        switch ($type) {
        case I2CE_Date::YEAR_ONLY:
            $pre = 'YEAR(';
            $post = ')';
            break;
        case I2CE_Date::YEAR_MONTH:
            $pre = 'MONTH(';
            $post = ')';
            break;
        case I2CE_Date::DATE:
            $pre = 'DATE(';
            $post = ')';
            break;
        case I2CE_Date::DATE_TIME:
            $pre = '';
            $post = '';
            break;
        case I2CE_Date::MONTH_DAY:
            $pre = 'DAYOFYEAR(';
            $post = ')';
            break;
        case I2CE_Date::TIME_ONLY:
            $pre = 'TIME(';
            $post_ref = "+''";
            $post = ')';
            break;
        }
        return  "$pre $ref $post_ref $post $operator $pre '" . $date->dbFormat()  . "'$post";
    }


    protected function checkDateLimitString($operator,$type,$fieldObj,$vals, $ref) {
        $operator=    self::$dateOperatorMaps[$operator];

        foreach ($vals as $key=>&$val) {
            if (!is_numeric($val) && !(is_string($val) && ctype_digit($val))) {
                unset($vals[$key]);
                continue;
            }
            $val = "'$key'=>$val";
        }
        return  "I2CE_Module_FormLimits::checkDateFromVals('$operator','$type', \$ref,array(" . implode(',',$vals) ."))";
    }

    public static function checkDateFromVals($operator, $type, $data1, $data2) {
        $operator=    self::$dateOperatorMaps[$operator];
        $date1 = I2CE_Date::now($type,$data1);
        $date2 = I2CE_Date::now($type,$data2);
        switch ($operator) {
        case '<':
            return $date2->before($date1);
        case '>':
            return $date2->after($date1);
        case '=':
            return $date2->equals($date1);
        case '<=':
            return ($date2->before($date1) || $date2->equals($date1));
        case '>=':
            return ($date2->after($date1) || $date2->equals($date1));
        case '=':
            return $date1->compare($date2) == 0;
        default:
            return null;
        }
    }


    protected function checkDateLimit($operator,$type,$fieldObj,$vals = array()) {
        $date = I2CE_Date::now($type,$vals);
        $f_date = $fieldObj->getValue();
        if (!$f_date instanceof I2CE_Date) {
            return null;
        }
        switch ($operator) {
        case '<':
            return $f_date->before($date);
        case '>':
            return $f_date->after($date);
        case '=':
            return $f_date->equals($date);
        case '<=':
            return ($f_date->before($date) || $f_date->equals($date));
        case '>=':
            return ($f_date->after($date) || $f_date->equals($date));
        case '=':
            return $date->compare($f_date) == 0;
        default:
            return null;
        }
    }



    protected function processDateLimitMenu($operator,$type,$fieldObj,$vals=array(),$strict = true) {
        if ($strict) {
            $date = I2CE_Date::now($type,$vals,true);
            if ( ! $date instanceof I2CE_Date) {
                I2CE::raiseError("Value $field was not set");
                return false;
            }
        } else {
            $date = I2CE_Date::now($type,$vals,'blank');
        }
        return $date->getValues();
    }

    protected function getDateLimitMenu($operator,$type,$fieldObj,$template,$prefix ='',$vals=array() ) {
        $menuNode = $template->loadFile( "limit_date_choice.html" );
        $spanNode = $template->createElement('span');
        $date = I2CE_Date::now($type,$vals,'blank');
        self::addDateTimeElements($fieldObj,$template,$date,$prefix,$spanNode);
        $menuNode->appendChild( $spanNode );
        return $menuNode;
    }










    /****
     * Definition for generic limit menus
     ***/


    protected static $menudata = array(
        'max_parent' => array( 'fields'=>array( 'linked_field','offset','allow_null','allow_all' ) ),
        'min_parent' => array( 'fields'=>array( 'linked_field','offset','allow_null','allow_all' ) ),
        'not_null' => array('fields'=>array()),
        'null' => array('fields'=>array()),
        'null_not_null' => array('fields'=>array('value')),
        'true' => array('fields'=>array()),
        'false' => array('fields'=>array()),
        'truefalse' => array('fields'=>array('value')),
        'yesno' => array('fields'=>array('value')),
        'yes' => array('fields'=>array()),
        'no' => array('fields'=>array()),
        'like' => array('fields'=>array('value')),
        'lowerlike' => array('fields'=>array('value')),
        'lowerequals' => array('fields'=>array('value')),
        'contains' => array('fields'=>array('value')),
        'startswith' => array('fields'=>array('value')),
        'in' => array('fields'=>array('value')),
        'equals' => array('fields'=>array('value')),
        'lessthan' => array('fields'=>array('value')),
        'lessthan_equals' => array('fields'=>array('value')),
        'greaterthan' => array('fields'=>array('value')),
        'greaterthan_equals' => array('fields'=>array('value')),
        'between' => array('fields'=>array('min','max')),
        'max_parent_form' => array('fields'=>array() ),
        'min_parent_form' => array('fields'=>array() ),
        'greaterthan_now' => array('fields'=>array() ),
        'lessthan_now' => array('fields'=>array() ),
        'greaterthan_equals_now' => array('fields'=>array() ),
        'lessthan_equals_now' => array('fields'=>array() ),
        'within' => array('fields'=>array('value') ),
        );






    /**
     * Generic (internal fuzzy method for)  processing menu for a particular limit style
     * @param I2CE_FormField $fieldObj the object the fuzzy method was called on
     * @param array $params are the calling parameters.  0=>array $vals the values we are processing. 1=>string $reportformfield
     * which defaults to null.  in the case of null, we except keys of the form $limit_field in the case of non-null,
     * we expect $reportformfield to be of the form "$reportform+$field" and then the keys are of the form
     * "limit_value_FORM_{$reportform}_FIELD_{$field}_LIMIT_{$style}_VALUE_{$field}";  2=>boolean $strict.  which
     * defaults to true.  if true we check that the key is in $vals and if not, return error.
     * @param string $style The style
     * @returns  mixed.  false on failure. on sucess an array of relevant values for this limit style.  The keys are
     * the short version of the keys e.g. 'max' instead of 'limit_value_FORM_person_FIELD_password_LIMIT_between'
     *
     */
    protected function PROCESS_generic($style, $fieldObj,$vals=array(),$strict=true) {
        if (!$fieldObj instanceof I2CE_FormField) {
            return;
        }
        if (!array_key_exists($style,self::$menudata)) {
            I2CE::raiseError("Do not know how to deal with style $style");
            return false;
        }
        $data = array();
        foreach (self::$menudata[$style]['fields'] as $field) {
            if (!is_array($vals) || !array_key_exists($field, $vals)) {
                if ($strict) {
                    I2CE::raiseError("Value $field was not set");
                    return false;
                } else {
                    continue;
                }
            }
            if ($field == 'offset') {
                $offset = $vals['offset'];
                if (!( (is_int($offset) && $offset >= 0 ) || (is_string($offset) && strlen($offset) > 0 && ctype_digit($offset)))) {
                    I2CE::raiseError("Bad offset ($strict)");
                    if ($strict) {
                        I2CE::raiseError("Invalid offset set");
                        return false;
                    } else {
                        continue;
                    }
                }
            }
            if ($field == 'allow_null' || $field == 'allow_all') {
                //no error checking -- let it eval to true/false
            }
            if ($field == 'linked_field') {
                $formObj = $fieldObj->getContainer();
                if ($formObj instanceof I2CE_Form) {
                    $fieldNames = $formObj->getFieldNames();
                } else {
                    $fieldNames = array();
                }
                $fieldNames[] = 'parent';
                if ( !in_array($vals['linked_field'],$fieldNames)) {
                    if ($strict) {
                        I2CE::raiseError("Invalid linked field  set" . $vals['linked_field']);
                        return false;
                    } else {
                        continue;
                    }
                }
            }
            $data[$field] = $vals[$field];
        }
        return $data;
    }

    /**
     * Generic (internal fuzzy method for)  display menu for a limit style
     * Generic (internal fuzzy method for)  processing menu for a particular limit style
     * @param string $style The style
     * @param I2CE_FormField $fieldObj the object the fuzzy method was called on
     * @param I2CE_Template $template.
     * @param string $prefix
     * @$vals the values we are processing.
     * @param string $limit_default  limiting information for choosing options. Defaults to 'default'
     * @returns  DOMNode containing the menu for this limit style
     */
    protected function DISPLAY_generic($style, $fieldObj,$template,$prefix='',$vals=array(),$limit_default='default') {
        if (!$fieldObj instanceof I2CE_FormField  || !$template instanceof I2CE_Template) {
            return;
        }
        if(!array_key_exists($style,self::$menudata)) {
            I2CE::raiseError("Badness on for " . get_class($fieldObj) . " $style");
            return array();
        }
        if (!is_string($prefix) || strlen($prefix) == 0)  {
            $prefix = '';
        }
        if (strlen($prefix) > 1) {
            if (!$prefix[strlen($prefix)-1] != ':') {
                $prefix .= ':';
            }
        }
        $find_limit = false;
        if ($fieldObj instanceof I2CE_FormField_MAPPED || $fieldObj instanceof I2CE_FormField_ENUM) {
            $limit_template = "limit_mapped_choice_" . $style . ".html";
            $find_limit = $template->findTemplate( $limit_template, false );
            $mapped = true;
        }
        if ( !$find_limit || $find_limit == "" ) {
            $mapped = false;
            $limit_template = "limit_choice_" . $style . ".html";
        }

        $menuNode = $template->loadFile( $limit_template );
        //$template->setDisplayDataImmediate('limit_desc',self::$menudata[$style]['desc'], $menuNode);
        foreach (self::$menudata[$style]['fields'] as $field) {
            $node =  $template->getElementByName( $field,0,$menuNode);
            if (!$node instanceof DOMElement) {
                continue;
            }
            if ( 'select' == $node->tagName) {
                $name = $node->getAttribute('name');
                if ($node->hasAttribute('multiple') && (substr($name,-2) != '[]')) {
                    $name .= '[]';
                }
                $node->setAttribute('name',$prefix . $name);
                $selected = array();
                if (is_array($vals) && array_key_exists($field,$vals)) {
                    if ($node->hasAttribute('multiple')) {
                        if (is_array($vals[$field])) {
                            $selected = $vals[$field];
                        } else {
                            $selected = explode(',',$vals[$field]); //hope that the values have no commas in them!
                        }
                    } else {
                        $selected = array($vals[$field]);
                    }
                }
                if (!is_array($selected)) {
                    $selected = array();
                }
                if ($field == 'linked_field') {
                    $formObj = $fieldObj->getContainer();
                    if ($formObj instanceof I2CE_Form) {
                        $fieldNames = $formObj->getFieldNames();
                    } else {
                        $fieldNames = array();
                    }
                    $fieldNames[] = 'parent';
                    foreach ($fieldNames as $fieldName) {
                        if ($fieldName == $field) {
                            continue;
                        }
                        $opt = $template->createElement( "option", array( "value" => $fieldName ), $fieldName);
                        if ( in_array($fieldName,$selected)) {
                            $opt->setAttribute( "selected", "selected" );
                        }
                        $node->appendChild( $opt );
                    }
                } else if ($fieldObj instanceof I2CE_FormField_MAPPED && $mapped) {

                    if ( !$node->hasAttribute('multiple')
                            && $style == "equals"
                            && $fieldObj->getDisplayedStyle(
                                $limit_default ) == "tree" ) {
                        $main = $template->createElement( "span" );
                        $name = $node->getAttribute("name");
                        $node->parentNode->replaceChild( $main, $node );
                        $data = $fieldObj->getMapOptions( $limit_default, false, false );
                        $tree_selected = null;
                        if ( count($selected) == 1
                                && strpos( $selected[0], '|' ) !== false ) {
                            list($select_form, $select_id) = explode( '|', $selected[0], 2 );
                            $tree_selected = array (
                                    'value' => $selected[0],
                                    'display' => I2CE_List::lookup( $select_id, $select_form ),
                                    );
                        }
                        $template->addAutoCompleteInputTree( $main, $name, "tree:".$name, $tree_selected, $data, array(), array() );
                    } elseif ( !$node->hasAttribute('multiple')
                            && $style == "equals"
                            && $fieldObj->getDisplayedStyle(
                                $limit_default ) == "ajax_list" ) {
                        $form_node = $template->createElement("span", array( "type" => "form" ) );
                        $name = $node->getAttribute("name");
                        $node->parentNode->replaceChild( $form_node, $node );
                        if ( count( $selected ) == 1 ) {
                            $fieldObj->setFromDB( $selected[0] );
                        }
                        $fieldObj->setHTMLName( $name );
                        $fieldObj->processDOMEditable( $form_node, $template, $form_node );
                    } else {

                        $optionList = $fieldObj->getMapOptions($limit_default);
                        if (!$node->hasAttribute('multiple') ) {
                            $select_value = "Select Value";
                            I2CE::getConfig()->setIfIsSet($select_value,"/modules/field-limits/text/select_value");
                            $opt = $template->createElement( "option", array( "value" => '' ), $select_value );
                            $node->appendChild( $opt );
                        }
                        foreach( $optionList as $data) {
                            $opt = $template->createElement( "option", array( "value" => $data['value'] ), $data['display'] );
                            if ( in_array($data['value'],$selected)) {
                                $opt->setAttribute( "selected", "selected" );
                            }
                            $node->appendChild( $opt );
                        }
                    }
                } else if ( $fieldObj instanceof I2CE_FormField_ENUM && $mapped ) {
                    $optionList = $fieldObj->getEnum( $limit_default );

                    $select_value = "Select Value";
                    I2CE::getConfig()->setIfIsSet($select_value,"/modules/field-limits/text/select_value");
                    $opt = $template->createElement( "option", array( "value" => '' ), $select_value );
                    $node->appendChild( $opt );
                    foreach( $optionList as $key => $val) {
                        $opt = $template->createElement( "option", array( "value" => $key ), $val );
                        if ( in_array($key,$selected)) {
                            $opt->setAttribute( "selected", "selected" );
                        }
                        $node->appendChild( $opt );
                    }

                 } else {
                    foreach ($selected as $val) {
                        $opt = $template->query("./option[@value='$val']", $node);
                        for ($i=0; $i < $opt->length; $i++) {
                            if ($opt->item($i) instanceof DOMElement) {
                                $opt->item($i)->setAttribute('selected','selected');
                            }
                        }
                    }
                }
            } else if (is_array($vals)) {
                $name = $node->getAttribute('name');
                $node->setAttribute('name',$prefix . $name);
                if (!array_key_exists($field,$vals)) {
                    continue;
                }
                switch ($node->tagName) {
                case 'input':
                    $node->setAttribute('value',$vals[$field]);
                    break;
                case 'textarea':
                    $node->appendChild($template->createTextNode($vals[$field]));
                    break;
                default:
                    I2CE::raiseError("Do not know how to display $field");
                    return false;
                }
            }
        }
        if (count(self::$menudata[$style]['fields']) == 0)  { //if there are no values to set, remove the Limit button
            $node = $template->getElementById('limit_type_menu_button_container',$menuNode);
            if ($node instanceof DOMNode){
                $this->template->removeNode($node);
            }
        }
        return  $menuNode;
    }



    /**
     * Get the limit styles available for the given field object
     * @param I2CE_FormField $fieldObj
     * @returns array of string, the limit styles available
     */
    public function getFieldLimitStyles($fieldObj) {
        $class = get_class($fieldObj);
        $factory = I2CE_ModuleFactory::instance();
        $methodData = $factory->getMethods($class);
        $limitStyles = array();
        foreach ($methodData as $className=>$methods) {
            foreach ($methods as $method) {
                if (!preg_match('/^generateLimit_([a-zA-Z0-9_]+)$/',$method,$matches)) {
                    continue;
                }
                $limit = $matches[1];
                if (array_key_exists($limit,self::$menudata) && is_array(self::$menudata[$limit]) &&  array_key_exists('fields',self::$menudata[$limit])) {
                    $data = self::$menudata[$limit]['fields'];
                } else {
                    $data = true;
                }
                $limitStyles [$limit] = $data;
            }
        }
        return $limitStyles;
  }


    /**
     * Get the description for the given style and data.
     * @param I2CE_FormField $fieldObj
     * @param string $limit
     * @param array $data
     * @return string
     */
    public function describeFieldLimit( $fieldObj, $limit, $data=array() ) {
        if ( !array_key_exists( $limit, self::$menudata ) ) {
            I2CE::raiseError( "Invalid limit type ($limit) passed to describeFieldLimit." );
            return null;
        }
        if ( I2CE::getConfig()->is_parent( '/modules/field-limits/text/description/' . $limit ) ) {
            $descConfig = I2CE::getConfig()->traverse( '/modules/field-limits/text/description/' . $limit, false, false );
        } else {
            $descConfig = I2CE::getConfig()->traverse( '/modules/field-limits/text/description/default', false, false );
        }

        if( !$descConfig instanceof I2CE_MagicDataNode ) {
            return null;
        }
        $text = $descConfig->text;
        $args = $descConfig->getAsArray('values');
        ksort( $args );
        $vals = array();
        $isblank = true;
        foreach( $args as $field ) {
            $value = $this->getFieldDisplay( $fieldObj, $field, $data );
            $vals[] = $value;
            if ( $value != "" ) {
                $isblank = false;
            }
        }
        if ( $isblank ) {
            return '';
        } else {
            return vsprintf( $text, $vals );
        }
    }

    /**
     * Get the description for the field limit.
     * @param I2CE_FormField $fieldObj
     * @param string $field
     * @param string $data
     * @return string
     */
    protected function getFieldDisplay( $fieldObj, $field, $data ) {
        if ( $fieldObj instanceOf I2CE_FormField_DB_DATE ) {
            if ( !array_key_exists( $field, $data ) ) {
                if ( is_array( $data ) ) {
                    $fieldObj->setFromPost( $data );
                } else {
                    $fieldObj->setFromDB( $data );
                }
            } else {
                if ( is_array( $data[$field] ) ) {
                    $fieldObj->setFromPost( $data[$field] );
                } else {
                    $fieldObj->setFromDB( $data[$field] );
                }
            }
            return $fieldObj->getDisplayValue();
        } elseif ( !array_key_exists( $field, $data ) ) {
            return '';
        } elseif ( is_array( $data[$field] ) ) {
            $disp_values = array();
            foreach( $data[$field] as $value ) {
                if ( $value == '' ) {
                    continue;
                }
                $fieldObj->setFromDB( $value );
                $disp_values[] = $fieldObj->getDisplayValue();
            }
            return implode( ', ', $disp_values );
        } else {
            if ( $data[$field] == '' ) {
                return '';
            }
            $fieldObj->setFromDB( $data[$field] );
            return $fieldObj->getDisplayValue();
        }
    }



    /**
     * Generates a limit expression for a field based on  limit data
     * @param I2CE_FormField $fieldObj
     * @param mixed $limit_data
     * @param callback $ref.  A callback function whose first arguement is the form, the second arguements
     * is the field and which returns the way the field value should be references as a field.  If the callback is null (the default) then
     * the reference used is "$form+$field"
     * @param string $parent_ref.  Defaults to null.  If not null, it is the referent to the parent id of the form
     * @returns string SQL statement false on failure
     */
    public function generateFieldLimit($fieldObj,$limit_data, $ref, $parent_ref = null) {
        if (!is_array($limit_data)) {
            I2CE::raiseError("Expected array for generating field limit, but not received");
            return false;
        }
        if (!array_key_exists('style', $limit_data) || !is_string($limit_data['style'])) {
            I2CE::raiseError("Style is not given at 'style' ");
            return false;
        }
        if (!array_key_exists('data',$limit_data) || !is_array($limit_data['data'])) {
            $limit_data['data'] = array();
        }
        $method = 'generateLimit_' .$limit_data['style'];
        if (!$fieldObj->_hasMethod($method)) {
            I2CE::raiseError("Not able to generate limit for style  " . $limit_data['style'] . " by method ($method) for class " . get_class($fieldObj));
            return false;
        }
        $ret = $fieldObj->$method($limit_data['data'],$ref,$parent_ref);
        if (is_string($ret)) {
            return $ret;
        } else {
            I2CE::raiseError("Unexpected return from limit $style:" . print_r($ret,true));
            return false;
        }
    }




    public function generateLimit_null($fieldObj,$vals,$ref, $parent_ref =null) {
        return ' ISNULL( ' . $ref . ')';
    }
    public function checkLimitString_null($fieldObj,$vals,$ref) {
        return  "$ref == null";
    }
    public function checkLimitFunction_null($fieldObj,$vals,$ref) {
        return function($data) use ($ref) {
            return $data[$ref] == null;
        };
    }
    public function checkLimit_null($fieldObj,$vals) {
        return $fieldObj->getDBValue() === null;
    }

    public function generateLimit_DB_DATE_null($fieldObj,$vals,$ref, $parent_ref =null) {
        return ' ( ISNULL( ' . $ref . ') OR ' . $ref . ' = \'0000-00-00 00:00:00\' ) ';
    }
    public function checkLimitString_DB_DATE_null($fieldObj,$vals,$ref) {
        return  "$ref == null || $ref == '0000-00-00 00:00:00'";
    }
    public function checkLimitFunction_DB_DATE_null($fieldObj,$vals,$ref) {
        return function($data) use ($ref) {
            return  $data[$ref] == null || $data[$ref] == '0000-00-00 00:00:00';
        };
    }
    public function checkLimit_DB_DATE_null($fieldObj,$vals) {
        return $fieldObj->getDBValue() === null || $fieldObj->getValue()->isBlank();
    }




    public function generateLimit_not_null($fieldObj,$vals,$ref,$parent_ref =null) {
        return ' NOT (ISNULL(' . $ref . '))';
    }
    public function checkLimitString_not_null($fieldObj,$vals,$ref) {
        return  "$ref != null";
    }
    public function checkLimitFunction_not_null($fieldObj,$vals,$ref) {
        return function($data) use($ref) {
            return  $data[$ref] != null;
        };
    }
    public function checkLimit_not_null($fieldObj,$vals) {
        return $fieldObj->getDBValue() !== null;
    }

    public function generateLimit_DB_DATE_not_null($fieldObj,$vals,$ref,$parent_ref =null) {
        return ' NOT (ISNULL(' . $ref . ') OR ' . $ref . ' = \'0000-00-00 00:00:00\' )';
    }
    public function checkLimitString_DB_DATE_not_null($fieldObj,$vals,$ref) {
        return  "$ref != null && $ref != '0000-00-00 00:00:00'";
    }
    public function checkLimitFunction_DB_DATE_not_null($fieldObj,$vals,$ref) {
        return function($data) use($ref) {
            return  $data[$ref] != null && $data[$ref] != '0000-00-00 00:00:00';
        };
    }
    public function checkLimit_DB_DATE_not_null($fieldObj,$vals) {
        return $fieldObj->getDBValue() !== null && !$fieldObj->getValue()->isBlank();
    }


    public function generateLimit_null_not_null($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals) || $vals['value'] == '')  {
            return '';
        }
        if ($vals['value']) {
            return $this->generateLimit_null($fieldObj,$vals,$ref);
        } else {
            return $this->generateLimit_not_null($fieldObj,$vals,$ref);
        }
    }
    public function checkLimitString_null_not_null($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals) || $vals['value'] == '')  {
            return 'null';
        }
        if ($vals['value']) {
            return $this->checkLimitString_null($fieldObj,$vals,$ref);
        } else {
            return $this->checkLimitLimitString_not_null($fieldObj,$vals,$ref);
        }
    }
    public function checkLimitFunction_null_not_null($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals) || $vals['value'] == '')  {
            return function($data) { return null; };
        }
        if ($vals['value']) {
            return $this->checkLimitFunction_null($fieldObj,$vals,$ref);
        } else {
            return $this->checkLimitFunction_not_null($fieldObj,$vals,$ref);
        }
    }
    public function checkLimit_null_not_null($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals) || $vals['value'] == '')  {
            return null;
        }
        if ($vals['value']) {
            return $this->checkLimit_null($fieldObj,$vals,$ref);
        } else {
            return $this->checkLimitLimit_not_null($fieldObj,$vals,$ref);
        }
    }

    public function generateLimit_DB_DATE_null_not_null($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals) || $vals['value'] == '')  {
            return '';
        }
        if ($vals['value']) {
            return $this->generateLimit_DB_DATE_null($fieldObj,$vals,$ref);
        } else {
            return $this->generateLimit_DB_DATE_not_null($fieldObj,$vals,$ref);
        }
    }
    public function checkLimitString_DB_DATE_null_not_null($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals) || $vals['value'] == '')  {
            return 'null';
        }
        if ($vals['value']) {
            return $this->checkLimitString_DB_DATE_null($fieldObj,$vals,$ref);
        } else {
            return $this->checkLimitLimitString_DB_DATE_not_null($fieldObj,$vals,$ref);
        }
    }
    public function checkLimitFunction_DB_DATE_null_not_null($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals) || $vals['value'] == '')  {
            return function($data) { return null; };
        }
        if ($vals['value']) {
            return $this->checkLimitFunction_DB_DATE_null($fieldObj,$vals,$ref);
        } else {
            return $this->checkLimitFunction_DB_DATE_not_null($fieldObj,$vals,$ref);
        }
    }
    public function checkLimit_DB_DATE_null_not_null($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals) || $vals['value'] == '')  {
            return null;
        }
        if ($vals['value']) {
            return $this->checkLimit_DB_DATE_null($fieldObj,$vals,$ref);
        } else {
            return $this->checkLimitLimit_DB_DATE_not_null($fieldObj,$vals,$ref);
        }
    }





    public function generateDateNowLimit( $comparison, $fieldObj, $vals, $ref, $parent_ref = null ) {
        $dateType = '';
        $dateObj = $fieldObj->getValue();
        if ( $dateObj instanceof I2CE_Date ) {
            $dateType = $dateObj->getType();
        }
        switch( $dateType ) {
            case I2CE_Date::DATE :
                return ' (DATE(' . $ref . ') '.$comparison.' DATE(NOW()) )';
                break;
            case I2CE_Date::YEAR_ONLY:
                return ' (YEAR(' . $ref . ') '.$comparison.' YEAR(NOW()) )';
                break;
            case I2CE_Date::YEAR_MONTH:
                return ' (MONTH(' . $ref . ') '.$comparison.' MONTH(NOW()) )';
                break;
            case I2CE_Date::MONTH_DAY:
                return ' (DAYOFYEAR(' . $ref . ') '.$comparison.' DAYOFYEAR(NOW()) )';
                break;
            case I2CE_Date::TIME_ONLY:
                return ' (TIME(' . $ref . '+\'\') '.$comparison.' TIME(NOW()) )';
                break;
            default :
                return ' (' . $ref . ' '.$comparison.' NOW() )';
        }
    }

    public function generateLimit_greaterthan_now($fieldObj,$vals,$ref,$parent_ref =null) {
        return $this->generateDateNowLimit( '>', $fieldObj, $vals, $ref, $parent_ref );
    }
    public function checkLimitString_greaterthan_now($fieldObj,$vals,$ref) {
        return ' I2CE_Date::now()->before(I2CE_Date::fromDB(' . $ref . '))';
    }
    public function checkLimitFunction_greaterthan_now($fieldObj,$vals,$ref) {
        return function($data) use ($ref) {
            return I2CE_Date::now()->before(I2CE_Date::fromDB($data[$ref]));
        };
    }
    public function checkLimit_greaterthan_now($fieldObj,$vals) {
        if (!$fieldObj->getValue() instanceof I2CE_Date) {
            return null;
        }
        return I2CE_Date::now()->before($fieldObj->getValue());
    }


    public function generateLimit_greaterthan_equals_now($fieldObj,$vals,$ref,$parent_ref =null) {
        return $this->generateDateNowLimit( '>=', $fieldObj, $vals, $ref, $parent_ref );
    }
    public function checkLimitString_greaterthan_equals_now($fieldObj,$vals,$ref) {
        return ' ! I2CE_Date::now()->after(I2CE_Date::fromDB(' . $ref . '))';
    }
    public function checkLimitFunction_greaterthan_equals_now($fieldObj,$vals,$ref) {
        return function($data) use ($ref) {
            return ! I2CE_Date::now()->after(I2CE_Date::fromDB($data[$ref]));
        };
    }
    public function checkLimit_greaterthan_equals_now($fieldObj,$vals) {
        if (!$fieldObj->getValue() instanceof I2CE_Date) {
            return null;
        }
        return (! I2CE_Date::now()->after($fieldObj->getValue()));
    }


    public function generateLimit_lessthan_now($fieldObj,$vals,$ref,$parent_ref =null) {
        return $this->generateDateNowLimit( '<', $fieldObj, $vals, $ref, $parent_ref );
    }
    public function checkLimitString_lessthan_now($fieldObj,$vals,$ref) {
        return ' I2CE_Date::now()->after(I2CE_Date::fromDB(' . $ref . '))';
    }
    public function checkLimitFunction_lessthan_now($fieldObj,$vals,$ref) {
        return function($data) use($ref) {
            return I2CE_Date::now()->after(I2CE_Date::fromDB($data[$ref]));
        };
    }
    public function checkLimit_lessthan_now($fieldObj,$vals) {
        if (!$fieldObj->getValue() instanceof I2CE_Date) {
            return null;
        }
        return I2CE_Date::now()->after($fieldObj->getValue());
    }

    public function generateLimit_lessthan_equals_now($fieldObj,$vals,$ref,$parent_ref =null) {
        return $this->generateDateNowLimit( '<=', $fieldObj, $vals, $ref, $parent_ref );
    }
    public function checkLimitString_lessthan_equals_now($fieldObj,$vals,$ref) {
        return ' !I2CE_Date::now()->after(I2CE_Date::fromDB(' . $ref . '))';
    }
    public function checkLimitFunction_lessthan_equals_now($fieldObj,$vals,$ref) {
        return function($data) use ($ref) {
            return !I2CE_Date::now()->after(I2CE_Date::fromDB($data[$ref]));
        };
    }
    public function checkLimit_lessthan_equals_now($fieldObj,$vals) {
        if (!$fieldObj->getValue() instanceof I2CE_Date) {
            return null;
        }
        return (! I2CE_Date::now()->before($fieldObj->getValue()));
    }



    public function generateLimit_DB_TEXT_equals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals))  {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' = ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimitString_DB_TEXT_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals))  {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' == \''  . addslashes($vals['value']) . '\'';
    }
    public function checkLimitFunction_DB_TEXT_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals))  {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] == $vals['value'];
        };
    }
    public function checkLimit_DB_TEXT_equals($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals))  {
            return null;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return null;
        }
        return $fieldObj->getDBValue() == $vals['value'];
    }

    public function generateLimit_MAP_MULT_equals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        $val = I2CE_PDO::escape_string( $vals['value'] );
        return "( $ref  = '$val' OR $ref LIKE '$val,%' OR $ref LIKE '%,$val' OR $ref LIKE '%,$val,%' )";
    }
    public function checkLimitString_MAP_MULT_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        $set_vals = explode( ',', $fieldObj->getDBValue() );
        foreach ($set_vals as &$v) {
            $v = "'" . addslashes(trim($v)) . "'";
        }
        unset($v);
        if ( count($set_vals) == 0 ) {
            return '';
        }
        return 'in_array( \'' . addslashes( $vals['value'] ) . '\', array( '
                    . implode(',', $set_vals ) . ' ) )';
    }
    public function checkLimitFunction_MAP_MULT_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $set_vals = explode( ',', $fieldObj->getDBValue() );
        if ( count($set_vals) == 0 ) {
            return true;
        }
        return function($data) use ($vals,$set_vals) {
            return in_array( $vals['value'], $set_vals );
        };
    }
    public function checkLimit_MAP_MULT_equals($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return null;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return null;
        }
        $set_vals = explode( ',', $fieldObj->getDBValue() );
        if ( count($set_vals) == 0 ) {
            return null;
        }
         return in_array( $vals['value'], $set_vals );
    }


    public function generateLimit_DB_STRING_equals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' = ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimitString_DB_STRING_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' == \''  . addslashes($vals['value']) . '\'';
    }
    public function checkLimitFunction_DB_STRING_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref]  == $vals['value'];
        };
    }
    public function checkLimit_DB_STRING_equals($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return null;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return null;
        }
        return $fieldObj->getDBValue() == $vals['value'];
    }



    public function generateLimit_MAP_within($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        $use_values = array();
        foreach( I2CE_List::findLowerMatches( $vals['value'],
                $fieldObj->getDisplayedFields(),
                $fieldObj->getSelectableForms(), true ) as $valid ) {
            $use_values[] = I2CE::PDO()->quote( $valid );
        }
        return $ref . ' IN (' . implode( ',', $use_values ) . ')';
    }
    public function checkLimitString_MAP_within($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        $use_values = array();
        foreach( I2CE_List::findLowerMatches( $vals['value'],
                $fieldObj->getDisplayedFields(),
                $fieldObj->getSelectableForms(), true ) as $valid ) {
            $use_values[] = "'" . addslashes( $valid ) . "'";
        }
        return 'in_array( ' . $ref . ', array( '
                . implode( ',', $use_values ) . ') )';
    }
    public function checkLimitFunction_MAP_within($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $use_values = I2CE_List::findLowerMatches( $vals['value'],
                $fieldObj->getDisplayedFields(),
                $fieldObj->getSelectableForms(), true );
        return function($data) use ($ref,$use_values) {
            return in_array( $data[$ref], $use_values );
        };
    }
    public function checkLimit_MAP_within($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return null;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return null;
        }
        $use_values = array();
        foreach( I2CE_List::findLowerMatches( $vals['value'],
                $fieldObj->getDisplayedFields(),
                $fieldObj->getSelectableForms(), true ) as $valid ) {
            $use_values[] = "'" . $valid . "'";
        }
        return in_array( $fieldObj->getDBValue(), $use_values );
    }







    public function generateLimit_DB_INT_equals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if ( strlen( (string)$vals['value'] ) == 0 ) {
            //return $ref . " IS NULL";
            return '';
        }
        if (!(is_integer($vals['value']) || ctype_digit($vals['value']))) {
            return '';
        }
        return $ref . ' =' .  $vals['value'] . '';
    }
    public function createLimitString_DB_INT_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if ( strlen( (string)$vals['value'] ) == 0 ) {
            return '';
            //return $ref . " == ''";
        }
        if (!(is_integer($vals['value']) || ctype_digit($vals['value']))) {
            return '';
        }
        return $ref . ' ==' .  $vals['value'] . '';
    }
    public function checkLimit_DB_INT_equals($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals) ||strlen( (string)$vals['value'] ) == 0) {
            return null;
        }
        return $fieldObj->getValue() == $vals['value'];
    }
    public function generateLimit_DB_FLOAT_equals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if ( strlen( (string)$vals['value'] ) == 0 ) {
            //return $ref . " IS NULL";
            return '';
        }
        if (!(is_numeric($vals['value']) )) {
            return '';
        }
        return $ref . ' =' .  $vals['value'] . '';
    }
    public function createLimitString_DB_FLOAT_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if ( strlen( (string)$vals['value'] ) == 0 ) {
            return '';
            //return $ref . " == ''";
        }
        if (!(is_numeric($vals['value']) )) {
            return '';
        }
        return $ref . ' ==' .  $vals['value'] . '';
    }
    public function checkLimit_DB_FLOAT_equals($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals) ||strlen( (string)$vals['value'] ) == 0) {
            return null;
        }
        return $fieldObj->getValue() == $vals['value'];
    }




    public function generateLimit_DB_TEXT_lessthan_equals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' <= ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimitString_DB_TEXT_lessthan_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' <= \'' . addslashes($vals['value']) . '\'';
    }
    public function checkLimitFunction_DB_TEXT_lessthan_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] <= $vals['value'];
        };
    }
    public function checkLimit_DB_TEXT_lessthan_equals($fieldObj,$vals) {
        return $fieldObj->getDBValue() <= $vals['value'];
    }


    public function generateLimit_DB_STRING_lessthan_equals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . '  <= ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimit_DB_STRING_lessthan_equals($fieldObj,$vals) {
        return $fieldObj->getDBValue() <= $vals['value'];
    }

    public function generateLimit_DB_INT_lessthan_equals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_integer($vals['value']) || ctype_digit($vals['value']))) {
            return '';
        }
        return $ref . ' <=' .  $vals['value'] . '';
    }
    public function checkLimit_DB_INT_lessthan_equals($fieldObj,$vals) {
        return $fieldObj->getDBValue() <= $vals['value'];
    }
    public function generateLimit_DB_FLOAT_lessthan_equals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_numeric($vals['value']) )) {
            return '';
        }
        return $ref . ' <=' .  $vals['value'] . '';
    }
    public function checkLimit_DB_FLOAT_lessthan_equals($fieldObj,$vals) {
        return $fieldObj->getDBValue() <= $vals['value'];
    }




    public function generateLimit_DB_TEXT_lessthan($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' < ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimitString_DB_TEXT_lessthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' < \'' . addslashes($vals['value']) . '\'';
    }
    public function checkLimitFunction_DB_TEXT_lessthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] < $vals['value'];
        };
    }
    public function checkLimit_DB_TEXT_lessthan($fieldObj,$vals) {
        return $fieldObj->getDBValue() < $vals['value'];
    }


    public function generateLimit_DB_STRING_lessthan($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' < ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimitString_DB_STRING_lessthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' < \'' . addSlashes($vals['value']) . '\'';
    }
    public function checkLimitFunction_DB_STRING_lessthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] < $vals['value'];
        };
    }
    public function checkLimit_DB_STRING_lessthan($fieldObj,$vals) {
        return $fieldObj->getDBValue() < $vals['value'];
    }


    public function generateLimit_DB_INT_lessthan($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_integer($vals['value']) || ctype_digit($vals['value']))) {
            return '';
        }
        return $ref . ' <' .  $vals['value'] . '';
    }
    public function checkLimitString_DB_INT_lessthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_integer($vals['value']) || ctype_digit($vals['value']))) {
            return '';
        }
        return $ref . ' <' .  $vals['value'] . '';
    }
    public function checkLimitFunction_DB_INT_lessthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        if (!(is_integer($vals['value']) || ctype_digit($vals['value']))) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] < $vals['value'];
        };
    }
    public function checkLimit_DB_INT_lessthan($fieldObj,$vals) {
        return $fieldObj->getDBValue() < $vals['value'];
    }
    public function generateLimit_DB_FLOAT_lessthan($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_numeric($vals['value']) )) {
            return '';
        }
        return $ref . ' <' .  $vals['value'] . '';
    }
    public function checkLimitString_DB_FLOAT_lessthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_numeric($vals['value']) )) {
            return '';
        }
        return $ref . ' <' .  $vals['value'] . '';
    }
    public function checkLimitFunction_DB_FLOAT_lessthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        if (!(is_numeric($vals['value']) )) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] < $vals['value'];
        };
    }
    public function checkLimit_DB_FLOAT_lessthan($fieldObj,$vals) {
        return $fieldObj->getDBValue() < $vals['value'];
    }




    public function generateLimit_DB_TEXT_greaterthan_equals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' >= ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimitString_DB_TEXT_greaterthan_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' >= \'' . addslashes($vals['value']) . '\'';
    }
    public function checkLimitFunction_DB_TEXT_greaterthan_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] >= $vals['value'];
        };
    }
    public function checkLimit_DB_TEXT_greaterthan_equals($fieldObj,$vals) {
        return $fieldObj->getDBValue() >= $vals['value'];
    }

    public function generateLimit_DB_STRING_greaterthan_equals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' >= ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimitString_DB_STRING_greaterthan_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' >= \'' . addSlashes($vals['value']) . '\'';
    }
    public function checkLimitFunction_DB_STRING_greaterthan_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] >= $vals['value'];
        };
    }
    public function checkLimit_DB_STRING_greaterthan_equals($fieldObj,$vals) {
        return $fieldObj->getDBValue() >= $vals['value'];
    }

    public function generateLimit_DB_INT_greaterthan_equals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_integer($vals['value']) || ctype_digit($vals['value']))) {
            return '';
        }
        return $ref . ' >=' .  $vals['value'] . '';
    }
    public function checkLimitString_DB_INT_greaterthan_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_integer($vals['value']) || ctype_digit($vals['value']))) {
            return '';
        }
        return $ref . ' >=' .  $vals['value'] . '';
    }
    public function checkLimitFunction_DB_INT_greaterthan_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        if (!(is_integer($vals['value']) || ctype_digit($vals['value']))) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] >= $vals['value'];
        };
    }
    public function checkLimit_DB_INT_greaterthan_equals($fieldObj,$vals) {
        return $fieldObj->getDBValue() >= $vals['value'];
    }
    public function generateLimit_DB_FLOAT_greaterthan_equals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_numeric($vals['value']) )) {
            return '';
        }
        return $ref . ' >=' .  $vals['value'] . '';
    }
    public function checkLimitString_DB_FLOAT_greaterthan_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_numeric($vals['value']) )) {
            return '';
        }
        return $ref . ' >=' .  $vals['value'] . '';
    }
    public function checkLimitFunction_DB_FLOAT_greaterthan_equals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        if (!(is_numeric($vals['value']) )) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] >= $vals['value'];
        };
    }
    public function checkLimit_DB_FLOAT_greaterthan_equals($fieldObj,$vals) {
        return $fieldObj->getDBValue() >= $vals['value'];
    }




    public function generateLimit_DB_TEXT_greaterthan($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' > ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimitString_DB_TEXT_greaterthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' > \'' . addslasshes($vals['value']) . '\'';
    }
    public function checkLimitFunction_DB_TEXT_greaterthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] > $vals['value'];
        };
    }
    public function checkLimit_DB_TEXT_greaterthan($fieldObj,$vals) {
        return $fieldObj->getDBValue() > $vals['value'];
    }

    public function generateLimit_DB_STRING_greaterthan($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' > ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimitString_DB_STRING_greaterthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' > \'' . addslashes($vals['value']) . '\'';
    }
    public function checkLimitFunction_DB_STRING_greaterthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] > $vals['value'];
        };
    }
    public function checkLimit_DB_STRING_greaterthan($fieldObj,$vals) {
        return $fieldObj->getDBValue() > $vals['value'];
    }

    public function generateLimit_DB_INT_greaterthan($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_integer($vals['value']) || ctype_digit($vals['value']))) {
            return '';
        }
        return $ref . ' >' .  $vals['value'] . '';
    }
    public function checkLimitString_DB_INT_greaterthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_integer($vals['value']) || ctype_digit($vals['value']))) {
            return '';
        }
        return $ref . ' >' .  $vals['value'] . '';
    }
    public function checkLimitFunction_DB_INT_greaterthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        if (!(is_integer($vals['value']) || ctype_digit($vals['value']))) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] > $vals['value'];
        };
    }
    public function checkLimit_DB_INT_greaterthan($fieldObj,$vals) {
        return ( $fieldObj->getDBValue() >  $vals['value'] );
    }
    public function generateLimit_DB_FLOAT_greaterthan($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_numeric($vals['value']) )) {
            return '';
        }
        return $ref . ' >' .  $vals['value'] . '';
    }
    public function checkLimitString_DB_FLOAT_greaterthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (!(is_numeric($vals['value']) )) {
            return '';
        }
        return $ref . ' >' .  $vals['value'] . '';
    }
    public function checkLimitFunction_DB_FLOAT_greaterthan($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        if (!(is_numeric($vals['value']) )) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return $data[$ref] > $vals['value'];
        };
    }
    public function checkLimit_DB_FLOAT_greaterthan($fieldObj,$vals) {
        return ( $fieldObj->getDBValue() >  $vals['value'] );
    }





    public function generateLimit_DB_TEXT_between($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return '';
        }
        $vals['max'] = '' . $vals['max'];
        if (strlen($vals['max']) == 0) {
            return '';
        }
        $vals['min'] = '' . $vals['min'];
        if (strlen($vals['min']) == 0) {
            return '';
        }
        return $ref . ' BETWEEN ' .
            I2CE::PDO()->quote($vals['min']) . ' AND ' .
            I2CE::PDO()->quote($vals['max']);
    }
    public function checkLimitString_DB_TEXT_between($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return '';
        }
        $vals['max'] = '' . $vals['max'];
        if (strlen($vals['max']) == 0) {
            return '';
        }
        $vals['min'] = '' . $vals['min'];
        if (strlen($vals['min']) == 0) {
            return '';
        }
        return  '((\''.addslashes($vals['min']) .'\' < '. $ref . ' ) && ( ' . $ref . '< \'' . addslashes($vals['max']) . '\'))';
    }
    public function checkLimitFunction_DB_TEXT_between($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return true;
        }
        $vals['max'] = '' . $vals['max'];
        if (strlen($vals['max']) == 0) {
            return true;
        }
        $vals['min'] = '' . $vals['min'];
        if (strlen($vals['min']) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return  (($vals['min'] < $data[$ref] ) 
                    && ( $data[$ref] < $vals['max'] ));
        };
    }
    public function checkLimit_DB_TEXT_between($fieldObj,$vals) {
        return ($fieldObj->getDBValue() >= $vals['min']) && ($fieldObj->getDBValue() <= $data['0']['max']);
    }

    public function generateLimit_DB_STRING_between($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return '';
        }
        $vals['max'] = '' . $vals['max'];
        if (strlen($vals['max']) == 0) {
            return '';
        }
        $vals['min'] = '' . $vals['min'];
        if (strlen($vals['min']) == 0) {
            return '';
        }
        return $ref . ' BETWEEN ' .
            I2CE::PDO()->quote($vals['min']) . ' AND ' .
            I2CE::PDO()->quote($vals['max']);
    }
    public function checkLimitString_DB_STRING_between($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return '';
        }
        $vals['max'] = '' . $vals['max'];
        if (strlen($vals['max']) == 0) {
            return '';
        }
        $vals['min'] = '' . $vals['min'];
        if (strlen($vals['min']) == 0) {
            return '';
        }
        return  '((\''.addslashes($vals['min']) .'\' < ' .$ref . ' ) && ( ' . $ref . '< \'' . addslashes($vals['max']) . '\'))';
    }
    public function checkLimitFunction_DB_STRING_between($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return true;
        }
        $vals['max'] = '' . $vals['max'];
        if (strlen($vals['max']) == 0) {
            return true;
        }
        $vals['min'] = '' . $vals['min'];
        if (strlen($vals['min']) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return  (($vals['min'] < $data[$ref] ) 
                    && ( $data[$ref] < $vals['max'] ));
        };
    }
    public function checkLimit_DB_STRING_between($fieldObj,$vals) {
        return ($fieldObj->getDBValue() >= $vals['min']) && ($fieldObj->getDBValue() <= $data['0']['max']);
    }


    public function generateLimit_DB_INT_between($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return '';
        }
        if (!(is_integer($vals['min']) || ctype_digit($vals['min']))) {
            return '';
        }
        if (!(is_integer($vals['max']) || ctype_digit($vals['max']))) {
            return '';
        }
        return $ref . ' BETWEEN ' . $vals['min'] .  ' AND ' . $vals['max'];
    }
    public function checkLimitString_DB_INT_between($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return '';
        }
        if (!(is_integer($vals['min']) || ctype_digit($vals['min']))) {
            return '';
        }
        if (!(is_integer($vals['max']) || ctype_digit($vals['max']))) {
            return '';
        }
        return  '( (' . $vals['min'] .  '< ' . $ref . ') && (' . $ref . '<' . $vals['max'] .'))';
    }
    public function checkLimitFunction_DB_INT_between($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return true;
        }
        if (!(is_integer($vals['min']) || ctype_digit($vals['min']))) {
            return true;
        }
        if (!(is_integer($vals['max']) || ctype_digit($vals['max']))) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return  ( ( $vals['min'] < $data[$ref] ) 
                    && ( $data[$ref] < $vals['max'] ));
        };
    }
    public function checkLimit_DB_INT_between($fieldObj,$vals) {
        return ($fieldObj->getDBValue() >= $vals['min']) && ($fieldObj->getDBValue() <= $data['0']['max']);
    }
    public function generateLimit_DB_FLOAT_between($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return '';
        }
        if (!(is_numeric($vals['min']) )) {
            return '';
        }
        if (!(is_numeric($vals['max']) )) {
            return '';
        }
        return $ref . ' BETWEEN ' . $vals['min'] .  ' AND ' . $vals['max'];
    }
    public function checkLimitString_DB_FLOAT_between($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return '';
        }
        if (!(is_numeric($vals['min']) )) {
            return '';
        }
        if (!(is_numeric($vals['max']) )) {
            return '';
        }
        return  '( (' . $vals['min'] .  '< ' . $ref . ') && (' . $ref . '<' . $vals['max'] .'))';
    }
    public function checkLimitFunction_DB_FLOAT_between($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('min',$vals) || !array_key_exists('max',$vals)) {
            return true;
        }
        if (!(is_numeric($vals['min']) )) {
            return true;
        }
        if (!(is_numeric($vals['max']) )) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return  ( ( $vals['min'] < $data[$ref] ) 
                    && ( $data[$ref] < $vals['max']));
        };
    }
    public function checkLimit_DB_FLOAT_between($fieldObj,$vals) {
        return ($fieldObj->getDBValue() >= $vals['min']) && ($fieldObj->getDBValue() <= $data['0']['max']);
    }






    public function generateLimit_DB_TEXT_in($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        foreach ($vals as $i=>$v) {
            $vals[$i] = I2CE::PDO()->quote(trim($v));
        }
        if (count($vals) == 0) {
            return '';
        }
        return $ref . ' IN (' . implode(',',$vals) . ')';
    }
    public function checkLimitString_DB_TEXT_in($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        foreach ($vals as $i=>$v) {
            $vals[$i] = "'" . addslashes(trim($v)) . "'";
        }
        if (count($vals) == 0) {
            return '';
        }
        return 'in_array(' . $ref . 'array(' . implode(',', $vals) . '))';
    }
    public function checkLimitFunction_DB_TEXT_in($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        if (count($vals) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return in_array( $data[$ref], $vals );
        };
    }
    public function checkLimit_DB_TEXT_in($fieldObj,$vals) {
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        return in_array($fieldObj->getDBValue(), $vals);
    }



    public function generateLimit_DB_DATE_in($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        foreach ($vals as $i=>$v) {
            $vals[$i] = I2CE::PDO()->quote(trim($v));
        }
        if (count($vals) == 0) {
            return '';
        }
        return $ref . ' IN (' . implode(',',$vals) . ')';
    }
    public function checkeLimitString_DB_DATE_in($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        foreach ($vals as &$v) {
            $v = 'I2CE_Date::fromDB(' . $ref . ')->equals(I2CE_Date::fromDB(\'' . addslashes(trim($v)) . '\'))';
        }
        if (count($vals) == 0) {
            return '';
        }
        return '(' . implode(' && ', $vals) . ')';
    }
    public function checkLimit_DB_DATE_in($fieldObj,$vals) {
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        $f_date = $fieldObj->getValue();
        if (!$f_date instanceof I2CE_Date) {
            return null;
        }
        foreach ($vals as $val) {
            $date= I2CE_Date::fromDB($val);
            if (!$date instanceof I2CE_Date) {
                continue;
            }
            if ($f_date->equals($date)) {
                return true;
            }
        }
        return false;
    }

    public function generateLimit_MAP_MULT_in($fieldObj,$vals,$ref,$parent_ref=null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        $return_clause = array();
        foreach( $vals as $i=>$v ) {
            $val = I2CE_PDO::escape_string(trim($v));
            $return_clause[] = "( $ref  = '$val' OR $ref LIKE '$val,%' OR $ref LIKE '%,$val' OR $ref LIKE '%,$val,%' )";
        }
        if ( count($return_clause) == 0 ) {
            return '';
        }
        return "( " . implode( ' OR ', $return_clause ) . " )";
    }
    public function checkLimitString_MAP_MULT_in($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        foreach ($vals as $i=>$v) {
            $vals[$i] = "'" . addslashes(trim($v)) . "'";
        }
        if (count($vals) == 0) {
            return '';
        }
        $set_vals = explode( ',', $fieldObj->getDBValue() );
        foreach ($set_vals as &$v) {
            $v = "'" . addslashes(trim($v)) . "'";
        }
        unset($v);
        if ( count($set_vals) == 0 ) {
            return '';
        }
        return 'count( array_intersect( array( ' . implode(',', $set_vals )
                        . ' ), array( ' . implode( ',', $vals ) . ' ) ) ) > 0';
    }
    public function checkLimitFunction_MAP_MULT_in($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        if (count($vals) == 0) {
            return true;
        }
        $set_vals = explode( ',', $fieldObj->getDBValue() );
        if ( count($set_vals) == 0 ) {
            return true;
        }
        return function($data) use ($set_vals,$vals) {
            return count( array_intersect( $set_vals, $vals ) ) > 0;
        };
    }
    public function checkLimit_MAP_MULT_in($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        $set_vals = explode( ',', $fieldObj->getDBValue() );
        return count( array_intersect( $set_vals, $vals ) ) > 0;
    }


    public function generateLimit_DB_STRING_in($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        foreach ($vals as $i=>$v) {
            $vals[$i] = I2CE::PDO()->quote(trim($v));
        }
        if (count($vals) == 0) {
            return '';
        }
        return $ref . ' IN (' . implode(',',$vals) . ')';
    }
    public function checkLimitString_DB_STRING_in($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        foreach ($vals as $i=>$v) {
            $vals[$i] = "'" . addslashes(trim($v)) . "'";
        }
        if (count($vals) == 0) {
            return '';
        }
        return 'in_array(' . $ref . ', array(' . implode(',', $vals) . '))';
    }
    public function checkLimitFunction_DB_STRING_in($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        if (count($vals) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return in_array( $data[$ref], $vals );
        };
    }
    public function checkLimit_DB_STRING_in($fieldObj,$vals) {
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        return in_array($fieldObj->getDBValue(), $vals);
    }

    public function generateLimit_DB_INT_in($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        foreach ($vals as $i=>$v) {
            if (!(is_integer($v) || ctype_digit($v))) {
                unset($vals[$i]);
            }
        }
        if (count($vals) == 0) {
            return '';
        }
        return $ref . ' IN (' . implode(',',$vals ) . ')';
    }
    public function checkLimitString_DB_INT_in($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        if (count($vals) == 0) {
            return '';
        }
        return 'in_array(' . $ref . 'array(' . implode(',', $vals) . '))';
    }
    public function checkLimitFunction_DB_INT_in($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        if (count($vals) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return in_array( $data[$ref]. $vals );
        };
    }
    public function checkLimit_DB_INT_in($fieldObj,$vals) {
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        return in_array($fieldObj->getDBValue(), $vals);
    }
    public function generateLimit_DB_FLOAT_in($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        foreach ($vals as $i=>$v) {
            if (!(is_numeric($v) )) {
                unset($vals[$i]);
            }
        }
        if (count($vals) == 0) {
            return '';
        }
        return $ref . ' IN (' . implode(',',$vals ) . ')';
    }
    public function checkLimitString_DB_FLOAT_in($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        if (count($vals) == 0) {
            return '';
        }
        return 'in_array(' . $ref . 'array(' . implode(',', $vals) . '))';
    }
    public function checkLimitFunction_DB_FLOAT_in($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        if (count($vals) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return in_array( $data[$ref], $vals );
        };
    }
    public function checkLimit_DB_FLOAT_in($fieldObj,$vals) {
        if (is_array($vals['value'])) {
            $vals = $vals['value'];
        } else {
            $vals = preg_split('/,/',$vals['value'],-1,PREG_SPLIT_NO_EMPTY);
        }
        return in_array($fieldObj->getDBValue(), $vals);
    }





    public function generateLimit_BOOL_true($fieldObj,$vals,$ref,$parent_ref =null) {
        return $ref . ' = 1 ';
    }
    public function checkLimitString_BOOL_true($fieldObj,$vals,$ref) {
        return $ref . ' == 1 ';
    }
    public function checkLimitFunction_BOOL_true($fieldObj,$vals,$ref) {
        return function($data) use ($ref) {
            return $data[$ref] == 1;
        };
    }
    public function checkLimit_BOOL_true($fieldObj,$vals) {
        return $fieldObj->getDBValue() == true;
    }


    public function generateLimit_BOOL_false($fieldObj,$vals,$ref,$parent_ref =null) {
        return $ref . ' = 0 ';
    }
    public function checkLimitString_BOOL_false($fieldObj,$vals,$ref) {
        return $ref . ' == 0 ';
    }
    public function checkLimitFunction_BOOL_false($fieldObj,$vals,$ref) {
        return function($data) use ($ref) {
            return $data[$ref] == 0;
        };
    }
    public function checkLimit_BOOL_false($fieldObj,$vals) {
        return $fieldObj->getDBValue() != true;
    }

    public function generateLimit_YESNO_yes($fieldObj,$vals,$ref,$parent_ref =null) {
        return $ref . ' = 1 ';
    }
    public function checkLimitString_YESNO_yes($fieldObj,$vals,$ref) {
        return $ref . ' == 1 ';
    }
    public function checkLimitFunction_YESNO_yes($fieldObj,$vals,$ref) {
        return function($data) use ($ref) {
            return $data[$ref] == 1;
        };
    }
    public function checkLimit_YESNO_yes($fieldObj,$vals) {
        return $fieldObj->getDBValue() == true;
    }


    public function generateLimit_YESNO_no($fieldObj,$vals,$ref,$parent_ref =null) {
        return $ref . ' = 0 ';
    }
    public function checkLimitString_YESNO_no($fieldObj,$vals,$ref) {
        return $ref . ' == 0 ';
    }
    public function checkLimitFunction_YESNO_no($fieldObj,$vals,$ref) {
        return function($data) use ($ref) {
            return $data[$ref] == 0;
        };
    }
    public function checkLimit_YESNO_no($fieldObj,$vals) {
        return $fieldObj->getDBValue() != true;
    }



    public function generateLimit_YESNO_yesno($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals) || $vals['value'] == '') {
            return '';
        }
        if ($vals['value'] == '1') {
            return $ref . ' = 1';
        } else {
            return $ref . ' = 0';
        }
    }
    public function checkLimitString_YESNO_yesno($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals) || $vals['value'] == '') {
            return '';
        }
        if ($vals['value'] == '1') {
            return $ref . ' == 1';
        } else {
            return $ref . ' == 0';
        }
    }
    public function checkLimitFunction_YESNO_yesno($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals) || $vals['value'] == '') {
            return true;
        }
        if ($vals['value'] == '1') {
            return function($data) use ($ref) {
                return $data[$ref] == 1;
            };
        } else {
            return function($data) use ($ref) {
                return $data[$ref] == 0;
            };
        }
    }
    public function checkLimit_YESNO_yesno($fieldObj,$vals) {
        if ($vals['value'] == '1') {
            return $fieldObj->getDBValue() == 1;
        } else {
            return $fieldObj->getDBValue() == 0;
        }
    }

    public function generateLimit_BOOL_truefalse($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if ($vals['value'] == '1') {
            return $ref . ' = 1';
        } else {
            return $ref . ' = 0';
        }
    }
    public function checkLimitString_BOOL_truefalse($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        if ($vals['value'] == '1') {
            return $ref . ' == 1';
        } else {
            return $ref . ' == 0';
        }
    }
    public function checkLimitFunction_BOOL_truefalse($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        if ($vals['value'] == '1') {
            return function($data) use ($ref) {
                return $data[$ref] == 1;
            };
        } else {
            return function($data) use ($ref) {
                return $data[$ref] == 0;
            };
        }
    }
    public function checkLimit_BOOL_truefalse($fieldObj,$vals) {
        if ($vals['value'] == '1') {
            return $fieldObj->getDBValue() == 1;
        } else {
            return $fieldObj->getDBValue() == 0;
        }
    }




    public function generateLimit_DB_TEXT_like($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' LIKE ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimitString_DB_TEXT_like($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp($vals['value']) . '$/';
        return 'preg_match(\''. addslashes($regexp) .'\',' . $ref .') > 0';
    }
    public function checkLimitFunction_DB_TEXT_like($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp($vals['value']) . '$/';
        return function($data) use ($ref,$regexp) {
            return preg_match( $regexp, $data[$ref] ) > 0;
        };
    }
    public function checkLimit_DB_TEXT_like($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return false;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp($vals['value']) . '$/';
        return preg_match($regexp,$fieldObj->getDBValue()) > 0;
    }

    public function generateLimit_DB_STRING_like($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return $ref . ' LIKE ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimitString_DB_STRING_like($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp($vals['value']) . '$/';;
        return 'preg_match(\''. addslashes($regexp) .'\',' . $ref .') > 0';
    }
    public function checkLimitFunction_DB_STRING_like($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp($vals['value']) . '$/';;
        return function($data) use ($ref,$regexp) {
            return preg_match( $regexp, $data[$ref] ) > 0;
        };
    }
    public function checkLimit_DB_STRING_like($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return false;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp($vals['value']) . '$/';
        return preg_match($regexp,$fieldObj->getDBValue()) > 0;
    }


    public function generateLimit_DB_TEXT_lowerlike($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return 'LCASE(' . $ref . ') LIKE ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimitString_DB_TEXT_lowerlike($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp($vals['value']) . '$/i';
        return 'preg_match(\''. addslashes($regexp) .'\',' . $ref .') > 0';
    }
    public function checkLimitFunction_DB_TEXT_lowerlike($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp($vals['value']) . '$/i';
        return function($data) use ($ref,$regexp) {
            return preg_match( $regexp, $data[$ref] ) > 0;
        };
    }
    public function checkLimit_DB_TEXT_lowerlike($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return false;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp($vals['value']) . '$/i';
        return preg_match($regexp,$fieldObj->getDBValue()) > 0;
    }



    public function generateLimit_DB_STRING_lowerlike($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return 'LCASE(' .$ref . ') = ' . I2CE::PDO()->quote($vals['value']);
    }
    public function checkLimitString_DB_STRING_lowerlike($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }

        $regexp = '/^' . I2CE_Util::convertLikeToRegExp($vals['value']) . '$/i';
        return 'preg_match(\''. addslashes($regexp) .'\',' . $ref .') > 0';
    }
    public function checkLimitFunction_DB_STRING_lowerlike($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }

        $regexp = '/^' . I2CE_Util::convertLikeToRegExp($vals['value']) . '$/i';
        return function($data) use ($ref,$regexp) {
            return preg_match( $regexp, $data[$ref] ) > 0;
        };
    }
    public function checkLimit_DB_STRING_lowerlike($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return false;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp($vals['value']) . '$/i';
        return preg_match($regexp,$fieldObj->getDBValue()) > 0;
    }





    public function generateLimit_DB_TEXT_lowerequals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return 'LCASE(' . $ref . ') = ' . I2CE::PDO()->quote(strtolower($vals['value']));
    }
    public function checkLimitString_DB_TEXT_lowerequals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return ' strtolower(' . $ref  . ') == \''  . addslashes(strtolower($vals['value'])) . '\'';
    }
    public function checkLimitFunction_DB_TEXT_lowerequals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        return function($data) use ($ref,$vals) {
            return strtolower( $data[$ref] ) == strtolower($vals['value']);
        };
    }
    public function checkLimit_DB_TEXT_lowerequals($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return false;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        return (strtolower($fieldObj->getDBValue()) == strtolower($vals['value']));
    }



    public function generateLimit_DB_STRING_lowerequals($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return 'LCASE(' .$ref . ') = ' . I2CE::PDO()->quote(strtolower($vals['value']));
    }
    public function checkLimitString_DB_STRING_lowerequals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return ' strtolower(' . $ref  . ') == \''  . addslashes(strtolower($vals['value'])) . '\'';
    }
    public function checkLimitFunction_DB_STRING_lowerequals($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        return function($data) use($ref,$vals) {
            return strtolower( $data[$ref] ) == strtolower($vals['value']);
        };
    }
    public function checkLimit_DB_STRING_lowerequals($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return false;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        return (strtolower($fieldObj->getDBValue()) == strtolower($vals['value']));
    }



    public function generateLimit_DB_TEXT_contains($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return 'LCASE(' . $ref . ') LIKE ' . I2CE::PDO()->quote('%'.strtolower($vals['value']).'%');
    }
    public function checkLimitString_DB_TEXT_contains($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp('%' . strtolower($vals['value']) . '%') . '$/i';
        return 'preg_match(\''. addslashes($regexp) .'\',' . $ref .') > 0';
    }
    public function checkLimitFunction_DB_TEXT_contains($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp('%' . strtolower($vals['value']) . '%') . '$/i';
        return function($data) use ($ref,$regexp) {
            return preg_match( $regexp, $data[$ref] ) > 0;
        };
    }
    public function checkLimit_DB_TEXT_contains($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return false;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp('%' . strtolower($vals['value']) . '%') . '$/i';
        return preg_match($regexp,$fieldObj->getDBValue()) > 0;
    }


    public function generateLimit_DB_STRING_contains($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return 'LCASE(' . $ref . ') LIKE ' . I2CE::PDO()->quote('%'.strtolower($vals['value']).'%');
    }
    public function checkLimitString_DB_STRING_contains($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp('%' . strtolower($vals['value']) . '%') . '$/i';
        return 'preg_match(\''. addslashes($regexp) .'\',' . $ref .') > 0';
    }
    public function checkLimitFunction_DB_STRING_contains($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp('%' . strtolower($vals['value']) . '%') . '$/i';
        return function($data) use ($ref,$regexp) {
            return preg_match( $regexp, $data[$ref] ) > 0;
        };
    }
    public function checkLimit_DB_STRING_contains($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return false;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp('%' . strtolower($vals['value']) . '%') . '$/i';
        return preg_match($regexp,$fieldObj->getDBValue()) > 0;
    }






    public function generateLimit_DB_TEXT_startswith($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return 'LCASE(' . $ref . ') LIKE ' . I2CE::PDO()->quote(strtolower($vals['value']).'%');
    }
    public function checkLimitString_DB_TEXT_startswith($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp( strtolower($vals['value']) . '%') . '$/i';
        return 'preg_match(\''. addslashes($regexp) .'\',' . $ref .') > 0';
    }
    public function checkLimitFunction_DB_TEXT_startswith($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp( strtolower($vals['value']) . '%') . '$/i';
        return function($data) use ($ref,$regexp) {
            return preg_match( $regexp, $data[$ref] ) > 0;
        };
    }
    public function checkLimit_DB_TEXT_startswith($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return false;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp(  strtolower($vals['value']) . '%') . '$/i';
        return preg_match($regexp,$fieldObj->getDBValue()) > 0;
    }


    public function generateLimit_DB_STRING_startswith($fieldObj,$vals,$ref,$parent_ref =null) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        return 'LCASE(' . $ref . ') LIKE ' . I2CE::PDO()->quote(strtolower($vals['value']).'%');
    }
    public function checkLimitString_DB_STRING_startswith($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return '';
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return '';
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp( strtolower($vals['value']) . '%') . '$/i';
        return 'preg_match(\''. addslashes($regexp) .'\',' . $ref .') > 0';
    }
    public function checkLimitFunction_DB_STRING_startswith($fieldObj,$vals,$ref) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return true;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp( strtolower($vals['value']) . '%') . '$/i';
        return function($data) use ($ref,$regexp) {
            return preg_match( $regexp, $data[$ref] ) > 0;
        };
    }
    public function checkLimit_DB_STRING_startswith($fieldObj,$vals) {
        if (!is_array($vals) || !array_key_exists('value',$vals)) {
            return false;
        }
        $vals['value'] = '' . $vals['value'];
        if (strlen($vals['value']) == 0) {
            return true;
        }
        $regexp = '/^' . I2CE_Util::convertLikeToRegExp( strtolower($vals['value']) . '%') . '$/i';
        return preg_match($regexp,$fieldObj->getDBValue()) > 0;
    }



    public function generateLimit_max_parent($fieldObj,$vals,$ref,$parent_ref =null) {
        //this assumes that it is not the primary form
        if ( array_key_exists( 'linked_field', $vals ) && $vals['linked_field'] ) {
            $parent_field = $vals['linked_field'];
        } else {
            $parent_field = "parent";
        }
        if ($parent_ref !== null) {
            $parent_id = $parent_ref;
        } else if (array_key_exists('parent_id', $vals) && $vals['parent_id']) {
            $parent_id = "'" . addslashes($vals['parent_id']) . "'";
        } else {
            $parent_id = '`parent_form`.`id`';
        }
        if (array_key_exists('offset',$vals) && ((is_int($vals['offset']) && $vals['offset']> 0) || (is_string($vals['offset']) && strlen($vals['offset'])> 0 && ctype_digit($vals['offset'])))) {
            $offset = $vals['offset'];
        } else {
            $offset = 0;
        }
        if ( array_key_exists( 'extra_where', $vals ) ) {
            if ( is_array( $vals['extra_where'] ) ) {
                $wheres = $vals['extra_where'];
            } else {
                $wheres = array( $vals['extra_where'] );
            }
        } else {
            $wheres = array();
        }
        $wheres[] = I2CE_CachedForm::getCachedTableName($fieldObj->getContainer()->getName()) .'.' . $parent_field . ' = ' . $parent_id;
        if ( array_key_exists('allow_null',$vals) && $vals['allow_null']) {
            $wheres[] =  ' NOT (ISNULL( ' . $fieldObj->getName() . ')) ';
        }
        if ( array_key_exists('allow_all',$vals) && $vals['allow_all']) {
            if ( preg_match( '/`[\w+-]+`\.`[\w+-]+`/', $ref ) ) {
                $id_ref = preg_replace ( '/`([\w+-]+)`\.`([\w+-]+)`/', "`$1`.`id`", $ref );
                return  $id_ref . ' = (SELECT `id` FROM ' .
                        I2CE_CachedForm::getCachedTableName($fieldObj->getContainer()->getName()) .
                        ' WHERE  (( ' . implode( ' ) AND ( ' , $wheres) . ' )) ' .
                        ' ORDER BY `' .$fieldObj->getName() . "` DESC LIMIT $offset, 1)  ";
            }
            I2CE::raiseError("Can't figure out how to allow all with this limit ref: $ref.  Please send a message to ihris@googlegroups.com with what you're trying to do and this message so we can make this work correctly.  Defaulting to standard behavior.");
        }

        return  $ref . ' = (SELECT DISTINCT `' . $fieldObj->getName() . '` AS `' . $fieldObj->getName()  . '` FROM ' .
            I2CE_CachedForm::getCachedTableName($fieldObj->getContainer()->getName()) .
            ' WHERE  (( ' . implode( ' ) AND ( ' , $wheres) . ' )) ' .
            ' ORDER BY `' .$fieldObj->getName() . "` DESC LIMIT $offset, 1)  ";
    }

    public function generateLimit_min_parent($fieldObj,$vals,$ref,$parent_ref =null) {
        //this assumes that it is not the primary form
        if ( array_key_exists( 'linked_field', $vals ) && $vals['linked_field'] ) {
            $parent_field = $vals['linked_field'];
        } else {
            $parent_field = "parent";
        }
        if ($parent_ref !== null) {
            $parent_id = $parent_ref;
        } else if (array_key_exists('parent_id', $vals) && $vals['parent_id']) {
            $parent_id = "'" . addslashes($vals['parent_id']) . "'";
        } else {
            $parent_id = '`parent_form`.`id`';
        }
        if (array_key_exists('offset',$vals) && ((is_int($vals['offset']) && $vals['offset']> 0) || (is_string($vals['offset']) && strlen($vals['offset'])> 0 && ctype_digit($vals['offset'])))) {
            $offset = $vals['offset'];
        } else {
            $offset = 0;
        }
        $wheres = array(I2CE_CachedForm::getCachedTableName($fieldObj->getContainer()->getName()) .'.' . $parent_field . ' = ' . $parent_id );
        if ( array_key_exists('allow_null',$vals) && $vals['allow_null']) {
            $wheres[] =  ' NOT (ISNULL( ' . $fieldObj->getName() . ')) ';
        }
        if ( array_key_exists('allow_all',$vals) && $vals['allow_all']) {
            if ( preg_match( '/`[\w+-]+`\.`[\w+-]+`/', $ref ) ) {
                $id_ref = preg_replace ( '/`([\w+-]+)`\.`([\w+-]+)`/', "`$1`.`id`", $ref );
                return  $id_ref . ' = (SELECT `id` FROM ' .
                        I2CE_CachedForm::getCachedTableName($fieldObj->getContainer()->getName()) .
                        ' WHERE  (( ' . implode( ' ) AND ( ' , $wheres) . ' )) ' .
                        ' ORDER BY `' .$fieldObj->getName() . "` ASC LIMIT $offset, 1)  ";
            }
            I2CE::raiseError("Can't figure out how to allow all with this limit ref: $ref.  Please send a message to ihris@googlegroups.com with what you're trying to do and this message so we can make this work correctly.  Defaulting to standard behavior.");
        }

        return  $ref . ' = (SELECT DISTINCT `' . $fieldObj->getName() . '` AS `' . $fieldObj->getName()  . '` FROM ' .
            I2CE_CachedForm::getCachedTableName($fieldObj->getContainer()->getName()) .
            ' WHERE  (( ' . implode( ' ) AND ( ' , $wheres) . ' )) ' .
            'ORDER BY `' .$fieldObj->getName() . "` ASC LIMIT $offset, 1)  ";
    }






    public function generateLimit_max_parent_form($fieldObj,$vals,$ref,$parent_ref =null) {
        if ($parent_ref !== null) {
            $parent_id = $parent_ref;
        } else if (array_key_exists('parent_id', $vals) && $vals['parent_id']) {
            $parent_id = "'" . addslashes($vals['parent_id']) . "'";
        } else {
            $parent_id = '`parent_form`.`id`';
            // Would this be better?
            //$parent_id = '`' . $fieldObj->getContainer()->getName() . '`.`parent`';
        }
        return  $ref . ' = (SELECT MAX( `' . $fieldObj->getName() . '`) FROM ' .
            I2CE_CachedForm::getCachedTableName($fieldObj->getContainer()->getName()) .
            ' WHERE  ' . I2CE_CachedForm::getCachedTableName($fieldObj->getContainer()->getName()) .'.parent = ' . $parent_id . ')';
    }

    public function generateLimit_min_parent_form($fieldObj,$vals,$ref,$parent_ref =null) {
        if ($parent_ref !== null) {
            $parent_id = $parent_ref;
        } else if (array_key_exists('parent_id', $vals) && $vals['parent_id']) {
            $parent_id = "'" . addslashes($vals['parent_id']) . "'";
        } else {
            $parent_id = '`parent_form`.`id`';
            // Would this be better?
            //$parent_id = '`' . $fieldObj->getContainer()->getName() . '`.`parent`';
        }
        return  $ref . ' = (SELECT MIN( `' . $fieldObj->getName() . '`) FROM ' .
            I2CE_CachedForm::getCachedTableName($fieldObj->getContainer()->getName()) .
            ' WHERE  ' . I2CE_CachedForm::getCachedTableName($fieldObj->getContainer()->getName()) .'.parent = ' . $parent_id . ')';
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
