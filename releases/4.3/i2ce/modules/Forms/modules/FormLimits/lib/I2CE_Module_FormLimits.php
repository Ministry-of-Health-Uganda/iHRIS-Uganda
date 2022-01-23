<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @version v4.0.6
* @since v4.0.6
* @filesource 
*/ 
/** 
* Class I2CE_Module_FormLimits
* 
* @access public
*/


class I2CE_Module_FormLimits extends I2CE_Module {

    /** 
     * Method called before the module is enabled for the first time.
     * @param boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        /*
         * This module was split off from Custom Reports.
         * If Custom Reports has been used previously then we should assume the methods
         * are defined there so they need to be turned off until CustomReports can be
         * upgraded when it is required.
         */
        $cr_vers = null;
        I2CE::getConfig()->setIfIsSet( $cr_vers, "/config/data/CustomReports/version" );
        if ( $cr_vers !== null && I2CE_Validate::checkVersion( $cr_vers, '<', '3.2' ) ) {
            I2CE::raiseError( "Removing hooks from CustomReports because they were moved to form-limits." );
            I2CE_ModuleFactory::instance()->removeHooks( "CustomReports" );
        }
        return true;
    }




    /**
     * The 'fuzzy' methods that this module implements.
     * @returns an associative array.
     */    
    public static function getMethods() {
        return   array(
            'I2CE_Form->getLimitStyles'=>'getLimitStyles',   
            'I2CE_Form->checkLimit'=>'checkLimit',
            'I2CE_Form->checkWhereClause'=>'checkWhereClause',
            'I2CE_Form->createCheckFunction'=>'createCheckFunction',
            'I2CE_Form->createCheckLimitString'=>'createCheckLimitString',
            'I2CE_Form->generateLimit'=>'generateLimit',
            'I2CE_Form->generateWhereClause'=>'generateWhereClause',
            );
    }



    /**
     * Implementation of fuzzy method on a I2CE_FormObject to determine what are the 
     * limit  styles for a given field
     * @param I2CE_Form $formObj
     * @param string $field  The name of a field of {$formObj}
     * @returns array
     */
    public function getLimitStyles($formObj,$field) {
        $limitStyles = array();
        $fieldObj = $formObj->getField($field);
        if (!$fieldObj instanceof I2CE_FormField) {
            return $limitStyles;
        }
        return $fieldObj->getLimitStyles();
    }



    /**
     * Checks a limit expression for a field based on  limit data
     * @param I2CE_Form $formObj
     * @param mixed $limit_data
     * array.
     * @returns boolean or null on failure
     */
    public function checkLimit($formObj,$limit_data=array()) {
        if (!is_array($limit_data)) {
            I2CE::raiseError("Expected array for generating where sub-expression, but not received");
            return null;
        }
        if (!array_key_exists('field', $limit_data) || !is_string($limit_data['field'])) {
            I2CE::raiseError("Field name is not given at 'field' " . print_r($limit_data,true));
            return null;
        }
        if (! ($fieldObj = $formObj->getField($limit_data['field'])) instanceof I2CE_FormField) {
            I2CE::raiseError("Field name is not given at {$limit_data['field']} is not a field of " . $formObj->getName());
            return null;
        }
        if (!array_key_exists('style', $limit_data) || !is_string($limit_data['style'])) {
            I2CE::raiseError("Style is not given at 'style' ");
            return null;
        }
        if (!array_key_exists('data',$limit_data) || !is_array($limit_data['data'])) {
            $limit_data['data'] = array();
        }
        $method = 'checkLimit_' .$limit_data['style'];
        if (!$fieldObj->_hasMethod($method)) {
            I2CE::raiseError("Not able to check limit for style " . $limit_data['style'] . " for class " . get_class($fieldObj));
            return null;
        }
        $ret = $fieldObj->$method($limit_data['data']);
        if (is_bool($ret)) {
            return $ret;
        } else {
            I2CE::raiseError("Unexpected return from limit " . print_r($ret,true));
            return null;
        }
    }



    /**
     * The implementation of the fuzzy method to check that a where clause is satisfied by the given form
     * Walks down the where clause data  create the WHERE query it defined.
     * @param I2CE_Form $formObj
     * @param mixed $expr array or class implementing ArrayAccess, Iterator, and Countable (e.g. MagicDataNode) . the where data.  
     * @returns boolean, null on failure
     */
    public function checkWhereClause($formObj,$expr) {
        if (! (is_array($expr) || ($expr instanceof ArrayAccess && $expr instanceof Countable && $expr instanceof Iterator ))) {
            I2CE::raiseError("array was not found while processing the where clause \n");
            return false;
        }
        if (!isset($expr['operator']) || !is_string($expr['operator'])) {
            I2CE::raiseError("No operator set");
            return false;
        }
        $operator = $expr['operator'];
        if ($operator == 'FIELD_LIMIT') {
            $ret = $this->checkLimit($formObj,$expr);
            if (!is_bool($ret)) {
                return null;
            } else {
                return $ret;
            }
        } else {
            if (!isset($expr['operand']) ) {
                I2CE::raiseError("No operands set");
                return false;
            }
            if (! (
                    is_array($expr['operand']) 
                    || ($expr['operand'] instanceof ArrayAccess && $expr['operand'] instanceof Iterator &&$expr['operand'] instanceof Countable))
                ) {
                I2CE::raiseError("Invalid operands set");                
                return false;                
            }
            if ($operator === 'NOT') {
                reset($expr['operand']);
                $ret = $this->checkWhereClause($formObj,current($expr['operand']));
                if (is_bool($ret)) {
                    return !$ret;
                } else {
                    return null;
                }
            } else if ($operator == 'AND') {
                foreach ($expr['operand'] as $sub) {                
                    $val = $this->checkWhereClause($formObj,$sub);
                    if ($val === false) {
                        return false;
                    } else if (!is_bool($val)) {
                        return null;
                    }
                }
                return true;
            } else if ($operator == 'OR') {
                $has_null = false;
                foreach ($expr['operand'] as $sub) {                
                    $val = $this->checkWhereClause($formObj,$sub);
                    if ($val === true) {
                        return true;
                    } else if (!is_bool($val)) {
                        $has_null =true;
                    }
                }
                if ($has_null) {
                    return null; 
                } else {
                    return false;
                }
            } else if ($operator == 'XOR') { //only an odd number of things should be true.
                $parity = false;
                foreach ($expr['operand'] as $sub) {                
                    $val = $this->checkWhereClause($formObj,$sub);
                    if (!is_bool($val)) {
                        return null;
                    }
                    if ($val === true) {
                        $parity = !$parity;
                    } 
                }
                return $parity;
            } else {
                I2CE::raiseError("Unrecognzied operator $operator\n");
                return null;
            }
        }
    }





    /**
     * Create a check function based on the where data.
     * The function takes on argument which is an array indexed by the field names and with values the value of the field.
     * @param I2CE_Form $form
     * @param mixed $expr array or class implementing ArrayAccess, Iterator, and Countable (e.g. MagicDataNode) . the where data.  
     * @param callback $field_refernece_callback.  A callback function whose first arguement is the form, the second arguements
     * is the field and which returns the way the field value should be references as a field.  If the callback is null (the default) then
     * the reference used is $data["$field']
     * @param string $func_args.  Defaults to $data
     * @returns false on failure a funciton on sucess.
     */
    public function createCheckFunction($formObj,$expr,$field_reference_callback=null, $func_args = '$data') {
        if (is_array($expr) && count($expr) > 0) {
            $function_string = $this->createCheckFunctionString($formObj, $expr,$field_reference_callback);
            if (!is_string($function_string)) {
                return false;
            }
        } else {
            $function_string = 'true';
        }
        I2CE::raiseError("Got $function_string");
        $function_string = 'return (' . $function_string . ');';
        return @create_function($func_args,$function_string);
    }


    protected  static $checkOperatorMap = 
        array(
            'AND'=>'&&',
            'OR'=>'||',
            'XOR'=>'xor'
            );

    /**
     * Create a check function boolean expression based on the where data.
     * The function takes on argument which is an array indexed by the field names and with values the value of the field.
     * @param I2CE_Form $form
     * @param mixed $expr array or class implementing ArrayAccess, Iterator, and Countable (e.g. MagicDataNode) . the where data.  
     * @param callback $field_refernece_callback.  A callback function whose first arguement is the form, the second arguements
     * is the field and which returns the way the field value should be references as a field.  If the callback is null (the default) then
     * the reference used is $data["$field']
     * @returns false on failure a string to which can be evalued as true/false on success.
     */
    public function createCheckFunctionString($formObj, $expr, $field_reference_callback=null) {
        I2Ce::raiseError("Creaging Check Function string on " . $formObj->getName() . " from:\n" . print_r($expr,true));
        if (! (is_array($expr) || ($expr instanceof ArrayAccess && $expr instanceof Countable && $expr instanceof Iterator ))) {
            I2CE::raiseError("array was not found while processing the where clause \n");
            return false;
        }
        if (!isset($expr['operator']) || !is_string($expr['operator'])) {
            I2CE::raiseError("No operator set");
            return false;
        }
        switch($expr['operator']) {
        case 'FIELD_LIMIT':
            $subExpr = $this->createCheckLimitString($formObj,$expr, $field_reference_callback);
            if ($subExpr === false || !is_string($subExpr)) {
                I2CE::raiseError("Could not generate check function for " . print_R($expr,true));
                return false;
            }
            $subExpr = trim($subExpr);
            if (strlen($subExpr) > 0) {
                $subExpr =   ' ( ' . $subExpr . ')' ;
            }
            return $subExpr;
        case 'AND': //we are allowing these to be n-ary operators
        case 'OR':
        case 'XOR':
            if (!isset($expr['operand']) ) {
                I2CE::raiseError("No operands set");
                return false;
            }
            if (! (
                    is_array($expr['operand']) 
                    || ($expr['operand'] instanceof ArrayAccess && $expr['operand'] instanceof Iterator &&$expr['operand'] instanceof Countable))
                ) {
                I2CE::raiseError("Invalid operands set");                
                return false;                
            }
            $subExpr = array();                
            foreach ($expr['operand'] as $sub) {
                $tmpExpr = $this->createCheckFunctionString($formObj,$sub, $field_reference_callback);
                if  ($tmpExpr == false || !is_string($tmpExpr)) {
                    I2CE::raiseError("GOT BAD subexpression from: " . print_r($sub,true));
                    return false;
                }
                $tmpExpr = trim($tmpExpr);
                if (strlen($tmpExpr) > 0) {
                    $subExpr[] = $tmpExpr;
                }
            }
            if (count($subExpr) > 0) {
                return  ' ( ' . implode(' ' . self::$checkOperatorMap[$expr['operator']] . ' ' ,  $subExpr) . ' ) ' ;
            } else {
                return '';
            }
        case 'NOT':
            if (!isset($expr['operand']) ) {
                I2CE::raiseError("No operands set");
                return false;
            }
            if (! (
                    is_array($expr['operand']) 
                    || ($expr['operand'] instanceof ArrayAccess && $expr['operand'] instanceof Iterator &&$expr['operand'] instanceof Countable))
                ) {
                I2CE::raiseError("Invalid operands set");                
                return false;                
            }
            if ( count($expr['operand']) != 1) {
                I2CE::raiseError("Expecing one operand but did not receive");
                return false;
            }
            reset($expr['operand']);
            $subExpr  = $this->createCheckFunctionString($formObj,current($expr['operand']), $field_reference_callback);
            if ($subExpr === false || !is_string($subExpr)) {
                I2CE::raiseError("got bad subexpression from :" . print_r(current($expr['operand']),true));
                return false;
            }            
            $subExpr = trim($subExpr);
            if (strlen($subExpr) > 0) {
                $subExpr = ' (!( ' . $subExpr. ' )) ';
            }
            return $subExpr;
        default:
            I2CE::raiseError("Unrecognzied operator " . $expr['operator'] . "\n");
            return false;
        }

    }



    /**
     * Checks a limit boolean expression for a field based on  limit data
     * @param I2CE_Form $formObj
     * @param mixed $limit_data
     * array.
     * @param callback $field_refernece_callback.  A callback function whose first arguement is the form, the second arguements
     * is the field and which returns the way the field value should be references as a field.  If the callback is null (the default) then
     * the reference used is $data["$field']
     * @returns string  or false on failure
     */
    public function createCheckLimitString($formObj,$limit_data=array(),$field_reference_callback = null) {
        if (!is_array($limit_data)) {
            I2CE::raiseError("Expected array for generating where sub-expression, but not received");
            return false;
        }
        if (!array_key_exists('field', $limit_data) || !is_string($limit_data['field'])) {
            I2CE::raiseError("Field name is not given at 'field' ");
            return false;
        }
        if (! ($fieldObj = $formObj->getField($limit_data['field'])) instanceof I2CE_FormField) {
            I2CE::raiseError("Field named  {$limit_data['field']} is not a field of " . $formObj->getName() . "\nValid fields are:\n\t" . implode(",",$formObj->getFieldNames()));
            return false;
        }
        if (!array_key_exists('style', $limit_data) || !is_string($limit_data['style'])) {
            I2CE::raiseError("Style is not given at 'style' ");
            return false;
        }
        if (!array_key_exists('data',$limit_data) || !is_array($limit_data['data'])) {
            $limit_data['data'] = array();
        }
        $method = 'checkLimitString_' . $limit_data['style'];
        if (!$fieldObj->_hasMethod($method)) {
            I2CE::raiseError("Not able to check limit (via $method)  for style " . $limit_data['style'] . " for class " . get_class($fieldObj));
            return false;
        }
        if ($field_reference_callback !== null) {
            if ( !is_string($ref = call_user_func($field_reference_callback, $formObj->getName(),$field)) === false){ 
                I2CE::raiseError("Invalid field reference callback function");
                return false;
            }
        } else {
            $ref = '$data[\'' . $limit_data['field'] . '\']';
        }
        $ret = $fieldObj->$method($limit_data['data'],$ref);
        if (is_string($ret)) {
            return $ret;
        } else {
            I2CE::raiseError("Unexpected return from limit $style");
            return false;
        }
    }



    /**
     * Generates a limit expression for a form based on  limit data.  Called by {generateWhereClause()}
     * @param I2CE_Form $formObj
     * @param mixed $limit_data
     * array.
     * @param callback $field_refernece_callback.  A callback function whose first arguement is the form, the second arguements
     * is the field and which returns the way the field value should be references as a field.  If the callback is null (the default) then
     * the reference used is "$form+$field"
     * @param string $parent_ref.  Defaults to null.  If not null, it is the referent to the parent id of the form
     * @returns string SQL statement false on failure
     */
    public function generateLimit($formObj,$limit_data=array(), $field_reference_callback = null, $parent_ref  = null) {
        if (!is_array($limit_data)) {
            I2CE::raiseError("Expected array for generating where sub-expression, but not received");
            return false;
        }
        if (!array_key_exists('field', $limit_data) || !is_string($limit_data['field'])) {
            I2CE::raiseError("Field name is not given at 'field' ");
            return false;
        }
        if (! ($fieldObj = $formObj->getField($limit_data['field'])) instanceof I2CE_FormField) {
            I2CE::raiseError("Field {$limit_data['field']} is not a field of " . $formObj->getName());
            return false;
        }
        if ($field_reference_callback !== null) {
            if ( !is_string($ref = call_user_func($field_reference_callback, $formObj->getName(),$limit_data['field']))) {
                I2CE::raiseError("Invalid field reference callback function");
                return false;
            }
        } else {
            $ref = '`' . $formObj->getName() . '+' . $limit_data['field'] . '`';
        }
        return $fieldObj->generateLimit($limit_data,$ref, $parent_ref);
    }



    

    /**
     * The implementation of the fuzzy method that recurses down the where clause data to make the limit part of a  SQL query.
     * Walks down the where clause data  create the WHERE query it defined.
     * @param I2CE_Form $formObj
     * @param mixed $expr array or class implementing ArrayAccess, Iterator, and Countable (e.g. MagicDataNode) . the where data.  
     * @param callback $field_refernece_callback.  A callback function whose first arguement is the form, the second arguements
     * is the field and which returns the way the field value should be references as a field.  If the callback is null (the default) then
     * the reference used is "$form+$field"
     * @param string $parent_ref.  Defaults to null.  If not null, it is the referent to the parent id of the form
     * @returns SQL statement, false on failure
     */
    public  function generateWhereClause($formObj,$expr,$field_reference_callback = null, $parent_ref = null) {
        if (! (is_array($expr) || ($expr instanceof ArrayAccess && $expr instanceof Countable && $expr instanceof Iterator ))) {
            I2CE::raiseError("array was not found while processing the where clause \n");
            return false;
        }
        if (!isset($expr['operator']) || !is_string($expr['operator'])) {
            I2CE::raiseError("No operator set:" . print_r($expr,true));
            return false;
        }
        $operator = $expr['operator'];
        switch($operator) {
        case 'FIELD_LIMIT':
            $subExpr = $this->generateLimit($formObj,$expr, $field_reference_callback, $parent_ref);
            if ($subExpr === false || !is_string($subExpr)) {
                I2CE::raiseError("Could not generate limit for " . print_R($expr,true));
                return false;
            }
            $subExpr = trim($subExpr);
            if (strlen($subExpr) > 0) {
                $subExpr =   ' ( ' . $subExpr . ')' ;
            }
            return $subExpr;
        case 'AND': //we are allowing these to be n-ary operators
        case 'OR':
        case 'XOR':
            if (!isset($expr['operand']) ) {
                I2CE::raiseError("No operands set");
                return false;
            }
            if (! (
                    is_array($expr['operand']) 
                    || ($expr['operand'] instanceof ArrayAccess && $expr['operand'] instanceof Iterator &&$expr['operand'] instanceof Countable))
                ) {
                I2CE::raiseError("Invalid operands set");                
                return false;                
            }
            $subExpr = array();            
            foreach ($expr['operand'] as $sub) {
                $tmpExpr = $this->generateWhereClause($formObj,$sub, $field_reference_callback, $parent_ref);
                if  ($tmpExpr === false || !is_string($tmpExpr)) {
                    I2CE::raiseError("Bad where clause from:" . print_r($sub,true));
                    return false;
                }
                $tmpExpr = trim($tmpExpr);
                if (strlen($tmpExpr) > 0) {
                    $subExpr[] = $tmpExpr;
                }
            }
            if (count($subExpr) > 0) {
                return  ' ( ' . implode(' ' . $operator . ' ' ,  $subExpr).' ) ' ;
            } else {
                return '';
            }
        case 'NOT':
            if (!isset($expr['operand']) ) {
                I2CE::raiseError("No operands set");
                return false;
            }
            if (! (
                    is_array($expr['operand']) 
                    || ($expr['operand'] instanceof ArrayAccess && $expr['operand'] instanceof Iterator &&$expr['operand'] instanceof Countable))
                ) {
                I2CE::raiseError("Invalid operands set");                
                return false;                
            }
            if ( count($expr['operand']) != 1) {
                I2CE::raiseError("Expecing one operand but did not receive");
                return false;
            }
            reset($expr['operand']);
            $subExpr  = $this->generateWhereClause($formObj,current($expr['operand']), $field_reference_callback, $parent_ref);
            if ($subExpr === false || !is_string($subExpr)) {
                I2CE::raiseError("Bad not");
                return false;
            }            
            $subExpr = trim($subExpr);
            if (strlen($subExpr) > 0) {
                $subExpr = ' (NOT( ' . $subExpr. ' )) ';
            }
            return $subExpr;
        default:
            I2CE::raiseError("Unrecognzied operator $operator\n");
            return false;
        }
        return '';
    }







}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
