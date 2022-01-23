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
* @version v4.0.3
* @since v4.0.3
* @filesource 
*/ 
/** 
* Class I2CE_UserAccess_LDAP
* 
* @access public
*/


class I2CE_UserAccess_LDAP extends I2CE_UserAccess_Mechanism{


    /**
     * LDAP escaping function to prevent against injection.
     * 
     * 
     * @param string $str the string to escape
     * @param booleans $for_dn.  Defaults to false.   True if we are escaping for a dn
     * returns string
     */
    protected function ldap_escape($str, $for_dn = false) {
        //Gratefully stolen from   http://www.php.net/manual/en/function.ldap-search.php#90158            
        // see:
        // RFC2254
        // http://msdn.microsoft.com/en-us/library/ms675768(VS.85).aspx
        // http://www-03.ibm.com/systems/i/software/ldap/underdn.html       
        
        if  ($for_dn) {
            $metaChars = array(',','=', '+', '<','>',';', '\\', '"', '#');
        } else {
            $metaChars = array('*', '(', ')', '\\', chr(0));
        }
        $quotedMetaChars = array();
        foreach ($metaChars as $key => $value) $quotedMetaChars[$key] = '\\'.str_pad(dechex(ord($value)), 2, '0');
        $str=str_replace($metaChars,$quotedMetaChars,$str); //replace them
        return ($str); 
    }


    /**
     * An array of options for connecting and querying to the ldap server
     */
    protected $options;



    /**
     * ensrure default options are set
     * @param array $options
     * @returns array 
     */
    public  function ensureDefaultOptions($options) {
        return I2CE_Module_UserAccess_LDAP::ensureDefaultOptions($options);
    }

    
    /**
     * @var protected resource $ldap the ldap connect;
     */
    protected $ldap = null;
    /**
     * Get the ldap connection
     * @param boolean $cached.  Defaiults to true in which case we get the cached connection
     * @param boolean $bind_user.  Defaults to  null in which case we get the user from $this->options['ldap_user']
     * @param string $dn. The dn underwhich the user lives. Defaults to null in which case we user $this->options['ldap_user_dn'];
     * @param boolean $bind_pass.  Defaults to  null in which case we get the user from $this->options['ldap_user']
     * @returns mixed.  False on failure or resource on success
     */
    protected function getConnection($cached = true,$bind_user = null,$bind_dn = null,$bind_pass = null ) {
        if (!$cached || $this->ldap === null) { //we only will try to make a connection once.
            $ldap  = @ldap_connect($this->options['host'], $this->options['port']);
            if (!is_resource($ldap)) {
                I2CE::raiseError("Could not connect to ldap server on {$this->options['host']}:{$this->options['port']}");
                $ldap = false;
                return false;
            }
            @ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
            @ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
            if ($bind_user === null) {
                $bind_user = $this->options['ldap_user'];
            }
            if ($bind_pass === null) {
                $bind_pass = $this->options['ldap_pass'];
            }
            if ($bind_dn === null) {
                $bind_dn = $this->options['ldap_user_dn'];
            }
            if (!@ldap_bind($ldap, 'cn=' . $bind_user . ',' . $bind_dn ,$bind_pass)) {
                I2CE::raiseError("Could not bind to ldap server with as user $bind_user at $bind_dn");
                $ldap = false;                
            }
            if ($cached) {
                $this->ldap = $ldap;
            } else {
                return $ldap;
            }
        }
        return $this->ldap;
    }

    /**
     * Encrypts the password
     * @param string $passwd
     * @returns string
     */
    protected function encryptPassword($passwd) {
        #http://www.openldap.org/doc/admin24/security.html#Password%20Storage

        switch (strtolower($this->options['encrypt'])) {
        case 'md5':
            return '{MD5}' . md5($passwd);            
        case 'plaintext':
            return $passwd;                        
        case 'SSHA':
            /* code stolen gratefyllu from http://www.php.net/manual/en/function.sha1.php#40226*/
            $salt = mhash_keygen_s2k(MHASH_SHA1, $passwd, substr(pack('h*', md5($this->options('salt'))), 0, 8), 4);
            return "{SSHA}".base64_encode(mhash(MHASH_SHA1, $passwd.$salt).$salt);
        case 'SHA':
            /* code stolen gratefuly from http://www.openldap.org/faq/data/cache/347.html */
            return "{SHA}" . base64_encode( pack( "H*", sha1( $passwd ) ) ); 
        }
    }


