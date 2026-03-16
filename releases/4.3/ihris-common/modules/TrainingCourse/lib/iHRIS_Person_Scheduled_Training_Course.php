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

class iHRIS_Person_Scheduled_Training_Course extends I2CE_Form {
  function populate($repopulate = false) {
       parent::populate($repopulate ); //this will do the default population e.g. read it from the entry tables
       //now we can try and set the value of the average field
       if (! ($aver = $this->getField('average')) instanceof I2CE_FormField_INT) {
            //I2CE::raiseError("Could not get average field")
            return;   
        }
       $calc = $this->getAverageScore();
       $aver->setFromDB( (int) $calc);        
    }
    
    public function getAverageScore() {
        if (! I2CE_ModuleFactory::instance()->isEnabled('training-exam')) {
            //the training exam module was not enabled... so let's not calculate anything
            return 0;    
        }
        //we want to get all child  training_course_exam and calculate an average from them
        $count = 0;
        $score = 0;
        $this->populateChildren('training_course_exam');
        foreach ($this->getChildren('training_course_exam') as $examObj) {
            if (!$examObj instanceof  iHRIS_Training_Course_Exam
                || ! ($scoreObj = $examObj->getField('score')) instanceof I2CE_FormField_INT
                 ) {
                continue;   
            }    
            $count++;
            $score += $scoreObj->getDBValue();
        }
        if ($count > 0) {
            return (int) ($score/$count);
        }  else {
             return 0;
        } 
    }
  }

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
