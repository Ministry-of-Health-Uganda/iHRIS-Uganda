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
 * Default Admin Page
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */

class I2CE_PageAdmin extends I2CE_Page {

        
    /**
     * @var protected string $shortname the shortname of a module's config page we should be displaying
     */
    protected $shortname;
    /**
     * @var protected array $args of strings -- any arguments for this page( from the URL)
     */
    protected $args;


    /**
     * Constructor -- called from page wrangler for URL of form admin/(module_name)/action/arg1/arg2
     * @param string $shortname A module name to show the admin page for. Defaults to null to mean no module.
     * @param string $action The action this pages is to perform
     * @param array $args an array of strings
     * @param string $message. A message to display on the page.  Defaults to null
     */
    public function __construct($args,$request_remainder) {
        parent::__construct($args,$request_remainder);
        if (count($request_remainder) == 0) {
            $this->shortname = 'I2CE';
        } else {
            $this->shortname = array_shift($request_remainder);
        }
        $this->mod_factory = I2CE_ModuleFactory::instance(); 
    }


    /*
     * Perform the actions for this page
     */
    protected function action() {
        parent::action();
        switch ($this->page) {
        case 'enable':
            if ($this->isPost()) {
                $this->actionEnableDisable();
            }                   
            break;
        case 'modules':
        default:            
            $this->actionMenu();
            break;
        } 
    }



        
    protected function actionEnableDisable() {
        I2CE_ModuleFactory::callHooks('pre_admin_enable_modules',array('page'=>$this));
        if ($this->post_exists('redirect') && $this->post('redirect')) {
            $redirect = $this->post('redirect');
        } else  if ($this->shortname == 'I2CE') {
            $redirect ="admin/modules";
        } else {
            $redirect ="admin/modules/{$this->shortname}";
        }
        if (!$this->post_exists('possibles') || !$this->post('possibles')) {
            $this->redirect($redirect);
            return;
        }
        $possibles = explode(':',$this->post('possibles'));
        $enable = array();
        if ($this->post_exists('modules')) {
            $enable = $this->post('modules');
        }
        $disable = array_diff($possibles,$enable);
        $msg = '';
        $optional_excludes = $disable;
        foreach ($enable as $i=>$e) {
            if ($this->mod_factory->isEnabled($e)) {
                unset($enable[$i]);
            }
        }
        foreach ($disable as $i=>$d) {
            if (!$this->mod_factory->isEnabled($d)) {
                unset($disable[$i]);
            }
        }
        if (count($enable) > 0) {
            $msg .= '<p>modules enabled: ' . implode(' ' , $enable) . "</p>";
        } 
        if (count($disable) > 0) {
            $msg .= '<p>modules disabled: ' . implode(' ' , $disable) . "</p>";
        } 
        if (I2CE_Updater::updateModules($enable,array(),$optional_excludes, $disable)) {
            $this->userMessage("Success" . $msg . "<br/>");
        } else {
            $this->userMessage("Failure on:" . $msg . "<br/>");
        }
        I2CE_ModuleFactory::callHooks('post_admin_enable_modules',array('page'=>$this,'possibles'=>$possibles,'enable'=>$enable, 'disable'=>$disable));
        $this->redirect($redirect);
    }



        