     /**
      * Returns the display name of the given detail
      * @param string $detail
      * @returns string
      */
    public function getDetailName($detail) {
        if (array_key_exists($detail,$this->options['p_detail_names']) && $this->options['p_detail_names'][$detail]) {
            return $this->options['p_detail_names'][$detail];
        } 
        return $detail;
    }


    /**
     * Whether or not this acccess mechansim can change a user's password
     * @returns boolean
     */
     public function canChangePassword() {         
         return $this->options['can_change_pass'];
     }

    /**
     * Whether or not this acccess mechansim can create edit details of existing users
     * @returns boolean
     */
     public function canEditUserDetails() {
         return $this->options['can_edit_user'];
     }

    /**
     * Whether or not this acccess mechansim can create ne users
     * @returns boolean
     */
     public function canCreateNewUser() {
         return $this->options['can_create_user'];
     }


    /**
     * Gets an array of the allowed user details such as email, firstname, lastname
     * @returns array
     */
    public function getAllowedDetails() {
        return array_keys($this->options['p_details']);
    }




    /**
     * Gets the role query for the application for the given username
     * @param string $username. Defaults to null in which we get the dn for the node containing the uid's
     * @returns string
     */
    protected function getRoleQry($username=null) {
        $qry = 
            'ou=' . $this->options['roles']. ', ' .
            'ou=' . $this->options['app'] .', ' .  
            'ou=' . $this->options['apps'] . ', ' .
            $this->options['dn'];
        if ($username) {
            $qry = 'ou=' .  self::ldap_escape($username,true) .', ' . $qry;
        }
        return $qry;
    }


    /**
     * Gets the id query for the application for the given username
     * @param string $username. Defaults to null in which we get the dn for the node containing the uid's
     * @returns string
     */
    protected function getIdQry($username=null) {
        $qry = 
            'ou=' . $this->options['ids']. ', ' .
            'ou=' . $this->options['app'] .', ' .  
            'ou=' . $this->options['apps'] . ', ' .
            $this->options['dn'];
        if ($username) {
            $qry = 'ou=' .  self::ldap_escape($username,true) .', ' . $qry;
        }
        return $qry;
    }


    
    /**
     * Gets the query for the people for the given username
     * @param string $username. Defaults to null in which we get the dn for the node containing the uid's
     * @returns string
     */
    protected function getPeopleQry($username=null) {
        if ($username) {
            return $this->options['person_comp'] . '=' . self::ldap_escape($username,true) . ', ' .  $this->options['people'] . ', ' .$this->options['dn'];
        } else {
            return   $this->options['people'] . ', ' .$this->options['dn'];
        }
    }
    

    /**
     * verifies that the specified user has the specified password
     * @param string $username
     * @param string $password
     * @returns boolean
     */
    public function _userHasPassword($username,$password) {
        if (!$username || !$password) {
            return false;
        }
        if (strtolower($this->options['encrypt'])== 'bind') {
            if (! ($ldap = $this->getConnection(false,$username,$this->getPeopleQry(),$password ))) {
                return false;
            }
            ldap_unbind($ldap);
            return true;
        } else {
            if ( ! ($ldap = $this->getConnection())) {
                return false;
            }
            if  ( !($r = @ldap_read( $ldap, $this->getPeopleQry($username), $this->options['password_field'] .'=' . self::ldap_escape($this->encryptPassword($password) )))) { 
                return false;
            }
            return @ldap_count_entries($ldap,$r) == 1;
        }
    }
    
    /**
     * Gets the user id 
     * @returns array of ids
     */
    public function _getUserIds() {
        if ( ! ($ldap = $this->getConnection())) {            
            return array();
        }
        if  ( !($r1 = @ldap_list(  $ldap, $this->getIdQry() ,'cn=*', array('cn')))) {
            return array();
        }      
        $entry = ldap_first_entry($ldap,$r1);
        $e = 0;
        $userids = array();
        while ($entry) {
            $values  = ldap_get_values($ldap,$entry,'cn');
            $entry = ldap_next_entry($ldap,$entry);
            if (!is_array($values) || $values['count'] != 1) {
                continue;
            }
            $userids[] =  $values[0];
        }
        return $userids;
    }


