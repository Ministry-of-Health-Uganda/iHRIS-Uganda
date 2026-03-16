<?php
/**
* © Copyright 2015 IntraHealth International, Inc.
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
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Module_FormRelationship
* 
* @access public
*/


class I2CE_Module_FormRelationship extends I2CE_Module {

    /**
     * Upgrade module method
     */
    public function upgrade($old_vers,$new_vers) {
        if (I2CE_Validate::checkVersion($old_vers,'<','4.2.0.6')) {
            if (! $this->createTable()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Method called when the module is enabled for the first time.
     * @param boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        return $this->createTable();
    }


    public function createTable() {
        $qry = 'CREATE TABLE IF NOT EXISTS form_relationship_importer  (
   hash char(32) NOT NULL,
   relationship text  NOT NULL,
   id text  NOT NULL,
   KEY hash_rey ( hash, relationship (130) )
) ENGINE=InnoDB DEFAULT CHARSET=utf8
';
        $db = I2CE::PDO();
        try {
            $result = $db->query($qry);
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Cannot execute  query:\n$qry");
            I2CE::raiseError("Could not create form_relationship_importer table");
            return false;
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
