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
* Class iHRIS_Module_PersonDemographic
*
* @access public
*/


class iHRIS_Module_Person extends I2CE_Module {


    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'validate_form_person' => 'validate_form_person',
                );
    }

    /**
     * Perform extra validation for the person form.
     * A new person record needs to verify there aren't any existing 
     * records with the same name.
     * @param I2CE_Form $form
     */
    public function validate_form_person( $form ) {

        $search = array();
        $surname_ignore = false;
        if ( isset( $form->surname_ignore ) ) {
            $surname_ignore = $form->surname_ignore;
        }
        if ( I2CE_ModuleFactory::instance()->isEnabled('forms-storage') 
             && $form->getId() == '0' && !$surname_ignore
             && I2CE_Validate::checkString( $form->surname ) 
             && I2CE_Validate::checkString( $form->firstname ) ) {
            $where = array(
                'operator' => 'AND',
                'operand'=>array(
                    0=>array(
                        'operator'=>'FIELD_LIMIT',
                        'field'=>'surname',
                        'style'=>'lowerequals',
                        'data'=>array('value'=>strtolower($form->surname))
                        ),
                    1=>array(
                        'operator'=>'FIELD_LIMIT',
                        'field'=>'firstname',
                        'style'=>'lowerequals',
                        'data'=>array('value'=>strtolower($form->firstname))
                        )
                    )
                );
            $results = I2CE_FormStorage::listFields('person',array('surname','firstname'),false,$where,array('surname','firstname'));
            if( count($results) > 0 ) {
                foreach ($results as $id=>&$data) {
                    $data = implode(', ', $data);
                }
                $form->setInvalidMessage('surname','unique', array( "view?id=" => $results ));
            }
        }
        
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
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.3' ) ) {
            /**
             * In 3.2.3 some lists were moved to magicdata storage so we need to save
             * any old record ids for the old lists for later reference before any field
             * types get changed in magic data.
             */
            $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";
            I2CE_FormStorage::storeMigrateData( array( "person" => array( "nationality", "residence_country",
                            "residence_district", "residence_county", "country", "district", "county" ) ),
                    $migrate_path );
        }
        return parent::pre_upgrade( $old_vers, $new_vers, $new_storage );
    }

    /**
     * Upgrade this module if necessary
     * @param string $old_vers
     * @param string $new_vers
     * @return boolean
     */
    public function upgrade( $old_vers, $new_vers ) {
        /*
         * In 3.2.3 we moved some lists from entry to magicdata storage so we need to get the
         * old data from entry and save them to the new form storage.
         */ 
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.3' ) ) {
            $user = new I2CE_User( 1, false, false, false );
            $class_config = I2CE::getConfig()->modules->forms->formClasses;
            $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";

            if ( !I2CE_FormStorage::migrateField( "person", 
                        array( "nationality" => "country",
                            "residence" => array( "residence_county" => "county", "county" => "county",
                                "residence_district" => "district", "district" => "district",
                                "residence_country" => "country", "country" => "country" ) ),
                        $migrate_path, $user ) ) {
                return false;
            }

            unset( $class_config->iHRIS_Person->fields->residence_country );
            unset( $class_config->iHRIS_Person->fields->residence_district );
            unset( $class_config->iHRIS_Person->fields->residence_county );

        }

        return parent::upgrade( $old_vers, $new_vers );
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
