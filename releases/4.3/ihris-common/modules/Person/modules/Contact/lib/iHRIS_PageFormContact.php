<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 */
/**
 * Manage adding or editing contact details to the database.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * Page object to handle the adding or editing contact details to the database.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageFormContact extends iHRIS_PageFormParentPerson {


    /**
     * Create a new instance of this page.
     * 
     * This will call the parent constructor and then setup the base
     * template pages for the {@link I2CE_Template template}.  It also sets up the values
     * for the member variables.
     * @param string $title The title for this page.
     * @param string $form_name The form name of the form being edited.
     * @param mixed $access The role required to access this page.
     * @param array $files The list of template files to load for this page.
     */
    public function __construct( $args,$request_remainder){
        $args['page_form'] = 'contact'; //we set it here to avoid an error message but it is not doing antyhign really
        parent::__construct($args,$request_remainder);
        if ($this->request_exists('contact_type')) { 
            //we have to do this after the parent contrsuctor is called b/c request/post/get variables have not been processed
            $form_name  = 'person_contact_' . $this->request('contact_type');
            $args['page_form'] = $form_name;
            $this->form_name = $form_name;
            $this->form_link  = 'contact?contact_type=' . $this->request('contact_type');
        } else {
            I2CE::raiseError("no contact type specified");
        }
    }

	
	public function save(){
		if($this->request('source')){
			$ff = I2CE_FormFactory::instance();			
			I2CE::raiseError("Saving from mHero");
			$contact_groups = explode('_', $this->post('contact_group'));
			I2CE::raiseError(print_r($contact_groups,true));
			$groups = array();
			foreach($contact_groups as $key=>$group){
				if($group == 'bad'){
					//this has been unchecked, don't add it
				}
				else{
					$groups[] = "contact_group|".$group;
				}
			}
			$groups = implode(',', $groups);			
			if(strpos($this->post('parent'), '|') !== false){
				//person has work contact, therefore update contact group
				I2CE::raiseError("We will update with $groups");
				$ids = explode('|', $this->post('parent'));
				$person_id = $ids[0];
				$contactObj = $ff->createContainer('person_contact_work|'.$ids[1]);
				$contactObj->populate();
				$contactObj->getField('contact_group')->setFromDB($groups);
				$contactObj->save($this->user);
				$contactObj->cleanup();
			}else{
				//person has no work contact, create a new
				I2CE::raiseError("We will create after confirmation $groups");
				$person_id = $this->post('parent');
				$contactObj = $ff->createContainer('person_contact_work');
				$contactObj->getField('contact_group')->setFromDB($groups);
				$contactObj->setParent("person|$person_id");
				$contactObj->save($this->user);
				$contactObj->cleanup();
			}
		}
		else{
			parent::save();
		}
	}
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
