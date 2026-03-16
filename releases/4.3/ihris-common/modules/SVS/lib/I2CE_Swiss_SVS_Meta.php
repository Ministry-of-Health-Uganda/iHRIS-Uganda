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
* @package ihris-common
* @subpackage svs
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_Meta
* 
* @access public
*/


class I2CE_Swiss_SVS_Meta extends I2CE_Swiss {

    protected static $fields = array('Status','Source','SourceURI','Definition','Type','Binding');

    protected static $styles =     array('display_style','code_style','code_system_style');

    
    public function processValues($vals) {
        $avail_styles = $this->getAllowedStyles();
	foreach (self::$styles as $style) {
            if (array_key_exists($style,$vals)
                && is_scalar($s = $vals[$style])
                && (in_array($s,$avail_styles) || ($s == '' && $style =='code_system_style'))
                ){
                $this->setField($style,$s);
            }
        }
	foreach (self::$fields as $field) {
	    if(array_key_exists($field,$vals)
	       && $vals[$field]) {
		$this->setField($field,$vals[$field]);
            }
	}
	return true;
    }

    protected function getAllowedStyles() {
        $styles = array();
        if ($this->parent instanceof I2CE_Swiss_SVS
            && ($list = $this->parent->getField('list'))
            && ($listObj  = I2CE_FormFactory::instance()->createContainer($list)) instanceof I2CE_List
            && is_array($lists = $listObj->getMeta('list'))
            ) {
            $styles = array_keys($lists);

        }
        $styles =array_unique(array_merge($styles,array('default','id')));
        return $styles;

    }

    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_svs_meta.html','div',$content_node)) instanceof DOMNode) {
            I2CE::raiseError("Could not load " . $this->getTemplate());
            return false;
        }
        $avail_styles = $this->getAllowedStyles();
        $default_styles =  array('code_style'=>'code','display_style'=>'default');
        foreach (self::$styles as $style) {
            if (! ($styleNode = $this->template->getElementByName($style,0,$mainNode)) instanceof DOMElement) {
                continue;
            }
            $sel = $this->getField($style);
            $t_avail_styles = $avail_styles;
            if ($style == 'code_style') {
                $t_avail_styles[] = 'id';
            }
            if (! $sel)  {
                if ( array_key_exists($style,$default_styles) && in_array($default_styles[$style],$avail_styles)) {
                    $this->setField($style,$default_styles[$style]);
                    $sel = $default_styles[$style];
                } else  if ($style == 'code_style') {
                    $this->setField($style,'id');
                    $sel = 'id';
                } 
                
            }
            foreach ($t_avail_styles as $s) {
                $attrs = array('value'=>$s);
                if ($s == $sel) {
                    $attrs['selected'] = 'selected';
                }
                $styleNode->appendChild($this->template->createElement('option',$attrs,$s));
            }
        }
        $type = $this->getField('Type'); 
        if (!$type) {
            $this->setField('Type','Expanded');
        }
        $uri = $this->getField('SourceURI'); 
        if (!$uri) {
            $this->setField('SourceURI',I2CE_Page::getAccessedBaseURL());
        }
        $stat = $this->getField('Status'); 
        if (!$stat) {
            $this->setField('Status','Active');
        }

        $src = $this->getField('Source');
        if (!$src) {
            $src = 'iHRIS';
            I2CE::getConfig()->setIfIsSet($src,"/config/site/module");
            $this->setField('Source',$src);
        }
        foreach (self::$fields as $field) {
            $this->template->setDisplayDataImmediate($field,$this->getField($field),$mainNode);
        }
        $this->renameInputs(self::$fields,$mainNode);
        $this->renameInputs(self::$styles,$mainNode);
        return true;
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
