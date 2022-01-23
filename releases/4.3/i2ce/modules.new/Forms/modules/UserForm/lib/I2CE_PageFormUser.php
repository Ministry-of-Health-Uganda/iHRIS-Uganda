<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
 * This page is used to add and edit user records to give access to the site.
 * @package I2CE
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v2.0.0
 * @version v2.0.0
 * @see I2CE_User
 */

/**
 * Object to display the form to edit users in the database.
 * @package I2CE
 * @subpackage Common
 * @access public
 */
class I2CE_PageFormUser extends I2CE_PageForm {

    /**
     * Load the HTML template files for editing and confirming the index information.
     */
    protected function loadHTMLTemplates() {
        $postfix = '';
        $access = get_class(I2CE::getUserAccess());
        if ($access && ($pos = strpos($access,'I2CE_UserAccess_')) !== false) {
            $postfix = substr($access,15);
        }
        if ( $this->getPrimary()->getId() != '0' ) {
            $node = $this->template->addFile( "user_form_edit{$postfix}.html" );
        } else {
            $node = $this->template->addFile( "user_form{$postfix}.html" );
        }
        if (!$node instanceof DOMNode) {
            return false;
        }

    }
    
    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the index object and if this is a form submission the load
     * the data from the $_POST array.
     */
    protected function loadObjects() {          
        $factory = I2CE_FormFactory::instance();
        if ( $this->isPost() ) {
            $user = $factory->createContainer( "user".'|'.$this->post('username'));            
            $user->load( $this->post );
            $this->setEditing();
            if ( !$this->isSave(false) ) {
                $user->tryGeneratePassword();
            }
            $user->getField("username")->setHref( "view_user?username=" );
        } elseif ( $this->get_exists('username')) {
            $user = $factory->createContainer( "user".'|'.$this->get('username'));
            $user->populate( false );
            $this->setEditing();
            $user->getField("username")->setHref( "view_user?username=" );
        } else {
            $user = $factory->createContainer( "user".'|0');
        }
        $this->setObject( $user );
    }
        
    /**
     * Display the save or confirm buttons as needed.
     * @param boolean $save Flag to show the save button.
     * @param boolean $show_edit (defaults to true)
     */
    protected function displayControls( $save = false, $show_edit =true ) {
        if ( $save ) {
            $this->template->addFile( "button_save.html" );
        } else {
            $this->template->addFile( "button_confirm_user.html" );     
        }
    }
        
    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited and return to the view page.
     */
    protected function save() {
        if ( !$this->hasPermission('task(users_can_edit)')) {
            $this->userMessage('You cannot edit users','notice',true);
            $this->setRedirect( "home" );
            return false;
        }
        if (I2CE_User::hasDetail('creator')) {
            $this->getPrimary()->creator =  $this->user->username;
            $this->fields['creator'] = $this->user->username;
        }

        parent::save();
        $this->setRedirect( "view_user?username=" . $this->getPrimary()->username );
    }
    
        
    /**
     * Perform the actions of the page.
     */
    protected function action() {
        if ( !$this->isPost() && !$this->get_exists('username') && !$this->get_exists('add') ) {
            if ($this->hasPermission('task(users_can_edit_all)')) {
                $this->template->addFile( "user_list.html" );           
                $this->listUsersToEdit(  'user_list' );
            } else if ( $this->hasPermission('task(users_can_edit)')  && I2CE_User::hasDetail('creator') ) {
                $this->template->addFile( "user_list.html" );           
                $this->listUsersToEdit('user_list', $this->user->username);
            } else  {
                $this->userMessage("You can not edit users",'notice',false);
                return false;
            }
        } else {
            parent::action();
        }
    }


    /**
     * Populate a drop down of users that can be edited by the current user given his/her access level.
     * @param string $selectId
     * @global array
     */
    public function listUsersToEdit(  $selectId, $username = null ) {
        $add_last = array();
        if ($username == null) { 
            $usernames = I2CE_User::findUsersByInfo(false,array(),false); //we all users except the interal admin user regardless or role or details.
        } else {
            $userAccess = I2CE::getUserAccess();
            if (!$userAccess instanceof I2CE_UserAccess_Mechansim) {
                return false;
            }            
            if (!in_array('creator',$userAccess->getAllowedDetails())) {
                return false;
            }
            $usernames = I2CE_User::findUsersByInfo(false, array('creator'=>$username));
        }
        if (!is_array($usernames)) {
            return false;
        }
        foreach ($usernames as $username) {
            $user = new I2CE_User($username,true,false,false);
            if (!$user instanceof I2CE_User) {
                continue;
            }
            $role = $user->getRole();
            if ($role) {
                $role = I2CE_User_Form::getRoleNameFromShortName( $role);
            }
            $disp = trim($user->displayName());
            if (!$disp) {
                $disp = "($username)";
            }
            if ( $role == "" ) {
                $add_last[$username] = 'No Access - ' . $disp;
            } else {
                $this->template->addOption( $selectId, $username, $role . ' - ' . $disp) ;
            }
        }
        foreach( $add_last as $username => $dispname ) {
            $this->template->addOption( $selectId, $username, $dispname );
        }
    }


    

  }

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:

    
