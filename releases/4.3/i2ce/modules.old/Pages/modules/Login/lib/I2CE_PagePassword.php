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
 * The page to change a user's password.
 * @package I2CE
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since Demo-v1.a
 * @version Demo-v2.a
 */

/**
 * The page class for displaying the password change page.
 * @package I2CE
 * @subpackage Common
 * @access public
 */
class I2CE_PagePassword extends I2CE_Page {
        
    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        parent::action();
        $this->template->setAttribute( "class", "active", "menuPassword", "a[@href='password']" );
        if ( $this->isPost() ) {
            $access = I2CE::getUserAccess();
            if ( !$access instanceof I2CE_UserAccess_Mechanism || !$access->canChangePassword() || $this->user->getRole() == 'guest' ) {
                $success = 'no_change';
            } else if ( !$this->post('old_password') || !$this->post('new_password')) {
                $success = 'none';
            } else if ( $this->post('new_password') != $this->post('confirm_password') ) {
                $success = 'no_match';
            } else   if (!$this->user->changePassword( $this->post('old_password'), $this->post('new_password') )) {
                $success = 'wrong';
            } else {
                $success = 'success';
            }
            //$this->template->addTextNode( "message", $message );
            if ( $success == 'success') {
                $this->template->addFile( "password_success.html" );
            } else {
                $this->template->addFile( "password_form.html" );
                $this->template->addFile( "password_" . $success . ".html", "td" );
            }
        } else {
            $this->template->addFile( "password_form.html" );
        }
    }
        


}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
