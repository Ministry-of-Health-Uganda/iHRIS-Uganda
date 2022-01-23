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
*  I2CE_Swiss_Default
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_Default extends I2CE_Swiss_Default_Base{



    public function displayValues($contentNode,$transient_options,$action) {
        $this->template->addHeaderLink('swiss_default.css');
        $showExpert =  $this->factory->getStoredOptions('expert');
        $node = $this->template->appendFileByNode("configuration_main.html",'div',$contentNode);
        $displayName = $this->storage->getAttribute('displayName');
        if (!$displayName) { //the display name has not been set.  set it to the name of this configuration(Group) node
            $displayName = self::humanText($this->storage->getName());
        }
        $this->template->setDisplayDataImmediate('displayName',$this->getDisplayName(),$node);
        $this->template->setDisplayDataImmediate('description',$this->getDescription(),$node);        


        $configurationListNode = $this->template->getElementById('configuration_options',$node);
        if (!$configurationListNode instanceof DOMNode) {
            I2CE::raiseError ("No place to add scalars");
            return false;
        }
        $configurationGroupListNode = $this->template->getElementById('configurationGroup_options',$node);
        if (!$configurationGroupListNode instanceof DOMNode) {
            I2CE::raiseError ("No place to add parents");
            return false;
        }
        if ($this->path == '/') {
            $path = '';
        } else {
            $path = $this->path;
        }            
        $added = false;
        $children = $this->getChildNames();
        if (!array_key_exists('leaf_as_link',$transient_options)) {
            $mod_factory = I2CE_ModuleFactory::instance();
            $transient_options['leaf_as_link'] =  ($mod_factory->isEnabled('stub') && $this->template->hasAjax());
        }
        foreach ($children as $child) {
            $swissChild = $this->factory->getSwiss($this->path,$child);
            if (!$swissChild instanceof I2CE_Swiss) {
                continue;
            }
            $childStorage = $swissChild->getStorage();
            if ($childStorage->is_indeterminate()) {
                continue;
            }
            if ($swissChild->getStatus('visible') === false) {
                continue;
            }
            if ( (!$showExpert) && ($swissChild->getStatus('advanced') !== false)) {
                continue;
            }            
            if ( ($swissChild instanceof I2CE_Swiss_Default_Leaf )
                && ! (array_key_exists('leaf_as_link',$transient_options) && $transient_options['leaf_as_link'])) {
                if (!$swissChild->displayValue($configurationListNode,$transient_options, $action)) {
                    continue;
                }
                $added = true;
            } else  {
                $gNode = $this->template->appendFileByNode("configurationGroup_default.html","li",$configurationGroupListNode);
                if (!$gNode instanceof DOMNode) {
                    I2CE::raiseError("Cannot append configurationGroup_default.html");
                    continue;
                }
                $swissChild->addAjaxLink('configurationGroup_link','configuration_main_contents','configurationGroup_link_ajax',$gNode,$action);
                $added = true;
            }
        }
        if (!$added && $node instanceof DOMNode) {
            $this->template->removeNode($node);
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
