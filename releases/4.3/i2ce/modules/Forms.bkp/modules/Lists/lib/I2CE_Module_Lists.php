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
* @version v3.2.3
* @since v3.2.3
* @filesource 
*/ 
/** 
* Class I2CE_Module_Lists
* 
* @access public
*/


class I2CE_Module_Lists extends I2CE_Module{





    public static function getHooks() {
        return array(
            'validate_formfield'=> 'validate_formfield',
            'form_post_changeid'=>'remapID_hook'
            );         
    }


    /**
     * Hooked method to remap a given id
     */
    public function remapID_hook($args) {
        if (!is_array($args)   ||  !array_key_exists('form',$args) || !array_key_exists('oldid',$args) || !array_key_exists('newid',$args)) {
            return;
        }
        return $this->remapID($args['form'],$args['oldid'],$args['newid']);
    }


    


    /**
     * method to remap a given id
     * @param string $form
     * @param string $oldid
     * @param string $newid
     */
    public function remapID($form,$oldid,$newid) {
        $ff = I2CE_FormFactory::instance();
        $list = $ff->createForm($form);
        if (!$list instanceof I2CE_List) {
            return;
        }
        //go through each  form and each field and 
        $forms =I2CE_List::getFieldsMappingToList($list);
        foreach ($forms as $mapForm=>$fields) {
            foreach ($fields as $fieldName=>$fieldObj) {
                I2CE::raiseError("Remapping $mapForm+" . $fieldName . " from $form|$oldid to $form|$newid");
                $fieldObj->remapField($form,$oldid,$newid);
            }
        }
    }



