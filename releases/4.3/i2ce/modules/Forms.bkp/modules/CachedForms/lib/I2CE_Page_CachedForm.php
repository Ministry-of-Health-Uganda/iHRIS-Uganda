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
*  I2CE_Page_CachedForm
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Page_CachedForm extends I2CE_Page{

    protected function addTextMessage($msg) {
        $this->template->getElementById('siteContent')->appendChild($this->template->createTextNode($msg));
    }


    /**
     * The business method if this page is called from the commmand line
     * @param array $request_remainder the remainder of the request after the page specfication.  
     * @param array $args the array of unix style command line arguments 
     * Arguements are link that in: http://us3.php.net/manual/en/features.commandline.php#78651
     * If we were called as: 
     *      index.php --page=/module/page/some/thing/else --long  -AB 2 -C -D 'ostrich' --eggs==good
     * Then $request_remainder = array('some','thing','else')
     * and $args = array('long'=>true, 'A'=>true, 'B'=>2, 'C'=>true, 'D'=>'ostrich', 'eggs'=>'good')
     */ 
    protected function actionCommandLine($args,$request_remainder)  {             
        if (count($this->request_remainder) == 0) {
            if ( $this->page == 'cacheAll') {
                $this->cacheAll(true);
            } else if ($this->page == 'cacheAllForce') {
                $this->cacheAll(false);
            } else if ($this->page == 'dropAll') {
                $this->dropAll();
            } else if ($this->page == 'cache'  &&  (count($selected = $this->getSelected()) > 0)) {
                return $this->cacheAll(true,$selected);
            } else if ($this->page == 'cacheForce'  &&  (count($selected = $this->getSelected()) > 0)) {
                return $this->cacheAll(false,$selected);
            } else if ($this->page == 'dropAndCache'  &&  (count($selected = $this->getSelected()) > 0)) {
                $this->dropAll($selected);
                return $this->cacheAll(true,$selected);
            } else if ($this->page == 'dropAndCacheForce'  &&  (count($selected = $this->getSelected()) > 0)) {
                $this->dropAll($selected);
                return $this->cacheAll(false,$selected);
            } else {
                I2CE::raiseError("No action specfied");
            }
            return true;
        } else {
            $form = $this->request_remainder[0];
            $id = null;
            if ( array_key_exists( 1, $this->request_remainder ) ) {
                $id = $this->request_remainder[1];
            }
            try {
                $cachedForm = new I2CE_CachedForm($form);
            }
            catch(Exception $e) {
                $this->addTextMessage ( "Unable to setup cached form $form");
                return false;
            }
            $msg = '';
            switch ($this->page) {
            case 'createCache':            
                if ( $id === null ) {
                    if ($cachedForm->generateCachedTable(true, true)) {
                        $msg =  "Cached Table for $form generated";
                    } else {
                        $msg =  "Cached Table for $form not generated";
                    }
                } else {
                    if ( $cachedForm->updateCachedTable($id, true) ) {
                        $msg =  "Cached Table for $form $id updated";
                    } else {
                        $msg =  "Cached Table for $form $id not updated";
                    }
                }
                break;
            case 'forceCreateCache':            
                if ( $id === null ) {
                    if ($cachedForm->generateCachedTable(false,false)) {
                        $msg =  "Cached Table for $form generated (forced)";
                    } else {
                        $msg =  "Cached Table for $form not generated (forced)";
                    }
                } else {
                    if ( $cachedForm->updateCachedTable( $id ) ) {
                        $msg =  "Cached Table for $form $id updated (forced)";
                    } else {
                        $msg =  "Cached Table for $form $id not updated (forced)";
                    }
                }
                break;
            case 'dropCache':
                if ($cachedForm->dropTable()) {
                    $msg =  "Cached Table for $form dropped";
                } else {
                    $msg =  "Cached Table for $form not dropped";
                }
                break;
            default:
                $msg = "No action specifed for $form.  The valid actions are 'createCache', 'forceCreateCache', and 'dropCache'";
                break;
            }
            I2CE::raiseError($msg);
            return true;
        }
    }

    public function action() {
        parent::action();
        if (!$this->hasPermission('task(cached_forms_can_administer)')) {
            return false;
        }
        $this->template->setAttribute( "class", "active", "menuConfigure", "a[@href='configure']" );
        $this->template->appendFileById( "menu_configure.html", "ul", "menuConfigure" );
        $this->template->setAttribute( "class", "active", "menuCachedForms", "a[@href='CachedForms']" );
        if ($this->page ==  'export' && $this->isPost()) {
            if (!$this->exportTables()) {
                $this->addTextMessage("Export Failed");
            }
        } elseif ($this->page == 'exportProfile') {
            if ($this->isPost()) {
                if (!$this->setProfile()) {
                    $this->addTextMessage("Setting Profile Failed");
                }
            } else {
                $this->showMenu('exportProfile');
                return true;
            }
        }
        if (count($this->request_remainder) == 0) {
            if ($this->page == 'cacheAll') {
                $this->cacheAll();
                $this->showMenu();
            } else if ($this->page == 'cacheAllForce') {
                $this->cacheAll(false);
                $this->showMenu();                  
            } else if ($this->page == 'dropAll') {              
                $this->dropAll();
                $this->showMenu();                                  
            } else if ($this->page == 'cache' && count($selected = $this->getSelected()) > 0) {
                $this->cacheAll(true,$selected);
            } else {
                $this->showMenu();
            }
            return;
        }
        if (count($this->request_remainder) != 1) {
            $this->addTextMessage ( "No form specifed");
            return false;
        }
        $form = $this->request_remainder[0];
        try {
            $cachedForm = new I2CE_CachedForm($form);
        }
        catch(Exception $e) {
            $this->addTextMessage ( "Unable to setup cached form $form");
            return false;
        }
        $msg = '';
        I2CE::raiseError($this->page);
        switch ($this->page) {
        case 'createCache':            
            if ($cachedForm->generateCachedTable()) {
                $msg =  "Cached Table for $form generated";
            } else {
                $msg =  "Cached Table for $form not generated";
            }
            break;
        case 'forceCreateCache':            
            if ($cachedForm->generateCachedTable(false,false)) {
                $msg =  "Cached Table for $form generated (forced)";
            } else {
                $msg =  "Cached Table for $form not generated (forced)";
            }
            break;
        case 'dropCache':
            if ($cachedForm->dropTable()) {
                $msg =  "Cached Table for $form dropped";
            } else {
                $msg =  "Cached Table for $form not dropped";
            }
            break;
        default:
            $msg = "No action specifed for $form.  The valid actions are 'createCache', 'forceCreateCache', and 'dropCache'";
            break;
        }
        $this->addTextMessage($msg);
        return true;
    }


    protected function getSelected() {
        if ($this->request_exists('export') && is_array($selected = $this->request('export'))) {
            $selected = array_keys($selected);
        } else {
            $selected = array();
        }        
        if ($this->request_exists('profile') && I2CE_MagicDataNode::checkKey($profile = $this->request('profile'))) {
            $config = I2CE::getConfig();
            $path = "/modules/CachedForms/export/profiles/$profile";
            if ($config->is_parent($path)) {
                foreach ($config->$path as $form=>$enabled) {
                    if (!is_scalar($enabled) || !$enabled) {
                        continue;
                    }
                    if ($enabled) {
                        $selected[] = $form;
                    }
                }
            }
            $selected = array_unique($selected);
        }
        if (count($selected) == 0) {
            return array();
        }
        return array_intersect($selected,I2CE_FormFactory::instance()->getNames());
    }

    protected function exportTables() {
        $mysqldump = trim(`which mysqldump`);
        $compress=  ($this->request_exists('compress') && $this->request('compress'));                
        if ($compress) {
            $compress = trim(`which bzip2`);
        }
        if (!$mysqldump) {
            I2CE::raiseError("No mysqldump");
            return false;
        }
        $forms = $this->getSelected();
        if (count($forms) == 0) {
            I2CE::raiseError("No forms selected for import");
            return false;
        }
        $mod_date = false;
        if ($this->request_exists('mod_date') 
            &&  preg_match( '/^(\d+)-(\d+)-(\d+)$/', $this->request('mod_date') ) ) {            
            $mod_date = $this->request('mod_date');
        }
        $db = I2CE::PDO();
        try {
            $result = $db->query( "SHOW TABLES FROM " . I2CE_PDO::details('dbname') );
            $tables = $result->fetchAll( PDO::FETCH_COLUMN, 0 );
        } catch ( PDOException $e ) {
            I2CE::pdoError( $e, "Unable to get table names." );
            return false;
        }
        $uncached = array();
        foreach ($forms  as $i=>&$form) {
            $form = I2CE_CachedForm::getCachedTableName($form,false);
            if (!in_array($form,$tables) ) {
                $uncached[] = $form;
                unset($forms[$i]);
            }
        }
        unset($form);
        if (count($uncached) > 0) {
            I2CE::raiseError("Skipping uncached forms from tables:\n" . implode(" ",$uncached) . "\nExisting tables are:\n" . implode(" ", $tables));
        }
        if (count($forms) == 0) {
            $this->userMessage( "No tables have been cached for the requested forms");
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                $this->redirect("CachedForms");
            }
            return false;
        }

        if ($mod_date) {
            $opts = " --replace  --skip-add-drop-table ";
        } else {
            $opts = " --no-create-info ";
        }
        $cmd = $mysqldump . '  --quick --dump-date --complete-insert  ' .
            ' --user=' . escapeshellarg(I2CE_PDO::details('user')) . 
            ' --password=' . escapeshellarg(I2CE_PDO::details('pass')) . ' ' . 
            escapeshellarg(I2CE_PDO::details('dbname')) . " " . 
            implode(" " , $forms)  ;
        if ($mod_date) {
            $cmd .= " --where=\"DATE(last_modified) >= '$mod_date'\" ";
        }
        //some of these header lines were stole from phpmyadmin.. thanks
        $date_str = date('Ymd');
        if ($this->request_exists('profile') && $this->request('profile')) {
            $filename = "cached_forms_export_" . $this->request('profile') ."_$date_str.sql";
        } else {
            $filename = "cached_forms_export_$date_str.sql";
        }
        if ($compress) {
            $filename .= '.bz2';
            $mime_type = "application/x-bzip2";
            $cmd .= " | " . $compress . " -c ";
        } else {
            $mime_type = "text/x-sql";
        }
        header('Content-Type: ' . $mime_type);
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        if (preg_match('/\s+MSIE\s+\d\.\d;/',$_SERVER['HTTP_USER_AGENT'])) { //internet explorer
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        } else {
            header('Pragma: no-cache');
            // test case: exporting a database into a .gz file with Safari
            // would produce files not having the current time
            // (added this header for Safari but should not harm other browsers)
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        }
        flush();
        //I2CE::raiseError("Dumping form caches via:\n$cmd");
        passthru($cmd);
        flush();
        die(0);        
    }

    protected function getProfiles() {
        $path = "/modules/CachedForms/export/profiles";
        if (!I2CE::getConfig()->is_parent($path)) {
            return array();
        }
        return I2CE::getConfig()->getKeys($path);
    }

    protected function setProfile() {
        if (!$this->request_exists('profile') || !($profile =$this->request('profile'))) {
            I2CE::raiseError("No profile named");
            return false;
        }
        if (!I2CE_MagicDataNode::checkKey($profile )) {
            I2CE::rasieError("Invalid profile");
            return false;
        }
        $path = "/modules/CachedForms/export/profiles/$profile";
        $config = I2CE::getConfig()->traverse($path,true);
        if (!$config instanceof I2CE_MagicDataNode) {
            I2CE::rasieError("Invalid profile structure ");
            return false;
        }
        if ($this->request_exists('export') && is_array($forms = $this->request('export'))) {
            $forms = array_keys($forms);
        } else {
            $forms = array();
        }        
        foreach ($config->getKeys() as $form) {
            if (!in_array($form,$forms)) {
                $config->$form = 0;
            }
        }        
        foreach ($forms as $form) {
            $config->$form = 1;
        }
        return true;
    }


    protected function showMenu($action = '') {
        $factory = I2CE_FormFactory::instance();
        $forms = $factory->getNames();
        $contentNode = $this->template->getElementById('siteContent');
        if (!$contentNode instanceof DOMNode) {
            I2CE::raiseError("Dont know where to put cached form menu");
            return false;
        }
        $selected = $this->getSelected();
        if (substr($action,0,6) == 'export') {
            $template = "cachedforms_menu_{$action}.html";
            $template_each = "cachedforms_menu_{$action}_each.html";
        } else {
            $template = "cachedforms_menu.html";
            $template_each = "cachedforms_menu_each.html";
        }
        $menuNode = $this->template->appendFileByNode($template,'div',$contentNode);
        if ($this->request_exists('profile')) {            
            $this->template->setDisplayDataImmediate('profile',$this->request('profile'),$menuNode);
        }
        if (!$menuNode instanceof DOMNode) {
            I2CE::raiseError("Could not get main template for cached form menu");
            return false;
        }
        if (substr($action,0,6) != 'export') {
            $profiles = $this->getProfiles();
            if (count($profiles) > 0) {
                $this->template->setDisplayDataImmediate('has_profiles',1,$menuNode);
                $args=array(
                    'format'=>'F j, Y', //name of day short textuyal month, day 4 digit year
                    'inputOutputFormat'=> 'Y-m-d',  //mysql 
                    'allowEmpty' => true, 'startView' => 'decades',
                    );
                $this->template->addDatePicker('datepicker_ymd', $args);
            } else {
                $this->template->setDisplayDataImmediate('has_profiles',0,$menuNode);
            }
            foreach ($this->template->query("//select[@name='profile']",$menuNode) as $selectNode) {
                foreach ($profiles as $profile) {
                    $selectNode->appendChild($this->template->createElement('option',array('value'=>$profile),$profile));
                }
            }
        }
        $ulNode = $this->template->getElementById('cached_forms_list',$menuNode);
        if (!$ulNode instanceof DOMNode ){
            I2CE::raiseError("Could not find where to add in cached forms");
            return false;
        }
        sort($forms);
        $timeConfig = I2CE::getConfig()->modules->CachedForms->times->generation;
        foreach ($forms as $form) {
            $formObj = $factory->createContainer($form);
            if (!$formObj instanceof I2CE_Form) {
                I2CE::raiseError("Unable to instantiate form ($form)");
                continue;
            }
            $formNode = $this->template->appendFileByNode($template_each,'li',$ulNode);
            if (!$formNode instanceof DOMNode) {
                I2CE::raiseError("Could not add in individual form");
                return false;
            }
            $time = 0;
            $timeConfig->setIfIsSet($time,$form);
            if ($time > 0) {
                $time = strftime('%c', $time)  ;
            } else {
                $time = '';
            }
            $this->template->setDisplayDataImmediate('form',$form,$formNode);
            $this->template->setDisplayDataImmediate('last_generated',$time,$formNode);
            $this->template->setDisplayDataImmediate('create_link',"CachedForms/createCache/$form", $formNode);
            $this->template->setDisplayDataImmediate('force_link',"CachedForms/forceCreateCache/$form", $formNode);
            $this->template->setDisplayDataImmediate('drop_link',"CachedForms/dropCache/$form", $formNode);
            if ( !($inputNode = $this->template->getElementById('export',$formNode)) instanceof DOMNode) {
                continue;
            }
            $inputNode->setAttribute('name','export:' . $form);
            if (in_array($form,$selected)) {
                $inputNode->setAttribute('checked','ON');
            }
        }
        
    }


    /**
     * Attempts to drop caches of  all forms one by one. 
     */
    public function dropAll($forms = null) {
        I2CE::raiseError("Dropping all");
        $config = I2CE::getConfig()->modules->CachedForms;
        $factory = I2CE_FormFactory::instance();
        if ($forms === null) {
            $forms = $factory->getNames();
        }
        $failure = array();
        $config->cache_all->erase(); 
        foreach ($forms as $form) {
            try {
                $cachedForm = new I2CE_CachedForm($form);
            }
            catch(Exception $e) {
                if (array_key_exists('HTTP_HOST',$_SERVER)) { //we don't need to check here, b/c it won't error out.  we are doing it to keep the log file clean
                    $this->userMessage ( "Unable to setup cached form $form");
                }
                $sucess[] = $form;
                continue;
            }
            if (!$cachedForm->dropTable()) {
                $failure[] = $form;
            }
        }
        if (count($failure) > 0) {
            I2CE::raiseError("DropAll: Could not drop " . implode(',',$failure));
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                $this->userMessage( "Drop Failed:" . implode(',',$failure));
            }
        }  else {
            $this->userMessage( "Dropped all tables");
        }
        if (array_key_exists('HTTP_HOST',$_SERVER)) {
            $this->redirect("CachedForms");
        }
    }

    /**
     * Attempts to caches all forms one by one. 
     */
    public function cacheAll($not_force = true, $forms = null) {
        I2CE::raiseError("Caching all");
        $config = I2CE::getConfig()->modules->CachedForms;
        $factory = I2CE_FormFactory::instance();
        if ($forms === null) {
            $forms = $factory->getNames();
        }
        $sucess = array();
        $status = '';
        $config->cache_all->volatile(true);
        $config->setIfIsSet($status,'cache_all/status');
        if ( $status === 'in_progress' && $not_force) {
            I2CE::raiseError("In progress");
            return;
        }
        $config->cache_all->status == 'in_progress';
        foreach ($forms as $form) {
            try {
                $cachedForm = new I2CE_CachedForm($form);
            }
            catch(Exception $e) {
                if (array_key_exists('HTTP_HOST',$_SERVER)) { //we don't need to check here, b/c it won't error out.  we are doing it to keep the log file clean
                    $this->userMessage ( "Unable to setup cached form $form");
                }
                $sucess[] = $form;
                continue;
            }
            if ($cachedForm->generateCachedTable($not_force,false)) {
                $msg =  "Cached Table for $form generated";
            } else {
                $msg =  "Cached Table for $form not generated";
                $sucess[] = $form;
            }
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                $this->userMessage($msg,'notice');
            } else {
                I2CE::raiseError($msg);
            }
        }
        if (count($sucess) > 0) {
            I2CE::raiseError("CacheAll: Could not cache " . implode(',',$sucess));
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                $this->userMessage( "Could not cache " . implode(',',$sucess));
            }
        }
        $config->cache_all->status = 'done';
        $config->cache_all->time = time();
        if (array_key_exists('HTTP_HOST',$_SERVER)) {
            $this->redirect("CachedForms");
        }
        
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
