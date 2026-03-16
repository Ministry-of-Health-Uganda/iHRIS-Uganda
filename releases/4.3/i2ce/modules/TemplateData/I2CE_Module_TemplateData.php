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
 *  I2CE_Module_TemplateData
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */


class I2CE_Module_TemplateData extends I2CE_Module {
    public static function getHooks() {
        return array(
            'pre_page_prepare_display_I2CE_TemplateMeister'=> 'processDisplayData',
            'post_page_prepare_display_I2CE_TemplateMeister'=> 'cleanupDataNodes'
            );
    }

    public static function getMethods() {
        return array(
            'I2CE_Page->setDataTypePriority'=>'setDataTypePriority',
            'I2CE_Template->setDataTypePriority'=>'setDataTypePriority',
            'I2CE_Page->setData'=>'setData',
            'I2CE_Template->setData'=>'setData',
            'I2CE_Page->getData'=>'getData',
            'I2CE_Template->getData'=>'getData',
            'I2CE_Page->getDefaultData'=>'getDefaultData',
            'I2CE_Template->getDefaultData'=>'getDefaultData',
            'I2CE_Page->removeData'=>'removeData',
            'I2CE_Template->removeData'=>'removeData',
            'I2CE_Page->getDataNames'=>'getDataNames',
            'I2CE_Template->getDataNames'=>'getDataNames',
            'I2CE_Page->ensureNode'=>'ensureNode',
            'I2CE_Template->ensureNode'=>'ensureNode'
            );
    }



    /**
     * An array which holds all data that is set realtive to a node.
     * The key of the arrays are integers (counters) greater than or equal to  -1.
     * -1 holds the default values. for each type in:
     * $data[-1][$type]
     * For example, the default form is held at $data[-1]['FORM']
     * <br/>
     * The indexing is as follows for counters >= 0
     * $data[$counter][$type][$name]['data']
     * $data[$counter][$type][$name]['nodes']
     *  $type is the type of data, such as 'FORM'
     *  $name is the name of the data
     *  at 'data' we hold the actual data
     * and at 'nodes' we hold an array of nodes at which the data is relative to.
     * @var array
     */
    protected $data;

    /**
     * Constructor
     */
    public function __construct() {
        $this->data= array();
        $this->data[-1] = array();// for defaults.  
        $this->data_priorities = array();
        $this->postponed_nodes = array();
    }

        
        
    public function processDisplayData($page) {
        I2CE_ModuleFactory::callHooks('pre_process_templatedata',$page);
        $this->templateData($page);
        I2CE_ModuleFactory::callHooks('post_process_templatedata',$page);
    }


        


    /**
     *  An array that holds information about optional propities for the data types
     */
    protected $data_priorities;
        
    /**
     * Set an optional  priority for process data types
     * @param string $type
     * @param integer $priority
     */
    public function setDataTypePriority($obj,$type,$priority) {
        $this->data_priorities[$type] = $priority;
    }


    protected function templateData($page) {
        if (!$page instanceof I2CE_Page) {
            I2CE::raiseError("Did not get expected page");
            return;
        }
        if (!$page->getTemplate() instanceof I2CE_TemplateMeister) {
            I2CE::raiseError("Did not get expected template");
            return;
        }
        $types = array();
        foreach ($this->data as $counter =>$data) {
            foreach (array_keys($data) as $type) {
                $types[$type] = true;
            }
        }
        $prioritized_list = array();
        $unprioritized_list = array();
        foreach(array_keys($types) as $type) {
            if (isset($this->data_priorities[$type])) {
                $prioritized_list[$this->data_priorities[$type]][] = $type;
            } else {
                $unprioritized_list[] = $type;
            }
        }
        ksort($prioritized_list);
        array_push($prioritized_list,           $unprioritized_list);        
        $this->processPostponed($page->getTemplate());
        foreach ($prioritized_list as $types) {
            foreach ($types as $type) {
                I2CE_ModuleFactory::callHooks("process_templatedata_$type",$page);
            }
        }
        

    }


    public function cleanupDataNodes($page) {
        if (!$page instanceof I2CE_Page) {
            I2CE::raiseError("Did not get expected page -- got" . get_class($page));
            return;
        }
        $template = $page->getTemplate();
        if (!$template instanceof I2CE_TemplateMeister) {
            return;
        }
        $qry = '//*[@I2CEDataNode]';
        $results = $template->query($qry);
        for( $i = 0; $i < $results->length; $i++ ) {
            $node = $results->item($i);
            $node->removeAttribute('I2CEDataNode');
        }
    }



        
        
