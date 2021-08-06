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


class I2CE_Module_UserAccess_DHIS extends I2CE_Module {


    /**
     * ensure default options are set
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
            $options['detailTable']=  $options['userDB'] . '.userinfo';
        }
        if (!array_key_exists('passTable',$options)) {
            $options['passTable'] =$options['userDB'] . '.users' ;
        }
        if (!array_key_exists('accessTable',$options)) {
            $options['accessTable']= 'access_dhis';
        }
        if (!array_key_exists('logTable',$options)) {
            $options['logTable']= 'user_log_dhis';
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
     * @param boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        //initialize the user tables
        //initialize the user tables
        $init = I2CE::getUserAccessInit('DHIS');
        if (empty($init)) {
            $options = array();
        } else {
            $options = json_decode($init,true);        
            if( !is_array($options)) {
                I2CE::raiseError("Invalid user access initilization string for DHIS");
                $options = array();
            }
        }        
        $options =  self::ensureDefaultOptions($options);
        $db = I2CE::PDO();
        $qrs = array();
        $qrs[] = 'CREATE TABLE IF NOT EXISTS ' . $options['detailTable']. ' '
            .'(userinfoid integer NOT NULL,'
            .' surname  varchar(160) NOT NULL,'
            .' firstname  varchar(160) NOT NULL,'
            .' email  varchar(160),'
            .' phonenumber  varchar(80),'
            .' CONSTRAINT userinfo_pkey PRIMARY KEY (userinfoid)'
            .')';
        $qrs[] = 'CREATE TABLE IF NOT EXISTS ' . $options['passTable']. ' '
            .'(userid integer NOT NULL,'
            .' username  varchar(255) NOT NULL,'
            .' password  varchar(255) NOT NULL,'
            .' CONSTRAINT users_pkey PRIMARY KEY (userid),'
            .' CONSTRAINT fk6a68e08f19893da FOREIGN KEY (userid)'
            .' REFERENCES ' . $options['detailTable'] . ' (userinfoid) MATCH SIMPLE'
            .' ON UPDATE NO ACTION ON DELETE NO ACTION,'
            .' CONSTRAINT users_username_key UNIQUE (username)'
            .')';
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
        foreach ($qrs as $qry) {
            try {
                $db->exec($qry);
            } catch( PDOException $e ) {
                I2CE::pdoError($e,"Cannot create user table");
                I2CE::raiseError("Could not initialize I2CE user tables") ;
                return false;
            }
        }
        return true;
    }





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
