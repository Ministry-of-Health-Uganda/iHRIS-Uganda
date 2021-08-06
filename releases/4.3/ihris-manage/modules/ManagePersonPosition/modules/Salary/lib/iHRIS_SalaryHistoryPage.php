<?php
/**
 * View a person's record.
 * @package iHRIS
 * @subpackage DemoManagePage
 * @access public
 * @author Carl Leitner <litlfred@ibibilio.org>
 * @copyright Copyright &copy; 2006, 2008 IntraHealth International, Inc. 
 * @since Demo-v2.a
 * @version Demo-v2.a
 */


/**
 * The page class for displaying the a person's salary history
 * @package iHRIS
 * @subpackage DemoManagePage
 * @access public
 */
class iHRIS_SalaryHistoryPage extends I2CE_Page {

    /**
     * Return the title for this page.
     * @return string
     */
    public function getTitle() { return "Salary History"; }


    /**
     * Load the HTML template files for editing and confirming the index and demographic information.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->appendFileById( "menu_view_link.html", "li", "navBarUL", true );
    }
        
    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        parent::action();
        if ($this->get_exists('delete')) {
            if (!$this->hasPermission('task(person_can_delete_child_form_salary)')) {
                $this->userMessage("You do not have permission to delete the salary");
                return false;
            }
            $personId = $this->deleteSalary();
            if ($personId > 0) {
                $this->redirect("salary_history?id=$personId");
            } else {
                $this->redirect('');
            }
            return;
        }
        if (!$this->hasPermission('task(person_can_view_child_form_salary)')) {
            $this->userMessage("You do not have permission to view the salaries");
            return false;
        }
        return $this->showSalaryHistory();
    }
                

    protected function deleteSalary() {
        if (!$this->get_exists('id')) {
            return false;
        }               
        $id = $this->get('id');
        $factory =& I2CE_FormFactory::instance();
        $salary = $factory->createContainer("salary".'|'.$id);
        if (!$salary instanceof I2CE_Form && $salary->getName() != "salary" ) {
            return 0;
        }
        $salary->populate();
        $position = $factory->createContainer($salary->getParent());
        if (!$position instanceof iHRIS_PersonPosition) {
            return 0;
        }
        $position->populate();
        $personID = $position->getParentID();
        $salaryIDs = $position->getChildIds('salary','-start_date');
        
        if (count($salaryIDs) < 2) { //we can only delete if there is at least one salary
            return $personID;
        }
        //we now can delete this history.
        $salary->delete();              
        return $personID;
    }


    protected function showSalaryHistory() {
        $factory = I2CE_FormFactory::instance();
        $person = $factory->createContainer( $this->get('id') );
        $person->populate();
        $this->template->setForm( $person );
        $positionIDs = $person->getChildIds('person_position','-start_date'); //get the positions ordered by descending start date
        $postions = array();
        $this->template->setDisplayData( "history_header",  "Salary History" );
        $firstPosition = true;
        foreach ($positionIDs as $positionId) {
            $position =  $factory->createContainer("person_position".'|'.$positionId);
            $sg =$factory->createContainer("salary_grade".'|'. $position->getSalaryGradeId()); 
            if (!$position instanceof iHRIS_PersonPosition) {
                continue;
            }
            $position->populate();
            if ($firstPosition) {
                $firstPosition = false; 
            } else {
                $this->template->appendFileById( "hr.html", "hr", "salary_history" );
            }
            $positionNode = $this->template->appendFileById( "salary_history_position.html", "div", "salary_history" );
            $this->template->setForm($position,$positionNode);
            $salaryListNode = $this->template->getElementById('salary_list');
            if ($salaryListNode instanceof DOMElement) {
                $salaryListNode->setAttribute('id', "salary_list_$positionId");
            }
            $salaryIDs = $position->getChildIds('salary','-start_date');
            foreach ($salaryIDs as $salaryId) {
                $salary = $factory->createContainer('salary'.'|'.$salaryId);
                if (!$salary instanceof I2CE_Form && $salary->getName() != "salary" ) {
                    continue;
                }
                $salary->populate();
                $salaryNode = $this->template->appendFileById( "salary_history_salary.html", "div", "salary_list_$positionId" );
                if (count($salaryIDs) == 1) {
                    $this->template->findAndRemoveNodes(".//*[@id='delete_salary']",$salaryNode);

                }
                $this->template->setForm( $salary,$salaryNode );
            }
            if (count($salaryIDs) == 0 && $this->hasPermission('task(person_can_edit_child_form_salary)'))  {
                $salaryNode = $this->template->appendFileById( "salary_history_add.html", "div", "salary_list_$positionId" );
                $this->template->appendFileById( "hr.html", "hr", "salary_history" );
            }
            $this->template->setForm( $position, $positionNode );
            if ($sg instanceof iHRIS_SalaryGrade) {
                $sg->populate(); 
                $this->template->setForm( $sg,$positionNode);
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
