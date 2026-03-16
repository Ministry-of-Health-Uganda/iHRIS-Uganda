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
 * Handles the login procedure for gaining access to the site.
 * @package I2CE
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying the login page.
 * @package I2CE
 * @access public
 */
class I2CE_PageLogin extends I2CE_Page {

    protected function getHome() {
        $home = 'home';
        if ( ($role = $this->user->getRole())) {
            I2CE::getConfig()->setIfIsSet($home,"/I2CE/formsData/forms/role/$role/fields/homepage");
        }
        return $home;
    }

    /**
     * Perform the main actions of the page.
     * @global array Get the home page from the global configuration
     */
    protected function action() {
        if ( $this->request_exists('message') && $this->request('message') ) {
            $this->template->userMessage($this->request('message'), 'default', false);
        }
        $i2ce_config = I2CE::getConfig()->I2CE;  
        parent::action();
        $this->template->setBodyId( "loginPage" );
        if ( $this->isPost() ) {
            if ( $this->post('submit') == "Login as Guest" ) {
                $this->post['username'] = $i2ce_config->guest->account;
                $this->post['password'] = $i2ce_config->guest->password;
            }
            $message = $this->user->login( $this->post('username'), $this->post('password') );
            if (is_string($message)) {
                $this->template->setDisplayDataImmediate('error_message',$message);
            } else if ($message === true) {
                //the user was logged in
                if ( I2CE_Locales::getPreferredLocale() != I2CE_Locales::getBrowserPreferredLocale()) {
                    $this->user->setPreferredLocale(I2CE_Locales::getPreferredLocale());
                }
            }
        }
        if ( $this->user->logged_in() ) {
            if (array_key_exists('referal', $_SESSION) && $_SESSION['referal']) {
                $site_url = $this->getAccessedBaseURL();
                $referal = $_SESSION['referal'];
                unset($_SESSION['referal']);
                if (($site_url . $this->page) == $referal) {   
                    //there is an off chance that we are redirect from the login page.  this can happen if we initialize the site by accessing the login page
                    $referal = $this->getHome();
                }
                if (preg_match('/login/',$referal) || preg_match('/logout/',$referal)) {
                    $referal = $this->getHome();
                }
            } else {
                $referal = $this->getHome();
            }
            
            if ( $this->user->username != 'i2ce_admin' && I2CE_User::userHasDefaultPassword($this->user->username) ) {
                $this->userMessage(
                    "Please you must change your default password before you continue using the system!"
                    ,"notice"
                    );
                $this->setRedirect('password');
            }
            else{
              $this->setRedirect($referal);
              }
                        
            return true ;
        }               
        if ($default_password = I2CE_User::userHasPassword('administrator','administrator')) {
            $username = $this->template->query('//input[@name="username"]');
            if($username->length == 1 ) {
                $username->item(0)->setAttribute('value','administrator');
            }
            $password = $this->template->query('//input[@name="password"]');
            if($password->length == 1 ) {
                $password->item(0)->setAttribute('value','administrator');
            }
        } else if ( (  $autologinuser = I2CE_User::getAutoLoginUser()) !== false) {
            $username = $this->template->query('//input[@name="username"]');
            if($username->length == 1 ) {
                $username->item(0)->setAttribute('value',$autologinuser);
            }
            
        }
        $this->template->addHeaderLink("welcomeText.css");
        if ( $this->user->logged_in() && $this->user->username=='administrator' && $default_password) {
            $this->userMessage(
                "Your password is currently set to the default password, administrator.  Please change this by clicking on the \"Change Password\" link  below."
                ,"notice"
                );
            $this->userMessage(
                "If you have not already done so, please create a new user with a non-administrative role for everyday use."
                ,"notice"
                );
            
        }

    }   
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