    /**
     * Hooked Function to check if a field is unique
     * @param I2CE_FormField $field_obj
     */
    public function validate_formfield($field_obj) {
        if (!$field_obj->hasOption('unique') || !$field_obj->getOption('unique') || !$field_obj->isValid()) {
            return;
        }
        if ( !$field_obj->hasOption('unique_field') ) {
            return;
        }
        $unique = $field_obj->getOption('unique_field');
        if ( strpos( $unique, ':' ) === false ) {
            //the value is not a mapped thing.  this is handled by hooked mehtod defined in I2CE_FormStorage
            return;
        }
        $form_obj = $field_obj->getContainer();        
        if (!$form_obj instanceof I2CE_Form) {
            return;
        }   
        $factory = I2CE_FormFactory::instance();
        //$unique should have the form 'unqique_field:form2(+field2):..:..:formM(+fieldM):...:formN
        //example $unique = 'region:country' or 'region+region+country:country' are the same.  
        //    means that $field_obj needs to be unqiue when reseticted to the set of forms within a country and all of its regions
        //    the country and the region that is specified is the 
        //    in this case, we need that region is a field of $form_obj
        //example: $unqiue = 'county:district+region:region:country' or 'county:district:region:country' are the same
        //    means that $field_obj needs to be unique when resitrcicted to a country, any of its regions any of those regions
        //    in this case, we need that county is a field of $form_obj
        //example: $unqiue = '[location]county:district+region:region:country' or 'county:district:region:country' are the same
        //    means that $field_obj needs to be unique when resitrcicted to a country, any of its regions any of those regions
        //    in this case, we need that location is a field of $form_obj
        $unique_fields = explode(',',$unique);
        $matches = null;
        $names = array();
        $main_where = array (
                'operator' => 'FIELD_LIMIT',
                'style' => 'equals',
                'field' => $field_obj->getName(),
                'data' => array( 'value' => $field_obj->getDBValue() )
                );
        foreach ($unique_fields as $unique_field) {
            if ($matches === false ) {
                break;
            }
            if ( strpos( $unique_field, ':' ) === false ) {
                //this field is not mapped... just handle it as a regular value.                
                if ( !($unique_field_obj = $form_obj->getField($unique_field)) instanceof I2CE_FormField ) {
                    I2CE::raiseError("Invalid field $unqiue_field");
                    return;
                }
                if ($unique_field_obj->hasHeader('default')) {
                    $names[] = $unique_field_obj->getHeader('default');
                } else {
                    $names[] = $unique_field_obj->getName();
                }
                $where = array( $main_where );
                if ( $unique_field_obj->getDBValue()->isValid()) {
                    $where[] = array(
                        'operator'=>'FIELD_LIMIT',
                        'style'=>'equals',
                        'field'=>$unique_field_obj->getName(),
                        'data'=>array('value'=>$unique_field_obj->getDBValue())
                        );                
                } else {
                    $where[] = 
                        array(
                            'operator'=>'OR',
                            'operand'=>array(
                                0=> array(
                                    'operator'=>'FIELD_LIMIT',
                                    'style'=>'equals',
                                    'field'=>$unique_field_obj->getName(),
                                    'data'=>array('value'=>$unique_field_obj->getDBValue())
                                    ),
                                1=>array(
                                    'operator'=>'FIELD_LIMIT',
                                    'style'=>'null',
                                    'field'=>$unique_field_obj->getName()
                                    )
                                )
                            );

                }
                if ( count( $where ) > 1 ) {
                    $where = array( 
                            'operator' => 'AND',
                            'operand' => $where
                            );
                }
                $found = I2CE_FormStorage::search($form_obj->getName(),false,$where);
                foreach ($found as &$f) {
                    $f = (string) $f;
                }
                $matches = ((count($found)> 0) && (in_array((string)$form_obj->getId(), $found,true)));
            } else {
                $field_path = explode(':',$unique_field);
                $restricted_field = false;
                if (preg_match('/^\[(.*?)\](.*)$/',$field_path[0])) {            
                    $restricted_field = $matches[1];
                    $field_path[0] = $matches[2];
                } else if (preg_match('/^(.*?)\+(.*)$/',$field_path[0],$matches)) {
                    $restricted_field = $matches[1];
                } else {
                    $restricted_field = $field_path[0];
                }
                $restricted_field_obj = $form_obj->getField( $restricted_field );
                if ( !$restricted_field_obj instanceof I2CE_FormField_MAP ) {
                    I2CE::raiseError( "Invalid field passed as restricted field for " . $form_obj->getName() . ": $unique_field" );
                    return;
                }
                if ($restricted_field_obj->hasHeader('default')) {
                    $names[] = $restricted_field_obj->getHeader('default');
                } else {
                    $names[] = $restricted_field_obj->getName();
                }                
                //now let's split up field_path into the forms and the fields
                $top_formid = I2CE_List::walkupFieldPath($field_path,$restricted_field_obj->getDBValue());
                if ($top_formid === false) {
                    //the value is not set. or inappropriately set.  error silently.  
                    //this is handled by hooked method defined in I2CE_Module_Form.
                    return;
                }
                //now we get all forms under $top_formid defined by the field path
                $field_name = $field_obj->getName();
                $field_val = $field_obj->getDBValue();
                $form_id = $form_obj->getID();
                
                $dtree_path = $field_path;
                array_unshift( $dtree_path, $form_obj->getName() );
                list( $top_form, $top_id ) = explode( '|', $top_formid, 2 );
                $dtree_limits = array(
                        $top_form =>
                        array( 'operator' => 'FIELD_LIMIT',
                            'style' => 'equals',
                            'field'=>'id',
                            'data' => array( 'value' => $top_id )
                            ),
                        $form_obj->getName() => $main_where,
                        );

                $options = I2CE_List::buildDataTree( $dtree_path, array( $form_obj->getName() ), $dtree_limits );
                $options = I2CE_List::flattenDataTree( $options );
                if ( count( $options ) == 1 ) {
                    // If there's only one match and it is this form then don't block
                    // changes.
                    if ( $options[0]['value'] != $form_obj->getNameId() ) {
                        $matches = true;
                    }
                } elseif ( count( $options ) > 1 ) {
                    $matches = true;
                }

                /*
                array_pop($field_path);
                $options = I2CE_List::monsterMash(
                    $form_obj->getName(),  //facility
                    $restricted_field, //location
                    $top_formid, //country|10
                    $field_path, //array(county+district,district+region,region+country)            
                    $field_name, //name
                    false
                    );

                //starts get all facility where location = country|10  
                //    get all regions region|X where region+country = country|10
                //    this means we need to start with
                //           link_field = country (e.g link_field_path[$len-1] 
                //           list(sub_form,sub_link_field) = explode(+,end(subfields) ) == (region,country)
                //             
                //next get all facility where location = residence|X 
                $option_matches = false;
                foreach ($options as $id=>$data) {
                    if (array_key_exists($field_name,$data) && ($data[$field_name] == $field_val) && ( $id != $form_id)) {
                        $option_matches =true;
                        break;
                    }
                }
                $matches = ($option_matches);
                */
            }
        }
        if ($matches === true) {
            if (count($names) > 1) {
                $field_obj->setInvalidMessage('unique_fields',null,' '.implode(', ',$names) );
            } else if (count($names) == 1) {
                $field_obj->setInvalidMessage('unique_field',null,' '.implode(', ',$names) );
            } else {
                $field_obj->setInvalidMessage('unique');
            }
            return;
        }  
    }


