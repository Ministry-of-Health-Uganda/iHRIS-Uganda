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
* @package iHRIS
* @subpackage Manage
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2.6
* @since v3.2.6
* @filesource 
*/ 
/** 
* Class iHRIS_Module_Benefit
* 
* @access public
*/


class iHRIS_Module_Benefit extends I2CE_Module {

    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'validate_form_benefit' => 'validate_form_benefit',
                );
    }

    /**
     * Do extra validation for the benefit form.
     * @param I2CE_Form $form
     */
    public function validate_form_benefit( $form ) {
        if ( $form->start_date->isValid() && $form->end_date->isValid() ) {
            if ( $form->start_date->compare( $form->end_date ) < 1 ) {
                $form->setInvalidMessage('end_date','bad_date'); 
            }
        }
    }
    


    /**
     * @var boolean A flag to determine if migrate needs to be called during the upgrade method.
     */
    protected $do_migrate;
    /**
     * @var boolean A flag to determine if migrate329 needs to be called during the upgrade method.
     */
    protected $do_migrate329;

    /**
     * Perform any pre migrate actions for this module.
     * This is for going from pre 3.2 versions where benefit data has
     * been saved to the database.
     * @return boolean
     */
    protected function pre_migrate() {
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";
        I2CE_FormStorage::storeMigrateData( array( "benefit" => array( "type", "source", "recurrence", "amount" ) ),
                $migrate_path );
        $this->do_migrate= true;
        return true;
    }

    /**
     * Perform any pre migrate actions for this module to upgrade from 3.2.8 to 3.2.9.
     * This is for going from pre 3.2 versions where benefit data has
     * been saved to the database.
     * @return boolean
     */
    protected function pre_migrate329() {
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";
        I2CE_FormStorage::storeMigrateData( array( "benefit" => array( "amount" ) ),
                $migrate_path );
        $this->do_migrate329 = true;
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

        $constant_recur = array( 1 => 'benefit_recurrence|once', 2 => 'benefit_recurrence|weekly',
                3 => 'benefit_recurrence|monthly', 4 => 'benefit_recurrence|yearly' );

        foreach( $constant_recur as $old_id => $new_id ) {
            $migrate_node->forms->benefit_recurrence->$old_id = $new_id;
        }

        if ( !I2CE_FormStorage::migrateForm( "benefit_type", "entry", $user, $migrate_path, 
                    false, array( "type" ) ) ) {
            return false;
        }

        if ( !I2CE_FormStorage::migrateField( "benefit", 
                    array( "type" => "benefit_type",
                        "source" => "salary_source",
                        "amount" => "currency",
                        "recurrence" => "benefit_recurrence" ),
                    $migrate_path, $user ) ) {
            return false;
        }
        return true;

    }

    /**
     * Perform the migrate actions for this module from 3.2.8 to 3.2.9
     * This is for going from pre 3.2 versions where benefit data has
     * been saved to the database.
     * @return boolean
     */
    protected function migrate329() {
        $user = new I2CE_User( 1, false, false, false );
        $class_config = I2CE::getConfig()->modules->forms->formClasses;
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";

        if ( !I2CE_FormStorage::migrateField( "benefit", 
                    array( "amount" => "currency" ),
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
        foreach( array( "benefit", "benefit_type" ) as $check_form ) {
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
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.8' ) ) {
            /**
             * In 3.2.3 some lists were moved to magicdata storage so we need to save
             * any old record ids for the old lists for later reference before any field
             * types get changed in magic data.
             */
            if ( !$this->pre_migrate() ) {
                return false;
            }
        } elseif ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.9' ) ) {
            /**
             * In 3.2.9 currency was updated so the benefit amount needs to be migrated.
             * This only needs to happen if the previous upgrade to 3.2.8 has
             * already happened.  Otherwise that upgrade will include the currency info.
             */
            if ( !$this->pre_migrate329() ) {
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
        if ( $this->do_migrate329 ) {
            if ( !$this->migrate329() ) {
                return false;
            }
        }

        return parent::post_update( $old_vers, $new_vers );
    }



    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_benefit' => 'action_benefit'
            );
    }



    public function action_benefit($page) {
        if (!$page instanceof iHRIS_PageView) {
            return false;
        }
        return $page->addChildForms('benefit'); 
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
