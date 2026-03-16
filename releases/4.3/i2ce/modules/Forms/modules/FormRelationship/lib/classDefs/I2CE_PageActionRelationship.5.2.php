<?php
/**
* © Copyright 2013 IntraHealth International, Inc.
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
* @package I2CE
* @subpackage FormRelationshi
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1.6
* @since v4.1.6
* @filesource 
*/ 
/** 
* Class I2CE_PageActionRelationship
* 
* @access public
*/


class I2CE_PageActionRelationship extends I2CE_Page {
    /**
     *@var I2CE_FormRelationship $formRelationship 
     */
    protected $formRelationship;


    /**
     *@var  I2CE_Form $primObj  The primary object in the relationship
     */
    protected $primObj;



    /**
     * @var I2CE_RelationshipData $data
     */
    protected $data = false;

    /**
     * Get the list of data fields we want from the relationship
     * @returns array with keys relationship form namess and values array of fields for that form
     */
    protected function getFields() {
        return array();
    }
    /**
     * Get the order  of data fields we want from the relationship
     * @returns array with keys relationship form namess and values array of fields for that form
     */
    protected function getOrdering() {
        return array();
    }

    /**
     * Perform the main actions of the page.
     * @return boolean
     */
    protected function action() {
        if ( !parent::action() ) {
            I2CE::raiseError("Base action failed");
            return false;
        }
        if (!$this->loadRelationship()) {
            I2CE::raiseError("Could not load relationship");
            return false;
        }
        $this->loadPrimary();
        return $this->action_main();
    }


    protected function action_main() {
        return true;
    }

    protected function loadPrimary() {
        $formFactory = I2CE_FormFactory::instance();        
        if ( $this->get_exists('id') ) {
            if  (! ($this->primObj = $formFactory->createContainer($this->get('id'))) instanceof I2CE_Form
                 ||               $this->formRelationship->getPrimaryForm() != $this->primObj->getName()
                ) {
                I2CE::raiseError("invalid form id :" . print_r($this->request(),true) . "\ndoes not match " . $this->formRelationship->getPrimaryForm());
                return false;
            }
            $this->primObj->populate();
        }
        return true;
    }

    protected function loadRelationship() {
        $rel_base = '/modules/CustomReports/relationships';
        if (!array_key_exists('relationship',$this->args)
            || !is_scalar($this->args['relationship'])) {
            I2CE::raiseError("Invalid relationship");
            return false;
        }
        if (array_key_exists('relationship_base',$this->args)
            && is_scalar($this->args['relationship_base'])) {
            $rel_base = $this->args['relationship_base'];
        }
        $use_cache = I2CE_ModuleFactory::instance()->isEnabled('CachedForms');
        if (array_key_exists('use_cache',$this->args)) {
            $use_cache = $this->args['use_cache'];
        }
        if ($this->request_exists('use_cache')) {
            $use_cache = $this->request('use_cache');
        }
        if ($use_cache) {
            $cache_callback =array('I2CE_CachedForm','getCachedTableName');
        } else {
            $cache_callback = null;
        }
        try {            
            $this->formRelationship = new I2CE_FormRelationship($this->args['relationship'],$rel_base,$cache_callback);
        } catch (Exception $e) {
            I2CE::raiseError("Could not create form relationship : " . $this->args['relationship']);
            return false;
        }
        if (array_key_exists('use_display_fields',$this->args) && (!$this->args['use_display_fields'])) {
            $this->formRelationship->useRawFields();
        }
        return true;
    }

    
    /**
     *Loads in the requeted data from the relationship
     * @returns boolean  True on success
     */
    protected function loadData($as_iterator = true) {
        $fields = $this->getFields();
        $ordering = $this->getOrdering();
        I2CE::longExecution( array( "max_execution_time" => 1800 ) );
        $this->data = $this->formRelationship->getFormData($this->primObj->getName(),$this->primObj->getId(),$fields,$ordering,$as_iterator);
        if ($as_iterator ) {
            if ( ! ($this->data) instanceof I2CE_RelationshipData) {
                I2CE::raiseError("No data");
                return false;
            }
        } else {
            if (!is_array($this->data)) {
                return false;
            }
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
