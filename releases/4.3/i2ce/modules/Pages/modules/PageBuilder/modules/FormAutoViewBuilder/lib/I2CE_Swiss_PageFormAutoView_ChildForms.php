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
* @package I2CE
* @subpackage page
* @author Michael Cote <michaelpcote@gmail.com>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_PageFormAutoView_ChildForms
* 
* @access public
*/


class I2CE_Swiss_PageFormAutoView_ChildForms extends I2CE_Swiss {
    
    public function processValues($vals) {
        // Add the new child form to the page with the user selected form type
        if (array_key_exists('form_which', $vals)   ) {
            if ($vals['form_which'] != "" && $vals['form_which'] != "All available forms already exist") {
                // Create new child form with the given type
                $newChild = $this->getChild($vals['form_which'], true);
                // Process the child form's display name
                $newChild->processValues(array('title'=>$vals['childform_displayname']));
                // Get the form object for the child
                if (  (  $childForm = I2CE_FormFactory::instance()->createContainer($newChild->getName())) instanceof I2CE_Form) {
                    // Set default printf format
                    $newChild->setField('printf', $childForm->getNameId());
                    // Set default printf argument
                    $printfArgs = $newChild->getChild('printf_args', true);
                }
            }
        }
        
        if (array_key_exists('display_order',$values)) {            
            $this->setOrder($values['display_order']);
        }
        
        return true;
    }

    protected function getTemplate() {
        return 'swiss_pageformautoview_childforms.html';
    }
	
    public function getChildType($child) {
        // Return the individual ChildForm object as a child of all forms
        return 'PageFormAutoView_ChildForm';
        
    }
	
