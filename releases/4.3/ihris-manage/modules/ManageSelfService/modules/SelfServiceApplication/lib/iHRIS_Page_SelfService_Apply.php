<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @package ihris-common
* @subpackage self-service
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.8
* @since v4.0.8
* @filesource 
*/ 
  /** 
   * Class iHRIS_Page_SelfService_Apply
   * 
   * @access public
   */


class iHRIS_Page_SelfService_Apply extends I2CE_PageForm{

    
    
    /**
     * @var protected I2CE_User_Form $userObj
     */
    protected $userObj = null;

    
    /**
     * @var protected I2CE_UserMap $userMap
     */
    protected $userMap = null;

    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the list object and if this is a form submission load
     * the data from the form data.
     */
    protected function loadObjects() {
        $factory = I2CE_FormFactory::instance();
        $this->userObj = null;
        if ($this->isPost()) {
            $person = $factory->createContainer('person');
            if (!$person instanceof iHRIS_Person) {
                I2CE::raiseError("Could not create person form");
                return;
            }
            $person->load($this->post);
            $person->load($this->post);
            if (($surname_ignore = $person->getField('surname_ignore')) instanceof I2CE_FormField
                && ($surname = $person->getField('surname')) instanceof I2CE_FormField
                ) {
                $ignore_paths = array( $surname->getHTMLName('ignore') , $surname_ignore->getHTMLName());
                foreach ($ignore_paths as $ignore_path) {
                    $ignore_path = explode('[', $ignore_path );
                    foreach ($ignore_path as &$comp) {
                        $comp = rtrim($comp,']');
                    }
                    unset($comp);
                    if ( $this->post_exists($ignore_path)) {                    
                        $surname_ignore->setFromDB($this->post($ignore_path));
                    }
                }
            }

            $application = $factory->createContainer('application');
            $application->load($this->post);

            
            if ($this->creatingNewUser()) {
                if (! ($this->userObj = $factory->createContainer( 'user')) instanceof      I2CE_User_Form) {
                    $this->userObj = null;
                    I2CE::raiseError("bad user form");
                    return false;
                }
                $this->userObj->load( $this->post );
                $map = iHRIS_Module_SelfService::getUserDetailsInPerson();
                foreach ($map as $detail => $field) {
                    $this->userObj->getField($detail)->setFromDB($person->getField($field)->getDBValue());
                }
                $this->userObj->load( $this->post );
                if ( !$this->isSave(false) ) {
                    $this->userObj->tryGeneratePassword();
                }
                
                $role =false;
                if (I2CE::getConfig()->setIfIsSet($role,"/modules/SelfService/default_user_role") && I2CE_MagicDataNode::checkKey($role) && I2CE::getConfig()->is_parent("/I2CE/formsData/forms/role/$role")) {
                    $this->userObj->getField('role')->setFromDB('role|' . $role);
                } else {
                    $this->userObj->getField('role')->setFromDB('|' );
                }
                $this->userMap = $factory->createContainer('user_map');
                $this->userMap->username = array('user',$this->userObj->username);            
            }
                
        } else {
            $person = $factory->createContainer('person');
            $application = $factory->createContainer('application');
            if ($this->get_exists('position')) {
                $application->getField('position')->setFromPost($this->get('position'));
            }
            if ($this->creatingNewUser()) {
                $this->userObj = $factory->createContainer('user');
            }
        }
        $this->setObject( $person, self::EDIT_PRIMARY);
        $this->setObject( $application, self::EDIT_CHILD);
        if ($this->userObj instanceof I2CE_Form) {
            $this->setObject( $this->userObj, self::EDIT_SECONDARY);
        }
        if ($this->userMap instanceof I2CE_Form) {
            $this->setObject( $this->userMap, self::EDIT_CHILD);
        }
    }

    /**
     * Load the HTML template files for editing.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $node = $this->template->loadFile( "form_person.html" ,'table');
        if (!$node instanceof DOMNode) {
            return false;
        }
        if (! ($personnode = $this->template->getElementById('list_fields',$node)) instanceof DOMNode) {
            return false;
        }        
        $this->template->appendNodeById($personnode,'person_fields');
        
        $this->template->appendFileById( "form_application.html", "tbody", "application" );
        if ($this->creatingNewUser()) {
            $access = get_class(I2CE::getUserAccess());
            $postfix ='';
            if ($access && ($pos = strpos($access,'I2CE_UserAccess_')) !== false) {
                $postfix = substr($access,15);
            }
            $node = $this->template->loadFile( "user_form{$postfix}.html" ,'table');
            if (!$node instanceof DOMNode) {
                return false;
            }
            if (! ($usernodes = $this->template->getElementById('form',$node)) instanceof DOMNode) {
                return false;
            }        
            if (! ($trNode = $this->template->getElementById('user_fields')) instanceof DOMNode) {
                return true;
            }
            while ($usernodes->hasChildNodes()) {
                $trNode->appendChild($usernodes->firstChild );
            }
            $this->template->findAndRemoveNodes("//*[@name='user:role' or @name='role']",$trNode);
            $map = iHRIS_Module_SelfService::getUserDetailsInPerson();

            foreach ($map as $detail => $field) {
                $this->template->findAndRemoveNodes("//*[@name='user:{$detail}' or @name='{$detail}']",$trNode);
            }
        }
    }
    
    /**
     *Check to see if we are creating a new user
     * @returns boolean
     */
    protected function creatingNewUser() {
        return iHRIS_Module_SelfService::selfRegister();
        //return ($this->request_exists('user_choice') && $this->request('user_choice') == 'new' );
    }


    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited and return to the view page.
     */
    protected function save() {
        if ($this->creatingNewUser()) {
            if (!$this->userObj instanceof I2CE_User_Form || !($username = $this->userObj->username) || !($this->userMap instanceof I2CE_Form)) {
                return false;
            }
            $accessMech = I2CE::getUserAccess();
            if ($accessMech->userExists($username,false)) {
                I2CE::raiseError("Trying to recreate existing user : " .$username);
                return false;
            }
        }
        if (!parent::save()) {
            $this->userMessage("There was an error submitting your application");
            $this->setRedirect('login');

        } else  if ($this->creatingNewUser()) {
            $this->user->login( $this->userObj->username, $this->userObj->password );            
            $this->setRedirect(  "view?id=" . $this->getPrimary()->getNameId() );
        } else {
            $this->userMessage("Thank you for your application");
            $this->setRedirect('login');
        }
    }



    /** 
     * Display the save or confirm buttons as needed.
     * 
     * If the page is a confirmation view then the save / edit button template will be displayed.  
     * Otherwise the confirm and return buttons will be shown.
     * @param boolean $save Flag to show the save button. (Defaults to false)
     * @param boolean $show_edit (defaults to true)
     * @global array
     */
    protected  function displayControls( $save = false, $show_edit = true ) {
        if ( $save ) {
            parent::displayControls( $save, $show_edit );
        }  else {       
            $this->template->addFile( 'button_confirm_notchild.html' );
        }               
    }               


  }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
