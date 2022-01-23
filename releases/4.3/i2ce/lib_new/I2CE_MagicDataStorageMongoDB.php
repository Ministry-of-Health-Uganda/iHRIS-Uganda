<?php
/**
* © Copyright 2012 IntraHealth International, Inc.
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
* @package I2CE
* @subpackage i2ce
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1.3
* @since v4.1.3
* @filesource 
*/ 
/** 
* Class I2CE_MagicDataStorageMongoDB
* 
* @access public
*/

require_once "I2CE_MagicDataStorage.php";
if(!class_exists("I2CE_MagicDataStorageMongoDB",false)) {
    class I2CE_MagicDataStorageMongoDB extends I2CE_MagicDataStorage {
        
        /**
         * @var protected $m, mongodb connection
         */
        protected $m; 
        /**
         * @var protected $m, mongodb 
         */
        protected $mdb; 


        /**
         * Create a new instance for magic data storage.
         * @param string $name The name assigned to this storage object
         */
        public function __construct( $name ) {
            parent::__construct($name);
            if (! ($db_name = I2CE_PDO::details('dbname') )) {
                I2CE::raiseError("No database to connect to MongoDB");
                return false;
            }
            if (! ($db_password = I2CE_PDO::details('pass') )) {
                I2CE::raiseError("No password to connect to MongoDB");
                return false;
            }
            if ( !($db_user = I2CE_PDO::details('user') )) {
                I2CE::raiseError("No user to connect to MongoDB");
                return false;
            }
            $conn_string ="mongodb://localhost:27017";
            //$conn_string = 'mongodb:///tmp/mongo-27017.sock';
            try {
                $this->m = new Mongo($conn_string);
            }
            catch (Exception $e) {
                I2CE::raiseError("Could not connect to mongodb using: " . $conn_string);
                return false;
            }
        }



        public function isAvailable() {
            if (!$this->m instanceof Mongo) {
                return false;
            }
            if (! ($db_name = I2CE_PDO::details('dbname') )) {
                I2CE::raiseError("No database to connect to MongoDB");
                return false;
            }
            if ( !is_array ($dbs = $this->m->listDBs()) || !array_key_exists('databases',$dbs) || !is_array($dbs['databases']) ) {
                I2CE::raiseError("MongoDB: $db_name needs to be created:" . print_r($dbs,true));
                return false;
            } else {
                $found = false;
                foreach ($dbs['databases'] as $info) {
                    if (!is_array($info) || !array_key_exists('name',$info) || !$info['name'] = $db_name) {
                        continue;
                    }
                    $found = true;
                }
            }
            if (!$found) {
                I2CE::raiseError("MongoDB: The database $db_name needs to be created:" . print_r($dbs,true));
                return false;
            }
            if (! ($this->mdb = $this->m->selectDB($db_name)) instanceof MongoDB) {
                I2CE::raiseError("Cannot connect to mongo database $db_name");
                return false;
            }            
            if ( !is_array($mcolls = $this->mdb->listCollections()) || count($mcolls) == 0 || !in_array($db_name . ".config",$mcolls)) {
                I2CE::raiseError("MongoDB: collection $db_name.config needs to be created");            
                //this is done automatically when we select below
                return false;
            }        
            if (! ($this->mcoll = $this->mdb->selectCollection('config')) instanceof MongoCollection) {
                I2CE::raiseError("Cannot connect to  config collection in  mongo database $db_name");
                return false;
            }   
            //I2CE::raiseError("MongoDB is available for config");
            return true;
        }
        

        /**
         * Clear the all keys/values associated with this storage 
         * @return boolean
         */
        public function clear() {
            if (!$this->isAvailable()) {
                I2CE::raiseError("Cannot clear when not available");
                return false;
            }
            $this->mdb->dropCollection("config");
            if (! ($this->mcoll = $this->mdb->selectCollection("config"))) {
                return false;
            }
            if (!self::setupIndices($this->mcoll)) {
                return false;
            }
            return $this->isAvailable();
        }

        /**
         *Setup the indices of a config collection
         *@param MongoCollection
         */
        public static function setupIndices($mcoll) {
            if (!$mcoll instanceof MongoCollection) {
                I2CE::raiseError("Not a collection");
                return false;
            }
            $mcoll->ensureIndex(array(self::PATH=>1),array('unique'=>true));
            return true;
        }

        const TYPE = 'type';
        const PATH = 'path';
        const CHILDREN = 'children';
        const VALUE = 'value';


        /**
         * Store the given I2CE_MagicDataNode into mongodb
         * @param I2CE_MagicDataNode $node
         */
        public function store( $node ){
            //first we need to see if it exists:
            $path = $node->getPath(false);
            $result = $this->mcoll->findOne(array(self::PATH=>$path),array(self::PATH));
            if (!is_array($result) || count($result) == 0) {
                //does not exist
                $data = array( 
                    self::PATH  => $path,
                    self::TYPE => $node->getType(),
                    self::VALUE => $node->getSaveValue(),
                    self::CHILDREN => $node->getKeys(NULL,TRUE)
                    );
                if (! is_array( $res = $this->mcoll->insert($data, array("safe" => true))) || (array_key_exists('err',$res) && $res['err'])) {
                    I2CE::raiseError("Could not inset new node:" . print_r($res,true));
                    return false;
                }
            } else {
                //it exists and we need to update
                $data = array( 
                    self::TYPE => $node->getType(),
                    self::VALUE => $node->getSaveValue(),
                    self::CHILDREN => $node->getKeys(NULL,TRUE)
                    );

                $newdata = array('$set' => $data);
                if (! is_array($res = $this->mcoll->update(array(self::PATH=> $path), $newdata,array('safe'=>true))) || (array_key_exists('err',$res) && $res['err'])) {
                    I2CE::raiseError("Could not update existing node:" .print_r($res,true));
                    return false;
                }
            }
            return true;
        }


        /**
         * Erases the given I2CE_MagicDataNode from the storage mechanism
         * @param I2CE_MagicDataNode
         * @param boolean.  True on sucess
         */
        public function destroy($node) {
            $path = $node->getPath(false);
            if (!is_array($res = $this->mcoll->remove(array(self::PATH=>$path),array('justOne'=>true,'safe'=>true))) ||  (array_key_exists('err',$res) && $res['err'])) {
                I2CE::raiseError("Could not destroy node:" . print_r($res,true));
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
            $keys = array(self::TYPE=>true, self::VALUE=>true , self::CHILDREN=>true);
            $result = $this->mcoll->findOne(array(self::PATH=>$node->getPath(false)), $keys);
            if (!is_array($result) || count($result) == 0) {
                //I2CE::raiseError("Bad result for ". $node->getPath(false) . "\n" . print_r($result,true));
                return null;
            }
            return array( "type"     => $result[self::TYPE],
                          "value"    => $result[self::VALUE],
                          "children" => $result[self::CHILDREN]);
        }
        
        

        /**
         *Renames a child node
         * @param I2CE_MagicDataNode $node
         *@param string $old
         *@param string $new
         *@returns boolean.  True on success, false on failure
         */        
        public function renameChild($node,$old,$new) {
            $keys = array(self::TYPE=>true, self::VALUE=>true , self::CHILDREN=>true);
            $result = $this->mcoll->findOne(array(self::PATH=>$node->getPath(false)), $keys);
            if (!is_array($result) || count($result) == 0) {
                I2CE::raiseError("Could not update -- non-existing node");
                return false;
            }
            if ( ($key = array_search($old,$result[self::CHILDREN])) === false) {
                I2CE::raiseError("Child $old on " . $node->getPath(false) . " does not exist");
                return false;
            }
            $result[self::CHILDREN][$key] = $new;
            $newdata = array('$set' => array(self::CHILDREN => $result[self::CHILDREN]));
            if (! is_array($res = $this->mcoll->update(array(self::PATH=> $node->getPath(false)), $newdata,array('safe'=>true))) || (array_key_exists('err',$res) && $res['err'])) {
                I2CE::raiseError("Could not update new children:" .print_r($res,true));
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
            $fail = false;
            foreach ($children as $old=>$new) { //these are the old child paths
                $old_path = $this->getChildPath($node,$old,false);
                $new_path = $this->getChildPath($node,$new,false);
                $newdata = array('$set' => array(self::PATH =>$new_path));
                if (!  is_array($res = $this->mcoll->update(array(self::PATH=> $old_path), $newdata,array('safe'=>true))) || (array_key_exists('err',$res) && $res['err'])) {
                    $fail = true;
                    continue;
                }            
                $keys = array(self::CHILDREN=>true);
                $result = $this->mcoll->findOne(array(self::PATH=>$new_path, $keys));
                if (!is_array($result) || !array_key_exists(self::CHILDREN,$result)) {
                    continue;
                }
                $t_children = array();
                foreach ($result[$self::CHILDREN] as $t_child) {
                    $t_children[$old . '/' . $t_child ] = $new . '/' . $t_child;
                }
                if (! $this->renameDescendents($node,$t_children)) {
                    $fail = true;
                }
            }
            return $fail;
        }


    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
