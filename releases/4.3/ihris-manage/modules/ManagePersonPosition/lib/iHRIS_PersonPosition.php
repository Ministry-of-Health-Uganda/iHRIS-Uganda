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
 * Object for dealing with positions assigned to people.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PersonPosition extends I2CE_Form {

    /**
     * Return the record id and the parent id for the person position record
     * for the given position.
     * @param $position of the form "postion|$id"
     * @return array
     */
    static public function getIds( $position ) {
        $where = array(
            'operator'=>'AND',
            'operand'=>array(
                0=>array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'position',
                    'style'=>'equals',
                    'data'=>array(
                        'value'=>$position
                        )
                    ),
                1=>array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'end_date',
                    'style'=>'null',
                    'data'=>array()
                    ),
                )                
            );
        $pers_pos = I2CE_FormStorage::listFields('person_position',array(),true,$where,array('-start_date'),1);        
        if (!is_array($pers_pos) || count($pers_pos) !== 1) {
            return array();
        }        
        reset($pers_pos);
        $id = key($pers_pos);
        $pers_pos = $pers_pos[$id];
        if (is_array($pers_pos) && array_key_exists('parent',$pers_pos)) {
            $parent = $pers_pos['parent'];
        } else {
            $parent = false;
        }
        return array('record'=>$id,'parent'=>$parent);
    }





    /**
     * Constant value for employed
     */
    const STATUS_EMPLOYED =1;
    /**
     * Constant value for not employed
     */
    const STATUS_NOT_EMPLOYED = 2;
    /**
     * @var array Employment Status
     */
    static public $stati = array( self::STATUS_EMPLOYED => 'Employed', self::STATUS_NOT_EMPLOYED => 'Not Employed' );


    /**
     * Lookup the given value from the gender array.
     * @param integer $id
     * @param string $form Not used for this method.
     * @return string
     */
    static public function lookupStatus( $id, $form="" ) {
        return I2CE_Form::lookupArray( $id, self::$stati );
    }
    /**
     * List all the options from the gender array.
     * @param string $form The  form we wish to lookup by
     * @returns array
     */
    static public function listStatusOptions($form ) {
        return  self::$stati ;
    }
        
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
