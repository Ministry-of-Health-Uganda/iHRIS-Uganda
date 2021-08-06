<?php
/**
* © Copyright 2011 IntraHealth International, Inc.
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
* @version v4.1.0
* @since v4.1.0
* @filesource 
*/ 
/** 
* Class iHRIS_Module_ManageAccessFacility
* 
* @access public
*/


class iHRIS_Module_ManageAccessFacility extends I2CE_ModuleAccess {

    /**
     * @var array A cached list of facility_access for a username
     */
    protected static $user_access_facility;

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
                'iHRIS_PageViewUser->action_access_facility' => 'action_access_facility',
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
            $facAccess = $factory->createContainer( "access_facility" );
            $forms = $facAccess->getField('location')->getSelectableForms();
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
                    'module' => "ManageAccessFacility",
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
        $locations = self::getAccessLocation( $this->user );
        $limit_add = array();
        foreach( $locations as $form => $data ) {
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
        $locations = self::getAccessLocation( $this->user );
        if ( count( $locations ) == 0 ) {
            return true;
        } elseif ( array_key_exists( $form, $locations ) ) {
            return $locations[$form];
        } else {
            return array();
        }
    }


    /**
     * Handle the display for the access_facility form on the view user page.
     * @return boolean
     */
    public function action_access_facility( $page ) {
        if ( !$page instanceof iHRIS_PageViewUser ) {
            return;
        }
        $template = $page->getTemplate();
        $template->appendFileById( "user_view_link_access_facility.html", "li", "user_edit_links" );

        $view_user = $page->getViewUser();
        $view_user->populateChildren("access_facility");
        if ( array_key_exists( 'access_facility', $view_user->children ) 
                && is_array( $view_user->children['access_facility'] ) ) {
            $node = $template->appendFileById( "user_view_access_facility_top.html", "div", "user_child_forms" );
            foreach( $view_user->children['access_facility'] as $child ) {
                $node = $template->appendFileById( "user_view_access_facility.html", "div", "access_facility" );
                if ( !$node instanceof DOMNode ) {
                    I2CE::raiseError( "Could not find template user_view_access_facility.html" );
                    return false;
                }
                $template->setForm( $child, $node );
            }
        }
        return true;
    }

    /**
     * Return a list of locations the user is allowed to access.
     * @param I2CE_User $user
     * @return array with keys form name, and values and array containing formids form that form
     */
    public static function getAccessLocation( $user ) {
        if ( !$user instanceof I2CE_User ) {
            return array();
        }
        $username = $user->username;
        if ( !$username ) {
            return array();
        }
        if ( !is_array( self::$user_access_facility ) ) {
            self::$user_access_facility = array();
        }
        if ( !isset( self::$user_access_facility[$username] ) ) {
            self::$user_access_facility[$username] = array();
            $user = I2CE_FormFactory::instance()->createContainer( "user|$username" );
            $user->populateChildren( "access_facility" );
            if ( array_key_exists( 'access_facility', $user->children ) 
                    && is_array( $user->children['access_facility'] ) ) {
                foreach( $user->children['access_facility'] as $child ) {
                    $ff = $child->getField("location");
                    self::$user_access_facility[$username][$ff->getMappedForm()][] =
                        $ff->getDBValue();
                }
            }
        }
        return self::$user_access_facility[$username];
    }
    

