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
*  I2CE_SwissConfig_CustomReports_Report_ReportingInternal
* @package I2CE
* @subpackage Core
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.1.4
* @version 4.1.4
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingInternal extends I2CE_Swiss_CustomReports_Report_Base{

    /**
     * For validation checks internals are always in the relationship since
     * they're internal values.
     * @return boolean
     */
    public function isInRelationship() {       
        return true;
    }

    public function getChildType($child) {
        switch($child) {
        case 'limits':
            return 'CustomReports_Report_ReportingInternal_Limits';
        default:
            return parent::getChildType($child);
        }
    }



    public function getFieldObj() {
        switch( $this->name ) {
            case "last_modified" :
                return $this->getFormFieldObj( 'DATE_TIME' );
                break;
            case "created" :
                return $this->getFormFieldObj( 'DATE_TIME' );
                break;

            default :
                return false;
        }
        /*
        $relationship = $this->getSwissRelationship();
        if (!$relationship instanceof I2CE_Swiss_FormRelationship) {
            return false;
        }
        $reportingInternals = $relationship->getChild('reporting_functions');
        if (!$reportingInternals instanceof I2CE_Swiss_FormRelationship_ReportingInternals) {
            return false;
        }
        $reportingInternal = $reportingInternals->getChild($this->name);
        if (!$reportingInternal instanceof I2CE_Swiss_SQLInternal) {
            return false;
        }
        $fieldObj =  $reportingInternal->getFieldObj();
        if (!$fieldObj instanceof I2CE_FormField) {
            return false;
        }
        $fieldObj->setHeaders(array('default'=>$this->getField('header')));
        return $fieldObj;
        */
    }

    /**
     * Create a formfield object class and return it.
     * @param string $formfield
     * @param array $config
     * @return I2CE_FormField
     */
    protected function getFormFieldObj( $formfield, $config=array() ) {
        $class = '';
        if ( !I2CE::getConfig()->setIfIsSet( $class, "/modules/forms/FORMFIELD/$formfield" ) ) {
            return false;
        }
        if ( !$class || !class_exists( $class ) ) {
            return false;
        }
        $options = array( 
                'in_db' => false,
                'required' => false,
                'unique' => false,
                'meta' => array() );
        if ( array_key_exists( 'link_fields', $config ) ) {
            $link = $config['link_fields'];
            if ( $link ) {
                $options['meta']['display'] = array( 'default' => array() );
                $options['meta']['display']['default']['fields'] = $link;
            }
        }
        if ( array_key_exists( 'select_forms', $config ) ) {
            $selects = preg_split( '/\s*,\s*/', $config['select_forms'], -1, PREG_SPLIT_NO_EMPTY );
            if ( count( $selects ) > 0 ) {
                $options['meta']['form'] = $selects;
            }
        }
        $fieldObj = new $class( $this->name, $options );
        if ( !$fieldObj instanceof I2CE_FormField ) {
            return false;
        }
        return $fieldObj;
    }


    public function processValues($vals) {
        if (!array_key_exists('submit',$vals)) {
            return true;
        }
        $this->setEnabled(array_key_exists('enabled',$vals) && $vals['enabled']);
        if (array_key_exists('header',$vals)) {
            $this->setHeader($vals['header']);
        }
        return true;
    }


    public function hasHeader() {
        return $this->hasField('header');
    }
    public function setHeader($header) {
        if (!$this->hasHeader() && $header == $this->getDefaultHeader()) {
            return;
        }
        $this->setTranslatableField('header',$header);
    }
    public function getDefaultHeader() {
        return $this->humanText($this->name);
    }
    public function getHeader() {
        if ($this->hasHeader()) {
            return $this->getField('header');
        } else {
            return $this->getDefaultHeader();
        }
    }
    public function setEnabled($enabled) {
        if ($enabled) {
            $this->setField('enabled',1);
        } else {
            $this->setField('enabled',0);
        }
    }

    public function isEnabled() {
        return ($this->hasField('enabled') && $this->getField('enabled') == 1);
    }

    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_report_internal.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add reported form template");
            return false;
        }
        $this->template->setDisplayDataImmediate('name',$this->name, $mainNode);
        $this->template->setDisplayDataImmediate('header',$this->getHeader(), $mainNode);
        if ($this->isEnabled()) {
            $this->template->setDisplayDataImmediate('enabled', 1,$mainNode);
        } else {
            $this->template->setDisplayDataImmediate('enabled', null,$mainNode);
        }        
        $this->renameInputs(array('enabled','submit','header'),$mainNode);
        $swissLimits = $this->getChild('limits',true);
        if ($swissLimits instanceof I2CE_Swiss_CustomReports_Report_ReportingInternal_Limits) {
            $swissLimits->addAjaxLink('limits_link','limits_contents','limits_ajax',$mainNode,$action, $transient_options);            
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