    /**
     * Upgrade module method
     */
    public function upgrade($old_vers,$new_vers) {
        if (I2CE_Validate::checkVersion($old_vers,'<','3.2.3')) {
            $config= I2CE::getConfig()->traverse('/modules/forms/formClasses/I2CE_SimpleList/fields/type',false,false);
            if ($config instanceof I2CE_MagicDataNode) {
                $config->erase();
            }
        }
        if (I2CE_Validate::checkVersion($old_vers,'<','4.0.1')) {
            if (!I2CE_Util::runSQLScript('CREATE_formfield_mult_map_componentize.sql',null,false)) { //don;t use a transaction
                I2CE::raiseError("Could not install remapping SQL  function");
                return false;
            }        
        }
        if (I2CE_Validate::checkVersion($old_vers,'<','4.2.0.5')) {
            if (!$this->fixOrders()) {
                I2CE::raiseError("Could not fix orders");
                return false;
            }        
        }
        if (I2CE_Validate::checkVersion($old_vers,'<','4.2.0.4')) {
            if (!$this->fixReportDisplay()) {
                I2CE::raiseError("Could not fix orders");
                return false;
            }        
        }
        if (I2CE_Validate::checkVersion($old_vers,'<','4.2.0.4')) {
            if (!$this->fixPrintfNoLimits()) {
                I2CE::raiseError("Could not fix orders");
                return false;
            }        
        }
        return true;
    }

    protected function fixOrders() {
        //change  meta/display/orders/$type to to meta/display/$type/orders to follow rest of the meta/display/* options
        $formClasses = I2CE::getConfig()->getKeys("/modules/forms/formClasses");        
        foreach ($formClasses as $formClass ) {
            $fields =  I2CE::getConfig()->getKeys("/modules/forms/formClasses/$formClass/fields");        
            foreach ($fields as $field) {                
                $disp_path = "/modules/forms/formClasses/$formClass/fields/$field/meta/display";
                $ord_path = "/modules/forms/formClasses/$formClass/fields/$field/meta/display/orders";
                if ( ! ($ordNode = I2CE::getConfig()->traverse($ord_path,false,false)) instanceof I2CE_MagicDataNode
                     ||! ($dispNode = I2CE::getConfig()->traverse($disp_path,true,false)) instanceof I2CE_MagicDataNode
                    ){
                    continue;
                }
                foreach ($ordNode->getAsArray() as $style=>$data) {
                    I2CE::raiseError("Moving $ord_path\nto $disp_path/$style/orders");
                    $dispNode->$style->orders = $data;
                }
                $ordNode->erase();
            }
        }
        return true;
    }


    protected function fixReportDisplay() {
        // meta/reportSelect/$style
        //   meta/display_report/$stle

        $formClasses = I2CE::getConfig()->getKeys("/modules/forms/formClasses");        
        $keys = array('reportSelect','display_report');
        foreach ($formClasses as $formClass ) {
            $fields =  I2CE::getConfig()->getKeys("/modules/forms/formClasses/$formClass/fields");        
            foreach ($fields as $field) {              
                foreach ($keys as $key) {
                    $src_path ="/modules/forms/formClasses/$formClass/fields/$field/meta/$key";
                    if (! ($sourceNode = I2CE::getConfig()->traverse($src_path,false,false)) instanceof I2CE_MagicDataNode
                        ){
                        continue;
                    }
                    foreach ($sourceNode->getAsArray() as $style=>$data) {
                        $dest_path ="/modules/forms/formClasses/$formClass/fields/$field/meta/display/$style/$key";
                        if (! ($destNode = I2CE::getConfig()->traverse($dest_path,true,false)) instanceof I2CE_MagicDataNode) {
                            continue;
                        }
                        I2CE::raiseError("Moving $src_path/$style\nto $dest_path");
                        $destNode->setValue($data);
                    }
                    $sourceNode->erase();
                }
            }
        }
        return true;
    }



