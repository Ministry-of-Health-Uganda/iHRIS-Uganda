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
* Class I2CE_Swiss_PageFormAutoView_AutoTemplate
* 
* @access public
*/


class I2CE_Swiss_PageFormAutoView_AutoTemplate extends I2CE_Swiss {

	public function processValues($vals) {
		// Call I2CE_Swiss->processValues() as there are no specific values to process
        if (!parent::processValues($vals)) {
            return false;
        }
		
		// Set the form display name
        if (array_key_exists('form_display_name', $vals)) {
            $this->setField('form_display_name', $vals['form_display_name']);	   
        }
		
		// Set the display order
        if (array_key_exists('display_order',$vals) && is_array($vals['display_order']) ) {
		
			$display_order = array();
			$actual = 0;
			$correct = 0;
			$i = 0;
			foreach($vals['display_order'] as $v) {
				$actual += $v;
				$correct += ++$i;
				$display_order[] = '';
            }
			if ( $correct != $actual ) {
				I2CE::raiseError('Correct: '.$correct.' Actual: '.$actual);
				return false;
			}
			foreach($vals['display_order'] as $child=>$index) {
				$display_order[($index - 1)] = $child;
            }
			$comma_sep = implode( ',', $display_order );
			$this->setField('display_order', $comma_sep);
		} 
		
		// Set the title for the page
        if (array_key_exists('title', $vals)) {
            $this->setField('title', $vals['title']);	   
        }
		
		if (array_key_exists('task',$vals) && is_array($vals['task']) 
			&& ($tasksNode = $this->storage->traverse('task',true,false)) instanceof I2CE_MagicDataNode
			) {
			$tasks = I2CE::getConfig()->getAsArray("/I2CE/tasks/task_description");
			$new_tasks = array();
			foreach($vals['task'] as $task=>$checked) {
				if (!I2CE_MagicDataNode::checkKey($task)
                    || !is_scalar($checked)) {
					continue;
                }
				if( $this->storage->is_scalar("task/$task")
                    || $checked ) {
                    //either key was already set and we are ovewriting it, or we are adding a new key with a set value
					$new_tasks[] = $task;
                }
			}
			$tasksNode->eraseChildren();
			$tasksNode->setValue($new_tasks);
		}
		
		/**
		 *This checks to see if there are any checkboxes selected. If not, delete them all.
		 */
        if ( !array_key_exists('task',$vals) && ($tasksNode = $this->storage->traverse('task',true,false)) instanceof I2CE_MagicDataNode) {
            $tasksNode->eraseChildren();   
        } 
		
		
		if (array_key_exists('childOrder',$vals) && is_array($vals['childOrder']) 
			&& ($orderNode = $this->storage->traverse('child_form_order',true,false)) instanceof I2CE_MagicDataNode
			) {
			$new_child_order = array();
			$actual = 0;
			$correct = 0;
			$i = 0;
			foreach($vals['childOrder'] as $v) {
				$actual += $v;
				$correct += ++$i;
				$new_child_order[] = '';
            }
			if ( $correct != $actual ) {
				I2CE::raiseError('Correct: '.$correct.' Actual: '.$actual);
				return false;
			}
			foreach($vals['childOrder'] as $child=>$index) {
				$new_child_order[($index - 1)] = $child;
            }
			$orderNode->eraseChildren();
			$orderNode->setValue($new_child_order);
		}
		
		/* Attempting to 
        // Set the display order
        if (array_key_exists('childOrder',$vals) && is_array($vals['childOrder']) ) {
		
			$display_order = array();
			foreach($vals['childOrder'] as $k) {
				$display_order[] = '';
            }
			foreach($vals['childOrder'] as $child=>$index) {
				$display_order[($index - 1)] = $child;
            }
			$comma_sep = implode( ',', $display_order );
			$this->setField('child_form_order', $comma_sep);
		} */
        return true;
    }

    protected function getTemplate() {
		return 'swiss_pageformautoview_autotemplate_args.html';
	}
	
	protected function getChildType($child) {
		// If the child is child_forms return a ChildForms object
		if ($child == 'child_forms') {
			return 'PageFormAutoView_ChildForms';
		}
		// If the child is action_links return an ActionLinks object
		if ($child == 'action_links') {
			return 'PageFormAutoView_ActionLinks';
		}
		
		// If not either type of child, call I2CE_Swiss->getChildType() to determine the child type
        return parent::getChildType($child);
    }
	