	public function displayValues($content_node, $transient_options, $action) {
		// Append the html template and save it as a DOMNode object
		if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(), 'div', $content_node)) instanceof DOMNode) {
            return false;
        }
        
		// Get all of the names of the child forms
		$childForms = $this->getChildNames();
		// See if there are any child forms, if not, display so in the html
		$this->template->setDisplayDataImmediate('has_child', !(count($childForms) == 0), $mainNode);
		// If there are, then display a link for each child form
		if (count($childForms) >  0) {
            if (!$this->displayChildLinks($action, $childForms, $mainNode, $transient_options)) {
                I2CE::raiseError("Could not display existing forms");
                return false;
            }
        }
        
        // Display the menu used to add a new child form
        if (!$this->displayAddMenu($mainNode, $childForms, $action)) {
        	I2CE::raiseError("Could not display add menu");
        	return false;
        }
        
        return true;
    }
	
	/**
     * Displays all of the existing child forms, and links to them, if there are any
     * @param mixed $configPath
     * @param DOMNode $contentNode
     * @returns boolean true on success
     */
    protected function displayChildLinks($action, $childNames, $contentNode, $transient_options) {
		// This is pulled from childforms.html and tells us where to add or "append" our list
        $appendNode = $this->template->getElementById('existing_child_forms', $contentNode);
        if (!$appendNode instanceof DOMNode) {
            return false;
        } 
		// This is the start of the unordered list that will be added to appendNode
        $childrenNode = $this->template->appendFileByNode('swiss_pageformautoview_childforms_links.html', 'div', $appendNode);
        if (!$childrenNode instanceof DOMNode) {
            return false;
        }
		// This gets the current node in childforms_link.html to add additional lists to add to childrenNode
        $existingNode = $this->template->getElementById('child_forms_list', $childrenNode);
        if (!$existingNode instanceof DOMNode) {
            return false;
        } 
		
		// Go through each of the child forms
        foreach ($childNames as $child) {
			$swissChild = $this->getChild($child); 
            if (!$swissChild instanceof I2CE_Swiss) {
                continue;
            }
            $childNode = $this->template->appendFileByNode('swiss_pageformautoview_childform.html', 'li', $existingNode);   
            $delete_link = $swissChild->getURLRoot('delete') . $swissChild->path . $swissChild->getURLQueryString();
            $this->template->setDisplayDataImmediate("child_name", $swissChild->getStorage()->getName(), $childNode);
            $this->template->setDisplayDataImmediate("existing_child_delete_link", $delete_link, $childNode);
            $swissChild->addAjaxLink('existing_child_link', 'child_form_container', 'existing_child_ajax', $childNode, $action, $transient_options);
        }
        return true;
    }
    
    protected function displayAddMenu($mainNode, $existingChildForms, $action) {
    	// Get the node to add the new add child menu to
        $appendNode = $this->template->getElementById('add_new_childform', $mainNode);
        if (!$appendNode instanceof DOMNode) {
        	return false;
        }
        // Append the add_childform.html file to $appendNode
        $addNewChildNode = $this->template->appendFileByNode('swiss_pageformautoview_add_childform.html', 'div', $appendNode);
        if (!$addNewChildNode instanceof DOMNode) {
        	return false;
        }
        // Get the child form type selector node
        $childType = $this->template->getElementById('add_childform_type_selector', $addNewChildNode);
        if (!$childType instanceof DOMNode) {
    		return false;
    	}
    	// Add blank default option to the beginning of the child form type selector dropdown list
    	$this->template->appendElementByNode($childType, 'option');
        // Get the child form name node
    	$childName = $this->template->getElementById('childform_displayname', $addNewChildNode);
    	if (!$childName instanceof DOMNode) {
    		return false;
    	}
    	
    	// Get the primary form of the page from the Arguments
    	$primaryForm = $this->getParent()->getParent()->getField('primary_form');
    	// Get all child forms that can be added to the primary form
    	$allChildForms = I2CE_Form::getChildFormsByForm($primaryForm);
    	// Boolean flag for whether all available forms already exist 
    	$allFormsExist = true;
    	// Attempt to add each child form type to the dropdown selector
    	foreach ($allChildForms as $form) {
    		// Boolean flag to check whether a child form already exists for the page
    		$formExists = false;
    		// Check to see whether each child form already exists for the page
    		foreach ($existingChildForms as $existingForm) {
    			// If it does, set boolean flag to true
    			if ($form == $existingForm) {
    				$formExists = true;
    			}
    		}
    		
    		// Add the child form type if the child form doesn't already exist
    		if (!$formExists) {
    			$this->template->appendElementByNode($childType, 'option', array('value'=>$form), $form);      
    			// Set allFormsExist flag to false if a form has been added to the dropdown selector
    			$allFormsExist = false;
    		}
    	}
    	// If all available child forms already exist for the page, add N/A option
    	if ($allFormsExist) {
    		$this->template->appendElementByNode($childType, 'option', array(), 'All available forms already exist');
    	}
    	
    	// Get the list of existing child form names and use it to validate the name
    	$existingNames = $this->getChildNames();
    	$this->template->setClassValue($childName, 'validate_data', array('notinlist'=>$existingNames), '%');
    	
    	//Ajax link to add the new child form
        return $this->addAjaxOptionMenu('add_childform', 'child_forms_link', $mainNode);
    }
    
    public function setOrder($order) {
        if (is_string($order)) {
            $order = explode(',',$order);
        }
        if (!is_array($order)) {
            $order = array();
        }
        foreach ($order as &$ord) {
            $ord = trim($ord);
        }
        $this->setField('display_order',implode(',',$order));
        return true;
    }

    
    
    public function getOrder($as_array = true) {
        if ($this->hasField('display_order')) {
            if ($as_array) {
                return explode(",",$this->getField('display_order'));
            } else {
                return $this->getField('display_order');
            }
        } else {
            if ($as_array) {
                return array();
            } else {
                return false;
            }
        }
    }

 public function getAjaxJSNodes() {
        return parent::getAjaxJSNodes() . ',sortable';
    }
    
    
  protected function addFieldSorter($fieldHeaders,$mainNode) {
        $this->template->addHeaderLink('mootools-core.js');
        $this->template->addHeaderLink('mootools-more.js');
        $inputNode =  $this->template->createElement('input',array('name'=>'display_order','type'=>'hidden','value'=>implode(',',array_keys($fieldHeaders))));
        $this->template->appendNode($inputNode,        $mainNode);
        $ret = $this->renameInputs('display_order',$inputNode);
        if (!array_key_exists('display_order',$ret)) {
            I2CE::raiseError("Could not add sorter");
            return;
        }
        $inputNode->setAttribute('id',$ret['display_order']);
        $fields_id = 'fields_list:' . $this->path;
        $this->template->reIdNodes('fields_list', $fields_id, $mainNode);
        $display_order_name = $ret['display_order'];
        $js="
window.addEvent('domready',function() {
    var displayed_field_sorter = $('{$fields_id}');    
    var displayed_field_sort = $('{$display_order_name}');    
    if (displayed_field_sorter && displayed_field_sort ) { 
       var displayed_field_sortOptions = {
            handle:'span.sortablehandle',
            onComplete: function() {
                var order = new Array();;
                displayed_field_sorter.getElements('.sortme').each(function(e) {
                    order.push(e.get('text'));
                });
                displayed_field_sort.setProperty('value',order.join(','));
            },
            opacity: 0.5
       };
       new Sortables(displayed_field_sorter,displayed_field_sortOptions);
    }
});
";
        $this->template->addHeaderText($js,'script','sortable');

    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
