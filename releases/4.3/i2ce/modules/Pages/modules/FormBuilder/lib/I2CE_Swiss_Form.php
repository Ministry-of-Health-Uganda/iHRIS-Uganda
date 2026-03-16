<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
*/
/**
*  I2CE_SwissConfig_FormRelationship_Joins
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_Form extends I2CE_Swiss {




    protected function getChildType($child ) {
        if ($child  == 'meta') {
            return 'Form_meta';
        } else if ($child  == 'storage_options') {
            return 'Form_storage_options';
        } else {
            return parent::getChildType($child);
        }
    }



    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        if (array_key_exists('class',$vals)
            && is_scalar($class= $vals['class'])
            && in_array($class,I2CE::getConfig()->getKeys("/modules/forms/formClasses"))
            ) {
            $this->setField('class',$class);
        }
        if (array_key_exists('display',$vals)
            && is_scalar($display= $vals['display'])
            ) {
            $this->setTranslatableField('display',$display);
        }
        return true;
    }




    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_form.html','div',$content_node)) instanceof DOMNode) {
            I2CE::raiseError("no template form_class_builder_menu.html");
            return false;
        }
        if ( ($classNode = $this->template->getElementByName('class',0,$mainNode)) instanceof DOMNode) {
            $selected = $this->getField('class');
            foreach (I2CE::getConfig()->getKeys("/modules/forms/formClasses") as $class) {
                $attrs = array('value'=>$class);
                if ($selected == $class) {
                    $attrs['selected'] = $selected;
                }
                $classNode->appendChild($this->template->createElement('option',$attrs,$class));
            }
        }
        $this->template->setDisplayDataImmediate('display',$this->getField('display'),$mainNode);
        $storage = $this->getField('storage');
        if (!$storage) {
            $storage = 'entry';
        }
        $this->template->setDisplayDataImmediate('storage',$storage,$mainNode);
        $this->renameInputs(array('display','class'),$mainNode);        

        if ( ($metaChild = $this->getChild('meta',true)) instanceof I2CE_Swiss
             && ( $metaNode = $this->template->getElementById('meta',$mainNode)) instanceof DOMNode
            ) {
            $metaChild->addAjaxLink('meta_link','container', 'meta_ajax' ,$metaNode,$action, $transient_options);
        }
        if ( ($storage_optionsChild = $this->getChild('storage_options',true)) instanceof I2CE_Swiss
             && ( $storage_optionsNode = $this->template->getElementById('storage_options',$mainNode)) instanceof DOMNode
            ) {
            $storage_optionsChild->addAjaxLink('storage_options_link','container', 'storage_options_ajax' ,$storage_optionsNode,$action, $transient_options);
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
