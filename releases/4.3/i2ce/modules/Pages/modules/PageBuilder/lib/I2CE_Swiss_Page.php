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
* @subpackage page
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_Page
* 
* @access public
*/


class I2CE_Swiss_Page extends I2CE_Swiss {

    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        I2CE::raiseError("Received:" . print_r($vals,true));
        if (array_key_exists('style',$vals)) {
            $this->setField('style',$vals['style']);	   
        }
	
	return true;
    }

    protected function getTemplate() {
        return 'swiss_page.html';
    }

    protected function getArgsHandler() {
        return 'PageArgs';
    }

    protected function getChildType($child) {
        if ($child =='args') {
            return $this->getArgsHandler();
        }
        return parent::getChildType($child);
    }

    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(),'div',$content_node)) instanceof DOMNode) {
            return false;
        }
        if (! ($this->displayMeta($mainNode,$transient_options,$action))) {
            return false;
        }
        if ( ($argsChild = $this->getChild('args',true)) instanceof I2CE_Swiss_PageArgs
             && ( $argsNode = $this->template->getElementById('args',$mainNode)) instanceof DOMNode
            ) {
            $argsChild->addAjaxLink('args_link','args_container', 'args_ajax' ,$argsNode,$action, $transient_options);
        }
        return true;
    }

    protected function displayMeta($mainNode,$transient_options,$action) {
        if ( ($styleNode = $this->template->getElementByName('style',0,$mainNode)) instanceof DOMNode) {
            $styles = I2CE::getConfig()->getKeys("/I2CE/template/page_styles");
            $selected_style = $this->getField('style');
            foreach ($styles as $style) {
                $attrs = array('value'=>$style);
                if ( ($style == $selected_style && $selected_style != '' ) || ($style == 'shell' && $selected_style == '') ) {
                    $attrs['selected'] = 'selected';
                } 
                $styleNode->appendChild($this->template->createElement('option',$attrs,$style));
            }
            $this->renameInputs('style',$mainNode);
        }
        if (($descNode = $this->template->getElementByName('description',0,$mainNode)) instanceof DOMNode) {
            $this->template->setDisplayData("page", $this->name, $descNode);
            $this->template->setDisplayData("class", $this->getField('class'), $descNode);
        }
        if ( ($linkNode = $this->template->getElementByName('link_name',0,$mainNode)) instanceof DOMNode) {
            $page = $this->getName();
            $url = I2CE_Page::getAccessedBaseURL() . '/' . $page;
            $c =0;
            $formids = $this->getSampleIDs();
            foreach ($formids as $formid) {
                $this->template->setDisplayDataImmediate("link_title_" . $c, "$page $c" , $linkNode);
                $this->template->setDisplayDataImmediate("page_edit_link_" . $c, "$url?id=$formid" , $linkNode );
                $c++;
            }
            return true;
        }
    }


	
    protected function getSampleIDs() {
        return array();
    }

  }
# Local Variables:
# mode: php
# c-default-task: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
    