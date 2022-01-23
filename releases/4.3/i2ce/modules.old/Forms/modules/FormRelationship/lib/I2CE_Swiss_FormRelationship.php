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
*  I2CE_SwissConfig_FormRelationship
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_FormRelationship extends I2CE_Swiss_FormRelationship_Base {

    public function processValues($vals) {
        $success = true;
        if (array_key_exists('form',$vals)  && $vals['form']) {
            $factory = I2CE_FormFactory::instance();
            if (!$factory->exists($vals['form'])) {
                $this->page->userMessage("The requested form {$vals['form']} does not exist",'notice',false);
                $success = false;
            } else {
                $success &= $this->setForm($vals['form']);
            }
        } 
        if (array_key_exists('description',$vals)) {
            $success &= $this->setDescription($vals['description']);
        }
        if (array_key_exists('display_name',$vals)) {
            $success &= $this->setDisplayName($vals['display_name']);
        }
        return $success;
    }

    public function getDisplayName() {
        $disp = $this->getField('display_name');
        if ($disp) {
            return $disp;
        }
        return parent::getDisplayName();;
    }

    public function getDescription() {
        $disp = $this->getField('description');
        if ($disp) {
            return $disp;
        }
        return parent::getDescription();
    }

    public function setDescription($desc) {
        return $this->setTranslatableField('description',$desc);
    }

    public function setDisplayName($displayName) {
        return $this->setTranslatableField('display_name',$displayName);
    }


    public function getForm() {
        return $this->getField('form');
    }

    public function setForm($form) {
        return $this->setField('form',$form);
    }



    /**
     * Gets the name of all child forms to the specified depth
     * @var int $depth.  Defaults to 1 in which case we only get the immediate children
     * @returns array
     */
    public function getChildFormNames($depth = 1) {
        if (!is_int($depth ) || $depth <= 0) {
            return array();
        }        
        $joins  = $this->getChild('joins');
        $childNames = array();
        if ($joins instanceof I2CE_Swiss_FormRelationship_Joins) {
            $depth--;
            foreach ($joins as $swissJoin) {
                if (!$swissJoin instanceof I2CE_Swiss_FormRelationship) {
                    continue;
                }
                $childNames[] = $swissJoin->getName();
                $childNames = array_unique(array_merge($childNames,$swissJoin->getChildFormNames($depth)));
            }
        }
        return $childNames;
    }


    /**
     * Gets the ancesestor forms
     * @returns array of string, the named ancestral forms
     */
    public function getAncestorFormNames( $depth = 0) {
        $ancForms = array();
        $parent = $this->getParent()->getParent();
        do {
            $ancForms[] = $parent->getName();
            $ancForms =  array_merge($ancForms , $parent->getChildFormNames($depth));
            $parent = $parent->getParent()->getParent();
            $depth++;
        }while ($parent instanceof I2CE_Swiss_FormRelationship) ; //will stop when we get the I2CE_Swiss_FormRelationship.. the main node.
        return array_unique($ancForms);        
    }


    public function getChildType($child) {
        switch ($child) {
        case 'reporting_functions':
            return 'FormRelationship_ReportingFunctions';
        case 'joins':
            return 'FormRelationship_Joins';
        case 'where':
            return 'FormRelationship_Where';
        default:
            return parent::getChildType($child);
        }
    }

    protected function showDropEmpty($action,$contentNode) {
        return true;
    }
    
    protected function showDisplayStyle($action,$contentNode) {
        return true;
    }



    public function displayValues($contentNode,$transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('formRelationship_relationship.html','div',$contentNode);
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not load main template");
            return false;
        }       
        $this->displayMetaData($action, $mainNode);
        $dropNode = $this->template->getElementById('drop_empty_contents',$mainNode);
        if ($dropNode instanceof DOMNode) {
            $this->showDropEmpty($action,$dropNode);
        }
        $dsNode = $this->template->getElementById('displaystyle_contents',$mainNode);
        if ($dsNode instanceof DOMNode) {
            $this->showDisplayStyle($action,$dsNode);
        }
        //display the swiss config for the joins and where
        $actionMethod = $action . 'Values';
        $this->manageFunctions($action, $mainNode, $transient_options);
        $this->displayAncestors($mainNode,$transient_options,$action);
        foreach (array('where'=>'relationship_where','joins'=>'relationship_joins') as $sub=>$id) {
            $swissChild = $this->getChild($sub,true);
            if (!$swissChild instanceof I2CE_Swiss) {
                continue;
            }
            $node = $this->template->getElementById($id,$mainNode); 
            if (!$node instanceof DOMNode) {
                continue;
            }
            if ($sub == 'joins') {
                $swissChild->addAjaxLink($id .'_link',$id .'_container:' . $this->path . '/joins', $id  . '_ajax' ,$node,$action, $transient_options);
            } else { 
                $swissChild->addAjaxLink($id .'_link',$id .'_container', $id  . '_ajax' ,$node,$action, $transient_options);
            }
        }
        return true;
    }

    protected function displayAncestors($contentNode,$transient_options,$action) {
        $node = $this->template->getElementById('relationship_ancestors');
        if ($node instanceof DOMNode) {
            $this->template->removeNode($node);
        }
        return true;
    }


    protected function manageFunctions($action, $mainNode, $transient_options) {
        try {
            $swissChild = $this->getChild('reporting_functions',true);
        } catch (Exception $e) {
            return;
        }
        $node = $this->template->getElementById('reporting_functions',$mainNode); 
        if (!$node instanceof DOMNode) {
            return;
        }
        $swissChild->addAjaxLink('reporting_functions_link','reporting_functions_container',  'reporting_functions_ajax' ,$node,$action, $transient_options);
    }

    public function hasFunctions() {
        try {
            $swissChild = $this->getChild('reporting_functions',true);
        } catch (Exception $e) {
            return false;
        }
        return $swissChild->hasFunctions();
    }

    public function getSwissFunctions() {
        try {
            $swissChild = $this->getChild('reporting_functions',true);
        } catch (Exception $e) {
            return array();
        }
        return $swissChild;
    }

    public function getSwissFunctionDependency($function) {
        return $this->getSwissFunctionWalker($this->getSwissFunctions(),$function,array());
    }

    protected function getSwissFunctionWalker($swissFunctions,$function,$dependents) {
        if (!$swissFunctions instanceof I2CE_Swiss_FormRelationship_ReportingFunctions) {
            return false;
        }
        if (($swissFunction = $swissFunctions->getChild($function)) instanceof I2CE_Swiss_SQLFunction) {
            $dependents[$function] = $swissFunction;
            return $dependents;
        }
        foreach ($swissFunctions as $func=>$swissFunction) {
            $t_dependents = $dependents;
            $t_dependents[$func] = $swissFunction;
            $ret = $this->getSwissFunctionWalker($swissFunction->getChild('reporting_functions'),$function,$t_dependents);
            if ($ret !== false) {
                return $ret;
            }
        }
        return false;
    }


    /**
     * Displays the meta data about this form
     * @param mixed$configPath
     * @param mixed $configPath
     * @param DOMNode $appendNode The page template node from which we wish to add the join menu to
     * @returns boolean true  on success
     */
    protected function displayMetaData( $action,$node ) {
        $file =  'formRelationship_meta_' . $action . '.html';
        $appendNode = $this->template->getElementById('relationship_meta',$node); 
        if (!$appendNode instanceof DOMNode) {
            I2CE::raiseError("Could not find node to attach meta data to");
            return false;
        }
        $metaNode = $this->template->appendFileByNode($file,'div',$appendNode);        
        if (!$metaNode instanceof DOMNode) {
            I2CE::raiseError("Could not load template for meta data");
            return false;
        }
        //set the metadata
        $name = $this->storage->getName();
        $form = $this->getForm();
        $this->template->setDisplayData('form_name',$name,$metaNode);
        $this->template->setDisplayData('real_form',$form,$metaNode);
        $this->template->setDisplayDataImmediate('display_name',$this->getDisplayName(),$metaNode);
        $this->template->setDisplayDataImmediate('description',$this->getDescription(),$metaNode);
        $this->renameInputs(array('display_name','description'),$metaNode);
        return true;
    }
    





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
