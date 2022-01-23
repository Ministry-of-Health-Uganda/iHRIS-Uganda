<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
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
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2.69
* @since v3.2.69
* @filesource 
*/ 
/** 
* Class I2CE_FormField_MAPPED
* 
* @access public
*/


abstract class I2CE_FormField_MAPPED extends I2CE_FormField_DB_STRING { 

    /**
     * Componentizes the given $db_value based on component
     * @param string $db_value.  The non-componentized value
     * @param array $forms of stirng. The form names which we wish to componentize.
     * @param string $component The component we wish to encode
     * @returns string The componentized db_value
     */
    abstract public function getComponentizedValue($db_value,$forms,$component);

    /**
     * Componentizes the given $db_value based on component
     * @param string $db_ref.  The reference to the data
     * @param array $forms of stirng. The form names which we wish to componentize.
     * @param string $component The component we wish to encode
     * @returns string The componentized db_value
     */
    abstract public function getSQLComponentization($db_ref,$forms,$component);


    abstract public function getDefaultDisplayStyle($type);



    

    /**
     * Hooked method to remap a given id on a given form and field
     * @param I2CE_List $lsit
     * @param string $oldid
     * @param string $newid
     */
    public function remapField($form,$oldid,$newid) {
        I2CE::raiseError("Remapping of field not implement for " . get_class($this));
        return false;
    }






    public function getDisplayedStyle($type = 'default') {
        $default_style = $this->getDefaultDisplayStyle($type);
        $path = "meta/display/$type/style";
        if ( !$this->optionsHasPath($path) || ! is_scalar( $style = $this->getOptionsByPath($path) )) {
            $style =  $default_style;
        }
        if ($style !== $default_style) {
            $checkMethod = "checkStyle_$style";
            if (!$this->_hasMethod($checkMethod) 
                || !$this->$checkMethod()
                ) {
                $style = $default_style;
            }
        }
        return $style;
    }

    public function getDisplayedFields($type = 'default', $check_forms = true) {            
        $path = "meta/display/$type/fields";
        if (!$this->optionsHasPath($path) || ! is_scalar( $fields = $this->getOptionsByPath($path) )) {
            //$path = "meta/form";
            //if (!$check_forms  || !$this->optionsHasPath($path) || ! is_array( $forms = $this->getOptionsByPath($path) )) {
            if (!$check_forms  || ! is_array( $forms = $this->getSelectableForms() )) {
                return array($this->name);
            } else {
                return $forms;
            }
        }
        return explode(':', $fields);
    }




    public function getFormOrders ($type = 'default') {
        $path = "meta/display/$type/orders";
        if (!$this->optionsHasPath($path) || ! is_array( $orders = $this->getOptionsByPath($path) )) {
            return array();
        }
        return $orders;
    }


    /**
     * Checks to see if this field can have map to any form or not.   Set to true in meta/form_any
     *@returns boolean
     */
    public function canSelectAnyForm() {
        $path = "meta/form_any";
        return ($this->optionsHasPath($path) && $this->getOptionsByPath($path));
    }

    /**
     * Checks to see which forms this form can map to.
     * @returns array()
     */
    public function getSelectableForms() {            
        if ($this->getName() == 'remap'  && $this->getContainer() instanceof I2CE_Form) {
            return array($this->getContainer()->getName());
        }
        if ($this->canSelectAnyForm()) {
            return I2CE::getConfig()->getKeys("/modules/forms/forms");
        }
        $path = "meta/form";
        if (!$this->optionsHasPath($path) || ! is_array( $forms = $this->getOptionsByPath($path) ) || count($forms) == 0) {
            return array($this->name);
        }
        return $forms;
    }

    /**
     * Return a report name to use for getting the display data for this mapped field instead of using
     * the direct data or from cached tables when that may be very slow.  This is generally for
     * more complicated displays using a tree view.
     * @param string $type
     * @return string
     */
    public function getDisplayReport( $type = 'default' ) {
        $path = "meta/display/$type/display_report";
        if ( !$this->optionsHasPath($path) || !is_array( $report = $this->getOptionsByPath( $path ) ) ) {
            $path = "meta/display/default/display_report";
            if ( !$this->optionsHasPath($path) || !is_array( $report = $this->getOptionsByPath( $path ) ) ) {
                return null;
            }
        }
        return $report;
    }



    protected $alternate_limits = array();
    
