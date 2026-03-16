<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
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
* @package iHRIS
* @subpackage Manage
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class iHRIS_Module_ManageAccessSupervisor
* 
* @access public
*/


class iHRIS_Module_ManageAccessSupervisor extends I2CE_ModuleAccess {

    /**
     * @var array A cached list of supervisor_access for a username
     */
    protected static $user_access_supervisor;

    /**
     * @var array A list of limit options for this module.
     */
    protected static $report_limit_options;

    /**
     * Return the list of fuzzy methods handled by this module.
     * @return array
     */
    public static function getMethods() {
        return array(
                'iHRIS_PageViewUser->action_access_supervisor' => 'action_access_supervisor',
                );
    }

    /**
     * Retrn the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'get_report_module_limit_options' => 'get_report_limit_options',
                );
    }

    /**
     * Return the array of details for report limit options for this
     * module.
     * @return array
     */
    public function get_report_limit_options() {
        if ( !self::$report_limit_options 
                && !is_array( self::$report_limit_options ) ) {
            $factory = I2CE_FormFactory::instance();
            $facAccess = $factory->createContainer( "access_supervisor" );
            $forms = $facAccess->getField('supervisor')->getSelectableForms();
            $fields = array();
            foreach( $forms as $form ) {
                $formObj = $factory->createContainer( $form );
                if ( $formObj instanceof I2CE_Form ) {
                    $fields[$form] = $formObj->getDisplayName();
                }
                $formObj->cleanup();
                unset( $formObj );
            }
            $facAccess->cleanup();
            unset( $facAccess );
            self::$report_limit_options = array(
                    'module' => "ManageAccessSupervisor",
                    'fields' => $fields,
                    );
        }
        return self::$report_limit_options;
    }

    /**
     * Static helper method to remove the given form name from
     * an id.
     * @param string &$item An entry in an array.
     * @param mixed $key The array key for this item.
     * @param string $form The form name to remove.
     */
    protected static function removeFormName( &$item, $key, $form ) {
        $item = substr( $item, strlen( $form ) + 1 );
    }

    /**
     * Return an array of limit_add options to add
     * for this user to limit fields.
     * @param I2CE_Form $formObj
     * @param array $args
     * @return array
     */
    public function getLimitAdd( $formObj, $args ) {
        $supervisors = self::getAccessSupervisor( $this->user );
        $limit_add = array();
        foreach( $supervisors as $form => $data ) {
            $limit_add[$form] = array( 'operator' => 'FIELD_LIMIT',
                    'field' => 'id' );
            $id_data = $data;
            array_walk( $id_data, array( "self", "removeFormName" ),
                    $form );
            if ( count( $id_data ) == 1 ) {
                $limit_add[$form]['style'] = 'equals';
                $limit_add[$form]['data']['value'] = $id_data[0];
            } else {
                $limit_add[$form]['style'] = 'in';
                $limit_add[$form]['data']['value'] = $id_data;
            }
        }
        //I2CE::raiseMessage("args are " .$args['meta']['display']['default']['fields'] . " " . print_r( $args, true ) );
        return $limit_add;
    }

    /**
     * Return the list of allowed id values for the given form
     * assigned to the current user.  If the form isn't 
     * given directly by this user then return an empty array.
     * Return true if there is no limits at all for this
     * user.
     * @param string $form
     * @return mixed
     */
    public function getLimitsByForm( $form ) {
        $supervisors = self::getAccessSupervisor( $this->user );
        if ( count( $supervisors ) == 0 ) {
            return true;
        } elseif ( array_key_exists( $form, $supervisors ) ) {
            return $supervisors[$form];
        } else {
            return array();
        }
    }


    /**
     * Handle the display for the access_supervisor form on the view user page.
     * @return boolean
     */
    public function action_access_supervisor( $page ) {
        if ( !$page instanceof iHRIS_PageViewUser ) {
            return;
        }
        $template = $page->getTemplate();
        $template->appendFileById( "user_view_link_access_supervisor.html", "li", "user_edit_links" );

        $view_user = $page->getViewUser();
        $view_user->populateChildren("access_supervisor");
        if ( array_key_exists( 'access_supervisor', $view_user->children ) 
                && is_array( $view_user->children['access_supervisor'] ) ) {
            $node = $template->appendFileById( "user_view_access_supervisor_top.html", "div", "user_child_forms" );
            foreach( $view_user->children['access_supervisor'] as $child ) {
                $node = $template->appendFileById( "user_view_access_supervisor.html", "div", "access_supervisor" );
                if ( !$node instanceof DOMNode ) {
                    I2CE::raiseError( "Could not find template user_view_access_supervisor.html" );
                    return false;
                }
                $template->setForm( $child, $node );
            }
        }
        return true;
    }

    
    /**
     * Template function to see if person_can_view_child_forms
     *  @param DOMNode $node
     * @param I2CE_Template $template
     * @param string $link
     */
    public function userAccessSupervisor($node,$template) {
        // This should only work for the facility_manager role so ignore any others.
        if ( $template->getUser()->getRole() != "supervisor" ) {
            return false;
        }
        if ( !$template instanceof I2CE_Template) {
            return false;
        }
        if (!$node instanceof DOMNode) {
            $node = null;
        }
        if ( ! ($person = $template->getForm('person',$node)) instanceof iHRIS_Person) {
            //No person associated with this node.  so this user can have permission
            return true;
        }
        
        //gets the person id from the user
        $user_person_id = iHRIS_UserMap::getPersonId();
        if ( count( $user_person_id ) == 0 ) {
            return false;
        }else{
            //Get Current person_position  for the user
        	$factory = I2CE_FormFactory::instance(); 
        	$user = $factory->createContainer( $user_person_id );
        	$user->populateLast( array( "person_position" => "start_date" ) );
        	if ( !array_key_exists( 'person_position', $user->children ) || !is_array( $user->children['person_position'] ) || count($user->children['person_position']) == 0 ) {
            	// If there is not person position then access is not granted.
            	return false;
        	}
        	foreach( $user->children['person_position'] as $user_pers_pos ) {
            	if ( $user_pers_pos->end_date->isValid() 
                	&& $user_pers_pos->end_date->before( I2CE_Date::now() ) ) {
                	// Not a current position so access is  not granted.
                	return false;
            	}
            //get position of the supervisor
            $user_position = $user_pers_pos->getField("position")->getDBValue();
        	
        }
        //look at the positions this person has had sorted by start date
        $person->populateLast( array( "person_position" => "start_date" ) );
        if ( !array_key_exists( 'person_position', $person->children ) || !is_array( $person->children['person_position'] ) || count($person->children['person_position']) == 0 ) {
            // If there is not person position then access is not granted.
            return false;
        }
                
        foreach( $person->children['person_position'] as $pers_pos ) {
            if ( $pers_pos->end_date->isValid() 
                && $pers_pos->end_date->before( I2CE_Date::now() ) ) {
                // Not a current employee so access is  notgranted.
                return false;
            }
            $position = $pers_pos->getField("position")->getMappedFormObject();
            if (!$position instanceof iHRIS_Position) {
                continue;
            }
            if ( $position->getField('supervisor')->getDBValue() ==  $user_position ) {
                return true;
            }
        }
        return false;
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
