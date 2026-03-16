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
* @subpackage ihris-common
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2.4
* @since v3.2.4
* @filesource 
*/ 
/** 
* Class iHRIS_Module_TrainingInstiution
* 
* @access public
*/


class iHRIS_Module_TrainingInstitution extends I2CE_Module {

    /**
     * Upgrades the modules
     * @param string $old_vers
     * @param string $new_vers
     * @returns boolean
     */
    public function upgrade($old_vers,$new_vers) {
        if (I2CE_Validate::checkVersion($old_vers,'<','3.2.12')) { 
            I2CE::raiseError("Changing contact child forms of training_institution to training_institution_contact");
            if (! iHRIS_Module_Contact::changeContactForm('training_institution', 'contact_type|facility','training_institution_contact',true)) {
                I2CE::raiseError("Could not upgrade training instituion contacts");
                return false;
            }
        }
        if (I2CE_Validate::checkVersion($old_vers,'=','3.2.12') ) {
            //the changeContactForm did not remvoe the contact form before
            if (! iHRIS_Module_Contact::removeContactForm('training_institution')) {
                return false;
            }
        }
        if (I2CE_Validate::checkVersion($old_vers,'<','3.2.19')) { 
            I2CE::raiseError("Changing contact child forms of training_funder to training_funder_contact");
            if (! iHRIS_Module_Contact::changeContactForm('training_funder', 'contact_type|facility','training_funder_contact',true)) {
                I2CE::raiseError("Could not upgrade training funder contacts");
                return false;
            }
        }
        if (I2CE_Validate::checkVersion($old_vers,'<','3.2.20')) { 
            $user = new I2CE_User( 1, false, false, false );
            $class_config = I2CE::getConfig()->modules->forms->formClasses;
            $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";

            if ( !I2CE_FormStorage::migrateField( 
                     "training_institution", 
                     array("location" => 
                           array( "county" => "county", 
                                  "district" => "district", 
                                  "country" => "country" ) ),
                     $migrate_path, $user ) ) {
                return false;
            }
            if ( !I2CE_FormStorage::migrateField( 
                     "training_funder", 
                     array("location" => 
                           array( "county" => "county", 
                                  "district" => "district", 
                                  "country" => "country" ) ),
                     $migrate_path, $user ) ) {
                return false;
            }

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
