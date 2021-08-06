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
*  I2CE_Swiss
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


abstract class I2CE_Swiss extends I2CE_Fuzzy implements Iterator, Countable {

    /**
     * Get the storage for this class
     * @retursn I2CE_MaficDataNode
     */
    public function getStorage() {
        return $this->storage;
    }

    public function getKeys() {
        if ($this->storage->is_scalar()) {
            return array();
        } else {
            return $this->storage->getKeys();
        }
    }

    public function getFactory() {
        return $this->factory;
    }

    public function getPage() {
        return $this->factory->getPage();
    }



    /**
     * Constructor
     * @param I2CE_MagicDataNode the storage for this swiss 
     */
    public function __construct($storage, $factory, $name=null,$parent = null) {
        $this->parent = $parent;
        if ($this->parent instanceof I2CE_Swiss) {
            $parentPath = $this->parent->getPath();
            if ($parentPath == '/') {
                $this->path =  '/' . $name;
            } else {
                $this->path = $parentPath . '/' . $name;
            }
        } else {
            $this->path = '/';
        }
        if (!$storage instanceof I2CE_MagicDataNode) {
            throw new Exception("No magic data storage for " . $this->path);
        }
        $this->storage = $storage;
        $this->children = array();
        $this->factory = $factory;
        $this->name = $name;
    }
    protected $name;
    protected $path;
    protected $parent;
    protected $page;
    protected $template;

    public function getAjaxJSNodes() {
        return 'swiss,stub_events,message_box_notice';
    }


    public function initializeDisplay($action) {
        $mod_factory = I2CE_ModuleFactory::instance();
        if ($mod_factory->isEnabled('stub') && $this->template->hasAjax()) {            
            //if we are doing an ajax.  make sure any possible required things are loaded
            $this->template->addHeaderLink('mootools-core.js');
            $this->template->addHeaderLink('select_update.js');
            $this->template->addHeaderLink('stubs.js');
            $this->template->addHeaderLink('stub.css');
            $this->template->addHeaderLink('messageBox.js');
            $this->template->addHeaderLink('messageBox.css');
        }
        return true;
    }

    public function postprocessDisplay($action) {        
        return true;
    }

    public function setPage($page) {
        $this->page = $page;
        $this->template = null;
        if ($this->page instanceof I2CE_Page) {
            $this->template = $this->page->getTemplate();
        }
    }

    public function getParent() {
        return $this->parent;
    }


    public function hasParent() {
        return ($this->parent instanceof I2CE_Swiss);
    }

    protected $children;

    public function getChild($child, $create_if_not_exists = false) {
        if (!array_key_exists($child,$this->children)) {
            try {
                $swissChild =  $this->_getChild($child, $create_if_not_exists);
            }  catch (Exception $e) {
                I2CE::raiseError("Could not create swiss $child at {$this->path}:" . $e->getMessage());
                return null;
            }
            if ( !$swissChild instanceof I2CE_Swiss ) {
                //I2CE::raiseError( "Could not get child: $child" );
                return null;
            }
            $this->children[$child] = $swissChild;
        }
        return $this->children[$child];
    }


    protected function getChildType($child) {
        return $this->factory->getChildType($this,$child);
    }
        
    /**
     *Should not be called outside of getChild()
     */
    protected function _getChild($child, $create_if_not_exists) {
        $childStorage = $this->factory->getChildStorage($this,$child,false);
        if (!$childStorage instanceof I2CE_MagicDataNode) {
            if (!$create_if_not_exists) {
                return null;
            }
            //we need to create it.
            $childStorage = $this->factory->getChildStorage($this,$child,true);
            if (!$childStorage instanceof I2CE_MagicDataNode) {
                I2CE::raiseError("Could not create MDN child $child");
                return null;
            }
        }
        $childType = $this->getChildType($child);
        if ($childType) {
            $childClass = 'I2CE_Swiss_' . $childType;
        } else {
            $childClass = 'I2CE_Swiss_Default';
        }
        if (!class_exists($childClass)) {
            I2CE::raiseError($childClass . " not found");
            return null;
        }
        try {
            $swisschild = new $childClass($childStorage, $this->factory, $child,$this);
        } catch (Exception $e) {
            I2CE::raiseError($e->getMessage());
        }
        if (!$swisschild instanceof I2CE_Swiss) {
            I2CE::raiseError( "swisschild isn't I2CE_Swiss!" );
            return null;
        }
        $swisschild->setPage($this->page);
        return $swisschild;
    }




