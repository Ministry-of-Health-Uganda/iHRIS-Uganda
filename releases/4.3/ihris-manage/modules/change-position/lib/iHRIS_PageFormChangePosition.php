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
 * Manage editing person position departure in the database.
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
 * Page object to handle the editing of person position departure in the database.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageFormChangePosition extends iHRIS_PageFormParentPerson  {
       
    /**
     * Return the form name for this page.
     * @param boolean $html Set to true if this is to be used for the html template page.
     * @return string
     * @see PersonPageForm
     */
    protected function getForm( $html=false ) {
        return "change_position";
    }

    /**
     * @var I2CE_Form The current position object this person has been assigned to.
     */
    protected $current_position;
    /**
     * @var I2CE_Form The new position object this person will be assigned to.
     */
    protected $new_position;

    /**
     * @var iHRIS_PersonPosition $new_person_position The new person position this person held and is being promoted to .
     */
    protected $new_person_position;
    /**
     * @var iHRIS_PersonPosition $current_person_position The current person position this person held and is being promoted from.
     */
    protected $current_person_position;

    /**
     * @var iHRIS_Salary $new_salary The new salary this person will have
     */
    protected $new_salary;

    /**
     * @var iHRIS_Salary $current_salary The current salary this person had
     */
    protected $current_salary;

    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the list object and if this is a form submission load
     * the data from the form data.  It determines the type based on the
     * {@link $type} member variable.
     */
    protected function loadObjects() {
        if (!$this->hasPermission('task(person_can_edit_child_form_person_position)')) {
            $this->userMessage("You can change a person's position");
            return false;
        }
        $this->current_person_position = false;
        $this->new_person_position = false;
        $this->current_salary = false;
        $this->new_salary = false;
        $this->current_position = false;
        $this->new_position = false;
        if ($this->isPost()) {
            if (!$this->request_exists('parent') ||  ! ($this->person = $this->factory->createContainer($this->request('parent'))) instanceof iHRIS_Person || $this->person->getId() == 0) {
                I2CE::raiseError("Invalid person from " . $this->request('parent'));
                return false;
            }
            $this->person->populate();
            if ( ($this->current_person_position = $this->person->getLastPosition()) instanceof iHRIS_PersonPosition && $this->current_person_position->getId() != 0) {
                $this->current_person_position->populate();
                $curr_per_pos_id = $this->current_person_position->getID();
                if (array_key_exists('form',$this->post) && is_array($this->post['form'])
                    && array_key_exists('person_position',$this->post['form']) && is_array($this->post['form']['person_position'])
                    && array_key_exists($curr_per_pos_id,$this->post['form']['person_position']) && is_array($this->post['form']['person_position'][$curr_per_pos_id])) {
                    $this->current_person_position->setFromPost($this->post['form']['person_position'][$curr_per_pos_id]);
                }
                if ( ($this->current_salary = $this->current_person_position->getLastSalary()) instanceof iHRIS_Salary) {
                    $curr_sal_id = $this->current_salary->getID();
                    $this->current_salary->populate();
                    if (array_key_exists('form',$this->post) && is_array($this->post['form'])
                        && array_key_exists('salary',$this->post['form']) && is_array($this->post['form']['salary'])
                        && array_key_exists($curr_sal_id,$this->post['form']['salary']) && is_array($this->post['form']['salary'][$curr_sal_id])) {
                        $this->current_salary->setFromPost($this->post['form']['salary'][$curr_sal_id]);
                    }
                }
                if ( !($this->current_position = $this->factory->createContainer( $this->current_person_position->getField('position')->getDBValue() )) instanceof iHRIS_Position || $this->current_position->getID() == 0) {
                    I2CE::raiseError("Invalid Current position");
                    return false;
                } else {
                    $this->current_position->populate();
                    $curr_pos_id = $this->current_position->getID();
                    if (array_key_exists('form',$this->post) && is_array($this->post['form'])
                        && array_key_exists('position',$this->post['form']) && is_array($this->post['form']['position'])
                        && array_key_exists($curr_pos_id,$this->post['form']['position']) && is_array($this->post['form']['position'][$curr_pos_id])) {
                        $this->current_position->setFromPost($this->post['form']['position'][$curr_pos_id]);
                    }
                }
            }
            $this->new_position = $this->factory->createContainer('position');
            $this->new_position->setFromPost($this->post['form']['position'][0][0]);
            $this->new_person_position = $this->factory->createContainer('person_position');
            $this->new_person_position->setParent($this->person->getFormID());
            $this->new_person_position->setFromPost($this->post['form']['person_position'][0][1]);
            if ($this->hasPermission('task(person_can_edit_child_form_salary)')) {
                $this->new_salary  = $this->factory->createContainer('salary');
                $this->new_salary->setFromPost($this->post['form']['salary'][0][0]);
            }
        } else {
            if ( $this->get_exists('id') ) {
                if (! ($this->current_person_position = $this->factory->createContainer($this->get('id')))  instanceof iHRIS_PersonPosition || $this->current_person_position->getId() == 0 ) {
                    I2CE::raiseError("Invalid person position identified by " . $this->get('id'));
                    return false;
                }
                $this->current_person_position->populate();
                if (! ($this->person = $this->factory->createContainer($this->current_person_position->getParent())) instanceof iHRIS_Person || $this->person->getId() == 0) {
                    I2CE::raiseError("invalid person associated to " . $this->get('id'));
                    return false;
                }
                $this->person->populate();
            } elseif ( $this->get_exists( 'parent' ) ) {
                if ( ! ($this->person = $this->factory->createContainer($this->get('parent'))) instanceof iHRIS_Person || $this->person->getId() == 0) {
                    I2CE::raiseError("Invalid person from " . $this->get('parent'));
                    return false;
                }
                $this->person->populate();
                $this->current_person_position = $this->person->getLastPosition(); //note: there may not be an current person position.
            } else {
                I2CE::raiseError("No forms specified");
                return false;
            }
            if ($this->current_person_position instanceof iHRIS_PersonPosition) {
                $this->current_position = $this->current_person_position->getField('position')->getMappedFormObject();
            }
            $this->current_salary = $this->current_person_position->getLastSalary();
            $this->new_position = $this->factory->createContainer('position'); //create a new position
            $this->new_person_position = $this->factory->createContainer('person_position'); //create a new person position
            $this->new_person_position->setParent( $this->person->getFormId() );
            if ($this->hasPermission('task(person_can_edit_child_form_salary)')) {
                $this->new_salary = $this->factory->createContainer('salary');
            }

        }
        
        if ($this->current_person_position instanceof iHRIS_PersonPosition) {
            if (!$this->current_person_position->getField('end_date')->isValid()) {
                $this->current_person_position->end_date = I2CE_Date::now();
            }
            if ( $this->current_salary instanceof iHRIS_Salary ) {
                $this->current_salary->end_date = $this->current_person_position->end_date;
            }
            if ($this->new_person_position instanceof iHRIS_PersonPosition ) {
                $this->new_person_position->start_date = $this->current_person_position->end_date;
            }
            if ( $this->new_salary instanceof iHRIS_Salary && (!$this->new_salary->getField('start_date')->isValid() )) {
                $this->new_salary->start_date = $this->new_person_position->start_date;
            }
        }
        return true;
    }
       

    /**
     * Load the HTML template files for editing.
     */
    protected function setForm() {
        return ;
    }

    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        /* need to set new_person_position and then person here to make sure defaults are OK.
         Otherwise need form_person_base.html and button_confirm.html to be different */
        $this->template->setForm($this->new_person_position); 
        $this->template->setForm($this->person);
        $forms = array(
            'current_person_position'=>$this->new_person_position,
            'current_person_position'=>$this->current_person_position,
            'new_position'=>$this->new_position,
            'current_position'=>$this->current_position,
            'new_salary'=>$this->new_salary,
            'current_salary'=>$this->current_salary
            );
            
        foreach ($forms as $class=>$form) {
            if (!$form instanceof I2CE_Form) {
                I2CE::raiseMessage("$class isn't available");
                continue;
            }
            foreach ($this->template->query("//*[@class='form:$class']") as $node) {
                $this->template->setForm($form,$node);
            }
        }
        foreach (array('id','parent') as $key) {
            if ($this->request_exists($key) && $this->request($key)) {
                $this->template->setDisplayData($key, $this->request($key));
            }
        }
    }



    /**
     * Run extra validation for the fields being edited.
     */
    protected function validate() {
        if (!$this->isPost()) {
            return;
        }
        if ( $this->current_position instanceof iHRIS_Position && !$this->current_position->getField('status')->isValid() ) {
            $this->current_position->getField('status')->setInvalid(  "Please enter a new status." );                                
        }
        if ($this->current_person_position instanceof iHRIS_PersonPosition
            && $this->current_person_position->end_date->compare( $this->current_person_position->start_date ) != -1 ) {
            $this->current_person_position->getField('end_date')->setInvalid( "Please enter an end date after the previous position's start date." );
        }
        if ($this->current_person_position instanceof iHRIS_PersonPosition && !$this->current_person_position->getField('reason')->isValid()) {
            $this->current_person_position->getField('reason')->setInvalid( "Required Field" );
        }
        $this->new_position->validate();
    }

    /**
     * Checks to see if any of the forms on this page have invalid messages
     * @return boolean
     */
    public function hasInvalid() {
        return parent::hasInvalid() || $this->new_position->hasInvalid() || $this->current_person_position->hasInvalid() ||
            $this->new_person_position->hasInvalid() || $this->new_salary->hasInvalid() || $this->current_salary->hasInvalid();
    }
                        


    /**
     * Update the position for this to mark it as closed and then save the object.
     */ 
    public function save() {
        if (!$this->hasPermission('task(person_can_edit_child_form_person_position)')) {
            $this->userMessage("You cannot change a person's position");
            return false;
        }
        //we are only displaying one person_position form on the page, it is the new person_position form. W
        if ($this->current_person_position instanceof iHRIS_PersonPosition) {
            $this->current_person_position->save($this->user);
        }
        if ($this->current_position instanceof iHRIS_Position) {
            //now close out the current position
            $this->current_position->statusOnly();
            $this->current_position->save($this->user);
        }

        //we can now save the details on the new position
        $this->new_position->setFromDB( 'position_status|closed' );
        $this->new_position->save($this->user);        
        //now that the new position has been saved, we can link it to the new_person_position
        $this->new_person_position->getField('position')->setFromDB($this->new_position->getNameID());
        $this->new_person_position->save($this->user);
        if ($this->hasPermission('task(person_can_edit_child_form_salary)')) {
            if ($this->current_salary instanceof iHRIS_Salary) {
                $this->current_salary->save($this->user);
            }
            $this->new_salary->setParent($this->new_person_position);
            $this->new_salary->save($this->user);
        }
        $this->setRedirect(  "view?id=" . $this->person->getFormID());
    }


}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
