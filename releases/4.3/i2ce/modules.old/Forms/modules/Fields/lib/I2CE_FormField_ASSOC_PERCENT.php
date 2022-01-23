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
class I2CE_FormField_ASSOC_PERCENT extends I2CE_FormField_ASSOC_FLOAT { 


    /**  
     * Sets the value of this field from the posted form.
     * @param array $post The $_POST array holding the values for this form.
     */
    public function setFromPost( $post ) {
        parent::setFromPost($post);
        if (is_array($this->value)) {
            foreach ($this->value as &$val) {
                $val = $this->extractPercent($val);
            }
            unset($val);
        }
    }


    protected function extractPercent($val) {
        if (is_string($val)) {
            $val = doubleval(str_replace(",","",$val));
            if (preg_match('/\s*(-?(([0-9]+\.?)|([0-9]*\.[0-9]+)))(\s*%)*$/',$val,$matches)) {
                $val = (float) $matches[1];
            }
        }
        if ( ! is_numeric($val)) {
            return false;
        }
        $do_small_percent_check = $this->optionsHasPath(array('meta',"check_bad_percent")) && $this->getOption(array('meta',"check_bad_percent"));
        if ( $do_small_percent_check &&  abs($val) < 1) {
            $val = 100.0*$val;
        }
        return $val;
    }

    public function setValueOfKey($key,$val) {
        if ( ($val = $this->extractPercent($val)) !== false) {
            parent::setValueOfKey($key,$val);
        }
    }
    
    protected function getSingleDisplayValue($val) {
        if ( ($val = $this->extractPercent($val)) !== false) {
            return $val . ' %';
        } else {
            return  ' %';
        }

    }


    /**
     * Return the value of this field from the database format for the given type
     * @param integer $type The type of the field to be returned.
     * @param mixed $value
     */
    public function getFromDB($value ) {
        $vals =  json_decode($value,true );
        if (!is_array($vals)) {
            return $vals;
        }
        foreach ($vals as &$val) {
            $val = $this->extractPercent($val);
        }
        unset($val);
        return $vals;
    }
        


    public function postprocessInput($node,$template,$form_node) {
        $validation = '';
        if ( $this->getOption('required') ) {
            $validation = ",{'nonempty':{}}";
        }
        $node->setAttribute('onchange','I2CE_InputFormatter.format(this,"percentage",1' . $validation .')');
    }





}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