    protected function fixPrintfNoLimits() {
        // meta/display/$form/$style/printf 
        // meta/display/$form/$style/printf_args
        // meta/display/$form/$style/no_limits
        //  meta/display/$form/$s/printf_arg_styles 

        $formClasses = I2CE::getConfig()->getKeys("/modules/forms/formClasses");        
        $keys = array('printf','printf_args','printf_arg_styles','no_limits');
        $forms = I2CE::getConfig()->getKeys("/modules/forms/forms");
        foreach ($formClasses as $formClass ) {
            $fields =  I2CE::getConfig()->getKeys("/modules/forms/formClasses/$formClass/fields");        
            foreach ($fields as $field) {              
                foreach ($forms as $form) {
                    $form_path ="/modules/forms/formClasses/$formClass/fields/$field/meta/display/$form";
                    if (! ($formNode = I2CE::getConfig()->traverse($form_path,false,false)) instanceof I2CE_MagicDataNode
                        ){
                        continue;
                    }                
                    foreach ($formNode->getKeys() as $style) {
                        $style_path ="/modules/forms/formClasses/$formClass/fields/$field/meta/display/$form/$style";
                        if (! ($styleNode = I2CE::getConfig()->traverse($style_path,false,false)) instanceof I2CE_MagicDataNode
                            ) {
                            continue;
                        }
                        foreach ($keys as $key) {
                            $src_path ="/modules/forms/formClasses/$formClass/fields/$field/meta/display/$form/$style/$key";
                            $dest_path ="/modules/forms/formClasses/$formClass/fields/$field/meta/display/$style/$key/$form";
                            if (! ($sourceNode = I2CE::getConfig()->traverse($src_path,false,false)) instanceof I2CE_MagicDataNode
                                || ! ($destNode = I2CE::getConfig()->traverse($dest_path,true,false)) instanceof I2CE_MagicDataNode
                                ){
                                continue;
                            }
                            I2CE::raiseError("Moving $src_path\nto $dest_path");
                            $destNode->setValue($sourceNode);
                            $sourceNode->erase();
                        }
                        if (count($styleNode->getKeys())  == 0) {
                            $styleNode->erase();
                        }
                    }
                    if (count($formNode->getKeys()) == 0) {
                        $formNode->erase();
                    }
                }
            }
        }
        return true;
    }