    /**
     * Ensure that a node is really a node.  If it is not, hopes that it is an ID
     * and then makes it the corresponding node
     * @param I2CE_Template $template
     * @param mixed &$node.  Either a DOMNode or a node id.  
     * @param bool $make_doc_on_failure defaults to true.  If we can't find the node, make it the document element
     * If false, we leave $node alone.
     */
    protected function ensureNodeArray($template,$node,$make_doc_on_failure = true) {
        if ( $node instanceof DOMNode) {
            return  array($node);
        } 
        if ($node instanceof DOMNodeList) {
            $nodes = array();
            for ($i=0; $i < $node->length; $i++) {
                $nodes[] = $node->item($i);
            }
            return $nodes;
        }               
        if (is_string($node)) {
            $nodes = $template->query("//*[@id='$node']");
            if ($nodes->length > 0) {
                return $this->ensureNodeArray($template,$nodes,$make_doc_on_failure);
            } else {
                if ($make_doc_on_failure) {
                    return array($template->getDoc()->documentElement);
                } else {
                    return array($node); //node does not exist at this point
                }
            }
        }
        //we got some garbage
        if ($make_doc_on_failure) {
            return array($template->getDoc()->documentElement);
        } else {
            return array();
        }
    }

    /**
     * Ensure that a node is really a node.  If it is not, hopes that it is an ID
     * and then makes it the corresponding node
     * @param I2CE_Template $template
     * @param mixed $node.  Either a DOMNode or a node id.  
     * @param bool $make_doc_on_failure defaults to true.  If we can't find the node, make it the document element
     * If false, we leave $node alone.
     */
    public function ensureNode($template,$node,$make_doc_on_failure = false) {
        $this->_ensureNode($template,$node,$make_doc_on_failure);
        return $node;
    }


    /**
     * Ensure that a node is really a node.  If it is not, hopes that it is an ID
     * and then makes it the corresponding node
     * @param I2CE_Template $template
     * @param mixed &$node.  Either a DOMNode or a node id.  
     * @param bool $make_doc_on_failure defaults to true.  If we can't find the node, make it the document element
     * If false, we leave $node alone.
     */
    protected function _ensureNode($template,&$node,$make_doc_on_failure = true) {
        if ( isset($node) && ($node instanceof DOMNode)) {
            //we are good so quit.
            return;
        }
        $new_node = null;
        if (is_string($node) ) {
            $new_node = $template->getElementById( $node);
        }
        if ($new_node == null) {
            if ($make_doc_on_failure) {
                $node = $template->getDoc()->documentElement;
            }
        } else {
            $node = $new_node;
        }
    }

        
        
        
    /**
     * Remove the specified data 
     * @param string $type the type of data
     * @param string $name the name of the data.  if null it removes all the data of the specifed type
     * @param mixed $nodes Specfies the node(s) at which the data is removed. If $node is a DOMNode
     * then it is the node.  If null (default) then we search for data that applies to the whole.  Otherwise $node should
     * specify the ID of some node in the DOM.  We will remove the data at all nodes that lie at or below the given node.  Setting
     * to false, means we dont check below any nodes.
     * $param mixed $above_nodes.  Defaults to null in which case no action is taken.  Otherwise, if it is a DOMNode, or an id of one,
     * or an arra of such things.  we start check at that node and move up the DOM until we find the data we are looking for and
     * then remove it.  
     * 
     * It can also be a DOMNodeList or an array of DOMNodes.
     */
    public function removeData($template, $type,$name,$nodes =null, $above_nodes = null) {
        if ($template instanceof I2CE_Page) {
            $template = $template->getTemplate();
        }
        if (!$template instanceof I2CE_TemplateMeister) {
            return;
        }
        if ($type === null) {
            I2CE::raiseError("Type needs to be given", E_USER_ERROR);
            return;
        }
        $doc = $template->getDoc();
        if ($nodes === null) {
            $nodes = array($doc);
        } else if ($nodes === false) {
            $nodes = array();
        } else {
            $nodes = $this->ensureNodeArray($template,$nodes,false,true);
        }
        if ($above_nodes !== null) {
            $above_nodes = $this->ensureNodeArray($template,$above_nodes,false,true);
        } else {
            $above_nodes = array();
        }
        foreach ($above_nodes as $node) {                        
            foreach ($this->data as $counter=>$data_types) {
                if ($counter == -1) {
                    continue; //don't remove defaults
                }
                if (!array_key_exists($type,$data_types) || !isset($data_types[$type])) {
                    continue;
                }
                if ($name === null) {
                    $name_list = $this->data[$counter][$type];
                } else {
                    if (!isset($this->data[$counter][$type][$name])) {
                        continue;
                    }
                    $name_list = array($name);
                }
                foreach ($name_list as $tmp_name) {
                    $tmp_nodes = $this->data[$counter][$type][$tmp_name]['nodes'];
                    foreach ($tmp_nodes as $i=>$tmp_node) {
                        $this->_ensureNode($template,$tmp_node,false);
                        if ((!$tmp_node instanceof DOMNode) || (!$node instanceof DOMNode)) {
                            continue;
                        }
                        $walker_node = $node;
                        do {
                            if ($walker_node->isSameNode($tmp_node)) {
                                unset($this->data[$counter][$type][$tmp_name]['nodes'][$i]);
                                break;
                            }
                            $walker_node = $walker_node->parentNode;
                        } while ($walker_node instanceof DOMNode);
                    }
                }
            }
        }

        foreach ($nodes as $node) {            
            foreach ($this->data as $counter=>$data_types) {
                if ($counter == -1) {
                    continue; //don't remove defaults
                }
                if (!array_key_exists($type,$data_types) || !isset($data_types[$type])) {
                    continue;
                }
                if ($name === null) {
                    $name_list = $this->data[$counter][$type];
                } else {
                    if (!isset($this->data[$counter][$type][$name])) {
                        continue;
                    }
                    $name_list = array($name);
                }
                foreach ($name_list as $tmp_name) {
                    $tmp_nodes = $this->data[$counter][$type][$tmp_name]['nodes'];
                    foreach ($tmp_nodes as $i=>$tmp_node) {
                        $this->_ensureNode($template,$tmp_node,false);
                        if (($tmp_node instanceof DOMNode) && ($node instanceof DOMNode)) {
                            //while ($tmp_node && !(   $tmp_node->isSameNode($doc)  ||  $tmp_node->isSameNode($node))) {
                            while ($tmp_node && !($tmp_node->isSameNode($node))) {
                                $tmp_node = $tmp_node->parentNode;
                            }
                            if ($tmp_node instanceof DOMNode && $tmp_node->isSameNode($node)) {
                                unset($this->data[$counter][$type][$tmp_name]['nodes'][$i]);
                            }
                        } else if ( is_string($node) && is_string($tmp_node)) {
                            if ($node == $tmp_node) {
                                unset($this->data[$counter][$type][$tmp_name]['nodes'][$i]);
                            }                                   
                            unset($this->postponed_nodes[$node]);
                        }
                    }
                }
            }
        }

    }


