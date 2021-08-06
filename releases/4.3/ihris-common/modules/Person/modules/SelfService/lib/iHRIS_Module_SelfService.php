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
* @subpackage person
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.7
* @since v4.0.7
* @filesource 
*/ 
/** 
* Class iHRIS_Module_SelfService
* 
* @access public
*/


class iHRIS_Module_SelfService extends I2CE_Module {
    

    public function canSelfRegister() {
        return self::selfRegister();
    }

    public static function selfRegister() {
        if (!I2CE::getUserAccess()->canCreateNewUser()) {
            return false;
        }
        $can_register = false;
        I2CE::getConfig()->setIfIsSet($can_register,"/modules/SelfService/can_self_register");
        return $can_register;
    }


    /*
     *Checks to see if we can view the report for the self serice module
     * @param string $report
     * @returns boolan
     */
    public static function hasReport($report) {
        $registered_reports = array();
        I2CE::getConfig()->setIfIsSet($registered_reports,"/modules/SelfService/reports",true);
        return (in_array($report,$registered_reports) 
                && I2CE::getConfig()->is_parent("/modules/CustomReports/reportViews/$report"));
        
    }

    /**
     *Checks to see if we can view the report for the self serice module
     * @param string $report
     *@returns boolan
     */
    public function canViewReport($report) {
        return self::hasReport($report);
    }


    /**
     * Function to see if the user is mapped to a personnel record
     */
    public function userHasRecord() {
        return (iHRIS_UserMap::getPersonId() != '|');
    }


    /**
     *Get the fields in the user form that can be read from the person form
     *@returns array of string
     */ 
    public static function getUserDetailsInPerson() {
        $person = I2CE_FormFactory::instance()->createContainer('person');
        $map = array();
        $accessMech = I2CE::getUserAccess();
        $details = $accessMech->getAllowedDetails();
        if (in_array('lastname',$details)) {
            $map['lastname'] = 'surname';
        }
        foreach ($details as $detail) {
            if ($person->hasField($detail)) {
                $map[$detail] = $detail;
            }
        }
        return $map;            
    }


    /**
     * Template function to see if the user is mapped to a personnel record and if so replace the node with the given link
     *  @param DOMNode $node
     * @param I2CE_Template $template
     * @param string $link
     */
    public function userIsPerson($node,$template) {
        //if (!$node instanceof DOMNode || !$template instanceof I2CE_Template) {
        if ( !$template instanceof I2CE_Template) {
            return false;
        }
        if (!$node instanceof DOMNode) {
            $node = null;
        }
        if ( ($user_personid  = iHRIS_UserMap::getPersonId()) === '|') {
            return false;
        }
        if ( ! ($personObj = $template->getForm('person',$node)) instanceof iHRIS_Person) {
            return false;
        }
        return ($personObj->getNameId() == $user_personid);
    }

    /**
     * Template function to see if the user is mapped to a personnel record and if so replace the node with the given link
     *  @param DOMNode $node
     * @param I2CE_Template $template
     * @param string $link
     */
    public function linkToPersonRecord($node,$template, $link) {
        if (!$node instanceof DOMNode || !$template instanceof I2CE_Template || !$node->parentNode instanceof DOMNode || ($id = iHRIS_UserMap::getPersonId())  == '|') {
            $template->removeNode($node);
            return;
        }
        $a = $template->createElement('a',array('href'=>$link . $id));
        $node->parentNode->replaceChild( $a, $node );
        while ($node->firstChild instanceof DOMNode) {
            $a->appendChild($node->firstChild);
        }
    }


    public function action_user_map($obj) {
        if (!$obj instanceof iHRIS_PageView) {
            return;
        }
        $obj->addChildForms('user_map','siteContent'); //should only be one of them        
        return true;
    }

    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_user_map' => 'action_user_map',
            'I2CE_PermissionParser->hasPermission_userSatisfies' => 'hasPermission_userSatisfies'
            );
    }





    public function hasPermission_userSatisfies($node,$args) {
        if (count($args) != 3) {
            I2CE::raiseError("Two few arguments for permision satosfoes() method");
            return null;
        }
        if (  !I2CE_MagicDataNode::checkKey($relationship = array_shift($args))) {
            I2CE::raiseError("No valid relationship specified");
            return null;
        }
        try {
            $relationshipObj = new I2CE_FormRelationship($relationship);
        } catch (Exception $e) {
            I2CE::raiseError("Relationship $relationship is not valid");
            return null;
        }
        if ($relationshipObj->getPrimaryForm()  != 'person') {
            I2CE::raiseError("Relationship does not have user as its primary form");
            return null;
        }
        array_unshift($args,new I2CE_User());
        return call_user_func_array(array($relationshipObj,'formSatisfiesRelationship'),$args);
    }

    


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
