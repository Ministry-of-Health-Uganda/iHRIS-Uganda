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
* Class I2CE_Swiss_PageFormAutoView_ChildForm
* 
* @access public
*/

class I2CE_Swiss_PageFormAutoView_ChildForm extends I2CE_Swiss {

    public function processValues($vals) {
    	// Check that the title exists
		/*
        $title = trim($vals['title']);
        if (!$title) {
            $this->userMessage("Nonexistent title");
            I2CE::raiseError("Nonexistent title");
            return false;
        } */
        // Set the title for the child form
        if (array_key_exists('title', $vals)) {
			$title = trim($vals['title']);
			$this->setTranslatableField('title', $title);
        }
		if (array_key_exists('link', $vals)) {
			$this->setField('link', $vals['link']);
        }
		if (array_key_exists('link_filter', $vals)) {
			$this->setField('link_filter', $vals['link_filter']);
        }
		if (array_key_exists('task', $vals)) {
			$this->setField('task', $vals['task']);   
		}
		return true;
    }
	
	protected function getTemplate() {
		return 'swiss_pageformautoview_childform_args.html';
	}
	
	protected function getChildType($child) {
		// If the child type is printf_args, return a Printf object
		if ($child == 'printf_args') {
			return 'PageFormAutoView_ChildForm_Printf';
		}
		// If the child type is action_links then return an ActionLinks object
		if ($child == 'action_links') {
			return 'PageFormAutoView_ActionLinks';
		}
        return parent::getChildType($child);
    }
	
	public function displayValues($content_node, $transient_options, $action) {
		// Append the html template and save it as a DOMNode object
        if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(), 'div', $content_node)) instanceof DOMNode) {
            return false;
        }
        
        // Add an Ajax link to display the child form's printf arguments
		if (($printfChild = $this->getChild('printf_args', true)) instanceof I2CE_Swiss_PageFormAutoView_ChildForm_Printf
	     && ($printfNode = $this->template->getElementById('printf_args', $mainNode)) instanceof DOMNode
	    ) {
			$printfChild->addAjaxLink('printf_args_link', 'printf_container', 'printf_args_ajax', $printfNode, $action, $transient_options);
        } 
        // Add an Ajax link to display the child's action links
		if (($actionLinkChild = $this->getChild('action_links', true)) instanceof I2CE_Swiss_PageFormAutoView_ActionLinks
	     && ($actionLinkNode = $this->template->getElementById('action_links', $mainNode)) instanceof DOMNode
	    ) {
			$actionLinkChild->addAjaxLink('action_links_link', 'action_links_container', 'action_links_ajax', $actionLinkNode, $action, $transient_options);
        }
        
        // Display the child form's arguments
		return $this->displayArgs($mainNode, $transient_options, $action);
    }
	
	/**
	 * Displays the scalar node arguments from the Child Form
	 */
	public function displayArgs($mainNode, $transient_options, $action) {
		// Display the child form's form name, title, link, and link filter
		$this->template->setDisplayDataImmediate("form_name", $this->getStorage()->getName(), $mainNode);
		$this->template->setDisplayDataImmediate('title', $this->getField('title'), $mainNode);
		$this->template->setDisplayDataImmediate('link', $this->getField('link'), $mainNode);
		$this->template->setDisplayDataImmediate('link_filter', $this->getField('link_filter'), $mainNode);
		$inputs = array('form_name','title','link','link_filter');
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
