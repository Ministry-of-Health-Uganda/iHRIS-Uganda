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
if (!class_exists('I2CE_MagicDataStorageAPC',false)) {
/**
 * Implement an abstract class.
 */
    require_once "I2CE_MagicDataStorage.php";

/**
 * Configuration class to lookup and save configuration options.
 * @package I2CE 
 * @access public
 */
    class I2CE_MagicDataStorageAPC extends I2CE_MagicDataStorage {
        
        const APC_TIMEOUT = 72000;

        /**
         * Returns true if this storage mechanism is ready to be used.  false otherwise.
         */
        public function isAvailable() {
            if (!array_key_exists('HTTP_HOST',$_SERVER)) {
                return false;
            }
            if (!extension_loaded('apc')) {
                return false;
            }
            if (version_compare(phpversion('apc'), '3.1.3')== 0) {
                //3.1.3 which are the versions in meerkat and lucid are broken 
                I2CE::raiseError("Broken version of apc:3.1.3");
                return false;
            }
            if (ini_get('apc.slam_defense')) {
                I2CE::raiseError("Slam defense active in APC. --- not using magic data storage:" . ini_get('apc.slam_defense'));
                return false;
            }
            return true;
        }


        
        /**
         * Return the APC prefix for storing variables.
         * @param string $type The variable type (path/type/value/children)
         * @return string
         */
        public function getPrefix( $type ) {
            return "I2CE_MD_" . $this->name . "_" . $type . "_";
        }

        /**
         * Store the given I2CE_MagicDataNode into APC
         * @param I2CE_MagicDataNode $node
         * @returns boolean.  True on sucess
         */
        public function store( $node ){
            $hash = $this->getHash( $node );
            $save_data= array( 'type' => $node->getType(),
                               'path' => $node->getPath() );
                
            $value = $node->getSaveValue();
            if ( $value !== null ) {
                $save_data['value'] = $value;
            }
            $children = $node->getKeys(null,true);
            if ( count( $children ) > 0 ) {
                $save_data['children'] = $children;
            }
            if ( apc_store( $this->getPrefix( "data" ) . $hash,
                            serialize( $save_data ), self::APC_TIMEOUT ) !== true ) {
                I2CE_MagicDataNode::raiseError( "Error saving to APC " . $node->getPath() .
                                                " Type: " . $node->getType() .
                                                " Value: " . $value .
                                                " Children: " . implode(',',$children) );
                return false;
            }
            return true;
        }


        /**
         * Retrieve the given I2CE_MagicDataNode value and type.
         * @param I2CE_MagicDataNode $node
         * @return array
         */
        public function retrieve( $node ) {
            $hash = $this->getHash( $node );
            $fetch = apc_fetch( $this->getPrefix( "data" ) . $hash );
            if ( $fetch !== false ) {
                $values = unserialize( $fetch );
                if ( is_array( $values ) ) {
                    if (!array_key_exists('children',$values)) {
                        $values['children'] = array();
                    }
                    if (!array_key_exists('value',$values)) {
                        $values['value'] = null;
                    }
                    return $values;
                }
            }
            return false;
        }


        /**
         *Renames a child node
         * @param I2CE_MagicDataNode $node
         *@param string $old
         *@param string $new
         *@returns boolean.  True on success, false on failure
         */        
        public function renameChild($node,$old,$new) {
            $hash = $this->getHash($node);
            $fetch = apc_fetch( $this->getPrefix( "data" ) . $hash );
            if ( $fetch === false ) {
                I2CE::raiseError("Node " . $node->getPath(false) . " not found");
                return false;
            }
            $values = unserialize( $fetch );
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
            if ( apc_store( $this->getPrefix( "data" ) . $hash,  serialize( $values ), self::APC_TIMEOUT ) !== true ) {
                I2CE_MagicDataNode::raiseError( "Error renaming $old to $new in APC " . $node->getPath()); 
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
                $old_hash = $this->getHash($node , $old); //existing hash
                $fetch = apc_fetch( $this->getPrefix( "data" ) . $old_hash );
                if ( $fetch === false ) {
                    continue;
                }
                $values = unserialize( $fetch );
                if ( !is_array( $values ) ) {
                    continue;
                }
                $new_hash = $this->getHash($node,$new);
                $values['path'] = $this->getChildPath($node,$new);
                if ( apc_store( $this->getPrefix( "data" ) . $new_hash,  serialize( $values ), self::APC_TIMEOUT ) !== true ) {
                    I2CE_MagicDataNode::raiseError( "Error renaming $old to $new in APC " . $node->getPath()); 
                    $ret = false;
                    continue;
                }
                apc_delete( $this->getPrefix( "data" ) .$old_hash);
                $t_children = array();
                foreach ($values['children'] as $t_child) {
                    $t_children[$old . '/' . $t_child ] = $new . '/' . $t_child;
                }
                $this->renameDescendents($node,$t_children);
            }
            return $ret;
        }
    
        /**
         * Return a list of all APC keys associated with this storage object.
         * @return array
         */
        public function getKeys() {
            $user_cache = apc_cache_info('user' );
            $prefix = $this->getPrefix( "data" );
            $len = strlen($prefix);
            $iter = new APCIterator('user');
            $keys =array();
            foreach ($iter as $item) {
                if (!is_array($item) 
                    || !array_key_exists('key',$item)
                    || $prefix != substr($item['key'],0,$len)
                    ) {
                    continue;
                }
                $keys[] = $item['key'];
            }


            return $keys;
        }

        /**
         * Erases the given I2CE_MagicDataNode from the storage mechanism
         * @param I2CE_MagicDataNode
         */
        public function destroy($node) {
            //apc_delete returns false if the key is node there.
            apc_delete( $this->getPrefix( "data" ) .$this->getHash( $node )); 
            return true;
        }
    
        /**
         * Delete all APC keys associated with this storage object.
         * @return boolean
         */
        public function clear() {
            I2CE::raiseError("Clearing all keys from APC with prefix " . $this->getPrefix( "data" ));
            $success = true;
            $keys = $this->getKeys();
            foreach( $keys as $key ) {
    		$success = $success && apc_delete( $key );
            }
            return $success;
        }

    }

}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
