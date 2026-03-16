<?php
/**
 * View a person's record.
 * @package iHRIS
 * @subpackage DemoManagePage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2006, 2008 IntraHealth International, Inc. 
 * @since Demo-v2.a
 * @version Demo-v2.a
 */
/**
 * Include the main include file to load global functions and configurations.
 */
require_once 'main.inc.php';

/**
 * The page class for displaying the a person's record.
 * @package iHRIS
 * @subpackage DemoManagePage
 * @access public
 */
class SalaryHistoryPage extends I2CE_Page {

    /**
     * Return the title for this page.
     * @return string
     */
    protected function getTitle() { return "Salary History"; }
    /**
     * Return the default HTML file used by this page.
     * 
     * This method is only used by simple pages to load a single file from the default loadHTMLTemplates method.
     * If that method is overridden then this method isn't necessary.
     * @return string
     */
    protected function getDefaultHTMLFile() { return "history.html"; }

    /**
     * Load the HTML template files for editing and confirming the index and demographic information.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->appendHTMLFileById( "menu_view_link.html", "li", "navBarUL", true );
    }
        
        
    public function __construct() {
        parent::__construct(
            array(
                'access'=>'admin',
                'title'=>'update salary history',
                'templates' => array('history.html'),
                'defaultHTMLFile' => 'history.html'
                ),
            array()
            );
    }

        
    /**
     * Returns a map keys are positions, values are the salaries associated to a position.
     * all salaraies not associated to a position are given positon -1.
     * Does _not_ return any salaries that have a person_position field
     */
    protected function  getPositionSalaryMap($personId) {
        $factory =& I2CE_FormFactory::instance();
        $person = $factory->createForm( "person", $personId ); //replace with a foreach!
        $person->populate();
        $positionIDs = $person->getChildIds('person_position','-start_date'); //get the positions ordered by descending start date
        $salaryIDs = $person->getChildIds('salary','-start_date'); //get the positions ordered by descending start date
        $positions = array();
        $position_start = array();
        $position_end = array();
        $map = array();
        foreach ($positionIDs as $positionId) {
            $position =  $factory->createForm("person_position",$positionId);
            $position->populate();
            $position_start[$positionId]  = $position->getField('start_date');
            $position_end[$positionId] =  $position->getField('end_date');
        }
        $salarys = array();
        foreach ($salaryIDs as $salaryId) {
            $salary = $factory->createForm("salary",$salaryId);
            if (!$salary instanceof iHRIS_Salary) {
                continue;
            }
            $salary->populate();
            if ($salary->getParent() != $personId) {
                //we have already updated this record.
                continue;
            }
            $start_date = $salary->getField('start_date');
            $end_date = $salary->getField('end_date');
            $id = -1;
            foreach ($positionIDs as $positionId) {
                /* $date1->compare($date2);
                 * Compares  date2 to date1  and returns -1 if date2 is before date1, 0 if the same and 1  if date2  is after
                 *  date1.
                 */
                if ($position_start[$positionId]->getValue()->compare($start_date->getValue())  >=  0 ) {
                    //the salary start date is at the same time or  after  the position start date
                    if ($end_date->isValid()) {
                        if ($position_end[$positionId]->isValid()) {
                            if ($position_end[$positionId]->getValue()->compare($end_date->getValue())  <=  0 ) {
                                //the salary start date is at the same time or before  the position end date
                                $id = $positionId; 
                                break 1;
                            }
                        } else {
                            $id = $positionId;
                            break 1;
                        }
                    } else {
                        //the end date of the salary is not set.
                        if (!$position_end[$positionId]->isValid()) {
                            $id = $positionId;
                            break 1;
                        }
                    } 
                }

            }
            $map[$id][] = $salaryId;
        }
        return $map;
    }


    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        parent::action();
        if ($this->isPost()) {
            if ($this->post_exists('id')) {
                echo $this->fixSalary($this->post('id'));
                die();
            } elseif ($this->post_exists('all_ids')) {
                echo $this->fixAllSalaries();
                die();
            } else {
                $this->showMenu();
            }
        } else {
            if ($this->get_exists('show')) {
                $this->previewChanges();
            } else {
                $this->showMenu();
            }
        }
    }


    protected function showMenu() {
        echo "<a href='update_salary_history.php?show'>View</a> all salary/positions that have a problem<br/>";
        echo "<a href='update_salary_history.php?show&all'>View</a> all salary/positions <br/>";
        die();          
    }
                
    protected function fixAllSalaries() {
        $factory =& I2CE_FormFactory::instance();
        $results = $factory->callStatic( "person", "search", array( array(), 'AND' ) );
        $out =  "Examining " . count($results) . " People <br/>";
        $ids= array();
        foreach( $results as $id => $name ) {
            $ids[] = $id;
        }               
        $out .=  "<h2> Updating All Salaries</h2>";
        $out .=  "<ul>";
        foreach ($ids as $id) {
            $out .= "<li>" . $this->fixSalary($id)  . "</li>";
        }
        return $out;
    }

    protected function fixSalary($id) {
        $factory =& I2CE_FormFactory::instance();
        $person = $factory->createForm('person',$id);
        if (! $person instanceof iHRIS_Person) {
            return "$id does not refer to a person ";
        }
        $person->populate();
        $map = $this->getPositionSalaryMap($id);
        if (count($map) == 0) {
            return "Position $id is already up to date";
        } if (isset($map[-1])) {
            return "Position <a href='update_salary_history.php?show&id=$id'>$id</a>is not ready to be updated";
        }
        $errors = '';
        foreach ($map as $pos=>$sals) {
            foreach ($sals as $sal) {
                $salary = $factory->createForm('salary',$sal);
                if (!$salary instanceof iHRIS_Salary) {
                    $errors.= "<li>ID $sal is not a salary</li>";
                    continue;
                }
                $salary->populate();
                $salary->setParent($pos);
                $salary->save($this->user);
            }
        }
        if (strlen($errors) > 0) {
            return "Errors for $id:<ul>$errors</ul>";
        } else {
            return "Success on $id"; 
        }
    }

    protected function previewChanges() {
        $factory =& I2CE_FormFactory::instance();
        $ids= array();
        if ($this->get_exists('id')) {
            $ids = array($this->get('id'));
        } else {
            $results = $factory->callStatic( "person", "search", array( array(), 'AND' ) );
            echo "Examining " . count($results) . " People <br/>";
            foreach( $results as $id => $name ) {
                $ids[] = $id;
            }
        }

                
        $output = '<h2>Individual Records</h2>';
        $not_updated = 0;
        foreach ($ids as $id) {
            $map = $this->getPositionSalaryMap($id);
            if (count($map) > 0 && !isset($map[-1])) {
                $not_updated ++;
            }
            if (isset($map[-1])) {
                $output.= "<li>";
                $person = $factory->createForm('person',$id);
                $person->populate();
                $output.= "<b>Warning</b>  Person <a href='view.php?id=$id'>"
                    . $person->firstname . ' ' . $person->surname .  
                    "</a> has invalid salaries";
            } else      if ($this->get_exists('all')) {
                if (count($map) == 0) {
                    $output.= "<li>";
                    $person = $factory->createForm('person',$id);
                    $person->populate();
                    $output.= "Person <a href='view.php?id=$id'>". $person->firstname . ' ' . $person->surname .  "</a> has been updated";  
                } else {
                    $output.= "<li>";
                    $person = $factory->createForm('person',$id);
                    $person->populate();
                    $output.= "Person <a href='view.php?id=$id'>". $person->firstname . ' ' . $person->surname .  "</a>";  
                    $output.= " <form action'update_salary_history.php' method='post'><input type='hidden' name='id' value='$id'/>";
                    $output.= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='Update'></form>";
                }
            }
            if ($this->get_exists('all') || isset($map[-1])) {
                if (count($map) > 0) {
                    $output.= "<ul>";
                    foreach ($map as $pos => $sals) { 
                        if ($pos == -1) { 
                            $output.= "<li> <b>Warning</b> No position found for the following: <ul>"; 
                        } else { 
                            $personPos = $factory->createForm('person_position',$pos);
                            $personPos->populate();
                            $position = $factory->createForm('position',$personPos->getField('position')->getValue());
                            $position->populate();
                            $output.= "<li> Position <A href='person_position.php?id=$pos&parent=$id'>".
                                $position->getField('title')->getDisplayValue() ."</a> <ul>"; 
                        } 
                        foreach ($sals as $sal) { 
                            $output.= "<li>Salary <a href='salary.php?id=$sal&parent=$id'>$sal</a></li>"; 
                        } 
                        $output.= "</ul></li>"; 
                    } 
                    $output.= "</ul>"; 
                }
            } 
            if ($this->get_exists('all') || isset($map[-1])) {
                $output.= "</li>";
            }
        } 
        $output.= "</ul>"; 
        if ($not_updated > 0) {
            echo "There are $not_updated records which are ready to be updated<br/>";
            echo " <form action'update_salary_history.php' method='post'><input type='hidden' name='all_ids' value='true'/>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='Update $not_updated'></form>";
        } else {
            echo "All records possible that can be updated have been<br/>";
        }
        echo $output;
        die(); 
    } 
        
}

$deletes = array();
$factory = I2CE_FormFactory::instance();
foreach ($deletes as $delete) {
    $salary = $factory->createForm('salary',$delete);
    if($salary instanceof iHRIS_Salary) {
        $salary->delete();
    }
}

$page = new SalaryHistoryPage();
$page->display();


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