    /**
     * Template function to see if person_can_view_child_forms
     *  @param DOMNode $node
     * @param I2CE_Template $template
     * @param string $link
     */
    public function userAccessFacility($node,$template) {
        // This should only work for the facility_manager role so ignore any others.
        if ( $template->getUser()->getRole() != "facility_manager" ) {
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

        $access = self::getAccessLocation( $template->getUser() ); // a list of locations a user is allowed to access
        if ( count( $access ) == 0 ) {
            return false;
        }
        
        //look at the positions this person has had sorted by start date
        $person->populateLast( array( "person_position" => "start_date" ) );
        if ( !array_key_exists( 'person_position', $person->children ) || !is_array( $person->children['person_position'] ) || count($person->children['person_position']) == 0 ) {
            // If there is not person position then access is granted.
            return true;
        }
        $fieldWalks = array(
            'facility'=> 'location'
            ,'county' => 'district'
            ,'district'=> 'region'
            ,'region' => 'country'
            ,'country' => false
            );
        $formObjs = $this->getTemplateForms($template,$node,array_keys($fieldWalks));
        $fieldWalks['position']='facility';
                
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
            $t_formObjs = $formObjs;
            $t_formObjs['position'] =$position;
            if ($this->ValidateAccessAgainstWalkableForms($fieldWalks,$access, $t_formObjs)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Template function to see if person_can_view_child_forms
     *  @param DOMNode $node
     * @param I2CE_Template $template
     * @param string $link
     */
    public function userAccessFacilityStaff($node,$template) {
        // This should only work for the facility_manager role so ignore any others.
        if ( $template->getUser()->getRole() != "facility_staff" ) {
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

        $access = self::getAccessLocation( $template->getUser() ); // a list of locations a user is allowed to access
        if ( count( $access ) == 0 ) {
            return false;
        }
        
        //look at the positions this person has had sorted by start date
        $person->populateLast( array( "person_position" => "start_date" ) );
        if ( !array_key_exists( 'person_position', $person->children ) || !is_array( $person->children['person_position'] ) || count($person->children['person_position']) == 0 ) {
            // If there is not person position then access is granted.
            return true;
        }
        $fieldWalks = array(
            'facility'=> 'location'
            ,'county' => 'district'
            ,'district'=> 'region'
            ,'region' => 'country'
            ,'country' => false
            );
        $formObjs = $this->getTemplateForms($template,$node,array_keys($fieldWalks));
        $fieldWalks['position']='facility';
                
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
            $t_formObjs = $formObjs;
            $t_formObjs['position'] =$position;
            if ($this->ValidateAccessAgainstWalkableForms($fieldWalks,$access, $t_formObjs)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Template function to see if person_can_view_child_forms
     *  @param DOMNode $node
     * @param I2CE_Template $template
     * @param string $link
     */
    public function userAccessFacilityDataEntrant($node,$template) {
        // This should only work for the facility_manager role so ignore any others.
        if ( $template->getUser()->getRole() != "facility_data_entrant" ) {
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

        $access = self::getAccessLocation( $template->getUser() ); // a list of locations a user is allowed to access
        if ( count( $access ) == 0 ) {
            return false;
        }
        
        //look at the positions this person has had sorted by start date
        $person->populateLast( array( "person_position" => "start_date" ) );
        if ( !array_key_exists( 'person_position', $person->children ) || !is_array( $person->children['person_position'] ) || count($person->children['person_position']) == 0 ) {
            // If there is not person position then access is granted.
            return true;
        }
        $fieldWalks = array(
            'facility'=> 'location'
            ,'county' => 'district'
            ,'district'=> 'region'
            ,'region' => 'country'
            ,'country' => false
            );
        $formObjs = $this->getTemplateForms($template,$node,array_keys($fieldWalks));
        $fieldWalks['position']='facility';
                
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
            $t_formObjs = $formObjs;
            $t_formObjs['position'] =$position;
            if ($this->ValidateAccessAgainstWalkableForms($fieldWalks,$access, $t_formObjs)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Helper method to get forms objects in template
     * @param I2CE_Template $template
     * @param DOMNode $node
     * @param array $forms of string, the name of the forms
     */
    public function getTemplateForms($template,$node,$forms) {
        $formObjs = array();
        if (!$template instanceof I2CE_Template) {
            return $formObjs;
        }
        foreach ($forms as $form) {
            if ( ! ($formObj = $template->getForm($form,$node)) instanceof I2CE_Form) {
                continue;
            } 
            if ($formObj->form() != $form ) {
                continue;
            }
            $formObjs[$form] = $formObj;
        }
        return $formObjs;
    }

    /**
     * Template function to see if  can_edit_database_list_position for the 
     *  @param DOMNode $node
     * @param I2CE_Template $template
     * @returns boolean
     */
    public function userAccessFacilityList($node,$template) {
        // This should only work for the facility_manager role so ignore any others.
        if ( $template->getUser()->getRole() != "facility_manager" ) {
            return false;
        }
        $fieldWalks = array(
            'position'=> 'facility'
            ,'facility'=> 'location'
            ,'county' => 'district'
            ,'district'=> 'region'
            ,'region' => 'country'
            ,'country' => false
            );
        return $this->userAccessWalkableForms($node,$template,$fieldWalks);
    }

    /**
     * Template function to see if  can_edit_database_list_position for the 
     *  @param DOMNode $node
     * @param I2CE_Template $template
     * @param array $fieldsWalks, arrays whos keys are the forms we are looking for, and value is either a mapped field of that form we want to walk or false.
     * @param boolean $permit_empty.  Defaults to true in which case we grant permission if none of the forms from {$fieldWalks} are found in the template, or if there is no template
     * @returns boolean;
     */
    public function userAccessWalkableForms($node,$template, $fieldWalks,$permit_empty =true) {
        if ( !$template instanceof I2CE_Template) {            
            return $permit_empty;
        }
        if (!$node instanceof DOMNode) {
            $node = null;
        }
        $formObjs = $this->getTemplateForms($template,$node,array_keys($fieldWalks));
        if (count($formObjs) == 0) {
            //no location data associate to this node so if we have any positions under it, we should mark them as being true.
            I2CE::raiseError("No data set on given node, giving permission");
            return $permit_empty;
        }
        //now we go through each of the location data objects to see if we are allowed to edit 
        $access = self::getAccessLocation( $template->getUser() );       
        return $this->ValidateAccessAgainstWalkableForms($fieldWalks,$access,$formObjs);
    }


    /**
     * Check to see if we have access based on a list of access ids and form objects which we are allowed to edit
     * @param array $fieldsWalks, arrays whos keys are the forms we are looking for, and value is either a mapped field of that form we want to walk or false.
     * @param array $access with keys form name, and values and array containing formids form that form which we are checking for access agagins
     * @param array $formObjs with keys the name of a  form and value a instance I2CE_Form for  that form
     */
    public function ValidateAccessAgainstWalkableForms($fieldWalks,$access, $formObjs) {
        $ff = I2CE_FormFactory::instance();
        if (!is_array($formObjs)) {
            return false;
        }
        foreach ($formObjs as $form=>$formObj) {
            if (!$formObj instanceof I2CE_Form) {
                continue;
            }
            $matched = false;
            //now we should walk the linked fields of the formObj to see if can get accessid
            $walkObj = $formObj;
            $walkForm = $form;
            $walkFieldVal = $formObj->getFormID();
            $walkFormObjs = array();
            while (true) { 
                $walkFormObjs[$walkFieldVal] = $walkObj;
                if (array_key_exists($walkForm,$access) && is_array($access[$walkForm])) {
                    foreach ($access[$walkForm] as $accessID) {
                        if ( $walkFieldVal == $accessID) {
                            $matched = true;
                            break 2;
                        }
                    }
                }
                if (!array_key_exists($walkForm,$fieldWalks) || !$fieldWalks[$walkForm]) {
                    //nothing to walk up so stop
                    break;
                }
                //now we get the value of the field we are falking on
                $walkField = $fieldWalks[$walkForm];
                $walkFieldObj = $walkObj->getField($walkField);
                if ( ! $walkFieldObj instanceof I2CE_FormField_MAP) {
                    //stop walking up   
                    break;
                }
                if (!$walkFieldObj->isValid()) {
                    //stop walking up   
                    break;
                }
                $walkFieldVal = $walkFieldObj->getDBValue();
                if (array_key_exists($walkFieldVal,$walkFormObjs)) {
                    //avoid recursion in fieldWalks
                    break;
                }
                if (! ($walkObj = $ff->createForm($walkFieldVal)) instanceof I2CE_Form) {
                    //stop walking up as we could not create the new walk form
                    break;
                }
                //get the mapped form for the next loop around
                $walkObj->populate();
                $walkForm =  $walkObj->form();
            }
            if ($matched) {
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