    /**
     * Gets the user id from the username
     * @param string $username
     * @returns int or false on failure
     */
    public function _getUserId($username) {
        if (!$username || ! ($ldap = $this->getConnection())) {            
            return false;
        }
        if  ( !($r = @ldap_read( $ldap,  $this->getIdQry($username),'cn=*',array('cn')))) {
            return false;
        }
        if (@ldap_count_entries($ldap,$r) != 1) {
            return false;
        }
        if ( ! ($entry = @ldap_first_entry( $ldap, $r))) {
            return false;
        }
        $values  = ldap_get_values($ldap,$entry,'cn');
        if (!is_array($values) || $values['count'] != 1) {
            return false;
        }
        return $values[0];
    }

    /**
     * Gets the user id from the username
     * @param int $userid
     * @returns string or false on failure
     */
    public function _getUserNameFromId($userid) {
        if (!$useid || ! ($ldap = $this->getConnection())) {
            return false;
        }
        if  ( !($r = @ldap_list(  $ldap, $this->getIdQry() ,  'cn=' . ldap_escape($userid,true), array('cn')))) {
            return false;
        }        
        if ( ! ($result = @ldap_get_results( $ldap, $r))) {
            return false;
        }
        if (count($results) !== 1) {
            return false;
        }
        reset($results);
        $results = current($results);
        if ($results['cn'] !=  $userid) { //sanity check
            return false;
        }
        return $this->getUIDFromDN($results['dn']);
    }

    /**
     * Looks for the UID in a DN
     * @param string $dn
     * @return mixed. String, the uid on success, false on failure
     */
    protected function getUIDFromDN($dn) {
        $components = ldap_explode_dn($dn,0);
        foreach ($components as $key=>$component) {
            if ($key === 'count') {
                continue;
            }
            if (substr($component,0,4) != 'uid=') {
                continue;
            }
            /* the preg_replace is here b/c of http://us2.php.net/manual/en/function.ldap-explode-dn.php#34724 */
            return preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''", substr($component,4));
        }
        return false;
    }


