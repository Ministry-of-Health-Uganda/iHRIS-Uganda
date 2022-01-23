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
* @package i2ce
* @subpackage formrelationships
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.6
* @since v4.0.6
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_FormRelationship_AncestralConditions
* 
* @access public
*/


class I2CE_Swiss_FormRelationship_AncestralConditions extends I2CE_Swiss_FormRelationship_Base{

    public function getChildType($child) {
        return 'FormRelationship_AncestralCondition';
    } 


    public function processValues($vals) {
        if (!array_key_exists('action',$vals) || $vals['action'] != 'Add') {
            return true; //nothing to do
        }
        $keys = $this->getChildNames();
        if (count($keys) == 0) {
            $key = '0';
        } else {
            $key = max($keys) + 1;
        }        
        $this->storage->$key = array(); //touch the new key so it exists  as a parent
        return true;
    }
    


    public function displayValues($content_node,$transient_options, $action) {
        if ($action == 'view') {
            return true;
        }
        $mainNode = $this->template->appendFileByNode('formRelationship_conditions_container.html','div',$content_node);        
        if (!$mainNode instanceof DOMNode) {
            return false;
        }
        $this->renameInputs(array('action'),$mainNode);
        $container_id = 'relationship_conditions_container';
        $this->template->reIdNodes($container_id, $container_id . ':' . $this->path, $mainNode);
        //show the existing conditions
        if (!$this->displayExistingConditions($action,$this->getChildNames(),$mainNode, $transient_options)) {
            I2CE::raiseError("Could not display existing conditions");
            return false;
        }
        return true;
    }

    /**
     * Displays the existing joined forms
     * @param mixed $configPath
     * @param DOMNode $contentNode
     * @returns boolean true  on success
     */
    protected function displayExistingConditions( $action,$conditions,$contentNode, $transient_options) {
        if (count($conditions) == 0) {
            return true;
        }
        $existing_id = 'relationship_existing_conditions';
        $appendNode = $this->template->getElementById($existing_id,$contentNode);        
        if (!$appendNode instanceof DOMNode) {
            return false;
        }
        $conditionsNode = $this->template->appendFileByNode('formRelationship_existing_conditions.html','div',$appendNode);
        if (!$conditionsNode instanceof DOMNode) {
            return false;
        }
        $existingNode = $this->template->getElementById('existing_conditions',$conditionsNode);
        if (!$existingNode instanceof DOMNode) {
            return false;
        }
        foreach ($conditions as $condition) {
            $swissChild = $this->getChild($condition);
            if (!$swissChild instanceof I2CE_Swiss) {
                continue;
            }
            $conditionNode = $this->template->appendFileByNode('formRelationship_existing_condition.html','li',$existingNode);            
            $delete_link = $swissChild->getURLRoot('delete')  .  $swissChild->path .$swissChild->getURLQueryString();
            $this->template->setDisplayDataImmediate("existing_condition_delete_link", $delete_link,$conditionNode);
            //$this->template->setDisplayDataImmediate("description", $swissChild->getDescription(),$conditionNode);
            $swissChild->addAjaxLink('existing_condition_link','condition_contents','existing_condition_ajax',$conditionNode,$action, $transient_options);            

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
