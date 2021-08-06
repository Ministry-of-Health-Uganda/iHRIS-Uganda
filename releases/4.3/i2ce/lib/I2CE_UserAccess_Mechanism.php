<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
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
* @package I2CE
* @subpackage i2ce
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.3
* @since v4.0.3
* @filesource 
*/ 
/** 
* Class I2CE_UserAccess_Mechanism
* 
* @access public
*/


class I2CE_UserAccess_Mechanism extends I2CE_Fuzzy {
    /**
     * @var protected array $options. Intialization options
     */
    protected $options = array();

    /**
     * @var protected string $protocol.  The user access protocol this is.
     */
    protected $protocol;
    /**
     * Create a new instance of a default I2CE  table user access mechanism
     */
    public function __construct() {
        $protocol = substr(get_class($this),16);        
        if (!$protocol) {
            $protocol = 'DEFAULT';
        }
        $this->protocol = $protocol;
        $init = I2CE::getUserAccessInit($protocol);
        if (empty($init)) {
            $options = array();
        } else {
            $options = json_decode($init,true);        
            if( !is_array($options)) {
                I2CE::raiseError("Invalid user access initilization string");
                $options = array();
            }
        }
        if (!array_key_exists('admin_user',$options)) {
            $options['admin_user'] = 'i2ce_admin';
        }
        if (!array_key_exists('admin_pass',$options)) {
            $options['admin_pass'] = I2CE_PDO::details( 'pass' );
        }
        if (!array_key_exists('admin_details',$options) || !is_array($options['admin_details'])) {
            $options['admin_details']= array();
        }
        if (!array_key_exists('auto_login',$options)) {
            $options['auto_login'] = 0;
        }
        if (!array_key_exists('auto_login_user',$options)) {
            $options['auto_login_user'] = false;
        }
        $this->options =  $this->ensureDefaultOptions($options);
    }
    
    /**
     * Check to see if we should do a automatic login
     */
    public function doAutoLogin() {
        return ($this->options['auto_login'] && $this->options['auto_login_user']);
    }
    /**
     *  Get the username for an autologin
     * @returns string
     */
    public function getAutoLoginUser() {
        return ($this->options['auto_login_user']);
    }

    /**
     * Ensure default options are set
     * @param array $options
     * @returns array;
     */
    protected function ensureDefaultOptions($options) {
        
        return $options;
    }




    /**
     * Generate a password
     * @returns string
     *
     */
    public function generatePassword() {
        include_once "Text/Password.php";
        if (class_exists('Text_Password',false)) {
            $tp = new Text_Password();
            $pass = $tp->create( 8 );
        } else {
            $allowed = "abcdefghijklmnopqrstuvwxyz1234567890";
            $len = strlen($allowed) - 1;
            $pass ='';
            for ($i=0; $i < 8; $i++) {
                $pass .= $allowed[rand(0,$len)];
            }
        }
        return $pass;
    }



    /**
     * Gets the user ids
     * @returns array of ids
     */
    final public function getUserIds() {
        $ids =  $this->_getUserIds();
        if (!in_array('0',$ids)) {
            $ids[] = '0';
        }
        return $ids;
    }


    /**
     * Gets the user ids
     * @param string $username

     */
    public function _getUserIds() {
        return array();
    }

    /**
     * Gets the user id from the username
     * @param string $username
     * @returns int or false on failure
     */
    final public function getUserId($username) {
        if ($username == $this->options['admin_user']) {
            return 0;
        } else {
           $id =  $this->_getUserId($username);
           if ($id == 0) {
               //I2CE::raiseError("Invalid id for $username");
               return false;
           }
           return $id;
        }
    }


    /**
     * Gets the user id from the username. Worker method
     * @param string $username
     * @returns int or false on failure
     */
    public function _getUserId($username) {
        return false;
    }

    /**
     * Gets the user id from the username
     * @param int $userid
     * @returns string or false on failure
     */
    final public function getUserNameFromId($userid) {
        if ($userid == '0') {
            return $this->options['admin_user'];
        } else {
            return $this->_getUserNameFromId($userid);
        }
    }

    /**
     * Gets the user id from the username
     * @param int $userid
     * @returns string or false on failure
     */
    public function _getUserNameFromId($userid) {
        return false;
    }


    /**
     * Whether or not this acccess mechansim can create ne users
     * @returns boolean
     */
     public function canCreateNewUser() {
         return false;
     }

    /**
     * Whether or not this acccess mechansim can create edit details of existing users
     * @returns boolean
     */
     public function canEditUserDetails() {
         return false;
     }


