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
*  I2CE_SwissConfig_FormRelationship_Where
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
 @version 2.1
* @access public
*/


class I2CE_Swiss_Where extends I2CE_Swiss {

    public function getChildType($child) {
        if($child =  'operand') {
            return 'Where_Operands';
        }
        return parent::getChildType($child);
    }

    public function getForm() {
        if ($this->parent instanceof I2CE_Swiss_Where_Operands) {
            return $this->parent->getForm();
        }
        return null;
    }
    public function getFormName() {
        if ($this->parent instanceof I2CE_Swiss_Where_Operands) {
            return $this->parent->getFormName();
        }
        return null;
    }
     
    public function getExcludedForms() {
        return array();
    }


    public function displayValues($contentNode,$transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('swiss_where_container.html','div',$contentNode);
        $operator = $this->getField('operator');
        if (!$operator) {
            //nothing has ever been done, we need to choose the operator 
            //or choose to set a limit.  If we choose to set a limit, then
            //we want to display the where menu.  
            $appendNode = $this->template->getElementById('relationship_new_where', $contentNode);
            if (!$appendNode instanceof DOMNode ) {
                I2CE::raiseError("Do not where to put where menu");
                return false;
            }
            if (!$this->displayWhereChoice($appendNode, $action)) {
                I2CE::raiseError("Could not display where choice");
                return false;
            }
        }else {            
            if ($operator == 'FIELD_LIMIT') {
                $this->displayFieldLimit($contentNode, $action);
            } else {
                $appendNode = $this->template->getElementById('relationship_existing_where', $contentNode);
                if (!$appendNode instanceof DOMNode ) {
                    I2CE::raiseError("Do not where to put operand menu");
                    return false;
                }
                if (!$this->displayOperator($appendNode, $transient_options, $action)) {
                    I2CE::raiseError("Could not display for operator");
                    return false;
                }
            }
        }
        return true;
    }

    protected function displayFieldLimit($contentNode,$action) {
        $field = $this->getField('field');
        $style = $this->getField('style');
        if (!($field && $style)) {
            return $this->displayLimitMenu($contentNode, $action);
        } else {
            $appendNode = $this->template->getElementById('relationship_existing_where', $contentNode);
            if (!$appendNode instanceof DOMNode ) {
                I2CE::raiseError("Do not where to put operand menu");
                return false;
            }            
            return $this->displayExistingLimit($appendNode, $action);
        }
    }


    public function processValues($vals) {
        $operator = strtoupper($this->getField('operator'));
        if (!in_array($operator,array('FIELD_LIMIT','NOT','AND','OR','XOR'))) {
            //nothing has ever been done, we need to choose the operator 
            //or choose to set a limit.  If we choose to set a limit, then
            //we want to display the where menu.  
            if (!$this->processWhereChoice($vals)) {
                $this->userMessage("Could not process new where specification",'notice',false);
                return false;
            }
        } else  {            
            if ($operator == 'FIELD_LIMIT') {
                $this->processFieldLimit($vals);
            } //else we are dealing with an operand and they are already handled by the child
        }
        return true;
    }



    protected function processWhereChoice($vals) {
        if (!array_key_exists('limit_type',$vals) || !$vals['limit_type']) {
            //$this->page->userMessage('Limit type was not set','notice',false);
            return true;
        }
        $type = $vals['limit_type'];
        if (!in_array($type,array('limit','operator'))) {
            $this->page->userMessage("Invalid limit type specified", 'notice',false);
            return false;
        }
        if ($type == 'operator') {
            if (!array_key_exists('limit_operator',$vals)) {
                $this->page->userMessage("No operator specfied", 'notice',false);
                return false;
            }
            $operator = strtoupper($vals['limit_operator']);
            if (!in_array($operator,array('XOR','AND','NOT','OR'))) {
                $this->page->userMessage("Invalid operator specfied", 'notice',false);
                return false;
            }
            //we are good to go
            if (!$this->setField('operator', $operator)) {
                $this->page->userMessage("Could not add operartor $operator", 'notice',false);
            }
        } else {
            //$type == 'limit'
            if (!$this->setField('operator', 'FIELD_LIMIT')) {
                $this->page->userMessage("Could limit by a field", 'notice',false);
                return false;
            }            
        }       
        return true;
    }




