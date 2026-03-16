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
* @package i2ce
* @subpackage user
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1.0
* @since v4.1.0
* @filesource 
*/ 
/** 
* Class I2CE_UserAccess_Single
* 
* @access public
*/


class I2CE_UserAccess_Single extends I2CE_UserAccess_Mechanism{

    /**
     * Check to see if we should do a automatic login
     */
    public function doAutoLogin() {
        return ($this->options['auto_login'] && $this->options['auto_login_user'] && $this->options['user_details']['password'] === false);
    }
     /**
      * verifies that the specified user has the specified password.  
      * @param string $username
      * @param string $password
      * @returns boolean
      */
     public function _userHasPassword($username,$password) {
         if ($this->options['user_details']['password'] === false) {
             return false;
         } else {
             return ($this->options['username'] == $username && $password == $this->options['user_details']['password']);
         }
     }


    /**
     * ensrure default options are set
     * @param array $options
     * @returns array 
     */
    public function ensureDefaultOptions($options) {
        if (!array_key_exists('admin_details',$options) || !is_array($options['admin_details'])) {
            $options['admin_details']  = array();
        }
        $admin_dets =array(
            'firstname'=>'System',
            'lastname'=>'Administator',
            'email'=>'root@localhost',
            'locale'=>'en_US'
            );
        foreach ($admin_dets as $key=>$val) {
            if (!array_key_exists($key, $options['admin_details'])) {
                $options['admin_details'][$key] = $val;
            }
        }
        if (!array_key_exists('username',$options) || !$options['username']) {
            $options['username']= 'guest';
        }
        if (!array_key_exists('userrole',$options) || !$options['userrole']) {
            $options['userrole']= 'guest';
        }
        if (!array_key_exists('userid',$options) ) {
            $options['userid']= '1';
        }
        if (!array_key_exists('user_details',$options) || !is_array($options['user_details'])) {
            $options['user_details']  = array();
        }
        $user_dets =array(
            'password'=>false,
            'firstname'=>'',
            'lastname'=>'',
            'email'=>'root@localhost',
            'locale'=>'en_US'
            );
        foreach ($user_dets as $key=>$val) {
            if (!array_key_exists($key, $options['user_details'])) {
                $options['user_details'][$key] = $val;
            }
        }
        return $options;
    }


    /**
     * An array of options for connecting and querying to the ldap server
     */
    protected $options;

    

    /**
     * Whether or not this acccess mechansim can change a user's password
     * @returns boolean
     */
     public function canChangePassword() {         
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
     * Whether or not this acccess mechansim can create ne users
     * @returns boolean
     */
     public function canCreateNewUser() {
         return false;
     }


    /**
     * Gets an array of the allowed user details such as email, firstname, lastname
     * @returns array
     */
    public function getAllowedDetails() {
        return array_keys($this->options['user_details']);
    }




    /**
     * Gets the display name for the user     
     * @param string $username
     * @param array $user details
     * @returns string
     */
    public function displayName($username, $user) {
        $details = array();
        if (array_key_exists('firstname',$user) 
                   && array_key_exists('lastname',$user)
                   && $user['lastname']
            ) {
            if ($user['firstname']) {
                return $user['firstname'] . ' ' . $user['lastname'];
            } else {
                return $user['lastname'];
            }
        } else {
            return parent::displayName($username, $user);
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
         if ($username != $this->options['username']) {
             return false;
         }
         $dets = array();
         foreach ($details as $det) {
             if (array_key_exists($det,$this->options['user_details'])) {
                 $dets[$det] = $this->options['user_details'][$det];
             } else {
                 $dets[$det] = '';
             }
         }
         $locale = null;
         if (I2CE::getConfig()->setIfIsSet($locale,"/user_prefs/users/{$username}/preferred_locale") && is_string($locale) && strlen(trim($locale)) > 0) {
             $dets['locale'] = $locale;
         }
         if ($getRole) {
             $dets['role'] = $this->options['userrole'];
         }
         return $dets;
     }




    /**
     * Gets the userss by the indicated  details as well as the  role. worker method
     * @oaram boolean $role Defaults to false
     * @param array $details of string.  The details we wish on the user.  Defaults to empty array
     * @returns array of usernames which mathc the give input
     */
    public function _getUsersByInfo($role = false, $details=array()) {
        if ($role) {
            if ($role == 'admin') {
                if ( $this->options['userrole'] == 'admin') {
                    $check= array_unique(array($this->options['username'],$this->options['admin_user']));
                } else {
                    $check= array($this->options['admin_user']);
                }
            } else if ($role == $this->options['userrole']) {
                $check =  array($this->options['username']);
            }
        } else {
            $check= array_unique(array($this->options['username'],$this->options['admin_user']));
        }
        $usernames = array();
        foreach ($check as $username) {
            $user_dets = $this->getUserInfo($username,false,array_keys($details));
            $matches = true;
            foreach ($details as $detail=>$value) {            
                if (!array_key_exists($detail,$user_dets) ||  $user_dets[$detail] != $value) {
                    $matches = false;
                    break;
                }
            }
            if (!$matches) {
                continue;
            }
            $usernames[] = $username;
        }
        return $usernames;
    }



     /**
      * See if a user is in the system
      * @param string $username
      * @param boolean $has_role.  If true  we verify that they have a role set.
      * @returns boolean.
      */
     public function _userExists($username, $has_role) {
         return ($username == $this->options['username']);
     }




    /**
     * Gets the user ids
     * @param string $username

     */
    public function _getUserIds() {
        return array($this->options['userid']);
    }




    /**
     * Gets the user id from the username
     * @param int $userid
     * @returns string or false on failure
     */
    public function _getUserNameFromId($userid) {
        if ($this->options['userid'] == $userid) {
            return $this->options['username'];
        } else {
            return false;
        }
    }




    /**
     * Gets the user id from the username. Worker method
     * @param string $username
     * @returns int or false on failure
     */
    public function _getUserId($username) {
        if ($username != $this->options['username']) {
            return false;
        } else {
            return $this->options['userid'];
        }
    }







    /**
     *  Sets the role in the ldap hierarchy for the indicated user
     * @param string $username
     * @param string $role
     * @returns boolean. true on success
     */
    protected function setRole($username,$role) {
        return false;
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
     * Set the person details.
     * @param string $username
     * @param array $details
     * @param boolean $create
     */ 
    protected function setPerson($username,$password,$details, $create ) {
        return false;
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
         return false;
     }

     



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
