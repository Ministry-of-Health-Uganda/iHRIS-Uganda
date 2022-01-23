<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
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
* @subpackage form-builder
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_MAPPED_orders
* 
* @access public
*/


abstract class I2CE_Swiss_field_orders extends I2CE_Swiss {


    public function processValues($vals) {
        //this function should be overridden by the sub-class to store in magic data as needed.
        //example $vals array is:
        // Array
        //     (
        //     [enabled] => Array
        //     (
        //             [start_date] => 1
        //             [persons_involved] => 1
        //         )
        //     [display_order] => accident_type,end_date,followup,start_date,occurence_date,persons_involved
        //         )
        //)

        return true;
    }

    //return the form name for which we wish to sort fields ons (e.g. person)
    abstract public function getFormName();

    public function getFieldNames() {
        if (!($formObj = I2CE_FormFactory::instance()->createContainer($this->getFormName())) instanceof I2CE_Form) {
            I2CE::raiseError("Bad form object");
            return array();
        }
        $fields = $formObj->getFieldNames();
        if (!is_array($fields)) {
            $fields =array();
        }
        return $fields;
    }

    protected function getContainerTemplate() {
        return 'swiss_field_sorter.html';
    }

    protected function getFieldTemplate() {
        return 'swiss_field_sorter_field.html';
    }

    public function getOrders() {
        return $this->storage->getAsArray();
    }


    public function displayValues($contentNode, $transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode( $this->getContainerTemplate(),'div',$contentNode)) instanceof DOMNode
            || !($fieldsNode = $this->template->getElementByID('fields_list',$mainNode)) instanceof DOMNode
            ) {
            I2CE::raiseError("Could not load template");
            return false;
        }
        $fields = $this->getSortedFields();
        $orders = $this->getOrders();
        $inputs = array();
        foreach ($fields as $field) {
            if ( !($fieldNode =  $this->template->appendFileByNode($this->getFieldTemplate(),'div',$fieldsNode)) instanceof DOMNode) {
                continue;
            }
            $this->template->setDisplayDataImmediate('name',$field,$fieldNode);
            $this->template->setDisplayDataImmediate('field',$field,$fieldNode);

            if ( ($fieldNode  =$this->template->getElementByName('enabled',0,$fieldNode)) instanceof DOMNode) {
                if (in_array($field,$orders)) {
                    $fieldNode->setAttribute('checked','checked');
                }
                $input = 'enabled[' . $field . ']';
                $fieldNode->setAttribute('name',$input);
                $inputs[] = $input;
            }
            
        }        
        $this->renameInputs($inputs,$fieldsNode);
        $this->addFieldSorter($fields,$fieldsNode);
        return true;

    }

    public function getAjaxJSNodes() {
        return parent::getAjaxJSNodes() . ',sortable';
    }

    public function getSortedFields() {
        $fields = $this->getFieldNames();
        $orders = $this->getOrders();
        ksort($orders);
        $t_fields = array();
        foreach($orders as $field) {
            if (!in_array($field,$fields)) {
                continue;
            }
            $t_fields[] = $field;
        }
        $fields = array_diff($fields,$t_fields);        
        return array_merge($t_fields , $fields);
    }


    protected function addFieldSorter($fields,$mainNode) {
        $this->template->addHeaderLink('mootools-core.js');
        $this->template->addHeaderLink('mootools-more.js');
        $inputNode =  $this->template->createElement('input',array('name'=>'display_order','type'=>'hidden','value'=>implode(',',$fields)));
        $this->template->appendNode($inputNode,        $mainNode);
        $ret = $this->renameInputs('display_order',$inputNode);
        if (!array_key_exists('display_order',$ret)) {
            I2CE::raiseError("Could not add sorter");
            return;
        }
        $inputNode->setAttribute('id',$ret['display_order']);
        $fields_id = 'fields_list:' . $this->path;
        $this->template->reIdNodes('fields_list', $fields_id, $mainNode);
        $display_order_name = $ret['display_order'];
        $js="
window.addEvent('domready',function() {
    var displayed_field_sorter = $('{$fields_id}');    
    var displayed_field_sort = $('{$display_order_name}'); 
    if (displayed_field_sorter && displayed_field_sort ) { 
       var displayed_field_sortOptions = {
            handle:'span.sortablehandle',
            onComplete: function() {
                var order = new Array();;
                displayed_field_sorter.getElements('.sortme').each(function(e) {

                    order.push(e.get('text'));
                });
                displayed_field_sort.setProperty('value',order.join(','));
            },
            opacity: 0.5
       };
       new Sortables(displayed_field_sorter,displayed_field_sortOptions);

    }
});
";
        $this->template->addHeaderText($js,'script','sortable');

    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
