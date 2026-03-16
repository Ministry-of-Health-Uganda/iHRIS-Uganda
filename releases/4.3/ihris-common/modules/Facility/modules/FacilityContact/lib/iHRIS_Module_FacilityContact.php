<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
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
* @package I2CE
* @subpackage common
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2.6
* @since v3.2.6
* @filesource 
*/ 
/** 
* Class iHRIS_Module_FacilityContact
* 
* @access public
*/


class iHRIS_Module_FacilityContact extends I2CE_Module {

    /**
     * Migrate the facility contact data from entry to the current storage (magicdata)
     */
    public function migrate() {
        $user = new I2CE_User( 1, false, false, false );
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";

        if ( !I2CE_FormStorage::migrateForm( "facility_contact", "entry", $user, $migrate_path ) ) { 
            return false;
        }
        return true;
    }

    /**
     * Upgrade this module if necessary
     * @param string $old_vers
     * @param string $new_vers
     * @return boolean
     */
    public function upgrade( $old_vers, $new_vers ) {
        /*
         * In 3.2.3 we moved some forms to magicdata so we need to get the old data from entry
         * and save it to the new form storage.
         */
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.6' ) ) {
            if ( !$this->migrate() ) {
                return false;
            }
        }
        return parent::upgrade( $old_vers, $new_vers );
    }


    /**
     * post updates the modules
     * @param string $old_vers
     * @param string $new_vers
     * @returns boolean

     * @returns boolean
     */
    public function post_update($old_vers,$new_vers) {
        if (I2CE_Validate::checkVersion($old_vers,'=','0')) { 
            //only fixup contact on a new installation
            if (!$this->fixupContact()) {
                return false;
            }
            // After fixing up the contact, we need to migrate 
            // the data to magic data storage.
            if ( !$this->migrate() ) {
                return false;
            }
        }
        return true;
    }



    
    protected function fixupContact() {
        $facilityObj = I2CE_FormFactory::instance()->createContainer('facility');
        if (!$facilityObj instanceof iHRIS_Facility) {
            I2CE::raiseError("Bad facility form");
            return false;
        }
        $childForms = $facilityObj->getChildForms();
        if (!in_array('contact',$childForms)) {
            return true;
        }
        I2CE::raiseError("Changing contact child forms of facility to facility_contact");
        if (! iHRIS_Module_Contact::changeContactForm('facility', 'contact_type|facility','facility_contact',true)) {
            I2CE::raiseError("Could not upgrade training funder contacts");
            return false;
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
