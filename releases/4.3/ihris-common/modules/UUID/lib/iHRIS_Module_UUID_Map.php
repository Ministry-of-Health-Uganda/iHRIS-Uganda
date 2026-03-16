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
* @package common
* @subpackage uuid_map
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.0
* @since v4.0.0
* @filesource 
*/ 
/** 
* Class iHRIS_Module_UUID_Map
* 
* @access public
*/


class iHRIS_Module_UUID_Map extends I2CE_Module{
    
    /**
     * Method called when the module is enabled for the first time.
     * @param boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        I2CE::raiseError("Initializing UUID Map Tables");
        if (!I2CE_Util::runSQLScript('init_uuid_table.sql')) {
            I2CE::raiseError("Could not initialize uuid table");
            return false;
        }
        return true;
    }


    /**
     * Upgrade this module if necessary
     * @param string $old_vers
     * @param string $new_vers
     * @return boolean
     */
    public function upgrade( $old_vers, $new_vers ) {
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '4.0.6.1' ) ) {
            if (!$this->addLastModifiedColumn()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Adds the last_modifed column to the uuid_map table if it is not there
     * @returns true on success
     */
    protected function addLastModifiedColumn() {
        $db = I2CE::PDO();
        try {
            $result = $db->query("SHOW FULL COLUMNS FROM `uuid_map` WHERE Field='last_modifed'");
            $rows = $result->fetchAll();
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to get uuid_map column for last modified: ");
            return false;
        }

        if(count($rows)> 0) {
            I2CE::raiseError("uuid_map table already has last_modifed");
        } else {
            $qry_alter = "ALTER TABLE `uuid_map` ADD COLUMN   `last_modified` timestamp  NULL DEFAULT CURRENT_TIMESTAMP;";
            try {
                $db->exec($qry_alter);
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error adding parent_id, parent_form column to $table table:");
                return false;
            }
        }
        return true;
    }



    /**
     * Checkst to see if the UUID Pecl module is installed
     * @returns boolean
     */
    public static function hasUUID() {
        return extension_loaded('uuid');
    }


    //below is adapted from https://gist.github.com/dahnielson/508447
    /**
     * Generate v3 UUID
     *
     * Version 3 UUIDs are named based. They require a namespace (another 
     * valid UUID) and a value (the name). Given the same namespace and 
     * name, the output is always the same.
     * 
     * @paramuuid$namespace
     * @paramstring$name
     */
    public static function v3($namespace, $name)
    {
        if(!self::is_valid($namespace)) return false;

        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-','{','}'), '', $namespace);

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for($i = 0; $i < strlen($nhex); $i+=2) 
        {
            $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
        }

        // Calculate hash value
        $hash = md5($nstr . $name);

        return sprintf('%08s-%04s-%04x-%04x-%12s',

                       // 32 bits for "time_low"
                       substr($hash, 0, 8),

                       // 16 bits for "time_mid"
                       substr($hash, 8, 4),

                       // 16 bits for "time_hi_and_version",
                       // four most significant bits holds version number 3
                       (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,

                       // 16 bits, 8 bits for "clk_seq_hi_res",
                       // 8 bits for "clk_seq_low",
                       // two most significant bits holds zero and one for variant DCE1.1
                       (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

                       // 48 bits for "node"
                       substr($hash, 20, 12)
            );
    }


    /**
     * 
     * Generate v4 UUID
     * 
     * Version 4 UUIDs are pseudo-random.
     */
    public static function v4() 
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

                       // 32 bits for "time_low"
                       mt_rand(0, 0xffff), mt_rand(0, 0xffff),

                       // 16 bits for "time_mid"
                       mt_rand(0, 0xffff),

                       // 16 bits for "time_hi_and_version",
                       // four most significant bits holds version number 4
                       mt_rand(0, 0x0fff) | 0x4000,

                       // 16 bits, 8 bits for "clk_seq_hi_res",
                       // 8 bits for "clk_seq_low",
                       // two most significant bits holds zero and one for variant DCE1.1
                       mt_rand(0, 0x3fff) | 0x8000,

                       // 48 bits for "node"
                       mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
    }

    /**
     * Generate v5 UUID
     * 
     * Version 5 UUIDs are named based. They require a namespace (another 
     * valid UUID) and a value (the name). Given the same namespace and 
     * name, the output is always the same.
     * 
     * @paramuuid$namespace
     * @paramstring$name
     */
    public static function v5($namespace, $name) 
    {
        if(!self::is_valid($namespace)) return false;

        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-','{','}'), '', $namespace);

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for($i = 0; $i < strlen($nhex); $i+=2) 
        {
            $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
        }

        // Calculate hash value
        $hash = sha1($nstr . $name);

        return sprintf('%08s-%04s-%04x-%04x-%12s',

                       // 32 bits for "time_low"
                       substr($hash, 0, 8),

                       // 16 bits for "time_mid"
                       substr($hash, 8, 4),

                       // 16 bits for "time_hi_and_version",
                       // four most significant bits holds version number 5
                       (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,

                       // 16 bits, 8 bits for "clk_seq_hi_res",
                       // 8 bits for "clk_seq_low",
                       // two most significant bits holds zero and one for variant DCE1.1
                       (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

                       // 48 bits for "node"
                       substr($hash, 20, 12)
            );
    }

    public static function is_valid($uuid) {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.
                          '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
