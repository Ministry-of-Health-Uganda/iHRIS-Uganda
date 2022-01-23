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
* @package I2CE
* @subpackage CustomReprots
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.9
* @since v4.0.9
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merge
* 
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merge extends I2CE_Swiss_CustomReports_Report_Base {


    
    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_report_merge_' . $action .'.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not load report merge template");
            return false;
        }        
        if ($this->isEnabled()) {
            $this->template->setDisplayDataImmediate('enabled', 1,$mainNode);
        } else {
            $this->template->setDisplayDataImmediate('enabled', null,$mainNode);
        }
        $inputs = array('enabled','enabled_placeholder');
        if ($action == 'edit') {
            $this->renameInputs($inputs,$mainNode);        
        }
        $this->template->setDisplayDataImmediate('merge_report_description',$this->getDescription(),$mainNode);
        $this->template->setDisplayDataImmediate('merge_report_name',$this->getDisplayName(),$mainNode);
        return true;
    }






    public function processValues($vals) {
        $this->setEnabled(array_key_exists('enabled',$vals) && $vals['enabled']);
        if (! $this->processJoin($vals)) {
            return false;
        }
        return parent::processValues($vals);
    } 

    public function getMergeData($as_linkable = true) {
        //this is 
        $vals = array();
        $vals['report'] = $this->getField('report');
        $vals['reportForm'] = $this->getField('reportForm');
        $vals['join_style'] = $this->getField('join_style');
        $this->storage->setIfIsSet($vals['field'],"join_data/field"); //will not be set if join_style is parent or child 
        return $vals;
    }

    public function getSwissMergedReport($report = null) {
        if ($report == null) {
            if (  !($report = $this->getField('report'))) {
                return false;
            }
        }
        if (! ($swissrep = $this->getSwissReport($report)) instanceof I2CE_Swiss_CustomReports_Report){
            return false;
        }
        return $swissrep;
    }


    public  function verifyMergeData($vals) {
        if (!in_array($vals['join_style'], array('parent','child','parent_field','child_field'))) {
            I2CE::raiseError("Invalid join_style:{$vals['join_style']}");
            return false;
        }
        if (!array_key_exists('report',$vals)) {
            I2CE::raiseError("No report set");
            return false;
        }
        if (! ($swissReport = $this->getSwissMergedReport($vals['report'])) instanceof I2CE_Swiss_CustomReports_Report) {
            I2CE::raiseError("invalid report");
            return false;
        }
        if ( ! ($swissRel = $swissReport->getSwissRelationship()) instanceof I2CE_Swiss_FormRelationship) {
            I2CE::raiseError("Report {$vals['report']} has bad relationship:" . gettype($swissRel) . " " . get_class($swissRel));
            return false;
        }
        if (!array_key_exists('reportForm',$vals)) {
            I2CE::raiseError("No report form set");
            return false;
        }
        if (! ($swissRelForm = $swissRel->getSwissForm($vals['reportForm'])) instanceof I2CE_Swiss_FormRelationship) {
            I2CE::raiseError("Invalid form " .$vals['reportForm'] . " in relationship " . $swissRel->getName());
            return false;
        }
        if (!$formName = $swissRelForm->getForm()) {
            I2CE::raiseError("missing form");
            return false;
        }
        $factory = I2CE_FormFactory::instance();
        $formObj= $factory->createContainer($formName);
        if (!$formObj instanceof I2CE_Form) {
            I2CE::raiseError("Invalid form $formName");
            return false;
        }                    

        $fields = array_merge($factory->getFieldNames($formName,array('in_db'=>true)),array('id','parent'));
        if (!in_array($vals['field'],$fields)) {
            I2CE::raiseError("Invalid field at " . $this->getPath() ." (" . $vals['field'] . ")" . "\n" . print_r($fields,true));
            return false;
        }
        return true;
    }

    public function processJoin($vals) {
        if (!array_key_exists('join_style',$vals)) {
            return true;
        }
        if (!$this->verifyMergeData($vals)) {
            return false;
        }
        //we are saving it here in the same structre as that saved in a form relationship join so we can
        //abuse the SQL generator for joins.

        $this->setField('report',$vals['report']);
        $this->setField('reportForm',$vals['reportForm']);
        if (in_array($vals['join_style'], array('parent_field','child_field'))){
            $this->storage->join_data->field = $vals['field'];
        }
        $this->storage->join_style = $vals['join_style'];
        return true;
    }


    public function getJoinField() {
        $field = false;
        $this->storage->setIfIsSet($field,"join_data/field");
        return $field;
    }


    public function setEnabled($enabled) {
        if ($enabled) {
            $this->setField('enabled',1);
        } else {
            $this->setField('enabled',0);
        }
    }

    public function isEnabled() {
        if($this->hasField('enabled') && $this->getField('enabled') == 1) {
            //now we need to get the corrsesponding merge in the report to make sure it is enabled as well.
            return $this->isLinkable();
        } else {
            return false;
        }
    }

    protected $checked_linkable = null;
    public function isLinkable() {
        if (is_bool($this->checked_linkable)) {
            return $this->checked_linkable;
        }
        $this->checked_linkable = $this->verifyMergeData($this->getMergeData());
        // if (! ($parent = $this->getParent()) instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merges) {
        //     $this->checked_linkable = false;
        // } else {
        //     $this->checked_linkable = $parent->isLinkable($this->getField('report'),$this->getField('reportForm'),$this->getField('field'),$this->getField('join_style'));
        // }
        return $this->checked_linkable;
    }
    




    public function getDisplayName() {
        $swissRep = $this->getSwissMergedReport();
        if (! $swissRep instanceof I2CE_Swiss_CustomReports_Report) {
            I2CE::raiseError("Mad report " . $this->getField('report'));
            return $this->name;
        }
        $form = $this->getField('reportForm');
        if ($form == 'primary_form') {
            $form = $this->getField('report');
        } 
        $msgs = array('merge_display'=> "%s linked on %s");
        foreach ($msgs as $k=>&$v) {
            I2CE::getConfig()->setIfIsSet($v,"/modules/CustomReports/text/$k");
        }       
        return sprintf($msgs['merge_display'],$swissRep->getDisplayName() , $form);
    }
    
    public function getDescription() {        
        $swissRep = $this->getSwissMergedReport();
        if (! $swissRep instanceof I2CE_Swiss_CustomReports_Report) {
            I2CE::raiseError("Mad report " . $this->getField('report'));
            return $this->name;
        }
        $msgs = array(
            'parent'=> "%s linked on %s as a parent form",
            'child'=> "%s linked on %s as a child form",
            'default'=> "%s linked on %s");
        foreach ($msgs as $k=>&$v) {
            I2CE::getConfig()->setIfIsSet($v,"/modules/CustomReports/text/merge_descriptions/$k");
        }
        if (array_key_exists($this->getField('join_style'),$msgs)) {
            $msg = $msgs[$this->getField('join_style')];
        } else {
            $msg = $msgs['default'];
        }
        return sprintf($msg, $swissRep->getDisplayName(), $this->getField('reportForm'));
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
