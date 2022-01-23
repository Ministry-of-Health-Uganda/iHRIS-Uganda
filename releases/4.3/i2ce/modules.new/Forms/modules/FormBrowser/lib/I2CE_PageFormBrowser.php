<?php
/**
 * @copyright © 2007, 2009 Intrahealth International, Inc.
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
*  I2CE_PageFormBrowser
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_PageFormBrowser extends I2CE_Page{


    public function action() {
        $this->template->setAttribute( "class", "active", "menuConfigure", "a[@href='configure']" );
        $this->template->appendFileById( "menu_configure.html", "ul", "menuConfigure" );
        $this->template->setAttribute( "class", "active", "menuFormBrowser", "a[@href='formBrowser/showForm']" );
        $editForm = false;
        $factory = I2CE_FormFactory::instance();                    
        switch (count($this->request_remainder)) {
        case 2:
            $id = $this->request_remainder[1];
            $form = $this->request_remainder[0];
            break;
        case 1:
            $id = $this->get('id');
            $form = $this->request_remainder[0];
            break;
        case '0':
            if ($this->get_exists('form')) {
                $id = $this->get('id');
                $form = $this->get('form');
                $this->addFormBrowser($form,$id,$editForm);
            } else if ($this->get_exists('id')) {
                $id = $this->get('id');
                $form = $factory->lookupFormByRecordId($id);
            } else if ($this->get_exists('child_form')) { //check for a child form request
                list($form,$id) = explode(':',$this->get('child_form'));
            } else {
                $this->selectForm();
                return;
            }
            break;
        default:  //dont know what to do
            $this->selectForm();
            return;
            break;
        }
        if ($this->page == 'saveForm' && $id > 0 && $this->isPost()) {
            $formObj = $factory->createContainer($form.'|'.$id);
            if ($formObj instanceof I2CE_Form) {
                $formObj->populate();
                $formObj->load($this->post);
                if ($this->saveForm($formObj)) {
                    $this->userMessage("Form updated");
                } else {
                    $this->userMessage("Form not updated");
                    $this->page = 'editForm';
                }
            } else {
                    $this->userMessage("Form not updated -- form could not be created");
                    $this->page = 'editForm';
            }
            $editForm = false;
        }
        if ($this->page ==  'editForm' ) {
            $editForm = true;
        }
        $this->template->addHeaderLink("formBrowser.css");
        $options = array();
        if ($this->post_exists('FBPrefix')) {
            $options['FBPrefix'] = $this->post('FBPrefix');
        } 
        if ($this->get_exists('FBPrefix')) {
            $options['FBPrefix'] =  $this->get('FBPrefix');
        } 
        $browser = new I2CE_FormBrowser($this,$this->page, $options);
        $this->template->appendNodeById(
            $browser->getFormBrowser($form,$id)
            ,'siteContent');
    }

    protected function selectForm() {
        $formBaseConfig = I2CE::getConfig()->modules->forms->forms;
        $forms = $formBaseConfig->getKeys();
        $menuNode = $this->template->addFile( "formBrowser_menu.html", "div" );
        if (!$menuNode instanceof DOMNode) {
            return;
        }
        rsort($forms);
        foreach ($forms as $form) {        
            $formNode = $this->template->appendFileById( "formBrowser_menu_form.html", "li", "formBrowser_menu_list" , $menuNode);
            if (!$formNode instanceof DOMNode) {
                return; //we return instead of  continue b/c there is no sense in trying multiple times when we know its going to fail
            }
            $formConfig = $formBaseConfig->$form;
            $formDispName = '';
            $formDesc ='';
            $formConfig->setIfIsSet($formDispName,'display');
            $formConfig->setIfIsSet($formDesc,"meta/desctiption");
            $this->template->setDisplayDataImmediate("form_name",$form,$formNode);
            $this->template->setDisplayDataImmediate("form_link","formBrowser/showForm/$form",$formNode);
            $this->template->setDisplayDataImmediate("form_dispname",$formDispName,$formNode);
            $this->template->setDisplayDataImmediate("form_desc",$formDesc,$formNode);
        }
    }

    

    protected function saveForm($formObj) {
        $formObj->validate();
        $i2ce_config = I2CE::getConfig()->modules->forms;
        if (!$formObj->hasInvalid()) {
            $this->template->addFile( $i2ce_config->template->form_error );
            $this->userMessage("Invalid Form");
            return false;
        }else {
            $this->userMessage("Valid Form");
            return $formObj->save($this->user); 
        }

    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