    /**
     * Method called when the module is enabled for the first time.
     * @param boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        if (!I2CE_Util::runSQLScript('CREATE_formfield_mult_map_componentize.sql',null,false)) { //don;t use a transaction
            I2CE::raiseError("Could not install remapping SQL  function");
            return false;
        }        
        return true;
    }




    /**
     * Remaps the list values of the given list based on the field to lookup on
     * for the form 
     * @param string $list the list form.
     * @param array $evals an array when keys the new id you want the value to be.  the values
     * are the values we lookup on the form name.  If a list instance with the new id does not
     * exist it creates it.
     * @param array $forms.an array of  string keys are forms values are mapped fields.  Defulats to  false
     * in which case we do not try to update any form.
     * @param string $lookup_field  the name of the list field to lookup on. Defaults to 'name'
     * @param string $mapped_field.  the mapped field of $form.  The Defaults to null
     * in which case  then mapped field that is used is the $list
     * @returns boolean. true on success
     */
    public static  function remapFields($list,$evals, $forms = false, $lookup_field = 'name'  ) {
        $factory = I2CE_FormFactory::instance();
        $user = new I2CE_User( 1, false, false, false );
        $existing_ids = I2CE_FormFactory::instance()->getRecords($list);
        if (!is_array($existing_ids)) {
            I2CE::raiseError("Could not get existing ids");
            return false;
        }
        if (!is_array($forms)) {
            $forms = array();
        }
        foreach ($evals as $eval_id=>$eval) {
            if (!in_array($eval_id,$existing_ids)) {
                //create the new id.
                $listObj = $factory->createContainer("$list|$eval_id");
                if (!$listObj instanceof I2CE_Form) {
                    I2CE::raiseError("Could not instantiate new '$list|$eval_id'");
                    return false;
                }
                $listObj->$lookup_field = $eval;
                if (!$listObj->save($user)) {
                    I2CE::raiseError("Could not save new $list|$eval_id");
                    $listObj->cleanup();
                    return false;
                }
                $listObj->cleanup();
            }
            $eval_where = array(
                'operator' => 'FIELD_LIMIT',
                'field'=>$lookup_field, 
                'style'=>'equals',
                'data'=>array(
                    'value'=>$eval
                    ));
            $ids = I2CE_FormStorage::search($list, false,$eval_where);
            $ids = array_diff($ids, array($eval_id));
            if (count($ids) > 1) {
                I2CE::raiseError("Found too many $list's with $lookup_field $eval");
            } else  if (count($ids) == 0) {
                I2CE::raiseError("Could not find $list with $lookup_field $eval");
                continue;
            }            
            reset($ids);
            $id = current($ids);
            foreach ($forms as $form=>$mapped_field ) {
            $form_where = array(
                'operator' => 'FIELD_LIMIT',
                'field'=>$mapped_field,
                'style'=>'equals',
                'data'=>array(
                    'value'=>"$list|$id"
                    ));
                $form_ids = I2CE_FormStorage::search($form, false, $form_where);
                if (count($form_ids) > 0) {
                    I2CE::raiseError(
                        "Remapping $list with $lookup_field $eval  from id  $id to id $eval_id on $form ids:\n" 
                        . implode(',' , $form_ids));
                } else {
                    I2CE::raiseError("Remapping  list with $lookup_field $eval  from id  $id to id $eval_id.  No existing $list=$id for $form");
                }
                foreach ($form_ids as $form_id) {
                    $formObj = $factory->createContainer("$form|$form_id");
                    if (!$formObj instanceof I2CE_Form) {
                        I2CE::raiseError("Could not instantitate '$form|$form_id '") ;
                        continue;
                    }
                    $formObj->populate();
                    $evalField =  $formObj->getField($mapped_field);
                    if(!$evalField instanceof I2CE_FormField_MAP) {
                        I2CE::raiseError("Bad field $mapped_field");
                        $formObj->cleanup();
                        continue;
                    }
                    $evalField->setFromDB("$list|$eval_id");
                    if (!$formObj->save($user)) {
                        I2CE::raiseError("Could not save $form");
                        return false;
                    }
                    $formObj->cleanup();
                }
            }
        }
        return true;
    }


    /**
     * Deletes id's of a list with field matchin a given value.  Intended to be called after
     * rempaFields has been succesfully called.
     * @param string $list the list form.
     * @param array $evals an array when keys the new id you want the value to be.  the values
     * are the values we lookup on the form name
     * @param string $lookup_field  the name of the list field to lookup on. Defaults to 'name'
     * @returns voolean. true on success
     */
    public static function deleteMappedValues($list,$evals, $lookup_field = 'name') {
        $factory = I2CE_FormFactory::instance();
        foreach ($evals as $eval_id => $eval) {
            $eval_where = array(
                'operator' => 'FIELD_LIMIT',
                'field'=>$lookup_field,
                'style'=>'equals',
                'data'=>array(
                    'value'=>$eval
                    ));
            $ids = I2CE_FormStorage::search($list, false,$eval_where);
            $ids = array_diff($ids, array($eval_id));
            if (count($ids) > 1) {
                I2CE::raiseError("Found too many $list's with $lookup_field $eval");
            } else  if (count($ids) == 0) {
                I2CE::raiseError("Could not find $list with $lookup_field $eval");
                continue;
            }
            reset($ids);
            $id = current($ids);

            $formObj = $factory->createContainer($list . '|' . $id);
            if (!$formObj instanceof I2CE_Form) {
                I2CE::raiseError("Could not instantiate old $list|$id");
                return false;
            }            
            I2CE::raiseError("Deleting  $list|$id");
            if (!$formObj->delete()) {
                I2CE::raiseError("Could not delete $list|$id");
            }
            $formObj->cleanup();
            I2CE::raiseError("Deleted  $list|$id");
        }
        return true;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
