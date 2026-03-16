<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
 * This page is used to add and edit user records to give access to the site.
 * @package I2CE
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v2.0.0
 * @version v2.0.0
 * @see I2CE_User
 */

/**
 * Object to display the form to edit users in the database.
 * @package I2CE
 * @subpackage Common
 * @access public
 */
class I2CE_PageForm_UserRequestEmail extends I2CE_PageForm {


    /**
     * Perform the actions of the page.
     * 
     * The default method sets up a form object to display the form and confirmation pages
     * for the given object.  It handles everything necessary for editing and saving
     * a single object.  Some forms may need to override this if the actions are more complex.
     * Or more simple. 
     */
    protected function action() {
        if ($this->user->logged_in) {
            I2CE::raiseError("Attempting to access account verification page while logged in");
            $this->setRedirect('home');                
            return true;
        }
        if ($this->request_exists('verify')) {
            $result = $this->verifyAccount($this->request('verify'));
            if ($result) {
                $key = 'verified';
                $message = $this->user->passwordlessLogin( $result );
            } else {
                $key = 'not_verified';
            }
            $msg = false;
            I2CE::getConfig()->setIfIsSet($msg,"/modules/RequestAccount-VerifyEmail/user_messages/" . $key);
            if ($msg) {
                $this->userMessage($msg); 
            }
            $this->setRedirect('home');                
            return true;
            //return $result;
        } else if (  $this->request_exists('resend') && $this->request('resend') && $this->isPost()) {
            return $this->resendVerificationEmail();
            
        } else {
            
            return parent::action();
        }
    }


    protected function verifyAccount($request_number) {
        return I2CE_Module_UserRequest::verifyUserRequest($request_number);
    }


    /**
     * Load the HTML template files for editing and confirming the index information.
     */
    protected function loadHTMLTemplates() {
        $postfix = '';
        $resend = ($this->request_exists('resend') && $this->request('resend'));
        if ($resend) {
            $node = $this->template->addFile( "resend_email.html" );
            if (!$node instanceof DOMNode) {
                return false;
            }
            
        } else {
            $access = get_class(I2CE::getUserAccess());
            if ($access && ($pos = strpos($access,'I2CE_UserAccess_')) !== false) {
                $postfix = substr($access,15);
            }
            $node = $this->template->addFile( "user_form_edit{$postfix}.html" );
            if (!$node instanceof DOMNode) {
                return false;
            }
            $this->updateTemplateNames($node);
        }
    }
    

    protected function updateTemplateNames($node) {
        $qry = '//form[@action=\'user\']';
        $results = $this->template->query($qry,$node);
        if (!$results instanceof DOMNodeList) {
            I2CE::raiseError("Could not get form submission node for user");
            return false;
        }
        foreach($results as $node)  {
            $node->setAttribute('action',$this->pageRoot());
        }
        
        $qry = '//span[@type=\'form\']';
        $results = $this->template->query($qry,$node);
        if (!$results instanceof DOMNodeList) {
            I2CE::raiseError("Could not get form nodes in template");
            return false;
        }
        foreach($results as $node)  {
            if (!$node->hasAttribute('name')
                || ! ($name= trim($node->getAttribute( "name" )))) {
                continue;
            }
            list($old_form,$old_field)= array_pad(explode(":",$name,2),2,'');
            if ($old_form) {
                if ( $old_form != 'user') {
                    continue;
                }
            } else  {
                $old_field = $old_form;
            }
            if ($old_field == 'role') {
                $this->template->removeNode($node);
            }
            $node->setAttribute('name','user_request:' . $old_field);
        }
    }

