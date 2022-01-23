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
class I2CE_FormField_STRING_MLINE extends I2CE_FormField_DB_STRING { 

    public function processDOMEditable($node,$template,$form_node) {
        $ele_name = $this->getHTMLName();
        $element = $template->createElement( "textarea", array( "name" => $ele_name, "id" => $ele_name, "rows" => 3, "cols" => 50 ) );
        $this->setElement($element);
        $element->appendChild( $template->createTextNode( $this->getDBValue() ) );
        $node->appendChild($element);                
    }

    /**
     * Return the display value of this form field as a DOM Node.
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @return DOMNode
     */
    public function getDisplayNode( $node,$template ) {
        $text = explode( "\n", $this->getDisplayValue() );
        if ( count( $text ) < 2 ) {
            return parent::getDisplayNode($node, $template );
        } else {
            $node = $template->createElement( "span" ,array('class'=>'string_mline'));
            foreach( $text as $line ) {
                if ( !empty( $line ) ) {
                    $node->appendChild( $template->createTextNode( $line ) );
                    $node->appendChild( $template->createElement( "br" ) );
                }
            }
            return $node;
        }
    }

    public function postprocessDOMEditable( $node, $template, $form_node ) {
        if ( !($inputs = $template->query(".//textarea" ,$node))  instanceof DOMNodeList) {
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
            $input->setAttribute('onchange','I2CE_InputFormatter.format(this,false,false' . $validation .')');
        }
    }

        


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
