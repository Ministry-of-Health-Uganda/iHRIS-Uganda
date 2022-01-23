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
*  I2CE_SwissConfig_FormRelationships
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_FormRelationships extends I2CE_Swiss_FormRelationship_Base {

    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('formRelationship_' . $action . '.html','div',$contentNode);        
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not get form relationship view template");
            return false;
        }
        if (!$this->doRelationsLinks($action , $mainNode)) {
            return false;
        }
        if ($action === 'edit') {
            if (!$this->editValues($mainNode, $transient_options)) {
                return false;
            }
        }
        return true;
    }

    public function doRelationsLinks($action, $node) {
        $join_file = 'formRelationship_relationship_each.html';
        $append_node = $this->template->getElementById('relationships',$node);
        if (!$append_node instanceof DOMNode) {
            I2CE::raiseError("No node to append $join_file -- searched by id for relationships");
            return false;
        }
        $relations = $this->getChildNames();
        foreach ($relations as $relation) {
            $swissChild = $this->getChild($relation);
            if (!$swissChild instanceof I2CE_Swiss_FormRelationship) {
                continue;
            }
            $relNode = $this->template->appendFileByNode( $join_file,'li',$append_node);
            if (!$relNode instanceof DOMNode) {
                I2CE::raiseError("bad template $join_file -- could not append");
                return false;
            }
            $this->template->setDisplayDataImmediate("relation_name",$relation,$relNode);
            $this->template->setDisplayDataImmediate("relation_dispname",$swissChild->getDisplayName(),$relNode);            
            $this->template->setDisplayDataImmediate("relation_desc",$swissChild->getDescription(),$relNode);            

            $this->template->setDisplayDataImmediate("relation_delete_link",$this->getURLRoot('delete') . '/' . $relation,$relNode);
            $this->template->setDisplayDataImmediate("relation_edit_link",$this->getURLRoot('edit') . '/' . $relation,$relNode);
            $this->template->setDisplayDataImmediate("relation_view_link",$this->getURLRoot('view') . '/' . $relation,$relNode);
            $this->template->setDisplayDataImmediate("relation_export_link",$this->getURLRoot('export') . '/' . $relation,$relNode);
        }
        return true;
    }



    public function getChildType($child) {
        return 'FormRelationship';
    }


    /**
     * Display the configuration menu for the specified config node
     * @param DOMNode $contentNode null.  All the  swiss should display all content relative to that node
     * @returns boolean true on sucess
     */
    public function editValues($contentNode,$transient_options) {
        $formBaseConfig = I2CE::getConfig()->modules->forms->forms;
        $forms = $formBaseConfig->getKeys();

        $children = $this->getChildNames();
        if (count($children) > 0) {
            $selectNode = $this->template->getElementById("existing_relationship",$contentNode);
            if (!$selectNode instanceof DOMElement) {
                I2CE::raiseError("Don't know where to add existing names");
                return false;
            }
            $relationList = $this->template->getElementById("formRelationship_relationship_copy_menu_list",$contentNode);
            if (!$relationList instanceof DOMElement) {
                I2CE::raiseError("Don't know where to add existing");
                return false;
            }       
            $this->template->addUpdateSelect('existing_relationship',array('show_class'=>'selector_visible','hide_class'=>'selector_hidden'));
            foreach ($children as $relation) {
                $swissChild = $this->getChild($relation);
                if (!$swissChild instanceof I2CE_Swiss_FormRelationship) {
                    continue;
                }                
                $selectNode->appendChild($this->template->createElement('option',array('value'=>$relation) , $swissChild->getDisplayName()));
                $relNode = $this->template->appendFileByNode( "formRelationship_menu_relation_copy.html", "div", $relationList);
                $relNode->setAttribute('id','select_update:existing_relationship' .':'. $relation);
                $this->template->setDisplayDataImmediate("relation_name",$relation,$relNode);
                $this->template->setDisplayDataImmediate("relation_dispname",$swissChild->getDisplayName(),$relNode);            
                $this->template->setDisplayDataImmediate("relation_desc",$swissChild->getDescription(),$relNode);                            
            }
        }
        //display the available forms to choose to construct a new  relationship
        $factory = I2CE_FormFactory::instance();
        $forms = $factory->getNames();
        natsort($forms);
        $formList = $this->template->getElementById("formRelationship_relationship_new_menu_list",$contentNode);
        if (!$formList instanceof DOMElement) {
            I2CE::raiseError("Don't know where to add forms");
            return false;
        }       
        $selectNode = $this->template->getElementById("form_name",$contentNode);
        if (!$selectNode instanceof DOMElement) {
            I2CE::raiseError("Don't know where to add form names");
            return false;
        }
        $this->template->addUpdateSelect('form_name',array('show_class'=>'selector_visible','hide_class'=>'selector_hidden'));
        foreach ($forms as $form) {   
            $formObj = $factory->createContainer($form);
            if (!$formObj instanceof I2CE_Form) {
                continue;
            }
            $formNode = $this->template->appendFileByNode( "formRelationship_menu_form.html", "div", $formList);
            if (!$formNode instanceof DOMElement) {
                I2CE::raiseError("Could not add new form to relationships menu");
                return false; //we return instead of  continue b/c there is no sense in trying multiple times when we know its going to fail
            }
            $selectNode->appendChild($this->template->createElement('option',array('value'=>$form),$form));
            $formNode->setAttribute('id','select_update:form_name' .':'. $form);
            $formConfig = $formBaseConfig->$form;
            $formDispName = '';
            $formDesc ='';
            $formConfig->setIfIsSet($formDispName,'display');
            $formConfig->setIfIsSet($formDesc,"meta/description");
            $this->template->setDisplayDataImmediate("form_name",$form,$formNode);
            $this->template->setDisplayDataImmediate("form_dispname",$formDispName,$formNode);
            $this->template->setDisplayDataImmediate("form_desc",$formDesc,$formNode);
            $formObj->cleanup();
        }
        $this->renameInputs(array('display_name','description','relationship','existing_relationship','form_name'),$contentNode);
        $relationNode = $this->template->getElementById('relationship',$contentNode);
        if (!$relationNode instanceof DOMElement) {
            I2CE::raiseError("Could not find node with id 'relation' in  relationships menu template");
            return false;
        }
        $this->template->setClassValue($relationNode,'validate_data',array('notinlist'=>$this->getChildNames()), '%');
        return true;
    }
    



    public function processValues($vals) {
        //the only editing we allow here is the creation of new relationships
        $fields = array('relationship' => 'Relationship Name','display_name'=>'Display Name');
        foreach ($fields as $field=>$desc) {
            if ((!array_key_exists($field,$vals)) || (!$vals[$field])) {
                $this->page->userMessage("The field $desc was not specified",'notice' );
                return false;
            }
        }
        $relationship = trim($vals['relationship']);
        if (!I2CE_MagicDataNode::checkKey($relationship)) {
            $this->page->userMessage("The relationship name $relationship is invalid",'notice');
            return false;
        }
        if (isset($this->storage->$relationship)) {
            $this->page->userMessage("The relationship name $relationship is already in use",'notice');
            $this->page->userMessage("The relationship name $relationship is already in use -true",'notice');
            return false;
        }
        $child = null;
        if (array_key_exists('existing_relationship',$vals) && is_string($vals['existing_relationship']) && strlen($vals['existing_relationship'])>0) {
            $copy = $this->getChild($vals['existing_relationship']);
            if ($copy instanceof I2CE_Swiss_FormRelationship) {
                $this->storage->$relationship = $copy->getStorage();
            } else {
                $this->page->userMessage("Could not copy existing relationship",'notice');
                return false;
            }
            $child = $this->getChild($relationship);
            $check_form = false;
        } else {
            $child = $this->getChild($relationship,true);
            $check_form = true;
        }
        if (!$child instanceof I2CE_Swiss_FormRelationship) {
            I2CE::raiseError("Could not get child");
            return false;
        }
        if ($check_form) {
            if (!array_key_exists('form_name',$vals) || !is_scalar($vals['form_name']) || !$vals['form_name']) {
                $this->page->userMessage("You either need to select an existing relationship to copy or select a primary form",'notice');
                return false;
            }
            $factory = I2CE_FormFactory::instance();
            if (!$factory->exists($vals['form_name'])) {
                $this->page->userMessage("The requested form $primary does not exist",'notice');
                return false;
            }
            $child->setForm($vals['form_name']);
        }        
        $child->setDisplayName($vals['display_name']);
        if (array_key_exists('description',$vals) ) {
            $child->setDescription($vals['description']);
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
