<?php
/**
* © Copyright 2011 IntraHealth International, Inc.
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
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.11
* @since v4.0.11
* @filesource 
*/ 
/** 
* Class I2CE_FormField_REFERENCE
* 
* @access public
*/


class I2CE_FormField_REFERENCE extends I2CE_FormField_DB_STRING {




    public function loadFromXML($node) {
        if (!$node instanceof DOMElement
            || ! ($val_nodes = $node->getElementsByTagName('value')) instanceof DOMNodeList
            || ! ($val_nodes->length == 1)
            || ! ($val_node = $val_nodes->item(0)) instanceof DOMElement
            || ! ($val_node->hasAttribute('form'))
            ) {
            return;
        }
        $this->setValue(array($val_node->getAttribute('form'),$val_node->textContent));
    }
    /**
     * Appends an XML representation of the field data onto the current node
     * @param DOMNode $field_node the node we are appending the representation onto
     */
    protected function appendXMLRepresentation($field_node) {
        $doc = $field_node->ownerDocument;
        $value = $this->getValue();
        if (is_array($value) && count($value) == 2) {
            list($form,$id) = $value;
            $field_node->appendChild($val_node= $doc->createElement('value',$id));
            $val_node->setAttribute('form',$form);
        }        
    }


    /**
     * Checks to see if the value has been set.
     * @return boolean
     */
    public function issetValue() {
        if(!is_array($this->value) || count($this->value) != 2) {
            return false;
        }
        list($form,$id) =$this->value;
        return (I2CE_Validate::checkString($form) && I2CE_Validate::checkString($id));
    }


    /**
     * Return the native value for this form field.
     * @return array
     */
    public function getValue() {
        if ( !$this->isSetValue() ) {
            return array( "", "" );
        }
        return $this->value;
    }
    
    /**
     * Return the value of this field from the database format for the given type.
     * @param mixed $value
     */
    public function getFromDB( $value ) {
        if (strpos($value,'|') !== false) {
            return  explode( "|", $value, 2 );
        } else {
            return false;
        }
    }



    /**
     * Checks to see if the current value for this is set and valid.
     * @return boolean
     */
    public function isValid() {
        if ( !$this->issetValue()) {
            return false;
        }
        list($form,$id) = $this->value;
        return $this->isValidForm($form);
    }
    
    
    /**
     * Returns the value of this field as a human readable format.
     * @param I2CE_Entry $entry If a I2CE_Entry object has been passed to this method then it will return the value for that entry assuming it's an 
     * entry for this field.
     * @return mixed
     */
    public function getDisplayValue( $entry=false,$style='default' ) {
        return $this->_getDisplayValue($entry,$style);
    }

    /**
     * Returns the value of this field as a human readable format.
     * @param I2CE_Entry $entry If a I2CE_Entry object has been passed to this method then it will return the value for that entry assuming it's an 
     * entry for this field.
     * @param string $style.  Defaults to 'default'
     * @return mixed
     */
    public function _getDisplayValue( $entry=false, $style='default' ) {
        if ( $entry instanceof I2CE_Entry ) {
            $value = $entry->getValue();
        } else {
            $value = $this->getValue();
        }
        if (!is_array($value) || count($value) != 2) {
            return '';
        }
        list($form,$id) = $value;
        if ( $id == '0' || $id == '') {
            return '';
        }
        if (!$form) {
            return '';
        }
        $formid = "$form|$id";
        $ff = I2CE_FormFactory::instance();
        $formObj = $ff->createForm($formid);
        if (!$formObj instanceof I2CE_Form) {
            return '';
        }
        $formObj->populate();
        if (!$this->isValidForm($form)) {
            return $formid;
        }
        $style =  $this->ensureStyle($style);                
        $styles = array($style);
        if ($style != 'default') {
            $styles[] = 'default';
        }
        foreach ($styles as $s) {
            $printf_path = "meta/display/$form/$s/printf";
            $printfargs_path = "meta/display/$form/$s/printf_args";
            if (!$this->optionsHasPath($printf_path) || ! is_string( $printf = $this->getOptionsByPath($printf_path) )) {
                continue;
            }
            if (!$this->optionsHasPath($printfargs_path) || ! is_array( $printfargs = $this->getOptionsByPath($printfargs_path) )) {
                continue;
            }
            if (strlen($printf)== 0) {
                continue;
            }
            if (count($printfargs) == 0) {
                continue;
            }
            ksort($printfargs);
            $printfvals = array();
            foreach ($printfargs as $printfarg) {
                if (! ($fieldObj = $formObj->getField($printfarg)) instanceof I2CE_FormField) {
                    $printfvals[] = '';
                    continue;
                }
                $printfvals[] = $fieldObj->getDisplayValue();
            }
            return  @vsprintf($printf , $printfvals );
                        
        }
        //if we made it here, we have no valid displays.  try to do something.
        return $formid;
    }


