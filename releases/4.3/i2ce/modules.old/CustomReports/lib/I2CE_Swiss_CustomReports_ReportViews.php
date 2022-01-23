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
*  I2CE_SwissConfig_CustomReports_ReportViews
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_ReportViews extends I2CE_Swiss_CustomReports_ReportView_Base {

    public function processValues($values) {
        //here we add in a new report
        if (!$this->page->hasPermission('task(custom_reports_can_edit_reportViews)')) {
            return false;
        }
        if (!array_key_exists('report',$values) ) {
            return true; //no report specifed.
        }
        $report = $values['report'];
        if (is_string($report) && strlen($report) == 0) {
            return true; // no report specified
        }
        $swissRep = $this->getSwissReport($values['report']);
        if (!$swissRep instanceof I2CE_Swiss_CustomReports_Report){
            I2CE::raiseError("Invalid report " . $values['report']);
            return false;
        }
        //we are good to go, we need the index of the next unused report view
        $views = $this->storage->getKeys();
        foreach ($views as $i=>$view)  {
            if (!is_numeric($view)) {
                unset($views[$i]);
            }
        }
        $view = time();
        // if (count($views) == 0) {
        //     $view = 0;
        // } else {
        //     $view = max($views) + 1;
        // }
        //just to be extra paranoid:
        if  ($this->getChild($view) instanceof I2CE_Swiss_CustomReports_ReportView) {            
            I2CE::raiseError("Someone messed up on report views: $view");
            return false;
        }
        $swissReportView = $this->getChild($view,true);
        if (!$swissReportView instanceof I2CE_Swiss_CustomReports_ReportView) {
            I2CE::raiseError("Could not create new report view: $view");
            return false;
        }
        if (!$swissReportView->setReport($values['report'])) {
            return false;
        }
        if (array_key_exists('description',$values)) {
            $swissReportView->setDescription($values['description']);
        }
        if (array_key_exists('displayName',$values)) {
            $swissReportView->setDisplayName($values['displayName']);
        }
        return true;        
    }

    public function getChildType($child) {
        return 'CustomReports_ReportView';
    }




    public function getSwissReportViewsByReport() {
        $reportViews = $this->storage->getKeys();
        $reports = array();
        foreach ($reportViews as $reportView)  {
            $swissReportView = $this->getChild($reportView);
            if (!$swissReportView instanceof I2CE_Swiss_CustomReports_ReportView) {
                continue;
            }
            if (!$swissReportView->canAccess()) {
                continue;
            }
            $report = $swissReportView->getReport();
            if ($report === false) {
                continue;
            }
            if (!array_key_exists($report,$reports)) {
                $reports[$report] = array();
            }
            $reports[$report][$reportView] = $swissReportView;
        }
        return $reports;
    }

    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_reportViews_' . $action .'.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not load report views template");
            return false;
        }
        if ($action == 'edit') {
            $swissReports = $this->getSwissReports();
            if ($swissReports instanceof I2CE_Swiss_CustomReports_Reports) {
                $report = array();
                foreach ($swissReports as $report=>$swissReport) {
                    $reports[$report] = $swissReport->getDisplayName();
                }
                $this->template->setDisplayDataImmediate('report',$reports, $mainNode);
                $this->renameInputs(array('report','displayName','description'),$mainNode);
            }
        }


        $reportsByCat = $this->getReportsByCategory(true); //array with keys categories, values array of reports.        
        $swissViews = $this->getSwissReportViewsByReport(); //array with kets reports, values array of swissreportviews        
        $appendNode = $this->template->getElementById('reports_by_category_container',$mainNode);
        if (!$appendNode instanceof DOMNode) {
            I2CE::raiseError("Dont know where to display reports by category");
            return false;
        }
        foreach ($reportsByCat as $cat=>$reports) {            
            //if we are in edit mode, add abailty to create a new view for each report.
            //otherwise if there are no reports views for the report, dont show it
            //we won't know how many until we are done processing, so we will import a node,
            //but not add it to the template
            $views_by_cat = 0;
            $catNode = $this->template->appendFileByNode('customReports_reportViews_reports_'.$action .'.html','li', $appendNode);
            if (!$catNode instanceof DOMNode) {
                I2CE::raiseError("Unable to add report by category template");
                return false;
            }
            $this->template->setDisplayDataImmediate('report_category',$cat,$catNode);
            $reportsNode = $this->template->getElementById('reports_container',$catNode);
            if (!$reportsNode instanceof DOMNode ) {
                I2CE::raiseError("Don't know where to put reports by category");
                return false;
            }
            foreach ($reports as $report) {
                if (!array_key_exists($report, $swissViews) || !is_array($swissViews[$report])) {
                    $swissViewsByReport = array();
                } else {
                    $swissViewsByReport =$swissViews[$report];
                }
                $views_by_rep = 0;
                foreach ( $swissViewsByReport as $reportView=>$swissReportView) {
                    if (!$swissReportView instanceof I2CE_Swiss_CustomReports_ReportView) { //safety
                        continue;
                    }
                    if (!$swissReportView->canAccess()) {
                        continue;
                    }
                    $reportViewNode = $this->template->appendFileByNode('customReports_reportViews_views_each_' . $action .'.html','li', $reportsNode);
                    if (!$reportViewNode instanceof DOMNode) {
                        I2CE::raiseError("Could not add the report's  views template");
                        return false;
                    }
                    //we are good to go.
                    $views_by_cat++;
                    $views_by_rep++;
                    $this->template->setDisplayData('reportView',$reportView,$reportViewNode);
                    $this->template->setDisplayDataImmediate('show_link',$this->getURLRoot('show') . '/../' . $reportView,$reportViewNode);
                    $this->template->setDisplayDataImmediate('displayName', $swissReportView->getDisplayName(),$reportViewNode);
                    $this->template->setDisplayDataImmediate('description', $swissReportView->getDescription(),$reportViewNode);
                    $this->template->setDisplayDataImmediate('edit_link',$this->getURLRoot('edit') .  $this->getPath(false) .'/' . $reportView,$reportViewNode);
                    $this->template->setDisplayDataImmediate('delete_link',$this->getURLRoot('delete') .  $this->getPath(false) .'/' . $reportView,$reportViewNode);
                    $this->template->setDisplayDataImmediate('save_link',$this->getURLRoot('export') .  $this->getPath(false) .'/' . $reportView,$reportViewNode);
                }
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
