<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
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
*  I2CE_SwissFactory
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


abstract class I2CE_SwissFactory {
    /***
     * @param I2CE_Page @page
     */
    protected $page;
    /***
     * @param  I2CE_Swiss $swiss.  
     */
    protected $swiss;


    protected $stored_options;
    protected $template;
    /**
     * construct a swiss swiss factory and create it if it doesn't exist.
     * @throws Exception
     */
    public function __construct(  $page,$init_options=array()) {
        if (!$page  instanceof I2CE_Page) {
            throw new Expection ("no valid page");
        }
        $this->page = $page;
        if (!$this->page instanceof I2CE_Page) {
            throw new Exception("Expecting page");
        }
        if ($this->page->request_exists(array('swissFactory','options'))) {
            $this->stored_options = $this->page->request(array('swissFactory','options'));
        } else {
            $this->stored_options = array();
        }
        $this->template = $this->page->getTemplate();
        if (!$this->template instanceof I2CE_TemplateMeister) {
            throw new Exception("Expecting template");
        }
        $this->statii['/'] = array();
        $this->redirect = null;
    }

    public function setRootSwiss() {
        $type = $this->getRootType();
        $swissName = 'I2CE_Swiss_' . $type;
        if (!class_exists($swissName)) {
            throw new Exception ("there is no swiss class $swissName");
        }        
        $storage = $this->getRootStorage();
        $this->swiss = new $swissName($storage, $this); //this could throw an error
        if (!$this->swiss instanceof I2CE_Swiss) {
            throw new Exception ("the class $swissName does not subclass I2CE_Swiss");            
        }
        $this->swiss->setPage($this->page);
    }
 
    protected function getRootType() {
        return 'Default';
    }

    abstract protected function getRootStorage();

    /**
     * Get the page
     * @returns I2CE_Page 
     */
    public function getPage() {
        return $this->page;
    }
    

    /**
     *Gets the string representation of a path
     *@param mixed $path. Either a string or an arrray of path components
     * @param mixed $addComponents. Defaults to null.  If a string or an array of path components  then it is appended to
     * the path
     *@return string
     */
    public static function getPath($path,$addComponents = null) {
        $components = self::getPathComponents($path,$addComponents);
        if (is_array($components)) {
            return  '/' . implode('/',$components);
        } else {
            return null;
        }
    }


    abstract public function getURLRoot($action = null);

    /**
     *Gets the array representation of the config node path
     *@param mixed $path. Either a string or an arrray of path components
     * @param mixed $addComponents. Defaults to null.  If a string or an array of path components  then it is appended to
     * the path
     *@return array
     */
    public static function getPathComponents($path, $addComponents = null) {
        if ($path === null) {
            $path = '';
        } 
        if ($addComponents === null) {
            $addComponents  = array();
        } 
        if (is_array($path)) {            
            $components =  $path;
        } else  if (is_string($path)) {
            $components =  preg_split('/\//',$path,-1,PREG_SPLIT_NO_EMPTY);
        } else   if (is_numeric($path)) {
            $components = array($path);
        } else {
            I2CE::raiseError("Don't know what to do with " . print_r($path,true));
            return null;
        }
        if (is_array($addComponents)) {
            //do nothing
        }else if (is_string($addComponents)) {
            $addComponents =  preg_split('/\//',$addComponents,-1,PREG_SPLIT_NO_EMPTY);
        } else   if (is_numeric($addComponents)) {
            $addComponents = array($addComponents);
        } else {
            I2CE::raiseError("Don't know what to do with " . print_r($addComponents,true));
            return null;
        }
        $components = array_merge($components,$addComponents);
        $t_components = array();
        foreach ($components as $c) {
            switch ($c) {
            case '..':
                if (count($t_components) > 0) {
                    array_pop($t_components);
                }
                break;
            case '.':
                //do nothing
                break;
            default:
                $t_components[] = $c;
                break;
            }
        }
        return $t_components;
    }






    public function getStorage($path,$add_path = null) {
        $swiss = $this->getSwiss($path,$add_path);
        if ($swiss instanceof I2CE_Swiss) {
            return $swiss->getStorage();
        } else {
            return null;
        }
    }
    

    /**
     * get the swiss for a path
     * @param mixed $path Either a string which is a config node path or an array of path components.
     * @param mixed $addPath Either a string which is a config node path or an array of additional path components.
     * @return I2CE_Swiss or null
     */
    public function getSwiss($path,$add_path = null) {
        $components = self::getPathComponents($path,$add_path);
        $swiss = $this->swiss;
        foreach ($components as $comp) {
            $swiss = $swiss->getChild($comp);
            if (!$swiss instanceof I2CE_Swiss) {
                return null;
            }
        }
        return $swiss;
    }


    public function getStoredOptions($key = null) {
        if ($key === null) {
            return $this->stored_options;
        }
        if (is_scalar($key)) {
            $key = explode(':', $key);
        }
        $opt = $this->stored_options;            
        foreach ($key as $k) {
            if (!is_array($opt)) {
                return null;
            }
            if (!array_key_exists($k,$opt)) {
                return null;
            }
            $opt = $opt[$k];
        }
        return $opt;
    }

