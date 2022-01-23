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
 *  I2CE_Module_Contact
 * @package iHRIS
 * @subpackage Common
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @author Carl Leitner <litlfred@intrahealth.org>
 * @version 3.2.3
 * @since 3.2.3
 * @access public
 */


class iHRIS_Module_Contact extends I2CE_Module {

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
            I2CE_FormStorage::storeMigrateData( array( "contact" => array( "contact_type" ) ),
                    $migrate_path );
        }
        return parent::pre_upgrade( $old_vers, $new_vers, $new_storage );
    }   


    /**
     *@var protected static $constant_migraate  array with keys the numeric ids
     *of contact types before the new I2CE_FormField_MAPPED was introduced.
     *The values are the corresponding id's after I2CE_FormField_MAPPED.
     */
    protected static     $constant_migrate = 
        array( 1 => 'contact_type|personal', 
               2 => 'contact_type|work',
               3 => 'contact_type|emergency', 
               4 => 'contact_type|other',
               5 => 'contact_type|facility' );
    

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
            //$class_config = I2CE::getConfig()->modules->forms->formClasses;
            $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";

            $migrate_node = I2CE::getConfig()->traverse( $migrate_path, true, false );


            foreach( self::$constant_migrate as $old_id => $new_id ) {
                $migrate_node->forms->contact_type->$old_id = $new_id;
            }

            if ( !I2CE_FormStorage::migrateField( "contact", array( "contact_type" => "contact_type" ), 
                        $migrate_path, $user ) ) {
                return false;
            }

        }
        return true;
    }

    /**
     * Change the all child contact form with given type of a form to a form which subclasses the contact form.

     * @param string $parent_form The parent form 
     * @param string  This function assumes that iHRIS_Module_Contact->upgrade() has been called for $new_vers >= 3.2.5
     * so that the $contact_type should be one of the values of {@link iHRIS_Module_Contact::$contact_type}.  E.g. 'contact_type|facilty'
     * @param string $new_child_form The new child form (which should subclass contact)  in which the existing values are to be saved.
     * @param boolean $delete_old.  If true the old child (matching) forms are removed
     * @param boolean $remove_contact.  Defaults to true in which case we remove 'contact' as a child form
     * @returns boolean.  True on success.
     */
    public  static function changeContactForm($parent_form,$contact_type, $new_child_form, $delete_old, $remove_contact = true) {
        if (!I2CE_ModuleFactory::instance()->isEnabled('forms-storage')) {
            return true;
        }
        if (!in_array($contact_type,self::$constant_migrate)) {
            I2CE::raiseError("Invalid contact type: " . $contact_type);
            return false;
        }
        $constant = array_search($contact_type,self::$constant_migrate);
        // http://open.intrahealth.org/wiki/Technical_Overview:_Limiting_Forms
        $where = array(
            'operator' => 'FIELD_LIMIT',
            'field'=>'contact_type',
            'style'=>'equals',
            'data'=>array(
                'value'=>$constant
                ));
        $form_factory = I2CE_FormFactory::instance();
        $contactObj = $form_factory->createContainer("contact", true);
        if (!$contactObj instanceof I2CE_Form || !$contactObj->getField('contact_type') instanceof I2CE_FormField) {
            I2CE::raiseError("No contact_type found. Go directly to home");
            $contactObj->cleanup();
            unset( $contactObj );
            return true;
        }
        $contactObj->cleanup();
        unset( $contactObj );
        $parent_ids = I2CE_FormStorage::search($parent_form); //get all parent form id's        
        I2CE::raiseError("Checking $parent_form (" . implode(',',$parent_ids) . ") against:\n" . print_r($where,true));
        $user = new I2CE_User(0,false,false,false);
        foreach ($parent_ids as $parent_id) {
            if ($parent_id == '0') {
                I2CE::raiseError("Oops! you have a saved form $parent_form with id 0");
                continue;
            }
            $child_form_ids = I2CE_FormStorage::search('contact',$parent_form . '|' . $parent_id, $where);
            if (count($child_form_ids) == 0) {
                continue;
            }
            $parentObj = $form_factory->createContainer($parent_form.'|'.$parent_id, true);
            if (!$parentObj instanceof I2CE_Form) {
                I2CE::raiseError("Could not instantiate $parent_form");
                return false;
            }
            I2CE::raiseError("Found for $contact_type $parent_id:" . implode(',',$child_form_ids));
            foreach ($child_form_ids as $child_form_id) {
                if ($child_form_id == '0' || $child_form_id === null) {
                    continue;
                }
                $childObj = $form_factory->createContainer('contact'.'|'.$child_form_id, true);
                if (!$childObj instanceof iHRIS_Contact) {
                    I2CE::raiseError("Could not instantiate contact");
                    return false;
                }

                $childObj->populate();
                $newChildObj = $form_factory->createContainer($new_child_form, true);


                if (!$newChildObj instanceof iHRIS_Contact) {
                    I2CE::raiseError("Could not instantiate $new_child_form as a sub-class of iHRIS_Contact");
                    $childObj->cleanup();
                    unset( $childObj );
                    $parentObj->cleanup();
                    unset( $parentObj );
                    return false;
                }

                foreach ($childObj as $field=>$fieldObj) {
                    if ($field == 'contact_type') {
                        continue;
                    }
                    $newFieldObj = $newChildObj->getField($field);
                    if (!$newFieldObj instanceof I2CE_FormField) {
                        I2CE::raiseError("Could not get field $field in $new_child_form");
                        return false;
                    }
                    $newFieldObj->setFromDB($fieldObj->getDBValue());
                }
                foreach ($childObj as $field=>$fieldObj) {
                    if ($field == 'contact_type') {
                        continue;
                    }
                    $newFieldObj = $newChildObj->getField($field);
                    if (!$newFieldObj instanceof I2CE_FormField) {
                        I2CE::raiseError("Could not get field $field in $new_child_form");
                        $childObj->cleanup();
                        unset( $childObj );
                        $newChildObj->cleanup();
                        unset( $newChildObj );
                        $parentObj->cleanup();
                        unset( $parentObj );
                        return false;
                    }
                    $newFieldObj->setFromDB($fieldObj->getDBValue());
                }

                $newChildObj->setParent($childObj->getParent());
                if (!$newChildObj->save($user)) {
                    I2CE::raiseError("Could not update contact|$child_form_id of  $parent_form|$parent_id to $new_child_form");
                    $childObj->cleanup();
                    unset( $childObj );
                    $newChildObj->cleanup();
                    unset( $newChildObj );
                    $parentObj->cleanup();
                    unset( $parentObj );
                    return false;
                }
//                 if ($delete_old && !$childObj->delete(true,true)) { //SHOULD THIS BE COMPLETE?
//                     I2CE::raiseError("Could not delete form contact|$child_form_id");
//                     return false;
//                 }
                $childObj->cleanup();
                unset( $childObj );
                $newChildObj->cleanup();
                unset( $newChildObj );

            }
            $parentObj->cleanup();
            unset( $parentObj );

        }        
        if ($remove_contact) {
            return self::removeContactForm($parent_form);
        } else {
            return true;
        }
    }

    /**
     * Remove the contact form from the given parent form
     * @param string $parent_form
     * @returns booelan. true on success
     */
    public static function removeContactForm($parent_form) {
        //now we remove the contact from as a child form
        $childFormConfig = I2CE::getConfig()->traverse("/modules/forms/forms/{$parent_form}/meta/child_forms", false);
        if (!$childFormConfig instanceof I2CE_MagicDataNode) {
            return true;
        }
        foreach ($childFormConfig as $i=>$form) {
            if (is_scalar($form) && $form == 'contact') {
                unset($childFormConfig->$i);
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