    /**
     *  Looks through the list of postponed nodes  IDs to see if there is now a corresponding
     *  node in the DOM.  If so, we clear the node ID from our list of postponed nodes and
     *  set the node to have the attribute I2CEDataNode with the appropripate counter value
     */
    protected function processPostponed($template, $id = null) {
        if ($id === null || !is_scalar($id) || !array_key_exists($id,$this->postponed_nodes)) {
            $p_nodes = $this->postponed_nodes;
        } else {
            $p_nodes = array($id=>$this->postponed_nodes[$id]);
        }
        foreach ($this->postponed_nodes as $id=>$p_data) {
            $counter = $p_data['counter'];
            $nodes = $template->query("//*[@id='$id']");
            if ($nodes->length > 0) {
                for ($i=0; $i < $nodes->length; $i++) {
                    foreach ($p_data['data'] as $data) {
                        $this->data[$counter][$data['type']][$data['name']]['nodes'] = array();
                        $postponed = $nodes->item($i);
                        if (!$postponed instanceof DOMElement) {
                            continue;
                        }
                        $postponed->setAttribute('I2CEDataNode',$counter);
                        $this->data[$counter][$data['type']][$data['name']]['nodes'][] = $postponed;
                    }
                }
                unset($this->postponed_nodes[$id]);
            }
        }
    }

        




