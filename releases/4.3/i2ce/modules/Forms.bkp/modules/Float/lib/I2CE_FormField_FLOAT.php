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
 */
/**
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v3.1.0
 * @version v3.1.0
 */
/**
 * Class for defining all the database fields used by a {@link I2CE_Form} object.
 * @package I2CE
 * @access public
 */
class I2CE_FormField_FLOAT extends I2CE_FormField_DB_FLOAT { 




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
     * @param mixed $post.  
     */
    public function setFromPost( $post) {

        if (is_string($post)) {
            $post = doubleval(str_replace(",","",$post));
        }
        $this->value = $this->getFromDB( $post );
    }

    /**
     * Sets the value of this field from the database format.
     * @param mixed $value
     */
    public function setFromDB( $value ) {
        if (is_string($value)) {
            $value = doubleval(str_replace(",","",$value));
        }
        $this->value = $this->getFromDB( $value );
    }

        
    /**
     * Checks to see if the current value for this is set and valid.
     * @return boolean
     */
    public function isValid() {
        if ( $this->issetValue()) {
            return I2CE_Validate::checkNumber( $this->getValue() );
        }
        return false;
    }

    /**
     * @returns array of DOMNodes
     */
    public function processDOMNotEditable($node,$template,$form_node) {      
        $ele_name = $this->getHTMLName();
        $hidden = $template->createElement( "input", array( "name" => $ele_name, "type" => "hidden", "value" => $this->getDBValue() ) );
        $node->appendChild( $hidden );
        $node->appendChild( $template->createTextNode( $this->getDisplayValue() ) );
    }


    /**
     * @returns array of DOMNode
     */
    public function processDOMEditable($node,$template,$form_node) {
        $ele_name = $this->getHTMLName();
        $element = $template->createElement( "input", array( "name" => $ele_name, "id" => $ele_name, "type" => "text", "value" => $this->getDisplayValue()) );
        $this->setElement($element);
        $node->appendChild( $element );
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
            return number_format($value,2);
        } else {
            if ($this->isValid()) {
                $value = $this->getValue();
                return number_format($value,2);
            } else {
                return '';
            }
        }
    }



    public function postprocessDOMEditable( $node, $template, $form_node ) {
        if ( !($inputs = $template->query(".//input" ,$node))  instanceof DOMNodeList) {
            return;
        }
        $template->addHeaderLink('mootools-core.js');
        $template->addHeaderLink('mootools-more.js');
        $template->addHeaderLink('I2CE_InputFormatter.js');
        
        $validation = '';
        if ( $this->getOption('required') ) {
            $validation = ",{'nonempty':{}}";
        }

        foreach ($inputs as $input) {
            if (!$input instanceof DOMElement) {
                continue;
            }
            $input->setAttribute('onchange','I2CE_InputFormatter.format(this,"number",{"decimals":2}' . $validation .')');
        }
    }


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
