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
class I2CE_FormField_TOGGLE extends I2CE_FormField_DB_INT { 

    /**
     * Appends an XML representation of the field data onto the current node
     * @param DOMNode $field_node the node we are appending the representation onto
     */
    protected function appendXMLRepresentation($field_node) {
        $doc = $field_node->ownerDocument;
        $field_node->appendChild( $doc->createTextNode($this->getValue() ?  1 : 0));
    }




    /**
     * Create a new instance of a I2CE_FormField
     * @param string $name
     * @param array $options A list of options for this form field.
     */
    public function __construct( $name, $options ) {
        parent::__construct($name, $options);
    }

    /** 
     * Sets the value of this field from the posted form.
     * @param array $post The $_POST array holding the values for this form.
     */
    public function setFromPost( $post ) { 
        $this->value = (  $post == "yes" || $post == 1 ) ;
    }



    /** 
     * Returns the value of this field ready to be stored in the database.
     * @return mixed
     */
    public function getDBValue() {
        return ( $this->getValue() ? 1 : 0 );
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
        return ( $value == 1 ? "Yes" : "No" );
    }


    /**
     * Checks to see if the current value for this is set and valid.
     * @return boolean
     */
    public function isValid() {
        // Changed this to only return valid when true so that ifset works correctly.
        // Changed this back to be valid if it's true or false and modified ifset
        // to work more consistently.
        //return $this->getValue();
        if ( $this->issetValue()) { 
            return true;
        }
    }




    /**
     * @returns array of DOMNode
     */
    public function processDOMEditable($node,$template,$form_node) {
        $ele_name = $this->getHTMLName();
        $element = $template->createElement( "select", array( "name" => $ele_name ) );
        if ($form_node->hasAttribute('id')) {
          $element->setAttribute('id',$form_node->getAttribute('id'));
        }
        $this->setElement($element);
        $opt1 = $template->createElement( "option", array( "value" => "yes" ), "Yes" );
        $opt2 = $template->createElement( "option", array( "value" => "no" ), "No" );
        if ( $this->getDBValue() == 1 ) {
            $opt1->setAttribute( "selected", "selected" );
        } else {
            $opt2->setAttribute( "selected", "selected" );                                      
        }
        $element->appendChild( $opt1 );
        $element->appendChild( $opt2 );
        $node->appendChild($element);
    }



}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