    /**
     * Sets data relative to a node(s)
     * @param mixed $obj.  The  data to  set
     * @param mixed $nodes Specfies the node at which the data is set. If $node is a DOMNode
     * then it is the node.  If null then the data applies to the whole.  Otherwise $node should
     * specify the ID of some node in the DOM or a DOMNode.  Also can be an array of string or DOMNode
     * @param string $type.  The type of the data.  E.g. 'FORM'.  For a form it defaults to $form->getName(), otherwise
     * it defaults to null and throws an error if not set.
     * @param string $name. The name of the data.  Defaults to ''
     * @param boolean $overwrite Defaults to false.  Set to true to overwrite any data of this particular
     * name and type at the specifed node.
     *
     *<br/>
     * Before where you would use addOption($selectID,$id,$value) you can  now 
     * use setData(array('value'=>$id,'text'=>$value),$selectID,'OPTION')  -- this should probably be a   fuzzy method
     *
     * 
     */
    public function setData($template,$obj,$nodes, $type,$name='',$overwrite = false) {
        if ($template instanceof I2CE_Page) {
            $template = $template->getTemplate();
        }
        if (!$template instanceof I2CE_TemplateMeister) {
            return;
        }
        //if node is null, it was (presumably) inentionally so and we should make it the doc
        //otherwise we might need to postpone it if the node does not exist yet so we don't
        //want to make it the doc.  this is why we do $node===null in the next line.
        if ($type === null) {
            I2CE::raiseError("Please specify the type of this data");
            return;
        }
        $nodes = $this->ensureNodeArray($template,$nodes,$nodes === null);
                        
        foreach ($nodes as $node) {            
            if ($node instanceof DOMNode) {
                if ($node->hasAttribute('id') && array_key_exists($node->getAttribute('id'),$this->postponed_nodes)) {
                    //need to move the postponed nodes over
                    $this->processPostponed($template, $node->getAttribute('id'));
                }
                if (!($node->hasAttribute('I2CEDataNode'))) {
                    //mark this node as a data node with a unique ID.
                    $counter = count($this->data) - 1;  //this is always the next index b/c $this->data has an entry at [-1] for the defaults.
                    $node->setAttribute('I2CEDataNode',$counter);
                } else {
                    $counter = $node->getAttribute('I2CEDataNode');
                }
            } else {
                //the node does not exist at this point.  hopefully it will become available 
                //we need to postpone setting the node attribute.
                //check to see if this node Id is already registered as a postponed node.
                if (isset($this->postponed_nodes[$node])) {
                    $this->postponed_nodes[$node]['data'][] = array('type'=>$type,'name'=>$name);
                    $counter = $this->postponed_nodes[$node]['counter'];
                } else {
                    //We have not already set this node as being postponed
                    $counter = count($this->data) -1;  //this is always the next index b/c $this->data has an entry at [-1] for the defaults.
                    $this->postponed_nodes[$node] = array('counter'=>$counter,'data'=>array(array('type'=>$type,'name'=>$name)));
                }
            }
            if (!array_key_exists($counter, $this->data) || !is_array($this->data[$counter])) {
                $this->data[$counter] = array();
            }
            if (!array_key_exists($type,$this->data[$counter]) || !is_array($this->data[$counter][$type])) {
                $this->data[$counter][$type] = array();
            }
            if (!array_key_exists($name,$this->data[$counter][$type]) || !is_array($this->data[$counter][$type][$name])) {
                $this->data[$counter][$type][$name] = array();
            }
            if (array_key_exists('nodes', $this->data[$counter][$type][$name]) && is_array($this->data[$counter][$type][$name]['nodes'])) {
                $this->data[$counter][$type][$name]['nodes'][] = $node;
            } else {
                $this->data[$counter][$type][$name]['nodes'] = array($node);
            }
            if (!array_key_exists('data',$this->data[$counter][$type][$name]) ||!is_array($this->data[$counter][$type][$name]['data'])) {
                $this->data[$counter][$type][$name]['data'] = array();
            }
            if ($overwrite   && array_key_exists(0,$this->data[$counter][$type][$name]['data'])) {
                //clear out any old data
                $this->data[$counter][$type][$name]['data'] = array();
                $this->data[$counter][$type][$name]['data'][0] = $obj;
            } else {
                $this->data[$counter][$type][$name]['data'][] = $obj;
            }
            if (! array_key_exists($type, $this->data[-1])) {
                $this->data[-1][$type] = $obj;
            }                                        
        }
    }
                





        


    /**
     * Get the data of the specified type and name that sits at or above the specifed node.
     * @param string $type.  The type of the data.  E.g. 'FORM'
     * @param string $name.  The name of the data. 
     * @param mixed $node Specfies the node at which the data is set. If $node is a DOMNode
     * then it is the node.  If null (default) then we search for data that applies to the whole.  Otherwise $node should
     * specify the ID of some node in the DOM.
     * @param boolean $all Defaults to false.  Set to true to get all data of the specified type and name
     * at the selected node.   If false it gets the next unread piece of data on the list.
     * @param boolean $use_default Set to true(default) if we should return the default data of the specified type if we
     * did not find data of the specified type
     * 
     *
     */
    public function getData($template,$type,$name,$node, $all = false, $use_default = false) {
        if ($template instanceof I2CE_Page) {
            $template = $template->getTemplate();
        }
        if (!$template instanceof I2CE_TemplateMeister) {
            return;
        }
        $this->_ensureNode($template,$node,false);
        if ($node instanceof DOMNode){
            return  $this->getDataWalkUpNodes($template,$type,$name,$node,$all,$use_default);
        } else {
            return $this->getDataByNodeID($type,$name,$node,$all,$use_default);
        }
    }

