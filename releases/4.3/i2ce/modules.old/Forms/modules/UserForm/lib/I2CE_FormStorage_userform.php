<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @package i2ce
* @subpackage userform
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.7
* @since v4.0.7
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_userform
* 
* @access public
*/


class I2CE_FormStorage_userform extends I2CE_FormStorage_Mechanism {
    
    /**
     * Checks to see if this is writalbe
     * @returns boolean
     */
    public function isWritable() {
        return ($this->userAccess->canEditUserDetails() && $this->userAccess->canCreateNewUser() && $this->userAccess->canChangePassword());
    }

    /**
     * @var protected I2CE_UserAccess_Mechansim $userAccess
     */
    protected $userAccess;
    /**
     * Construct this module class
     * @param string $name The name of this storage mechanism
     * @param array $options
     */
    public function __construct( $name, $options=array() ) {
        $this->userAccess= I2CE::getUserAccess();
        parent::__construct( $name, $options );
    }


    /**
     * Populate the member variables of the object from the database.
     * @param I2CE_Form $form
     */
    public function populate( $form) {
        //this shoudn't really be called b/c it is a fuzzy method which is already implemented in I2CE_User_Form
        if (!$form instanceof I2CE_User_Form) {
            return ;
        }
        $form->populate();
    }
    

    
    /**
     * Return an array of all the record ids for a given form.
     * @param string $form
     * @param integer $mod_time.  Defaults to  -1.  If non-negative, it is a unix timestamp and we retrieve records modified at or after the given time
     * @param string $parent Defaults to false.  If true, The parent field we want to restrict values to
     * @return array
     */
    public function getRecords( $form,$mod_time = -1, $parent = false) {
        if ($mod_time < 0) {
            return $this->userAccess->getUsersByInfo();
        } else {
            //Needs to be done.
            return $this->userAccess->getUsersByInfo();

        }
    }

    

  }

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
