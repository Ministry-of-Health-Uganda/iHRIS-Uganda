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
 *
 *  I2CE_SwissConfig_FormRelationship_ReportingFunction
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */

class I2CE_Swiss_SQLFunction extends I2CE_Swiss {

    /**
     * Get the field object associated to this node.
     */
    public function getFieldObj() {
        $formfield = $this->getField('formfield');
        if (!$formfield) {
            return false;
        }
        $class = '';
        if (!I2CE::getConfig()->setIfIsSet($class,"/modules/forms/FORMFIELD/$formfield")) {
            return false;
        }        
        if (!$class || !class_exists($class)) {
            return false;
        }
        $options = array(
            'in_db'=>false,
            'required'=>false,
            'unique'=>false,
            'meta'=>array());
        $link = $this->getField('link_fields');
        if ($link) {
            $options['meta']['display'] = array('default'=>array());
            if ($link) {
                $options['meta']['display']['default']['fields'] = $link;
            }
        }
        if ( $this->getStorage()->is_parent('limits') ) {
            $options['meta']['limits'] = $this->getStorage()->getAsArray('limits');
        }
        $selects = preg_split('/\s*,\s*/',$this->getField('select_forms'),-1,PREG_SPLIT_NO_EMPTY);
        if (count($selects) > 0) {
            $options['meta']['form'] = $selects;
        }
        $top = $this;
        while ( !$top instanceof I2CE_Swiss_FormRelationship &&
                $top instanceof I2CE_Swiss && $top->hasParent() ) {
            $top = $top->getParent();
        }
        if ( $top instanceof I2CE_Swiss_FormRelationship ) {
            $options['meta']['relationship'] = $top->getName();
        }
        $fieldObj = new $class($this->name,$options);
        if (!$fieldObj instanceof I2CE_FormField) {
            return false;
        }
        return $fieldObj;
    }


    public function getChildType($child) {
        if ($child == 'reporting_functions') {
            return 'FormRelationship_ReportingFunctions';
        }
        return parent::getChildType();
    }
    

    /**
     * Display method 
     * Displays menu
     * @param DOMNode $contentNode  Defaults to null.  If set the swissconfig should display all content relative to that node
     */
    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('swiss_sqlfunction_' . $action .  '.html','div',$contentNode);
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not add report function template");
            return false;
        }
        $this->template->setDisplayDataImmediate('name',$this->getName(),$mainNode);
        $fields =array('aggregate','qry','description','link_fields','select_forms') ;        
        foreach ($fields as $key) {
            $this->template->setDisplayDataImmediate($key,$this->getField($key),$mainNode);
        }       
        if ($action == 'edit') {
            $formfields = I2CE::getConfig()->getAsArray('/modules/forms/FORMFIELD');
            $formfield = NULL;
            $this->storage->setIfIsSet($formfield ,'formfield');
            $this->template->setDisplayDataImmediate('formfield',$formfields,$mainNode);
            if ($formfield !== NULL) {
                $this->template->selectOptionsImmediate('formfield',$formfield,$mainNode);
            }
        } else {
            $formfield = $this->getField('formfield');
            I2CE::getConfig()->setIfIsSet($formfield, '/modules/forms/FORMFIELD/' . $formfield);
            $this->template->setDisplayDataImmediate('formfield',$formfield,$mainNode);
        }
        $fields[] = 'formfield';
        if ($action === 'edit') {
            $this->renameInputs($fields,$mainNode);
        }
        if ( ($swissChild = $this->getChild('reporting_functions',true)) instanceof I2CE_Swiss) {        
            $swissChild->addAjaxLink('dependent_functions_link','reporting_functions_container','dependent_functions_ajax',$mainNode,$action, $transient_options);            
        }
        return true;
    }
    
    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */    
    public function processValues($vals) {
        $success = true;
        foreach (array('aggregate','qry','description', 'formfield','link_fields','select_forms') as $key) {
            if (array_key_exists($key,$vals) ) {
                if (! $this->setField($key, $vals[$key])) {
                    I2CE::raiseError("Could not set $key at {$this->path} to be " . print_r($vals[$key],true));
                    $success =  false;
                }
            }
        }
        return $success;
    }



    public function getFunctionDetails() {
        $qry = '';
        if (!$this->storage->setIfIsSet($qry,'qry')) {
            I2CE::raiseError("Function {$this->name} is not defined");
            return false;
        }
        $qry= trim($qry);
        if (!$qry) { //no query set so skip it
            I2CE::raiseError("Function {$this->name} is empty");
            return false;
        }
        $formfield = NULL;
        $this->storage->setIfIsSet($formfield ,'formfield');
        preg_match_all('/`([a-zA-Z0-9\_\-]+\+[a-zA-Z0-9\_\-]+)`/',$qry,$required_fields);
        return array('qry'=>$qry,'formfield'=>$formfield,'required_fields'=>array_unique($required_fields[1]));
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
