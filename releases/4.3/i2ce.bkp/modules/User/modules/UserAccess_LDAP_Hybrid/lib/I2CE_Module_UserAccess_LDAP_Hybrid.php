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
* Class I2CE_Module_UserAccess
* 
* @access public
*/


class I2CE_Module_UserAccess_LDAP_Hybrid extends I2CE_Module {


    /**
     * ensrure default options are set
     * @param array $options
     * @returns array 
     */
    public static function ensureDefaultOptions($options) {
        if (!array_key_exists('dn',$options)) {
            $options['dn'] = 'dc=localhost';
        }
        if (!array_key_exists('ldap_user_dn',$options)) {
            $options['ldap_user_dn'] = $options['dn'];
        }
        if (!array_key_exists('people',$options)) {
            $options['people'] = 'ou=People';
        }
        if (!array_key_exists('person_comp',$options)) {
            $options['person_comp']  = 'cn';
        }
        if (!array_key_exists('apps',$options)) {
            $options['apps'] = 'Application';
        }
        if (!array_key_exists('app',$options)) {
            I2CE::getConfig()->setIfIsSet($options['app'],'/config/site/module');
        }
        if (!array_key_exists('roles',$options)) {
            $options['roles'] = 'Roles';
        }
        if (!array_key_exists('ids',$options)) {
            $options['ids'] = 'Ids';
        }
        if (!array_key_exists('host',$options)) {
            $options['host'] = 'localhost';
        }
        if (!array_key_exists('port',$options)) {
            $options['port'] = 389;
        }
        if (!array_key_exists('encrypt',$options)) {
            $options['encrypt'] = 'bind';
        }
        if (!array_key_exists('p_details',$options)) {
            $options['p_details'] = 
                array(
                    'commonname'=>'cn',
                    'email'=>'mail',
                    'locale'=>'preferredLanguage'
                );            
        }
        if (!array_key_exists('p_detail_names',$options)) {
            $options['p_detail_names'] = 
                array(
                    'commonname'=>'Common Name',
                    'email'=>'E-mail',
                    'locale'=>'Preferred Locale'
                );            
        }
        if (!array_key_exists('admin_details',$options) || !is_array($options['admin_details'])) {
            $options['admin_details']  = array();
        }
        $admin_dets =array(
            'commonname'=>'admin',
            'email'=>'root@localhost',
            'locale'=>'en_US'
            );
        foreach ($admin_dets as $key=>$val) {
            if (!array_key_exists($key, $options['admin_details'])) {
                $options['admin_details'][$key] = $val;
            }
        }
        if (!array_key_exists('ldap_user',$options)) {
            $options['ldap_user'] = null;
        }
        if (!array_key_exists('ldap_pass',$options)) {
            $options['ldap_pass'] =  null;
        }
        if (!array_key_exists('password_field',$options)) {
            $options['password_field'] = 'userPasssord';
        }
        if (!array_key_exists('can_change_pass',$options)) {
            $options['can_change_pass'] = false;
        }
        if (!array_key_exists('can_edit_user_details',$options)) {
            $options['can_edit_user_details'] = false;
        }
        if (!array_key_exists('can_edit_user_role',$options)) {
            $options['can_edit_user_details'] = true;
        }
        if (!array_key_exists('can_create_user',$options)) {
            $options['can_create_user'] = false;
        }
        if (!array_key_exists('person_objectClass',$options)) {
            $options['person_objectClass'] = 'inetOrgPerson';
        }
        $options['userDB'] = trim($options['userDB']);
        if (!$options['userDB']) {
            $options['userDB'] =  '`' . I2CE_PDO::details('dbname') . '`';
        }
        if (!array_key_exists('user_table',$options)) {
            $options['user_table']=  $options['userDB'] . '.user_ldap';
        }

        return $options;
    }


    /**
     * Method called when the module is enabled for the first time.
     * @returns boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        //initialize the user tables
        if (!$this->ensureUserTable()) {
            I2CE::raiseError("Could not ensure user table");
            return false;
        } else {
            I2CE::raiseError("Ensured organizational entries");
        }
        return true;
    }


    /**
     * Ensure that the user table is presnet, if not attempt to create it
     * @returns boolean
     */
    protected function ensureUserTable() {
                //initialize the user tables
        $init = I2CE::getUserAccessInit('DEFAULT');
        if (empty($init)) {
            $options = array();
        } else {
            $options = json_decode($init,true);        
            if( !is_array($options)) {
                I2CE::raiseError("Invalid user access initilization string for Default");
                $options = array();
            }
        }        
        $options =  self::ensureDefaultOptions($options);
        $db = I2CE::PDO();
        $qry = 'CREATE TABLE IF NOT EXISTS ' . $options['user_table']. ' '
            .'(`id` int(11) NOT NULL auto_increment,'
            .' `username` varchar(20) NOT NULL,'
            .' `role` varchar(255) collate utf8_bin NOT NULL,'
            .' PRIMARY KEY  (`id`),'
            .' UNIQUE KEY `username` (`username`)'
            .') ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin';
        I2CE::raiseError("Initializing LDA-DB User Table. Users' details table se stored in database {$options['userDB']}");
        try {
            $db->exec($qry);
            return true;
        } catch( PDOException $e ) {
            I2CE::pdoError($e,"Cannot create user access table: $qry");
            I2CE::raiseError("Could not initialize LDAP-DB user table") ;
            return false;
        }
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
