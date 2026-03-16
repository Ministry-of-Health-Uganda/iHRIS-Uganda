<?php
/**
* © Copyright 2008 IntraHealth International, Inc.
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
*  iHRIS_Module_Accident
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @copyright Copyright &copy; 2008 IntraHealth International, Inc. 
* This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
* the terms of the GNU General Public License as published by the Free Software Foundation; either 
* version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
* that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
* received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
* @version 2.1
* @access public
*/


class iHRIS_Module_Accident extends I2CE_Module {

    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_accident' => 'action_accident'
            );
    }

    /** 
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'validate_form_accident' => 'validate_form_accident',
                );  
    }   



    public function action_accident($obj) {
        if (!$obj instanceof iHRIS_PageView) {
            I2CE::raiseError("invalid call");
            return false;;
        }
        //do nothing so that an warning message is avoided
        return true;
    }


    /** 
     * Checks to make sure the end of applicability is after the start of applicability.
     * @param I2CE_Form $form
     */
    public function validate_form_accident( $form ) { 
        if ( $form->start_date->isValid() && $form->end_date->isValid() ) { 
            if ( $form->start_date->compare( $form->end_date ) < 0 ) { 
                $form->setInvalidMessage('end_date','bad_date');
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
        if (I2CE_Validate::checkVersion($old_vers,'<','4.0.0')) {
            //pre upgrade is called before magic data is updated from the module.
            //this will store the existiing INT accident_type to be referenced later in post upgrade
            if (! $this->preUpgradeAccidentTypeFields()) {
                return false;
            }
        }
        return true;
    }



    /**
     * Upgrade this module if necessary
     * @param string $old_vers
     * @param string $new_vers
     * @return boolean
     */
    public function upgrade($old_vers,$new_vers) {
        if (I2CE_Validate::checkVersion($old_vers,'<','4.0.0')) {
            //we have stored the INT accident type field no we can store it as a map
            if (! $this->postUpdateAccidentTypeFields()) {
                return false;
            }
        }
        return true;
    }



    public function showCurrentAccidents($node,$template) {
        return $this->showAccidents($node,$template,true);
    }

    public function showAllAccidents($node,$template) {
        return $this->showAccidents($node,$template,false);
    }


    protected function showAccidents($node,$template,$only_active) {
        $personForm = $template->getData('FORM','person',$node);
        if (!$personForm instanceof iHRIS_Person || $personForm->getId() < 1) {
            $node->parentNode->removeChild($node);
            return;
        }
        $node->removeAttribute('type');
        $node->removeAttribute('name');
        $accidents = iHRIS_Module_Accident::getAccidents($personForm,$only_active);
        foreach ($accidents as $accident) {
            $accident->populate();
            $actionNode = $template->appendFileByNode('view_accident.html','div',$node);
            if (!$actionNode instanceof DOMNode) {
                continue;
            }
            $template->setForm($accident,$actionNode);
        }

    }


    public static function getAccidents($person , $only_active = true) {
        $accidents = array();
        $factory = I2CE_FormFactory::instance();
        if (is_string($person)) {
            $person = $factory->createContainer('person|'.$person_id);
        }
        if (!$person instanceof iHRIS_Person) {
            return $accidents;
        }
        if ($only_active) {
            $where =array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'end_date',
                'style'=> 'greaterthan_equals',
                'data'=> I2CE_Date::now(I2CE_DATE::DATE)->getValues()
                );
        } else {
            $where = array();
        }
        $accidentIds = $person->getChildIds('accident','start_date',$where); //get all accidents actions in order of increasing start date
        $now = I2CE_Date::now();
        foreach ($accidentIds as $id) {
            $accident = $factory->createContainer('accident|'.$id);
            $accident->populate();
            if (!$accident instanceof iHRIS_Accident || $accident->getId() == '0') {
                continue;
            }
            $accidents[] = $accident;
        }
        return $accidents;
    }



    protected function preUpgradeAccidentTypeFields() {
        $migrate_path = "/I2CE/formsData/migrate_data/4.0";
        //first let us store the INT fields accident_type that we are updating in case there is an issue.
        I2CE_FormStorage::storeMigrateData( array( "accident" => array( "accident_type" ) ),  $migrate_path );
        //data is stored in $migrage_path/fields/accudent/$formid/accident_type = $int_value

        return true;
    }


    protected function postUpdateAccidentTypeFields() {
        $migrate_path = "/I2CE/formsData/migrate_data/4.0";
        $user = new I2CE_User( 1, false, false, false );
        $class_config = I2CE::getConfig()->modules->forms->formClasses;

        if ( !I2CE_FormStorage::migrateField( 
                 "accident", 
                 array( "accident_type" => "accident_type"),
                 $migrate_path, $user ) ) {
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
