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
*  I2CE_SwissConfig_CustomReports_Report_ReportingFunction
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingFunction extends I2CE_Swiss_CustomReports_Report_Base{


    public function getChildType($child) {
        switch($child) {
        case 'limits':
            return 'CustomReports_Report_ReportingFunction_Limits';
        default:
            return parent::getChildType($child);
        }
    }


    protected function findDependentFunction( $function, $reportingFunctions ) {
        foreach( $reportingFunctions->getChildNames() as $child ) {
            if ( ($depFuncs = $reportingFunctions->getChild($child)->getChild('reporting_functions') ) instanceof I2CE_Swiss_FormRelationship_ReportingFunctions ) {
                if ( ($reportingFunction = $depFuncs->getChild($function)) instanceof I2CE_Swiss_SQLFunction ) {
                    return $reportingFunction;
                } else {
                    if ( ( $depFunc = $this->findDependentFunction( $function, $depFuncs ) ) instanceof I2CE_Swiss_SQLFunction ) {
                        return $depFunc;
                    }
                    // This wouldn't search later functions for dependencies so only return if something is found.
                    //return $this->findDependentFunction( $function, $depFuncs );
                }
            }
        }
        return null;
    }


    public function getFieldObj() {
        $relationship = $this->getSwissRelationship();
        if (!$relationship instanceof I2CE_Swiss_FormRelationship) {
            return false;
        }
        $reportingFunctions = $relationship->getChild('reporting_functions');
        if (!$reportingFunctions instanceof I2CE_Swiss_FormRelationship_ReportingFunctions) {
            return false;
        }
        $reportingFunction = $reportingFunctions->getChild($this->name);
        if (!$reportingFunction instanceof I2CE_Swiss_SQLFunction) {
            $reportingFunction = $this->findDependentFunction( $this->name, $reportingFunctions );
            if ( !$reportingFunction instanceof I2CE_Swiss_SQLFunction ) {
                return false;
            }
        }
        $fieldObj =  $reportingFunction->getFieldObj();
        if (!$fieldObj instanceof I2CE_FormField) {
            return false;
        }
        $fieldObj->setHeaders(array('default'=>$this->getField('header')));
        return $fieldObj;
    }


    public function processValues($vals) {
        if (!array_key_exists('submit',$vals)) {
            return true;
        }
        $this->setEnabled(array_key_exists('enabled',$vals) && $vals['enabled']);
        if (array_key_exists('header',$vals)) {
            $this->setHeader($vals['header']);
        }
        return true;
    }


    public function hasHeader() {
        return $this->hasField('header');
    }
    public function setHeader($header) {
        if (!$this->hasHeader() && $header == $this->getDefaultHeader()) {
            return;
        }
        $this->setTranslatableField('header',$header);
    }
    public function getDefaultHeader() {
        return $this->humanText($this->name);
    }
    public function getHeader() {
        if ($this->hasHeader()) {
            return $this->getField('header');
        } else {
            return $this->getDefaultHeader();
        }
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

    protected $swissRelFuncDep = null;


    protected function ensureSwissFunctionDependency() {
        if (is_array($this->swissRelFuncDep)) {
            return;
        }
        $swissRel = $this->getSwissRelationship();
        if (!$swissRel instanceof I2CE_Swiss_FormRelationship) {
            $this->swissRelFuncDep = array();
            return;
        }
        $dep = $swissRel->getSwissFunctionDependency($this->name);
        if (!is_array($dep)) {
            $this->swissRelFuncDep = array();
        }        
        $this->swissRelFuncDep = $dep;
    }

    public function getSwissRelationshipFunction() {
        $this->ensureSwissFunctionDependency();
        if (is_array($this->swissRelFuncDep) && count($this->swissRelFuncDep) > 0) {
            end($this->swissRelFuncDep);
            return current($this->swissRelFuncDep);
        } 
        return false;
    }

    public function isInRelationship() {       
        return ($this->getSwissRelationshipFunction() instanceof I2CE_Swiss_SQLFunction);
    }






    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_report_function.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add reported form template");
            return false;
        }
        $this->template->setDisplayDataImmediate('name',$this->name, $mainNode);
        $this->template->setDisplayDataImmediate('header',$this->getHeader(), $mainNode);
        if ($this->isEnabled()) {
            $this->template->setDisplayDataImmediate('enabled', 1,$mainNode);
        } else {
            $this->template->setDisplayDataImmediate('enabled', null,$mainNode);
        }        
        $this->renameInputs(array('enabled','submit','header'),$mainNode);
        $swissLimits = $this->getChild('limits',true);
        if ($swissLimits instanceof I2CE_Swiss_CustomReports_Report_ReportingFunction_Limits) {
            $swissLimits->addAjaxLink('limits_link','limits_contents','limits_ajax',$mainNode,$action, $transient_options);            
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
