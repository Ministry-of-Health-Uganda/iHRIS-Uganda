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


class I2CE_Module_UserAccess_LDAP extends I2CE_Module {


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
            $options['person_comp']  = 'uid';
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
            $options['encrypt'] = 'SHA';
        }
        if (!array_key_exists('password_field',$options)) {
            $options['password_field'] = 'userPasssord';
        }
        if (!array_key_exists('p_details',$options)) {
            $options['p_details'] = 
                array(
                    'firstname'=>'givenName',
                    'lastname'=>'sn',
                    'commonname'=>'cn',
                    'email'=>'mail',
                    'locale'=>'preferredLanguage'
                );            
        }
        if (!array_key_exists('p_detail_names',$options)) {
            $options['p_detail_names'] = 
                array(
                    'firstname'=>'Firstname',
                    'lastname'=>'Surname',
                    'commonname'=>'Common Name',
                    'email'=>'E-mail',
                    'locale'=>'Preferred Locale'
                );            
        }
        if (!array_key_exists('admin_details',$options) || !is_array($options['admin_details'])) {
            $options['admin_details']  = array();
        }
        $admin_dets =array(
            'firstname'=>'System',
            'lastname'=>'Administrator',
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
            $options['ldap_user'] = 'admin';
        }
        if (!array_key_exists('ldap_pass',$options)) {
            $options['ldap_pass'] =  I2CE_PDO::details('pass');
        }
        if (!array_key_exists('can_change_pass',$options)) {
            $options['can_change_pass'] = true;
        }
        if (!array_key_exists('can_edit_user',$options)) {
            $options['can_edit_user'] = true;
        }
        if (!array_key_exists('can_create_user',$options)) {
            $options['can_create_user'] = true;
        }
        if (!array_key_exists('person_objectClass',$options)) {
            $options['person_objectClass'] = 'inetOrgPerson';
        }
        return $options;
    }


    /**
     * Method called when the module is enabled for the first time.
     * @returns boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        //initialize the user tables
        if (!$this->ensureOrganizationalEntries()) {
            I2CE::raiseError("Could not ensure organizational entries");
            return false;
        } else {
            I2CE::raiseError("Ensured organizational entries");
        }
        return true;
    }

    
    /**
     * Ensure the organziational entries in the LDAP server for People and Application
     * @returns boolean. True on success
     */
    protected function ensureOrganizationalEntries() {
        $init = I2CE::getUserAccessInit('LDAP');
        if (empty($init)) {
            $options = array();
        } else {
            $options = json_decode($init,true);        
            if( !is_array($options)) {
                I2CE::raiseError("Invalid user access initilization string for LDAP");
                $options = array();
            }
        }        
        $options =  self::ensureDefaultOptions($options);
        $ldap = @ldap_connect($options['host'], $options['port']);
        if (!is_resource($ldap)) {
            I2CE::raiseError("Could not connect to ldap server on {$options['host']}:{$options['port']}");
            return false;
        }
        @ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        @ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        if ($options['ldap_user'] && $options['ldap_pass'] 
            && !@ldap_bind($ldap, 'cn=' . $options['ldap_user'] . ',' . $options['dn'] ,$options['ldap_pass'])) {
            I2CE::raiseError("Could not bind to ldap server as user {$options['ldap_user']},{$options['dn']} ");
            @ldap_close($ldap);
            return false;
        }

        $dns = array(            
            $options['dn'] => array($options['people'], $options['apps']),
            'ou=' .$options['apps'] . ', ' . $options['dn'] => array( $options['app']),
            'ou='. $options['app'] . ',ou=' .$options['apps'] . ', ' . $options['dn'] => array( $options['roles'] , $options['ids'])
            );
        foreach ($dns as $base=>$ous) {
            foreach ($ous as $ou) {
                if (!$r = @ldap_search($ldap,$base,'ou=' . $ou,array())) {
                    I2CE::raiseError("Could not access at $base");
                    return false;
                }
                $dn = "ou=$ou,$base";
                if (ldap_count_entries($ldap,$r) > 0) {
                    I2CE::raiseError("$dn exists. not creating");
                    continue;
                }
                $entry =  array(
                    'ou'=>$ou,
                    'objectClass'=>'organizationalUnit'
                    );
                if (!@ldap_add($ldap,$dn,$entry)) {
                    I2CE::raiseError("Could not add at $dn:\n" . print_r($entry,true));
                    @ldap_close($ldap);
                    return false;
                }
                I2CE::raiseError("$dn created");
            }
        }
        @ldap_close($ldap);
        return true;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
