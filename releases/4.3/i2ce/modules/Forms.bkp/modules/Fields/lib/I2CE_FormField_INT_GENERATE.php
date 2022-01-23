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
class I2CE_FormField_INT_GENERATE extends I2CE_FormField_DB_INT { 
    /**
     * @var boolean A flag to determine if this value needs to be auto generated from the database.
     */
    protected $generate;

    /**
     * Set the generate value for this field.
     * @param boolean $generate
     */
    public function setGenerate( $generate = true ) {
        $this->generate = $generate;
    }
    /**
     * Return the value of the generate flag for this field.
     * @return boolean
     */
    public function getGenerate() {
        return $this->generate;
    }

    /**
     * Create a new instance of a I2CE_FormField
     * @param string $name
     * @param array $options A list of options for this form field.
     */
    public function __construct( $name, $options ) {
        parent::__construct($name, $options);
        $this->generate = false;
    }

        
    /** 
     * Sets the value of this field from the posted form.
     * @param array $post The $_POST array holding the values for this form.
     */
    public function setFromPost( $post ) { 
        if (!is_array($post)) {
            return;
        }
        if (array_key_exists('generate',$post) && $post['generate'] == 'yes') {
            $this->setGenerate();        
        } else  {
            $this->value = $post['value'];
        }
    }



    /** 
     * Returns the value of this field ready to be stored in the database.
     * @return mixed
     */
    public function getDBValue() {
        if ( $this->getGenerate() ) {
            return "";
        } else {
            return $this->getValue();
        }
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
        if ( $this->getGenerate() ) {
            return "Will be generated.";
        } else {
            return $value;
        }
    }



        
    /**
     * Checks to see if the current value for this is set and valid.
     * @return boolean
     */
    public function isValid() {
        if ( $this->getGenerate() ) {
            return true;
        } else {
            return parent::isValid();
        }
    }


    /**
     * @returns array of DOMNodes
     */
    public function processDOMNotEditable($node,$template,$form_node) {
        $ele_name = $this->getHTMLName();
        if (  $this->getGenerate() ) {
            $hidden = $template->createElement( "input", array( "name" => $ele_name . "[generate]", "type" => "hidden", "value" => "yes" ) );
            $node->appendChild( $hidden );
            $generate_text = "Generate this Number";
            if ( $form_node->hasAttribute( "generate" ) ) {
                $generate_text = $form_node->getAttribute( "generate" );
            } elseif ( $this->hasHeader( "generate" ) ) {
                $generate_text = $this->getHeader( "generate" );
            }
            $node->appendChild( $template->createTextNode( $generate_text ) );
        } else {
            $hidden = $template->createElement( "input", array( "name" => $ele_name . '[value]', "type" => "hidden", "value" => $this->getDBValue() ) );
            $node->appendChild( $hidden );
            $node->appendChild( $template->createTextNode( $this->getDisplayValue() )) ;
        }
    }


    /**
     * @returns array of DOMNode
     */
    public function processDOMEditable($node,$template,$form_node) {
        $ele_name = $this->getHTMLName();
        $element = $template->createElement( "input", array( "name" => $ele_name . '[value]', "id" => $ele_name . '[value]', "type" => "text", "value" => $this->getDBValue() ) );
        $this->setElement($element);
        $template->addHeaderLink( "uncheck.js" );
        $generate_text = "Generate this Number";
        if ( $form_node->hasAttribute( "generate" ) ) {
            $generate_text = $form_node->getAttribute( "generate" );
        } elseif ( $this->hasHeader( "generate" ) ) {
            $generate_text = $this->getHeader( "generate" );
        }
        $check_box = $template->createElement( "input", array( "type" => "checkbox", "name" => $ele_name . "[generate]",
                                                               "id" => $ele_name . "[generate]", "value" => "yes", "onclick" => "check(this, '{$ele_name}[value]');" ) );
        if ( $this->getGenerate() ) {
            $check_box->setAttribute( "checked", "checked" );
        }
        $node->appendChild($check_box) ;
        $node->appendChild($template->createElement( "label", array( "for" => $ele_name . "[generate]" ), $generate_text )) ;
        $node->appendChild($template->createElement( "br" )) ;
        $element->setAttribute( "onchange", "uncheck('" . $ele_name . "[generate]');" );
        $node->appendChild( $element );

    }


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
