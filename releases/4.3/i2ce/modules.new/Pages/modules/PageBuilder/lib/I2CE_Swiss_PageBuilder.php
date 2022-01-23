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
*  I2CE_SwissConfig_FormRelationship_Joins
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_PageBuilder extends I2CE_Swiss {



    protected function getHandlers() {
        return I2CE::getConfig()->getAsArray('/modules/page-builder/handlers');
    }

    public function getChildNames() {
        return array_keys($this->getPages());
    }

    public function getPages() {
        //only allow to edit pages for which we have a handler for
        $existing_pages = $this->storage->getKeys();
        $pages = array();
        foreach ($existing_pages as $page) {
            $class = false;
            if (! ($this->storage->setIfIsSet($class,"$page/class"))
                || !$this->getChildType($page)) {
                continue;
            }
            $pages[$page] = $class;
        }
        return $pages;
    }


    protected function getChildType($child) {
        $class = false;
        if (! ($this->storage->setIfIsSet($class,"$child/class"))
            || ! ($class)
            || ! class_exists($class)
            || ! ($class == 'I2CE_Page' || is_subclass_of($class,'I2CE_Page'))
            ){
            return false;
        }        
        $handlers =   $handlers = $this->getHandlers();
        while ($class) {
            //See if any swiss object has been registered to handle this class.
            if (array_key_exists($class,$handlers)
                && is_array($handlers[$class])
                && array_key_exists('swiss',$handlers[$class])
                && is_scalar($swiss =  $handlers[$class]['swiss'])
                && $swiss
                ) {
                return $swiss;
            }
            //nothing valid, try the parent class
            $class = get_parent_class($class);
        }
        return false;
    }



    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        $required_keys = array('class','name');
        foreach ($required_keys as $required_key) {
            if (!array_key_exists($required_key,$vals)
                || !is_string($vals[$required_key])
                || strlen($vals[$required_key]) == 0) {
                I2CE::raiseError('Missing required key: ' . $required_key);
                return false;
            }
        }
        $name = $vals['name'];
        $class = $vals['class'];
        if (! I2CE_MagicDataNode::checkKey($name)) {
            I2CE::raiseError("Invalid page name:" . $name);
            return false;
        }
        $existing_pages = $this->storage->getKeys();
        if (in_array($name,$existing_pages)) {
            I2CE::raiseError('Requested New Page:' . $name . ' already exists in ' . implode(',',$existing_pages));
            return false;
        }
        $handlers =         $handlers = $this->getHandlers();
        if (!array_key_exists($class,$handlers) 
            || ! $handlers[$class]) {
            I2CE::raiseError('No handler for the class: ' . $class . ' has been registered');
            return false;
        }
        I2CE::raiseError("Creating page named $name with class $class");
        $this->storage->$name = array('class'=>$class); //add the new page with its class into root magic data node 
        return true;
    }




    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('page_builder_menu.html','div',$content_node)) instanceof DOMNode) {
            return false;
        }
        if ( ($nameNode = $this->template->getElementByName('name',0,$mainNode)) instanceof DOMNode) {
            $all_pages = $this->storage->getKeys(); //these are all the pages, not just the ones with a registered handler
            $this->template->setClassValue($nameNode,'validate_data',array('notinlist'=>$all_pages), '%');
        }
        if ( ($classNode = $this->template->getElementByName('class',0,$mainNode)) instanceof DOMNode) {
            $handlers = $this->getHandlers();
            foreach ($handlers as $page_class => $h_data) {
                if (!is_array($h_data)
                    || !array_key_exists('swiss',$h_data)                        
                    || ! is_scalar($h_data['swiss'])
                    || ! $h_data['swiss']
                    ) {
                    continue;
                }
                $attrs = array('value'=>$page_class);
                if (array_key_exists('description',$h_data)
                    && $h_data['description']) {
                    $attrs['title'] = $h_data['description'];
                }
                $classNode->appendChild($this->template->createElement('option',$attrs,$page_class));
            }
        }
        $this->renameInputs(array('class','name'),$mainNode);        

        if (  ($append_node = $this->template->getElementById('pages',$mainNode)) instanceof DOMNode) {
            $pages = $this->getPages(); //these are the pages that have a registered handler 
            foreach ($pages as $page=>$class) {
                if (! ($swissChild = $this->getChild($page)) instanceof I2CE_Swiss_Page
                    || ! ($pageNode = $this->template->appendFileByNode( 'page_builder_menu_each.html','li',$append_node))
                    ) {
                    continue;
                }
                $this->template->setDisplayDataImmediate("page",$page ,$pageNode);
                $this->template->setDisplayData("class",$class ,$pageNode);
                $this->template->setDisplayDataImmediate("page_edit_link",$this->getURLRoot('edit') . '/' . $page,$pageNode);
				$delete_link = $swissChild->getURLRoot('delete') . $swissChild->path . $swissChild->getURLQueryString();
				$this->template->setDisplayDataImmediate("existing_page_delete_link", $delete_link, $pageNode);
            }
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
