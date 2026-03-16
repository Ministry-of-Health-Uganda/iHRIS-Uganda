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
* Class I2CE_Swiss_FormMainBuilder
* 
* @access public
*/


class I2CE_Swiss_FormMainBuilder extends I2CE_Swiss{



    protected function getChildType($child) {
	if ($child =='forms') {
	    return 'FormBuilder';
	} else if ($child == 'formClasses') {
	    return 'FormClassBuilder';
	} else {
	    return parent::getChildType($child);
	}
    }

    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('form_main_builder.html','div',$content_node)) instanceof DOMNode) {
            I2CE::raiseError("no template form_main_builder_menu.html");
            return false;
        }
	if ( ($formClassesChild = $this->getChild('formClasses',true)) instanceof I2CE_Swiss
             && ( $formClassesNode = $this->template->getElementById('formClasses',$mainNode)) instanceof DOMNode
            ) {
            $formClassesChild->addAjaxLink('formClasses_link','container', 'formClasses_ajax' ,$formClassesNode,$action, $transient_options);
        }
        if ( ($formsChild = $this->getChild('forms',true)) instanceof I2CE_Swiss
             && ( $formsNode = $this->template->getElementById('forms',$mainNode)) instanceof DOMNode
            ) {
            $formsChild->addAjaxLink('forms_link','container', 'forms_ajax' ,$formsNode,$action, $transient_options);
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
