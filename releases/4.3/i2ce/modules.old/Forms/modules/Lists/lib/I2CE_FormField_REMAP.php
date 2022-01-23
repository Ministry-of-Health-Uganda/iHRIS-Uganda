<?php
/**
* © Copyright 2013 IntraHealth International, Inc.
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
* @package I2CE
* @subpackage List
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1.6
* @since v4.1.6
* @filesource 
*/ 
/** 
* Class I2CE_FormField_REMAP
* 
* @access public
*/


class I2CE_FormField_REMAP extends I2CE_FormField_MAP{


    /**
     * Checks to see which forms this form can map to.
     * @returns array()
     */
    public function getSelectableForms() {            
	return array($this->getContainer()->getName());
    }

    /**
     *@returns array where keys are ids, values are arrays with the following keys 'value', 'display'
     */
    public function getMapOptions($type='default', $show_hidden= 0,$flat = true,$add_limits = array()) {
	$form = $this->getContainer()->getName();
	$ff = I2CE_FormFactory::instance();
        $data = array();
        $orders = $this->getFormOrders($type);
        if (array_key_exists($form,$orders)
            && is_array($orders[$form])
            ) {
            $orders= $orders[$form];
        } else {
            $orders= array();
        }
	foreach (I2CE_FormStorage::search($form,false,array(),$orders) as $id) {
	    if ( ! ($formObj = $ff->createContainer($form . '|' . $id)) instanceof I2CE_List) {
		continue;
	    }
	    $formObj->populate();
	    $data[]  = array(
		'display'=>$formObj->name() . ' [['. $id . ']]',
		'value' => "$form|$id"
		);
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
