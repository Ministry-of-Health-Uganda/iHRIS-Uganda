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
*  I2CE_SwissConfig_CustomReports_Report
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_Report extends I2CE_Swiss_CustomReports_Report_Base{

    protected $meta;
    
    public function getMeta() {
        if (!$this->meta instanceof I2CE_Swiss_CustomReports_Report_Meta) {
            $this->meta = $this->getChild('meta',true);
        }
        return $this->meta;
    }

    public function setDisplayName($name) {
        $meta = $this->getMeta();
        if ($meta instanceof I2CE_Swiss_CustomReports_Report_Meta) {
            $meta->setDisplayName($name);
        }
    }

    public function getDisplayName() {
        $meta = $this->getMeta();
        if ($meta instanceof I2CE_Swiss_CustomReports_Report_Meta) {
            return $meta->getDisplayName();
        } else {
            return false;
        }
    }

    public function setDescription($name) {
        $meta = $this->getMeta();
        if ($meta instanceof I2CE_Swiss_CustomReports_Report_Meta) {
            $meta->setDescription($name);
        }
    }

    public function getDescription() {
        $meta = $this->getMeta();
        if ($meta instanceof I2CE_Swiss_CustomReports_Report_Meta) {
            return $meta->getDescription();
        } else {
            return false;
        }
    }

    public function setCategory($name) {
        $meta = $this->getMeta();
        if ($meta instanceof I2CE_Swiss_CustomReports_Report_Meta) {
            $meta->setCategory($name);
        }
    }

    public function getCategory() {
        $meta = $this->getMeta();
        if ($meta instanceof I2CE_Swiss_CustomReports_Report_Meta) {
            return $meta->getCategory();
        } else {
            return false;
        }
    }

    public function setRelationship($rel) {
        $this->setField('relationship',$rel);
    }

    public function getRelationship() {
        if ($this->hasField('relationship')) {
            return $this->getField('relationship');
        } else {
            return false;
        }
    }

    public function getChildType($child) {
        switch($child) {
        case 'meta':
            return 'CustomReports_Report_Meta';
        case 'reporting_forms':
            return 'CustomReports_Report_ReportingForms';
        case 'reporting_functions':
            return 'CustomReports_Report_ReportingFunctions';
        case 'reporting_internals':
            return 'CustomReports_Report_ReportingInternals';
        default:
            return false;
        }
    }

    public function getSwissFunction($func) {
        $swissFunctions = $this->getChild('reporting_functions',true);
        if (!$swissFunctions instanceof I2CE_Swiss_CustomReports_Report_ReportingFunctions) {
            return false;
        }
        $swissFunction = $swissFunctions->getChild($func);
        if (!$swissFunction instanceof I2CE_Swiss_CustomReports_Report_ReportingFunction) {
            return false;
        }
        return $swissFunction;
    }

    public function getSwissInternal($internal) {
        $swissInternals = $this->getChild('reporting_internals',true);
        if (!$swissInternals instanceof I2CE_Swiss_CustomReports_Report_ReportingInternals) {
            return false;
        }
        $swissInternal = $swissInternals->getChild($internal);
        if (!$swissInternal instanceof I2CE_Swiss_CustomReports_Report_ReportingInternal) {
            return false;
        }
        return $swissInternal;
    }

    public function getSwissField($reportform,$field) {
        $swissForms = $this->getChild('reporting_forms',true);
        if (!$swissForms instanceof I2CE_Swiss_CustomReports_Report_ReportingForms) {
            return false;
        }
        $swissForm = $swissForms->getChild($reportform);
        if (!$swissForm instanceof I2CE_Swiss_CustomReports_Report_ReportingForm) {
            return false;
        }
        $swissFields = $swissForm->getSwissFields();
        if (!$swissFields instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Fields) {
            return false;
        }
        $swissField = $swissFields->getChild($field);
        if (!$swissField instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
            return false;
        }
        return $swissField;
    }



    public function getSwissForms() {
        $swissForms = $this->getChild('reporting_forms',true);
        if (!$swissForms instanceof I2CE_Swiss_CustomReports_Report_ReportingForms) {
            return array();
        }
        return $swissForms;
    }

    
    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_report.html','div',$contentNode);        
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add report template");
            return false;
        }        
        $report = $this->getDisplayName();
        $relationship = $this->getField('relationship');
        $this->template->setDisplayDataImmediate('report_display_name',$report,$mainNode);
        $this->template->setDisplayDataImmediate('relationship_link',$this->getURLRoot('edit').'/../relationships/'.$relationship,$mainNode);
        $swissRelationship = $this->getSwissRelationship();
        if ( $swissRelationship ) {
            $this->template->setDisplayDataImmediate('report_relationship',$swissRelationship->getDisplayName(),$mainNode);
        } else {
            $this->template->setDisplayDataImmediate('report_relationship',$relationship,$mainNode);
        }
        $children = array('meta','reporting_forms', 'reporting_functions','reporting_internals');
        foreach ($children as $child) {
            $swissChild = $this->getChild($child,true);
            if (!$swissChild instanceof I2CE_Swiss) {
                continue;
            }
            $bucketNode = $this->template->getElementById($child . "_bucket", $mainNode);
            if (!$bucketNode instanceof DOMNode) {
                continue;
            }
            $swissChild->addAjaxLink($child .'_link',$child .'_container', $child  . '_ajax' ,$bucketNode,$action, $transient_options);
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
