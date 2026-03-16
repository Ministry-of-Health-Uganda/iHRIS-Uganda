<?php
/**
 * @copyright © 2011, 2012 Intrahealth International, Inc.
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
*  I2CE_Swiss_CustomReports_Report_ReportingForm_Field_ModuleLimits
* @package I2CE
* @subpackage Core
* @author Luke Duncan <lduncan@intrahealth.org>
* @since 4.1
* @version 4.1
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingForm_Field_ModuleLimits extends I2CE_Swiss_CustomReports_Report_Base {


    
    /**
     * Get the child type for this object.
     * @return string
     */
    public function getChildType($child) {
        return 'CustomReports_Report_ReportingForm_Field_ModuleLimit';
    }



    
    /**
     * Display the values in the template for this object.
     * @param DOMNOde $contentNode
     * @param array $transient_options
     * @param string $action
     * @return boolean
     */
    public function displayValues($contentNode, $transient_options, $action) {
        $this->ensureModuleLimits();
        $mainNode = $this->template->appendFileByNode('customReports_report_module_limits.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add reported form template");
            return false;
        }        
        $appendNode = $this->template->getElementById('module_limits_contents',$mainNode);
        if( !$appendNode instanceof DOMNode) {
            I2CE::raiseError("Could not find id=module_limit_options element");
            return false;
        }
        //$this->displayLimitDefaults($mainNode,$action);
        if ($action == 'edit') {
            $this->renameInputs('*',$mainNode);
        }
        foreach ($this->allowed as $limit) {
            if (! ($swissModuleLimit = $this->getChild($limit)) instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_ModuleLimit) {
                continue;
            }
            //foreach ($this as $limit=>$swissModuleLimit) {
            $moduleLimitNode = $this->template->appendFileByNode('customReports_report_module_limits_each.html','tbody',$appendNode);                
            $swissModuleLimit->addLink('module_limit_contents','module_limit_fill',$moduleLimitNode,$action, $transient_options);            
        }
        return true;
    }


    /**
     * Rewind the iterator for this object.
     */
    public function rewind() {
        $this->ensureModuleLimits();
        parent::rewind();
    }

    /**
     * Check to see if there are any possible module limits for the parent field
     */
    public function hasLimits() {
        $this->ensureModuleLimits();
        return count($this->allowed) > 0;
    }

    /**
     * @var boolean To make sure the module limits are available.
     */
    protected $ensured = false;

    /**
     * @var protected array $allowed  Allowed children
     */
    protected $allowed = array();
    /**
     * Make sure all the appropriate module limits are available.
     */
    protected function ensureModuleLimits() {
        if ($this->ensured) {
            return;
        }        
        if ($this->storage->is_scalar()) {
            return false;
        }
        if (!$this->parent instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
            return false;
        }
        if ( ($fieldObj = $this->parent->getFieldObj()) instanceof I2CE_FormField) {
            I2CE::raiseError("Calling get_report_module_limit_options on " . $fieldObj->getHTMLName());
            $limits = I2CE_ModuleFactory::callHooks( "get_report_module_limit_options"  );
            I2CE::raiseError("get_report_module_limit_options returns" . print_r($limits,true));
            foreach( $limits as $limit ) {
                if (!is_array($limit) || !array_key_exists('fields',$limit) || !is_array($limit['fields']) ) {
                    continue;
                }
            
                if ( $fieldObj->getName() == 'id' && ($formObj = $fieldObj->getContainer()) instanceof I2CE_Form && array_key_exists($form = $formObj->getName(),$limit['fields'])) {
                    $limit['fields'] = array($form=>$limit['fields'][$form]);
                } elseif ($fieldObj instanceof I2CE_FormField_MAPPED &&  count($field_forms = $fieldObj->getSelectableForms()) == 1) {
                    //check to see if the fields mapped value can be one of the selectable form
                    I2CE::raiseError("Have a mapped Field with selectable forms:\n\t" . implode(" ", $field_forms). "\ncomapring against access_facility+location selectable:\n\t" . implode(" ", array_keys($limit['fields'])));
                    foreach( $limit['fields'] as $form=>$formName ) {
                        if (!in_array($form,$field_forms)) {
                            unset($limit['fields'][$form]);
                        }
                    }
                } else {
                    $limit['fields'] = array();
                }
                if (count($limit['fields']) == 0) {
                    continue;
                }
                $swissModuleLimit = $this->getChild( $limit['module'], true );
                if (!$swissModuleLimit instanceof  I2CE_Swiss_CustomReports_Report_ReportingForm_Field_ModuleLimit) {
                    I2CE::raiseError("Bad swiss child for " . $limit['module']);
                }
                $swissModuleLimit->setFieldOptions( $limit['fields'] );
                $this->allowed[] = $limit['module'];
            }
        }
        $this->ensured =true;
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