    /**
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @param DOMNode $form_node
     * 
     */
    public function processDOMEditable($node,$template,$form_node) {
        if (!$this->processDOMEditable_reportSelect($node,$template,$form_node)) {
            $this->processDOMNotEditable($node,$template,$form_node);
        }
    
    }


    /**
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @param DOMNode $form_node
     * 
     */
    public function processDOMEditable_reportSelect($node,$template,$form_node) {
        $mf = I2CE_ModuleFactory::instance();
        if (!$mf->isEnabled('CustomReports-Selector')) {
            return false;
        }
        $template->addHeaderLink("mootools-more.js");
        $template->addHeaderLink("getElementsByClassName-1.0.1.js");
        $template->addHeaderLink("I2CE_ClassValues.js");       
        $template->addHeaderLink("I2CE_Window.js");       
        $template->addHeaderLink("I2CE_ToggableWindow.js");       
        $template->addHeaderLink("I2CE_TreeSelect.js");       
        $template->addHeaderLink('Observer.js');
        $template->addHeaderLink('Autocompleter.js');
        $template->addHeaderLink('Autocompleter.css');
        $template->addHeaderLink('I2CE_TreeSelectAutoCompleter.js');
        $template->addHeaderLink("Tree.css");       

        $style = 'default';
        if ($form_node->hasAttribute('show')) {
            $style = $this->ensureEditStyle($form_node->getAttribute('show'));
        }
        $styles = array($style);
        if ($style != 'default') {
            $styles[] = 'default';
        }
        foreach ($styles as $style) {
            $edit_path = "meta/display/$style/reportSelect";
            $report_path = "$edit_path/reportView";
            if (!$this->optionsHasPath($edit_path) || ! is_array(  $options =$this->getOptionsByPath($edit_path) )) {
                continue;
            }
            if (!$this->optionsHasPath($report_path) || ! is_scalar(  $reportView = $this->getOptionsByPath($report_path) )) {
                continue;
            }    
            $report = false;
            $relationship = false;
            $form = false;
            I2CE::getConfig()->setIfIsSet($report,"/modules/CustomReports/reportViews/$reportView/report");
            if (!$report) {
                continue;
            }
            I2CE::getConfig()->setIfIsSet($relationship,"/modules/CustomReports/reports/$report/relationship");
            if (!$relationship) {
                continue;
            }
            $form = false;
            $reportform_path =  "meta/reportSelect/$style/reportform";
            $reportform = 'primary_form';
            if ($this->optionsHasPath($reportform_path)) {
                //we are not using the primary form in the relationship, instead we are using a named form.   
                $reportform = $this->getOptionsByPath($reportform_path);
                if ($reportform == 'primary_form') {
                    I2CE::getConfig()->setIfIsSet($form,"/modules/CustomReports/relationships/$relationship/form");
                } else {
                    try {
                        $relObj = new I2CE_FormRelationship($relationship);
                    }
                    catch(Exception $e) {
                        continue;
                    }
                    $form = $relObj->getForm($reportform);
                }
            } else {
                //we use the primary form
                I2CE::getConfig()->setIfIsSet($form,"/modules/CustomReports/relationships/$relationship/form");
            }
            if (!$form) {
                continue;
            }
            if (!$this->canSelectAnyForm()) {                
                $form_path = "meta/form";
                if (!$this->optionsHasPath($form_path) || ! is_array( $forms = $this->getOptionsByPath($form_path) )) {
                    continue;
                }
                if (!in_array($form,$forms)) {
                    continue;
                }
            }
            $printf_path = "meta/display/$style/printf/$form";
            $printfargs_path = "meta/display/$style/printf_args/$form";
            if (!$this->optionsHasPath($printf_path) || ! is_string( $printf = $this->getOptionsByPath($printf_path) )) {
                continue;
            }
            if (!$this->optionsHasPath($printfargs_path) || ! is_array( $printfargs = $this->getOptionsByPath($printfargs_path) )) {
                continue;
            }
            if (strlen($printf)== 0) {
                continue;
            }
            if (count($printfargs) == 0) {
                continue;
            }
            ksort($printfargs);
            $disp = $this->_getDisplayValue(null,$style);
            $node->setAttribute('id',$this->getHTMLName());
            $options = array(
                'reportview'=>$reportView,
                'printf'=>$printf,
                'printfargs'=>$printfargs,
                'display'=>$disp,
                'value'=>$this->getDBValue(),
                'reportform'=>$reportform
                );
            //print_r($options);
            if ( $this->getOption('required') ) {
                $options['allow_clear'] =false;
            }
            $no_limits_path = "meta/display/$form/$style/no_limits";
            if ($this->optionsHasPath($no_limits_path)  && $this->getOptionsByPath($no_limits_path)) {
                $options['contentid']='report_results';
            } else {
                $options['contentid']='report_results_with_limits';
            }
            if ($template->addReportSelector($node,$options)) {
                return true;
            }
        }
        return false;
    }


