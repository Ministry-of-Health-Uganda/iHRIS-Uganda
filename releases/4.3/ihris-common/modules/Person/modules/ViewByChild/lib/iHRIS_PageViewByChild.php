<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 */
/**
 * View a person's record
 * @package iHRIS
 * @subpackage Common
 * @access public
 * @author Jeddu Villatoro <jvillatoro@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v4.1.9
 * @version v4.1.9
 */

/**
 * The page class for displaying the a person's record.
 * @package iHRIS
 * @subpackage Common
 * @access public
 */
class iHRIS_PageViewByChild extends I2CE_Page { 

    /**
     * Perform any actions for the page
     * 
     * @return boolean.  true on success
     */
	public function action() {
		if (!$this->get_exists("id")){
			$this->redirect("home");
			return true;
		}

		$child = I2CE_FormFactory::instance()->createContainer($this->get("id"));
		if (!$child instanceof I2CE_Form){
			$this->redirect("home");
			return true;
		}
		$child->populate();
		if ($child->getParentForm() != "person"){
			$this->redirect("home");
			return true;
		}
		$this->redirect("view?id=".$child->getParent());
		return true;
	}



}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
