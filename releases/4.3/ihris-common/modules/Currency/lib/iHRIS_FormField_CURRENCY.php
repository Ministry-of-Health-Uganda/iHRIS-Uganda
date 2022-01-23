<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 *//**
    * @package I2CE
    * @author Luke Duncan <lduncan@intrahealth.org>
    * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
    * @since v2.0.0
    * @version v2.0.0
    */
/**
 * Class for defining all the database fields used by a {@link I2CE_Form} object.
 * @package I2CE
 * @access public
 */
class iHRIS_FormField_CURRENCY extends I2CE_FormField_MAP { 


    public function loadFromXML($node) {
        if (!$node instanceof DOMElement
            || ! ($val_nodes = $node->getElementsByTagName('value')) instanceof DOMNodeList
            || ! ($val_nodes->length == 1)
            || ! ($val_node = $val_nodes->item(0)) instanceof DOMElement
            || ! ($val_node->hasAttribute('currency'))
            ) {
            return;
        }
        $this->value = array($val_node->getAttribute('currency'),$val_node->textContent);
    }
    /**
     * Appends an XML representation of the field data onto the current node
     * @param DOMNode $field_node the node we are appending the representation onto
     */
    protected function appendXMLRepresentation($field_node) {
        $doc = $field_node->ownerDocument;
        $value = $this->getValue();
        if (is_array($value) && count($value) == 2) {
            list($currency,$amount) = $value;
            $field_node->appendChild($val_node= $doc->createElement('value',$amount));
            $val_node->setAttribute('currency',$currency);
        }        
    }



    /**
     * Componentizes the given $db_value based on component
     * @param string $db_value.  The non-componentized value
     * @param array $forms of stirng. The form names which we wish to componentize.
     * @param string $component The component we wish to encode
     * @returns string The componentized db_value
     */
    public function getComponentizedValue($db_value,$forms,$component) {        
        //this really shou;dn't be componentized 
        return $db_value;
    }


    /**
     * Componentizes the given $db_value based on component
     * @param string $db_ref.  The reference to the data
     * @param array $forms of stirng. The form names which we wish to componentize.
     * @param string $component The component we wish to encode
     * @returns string The componentized db_value
     */
    public function getSQLComponentization($db_ref,$forms,$component) {
        //this really shou;dn't be componentized 
        return $db_ref;
    }



    /**
     * Return the default display style for this mapped field.
     * @param string $type
     * @return string
     */
    public function getDefaultDisplayStyle( $type ) {
        return 'list';
    }

    /**
     * Return the displayed style for the given display type.
     * CURRENCT field can only have one option so ignore anything given.
     * @param string $display
     * @return string
     */
    public function getDisplayedStyle( $display='default' ) {
        return $this->getDefaultDisplayStyle( $display );
    }

    /**
     * Return the displayed fields for this field.  CURRENCY field types
     * can only select the current field.
     * @param string $display
     * @return array
     */
    public function getDisplayedFields( $display='default', $check_forms = true ) {
        return array( "currency" );
    }

    /**
     * Return the selectable forms for this field.  CURRENCY field types
     * can only select "currency" forms.
     * @return array
     */
    public function getSelectableForms() {
        return array( "currency" );
    }

    /**
     * Return the form limits for this field.  CURRENCY doesn't support
     * form limits for now.
     * @param string $type
     * @return string
     */
    public function getFormLimits( $type = 'default' ) {
        return array();
    }


    /**
     * Return the value of this field from the database format for the given type
     * @param integer $type The type of the field to be returned.
     * @param mixed $value
     */
    public function getFromDB($value ) {
        $value = explode( "=", $value,2);
        if (count($value) < 2) {
            $value[] = 0;
        }
        return $value;
    }

        
                
    /**  
     * Sets the value of this field from the posted form.
     * @param array $post The $_POST array holding the values for this form.
     */
    public function setFromPost( $post ) {
        $value = array();
        if (is_array($post)) {
            if ( array_key_exists( 'currency', $post ) ) {
                $value[0] = $post['currency'];
                $value[1] = $post['value'];
            }
        } elseif ( is_string($post)) {
            $value = explode( "=", $post, 2 );
            if ( count( $value ) == 1 ) {
                $value = array( $value[0], "0" );
            }
        }
        $this->value = $value;
    }
        
        

    public function getValue() {
        if (!$this->isSetValue()) {
            return array( "", "" );
        }
        return $this->value;
    }