    /**
     * Gets the indicated user details as well as the  role. worker function
     * @param string $username the user name
     * @oaram boolean $getRole Defaults to false
     * @param array $details of string.  The details we wish on the user.  Defaults to empty array
     * @returns array with indexed by the values of $details and values the corresponding detail
     */
    protected function _getUserInfo($username, $getRole = false, $details=array()) {
        if (!$username || ! ($ldap = $this->getConnection())) {
            return false;
        }
        $return = array();
        $p_attrs = array();
        $p_attr_keys = array(); //using array of keys b/c keys for attributes need to be numerically indeixed
        foreach ($details as $detail) {
            if (!array_key_exists($detail, $this->options['p_details'])) {                
                continue;
            }
            $p_attrs[] = $this->options['p_details'][$detail];
            $p_attr_keys[] = $detail;
            $return[$detail] = null;
        }
        if (count($p_attrs) > 0   
            && ( $r = @ldap_read( $ldap,  $this->getPeopleQry($username),'cn=*',$p_attrs))
            && (ldap_count_entries($ldap,$r) == 1 )
            && ($entry = @ldap_first_entry( $ldap, $r))
            ) {
            foreach ($p_attrs as $key=>$attr) {
                $values = ldap_get_values($ldap,$entry, $attr);
                if (!is_array($values) || $values['count'] != 1 ) {
                    $return[$p_attr_keys[$key]] = null;
                    continue;
                }
                $return[$p_attr_keys[$key]] = $values[0];
            }
        }
        if ($getRole) {
            $return['role'] = null;
            if  (( $r = @ldap_list( $ldap,  $this->getRoleQry(),'ou=' . self::ldap_escape($username) ,array('cn')))
                 && ( @ldap_count_entries($ldap,$r) == 1)
                 && ($entry = @ldap_first_entry( $ldap, $r))
                ){
                $values = ldap_get_values($ldap,$entry,'cn');
                if ($values['count'] == 1) { //sanity check
                    $return['role'] = $values[0];
                }
            }
        }
        return $return;
    }

    
    /**
     * See if a user is in the system
     * @param string $username
     * @param boolean $has_role.  If true  we verify that they have a role set.
     * @returns boolean.
     */
    public function _userExists($username, $has_role) {
        if (!$username || ! ($ldap = $this->getConnection())) {
            return false;
        }
        if  ( !$r = @ldap_list( $ldap,   $this->getPeopleQry(),'uid=' . self::ldap_escape($username),array('dn'))) {
            return false;
        }
        if (@ldap_count_entries($ldap,$r) !== 1) {
            return false;
        }
        if (!$has_role) {
            return true;
        }
        //now we need to check the role
        if  ( !$r = @ldap_read( $ldap,  $this->getRoleQry(),'(&(ou=' . self::ldap_escape($username) . ')(cn=*)',array('cn'))) {
            return false;
        }
        if (@ldap_count_entries($ldap,$r) !== 1) {
            return false;
        }
        if ( !$entry = @ldap_first_entry( $ldap, $r)) {
            return false;
        }
        $values = @ldap_get_values($ldap,$entry,'cn');
        if (!is_array($values) || $values['count'] != 1) {
            return false;
        }
        return !empty($values[0]);
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
        } else if (array_key_exists('commonname',$user) && $user['commonname']) {
            return $user['commonname'];
        } else {
            return parent::displayName($username, $user);
        }
    }


    /**
     * Gets the userss by the indicated  details as well as the  role. worker method
     * @oaram boolean $role Defaults to false
     * @param array $details of string.  The details we wish on the user.  Defaults to empty array
     * @returns array of usernames which mathc the give input
     */
    public function _getUsersByInfo($role = false, $details=array()) {
        $usernames = array();
        if (!$ldap = $this->getConnection()) {
            return $usernames;
        }
        $usernames = array();
        $p_attrs = array();
        foreach ($details as $detail=>$value) {
            if (array_key_exists($detail, $this->options['p_details'])) {
                $p_attrs[] = '(' . $this->options['p_details'][$detail] .'=' .$value . ')';
            }
        }
        //first we get all the people by their people details. then we limit them based on app details if there are any
        $p_filter = '';
        $p_attrs[] = '(cn=*)';
        if (count($p_attrs) > 0) {
            $p_filter = '(&' . implode('',$p_attrs) .')';
        }
        if  ( !($r1 = @ldap_list(  $ldap, $this->getPeopleQry() , $p_filter, array('dn','uid')))) {
            return false;
        }      
        $entry = ldap_first_entry($ldap,$r1);
        $e = 0;
        while ($entry) {
            $dn = ldap_get_dn($ldap,$entry);
            $entry = ldap_next_entry($ldap,$entry);
            $username = $this->getUIDFromDN($dn);
            if (!$username) {
                I2CE::raiseError("No username from $dn");
                continue;
            }
            if ($role) {
                if  ( !($r2 = @ldap_read( $ldap,  $this->getRoleQry($username),'cn='. self::ldap_escape($role),array()))) {
                    continue;
                }
                if (@ldap_count_entries($ldap,$r2) !== 1) {
                    continue;
                }
            }
            $usernames[] = $username;
        }
        return $usernames;
    }










    /**
     * Ensures that an id is set for the given username
     * @param string $usernmae
     * @returns int the id, 0 on failure
     */    
    protected function ensureID($username) {
        if (!$username || ! ($ldap = $this->getConnection())) {
            return 0;
        }
        $dn = $this->getIdQry();
        if  ( !$r = @ldap_read( $ldap,   $dn,'ou=' . self::ldap_escape($username),array('cn'))) {
            return 0;
        }
        if (ldap_count_entries($ldap,$r) > 1) {
            return 0;  //sanity check 
        } 
        if (ldap_count_entries($ldap,$r) == 1) {
            $vals = ldap_get_values($ldap, ldap_first_entry($dn,$r),'cn');
            if ($vals['count'] > 1) { //sanity check
                return 0;
            }
            if ($vals['count'] == 1) { 
                return $vals[0];
            }
        }
        //if we got here, we did not have an id stored.
        //get the maxid
        $maxid = 0;
        $dn = $this->getIdQry();
        if  ( $r = @ldap_read( $ldap,   $dn,'',array('cn'))) {
            $entry = ldap_first_entry($ldap,$r);
            while ($entry) {
                $vals = ldap_get_values($ldap, $entry,'cn');
                $entry = ldap_next_entry($ldap,$entry);
                if ($vals['count'] != 1) { //sanity check
                    continue;
                }
                $maxid = max($maxid,  $vals[0]);
            }
        }
        $maxid++;
        //create the id.         
        $dn = $this->getIdQry($username);
        $id_details['ou'] = $username;
        $id_details['objectClass'] = 'applicationProcess';
        $id_details['cn'] = $maxid;
        if (!@ldap_add($ldap,$dn,$id_details)) {
            I2CE::raiseError("Could not set user id $username at:$dn\nwith:\n" . print_r($id_details,true));
            return false;
        }
        return $maxid;
    }


    /**
     *  Sets the role in the ldap hierarchy for the indicated user
     * @param string $username
     * @param string $role
     * @returns boolean. true on success
     */
    protected function setRole($username,$role) {
        if ( !$username || ! ($ldap = $this->getConnection())) {
            return false;
        }
        $dn = $this->getRoleQry();
        if  ( !$r = @ldap_list( $ldap,   $dn,'(&(ou=' . self::ldap_escape($username) . ')(cn=*))',array('cn'))) {
            return false;
        }
        if (ldap_count_entries($ldap,$r) > 1) {
            return false;  //sanity check 
        } 
        if  (ldap_count_entries($ldap,$r) == 1) { 
            //the entry for the role exists, we may just need to modify it
            if ( ($entry = ldap_first_entry($ldap,$r))) {
                $vals = ldap_get_values($ldap, $entry,'cn');
                if ($vals['count'] == 1 && $vals[0] == $role) {
                    //role is already set to be what we want
                    return $role;
                }
            }
            if (empty($role)) {
                //we  need to delete it
                if (!@ldap_delete($ldap,$this->getRoleQry($username))) {
                    I2CE::raiseError("Could not remove  user role for $username with at:$dn");
                    return false;
                }        
            } else {
                //we  need to modify it
                $r_details = array('cn'  => array($role));
                if (!@ldap_mod_replace($ldap,$this->getRoleQry($username),$r_details)) {
                    I2CE::raiseError("Could not set user role for $username with at:$dn");
                    return false;
                }        
            }
        } else {
            //the entry for the role does not exist
            if (empty($role)) {
                return; //we don't need to set it
            }       
            $r_details = array('cn'  => array($role));
            $r_details['ou'] = $username;
            $r_details['objectClass'] = 'applicationProcess';
            if (!@ldap_add($ldap,$this->getRoleQry($username),$r_details)) {
                I2CE::raiseError("Could not set user $username with details at:$dn");
                return false;
            }
        }
        return true;
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
        //first we must verify that the old passwrod is correct
        return $this->setPerson($username,$new_password,array(),false);  //only sets the userpassword
    }


    /**
     * Set the person details.
     * @param string $username
     * @param array $details
     * @param boolean $create
     */ 
    protected function setPerson($username,$password,$details, $create ) {
        if (!$username || ! ($ldap = $this->getConnection())) {        
            return false;
        }
        $p_details = array();
        foreach ($details as $detail=>$value) {
            if ($value === null) {
                continue;
            }
            if (!array_key_exists($detail,$this->options['p_details'])) {
                continue;
            }
            if ($create && (is_string($value) && strlen($value) == 0)) {
                $value = array();
                #want to know why am i doing this?
                #see: http://www.php.net/manual/en/function.ldap-modify.php#43216
                #and: http://www.php.net/manual/en/function.ldap-modify.php#38092                
            }
            $p_details[$this->options['p_details'][$detail]] = $value;
        }
        if ($password) {
            $p_details[$this->options['password_field']] = $this->encryptPassword($password);
        }
        $dn = $this->getPeopleQry($username);
        if ($create) {
           $p_details['objectClass'] = $this->options['person_objectClass'];
           $p_details['uid'] = $username;
           if (!@ldap_add($ldap,$dn,$p_details)) {
               I2CE::raiseError("Could not create user $username with details at: " .$dn);
               return false;
           }           
        } else {
           if (!@ldap_modify($ldap,$dn,$p_details)) {
               I2CE::raiseError("Could not modify user $username with details at: " .$dn);
               return false;
           }                       
        }
        return true;
    }


    /**
     * sets the indicated user details/role . worker function
     * @param string $username the user name
     * @param mixed $setRole.  Defaulst to false.  If a string, it is the role to assign to the user
     * @param array $details of string.  The keys are the detail, e.g. 'email' and the value at the key is the value of that detail
     * @returns boolean true on success
     */
    protected  function _setUserInfo($username, $setRole = false, $details= array()) {
        if ($setRole !== false && !$this->setRole($username,$setRole)) {
            return false;
        }
        return $this->setPerson($username,false,$details,false);
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
        if (!$password || !$username) {
            return false;
        }
        if (!$this->ensureID($username)) { 
            //first we need to make sure we can set an id for the user before we create it, otherwise we might have issues later
            I2CE::raiseError("Could not ensure id");
            return false;
        }
        if (!$this->setPerson($username,$password,$details,true)) {
            I2CE::raiseError("Could not create user $username");
            return false;
        }
        if ($role !== false && !$this->setRole($username,$role)) {
            I2CE::raiseError("Could not set role $role for $username");
            return false;            
        }
        return true;
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
