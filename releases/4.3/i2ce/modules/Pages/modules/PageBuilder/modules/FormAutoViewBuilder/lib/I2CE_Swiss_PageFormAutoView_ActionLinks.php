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
* Class I2CE_Swiss_PageFormAutoView_ActionLinks
* 
* @access public
*/


class I2CE_Swiss_PageFormAutoView_ActionLinks extends I2CE_Swiss {
    
	public function processValues($vals) {
		
		// Get and check to see if the new action link's unique id is empty
        $name = trim($vals['key_name']);
        if (!$name) {
            $this->userMessage("Name is empty");
            I2CE::raiseError("Name is empty");
            return false;
        }
        // Make sure that the unique id doesn't already exist
        $usedNames = $this->storage->getKeys();
        if (in_array($name, $usedNames)) {
            $this->userMessage("Name $name is already being used");
            I2CE::raiseError("Name $name is already being used");
            return false;
        }
        
        // Create an array used to process the new action link's form field,
        // link location, and link text
		$new_vals = array(
        	'formfield'=>$vals['form_field'],
        	'linkloc'=>$vals['linkloc'],
        	'linktext'=>$vals['linktext']
            );
        
        // Create an action link with the unique name given by the user
        $link = $this->getChild($name, true);
        if (!$link instanceof I2CE_Swiss_PageFormAutoView_ActionLink) {
            I2CE::raiseError("Link $name is incorrect");
            return false;
        }
        
        // Process the new action link's form field, link location, and link text
        return $link->processValues($new_vals);
	}

	protected function getChildType($child) {
		// The child of ActionLinks is a single ActionLink
		return 'PageFormAutoView_ActionLink';
	}
	
    protected function getTemplate() {
		return 'swiss_pageformautoview_actionlinks.html';
	}
	
	public function displayValues($content_node, $transient_options, $action) {
		// Append the html template and save it as a DOMNode object
		if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(), 'div', $content_node)) instanceof DOMNode) {
            return false;
        }
        
		// Get all of the names of the page's action links
		$actionLinks = $this->getChildNames();
		// See if there are any action links, if not, display so in the html
		$this->template->setDisplayDataImmediate('has_action_link', !(count($actionLinks) == 0), $mainNode);
		// If there are existing action links, display them
		if (count($actionLinks) >  0) {
            if (!$this->displayActionLinks($action, $actionLinks, $mainNode, $transient_options)) {
                I2CE::raiseError("Could not display existing links");
                return false;
            }
        }
        
		// Display the add menu for adding a new action link
		if (!$this->displayAddMenu($mainNode, $action)) {
        	I2CE::raiseError("Could not display add menu");
        	return false;
        }
        
		return true;
    }
	
	/**
     * Displays all of the existing action links if there are any
     * @param mixed $configPath
     * @param DOMNode $contentNode
     * @returns boolean true on success
     */
    protected function displayActionLinks( $action, $actionLinkNames, $contentNode, $transient_options) {
		// Get the existing_action_links as an element node to append existing action links to
        $appendNode = $this->template->getElementById('existing_action_links', $contentNode);
        if (!$appendNode instanceof DOMNode) {
            return false;
        } 
		// Append the actionlinks_list.html template to $appendNode
        $childrenNode = $this->template->appendFileByNode('swiss_pageformautoview_actionlinks_list.html', 'div', $appendNode);
        if (!$childrenNode instanceof DOMNode) {
            return false;
        }
		// Get the action_links_list element
        $existingNode = $this->template->getElementById('action_links_list', $childrenNode);
        if (!$existingNode instanceof DOMNode) {
            return false;
        } 
		
		// Go through each of the existing action links
        foreach ($actionLinkNames as $link) {
			// Get the each action link as an ActionLink object
			$swissChild = $this->getChild($link); 
            if (!$swissChild instanceof I2CE_Swiss) {
                continue;
            }
            
            // Add an Ajax link for the action link as well as a Delete link for it
            $linkNode = $this->template->appendFileByNode('swiss_pageformautoview_actionlink.html', 'li', $existingNode);   
            $delete_link = $swissChild->getURLRoot('delete') . $swissChild->path . $swissChild->getURLQueryString();
            $this->template->setDisplayDataImmediate("link_name", $swissChild->getStorage()->getName(), $linkNode);
            $this->template->setDisplayDataImmediate("existing_action_link_delete_link", $delete_link, $linkNode);
            $swissChild->addAjaxLink('existing_action_link', 'action_link_container', 'existing_action_link_ajax', $linkNode, $action, $transient_options);
        }
        
        return true;
    }
	
	protected function displayAddMenu($mainNode, $action) {
    	// Get the node to add the new add action link menu to
        $appendNode = $this->template->getElementById('add_new_action_link', $mainNode);
        if (!$appendNode instanceof DOMNode) {
        	return false;
        }
        // Append the add_actionlink.html file to $appendNode
        $addNewChildNode = $this->template->appendFileByNode('swiss_pageformautoview_add_actionlink.html', 'div', $appendNode);
        if (!$addNewChildNode instanceof DOMNode) {
        	return false;
        }
        // Get the child form type selector node
        $keyName = $this->template->getElementById('key_name', $addNewChildNode);
        if (!$keyName instanceof DOMNode) {
    		return false;
    	}
    	
    	// Add a validator to make sure no already used names are being used for the action link's unique identifier
		$usedNames = $this->storage->getKeys();
        $this->template->setClassValue($keyName, 'validate_data', array('notinlist'=>$usedNames), '%');
        // Get the page's primary form and display it as the form field for the new action link
		$primaryForm = $this->getParent()->getParent()->getField('primary_form');
		$this->template->setDisplayDataImmediate('form_field', $primaryForm . ':id', $mainNode);
    	    	
    	// Use ajax link to add the new action link
        return $this->addAjaxOptionMenu('add_actionlink', 'action_links_link', $mainNode);
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