    public function getDBValue() {
        if ( $this->isValid() ) {
            return implode( "=", $this->getValue() );
        } else {
            return "";
        }
    }




    /**
     * Return the display value of this form field as a DOM Node.
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @return DOMNode
     */
    public function getDisplayNode( $node,$template ) {
        if ($this->isValid()) {
            return $template->createTextNode( $this->getDisplayValue() );
        } else {
            return $template->createTextNode( '' );
        }
    }
 
        
    /**
     * Returns the value of this field as a human readable format.
     * @param I2CE_Entry $entry If a I2CE_Entry object has been passed to this method then it will return the value for that entry assuming it's an 
     * entry for this field.
     * @param boolean $number_format If true, call number_format on the returned value.
     * @return mixed
     */
    public function getDisplayValue( $entry=false, $number_format=false ) {
        if ( $entry instanceof I2CE_Entry ) {
            $value = $entry->getValue();
        } else {
            $value = $this->getValue();
        }
        $curr_value = parent::getFromDB( $value[0] );
        $curr_string = I2CE_List::lookup( $curr_value[1], $curr_value[0] );
        $number_value = $value[1];
        if ( $number_format ) $number_value = number_format( $number_value );
        return $curr_string . $number_value;
    }

    /**
     * Multiply the value of this field by the given value.
     */
    public function multiply( $value ) {
        if ( $value instanceof iHRIS_FormField_CURRENCY ) {
            $add_value = $value->getValue();
            $add_number = $add_value[1];
        } else {
            $add_number = $value;
        }
        $this->value[1] = round( $this->value[1] * $add_number );
    }
    /**
     * Add the given value to the value of this field.
     */
    public function add( $value ) {
        if ( $value instanceof iHRIS_FormField_CURRENCY ) {
            $add_value = $value->getValue();
            $add_number = $add_value[1];
        } else {
            $add_number = $value;
        }
        $this->value[1] += $add_number;
    }

    /**
     * Checks to see if the current value for this is set and valid.
     * @return boolean
     */
  
    public function isValid() {
        $value = $this->getValue();
        $curr_value = parent::getFromDB( $value[0] );
        return count($this->value) == 2 
            && I2CE_Validate::checkCurrency( $value[1] )
            && I2CE_Validate::checkString( $curr_value[1] ) 
            && in_array($curr_value[0],$this->getSelectableForms());
    }
        

    /**
     * Compares this form field agains the given form field.
     * @param mixed $db_value Either a DB Value or an I2CE_FormField
     * @returns -1,0,1
     */
    public function compare($db_value) {
        if ($db_value instanceof iHRIS_FormField_CURRENCY) {
            $db_value  = $db_value->getValue();
        } elseif (is_string($db_value)) {
            $db_value = explode("=", $db_value);
        }
        if (!is_array($db_value) || count($db_value) != 2) {
            return 0;
        }
        $value = $this->getValue();
        if ($value[0] != $db_value[0]) {
            if ($value[0] <$db_value[0]) {
                return -1;
            } else {
                return 1;
            }
        }      
        if ($value[1] < $db_value[1]) {
            return -1;
        } else  if ($value[1] == $db_value[1]) {
            return 0;
        } else {
            return 1;
        }
    }





    /**
     * Creates a drop down list of options.
     * @param I2CE_Template $template
     * @param DOMNode $node -- the node we wish to add the drop down list under
     * @param boolean $show_hidden.  Show the hidden members of the list, defaults to false.
     * @returns mixed DOMNode or an array of DOMNodes to add.
     */
    protected function create_DOMEditable_list($node, $template, $form_node,$show_hidden= false) {
        $list = $this->getMapOptions('default',$show_hidden);
        $value = $this->getValue();
        $ele_name = $this->getHTMLName();

        $selectNode = $template->createElement('select',array( 'name' => $ele_name . '[currency]', 
                    'class' => 'currency' ) );    
        foreach ($list as $d) {
            $attrs = array('value'=>$d['value']);
            if ($d['value'] == $value[0]) {
                $attrs['selected'] = 'selected';
            }
            $selectNode->appendChild($template->createElement('option', $attrs, $d['display']));
        }
        $node->appendChild($selectNode);
        if (!array_key_exists(1,$value)) {
            $value[1] = null;
        }
        $element = $template->createElement( "input", array( "name" => $ele_name . '[value]', "id" => $ele_name, 
                    "type" => "text", "value" => $value[1], "class" => "currency" ) );
        $this->setElement($element);
        $node->appendChild( $element) ;
    }

                




}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
