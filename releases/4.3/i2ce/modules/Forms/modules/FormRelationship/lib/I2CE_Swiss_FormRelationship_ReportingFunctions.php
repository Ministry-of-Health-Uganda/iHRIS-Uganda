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
*  I2CE_SwissConfig_FormRelationship_ReportingFunctions
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_FormRelationship_ReportingFunctions  extends I2CE_Swiss_FormRelationship_Base {


    
    public function displayValues($contentNode, $transient_options,$action) {
        $mainNode = $this->template->appendFileByNode('formRelationship_reporting_functions_' . $action .'.html','div',$contentNode);
        $existingFunctions = $this->getChildNames();
        if ($action === 'edit') {
            if (count($existingFunctions) > 0) {
                $functionNode = $this->template->getElementById('function_name',$contentNode);
                if (!$functionNode instanceof DOMNode) {
                    I2CE::raiseError("Function name node could not be found");
                    return false;
                }
                $this->template->setClassValue($functionNode,'validate_data',array('notinlist'=>$existingFunctions), '%');
            }
            $formfields = I2CE::getConfig()->getAsArray('/modules/forms/FORMFIELD');
            $this->template->setDisplayDataImmediate('formfield',$formfields,$mainNode);            
            if (!$this->addAjaxOptionMenu('new_function', 'functions_container', $contentNode)) {
                return false;
            }
        }
        if (count($existingFunctions) == 0) {
            $this->template->setDisplayDataImmediate('has_existing_functions','',$mainNode);
            return true;
        } 
        $this->template->setDisplayDataImmediate('has_existing_functions',1,$mainNode);
        $addNode = $this->template->getElementById('existing_functions_list',$mainNode);
        if (!$addNode instanceof DOMNode) {
            I2CE::raiseError("Don't know where to add existing report functions");
            return false;
        }
        $success = true;
        foreach ($existingFunctions as $func) {
            $swissChild = $this->getChild($func);
            if (!$swissChild instanceof I2CE_Swiss) {
                continue;
            }
            $liNode = $this->template->appendFileById('formRelationship_existing_function_' . $action . '.html','li',$addNode);
            if (!$liNode instanceof DOMNode) {
                I2CE::raiseError("Cannot add existing function  template");
            }
            $delete_link = $swissChild->getURLRoot('delete')  .  $swissChild->path .$swissChild->getURLQueryString();
            $this->template->setDisplayDataImmediate('function_delete_link',$delete_link,$liNode);
            $swissChild->addAjaxLink('function_link','sql_function',  'function_ajax' ,$liNode,$action, $transient_options);            
        }
        return $success;
    }


    public function getFunctionDetails($function) {
        $swissFunction = $this->getChild($funciton);
        if (!$swissFunction instanceof I2CE_Swiss_SQLFunction) {
            return false;
        }
        return $swissFunction->getFunctionDetails();
    }



    public function getChildType($child) {
        return 'SQLFunction';
    }


    /**
     * Update config for given values -- creates a new reportung fynction
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */    
    public function processValues($vals) {
        if (!array_key_exists('name', $vals) || !$vals['name']) {
            $this->userMessage("No function name");
            I2CE::raiseError("No function name");
            //no name provided.
            return false;
        }
        if (!I2CE_MagicDataNode::checkKey($vals['name']) || strpos($vals['name'],'+') !== false ) {
            $this->userMessage("An invalid function name has been provided.  It must consist only of letters, numbers, _, and -");
            return false;
        }
        $existingFunctions = $this->getChildNames();
        if (in_array($vals['name'], $existingFunctions)) {
            $this->userMessage("Name {$vals['name']} is already being used");
            I2CE::raiseError("Name {$vals['name']} is already being used");
            return false;

        }
        $swissFunction = $this->getChild($vals['name'],true);
        if (!$swissFunction instanceof I2CE_Swiss_SQLFunction) {
            I2CE::raiseError("SQL Function Badness");
            return false;
        }
        return $swissFunction->processValues($vals);
    }
        

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