    protected function actionMenu() {           
        I2CE_ModuleFactory::callHooks('pre_admin_menu_modules',array('page'=>$this));
        $this->template->setBodyId( "adminPage" );
        $config = I2CE::getConfig()->config;
        $this->template->setAttribute( "class", "active", "menuConfigure", "a[@href='configure']" );
        $this->template->appendFileById( "menu_configure.html", "ul", "menuConfigure" );
        $this->template->setAttribute( "class", "active", "menuConfigureModules", "a[@href='admin/modules']" );
        if ($this->isGet() && $this->get_exists('redirect') && $this->get('redirect')) {
            $this->template->setDisplayData( "redirect", $this->get('redirect'));
        }
        $siteModule = '';
        $this->mod_factory->checkForNewModules(); //re-read all the module information available to the system
        $config->setIfIsSet($siteModule,'site/module');               
        //now we get the sub-modules of the current module
        if ($this->isGet() && $this->get_exists('possibles')) {
            $modules = explode(':',$this->get('possibles'));
        } else  if ($this->shortname == 'I2CE') {
            $modules = array();
            if ($siteModule) {
                $modules = $this->mod_factory->checkForNewModules($siteModule);
            }
            $t_modules = $this->mod_factory->checkForNewModules('I2CE');
            foreach ($t_modules as $module) {
                if (!in_array($module,$modules)) {
                    $modules[] = $module;
                }
            }
        } else{
            $modules = $this->mod_factory->checkForNewModules($this->shortname);
        }
        $this->template->addHeaderLink("admin.css");
        $this->template->addHeaderLink("mootools-core.js");
        $this->template->addHeaderLink("admin.js");
        $cats = array();
        foreach ($modules as $module) { 
            $dispName =$config->data->$module->displayName;
            if ($dispName != 'I2CE') {
                $dispName =  preg_replace('/^I2CE\s+/','',$dispName);
            }
            
            if (isset($config->data->$module->category)) { 
                $cats[$config->data->$module->category][$module] = $dispName;
            } else { 
                $cats['UNCATEGORIZED'][$module] = $dispName;
            } 
        }
        ksort($cats);
        $compare = create_function('$m,$n','return strcasecmp($m,$n);');
        foreach ($cats as $cat=>$modules) {
            uasort($modules,$compare);
            $cats[$cat] = $modules;
        }
        $menuNode = $this->template->addFile( "module_menu.html", "div" );
        $displayData = array('module_link'=>'link',
                             'module_description'=>'description',
                             'module_email'=>'email',
                             'module_email_2'=>'email',
                             'module_version'=>'version',
                             'module_creator'=>'creator',
                             'module_name'=>'displayName');
        $possibles = array();
        $configurator = new I2CE_Configurator(I2CE::getConfig());
        foreach ($cats as $cat=>$modList) {
                        
            $catNode = $this->template->appendFileById( "module_category.html", "div", "modules" );
            if (!$catNode instanceof DOMNode) {
                continue;
            }
            if ($cat != 'UNCATEGORIZED') {
                $this->template->setDisplayData( "module_category", $cat,$catNode);
            } else {
                $this->template->setDisplayData( "module_category", 'Uncategorized',$catNode);
            }
            foreach ($modList as $module=>$displayName) {
                $conflicting = false;
                $modNode = $this->template->appendFileByName( "module_module.html", "li", "module_list", 0, $catNode );
                if (!$modNode instanceof DOMNode) {
                    continue;
                }
                $modNode->setAttribute('id','tr_'.$module);
                $modNode->setAttribute('name',$module);
                $data = $config->data->$module;
                $origEnableNode = $this->template->getElementByName('module_enable',0);
                    
                if ($module == 'I2CE') {
                    $origEnableNode->parentNode->removeChild($origEnableNode);
                } else if ($module ==  $config->site->module) {
                    $origEnableNode->parentNode->removeChild($origEnableNode);
                } else {
                    $reqs = $configurator->getDependencyList($module);
                    $badness = '';
                    $origEnableNode->setAttribute('id',$module);
                    if (array_key_exists('badness',$reqs) && !empty($reqs['badness'])) {
                        $badness = $reqs['badness'];
                        if ($this->mod_factory->isEnabled($module)) {
                            $checked = "checked='checked' disabled='disabled'"; //shouldn't be
                        } else {
                            $checked = ' disabled="disabled"';
                        }
                        $conflicting = true;
                        $modNode->setAttribute('class','conflict');
                        $deps = implode(',',$reqs['requirements']);
                        $optional = implode(',',$reqs['enable']);
                        $conflicts = implode(',',$reqs['conflicts']);
                        $html =                                                 
                            " <div class='check'  deps='$deps' cons='$conflicts' opt='$optional'>
    <input type='checkbox' name='modules[]' value='$module' $checked id='input_enable_$module' />
  </div>
";
                        $origEnableNode->parentNode->replaceChild( $this->template->importText($html), $origEnableNode );
                        
                    } else if ($configurator->moduleRequires($module,$data->version,$config->site->module)) {

                        if ($this->mod_factory->isEnabled($module)) {
                            $checked = "checked='checked'";
                        } else {
                            $checked = '';
                        }
                        $deps = implode(',',$reqs['requirements']);
                        $optional = implode(',',$reqs['enable']);
                        $conflicts = implode(',',$reqs['conflicts']);
                        $html =                                                 
                            "<div class='check'  deps='$deps' cons='$conflicts' opt='$optional'>
    <input type='hidden' name='modules[]' value='$module' id='input_enable_$module' />
    <input type='checkbox' $checked disabled='disabled' value='$module' $checked ' />
  </div>
";
                        $origEnableNode->parentNode->replaceChild( $this->template->importText($html), $origEnableNode );
        
                    } else if ($configurator->moduleConflicts($module,$data->version,$config->site->module)) {                                          
                        if ($this->mod_factory->isEnabled($module)) {
                            $checked = "checked='checked' disabled='disabled'";
                        } else {
                            $checked = ' disabled="disabled"';
                        }
                        $conflicting = true;
                        $modNode->setAttribute('class','conflict');
                        $deps = implode(',',$reqs['requirements']);
                        $optional = implode(',',$reqs['enable']);
                        $conflicts = implode(',',$reqs['conflicts']);
                        $html =                                                 
                            " <div class='check' id='$module' deps='$deps' cons='$conflicts' opt='$optional'>
    <input type='checkbox' name='modules[]' value='$module' $checked id='input_enable_$module' />
  </div>
";
                        $origEnableNode->parentNode->replaceChild( $this->template->importText($html), $origEnableNode );

                    } else {
                        if ($this->mod_factory->isEnabled($module)) {
                            $checked = "checked='checked'";
                        } else {
                            $checked = '';
                        }
                        $deps = implode(',',$reqs['requirements']);
                        $optional = implode(',',$reqs['enable']);
                        $conflicts = implode(',',$reqs['conflicts']);
                        $html =                                                 
                            " <div class='check' id='$module' deps='$deps' cons='$conflicts' opt='$optional'>
    <input type='checkbox' name='modules[]' value='$module' $checked id='input_enable_$module' />
  </div>
";
                        $origEnableNode->parentNode->replaceChild( $this->template->importText($html), $origEnableNode );
                        $possibles[] = $module;
                    }
                                        
                }
                $display = array();
                foreach ($displayData as $name=>$dd) {
                    if (isset($data->$dd)) {
                        $display[$name] = $data->$dd;
                    } else {
                        $display[$name] = '';
                    }
                }
                if (!empty($display['module_email'])) {
                    if (! (substr($display['module_email'],0,6) == 'mailto')) {
                        $display['module_email'] =  'mailto://' . $display['module_email'];
                    }
                }
                $display['module_message'] = '';
                foreach ($display as $name=>$val) {
                    $this->template->setDisplayData($name,$val,$modNode);
                }
                $module_menu =    'admin/modules';
                if ($module != 'I2CE') {
                    $module_menu .=  '/' . $module;
                }
                $module_configure = '';                 
                if (!$conflicting
                    && (!isset($data->noConfigData) || $data->noConfigData != 1)
                    && $this->mod_factory->isInitialized($module)
                    && $this->mod_factory->isEnabled('swissConfig')) {
                    $module_configure =   'swissConfig/edit/' . $module;
                }
                $menuLinkNodeList = $this->template->query(".//a[@name='module_menu']",$modNode );
                if ($menuLinkNodeList->length > 0) {
                    $menuLinkNode= $menuLinkNodeList->item(0);
                }
                if ( $menuLinkNode instanceof DOMElement) {
                    $menuLinkNode->setAttribute('id','menu_link_' .$module);
                }                               
                if ((!isset($data->paths->MODULES)) 
                    || ( ($this->shortname==         $module ))
                    || ($conflicting)
                    ){
                    $module_menu = '';
                } else if ($this->hasAjax() === true) {
                    $subModNode = $this->template->appendFileByName( "module_sub_module.html", "li", "module_list", -1 );
                    if ($subModNode instanceof DOMElement) {
                        $subModNode->setAttribute('id',"sub_module_li_$module");
                    }
                    $subModDiv = $this->template->query("./descendant-or-self::node()[@id='sub_module']",$subModNode);
                    if ($subModDiv->length > 0) {
                        $subModDiv = $subModDiv->item(0);
                    }
                    if ($subModDiv instanceof DOMElement) {
                        $subModDiv->setAttribute('id',"sub_module_$module");
                    }
                    $arrowNode = $this->template->createElement('img',array('src'=>'file/admin-arrow-down.gif',
                                                                            'id'=>"menu_link_arrow_$module"));
                    $menuLinkNode->parentNode->appendChild($arrowNode); 
                    $this->addAjaxUpdate("sub_module_$module","menu_link_arrow_$module",'click',$module_menu,"modules",true);  //fuzzy method from stub module
                    $this->addAjaxCompleteFunction("menu_link_arrow_$module","Modules.update(\"$module\");");   //fuzzy method from stub module 
                }
                $this->template->setDisplayDataImmediate("module_configure",$module_configure,$modNode); 
                $this->template->setDisplayDataImmediate("module_menu",$module_menu,$modNode);                           
            }
        }
        $possibleNode = $this->template->getElementById('module_possibles', $menuNode);
        if ($possibleNode instanceof DOMElement) {
            $possibleNode->setAttribute('value',implode(':',$possibles));
        }
        $formNode = $this->template->getElementByName('admin_enable_form',0);
        if ($formNode instanceof DOMElement) {
            $action = 'admin/enable';
            if ($this->shortname != 'I2CE') {
                $action .= '/' . $this->shortname;
            }  
            $formNode->setAttribute('action',$action);
        }               
        I2CE_ModuleFactory::callHooks('post_admin_menu_modules',array('page'=>$this,'possibles'=>$possibles));
    }
        
        
        


}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
