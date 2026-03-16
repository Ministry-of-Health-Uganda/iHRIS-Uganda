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
* @subpackage customreprots
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.6
* @since v4.0.6
* @filesource 
*/ 
/** 
* Class I2CE_Page_CustomReport_ArchiveMenu
* 
* @access public
*/


class I2CE_Page_CustomReport_ArchiveMenu extends I2CE_PageFormLists {

    
    /**
     * Return the view list page for this type of form.
     * If the page exists for view_$type it returns that, otherwise
     * it uses view_list.
     * @return string
     */
    protected function getViewPage( $type ) {
        return "CustomReports/viewArchives/{$this->reportView}";
    }

    /**
     *@var protected string $rerpotView The report view of interest
     */
    protected $reportView;

    /**
     * Perform any actions for the page
     * 
     * @returns boolean.  true on sucess
     */
    public function action() {
        parent::action();
        if (count($this->request_remainder) != 1) {
            $this->userMessage("Cannot view archived report");
            $this->setRedirect("CustomReports/view/reportViews");
            return false;
        }
        reset($this->request_remainder);
        $this->reportView = (string) current($this->request_remainder);
        if (isset($config->reportViews->$this->reportView->limit_view_to) && $config->reportViews->$this->reportView->limit_view_to) {
            if (!$this->hasPermission(' task(custom_reports_admin) or ' . $config->reportViews->$this->reportView->limit_view_to)) {
                $this->userMessage("You do not have permission to view this report");
                $this->setRedirect("CustomReports/view/reportViews");
                return false;                
            }
        }
        if ($this->request_exists('id')) {
            $id = $this->request('id');
            $link = I2CE_FormField_BINARY_FILE::getFieldLink($id,'report');
            $this->redirect($link);
            return true;
        } else {
            $node  = $this->template->addFile( "archiveReports_menu.html" );
            if (!$node instanceof DOMNode) {
                return false;
            }
            $dn = $this->reportView;
            I2CE::getConfig()->setIfIsSet($dn,"/modules/CustomReports/reportViews/{$this->reportView}/display_name");           
            $this->template->setDisplayDataImmediate('reportView_name',$dn, $node);
            $where = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'report_view',
                'style'=>'equals',
                'data'=>array(
                    'value'=>$this->reportView
                    ));
            $list = I2CE_FormStorage::listDisplayFields( 
                'archived_report', 
                I2CE_List::getDisplayFields('archived_report'), 
                false, 
                $where, 
                I2CE_List::getSortFields('archived_report')
                );
            $disp_string = I2CE_List::getDisplayString('archived_report');
            foreach ($list as &$val) {
                $val = vsprintf($disp_string, $val);
            }

            
            return $this->paginateList($list);
        }
    }
    


    /**
     *Get the base link for a displayed row row
     * @returns string
     */ 
    protected function getRowBaseLink() {
        return   "CustomReports/viewArchives/{$this->reportView}?id=archived_report|";
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
