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
   * @package I2CE
   * @author Luke Duncan <lduncan@intrahealth.org>
   * @since v2.0.0
   * @version v2.0.0
   */
  /**
   * Object for dealing with system users.
   *
   * This class uses the {@link I2CE_Form} interface to handle editing of users from within
   * the system as well as handles role access for pages.
   * @package I2CE
   * @access public
   */
class I2CE_User_Form extends I2CE_List {


        
    /**
     * Returns the role trickle up from the shortname
     * @param string $name the role shortname
     * @returns array (an empty array if there is no such tag name)
     */
    public static function getTrickleUpFromShortName($name) {
        $roleForm = I2CE_FormFactory::instance()->createContainer("role|$name");
        if (!$roleForm instanceof I2CE_Form) {
            I2CE::raiseError("Unable to instantitate role $name");
            return array();
        }
        $roleForm->populate();
        $trickleField = $roleForm->getField("trickle_up");
        if (!$trickleField instanceof I2CE_FormField) {
            I2CE::raiseError("No field trickle_up for role $name");
            return array();
        }
        $trick = $trickleField->getValue();
        if (!is_array($trick)) {
            I2CE::raiseError("Unexpected value format for trickle up");
            return array();
        }
        return $trick;
    }

                
                
    /**
     * Get the display name associated to a role's shortname
     * @param string $name the shortname of the role
     * @returns string
     */
    static function getRoleNameFromShortName($name) {
        $disp =  I2CE_FormStorage::lookupField('role',$name);
        if ($disp === false) {
            I2CE::raiseError( "Invalid shortname  for getRoleNameFromShortName: $name." );
            return null;                        
        }
        return  $disp;
    }

        


    /**
     * The I2CE_User which makes up this form
     * protected I2CE_User $user
     */
    protected $user;


    /**
     * @var protected array $allowedDetails of string the allowed details
     */
    protected $allowedDetails;
        


    /**
     * Create a new instance of a I2CE_User_Form object.
     * If the username isn't given then it will be determined from the session array.
     * @param I2CE_FieldContainer_Factory $factory
     * @param string $form The name of this form.  Should be 'user'.
     * @param string $username
     */
    public function __construct( $factory,$form,$username = '0') {
        parent::__construct( $factory, $form,  $username);
        $this->user = new I2CE_User($username,true,false,false);
        $access = I2CE::getUserAccess();
        if ($access instanceof I2CE_UserAccess_Mechanism) {
            $this->allowedDetails =  $access->getAllowedDetails();
        } else {
            $this->allowedDetails = array();
        }
        foreach ($this->allowedDetails as $detail) {
            if (array_key_exists($detail,$this->fields)) {
                continue;
            }
            if ($detail == 'locale') {                
                $header = I2CE_User::getDetailName('locale') ;
                $options = array(
                    'in_db'=>true, 
                    'required'=>I2CE_User::isRequired('locale'),
                    'meta'=>array(
                        'limits'=>array(
                            'default'=>array(
                                'locale' => array(
                                    'operator' => 'FIELD_LIMIT',
                                    'field' => 'selectable',
                                    'style'=>'yes',
                                    'data'=>array()
                                    )
                                )
                            ),
                        'display'=>array(
                            'default'=>array(
                                'fields'=>'locale+name'
                                )
                            )
                        )
                    ); 
                $this->fields['locale'] = new I2CE_FormField_MAP('locale',$options);
                $this->fields['locale']->setHeaders(array('default'=>$header));
                $this->fields['locale']->setContainer($this);   
                $locale =  I2CE_Locales::ensureSelectableLocale($this->fields['locale']->getDBValue());
                $this->fields['locale']->setFromDB( 'locale|' . $locale);
                $this->user->locale = $locale;            
            } else {
                $this->fields[$detail] = new I2CE_FormField_STRING_LINE($detail,array('in_db'=>true, 'required'=>I2CE_User::isRequired($detail)));
                $this->fields[$detail]->setHeaders(array('default'=>I2CE_User::getDetailName($detail)));
                $this->fields[$detail]->setContainer($this);
            }
        }
        $this->fields['username'] = new I2CE_FormField_STRING_LINE('username',array('in_db'=>true,'required'=>true));
        $this->fields['username']->setHeaders(array('default'=>'Username'));
        $this->fields['username']->setContainer($this);
    }



    
   /**
    * @return string The first initial and last name of this user.
    */
    public function displayName() {
        return $this->user->displayName();
    }


