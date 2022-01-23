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
*  I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merges
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merges extends I2CE_Swiss_CustomReports_Report_Base {

    public function getChildType($child) {
        return 'CustomReports_Report_ReportingForm_Field_Merge';
    }



    
    
    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_report_merges.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add reported form template");
            return false;
        }        
        $appendNode = $this->template->getElementById('merges_list',$mainNode);
        if( !$appendNode instanceof DOMNode) {
            I2CE::raiseError("Could not find id=merge_options element");
            return false;
        }
        //$this->displayMergeDefaults($mainNode,$action);
        if ($action == 'edit') {
            $this->renameInputs('*',$mainNode);
        }
        $this->ensureLinkableReports();
        foreach ($this as $merge=>$swissMerge) {
            if ((!$swissMerge instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merge)) {
                continue;
            }
            if (!$swissMerge->isLinkable()) {
                //turn off the lights when you leave a room
                $swissMerge->setEnabled(false);
            }
            $mergeNode = $this->template->appendFileByNode('customReports_report_merges_each.html','tr',$appendNode);                
            $this->template->setDisplayDataImmediate('merge_report_name',$swissMerge->getDisplayName(),$mergeNode);
            $swissMerge->addAjaxLink('merge_report_link','merge_report_content','merge_report_ajax',$mainNode,$action, $transient_options);            
        }
        return true;
    }

    public function rewind() {
        $this->ensureLinkableReports();
        parent::rewind();
    }
    

    public function hasLinkableReports() {
        //i am purposefully not ensureLinkableReports here as it is expensive in this is meant to be called repeatedly in report view
        foreach ($this->getChildNames() as $merge) {
            $swissMerge = $this->getChild( $merge );
            if (!$swissMerge instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merge) {
                continue;
            }
            if ($swissMerge->isEnabled()) {
                return true;
            }
        }
        return false;
    }

    protected $ensured = false;

    protected function getLinkName() {
        //the name is not require to be like this.  doing it for ease of creation.
        //return $data['report'] . ':' . $data['reportForm'] . ':' . $data['field'] . ':' . $data['join_style'];
        $existing = $this->getChildNames();
        do {
            $name = str_replace( '.', '_', microtime(true) . '_' . mt_rand() );             //should be universally unique among all possible merges.
        }while  (in_array($name,$existing));
        return $name;
    }

    public function getLinkableReports() {
        if (!$this->getParent() instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
            return array();
        }
        return $this->getParent()->getLinkableReports();
    }

    protected function ensureLinkableReports() {
        if ($this->ensured) {
            return;
        }
        $swissMerges =array();
        foreach ($this->getChildNames() as $merge) {
            $swissMerge = $this->getChild( $merge );
            if (!$swissMerge instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merge) {
                continue;
            }            
            if (!$swissMerge->isLinkable()) {
                //turn off the lights when you leave a room
                $swissMerge->setEnabled(false);
            }
            $swissMerges[] = $swissMerge;
        }
        foreach ($this->getLinkableReports() as $report_data) {
            foreach ($swissMerges as $swissMerge) {
                if ($this->isSameMerge($swissMerge,$report_data)) {
                    continue  2;
                }
            }
            $child = $this->getChild($this->getLinkName(),true);
            //if we are here, we have not added this in yet.  do so now.
            if (!$child instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merge) {
                I2CE::raiseError("Bad creation");
                continue;
            }            
            $report_data['enabled'] =false;
            $child->processValues( $report_data);
        }
        $this->ensured =true;
    }

    public function isLinkable($report,$reportForm,$field,$style) {
        foreach ($this->getLinkableReports() as $report_data) {
            if ($report != $report_data['report']) {
                continue;
            }
            if ($reportForm != $report_data['reportForm']) {
                continue;
            }
            if ($field != $report_data['field']) {
                continue;
            }
            if ($style != $report_data['join_style']) {
                continue;
            }
            //we have a match
            return true;
        }
        return false;
    }
    

    public function isSameMerge($swissMerge,$data) {
        if (!$swissMerge instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merge) {
            return false;
        }
        if (!is_array($data)) {
            return false;
        }
        foreach (array('report','reportForm','join_style') as $key) {
            if (!array_key_exists($key,$data) || $data[$key] != $swissMerge->getField($key)) {
                return false;
            }
        }
        if (in_array($data['join_style'],array('parent_field','child_field')) && ! $data['field'] = $swissMerge->getJoinField()) {
            return false;
        }
        //if we made it here we match
        return true;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