	public function displayValues($content_node, $transient_options, $action) {
		// Append the html template and save it as a DOMNode object
		if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(), 'div', $content_node)) instanceof DOMNode) {
            return false;
        }
        // Add an Ajax link to display child forms
		if (($childFormChild = $this->getChild('child_forms', true)) instanceof I2CE_Swiss_PageFormAutoView_ChildForms
	     && ($childFormNode = $this->template->getElementById('child_forms', $mainNode)) instanceof DOMNode
	    ) {
			$childFormChild->addAjaxLink('child_forms_link', 'child_forms_container', 'child_forms_ajax', $childFormNode, $action, $transient_options);
        } 
        
        // Add an Ajax link to display the action links
		if (($actionLinkChild = $this->getChild('action_links', true)) instanceof I2CE_Swiss_PageFormAutoView_ActionLinks
	     && ($actionLinkNode = $this->template->getElementById('action_links', $mainNode)) instanceof DOMNode
	    ) {
			$actionLinkChild->addAjaxLink('action_links_link', 'action_links_container', 'action_links_ajax', $actionLinkNode, $action, $transient_options);
        }

		// Display the arguments for the page
        if (!($this->displayArgs($mainNode, $transient_options, $action))) {
            return false;
        }
        
		return true;
    }
	
	public function displayArgs($mainNode, $transient_options, $action) {
		//Declare inputs for renameInputs
    	$inputs = array('form_display_name', 'title');
		
		//Get the primary form
		$primaryForm = $this->getField('../primary_form');
		
		//Display for the title
		if ( $this->getField('title') == null || $this->getField('title') == '' ) {
			$formName = I2CE::getConfig()->getAsArray("/modules/forms/forms/".$primaryForm."/display");
			$this->template->setDisplayDataImmediate('title', 'View '.$formName,$mainNode);
		} else {
			$this->template->setDisplayDataImmediate('title', $this->getField('title'), $mainNode);
		}
		
        // Get the page's primary form and add all forms to choose from as a drop down
        if ( $this->getField('form_display_name') == null || $this->getField('form_display_name') == '' ) {
			$formName = I2CE::getConfig()->getAsArray("/modules/forms/forms/".$primaryForm."/display");
			$this->template->setDisplayDataImmediate('form_display_name', $formName,$mainNode);
		} else {
			$this->template->setDisplayDataImmediate('form_display_name', $this->getField('form_display_name'), $mainNode);
		}
		if (( $childrenOrder = $this->template->getElementByName('child_form_order', 0, $mainNode)) instanceof DOMNode) {
			$childOrder = $this->storage->getAsArray('child_form_order');
			//$childOrder = I2CE::getConfig()->getAsArray('/I2CE/page/'.$primaryForm.'/args/auto_template/child_forms');
			$first = true;
			$i = 0;
			if (!is_array($childOrder) || count($childOrder) == 0) {
				$childOrder = $this->storage->getAsArray('child_forms');
				$this->template->setDisplayDataImmediate('child_number', count($childOrder), $mainNode);
				foreach ( $childOrder as $child=>$v ) {
					$i++;
					if (!$first) {
						$childrenOrder->appendChild($this->template->createElement('br',array()));
					} else {
						$first = false;
					}
					$input = 'childOrder[' . $child  .']';
					$inputs[] = $input;
					$childrenOrder->appendChild($this->template->createTextNode($child . ':'));
					$childrenOrder->appendChild($this->template->createElement('input',array('name'=>$input,'value'=>$i)));
				}
			} else {
				$this->template->setDisplayDataImmediate('child_number', count($childOrder), $mainNode);
				foreach ($childOrder as $child=>$v) {
					$i++;
					if (!$first) {
						$childrenOrder->appendChild($this->template->createElement('br',array()));
					} else {
						$first = false;
					}
					$input = 'childOrder[' . $v  .']';
					$inputs[] = $input;
					$childrenOrder->appendChild($this->template->createTextNode($v. ':'));
					$childrenOrder->appendChild($this->template->createElement('input',array('name'=>$input,'value'=>$i)));
				}
			} 
		}
		
		/* Seeing if making this comma separated would make this work
		if (( $childrenOrder = $this->template->getElementByName('child_form_order', 0, $mainNode)) instanceof DOMNode) {
			$childOrder = $this->getField('child_form_order');
			$first = true;
			$i = 0;
			if ( $order != null && $order != '' ) {
				$childOrder = explode( ',', $childOrder );
				foreach ($childOrder as $child=>$v) {
					$i++;
					if (!$first) {
						$childrenOrder->appendChild($this->template->createElement('br',array()));
					} else {
						$first = false;
					}
					$input = 'childOrder[' . $v  .']';
					$inputs[] = $input;
					$childrenOrder->appendChild($this->template->createTextNode($v. ':'));
					$childrenOrder->appendChild($this->template->createElement('input',array('name'=>$input,'value'=>$i)));
				}
			} else {
				$childOrder = $this->storage->getAsArray('child_forms');
				if ( is_array( $childOrder ) ) {
					foreach ( $childOrder as $child=>$v ) {
						$i++;
						if (!$first) {
							$childrenOrder->appendChild($this->template->createElement('br',array()));
						} else {
							$first = false;
						}
						$input = 'childOrder[' . $child  .']';
						$inputs[] = $input;
						$childrenOrder->appendChild($this->template->createTextNode($child . ':'));
						$childrenOrder->appendChild($this->template->createElement('input',array('name'=>$input,'value'=>$i)));
					}
				}
			}
		} */
		
		//Set the display order
		if (( $displayOrder = $this->template->getElementByName('display_order', 0, $mainNode)) instanceof DOMNode) {
			$order = $this->getField('display_order');
			$first = true;
			$i = 0;
			if ( $order != null && $order != '' ) {
				$display = explode( ',', $order );
				$this->template->setDisplayDataImmediate('display_number', count($display), $mainNode);
				foreach( $display as $ord ) {
					$i++;
					if (!$first) {
						$displayOrder->appendChild($this->template->createElement('br',array()));
					} else {
						$first = false;
					}
					$input = 'display_order[' . $ord  .']';
					$inputs[] = $input;
					$displayOrder->appendChild($this->template->createTextNode($ord.':'));
					$displayOrder->appendChild($this->template->createElement('input',array('name'=>$input,'value'=>$i)));
				}
			} else {
				$form = I2CE_FormFactory::instance()->createContainer($primaryForm);
				$fields = $form->getFieldNames();
				if ( isset( $fields ) && $fields != '' ) {
					$this->template->setDisplayDataImmediate('display_number', count($fields), $mainNode);
					foreach( $fields as $ord ) {
						$i++;
						if (!$first) {
							$displayOrder->appendChild($this->template->createElement('br',array()));
						} else {
							$first = false;
						}
						$input = 'display_order[' . $ord  .']';
						$inputs[] = $input;
						$displayOrder->appendChild($this->template->createTextNode($ord . ':'));
						$displayOrder->appendChild($this->template->createElement('input',array('name'=>$input,'value'=>$i)));
					}
				}
			}
			$displayOrder->appendChild($this->template->createElement('br',array()));
		} 
		//Create checkboxes for tasks
		if ( ($tasksNode = $this->template->getElementByName('task',0,$mainNode)) instanceof DOMNode) {
            $tasks = I2CE::getConfig()->getAsArray("/I2CE/tasks/task_description");
            $selected_task = $this->storage->getAsArray('task');
			$i = 0;
			$trNode;
			$tasksNode->appendChild($tableNode = $this->template->createElement('table',array('style'=>'display:inline-block;width:33%; min-width:33%')));
			foreach ($tasks as $task=>$desc) {
				$input = 'task[' .$task. ']';
				$inputs[] = $input;
                $attrs = array('value'=>$task,'name'=>$input,'type'=>'checkbox');
				if ( $selected_task != null ) {
					if ( in_array($task, $selected_task)) {
						$attrs['checked'] = 'checked';
					} 
				}
				if ( $i%2 == 0 ) {
					$tableNode->appendChild($trNode = $this->template->createElement('tr'));
				}
				$trNode->appendChild($tdNode = $this->template->createElement('td'));
				$tdNode->appendChild($this->template->createElement('input',$attrs));
				$tdNode->appendChild($this->template->createTextNode(' '.$task.' - '.$desc));
				$tdNode->appendChild($this->template->createElement('br'));
				$tdNode->appendChild($this->template->createElement('br'));
				$i++;
			}
        }
		
		
        // Rename the inputs to include swiss instance path
        $this->renameInputs($inputs, $mainNode);

        return true;
    }
    
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