    protected $requested_user;



    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the index object and if this is a form submission the load
     * the data from the $_POST array.
     */
    protected function loadObjects() {          
        $resend = ($this->request_exists('resend') && $this->request('resend'));
        $factory = I2CE_FormFactory::instance();

        if ($resend) {     
            if ( ! ($this->requested_user = $factory->createContainer( 'user_request'))  instanceof I2CE_User_Form) {
                I2Ce::raiseError("Bad load of user_request ");
                $this->requested_user = false;
                return false;
            }            
            if ($this->isPost()) {
                $this->requested_user->load( $this->post, false,false );          
                $msg = "The requested email address \"%1\$s\" is invalid";
                I2CE::getConfig()->setIfIsSet($msg,"/modules/RequestAccount-VerifyEmail/user_messages/invalid_email");                    
                $email = $this->requested_user->email;
                $msg = sprintf($msg,$email);
                if (!$email) {
                    $this->userMessage($msg); 
                    $this->setRedirect($this->pageRoot());
                    return false;
                }

                //see if we can find a user request for this account
                $where =    array(
                    'field'=>'email',
                    'operator'=>'FIELD_LIMIT',
                    'style'=>'equals',
                    'data'=>array(
                        'value'=>$email
                        )
                    );
                $requested_users = I2CE_FormStorage::search('user_request',false,$where);
                if (count($requested_users) != 1) {
                    $this->userMessage($msg); 
                    $this->setRedirect($this->pageRoot());
                    return false;                    
                }
                $this->requested_user->cleanup();
                $this->requested_user = false;
                $req_id  = current($requested_users);
                if ( !($this->requested_user = $factory->createContainer('user_request|' . $req_id )) instanceof I2CE_Form) {
                    $this->userMessage($msg); 
                    $this->setRedirect($this->pageRoot());
                    return false;                    
                } 
                $this->requested_user->populate();
            }

        } else {
            if ( ! ($this->requested_user = $factory->createContainer( 'user_request'))  instanceof I2CE_User_Form
                 || ! ($usernameField = $this->requested_user->getField('username')) instanceof I2CE_FormField_STRING_LINE
                 || ! ($emailField = $this->requested_user->getField('email')) instanceof I2CE_FormField_STRING_LINE) {
                I2Ce::raiseError("Bad load of user_request ");
                $this->requested_user = false;
                return false;
            }
            if ( $this->isPost() ) {
                $post = I2CE_Module_UserRequest::manipulatePostForm($this->post,'user','user_request');
                $this->requested_user->load( $post, true,false );          
                $req_num = uniqid();
                $this->requested_user->getField('request_number')->setValue($req_num);            
                $username = $usernameField->getValue();
                $email = $emailField->getValue();
                $details = array('email'=>$email);
                if (!I2CE_Module_UserRequest::canAddUser($this->requested_user,false,true)) {
                    $this->requested_user =false;
                    $msg = "The requested username \"%1\$s\" or email address \"%2\$s\" is invalid or already in use";
                    I2CE::getConfig()->setIfIsSet($msg,"/modules/RequestAccount-VerifyEmail/user_messages/invalid_username");
                    $msg = sprintf($msg,$username,$email);
                    $this->userMessage($msg); 
                    $this->setRedirect($this->pageRoot());                    
                }
            }
        }
        if ($this->requested_user) {
            $this->setObject( $this->requested_user );
        }
        return true;
    }

        
    /**
     * Display the save or confirm buttons as needed.
     * @param boolean $save Flag to show the save button.
     * @param boolean $show_edit (defaults to true)
     */
    protected function displayControls( $save = false, $show_edit =true ) {
        if (  $this->request_exists('resend') && $this->request('resend')) {
            $this->template->addFile( "button_request_account_resend.html" );
        } else {
            $this->template->addFile( "button_request_account.html" );
        }
    }
        
    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited and return to the view page.
     */
    protected function save() {
        if (!$this->requested_user) {
            return false;
        }
        parent::save();
        if (!$this->sendVerificationEmail()) {
            $msg = "We had difficulty in sending an e-mail to (%s).  Please contact the system administrator";
            I2CE::getConfig()->setIfIsSet($msg,"/modules/RequestAccount-VerifyEmail/user_messages/verify_email_fail");            
        } else {
            $msg = "Please check your email to confirm your e-mail address (%s) in order to login.";
            I2CE::getConfig()->setIfIsSet($msg,"/modules/RequestAccount-VerifyEmail/user_messages/verify_email");
        }
        $msg = sprintf($msg,  $this->requested_user->email);
        $this->userMessage($msg);
        $this->setRedirect('home');
    }
    
    protected function resendVerificationEmail() {
        I2CE::raiseError("Resending verification email");
        $email = $this->requested_user->email;
        $this->sendVerificationEmail();
        $msg = "Please check your email to confirm your e-mail address (%s) in order to login.";
        I2CE::getConfig()->setIfIsSet($msg,"/modules/RequestAccount-VerifyEmail/user_messages/verify_email");
        $msg = sprintf($msg,  $this->requested_user->email);
        $this->userMessage($msg);       
        $this->setRedirect('home');                
        return true;
    }
 
    protected function sendVerificationEmail() {
        $verify_only = false;
        $msg_html = false;
        I2CE::getConfig()->setIfIsSet($verify_only,"/modules/RequestAccount-VerifyEmail/verify_only");
        if ($verify_only) {
            I2CE::getConfig()->setIfIsSet($msg,"/modules/RequestAccount-VerifyEmail/user_messages/email_message_verify_only");
            I2CE::getConfig()->setIfIsSet($msg_html,"/modules/RequestAccount-VerifyEmail/user_messages/email_message_verify_only_html");
        } else {
            I2CE::getConfig()->setIfIsSet($msg,"/modules/RequestAccount-VerifyEmail/user_messages/email_message");
            if (! (I2CE::getConfig()->setIfIsSet($msg_html,"/modules/RequestAccount-VerifyEmail/user_messages/email_message_html"))) {
                I2CE::raiseError("NO HTML");
            }
        }
        I2CE::getConfig()->setIfIsSet($subject,"/modules/RequestAccount-VerifyEmail/user_messages/email_subject");
        $site_link = I2CE_Page::getAccessedBaseURL()  . '/' . $this->pageRoot() ;
        $link = $site_link . "?verify=" . $this->requested_user->getField('request_number')->getValue();
        $username = $this->requested_user->username;
        $email = $this->requested_user->email;
        $site = 'I2CE';
        I2CE::getConfig()->setIfIsSet($site,"/I2CE/template/prefix_title");
        $msg = sprintf($msg,$username,$link,$site,$site_link);
        if ($msg_html) {
            $msg_html = sprintf($msg_html,$username,$link,$site,$site_link);
        }
        $subject = sprintf($subject,$site);
        if (!$email || !$msg || !$subject) {
            I2CE::raiseError("Invalid message<$msg> or subject<$subject> or email<$email>");
            return false;
        }
        I2CE::raiseError("Want to send email to $email with subject $subject");
        I2CE::raiseError("Want to send email to user $username with message $msg");
        I2CE::raiseError("Want to send email to user $username with message  html " . $msg_html);
        if (!I2CE_Mailer::mail($email,array('Subject'=>$subject),$msg,$msg_html)) {
            I2CE::raiseError("Could not mail verification message to $username / $email");
            return false;
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

    