    public function setAlternateLimits($limits,$type = 'default') {
        $this->alternate_limits[$type] = $limits;
    }
    public function restoreLimits($type = 'default') {
        unset($this->alternate_limits[$type]);
    }
    public function getFormLimits ($type = 'default') {
        if (array_key_exists($type,$this->alternate_limits)) {
            return $this->alternate_limits[$type];
        }
        $path = "meta/limits/$type";
        if (!I2CE_ModuleFactory::instance()->isEnabled('form-limits')
            || !$this->optionsHasPath($path) || ! is_array( $limits = $this->getOptionsByPath($path) )) {
            $limits = array();
        }
        if ( $this->optionsHasPath( "meta/enable_limits_add" ) && is_array( $enabled_add = $this->getOptionsByPath( "meta/enable_limits_add" ) ) ) {
            $limits_add = array();
            foreach( $enabled_add as $module => $enable ) {
                if ( $enable == 1 ) {
                    if ( $this->optionsHasPath( "meta/limits_add/$module" ) ) {
                        $limits_add[$module] = $this->getOptionsByPath( "meta/limits_add/$module" );
                    }
                }
            }
            foreach( $limits_add as $module => $form_limit ) {
                foreach( $form_limit as $form => $limit ) {
                    if ( array_key_exists( $form, $limits ) ) {
                        if ( $limits[$form]['operator'] == 'AND' ) {
                            $limits[$form]['operand'][] = $limit;
                        } else {
                            $limits[$form] = array(
                                    'operator' => 'AND',
                                    'operand' => array( 0 => $limits[$form],
                                        1 => $limit )
                                    );
                        }
                    } else {
                        $limits[$form] = $limit;
                    }
                }
            }
        }
        return $limits;
    }

    /**
     *Gets any additional dynamic limits 
     * @param I2CE_Tempalte $template
     * @param DOMElment  $node
     * @param mixed $limit a json encoded string of limit data or an array of limit data
     * @returns array who keys are selectable form norms with additional limits and values are the limit data
     */
    protected function getAdditionalLimits($template,$node,$limit) {
        $add_limits = array();
        if (!$template instanceof I2CE_Template || !$node instanceof DOMNode) {
            return $add_limits;
        }
        if (is_string($limit)) {
            $limit = json_decode($limit,true);
        }
        if (!is_array($limit)) {
            $limit = array();
        }
        $forms = $this->getSelectableForms();
        $ff = I2CE_FormFactory::instance();        
        foreach ($limit as $limitForm=>$limitFields) {
            if (!in_array($limitForm,$forms)) {
                continue;
            }
            $formObj= $ff->createContainer($limitForm);
            if (!$formObj instanceof I2CE_Form) {
                continue;
            }
            foreach ($limitFields as $limitField=>$limitStyles) {
                $fieldObj = $formObj->getField($limitField);
                if (!$fieldObj instanceof I2CE_FormField) {
                    continue;
                }
                $allowedStyles = $fieldObj->getLimitStyles();
                foreach ($limitStyles as $limitStyle => $displayValue) {
                    if (!is_string($displayValue) || strlen($displayValue) == 0) {
                        continue;
                    }
                    if (!array_key_exists($limitStyle,$allowedStyles)) {
                        continue;
                    }
                    $limitData = $allowedStyles[$limitStyle];
                    if (!is_array($limitData) || !count($limitData) == 1 || !current($limitData) == 'value') {
                        continue;
                    }
                    $value = null;
                    if ($displayValue[0] == '$')  { 
                        $value = $template->getData('DISPLAY',$displayValue,$node,false,false);
                    } else {
                        //assume it is a form value
                        list($t_form,$t_field) = array_pad(explode('+',$displayValue,2),2,'');
                        if (!$t_form || !$t_field) {
                            continue;
                        }
                        $t_formObj = $template->getData('FORM',$t_form,$node,false,false);
                        if ((!$t_formObj instanceof I2CE_Form) || ($t_formObj->getName() != $t_form)) {
                            continue;
                        }
                        $t_fieldObj = $t_formObj->getField($t_field);
                        if (!$t_fieldObj instanceof I2CE_FormField) {
                            continue;
                        }
                        $value = $t_fieldObj->getDBValue();
                    }
                    if (!is_scalar($value) || $value === null) {
                        continue;
                    }
                    //we are good to go
                    if (!array_key_exists($limitForm,$add_limits)) {
                        $add_limits[$limitForm] = array( 'operator'=>'AND', 'operand'=>array());
                    }
                    $add_limits[$limitForm]['operand'][] = array(
                        'operator'=>'FIELD_LIMIT',
                        'field'=>$limitField,
                        'style'=>$limitStyle,
                        'data'=>array(
                            'value'=>$value
                            )
                        );
                        
                }
            }
        }
        return $add_limits;

    }


