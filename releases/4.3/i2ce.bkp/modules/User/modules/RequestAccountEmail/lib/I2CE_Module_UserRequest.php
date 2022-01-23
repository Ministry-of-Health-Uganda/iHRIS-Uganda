<?php
/**
* © Copyright 2011 IntraHealth International, Inc.
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
* @package i2ce
* @subpackage user
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1.1
* @since v4.1.1
* @filesource 
*/ 
/** 
* Class I2CE_Module_UserRequest
* 
* @access public
*/


class I2CE_Module_UserRequest extends I2CE_Module {


    public static function getHooks() {
        return array(
            'formfactory_post_construct'=> 'ensureUserRequestClass',
            'autoload_search_for_class'=>'invisibleUserRequestClass',
            'validate_form_user_request_field_email'=>'validate_form_user_request_field_email'
            );         
    }



    /**
     * Validate the email field for contact forms.
     * @param I2CE_FormField $formfield
     */
    public function validate_form_user_request_field_email( $formfield ) {
        $value = $formfield->getValue();
        if ( I2CE_Validate::checkString( $value ) 
             && !I2CE_Validate::checkEmail( $value ) ) {
            $formfield->setInvalidMessage("invalid_email");
        } 
    }



    public function invisibleUserRequestClass($class_name) {
        if ($class_name != 'I2CE_User_Request') {
            return null;
        }
        $userClass = 'I2CE_User_Form';
        I2CE::getConfig()->setIfIsSet($userClass,'/modules/forms/forms/user/class');        
        $functions=array(
            'save'=> 'public function save($user,$transact = true) {return I2CE_Module_UserRequest::userRequestSave($this,$user,$transact);}',
            'populate'=> 'public function populate($repopulate = false) {return I2CE_Module_UserRequest::userRequestpopulate($this,$repopulate);}'
            );
        return  'class ' . $class_name . ' extends ' . $userClass . ' { '  . implode(' ', $functions) . ' } ';
    }
    

    public static function userRequestPopulate($form,$repopulate = false) {
        if (!$form instanceof I2CE_User_Request) {
            return false;
        }
        $mf = I2CE_ModuleFactory::instance();
        $fs = $mf->getClass("forms-storage");
        if (!$fs instanceof I2CE_FormStorage) {
            return false;
        }
        $fs->populate($form,$repopulate);        
        //at this point all of the $form->feilds[blah] have been set, but not the $form->user->$detail
        foreach ($form->getFieldNames() as $fn) {
            if (!($fieldObj = $form->getField($fn)) instanceof I2CE_FormField) {
                continue;
            }
            $form->$fn =  $fieldObj->getDBValue();
        }
        return true;
    }


    public static function userRequestSave($form,$user,$transact=true) {
        if (!$form instanceof I2CE_User_Request) {
            return false;
        }
        $mf = I2CE_ModuleFactory::instance();
        $fs = $mf->getClass("forms-storage");
        if (!$fs instanceof I2CE_FormStorage) {
            return false;
        }
        return $fs->save($form,$user,$transact);
    }


    

    protected $ensured =null;
    public  function ensureUserRequestClass() {
        if (is_bool($this->ensured)) {
            return $this->ensured;
        }        
        $userClass = 'I2CE_User_Form';
        I2CE::getConfig()->setIfIsSet($userClass,'/modules/forms/forms/user/class');        
        $userReqClass = 'I2CE_User_Request';
        I2CE::getConfig()->setIfIsSet($userReqClass,'/modules/forms/forms/user_request/class');
        if (!I2CE_MagicDataNode::checkKey($userReqClass)
            ||  ! ($userReqClassConfig = I2CE::getConfig()->traverse("/module/forms/formClasses/" . $userReqClass ,true)) instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError("bad user request class configuration");
            $this->ensure = false;
            return false;
        }
        $userReqClassConfig->extends = $userClass;
        $this->ensure = true;
        return true;
    }

