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
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_FormParentAutoTemplate
* 
* @access public
*/


class I2CE_Gizmo_FormParent extends I2CE_Gizmo_Form {


    protected function getDefaultOptions() {
	$options= parent::getDefaultOptions();
        $options['template'] = "auto_edit_parent_form.html";
	$options['parent_display'] = array(
	    'printf'=>'',
	    'printf_args'=>array()
	    );
	return $options;
    }

    public function generate($node) {
	parent::generate($node);
	if (!$node instanceof DOMNode
	    || ! $this->options['parent_obj'] instanceof I2CE_Form) {
	    return;
	}
	if ( $this->options['parent_display']['printf']) {
            ksort($this->options['parent_display']['printf_args']);
	    $vals = array();
            foreach ($this->options['parent_display']['printf_args'] as $field) {
                $fieldObj = $this->options['parent_obj']->getField($field);
                if ($fieldObj instanceof I2CE_FormField) {
                    $vals[] = $fieldObj->getDisplayValue();
                } else {
                    $vals[] = '';
                }
            }
            $disp =  @vsprintf($this->options['parent_display']['printf'] , $vals );
            $this->setDisplayDataImmediate('parent_disp',$disp,$node);
        }


    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