    protected function processFieldLimit($vals) {
        $form = $this->getForm();
        $factory = I2CE_FormFactory::instance();
        $formObj=$factory->createContainer($form);
        if (!$formObj instanceof I2CE_Form) {
            I2CE::raiseError("Invalid form ");
            return false;
        }
        if (!array_key_exists('limit_field',$vals) || !$vals['limit_field']) {
            //I2CE::raiseError("Limit field was not set");
            return false;
        }
        $field = $vals['limit_field'];
        if (!array_key_exists('limit_style',$vals) || !$vals['limit_style']) {
            //I2CE::raiseError("Limit style was not set (0)");
            return false;
        }
        if (!is_array($vals['limit_style']) || !array_key_exists($field,$vals['limit_style']) || !$vals['limit_style'][$field]) {
            //I2CE::raiseError("Limit style was not set (1)");
            return false;
        }
        $style = $vals['limit_style'][$field];
        $fieldObj = $formObj->getField($field);
        if (!$fieldObj instanceof I2CE_FormField) {
            I2CE::raiseError("Could not instantiate $field form {$formObj->getName()}");
            return false;
        }        
        $method = 'processLimitMenu_' . $style;
        $reportformfield = $this->getFormName()  . '+' . $fieldObj->getName();
        $data = null;
        if (array_key_exists('limits', $vals)  && is_array($vals['limits'])
            && array_key_exists($field,  $vals['limits']) && is_array($vals['limits'][$field])
            && array_key_exists($style,  $vals['limits'][$field]) && is_array($vals['limits'][$field][$style]))  {
            $data = $vals['limits'][$field][$style];
        }
        $data = $fieldObj->$method($data,$reportformfield,true); 
        if (!is_array($data)) {
            I2CE::raiseError("Could not process the limit {$vals['limit_style']} for $reportformfield");
            return false;
        }
        $this->setField('style',$style);
        $this->setField('field',$field);
        $this->storage->data->erase();
        $this->storage->data = $data;
        return true;
    }




    

    protected function displayOperator($contentNode , $transient_options, $action) {
        //display operator metadata
        $swiss = $this->getChild('operand',true);
        if (!$swiss instanceof I2CE_Swiss) {
            I2CE::raiseError("Could not get swiss for operands");
            return false;
        }
        return $swiss->displayValues($contentNode,$transient_options, $action);
    }