    public function getPath($leading_slash = true) {
        if ($leading_slash) {
            return $this->path;
        } else {
            return substr($this->path,1);
        }
    }

    /**
     * Check to see if a magic data subnode $child should be created if it does not exist
     * @param string $child
     * @return boolean
     */
    protected function createIfNotExists($child) {
        return false;
    }

    

    public function display($contentNode,$transient_options) {
        if (!$contentNode instanceof DOMNode) {
            return false;
        }
        $this->displayValues($contentNode,$transient_options);
    }



    /**
     * Display the configuration menu for the specified config node
     * @param DOMNode $contentNode null.  All the  swiss should display all content relative to that node
     * @param array $transient_options
     * @param string $action
     * @returns boolean true on sucess
     */
    abstract protected function displayValues($contentNode,$transient_options, $action);


    protected function validateValues($vals) {
        return true;
    }


    public function processValues($vals) {
        return true;
    }

    /**
     * Update  values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @param string $linkBase The base page link used to export the magic data to a magicdatatemplate for validation.  Should be a page which
     * which subclasses I2CE_Page_MagicDataExport. 
     * @param integer $pipe The pipe length used for the magic data export.  Defaults to -1
     * @param boolean $transact.  Defaults to true -- all the SQL statements of this save should be wrapped in a transaction if possible.
     * On failure we rollback the transaction and clear the apc cache for the magic data
     * @returns  true on sucess
     */
    public function updateValues($vals) {
        //now  the values that we process from the (presumably)  page submission.
        if (!$this->validateValues($vals)) {
            return false;
        }
        if (!$this->processValues($vals)) {
            return false;
        }        
        return true;
    }


    /**
     * Tries to turn a string (such as a magic data key) into  human text
     * @param $text
     * @returns string
     */
    public static function humanText($text){
        return I2CE_SwissFactory::humanText($text);
    }


    
    public  function getDescription() {
        return $this->factory->getDescription($this);
    }


    public function getDisplayName() {
        return $this->factory->getDisplayName($this);
    }


    public function getName() {
        return $this->name;
    }
    

    public function getAttribute($attr) {
        return $this->factory->getAttribute($this,$attr);
    }

    public function hasAttribute($attr) {
        return $this->factory->hasAttribute($this,$attr);
    }


    public function getStatus($key = null) {
        $status =  $this->factory->getStatus($this);
        if ($key === null) {
            return $status;
        } else {
            if (array_key_exists($key,$status)) {
                return $status[$key];
            } else {
                return null;
            }
        }
    }

    public function displayOptions($optionsNode,$transient_options) {
        
    }

    public function getURLRoot($action = null) {
        return $this->factory->getURLRoot($action);
    }

    public function getURLQueryString($additional = array(),$remove=array()) {
        $openName = 'openedLinks' . strtr($this->path, '/',':');
        $openedSub = $this->factory->getStoredOptions($openName);
        $remove[] = 'openedLinks';   
        if (is_array($openedSub)) {
            if (array_key_exists('/open',$openedSub)) {
                unset($openedSub['/open']);
            }
            $keys = $this->factory->getPathComponents($this->path);
            if (count($keys) > 0) {
                array_unshift($keys, 'openedLinks');
                $addOpen = &$additional;
                foreach ($keys as $key) {
                    if (! is_array($addOpen)) {
                        $addOpen = array();
                    }
                    $addOpen = &$addOpen[$key];
                }
                $addOpen = $openedSub;
            }
        }
        return $this->factory->getURLQueryString($additional,$remove);
    }
    


    public function getChildNames() {
        return $this->factory->getChildNames($this);
    }

    public function prefixName($name, $prefix='swissFactory:values') {
        return str_replace('.','%2F',$prefix . ':' . $this->path . ':' . $name);
    }

