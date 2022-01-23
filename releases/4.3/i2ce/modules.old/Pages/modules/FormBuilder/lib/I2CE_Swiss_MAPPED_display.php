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
* Class I2CE_Swiss_MAPPED_display
* 
* @access public
*/


class I2CE_Swiss_MAPPED_display extends I2CE_Swiss {
    

    public function processValues($vals) {
	if (array_key_exists('fields',$vals)) {
            $this->setField('fields',$vals['fields']);
        }
	if (array_key_exists('style',$vals)) {
            $this->setField('style',$vals['style']);
        }
	if (array_key_exists('display_report',$vals)) {
            $this->setField('display_report',$vals['display_report']);
        }

	return parent::processValues($vals);
    }

    protected function getChildType($child) {
        if ($child =='orders') {
            return 'MAPPED_orders';
        }
        return parent::getChildType();
    }

    protected function getTemplate() {
        return 'swiss_mapped_display.html';
    }

    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(),'div',$content_node)) instanceof DOMNode) {
            I2CE::raiseError("Could not load " . $this->getTemplate());
            return false;
        }
        if ( !($this->displayMain($mainNode,$transient_options,$action))) {
            return false;
        }
        if (! ($this->displayAjax($mainNode,$transient_options,$action))) {
            return true;
        }
        return true;
    }

    protected function displayAjax($mainNode,$transient_options,$action) {
        if ( ($ordersChild = $this->getChild('orders',true)) instanceof I2CE_Swiss
             && ( $ordersNode = $this->template->getElementById('orders',$mainNode)) instanceof DOMNode
            ) {
            $ordersChild->addAjaxLink('orders_link','form_container', 'orders_ajax' ,$ordersNode,$action, $transient_options);
        }
        return true;
    }

    protected function getStyles() {
        return array(); //needs to be set by the subclasses
    }

    protected function displayMain($mainNode,$transient_options,$action) {
        $this->template->setDisplayDataImmediate('fields',$this->getField('fields'));
        if ( ( $styleNode = $this->template->getElementByName('style',0,$mainNode)) instanceof DOMNode) {
            $selected = $this->getField('style');
            if (!$selected
                && ($fieldObj = $this->getFieldObj()) instanceof I2CE_FormField_MAPPED
                ) {
                $selected = $fieldObj->getDefaultDisplayStyle('default');
            }
            $styles = $this->ensureStyles($this->getStyles());
            foreach ($styles as $style) {
                $attrs = array('value'=>$style);
                if ($selected == $style) {
                    $attrs['selected'] = 'selected';
                }
                $styleNode->appendChild($this->template->createElement('option',$attrs,$style));
            }
        }
        if ( ( $reportNode = $this->template->getElementByName('display_report',0,$mainNode)) instanceof DOMNode) {
            $selected = $this->getField('display_report');
            $reports = I2CE::getConfig()->getKeys("/modules/CustomReports/reports");
            foreach ($reports as $report) {
                $attrs = array('value'=>$report);
                if ($selected == $report) {
                    $attrs['selected'] = 'selected';
                }
                $reportNode->appendChild($this->template->createElement('option',$attrs,$report));
            }
        }

        $this->renameInputs(array('style','fields','display_report'),$mainNode);
        return true;
        
    }


    protected function getFieldObj() {
        if (($p = $this->parent) instanceof I2CE_Swiss_MAPPED_displays
            && ($gp = $p->parent) instanceof I2CE_Swiss_MAPPED_meta
            && ($ggp = $gp->parent) instanceof I2CE_Swiss_FormField
            && I2CE_MagicDataNode::checkKey($formfield = $ggp->getField('formfield'))
            && is_scalar($class = I2CE::getConfig()->traverse("/modules/forms/FORMFIELD/$formfield",false,true))
            && class_exists($class)
            ) {
            return new $class($ggp->name,array());
        }
        return null;
    }

    protected function ensureStyles($styles) {
        $ensured =array();
        if (is_array($styles)
            && ($fieldObj = $this->getFieldObj()) instanceof I2CE_FormField
            ) {
            foreach ($styles as $style) {
                $method ='checkStyle_' . $style;
                if ( $fieldObj->_hasMethod($method,true)
                     && ! $fieldObj->$method()
                    ) {
                    continue;
                }
                $ensured[] = $style;
            }
        }
        return $ensured;

    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
