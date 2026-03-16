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
 * Object for dealing with contacts.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_Contact extends I2CE_Form {

    /**
     * Constant values for contact types: Personal.
     */
    const TYPE_PERSONAL = 1;
    /**
     * Constant values for contact types: Work.
     */
    const TYPE_WORK = 2;
    /**
     * Constant values for contact types: Emergency.
     */
    const TYPE_EMERGENCY = 3;
    /**
     * Constant values for contact types: Other.
     */
    const TYPE_OTHER = 4;
    /**
     * Constant values for contact types: Facility.
     */
    const TYPE_FACILITY = 5;

    /**
     * @var array The list of contact types.
     */
    static protected $types = array( self::TYPE_PERSONAL => 'Personal', self::TYPE_WORK => 'Work', 
                                   self::TYPE_EMERGENCY => 'Emergency', self::TYPE_OTHER => 'Other', 
                                   self::TYPE_FACILITY => 'Facility' );


    
    /**
     * Lookup the given value from the status array.
     * @param integer $id
     * @param string $form Not used for this method.
     * @return string
     */
    static public function lookupType( $id, $form="" ) {
        return I2CE_Form::lookupArray( $id, self::$types );
    }
    /**
     * List all the options from the status array.
     * 
     * The facility type isn't displayed since that's only used for facility contact info and
     * is automatically set on those.
     * @param string $form The  form we wish to lookup by
     * @returns array
     */
    static public function listTypeOptions($form) {
        return  self::$types;
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