     /**
      * checks to see if a user has a default password.  
      * @param string $username
      * @param string $password
      * @returns boolean
      */
     final public function userHasDefaultPassword($username) { 
        if($username != 'i2ce_admin'){
          return $this->_userHasDefaultPassword($username);
        }
        else{
            return true;
          }
     }

     /**
      * verifies that the specified user has the specified password.  
      * @param string $username
      * @param string $password
      * @returns boolean
      */
     public function _userHasDefaultPassword($username) {
         return false;
     }


     /**
      * verifies that the specified user has the specified password.  
      * @param string $username
      * @param string $password
      * @returns boolean
      */
     final public function userHasPassword($username,$password) {
         if ($this->options['admin_user'] == $username) {
             return ($this->options['admin_pass'] == $password);
         } else { 
             return $this->_userHasPassword($username,$password);
         }
     }

     /**
      * verifies that the specified user has the specified password.  
      * @param string $username
      * @param string $password
      * @returns boolean
      */
     public function _userHasPassword($username,$password) {
         return false;
     }


    /**
     * Gets an array of the allowed user details such as email, firstname, lastname
     * @returns array
     */
     public function getAllowedDetails() {
         return array();
     }

    /**
     * Gets the indicated user details as well as the  role
     * @param string $username the user name
     * @oaram boolean $getRole Defaults to false
     * @param array $details of string.  The details we wish on the user.  Defaults to empty array
     * @returns array with indexed by the values of $details and values the corresponding detail.  Returns false on failture
     */
     final public function getUserInfo($username, $getRole = false, $details=array()) {
        $allowed = $this->getAllowedDetails();
        foreach ($details as $i=>$detail) {
            if (!in_array($detail,$allowed)) {
                unset($details[$i]);
            }
        }
        if ($username == $this->options['admin_user']) {
            $return = array();
            if ($getRole) {
                $return['role'] = 'admin';
            }
            foreach($details as $detail) {
                if (!array_key_exists($detail, $this->options['admin_details'])) {
                    $return[$detail] = null;
                } else {
                    $return[$detail] = $this->options['admin_details'][$detail];
                }
            }
            return $return;
        } else {
            return $this->_getUserInfo($username,$getRole, $details);        
        }
    }



    /**
     * Gets the indicated user details as well as the  role. worker function
     * @param string $username the user name
     * @oaram boolean $getRole Defaults to false
     * @param array $details of string.  The details we wish on the user.  Defaults to empty array
     * @returns array with indexed by the values of $details and values the corresponding detail. Returns false on failure
     */
     protected function _getUserInfo($username, $getRole = false, $details=array()) {
         return false;
     }


    /**
     * sets the indicated user details/role.
     * @param string $username the user name
     * @param mixed $setRole.  Defaulst to false.  If a string, it is the role to assign to the user
     * @param array $details of string.  The keys are the detail, e.g. 'email' and the value at the key is the value of that detail
     * @returns boolean true on success
     */
    final public function setUserInfo($username, $setRole = false, $details = array()) {
        if ($username == $this->options['admin_user']) {
            return false;
        }
        if (count($details) > 0 &&!$this->canEditUserDetails()) {
            return false;
        }
        $allowed = $this->getAllowedDetails();
        foreach ($details as $detail=>$value) {
            if (!in_array($detail,$allowed)) {
                unset($details[$detail]);
            }
        }
        return $this->_setUserInfo($username,$setRole,$details);
    }
    
    /**
     * sets the indicated user details/role . worker function
     * @param string $username the user name
     * @param mixed $setRole.  Defaulst to false.  If a string, it is the role to assign to the user
     * @param array $details of string.  The keys are the detail, e.g. 'email' and the value at the key is the value of that detail
     * @returns boolean true on success
     */
    protected  function _setUserInfo($username, $setRole = false, $details= array()) {
        return false;
    }

