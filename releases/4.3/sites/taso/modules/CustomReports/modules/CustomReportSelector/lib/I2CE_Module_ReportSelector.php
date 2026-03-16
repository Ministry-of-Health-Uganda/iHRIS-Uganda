<?php
/**
* © Copyright 2011 IntraHealth International, Inc.
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
* @subpackage customreports
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.11
* @since v4.0.11
* @filesource 
*/ 
/** 
* Class I2CE_Module_ReportSelector
* 
* @access public
*/


class I2CE_Module_ReportSelector extends I2CE_Module{

    public static function getMethods() {
        return array(
            'I2CE_Page->addReportSelector'=>'addReportSelector',
            'I2CE_Template->addReportSelector'=>'addReportSelector'
            );
    }

    public static function getHooks() {
        return array(
            'post_page_prepare_display_I2CE_Template'=> 'writeOutJS'
            );
    }

    protected $selectors = array();


    /**
     * Adds a report selector
     * @param DOMNode $node  The node we are adding the selector on.
     * @param array $options.  Array ofoptions.  should include keys: 'printf', 'printfargs', 'reportiew' or they should be set as attributes on the node.   'reportform' the form in the report view we want to select (Defaults to primary_form)
     * other optional keys are 'updateval' and 'updatedisp' which are names of elements to update the name and id of.  If not set, then it is 'updateval'=$id:value and 'updatedisp=$id:display
     * and 'value' which contains the current db value
     * and 'display' which contains the current display value
     */
    public function addReportSelector($obj,$node,$options= array()) {
        if ($obj instanceof I2CE_Page) {
            $template = $obj->getTemplate();
        } else {
            $template = $obj;
        }
        if (!$template instanceof I2CE_Template) {
            return false;
        }
        if (!$node instanceof DOMElement) {
            return false;
        }
        if (!$node->hasAttribute('id') || !  ($id = $node->getAttribute('id'))) {
            return false;
        }
        if (!is_array($options) ) {
            return false;
        }
        if ($node->hasAttribute('reportview')) {
            $options['reportview'] = $node->getAttribute('reportview');
            $node->removeAttribute('reportview');
        }
        if ($node->hasAttribute('reportform')) {
            $options['reportform'] = $node->getAttribute('reportform');
            $node->removeAttribute('reportform');
        }
        if ($node->hasAttribute('printf')) {
            $options['printf']=$node->getAttribute('printf');
            $node->removeAttribute('printf');
        }
        if ($node->hasAttribute('printfargs')) {
            $options['printfargs'] =explode(',',$node->getAttribute('printfargs'));
            $node->removeAttribute('printfargs');
        }
        if ($node->hasAttribute('contentid')) {
            $options['contentid'] = $node->getAttribute('contentid');
            $node->removeAttribute('contentid');
        }

        if (!array_key_exists('reportview',$options) || ! ($reportview = $options['reportview'])
            || !I2CE::getConfig()->is_parent("/modules/CustomReports/reportViews/$reportview")
            || !array_key_exists('printf',$options)  || !$options['printf']
            || !array_key_exists('printfargs',$options) || !is_array($options['printfargs'])
            || count($options['printfargs']) == 0) {
            return false;
        }
        if (!array_key_exists('value',$options)) {
            $options['value'] = '';
        }
        if (!array_key_exists('display',$options)) {
            $options['display'] ='';
        }
        if (!array_key_exists('style',$options)) {
            $options['style'] ='default';
        }
        if (!is_string($options['display']) || strlen(trim($options['display'])) == 0 ){
            I2CE::getConfig()->setIfIsSet($display['config'],"/modules/CustomReports/displays/Selector/display_options/select_value");
            if (!is_string($options['display']) || strlen(trim($options['display'])) == 0 ){
                $options['display']  = 'Select Value';
            }
        }
        if (!array_key_exists('value',$options)) {
            $options['value'] ='';
        }
        if ( ! ($mainNode = $template->loadFile("reportselector.html","div")) instanceof DOMElement) {
            return false;
        }        
        if ( !($windowNode = $template->getElementByName("window",0,$mainNode)) instanceof DOMNode) {
            return false;
        }
        if ( !($dispNode = $template->getElementByName("display",0,$mainNode)) instanceof DOMNode) {
            return false;
        }
        if ( !($valNode = $template->getElementByName("value",0,$mainNode)) instanceof DOMNode) {
            return false;
        }
        if ( !($selectNode = $template->getElementByName("selector",0,$mainNode)) instanceof DOMNode) {
            return false;
        }
        $contentNode = $template->appendFileByName("reportselector_content.html","div","content",0,$mainNode);
        if (!$contentNode instanceof DOMElement) {
            return false;
        }
        $node->appendChild($mainNode);

        //we are good to go
        $windowNode->setAttribute('id',$id . ':window');
        $dispNode->setAttribute('id',$id . ':display');
        $template->addClass($dispNode, $id . '_toggle');        
        $template->addTextNode($dispNode,$options['display']);
        $valNode->setAttribute('id',$id . ':value');
        $valNode->setAttribute('value',$options['value']);
        $valNode->setAttribute('name',$id);
        $jsSelect = "if (reportselectors && reportselectors['" . addslashes($id) . "']) {reportselectors['" . addslashes($id) . "'].show(); return false;} else { return false;}";
        $selectNode->setAttribute('onClick',$jsSelect);
      
        if (!array_key_exists('allow_clear',$options)) {
            $options['allow_clear'] = true;
        }
        if ( ($clearNode = $template->getElementByName("clear_value_button",0,$mainNode)) instanceof DOMNode) {
            if ($options['allow_clear']) {
                $jsClear = "if (reportselectors && reportselectors['" . addslashes($id) . "']) {reportselectors['" . addslashes($id) . "'].clear(); return false;} else { return false;}";
                $clearNode->setAttribute('onClick',$jsClear);
            } else {
                $template->removeNode($clearNode);
            }
        }

        $contentNode->setAttribute('id',$id . ':content');
        $this->selectors[$id] = $options;



        return true;
    }

