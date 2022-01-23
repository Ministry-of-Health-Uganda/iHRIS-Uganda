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
 * Delete records by person.
 * @package iHRIS
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2012 IntraHealth International, Inc. 
 * @since v4.1.4
 * @version v4.1.4
 */

/**
 * The action page class for mass deleting records by person.
 * @package iHRIS
 * @subpackage Common
 * @access public
 */
class iHRIS_PageMassDeleteSearchAction extends I2CE_Page { 

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
            $this->template->addFile("mass_delete_by_search_error_invalid.html");
            return true;
        }

        $people = $this->post('people');
        if ( !is_array($people) || count($people) < 1 ) {
            $this->template->addFile("mass_delete_by_search_empty.html");
        } else {
            $step = 'choose';
            if ( $this->post_exists('step') ) {
                $step = $this->post('step');
            }
            if ( $step == "delete" ) {
                if ( $this->post('yes') != 'yes' ) {
                    $this->template->appendFileById( "mass_delete_by_search_error_yes.html", "p", "error" );
                    $step = "confirm";
                }
                $userAccess = new I2CE_UserAccess_Mechanism();
                if ( !$this->post_exists('admin_pass') 
                        || !$userAccess->userHasPassword('i2ce_admin', $this->post('admin_pass') ) ) {
                    $this->template->appendFileById( "mass_delete_by_search_error_password.html", "p", "error" );
                    $step = "confirm";
                }
            }
            switch( $step ) {
            case "choose" :
                $this->template->addFile("mass_delete_by_search_form.html");
                $msgNode = $this->template->addFile("mass_delete_by_search_confirm_message.html");
                foreach( $people as $person ) {
                    $persObj = I2CE_FormFactory::instance()->createContainer( $person );
                    $persObj->populate();
                    $persNode = $this->template->appendFileById( "mass_delete_by_search_each.html", "li", "search_list" );
                    $this->template->setDisplayDataImmediate( "people[]", array('value'=>$person, 'id' => "check_$person"), $persNode );
                    $this->template->setDisplayDataImmediate( "person_name", $persObj->surname.', '.$persObj->firstname, $persNode );
                    $label = $this->template->query( "label[@name='search_label']", $persNode );
                    if ( $label->length == 1 ) {
                        $label->item(0)->setAttribute( "for", "check_$person" );
                    }
                }
                break;
            case "confirm" :
                $list = $this->getDeleteList( $people );
                if ( $list === null ) {
                    $this->template->addFile("mass_delete_by_search_error_notfound.html" );
                } elseif ( count($list) < 1 ) {
                    I2CE::raiseMessage("Invalid return data from getDeleteList!");
                    $this->template->addFile("mass_delete_by_search_error_unkonwn.html" );
                } else {
                    $formNode = $this->template->addFile("mass_delete_by_search_form.html");
                    $this->template->setDisplayDataImmediate("step", "delete");
                    $addNode = $this->template->addFile("mass_delete_by_search_authenticate_form.html");
                    $would_delete = I2CE_FormStorage_entry::massDelete( $list, array() );
                    $msgNode = $this->template->addFile("mass_delete_by_search_delete_count.html");
                    $this->template->setDisplayDataImmediate("delete_count", $would_delete, $msgNode );

                    foreach( $people as $person ) {
                        $persObj = I2CE_FormFactory::instance()->createContainer( $person );
                        $persObj->populate();
                        $persNode = $this->template->appendFileById( "mass_delete_by_search_each_final.html", "li", "search_list" );
                        $this->template->setDisplayDataImmediate( "people[]", $person, $persNode );
                        $this->template->setDisplayDataImmediate( "person_name", $persObj->surname.', '.$persObj->firstname, $persNode );
                    }
                }
                break;
            case "delete" :
                $list = $this->getDeleteList( $people );
                if ( $list === null ) {
                    $this->template->addFile("mass_delete_by_search_error_notfound.html" );
                } elseif ( count($list) < 1 ) {
                    I2CE::raiseMessage("Invalid return data from getDeleteList!");
                    $this->template->addFile("mass_delete_by_search_error_unkonwn.html" );
                } else {
                    $formNode = $this->template->addFile("mass_delete_by_search_form.html");
                    $this->template->setDisplayDataImmediate("step", "delete");
                    $addNode = $this->template->addFile("mass_delete_by_search_authenticate_form.html");
                    I2CE_ModuleFactory::callHooks( "pre_mass_delete_person", $people, $this->post() );
                    if ( ($deleted = I2CE_FormStorage_entry::massDelete( $list, array(), false )) !== false ) {
                        $node = $this->template->addFile("mass_delete_by_search_success.html");
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
                                $this->template->addFile( "mass_delete_by_search_cache_fail.html", "p" );
                            } else {
                                $this->template->addFile( "mass_delete_by_search_cache_success.html", "p" );
                            }
                        }
                    } else {
                        I2CE::raiseError("An error occurred trying to mass delete by search.");
                        $this->template->addFile("mass_delete_by_search_error_unkonwn.html" );
                    }
                }
                break;
            }
        }

    }


    /**
     * From the given list of people get the positions and stripped people ids to be
     * passed to the mass delete method.
     * @param array $people
     * @return array
     */
    protected function getDeleteList( $people ) {
        // Get all positions for the given facilities
        $pos_where = array( 'operator' => "AND",
                'operand' => array( 0 => array( 
                        'operator' => "FIELD_LIMIT",
                        'style' => "in",
                        'field' => "parent",
                        'data' => array( 'value' => $people ),
                        ),
                    1 => array(
                        'operator' => "FIELD_LIMIT",
                        'style' => "null",
                        'field' => "end_date",
                        )
                    )
                );
        $positions = I2CE_FormStorage::listFields( "person_position", array("position"), false, $pos_where );
        $results = array();
        foreach( $positions as $pers_pos => $data ) {
            $results[] = str_replace( 'position|', '', $data['position'] );
        }
        foreach( $people as $person ) {
            $results[] = str_replace( 'person|', '', $person );
        }
        if ( count($results) < 1 ) {
            return null;
        } else {
            return $results;
        }
    }
}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
