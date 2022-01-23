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
* Class I2CE_Page_ArchiveReport
* 
* @access public
*/


class I2CE_Page_ArchiveReport extends I2CE_Page{

    protected function actionCommandLine($args, $request_remainder) {
        return $this->archive();
    }
    
    /**
     * The action to archive the reports
     */
    protected function action() {
        parent::action();
        return $this->archive();
    }


    protected function archive(){
        if (count($this->request_remainder) != 1) {
            $this->userMessage("Cannot archive report");
            $this->setRedirect("CustomReports/view/reportViews");
            return false;
        }
        reset($this->request_remainder);
        $reportView = (string) current($this->request_remainder);
        $config = I2CE::getConfig()->modules->CustomReports;
        if ( strlen($reportView)==0    || !$config->is_parent("reportViews/$reportView")) {
            $this->userMessage("Cannot archive report");
            $this->setRedirect("CustomReports/view/reportViews");
            return false;
        }
        if (isset($config->reportViews->$reportView->limit_view_to) && $config->reportViews->$reportView->limit_view_to) {
            if (!$this->hasPermission(' task(custom_reports_admin) or ' . $config->reportViews->$reportView->limit_view_to)) {
                $this->userMessage("You do not have permission to view this report");
                $this->setRedirect("CustomReports/view/reportViews");
                return false;                
            }
        }
        if (!isset($config->displays->Export) || !isset($config->displays->Export->class))  {
            I2CE::raiseError("No report display $display");
            continue;
        }
        $displayClass = $config->displays->Export->class;
        try {
            $displayObj = new $displayClass($this,$reportView);                
        }
        catch (Exception $e) {
            $this->userMessage("Could not archive report data");
            $this->setRedirect("CustomReports/view/reportViews");
            return false;
        }
        if (!$displayObj instanceof I2CE_CustomReport_Display_Export) {
            $this->userMessage("Could not archive the report data");
            $this->setRedirect("CustomReports/view/reportViews");
            return false;
        }
        $output = $displayObj->generateExport();
        if (!$output) {
            $this->userMessage( "Nothing archived");
            $this->setRedirect("CustomReports/view/reportViews");
            return false;            
        }
        $filename = $displayObj->getFileName();
        $ff = I2CE_FormFactory::instance();
        $date= I2CE_Date::now(I2CE_DATE::DATE)->dbFormat();
        $ids = I2CE_Module_ReportArchiver::getArchiveIds($reportView,$date); //only allow one per day
        if (count($ids) == 1) {
            $replaced = true;
            reset($ids);
            $archiveObj = $ff->createContainer('archived_report|' . current($ids));
        } else if (count($ids) > 2) {
            $this->userMessage( "Unable to save report data -- to many exisitng reports for today");
            $this->setRedirect("CustomReports/view/reportViews");
            return false;                        
        } else {
            $replaced = false;
            $archiveObj = $ff->createContainer('archived_report');
        }
        if (!$archiveObj instanceof I2CE_ArchivedReport) {
            $this->userMessage( "Unable to save report");
            $this->setRedirect("CustomReports/view/reportViews");
            return false;            
        }                                                       
        $docField = $archiveObj->getField('report');
        if (!$docField instanceof I2CE_FormField_BINARY_FILE) {
            $this->userMessage( "Unable to save report data");
            $this->setRedirect("CustomReports/view/reportViews");
            return false;            
        }
        $docField->setFromData($output,$filename);
        $dn = $reportView;
        $config->setIfIsSet($dn,"reportViews/$reportView/display_name");
        $archiveObj->name = $dn;
        $archiveObj->report_view = $reportView;
        $archiveObj->save($this->user);            
        if ($replaced) {
            $this->userMessage("Succesully replaced existing archived report");
        } else {
            $this->userMessage("Succesully archived report");
        }
        $this->redirect("CustomReports/view/reportViews");
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
