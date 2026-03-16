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
*  I2CE_SwissConfig_CustomReports_Report_Meta
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_Report_Meta extends I2CE_Swiss_CustomReports_Report_Base{
    

    
    public function setDisplayName($name) {
        $this->setTranslatableField('display_name',$name);
    }

    public function getDisplayName() {
        if ($this->hasField('display_name')) {
            return $this->getField('display_name');
        } else {
            if ($this->parent instanceof I2CE_Swiss_CustomReports_Report) {
                return $this->parent->name;
            } else {
                return false;
            }
        }
    }

    public function setCategory($name) {
            $this->setTranslatableField('category',$name);
    }

    public function getCategory() {
        if ($this->hasField('category')) {
            return $this->getField('category');
        } else {
            return false;
        }
    }
    public function setDescription($name) {
        $this->setTranslatableField('description',$name);
    }

    public function getDescription() {
        if ($this->hasField('description')) {
                return $this->getField('description');
        } else {
            return false;
        }
    }





    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_report_meta.html','div',$contentNode);        
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("No node to append conetnt to");
            return false;
        }
        if (!$this->parent instanceof I2CE_Swiss_CustomReports_Report) {
            I2CE::raiseError("Configuration  corruption");
            return false;
        }
        $report = $this->parent->name;
        $this->template->setDisplayDataImmediate('description',$this->getDescription(), $mainNode);
        $this->template->setDisplayDataImmediate('category',$this->getCategory(), $mainNode);
        $this->template->setDisplayDataImmediate('display_name',$this->getDisplayName(), $mainNode);        
        $this->renameInputs(array('display_name','category','description'), $mainNode);
        return true;
    }


    public function processValues($vals) {
        I2CE::raiseError(print_r($vals,true));
        if (array_key_exists('display_name',$vals)) {
            $this->setDisplayName($vals['display_name']);
        }
        if (array_key_exists('description',$vals)) {
            $this->setDescription($vals['description']);
        }
        if (array_key_exists('category',$vals)) {
            $this->setCategory($vals['category']);
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