    /**
     * Add in the display for the existing Wheres
     * @param DOMNode $contentode
     * @returns boolean true  on success
     */
    protected function displayWhereChoice( $contentNode, $action) {
        if ($action !== 'edit') {
            return true;
        }
        $mainNode = $this->template->appendFileByNode('swiss_new_limit_choice.html','div',$contentNode);        
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not find where choice template");
            return false;
        }
        $lo = $this->prefixName('limit_operator');
        $lt = $this->prefixName('limit_type');
        $nl = $this->prefixName('no_limit');
        foreach ($this->template->query('.//input[@name="limit_type" and @value="operator"]',$mainNode) as $node) {
            $node->setAttribute('id',$lt . ':operator');
        }
        $this->template->changeAttributesOnNodes(
            array('name'=>'limit_type'),
            array('name'=>$lt),
            $mainNode);
        $this->template->changeAttributesOnNodes(
            array('name'=>'limit_operator'),
            array('name'=>$lo,'id'=>$lo),
            $mainNode);
        $this->template->addUpdateSelect(
            $lo,
            array(
                'show_on_init'=>false,
                'show_function'=>"function (select) { var selected = select.getSelected(); if (selected.length > 0 && selected[0].getProperty('value')) { var input = \$('$lt:operator'); if (input) {input.checked=true;}}}")
            );
        return true;
    }


    protected function displayExistingLimit($contentNode, $action ){
        $style = $this->getField('style');
        $field = $this->getField('field');
        $factory = I2CE_FormFactory::instance();
        $form = $this->getForm();
        $formObj=$factory->createContainer($form);
        if (!$formObj instanceof I2CE_Form) {
            I2CE::raiseError("Invalid form for " . $this->path);
            return false;
        }
        $fieldObj = $formObj->getField($field);
        if (!$fieldObj instanceof I2CE_FormField) {
            I2CE::raiseError("Invalid field at " . $this->path . '/field');
            return false;
        }
        $mainNode = $this->template->appendFileByNode('swiss_existing_limit.html','div',$contentNode);        
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add template for existing limit");
            return false;
        }        
        $method = 'getLimitMenu_' . $style;
        $reportformfield = $this->getFormName()  . '+' . $fieldObj->getName();
        $data = array();
        $this->storage->setIfIsSet($data,'data',true);
        $path_prefix = "swissFactory:values:" . $this->getPath() . ':limits:'.$field.':'.$style ;
        $node = $fieldObj->$method($this->template,$path_prefix,$data); //don't need to rename the inputs here as a prefix is provided
        if ( !$node instanceof DOMNode) {
            I2CE::raiseError("Invalid menu -- no DOMNode");
            return false;
        }        
        
        $delete_link = $this->getURLRoot('delete')  .  $this->path .$this->getURLQueryString();
        $this->template->setDisplayDataImmediate('remove_limit_link',$delete_link  , $mainNode);
        $form = $this->getForm();
        $data = array();
        $this->storage->setIfIsSet($data,'data',true);
        $this->template->appendNodeById($node,'limit_type_menu',false,$mainNode);
        $this->template->setDisplayDataImmediate('limit_style',$style, $mainNode);
        $this->template->setDisplayDataImmediate('limit_field',$field,$mainNode);
        $this->renameInputs('limit_field',$mainNode,'','', true);
        $this->renameInputs('limit_style',$mainNode,'',$field, true);
        return true;
    }





    


    /** 
     * Add in the UI to display the Where menu
     * @param DOMNode $contentNode 
     * @returns boolean true  on success
     *
     */
    protected function displayLimitMenu( $contentNode, $action) {
        if ($action !== 'edit') {
            return true;
        }
        //for each field of this form,
        //we go through each of their limit style.   for eaech pair of (field,limit style)
        //we will add to a 'select' and then put in their configuration menu whose display
        //will be handled via the form worm.
        $appendNode = $this->template->getElementById('relationship_new_where',$contentNode);
        if (!$appendNode instanceof DOMNode) {
            I2CE::raiseError("Could not figure out where to put the new field limit menu");
            return false;
        }
        $form =  $this->getForm();
        if (!$form) {
            I2CE::raiseError("Could not get form name ");
            return false;
        }
        $factory = I2CE_FormFactory::instance();
        $formObj = $factory->createContainer($form);
        if (!$formObj instanceof I2CE_Form) {
            I2CE::raiseError("Could not instantiate form $form at" . $this->factory->getPath($configPath));
            return false;
        }
        $fields = $factory->getFieldNames($form,array('in_db'=>true));
        if (count($fields) == 0) {
            return true; //no fields 
        }
        $mainNode = $this->template->appendFileByNode('swiss_new_limits.html','div',$appendNode);
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not find limits menu template");
            return false;
        }
        $limitsNode = $this->template->getElementById('new_limit_styles',$mainNode);
        if (!$limitsNode instanceof DOMNode) {
            I2CE::raiseError("Could not find limits id");
            return false;
        }
        $selectNode = $this->template->getElementById('limit_field',$mainNode);
        if (!$selectNode instanceof DOMNode) {
            I2CE::raiseError("No field selector");
            return false;
        }
        $reportform = $this->getFormName();
        $excludes =   $this->getExcludedForms();
        $lf = $this->renameInputs('limit_field',$mainNode,'','',true);
        if (count($lf) == 0) {
            return false;
        }
        $lf = current($lf);
        //from selectUpdate $(this.options.prefix + ':' + this.select_id + ':'+ val);
        $lf_select = 'select_styles' . ':' . $lf;
        
        //selectUpdateInstances['swiss_values:/position/where:limit_style:interview_comments'].hide(
        $this->template->addUpdateSelect(
            $lf,
            array(
                'show_class'=>'selector_visible',
                'hide_class'=>'selector_hidden',
                'prefix'=>'select_styles',
                'hide_function_by_val'=>"function (node) {  node.getElements('input, textarea, select').each(function (input) { if (selectUpdateInstances[input.getProperty('name')]) { selectUpdateInstances[input.getProperty('name')].hide(input.getSelected(),new Array());  } input.set('disabled',true); });}",
                'show_function_by_val'=>'function (node) {  node.getElements("input, textarea, select").each(function (input) { input.removeProperty("disabled"); if (selectUpdateInstances[input.getProperty("name")]) { selectUpdateInstances[input.getProperty("name")].show(false, input.getSelected(),true);} });}'
                ));
        $qry = './descendant-or-self::*[("input" or "select" or "textarea")   and @name]';
        foreach ($fields as $field) {
            $limitStyles = $formObj->getLimitStyles($field);
            if (!is_array($limitStyles)) {
                continue;
            }            
            $limitStyles = array_diff(array_keys($limitStyles),$excludes);
            if (count($limitStyles) == 0) {
                continue;
            }
            $fieldObj = $formObj->getField($field);            
            if (!$fieldObj instanceof I2CE_FormField) {
                I2CE::raiseError("Could not instantiate form field $field at " . $this->factory->getPath($configPath));
                continue;
            }
            $limitStylesNode = $this->template->appendFileByNode('swiss_new_limit_styles.html','tr',$limitsNode);
            if (!$limitStylesNode instanceof DOMNode) {
                continue;
            }
	    //from selectUpdate $(this.options.prefix + ':' + this.select_id + ':'+ val);
            $limitStylesNode->setAttribute('id', $lf_select . ':' . $field);            
            $ls = $this->renameInputs('limit_style',$limitStylesNode,'',$field, true);
            if (count($ls) != 1) {
                continue;
            }
            $ls = current($ls);
            $selectStylesNode = $this->template->getElementById($ls, $limitStylesNode);
            if (!$selectStylesNode instanceof DOMNode) {
                continue;
            }
            $ls_select = 'select_style_options' . ':' . $ls;
            $this->template->addUpdateSelect(
                $ls,
                array('show_class'=>'selector_visible',
                      'hide_class'=>'selector_hidden',
                      'prefix'=>'select_style_options','show_on_init'=>false,
                      'hide_function_by_val'=>'function (node) { node.getElements("input, textarea, select").each(function (input) {input.set("disabled",true);});}',
                      'show_function_by_val'=>'function (node) { node.getElements("input, textarea, select").each(function (input) {input.removeProperty("disabled");});}'
                    ));
            $selectNode->appendChild(
                $this->template->createElement(
                    'option',
                    array(
                        'value'=>$field
                        ),
                    $field));
            $reportformfield = $reportform . '+' . $fieldObj->getName();
            foreach ($limitStyles as $style) {                
                $method = 'getLimitMenu_' . $style;
                $path_prefix = "swissFactory:values:" . $this->getPath() . ':limits:'.$field.':'.$style ;
                $limitStyleNode = $fieldObj->$method($this->template,$path_prefix); //don't need to rename these inuputs as there is a prefix here
                if (!$limitStyleNode instanceof DOMNode){ 
                    I2CE::raiseError("Invalid data returned from getLimitMenu_$style for field $field");
                    continue;
                }
                $limitContainerNode = $this->template->appendFileByNode('swiss_new_limit_style.html','tr',$limitsNode);
                if (!$limitContainerNode instanceof DOMNode) {
                    I2CE::raiseError("Limit style template not found");
                    continue;
                }
                $results = $this->template->query($qry,$limitStyleNode);
                for ($i=0; $i < $results->length; $i++) {
                    $results->item($i)->setAttribute('disabled','disabled');
                }
                $limitMenuNode = $this->template->getElementById('limit_type_menu',$limitContainerNode);
                if (!$limitMenuNode instanceof DOMNode) {
                    I2CE::raiseError("node 'limit_type_menu' not found");
                    continue;
                }
                $limitContainerNode->setAttribute('id', $ls_select . ':' . $style);
                $limitMenuNode->appendChild($limitStyleNode);
                $selectStylesNode->appendChild(
                    $this->template->createElement(
                        'option',
                        array(
                            'value'=>$style
                            ),
                        $style));
            }
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
