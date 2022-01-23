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
 ** The module that adds in an image data type
 * @package I2CE
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v3.1.0
 * @version v3.1.0
 */

/*
 *The module meta data
Module Name: Float
Module Version: 1.0
Module URI: http://www.capacityproject.org
Description:  Module to handle float form fields
Author: Luke Duncan
Author Email:lduncan@intrahealth.org
*/


class I2CE_Module_Float extends I2CE_Module {
    /**
     * @var PDO The instance of the database to perform queries on.
     */
    private $db;
        

    public function __construct() {
        parent::__construct();
        $this->db = I2CE::PDO();
    }



        
        
    /**
     * Make sure the float column is in the database in the entry/last entry tables.
     */
    public function action_initialize() { 
        //check to see that the large blobs are there.
        foreach( array('entry','last_entry') as $table ) {
            $qry_show = "SHOW COLUMNS FROM $table LIKE '%_value'";
            $qry_alter = "ALTER TABLE $table ADD COLUMN `float_value` float";
            try {
                $results = $this->db->query( $qry_show );
                $found = false;
                while( $row = $results->fetch() ) {
                    if ($row->field == 'float_value') {
                        $found = true;
                    }
                }
                if (!$found) {
                    //add the blob column to last_entry
                    try { 
                        $this->db->exec($qry_alter);
                    } catch ( PDOException $e ) {
                        I2CE::pdoError( $e, "Error adding float column to $table:");
                        return false;
                    }
                }
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error getting columns from $table table: on {$qry_show}" );
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