    /**
     * returns the default data of the specified type
     * @param string $type
     * returns mixed
     */
    public function getDefaultData($calling_obj,$type) {
        return $this->data[-1][$type];
    }
        
        
    /**
     * Returns the list of names registered for the specified data type
     * @param string $type
     * #returns array of boolean.  The keys are the names the values are all true.
     */
    public function getDataNames($calling_obj,$type) {
        $names = array();
        foreach ($this->data as $counter=>$data_types) {
            if ($counter == -1) {
                continue; //don't look at the default
            }
            if (!isset($data_types[$type])) {
                continue;
            }
            foreach (array_keys($data_types[$type]) as $name) {
                $names[$name] = true;
            }
        }
        return $names;
    }



        



    protected function getDataWalkUpNodes($template,$type,$name,$node,$all,$use_default) {
        //if (!$node instanceof DOMNode || $node->isSameNode($template->getDoc())) {
        if (!$node instanceof DOMNode) {
            //we did not find a data object... we went all the way up the DOM.
            if ($use_default && array_key_exists($type,$this->data[-1])) {
                return $this->data[-1][$type];
            } else {
                return null;
            }
        }
        $data_nodes = array();
        if ($node instanceof DOMElement && $node->hasAttribute('I2CEDataNode')) { 
            $counter = $node->getAttribute('I2CEDataNode');
            if (isset($this->data[$counter][$type][$name]['nodes'])) {
                $data_nodes = $this->data[$counter][$type][$name]['nodes'];
            }
        } else if ( $node->isSameNode($template->getDoc())) {
            if ($use_default && array_key_exists($type,$this->data[-1])) {
                return $this->data[-1][$type];
            } else {
                return null;
            }

        }
        foreach ($data_nodes as $data_node) {
            if (!$data_node instanceof DOMNode) {
                continue;
            }
            if ($data_node->isSameNode($node)) {
                //we found data.     
                return  $this->getDataByCounter($counter,$type,$name,$all);
            }
        }
        //no luck so walk up
        if ( !$node->parentNode instanceof DOMNode && !$node->isSameNode($node->ownerDocument) ) {
            return null;
        }
        return $this->getDataWalkUpNodes($template,$type,$name,$node->parentNode,$all,$use_default);            
    }

    protected function getDataByCounter($counter,$type,$name,$all) { 
        //convience reference
        $data =&$this->data[$counter][$type][$name]; 
        if ($all) {
            if (isset($data['data'])) {
                return $data['data'];
            } else {
                return array();
            }
        } 
        //we now know wr only want to get the next piece of data  from the set
        if (!isset($data['which'])) {
            $data['which'] = -1;
        } 
        $data['which']++;
        if ($data['which'] > count($data['data'])-1) {
            //recycle back to the beginning;
            $data['which'] = 0;
        }
        return $data['data'][$data['which']];
    }

        
    protected function getDataByNodeID($type,$name,$node,$all,$use_default) { 
        if (!array_key_exists($node,$this->postponed_nodes) || !is_array($this->postponed_nodes[$node]) || !array_key_exists('counter',$this->postponed_nodes[$node])) {
            if ($use_default) {
                return $this->data[-1][$type];
            } else {
                return null;
            }
        }
        $counter = $this->postponed_nodes[$node]['counter'];
        if ( (!is_int($counter)) 
             || (!array_key_exists($counter,$this->data)) 
             || (!is_array($this->data[$counter])) 
             || (!array_key_exists($type,$this->data[$counter])) 
             || (!array_key_exists($type,$this->data[$counter])) 
             || (!is_array($this->data[$counter][$type])) 
             || (!array_key_exists($name,$this->data[$counter][$type])) 
             || (!is_array($this->data[$counter][$type][$name])) 
             || (!array_key_exists('data',$this->data[$counter][$type][$name]))) {
            //we found no data
            if ($use_default) {
                return $this->data[-1][$type];
            } else {
                return null;
            }
        } 
        return $this->getDataByCounter($counter,$type,$name,$all);
    }


        


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
