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
 * Page Wrangler
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */
if (!class_exists('I2CE_Wrangler',false)) {
    class I2CE_Wrangler extends I2CE_Fuzzy{
        
        
        /**
         * Method to call to handle page wrangling
         * @param string $path.  If non-null the path of the page we want to wrangle.  otherwise it processes from the url or commandline arguments
         * Defaults to null
         * @param boolean $display Set to true (default) to call the page's display() method
         * @return I2CE_Page -- the page created
         */
        public function wrangle($path = null,$display = true, $get = null, $post = null) {
            try {
                if ($display &&  array_key_exists('HTTP_HOST',$_SERVER) ) { //start output buffering if we call the page's display. we are not cli                    
                    ob_start();
                }
                $args = array();
                if (!$this->command_line) {
                    if ($path === null) {
                        if (array_key_exists('PATH_INFO',$_SERVER)) {
                            $path = $_SERVER['PATH_INFO'];
                        } else {
                            $path = '';
                        }
                    }
                } else {
                    $args = $this->getArgs($_SERVER['argv']);
                    if ($path ===null) {
                        if (!isset($args['page'])) {
                            I2CE::raiseError("No page specified");
                            exit(0);
                        } 
                        $path = $args['page'];
                        unset($args['page']);
                    }
                    if ( !is_array( $get ) && array_key_exists( "get", $args ) ) {
                        $get = array();
                        parse_str( $args['get'], $get );
                    }
                    if ( !is_array( $post ) && array_key_exists( "post", $args ) ) {
                        $post = array();
                        parse_str( $args['post'], $post );
                    }
                    $options = array();
                }
                $page = $this->processPath($path);
                $pageObj = $this->getPage($page['module'],$page['page'],$page['request'],$args,$get,$post );
                if (!$pageObj instanceof I2CE_Page) {
                    $this->userMessage("Unable to find requested page",'notice');
                    I2CE::raiseError("Unable to create page from path ($path)",E_USER_WARNING);
                    if ($this->command_line) {
                        exit(0);
                    } else {
                        return null;
                    }
                }
                $pageObj->module($page['module']);
                $pageObj->page($page['page']);
                $pageObj->pageRoot($page['pageRoot']);
                $pageObj->pageRemainder($page['pageRemainder']);
                if ($display) { 
                    $pageObj->display(); 
                    if ($this->command_line) {
                        exit(0);
                    }
                }
                return $pageObj; 
            }  catch (Exception $e) {
                echo "<pre>There was an unexpected error on the page " . $_SERVER['PHP_SELF'] . ".\nPlease contact your system administator if this persists:\n";
                echo get_class($e) . ": " . $e->getMessage() ."\n";
                echo "The error originated on line {$e->getLine()} of file {$e->getFile()}.\n";
                echo "\nTrace:\n" . $e->getTraceAsString();
            }
        }


        /**
         * true if we are called from the command line
         * @var protected boolean $command_line
         */
        protected $command_line;
        
        public function __construct() {
            $this->command_line = !array_key_exists('HTTP_HOST',$_SERVER);
            I2CE_Locales::setPreferredLocale(I2CE_Locales::getPreferredLocale());
        }


        /**
         *  Gets the page assoicated with a module
         * @param string $module
         * @param string $page
         * @param array $request_remainder of string... anything that would be a part of the remainder of the URL
         * @param array $args an array of page arguments.  Defaults to the empty array. Overwrites anything found in config for the page style or page 
         */
        public function getPage($module,$page,$request_remainder=array(),$args=array(), $get = null, $post = null ){           
            $mod_factory = I2CE_ModuleFactory::instance();
            if (!$mod_factory->exists($module)) {
                I2CE::raiseError("Cannot request the  page $page for module $module.  The module is not present",E_USER_ERROR);
                return;
            }
            if (!$mod_factory->isEnabled($module)) {
                I2CE::raiseError("Cannot request the  page $page for module $module.  The module is not enabled",E_USER_ERROR);
                return;
            }
            $storage = I2CE::getConfig();
            if ($module == 'I2CE') {
                $storage = $storage->I2CE;
            } else {
                $storage = $storage->modules->$module;
            }
            if (!$this->command_line) {
                $pageType = 'page';
            } else {
                $pageType = 'command_line';
            }
            $pageStylesInfo = I2CE::getConfig()->traverse("/I2CE/template/{$pageType}_styles");
            if ((!isset($storage->$pageType)) || (!isset($storage->$pageType->$page))) {
                //check to  see if there is a default page registered:
                $default_page = "";
                $storage->setIfIsSet($default_page,"{$pageType}_default");
                if (empty($default_page) || !isset($storage->$pageType->$default_page)) { 
                    I2CE::raiseError("The requested  $pageType ($page) for the module $module is not present at: " . $storage->getPath(false),E_USER_ERROR);
                    return;
                }
                $page= $default_page;
            }
            $pageInfo = $storage->$pageType->$page;         
            $pageArgs = array();
            $pageInfo->setIfIsSet($pageArgs,'args',true);
            $pageClass = null;
            $pageInfo->setIfIsSet($pageClass,'class');
            $style = null;
            $pageInfo->setIfIsSet($style,'style');            
            $checked = array();
            $execParams = array();
            $pageInfo->setIfIsSet($execParams,'execution_parameters',true);            
            while ($style) {                
                //start from the top-level style for the page and 
                //backtrack throuhg all the lowe levels styles getting any args for the page and
                //storing them in $pageArgs
                //the top-level styles should overwrite the lower lower styles.
                if (!$pageStylesInfo instanceof I2CE_MagicDataNode || !$pageStylesInfo->is_parent($style)) {
                    $style = '';
                    continue;
                }
                $styleInfo = $pageStylesInfo->traverse($style);
                if (!$pageClass) {
                    $styleInfo->setIfIsSet($pageClass,'class');
                }
                $t_execParams = array();
                if ($styleInfo->setIfIsSet($t_execParams,'execution_parameters')) {
                    I2CE_Util::merge_recursive($t_execParams,$pageArgs);
                    $execParams = $t_execParams;
                }          
                $t_args = array();
                if ($styleInfo->setIfIsSet($t_args,'args',true)) {               
                    //$t_Args are the  lower level args.  they need to be ovewritten by
                    //what is in pageArgs
                    I2CE_Util::merge_recursive($t_args,$pageArgs);
                    $pageArgs = $t_args;
                }
                $style  ='';
                if ($styleInfo->setIfIsSet($style,'style')) {
                    if ($style && array_key_exists($style,$checked) && $checked[$style]) {
                        $style = null;
                    }
                }
            }
            if (!$pageClass) {
                I2CE::raiseError("Cannot find a  class associated to the requested  page $page for the module $module",E_USER_ERROR);
                return;
            }
            if (!class_exists($pageClass)) {
                I2CE::raiseError("Cannot find the class ($pageClass) for the requested  page $page for the module $module",E_USER_ERROR);
                return;
            }
            foreach ($execParams as $key=>$val) {
                ini_set($key,$val);
            }
            I2CE_Util::merge_recursive($pageArgs,$args);
            return new $pageClass($pageArgs,$request_remainder, $get,$post);            
        }



        protected function processPath($path) {
            $request = array();
            if (is_string($path) && strlen($path ) > 0) {
                //we were called as <index.php>/something/else/entirely/  
                if ($path[0] == '/') {
                    $path = substr($path,1);
                }
                if ($path[strlen($path)-1] == '/') {
                    $path = substr($path,0,-1);
                }
                $request = preg_split('/\//',$path,-1,PREG_SPLIT_NO_EMPTY);
            }              
            $req_count = count($request);
            if ($req_count == 0) {
                $module = 'I2CE';
                $page= 'home';
                $request = array();
                $page_root = '';
                $page_remainder = '';
            } else {
                $page = array_shift($request);
                if (isset(I2CE::getConfig()->I2CE->page->$page)) {
                    $page_root = $page;
                    $module = 'I2CE';
                } else {
                    $module = $page;
                    if (count($request) > 0) {
                        $page =array_shift($request);
                        $page_root = $module . '/'. $page;
                    } else {
                        $page_root = $module ;
                        $page = 'home';
                    }
                }
                $page_remainder = implode('/',$request);
            }
            $manipulateWrangler  = 'manipulateWrangler_' . $module .'_' . $page;
            if ($this->_hasMethod($manipulateWrangler)) {
                $ret = $this->$manipulateWrangler($module,$page,$request,$page_root,$page_remainder);
            } else {
                $ret = array('module'=>$module,'page'=>$page,'request'=>$request,'pageRoot'=>$page_root,'pageRemainder'=> $page_remainder);
            }
            return $ret;
        }



        /*
         *Taken from: http://us3.php.net/manual/en/features.commandline.php#78651
         */
        protected function getArgs($args) {
            $out = array(); 
            $last_arg = null;
            for($i = 1, $il = sizeof($args); $i < $il; $i++) {
                if( (bool)preg_match("/^--(.+)/", $args[$i], $match) ) {
                    $parts = explode("=", $match[1], 2);
                    $key = preg_replace("/[^a-z0-9]+/", "", $parts[0]);
                    if(isset($parts[1])) {
                        $out[$key] = $parts[1];   
                    }
                    else {
                        $out[$key] = true;   
                    }
                    $last_arg = $key;
                }
                else if( (bool)preg_match("/^-([a-zA-Z0-9]+)/", $args[$i], $match) ) {
                    for( $j = 0, $jl = strlen($match[1]); $j < $jl; $j++ ) {
                        $key = $match[1]{$j};
                        $out[$key] = true;
                    }
                    $last_arg = $key;
                }
                else if($last_arg !== null) {
                    $out[$last_arg] = $args[$i];
                }
            }
            return $out;
        }


    }

}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