    /**
     * Gets the userss by the indicated  details as well as the  role. worker method
     * @oaram boolean $role Defaults to false
     * @param array $details of string.  The details we wish on the user.  Defaults to empty array
     * @param boolean $include_internal.  Defaults to true.  If so, then we include the internal
     * administrator user if they match the given details and role
     * @returns array of usernames which mathc the give input
     */
    public function getUsersByInfo($role = false, $details=array(), $include_internal = true) {        
        $allowed = $this->getAllowedDetails();
        foreach ($details as $i=>$detail) {
            if (!in_array($i,$allowed)) {
                return array();
            }
        }
        $usernames =  $this->_getUsersByInfo($role, $details);        
        //now handle the internal administrator user
        if ($include_internal) {
            if ($role) {
                $matches = ($role == 'admin');
            } else {
                $matches = true;
            }
            if ($matches) {
                foreach ($details as $detail=>$value) {            
                    if (!array_key_exists($detail,$this->options['admin_details']) ||  $this->options['admin_details'][$detail] != $value) {
                        $matches = false;
                        break;
                    }
                }
            }
            if ($matches) {
                $usernames[] = $this->options['admin_user'];
            }        
        }
        return $usernames;
    }



    /**
     * Gets the userss by the indicated  details as well as the  role. worker method
     * @oaram boolean $role Defaults to false
     * @param array $details of string.  The details we wish on the user.  Defaults to empty array
     * @returns array of usernames which mathc the give input
     */
    public function _getUsersByInfo($role = false, $details=array()) {
        return array();
    }



    /**
     * Whether or not this acccess mechansim can change a user's password
     * @returns boolean
     */
     public function canChangePassword() {
         return false;
     }

     /**
      * Returns the display name of the given detail
      * @param string $detail
      * @returns string
      */
     public function getDetailName($detail) {
         return null;
     }


     /**
      * Checks to see if the indicated detail is required
      * $param string $detail
      * returns boolean
      */
     public  function isRequired($detail) {
         return false;
     }

     /**
      * Change the password for this user.
      * 
      * This will update a user's record to change the password in the database.  It checks to make sure the
      * new password matches the confirmation.
      * 
      * @param string $username
      * @param string $old_password.  If false then we don't check against the old password
      * @param string $new_password
      * @returns booelan. true on success
      */
     final public function changePassword( $username, $old_password,$new_password ) {
         if ($username == $this->options['admin_user']) {
             return false;
         }
         if (!$this->canChangePassword()) {
             return false;
         }
         if (!$this->userHasPassword($username,$old_password)) {
             return false;
         }         
         return $this->_changePassword($username,$old_password,$new_password);
     }
    


     /**
      * Change the password for this user. Worker method
      * 
      * This will update a user's record to change the password in the database.  It checks to make sure the
      * new password matches the confirmation.
      * 
      * @param string $username
      * @param string $old_password
      * @param string $new_password
      * @returns boolean true on success
      */
     public function _changePassword( $username, $old_password,$new_password ) {
         return false;
     }




     /**
      *  Create user .
      *  @param string $username
      * @param string $password
      * @param string $role. Defaults to false
      * @param array $details.  Defatuls to empty array,
      * @returns boolean. true on success
      */
     final public function createUser($username, $password, $role, $details= array()) {
         if ($username == $this->options['admin_user']) {
            I2CE::raiseError("Can not create $username -- it is internal admin user");
            return false;
         }         
         if (!$this->canCreateNewUser()) {
             return false;
         }
         if (count($details) > 0 && !$this->canEditUserDetails()) {
             return false;
         }
         if ($this->userExists($username, false)) {
             I2CE::raiseError("Trying to recreate existing user $username");
             return false;
         }
         return $this->_createUser($username,$password,$role, $details);
     }
    

     /**
      *  Create user worker method
      *  @param string $username
      * @param string $password
      * @param string $role Defaults to false
      * @param array $details.  Defatuls to empty array,
      * @returns boolean. true on success
      */
     public function _createUser($username, $password, $role = false, $details= array()) {
         return false;
     }
    



     /**
      * Logs a user activiity
      * @param string $username
      * @param string $actitivty, such as login, logout, access.  Default is access
      * @param int $timesamp. Defaults to null, in which case it should be now
      */
     public function logActivity($username, $activity='access', $timestamp= null) {
         //does nothing
     }

     /**
      * Checks to see if this user has been logged out by other means
      * to invalidate login.
      * This is a placeholder to be implemented in sub classes.
      * @param string $username
      * @return boolean
      */
     public function hasBeenLoggedOut( $username ) {
         return false;
     }

     /**
      * Logs out other sessions with the same username if single login option
      * is set.
      *
      * This does nothing in the base class, but can be implemented in subclasses.
      * @param string $username
      * @return boolean
      */
     public function logOutPreviousSessions( $username ) {
         $single_login = false;
         I2CE::getConfig()->setIfISSet( $single_login, "/config/site/single_login" );
         if ( $single_login ) {
             I2CE::raiseError( "Trying to log out previous sessions, but user access mechanism doesn't support this." );
             return false;
         }
         return true;
     }

