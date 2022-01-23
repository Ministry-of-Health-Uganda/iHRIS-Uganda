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
*  I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Limit
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Limit extends I2CE_Swiss_CustomReports_Report {


    public function getDisplayName() {
        return $this->getHeader();
    }

    public function getHeader() {
        if ($this->hasField('header')) {
            return $this->getField('header');
        } else {
            return $this->getDefaultHeader();
        }
    }

    public function getStyle() {
        return $this->humanText($this->getName());
    }

    public function getDefaultHeader() {
        $header =  $this->getStyle();
        $swissField = $this->getAncestorByClass('I2CE_Swiss_CustomReports_Report_ReportingForm_Field');
        if ($swissField instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
            $header .=   ' ' . $swissField->getDisplayName();
        }
        return $header;
    }


    public function hasHeader() {
        return $this->hasField('header');
    }

    public function setHeader($header) {
        if (!$this->hasHeader() && $this->getDefaultHeader() == $header){ 
            return;
        }
        $this->setTranslatableField('header',$header);
    }

    public function setAllowPivot($can_pivot) {
        if ($can_pivot) {
            $this->setField('allow_pivot',1);
        } else {
            $this->setField('allow_pivot',0);
        }

    }
    public function allowPivot() {
        return ($this->hasField('allow_pivot') && $this->getField('allow_pivot') == 1);
    }


    public function setPivot($enabled) {
        if ($enabled) {
            $this->setField('pivot',1);
        } else {
            $this->setField('pivot',0);
        }
    }

    public function isPivotable() {
        return ($this->isEnabled() && $this->allowPivot()  && $this->hasField('pivot') && $this->getField('pivot') == 1);
    }

    public function setEnabled($enabled) {
        if ($enabled) {
            $this->setField('enabled',1);
        } else {
            $this->setField('enabled',0);
        }
    }

    public function isEnabled() {
        return ($this->hasField('enabled') && $this->getField('enabled') == 1);
    }

    public function processValues($vals) {
        if (!array_key_exists('submit',$vals)) {
            return true;
        }
        $this->setEnabled(array_key_exists('enabled',$vals) && $vals['enabled']);
        $this->setPivot(array_key_exists('pivot',$vals) && $vals['pivot']);
        if (array_key_exists('header',$vals) ) {
            $this->setHeader($vals['header']);
        }
        return true;
    }



    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_report_form_field_limit.html','tr',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to find limit option template");
            return false;
        }
        $this->template->setDisplayDataImmediate('header', $this->getHeader(), $mainNode);
        $this->template->setDisplayDataImmediate('style', $this->getStyle(), $mainNode);
        $this->template->setDisplayDataImmediate('enabled', $this->isEnabled(),$mainNode);        
        $this->template->setDisplayDataImmediate('pivot', $this->isPivotable(),$mainNode);        
        if ($this->allowPivot()) {
            $this->template->setDisplayDataImmediate('pivot_allow', 1,$mainNode);        
        } else {
            $this->template->setDisplayDataImmediate('pivot_allow', 0,$mainNode);        
        }
        if ($action == 'edit') {
            $this->renameInputs('*',$mainNode);
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