    public function getSelectableForms() {
        $path = 'meta/form';
        if (!$this->optionsHasPath($path) 
            || ! array( $forms = $this->getOptionsByPath($path) )
            ) {
            return array();
        }
        return $forms;
    }


    /**
     * Checks to see if this field can have map to any form or not.   Set to true in meta/form_any
     *@returns boolean
     */
    public function canSelectAnyForm() {
        $path = "meta/form_any";
        return ($this->optionsHasPath($path) && $this->getOptionsByPath($path));
    }


    /**
     *Check to see if the given form is a valid form to reference
     * @param string $form
     * @return boolean.  
     */
    protected function isValidForm($form) {
        if (!is_string($form) || strlen($form) == 0) {
            return false;
        }
        if ($this->canSelectAnyForm()) {
            $forms =  I2CE::getConfig()->getKeys("/modules/forms/forms");
        } else {
            $form_path = "meta/form";
            if (!$this->optionsHasPath($form_path) || ! is_array( $forms = $this->getOptionsByPath($form_path) )) {
                return false;
            }
        }
        return in_array($form,$forms);
    }

    /**
     *Ensures that the given style has been defined.  If so, returns that style, otherwise returns 'default'
     *@param string
     */
    protected function ensureStyle($style) {
        //note this field may or may not be popualted at this point.
        $value = $this->getValue();
        if (!is_array($value) || count($value) != 2) {
            return 'default';
        }
        list($form,$id) = $value; 

        if (!is_string($form) || strlen($form) == 0) {
            return 'default';
        }
        if (!$this->isValidForm($form)) {
            return 'default';
        }
        $display_path = "meta/display/$form/$style";
        if (!$this->optionsHasPath($display_path) || ! is_array(  $this->getOptionsByPath($display_path) )) {
            return 'default';
        }
        return $style;
    }


    /**
     * Return the DB value for this form field.
     * @return string
     */
    public function getDBValue() {
        if ( $this->isValid() ) {
            return implode( "|", $this->getValue() );
        } else {
            return "";
        }
    }
    
        

    /**
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @param DOMNode $form_node
     */
    public function processDOMNotEditable( $node, $template, $form_node) {
        $style = 'default';
        if ($form_node->hasAttribute('show')) {
            $style = $this->ensureStyle($form_node->getAttribute('show'));
        }
        $ele_name = $this->getHTMLName();
        $node->appendChild(
            $template->createElement( "input", array( "name" => $ele_name, "type" => "hidden", "value" => $this->getDBValue() ))
            );
        $node->appendChild($this->_getDisplayNode($node, $template, $style ));
    }



    /**
     * Return the display value of this form field as a DOM Node.
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @return DOMNode
     */
    public function getDisplayNode( $node,$template ) {
        return $this->_getDisplayNode($node,$template);
    }


    /**
     * Return the display value of this form field as a DOM Node.
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @param string $style.  Defaults to 'default'
     * @return DOMNode
     */
    public function _getDisplayNode( $node,$template , $style = 'default') {
        $text_node = $template->createTextNode( $this->_getDisplayValue($style) );
        if ( ($href = $this->getHref()) ) {
            $link_node = $template->createElement( "a", array( "href" => $href ) );
            $link_node->appendChild( $text_node );
            return $link_node;
        } else {
            return $text_node;
        }
     }



    



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
