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
class I2CE_FormField_DATE_Y extends I2CE_FormField_DB_DATE { 
    public function loadFromXML($node) {
        if (!$node instanceof DOMElement) {
            return;
        }        
        $expected_values = array('year');
        $values = array();
        foreach ($expected_values as $type) {
            if (! ($val_nodes = $node->getElementsByTagName($type)) instanceof DOMNodeList
                || ! ($val_nodes->length == 1)
                || ! ($val_node = $val_nodes->item(0)) instanceof DOMElement
                ) {
                return;
            }
            $values[$type] = $val_node->textContent;
        }
        $this->setValue(I2CE_Date::getYear(   $values['year'] ));        
    }
        
    /** 
     * Sets the value of this field from the posted form.
     * @param array $post The $_POST array holding the values for this form.
     */
    public function setFromPost( $post ) {
        if (is_array($post)) {
            if ( array_key_exists( 'year', $post ) ) {
                $this->setValue( I2CE_Date::getYear( $post['year'] ));
                return;
            }
        } else if (is_string($post)) {
            $this->setFromDB( $post);
            return;
        }
        $this->value = I2CE_Date::getYear();
    }
        



    public function processDOMNotEditable($node,$template,$form_node) {
        $ele_name = $this->getHTMLName();
        I2CE_Date::addYearElement( $template, $ele_name, $this->getValue(), false, $node, false, true );
        $node->appendChild($template->createTextNode( $this->getDisplayValue() )) ;
    }



    public function processDOMEditable($node,$template,$form_node) {
        $default = $this->getValue();
        $ele_name = $this->getHTMLName();
        if ( ! $default instanceof I2CE_Date ) {
            $default = I2CE_Date::now();
        }
        I2CE_Date::addYearElement( $template, $ele_name, $default, $this->hasInvalid(), $node, $this->getYearRange(), false, true );
    }

          

   /**
     * Return the value of this field from the database format for the given type
     * @param integer $type The type of the field to be returned.
     * @param mixed $value
     */
    public function getFromDB(  $value ) {
        $value = parent::getFromDB($value);
        if (! $value instanceof I2CE_Date) {
            return;
        }
        $value->setType(I2CE_Date::YEAR_ONLY);
        return $value;
    }          


    /**
     * Sets the value of this field.
     * @param mixed $value
     */
    public function setValue( $value ) {
        parent::setValue($value);
        if (! $this->value instanceof I2CE_Date) {
            return;
        }
        $this->value->setType(I2CE_Date::YEAR_ONLY);
    }




}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