    /**
     *@returns array where keys are ids, values are arrays with the following keys 'value', 'display'
     */
    public function getMapOptions($type='default', $show_hidden= 0,$flat = true,$add_limits = array()) {
        $forms = $this->getSelectableForms();
        $fields = $this->getDisplayedFields($type);
        $limits = $this->getFormLimits($type);
        $report = $this->getDisplayReport($type);
        if (is_array($add_limits) && count($add_limits) > 0) {
            if (!is_array($limits) || count($limits) == 0) {
                $limits = $add_limits;
            } else{
                //need to go through each form and possibly merge limits
                foreach ($add_limits as $form=>$formLimits)  {
                    if (!array_key_exists($form,$limits) || !is_array($limits[$form]) || count($limits[$form]) == 0) {
                        $limits[$form] = $formLimits;
                    }  else {
                        $limits[$form] = array(
                            'operator'=>'AND',
                            'operand'=>array(0=>$limits[$form], 1=>$formLimits)
                            );
                    }
                }
            }
        }
        $orders = $this->getFormOrders($type);
        $data = I2CE_DataTree::buildDataTree( $fields,$forms,$limits, $orders, $show_hidden, $report,$type );        
        if (!$data) {
            //I2CE::raiseError("Could not build data tree for " .implode(',',$fields) . " and " . implode(",",$forms));
            return array();
        }
        if ($flat) {
            $data = I2CE_DataTree::flattenDataTree( $data );
        } 
        return $data;
    }
    

    
    /**
     * Process the header of an editable node
     * @param I2CE_Template $template
     * @param DOMNode $node;
     * @param DOMNode $head_node;
     */
    protected function processHeaderEditable($template,$node,$head_node) {
        parent::processHeaderEditable($template,$node,$head_node);
        if ( !$node->hasAttribute( "addlink" ) || ! ($href = $node->getAttribute('addlink') )) {
            return;
        }
        if ($node->hasAttribute('addlinktext')) {
            //$add_link = $template->createElement('span');
            $add_link = $template->createElement('a',array('name'=>'add_link'),$node->getAttribute('addlinktext'));
            $head_node->appendChild($add_link);
        } else  {
            $add_link = $template->appendFileByNode('list_add_link.html','span',$head_node);
            if ( !( $add_link instanceof DOMNode)) {
                return;
            }
        }
        $template->setDisplayDataImmediate("add_link",$href,$add_link);
        if ( $node->hasAttribute( "addtask" ) ) {
            $template->setAttribute('task', $node->getAttribute( "addtask" ) ,null,".//a",$add_link);
        }
    }




    
     // /modules/forms/forms/$form/fields/$field/meta/forms =   array(county,district)
     //  selectable forms for this field    // if it does not exist it is $field
     //
     // /modules/forms/forms/$form/fields/$field/meta/display/default/fields = county+district:district+region:[region]:country
     // /modules/forms/forms/$form/fields/$field/meta/display/default/fields = county:district+cssc_region:[cssc_region+cssc_country]:country
     //  if it does not exist, it is "$field"
     //
     //
     // /modules/forms/forms/$form/fields/$field/meta/display/fancy/fields = facility:position
     // /modules/forms/forms/$form/fields/$field/meta/limits/fancy/position = BLAH (status = position_status|open)
     // /modules/forms/forms/$form/fields/$field/meta/limits/fancy/facility =NULL
     // /modules/forms/forms/$form/fields/$field/meta/display/fancy/style = tree
     // /modules/forms/forms/$form/fields/$field/meta/display/fancy/style = list

     /**
      * Set up the default editable display for this form field.
      * @return array of DOMNode
      */
    public function processDOMEditable( $node, $template, $form_node ) {
        $display = 'default';
        if ($form_node->hasAttribute('display')) {
            $t_display = $form_node->getAttribute('display');
            if (strtolower($t_display) != 'true' && strtolower($t_display) != 'false') {
                $display = $t_display;
            }
        }

        $style = $this->getDisplayedStyle($display);        
        $sMethod = 'processDOMEditable_' . $display;
        $dMethod = 'processDOMEditable_' . $style;
        if ($this->_hasMethod($dMethod,true)) {
            return $this->$dMethod($node,$template,$form_node);
        }else if ($this->_hasMethod($sMethod)) {
            return $this->$sMethod($node,$template,$form_node);
        } else {
            return $this->_processDOMEditable($node,$template,$form_node,$display);
        }
        $form_node->setAttribute('display_style',$display);
    }

