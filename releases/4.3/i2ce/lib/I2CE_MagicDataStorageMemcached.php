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
if (!class_exists('I2CE_MagicDataStorageMemcached',false)) {
/**
 * Implement an abstract class.
 */
    require_once "I2CE_MagicDataStorage.php";

/**
 * Configuration class to lookup and save configuration options.
 * @package I2CE 
 * @access public
 */
    class I2CE_MagicDataStorageMemcached extends I2CE_MagicDataStorage {
   

        const TIMEOUT = 72000;      //20 hours
        /**
         * @protected var Memcached $memcached.  The memcached instance
         */
        protected $memcached = null;

        /**
         * Create a new instance for magic data storage.
         * @param string $name The name assigned to this storage object
         */
        public function __construct( $name ) {
            parent::__construct($name);
            if (class_exists('Memcached',false)) {
                $this->memcached = new Memcached(); 
                if (count($this->memcached->getServerList()) == 0) {
                    $this->memcached->addServer('127.0.0.1',11211);            
                }
            }
        }

        /**
         * Returns true if this storage mechanism is ready to be used.  false otherwise.
         */
        public function isAvailable() {
            if (!class_exists('Memcached',false)) {
                return false;
            }
            if (count($this->memcached->getServerList()) == 0) {
                return false;
            }
            if ( $this->memcached->getStats() === false ) {
                return false;
            }
            return true;
        }
        

        

        
        /**
         * Return the APC prefix for storing variables.
         * @param string $type The variable type (path/type/value/children)
         * @return string
         */
        public function getKey($node, $childPath = null ) {
            if ($childPath) {
                return md5( "I2CE_MD_" . $this->name . ":" . $this->getChildPath($node,$childPath) );
            } else{
                return md5( "I2CE_MD_" . $this->name . ":" . $node->getPath() );
            }
        }

        /**
         * Store the given I2CE_MagicDataNode into APC
         * @param I2CE_MagicDataNode $node
         * @returns boolean.  True on sucess
         */
        public function store( $node ){
            $save_data= array( 'type' => $node->getType());
            if ($node->is_scalar()) {
                $save_data['value'] = $node->getSaveValue();
            } else {
                $save_data['value'] = null;
            }
            $children = $node->getKeys(null,true);
            if ( count( $children ) > 0 ) {
                $save_data['children'] = $children;
            }  else {
                $save_data['children'] = null;
            }          
            if (!$this->memcached->set($this->getKey($node),$save_data, self::TIMEOUT)) {
                I2CE_MagicDataNode::raiseError( "Error saving to Memcached " . $node->getPath() .
                                                " Type: " . $node->getType() .
                                                " Children: " . print_r($save_data['children'], true) .
                                                " Message: " . $this->memcached->getResultMessage() .
                                                " Stats: " . print_r( $this->memcached->getStats(),true) .
                                                " Value: " . $save_data['value']
                                                );
                return false;
            } else {
                return true;
            }
        }



        /**
         *Renames a child node
         * @param I2CE_MagicDataNode $node
         *@param string $old
         *@param string $new
         *@returns boolean.  True on success, false on failure
         */        
        public function renameChild($node,$old,$new) {
            $key = $this->getKey($node);
            $values = $this->memcached->get($key);
            if ( !is_array( $values ) ) {
                I2CE::raiseError("Bad data for " . $node->getPath());
                return false;
            }
            if (!array_key_exists('children',$values)) {
                I2CE::raiseError($node->getPath() . " has no children");
                return false;
            }
            if ( ($key = array_search($old,$values['children'])) === false) {
                I2CE::raiseError("Child $old on " . $node->getPath(false) . " does not exist");
                return false;
            }
            $values['children'][$key] = $new;
            if (!$this->memcached->set($key,$values, self::TIMEOUT)) {
                I2CE_MagicDataNode::raiseError( "Error renaming $old to $new in MemCached " . $node->getPath()); 
                return false;
            }
            return $this->renameDecendents($node,array($old=>$new));
        }


        /**
         * Rename the descenedent children for which we need to rename  its paths
         * @param I2CE_MagicDataNode $node 
         * @param array $children an array of child paths we need to rename its path
         */
        protected function renameDecendents($node,$children) {
            $ret = true;
            foreach ($children as $old=>$new) { //these are the old child paths
                $old_key = $this->getKey($node , $old); //existing key
                $values = $this->memcached->get($old_key);
                if ( !is_array( $values ) ) {
                    continue;
                }
                $new_key = $this->getKey($node,$new);
                $values['path'] = $this->getChildPath($node,$new);
                if (!$this->memcached->set($new_key,$values, self::TIMEOUT)) {
                    I2CE_MagicDataNode::raiseError( "Error renaming $old to $new in MemCached " . $node->getPath()); 
                    $ret = false;
                    continue;
                }
                $this->memcached->delete( $old_key );
                $t_children = array();
                foreach ($values['children'] as $t_child) {
                    $t_children[$old . '/' . $t_child ] = $new . '/' . $t_child;
                }
                $this->renameDescendents($node,$t_children);
            }
            return $ret;
        }
    


        /**
         * Retrieve the given I2CE_MagicDataNode value and type.
         * @param I2CE_MagicDataNode $node
         * @return array
         */
        public function retrieve( $node ) {
            $save_data = $this->memcached->get($this->getKey($node));
            if (is_array($save_data)) {
                return $save_data;
            }
            return false;
        }

        /**
         * Erases the given I2CE_MagicDataNode from the storage mechanism
         * @param I2CE_MagicDataNode
         */
        public function destroy($node) {
            if ($node->is_indeterminate()) { 
                return true;
            }
            if ( $this->memcached->delete( $this->getKey($node) ) ) {
                return true;
            }
            if ($this->memcached->getResultCode() == Memcached::RES_NOTFOUND) {
                //I2CE::raiseError( "attempted to destroy non-existent key from memcached:" . $this->getKey($node));
                return true;
            }
            I2CE::raiseError( "Couldn't destroy node (" . $node->getPath() . ") from memcached: " 
                              . $this->memcached->getResultMessage() );
            return false;
        }
    
        /**
         * Delete all APC keys associated with this storage object.
         * @return boolean
         */
        public function clear() {
            return $this->memcached->flush();
        }

    }

}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
