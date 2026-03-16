<?php
/**
 * @copyright © 2007-11 Intrahealth International, Inc.
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
 * @since v4.1.0
 * @version v4.1.0
 * @see I2CE_User
 */

/**
 * Object to display the form to view users in the database.
 * @package I2CE
 * @subpackage Common
 * @access public
 */
class I2CE_PageViewUser extends I2CE_Page {

    /**
     * @var I2CE_User_Form The user being viewed.
     */
    protected $view_user;

    /**
     * Return the view user object for this page.
     * @return I2CE_User_Form
     */
    public function getViewUser() {
        return $this->view_user;
    }

    /**
     * Load the HTML template files for editing and confirming the index information.
     */
    protected function loadHTMLTemplates() {
        $postfix = '';
        $access = get_class(I2CE::getUserAccess());
        if ($access && ($pos = strpos($access,'I2CE_UserAccess_')) !== false) {
            $postfix = substr($access,15);
        }
        $base = $this->template->addFile( "user_form_base_view.html" );
        $node = $this->template->addFile( "user_form_view{$postfix}.html", "table" );
        if (!$node instanceof DOMNode) {
            return false;
        }

    }
    
    /**
     * Initializes any data for the page.
     *
     * @return boolean
     */
    protected function initPage() {          
        $factory = I2CE_FormFactory::instance();
        if ( $this->get_exists('username')) {
            $this->view_user = $factory->createContainer( "user".'|'.$this->get('username'));
            $this->view_user->populate();
        } else {
            $this->setRedirect( "user" );
        }
        return true;
    }
        
        
    /**
     * Perform the actions of the page.
     */
    protected function action() {

        $can_see = false;
        if ( $this->hasPermission( 'task(users_can_edit_all)' ) ) {
            $can_see = true;
        } elseif ( $this->hasPermission('task(users_can_edit)' ) ) {
            $userAccess = I2CE::getUserAccess();
            if ( $userAccess instanceof I2CE_UserAccess_Mechansim 
                    && in_array('creator',$userAccess->getAllowedDetails() ) 
                    && $this->view_user->creator == $this->user->id ) {
                $can_see = true;
            }
        }
        if ( !$can_see ) {
            $this->userMessage("You can not edit this user.",'notice',false);
            $this->setRedirect("user");
            return false;
        }
        I2CE_ModuleFactory::callHooks( "pre_page_view_user", $this );
        parent::action();

        $this->template->setForm( $this->view_user );

        $child_forms = $this->view_user->getChildForms();
        foreach( $child_forms as $child ) {
            $method = "action_" . $child;
            if ( $this->_hasMethod( $method ) ) {
                if ( !$this->$method() ) {
                    I2CE::raiseError( "Could not do action for $form." );
                }
            }
        }
        I2CE_ModuleFactory::callHooks( "post_page_view_user", $this );
        return true;
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:

    
