<?php
/*
 * © Copyright 2013 IntraHealth International, Inc.
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
 * Manage adding or editing forms associated with a training to the database.
 * 
 * @package iHRIS
 * @subpackage Train
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2013 IntraHealth International, Inc. 
 * @since v4.1.5
 * @version v4.1.5
 */

/**
 * Page object to handle the adding or editing forms associated with a training to the database.
 * 
 * @package iHRIS
 * @subpackage Train
 * @access public
 */

class iHRIS_Training_Course_Exam extends I2CE_Form {
  public function save($user, $transact = true) {
    if (!parent::save($user,$transact)) {
      return false;   
    }   
    if ( ! ($parentObj = I2CE_FormFactory::instance()->createContainer($this->getParent())) 
         instanceof iHRIS_Person_Scheduled_Training_Course) {
        //no parent form.
       return true;
      }
    $parentObj->populate(); //this causes the average field in the person_scheduled_training_course to be calculate
    return $parentObj->save($user,$transact);
  }        
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
