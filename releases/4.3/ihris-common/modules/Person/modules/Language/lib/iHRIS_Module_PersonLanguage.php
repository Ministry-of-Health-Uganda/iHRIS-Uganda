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
* Class iHRIS_Module_PersonLanguage
*
* @access public
*/


class iHRIS_Module_PersonLanguage extends I2CE_Module {

    /**
     * @ var boolean A flag to determine if migrate needs to be called during the upgrade method.
     */
    protected $do_migrate;

    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_person_language' => 'action_person_language'
            );
    }


    public function action_person_language($obj) {
        if (!$obj instanceof iHRIS_PageView) {
            return;
        }
        return $obj->addChildForms('person_language');
    }

    /** 
     * Perform any pre migrate actions for this module.
     * This is for going from pre 3.2 versions where benefit data has
     * been saved to the database.
     * @return boolean
     */
    protected function pre_migrate() {
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";
        I2CE_FormStorage::storeMigrateData( array( "person_language" => array( "language", 
                        "speaking", "reading", "writing" ) ),
                $migrate_path );
        $this->do_migrate= true;
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

        $migrate_node = I2CE::getConfig()->traverse( $migrate_path, true, false );

        $constant_prof = array( 1 => 'language_proficiency|elementary', 
                2 => 'language_proficiency|limited', 3 => 'language_proficiency|professional',
                4 => 'language_proficiency|full_professional', 5 => 'language_proficiency|fluent',
                );
        foreach( $constant_prof as $old_id => $new_id ) {
            $migrate_node->forms->language_proficiency->$old_id = $new_id;
        }

        if ( !I2CE_FormStorage::migrateForm( "language", "entry", $user, $migrate_path, 
                    false, array( "type" ) ) ) {
            return false;
        }

        if ( !I2CE_FormStorage::migrateField( "person_language", 
                    array( "language" => "language",
                        "speaking" => "language_proficiency",
                        "reading" => "language_proficiency",
                        "writing" => "language_proficiency", ),
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
        foreach( array( "language", "person_language" ) as $check_form ) {
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




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
