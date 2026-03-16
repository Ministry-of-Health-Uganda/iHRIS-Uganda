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
* Class I2CE_Swiss_module_association.php
* 
* @access public
*/


abstract class I2CE_Swiss_module_association extends I2CE_Swiss {


    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
	$modules = I2CE_ModuleFactory::instance()->getAvailable();
        if (array_key_exists('modules',$vals)
            && is_array($vals['modules'])
            ) {
            $updates = array();
            foreach ($vals['modules'] as $module=>$val) {
                if (!in_array($module,$modules)  
                    || ! is_scalar($val)
                    || $val === '') {
                    continue;
                }
                $updates[$module] = $val;
            }
            I2CE::raiseError("Setting to ". print_r($updates,true));
            $this->storage->eraseChildren();
            $this->storage->setValue($updates);
        }
        return true;
    }

    abstract protected function getTemplate();


    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(),'div',$content_node)) instanceof DOMNode) {
            I2CE::raiseError("Could not load " . $this->getTemplate());
            return false;
        }
	$inputs = array();
	$modules = I2CE_ModuleFactory::instance()->getAvailable();
        if (($listNode  = $this->template->getElementByName('module_list',0,$mainNode)) instanceof DOMNode) {
            foreach ($modules as $module) {
                $val = '';
                $this->storage->setIfIsSet($val,$module);
                $input = 'modules['. $module . ']';
                $attr = array('value'=>$val,'name'=>$input);
                $listNode->appendChild($modNode = $this->template->createElement('span',array('style'=>'display:inline-block;width:33%; min-width:33%')));
                $modNode->appendChild($this->template->createElement('h3',array(),$module));
                $modNode->appendChild($this->template->createElement('input',$attr));
                $inputs[] =$input;
            }
        }
	$this->renameInputs($inputs,$mainNode);
	return true;
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
