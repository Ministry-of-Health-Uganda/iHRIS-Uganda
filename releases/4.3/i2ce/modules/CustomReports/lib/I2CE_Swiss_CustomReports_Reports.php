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
*  I2CE_SwissConfig_CustomReports_Reports
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_Reports extends I2CE_Swiss_CustomReports_Report_Base {

    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_reports.html','div',$contentNode);        
        if (!$this->displayExistingReports($mainNode, $transient_options,$action)) {
            I2CE::raiseError("Could not show menu for existing report");
            return false;
        }
        if ($action === 'edit') {
            //now display the option to create a new report choosing a relationship and a shortname for the report
            //which does not exist yet
            if (!$this->displayNewReport($mainNode, $transient_options)) {
                I2CE::raiseError("Could not show menu for new report");
                return false;
            }
        }
        return true;
    }

    public function processValues($vals) {
        $msgs = array( 
            'report_no_name'=>'No shortname specified for the report',
            'report_bad_name'=>'Invalid shortname specified for the report',
            'report_name_used'=>'Shortname specified for the report is already in use',
            'report_no_relationship'=>"No report relationship specified for the report",
            'report_bad_relationship'=>"Specified report relationship is invalid"
            );
        foreach ($msgs as $k=>&$v) {
            I2CE::getConfig()->setIfIsSet($v,"/modules/CustomReports/text/user_messages/$k");
        }                        
        
        if (!array_key_exists('shortname',$vals) ){
            $this->userMessage($msgs['report_no_name'],'notice');
            return false;
        }
        $shortname = $vals['shortname'];
        if (!I2CE_MagicDataNode::checkKey($shortname)) {
            $this->userMessage($msgs['report_bad_name'],'notice');
            return false;
        }
        if ($this->getChild($shortname)) {
            $this->userMessage($msgs['report_name_used'],'notice');
            return false;
        }
        if (!array_key_exists('relationship',$vals)) {
            $this->userMessage($msgs['report_no_relationship'],'notice');
            return false;
        }
        $relationship = $vals['relationship'];
        $swissRelationships = $this->getSwissRelationships();
        if (!$swissRelationships instanceof I2CE_Swiss_FormRelationships) {
            $this->userMessage($msgs['report_bad_relationship'],'notice');
            return false;
        }
        if (!$swissRelationships->getChild($relationship) instanceof I2CE_Swiss_FormRelationship) {
            $this->userMessage($msgs['bad_relationship'],'notice');
            return false;
        }
        $child = $this->getChild($shortname,true);
        if (!$child instanceof I2CE_Swiss_CustomReports_Report) {
            return false;
        }
        $child->setRelationship($relationship);
        if (array_key_exists('display_name',$vals)) {
            $child->setDisplayName($vals['display_name']);
        }
        if (array_key_exists('description',$vals)) {
            $child->setDescription($vals['description']);
        }
        if (array_key_exists('category',$vals)) {
            $child->setCategory($vals['category']);
        }
        return true;
    }

    

    protected function displayNewReport($contentNode, $transient_options) {
        //get the existing report relationships.
        $swissRelationships = $this->setupRelationshipFactory();
        if (!$swissRelationships instanceof I2CE_SwissFactory || count($swissRelationships) == 0
                || !$swissRelationships->getSwiss( '/' ) instanceof I2CE_Swiss_FormRelationships ) {
            //we have no relationships to create a report on.
            $noReportNode = $this->template->appendFileById('customReports_reports_no_new.html','div','new_report',false,$contentNode);
            if ($noReportNode instanceof DOMNode) {
                $this->template->setDisplayDataImmediate(
                    'create_link',$this->getURLRoot('edit') . '../relationships');
            }
            return true;
        } 
        $mainNode = $this->template->appendFileById('customReports_reports_new.html','div','new_report',false,$contentNode);
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not add report categories template");
            return false;
        }
        //get the existing report names so we can limit the new name to not be in the list
        //$reports = I2CE_CustomReport_Template::getReportNames();
        $reports = $this->getChildNames();
        $shortNameNode = $this->template->getElementByName('shortname',0,$mainNode);
        if (!$shortNameNode instanceof DOMNode) {
            I2CE::raiseError("Could not find report shortname input");
            return false;
        }
        $this->template->setClassValue($shortNameNode,'validate_data',array('notinlist'=>array_keys($reports)), '%');
        $rels = array();
        foreach ($swissRelationships->getSwiss( '/' ) as $relationship=>$swissRelationship) {
            $rels[$relationship] = $swissRelationship->getDisplayName();
        }
        $this->template->setDisplayDataImmediate('relationship', $rels,$mainNode);
        $this->renameInputs(array('display_name','shortname','relationship','description','category'),$mainNode);
        return true;
    }




    public function getChildType($child) {
        return 'CustomReports_Report';
    }


    protected function getReportsByCategory() {
        $reports = $this->getChildNames();
        $cats = array();
        foreach ($reports as $report) {
            $swissReport = $this->getChild($report);
            if (!$swissReport instanceof I2CE_Swiss) {
                continue;
            }
            $cat = $swissReport->getCategory();
            if (!$cat) {
                $cat = 'Uncategorized';
            }
            if (!array_key_exists($cat,$cats)) {
                $cats[$cat] = array();
            }
            $cats[$cat][] = $report;
        }
        uksort($cats,'strnatcasecmp');
        foreach ($cats as $c=>&$reports) {
            uksort($reports,'strnatcasecmp');
        }
        return $cats;
    }



    protected function displayExistingReports($contentNode, $transientOptions, $action) {
        $categories = $this->getReportsByCategory();
        //now display the reports by category
        $catsNode = $this->template->appendFileById('customReports_reports_categories.html','div','existing_reports',false,$contentNode);
        if (!$catsNode instanceof DOMNode) {
            I2CE::raiseError("Could not add report categories template");
            return false;
        }
        foreach ($categories as $cat=>$reports) {
            $catNode = $this->template->appendFileById('customReports_reports_category.html','div','existing_reports_categories',false,$catsNode);
            if (!$catNode instanceof DOMNode) {
                I2CE::raiseError("Could not add report category template");
                return false;
            }
            if (strlen($cat)== 0 ) {
                $cat = 'Uncategorized';
            }
            $this->template->setDisplayDataImmediate('report_category',$cat,$catNode);
            foreach ($reports as $shortname) {
                $swissReport = $this->getChild($shortname);
                if (!$swissReport instanceof I2CE_Swiss_CustomReports_Report) {
                    continue;
                }
                $name = $swissReport->getDisplayName();
                $desc = $swissReport->getDescription();
                if(!$name) {
                    $name =$shortname;
                }
                $repNode = $this->template->appendFileById('customReports_reports_category_report.html','li','existing_reports_category',false,$catNode);
                if (!$repNode instanceof DOMNode) {
                    I2CE::raiseError("Could not add report category template");
                    return false;
                }
                $reportStatus =  I2CE_CustomReport::getStatus($shortname);
                if ($reportStatus == 'generated') {
                    $reportLastGenerated = strftime("%c",I2CE_CustomReport::getLastGenerationTime($shortname));                                
                } else {
                    $reportLastGenerated = '';
                }
                $reportHoomanStatus = I2CE_CustomReport::getStatus($shortname,true);
                $this->template->setDisplayDataImmediate('report_edit_link',$this->getURLRoot($action) . '/' . $shortname,$repNode);
                $this->template->setDisplayDataImmediate('report_save_link',$this->getURLRoot('export') . '/../' . $shortname . '?pipe=2',$repNode);
                $this->template->setDisplayDataImmediate('report_delete_link',$this->getURLRoot('delete') . '/../' . $shortname,$repNode);
                $this->template->setDisplayDataImmediate('report_generate_link',$this->getURLRoot('generate') . '/../' . $shortname,$repNode);
                $this->template->setDisplayDataImmediate('report_generate_force_link',$this->getURLRoot('generate_force') . '/../' . $shortname,$repNode);
                $this->template->setDisplayDataImmediate('report_name',$name,$repNode);
                $this->template->setDisplayDataImmediate('report_description',$desc,$repNode);
                $this->template->setDisplayDataImmediate('report_status',$reportHoomanStatus,$repNode);
                $this->template->setDisplayDataImmediate('report_last_generated',$reportLastGenerated,$repNode);
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
