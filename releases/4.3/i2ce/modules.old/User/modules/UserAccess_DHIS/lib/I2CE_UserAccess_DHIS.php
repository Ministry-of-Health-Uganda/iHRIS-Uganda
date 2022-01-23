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
* @subpackage User
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.3
* @since v4.0.3
* @filesource 
*/ 
/** 
* Class I2CE_UserAccess_Internal
* A user access control mechansim where username and the user details are stored in one table
* and access is in another table and the tables are joined on a userid.
* @access public
*/


class I2CE_UserAccess_DHIS extends I2CE_UserAccess_Mechanism {
    

    /**
     * Encrypts the password
     * @param string $passwd
     * @returns string
     */
    protected function encryptPassword($passwd) {
        //For the Java String's hashcode() implementation s[0]*31^(n-1) + s[1]*31^(n-2) + ... + s[n-1]
        //from http://www.coderanch.com/t/329128/Java-General/java/Java-String-hashcode-base-computation
        $len = strlen($passwd);
        $passHash = 0;
        for ($i=0; $i  < $len; $i++) {
            $passHash = 31*$passHash + ( (int) $passwd[$len]);
        }        
        return md5($password . '{' . $passHash . '}');
    }
     
    /**
     * Whether or not this acccess mechansim can create ne users
     * @returns boolean
     */
    public function canCreateNewUser() {
        return true;
    }
     
    /**
     * Whether or not this acccess mechansim can create edit details of existing users
     * @returns boolean
     */
    public function canEditUserDetails() {
        return true;
    }



    /**
     * Gets an array of the allowed details
     * @returns array
     */
    public function getAllowedDetails() {
        return array_keys($this->getDetailColumns());
    }




    /**
     * An array of the details and their associated columns
     */
    protected function getDetailColumns() {
        return
            array(
            'firstname'=>'firstname',
            'lastname'=>'surname',
            'email'=>'email',
            'phone'=>'phonenumber',
            'email'=>'email'
                );
    }
    

    /**
     * ensrure default options are set
     * @param array $options
     * @returns array 
     */
    public  function ensureDefaultOptions($options) {
        return I2CE_Module_UserAccess_DHIS::ensureDefaultOptions($options);
    }
    
    
    /**
     * Create a new instance of a dhis user access mechanism
     */
    public function __construct( ) {
        parent::__construct();
        $this->db = I2CE::PDO();
        $this->passTable = $this->options['passTable'];
        $this->accessTable = $this->options['accessTable'];
        $this->logTable = $this->options['logTable'];
        $this->detailTable = $this->options['detailTable'];
    }
    /**
     *@var protected string $userTable the user detail table.
     *
     */
    protected $detailTable;
    /**
     *@var protected string $passTable the user password table.
     *
     */
    protected $passTable;
    /**
     *@var protected string $accessTable the user access table.
     *
     */
    protected $accessTable;


    /**
     *@var protected string $logTable the user acitivity log table.
     *
     */
    protected $logTable;





    
     /**
      * Gets the role assigned to a user
      * @param string $username
      * @param mixed $setRole.  Defaulst to false.  If a string, it is the role to assign to the user
      * @param string 
      * @returns boolean. true on success
      */
    public function _setUserInfo($username, $setRole = false, $details = array()) {
        if ($setRole) {
            $qry = "INSERT INTO " . $this->accessTable . " (role,user) SELECT ? as role, u.id as user  FROM " . $this->passTable . 
                " AS p LEFT JOIN " . $this->accessTable ." AS a ON p.userid = a .user  WHERE  u.username = ?  ON DUPLICATE KEY UPDATE role = ?";
            $params = array($setRole,$username, $setRole);
            try {
                I2CE_PDO::execParam( $qry, $params );
            } catch ( PDOException $e ) {
                I2CE::pdoError($e, "Cannot update user role");
                return false;
            }
        }         
        if (count($details) > 0) {
            $cols = $this->getDetailColumns();
            $params = array();
            $sets = array();
            foreach ($details as  $detail=>$value) {
                if ($detail == 'creator' ) {
                    $params[] = $this->getUserId($username);
                } else {
                    $params[] = $value;
                }
                $sets[] = 'u `' . $cols[$detail] . '` = ? ' ;
            }
            $params[] = 'text';
            $values[] = $username;
            $qry = "UPDATE  " . $this->detailTable . ' AS u,  ' .
                $this->passTable . ' AS p ' .
                "  SET " . implode(',',$sets) .
                ' WHERE p.username = ? AND a.user = p.userid ';
            try {
                I2CE_PDO::execParam( $qry, $params );
            } catch ( PDOException $e ) {
                I2CE::pdoError($e, "Cannot update user details");
                return false;
            }
        }
        return true;
    }



