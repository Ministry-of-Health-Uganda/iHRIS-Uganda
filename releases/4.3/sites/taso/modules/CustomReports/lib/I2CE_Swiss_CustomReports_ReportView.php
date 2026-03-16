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
*  I2CE_SwissConfig_CustomReports_ReportView
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_ReportView extends I2CE_Swiss_CustomReports_ReportView_Meister {
    public function setReport($report) {
        $swissRep = $this->getSwissReport($report);
        if (!$swissRep instanceof I2CE_Swiss_CustomReports_Report){
            I2CE::raiseError("Invalid report $report");
            return false;
        }
        $this->setField('report',$report);
        return true;        
    }
    public function getReport() {
        if (!$this->hasField('report')) {
            return false;
        }
        return $this->getField('report');
    }

    public function isDisabled() {
        if (!$this->hasField('disable')) {
            return false;
        }
        return ($this->getField('disable') == 1);
    }

    public function setDisabled($disable) {
        if ($disable) {
            $this->setField('disable',1);
        } else {
            $this->setField('disable',0);
        }
        
    }


    public function hasLimitedView() {
        if (!$this->hasField('limit_view_to')) {
            return false;
        }
        return (strlen($this->getField('limit_view_to')) > 0);
    }

    public function setLimitedView($limited_view) {
        $this->setField('limit_view_to',$limited_view);
    }

    public function getLimitedView() {
        if (!$this->hasField('limit_view_to')) {
            return false;
        }
        return $this->getField('limit_view_to');    
    }


    public function hasLimitedViewByTask() {
        if (!$this->hasField('limit_view_to_task')) {
            return false;
        }
        return (strlen($this->getField('limit_view_to_task')) > 0);
    }

    public function setLimitedViewByTask($limited_view) {
        $this->setField('limit_view_to_task',$limited_view);
    }

    public function getLimitedViewByTask() {
        if (!$this->hasField('limit_view_to_task')) {
            return false;
        }
        return $this->getField('limit_view_to_task');    
    }


    public function canAccess() {
        if (!$this->page instanceof I2CE_Page) {
            return false;
        }
        if ($this->isDisabled()) {
            return $this->page->hasPermission('role(admin) or task(custom_reports_admin)');
        } else {
            $permissions = array();
            if ($this->hasLimitedView()) {
                //this is a general permission string
                $permissions[] = $this->getLimitedView();
            }
            if ($this->hasLimitedViewByTask()) {
                //this is a specific task
                $permissions[] = 'task(' . $this->getLimitedViewByTask() . ')';
            }
            if (count($permissions) == 0) {
                return true;
            }
            $permissions[] = 'task(custom_reports_admin)';        
            return $this->page->hasPermission(implode(' or ' , $permissions));
        }
    }


    public function setDisplayName($displayName) {
        $this->setTranslatableField('display_name',$displayName);
    }

    public function getDisplayName() {
        if ($this->hasField('display_name')) {
            return $this->getField('display_name');
        } else {
            return $this->humanText($this->name);
        }
    }

    public function hasDescription() {
        return $this->hasField('description');
    }

    public function getDescription() {
        if ($this->hasDescription()) {
            return $this->getField('description');
        } else {
            return false;
        }
    }

    public function setDescription($descr) {
        $this->setTranslatableField('description',$descr);
    }

    public function setRelatedViews($views) {
        if (is_string($views)) {
            $views = explode(',',$views);
        }
        if (!is_array($views)) {
            $views = array();
        }
        foreach ($views as &$view) {
            $view = trim($view);
        }
        $this->setField('related_views',implode(',',$views));
        return true;
    }


    public function getRelatedViews($as_array = true) {
        if ($this->hasField('related_views')) {
            if ($as_array) {
                return explode(",",$this->getField('related_views'));
            } else {
                $this->getField('related_views');
            }
        } else {
            if ($as_array) {
                return array();
            } else {
                return false;
            }
        }
    }


    public function hasTotal() {
        return ($this->hasField('total') && $this->getField('total') == 1);
    }

    public function setTotal($total) {
        if ($total) {
            $this->setField('total','1');
        } else {
            $this->setField('total','0');
        }
    }




    public function processValues($values) {
        if (!$this->page->hasPermission('task(custom_reports_can_edit_reportViews)')) {
            return false;
        }
        parent::processValues($values);
        if (array_key_exists('total_hide',$values)) {
            if (array_key_exists('total',$values) && $values['total']) {
                $this->setTotal(true);
            } else {
                $this->setTotal(false);
            }
        }
        if (array_key_exists('disable_hide',$values)) {
            if (array_key_exists('disable',$values) && $values['disable']) {
                $this->setDisabled(true);
            } else {
                $this->setDisabled(false);
            }
        }
        if (array_key_exists('related_views',$values)) {
            $this->setRelatedViews($values['related_views']);
        }
        if (array_key_exists('description',$values)) {
            $this->setDescription($values['description']);
        }
        if (array_key_exists('displayName',$values)) {
            $this->setDisplayName($values['displayName']);
        }
        if (array_key_exists('limit_view_to_task',$values)) {
            $this->setLimitedViewByTask($values['limit_view_to_task']);
        }
        return true;
    }



    public function getChildType($child) {
        switch ($child) {
        case 'display_options':
            return 'CustomReports_ReportView_Displays';
        case 'default_display_options':
            return 'CustomReports_ReportView_DisplayOptions';
        default:
            return parent::getChildType($child);
        }
    }

    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_reportView_' . $action .'.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not load report views template");
            return false;
        }        
        $this->template->setDisplayDataImmediate('report_link', $this->getURLRoot('edit').'/../reports/'.$this->getReport(), $mainNode);
        $swissRep = $this->getSwissReport($this->getReport());
        if ( $swissRep instanceof I2CE_Swiss_CustomReports_Report && ($reportName = $swissRep->getDisplayName()) != '' ) {
            $this->template->setDisplayDataImmediate('reportName', $reportName, $mainNode);
        } else {
            $this->template->setDisplayDataImmediate('reportName', $this->getReport(), $mainNode);
        }
        $this->template->setDisplayDataImmediate('displayName',$this->getDisplayName(),$mainNode);
        $this->template->setDisplayDataImmediate('description',$this->getDescription(),$mainNode);
        $this->template->setDisplayDataImmediate('show_link',$this->getURLRoot('show'). '/../' . $this->name,$mainNode);        
        if ($this->getField('default_display')) {        
            $this->template->setDisplayDataImmediate('has_default_display',1, $mainNode);
            $this->template->setDisplayDataImmediate('clear_default_display_link',$this->getURLRoot('delete'). '/' . $this->getPath(false). '/default_display' ,$mainNode);        
        } else {
            $this->template->setDisplayDataImmediate('has_default_display',0, $mainNode);
        }
        $this->template->setDisplayDataImmediate('total',$this->hasTotal(),$mainNode);
        $this->template->setDisplayDataImmediate('disable',$this->isDisabled(),$mainNode);
        $all_views = array();
        $swissReportViews = $this->getAncestorByClass('I2CE_Swiss_CustomReports_ReportViews');
        if ($swissReportViews instanceof I2CE_Swiss_CustomReports_ReportViews) {
            foreach ($swissReportViews as $view=>$swissReportView) {
                if ($view == $this->name || !$swissReportView->canAccess()) {
                    continue;
                }
                $all_views[$view] = $swissReportView->getDisplayName();
            }
        }
        $this->template->setDisplayDataImmediate('related_views[]',$all_views,$mainNode);
        $this->template->selectOptionsImmediate('related_views[]',$this->getRelatedViews(),$mainNode);
        $this->template->setDisplayDataImmediate('limit_view_to_task',$this->getTasks(),$mainNode);
        if ($this->hasLimitedViewByTask()) {
            $this->template->selectOptionsImmediate('limit_view_to_task',array($this->getLimitedViewByTask()),$mainNode);
        }
        $this->renameInputs(array('displayName','description','disable','disable_hide','total','total_hide','related_views[]','limit_view_to_task'),$mainNode);
        $has_displays = false;
        if (  ($swissDisplays = $this->getChild('display_options',true))  instanceof I2CE_Swiss_CustomReports_ReportView_Displays){
            if (($has_displays = $swissDisplays->hasDisplayEditors())
                &&  (($node = $this->template->getElementById('display_editors',$mainNode))  instanceof DOMNode))  {                

                $swissDisplays->addAjaxLink('displays_link','displays_container',  'displays_ajax' ,$node,$action, $transient_options);
            }
        }
        $this->template->setDisplayDataImmediate('has_display_editors',$has_displays ? 1 : null, $mainNode);
        return $this->displayFields($mainNode,$transient_options,$action);
    }

    protected function getTasks() {
        $tasks = I2CE::getConfig()->getAsArray("/I2CE/tasks/task_description");
        if (! is_array($tasks)) {
            $tasks = array();
        }
        return $tasks;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
