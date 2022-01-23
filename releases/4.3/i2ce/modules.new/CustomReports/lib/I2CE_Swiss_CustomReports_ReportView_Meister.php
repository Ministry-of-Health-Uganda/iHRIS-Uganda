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
* @version v4.0.9
* @since v4.0.9
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_CustomReports_ReportView_Meister
* 
* @access public
*/


abstract class I2CE_Swiss_CustomReports_ReportView_Meister extends I2CE_Swiss_CustomReports_ReportView_Base{

    protected $parent_fields = null;
    public function getAllParentFields($get_my_children) {
        if (!is_array($this->parent_fields)) {
            $parent_fields = array();
            if( (($parent = $this->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Merges)              
                && (($gparent = $parent->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Field)               
                &&  (($ggparent = $gparent->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Fields)
                && (($gggparent = $ggparent->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Meister)) {
                $parent_fields = $gggparent->getAllParentFields(true);
            }
            if (!$get_my_children) {
                $child_fields = array();
            } else {
                $child_fields = $this->getChildFields();
            }
            $this->parent_fields = array_merge($parent_fields, $child_fields);
        }
        return $this->parent_fields;
    }

    public function getChildFields($only_enabled_in_report = true) {
        $swissFields = $this->getChild('fields',true);
        if (! $swissFields instanceof I2CE_Swiss_CustomReports_ReportView_Fields) {
            return array();
        }
        $fields = array();
        foreach ($swissFields as $field=>$swissField) {
            if (!$swissField instanceof I2CE_Swiss_CustomReports_ReportView_Field) {
                continue;
            }
            if ($only_enabled_in_report && !$swissField->isEnabledInReport()) {
                continue;
            }
            $fields[$swissField->getPath()] = $swissField;
        }
        return $fields;
    }


    public function sortFields(&$fields) {
        $orders = $this->getOrder();
        $t_fields = array();
        foreach($orders as $order) {
            //first put all the fields that have been ordered
            if (!array_key_exists($order,$fields)) {
                continue;
            }
            $t_fields[$order]= $fields[$order];
            unset($fields[$order]);
        }
        while (count($fields) > 0) {
            //now put the remaining fields 
            reset($fields);
            $key = key($fields);
            $t_fields[$key] = array_shift($fields);
        }
        $fields = $t_fields;
    }

    public function setOrder($order) {
        if (is_string($order)) {
            $order = explode(',',$order);
        }
        if (!is_array($order)) {
            $order = array();
        }
        foreach ($order as &$ord) {
            $ord = trim($ord);
        }
        $this->setField('display_order',implode(',',$order));
        return true;
    }

    public function getOrder($as_array = true) {
        if ($this->hasField('display_order')) {
            if ($as_array) {
                return explode(",",$this->getField('display_order'));
            } else {
                return $this->getField('display_order');
            }
        } else {
            if ($as_array) {
                return array();
            } else {
                return false;
            }
        }
    }


    public function processValues($values) {
        if (array_key_exists('display_order',$values)) {            
            $this->setOrder($values['display_order']);
        }
        return true;
    }
    abstract public function getReport();
    public function getChildType($child) {
        switch ($child) {
        case 'fields':
            return 'CustomReports_ReportView_Fields';
        default:
            return parent::getChildType($child);
        }
    }

    protected function displayFields($contentNode,$transient_options,$action) {
        $swissFields = $this->getChild('fields',true);
        if ($swissFields instanceof I2CE_Swiss_CustomReports_ReportView_Fields) {
            $fieldHeaders = array();
            foreach ($swissFields as $field=>$swissField) {
                $fieldHeaders[$field] = $swissField->getHeader();
            }
            $this->sortFields($fieldHeaders);                       
            $fieldsNode = $this->template->getElementById('fields_fill', $contentNode);
            if (!$fieldsNode instanceof DOMNode) {
                I2CE::raiseError("Dont know where to put fields");
                return false;
            }
            $transient_options['field_order'] = array_keys($fieldHeaders);
            $swissFields->addLink('fields_content','fields_fill',$contentNode,$action,$transient_options);
            $this->addFieldSorter($fieldHeaders,$fieldsNode);
        }
        return true;
    }


    public function getAjaxJSNodes() {
        return parent::getAjaxJSNodes() . ',sortable';
    }




    protected function addFieldSorter($fieldHeaders,$mainNode) {
        $this->template->addHeaderLink('mootools-core.js');
        $this->template->addHeaderLink('mootools-more.js');
        $inputNode =  $this->template->createElement('input',array('name'=>'display_order','type'=>'hidden','value'=>implode(',',array_keys($fieldHeaders))));
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