    /**
     * Gets the indicated user details as well the  role
     * 
     * @param string $username;
     * @oaram boolean $getRole Defaults to false
     * @returns array. associative array of user info (role, email and other details)
     */
    public function _getUserInfo($username,$getRole = false,$details = array()) {
        $details = array_intersect($details , $this->getAllowedDetails());
        $detail_columns = $this->getDetailColumns();
        $details_access = array();
        foreach ($details as $detail) {
            $details_access[] = 'u.`' . $detail_columns[$detail] . "` AS `$detail`";
        }
        if ($getRole) {
            $details_access[] = ' a.role AS role';
            $qry = 'SELECT ' .
                implode (',', $details_access) 
                . ' FROM ' . $this->detailTable . ' u'
                . ' JOIN ' . $this->passTable  .' p ON p.userid = u.userinfoid '
                . ' LEFT JOIN ' . $this->accessTable  .' a ON a.user = p.userid '
                . ' WHERE p.username = ?';
        } else {
            $qry = 'SELECT ' .
                implode (',', $details_access) 
                . ' FROM ' . $this->detailTable . ' u'
                . ' JOIN ' . $this->passTable  .' p ON p.userid = u.userinfoid '
                . ' WHERE p.username = ?';
        }
        $results = array();
        try {

            $row = I2CE_PDO::getRow( $qry, array( $username ) );

            if ($getRole) {
                $results['role'] = $row->role;
            }
            foreach ($details as $detail) {
                $results[$detail] = $row->$detail;
            }
            unset( $row );
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,  "Cannot get user info for $username"  );
            return array();
        }
        return  $results;
    }



    /**
     * Gets the userss by the indicated  details as well as the  role. worker method
     * @oaram boolean $role Defaults to false
     * @param array $details of string.  The details we wish on the user.  Defaults to empty array
     * @returns array of usernames which mathc the give input
     */
    public function _getUsersByInfo($role = false, $details=array()) {
        $qry = "SELECT p.username AS username "
            . " FROM " . $this->passTable . " AS p ";
        $cols = $this->getDetailColumns();
        $params = array();
        $limits = array();
        if (count($details) > 0) {
            $qry .= ' JOIN ' . $this->detailTable . ' AS u';
        }
        foreach ($details as  $detail=>$value) {
            $params[] = $value;
            $limits[] = ' u.`' . $cols[$detail] . '` = ? ' ;
        }            
        if ($role) {
            ' LEFT JOIN ' . $this->accessTable  .' a ON a.user = p.userid ';
            $params[] = $role;
            $limits[] = ' a.role  ' ;
        }
        if (count($limits) > 0) {
            $qry .= ' WHERE (' . implode(" AND " , $limits) . ')';
        }
        try {
            $stmt = $this->db->prepare($qry);
            $stmt->execute( $params);
            $usernames = array();
            while( $row = $stmt->fetch() ) {
                $usernames[] = $row->username;
            }
            $stmt->closeCursor();
            unset( $stmt );
            return $usernames;
        } catch( PDOException $e ) {
            I2CE::pdoError($e, "Cannot query users");
            return array();
        }
    }




    /**
     * Whether or not this acccess mechansim can change a user's password
     * @returns boolean
     */
    public function canChangePassword() {
        return true;
    }



    /**
     * See if a user is in the system
     * @param string $username
     * @param boolean $has_role.  If true  we also verify that they have a role set.
     * @returns boolean.
     */
    public function _userExists($username, $has_role ) {
        if ($has_role) {
            $qry = "SELECT count(*) AS num "
                . ' FROM ' . $this->passTable . ' p'
                . ' LEFT JOIN ' . $this->accessTable  .' a ON a.user = p.userid '
                . ' WHERE p.username  = ? AND a.role IS NOT NULL AND LENGTH(a.role) > 0 ';
            try {
                $row = I2CE_PDO::getRow( $qry, array( $username ) );
                if (!$row || !$row->num) {
                    return false;
                }
                return ($row->num > 0);
            } catch ( PDOException $e ) {
                I2CE::pdoError($e,  "Cannot get user for $username"  );
                return false;
            }            
        } else {
            return $this->getUserId($username) !== false;
        }
    }

    /**
     * Gets the user id from the user name
     * @param string $username
     * @returns int or false on failure
     */
    public function _getUserId($username) {
        $qry = "SELECT userid AS id"
            . ' FROM ' . $this->passTable . ' p'
            . ' WHERE p.username  = ? ';
        try {
            $row = I2CE_PDO::getRow( $qry, array( $username ) );
            if (!$row || !$row->id) {
                return false;
            }
            return $row->id;
        } catch ( PDOException $e ) {
            I2CE::pdoError($row,  "Cannot get user id for $username"  );
            return false;
        }
    }

    /**
     * Gets the user ids
     * @returns array of ids
     */
    public function _getUserIds() {
        $qry = "SELECT userid AS id  FROM " . $this->passTable ;        
        try {
            $sth = $this->db->prepare($qry);
            $sth->execute();
            $userIds= array();
            while ( $data = $sth->fetch() ) {
                $userIds[] = $data->id;
            }
            $sth->closeCursor();
            unset( $sth );
            return $userIds;
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error getting userids: " );
            return array();
        }
    }






    /**
     * Gets the user id from the username
     * @param int $userid
     * @returns string or false on failure
     */
    public function _getUserNameFromId($userid) {
        $qry = "SELECT username "
            . ' FROM ' . $this->passTable . ' p'
            . ' WHERE p.userid  = ? ';
        try {
            $row = I2CE_PDO::getRow( $qry, array( $userid ) );
            if (!$row || !$row->username || $row->username=='0') {
                return false;
            }
            return $row->username;
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Cannot get username for id for $userid" );
            return false;
        }
        
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
     * @returns boolean True on success
     */
    public function _changePassword( $username, $old_password,$new_password ) {
        if ($old_password === false) {
            $qry = "UPDATE " . $this->passTable . ' SET password = ? WHERE username = ?';
            $params = array(
                $this->encryptPassword($new_password),
                $username
                );
            try {
                $result = I2CE_PDO::execParam( $qry, $params );
                return $result  == 1;
            } catch ( PDOException $e ) {
                I2CE::pdoError($e, "Cannot update password");
                return false;
            }
        } else {
            $qry = "UPDATE " . $this->passTable . ' SET password = ? WHERE ( ' . 
                'username = ? AND ' .
                'password = ? )' ;
            $params = array(
                $this->encryptPassword($new_password),
                $username,
                $this->encryptPassword($old_password)
                );
            try {
                $result = I2CE_PDO::execParam( $qry, $params );
                return $result == 1;
            } catch ( PDOException $e ) {
                I2CE::pdoError($e, "Cannot update password");
                return false;
            }
        }
    }


     /**
      * verifies that the specified user has the specified password
      * @param string $username
      * @param string $password
      * @returns boolean
      */
    public function _userHasPassword($username,$password) {
        $qry = "SELECT COUNT(*) as num FROM  " . $this->passTable . ' WHERE ( ' . 
            'username = ? AND ' .
            'password = ? )' ;
        try {
            $row = I2CE_PDO::getRow( $qry, array( $username,$this->encryptPassword($password) ) );                
            if (!$row ) {
                return false;
            }
            return ($row->num == 1);        
        } catch ( PDOException $e ) {
            I2CE::pdoError($row,  "Cannot check password  for $username"  );
            return false;
        }
    }


     /**
      * Logs a user activiity
      * @param string $username
      * @param string $actitivty, such as login, logout, access.  Default is access
      * @param int $timesamp. Defaults to null, in which case it should be now
      */
    public function logActivity($username, $activity='access', $timestamp= null) {
        if ($timestamp === null) {
            $timestamp = time();
        }
        switch ($activity) {
        case 'login':
            break;
        case 'logout':
            break;
        case 'access':
        default:
            $this->logTable;
        }

    }




     /**
      *  Create user worker method
      *  @param string $username
      * @param string $password
      * @param string $role
      * @param array $details.  Defatuls to empty array,
      * @returns boolean. true on success
      */
    public function _createUser($username, $password, $role = false, $details= array()) {
        $qry = "INSERT INTO " . $this->passTable . " ( username, password) VALUES (?,?)";
        $params = array($username,$this->encryptPassword($password));
        try {
            I2CE_PDO::execParam($qry, $params );
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Cannot create user $username" );
            return false;
        }
        return $this->setUserInfo($username,$role,$details);
    }


    /**
     * Gets the display name for the user     
     * @param string $username 
     * @param array $user user details
     * @returns string
     */
    public function displayName($username, $user) {
        return substr( $user['firstname'], 0, 1 ) . ' '  . $user['lastname'];
    }


  }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
