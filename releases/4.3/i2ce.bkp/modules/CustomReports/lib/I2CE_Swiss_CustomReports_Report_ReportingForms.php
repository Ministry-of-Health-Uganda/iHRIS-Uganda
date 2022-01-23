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
*  I2CE_SwissConfig_CustomReports_Report_ReportingForms
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingForms extends I2CE_Swiss_CustomReports_Report_Base{
    
    public function getChildType($child) {
        return 'CustomReports_Report_ReportingForm';
    }


    public function getForms() {
        return $this->storage->getKeys();
    }


    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_report_forms.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add reported form list template");
            return false;
        }
        $listNode = $this->template->getElementById('report_forms_list', $mainNode);
        if (!$listNode instanceof DOMElement) {
            I2CE::raiseError("Don't knwo where to add forms");
            return false;
        }
        $swissRelationshipForms = $this->getSwissFormsInRelationship();
        foreach ($swissRelationshipForms as $form=>$swissRelationshipForm) {
            if ($swissRelationshipForm instanceof I2CE_Swiss_FormRelationship_Join) {
                $swissForm = $this->getChild($form,true);
            } else {
                $swissForm = $this->getChild('primary_form',true);
            }
            if (!$swissForm instanceof I2CE_Swiss_CustomReports_Report_ReportingForm) {
                continue;
            }
            $formNode = $this->template->appendFileByNode('customReports_report_forms_each.html','li',$listNode);            
            if (!$formNode instanceof DOMNode) {
                continue;
            }
            $this->template->setDisplayDataImmediate('name',$form,$formNode);
            $swissForm->addAjaxLink('form_link','form_contents','form_ajax',$formNode,$action, $transient_options);            
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