    /**
     * Magic method to access user info
     * @param string $detail
     * @returns string
     */
    public function __get($detail) {
        switch($detail){
        case 'generate_password':
        case 'password':
        case 'confirm':
            return $this->fields[$detail]->getValue();
        case 'role':
            return $this->user->role;
        case 'username':            
            return $this->user->username;
        case 'user' :
            return $this->user;
        default:
            if (!I2CE_User::hasDetail($detail)) {
                return null;
            }
            if ($detail == 'locale') {
                return 'locale|' . I2CE_Locales::ensureSelectableLocale($this->user->$detail);
            } else {
                return $this->user->$detail;
            }
        }
    }

    

    /**
     * Magic method to set user info
     * @param string $detail
     * @returns string
     */
    public function __set($detail,$value) {
        parent::__set($detail,$value);
        switch($detail){
        case 'password':
            $this->user->setPassword($value);
            break;
        case 'role':
            if (is_string($value)) {
                $value = array_pad(explode('|',$value,2),2,'');
            }
            if (!is_array($value) || count($value) != 2 || $value[0] != 'role') {
                I2CE::raiseError("Invalid role ".  print_r($role,true));
                break;
            }
            $this->user->role = $value[1];
            break;
        case 'username':
            $this->user->username = $value;
            break;            
        default:            
            if (!in_array($detail,$this->allowedDetails)) {
                break;
            }
            if ($detail == 'locale') {
                $value = I2CE_Locales::ensureSelectableLocale($value);
            }
            $this->user->$detail = $value;
        }
    }


    /**
     * Magic method to set user info
     * @returns string
     */
    public function __unset($detail) {
        switch($detail){
        case 'password':
            $this->user->setPassword('');
            break;
        case 'role':
            $this->user->role = '';
            break;
        case 'username':
            $this->user->username = '0';
            break;
        default:
            if (!I2CE_User::hasDetail($detail)) {
                return;
            }
            $this->user->$detail = null;
        }
    }




    /**
     * Populate the member variables of this object.
     * 
     * This will also update the user log to show the latest activity for this login.
     * @param boolean $update_log
     * @global array
     */
    public function populate($repopulate = false) {
        if ($this->id == '0') {
            return;
        }
        $this->user->populate();
        foreach ($this->allowedDetails as $detail) {
            if ($detail == 'locale') {
                $locale = I2CE_Locales::ensureSelectableLocale($this->user->locale);
                $this->fields[$detail]->setFromDB('locale|' . $locale);
            } else {
                $this->fields[$detail]->setFromDB($this->user->$detail);
            }
        }
        $this->fields['username']->setValue($this->user->username);
        //$this->fields['role']->setValue(array('role' , $this->user->role));
        $this->fields['role']->setFromDB('role|'.$this->user->role);
    }


    

    
    /**
     * Change the password for this user.
     * 
     * This will update a user's record to change the password in the database.  It checks to make sure the
     * new password matches the confirmation.
     * 
     * @param array $post
     * @return string The message to display back to the user signifying success or failure.
     * @global array
     */
    public function changePassword( $post ) {
        if ($this->role == 'role|guest') {
            return "That account cannot change passwords.";
        }
        if ( $post['new_password'] != $post['confirm_password'] ) {
            return "Your new password didn't match your confirmed password.  Please try again.";
        }
        $result = $this->user->changePassword($post['new_password'],$post['confirm_password']);
        if ($result === true) {
            return "Your password has been changed.";
        } 
        return $result;
    }