    public function setStoredOptions($options) {
        $this->stored_options = $options;
    }


    public function getAttribute($swiss,$attr) {
        return  $swiss->getStorage()->getAttribute($attr);
    }


    public function hasAttribute($swiss,$attr) {
        return $swiss->getStorage()->hasAttribute($attr);
    }



    abstract public function getChildStorage($swiss,$child=null, $create = false);
    abstract public function getChildType($swiss,$child);
    public function getDescription($swiss) {
        return null;
    }
    public function getDisplayName($swiss) {
        return self::humanText($swiss->getName());
    }

    /**
     * Tries to turn a string (such as a magic data key) into  human text
     * @param $text
     * @returns string
     */
    public static function humanText($text){
        $text = preg_replace('/([a-zA-Z0-9])[-_+\\.]([a-zA-Z0-9])/','\\1 \\2',$text ); //words with punctuation
        $text = preg_replace('/([a-z])([A-Z])/','\\1 \\2',$text ); //camel case
        $text = ucwords($text);        
        return $text;
    }


    abstract public function getStatus($swiss);
    


    public function setRedirect($redirect) {
        $this->redirect = $redirect;
    }


    public function getURLQueryString($additional = array(),$remove=array()) {
        $options  = $this->stored_options;
        foreach ($remove as $rem) {
            $keys = explode(':',$rem);
            if (count($keys) === 0) {
                continue;
            }
            $t_options = &$options;
            $p_options = false;
            $p_key = false;
            foreach ($keys as $key) {
                if (!array_key_exists($key, $t_options)) {
                    break 2;
                }
                if (!is_array($t_options)) {
                    break 2; //trying to set array value on a a scalar node
                }
                $p_key = $key;
                $p_options = &$t_options;
                $t_options = &$t_options[$key];
            }
            unset($p_options[$p_key]);
        }
        foreach ($additional as $add=>$val) {
            $keys = explode(':',$add);
            if (count($keys) == 0) {
                continue;
            }
            $t_options = &$options;
            foreach ($keys as $key) {
                if (!is_array($t_options)) {
                    break 2; //trying to set array value on a a scalar node
                }
                $t_options = &$t_options[$key];
            }
            if (is_array($val)) {
                if (!is_array($t_options)) {
                    $t_options = array();
                }
                I2CE_Util::merge_recursive($t_options, $val);
            } else  {
                $t_options = $val;
            }
        }
        if (is_scalar($options) || (is_array($options) && count($options) > 0)) {
            return '?i2ce_json[]=' . urlencode(json_encode(array('swissFactory'=>array('options'=>$options))));
        } else {
            return '';
        }
    }

    public function updateValues($vals, $do_redirect = true, $transact = false) {
        if ($do_redirect) {
            $redirect = '';
            if ($this->redirect !== null) {
                $redirect = $this->redirect;
            } else {        
                $redirect = $this->getURLRoot('edit');                
                if ($this->page->request_exists(array('swissFactory','path'))) {
                    $redirect .=  '/' . $this->page->request(array('swissFactory','path'));
                }
                $redirect .= $this->getURLQueryString();
            }
            if ($redirect) {
                $this->page->setRedirect($redirect);
            }
        }
        if (!is_array($vals) 
            || !array_key_exists('swissFactory',$vals) 
            || !is_array($vals['swissFactory'])
            || !array_key_exists('values',$vals['swissFactory']) 
            || !is_array($vals['swissFactory']['values'])
            ) {
            return true;
        }
        $sucess = true;
        $db = I2CE::PDO();
        $updates = array();
        $has_error = false;
        if ($transact) {
            if ( is_string($transact) ||  $db->inTransaction()) {
                if (!is_string($transact)) {
                    $transact = "`SWISSFACTORY_" . rand(1000,9999) . "`";
                }
                try {
                    $db->exec('SAVEPOINT $transact');
                    I2CE::raiseError("Set SAVEPOINT $transact");
                } catch ( PDOException $e ) {
                    I2CE::pdoError($e,"Cannot set savepoint $transact");
                    $transact = false;
                }
            } else {
                try {
                    $db->beginTransaction(); 
                } catch( PDOException $e ) {
                    I2CE::pdoError($e, "Unable to begin transaction.");
                    $transact = false;
                }
            }
        }       
        foreach ($vals['swissFactory']['values'] as $path=> $swissVals) {
            $swiss = $this->getSwiss($path);
            if (!$swiss instanceof I2CE_Swiss) {
                continue;
            }
            $t_sucess = $swiss->updateValues($swissVals);
            if (!$t_sucess) {
                I2CE::raiseError("Could not update swiss at $path for MD=" . $swiss->getStorage()->getPath(false));
            }
            $sucess &= $t_sucess;
        }
        if (!$sucess) {
            I2CE::raiseError("Desired update is not valid");
            if ($transact === true && $db->inTransaction())  {
                I2CE::getConfig()->clearCache();
                $db->rollback();
                I2CE::raiseError("Rolled back transaction");
            } else if (is_string($transact)) {
                I2CE::getConfig()->clearCache();
                try {
                    $db->exec("ROLLBACK TO SAVEPOINT $transact");
                    I2CE::raiseError("Rolled back to savepoint $transact");
                } catch( PDOException $e ) {
                    I2CE::pdoError($e,"Unable to rollback to $transact");
                }
            }
            return false;
        }
        if ($transact) {
            if ($trasact === true && $db->inTransaction()) { 
                if ( !$db->commit() ) {
                    return false;
                }
            } else { //save point 
                try {
                    $db->exec("RELEASE SAVEPOINT $transact");
                    I2CE::raiseError("Released savepoint $transact");
                } catch ( PDOException $e ) {
                    I2CE::pdoError($e,"Unable to release $transact");
                    return false;
                }
            }
        }
        return true;
        
    }



