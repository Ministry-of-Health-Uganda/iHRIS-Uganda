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
*  I2CE_SwissConfig_FormRelationship_Joins
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_FormRelationship_Joins extends I2CE_Swiss_FormRelationship_Base {


    public function getChildType($child) {
        return 'FormRelationship_Join';
    }


    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        if (!array_key_exists('form_which',$vals)) {
            return true;
        }
        if (!array_key_exists('form_name',$vals)) {
            return true;
        }
        $name = trim($vals['form_name']);
        if (!$name) {
            $this->userMessage("Name is empty");
            I2CE::raiseError("Name is empty");
            return false;
        }
        $usedNames = $this->getExistingFormNames();
        if (in_array($name,$usedNames)) {
            $this->userMessage("Name $name is already being used");
            I2CE::raiseError("Name $name is already being used");
            return false;
        }
        if (!array_key_exists('description',$vals)) {
            $vals['description'] = '';
        }
        if ((!array_key_exists('display_name',$vals)) || (strlen($vals['display_name']) == 0)) {
            $vals['display_name'] = $name;
        }
        $regexp = '/([a-zA-Z0-9\_\-\+\.]+)\(([a-zA-Z0-9\_\-\+\.]+):?([a-zA-Z0-9\_\-\+\.]*)\)/';
        if (!preg_match($regexp,$vals['form_which'],$matches)) {
            return false;
        }
        list($all,$childForm,$style,$field) = $matches;
        // parent_field options are reversed so that they all show up in the
        // drop down.  This puts it back so the following works right.
        $new_vals = array(
            'display_name'=>$vals['display_name'],
            'description'=>$vals['description'],
            'form'=>$childForm,
            'join_style'=>$style,
            'join_field'=>$field
            );
        $joined = $this->getChild($name,true);
        if (!$joined instanceof I2CE_Swiss_FormRelationship) {
            I2CE::raiseError("Badness on joined $name");
            return false;
        }
        return $joined->processValues($new_vals);
    }




    public function displayValues($content_node,$transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('formRelationship_join_container.html','div',$content_node);        
        if (!$mainNode instanceof DOMNode) {
            return false;
        }
        $relatedForms = $this->getRelatedForms();
        $t = 0;
        foreach (array('child','parent','parent_field','child_field','reference') as $key) {
            $t += count($relatedForms[$key]);
        }
        $joins = $this->getChildNames();
        $this->template->setDisplayDataImmediate('has_join_info',!($t == 0 && count($joins) == 0), $mainNode);
        //now do the join menu
        if ($t > 0) {
            if (!$this->displayJoinMenu( $action,$relatedForms,$mainNode)) {
                I2CE::raiseError("Could not display join menu");
                return false;
            }
        }
        //show the existing joined forms
        if (count($joins) >  0) {
            if (!$this->displayExistingJoins($action,$joins,$mainNode, $transient_options)) {
                I2CE::raiseError("Could not display existing joins");
                return false;
            }
        }
        return true;
    }



    /**
     * Displays the existing joined forms
     * @param mixed $configPath
     * @param DOMNode $contentNode
     * @returns boolean true  on success
     */
    protected function displayExistingJoins( $action,$joins,$contentNode, $transient_options) {
        $appendNode = $this->template->getElementById('relationship_existing_joins',$contentNode);
        if (!$appendNode instanceof DOMNode) {
            return false;
        }
        $joinsNode = $this->template->appendFileByNode('formRelationship_existing_joins.html','div',$appendNode);
        if (!$joinsNode instanceof DOMNode) {
            return false;
        }
        $existingNode = $this->template->getElementById('existing_joined_forms',$joinsNode);
        if (!$existingNode instanceof DOMNode) {
            return false;
        }
        foreach ($joins as $join) {
            $swissChild = $this->getChild($join);
            if (!$swissChild instanceof I2CE_Swiss) {
                continue;
            }
            $joinNode = $this->template->appendFileByNode('formRelationship_existing_join.html','li',$existingNode);            
            $delete_link = $swissChild->getURLRoot('delete')  .  $swissChild->path .$swissChild->getURLQueryString();
            $this->template->setDisplayDataImmediate("join_name", $swissChild->getStorage()->getName(),$joinNode);
            $this->template->setDisplayDataImmediate("existing_join_delete_link", $delete_link,$joinNode);
            $swissChild->addAjaxLink('existing_join_link','relationship_contents','existing_join_ajax',$joinNode,$action, $transient_options);            
        }
        return true;
    }


    /**
     * Add in the UI to select the joinable forms for the specified form
     * @param DOMNode $contentNode 
     * @returns boolean true  on success
     */
    protected function displayJoinMenu( $action,$relatedForms, $contentNode) {
        $appendNode = $this->template->getElementById('relationship_new_joins',$contentNode);
        if (!$appendNode instanceof DOMNode) {
            return false;
        }
        $joinNode = $this->template->appendFileByNode('formRelationship_join.html','div',$appendNode);
        if (!$joinNode instanceof DOMNode) {
            return false;
        }
        $selectNode = $this->template->getElementById('join_forms_selector',$joinNode);
        if (!$selectNode instanceof DOMNode) {
            return false;
        }
        $desc = array('parent_field'=>'linked to by the field','child_field'=>'linked by the field','parent'=>'parent','child'=>'child','reference'=>'linked by the reference field');
        foreach ($relatedForms as $key=>$joinableForms) {
            foreach ($joinableForms as $joinableForm=>$data) {
                if (is_array($data)) { //either linking to or linking from this field
                    if ($key == 'child_field') {
                        foreach ($data as $map_field) {
                            $this->template->appendElementByNode(
                                $selectNode, 'option',
                                array('value'=>$joinableForm . '(' . $key . ':' .  $map_field .')'),
                                $joinableForm .  ' (' . $desc[$key] . ' ' . $map_field . ')');                    
                        }                        
                    } else if ($key == 'parent_field') {
                        foreach ($data as $map_form) {
                            $this->template->appendElementByNode(
                                $selectNode, 'option',
                                array('value'=>$map_form . '(' . $key . ':' .  $joinableForm .')'),
                                $map_form .  ' (' . $desc[$key] . ' ' . $joinableForm . ')');                    
                        }
                    } else if ($key =='reference') {
                        foreach ($data as $map_form) {
                            $this->template->appendElementByNode(
                                $selectNode, 'option',
                                array('value'=>$map_form . '(' . $key . ':' .  $joinableForm .')'),
                                $map_form .  ' (' . $desc[$key] . ' ' . $joinableForm . ')');                    
                        }
                    }
                } else {
                    //either a parent or child
                    $this->template->appendElementByNode(
                        $selectNode, 'option',
                        array('value'=>$joinableForm . '(' . $key . ')'),
                        $joinableForm . '(' . $desc[$key] . ')');
                }
            }
            
        }
        $joinName = $this->template->getElementById('form_name',$joinNode);
        if (!$joinName instanceof DOMNode) {
            return false;
        }
        $usedNames = $this->getExistingFormNames();
        $this->template->setClassValue($joinName,'validate_data',array('notinlist'=>$usedNames), '%');
        return $this->addAjaxOptionMenu('join_form','relationship_joins_container', $contentNode);
    }





    /**
     * Gets the data on forms related to the specified form
     * @returns array with four keys: 'child','parent', 'parent_field','child_field' each of which has a value an array.
     * the first two have arrays with keys forms and value 'true'.  The later two have arrays with keys forms
     * and value an array with keys 'field' and 'formClass'
     *
     */
    public function getRelatedForms() {
        $ret = array('child'=>array(),'parent'=>array(),'parent_field'=>array(),'child_field'=>array(),'reference'=>array());
        $parent = $this->getAncestorByClass('I2CE_Swiss_FormRelationship');
        if (!$parent instanceof I2CE_Swiss_FormRelationship) {
            I2CE::raiseError("Could not find parent form");
            return $ret;
        }
        $form = $parent->getForm();
        if (!$form) {
            I2CE::raiseError("No form name found");
            return $ret;
        }        
        if (!$form) {
            I2CE::raiseError("Not a form");
            return $ret;
        }
        $factory = I2CE_FormFactory::instance();
        $formObj = $factory->createContainer($form);
        if (!$formObj instanceof I2CE_Form) {
            I2CE::raiseError("Could no instantiate $form");
            return $ret;
        }
        $formBaseConfig = I2CE::getConfig()->traverse("/modules/forms/forms/",false,false);
        if (!$formBaseConfig instanceof I2CE_MagicDataNode) {
            return $ret;
        }
        $formClassBaseConfig = I2CE::getConfig()->traverse("/modules/forms/formClasses/",false,false);
        if (!$formClassBaseConfig instanceof I2CE_MagicDataNode) {
            return $ret;
        }
        $formConfig = $formBaseConfig->traverse($form,false,false);
        if (!$formConfig instanceof I2CE_MagicDataNode) {
            return $ret;
        }
        //get the child forms of form
        $child_forms = array();
        $formConfig->setIfIsSet($child_forms,"meta/child_forms",true);
        foreach ($child_forms as $child) {
            $ret['child'][$child] = true;
        }
        //get any forms that this form links to
        $formfieldConfig = I2CE::getConfig()->traverse('/modules/forms/FORMFIELD',false,false);
        if (!$formfieldConfig instanceof I2CE_MagicDataNode) {
            return $ret;
        }
        $fieldNames = $factory->getFieldNames($form,array('in_db'=>true));
        foreach ($fieldNames as $fieldName) {
            $fieldObj = $formObj->getField($fieldName);
            if (!$fieldObj instanceof I2CE_FormField) {
                I2CE::raiseError("Bad field $fieldName in $form");
                continue;
            }
            if ($fieldObj instanceof I2CE_FormField_REFERENCE) {                
                $ret['reference'][$fieldName] = $fieldObj->getSelectableForms();
            } else if ($fieldObj instanceof I2CE_FormField_MAP) {
                if ($fieldObj->canSelectAnyForm()) {
                    continue; //too messy for now.
                }
                //The field $fieldName of this form $form maps to $mapForms
                $ret['parent_field'][$fieldName] = $fieldObj->getSelectableForms();
            } else {
                continue;
            }
        }
        $formObj->cleanup();
        //now do the same thing for forms that reference $form
        $forms = $formBaseConfig->getKeys(); //get a list of all forms
        $forms = array_diff($forms,array($form)); //remove the current form from this list
        foreach ($forms as $f) {  
            $formObj = $factory->createContainer($f);
            if (!$formObj instanceof I2CE_Form) {
                continue;
            }
            //get any forms which are a parent of $form.
            $child_forms = array();
            $formBaseConfig->setIfIsSet($child_forms, './' . $f .'/meta/child_forms'  ,true);
            if (in_array($form,$child_forms)) {
                $ret['parent'][$f] = true;
            }
            //get any forms with a field that links to this form
            $fieldNames = $factory->getFieldNames($f,array('in_db'=>true));
            $data = array();
            foreach ($fieldNames as $fieldName) {
                $fieldObj = $formObj->getField($fieldName);
                if (!$fieldObj instanceof I2CE_FormField_MAP) {
                    continue;
                }
                $mapForms = $fieldObj->getSelectableForms();
                if (!in_array($form,$mapForms)) {
                    continue;
                }
                $data[] = $fieldName;
            }
            if (count($data) == 0) {
                continue;
            }
            $ret['child_field'][$f] = $data; //conatins an array of each of the fields of form $f which link to $form            
        }
        return $ret;        
    }





  }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
