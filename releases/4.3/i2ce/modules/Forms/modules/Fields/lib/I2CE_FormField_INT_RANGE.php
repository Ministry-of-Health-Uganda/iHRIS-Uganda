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
class I2CE_FormField_INT_RANGE extends I2CE_FormField_DB_INT{ 




    /**
     * Create a new instance of a I2CE_FormField
     * @param string $name
     * @param array $options A list of options for this form field.
     */
    public function __construct( $name, $options ) {
        parent::__construct($name, $options);
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
        return $value;                      
    }


    /**
     * Checks to see if the current value for this is set and valid.
     * @return boolean
     */
    public function isValid() {
        if ( !$this->issetValue()) {
            return false;
        }
        $val = $this->getValue();
        if (! (is_int($val) || (is_string($val) && ctype_digit($val)))) {
            return false;
        }
        $start = $this->getStart(); 
        $end = $this->getEnd();
        if ($val < $start || $val > $end) {
            return false;
        }
        $step = ((int) $this->getStep());
        if ($step > 1) {
            return (($val - $start) % $step) == 0;
        }
        return true;
    }

    /**
     * Get the start for this range
     */
    public function getStart() {
        $path = "meta/start";
        if (!$this->optionsHasPath($path) || ! is_scalar( $start = $this->getOptionsByPath($path) )) {
            return 0;
        }
        return $start;

    }

    /**
     * Get the start for this range
     */
    public function getEnd() {
        $path = "meta/end";
        if (!$this->optionsHasPath($path) || ! is_scalar( $end = $this->getOptionsByPath($path) )) {
            return 10;
        }
        return $end;
    }

    /**
     * Get the step for this range
     */
    public function getStep() {
        $path = "meta/step";
        if (!$this->optionsHasPath($path) || ! is_scalar( $step = $this->getOptionsByPath($path) )) {
            return 1;
        }
        return $step;
    }


    /**
     * @returns array of DOMNodes
     */
    public function processDOMNotEditable($node,$template,$form_node) {      
        $ele_name = $this->getHTMLName();
        $node->appendChild($template->createElement( "input", array( "name" => $ele_name, "type" => "hidden", "value" => $this->getDBValue() ) ));
        $node->appendChild($template->createTextNode( $this->getDisplayValue() )) ;
    }


    /**
     * @returns array of DOMNode
     */
    public function processDOMEditable($node,$template,$form_node) {
        $selected = $this->getValue();        
        $start = $this->getStart();
        $end = $this->getEnd();
        $step = $this->getStep();
        if ($step < 1) {
            $step = 1;
        }
        if ($start > $end) {
            $end = $start;
        }
        $selectNode = $template->createElement('select',array( 'name'=>$this->getHTMLName()));                
        $this->setElement($selectNode);
        if ( $form_node->hasAttribute( "blank" ) ) {
            $blank_text = $form_node->getAttribute( "blank" );
        } else {
            $blank_text = "Select One";
        }
        $selectNode->appendChild( $template->createElement( "option", array( 'value' => '' ), $blank_text ) );
        for ($i=$start; $i <= $end; $i += $step) {
            $attrs = array('value'=>$i);
            if ($i == $selected) {
                $attrs['selected'] = 'selected';
            }            
            $selectNode->appendChild($template->createElement('option', $attrs, $i));
        }
        $node->appendChild($selectNode);
    }


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
