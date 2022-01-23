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
* @package I2CE
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1.0
* @since v4.1.0
* @filesource 
*/ 
/** 
* Class I2CE_PageFormBase
* 
* @access public
*/


abstract class I2CE_PageFormBase extends I2CE_Page{
    /**
     * A flag to determine if the page is being edited for the dynamic lists.
     * @var boolean
     */
    protected $editing;
    /**
     * The factory object that creates new instances of forms
     * @var I2CE_FormFactory
     */
    protected $factory;

    /**
     * An array of the type of button templates to be used for thie page
     * @var protected array $button_templates
     */
    protected $button_templates;
        

    public function getButtons() {
        return array(
            'button_save'=> 'button_save.html',
            'button_save_only'=> 'button_save_only.html',
            'button_save_return'=>'button_save_return.html',
            'button_confirm'=> 'button_confirm.html' ,
            'button_return_only'=> 'button_return_only.html'
            );
    }

    /**
     * Create a new instance of a form page.
     * 
     * This will call the constructor for all Page objects and then set up some additional
     * member variables for forms.
     * @param string $title The title for this page.
     * @param string $defaultHTMLFile The default HTML file for this page.
     * @param mixed $access The role required to access this page.
     * @param array $files The list of template files to load for this page.
     */
    public function __construct( $args,$request_remainder, $get = null,$post = null) {
        parent::__construct( $args,$request_remainder,$get,$post);
        $this->template->addHeaderLink('mootools-core.js');
        $this->template->addHeaderLink('I2CE_ClassValues.js');
        $this->template->addHeaderLink('I2CE_SubmitButton.js');
        $this->button_templates = $this->getButtons();
        if (array_key_exists('buttons',$this->args) && is_array($this->args['buttons']))  {
            foreach (array_keys($this->button_templates) as $key) {
                if (array_key_exists($key,$this->args['buttons']) && is_string($this->args['buttons'][$key]) && strlen($this->args['buttons'][$key]) > 0) {
                    $this->button_templates[$key] = $this->args['buttons'][$key];
                }
            }
        }
        $this->editing = false;
        $this->factory = I2CE_FormFactory::instance();
        if (array_key_exists('confirm', $args)) {
            $this->usesConfirmPage = $args['confirm'];
        } else {
            $this->usesConfirmPage = true;
        }
    }


    /**
     * Set this page to be an editing page for the dynamic lists.
     */
    protected final function setEditing() {
        $this->editing = true;
    }
    /**
     * Check to see if this page is an editing page and already has data populated.
     * @return boolean;
     */
    protected final function isEditing() {
        return $this->editing || $this->isPost();
    }

    /**
     * Set the I2CE_Form object in the page template.
     * 
     * This method will pass the edit object to the page template so that it can process all the form variables.
     */
    abstract protected function setForm();


