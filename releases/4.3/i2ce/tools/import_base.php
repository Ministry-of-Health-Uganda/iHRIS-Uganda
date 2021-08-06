<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 * The page wrangler
 * 
 * This page loads the main HTML template for the home page of the site.
 * @package iHRIS
 * @subpackage DemoManage
 * @access public
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since Demo-v2.a
 * @version Demo-v2.a
 */







require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'CLI.php');



/******************************
 *
 * helper functions to get columns from a csv file based on a header row
 *
 *****************************/



$header_map = array();
function getColumn($field_name,$data) {
    global $header_map;
    if (!array_key_exists($field_name,$header_map)) {
        I2CE::raiseError("Trying to get unmapped field $field_name". E_USER_ERROR);        
    }
    return trim($data[$header_map[$field_name]]);
}


function setupColumns($fp,$required_columns) {
    global $header_map;
    $header_map = array();
    $headers = fgetcsv($fp);
    if (!is_array($headers) || count($headers) == 0) {
        I2CE::raiseError("Could not get header information", E_USER_ERROR);
        return false;
    }    
    foreach ($required_columns as $field_name=>$header)  {
        $j = array_search($header,$headers);
        if ($j === false) {
            I2CE::raiseError("Could not find $header column",E_USER_ERROR);
            die();
        }
        $header_map[$field_name] = $j;
    }
    return $headers;
}


/******************************
 *
 * form creation and caching  
 *
 *****************************/


$form_factory = I2CE_FormFactory::instance();
$user = new I2CE_User(1, false, false, false);


function getFormObjByWhere($form,$where) {
    global $form_factory;
    $formIds = I2CE_FormStorage::search($form,false,$where);
    if (count($formIds) > 1) {
        //I2CE::raiseError("Ambigous lookup for form $form:" . print_r($where,true)); 
        return null; //the above should die but who knows.
    } else if (count($formIds) == 1) {
        reset($formIds);
        $formObj = $form_factory->createForm($form . '|' . current($formIds));
        if (!$formObj instanceof I2CE_Form) {
            return null;
        }
        $formObj->populate();
        return $formObj;
    } else {
        return null;
    }

}



$always_create = array();

function getFormObjByFields($form,$fields,$create=false,$parent = false,$id = '0') {
    global $always_create;
    if (!is_array($fields) || count($fields) == 0) {
        I2CE::raiseError("No fields to lookup form $form on");
        return false;
    }    
    $where = array(
        'operator'=>'AND',
        'operand'=>array()
        );
    foreach ($fields as $field=>$val) {
        if ($field == 'name') {
            $where['operand'][] = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>$field,
                'style'=>'lowerlike',
                'data'=>array(
                    'value'=>$val
                    )            
                );
        } else {
            $where['operand'][] = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>$field,
                'style'=>'equals',
                'data'=>array(
                    'value'=>$val
                    )            
                );
        }
    }
    $formObj = getFormObjByWhere($form,$where);    
    if ($formObj instanceof I2CE_Form) {
        return $formObj;
    } 
    if (is_string($create)) {
        $str = "Create the form `$form` with the fields:\n";
        foreach ($fields as $f=>$v) {
            $str .= "\t$f => '$v'\n";
        }
        if ($create == 'simple_prompt') {
            $create = simple_prompt($str);
        } else if ($create == 'prompt') {
            if (!array_key_exists($form,$always_create)) {
                $always_create[$form] = null;
            }
            $create = prompt($str,$always_create[$form]);
        } else {
            I2CE::raiseError("Unrecognized creation",E_USER_ERROR);
            return false;
        }
    }       
    if (!$create) {
        return null;
    }
    $formObj = createFormWithFields($form,$fields,$parent,$id);
    if (!$formObj instanceof I2CE_Form) {
        return null;
    }
    //try to create the object
    return $formObj;
}




function createFormWithFields($form,$fields,$parent = false,$id = '0') {
    global $user;
    global $form_factory;
    $formObj = $form_factory->createForm($form);
    if (!$formObj instanceof I2CE_Form) {
        I2CE::raiseError("Could not instantiate form $form", E_USER_ERROR);
        return null;
    }
    foreach ($fields as $k=>$v) {
        $formObj->getField($k)->setFromDB($v);
    }
    if ($parent !== false) {
        $formObj->setParent($parent);
    }
    if ($id !== '0') {
        $formObj->setId($id);
    }
    $formObj->save($user);
    return $formObj;
}


function getFormIdByWhere($form,$where) {
    $formObj = getFormObjByWhere($form,$where);
    if (!$formObj instanceof I2CE_Form) {
        return '0';
    }
    $id = $formObj->getId();
    $formObj->cleanup();
    return  $id;
}


function getFormIdByFields($form,$fields, $create = false,$parent = false,$id = '0') {
    $formObj = getFormObjByFields($form,$fields,$create,$parent,$id);
    if (!$formObj instanceof I2CE_Form) {
        return '0';
    }
    $id = $formObj->getId();
    $formObj->cleanup();
    return  $id;
}






