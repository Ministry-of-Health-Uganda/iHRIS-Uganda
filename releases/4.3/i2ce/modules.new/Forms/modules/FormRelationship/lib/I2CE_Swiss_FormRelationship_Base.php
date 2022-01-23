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
*  I2CE_Swiss_FormRelationship_Base
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


abstract class I2CE_Swiss_FormRelationship_Base extends I2CE_Swiss {

    protected function getAncestorByClass($class) {
        $swiss = $this;
        while ( (!$swiss instanceof $class) && ($swiss instanceof I2CE_Swiss) && ($swiss->hasParent())) {
            $swiss = $swiss->getParent();
        }
        if (!$swiss instanceof $class) {
            return null;
        } else {
            return $swiss;
        }
    }


    public function getAjaxJSNodes() {
        return parent::getAjaxJSNodeS() .',select_update,ajax_list';
    }


    protected $swissRelationship;

    public function getRelationship() {
        if ($this->swissRelationship === null) {
            $this->swissRelationship = $this->getAncestorByClass('I2CE_Swiss_FormRelationship');
            if (!$this->swissRelationship instanceof I2CE_Swiss_FormRelationship) {
                $this->swissRelationship = false;
            }
        }
        return $this->swissRelationship;
    }
    

    public function getRelationships() {
        $swiss = $this->getAncestorByClass('I2CE_Swiss_FormRelationships');
        if (! $swiss  instanceof I2CE_Swiss_FormRelationships) {
            return array();
        }
        $storage = $swiss->getStorage();
        if ($storage->is_parent()) {
            return $storage->getKeys();
        } else { 
            return array();
        }
    }

    public function getRelationshipBase() {
        $base = $this;
        do {
            $t_base = $base->getParent()->getAncestorByClass('I2CE_Swiss_FormRelationship');
            if ($t_base instanceof I2CE_Swiss_FormRelationship) {
                $base = $t_base;
            } else {
                $t_base = null;
            }
        } while ($t_base instanceof I2CE_Swiss_FormRelationship && $t_base->hasParent());

        if ($base instanceof I2CE_Swiss_FormRelationship) {  //maybe we called not from under a relationship
            return $base;
        } else {
            return null;
        }
    }

    public function getSwissForm($form) {
        $base = $this->getRelationshipBase();
        if (!$base instanceof I2CE_Swiss_FormRelationship) {
            return false;
        }
        if ($form == 'primary_form') {
            return $base;
        }
        $swissForms = $this->getExistingSwissForms();
        if (!array_key_exists($form,$swissForms)) {
            return false;
        }
        return $swissForms[$form];
        
    }

    protected $swissForms;
    public function getExistingSwissForms() {
        if (is_array($this->swissForms)) { 
            return $this->swissForms;
        }
        $base = $this->getRelationshipBase();
        if (!$base instanceof I2CE_Swiss_FormRelationship) {
            return array();
        }
        $this->swissForms = array();
        $stack = array($base);
        while (count($stack) > 0) {
            $form = array_pop($stack);
            $this->swissForms[$form->getStorage()->getName()] = $form;
            $joins = $form->getChild('joins');
            if (!$joins instanceof I2CE_Swiss_FormRelationship_Joins) {
                continue;
            }
            $joinNames = $joins->getChildNames();
            foreach ($joinNames as $joinName) {
                $join = $joins->getChild($joinName);
                if (!$join instanceof I2CE_Swiss_FormRelationship) {
                    continue;
                }
                $stack[] = $join;
            }
        }
        return $this->swissForms;
    }

    public function getExistingFormNames($add_primary = true) {
        $usedNames = array_keys($this->getExistingSwissForms());
        if ($add_primary) {
            $usedNames[] = 'primary_form';
        }
        return $usedNames;
    }


    public function initializeDisplay($action) {
        parent::initializeDisplay($action);
		$module_factory = I2CE_ModuleFactory::instance();
		$this->template->addHeaderLink('FormRelationship.css');
        $this->template->addHeaderLink('mootools-core.js');
        $this->template->addHeaderLink('mootools-more.js');
        $this->template->addHeaderLink('select_update.js');
		if( $module_factory->isEnabled("web-services-lists")){
			$this->template->addHeaderLink('I2CE_AjaxList.js');
		}
		return true;
    }


    public function displayOptions($optionsNode,$transient_options) {
        if (!$this->template->appendFileByNode('formrelationships_options.html','span',$optionsNode) instanceof DOMNode) {
            return;
        }
        $this->template->setDisplayData('locale_link',$this->getURLRoot(). $this->path . $this->getURLQueryString(array('locale'=>null)), $optionsNode);
        $this->template->setDisplayData('current_locale', $this->getLocale(),$optionsNode);
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
