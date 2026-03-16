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
* Class iHRIS_Module_ManageAccessDepartment
* 
* @access public
*/


class iHRIS_Module_ManageAccessDepartment extends I2CE_ModuleAccess {

    /**
     * @var array A cached list of department_access for a username
     */
    protected static $user_access_department;

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
                'iHRIS_PageViewUser->action_access_department' => 'action_access_department',
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
            $facAccess = $factory->createContainer( "access_department" );
            $forms = $facAccess->getField('department')->getSelectableForms();
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
                    'module' => "ManageAccessDepartment",
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
        $departments = self::getAccessDepartment( $this->user );
        $limit_add = array();
        foreach( $departments as $form => $data ) {
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
        $departments = self::getAccessDepartment( $this->user );
        if ( count( $departments ) == 0 ) {
            return true;
        } elseif ( array_key_exists( $form, $departments ) ) {
            return $departments[$form];
        } else {
            return array();
        }
    }


    /**
     * Handle the display for the access_department form on the view user page.
     * @return boolean
     */
    public function action_access_department( $page ) {
        if ( !$page instanceof iHRIS_PageViewUser ) {
            return;
        }
        $template = $page->getTemplate();
        $template->appendFileById( "user_view_link_access_department.html", "li", "user_edit_links" );

        $view_user = $page->getViewUser();
        $view_user->populateChildren("access_department");
        if ( array_key_exists( 'access_department', $view_user->children ) 
                && is_array( $view_user->children['access_department'] ) ) {
            $node = $template->appendFileById( "user_view_access_department_top.html", "div", "user_child_forms" );
            foreach( $view_user->children['access_department'] as $child ) {
                $node = $template->appendFileById( "user_view_access_department.html", "div", "access_department" );
                if ( !$node instanceof DOMNode ) {
                    I2CE::raiseError( "Could not find template user_view_access_department.html" );
                    return false;
                }
                $template->setForm( $child, $node );
            }
        }
        return true;
    }

    /**
     * Return a list of departments the user is allowed to access.
     * @param I2CE_User $user
     * @return array with keys form name, and values and array containing formids form that form
     */
    public static function getAccessDepartment( $user ) {
        if ( !$user instanceof I2CE_User ) {
            return array();
        }
        $username = $user->username;
        if ( !$username ) {
            return array();
        }
        if ( !is_array( self::$user_access_department ) ) {
            self::$user_access_department = array();
        }
        if ( !isset( self::$user_access_department[$username] ) ) {
            self::$user_access_department[$username] = array();
            $user = I2CE_FormFactory::instance()->createContainer( "user|$username" );
            $user->populateChildren( "access_department" );
            if ( array_key_exists( 'access_department', $user->children ) 
                    && is_array( $user->children['access_department'] ) ) {
                foreach( $user->children['access_department'] as $child ) {
                    $ff = $child->getField("department");
                    self::$user_access_department[$username][$ff->getMappedForm()][] =
                        $ff->getDBValue();
                }
            }
        }
        return self::$user_access_department[$username];
    }
    

    /**
     * Template function to see if person_can_view_child_forms
     *  @param DOMNode $node
     * @param I2CE_Template $template
     * @param string $link
     */
    public function userAccessDepartment($node,$template) {
        // This should only work for the facility_manager role so ignore any others.
        if ( $template->getUser()->getRole() != "department_manager" ) {
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

        $access = self::getAccessDepartment( $template->getUser() ); // a list of locations a user is allowed to access
        if ( count( $access ) == 0 ) {
            return false;
        }
        
        //look at the positions this person has had sorted by start date
        $person->populateLast( array( "person_position" => "start_date" ) );
        if ( !array_key_exists( 'person_position', $person->children ) || !is_array( $person->children['person_position'] ) || count($person->children['person_position']) == 0 ) {
            // If there is not person position then access is granted.
            return true;
        }
                
        foreach( $person->children['person_position'] as $pers_pos ) {
            if ( $pers_pos->end_date->isValid() 
                && $pers_pos->end_date->before( I2CE_Date::now() ) ) {
                // Not a current employee so access is granted.
                return true;
            }
            $position = $pers_pos->getField("position")->getMappedFormObject();
            if (!$position instanceof iHRIS_Position) {
                continue;
            }
            if ( in_array( $position->getField('department')->getDBValue(), $access ) ) {
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
