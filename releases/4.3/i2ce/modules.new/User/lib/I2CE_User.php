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
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v2.0.0
 * @version v2.0.0
 */
/**
 * Object for dealing with system users.
 *
 * This class uses the {@link I2CE_Form} interface to handle editing of users from within
 * the system as well as handles role access for pages.
 * @package I2CE
 * @access public
 */
if (!class_exists('I2CE_User',false)) {
    class I2CE_User  extends I2CE_Fuzzy{


        /**
         * @var public mixed $role
         */
        public $role = false;
        
        /**
         * @var public string $username the username
         */
        public $username = '0';
        /**
         * @var public string $role the role associated to the user
         */
        /**
         * @var protected array $details.  An array of user details
         */
        protected $details = array();

        /**
         * @var protected boolean $logged_in  flags the user as logged in
         */
        protected $logged_in = false;

        /**
         * check if  the specified detail can be set
         * @params string $detail
         * @returns boolean
         */
        public static function hasDetail($detail) {
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism");
                return false;
            }      
            return in_array($detail,$userAccess->getAllowedDetails());
        }



        /**
         * Generate a password
         * @returns string
         *
         */
        public static function generatePassword() {
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism");
                return false;
            }      
            return $userAccess->generatePassword();
        }

        /**
         * Change the password for this user.
         * 
         * This will update a user's record to change the password in the database.  It checks to make sure the
         * new password matches the confirmation.
         * 
         * @param string $old_password
         * @param string $new_password
         * @returns boolean. true on success
         */
        public function changePassword( $old_password,$new_password ) {
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism");
                return false;
            }      
            if (!$userAccess->changePassword($this->username,$old_password,$new_password)) {
                return false;
            }
            return true;
        }

        /**
         * Set the password for the user
         * @param string $password.  
         * @returns mixed. true on success. on failure it is false or a the message to display back to the user signifying why it  failed.
         * 
         */
        public function setPassword($password){
            return $this->changePassword(false,$password);
        }


        /**
         * See if a user is in the system
         * @param string $username
         * @param boolean $has_role.  If true we verify that they have a role set.
         * @returns boolean.
         */
        public static function userExists($username, $has_role) {
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism");
                return false;
            }      
            return $userAccess->userExists($username,$has_role);
        }
    
        /**
         * Returns the display name of the given detail
         * @param string $detail
         * @returns string
         */
        public static function getDetailName($detail) {
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism");
                return null;
            }      
            return $userAccess->getDetailName($detail);
        }

        /**
         * Checks to see if the indicated detail is required
         * $param string $detail
         * returns boolean
         */
        public static function isRequired($detail) {
            if (!self::hasDetail($detail)) {
                return false;
            }
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism");
                return false;
            }      
            return $userAccess->isRequired($detail);            
        }


        /**
         * @var protected string $localeThe preferred locale for the user
         */
        protected $locale =null;

        /**
         * Gets the prefered locales for the user
         * @returns $string
         */
        public function getPreferredLocale() {
            if (is_string($this->locale) && strlen($this->locale) > 0) {
                return $this->locale;
            }
            if (array_key_exists('locale',$this->details) && strlen(trim($this->details['locale']))> 0 ) {                
                $this->locale = $this->details['locale'];
            }
            if (!$this->username || !I2CE::getConfig()->setIfIsSet($this->locale,"/user_prefs/users/{$this->username}/preferred_locale")) {
                $this->locale =  I2CE_Locales::getPreferredLocale();
            }
            $this->locale = I2CE_Locales::ensureSelectableLocale($this->locale);
            return $this->locale;
        }
        



        /**
         * Checks if  the prefered locale for the user has been saved
         * @returns boolean
         */
        public function hasPreferredLocale() {
            if (array_key_exists('locale',$this->details) && $this->details['locale'] ) {                
                return true;
            }
            $locale = false;
            if ($this->username  && I2CE::getConfig()->setIfIsSet($locale,"/user_prefs/users/{$this->username}/preferred_locale")  && $locale) {
                return true;
            }
            return false;
        }
        



        /**
         * Sets the prefered locales for the user
         * @param string $locale
         * @param boolean $save.   Defaults to true.
         * @returns string $locales.  false on failure  string or array of strings, the locales on success
         */
        public function setPreferredLocale($locale, $save = true) {                
            $locale = I2CE_Locales::ensureSelectableLocale($locale);
            I2CE_Locales::setPreferredLocale($locale);
            if (!$this->username == 'i2ce_admin' ) {
                if ($this->id <= 0 || !$this->username) {                
                    //I2CE::raiseError("Could not save locales for user no id or username ({$this->id}/{$this->username})");
                    return $locale;
                }
                if (self::hasDetail('locale')) {                    
                    $this->details['locale'] = $locale;
                    if ($save && !$this->save()) {
                        I2CE::raiseError("Cannot save preferred locale");
                    }
                }
            } 
            $userPrefs = I2CE::getConfig()->traverse("/user_prefs/users/{$this->username}",true);
            if (!$userPrefs instanceof I2CE_MagicDataNode) {
                I2CE::raiseError("Error in accessing user prefs for {$this->username}");
                return $locales;
            }
            $userPrefs->preferred_locale = $locale;
            return $locale;
        }


        /**
         *  @var protected mixed $id.  False or int, the id for this user. 
         */
        protected $id = false;


        /**
         * Gets the user id from the username
         * @returns int or false on failure
         */
        public function getId() {
            return  $this->id;
        }

        
        /**
         * Checks to see if the specified user has a default password
         * @param string $username
         * @returns boolean
         */
        public static function userHasDefaultPassword($username) {
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism set");
                return false;
            }            
            return $userAccess->userHasDefaultPassword($username);
        }
        
        /**
         * Checks to see if the specified user has the given password
         * @param string $username
         * @param string $password
         * @returns boolean
         */
        public static function userHasPassword($username,$password) {
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism set");
                return false;
            }            
            return $userAccess->userHasPassword($username, $password);
        }

        /**
         * Gets the auto login user, if any
         * @returns mixed. false on failure the username if a autologin user has been set
         */
        public static function getAutoLoginUser() {
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism set");
                return false;
            }            
            return $userAccess->getAutoLoginUser();
        }
        
        /**
         * Create a new instance of a user.
         * 
         * If the username isn't given then it will be determined from the session array.
         * @param integer $username The id of the user in the database.  or '0' (the detauls) to get it from the session
         * @param boolean $populate A flag to determine if the user should be automatically populated at creation. Defaults to true
         * @param boolean $checkSession A flag to determine if we should check the $_SESSION for user information Defaults to true
         * @param boolean $log.  Defaults to true which means we log the activity
         */
        public function __construct( $username = '0', $populate = true , $checkSession = true, $log = true ) {
            if ( $username == '0' ) {                
                if ( $checkSession && I2CE_UserAccess_Mechanism::hasSession()) {
                    $checkSession = true;
                    $this->username = I2CE_UserAccess_Mechanism::getSessionUserName();
                } else {
                    $checkSession = false;
                }
                if (!$checkSession
                    && array_key_exists('PHP_AUTH_USER',$_SERVER)
                    && ($basic_auth_user = $_SERVER['PHP_AUTH_USER'])
                    && array_key_exists('PHP_AUTH_PW',$_SERVER)
                    && ($basic_auth_pass = $_SERVER['PHP_AUTH_PW'])
                    ){
                    $this->login($basic_auth_user,$basic_auth_pass);
                }
            } else {
                $this->username = $username;
            }
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism set");
                return ;
            }            
            if ( $log && $this->username != '0' && $userAccess->hasBeenLoggedOut( $this->username ) ) {
                $msg = "This username has been logged in on another computer.";
                $msg = I2CE::getConfig()->setIfIsSet($msg, "/config/site/single_login_message" );
                $this->userMessage($msg);
                I2CE_UserAccess_Mechanism::unsetSession();
                return;
            }

            $this->logged_in = false;
            if ($this->username == '0') {
                if (!$userAccess->doAutoLogin()) {
                    return;
                }
                $t_username = $userAccess->getAutoLoginUser();
                if ($t_username ===false) {
                    $t_username = 'i2ce_admin';
                }
                $this->username =$t_username;
                $this->logged_in = true;
            }

            foreach ($userAccess->getAllowedDetails() as $detail) {
                $this->details[$detail] = null;
            }
            if ($log) {
                $userAccess->logActivity($this->username,'access');
            }

            if ($populate) {
                $details = null;
                $role = null;
                $id = false;
                if ($checkSession) {
                    $details = I2CE_UserAccess_Mechanism::getSessionDetails();
                    $role = I2CE_UserAccess_Mechanism::getSessionRole();
                    $id = I2CE_UserAccess_Mechanism::getSessionId();
                }
                if (! $this->populate($details,$role,$id)) {
                    I2CE::raiseError( "Could not get  user information");
                    return;
                }
                if ($checkSession) {
                    $this->logged_in =true;
                }
            }
        }
    

        /**
         * Checks to see if this user has been logged in
         */
        public function logged_in() {
            return ($this->username != '0' && $this->logged_in);
        }


        /**
         * Login the user and populate their details
         * 
         * then an error message will be set on the {@link I2CE_Template template}.
         *
         * @param string $username
         * @param string $password
         * @returns boolean.  True on success, error message  of failure
         */
        public function login( $username, $password ) {
            if ( $this->logged_in() ) {
                return true;
            }
            $this->username = $username;
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism set");
                return false;
            }            
            if (! $userAccess->userHasPassword($username,$password)) {
                $userAccess->logActivity($username,'invalid_login');
                return "Invalid Username or Password";
            }
            if (! $this->populate()) {
                return "Could not get your user information";
            }
            $this->logged_in = true;
            I2CE_UserAccess_Mechanism::setSession($this->username,$this->role,$this->details, $this->id);
            if ( !$userAccess->logOutPreviousSessions( $username ) ) {
                I2CE::raiseError( "Unable to log out previous sessions for $username" );
            }
            $userAccess->logActivity($username,'login');
            return true;
        }


        /**
         * Login the user and populate their details
         * 
         * then an error message will be set on the {@link I2CE_Template template}.
         *
         * @param string $username
         * @returns boolean.  True on success, error message  of failure
         */
        public function passwordlessLogin( $username ) {
            $this->logout();
            $this->username = $username;
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism set");
                return false;
            }            
            if (! $this->populate()) {
                return "Could not get your user information";
            }
            $this->logged_in = true;
            I2CE_UserAccess_Mechanism::setSession($this->username,$this->role,$this->details, $this->id);
            $userAccess->logActivity($username,'login');
            return true;
        }



        /**
         * Populate the member variables of this object.
         * 
         * This will also update the user log to show the latest activity for this login.
         * @param mixed $details.  If null, we try to get the details and rolefrom the access mechanism
         * if an array, it is the array of details with key the name of details.  Defaults to null.
         * @param mixed role.  Default to null otherwise it should be a string, the role.
         * @param mixed $id Defaults to false otherwise it should be an int  the id.
         * @returns true on success
         */
        public function populate($details = null, $role = null, $id = false ) {
            $this->details = array();
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism set");
                return false;
            }            
            if ( $this->username == '0' ) {
                I2CE::raiseError("Bad username\n");
                return false;
            }
            if (!is_array($details)) {
                $role = false;
                $id = false;
                $details= $userAccess->getUserInfo($this->username, true,$userAccess->getAllowedDetails());
                if (!is_array($details)) {
                    $details = array();
                }
                if (array_key_exists('role',$details)) {
                    $role = $details['role'];
                    unset($details['role']);
                }
            }            
            if ($id === false) {
                $id = $userAccess->getUserId($this->username);
            }
            $this->id = $id;
            $this->role = $role;
            $this->details = $details;
            return true;
        }

        /**
         * Magic method to set user details
         * @param string $detail
         * @param mixed $value
         */
        public function __set($detail,$value) {
            if (self::hasDetail($detail)) {
                if ($detail == 'locale') {
                    $value = I2CE_Locales::ensureSelectableLocale($value);
                }
                $this->details[$detail] = $value;                
            } else      if ($detail == 'locale')  {
                $this->setPreferedLocale($value, false);
            }
        }

        /**
         * Magic method to unset user details
         * @param string $detail
         */
        public function __unset($detail) {
            $this->details[$detail] = '';;
        }

        /**
         * Magic method to get user details
         * @param string $detail
         * @returns mixed. false on failure
         */
        public function __get($detail) {
            if (array_key_exists($detail,$this->details)) {
                if ($detail == 'locale') {
                    return I2CE_Locales::ensureSelectableLocale($this->details['locale']);
                } else {
                    return $this->details[$detail];
                }
            } else if ($detail == 'locale') {
                return $this->getPreferedLocale();
            } else {
                return false;
            }
        }
    
        /**
         * Get's the role associated with a user.
         * @returns string
         */
        public function getRole() {
            return $this->role;
        }


        /**
         * Get's the role associated with a user.
         * @param string $ole
         */
        public function setRole($role) {
            $this->role = $role;
        }
    

        /**
         * Log the user out of the system.
         * @global array
         */
        public function logout() {
            if ( !$this->logged_in() ) {
                return;
            }
            I2CE_UserAccess_Mechanism::unsetSession();
            unset($_SESSION['referal']);
            $this->logged_in = false;
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism set");
                return false;
            }            
            $userAccess->logActivity($this->username,'logout');
        }


        /**
         * Get the username 
         */
        public function username() {
            return $this->username;
        }

        /**
         * @return string The display name of this user.
         */
        public function displayName() {
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism set");
                return false;
            }            
            return $userAccess->displayName($this->username, $this->details);
        }



        /**
         * Gets the userss by the indicated  details as well as the  role. 
         * @oaram boolean $role Defaults to false
         * @param array $details of string.  The details we wish on the user.  Defaults to empty array
         * @param boolean $include_internal.  Defaults to true in which case we include the internal administator
         * user if it matches the given details/role
         * @returns mixed.  I2CE_User on success, false on failure.
         */
        static public function findUsersByInfo($role = false, $details = array(), $include_internal  = true) {
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism");
                return array();
            }            
            return $userAccess->getUsersByInfo($role,$details, $include_internal);
        }
    
        /**
         * Saves the user to the database.
         * 
         * This method saves all the user data and updates the access the user has for this system.
         * @param mixed $password.  If it is a string, it is the password to set for the user.  detaulst to false in which
         * case we do not set the password
         */
        public function save($password = false) {
            $userAccess= I2CE::getUserAccess();
            if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
                I2CE::raiseError("No user access mechanism" );
                return false;
            }      
            if ($password) {
                if (!self::userExists($this->username,false)) {                    
                    I2CE::raiseError("User {$this->username} does not exist, creating");
                    return $userAccess->createUser($this->username,$password, $this->role, $this->details);
                } else {
                    if (!$userAccess->setUserInfo($this->username,$this->role, $this->details)) {
                        return false;
                    }
                    return ($this->setPassword($password) === true);
                }
            } else {
                if (!self::userExists($this->username,false)) {
                    I2CE::raiseError("No password set for new user {$this->username}");
                    return false;
                } else {
                    return $userAccess->setUserInfo($this->username,$this->role, $this->details);
                }
            }
        }


    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
