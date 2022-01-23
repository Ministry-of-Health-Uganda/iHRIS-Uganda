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
* Class I2CE_Swiss_CustomReports_ReportView_XSLTS
* 
* @access public
*/


class I2CE_Swiss_CustomReport_ReportView_ExportEditor extends I2CE_Swiss {

    /**
     * Get the swiss child type
     * @param string $child
     * @returns string
     */
    public function getChildType($child) {
        switch ($child) {
        case 'xslts':
            return 'XSLTS';
        default:
            return parent::getChildType($child);
        }
    }


    public function getDisplayName() {
        return 'Export Options';
    }


    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('swiss_exporteditor.html','div',$contentNode);        
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add export editor template");
            return false;
        }        
        $swissChild = $this->getChild('xslts',true);
        if (!$swissChild instanceof I2CE_Swiss) {
            continue;
        }
        $bucketNode = $this->template->getElementById( "xslt_bucket", $mainNode);
        if (!$bucketNode instanceof DOMNode) {
            continue;
        }
        $swissChild->addAjaxLink( 'xslt_link','xslt_content', 'xslt_ajax',$bucketNode,$action,$transient_options);
        return true;
    }



    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
