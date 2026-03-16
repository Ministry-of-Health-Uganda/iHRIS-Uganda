<?php 
/**
 * © Copyright 2008, 2009 IntraHealth International, Inc.
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
 */
/**
 *  iHRIS_Module_ManageJob
 * @package iHRIS
 * @subpackage Manage
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @version 3.2.3
 * @since 3.2.3
 * @access public
 */


class iHRIS_Module_ManageJob extends I2CE_Module {

    /**
     * @var boolean A flag to determine if migrate needs to be called during the upgrade method.
     */
    protected $do_migrate;
    /**
     * @var boolean A flag to determine if migrate326 needs to be called during the upgrade method.
     */
    protected $do_migrate326;

    /**
     * Perform any pre migrate actions for this module.
     * This is for going from pre 3.2 versions where benefit data has
     * been saved to the database.
     * @return boolean
     */
    protected function pre_migrate() {
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";
        I2CE_FormStorage::storeMigrateData( array( "job" => array( "salary_grade", "cadre", "classification" ), "salary_grade" => array( "start", "midpoint", "end" ), ),
                $migrate_path );
        $this->do_migrate = true;
        return true;
    }
    /**
     * Perform any pre migrate actions for this module from 3.2.5 to 3.2.6.
     * This is for going from pre 3.2 versions where benefit data has
     * been saved to the database.
     * @return boolean
     */
    protected function pre_migrate326() {
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";
        I2CE_FormStorage::storeMigrateData( array( "salary_grade" => array( "start", "midpoint", "end" ), ),
                $migrate_path );
        $this->do_migrate326 = true;
        return true;
    }


    /**
     * Perform the migrate actions for this module
     * This is for going from pre 3.2 versions where benefit data has
     * been saved to the database.
     * @return boolean
     */
    protected function migrate() {
        $user = new I2CE_User( 1, false, false, false );
        $class_config = I2CE::getConfig()->modules->forms->formClasses;
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";


        if ( !I2CE_FormStorage::migrateForm( "salary_grade", "entry", $user, $migrate_path, 
                    "code", array( "type" ), array( "start" => "currency",
                       "midpoint" => "currency", "end" => "currency" ) ) ) {
            return false;
        }

        if ( !I2CE_FormStorage::migrateForm( "cadre", "entry", $user, $migrate_path, 
                    false, array( "type" ) ) ) {
            return false;
        }

        if ( !I2CE_FormStorage::migrateForm( "classification", "entry", $user, $migrate_path, 
                    false, array( "type" ) ) ) {
            return false;
        }

        if ( !I2CE_FormStorage::migrateForm( "job", "entry", $user, $migrate_path, 
                    "code", array( "type" ), array( "salary_grade" => "salary_grade",
                        "cadre" => "cadre", "classification" => "classification" ) ) ) {
            return false;
        }


        // If everything migrated correctly, then remove the unused fields.
        unset( $class_config->iHRIS_Job->fields->type );
        unset( $class_config->iHRIS_SalaryGrade->fields->type );
        unset( $class_config->iHRIS_Classification->fields->type );
        return true;
    }
    /**
     * Perform the migrate actions for this module from 3.2.5 to 3.2.6.
     * This is for going from pre 3.2 versions where benefit data has
     * been saved to the database.
     * @return boolean
     */
    protected function migrate326() {
        $user = new I2CE_User( 1, false, false, false );
        $class_config = I2CE::getConfig()->modules->forms->formClasses;
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";


        if ( !I2CE_FormStorage::migrateField( "salary_grade",
                    array( "start" => "currency", "midpoint" => "currency",
                        "end" => "currency" ),
                    $migrate_path, $user ) ) {
            return false;
        }
        return true;
    }


    /**
     * Method called before the module is enabled for the first time.
     * @param boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        /*
         * This module was split off from ihris-manage.
         * If any of the forms are defined in magic data then 
         * it needs to be migrated.
         */
        $config = I2CE::getConfig();
        $do_migrate = false;
        foreach( array( "salary_grade", "cadre", "classification", "job" ) as $check_form ) {
            if ( $config->is_parent( "/modules/forms/forms/$check_form" ) ) { 
                $storage = "entry";
                $config->setIfIsSet( $storage, "/modules/forms/forms/$check_form/storage" );
                $do_migrate |= ($storage == "entry");
            }   
        }   
        if ( $do_migrate ) {

            if ( !$this->pre_migrate() ) {
                return false;
            }
            $this->force_upgrade_from_0 = true;
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
        } elseif ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.6' ) ) {
            /**
             * In 3.2.6 currency was updated so the salary_grade fields
             * need to be migrated.  This only needs to happen when going from
             * 3.2.5 to 3.2.6 otherwise the standard migrate for pre3.2.5 handles it.
             */
            if ( !$this->pre_migrate326() ) {
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
        if ( $this->do_migrate326 ) {
            if ( !$this->migrate326() ) {
                return false;
            }
        }
        return parent::post_update( $old_vers, $new_vers );
    }


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
