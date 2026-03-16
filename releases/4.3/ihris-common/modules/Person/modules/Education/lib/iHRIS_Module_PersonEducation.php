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
*
* @package ihris-common
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2
* @since v3.2
* @filesource
*/
/**
* Class iHRIS_Module_PersonEducation
*
* @access public
*/


class iHRIS_Module_PersonEducation extends I2CE_Module {


    /**
     * @var bolean A flag to determine if migrate needs to be called during the upgrade method.
     */
    protected $do_migrate;

    /**
     * Run the pre migrate for this module.  This can use the old config data before it
     * has been changed from the config.
     * @return boolean
     */
    protected function pre_migrate() {
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";
        I2CE_FormStorage::storeMigrateData( array( "education" => array( "edu_type", "degree" ),
                    "degree" => array( "edu_type" ) ),
                $migrate_path );
        $this->do_migrate = true;
        return true;
    }

    /**
     * Perform the migrate actions for this module.
     * @return boolean
     */
    protected function migrate() {
            $user = new I2CE_User( 1, false, false, false );
            $class_config = I2CE::getConfig()->modules->forms->formClasses;
            $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";

            if ( !I2CE_FormStorage::migrateForm( "edu_type", "entry", $user, $migrate_path, 
                        false, array( "type" ) ) ) {
                return false;
            }
            if ( !I2CE_FormStorage::migrateForm( "degree", "entry", $user, $migrate_path, 
                        false, array( "type" ), array( "edu_type" => "edu_type" ) ) ) {
                return false;
            }

            if ( !I2CE_FormStorage::migrateField( "education", 
                        array( "degree" => "degree", ),
                        $migrate_path, $user ) ) {
                return false;
            }

            unset( $class_config->iHRIS_Degree->fields->type );
            unset( $class_config->iHRIS_Education->fields->edu_type );
            return true;
    }

    /**
     * Method called before the moduled is enabled for the first time.
     * @return boolean
     */
    public function action_initialize() {
        /**
         * This module was split off from ihris-manage.
         * If any of the forms are defined in magic data then 
         * it needs to be migrated.
         */
        $config = I2CE::getConfig();
        $do_migrate = false;
        foreach( array( "edu_type", "degree", "education" ) as $check_form ) {
            if ( $config->is_parent( "/modules/forms/forms/$check_form" ) ) {
                $do_migrate = true;
            }
        }
        if ( $do_migrate ) {
            if ( !$this->pre_migrate() ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Run the pre upgrade for this module.  This can use the old config data before it
     * has been changed from the config.
     * @param string $old_vers
     * @param string $new_vers
     * @param I2CE_MagicDataNode $new_storage
     * @return boolean
     */
    public function pre_upgrade( $old_vers, $new_vers, $new_storage ) {
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.5' ) ) {
            /**
             * In 3.2.3 some lists were moved to magicdata storage so we need to save
             * any old record ids for the old lists for later reference before any field
             * types get changed in magic data.
             */
            if ( !$this->pre_migrate() ) {
                return false;
            }
        }
        return parent::pre_upgrade( $old_vers, $new_vers, $new_storage );
    }

    /**
     * Upgrade this module if necessary
     * @param string $old_vers
     * @param string $new_vers
     * @return boolean
     */
    public function post_update( $old_vers, $new_vers ) {
        /*
         * In 3.2.3 we moved some lists from entry to magicdata storage so we need to get the
         * old data from entry and save them to the new form storage.
         */ 
        if ( $this->do_migrate ) {
            if ( !$this->migrate() ) {
                return false;
            }
        }
        return parent::post_update( $old_vers, $new_vers );
    }




    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_education' => 'action_education'
            );
    }


    public function action_education($obj) {
        if (!$obj instanceof iHRIS_PageView) {
            return;
        }
        $obj->addChildForms('education');
        return true;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