    public static function verifyUserRequest($request_number) {
        $verify_only = false;
        I2CE::getConfig()->setIfIsSet($verify_only,"/modules/RequestAccount-VerifyEmail/verify_only");
        $ff = I2CE_FormFactory::instance();
        $where =    array(
            'field'=>'request_number',
            'operator'=>'FIELD_LIMIT',
            'style'=>'equals',
            'data'=>array(
                'value'=>$request_number
                )
            );
        $requests = I2CE_FormStorage::search('user_request',false,$where);
        if (count($requests) != 1) {
            return false;
        }
        reset($requests);
        $user_request_id = current($requests);
        if (! ($userRequest = $ff->createContainer('user_request|' . $user_request_id)) instanceof I2CE_User_Request) {
            I2CE::raiseError("Bad request object");
            return false;
        }
        $userRequest->populate();

        if (!($usernameField = $userRequest->getField('username')) instanceof I2CE_FormField_STRING_LINE) {
            I2CE::raiseError("Bad User Request id " . $user_request_id);
            return false;
        }
        $verifiedField = $userRequest->getField('verified');
        $verified = ($verifiedField instanceof I2CE_FormField_BOOL && $verifiedField->getValue());
        if ($verify_only && $verified) {
            //its already been verfied
            return false;
        }
        if (!$verified) {
            if (!self::canAddUser($userRequest,$request_number)) {
                I2CE::raiseError("User already exists or has duplicate requests");
                return false;
            }
            $user = new I2CE_User();
            $userVerify = $ff->createContainer( "user".'|' .$usernameField->getValue());
            $post = $userRequest->getPost();
            if(array_key_exists('fields',$post) && is_array($post['fields']) && array_key_exists('id',$post['fields'])) {
                unset($post['fields']['id']);
            }
            $userVerify->setFromPost($post);
            $userVerify->validate();
            if (!$userVerify->hasInvalid()) {
                I2CE::raiseError("Have invalid");
                return false;
            }
            $creation_role = 'guest';
            I2CE::getConfig()->setIfIsSet($creation_role,"/modules/RequestAccount-VerifyEmail/creation_role");
            $userVerify->role = array('role' , $creation_role);
            $userVerify->save($user);
            if ($verifiedField instanceof I2CE_FormField_BOOL) {
                $verifiedField->setValue(1);
                $userRequest->save($user);
            };
        }
        return $usernameField->getValue();   
        
    }




    public static function manipulatePostForm($post,$old,$new) {
        if (!is_array($post) || !array_key_exists('forms', $post) || !is_array($post['forms']) || !array_key_exists($old,$post['forms'])) {
            return $post;
        }
        $post[$new] = $post[$old];
        return $post;
    }


    public static function canAddUser($requested_user, $request_number = false, $allow_delete = false ) {
        $factory = I2CE_FormFactory::instance();
        if ($request_number) {
            $allow_delete = false; 
        }
        if ( ! $requested_user  instanceof I2CE_User_Request
             || ! ($usernameField = $requested_user->getField('username')) instanceof I2CE_FormField_STRING_LINE
             || ! ($emailField = $requested_user->getField('email')) instanceof I2CE_FormField_STRING_LINE) {
            return false;
        }
        $username = $usernameField->getValue();
        $email = $emailField->getValue();
        $userAccess = I2CE::getUserAccess();       
        $where =    array(
            'field'=>'id',
            'operator'=>'FIELD_LIMIT',
            'style'=>'equals',
            'data'=>array(
                'value'=>$username
                )
            );
         
        $details = array('email'=>$email);

        if (count($details) > 0) {
            $where =
                array(
                    'operator'=>'OR',
                    'operand'=>array($where)
                    );
            foreach ($details as $detail=>$value) {
                $where['operand'][] =
                    array(
                        'field'=>$detail,
                        'operator'=>'FIELD_LIMIT',
                        'style'=>'equals',
                        'data'=>array(
                            'value'=>$value
                            )
                        );
            }
        }
        if ($request_number !== false) {
            $where = 
                array(
                    'operator'=> 'AND',
                    'operand'=>array(
                        $where,
                        array(
                            'operator'=>'NOT',
                            'operand'=>array(
                                array(
                                    'field'=>'request_number',
                                    'operator'=>'FIELD_LIMIT',
                                    'style'=>'equals',
                                    'data'=>array(
                                        'value'=>$request_number
                                        )
                                    )
                                )

                            )
                        )
                    );
        }
        $requested_users = I2CE_FormStorage::search('user_request',false,$where);
        if (!$username  
            || $username == 'i2ce_admin' 
            || $userAccess->getUserID($username) !== false   //a user already exists with this username
            || count($userAccess->getUsersByInfo(false,$details)) > 0 //a user with the given email already exists
            ) {
            return false;
        }
        if (is_array($requested_users) && count($requested_users) > 0) {
            //we already have a user request for this email/request number
            if (!$allow_delete) {
                return false;
            }
            if (!$request_number) {
                //we will want to create a new request, so we need to delete any existing ones
                foreach ($requested_users as $req_id) {
                    if (! ($reqObj = $factory->createContainer('user_request|' . $req_id)) instanceof I2CE_User_Request) {
                        I2CE::raiseError("Bad user_request|$req_id");
                        continue;
                    }
                    I2CE::raiseError("Deleting $req_id");
                    $reqObj->delete();
                }
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