    /**
     * Load the member variables from an array
     * The array can contain the keys 'id', 'parent', 'fields'.  The later of which
     * is an array indexed by field names and which contains the values of the field
     * 
     * @param array $post The post object is passed as a reference
     */
    public function setFromPost($post, $populate_on_set_id = false) {
        if (array_key_exists('fields',$post) || is_array($post['fields'])) {
            $fields = $post['fields'];
            if (array_key_exists('username', $fields)) {
                $this->user->username = $fields['username'];
            }
            if (array_key_exists('role', $fields)) {
                list($junk,$role) = array_pad(explode('|',$fields['role'],2), 2,'');
                $this->user->role = $role;
                if (empty($role)) {
                    $post['fields']['role'] = 'role|';
                }
            }
            foreach ($this->allowedDetails as $detail) {
                if (!array_key_exists($detail,$fields)) {
                    continue;
                }
                $this->user->$detail = $fields[$detail];
            }
        }
        parent::setFromPost($post,$populate_on_set_id);
    }

    

    /**
     * Try to generate a new password and assign it to the password and confirm variables.
     */
    public function tryGeneratePassword() {
        if (!I2CE_User::hasDetail('email')) {
            return false;
        }
        if (!I2CE_Validate::checkEmail($this->email)) {
            return false;
        }        
        if ( $this->generate_password ) {
            $pass = I2CE_User::generatePassword();
            $this->__set('password',$pass);
            $this->__set('confirm',$pass);
        }
        return true;
    }
    
    



    /**
     * Checks to make sure all the required fields are valid.
     *
     * Checks to make sure the username is unique in the system and that the password matches the confirmed password.

     * @global array
     */
    public function validate(  ) {
        parent::validate();
        if ( $this->id == '0' ) {
            if (I2CE_User::userExists($this->username,false)) {
                $this->setInvalidMessage('username','unique'); 
            }
        }
        if ( strlen($this->password) > 0  && $this->password != $this->confirm ) {
            $this->setInvalidMessage('password','mismatch');
        }
        if ( $this->id == '0' && $this->password == "" && !$this->generate_password ) {
            $this->setInvalidMessage('password','required');
        }
        $saving_user = new I2CE_User('0',true,true,false);
        if ( $saving_user->role != 'admin' ) {
            // Allow anyone to set the self service role
            $role_field = $this->getField('role');
            $role_val = $role_field->getDBValue();
            $role_val = str_replace( 'role|', '', $role_val );
            if ( $saving_user->role != $role_val ) {
                $default_ss = '';
                I2CE::getConfig()->setIfIsSet( $default_ss, "/modules/SelfService/default_user_role" );
                if ( $role_val != $default_ss ) {
                    $where = array( 'operator' => 'AND',
                            'operand' => array(
                                array( 'operator' => 'FIELD_LIMIT',
                                    'field' => 'assignable',
                                    'style' => 'yes' ),
                                array( 'operator' => 'FIELD_LIMIT',
                                    'field' => 'trickle_up',
                                    'style' => 'equals',
                                    'data' => array( 'value' => $saving_user->role ) ),
                                array( 'operator' => 'FIELD_LIMIT',
                                    'field' => 'id',
                                    'style' => 'equals',
                                    'data' => array( 'value' => $role_val ) ),
                                ) );
                    $results = I2CE_FormStorage::search( 'role', false, $where );
                    if ( count($results) == 0 ) {
                        $this->setInvalidMessage('role', 'notallowed');
                    }
                }
            }
        }
    }
    
    /**
     * Saves the user to the database.
     * 
     * This method saves all the user data and updates the access the user has for this system.
     * @global array
     */
    public function save($user,$transact =true) {
        I2CE_ModuleFactory::callHooks( "form_pre_save", array( 'form' => $this, 'user' => $user ) );
        foreach ($this->allowedDetails as $detail) {
            if (!array_key_exists($detail,$this->fields)) {
                I2CE::raiseError("For " . $this->getFormID() . ", trying to access field $detail which is not in class " . get_class($this).  "\nAvail = " . implode(" ", array_keys($this->fields)));
                continue;
            }
            $this->user->$detail = $this->fields[$detail]->getValue();
        }
        $this->user->username = $this->username;
        list($junk,$role) = $this->fields['role']->getValue();
        $this->user->setRole($role);
        if (!   $this->user->save($this->password)) {
            return false;
        }
        I2CE_ModuleFactory::callHooks( "form_post_save", array( 'form' => $this, 'user' => $user ) );
        return true;
    }




}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
