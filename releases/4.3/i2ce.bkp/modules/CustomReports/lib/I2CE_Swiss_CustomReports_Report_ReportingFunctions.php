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
*  I2CE_SwissConfig_CustomReports_Report_ReportingFunctions
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingFunctions extends I2CE_Swiss_CustomReports_Report_Base{


    
    public function getChildType($child) {
        return 'CustomReports_Report_ReportingFunction';
    }



    public function displayValues($contentNode, $transient_options, $action) {
        $this->ensureFunctions();
        if ($this->reportHasFunctions()) {
            $mainNode = $this->template->appendFileByNode('customReports_report_functions_has.html','div',$contentNode);                
            if (!$mainNode instanceof DOMNode) {
                I2CE::raiseError("Unable to add reported form template");
                return false;
            }
        } else {
            $mainNode = $this->template->appendFileByNode('customReports_report_functions_no.html','div',$contentNode);                
            if (!$mainNode instanceof DOMNode) {
                I2CE::raiseError("Unable to add reported form template");
                return false;
            }
            return true;
        }
        $listNode = $this->template->getElementById('report_functions_list',$mainNode);
        if (!$listNode instanceof DOMNode) {
            I2CE::raiseError("Don't know where to add functions");
            return false;
        }
        foreach ($this as $func=>$swissFunction) {
            $functionNode = $this->template->appendFileByNode('customReports_report_functions_each.html', 'div',$listNode);
            if (!$functionNode instanceof DOMNode) {
                I2CE::raiseError("Bad functions_each");
                return false;
            }
            $swissFunction->addLink('function_contents','function_link',$functionNode,$action, $transient_options);            
        }
        return true;
    }

    public function reportHasFunctions() {
        $swissRel = $this->getSwissRelationship();
        if (!$swissRel instanceof I2CE_Swiss_FormRelationship) {
            return false;
        }
        $swissFuncs = $swissRel->getChild('reporting_functions');
        if (!$swissFuncs instanceof I2CE_Swiss_FormRelationship_ReportingFunctions) {
            return false;
        }
        return (count($swissFuncs) > 0);
    }

    protected $ensured = false;
    protected function ensureFunctions() {
        if ($this->ensured) {
            return true;
        }
        $swissRel = $this->getSwissRelationship();
        if (!$swissRel instanceof I2CE_Swiss_FormRelationship) {
            I2CE::raiseError("Could not get Relationship");
            return false;
        }
        $relFunctions = $swissRel->getSwissFunctions();
        if (!$relFunctions instanceof I2CE_Swiss_FormRelationship_ReportingFunctions) {
            return false;
        }
        $rels = array($relFunctions);
        while (count($rels) > 0) {
            $relFunctions = array_pop($rels);
            foreach ($relFunctions as $func => $swissRelFunction) {
                $this->getChild($func,true);
                if (($t_relFunctions = $swissRelFunction->getChild('reporting_functions')) instanceof I2CE_Swiss_FormRelationship_ReportingFunctions) {
                    $rels[] = $t_relFunctions;
                }
            }
        }
        $this->ensured = true;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
