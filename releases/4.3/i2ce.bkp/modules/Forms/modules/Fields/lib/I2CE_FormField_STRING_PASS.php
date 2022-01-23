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
class I2CE_FormField_STRING_PASS extends I2CE_FormField_DB_STRING { 



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
        if ( strlen( $value ) > 0 ) {
            return "********";
        } else {
            return "";
        }
    }

    /**
     * Return the string for generating a new password for the form prompt.
     * @param DOMNode $form_node The form node that has the generate attribute
     */
    protected function getGenerateText( $form_node ) {
        if ( $form_node->hasAttribute( "generate" ) ) {
            return $form_node->getAttribute( "generate" );
        } elseif ( $this->hasHeader( "generate" ) ) {
            return $this->getHeader( "generate" );
        }
        return null;
    }

    public function processDOMNotEditable($node,$template,$form_node) {
        $ele_name = $this->getHTMLName();
        $show_password = false;
        $generate_text = $this->getGenerateText( $form_node );

        if ( $generate_text !== null ) {
            $name = $this->name;
            if ( $template->getField( $this->container->getName() . ":generate_" . $name,$node )->getValue() ) {
                $checkbox = $template->createElement( "input", array( "name" => "generate_" . $ele_name, "type" => "hidden", "value" => "yes" ) ); 
                $node->appendChild( $checkbox) ; 
                $node->appendChild( $template->createTextNode( $generate_text ));
                $show_password = true; 
            } 
            $node->appendChild( $template->createElement( "br" ) ); 
        }
        $hidden = $template->createElement( "input", array( "name" => $ele_name, "type" => "hidden", "value" => $this->getDBValue() ) );
        $node->appendChild($hidden );
        if ( $form_node->hasAttribute( "confirm" ) ) {
            $con_field_name = $form_node->getAttribute( "confirm" );
            $confirm_field = $template->getField( $this->container->getName() . ":" . $con_field_name,$node);
            $confirm = $template->createElement( "input", array( "name" => $confirm_field->getHTMLName(), "type" => "hidden",
                                                                 "value" => $confirm_field->getDBValue() ) );
            $node->appendChild( $confirm );
        }
        $node->appendChild( $template->createTextNode( $show_password ? $this->getDBValue() : $this->getDisplayValue() ) );
    }



    public function processDOMEditable($node,$template,$form_node) {
        $ele_name = $this->getHTMLName();
        $generate_text = $this->getGenerateText( $form_node );

        if ( $generate_text != null ) {
            //$name = $form_node->getAttribute('name');                    
            $name = $this->name;
            $gen_field = $template->getField( $this->container->getName() . ":generate_" . $name,$node );             
            $check_arr = array( "name" => $gen_field->getHTMLName(), "id" => $gen_field->getHTMLName(), "type" => "checkbox", "value" => "yes" );
            if ( $gen_field->getValue() ) {
                $check_arr["checked"] = "checked";
            }
            $checkbox = $template->createElement( "input", $check_arr );
            $node->appendChild( $checkbox );
            $node->appendChild( $template->createElement( "label", array( "for" => $gen_field->getHTMLName() ), $generate_text )) ;
            $node->appendChild( $template->createElement( "br" ) );
        } 
        $element = $template->createElement( "input", array( "name" => $ele_name, "id" => $ele_name, "type" => "password", "value" => $this->getDBValue() ) );
        $this->setElement($element);
        $node->appendChild( $element );
        if ( $form_node->hasAttribute( "confirm" ) ) {
            $con_field_name = $form_node->getAttribute( "confirm" );
            $confirm_field = $template->getField( $this->container->getName() . ":" . $con_field_name,$node);
            if ( $this->hasHeader( "second" ) ) {
                $second =  $this->getHeader( "second" );
                $node->appendChild( $template->createElement( "br" ) );
                $node->appendChild( $template->createElement( "label", array( "for" => $confirm_field->getHTMLName() ), $second )) ;
            }

            $confirm = $template->createElement( "input", array( "name" => $confirm_field->getHTMLName(), "type" => "password",
                                                                 "value" => $confirm_field->getDBValue() ) );
            $node->appendChild( $template->createElement( "br" ) );
            $node->appendChild( $confirm );
        }
                
    }

          
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