    public function renameInputs($names,$node, $name_prefix ='', $name_postfix = '', $also_id = false) {
        $ret = array();
        if ($names === '*') {
            $name_str =
                $qry = './descendant-or-self::*[("input" or "select" or "textarea")   and @name]';
        } else {
            if (is_scalar($names)) {
                $names = array($names);
            }
            if (!is_array($names)) {
                return $ret;
            }
            if (count($names) == 0) {
                return $ret;
            }
            $qry = './descendant-or-self::*[' .
                ' ("input" or "select" or "textarea")   and ' .
                '(@name="' . implode( '" or @name="',$names) . '")'
                . ']';
        }
        $results = $this->template->query($qry,$node);
        if (!$results instanceof DOMNodeList) {
            return $ret;
        }
        if (is_array($name_prefix)) {
            $name_prefix = implode(':',$name_prefix);
        }
        if (!is_scalar($name_prefix)) {
            $name_prefix = false;
        } else {
            $name_prefix = '' . $name_prefix;
        }
        if ($name_prefix && strlen($name_prefix) > 0 && $name_prefix[strlen($name_prefix)-1] != ':') {
            $name_prefix .= ':';
        } 
        if (is_array($name_postfix)) {
            $name_postfix = implode(':',$name_postfix);
        }
        if (!is_scalar($name_postfix)) {
            $name_postfix = false;
        } else {
            $name_postfix = '' . $name_postfix;
        }
        if ($name_postfix && strlen($name_postfix) > 0 && $name_postfix[0] != ':') {
            $name_postfix = ':' . $name_postfix;
        }
        for ($i=0; $i < $results->length; $i++) {
            $input = $results->item($i);
            $old_name = $input->getAttribute('name');
            $new_name = $this->prefixName($name_prefix . $old_name . $name_postfix);
            $ret[$old_name] = $new_name;
            if ($input->hasAttribute('type') && $input->getAttribute('type') == 'file') {
                $hidden_name = $this->prefixName($name_prefix . '__files_' . $old_name . $name_postfix);
                $fileInput = $this->template->createElement('input',array('type'=>'hidden','name'=>$hidden_name,'value'=>$new_name));
                $this->template->appendNode($fileInput,$input->parentNode);                                                            
            }
            $input->setAttribute('name',$new_name);
            if ($also_id) {
                $input->setAttribute('id',$new_name);
            }
        }
        return $ret;
    }


    public function addLink( $source_id, $target_id, $node  , $action, $transient_options = array()  ) {
        return $this->addAjaxLink(false,$source_id,$target_id,$node,$action,$transient_options);
    }

