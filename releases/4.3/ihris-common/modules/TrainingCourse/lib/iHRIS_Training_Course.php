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
 * @package iHRIS
 * @subpackage Manage
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v1.0.0
 * @version v2.0.0
 */
/**
 * Object for dealing with classifications.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_Training_Course extends I2CE_List {
    



    public static function getAvailableCourses() {
        $status = I2CE_FormStorage::listFields('training_course_status','name');
        foreach ($status as $i=>&$s) {
            if (!array_key_exists('name',$s)) {
                unset($status[$i]);
                continue;
            }
            $s = $s['name'];
        }
        $t_filters = array();
        I2CE::getConfig()->setIfIsSet($t_filters,'/modules/training-course/filters/availableCourses',true);
        //this should be moved to magic data form storage!
        $filters = array();
        foreach ($t_filters as $val) {
            if (($key = array_search($val,$status)) !==false ) {
                $filters[] = $key;
            }
        }
        $where = array();
        if (count($filters) > 0) {
            $where = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'training_course_status',
                'style'=>'in',
                'data'=>array(
                    'value'=>$filters
                    )
                );
        }      
        $courses = I2CE_FormStorage::listFields('training_course','name',false,$where);  
        foreach ($courses as $i=>&$c) {
            if (!array_key_exists('name',$c)) {
                unset($courses[$i]);
                continue;
            }
            $c = $c['name'];
        }
        return $courses;
    }



    
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
