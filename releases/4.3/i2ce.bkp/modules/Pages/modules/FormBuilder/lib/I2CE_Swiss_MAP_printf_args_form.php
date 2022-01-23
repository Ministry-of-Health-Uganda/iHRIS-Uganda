<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
* it under the terms of the GNU General Public License as published by 
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
* @package i2ce
* @subpackage form-builder
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_MAPPED_orders
* 
* @access public
*/


class I2CE_Swiss_MAP_printf_args_form extends I2CE_Swiss_field_orders {


    public function getFormName() {
        return $this->name;
    }


    public function processValues($vals) {
        $fields = $this->getFieldNames();

        if (array_key_exists('display_order',$vals)
            && is_scalar($vals['display_order'])
            && is_array($display_order = explode(',',$vals['display_order']))
            && array_key_exists('enabled',$vals)
            && is_array($enabled = $vals['enabled'])) {
            $orders = array();
            foreach ($display_order as $field) {
                if (!array_key_exists($field,$enabled)
                    || !$enabled[$field]
                    || !in_array($field,$fields)
                    ){
                    continue;
                }
                $orders[] = $field;
            }

        }
        if (is_array($orders)) {
            $this->storage->eraseChildren();
            $this->storage->setValue( $orders);
        }
        return true;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