    public function addAjaxLink($link_name, $source_id, $target_id , $node  , $action, $transient_options = array()  ) {
        if (!$this->template instanceof I2CE_Template) {
            return false;
        }
        $mod_factory = I2CE_ModuleFactory::instance();
        $this->template->setDisplayDataImmediate('description',$this->getDescription(), $node);
        $this->template->setDisplayDataImmediate('displayName',$this->getDisplayName(), $node);
        $this->template->reIdNodes($target_id, $target_id . ':' . $this->path, $node);
        $comps = $this->factory->getPathComponents($this->path);
        if ($link_name === false) {
            $opened = true;
        } else {
            $openName = 'openedLinks:' . implode(':', $comps) . ":/open";
            $opened = $this->factory->getStoredOptions($openName);
        }
        $open = false;
        if ($opened) {
            $targetNode = $this->template->getElementById($target_id . ':' . $this->path, $node);
            if ($targetNode instanceof DOMElement) {
                $open = true;
                $contentNode = $this->template->createElement('span');
                //$node->appendChild($contentNode);
                //we actually display this  swiss;
                $this->displayValues($contentNode,$transient_options, $action);        
                $s_contentNode = $this->template->getElementById($source_id, $contentNode);
                if ($s_contentNode instanceof DOMNode) {
                    $s_contentNode->setAttribute('id' , $s_contentNode->getAttribute('id') . ':' . $this->path);
                    $targetNode->appendChild($s_contentNode);                               
                }
            }
        }
        if ($link_name == false) {
            return true;
        }
        $link = $this->getURLRoot($action)  .  $this->path .$this->getURLQueryString();
        $this->template->setDisplayDataImmediate($link_name,$link,$node);
        $inputNode = $this->template->getElementById($link_name,$node);
        if ($inputNode instanceof DOMElement && $inputNode->tagName == 'a') {            
            I2CE_DisplayData::processDisplayValue($this->template,$inputNode,$link);
        }
        if (!$mod_factory->isEnabled('stub') || !$this->template->hasAjax() ) {
            return true;
        }
        $this->template->reIdNodes($link_name, $link_name . ':' . $this->path, $node);
        $this->template->addAnchorIdByName($link_name, $link_name . ':' . $this->path, $node);
        $this->template->addAjaxToggle(
            $target_id . ':'. $this->path , 
            $link_name . ':' . $this->path , 
            'click', 
            $link, 
            $source_id , 
            $this->getAjaxJSNodes(), '', false, $open);
       $js = "formworms['swiss_form'].scanForSubmits('{$target_id}:{$this->path}'); " 
           .  "var input = $$('input[name=swissFactory:options:$openName]'); "
           . "if (input && input.length > 0) {  input[0].setProperty('value',1);} "
           . "else {  new Element('input',{type:'hidden',name:'swissFactory:options:$openName', value: 1}).inject('swiss_form');}";
       $this->template->addAjaxCompleteFunction(  $link_name . ':' . $this->path, $js);
        $js = "formworms['swiss_form'].scanForSubmits('{$target_id}:{$this->path}'); " 
            . "var input = $$('input[name=swissFactory:options:$openName]'); "
            . "if (input && input.length > 0) {  input[0].setProperty('value',0);} "
            . "else { new Element('input',{type:'hidden',name:'swissFactory:options:$openName', value: 0}).inject('swiss_form');}";
       $this->template->addAjaxToggleOffFunction($link_name . ':' . $this->path,$js);
        if ($inputNode instanceof DOMElement && $inputNode->hasAttribute('toggle_button_show') && $inputNode->hasAttribute('toggle_button_hide')) {
            $js_off = "var button=\$('{$link_name}:{$this->path}');"
                ." if(button){"
                .  "button.removeClass('"  . addslashes($inputNode->getAttribute('toggle_button_show')) . "');"
                .  "button.addClass('"  . addslashes($inputNode->getAttribute('toggle_button_hide')) . "');"
                .'}';
            $js_on = "var button=\$('{$link_name}:{$this->path}');"
                ."if(button){"
                .  "button.removeClass('"  . addslashes($inputNode->getAttribute('toggle_button_hide')) . "');"
                .  "button.addClass('"  . addslashes($inputNode->getAttribute('toggle_button_show')) . "');"
                .'}';
            $inputNode->removeAttribute('toggle_button_show');
            $inputNode->removeAttribute('toggle_button_hide');
            $this->template->addAjaxToggleOffFunction($link_name . ':' . $this->path,$js_off);
            $this->template->addAjaxToggleOnFunction($link_name . ':' . $this->path,$js_on);
        }
        return true;
    }

    protected function addOptionMenu($input_id,  $contentNode) {
        return $this->addAjaxOptionMenu($input_id,false,$contentNode);
    }


    protected function addAjaxOptionMenu($input_id, $replace_container_id, $contentNode) {
        $mod_factory = I2CE_ModuleFactory::instance();
        $optionsMenu = $this->template->getElementById($input_id . '_options_menu',$contentNode);
        if (!$optionsMenu instanceof DOMNode) {
            I2CE::raiseError("Could not get the option menu for $input_id");
            return false;
        }
        $this->renameInputs('*' ,$optionsMenu);        
        $addButton = $this->template->getElementById($input_id, $contentNode);
        $link = $this->getURLRoot('update')  .  $this->path .$this->factory->getURLQueryString(array(),array('openedLinks'));                        
        if ($replace_container_id !== false) {
            if ($mod_factory->isEnabled('stub')  && $this->template->hasAjax()) {
                if ($addButton instanceof DOMElement) {
                    if (strpos($link,'?') !== false) {
                        $link .= '&';
                    }else {
                        $link .= '?';
                    }                
                    $link .= 'noRedirect=1';
                    $link = 'stub/id?request=' . urlencode($link) .  '&content=' . urlencode($replace_container_id . ':' . $this->path ). '&keep_javascripts=' . $this->getAjaxJSNodes();
                    if (!$this->page->rewrittenURLS()) {
                        $link = 'index.php/' . $link;
                    }
                    $addButton->setAttribute('ajaxTargetID',$replace_container_id . ':' .  $this->path);
                    $addButton->setAttribute('action',$link);
                    $optionsMenu->appendChild(
                        $this->template->createElement(
                            'input',
                            array('type'=>'hidden','name'=>'swiss_path','value'=>$this->path)
                            ));
                }
            }
            $this->template->reIDNodes($replace_container_id, $replace_container_id . ':' . $this->path, $contentNode);
        }
        $pf_input_id = $this->prefixName($input_id);
        $this->template->reIDNodes($input_id,$pf_input_id,$contentNode);
        $this->template->reIDNodes($input_id . '_options_menu',$pf_input_id . '_options_menu',$contentNode);
        $this->changeClassOnNodes($input_id . '_options_hide',$pf_input_id . '_options_hide',$contentNode);
        $this->changeClassOnNodes($input_id . '_options_show',$pf_input_id . '_options_show',$contentNode);
        $this->changeClassOnNodes($input_id . '_options_toggle',$pf_input_id . '_options_toggle',$contentNode);

        return true;
    }


