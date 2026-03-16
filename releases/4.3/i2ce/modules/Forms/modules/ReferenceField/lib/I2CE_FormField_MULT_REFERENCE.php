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


class I2CE_FormField_MULT_REFERENCE extends I2CE_FormField_REFERENCE {

    public function loadFromXML($node) {
        if (!$node instanceof DOMElement
            || ! ($val_nodes = $node->getElementsByTagName('value')) instanceof DOMNodeList
            || ! ($val_nodes->length > 0)
            ) {
            return;
        }
        $value = array();
        foreach ($val_nodes as $val_node) {
            if (! $val_node instanceof DOMElement
                || ! $val_node->hasAttribute('form')
                ) {
                continue;
            }
            $value[] = array($val_node->getAttribute('form',$val_node->textContent));
        }
        $this->setValue($value);
    }

    /**
     * Appends an XML representation of the field data onto the current node
     * @param DOMNode $field_node the node we are appending the representation onto
     */
    protected function appendXMLRepresentation($field_node) {
        $doc = $field_node->ownerDocument;
        $values = $this->getValue();
        if (is_array($values)) {
            foreach ($values as $value) {
                if (!is_array($value) || count($value) != 2) {
                    continue;
                }
                list($form,$id) = $value;
                $field_node->appendChild($val_node= $doc->createElement('value',$id));
                $val_node->setAttribute('form',$form);
            }
        }        
    }


    /**
     * Checks to see if the value has been set.
     * @return boolean
     */
    public function issetValue() {
        if(!is_array($this->value) || count($this->value) == 0) {
            return false;
        }
        foreach ($this->value as $value) {
            if (!is_array($value) || count($value) != 2) {
                return false;
            }
            list($form,$id) =$value;
            if  (!I2CE_Validate::checkString($form) 
                 || !I2CE_Validate::checkString($id)
                ) {
                return false;
            }
        }
        return true;
    }


    /**
     * Return the native value for this form field.
     * @return array
     */
    public function getValue() {
        if ( !$this->isSetValue() ) {
            return array();
        }
        return $this->value;
    }
    
    /**
     * Return the value of this field from the database format for the given type.
     * @param mixed $value
     */
    public function getFromDB( $value ) {
        return json_decode($value,true);
    }


    /**
     * Return the DB value for this form field.
     * @return string
     */
    public function getDBValue() {        
        return json_encode($this->getValue(),JSON_FORCE_OBJECT);
    }
    
        

 

    /**
     * Checks to see if the current value for this is set and valid.
     * @return boolean
     */
    public function isValid() {
        if ( !$this->issetValue()) {
            return false;
        }
        foreach ($this->value as $value) {
            list($form,$id) = $value;
            if (!( $this->isValidForm($form))) {
                return false;
            }
        }
        return true;
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
            $values = $entry->getValue();
        } else {
            $values = $this->getValue();
        }
        if (!is_array($values)) {
            $values = array();
        }
        $ret = array();
        $ff = I2CE_FormFactory::instance();
        $style =  $this->ensureStyle($style);                
        $styles = array($style);
        if ($style != 'default') {
            $styles[] = 'default';
        }
        
        foreach ($values as $value) {
            list($form,$id) = $value;
            if ( $id == '0' || $id == '') {
                continue;
            }
            if (!$form) {
                continue;
            }
            $printfargs = array();
            $printf ='';
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
                break;
            }
            $formid = "$form|$id";
            if (!$this->isValidForm($form) || !$printf || count($printfargs) == 0) {
                $ret[] = $formid;
                continue;
            }
            $formObj = $ff->createForm($formid);
            if (!$formObj instanceof I2CE_Form) {
                continue;
            }
            $formObj->populate();

            $printfvals = array();
            foreach ($printfargs as $printfarg) {
                if (! ($fieldObj = $formObj->getField($printfarg)) instanceof I2CE_FormField) {
                    $printfvals[] = '';
                    continue;
                }
                $printfvals[] = $fieldObj->getDisplayValue();
            }
            $ret[] =  @vsprintf($printf , $printfvals );
        }
        return implode(",", $ret);
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
            $edit_path = "meta/reportSelect/$style";
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
            $printf_path = "meta/display/$form/$style/printf";
            $printfargs_path = "meta/display/$form/$style/printf_args";
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
            $no_limits_path = "meta/display/$form/$style/no_limits";
            if ($this->optionsHasPath($no_limits_path)  && $this->getOptionsByPath($no_limits_path)) {
                $options['contentid']='report_results';
            } else {
                $options['contentid']='report_results_with_limits';
            }
            $options['multi_select'] = 1;
            if ($template->addReportSelector($node,$options)) {
                return true;
            }
        }
        return false;
    }





    



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