    protected $style;

    public function displayValues($contentNode,$path,$action, $transient_options=array()) {
        if (!$contentNode instanceof DOMNode) {
            I2CE::raiseError("No node to place content on");
            return false;
        }        
        if (!$this->page instanceof I2CE_Page) {
            I2CE::raiseError("Not a page");
            return false;
        }
        $swiss = $this->getSwiss($path);
        if (!$swiss instanceof I2CE_Swiss) {
            I2CE::raiseError("No swiss at " . print_r($path,true));
            return false;
        }
        if (!$swiss->initializeDisplay($action)) {
            I2CE::raiseError("Could not initDisplay for $action");
            return false;
        }
        $optionsNode = $this->template->getElementByName('swiss_options',0);
        if ($optionsNode instanceof DOMNode) {
            $swiss->displayOptions($optionsNode, $transient_options);
        }
        if ($action == 'edit') {
            $tag = 'form';
        } else {
            $tag = 'span';
        }
        $subContentNode = $this->template->appendFileByNode('swiss_factory_' . $action . '.html',$tag,$contentNode);
        if (!$subContentNode instanceof DOMNode) {
            I2CE::raiseError("No subcontent node");
            return false;
        }
        $swissContentNode = $this->template->getElementByName('swiss_' . $action . '_content', 0, $subContentNode);
        if (!$swissContentNode instanceof DOMNode) {
            I2CE::raiseError("No swisscontent node");
            return false;
        }
        if ($swiss->getPath() != '/') {
            $parent_link = $this->getURLRoot(). $this->getPath($swiss->getPath(),'..') . $swiss->getURLQueryString();
        } else {
            $parent_link ='';
        }
        $this->template->setDisplayDataImmediate('parent_config_link',$parent_link, $subContentNode);
        $this->template->setDisplayDataImmediate('swissFactory:path',$swiss->getPath(),$subContentNode);
        if (!$this->setupDisplay($swiss,$action, $subContentNode)) {
            I2CE::raiseError("Can't setup display");
            return false;
        }
        if (! $swiss->displayValues($swissContentNode,$transient_options,$action)) {
            return false;
        }
        if (!$swiss->postprocessDisplay($action)) {
            I2CE::raiseError("Can't postprocess display");
            return false;
        }
        return true;
    }
    
    protected function setupDisplay($swiss,$action,$contentNode) {
        if ($action !== 'edit') {
            return true;
        }
        if (count($this->stored_options) > 0) {
            $flat_options = array();
            I2CE_Util::flattenVariables($this->stored_options,$flat_options,false,true,'swissFactory:options');
            foreach ($flat_options as $k=>$v) {
                if (is_scalar($v)) {
                    $v = array($v);
                } else if (is_array($v)) {
                    $k .= '[]';
                } else {
                    continue;
                }
                foreach ($v as $vv) {
                    if (!is_scalar($vv)) {
                        continue;
                    }
                    $contentNode->appendChild(
                        $this->template->createElement(
                            'input',
                            array('type'=>'hidden','name'=>$k,'value'=>$vv))
                        );
                }
            }
        }
        $updateLink = $this->getURLRoot('update') .  $swiss->getPath() . $swiss->getURLQueryString();
        $this->template->setDisplayDataImmediate('swiss_form',$updateLink ,$contentNode);     
        $update = $this->template->getElementById('swiss_update_button',$contentNode);
        if (!$update instanceof DOMElement) {
            return false;            
        }
        $update->setAttribute('action', $updateLink);
        $this->page->addFormWorm('swiss_form');
        return true;
    }



    abstract public function getChildNames($swiss);



    /**********************************
     *                                *
     *   Wrapper for Iterator Interface           *
     *                                *
     *********************************/
    
    public function current($swiss) {
        $key = $this->key($swiss);
        if ($key === null) {
            return null;
        }
        return $swiss->getChild($key);
    }

    abstract public function key($swiss);
    
    abstract public function next($swiss);
    
    abstract  function rewind($swiss);
    
    abstract public function valid($swiss);


    /**********************************
     *                                *
     *   Wrapper for CountableInterface           *
     *                                *
     *********************************/
    abstract public function count($swiss);




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
