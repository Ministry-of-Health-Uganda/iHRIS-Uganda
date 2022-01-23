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
 */
/**
 * View the details for then given record that is an instance of a I2CE_List.
 * @package I2CE
 * @subpackage Common
 * @access public
 * @author Carl Leitner
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying a I2CE_List record.
 * @package I2CE
 * @subpackage Common
 * @access public
 */

class I2CE_PageFormParentDelete extends I2CE_Page {


    protected $primaryObject  = false;


    protected function getPrimary() {
        if ($this->primaryObject === false) {
	    $this->primaryObject =null;
            $ff = I2CE_FormFactory::instance();
	    if ( ($primary_form_name = $this->getPrimaryFormName())) {
		$id = 0;
		$populate =false;
		if ($this->request_exists('id')) {
		    list($form,$id) = array_pad(explode('|',$this->request('id'),2),2,'');
		    if ($form != $primary_form_name) {
			return null;
		    }
		    $populate = true;
		}
		$this->primaryObject= $ff->createContainer(array($primary_form_name,$id));
		if ($populate) {
		    $this->primaryObject->populate();
		}
	    }
        }
        return $this->primaryObject;
    }


    protected function getPrimaryFormName() {
        if (!array_key_exists('primary_form',$this->args) 
            || !is_scalar($this->args['primary_form']) 
            || ! $this->args['primary_form']) {
            I2CE::raiseError("No primary form set");
            return false;
        }
        return $this->args['primary_form'];
    }



    protected function action() {
	if (!($primaryObject = $this->getPrimary()) instanceof I2CE_Form) {
	    I2CE::raiseError("No primary object");
	    return false;
	}
	$parent_form_id = $primaryObject->getParent();
	$primaryObject->delete();
	if (array_key_exists('return_link',$this->args)) {
	    $this->setRedirect(  $this->args['return_link'] . $parent_form_id);
	} else {
	    $this->setRedirect(  '/');
	}
	return true;
    }





}


