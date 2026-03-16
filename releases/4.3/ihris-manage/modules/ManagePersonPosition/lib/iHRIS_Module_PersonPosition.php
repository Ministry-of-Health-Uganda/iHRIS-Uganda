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
* Class iHRIS_Module_PersonPosition
* 
* @access public
*/


class iHRIS_Module_PersonPosition extends I2CE_Module{

    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'validate_form_person_position' => 'validate_form_person_position',
                );
    }

    /**
     * Checks to make sure the end date is after the start date for the person position.
     * @param I2CE_Form $form
     */
    public function validate_form_person_position( $form ) {
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
     * @var boolean A flag to determine if migrate3212 needs to be called during the upgrade method.
     */
    protected $do_migrate3212;


    /**
     * Perform any pre migrate actions for this module.
     * This is for going from pre 3.2 versions where benefit data has
     * been saved to the database.
     * @return boolean
     */
    protected function pre_migrate() {
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";
        I2CE_FormStorage::storeMigrateData( array( "person_position" => array( "position", "reason" ),
                    "position" => array( "job", "source", "supervisor", "facility", "department", "pos_type", "status", "proposed_salary" ),
                    ),
                $migrate_path );
        $this->do_migrate = true;
        return true;
    }
    /**
     * Perform any pre migrate actions for this module from 3.2.10 to 3.2.12
     * This is for going from pre 3.2 versions where benefit data has
     * been saved to the database.
     * @return boolean
     */
    protected function pre_migrate3212() {
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";
        I2CE_FormStorage::storeMigrateData( array( "position" => array( "proposed_salary" ),
                    ),
                $migrate_path );
        $this->do_migrate3212 = true;
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

        $constant_status = array( 1 => 'position_status|open', 
                2 => 'position_status|closed', 3 => 'position_status|discontinued' );
        foreach( $constant_status as $old_id => $new_id ) {
            $migrate_node->forms->position_status->$old_id = $new_id;
        }

        if ( !I2CE_FormStorage::migrateForm( "pos_change_reason", "entry", $user, $migrate_path, 
                    false, array( "type" ) ) ) {
            return false;
        }
        if ( !I2CE_FormStorage::migrateForm( "position_type", "entry", $user, $migrate_path, 
                    false, array( "type" ) ) ) {
            return false;
        }
        if ( !I2CE_FormStorage::migrateForm( "department", "entry", $user, $migrate_path, 
                    false, array( "type" ) ) ) {
            return false;
        }


        if ( !I2CE_FormStorage::migrateField( "person_position", 
                    array( "position" => "position",
                        "reason" => "pos_change_reason" ),
                    $migrate_path, $user ) ) {
            return false;
        }
        unset( $class_config->iHRIS_Position->fields->status->default_eval );
        if ( !I2CE_FormStorage::migrateField( "position", 
                    array( "job" => "job",
                        "source" => "salary_source",
                        "supervisor" => "position",
                        "facility" => "facility",
                        "department" => "department",
                        "pos_type" => "position_type",
                        "proposed_salary" => "currency", 
                        "status" => "position_status" ),
                    $migrate_path, $user ) ) {
            return false;
        }


        // If everything migrated correctly, then remove the unused fields.
        unset( $class_config->iHRIS_Departent->fields->type );
        unset( $class_config->iHRIS_Position->fields->type );

        return true;
    }
    /**
     * Perform the migrate actions for this module from 3.2.10 to 3.2.12
     * This is for going from pre 3.2 versions where benefit data has
     * been saved to the database.
     * @return boolean
     */
    protected function migrate3212() {
        $user = new I2CE_User( 1, false, false, false );
        $class_config = I2CE::getConfig()->modules->forms->formClasses;
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";


        if ( !I2CE_FormStorage::migrateField( "position", 
                    array( "proposed_salary" => "currency", ),
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
        foreach( array( "pos_change_reason", "position_type", "department", "position", "person_position" ) as $check_form ) {
            if ( $config->is_parent( "/modules/forms/forms/$check_form" ) ) { 
                $do_migrate = true;
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
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.10' ) ) {
            /**
             * In 3.2.3 some lists were moved to magicdata storage so we need to save
             * any old record ids for the old lists for later reference before any field
             * types get changed in magic data.
             */
            if ( !$this->pre_migrate() ) {
                return false;
            }
        } elseif ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.12' ) ) {
            /**
             * Currency was update in 3.2.12 so it needs to be migrated when
             * going from 3.2.10 to 3.2.12 otherwise the default migrate
             * will work.
             */
            if ( !$this->pre_migrate3212() ) {
                return false;
            }
       }
        return parent::pre_upgrade( $old_vers, $new_vers, $new_storage );
    }

    /**
     * Run the upgrade function for this module.
     * @param string $old_vers
     * @param string $new_vers
     * @return boolean
     */
    public function upgrade( $old_vers, $new_vers ) {
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.22' ) ) {
            I2CE::getConfig()->__unset( "/I2CE/page/position" );
        }
        if ( !$this->do_migrate && I2CE_Validate::checkVersion( $old_vers, '<', '3.2.24' ) ) {
            $user = new I2CE_User( 1, false, false, false );
            $class_config = I2CE::getConfig()->modules->forms->formClasses;
            $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";

            if ( !I2CE_FormStorage::migrateField( "position", 
                        array( "facility" => "facility", ),
                        $migrate_path, $user ) ) {
                return false;
            }
        }
        return parent::upgrade( $old_vers, $new_vers );
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
        if ( $this->do_migrate3212 ) {
            if ( !$this->migrate3212() ) {
                return false;
            }
        }
        return parent::post_update( $old_vers, $new_vers );
    }

    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_person_position' => 'action_person_position',
            'iHRIS_Person->getLastPosition' => 'getLastPosition',
            'iHRIS_Person->isActive' => 'isActive_Person',
            'iHRIS_PersonPosition->isActive' => 'isActive_PersonPosition'
            );
    }


    /**
     * Checks to see if the most recent position for this person is still active and doesn't have an end date
     * for his/her position.
     * @see getLastPosition()
     * @return boolean
     */
    public function isActive_Person($person) {
        return $this->isActive_PersonPosition($this->getLastPosition($person));
    }



    /**
     * Checks to see if the most recent position for this person is still active and doesn't have an end date
     * for his/her position.
     * @see getLastPosition()
     * @return boolean
     */
    public function isActive_PersonPosition($pers_pos) {
        if ( !$pers_pos instanceof iHRIS_PersonPosition) {
            return false;
        }
        if ( isset( $pers_pos->end_date ) && $pers_pos->end_date instanceof I2CE_Date ) {
            if ( $pers_pos->end_date->equals( I2CE_Date::blank() ) ) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }


    /**
     * Find and return the most recent position held by this person.
     * 
     * @return I2CE_Form or null
     */
    public function getLastPosition($person) {
        if (!$person instanceof iHRIS_Person) {
            return null;
        }
        $where = array(
            'operator'=>'FIELD_LIMIT',
            'field'=>'end_date',
            'style'=>'null'
            );
        $per_pos_id = I2CE_FormStorage::search('person_position', $person->getNameId(),$where,'-start_date',1);
        if (!$per_pos_id) {
            return null;
        }
        $pos = I2CE_FormFactory::instance()->createContainer('person_position'.'|'.$per_pos_id);
        if (!$pos instanceof iHRIS_PersonPosition) {
            return null;
        }
        $pos->populate();
        return $pos;
    }


    public function action_person_position($page) {
        if (!$page instanceof iHRIS_PageView) {
            return false;
        }
        $person = $page->getPerson();
        if (!$person instanceof iHRIS_Person) {
            return false;
        }
        $template=$page->getTemplate();
        if (  ($lastPos = $this->getLastPosition($person)) instanceof iHRIS_PersonPosition) {
            $person->addChildForm($lastPos);
            $childNode = $page->appendChildTemplate('person_position','siteContent');
        } else {
            $childNode = $page->addLastChildForm('person_position','start_date', 'siteContent');
        }
        if (!array_key_exists('person_position',$person->children) || !is_array($person->children['person_position']) 
            || count($person->children['person_position']) != 1) {
            return true;
        }
        reset($person->children['person_position']);        
        $pers_pos = current($person->children['person_position']);        
        if ( !$pers_pos instanceof iHRIS_PersonPosition)  {
            return true;
        }
        $pers_pos->getField( "position" )->setHref( "view_position?id=" );
        return $childNode;
    }


    


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