    /**
     * Set the data to be displayed for the outside of the form field elements.
     * 
     * Set up the static data to be displayed in the template.  The default method
     * doesn't do anything, but sub-classes may need to override this method.
     *  */
    protected function setDisplayData() {
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
            if ( $show_edit ) {
                $this->template->addFile( $this->button_templates['button_save'] );
            } else {
                if ($this->usesConfirmPage) {
                    $this->template->addFile( $this->button_templates['button_save_only']);
                } else {
                    $this->template->addFile( $this->button_templates['button_save_return'] );
                }
            }
        }  else {            
            if ($show_edit) {
                $this->template->addFile( $this->button_templates['button_confirm'] );     
            } else {
                $this->template->addFile( $this->button_templates['button_return_only'] );     
            }

        }
    }


    /**
     * Create and load any necessary objects for this form.
     * 
     * This method must be written for each class extending this class.
     * @returns boolean
     */
    abstract protected function loadObjects();


    /**
     * @var protected boolean $checked_validation Flag to see if we already checked validation
     * 
     */
    protected $checked_validation = false;
    /**
     * Run the validation methods for all the objects being edited.
     * 
     * If this is a form submit then run the validation methods for the default object being edited.  The default method
     * calls the {@link I2CE_Form::validate() validate} method on the {@link $edit_obj} object.
     */
    abstract protected function validate();


    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited b
     * @global array
     */
    abstract protected function save();


    /**
     * Checks to see if there are any permissions in the page's args for the given action.
     * If so, it evaluates them.  If not returns true.
     * @returns boolean
     */
    protected function checkActionPermission($action) {
        if (!array_key_exists('action_permission',$this->args) 
            || !is_array($this->args['action_permission']) 
            || !array_key_exists($action,$this->args['action_permission'])
            || !is_scalar($permission = $this->args['action_permission'][$action])
            ) {
            return true;
        }
        return $this->hasPermission($permission);
    }
    /**
     * Checks to see if the user can perform the save action and that all forms are valid
     * @returns boolean     
     */
    protected function canSave() {
        return $this->checkActionPermission('edit');
    }

    /**
     * Checks that all forms are valid, and if so performs the save
     */
    protected function action_save() {
        if (!$this->canSave()) {
            $this->userMessage("You do not have permission to save");
            return false;
        }        
        return $this->save();                 
    }


    /**
     * Checks to see if the page is being submitted as a save operation.
     * @param boolean $check_invalid
     * @return boolean
     */
    protected function isSave($check_invalid = true) {
        if ( ! ( $this->isPost() && $this->post_exists('submit_type') && $this->post( 'submit_type' ) == "save" )) {
            return false;
        }
        if ($check_invalid) {
            //we are trying to save.. need to verify that all is validated
            $this->validate();
            if ($this->hasInvalid()) {
                return false;
            }
        }
        return true;
    }


    /**
     * Checks to see if the page is being submitted as an edit operation.
     * @param boolean $validate Flag to also check to be sure the form data is valid.
     * @return boolean
     */
    protected function isEdit( $validate = true ) {
        if ( $this->post( 'submit_type' ) == "edit" ) {
            if ( $validate ? !$this->hasInvalid() : true ) {
                return true;
            } 
        }
        return false;
    }
    
    /**
     * Checks to see if the page is a confirmation page.
     * @return boolean
     */
    protected function isConfirm($check_invalid = true  ) {
        if ( ! ( $this->isPost() && $this->post_exists('submit_type') && $this->post( 'submit_type' ) == "confirm" )) {
            return false;
        }
        if ($check_invalid) {
            //we are trying to save.. need to verify that all is validated
            $this->validate();
            if ($this->hasInvalid()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Initializes any data for the page
     * @returns boolean.  True on sucess. False on failture
     */
    protected function initPage() {
        if ($this->loadObjects() === false) {
            I2CE::raiseError("Could not load objects");
            return false;
        }
        if (!$this->checkActionPermission('view')) {
            $this->userMessage("You do not have permisison to view this information");
            return false;
        }
        return parent::initPage();
    }
    
    /**
     * Perform the actions of the page.
     * 
     * The default method sets up a form object to display the form and confirmation pages
     * for the given object.  It handles everything necessary for editing and saving
     * a single object.  Some forms may need to override this if the actions are more complex.
     * Or more simple. 
     */
    protected function action() {
        $this->setForm();
        if ($this->isSave(false)) { //the requested action is a save, but no validation checks are performed
            $db = I2CE::PDO();
            if ( !$db->inTransaction() ) {
                try {
                    $db->beginTransaction();
                    $transact = true;
                } catch ( PDOException $e ) {
                    $transact = false;
                }
            }
            if ( $this->isSave()  ) { //this is a save and everything is valid
                if ($this->action_save() !==false) {
                    if ($transact) {
                        try {
                            return $db->commit();
                        } catch ( PDOException $e ) {
                            I2CE::pdoError( $e, "Failed to commit save transaction." );
                            return false;
                        }
                    } else {
                        return true;
                    }
                } else {
                    I2CE::raiseError("Could not save");
                    return false;
                }
            } else {
                //we did not have a valid save state
                if ( $transact && $db->inTransaction() ) {
                    try {
                        $db->rollback();
                    } catch ( PDOException $e ) {
                        I2CE::pdoError( $e, "Failed to rollback save transaction." );
                    }
                }
            }
        }
        //if we made it to here, a save action was not requested or we were not in a valid state to save
        return $this->action_display();
    }



    /**
     * Main action responsible for displaying the forms
     * @returns biikeab true on success
     */
    protected function action_display() {
        $this->setDisplayData();
        if (!  $this->checkActionPermission('edit')) {
            //just show a review
            $save = false;
            $show_edit = false;
            $this->template->setReview();            
        } else if ($this->usesConfirmPage) {
            if ( $this->isConfirm() ) {
                $this->template->setReview();
                $save = true;
                $show_edit = true;
            } else{
                //The invalid message should show up any time at the top of the page
                //in case the required field isn't actually displayed.
                //if ($this->isSave(false)) {  
                    $this->invalidMessage();
                    //we tried a save but failed
                //}
                $save = false;
                $show_edit = true;                
            }
        } else {
            $save = true;
            $show_edit = false;
            if ($this->isSave(false)) {  
                $this->invalidMessage();
                //we tried a save but failed
            }
        }
        $this->displayControls($save,$show_edit); 
    }

    /**
     *Checks to see if any of the forms on this page have invalid messages
     *@returns boolean
     */
    abstract public function hasInvalid();



    /**
     * Add the form_error template to the page if the template is marked as invalid.
     */
    public  function invalidMessage() {  
        if (! $this->hasInvalid()){
            return;
        }
        $i2ce_config = I2CE::getConfig()->modules->forms;        
        return $this->template->addFile( $i2ce_config->template->form_error );
    }



    /**
     * @var protected boolean $usesConfirmPage
     */
    protected $usesConfimPage;
        
    /** Set whether or not we use the confirm page when submitting the form
     * @param boolean $val.  True if we use a confirm page
     */
    public function usesConfirmPage($val) {
        $this->usesConfirmPage = $val;
    }





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
