<?php
/**
* © Copyright 2007 IntraHealth International, Inc.
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
*/
/**
*  iHRIS_Module_Manage
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @copyright Copyright &copy; 2007 IntraHealth International, Inc. 
* This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
* the terms of the GNU General Public License as published by the Free Software Foundation; either 
* version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
* that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
* received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
* @version 2.1
* @access public
*/


class iHRIS_Module_Manage extends I2CE_Module {

    public function upgrade($old_vers,$new_vers) {
        if (I2CE_Validate::checkVersion($old_vers,'<','3.0.1000')) {
            if (! $this->updatePersonFormFields()) {
                return false;
            }
            if (! $this->ensureFormsAndPages()) {
                return false;
            }
        }
        return true;
    }

    /*
     * In verison 3.0.1 we have a common iHRIS_Person class.  In order 
     * to make manage an qualify correspond, we need to change some of the
     * field names used by an ihris manage person.
     */
    protected function updatePersonFormFields() {
        ini_set('max_execution_time',6000);
        ini_set('memory_limit',"64M");
        $db = I2CE::PDO();
        $factory = I2CE_FormFactory::instance();
        try {
            $db->beginTransaction(); 
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Transaction failed to start." );
        }
        $personFormId = I2CE_Form::getFormId("person",true);
        if ($personFormId == 0) {
            I2CE::raiseError("Unable to get person form id.  Assuming that no person forms have ever been created.");
            if ($db->inTransaction()) { 
                $db->rollback();
            }
            return true;
        }
        $adminUser = I2CE_User::findUser('role','admin',false);
        if (!$adminUser instanceof I2CE_User) {
            I2CE::raiseError("Cannot find an administrative user");
            if ($db->inTransaction()) { 
                $db->rollback();
            }
            return false;
        }
        $details = array();
        $changes = array('country'=>'residence_country','district'=>'residence_district','county'=>'county_district');
        foreach (array_keys($changes) as $location) {
            die("deprecated badness in managemodule");
            $details[$location]=I2CE_FormField::getFormFieldIdAndType("person",$location);
            if ($details[$location] === null) {
                I2CE::raiseError("Unable to get details for person:$location.  Assuming that is has never beens used so skipping.");
                if ($db->inTransaction()) { 
                    $db->rollback();
                }
                unset($changes[$location]);
                continue;
            }
            try {
                $details[$location]['qry'] = $db->prepare(
                    "SELECT " . $details[$location]['type']  . "_value as val FROM last_entry where record = ? and form_field = ? LIMIT 1" );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e, "Error preping statement:" );
                if ($db->inTransaction()) { 
                    $db->rollback();
                }
                return false;
            }                  
        }
        try {
            $qry = $db->prepare( 'SELECT id from record where form = ?' );
            $qry->execute( $personFormId);
            while( $row = $qry->fetch() ) {
                $person = $factory->createContainer('person'.'|'. $row->id);
                if (!$person instanceof iHRIS_Person) {
                    I2CE::raiseError("Unable to create person with id " . $row->id);
                    if ($db->inTransaction()) { 
                        $db->rollback();
                    }
                    return false;
                }
                $person->populate();            
                foreach ($changes as $old=>$new) {
                    $details[$old]['qry']->execute( array( $row->id,$details[$old]['id']));                
                    $t_row = $details[$old]['qry']->fetch();
                    if (!$t_row) {
                        continue; //we did not get anything
                    }
                    $person->$new = $t_row->val;
                    $details[$old]['qry']->closeCursor();
                }
                if (!$person->save($adminUser)) {
                    I2CE::raiseError("Unable to save record " . $row->id);
                    if ($db->inTransaction()) { 
                        $db->rollback();
                    }
                    return false;
                }
                $person->cleanup();            
            }
            $qry->closeCursor();
            unset( $qry );
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Error preping select records" );
            if ($db->inTransaction()) { 
                $db->rollback();
            }
            return false;
        }
        if ($db->inTransaction()) { 
            return $db->commit();
        } else{
            return true;  
        }
    }

    public function ensureFormsAndPages() {
        $config = I2CE::getConfig();
        $forms = $config->modules->forms->forms;
        $changes = array(
            'demographic'=>array('old'=>'iHRIS_Demographic','new'=>'iHRIS_ManageDemographic'),
            'person'=>array('old'=>'iHRIS_Person','new'=>'iHRIS_ManagePerson')
            );
        foreach ($changes as $form=>$data) {
            if ($forms->$form->class != $data['old']) {
                $msg = "You have the class for the form $form set to " . $forms->$form->class . ".  There is defferent from the default ".
                    "iHRIS Manage installation values of " . $data['old'] . ".  The system is going through an upgrade to version > 3.0.999 and will" .
                    " now use " . $data['new'] . " instead.  Please make the neccesary changes to the class " . $forms->$form->class . " so that is uses " .
                    $data['new'] . " instead of " . $data['old'];
                I2CE::raiseError($msg);
                $this->userMessage($msg,'notice');
            } else {
                $forms->$form->class = $data['new'];
            }
        }
        $changes = array(
            'person'=>array('old'=>'iHRIS_PageFormPerson', 'new'=>'iHRIS_PageFormPersonManage'),
            'view'=>array('old'=>'iHRIS_PageView', 'new'=>'iHRIS_PageViewManage'),
            );
        $pages = $config->I2CE->page;
        foreach ($changes as $page=>$data) {
            if ($pages->$page->class != $data['old']) {
                $msg = "You have the class for the page $page set to " . $pages->$page->class . ".  There is defferent from the default ".
                    "iHRIS Manage installation values of " . $data['old'] . ".  The system is going through an upgrade to version > 3.0.999 and will" .
                    " now use " . $data['new'] . " instead.  Please make the neccesary changes to the class " . $pages->$page->class . " so that is uses " .
                    $data['new'] . " instead of " . $data['old'];
                I2CE::raiseError($msg);
                $this->userMessage($msg,'notice');
            } else {
                $pages->$page->class = $data['new'];
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
