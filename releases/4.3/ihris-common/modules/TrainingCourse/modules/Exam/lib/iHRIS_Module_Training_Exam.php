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

class iHRIS_Module_Training_Exam extends I2CE_Module {

    public function upgrade($old_vers,$new_vers) {
      if (I2CE_Validate::checkVersion($old_vers,'<','4.1.12.3')) {     //CHECK THAT THIS IS CORRECT VERSION
        if (! $this->resaveTrainings()) {
            return false;
        }
      }
      return true;
    }


    protected function resaveTrainings() {
    
      $user = new I2CE_User();
      $ff =   I2CE_FormFactory::instance();
      $ids = I2CE_FormStorage::search('person_scheduled_training_course');
      foreach ($ids as $id) {
         I2CE::longExecution( ); //to make sure we don't time out
        if (!  ( $pstc = $ff->createContainer(array('person_scheduled_training_course',$id))) 
          instanceof iHRIS_Person_Scheduled_Training_Course) {
          return false; //something is wrong
        }
        $pstc->populate(); //populate will recacluate the average field
        $pstc->save($user); //saves the new calculated average field to the databse
        $pstc->cleanup();  //free up memory
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
