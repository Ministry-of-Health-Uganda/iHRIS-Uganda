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
 * @since v3.2.0
 * @version v3.2.0
 */
/**
 * Class for defining all the database fields used by a {@link I2CE_Form} object.
 * @package I2CE
 * @access public
 */
class I2CE_FormField_MAP_MULTUNION extends I2CE_FormField_MAP_MULT { 

    public function getDisplayedFields($type = 'default', $check_forms = true) {            
        $forms = $this->getSelectableForms();
        $union_fields = array();
        foreach ($forms as $form) {
            $union_fields[$form] = array($form);
        }
        return $union_fields;
    }

    /**
     *@returns array where keys are ids, values are arrays with the following keys 'value', 'display'
     */
    public function getMapOptions($type='default', $show_hidden= 0,$flat = true,$add_limits = array()) {
        $forms = $this->getSelectableForms();
        $union_fields = $this->getDisplayedFields($type);
        $limits = $this->getFormLimits($type);
        if (is_array($add_limits) && count($add_limits) > 0) {
            if (!is_array($limits) || count($limits) > 0) {
                $limits = $add_limits;
            } else{
                //need to go through each form and possibly merge limits
                foreach ($add_limits as $form=>$formLimits)  {
                    if (!array_key_exists($form,$limits) || !is_array($limits[$form]) || count($limits[$form]) == 0) {
                        $limits[$form] = $formLimits;
                    }  else {
                        $limits[$form] = array(
                            'operator'=>'AND',
                            'operand'=>array(0=>$limits[$form], 1=>$formLimits)
                            );
                    }
                }
            }
        }
        $orders = $this->getFormOrders($type);
        $data = array();
        foreach ($union_fields as $form=>$fields) {            
            $data = array_merge($data,I2CE_List::flattenDataTree(I2CE_List::buildDataTree( $fields,array($form),$limits, $orders, $show_hidden)));
        }
        return $data;
    }

    
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
