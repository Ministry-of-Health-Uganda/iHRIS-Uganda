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
* @subpackage formbuilder
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_MAP_printf_form
* 
* @access public
*/


class I2CE_Swiss_MAP_reportSelect  extends I2CE_Swiss{


    public function processValues($vals) {
        if (array_key_exists('reportview',$vals)
	    && is_scalar($rv = $vals['reportview'])
            && ($rv == '' || in_array($rv, I2CE::getConfig()->getKeys("/modules/CustomReports/reportViews")))
	    ) {
	    $this->setField('reportView',$rv);
	}
	return true;
    }



    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_map_reportselect.html','div',$content_node)) instanceof DOMNode
            || ! ($selectNode = $this->template->getElementByName('reportview',0,$mainNode)) instanceof DOMNode
	    ) {
            I2CE::raiseError("Could not load template");
            return false;
        }
        $available = I2CE::getConfig()->getKeys("/modules/CustomReports/reportViews");
        $selected = $this->getField('reportView');
        foreach ($available as $rv) {
            $attrs = array('value'=>$rv);
            if ($rv == $selected) {
                $attrs['selected'] = 'selected';
            }
            $selectNode->appendChild($this->template->createElement('option',$attrs,$rv));
        }
	$this->renameInputs(array('reportview'),$mainNode);
	return true;
    }






}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
