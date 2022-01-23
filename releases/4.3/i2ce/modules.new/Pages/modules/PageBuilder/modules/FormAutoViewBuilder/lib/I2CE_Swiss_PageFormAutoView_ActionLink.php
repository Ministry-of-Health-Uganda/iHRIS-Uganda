<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
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
* @subpackage page
* @author Michael Cote <michaelpcote@gmail.com>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_PageFormAutoView_ActionLinks_Args
* 
* @access public
*/


/**
 *I thought that I might be able to add a getArgsHandler to ActionLinks and then use this class to 
 *print out the data from auto_tempate[edit]
 */
class I2CE_Swiss_PageFormAutoView_ActionLink extends I2CE_Swiss {
	
	public function processValues($vals) {
		// Set the value for the action link's form field 
        if (array_key_exists('formfield', $vals)) {
			$this->setTranslatableField('formfield', $vals['formfield']);	   
		}
		// Set the value for the action link's link location
		if (array_key_exists('linkloc', $vals)) {
			$this->setTranslatableField('href', $vals['linkloc']);   
		}
		// Set the value for the action link's link text
		if (array_key_exists('linktext', $vals)) {
			$this->setTranslatableField('text', $vals['linktext']);   
		}
		
		if (array_key_exists('task', $vals)) {
			//IC2E::raiseError("Trying to process task");
			$this->setField('task', $vals['task']);   
		}
		
		return true;
    }
	
    protected function getTemplate() {
		return 'swiss_pageformautoview_actionlink_args.html';
	}
	
	public function displayValues($content_node, $transient_options, $action) {
		// Append the html template and work with it as a DOMNode object
		if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(), 'div', $content_node)) instanceof DOMNode) {
            return false;
        }
        
        // Display the action link's fields/arguments
        return $this->displayArgs($mainNode, $transient_options, $action);
    }
	
	public function displayArgs($mainNode, $transient_options, $action) {
		// Display the action link's form field 
		$this->template->setDisplayDataImmediate('formfield', $this->getField('formfield'), $mainNode);
		// Display the action link's link location
		$this->template->setDisplayDataImmediate('linkloc', $this->getField('href'), $mainNode);
		// Display the action link's link text field
		$this->template->setDisplayDataImmediate('linktext', $this->getField('text'), $mainNode);
		// Rename the inputs to include swiss instance path
		$inputs = array('linkloc', 'formfield', 'linktext');
		if ( ($taskNode = $this->template->getElementByName('task',0,$mainNode)) instanceof DOMNode) {
            $tasks = I2CE::getConfig()->getAsArray("/I2CE/tasks/task_description");
            $selected_task = $this->getField('task');
            foreach ($tasks as $task=>$desc) {
                $attrs = array('value'=>$task,'title'=>$desc);
                if ($task == $selected_task) {
                    $attrs['selected'] = 'selected';
                } 
                $taskNode->appendChild($this->template->createElement('option',$attrs,$task));
            }
            $inputs[] = 'task';
        }
		$this->renameInputs($inputs,$mainNode);
		return true;
    }
}
# Local Variables:
# mode: php
# c-default-task: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
