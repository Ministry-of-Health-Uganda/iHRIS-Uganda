<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
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
*  I2CE_Module_ModulePrompter
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Module_ModulePrompter extends I2CE_Module{
    public static function getHooks() {
        return array(
            'pre_admin_enable_modules'=>'changeEnableAction',
            'post_admin_enable_modules'=>'cleanupModulePrompter',
            'post_admin_menu_modules'=>'changeEnableVariables'
            );
    }

    public static function getMethods() {
        return array(
            'I2CE_Wrangler->manipulateWrangler_I2CE_home'=>'changeHomePage'
            );
    }



    public function action_enable() {        
        $promptConfig = I2CE::getConfig()->traverse('/modules/modulePrompter/',true);
        $promptConfig->prompt_status = 'not_prompted';
        unset($_SESSION['checked_module_prompt']);
        return true;
    }


    

    public function cleanupModulePrompter($args) {
        $page = $args['page'];        
        if (!$page instanceof I2CE_page) {
            return;
        }
        if ($page->isGet()) {
            return;
        }
        if (!array_key_exists('checked_module_prompt',$_SESSION)) {
            return; //do nothing.
        }
        if ($_SESSION['checked_module_prompt'] !== 'prompting') {
            return; //do nothing.
        }
        if (!$page->post_exists('module_prompter') || !$page->post('module_prompter')) {
            return; //do nothing.
        }
        //if we reached here, we have just enabled modules that we requested via the module prompter.  Let us clean ourselves up.
        $this->destroyMyself();

    }


    public function changeEnableAction($args) {
        $page = $args['page'];
        if (!$page instanceof I2CE_page) {
            return;
        }
        if (!$page->isPost()) {
            return;
        }
        if (!$page->post_exists('continue')) {
            return;
        }
        //we hit the continue button. 
        $page->post('possibles','');
        $this->destroyMyself();
    }


    protected function destroyMyself() {
        $_SESSION['checked_module_prompt'] = 'prompted';
        $promptConfig = I2CE::getConfig()->traverse('/modules/modulePrompter/',true);
        $promptConfig->prompt_status = 'prompted';
        I2CE_Updater::updateModules(array(),array('modulePrompter'));
    }




    public function changeEnableVariables($args) {
        //we check to see if the admin menu was called via the module prompter.  if so, we pass on a hidden variable to the post method
        //so that the post know that it was called from the module prompter.
        //we also add a 'continue' button to the form
        $page = $args['page'];        
        if (!$page instanceof I2CE_Page) {
            return;
        }
        if ($page->isPost()) {
            return;
        }
        if (!array_key_exists('module_prompter',$_GET) || !$_GET['module_prompter']) {
            return;
        }
        $template = $page->getTemplate();
        if (!$template instanceof I2CE_Template) {
            return;
        }
        $results = $template->query('//form[@id="admin_enable_form"]');
        if ($results->length !== 1) {
            return;
        }
        $options = array('name'=>'module_prompter', 'value'=>'1', 'type'=>'hidden');
        $formNode = $results->item(0);
        $formNode->appendChild($template->createElement('input',$options));
        $results = $template->query('.//input[@type="submit" and @id="module_enable_button"]', $formNode);
        if ($results->length !== 1) {
            return;
        }
        $enableNode = $results->item(0);
        $options = array(
            'class'=>'button',
            'type'=>'submit',
            'name'=>'continue',
            'value'=>'Do not enable any'
            );
        $enableNode->parentNode->insertBefore( $template->createElement('input',$options), $enableNode);
        return;
    }


    


    public function changeHomePage($wrangler,$module,$page,$request,$page_root,$page_remainder) {
        $ret = array('module'=>$module,'page'=>$page,'request'=>$request,'pageRoot'=>$page_root,'pageRemainder'=> $page_remainder);
        if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { //this is a post.  don't do anything.
            return $ret;
        }
        $user = new I2CE_User();
        if (!$user->username == 'administrator') { //we are not the admin so dont prompt
            return $ret;
        }
        if ($_SESSION['checked_module_prompt']) { 
            if ($_SESSION['checked_module_prompt'] === 'prompted') { //we already checked if we should enable  modules.  SHould not be here, but this is safety.
                $this->destroyMyself();
                return $ret;
            }
        } else {
            $_SESSION['checked_module_prompt'] = 'prompting';
        }
        //we are the admin and we have not checked this session to see if we should be prompting.
        $promptConfig = I2CE::getConfig()->traverse('/modules/modulePrompter/',true);
        $prompted = ($promptConfig->prompt_status === 'prompted');
        if ($prompted) { //we have already prompted so don't do anything.  we really shouldn't be here but being safe.
            $this->destroyMyself();
            return $ret;
        }
        //we are admin, we have never asked 
        $modules = array();
        $promptConfig->setIfIsSet($modules,'prompt_list',true);
        if (count($modules) == 0) { //no modules to ask about so return
            $this->destroyMyself();
            return $ret; 
        }
        //we have modules to ask about enabling.
        $ret['module'] =  'admin';
        $ret['page'] = 'modules';
        $_GET['possibles'] = implode(':',$modules);  //set up the get variable 
        $_GET['redirect'] = 'home';
        $_GET['module_prompter'] = '1';        
        return  $ret;
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
