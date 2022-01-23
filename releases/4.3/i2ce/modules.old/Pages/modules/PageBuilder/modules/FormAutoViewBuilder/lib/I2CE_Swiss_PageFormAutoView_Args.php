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
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_PageFormAutoView
* 
* @access public
*/


class I2CE_Swiss_PageFormAutoView_Args extends I2CE_Swiss_PageArgs {
    
    public function processValues($vals) {
    	// Call I2CE_Swiss_PageArgs->processValues() to process the page's title, tasks and default html files
        if (!parent::processValues($vals)) {
            return false;
        }
        
        // Set the primary form for the page
        if (array_key_exists('primary_form', $vals)) {
            $this->setField('primary_form', $vals['primary_form']);	   
        }
		
        // Set the primary form for the page
        if (array_key_exists('title', $vals)) {
            $this->setField('title', $vals['title']);	   
        }
		
        return true;
    }

    protected function getTemplate() {
        return 'swiss_pageformautoview_args.html';
    }

    protected function getChildType($child) {
        // If the child type is auto_template, return an AutoTemplate object
        if ($child == 'auto_template') {
            return 'PageFormAutoView_AutoTemplate'; 
        }
        
        // Otherwise call I2CE_Swiss_PageArgs->getChildType() to get the child type
        return parent::getChildType($child);
    }
	
    public function displayValues($content_node, $transient_options, $action) {
        // Append the html template and save it as a DOMNode object
        if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(), 'div', $content_node)) instanceof DOMNode) {
            return false;
        }
        
        // Display the arguments for the page
        if (!($this->displayArgs($mainNode, $transient_options, $action))) {
            return false;
        }
        
        // Create an Ajax link for the page's AutoTemplate
        if ( ($autoTemplateChild = $this->getChild('auto_template', true)) instanceof I2CE_Swiss_PageFormAutoView_AutoTemplate
	     && ($autoTemplateNode = $this->template->getElementById('auto_template', $mainNode)) instanceof DOMNode
	    ) {
            $autoTemplateChild->addAjaxLink('auto_template_link', 'auto_template_container', 'auto_template_ajax', $autoTemplateNode, $action, $transient_options);
        } 
        
        return true;
    }


    public function displayArgs($mainNode, $transient_options, $action) {
    	// Call I2CE_Swiss_PageArgs->displayArgs() to display the page's title, tasks, and default html files
        if (!parent::displayArgs($mainNode, $transient_options, $action)) {
            //parent function failed
            return false;
        }
        $this->template->setDisplayDataImmediate('title', $this->getField('title'), $mainNode);
        $inputs = array('primary_form', 'form_display_name', 'title');
        // Get the page's primary form and add all forms to choose from as a drop down
        $primaryForm = $this->getField('primary_form');
		
		

        if (($formsNode = $this->template->getElementByName('primary_form', 0, $mainNode)) instanceof DOMNode) {
            $forms = I2CE_FormFactory::instance()->getForms();
            foreach ($forms as $form) {
                $attr = array('value'=>$form);
                // Select the primary form by default if there is one
                if ($form == $primaryForm) {
                    $attr['selected'] = 'selected';
                } 
                $formsNode->appendChild($this->template->createElement('option', $attr, $form));
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