    public function writeOutJS($page) {
        if (count($this->selectors) == 0) {
            return;
        }
        if (!$page instanceof I2CE_Page) {
            return;
        }
        $template= $page->getTemplate();
        if (!$template instanceof I2CE_Template) {
            return;
        }
        $template->addHeaderLink('FormWorm.css');
        $template->addHeaderLink('jumper.css');
        $template->addHeaderLink('reportSelector.css');
        $template->addHeaderLink('mootools-core.js');
        $template->addHeaderLink('mootools-more.js');
        $template->addHeaderLink("getElementsByClassName-1.0.1.js");
        $template->addHeaderLink('I2CE_ClassValues.js');
        $template->addHeaderLink('I2CE_Window.js');
        $template->addHeaderLink('stubs.js');
        $template->addHeaderLink('I2CE_ToggableWindow.js');
        $template->addHeaderLink('I2CE_WindowSelect.js');
        /* begin:this shouldn't realy be here */
        $template->addHeaderLink("I2CE_TreeSelect.js");       
        $template->addHeaderLink("Tree.css");       
        $template->addHeaderLink('Observer.js');
        $template->addHeaderLink('Autocompleter.js');
        $template->addHeaderLink('Autocompleter.css');
        $template->addHeaderLink('I2CE_TreeSelectAutoCompleter.js');
        /* end:this shouldn't realy be here */

        $js = "var reportselectors = new Array(); \nif (window.addEvent) {\n\twindow.addEvent('load',function(e) {\n";
        foreach ($this->selectors as $id=>$options) {
            if (!array_key_exists('reportform',$options)) {
                $options['reportform'] = 'primary_form';
            }
            $jsoptions = array(
                'printf'=>$options['printf'],
                'printfargs'=>implode(',',$options['printfargs']),
                'reportform'=>$options['reportform'], 
                'reportview' =>$options['reportview'],
                'contentid' =>$id . ':' . $options['contentid'],
                'style' => $options['style'],
                'select_id'=>$id
                );
            $toggleclass = $id . '_toggle';
            $js.= "\t\t" . 'reportselectors[\'' . addslashes($id) . '\'] = new I2CE_WindowSelect("'  . addslashes($id . ':window')  . '","' . addslashes($toggleclass) . '",' .  json_encode($jsoptions, JSON_FORCE_OBJECT) . ');';
        }
        $js .=  "\n\t\t}\n\t)\n}\n";
        $template->addHeaderText($js,'script','reportselectors');
        return true;
    }





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
