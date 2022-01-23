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


class I2CE_Module_UserAccess extends I2CE_Module {

    /**
     * ensrure default options are set
     * @param array $options
     * @returns array 
     */
    public static function ensureDefaultOptions($options) {
        if (!array_key_exists('userDB',$options)) {
            $options['userDB'] = '';
        }
        $options['userDB'] = trim($options['userDB']);
        if (!$options['userDB']) {
            $options['userDB'] =  '`' . I2CE_PDO::details('dbname') . '`';
        }
        if (!array_key_exists('detailTable',$options)) {
            $options['detailTable']=  $options['userDB'] . '.user';
        }
        if (!array_key_exists('accessTable',$options)) {
            $options['accessTable']= 'access';
        }
        if (!array_key_exists('logTable',$options)) {
            $options['logTable']= 'user_log';
        }
        if (!array_key_exists('admin_details',$options) || !is_array($options['admin_details'])) {
            $options['admin_details']  = array();
        }
        $admin_dets =array(
            'firstname'=>'System',
            'lastname'=>'Administrator',
            'email'=>'root@localhost',
            'locale'=>'en_US'
            );
        foreach ($admin_dets as $key=>$val) {
            if (!array_key_exists($key, $options['admin_details'])) {
                $options['admin_details'][$key] = $val;
            }
        }
        return $options;    
    }

    /**
     * Method called when the module is enabled for the first time.
     * @returns boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
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
        $qrs = array();
        $qrs[] = 'CREATE TABLE IF NOT EXISTS ' . $options['detailTable']. ' '
            .'(`id` int(11) NOT NULL auto_increment,'
            .' `username` varchar(20) NOT NULL,'
            .' `password` varchar(50) NOT NULL,'
            .' `firstname` varchar(50) NOT NULL,'
            .' `lastname` varchar(100) NOT NULL,'
            .' `email` varchar(100) default NULL,'
            .' `creator` int(11) NOT NULL,'
            .' `default_password` int(11) default 1,'
            .' PRIMARY KEY  (`id`),'
            .' UNIQUE KEY `username` (`username`)'
            .') ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=utf8';
        $qrs[] = 'CREATE TABLE IF NOT EXISTS ' . $options['accessTable']   .' ' 
            .'( `user` int(11) NOT NULL,'
            .' `role` varchar(255) collate utf8_bin NOT NULL,'
            .' PRIMARY KEY  (`user`)'
            .') ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin';
        $qrs[] = 'CREATE TABLE IF NOT EXISTS ' . $options['logTable'] . ' '
            .'(`user` int(11) NOT NULL,'
            .' `login` datetime NOT NULL,'
            .' `logout` datetime default NULL,'
            .' `session_id` varchar(50) NOT NULL,'
            .' `activity` datetime NOT NULL,'
            .' KEY `user` (`user`),'
            .' KEY `login` (`login`)'
            .') ENGINE=MyISAM DEFAULT CHARSET=utf8';
        
        I2CE::raiseError("Initializing User Table. Users' details table se stored in database {$options['userDB']}");
        try {
            foreach ($qrs as $qry) {
                $db->exec($qry);
            }
            return true;
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Cannot create user access tables." );
            return false;
        }
    }

    /**    
     * Post module update.
     */
    public function post_update($old_version,$new_version) {
        if (!$this->createAdminUser()) {
            I2CE::raiseError("Could not create admin user") ;
            return false;
        }
        return true;
    }

    protected function createAdminUser() {
        if (I2CE::getUserAccessProtocol() !== 'DEFAULT') {
            return true;
        }
        $admins = I2CE_User::findUsersByInfo('admin');
        if (is_array($admins) && count($admins) > 0) {
            I2CE::raiseError("Admin users already found");
            return true; // an admin  already exists.
        }        
        if (I2CE_User::userExists('administrator',false)) {
            //do nothing. somebody has been messing around here.
            return true;  //hopefully whoever allowed this to happen
        }
        $admin = new I2CE_User('administrator',false,false,false);
        $admin->email = 'administrator@example.com';
        $admin->creator = 'administrator';
        $admin->firstname= 'Site';
        $admin->lastname = 'Administrator';
        $admin->username = 'administrator';
        $admin->role = 'admin';
        I2CE::raiseError("Creating user 'administrator' with administrative privlages");
        return $admin->save('administrator');
    }


    public function upgrade($old_vers,$new_vers) {
        if (I2CE_Validate::checkVersion($old_vers,'<','3.1')) {
            if (! $this->updateAccessTable()) {
                return false;
            }
        }
        
        if (I2CE_Validate::checkVersion($old_vers,'<','4.2.0.1')) {
            if (! $this->updateUserDetailTable()) {
                return false;
            }
        }
        return true;
    }

    protected function updateAccessTable() {
        $db = I2CE::PDO();
        $options = self::getInitOptions(I2CE::getUserAccessInit('DEFAULT'));
        try {
            $result = $db->query("SHOW FULL COLUMNS FROM " . $options['accessTable'] . " WHERE Field='role'");
            $rows = $result->fetchAll();

            if(substr($rows[0]->type, 0, 3) != 'int') {
                I2CE::raiseError("NOT doing access table");
                return true;
            }

            I2CE::raiseError("Altering access table to 3.x format");
            return I2CE::runSQLScript('update-access-table.sql');
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to get format details for updating access table." );
            return false;
        }
    }


    protected function updateUserDetailTable(){
        $db = I2CE::PDO();
        $qry_show = "SHOW COLUMNS FROM user LIKE 'default_password'"; 
        $qry_alter = "ALTER TABLE user ADD COLUMN `default_password` int(11) default 1"; 
        try {
            $results = $db->query( $qry_show );
            if( $results->rowCount() != 1) { //the logic is vague: I could not find another logic to confirm the field is found
                $qry_alter_results = $db->exec($qry_alter);
            }
            return true;
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error getting columns from user table." );
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
