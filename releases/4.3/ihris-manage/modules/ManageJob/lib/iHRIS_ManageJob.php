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
 * Object for dealing with jobs.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_ManageJob extends iHRIS_Job {


    /**
     * Return the list of record ids that have this position as supervisor.
     * @return array
     */
    public function getPositions() {
        $where_data = array(            
            'operator'=>'FIELD_LIMIT',
            'field'=>'job',
            'style'=>'equals',
            'data'=>array(
                'value'=>$this->getNameId()
                )
            );
        return  I2CE_FormStorage::search('position',false,$where_data);
    }
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
