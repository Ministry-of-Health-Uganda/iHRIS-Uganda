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
*  I2CE_SwissConfigFactory
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_SwissConfigFactory extends I2CE_SwissFactory {


    protected function setupNode($path) {
        $pathComponents = $this->getPathComponents($path);            
        $node = $this->configNodes['/'];
        $status = $this->statii['/'];
        $t_path = '';
        foreach ($pathComponents as $i=>$comp) {
            $t_path .= '/' . $comp;
            if (array_key_exists($t_path,$this->configNodes)) {
                $node = $this->configNodes[$t_path];
                $status = $this->statii[$t_path];
            } else {
                if ($node instanceof DOMNode) {
                    $node = $this->getChildConfigNode($node,$comp);
                    $status =  $this->configTemplate->processStatus($node,$status);
                }
                $this->configNodes[$t_path] = $node;
                $this->statii[$t_path] = $status;
            }
        }
    }

    
        
    public function getChildNames($swiss) {
        $children = array();
        $childNodes = $this->getChildConfigNodes($swiss);
        if (!$childNodes instanceof DOMNodeList) {
            return $children;
        }
        for ($i=0; $i < $childNodes->length; $i++) {
            $children[] = $childNodes->item($i)->getAttribute('name');
        }
        return $children;
    }



    /**
     *@var protected DOMNode $configNode
     */
    protected $configNodes;
    protected $statii;    
    public function getConfigNode($swiss) {        
        $path = $swiss->getPath();
        if (!array_key_exists($path,$this->configNodes)) {            
            $this->setupNode($path);
        }
        return $this->configNodes[$path];
    }





    protected function getChildConfigNode($node,$child) {
        $qry = "./configurationGroup[@name='$child']|./configuration[@name='$child']";
        $nodes = $this->xpath->query($qry,$node);        
        $ret = null;
        if ($nodes instanceof DOMNodeList) {
            if ($nodes->length > 1) {
                I2CE::raiseError("Child $child is ambigous");
            } else if ($nodes->length == 1) {
                $ret = $nodes->item(0);        
            }
        }
        return $ret;
    }

    protected $module;
    /**
     * construct a swiss swiss factory and create it if it doesn't exist.
     * @param I2CE_MagicDataNode $storage.  The root of the magic data we will be operating on
     * @param string  $swissName.  The classname for the root swiss object.
     * @throws Exception
     */
    public function __construct( $page, $options=array()) {
        $this->child_node_cache= array();
        $this->child_node_cache_index= array();
        parent::__construct($page);
        if (!array_key_exists('module',$options) || !$options['module']) {
            throw new Exception ("No modules specified");
        }
        $this->module = $options['module'];
        $config_file = null;
        if (!I2CE::getConfig()->setIfIsSet($config_file,"/config/data/{$options['module']}/file")) {
            throw new Exception ("No magic data template found for {$options['module']}");
        }
        $this->configTemplate = new I2CE_MagicDataTemplate();
        if (!$this->configTemplate->loadRootFile($config_file)) {
            throw new Exception ("Invalid magic data template found for {$options['module']}");
        }
        $this->xpath = new DOMXPath($this->configTemplate->getDoc());
        $this->configNodes = array();
        $nodes = $this->xpath->query('/I2CEConfiguration/configurationGroup');
        if ($nodes->length != 1) {
            throw new Exception("No or invalid configuration data for {$options['module']}");
        }      
        $this->configNodes['/'] =  $nodes->item(0);
        $this->statii['/'] = $this->configTemplate->getDefaultStatus();
        if (!array_key_exists('version',$this->statii['/'])) {
            if (array_key_exists('version',$options)) {
                $this->statii['/']['version'] = $options['version'];
            } else {
                $this->statii['/']['version'] = 0;
            }
        }
        $file_search = new I2CE_FileSearch();
        $mod_factory = I2CE_ModuleFactory::instance();
        $mod_factory->loadPaths($this->module,'CONFIGS',true,$file_search);
        $translated=$file_search->search('CONFIGS',$config_file,true);
        $translated_locales=$file_search->getLocaleOfLastSearch();
        $preferred_locales = I2CE_Locales::getPreferredLocales();
        $this->translatedConfigs = array();
        foreach ($preferred_locales as $locale) {
            if ( ($index = array_search($locale,$translated_locales)) === false) {
                continue;
            }
            $trans_file = $translated[$index];
            $trans_template = new I2CE_MagicDataTemplate();
            if (!$trans_template->loadRootFile($trans_file)) {
                continue;
            }
            $this->translatedConfigs[$locale] = $trans_template;
        }
    }

    protected $translatedConfigs;
    
    protected function getRootType() {
        if ( $this->configNodes['/']->hasAttribute('config')) {
            return $this->configNodes['/']->getAttribute('config');
        } else {
            return parent::getRootType();
        }
    }


    protected function getRootStorage() {       
        $module = $this->configNodes['/']->getAttribute('name');
        if ($module == 'I2CE') {
            $storage = I2CE::getConfig();
        } else {
            //this may not be there, but that's OK if there is an absolute path set.  we will check
            //for that below
            $storage = I2CE::getConfig()->traverse('modules/' . $module ,false ,false);
        }
        $path = $this->configNodes['/']->getAttribute('path');
        if (is_string($path) && strlen($path) > 0) {
            if ($path[0] == '/') {
                $storage = I2CE::getConfig()->traverse($path);
            } else {
                if ($storage instanceof I2CE_MagicDataNode) {
                    $storage = $storage->traverse($path,true,false); //maybe a path that doesn't exist, but it should?
                }
            }
        }
        return $storage;        
    }


 
    public function getChildStorage($swiss,$child = null, $create = false) {
        $storage = $swiss->getStorage();
        if (!$storage instanceof I2CE_MagicDataNode) {
            if ($child === null) {
                return array();
            } else {
                return null;
            }
        }
        $childNodes = $this->getChildConfigNodes($swiss,$child);
        if ($child !== null) {
            if (!$childNodes instanceof DOMNode) {
                return null;
            }
            if ($childNodes->hasAttribute('path')) {
                return $storage->traverse($childNodes->getAttribute('path'),$create,false);
            } else {
                return $storage->traverse($child,$create,false);
            }
        } else {
            $children = array();
            for ($i=0; $i < $childNodes->length; $i++) {
                $childNode = $childNodes->item($i);
                $name = $childNode->getAttribute('name');
                if ($childNode->hasAttribute('path')) {
                    $childStorage =  $storage->traverse($childNode->getAttribute('path'),true,false);
                } else {
                    $childStorage =  $storage->traverse($child,true,false);
                }
                if (!$childStorage instanceof I2CE_MagicDataNode) {
                    I2CE::raiseError("Could not get magic data for $name");
                    continue;
                }
                $children[$name] = $childStorage;
            }
            return $children;
        }        
    }

    public function getURLRoot($action = null) {
        if ($action === null) {
            $action = $this->page->page();
        }
        return  $this->page->module() . '/' . $action . '/' . $this->module ;
    }



    protected function getChildConfigNodes($swiss,$child=null) {
        $node = $this->getConfigNode($swiss);
        if (!$node instanceof DOMNode) {
            return null;
        }
        if ($child !== null) {
            return $this->getChildConfigNode($node,$child);
        } else {
            $qry = "./configuration|./configurationGroup";
            return $this->xpath->query($qry,$node);
        }
    }



    public function getChildType($swiss,$child) {
        $childNode = $this->getChildConfigNodes($swiss,$child);
        if ($childNode instanceof DOMElement && $childNode->hasAttribute('config')) {
            return $childNode->getAttribute('config');
        }
        if ($childNode  instanceof DOMElement && $childNode->tagName == 'configuration') {
            return 'Default_Leaf';
        }
        return null;
    }


    protected function getTranslatedContentAtPath($tag,$path) {
        $qry = '/I2CeConfiguration/configurationGroup';
        $comps = $this->getPathComponents($path);
        if (count($comps) > 0) {
            $last = array_pop($comps);
            foreach ($comps as $comp) {
                $qry .= "/configurationGroup[@name='$comp']";
            }
            $qry = $qry . "/configurationGroup[@name='$last']/$tag | " . $qry .  "/configuration[@name='$last']/$tag";
        } else {
            $qry .= "/$tag";
        }
        foreach ($this->translatedConfigs as $template) {
            $results = $template->query($qry);
            if ($results->length == 1) {
                return $results->item(0)->textContent;
            }
        }
        return false;
    }


    public function getDescription($swiss) {
        if ( ($content = $this->getTranslatedContentAtPath('description',$swiss->getPath())) !== false) {
            return $content;
        }
        $node = $this->getConfigNode($swiss);
        if ($node instanceof DOMElement) {
            $results = $this->xpath->query('./description',$node);
            if ($results->length == 1) {
                return $results->item(0)->textContent;
            }
        }
        return parent::getDescription($swiss);
    }

    public function getDisplayName($swiss) {
        if ( ($content = $this->getTranslatedContentAtPath('displayName',$swiss->getPath())) !== false) {
            return $content;
        }
        $node = $this->getConfigNode($swiss);
        if ($node instanceof DOMElement) {
            $results = $this->xpath->query('./displayName',$node);
            if ($results->length == 1) {
                return $results->item(0)->textContent;
            }
        }
        return parent::getDisplayName($swiss);
    }


    public function getAttribute($swiss,$attr) {
        $node = $this->getConfigNode($swiss); 
        if ($node instanceof DOMElement && $node->hasAttribute($attr)) {
            return $node->getAttribute($attr);
        }
        return parent::getAttribute($swiss,$attr);
    }
    public function hasAttribute($swiss,$attr) {
        $node = $this->getConfigNode($swiss); 
        if ($node instanceof DOMElement && $node->hasAttribute($attr)) {
            return true;
        }
        return parent::hasAttribute($swiss,$attr);
    }


    
    public function getStatus($swiss) {
        $path = $swiss->getPath();
        if (!array_key_exists($path,$this->configNodes)) {            
            $this->setupNode($path);
        }
        return  $this->statii[$path];
    }



    public function displayValues($contentNode,$path,$action, $transient_options=array()) {
        $this->template->setAttribute( "class", "active", "menuConfigure", "a[@href='configure']" );
        $this->template->appendFileById( "menu_configure.html", "ul", "menuConfigure" );
        $this->template->setAttribute( "class", "active", "menuConfigureModules", "a[@href='" . $this->page->module() . '/' . $this->page->page() . "']" );
        return parent::displayValues($contentNode,$path,$action,$transient_options);
    }






    /**********************************
     *                                *
     *   Wrapper for Iterator Interface           *
     *                                *
     *********************************/

    protected $child_node_cache;
    protected $child_node_cache_index;


    


    protected function ensureIterator($swiss) {
        $configNode = $this->getConfigNode($swiss);
        if (!$configNode instanceof DOMElement || $configNode->tagName == 'configuration') {
            return false;
        }
        $path = $swiss->getPath();
        if (!array_key_exists($path,$this->child_node_cache) || !$this->child_node_cache[$path] instanceof DOMNodeList) {
            $this->child_node_cache[$path] = $this->getChildConfigNodes($swiss);
        }
        if (!array_key_exists($path,$this->child_node_cache_index)) {
            $this->child_node_index[$path] = 0;
        }
        return  ($this->child_node_cache[$path] instanceof DOMNodeList);
    }
    


    public function key($swiss) {
        if (!$this->ensureIterator($swiss)) {
            return null;
        }
        $path = $swiss->getPath();
        if ($this->child_node_index[$path] < $this->child_node_cache[$path]->length) {
            return $this->child_node_cache[$path]->item($this->child_node_index[$path])->getAttribute('name');
        } else {
            return null;
        }
    }
    
    public function next($swiss) {
        if (!$this->ensureIterator($swiss)) {
            return null;
        }
        $this->child_node_index[$swiss->getPath()]++;
    }
    public function rewind($swiss) {
        if (!$this->ensureIterator($swiss)) {
            return null;
        }
        $this->child_node_index[$swiss->getPath()] = 0;
    }
    public function valid($swiss) {
        $configNode = $this->getConfigNode($swiss);
        if (!$configNode instanceof DOMElement || $configNode->tagName == 'configuration') {
            return false;
        }
        if (!$this->ensureIterator($swiss)) {
            return false;
        }
        return  ($this->child_node_index[$path] < $this->child_node_cache[$path]->length);
    }

    /**********************************
     *                                *
     *   Wrapper for CountableInterface           *
     *                                *
     *********************************/
    public function count($swiss) {
        if (!$this->ensureIterator($swiss)) {
            return 0;
        }    
        return $this->child_node_cache[$swiss->getPath]->length;        
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
