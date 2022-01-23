<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
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
    * @package I2CE
    * @author Luke Duncan <lduncan@intrahealth.org>
    * @since v2.0.0
    * @version v2.0.0
    */
/**
 * Class for defining all the database fields used by a {@link I2CE_Form} object.
 * @package I2CE
 * @access public
 */
class I2CE_FormField_INT_LIST extends I2CE_FormField_DB_STRING { 


    public function loadFromXML($node) {
        if (!$node instanceof DOMElement
            || ! ($val_nodes = $node->getElementsByTagName('value')) instanceof DOMNodeList
            || ! ($val_nodes->length > 0)
            ) {
            return;
        }
        $value = array();
        foreach ($val_nodes as $val_node) {
            if (! $val_node instanceof DOMElement) {
                continue;
            }
            $value[] = $val_node->textContent;
        }
        $this->setValue($value);
    }



    /**
     * Appends an XML representation of the field data onto the current node
     * @param DOMNode $field_node the node we are appending the representation onto
     */
    protected function appendXMLRepresentation($field_node) {
        $doc = $field_node->ownerDocument;
        if ( is_array($value = $this->getValue())) {
            foreach ($value as $v) {
                $field_node->appendChild($doc->createElement('value',$v));
            }
        }
    }

    /**
     * Return the value of this field from the database format for the given type
     * @param integer $type The type of the field to be returned.
     * @param mixed $value
     */
    public function getFromDB($value ) {
        return explode( ",", $value );
    }

    /**
     * Sets the value of this field from the database format.
     * @param mixed $value
     */
    public function setFromDB( $value ) {
        $this->value = $this->getFromDB($value );
    }   
                
    /**  
     * Sets the value of this field from the posted form.
     * @param array $post The $_POST array holding the values for this form.
     */
    public function setFromPost( $post ) {
        if ( is_array( $post)) {
            $this->value = $post;
        } else if (is_string($post)) {
            $this->value = explode( ",", $post);
        } 
    }
        
        

    public function getValue() {
        if (!$this->issetValue()) {
            return array();
        } 
        return $this->value;
    }
        
    public function getDBValue() {
        return implode( ",", (array)$this->getValue() );
    }
        
    /**
     * Returns the value of this field as a human readable format.
     * @param I2CE_Entry $entry If a I2CE_Entry object has been passed to this method then it will return the value for that entry assuming it's an 
     * entry for this field.
     * @return mixed
     */
    public function getDisplayValue( $entry=false,$style='default' ) {
        if ( $entry instanceof I2CE_Entry ) {
            $value = $entry->getValue();
        } else {
            $value = $this->getValue();
        }
        return implode( ", ", $value );
    }



        
        
    /**
     * Checks to see if the current value for this is set and valid.
     * @return boolean
     */
    public function isValid() {
        $ret = true;
        if ( is_array( $this->getValue() ) ) {
            foreach( $this->getValue() as $val ) {
                $ret = $ret && I2CE_Validate::checkNumber( $val );
            }
        } else {
            $ret = false;
        }
        return $ret;
    }
        



    public function processDOMEditable($node,$template,$form_node) {
        $node->appendChild($template->createTextNode( "Error!!" )) ;
    }

          



}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