    protected function changeClassOnNodes($old,$new,$node) {
        $qry = 'descendant-or-self::*[@class]';
        $results = $this->template->query($qry,$node);
        for ($i=0; $i < $results->length; $i++) {
            $class = $results->item($i)->getAttribute('class');
            $class = preg_replace('/(\s+|^)' . $old . '(\s+|$)/','$1'  . $new . '$2' ,  $class);
            $results->item($i)->setAttribute('class',$class);
        }
    }





    public function hasField($field) {
        if (!I2CE_MagicDataNode::checkKey($field)) {
            return false;
        }
        return $this->storage->is_scalar($field);
    }


    public function setField($field,$value) {
        if (!I2CE_MagicDataNode::checkKey($field)) {
            I2CE::raiseError("Bad field name $field");
            return false;
        }
        if (!is_scalar($value)) {
            return false;
        }
        $value = (string)$value;  
        if ($this->storage->is_parent($field)) {
            I2CE::raiseError("Trying to set a parent to a scalar value at " . $this->storage->getPath(false) . "/"  . $field);
            return false;
        }
        return $this->storage->traverse($field,true,false)->setValue($value);    
    }

    public function getField($field) {
        if (!I2CE_MagicDataNode::checkKey($field)) {
            I2CE::raiseError("Bad field name $field");
            return null;
        }
        if ($this->storage->is_scalar($field)) {
            if ($this->storage->$field) {
                return $this->storage->$field;
            }
            if ($this->storage->is_translatable($field)) {
                $mdn = $this->storage->traverse($field,false,false);
                if ($mdn instanceof I2CE_MagicDataNode) {
                    //check to see if this field was set in another locale and use this.
                    foreach ($mdn->getTranslations() as $trans) {
                        if ($trans) {
                            return $trans;
                        }
                    }
                }
            }
        }
        return null;
    }


    public function setTranslatableField($field,$value) {
        if (!I2CE_MagicDataNode::checkKey($field)) {
            I2CE::raiseError("Bad field name $field");
            return false;
        }
        $locale = $this->getLocale();
        if ($this->storage->is_parent($field)) {
            I2CE::raiseError("Trying to set a parent to a scalar value at " . $this->storage->getPath(false) . "/"  . $field);
            return false;
        }
        $fieldStorage = $this->storage->traverse($field,true,false);
        $fieldStorage->setTranslatable();
        if ($fieldStorage->setTranslation($this->getLocale(),$value)) {
            return true;
        } else {
            I2CE::raiseError("Could not set " . $fieldStorage->getPath(false) . " as translatable with value $value at locale " . $this->getLocale());
            return false;
        }
    }


    public function getLocale() {
        $locale =  $this->factory->getStoredOptions('locale');
        if (!$locale) {
            return I2CE_Locales::getPreferredLocale();
        }  else {
            return $locale;
        }
    }


    /**********************************
     *                                *
     *   Iterator Interface           *
     *                                *
     *********************************/
    
    public function current() {        
        return $this->factory->current($this);
    }

    public function key() {
        return $this->factory->key($this);
    }
    
    public function next() {
        return $this->factory->next($this);
    }
    public function rewind() {
        return $this->factory->rewind($this);
    }
    public function valid() {
        return $this->factory->valid($this);
    }

    /**********************************
     *                                *
     *   CountableInterface           *
     *                                *
     *********************************/
    public function count() {
        return $this->factory->count($this);
    }


 

  }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
