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
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v3.0.0
 * @version v3.0.0
 */

/**
 * Deps.
 */
require_once "I2CE_MagicDataStorage.php";

if (!class_exists('I2CE_MagicDataStorageDB',false)) {

/**
 * Configuration class to lookup and save configuration options.
 * @package I2CE 
 * @access public
 */
    class I2CE_MagicDataStorageDB extends I2CE_MagicDataStorage {

        /**
         * @var array An array of prepared statements for looking up stored magic data.
         */
        private $db_statements;


        /**
         *Renames a child node
         * @param I2CE_MagicDataNode $node
         *@param string $old
         *@param string $new
         *@returns boolean.  True on success, false on failure
         */        
        public function renameChild($node,$old,$new) {
            I2CE::raiseError("Not implemented");
            return false;
        }

        public function __construct($name) {
            parent::__construct($name);
            $this->setUpStatements();
        }

        /**
         * Set up a cache of prepared statements.
         * @return PDOStatement
         */
        protected function setUpStatements() {
            $this->db_statements  = array();
            $db = I2CE::PDO();
            try {
                $this->db_statements['retrieve']
                    = $db->prepare( "SELECT type,value,children FROM " . $this->name . " WHERE hash = ? LIMIT 1" );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to prepare retrieve statement" );
                unset( $this->db_statements['retrieve'] );
            }
            try {
                $this->db_statements['destroy']
                    = $db->prepare( "DELETE FROM " . $this->name . " WHERE hash = ? LIMIT 1", array('text') );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to prepare destroy statement" );
                unset( $this->db_statements['destroy'] );
            }
            try {
                $this->db_statements['store'] 
                    = $db->prepare( "REPLACE INTO " . $this->name 
                            . " ( hash, path, type, value, children ) VALUES ( ?, ?, ?, ?, ? )" );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to prepare store statement" );
                unset( $this->db_statements['store'] );
            }
        }


        
        /**
         * Store the given I2CE_MagicDataNode into the database.
         * @param I2CE_MagicDataNode $node
         */
        public function store( $node ){
            $hash = $this->getHash( $node );
            $value = $node->getSaveValue();
            $children = $node->getKeys(null,true);
            if ( count( $children ) > 0 ) {
                $children_str = implode( ",", $children );
            } else {
                $children_str = null;
            }
            try {
                $result = $this->db_statements['store'];
                if( $result->execute( array( $hash, $node->getPath(),
                                $node->getType(), $value, $children_str ) )) {
                    $result->closeCursor();
                    return true;
                } else {
                    $result->closeCursor();
                    I2CE_MagicDataNode::raiseError( "Error saving to DB " . $node->getPath() .
                            " Type: " . $node->getType() . 
                            " Value: " . $value .
                            " Children: " . implode(',',$children) );
                    return false;
                }
            } catch( PDOException $e ) {
                I2CE::pdoError($e, "Failed saving to DB");
                I2CE_MagicDataNode::raiseError( "Error saving to DB " . $node->getPath() .
                        " Type: " . $node->getType() . 
                        " Value: " . $value .
                        " Children: " . implode(',',$children) );
                return false;
            }
        }
        /**
         * Retrieve the given I2CE_MagicDataNode value and type.
         * @param I2CE_MagicDataNode $node
         * @return array
         */
        public function retrieve( $node ) {
            $hash = $this->getHash( $node );
            try {
                $result = $this->db_statements['retrieve'];
                $result->execute( array( $hash ) );
                if ( $row = $result->fetch() ) {
                    if ( $row->type !== null ) {
                        $result->closeCursor();
                        return array( "type" => $row->type, "value" => $row->value, 
                                "children" => (strlen( $row->children ) > 0 ? explode( ",", $row->children ) : null ) );
                    }
                }
                unset( $row );
                $result->closeCursor();
            } catch( PDOException $e ) {
                I2CE::pdoError($e,"Cannot retrieve " . $node->getPath() . " from DB:");
                return false;
            }
            return false;
        }

        /**
         * Returns true if this storage mechanism is ready to be used.  false otherwise.
         */
        public function isAvailable() {
            $qry = 'SHOW TABLES LIKE "config"';
            $db = I2CE::PDO();
            try {
                $result = $db->query($qry);
                if ($result->rowCount() == 0) {
                    return false;
                }
                unset( $result );
            } catch( PDOException $e ) {
                I2CE::pdoError($result,"Cannot access database");
                return false;
            }
            if (count($this->db_statements) < 3) {
                return false;
            }
            return true;
        }


        public function destroy($node) {
            $hash = $this->getHash( $node );
            try {
                $this->db_statements['destroy']->execute( array( $hash ) );
                $this->db_statements['destroy']->closeCursor();
                return true;
            } catch( PDOException $e ) {
                I2CE::pdoError($e,"Cannot destroy " . $node->getPath() . " from DB:");
                return false;
            }
        }


        /**
         * Clear the all keys/values associated with this storage 
         * @return boolean
         */
        public function clear() {
            $db = I2CE::PDO();
            try {
                $db->exec("TRUNCATE TABLE " . $this->name);
                return true;
            } catch( PDOException $e ) {
                I2CE::pdoError( $e, "Unable to clear DB magic data table {$this->name}" );
                return false;
            }
        }

    }

}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
