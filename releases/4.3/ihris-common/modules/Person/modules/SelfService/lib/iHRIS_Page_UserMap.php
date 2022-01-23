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
* @subpackage personm
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.7
* @since v4.0.7
* @filesource 
*/ 
/** 
* Class iHRIS_Page_UserMap
* 
* @access public
*/


class iHRIS_Page_UserMap extends iHRIS_PageFormParentPerson {
    /**
     *Check to see if we are creating a new user
     * @returns boolean
     */
    protected function creatingNewUser() {
        return ($this->request_exists('user_choice') && $this->request('user_choice') == 'new' );
    }
    /**
     * Load the HTML template files for editing and confirming the index information.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        if (!$this->isPost() || $this->creatingNewUser()  ) {
            $postfix = '';
            $access = get_class(I2CE::getUserAccess());
            if ($access && ($pos = strpos($access,'I2CE_UserAccess_')) !== false) {
                $postfix = substr($access,15);
            }
            $node = $this->template->loadFile( "user_form{$postfix}.html" ,'table');
            if (!$node instanceof DOMNode) {
                return true;
            }
            if (! ($usernodes = $this->template->getElementById('form',$node)) instanceof DOMNode) {
                return true;
            }        
            if (! ($trNode = $this->template->getElementById('user_fields')) instanceof DOMNode) {
                return true;
            }
            while ($usernodes->hasChildNodes()) {
                $trNode->appendChild($usernodes->firstChild );
            }
            $this->template->setAttribute( 'display_style', 'user_map',
                    null, "//span[@name='user:role']", $node );
        } 
        
        if ($this->creatingNewUser() ) { 
            $this->template->removeNodeById('existing_user');
            $this->template->removeNodeById('list_fields');            
        } else {
            if ($this->getPrimary()->getField('username')->isValid() || $this->isPost()) { //a user name is set, 
                $this->template->removeNodeById('create_user');
                $this->template->removeNodeById('create_user_fields');
            }                
        }
        if ($this->isPost()) {
            if (!$this->request_exists('user_choice') || !$this->request('user_choice') == 'new') {
                $val = 'existing';
            } else {
                $val = 'new';
            }
            foreach ($this->template->query('//input[@name="user_choice" and @type="radio"]') as $n) {
                if ($n->getAttribute('value') != $val) {
                    $this->template->removeNode($n);
                    continue;
                }
                $n->removeAttribute('checked');
                $n->setAttribute('type','hidden');                
            }
        } else if ($this->getPrimary()->getField('username')->isValid()) {
            foreach ($this->template->query('//input[@name="user_choice" and @type="radio"]') as $n) {
                if ($n->getAttribute('value') != 'existing') {
                    $this->template->removeNode($n);
                    continue;
                }
                $n->removeAttribute('checked');
                $n->setAttribute('type','hidden');                
            }
        }
        return true;
    }

        /**
     * Create and load data for the objects used for this form.
     * 
     * Create the index object and if this is a form submission the load
     * the data from the $_POST array.
     */
    protected function loadObjects() {          
        parent::loadObjects();       
        $factory = I2CE_FormFactory::instance();
        $user = null;
        if ($this->isPost()) {
            if ($this->creatingNewUser()) {
                if (! ($user = $factory->createContainer( 'user')) instanceof      I2CE_User_Form) {
                    I2CE::raiseError("bad user form");
                    return false;
                }
                $user->load( $this->post );
                if ( !($username = $user->username) ) {
                    I2CE::raiseError("bad user name");
                    return false;
                }
                $this->setEditing();
                if ( !$this->isSave(false) ) {
                    $user->tryGeneratePassword();
                }
                $this->getPrimary()->username =  array('user' , $username);
            }
        } else {
            $user = $factory->createContainer( "user".'|0');
            if (( ($personObj = $this->getParent()) instanceof iHRIS_Person) && ($personObj->surname)) {
                $username = $this->generateUserName($personObj);
                $accessMech = I2CE::getUserAccess();
                $details = $accessMech->getAllowedDetails();
                $user->username = $username;
                if (in_array('lastname',$details)) {
                    $user->lastname = $personObj->surname;
                }
                $role = false;
                if (I2CE::getConfig()->setIfIsSet($role,"/modules/SelfService/default_user_role") && I2CE_MagicDataNode::checkKey($role) && I2CE::getConfig()->is_parent("/I2CE/formsData/forms/role/$role")) {
                    $user->getField('role')->setFromDB('role|' . $role);
                }
                foreach($details as $detail) {
                    if ($personObj->hasField($detail)) {
                        $user->getField($detail)->setFromDB( $personObj->getField($detail)->getDBValue());
                    }
                }
                if (I2CE_ModuleFactory::instance()->isEnabled('PersonContact')) {
                    $contact_form = false;
                    if (I2CE::getConfig()->setIfIsSet($contact_form,"/modules/SelfService/default_user_contact_form") && $contact_form) {
                        $personObj->populateChildren($contact_form);
                        foreach ($personObj->getChildren($contact_form) as $contactObj) {
                            foreach($details as $detail) {
                                if ($contactObj->hasField($detail)) {
                                    $user->getField($detail)->setFromDB( $contactObj->getField($detail)->getDBValue());
                                }
                            }                        
                            break;
                        }
                    }
                }
            }
        }
        if ($user instanceof I2CE_User_Form) {
            $this->userObj = $user;
            $this->setObject( $user, I2CE_PageForm::EDIT_SECONDARY, 'user_fields');
        } 
        return true;
    }

    /**
     * @var protected I2CE_User_Form $userObj
     */
    protected $userObj = null;


    /**
     * Generates a user name that is not used in the system
     * @var iRHIS_Person $personObj
     * @returns string
     */
    protected function generateUserName($personObj) {
        $len = strlen($personObj->firstname);
        $found = false;
        $count = 1;
        $accessMech = I2CE::getUserAccess();
        $username = false;
        while ($count <= $len) {
            $username = preg_replace("/\s+/",'',strtolower($personObj->surname . substr($personObj->firstname,0,$count)));
            if (!$accessMech->userExists($username,false)) {
                break;
            }
            $username = false;
            $count++;
        }
        if ($username) {
            return $username;        
        }
        $count =0;
        do {
            $username = preg_replace("/\s+/".'',strtolower($personObj->surname . $count));
            $count++;
        } while ($accessMech->userExists($username,false));
        return $username;
    }

    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited and return to the view page.
     */
    protected function save() {
        if ($this->creatingNewUser()) {
            if (  !$this->hasPermission('task(users_can_edit)')) {
                return false;
            }
            if (!$this->userObj instanceof I2CE_User_Form || !($username = $this->userObj->username) ) {
                return false;
            }
            $accessMech = I2CE::getUserAccess();
            if ($accessMech->userExists($username,false)) {
                I2CE::raiseError("Trying to recreate existing user : " .$username);
                return false;
            }
            if (I2CE_User::hasDetail('creator')) {
                $this->userObj->creator = $this->user->username;
            }
        }
        return parent::save();
    }

    

    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
