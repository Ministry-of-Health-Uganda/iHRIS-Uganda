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


class I2CE_Swiss_PageFormAutoView_ChildForm_Printf extends I2CE_Swiss {
	
    public function processValues($vals) {
        // Get the printf arguments magic data node to modify arguments that are added
        if (!($printfArgsNode = $this->getStorage()) instanceof I2CE_MagicDataNode) {
        	return false;
        }
        
        // Get the array of printf argument inputs
        if (!array_key_exists('arg', $vals)) {
        	return false;
        }
        $args = $vals['arg'];
        
        // Get the printf_format input
        if (!array_key_exists('printf_format', $vals)) {
        	return false;
        }
        $printf = $vals['printf_format'];
        $regexMatches = array();
        if (!array_key_exists('noArgs', $vals)) {
        	// Check that the printf_format contains formatters and 
        	// has the same number of formatters as arguments
        	if (preg_match_all('#%s|%[1-9][\d]*s|%[\d]*d|%[\d]*\.[\d]+d#', $printf, $regexMatches) != count($args)) {
        		return false;
        	}
        	
        	// Erase all the original printf arguments for the form
        	$printfArgsNode->eraseChildren();
        	// Add the new set of printf arguments
        	for ($i = 0; $i < count($args); $i++) {
        		$this->setField($i, $args[$i]);	
    		}
        } else {
        	if (preg_match_all('#%s|%[1-9][\d]*s|%[\d]*d|%[\d]*\.[\d]+d#', $printf, $regexMatches) != 0) {
        		return false;
        	}
        	// Erase all the original printf arguments for the form
        	$printfArgsNode->eraseChildren();
        }
        // Set the printf format field for the form
       	$this->getParent()->setField('printf', $vals['printf_format']);
        
        return true;
    }
	
	protected function getTemplate() {
		return 'swiss_pageformautoview_printf.html';
	}
	
	public function displayValues($content_node, $transient_options, $action) {
		// Append the html template and save it as a DOMNode object
		if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(), 'div', $content_node)) instanceof DOMNode) {
            return false;
        }
        
        // Display the arguments for the child form's printf
		return $this->displayArgs($mainNode, $transient_options, $action);
	}
	
	/**
	 * This function is still being worked on
	 **/
	public function displayArgs($mainNode, $transient_options, $action) {
		// Get the child form being worked on
		$form = I2CE_FormFactory::instance()->createContainer($this->getParent()->getName());
		// Get the printf node for displaying the printf format
		$printfNode = $this->template->getElementByID('printf', $mainNode);
		if (!$printfNode instanceof DOMNode) {
			return false;
		}
		// Create an input node that displays and allows users to change the printf format
		$printfNode->appendChild($this->template->createElement('input', array('id'=>'printf_format', 'name'=>'printf_format', 'value'=>$this->getParent()->getField('printf'))));
		
		// Get the printf_args node to display arguments
		$argNode = $this->template->getElementByID('printf_args_options', $mainNode);
		if (!$argNode instanceof DOMNode) {
			return false;
		}
		// Get all the current printf arguments for the child form
		$printfArgs = $this->storage->getAsArray();
		
		// Get all the current printf arguments for the child form
		$i = 0;
		
		foreach ($printfArgs as $arg) {
			// Generate an argument selector for each existing argument
			$this->generateArgSpan($form, $argNode, $arg, $i);
    		$i++;
    	}
    	
    	// Add an argument selector if there are no existing arguments
    	if ($i == 0) {
			$this->generateArgSpan($form, $argNode, null, $i);
			// Check the no arguments box
			$noArgsBox = $this->template->getElementByID('noArgs', $mainNode);
    		if (!$noArgsBox instanceof DOMElement) {
    			return false;
    		}
			$noArgsBox->setAttribute('checked', true);
    	}
    	
    	// Rename the printf format node
    	$this->renameInputs(array('printf_format', 'noArgs'), $mainNode);
    	
		return true;
    }
    
    protected function generateArgSpan($form, $argNode, $arg, $argIndex) {
    	// Create a span to wrap each argument in
		$argNode->appendChild($this->template->createElement('span', array('class'=>'printf_arg_span', 'id'=>'arg[' . $argIndex . ']_span')));
		// Get the argument selector node that was just created
        $argSpan = $this->template->getElementById('arg[' . $argIndex . ']_span', $argNode);
        if (!$argSpan instanceof DOMNode) {
    		return false;
    	}
    	// Create a text label node for each existing argument
    	$displayIndex = $argIndex + 1;
		$argSpan->appendChild($this->template->createTextNode('Field ' . $displayIndex . ': '));
		// Create a dropdown select node for each existing argument to display available arguments to choose from
		$argSpan->appendChild($this->template->createElement('select', array('id'=>'arg[' . $argIndex . ']', 'name'=>'arg[' . $argIndex . ']')));
		// Get the argument selector node that was just created
        $argSelector = $this->template->getElementById('arg[' . $argIndex . ']', $argSpan);
        if (!$argSelector instanceof DOMNode) {
    		return false;
    	}
    	
    	// Add the child form's id as an option to the argument dropdown list
    	$this->template->appendElementByNode($argSelector, 'option', array('value'=>'id'), 'childform_id');
    	// Get all the field names or arguments for the child form and add them as options to each argument dropdown list
    	$fields = $form->getFieldNames();
    	foreach ($fields as $field) {
    		// Make the option selected if it is the one currently being used for the printf argument
    		if ($field == $arg) {
    			$this->template->appendElementByNode($argSelector, 'option', array('value'=>$field, 'selected'=>true), $field);
    		} else {
    			$this->template->appendElementByNode($argSelector, 'option', array('value'=>$field), $field);  
    		}
    	}
    		
    	// Add delete button
    	$argSpan->appendChild($this->template->createElement(
    		'span', 
    		array('id'=>'del_arg[' . $argIndex . ']', 'onclick'=>'removePrintfArg(this);', 'style'=>'color:#0088cc; cursor:pointer;')
    	));
    	// Get the delete button node that was just created
        $delBtn = $this->template->getElementById('del_arg[' . $argIndex . ']', $argSpan);
        if (!$delBtn instanceof DOMNode) {
    		return false;
    	}
    	$delBtn->appendChild($this->template->createTextNode('Delete'));
    		
    	// Line break before displaying next argument selector
    	$argSpan->appendChild($this->template->createElement('br', array()));
    	
    	// Rename the argument selector node
    	$this->renameInputs(array('arg[' . $argIndex . ']'), $argNode);
    	
    	// If there is no printf argument, hide the argument span
    	if ($arg == null) {
    		$argSpan->setAttribute('style', 'visibility: hidden;');
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
