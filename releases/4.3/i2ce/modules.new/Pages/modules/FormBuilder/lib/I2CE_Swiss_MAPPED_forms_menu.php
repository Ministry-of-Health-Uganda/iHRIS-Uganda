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
* Class I2CE_Swiss_MAPPED_forms_menu
* 
* @access public
*/


abstract class I2CE_Swiss_MAPPED_forms_menu extends I2CE_Swiss {


    

    abstract protected function getContainerTemplate();


    protected function getFormTemplate() {
        return 'swiss_mapped_forms_menu_each.html';
    }


    protected function makeScalar() {
        return false;
    }

    protected function getAllowedForms() {
        return I2CE::getConfig()->getKeys("/modules/forms/forms");
    }

    public function processValues($vals) {
        $forms = array_diff($this->getAllowedForms(),$this->storage->getKeys());
        if (array_key_exists('form',$vals)
	    && I2CE_MagicDataNode::checkKey($form = $vals['form'])
	    && in_array($form,$forms)
            ){
            if ($this->makeScalar()) {
                $this->storage->set_scalar($form,true);
            }else{
                $this->storage->set_parent($form,true);
            }
	}
	return true;
    }

    protected function getAjaxContainer() {
        return $this->name . '_container';
    }
    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode($this->getContainerTemplate(),'div',$content_node)) instanceof DOMNode
	    || !($containerNode = $this->template->getElementByName('forms',0,$mainNode))
	    ) {
            I2CE::raiseError("Could not load " . $this->getContainerTemplate());
            return false;
        }
        if (($formsNode = $this->template->getElementByName('form',0,$mainNode)) instanceof DOMNode) {
            $forms = array_diff($this->getAllowedForms(),$this->storage->getKeys());
            foreach ($forms as $form ) {
                $formsNode->appendChild($this->template->createElement('option',array('value'=>$form),$form));
            }
        }
	$this->renameInputs(array('form'),$mainNode);
	foreach ($this->storage->getKeys() as $name) {	    
	    $containerNode->appendChild($liNode = $this->template->createElement('li'));
	    if ( !($childNode =$this->template->appendFileByNode($this->getFormTemplate(),'div',$liNode)) instanceof DOMNode
		 || !($swissChild = $this->getChild($name)) instanceof I2CE_Swiss
		) {
		continue;
	    }
	    $this->template->setDisplayDataImmediate('name',$name ,$childNode);
	    $swissChild->addAjaxLink( 'form_link', $this->getAjaxContainer(), 'form_ajax',$childNode,$action, $transient_options);            
	    $delete_link = $swissChild->getURLRoot('delete_class')  .  $swissChild->path;
	    $this->template->setDisplayDataImmediate('delete_link',$delete_link,$childNode);

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