     /**
      * Set up the default editable display for this form field.
      * @return array of DOMNode
      */    
    protected function _processDOMEditable( $node, $template,  $form_node, $display ) {
        $permissionParser = new I2CE_PermissionParser($template,new I2CE_User());
        $show_hidden = 0;
        if ($form_node->hasAttribute('show_i2ce_hidden') && $permissionParser->hasTask('can_hide_list_members')) {
            $show_hidden = (int) $form_node->getAttribute('show_i2ce_hidden');
	    if ($show_hidden < 0 || $show_hidden > 2) {
		$show_hidden = 0;
	    }
        }
        $form_node->removeAttribute('show_i2ce_hidden');
        if ($form_node->hasAttribute('selectableforms')) {
            $forms = $this->getSelectableForms();
            $t_forms  = explode(':',$form_node->getAttribute('selectableforms'));
            $t_forms = array_intersect($forms,$t_forms);
            if (count($t_forms) > 0) {
                $forms = $t_forms;
            }
            $form_node->removeAttribute('selectableforms');
        } else {
            $forms = $this->getSelectableForms();
        }
        if ($form_node->hasAttribute('show')) {
            $type = $form_node->getAttribute('show');
            $form_node->removeAttribute('show');
        } else {
            $type = 'default';
        }
        $limits = $this->getFormLimits($type);
        $limit_val = false;
        $calculated_limit_val = null;
        if ($form_node->hasAttribute('limit_field') 
            && ($limit_field = $form_node->getAttribute('limit_field'))
            &&  $form_node->hasAttribute('limit_val') 
            && ($limit_val = $form_node->getAttribute('limit_val'))) {
            if ((strpos($limit_val,':')) !== false) {
                list($form,$field) =  array_pad(explode(':',$limit_val),2,''); 
                //var_dump($template->getForm($form,$form_node));
                if (($formObj = $template->getForm($form,$node)) instanceof I2CE_Form && $formObj->getName() == $form && ($fieldObj  = $formObj->getField($field)) instanceof I2CE_FormField) {
                    $calculated_limit_val = $fieldObj->getDBValue();
                }
                $limit_val = $template->getData('DISPLAY',substr($limit_val,1),$form_node);
            } else if (strlen($limit_val) > 0 && $limit_val[0] == '$') {
                $limit_val = $template->getData('DISPLAY',substr($limit_val,1),$form_node);
            } else {
                $calculated_limit_val = $limit_val;
            }
            if (is_scalar($calculated_limit_val)) {
                $new_limits = array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>$limit_field,
                    'style'=>'equals',
                    'data'=>array(
                        'value'=>$calculated_limit_val
                        )
                    );
                $limit_index = $this->getName();
                if (is_array($limits) && array_key_exists($limit_index,$limits) && is_array($limits[$limit_index]) && count($limits[$limit_index]) > 0) {
                    $limits[$limit_index] = array(
                        'operator'=>'AND',
                        'operand' => array(
                            0=>$limits[$limit_index],
                            1=>$new_limits
                            ));
                } else {
                    $limits[$limit_index] = $new_limits;
                }
                $this->setAlternateLimits($limits);
            }
        }
        $orders = $this->getFormOrders($type);
        $style = $this->getDisplayedStyle($display);        
        $method = 'create_DOMEditable_' . $display;
        if (!$this->_hasMethod($method)) {
            $method = 'create_DOMEditable_' . $style;
            if (!$this->_hasMethod($method)) {
                $method =   'create_DOMEditable_' . $this->getDefaultDisplayStyle($type);
            }
        }
        $this->$method($node,$template,$form_node,$show_hidden);
        $this->restoreLimits();
    }


    
    public function __call($method,$params) { 
        if (preg_match('/^processDOMEditable_(.+)$/',$method,$matches)) {
            if ($this->hasDisplay($matches[1])) {
                if (parent::_hasMethod($method)) {
                    return parent::__call($method,$params);//check if there is a fuzzy method first.  if not pass it to the default handler
                } else {
                    $params[] = $matches[1];
                    return call_user_func_array(array($this,'_processDOMEditable'), $params);
                }
            }
        }
        return parent::__call($method,$params);
    }


    public function _hasMethod($method,$getFuzzy = false,$returnErrors = false) {  //this is b/c of the laziness above
        if (preg_match('/^processDOMEditable_(.+)$/',$method,$matches) && $this->hasDisplay($matches[1])) {
            return true;
        }
        return parent::_hasMethod($method,$getFuzzy,$returnErrors);
    }


    /**
     * Creates an ajax set of drop downs that are populated during the page load with
     * web services and as you select each level.
     * @param I2CE_Template $template
     * @param DOMNode $node -- the node that requested this drop down
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.  Defaults to 0
     * @param int $multiple set to true if multiple selections are allowed
     * @returns mixed DOMNode or an array of DOMNodes to add.
     */
    protected function _create_DOMEditable_ajax_list($node, $template, $form_node,$show_hidden = 0, $multiple=false) {
        $ele_name = $this->getHTMLName();
        $formsConfig = I2CE::getConfig()->modules->forms;

        $add_limits = $this->getAdditionalLimits($template,$node,$form_node->getAttribute('limit'));
        $display_style = 'default';
        if ( $form_node->hasAttribute( 'display_style' ) ) {
            $display_style = $form_node->getAttribute('display_style');
        }
        $selected =$this->getDBValue();

        $blank_text = "Select One";
        if ( $form_node->hasAttribute( "blank" ) ) {
            $blank_text = $form_node->getAttribute( "blank" );
        } else {
            $formsConfig->setIfIsSet( $blank_text, "template_text/blank" );
        }
        $load_text = "Loading";
        if ( $form_node->hasAttribute( "loading" ) ) {
            $load_text = $form_node->getAttribute( "loading" );
        } else {
            $formsConfig->setIfIsSet( $load_text, "template_text/loading" );
        }
        $no_results_text = "No results";
        if ( $form_node->hasAttribute( "no_results" ) ) {
            $no_results_text = $form_node->getAttribute( "no_results" );
        } else {
            $formsConfig->setIfIsSet( $no_results_text, "template_text/no_results" );
        }
        $default_text = "Select from below";
        if ( $form_node->hasAttribute( "default_display" ) ) {
            $load_text = $form_node->getAttribute( "default_display" );
        } else {
            $formsConfig->setIfIsSet( $default_text, "template_text/default_ajax_display" );
        }
        $clear_text = "Reset";
        if ( $form_node->hasAttribute( "clear_display" ) ) {
            $clear_text = $form_node->getAttribute( "clear_display" );
        } else {
            $formsConfig->setIfIsSet( $clear_text, "template_text/clear_ajax_display" );
        }

        $disp_fields = $this->getDisplayedFields( $display_style );
        $forms = $this->getSelectableForms();
        $form_limits = $this->getFormLimits( $display_style );
        foreach( $form_limits as $limited_form => $curr_limit ) {
            $max_disp = count($disp_fields);
            for( $i = 0; $i < $max_disp; $i++ ) {
                if ( $disp_fields[$i] == $limited_form && array_key_exists( 'style', $curr_limit )  
                        && ( $curr_limit['style'] == 'equals' || $curr_limit['style'] == 'in' ) ) {
                    $disp_fields = array_slice( $disp_fields, 0, $i+1 );
                    break;
                }
            }
        }

        $onchange = false;
        if ( $node->hasAttribute('onchange') ) {
            $onchange = $node->getAttribute('onchange');
        }
        $two_columns = false;

        $is_single = false;
        if ( count($disp_fields) == 1 ) {
            $is_single = true;
        }
        if ( $multiple && !$is_single ) {
            I2CE::raiseError( "Ajax list display currently isn't supported with MAP_MULT and multiple fields.  You should use checkbox_tree instead." );
            return $this->create_DOMEditable_list( $node, $template, $form_node,$show_hidden );
        }
 
        $js = array();
        $ff = '';
        if ( $this->getContainer() instanceof I2CE_Form ) {
            $ff = $this->getContainer()->getName() . '+' . $this->getName();
        } elseif ( $this->optionsHasPath( "meta/relationship" ) ) { 
            $ff = '[' . $this->getOptionsByPath( "meta/relationship" ) . '+' . $this->getName() . ']';
        }
  
        if ( $is_single ) {
            $defaultNode = $template->createElement( 'span', array( 'id' => "default_${ele_name}", 'style' => 'display: none;' ), $this->getDBValue() );
            $node->appendChild( $defaultNode );
        } else {
            $hiddenNode = $template->createElement( 'input', array( 'type' => 'hidden', 'name' => $ele_name, 
                        'id' => $ele_name, 'value' => $selected ) );
            if ( $selected == '' ) {
                $default_display = $default_text;
            } else {
                $default_display = $this->getDisplayValue();
            }
            $displaySection = $template->createElement( 'span', array( 'class' => 'field_selection'.($this->hasInvalid()?' error':'') ) );
            $displayNode = $template->createElement( 'span', array( 'id' => $ele_name . '_display' ), 
                    $default_display );
            $this->setElement( $hiddenNode );
    
            $clearNode = $template->createElement( 'span', array( 'id' => $ele_name . '_clear', 
                        'onclick' => "resetAjaxList('$ele_name');", 'style' => 'float: right; vertical-align: text-top; font-style: italics; display: ' . ($selected == ''?'none':'inline') . ';' ), 
                    ' - ' . $clear_text );

            $node->appendChild( $hiddenNode );
            $displaySection->appendChild( $displayNode );
            $displaySection->appendChild( $clearNode );
            $node->appendChild( $displaySection );

            $defaults = $_POST;
            if ( is_array( $defaults ) && count( $defaults ) > 0 ) {
                $ele_array = preg_split( '/(\[|\]\[|\])/', $ele_name);
                if ( !end($ele_array) ) {
                    array_pop( $ele_array );  // Remove the last blank from this
                }
                if ( count( $ele_array ) > 0 ) {
                    $ele_array[0] = 'ajax_list_' . $ele_array[0];
                    foreach ( $ele_array as $ele_part ) {
                        if ( array_key_exists( $ele_part, $defaults ) ) {
                            $defaults = $defaults[$ele_part];
                        } else {
                            // Can't find them so no defaults
                            $defaults = array();
                            break;
                        }
                    }
                } else {
                    $defaults = array();
                }
            }
    
            foreach( $defaults as $key => $value ) {
                if ( !is_scalar($value) ) {
                    I2CE::raiseError( "Invalid default value for $key in ajax list: " . print_r($value,true));
                    $value = '';
                }
                $defaultNode = $template->createElement( 'span', array( 'id' => "default_${ele_name}_${key}", 'style' => 'display: none;' ), $value );
                $node->appendChild( $defaultNode );
            }

            $disp_select_name = '';
            $choose = array();
            $first_forms = array();
            if ( /*count($forms) == 1 && */count( $disp_fields ) > 2 ) {
                // If more than 2 display fields listed then check to see if the link from first to 2nd/3rd/etc.
                // can have more than one link.  e.g. facility+location can be county or district, etc.
                $first = $disp_fields[0];
                // Check to see if the field is given here or determined by the next level,
                // e.g. facility+location:county:district or county:district
                $first_form = '';
                $first_field = '';
                if ( strpos( $first, '+' ) !== false ) {
                    list( $first_form, $first_field ) = explode( '+', $first, 2 );
                } else {
                    $first_form = $first;
                    $second = $disp_fields[1];
                    if ( strpos( $second, '+' ) !== false ) {
                        list( $second_form, $second_field ) = explode( '+', $second, 2 );
                        $first_field = $second_form;
                    } else {
                        $first_field = $second;
                    }
                }
                // Now determine what the first link can link to.
                $first_obj = I2CE_FormFactory::instance()->createContainer( $first_form );
                if ( $first_obj instanceof I2CE_Form && 
                        ( $first_field_obj = $first_obj->getField( $first_field ) ) instanceof I2CE_FormField_MAPPED ) {
                    $first_forms = $first_field_obj->getSelectableForms();
                    if ( count( $first_forms ) > 1 ) {
                        $two_columns = true;
                        $formsConfig->setIfIsSet( $disp_select_name, "forms/" . $forms[0] . "/display" );
                        $choose = array( 'form' => $first_form, 'field' => $first_field, 'ff' => $first_form.'+'.$first_field );
                    }
                }
            }

            if ( count($choose) > 0 ) {
                $acNode = $template->createElement('input', array( 'type' => 'text', 'name' => "ac-$ele_name", 'id' => "ac-$ele_name" ) );
                $node->appendChild( $acNode );
                $js[] = "window.addEvent('domready', function() {\n"
                    ."  new Autocompleter.Request.JSON( 'ac-$ele_name', 'web-services/lists/".$choose['form']."/"
                    .$ff."?array=1', {'selectMode' : false, 'filterSubset' : true, 'minLength' : 3, 'postVar' : '".urlencode($choose['ff'].'+name')."[contains]',\n"
                    ."    'injectChoice' : function(token) {\n"
                    ."      for( key in token.data ) {\n"
                    ."        var choice = new Element('li', {'html' : this.markQueryValue(token.data[key])});\n"
                    ."        choice.inputValue = key;\n"
                    ."        this.addChoiceEvents(choice).inject(this.choices);\n"
                    ."      }\n"
                    ."    },\n"
                    ."    'onSelection' : function( element, selected, value, input ) {\n"
                    ."      resetAjaxList('$ele_name');\n"
                    ."      $('$ele_name').set('value', value);\n"
                    ."      $('${ele_name}_display').set('text', selected.get('text'));\n"
                    ."      $('${ele_name}_clear').show('inline');\n"
                    ."      element.set('value', '' );\n"
                    ."    }\n"
                    ."  } );\n"
                    ."} );\n";
            }

        }

        $first = true;
        $count = 1;

        $disp_fields = array_reverse( $disp_fields );
        foreach( $disp_fields as $disp_idx => $disp_field ) {
            if ( $two_columns && $disp_idx == count($disp_fields)-1 ) {
                break;
            }
            if ( $disp_field[0] == '[' && $disp_field[strlen($disp_field)-1] == ']' ) {
                continue;
            }
            $block_name = $ele_name . '_block_' . $disp_field . '_' . $disp_idx;
            $opts = array( 'id' => $block_name, 'style' => 'display: block;' );
            if ( strpos( $disp_field, '+' ) === false ) {
                $disp_form = $disp_field;
            } else {
                list( $disp_form, $not_used ) = explode( '+', $disp_field, 2 );
            }
            $disp_form_name = '';
            $formsConfig->setIfIsSet( $disp_form_name, "forms/$disp_form/display" );
            $load_disp = vsprintf( $load_text, array( $disp_form_name ) );
            $spanNode = $template->createElement( 'span', $opts );
            $selectOpts = array( 'name' => 'ajax_list_' . $ele_name . '[' . $disp_field . ']', 
                        'id' => $ele_name . '_' . $disp_field . '_' . $disp_idx, 'disabled' => 'disabled' );
            if ( !$first ) {
                $selectOpts['style'] = 'display: none;';
            }
            if ( $is_single ) {
                $selectOpts['name'] = $ele_name;
                $selectOpts['id'] = $ele_name;
                if ( $multiple ) {
                    $size = '5';
                    if ($form_node->hasAttribute('size')) {
                        $size = $form_node->getAttribute('size');
                        $form_node->removeAttribute('size');
                    }
                    $selectOpts['multiple'] = 'multiple';
                    $selectOpts['size'] = $size;
                    $selectOpts['name'] .= '[]';
                }
            }
            if ( $this->hasInvalid() ) {
                $selectOpts['class'] = 'error';
            }
            //if ( count($choose) > 0 ) {
            $next_field = '';
            $next_field_suffix = '';
            $increment = 1;
            while ( array_key_exists( $disp_idx+$increment, $disp_fields ) ) {
                $next_field = $disp_fields[$disp_idx+$increment];
                $next_field_suffix = $next_field . '_' . ($disp_idx+$increment);
                if ( $next_field[0] == '[' && $next_field[strlen($next_field)-1] == ']' ) {
                    $increment++;
                } else {
                    break;
                }
            }
            if ( ($pluspos = strpos( $next_field, '+' )) ) {
                $next_field_form = substr($next_field, 0, $pluspos);
            } else {
                $next_field_form = $next_field;
            }
            if ( $is_single ) {
                if ( $onchange ) {
                    $selectOpts['onchange'] = $onchange;
                }
            } else {
                $js[] = "  $('${ele_name}_${disp_field}_${disp_idx}').addEvent('change', function() {\n"
                    . ($next_field != '' && ($two_columns?$disp_idx<count($disp_fields)-2:true) ? "    populateAjaxList( this, '${ele_name}', '${next_field_suffix}', '$ff', '" 
                            . $next_field . "', '" . urlencode($disp_field) . "', this.options[this.selectedIndex].value );\n"
                            : '')
                    . (in_array( $disp_form, $forms ) ? "    selectAjaxList( this, '$ele_name' );\n"
                            : '' )
                    . "  });\n";
                if ( in_array( $disp_form, $forms ) ) {
                    if ( $onchange ) {
                        $selectOpts['onchange'] = $onchange;
                    }
                    if ( array_key_exists( 'class', $selectOpts ) ) {
                        $selectOpts['class'] .= ' selectable';
                    } else {
                        $selectOpts['class'] = 'selectable';
                    }
                }
            }
            /*
               $selectOpts['onchange'] = "populateAjaxList( '${ele_name}_${count}', '', '" 
               . $choose['form'] . "', '" . urlencode($choose['ff']) . "', this.options[this.selectedIndex].value ); " 
               . "populateAjaxList( '${ele_name}_${next_field}', '$ff', '" 
               . $next_field . "', '" . urlencode($disp_field) . "', this.options[this.selectedIndex].value );";
             */
            //}
            $nameNode = $template->createElement( 'span', array( 'id' => "name_${ele_name}" . ($is_single?'':"_${disp_field}"), 
                        'style' => 'display: none' ), $disp_form_name );
            $spanNode->appendChild( $nameNode );
            $selectNode = $template->createElement( 'select', $selectOpts );
            $selectNode->appendChild( $template->createElement( "option", array( 'value' => '' ), $load_disp ) );
            if ( $first ) {
                $js[] = "   setupAjaxList( '$ele_name', '$ff', '$disp_field', '" . (count($disp_fields)-1) . "', '"
                    . ($multiple?'':$blank_text) . "', '$load_text', '$no_results_text', '$default_text', " 
                    . ($is_single?'true':'false'). ($display_style!='default'?", '$display_style'":'')." );";
                $first = false;
            }
            $spanNode->appendChild( $selectNode );
            if ( $is_single ) {
                $this->setElement( $selectNode );
            }
            if ( $two_columns && in_array( $disp_form, $first_forms ) ) {
                $nameNode = $template->createElement( 'span', array( 'id' => "name_${ele_name}_${count}", 
                            'style' => 'display: none', 'onchange' => $onchange ), $disp_select_name );
                $spanNode->appendChild( $nameNode );
                $chooseNode = $template->createElement( 'select', array( 'name' => 'ajax_list_' . $ele_name . '[' . $count . ']',
                            'id' => $ele_name . '_' . $count, 'disabled' => 'disabled', 
                            'class' => 'selectable' . ($this->hasInvalid()?' error':''),
                            'style' => 'display: none; width: 224px; margin-left: 30px;' ) );
                $chooseNode->appendChild( $template->createElement( "option", array( 'value' => '' ), $load_text ) );
                $spanNode->appendChild( $chooseNode );
                $js[] = "  $('${ele_name}_${count}').addEvent('change', function() {\n"
                    . "    selectAjaxList( this, '$ele_name', '$count' );\n"
                    . "  });\n";
                $js[] = "  $('${ele_name}_${disp_field}_${disp_idx}').addEvent('change', function() {\n"
                    /*
                    . "    populateAjaxList( this, '${ele_name}', '${count}', '$ff', '" 
                    . $choose['form'] . "', '" . urlencode($disp_field) . "', this.options[this.selectedIndex].value );\n" 
                    . "    populateAjaxList( this, '${ele_name}', '${count}', '', '" 
                    . $choose['form'] . "', '" . urlencode($choose['ff']) . "', this.options[this.selectedIndex].value );\n" 
                    */
                    . "    populateAjaxList( this, '${ele_name}', '${count}', '$ff', '" 
                    . $choose['form'] . "', '" . urlencode($choose['ff']) . "', this.options[this.selectedIndex].value );\n" 
                    . "  });\n";
            }

            //$spanNode->appendChild( $template->createElement( 'br' ) );
            $node->appendChild( $spanNode );
            $count++;
        }

        $template->addHeaderLink( 'mootools-core.js' );
        $template->addHeaderLink( 'mootools-more.js' );
        $template->addHeaderLink( 'Observer.js' );
        $template->addHeaderLink( 'Autocompleter.js' );
        $template->addHeaderLink( 'Autocompleter.css' );
        $template->addHeaderLink( 'Autocompleter.Request.js' );
        $template->addHeaderLink( 'I2CE_AjaxList.js' );
        $template->addHeaderText( "window.addEvent('domready', function() {\n" . implode( "\n", $js ) . " });\n",
                'script', 'ajax_list');

        /*
        $selectNode = $template->createElement('select',array( 'name'=>$this->getHTMLName()));
        $attrs = array('id','class');
        foreach ($attrs as $attr) {
            if ($form_node->hasAttribute($attr)) { 
                $selectNode->setAttribute($attr,$form_node->getAttribute($attr));
            }                
        }
        $this->setElement($selectNode);
        if ( $form_node->hasAttribute( "blank" ) ) {
            $blank_text = $form_node->getAttribute( "blank" );
        } else {
            $blank_text = "Select One";
            I2CE::getConfig()->setIfIsSet( $blank_text, "/modules/forms/template_text/blank" );
        }
        $selectNode->appendChild( $template->createElement( "option", array( 'value' => '' ), $blank_text ) );
        foreach ($list as $d) {
            $attrs = array('value'=>$d['value']);
            if ($d['value'] == $selected) {
                $attrs['selected'] = 'selected';
            }            
            $selectNode->appendChild($template->createElement('option', $attrs, $d['display']));
        }
        $node->appendChild($selectNode);
        */
     }
 


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