     /**
      * See if a user is in the system
      * @param string $username
      * @param boolean $has_role.  If true  we verify that they have a role set.
      * @returns boolean.
      */
     final public function userExists($username, $has_role) {
         if ($username == $this->options['admin_user']) {
             return true;
         } else {
             return $this->_userExists($username,$has_role);
         }
     }


     /**
      * See if a user is in the system
      * @param string $username
      * @param boolean $has_role.  If true  we verify that they have a role set.
      * @returns boolean.
      */
     public function _userExists($username, $has_role) {
         return false;
     }


    /**
     * Gets the display name for the userdetails
     * @param string $username
     * @param array  $user user details
     * @returns string
     */
     public function displayName($username, $user) {
         return $username;
     }


     /**
      * Store info on the authenticated user in session variables
      * @param string $username
      * @param string $role
      * @param array $deatails.  array of user details
      */
     final static public function setSession($username,$role,$details, $id) {
         $_SESSION['user_name'] = $username;         
         $_SESSION['user_role'] = $role;
         $_SESSION['user_details'] = $details;
         $_SESSION['user_id'] = $id;
         $_SESSION['user_activity'] = time();
     }


     /**
      * Store info on the authenticated user in session variables
      * @param string $username
      * @param string $role
      * @param array $deatails.  array of user details
      */
     final static public function unsetSession() {
         unset( $_SESSION['user_name'] );
         unset( $_SESSION['user_role'] );
         unset( $_SESSION['user_details'] );
         unset( $_SESSION['user_id'] );         
         unset( $_SESSION['user_activity'] );
     }

     /**
      * Checks to see if a user has been stored in the session
      */
     final static public function hasSession() {
         if (is_array($_SESSION) && array_key_exists('user_name',$_SESSION) 
                 && $_SESSION['user_name']) {
             $timeout = 0;
             I2CE::getConfig()->setIfIsSet( $timeout, "/config/site/user_timeout" );
             I2CE::getConfig()->setIfIsSet( $timeout, "/user_prefs/timeout/user_timeout" );
             $last_activity = self::getSessionActivity();
//             $timeout = 60;
             if ( $timeout && $last_activity ) {
                 if ( time() - $last_activity > $timeout ) {
                     self::unsetSession();
                     $timeout_message = "You have been logged out due to inactivity.";
                     I2CE::getConfig()->setIfIsSet( $timeout_message, "/config/site/user_timeout_message" );
                     I2CE::getConfig()->setIfIsSet( $timeout_message, "/user_prefs/timeout/user_timeout_message" );
                     if ( ($messages = I2CE_ModuleFactory::instance()->getClass( 'messageHandler' ) ) instanceof I2CE_Module ) {
                        $messages->addUserMessage( null, $timeout_message );
                     }
                     return false;
                 }
             }
             self::updateSessionActivity();
             return true;
         } 
         return false;
     }

     /**
      * Get any username stored in the suession
      * @returns mixed.  an string on succes, null on failture
      */
     final static public function getSessionUserName() {
         if (array_key_exists('user_name',$_SESSION)) {
             return $_SESSION['user_name'];
         } else {
             return null;
         }
     }

     /**
      * Get any user role
      * @returns mixed.  an strint on succes, null on failture
      */
     final static public function getSessionRole() {
         if (array_key_exists('user_role',$_SESSION)) {
             return $_SESSION['user_role'];
         } else {
             return null;
         }
     }


     /**
      * Get any user id
      * @returns mixed.  an int on succes, false on failture
      */
     final static public function getSessionID() {
         if (array_key_exists('user_id',$_SESSION)) {
             return $_SESSION['user_id'];
         } else {
             return false;
         }         
     }

     
     /**
      * Get any user details stored in the session
      * @returns mixed.  an array of details on succes, null on failture
      */
     final static public function getSessionDetails() {
         if (array_key_exists('user_details',$_SESSION) 
             && is_array($_SESSION['user_details'])
             ) {
             return $_SESSION['user_details'];
         } else {
             return null;
         }
     }

     /**
      * Get the last activity time for this session.
      * @return integer
      */
     final static public function getSessionActivity() {
         if (array_key_exists('user_activity',$_SESSION)) {
             return $_SESSION['user_activity'];
         } else {
             return 0;
         }         
      }

     /**
      * Update the session activity time.
      */
     final static public function updateSessionActivity() {
         $_SESSION['user_activity'] = time();
     }
     
  }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
