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

if (!class_exists('I2CE_MagicDataStorageDBAlt',false)) {

/**
 * Configuration class to lookup and save configuration options.
 * @package I2CE 
 * @access public
 */
    class I2CE_MagicDataStorageDBAlt extends I2CE_MagicDataStorage {

        /**
         * @var array An array of prepared statements for looking up stored magic data.
         */
        private $db_statements;

        /**
         * @var PDO $db DB instnace
         */
        protected $db;

        public function __construct($name) {
            parent::__construct($name);
            $this->db = I2CE::PDO();
            $this->setUpStatements();
            try {
                $this->db->exec("SET SESSION group_concat_max_len = @@max_allowed_packet");
                $res = $this->db->query("SELECT @@group_concat_max_len = @@max_allowed_packet AS use_quick,  @@group_concat_max_len as len");                        
                $row = $res->fetch();
                if ($row->use_quick) {
                    $this->use_quick = true;
                } else {
                    I2CE::raiseError("Using slow config_alt retreival.  Please set group_concat_max_len in mysql's config to be the same as max_allowed_packet.  Currently it is set " . $row->len . " to bytes" );
                }
                unset( $row );
                unset( $result );
            } catch( PDOException $e ) {
                I2CE::pdoError($e,"Bad group concat check");
                return false;
            }
        }

        /**
         * Set up a cache of prepared statements.
         */
        protected function setUpStatements() {
            $this->db_statements  = array();
            try {
                $this->db_statements['retrieve_type_val']
                    = $this->db->prepare( "SELECT type,value FROM config_alt WHERE path_hash = ? LIMIT 1" );
            } catch( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to prepare retrieve_type_val" );
                unset( $this->db_statements['retrieve_type_val'] );
            }
            try{
                $this->db_statements['retrieve_children']
                    = $this->db->prepare( "SELECT name as children  FROM config_alt WHERE parent = ? " );
            } catch( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to prepare retrieve_children" );
                unset( $this->db_statements['retrieve_children'] );
            }
            try{
                $this->db_statements['retrieve_type_val_children']
                    = $this->db->prepare( "SELECT n.type AS type,n.value AS value, GROUP_CONCAT(c.name SEPARATOR '/') as children FROM config_alt n  LEFT JOIN config_alt c ON "
                            . "c.parent = IF(n.parent = '/', CONCAT('/',n.name), CONCAT(n.parent,'/',n.name)) WHERE n.path_hash = ? GROUP BY type, value LIMIT 1" );
            } catch( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to prepare retrieve_type_val_children" );
                unset( $this->db_statements['retrieve_type_val_children'] );
            }
            try{
                $this->db_statements['store'] 
                    = $this->db->prepare( "REPLACE INTO config_alt  (  path_hash, parent , name , type, value) VALUES ( ?,?, ?, ?, ? )" );
            } catch( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to prepare store" );
                unset( $this->db_statements['store'] );
            }
            try{
                $this->db_statements['destroy']
                    = $this->db->prepare( "DELETE  FROM config_alt WHERE path_hash = ? LIMIT 1", array('text') );
            } catch( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to prepare destroy" );
                unset( $this->db_statements['destroy'] );
            }

        }



        
        /**
         * Store the given I2CE_MagicDataNode into the database.
         * @param I2CE_MagicDataNode $node
         */
        public function store( $node ){
            if ($node instanceof I2CE_MagicData) {
                $parent = '';
                $name = '';
                $path = '/';
            } else {
                $name = $node->getName();
                if (is_string($name) && strlen($name) == 0) {
                    I2CE::raiseError("Non-existent name on a non-root magic data node");
                    return false;
                }
                $parentNode = $node->getParent();
                if ($parentNode instanceof I2CE_MagicData) {
                    $parent = '/';
                    $path  = '/' . $name;
                } else {
                    $parent = $parentNode->getPath(false);
                    $path  = $parent .  '/' . $name;
                }
            }
            $value = $node->getSaveValue();
            //$path_hash = md5($path);
            $path_hash = $this->getHash( $node );
            try {
                if($this->db_statements['store']->execute( array(  $path_hash, $parent,$name, $node->getType(), $value  ) )) {
                    $this->db_statements['store']->closeCursor();
                    return true;
                } else {
                    $this->db_statements['store']->closeCursor();
                    I2CE_MagicDataNode::raiseError( "Error saving to DB " . $node->getPath() .
                            " Type: " . $node->getType() . 
                            " Value: " . $value );

                    return false;
                }
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to store in DB" );
                I2CE_MagicDataNode::raiseError( "Error saving to DB " . $node->getPath() .
                        " Type: " . $node->getType() . 
                        " Value: " . $value );

                return false;
            }
        }
        /**
         * Retrieve the given I2CE_MagicDataNode value and type.
         * @param I2CE_MagicDataNode $node
         * @return array
         */
        public function retrieve( $node ) {
            $ret = array();
            if ($this->use_quick) {
                try {
                    $result = $this->db_statements['retrieve_type_val_children'];
                    $result->execute( array( $this->getHash( $node ) ));
                    if (! $row = $result->fetch() ) {
                        return false;
                    }
                    if ( $row->type === null ) {
                        return false;
                    }
                    $ret['type'] = $row->type;
                    $ret['value'] = $row->value;
                    if (strlen($row->children)  > 0) {
                        $ret['children'] =  explode( "/", $row->children );
                    } else {
                        $ret['children'] = null;
                    } 
                    $row = null; 
                    $result->closeCursor();
                } catch( PDOException $e ) {
                    I2CE::pdoError($e,"Cannot retrieve val and type for " . $node->getPath() . " from DB:");
                    return false;
                }
            } else {
                try {
                    $result = $this->db_statements['retrieve_type_val'];
                    $result->execute( array( $this->getHash( $node ) ));
                    if (! $row = $result->fetch() ) {
                        return false;
                    }
                    if ( $row->type === null ) {
                        return false;
                    }
                    $ret['type'] = $row->type;
                    $ret['value'] = $row->value;
                    $row = null; 
                    $result->closeCursor();
                    if ($node instanceof I2CE_MagicData) {
                        $p_path = '/';
                    } else {
                        $name = $node->getName();
                        if (is_string($name) && strlen($name) == 0) {
                            I2CE::raiseError("Non-existent name on a non-root magic data node");
                            return false;
                        }
                        $parentNode = $node->getParent();
                        if ($parentNode instanceof I2CE_MagicData) {
                            $p_path  = '/' . $name;
                        } else {
                            $p_path  = $parentNode->getPath(false) . '/' . $name;
                        }
                    }
                    try {
                        $result = $this->db_statements['retrieve_children'];
                        $result->execute( array( $p_path ) );
                        $children = $result->fetchAll(PDO::FETCH_COLUMN,0);
                        $result = null;
                        unset( $result );
                        if (!is_array($children)) {
                            I2CE::raiseError("Cannot fetch children for " . $node->getPath());
                            return false;
                        }
                        $ret['children'] = $children;
                        $result->closeCursor();
                    } catch( PDOException $e ) {
                        I2CE::pdoError($result,"Cannot retrieve children for " . $node->getPath() . " from DB:");
                        return false;
                    }
                } catch( PDOException $e ) {
                    I2CE::pdoError($result,"Cannot retrieve val and type for " . $node->getPath() . " from DB:");
                    return false;
                }
            }
            return $ret;
        }

        protected $use_quick = false;

        /**
         * Returns true if this storage mechanism is ready to be used.  false otherwise.
         */
        public function isAvailable() {
            $qry = 'SHOW TABLES LIKE "config_alt"';
            try {
                $result = $this->db->query($qry);
                if ($result->rowCount() == 0) {
                    I2CE::raiseError("No config_alt");
                    return false;
                }
                unset( $result );
            } catch( PDOException $e ) {
                I2CE::pdoError($e,"Cannot access database");
                return false;
            }
            if (count($this->db_statements) != 5) {
                I2CE::raiseError("No prep stmts:" . count($this->db_statements));
                return false;
            }
            return true;
        }


        public function destroy($node) {
            if ($node->is_indeterminate()) { 
                return true;
            }            
            try {
                $this->db_statements['destroy']->execute( array( $this->getHash($node) ) );
                $this->db_statements['destroy']->closeCursor();
                return true;
            } catch ( PDOException $e ) {
                I2CE::pdoError($e,"Cannot destroy " . $node->getPath() . " from DB:");
                return false;
            }
        }


        /**
         * Clear the all keys/values associated with this storage 
         * @return boolean
         */
        public function clear() {
            try {
                $this->db->exec("TRUNCATE TABLE " . $this->name);
                return true;
            } catch( PDOException $e ) {
                I2CE::pdoError( $e,"Unable to clear DB magic data table {$this->name}");
                return false;
            }
        }



        /**
         *Renames a child node
         * @param I2CE_MagicDataNode $node
         *@param string $old
         *@param string $new
         *@returns boolean.  True on success, false on failure
         */        
        public function renameChild($node, $old,$new) {
            if ($node instanceof I2CE_MagicData) {
                $parentPath = '/';
                $offset = strlen('/' . $old)  +1;
                $fullOffset = strlen($node->getPath(true) . '/' . $old) ;
                // if $old ='old', results in calling SUBSTR(parent,5)
                //so if parent = '/old/path' then substr(parent,5) = '/path'
                //we want to result in /new/path
            } else {
                $parentPath = $node->getPath(false); //e.g. 
                $offset = strlen($node->getPath(false) . '/' . $old) +1;
                $fullOffset = strlen($node->getPath(true) . '/' . $old) ;
                //child: if $old = 'old' and $node='/some', then calling SUBSTR(parent, 10)
                //so if parent = /some/old/path then substr(parent,10) = /path
                //we want to result in /some/new/path
            }
            $newPath = $this->getChildPath($node,$new,false);
            $newFullPath = $this->getChildPath($node,$new,true);
            $oldPath = $this->getChildPath($node,$old,false);
            $qry_node = "UPDATE config_alt SET name = ?, path_hash = ? WHERE parent = ? AND name = ?";
            $qry_node_args = array( $new, $this->getHash($node, $new), $parentPath, $old );

            //change direct children
            $qry_child = "UPDATE config_alt SET path_hash = MD5(CONCAT(?, '/',name)), parent = ? WHERE parent = ?";
            $qry_child_args = array( $newFullPath, $newPath, $oldPath );
            //change descendents
            $qry_desc = "UPDATE config_alt SET path_hash = MD5( CONCAT(?, SUBSTR(parent,?) , '/',name)) "
                . ", parent = CONCAT(?, SUBSTR(parent,?) ) WHERE parent LIKE ?";
            $qry_desc_args = array( $newFullPath, $offset, $newPath, $offset, $oldPath .'/%' );


            try {
                $this->db->beginTransaction();
                I2CE_PDO::execParam( $qry_node, $qry_node_args );
                I2CE_PDO::execParam( $qry_child, $qry_child_args );
                I2CE_PDO::execParam( $qry_desc, $qry_desc_args );
                $this->db->commit();
            } catch( PDOException $e ) {
                $this->db->rollback();
                I2CE::pdoError( $e, "Unable to rename child.");
                return false;
            }


            return true;
        }

    }

}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
