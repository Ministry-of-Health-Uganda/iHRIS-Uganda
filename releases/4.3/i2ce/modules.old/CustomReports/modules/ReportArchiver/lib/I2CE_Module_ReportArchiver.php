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
* @version v4.0.6.0
* @since v4.0.6.0
* @filesource 
*/ 
/** 
* Class I2CE_Module_ReportArchiver
* 
* @access public
*/


class I2CE_Module_ReportArchiver extends I2CE_Module {

    public function showArchiveMenu($node,$template,$reportView, $show_archive = true) {
        $reportView = (string) $reportView;
        if (!$template instanceof I2CE_Template 
            || !$node instanceof DOMNode 
            || strlen($reportView)==0 
            || !I2CE::getConfig()->is_parent("/modules/CustomReports/reportViews/$reportView")) {
            return false;
        }
        if ( ! ($archiveNode = $template->appendFileByNode('reportArchive_menu.html','span', $node)) instanceof DOMNode) {
            return false;
        }
        $ids = self::getArchiveIds($reportView);
        $template->setDisplayDataImmediate('has_archives',count($ids),$archiveNode);
        if ($show_archive) {
            $template->setDisplayDataImmediate('reportView_archive_generate','CustomReports/archive/' . $reportView,$archiveNode);
            $template->setDisplayDataImmediate('show_archives',1,$archiveNode);
        } else {
            $template->setDisplayDataImmediate('show_archives',0,$archiveNode);
        }
        $template->setDisplayDataImmediate('reportView_archive_view','CustomReports/viewArchives/' . $reportView,$archiveNode);
        return true;
    }


    public static function getArchiveIds($reportView, $date = null) {
        $where = array(
            'operator'=>'FIELD_LIMIT',
            'field'=>'report_view',
            'style'=>'equals',
            'data'=>array(
                'value'=>$reportView
                )
            );
        if ($date) {
            $where = array(
                'operator'=>'AND',
                'operand'=>array(
                    $where,
                    array(
                        'operator'=>'FIELD_LIMIT',
                        'field'=>'date',
                        'style'=>'equals',
                        'data'=>array(
                            'value'=>$date
                            )
                        )
                    )
                );
        }
        return I2CE_FormStorage::search('archived_report',false,$where);
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
