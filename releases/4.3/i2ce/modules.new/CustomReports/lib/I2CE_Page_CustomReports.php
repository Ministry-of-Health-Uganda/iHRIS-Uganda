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
*  I2CE_Page_ReportRelationship
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Page_CustomReports extends I2CE_Page {

    
    /**
     * Constructor -- called from page wrangler for URL of form admin/(module_name)/action/arg1/arg2
     * @param string $shortname A module name to show the admin page for. Defaults to null to mean no module.
     * @param string $action The action this pages is to perform
     * @param array $args an array of strings
     */
    public function __construct($args,$request_remainder, $get=null, $post = null) {
        parent::__construct($args,$request_remainder, $get,$post);
        $this->configPath = $request_remainder;        
    }
    
    /**
     *  @var protected array $configPath.  The array of  path components where we are editting
     */
    protected $configPath;


    /**
     * Perform any command line actions for the page
     *
     * The business method if this page is called from the commmand line
     * @param array $request_remainder the remainder of the request after the page specfication.  
     * @param array $args the array of unix style command line arguments 
     * Arguements are link that in: http://us3.php.net/manual/en/features.commandline.php#78651
     * If we were called as: 
     *      index.php --page=/module/page/some/thing/else --long  -AB 2 -C -D 'ostrich' --eggs==good
     * Then $request_remainder = array('some','thing','else')
     * and $args = array('long'=>true, 'A'=>true, 'B'=>2, 'C'=>true, 'D'=>'ostrich', 'eggs'=>'good')
     * @returns boolean.  true on success
     */ 
    public function actionCommandLine($args, $request_remainder) {
        parent::action();
        if ($this->page === 'show') {
            $this->template->setAttribute( "class", "active", "menuCustomReports", "a[@href='CustomReports/view/reportViews']" );
            return $this->actionShow($this->request_remainder);
        } else  if (($this->page === 'delete')) {
            return $this->actionDelete();            
        } elseif (($this->page ==='generate')) {
            if (!$this->actionGenerate(false)) {
                $msg = "Could not generate report";
                I2CE::raiseError($msg);
                return false;
            }
            return true;
        } elseif (($this->page ==='generate_force')) {
            if (!$this->actionGenerate(true)) {
                $msg = "Could not generate report";
                I2CE::raiseError($msg);
                return false;
            }
            return true;        
        } elseif (($this->page ==='flash_data')) {
            $this->get('flash_data', true);
            //$this->actionView();
            $view = array_shift($this->request_remainder);
            $displayObj = new I2CE_CustomReport_Display_PieChart($this,$view);                
            $displayObj->display(null);
        } elseif ( $this->page === 'generate_complete' ) {
            //generate caches.
            // This is no longer necessary because each report will cache the required forms
            // so this is just extra processing.
            /*
            $wrangler = new I2CE_Wrangler();
            $cachedFormPage = $wrangler->getPage('CachedForms','cacheAll');
            if ($cachedFormPage instanceof I2CE_Page) {
                I2CE::raiseError( "Forcing cache of all forms." );
                $cachedFormPage->cacheAll( false );
            }       
            */
            if (!$this->actionGenerate(true)) {
                I2CE::raiseError( "Could not generate report" );
                return false;
            }
            return true;
        }
    }

    
    /**
     * Perform any actions for the page
     * 
     * @returns boolean.  true on sucess
     */
    public function action() {
        parent::action();
        if (!$this->hasPermission('task(custom_reports_can_access)')) {
            return false;
        }
        $msgs = array(
            'not_generated'=>'Could not generate report',
            'not_saved'=>'Could not save display options',
            'saved'=>'Saved display options');
        foreach ($msgs as $k=>&$v) {
            I2CE::getConfig()->setIfIsSet($v,"/modules/CustomReports/text/user_messages/$k");
        }

        if (($this->page === 'delete')) {
            return $this->actionDelete();            
        }
        if (($this->page ==='generate')) {
            $this->setRedirect('CustomReports/edit/reports');
            if (!$this->actionGenerate(false)) {
                $msg = $msgs['not_generated'];
                I2CE::raiseError($msg);
                $this->userMessage($msg,'notice');
                return false;
            }
            return true;
        } 
        if (($this->page ==='generate_force')) {
            $this->setRedirect('CustomReports/edit/reports');
            if (!$this->actionGenerate(true)) {
                $msg = $msgs['not_generated'];
                I2CE::raiseError($msg);
                $this->userMessage($msg,'notice');
                return false;
            }
            return true;
        } 
        $this->template->addHeaderLink("CustomReports.css");
        $this->template->addHeaderLink("CustomReports_iehacks.css", array('ie6' => true ));
        $which = false;
        if ($this->page === 'saveOptions') {            
            if (!$this->actionSaveOptions()) {
                $this->userMessage($msgs['not_saved'],'notice',false); //message is not delayed as we are showing below
            } else {
                $this->userMessage($msgs['saved'],'notice',false);  //message is not delayed as we are showing below
            }
            reset($this->request_remainder);
            return $this->actionShow(array(current($this->request_remainder))); //display the default page
        }
        if ($this->page === 'show') {
            $this->template->setAttribute( "class", "active", "menuCustomReports", "a[@href='CustomReports/view/reportViews']" );
            return $this->actionShow($this->request_remainder);
        }

        if ( array_key_exists( 0, $this->request_remainder )) {            
            $which = $this->request_remainder[0];
            $handledByFactory = $this->tryFactory($which);
            if ($handledByFactory !== 'not_handled' ) {
                return $handledByFactory !== false;
            }
        }
        return true;
    }


    protected function tryFactory($which) {
        if ($this->page == 'view' && $which == "reportViews") {
            $this->template->setAttribute( "class", "active", "menuCustomReports", "a[@href='CustomReports/view/reportViews']" );
        } else {
            //$this->template->appendFileById( "customReports_nav_menu.html", "ul", "menuCustomReports" );
            $this->template->setAttribute( "class", "active", "menuConfigure", "a[@href='configure']" );
            $this->template->appendFileById( "menu_configure.html", "ul", "menuConfigure" );
        }
        $this->template->setAttribute( "class", "active", "menuCustomReports", "//li/a[@href='CustomReports/edit/" . $which . "']");
        $factory_actions = array('view','edit','update');
        $root_types = array(
            'relationships'=>'FormRelationships',
            'reports'=>'CustomReports_Reports',
            'reportViews'=>'CustomReports_ReportViews'
            );            
        if (in_array($this->page, $factory_actions) &&  (array_key_exists($which,$root_types))) {
                $init_options = array(
                    'root_url_postfix'=>$which,
                    'root_path'=>'/modules/CustomReports/' . $which,
                    'root_path_create'=>true,                    
                    'root_type'=>$root_types[$which]);     
					I2CE::raiseError('WHICH: '.$which);
                try {
                    $swiss_factory = new I2CE_SwissMagicFactory($this,$init_options);
                } catch (Exception $e) {
                    I2CE::raiseError("Could not create swissmagic for $which:" . $e->getMessage());
                    return false;
                }
                try {
                    $swiss_factory->setRootSwiss();
                } catch (Exception $e) {
                    I2CE::raiseError("Could not create root swissmagic for $which:" . $e->getMessage());
                    return false;
                }
                $swiss_path = $this->request_remainder;
                array_shift($swiss_path); 
                $action = $this->page;
                if ($action == 'update' && $this->isPost()) {
                    if ($this->get('noRedirect')) {
                        $redirect = false;
                    } else {
                        $redirect = true;
                    }
                    $msgs = array(
                        'not_updated'=>'Unable to Update Values',
                        'updated'=>'Updated Values');
                    foreach ($msgs as $k=>&$v) {
                        I2CE::getConfig()->setIfIsSet($v,"/modules/CustomReports/text/user_messages/$k");
                    }

                    if ( $swiss_factory->updateValues($this->post(),$redirect)) {                        
                        $msg = $msgs['updated'];
                    } else {
                        $msg = $msgs['not_updated'];
                    }
                    if ($redirect) {
                        $this->userMessage($msg, 'notice',true);
                        $swiss = $swiss_factory->getSwiss($swiss_path);
                        if ($swiss instanceof I2CE_Swiss) { 
                            $redirect = $swiss->getURLRoot('edit') . $swiss->getPath() . $swiss->getURLQueryString();
                        } else {
                            $redirect ='CustomReports/edit/' . $which ;
                        }
                        $this->setRedirect($redirect);
                        return true;
                    }
                }
                if ($action == 'update') {
                    $action = 'edit';
                }
                return $swiss_factory->displayValues( $this->template->getElementById('siteContent'),$swiss_path, $action);
        }
        return 'not_handled'; //we did not handle this with the factory
    }
            
    
    protected function actionDelete() {
        $msgs = array(
            'cannot_delete'=>'You don\'t have access to delete report data',
            'bad_delete_path'=>"Invalid path for deletion"
            );
        foreach ($msgs as $k=>&$v) {
            I2CE::getConfig()->setIfIsSet($v,"/modules/CustomReports/text/user_messages/$k");
        }

        if (! $this->hasPermission('task(custom_reports_delete_reports)')) {
            $this->userMessage($msgs['cannot_delete'],false);
            return false;
        }
        $request = $this->request_remainder;
        if (count($this->request_remainder)  < 2) {
            $this->userMessage($msgs['bad_delete_path'] . ": /modules/CustomReports/" . implode('/',$request),false);
            return false;
        }
        $config = I2CE::getConfig()->traverse('/modules/CustomReports/' . implode('/',$request),false,false);
		$pathis = '/modules/CustomReports/' . implode('/',$request);
		I2CE::raiseError($pathis);
        if (!$config instanceof I2CE_MagicDataNode) {
            $this->userMessage($msgs['bad_delete_path'] .": /modules/CustomReports/" . implode('/',$request),false);
            return false;
        }
        $config->erase();
        if (array_key_exists('HTTP_REFERER',$_SERVER)) {            
            $this->redirect($_SERVER['HTTP_REFERER']);
        } else {
            array_pop($request);
            $this->redirect('CustomReports/' . implode('/',$request));
        }
        return true;
    }


    protected function actionGenerate($force) {
        /* Why not allow multiple reports to generate at once?
        if (count($this->request_remainder) > 1 ) {
            I2CE::raiseError("Requested generation of invalid report " . implode('/', $this->request_remainder));
            return false;
        }
        */
        if (count($this->request_remainder) == 0) {
            $config = I2CE::getConfig()->modules->CustomReports;
            $config->generate_all->volatile(true);


            $timeConfig = I2CE::getConfig()->traverse('/modules/CustomReports/times',true);        
            $timeConfig->volatile(true);
            $fail_time  = null;
            $timeConfig->setIfIsSet($fail_time,'fail');
            if (!is_integer($fail_time)) {
                $fail_time = 600;
            }
            $fail_time = (int)( ((int)$fail_time) * 60);        

            $generation = 0;
            $timeConfig->setIfIsSet($generation,"generate_all/time");        
            if (  (!(is_integer($generation) || ctype_digit($generation))) || ((int)$generation) < 1) {
                $generation = 0;
            }
            $generation = (int) $generation;
            
            $config->setIfIsSet($status,'generate_all/status');
            if ( $status === 'in_progress' && (  (!$force) || ((  (time() - $generation ) <   $fail_time)))) {
                I2CE::raiseError("In progress");
                return true;
            }
            $config->generate_all->status = 'in_progress';
            $config->generate_all->time = time(); //update the time 
            $reports = I2CE::getConfig()->modules->CustomReports->reports->getKeys();
            $all = true;
        } else {
            $reports = $this->request_remainder;
            $all = false;
        }        
//         //generate caches.
//         $wrangler = new I2CE_Wrangler();
//         $cachedFormPage = $wrangler->getPage('CachedForms','cacheAll');
//         if ($cachedFormPage instanceof I2CE_Page) {
//             $cachedFormPage->cacheAll();
//         }       
        $errors = array();
        foreach ($reports as $report) {
            if (!I2CE_CustomReport::reportExists($report)) {
                I2CE::raiseError("Requested generation of report $report which does not exist");
                $errors[] = "Requested generation of report $report which does not exist";
                continue;
            }
            try {
                $reportObj = new I2CE_CustomReport($report);
            }
            catch (Exception $e) {
                $errors[] = "Could not instantiate the report $report";
                continue;
            }
            if (!$reportObj->generateCache($force)) {
                $errors[] = "Could not generate report for $report";
            }
        }
        if ($all) {
            $hook_errs = I2CE_ModuleFactory::callHooks( 'custom_reports_post_generate_all', $force );
            foreach( $hook_errs as $hook_err ) { 
                if ( $hook_err && $hook_err !== true ) { 
                    if ( is_string( $hook_err ) && strlen( $hook_err ) > 0 ) {
                        $errors[] = $hook_err;
                    } else {
                        $errors[] = "An unknown error occurred trying to run post custom report generation hook.";
                    }
                }   
            }
            if (count($errors) > 0) {
                $config->generate_all->status = 'failed';
            } else {
                $config->generate_all->status = 'done';
            }
            $config->generate_all->time = time();
        }
        foreach ($errors as $error) {
            $this->userMessage($error,'notice');
        }
        return count ($errors)  == 0;
    }


    protected function canAccessReportView($view) {
        $config = I2CE::getConfig()->modules->CustomReports;
        $permissions = array();
        if (isset($config->reportViews->$view->limit_view_to) && $config->reportViews->$view->limit_view_to) {
            //this is a general permission string
            $permissions[] = $config->reportViews->$view->limit_view_to;
        }
        if (isset($config->reportViews->$view->limit_view_to_task) && $config->reportViews->$view->limit_view_to_task) {
            //this is a specific task
            $permissions[] = 'task(' . $config->reportViews->$view->limit_view_to_task . ')';
        }
        if (count($permissions) == 0) {
            return true;
        }
        $permissions[] = 'task(custom_reports_admin)';
        return $this->hasPermission(implode(' or ' , $permissions));
    }



    protected function getDisplayObj($request) {
        // expected format for URL is CustomReports/show/reportView(/display)
        $msgs = array(
            'no_report_view'=>'You need to specify a report to view',
            'invalid_report_view'=>"You specified an invalid report to view",
            'no_permission_report_view'=>"You do not have permission to view this report",
            'no_display_report_view'=>"Could not find any valid displays for this report view"
            );
        foreach ($msgs as $k=>&$v) {
            I2CE::getConfig()->setIfIsSet($v,"/modules/CustomReports/text/user_messages/$k");
        }
        
        if (count($request) == 0) {
            $msg = $msgs['no_report_view'];
            $this->userMessage($msg);
            I2CE::raiseError($msg);
            return false;
        }
        $config = I2CE::getConfig()->modules->CustomReports;
        $view = array_shift($request);
        if (!isset($config->reportViews->$view)) {
            $msg = $msgs['invalid_report_view'] . ": $view";
            $this->userMessage($msg);
            I2CE::raiseError($msg);
            return false;
        }       
        if (!$this->canAccessReportView($view)) {
            $msg =$msgs['no_permission_report_view'] . ": $view";
            $this->userMessage($msg);
            I2CE::raiseError($msg);
            return false;                
        }
        $displays = array(); //a list in increasing priority of displays we will try to fall back on.
        if (count($request)>0) {
            $displays[] =   array_shift($request);
        }
        if (isset($config->default_display) && is_string($config->default_display) && strlen($config->default_display) > 0) {
            $displays[] = $config->default_display;
        }
        if (isset($config->reportViews->$view->default_display) 
            && is_string($config->reportViews->$view->default_display) && strlen($config->reportViews->$view->default_display)>0) {
            $displays[] = $config->reportViews->$view->default_display;
        }        
        $displays[]= 'Default';
        $displayObj = null;
        $msg ='';
        while (count($displays) > 0 && !$displayObj instanceof I2CE_CustomReport_Display) {
            $display = array_shift($displays);
            if (!isset($config->displays->$display) || !isset($config->displays->$display->class))  {
                I2CE::raiseError("No report display $display");
                continue;
            }
            $displayClass = $config->displays->$display->class;
            if (!class_exists($displayClass) || !is_subclass_of($displayClass,'I2CE_CustomReport_Display')) {
                I2CE::raiseError("Cannot find class $displayClass as a subclass of I2CE_CustomReport_Display");
                continue;
            }
            try {
                $displayObj = new $displayClass($this,$view);                
            }
            catch (Exception $e) {
                $msg = $e->getMessage();
                I2CE::raiseError($msg);
                $displayObj = null;
            }
        }        
        if (!$displayObj instanceof I2CE_CustomReport_Display) {
            $msg = $msgs['no_display_report_view'] . ": $view\n" . $msg;
            I2CE::raiseError($msg);
            $this->userMessage($msg);
            return false;
        }
        return $displayObj;
    }

    protected function actionSaveOptions() {
        $displayObj = $this->getDisplayObj($this->request_remainder, false);
        if (!$displayObj) {
            return false;
        }
        return $displayObj->saveDisplayOptions();
    }


    protected function actionShow($req_remainder) {
        $displayObj = $this->getDisplayObj($req_remainder);
        if (!$displayObj) {
            $this->setRedirect('CustomReports/view/reportViews');
            return false;
        }
        $contentNode = null;
        if (!array_key_exists('HTTP_HOST',$_SERVER)) {
            $this->template->loadRootText("<span id='siteContent'/>");
        }
        $contentNode = $this->template->getElementById('siteContent');
        if (!$contentNode instanceof DOMNode) {
            $this->setRedirect('CustomReports/view/reportViews');
            return false;
        }
        //we are good to go at this point.
        $this->template->addHeaderLink("CustomReports.css");
        $this->template->addHeaderLink("CustomReports_iehacks.css", array('ie6' => true ));
        $this->template->setDisplayData( "limit_description", false );
        if (! $displayObj->display($contentNode)) {
            return false;
        }
        if (!array_key_exists('HTTP_HOST',$_SERVER)) {
            echo $this->template->getDisplay();
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
