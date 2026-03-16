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
*  I2CE_Swiss_CustomReports_ReportView_Base
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


abstract class I2CE_Swiss_CustomReports_ReportView_Base extends I2CE_Swiss_CustomReports_Base {




    protected  $reportFactory;
    protected  function setupReportFactory() {
        if ($this->reportFactory instanceof I2CE_Swiss_CustomReports_Reports) {            
            return $this->reportFactory;
        } else {
            $relConfig = $this->factory->getStorage('/')->traverse('../reports',false,false);
            if (!$relConfig instanceof I2CE_MagicDataNode) {
                I2CE::raiseError("Bad magic data path to reports");
                return false;
            }
            $init_options = array(
                'root_url_postfix'=>'reports',
                'root_path'=>$relConfig->getPath(false),
                'root_type'=>'CustomReports_Reports');
            try {
                $swiss_factory = new I2CE_SwissMagicFactory($this->factory->getPage(),$init_options);
            } catch (Exception $e) {
                I2CE::raiseError("Could not create swissmagic for reports:" . $e->getMessage());
                return false;
            }
            try {
                $swiss_factory->setRootSwiss();
            } catch (Exception $e) {
                I2CE::raiseError("Could not create root swissmagic for reports:" . $e->getMessage());
                return false;
            }            
            $this->reportFactory = $swiss_factory;
            return $this->reportFactory;
        }
    }

    public function getSwissReports() {
        $reportFactory = $this->setupReportFactory();
        if (!$reportFactory instanceof I2CE_SwissFactory) {
            return false;
        }
        $swissReports = $reportFactory->getSwiss('/');
        if (!$swissReports instanceof I2CE_Swiss_CustomReports_Reports) {
            return false;
        }
        return $swissReports;
    }


    public function getSwissReport($report) {
        $swissReports = $this->getSwissReports();
        if (!$swissReports instanceof I2CE_Swiss_CustomReports_Reports) {
            return false;
        }
        $swissReport =  $swissReports->getChild($report);
        if (!$swissReport instanceof I2CE_Swiss_CustomReports_Report) {
            I2CE::raiseError("Invalid report $report");
            return false;
        }
        return $swissReport;
    }

    public function getReportsByCategory() {        
        $swissReports = $this->getSwissReports();
        if (!$swissReports instanceof I2CE_Swiss_CustomReports_Reports) {
            return array();
        }
        $reports = array();
        foreach ($swissReports as $report=>$swissReport) {
            if (!$swissReport instanceof I2CE_Swiss_CustomReports_Report) {
                continue;
            }
            $cat = $swissReport->getCategory();
            if ($cat === false ) {
                $cat = 'Uncateorized';
            }
            if (!array_key_exists($cat,$reports)) {
                $reports[$cat] = array();
            }
            $reports[$cat][] = $report;
        }
        return $reports;
    }


    public function getSwissReportsByCategory() {
        $swissReports = $this->getSwissReports();
        if (!$swissReports instanceof I2CE_Swiss_CustomReports_Reports) {
            return array();
        }
        $reports = array();
        foreach ($reportFactory as $report=>$swissReport) {
            if (!$report instanceof I2CE_Swiss_CustomReports_Report) {
                continue;
            }
            $cat = $swissReport->getCategory();
            if ($cat === false ) {
                $cat = 'Uncateorized';
            }
            if (!array_key_exists($cat,$reports)) {
                $reports[$cat] = array();
            }
            $reports[$cat][$report] = $swissReport;
        }
        return $reports;
    }


    protected $swissReportView;
    public function getBaseReportView() {
        if ($this->swissReportView === null) {
            $this->swissReportView = $this->getAncestorByClass('I2CE_Swiss_CustomReports_ReportView');
            if (!$this->swissReportView instanceof I2CE_Swiss_CustomReports_ReportView) {
                $this->swissReportView = false;
            }
        }
        return $this->swissReportView;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
