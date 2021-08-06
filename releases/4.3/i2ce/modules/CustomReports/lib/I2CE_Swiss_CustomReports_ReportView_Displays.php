<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @package i2ce
* @subpackage customreports
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.6
* @since v4.0.6
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_CustomReports_ReportView_Displays
* 
* @access public
*/


class I2CE_Swiss_CustomReports_ReportView_Displays extends I2CE_Swiss_CustomReports_ReportView_Base{

    /**
     * Check to see if there are any editors registered for any of the displays
     * @returns boolean
     */ 
    public function hasDisplayEditors() {
        return (count($this->getDisplayEditors()) > 0);
    }

    /**
     * @var protected  array $editors.  keys are the displays and values are the i2ce_swiss subclass of the editor
     */
    protected $editors = null;

    /**
     * Gets any editors registered for any of the displays
     * @returns array.  keys are the displays and values are the i2ce_swiss subclass of the editor
     */ 
    public function getDisplayEditors() {
        if (is_array($this->editors)) {
            return $this->editors;
        }
        $displays = I2CE::getConfig()->traverse("/modules/CustomReports/displays");
        if (!$displays instanceof I2CE_MagicDataNode) {
            return array();
        }
        $this->editors = array();
        foreach ($displays as $display=>$displayConfig) {
            if (!$displayConfig instanceof I2CE_MagicDataNode) {
                continue;
            }
            if (!$displayConfig->is_scalar("editor_class") || !$displayConfig->editor_class) {
                continue;
            }
            $this->editors[$display] = $displayConfig->editor_class;
        }
        return $this->editors;
    }



    /**
     * Gets the swiss child type for the named child
     * @param string $child
     * @returns string, the swiss child type
     */
    public function getChildType($child) {
        $editors = $this->getDisplayEditors();
        if (array_key_exists($child,$editors)) {
            return $editors[$child];
        }
        return parent::getChildType($child);
    }

    
    /**
     * Display the swiss node
     * @param DOMNode $contentNode
     * @param array $transient_options
     * @param string $action
     * @return boolean.  True on success
     */
    public function displayValues($contentNode, $transient_options,$action) {
        if (! ($mainNode = $this->template->appendFileByNode('customReports_reportView_displays.html','div',$contentNode)) instanceof DOMNode) {
            I2CE::raiseError("Cant show display editors");
            return false;
        }
        if (! ( $addNode = $this->template->getElementById('displays_list',$mainNode)) instanceof DOMNode) {
            I2CE::raiseError("Dont know where to add display editors");
            return false;
        }
        $editors = $this->getDisplayEditors();
        foreach ($editors as $editor=>$class) {
            $swissChild = $this->getChild($editor,true);
            if (!$swissChild instanceof I2CE_Swiss) {
                continue;
            }
            $liNode = $this->template->appendFileById('customReports_reportView_displays_each.html','div',$addNode);
            if (!$liNode instanceof DOMNode) {
                I2CE::raiseError("Cannot add editor each  template");
            }
            $this->template->setDisplayDataImmediate('editor_name',$swissChild->getDisplayName(),$liNode);
            $swissChild->addAjaxLink( 'editor_link', 'editor_content', 'editor_ajax',  $liNode, $action,$transient_options);
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
