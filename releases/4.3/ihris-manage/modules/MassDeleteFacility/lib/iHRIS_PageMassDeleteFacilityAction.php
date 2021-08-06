<?php
/*
 * © Copyright 2012 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 * Delete records by facility.
 * @package iHRIS
 * @subpackage Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2012 IntraHealth International, Inc. 
 * @since v4.1.4
 * @version v4.1.4
 */

/**
 * The action page class for mass deleting records by facility.
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageMassDeleteFacilityAction extends I2CE_Page { 

    /**
     * Perform the main actions of the page.
     * @return boolean
     */
    protected function action() {
        if ( !parent::action() ) {
            return false;
        }
        if (!$this->hasPermission("role(admin)")) {
            $this->userMessage("You do not have permission to view this page.");
            return false;
        }
        $pos_mech = I2CE_FormStorage::getStorageMechanism( "position" );
        $pers_pos_mech = I2CE_FormStorage::getStorageMechanism( "person_position" );

        if ( !$pos_mech instanceof I2CE_FormStorage_entry || !$pers_pos_mech instanceof I2CE_FormStorage_entry ) {
            I2CE::raiseMessage("Invalid storage type for position and person position forms. ".get_class($pos_mech) . get_class($pers_pos_mech));
            $this->template->addFile("mass_delete_by_facility_error_invalid.html");
            return true;
        }

        $facilities = $this->post('facilities');
        if ( !is_array($facilities) || count($facilities) < 1 ) {
            $this->template->addFile("mass_delete_by_facility_empty.html");
        } else {
            $step = 'choose';
            if ( $this->post_exists('step') ) {
                $step = $this->post('step');
            }
            if ( $step == "delete" ) {
                if ( $this->post('yes') != 'yes' ) {
                    $this->template->appendFileById( "mass_delete_by_facility_error_yes.html", "p", "error" );
                    $step = "confirm";
                }
                $userAccess = new I2CE_UserAccess_Mechanism();
                if ( !$this->post_exists('admin_pass') 
                        || !$userAccess->userHasPassword('i2ce_admin', $this->post('admin_pass') ) ) {
                    $this->template->appendFileById( "mass_delete_by_facility_error_password.html", "p", "error" );
                    $step = "confirm";
                }
            }
            switch( $step ) {
            case "choose" :
                $this->template->addFile("mass_delete_by_facility_form.html");
                $msgNode = $this->template->addFile("mass_delete_by_facility_confirm_message.html");
                foreach( $facilities as $facility ) {
                    $facObj = I2CE_FormFactory::instance()->createContainer( $facility );
                    $facObj->populate();
                    $facNode = $this->template->appendFileById( "mass_delete_by_facility_each.html", "li", "facility_list" );
                    $this->template->setDisplayDataImmediate( "facilities[]", array('value'=>$facility, 'id' => "check_$facility"), $facNode );
                    $this->template->setDisplayDataImmediate( "facility_name", $facObj->name(), $facNode );
                    $label = $this->template->query( "label[@name='facility_label']", $facNode );
                    if ( $label->length == 1 ) {
                        $label->item(0)->setAttribute( "for", "check_$facility" );
                    }
                }
                break;
            case "confirm" :
                $lists = $this->getDeleteLists( $facilities );
                if ( $lists === null ) {
                    $this->template->addFile("mass_delete_by_facility_error_notfound.html" );
                } elseif ( count($lists) != 2 ) {
                    I2CE::raiseMessage("Invalid return data from getDeleteLists!");
                    $this->template->addFile("mass_delete_by_facility_error_unkonwn.html" );
                } else {
                    $formNode = $this->template->addFile("mass_delete_by_facility_form.html");
                    $this->template->setDisplayDataImmediate("step", "delete");
                    $addNode = $this->template->addFile("mass_delete_by_facility_authenticate_form.html");
                    $positions = $lists[0];
                    $pers_pos = $lists[1];
                    $would_delete = I2CE_FormStorage_entry::massDelete( $positions, $pers_pos );
                    $msgNode = $this->template->addFile("mass_delete_by_facility_delete_count.html");
                    $this->template->setDisplayDataImmediate("delete_count", $would_delete, $msgNode );
                    foreach( $facilities as $facility ) {
                        $facObj = I2CE_FormFactory::instance()->createContainer( $facility );
                        $facObj->populate();
                        $facNode = $this->template->appendFileById( "mass_delete_by_facility_each_final.html", "li", "facility_list" );
                        $this->template->setDisplayDataImmediate( "facilities[]", $facility, $facNode );
                        $this->template->setDisplayDataImmediate( "facility_name", $facObj->name(), $facNode );
                    }
                }
                break;
            case "delete" :
                $lists = $this->getDeleteLists( $facilities );
                if ( $lists === null ) {
                    $this->template->addFile("mass_delete_by_facility_error_notfound.html" );
                } elseif ( count($lists) != 2 ) {
                    I2CE::raiseMessage("Invalid return data from getDeleteLists!");
                    $this->template->addFile("mass_delete_by_facility_error_unkonwn.html" );
                } else {
                    $formNode = $this->template->addFile("mass_delete_by_facility_form.html");
                    $this->template->setDisplayDataImmediate("step", "delete");
                    $addNode = $this->template->addFile("mass_delete_by_facility_authenticate_form.html");
                    $positions = $lists[0];
                    $pers_pos = $lists[1];
                    if ( ($deleted = I2CE_FormStorage_entry::massDelete( $positions, $pers_pos, false )) !== false ) {
                        $node = $this->template->addFile("mass_delete_by_facility_success.html");
                        $this->template->setDisplayDataImmediate("delete_count", $deleted, $node);
                        if ( I2CE_ModuleFactory::instance()->isEnabled("CachedForms") ) {
                            $forms = I2CE_FormFactory::instance()->getNames();
                            $success = array();
                            $failure = array();
                            foreach( $forms as $form ) {
                                try {
                                    $cachedForm = new I2CE_CachedForm($form);
                                } catch( Exception $e ) {
                                    $success[] = $form;
                                    continue;
                                }
                                if ( !$cachedForm->dropTable() ) {
                                    $failure[] = $form;
                                }
                            }
                            if ( count( $failure ) > 0 ) {
                                $this->template->addFile( "mass_delete_by_facility_cache_fail.html", "p" );
                            } else {
                                $this->template->addFile( "mass_delete_by_facility_cache_success.html", "p" );
                            }
                        }
                    } else {
                        I2CE::raiseError("An error occurred trying to mass delete by facility.");
                        $this->template->addFile("mass_delete_by_facility_error_unkonwn.html" );
                    }
                }
                break;
            }
        }

    }


    /**
     * From the given list of facilities get the positions and person position records to be 
     * passed to the mass delete method.
     * @param array $facilities
     * @return array
     */
    protected function getDeleteLists( $facilities ) {
        // Get all positions for the given facilities
        $pos_where = array( 'operator' => "FIELD_LIMIT",
                'style' => "in",
                'field' => "facility",
                'data' => array( 'value' => $facilities )
                );
        $positions = I2CE_FormStorage::search( "position", false, $pos_where );
        if ( count($positions) < 1 ) {
            return null;
        }
        $limit_pos = array();
        foreach( $positions as $position ) {
            $limit_pos[] = "position|$position";
        }

        // Get all person_positions for the given positions that are active.
        $pp_where = array( 'operator' => "AND",
                'operand' => array( 0 => array(
                        'operator' => "FIELD_LIMIT",
                        'style' => "in",
                        'field' => "position",
                        'data' => array( 'value' => $limit_pos ),
                        ),
                    1 => array(
                        'operator' => "FIELD_LIMIT",
                        'style' => "null",
                        'field' => "end_date",
                        )
                    )
                );
        $pers_pos = I2CE_FormStorage::search( "person_position", false, $pp_where );

        return array( $positions, $pers_pos );
    }
}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
