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
class I2CE_FormField_ASSOC_BOOL extends I2CE_FormField_ASSOC_LIST { 





    public function setValueOfKey($key,$val) {
        if ( ! (is_int($val) || (is_string($val) &&  strlen(ltrim($val,'01')) == 0))) {
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
            $has_one = true;
            $valid &= (  is_int($val) || (is_string($val) && strlen($val) > 0 && strlen(ltrim($val,'01'))==0));
        }
        return $valid && $has_one;
    }
        
    public function keyIsSet($key) {
        return  is_array($this->value) 
            &&   array_key_exists($key,$this->value) 
            && (is_int($this->value[$key]) || (is_string($this->value[$key]) && strlen($this->value[$key]) > 0 && strlen(ltrim($val,'01'))==0));
    }



    public function processDOMEditable($node,$template,$form_node) {
        if (!$this->issetValue()) {
            return;
        }
        $name_base = $this->getHTMLName();
        $class = strtr($name_base,':','_');
        $qry = ".//input";
        if (! ($contNode = $template->loadFile('assoc_input_container.html','span')) instanceof DOMNode) {
            I2CE::raiseError("bad assoc_input_container.html");
            return false;
        }            
        $node->appendChild($contNode);
        if (! ($contNode = $template->getElementById('assoc_container',$contNode)) instanceof DOMNode) {
            I2CE::raiseError("No assoc_container node");
            return false;
        }
        if ($form_node->hasAttribute('input_class')) {
            $class = $form_node->getAttribute('input_class') . '_';
        } else {
            $class = 'input_';
        }
        $attrList = array();
        $attrs = array ('style');
        foreach ($attrs as $attr) {
            if (!$form_node->hasAttribute($attr)) {
                continue;
            }
            $attrList[$attr]  = $form_node->getAttribute($attr);
        }
        foreach ($this->value as $key=>$val) {
            if (! ($inpNode = $template->loadFile('assoc_bool_input.html','span')) instanceof DOMNode) {
                I2CE::raiseError("bad assoc_bool_input.html");
                return false;
            }            
            $name = $name_base . '[' . $key . ']';
            $template->setDisplayDataImmediate('key',$key,$inpNode);
            $template->setAttribute('name',$name,null,$qry,$inpNode);            
            $inputs = $template->query($qry,$inpNode);
            if ($inputs instanceof DOMNodeList) {
                foreach ($inputs as $input) {
                    if (!$input instanceof DOMElement) {
                        continue;
                    }
                    $exis_class = '';
                    if ($input->hasAttribute('class')) {
                        $exis_class = trim($input->getAttribute('class')) . ' ' ;
                    }        
                    $input->setAttribute('class',$exis_class . $class . $key);
                    if ($val == 1 ) {
                        $input->setAttribute('checked','checked');
                    }
                    $input->setAttribute('value',$this->getSingleDisplayValue($val));
                    foreach ($attrList as $attr=>$attrVal) {
                        $input->setAttribute($attr,$attrVal);
                    }
                }
            }
            $contNode->appendChild($inpNode);
        }
        $this->setElement($contNode);
    }





}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
