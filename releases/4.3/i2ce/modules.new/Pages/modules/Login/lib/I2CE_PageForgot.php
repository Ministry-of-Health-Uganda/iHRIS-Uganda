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
 * This page handles the forgotten username and/or password procedure.
 * 
 * When a user forgets his or her name and/or password this page allows him or her to reset
 * it or just view the username given the email address associated with the user.
 * @package I2CE
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying the forgotten password page.
 * @package I2CE
 * @subpackage Common
 * @access public
 */
class I2CE_PageForgot extends I2CE_Page {



        
    protected function mailPassword($email,$username,$password) {
        $i2ce_config = I2CE::getConfig()->I2CE;
        $msg =
              "The password for your account in " . $i2ce_config->template->prefix_title . " has been reset.\n\nUsername: " . $username
            . "\nPassword: " . $password . "\n" ;
        if (!I2CE_Mailer::mail( $email, array('Subject'=>"New " . $i2ce_config->template->prefix_title . " Password"), $msg)) {
            I2CE::raiseError("Could not mail reset password to $username");
            return false;
        }
        $this->userMessage( "Your password has been reset and is being mailed to you now." );
        return true;
    }
    

        
    /**
     * Perform the main actions of the page.
     * @global array Get the home page from the global configuration
     */
    protected function action() {
        parent::action();        
        if ( $this->user->logged_in() ) {
            $this->setRedirect('home');
            return;
        }               
        $access = I2CE::getUserAccess();
        $has_email  = $access instanceof I2CE_UserAccess_Mechanism &&  $access->canChangePassword() &&  I2CE_User::hasDetail('email');
        $this->template->setBodyId( "loginPage" );
        $this->template->setDisplayDataImmediate('has_email' , $has_email);
        if ( !$this->isPost() || !$has_email ) {
            return;
        }
        if ($this->post('submit') == "Reset") {
            if (I2CE_Validate::checkString( $this->post('username') ) && I2CE_User::userExists($this->post('username'),true )) {
                $user = new I2CE_User($this->post('username'),true,false,true);
                $email = $user->email;
                $valid_email =  I2CE_Validate::checkEmail( $email );
                $pass =trim(I2CE_User::generatePassword());
                if ($user->getRole() != 'guest' && $valid_email && $pass &&  $user->setPassword($pass) ) {
                    if ($this->mailPassword($email,$this->post('username'),$pass)) {
                        $this->template->addTextNode( "error_message", "Your password has been reset and mailed to you." );
                    } else {
                        $this->template->addTextNode( "error_message", "Your password has been reset, but could not mailed to you. Please contact your system administrator" );
                    }
                } else {
                    $this->template->addTextNode( "error_message", "Your password could not be reset.  Please contact your system administrator to change your password." );
                }
            } else {
                $this->template->addTextNode( "error_message", "Your username could not be found in the database.  Please contact your System Administrator." );
            }
        } elseif ( $this->post('submit') == "View" ) {            
            $usernames  = I2CE_User::findUsersByInfo(false,array( 'email'=> $this->post('email') ));
            if (is_array($usernames) && count($usernames) == 1) {
                reset($usernames);
                $this->template->addText( '<p id="error_message">Your username is: <b>' . current($usernames)
                                          . '</b><br />Enter it below to reset your password or return to the login page to login.</p>', 'p' );
            } else {
                $this->template->addTextNode( "error_message", "That email address was not found in the system.  Please contact your System Administrator." );
            }
        } else {
            $this->template->addTextNode( "error_message", "Please click one of the submit buttons or only enter one text field." );
        }
    }






}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
