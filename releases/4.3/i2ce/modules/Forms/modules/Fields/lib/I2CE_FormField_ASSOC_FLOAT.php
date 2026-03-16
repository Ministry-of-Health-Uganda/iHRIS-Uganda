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
    * @author Carl Leitner <litlfred@ibiblio.org>
    * @since v2.0.0
    * @version v2.0.0
    */
/**
 * Class for defining all the database fields used by a {@link I2CE_Form} object.
 * @package I2CE
 * @access public
 */
class I2CE_FormField_ASSOC_FLOAT extends I2CE_FormField_ASSOC_LIST { 

    public function postprocessInput($node,$template,$form_node) {
        $validation = '';
        if ( $this->getOption('required') ) {
            $validation = ",{'nonempty':{}}";
        }
        $node->setAttribute('onchange','I2CE_InputFormatter.format(this,"number",{"decimals":2}' . $validation .')');
    }



    /**  
     * Sets the value of this field from the posted form.
     * @param array $post The $_POST array holding the values for this form.
     */
    public function setFromPost( $post ) {
        parent::setFromPost($post);
        if (is_array($this->value)) {
            foreach ($this->value as &$val) {
                if (is_string($val)) {
                    $val = doubleval(str_replace(",","",$val));
                }
            }
            unset($val);
        }
    }


    public function setValueOfKey($key,$val) {
        if (is_string($val)) {
            $val = doubleval(str_replace(",","",$val));
        }
        if (!is_numeric($val)) {
            return;
        }
        parent::setValueOfKey($key,$val);
    }
        
        
    /**
     * Checks to see if the current value for this is set and valid.
     * @return boolean
     */
    public function isValid() {
        if ( !is_array($this->value)) {
            return false;
        }
        if (count($this->value) == 0) {
            return true;
        }
        $valid = true;
        $has_one =false;
        foreach ($this->value as $key=>$val) {
            if ($val === null || $val === '') {
                continue;
            }
            if (is_string($val)) {
                $val = doubleval(str_replace(",","",$val));
            }
            $valid &= is_numeric($val);
            $has_one =true;
        }
        return $valid && $has_one;
    }
        
    public function keyIsSet($key) {
        if (! is_array($this->value) || ! array_key_exists($key,$this->value)) {
            return false;
        } 
        $val = $this->value[$key];
        if (is_string($val) && strlen($val) > 0) {
            $val = doubleval(str_replace(",","",$val));
        }
        return is_numeric($val);
    }



}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
