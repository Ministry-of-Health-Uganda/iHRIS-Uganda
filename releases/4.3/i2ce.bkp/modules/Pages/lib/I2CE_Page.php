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
 * @since v1.0.0
 * @version v2.0.0
 */
/**
 * Handles all page display and operations.
 * 
 * All pages on the site are built from objects that extend this abstract object.
 * The {@link loadHTMLTemplates()} method must be overwritten but any standard pages can use
 * all the other methods here as is.
 * @package I2CE
 * @abstract
 * @access public
 * @see I2CE_Template
 */
if (! class_exists('I2CE_Page',false)) {
    class I2CE_Page extends I2CE_Fuzzy{
        /**
         * The template object for handling the HTML templates and data to be displayed.
         * @var I2CE_Template
         */
        protected $template;
        /**
         * The default HTML/XML files to be displayed by the template.
         * @var array of string
         */
        protected $defaultHTMLFile;
        /**
         * The role that can view this page.
         * @var mixed
         */
        protected $role;
        /**
         * The user viewing this page.
         * @var I2CE_User
         */
        protected $user;
        /**
         * The access level required for this page.
         * @var integer
         */
        private $access;
        /**
         * A url to redirect to instead of displaying the page.
         * @var string
         */
        protected $redirect;
        /**
         * Holds a reference to the $_POST array which is a list of all data sent from a form.
         * @var array
         */
        protected $post;
        /**
         * Holds a reference to the $_GET array which is a list of all variables sent in the URL or 
         * from a form with an action of "GET."
         * @var array
         */
        protected $get;
        
        /**
         * returns true if the url's have been written. false if not
         */
        public static function rewrittenURLs() {
            return I2CE::rewrittenURLs();
        } 

        /**
         *Returns the base url from which the site was accessed.  If no .htaccess is used, ths will include the index.php.
         *If rewrites are used (via .htacces) this will no include the index.php. Point is... this is the base url from which
         *the site was accessed, no questions asked.  This of course assumes that you are now accessing the site via the command line
         *@return string
         */
        public static function getAccessedBaseURL() {
            return I2CE::getAccessedBaseURL();
        }

        /**
         * Send the redirect header with the given URL.
         *
         * @param string $url
         */
        public function redirect( $url ) {              
            if (!array_key_exists('HTTP_HOST',$_SERVER)) {
                return;
            }
            if ($this->isGet() && !preg_match('/login/', $_SERVER['REQUEST_URI'])) {
                $_SESSION['referal'] = I2CE::getProtocol() . '://' . $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI'] ;
            }
            if ((preg_match('/^[a-zA-Z]+:\/\//',$url))  || ($url[0] == '/')) { //an absolute url
                //do nothing;
            } else {
                $url = $this->getAccessedBaseURL() . $url;
            }
            header( "Location: " . $url );
        }
        

        /**
         * Load the  template (HTML or XML) files to the template object.
         *  
         * 
         */  
        protected function loadHTMLTemplates() {
            if ($this->defaultHTMLFile === false) {
                return true;
            }
            if (is_array($this->defaultHTMLFile)) {
                $files = $this->defaultHTMLFile;
            } else {
                $files = array($this->defaultHTMLFile);
            }
            foreach ($files as $file) {
                if ( ! ($file === null || (is_string($file) && strlen($file) == 0))) {
                    $this->template->addFile( $file);
                }
            }
            return;
        }
        /**
         * Perform any actions
         * 
         * @returns boolean.  true on sucess
         */
        protected function action() {
            return true;
        }



        /**                                                  
         * Return the title for this page.
         * @return string
         */
        public function getTitle() { 
            return $this->args['title'];         
        }

        /**
         * Get the template associated to this page
         */
        public function getTemplate() {
            return $this->template;
        }

        
        /**
         * the page root -- this is the URL relative to the site base that is used to get to this page.
         * @var protected string $page_root
         */
        protected $page_root;

        /**
         * Get/set the page root -- this is the URL relative to the site base that is used to get to this page.
         * @param $page_root Defaults to null. If non-null we set the page root.  If null we get the page root.
         * @returns string if $page_root was null(default)
         */
        public function pageRoot($page_root=null) {
            if ($page_root === null) {
                return $this->page_root;
            } else {
                $this->page_root = $page_root;
            }
        }


        /**
         * the page remainder -- this is the remainder of the URL relative to the site base that is used to get to this page.
         * @var protected string $page_remainder
         */
        protected $page_remainder;

        /**
         * Get/set the page remainder -- this is the remainder of the URL relative to the site base that is used to get to this page.
         * @param $page_remainder Defaults to null. If non-null we set the page remainder.  If null we get the page remainder.
         * @returns string if $page_remainder was null(default)
         */
        public function pageRemainder($page_remainder=null) {
            if ($page_remainder === null) {
                return $this->page_remainder;
            } else {
                $this->page_remainder = $page_remainder;
            }
        }


        
        public function getURLRoot($page_remainder = null) {
            if ($this->module == 'I2CE') {
                $url =    $this->page;
            } else {
                $url =  $this->module . '/' . $this->page;
            }
            if ($this->root_url) {
                $url .= '/' . $this->root_url;
            }
            if (is_array($page_remainder)) {
                $url .=  '/' .implode("/",$page_remainder);
            } else   if ($page_remainder){
                $url .=  '/' .$page_remainder;
            }
            $comps = preg_split('/\//',$url,-1,PREG_SPLIT_NO_EMPTY);
            reset($comps);
            $cs = array();
            foreach ($comps as $c) { //cleanup
                if ($c == '.') {
                    continue;
                } else  if ($c == '..') {
                    array_pop($cs);
                } else {
                    $cs[] = $c;
                }
            }
            return  implode("/",$cs);
        }


        /**
         * The arguments passed in the constructor.
         * @param protected array $args
         */
        protected $args;
        /**
         *Get the page's arguments
         * @returns array()
         */
        public function get_args() {
            return $this->args;
        }
        
        /**
         * The remainder of the page request -- everything  after (option_module_name/)page_name(/reminder/of/the/request)
         * @param protected array $request_remainder
         */
        protected $request_remainder;

        public function request_remainder() {
            if (!is_array($this->request_remainder)) {
                return array();
            } else {
                return $this->request_remainder;
            }
        }
        /**
         * The permission parser for the user of this page.
         * @param I2CE_PermissionParser $permissionParser
         */
        protected $permissionParser;

        /**
         * Parse a permission string to see if we have permission.
         * @param string $permission
         * @param DOMNode $node.  Defaults to null.  If set, it is the node in the page's template that
         * we get the data for.
         * @returns boolean.  Null on failure.
         */ 
        public function hasPermission($permission, $node = null) {
            return $this->permissionParser->hasPermission($permission,$node);
        }


        protected $root_url ='';
        /**
         * Create a new instance of a page.
         * 
         * The default constructor should be called by any pages extending this object.  It creates the
         * {@link I2CE_Template} and {@link I2CE_User} objects and sets up the basic member variables.
         * @param array $args
         * @param array $request_remainder The remainder of the request path
         */
        public  function __construct( $args,$request_remainder , $get = null, $post = null) {
            if (array_key_exists('root_url',$args) && $args['root_url']) {
                $this->root_url = $args['root_url'];
                unset($args['root_url']);
            }

            $this->setIsPost( (array_key_exists('REQUEST_METHOD',$_SERVER)) && ( $_SERVER['REQUEST_METHOD'] == "POST" ));
            $this->user = new I2CE_User();
            if ( function_exists( 'apache_note' ) ) {
                apache_note( "iHRIS-username", ($this->user->username=='0'?'-':$this->user->username) );
            } elseif (array_key_exists('HTTP_HOST',$_SERVER) && !headers_sent()) {
                header( 'X-iHRIS-username: ' . ($this->user->username=='0'?'-':$this->user->username) );
            }
            I2CE_Locales::setPreferredLocale($this->user->getPreferredLocale());
            $this->args = $args;
            $this->request_remainder = $request_remainder;
            $i2ce_config = I2CE::getConfig()->I2CE;
            if (!array_key_exists('access',$args) ) {
                if (array_key_exists('HTTP_HOST',$_SERVER)) {
                    $args['access'] = array('any');  //default is anyone logged in.
                } else {
                    $args['access'] = array('all');
                }
            }
            $this->access = $args['access'];
            $this->setupGetPost($get,$post);
            $this->template = null;
            if (!$this->initializeTemplate()) {
                I2CE::raiseError("Could not setup templates");
            }
            $this->redirect = "";
            $this->permissionParser = new I2CE_PermissionParser($this->template);
            I2CE_ModuleFactory::callHooks('page_constructor',array('page'=>$this,'args'=>$args,'request_remainder'=>$request_remainder));
        }

        /**
         * Handles creating hte I2CE_TemplateMeister templates and loading any default templates
         * @returns boolean true on success
         */
        protected function initializeTemplate() {
            if (array_key_exists('defaultHTMLFile', $this->args) && $this->args['defaultHTMLFile']) {
                $this->defaultHTMLFile = $this->args['defaultHTMLFile'];
            } else {
                $this->defaultHTMLFile = false;
            } 
            if (!isset($this->args['templates'])) {
                $this->args['templates'] = array('');
            }
            if (is_scalar($this->args['templates'])) {
                $this->args['templates'] = array($this->args['templates']);
            }
            if (isset($this->args['template'])) {
                $template = $this->args['template'];
            } else {
                $template = 'I2CE_Template';
            }
            $this->template = new $template();
            if (!$this->template instanceof I2CE_TemplateMeister) {
                I2CE::raiseError("Could not make template $template");
                return false;
            }
            $this->template->setUser($this->user);
            if (array_key_exists(0,$this->args['templates']) && $this->args['templates'][0]) {
                $this->template->loadRootFile($this->args['templates'][0] );
            } else {
                $this->template->loadRootText('');
            }
            for( $i = 1; $i < count( $this->args['templates'] ); $i++ ) {
                $this->template->addFile( $this->args['templates'][$i] );
            }
            $this->template->processArgs($this->args);
            return true;
        }

        /**
         * setup of the get and post variables.
         * @param array $get.  If null (default) it will be $_GET if it is a HTTP request. otherwise it is the empty array
         * @param array $psot.  If null (default) it will be $_POST if it is a HTTP array request. otherwise it is the empty array
         * @param boolean $strip.  Defauls to true.  If true it will try to strip off magic quotes if they exist for a HTTP request.
         */
        protected function setupGetPost($get = null,$post=null, $strip = true) {
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                if (!is_array($get)) {
                    $get = $_GET;
                }
                if (!is_array($post)) {
                    $post = $_POST;
                }
                if ( array_key_exists( 'req_query', $get ) 
                        && array_key_exists( 'req_query', $_SESSION )
                     && array_key_exists( $get['req_query'], $_SESSION['req_query'] ) ) {
                    parse_str( $_SESSION['req_query'][$get['req_query'] ], $this->session_req );
                    unset( $get['req_query'] );
                } else {
                    $this->session_req = array();
                }
                $this->post = $post;
                $this->get = $get;
                if ($strip && get_magic_quotes_gpc()) {
                    $stripper = function(&$item,$key) {
                        $item = stripslashes($item);
                    };
                    array_walk_recursive($this->post, $stripper);
                    array_walk_recursive($this->get, $stripper);
                    array_walk_recursive($this->session_req, $stripper);
                }                
                if (!array_key_exists('dont_transform',$this->args) || !$this->args['dont_transform']) {
                    $this->get = $this->fixupRequestVariables($this->get);
                    $this->post = $this->fixupRequestVariables($this->post);
                    $this->session_req = $this->fixupRequestVariables($this->session_req);
                }
            } else {
                if (!is_array($get)) {
                    $get = array();
                }
                if (!is_array($post)) {
                    $post = array();
                }
                $this->get = $get;
                $this->post = $post;
                $this->session_req = array();
            }
        }


        /**
         * Applies any transformations (make sure magic quotes is off, nesting, and json decoding) to an array variables
         * @param array $vars
         * @param boolean $transform.  Defaults to true
         * @returns $vars;
         */
        public static function fixupRequestVariables($vars) {
            if (!is_array($vars)) {
                return array();                
            }
            $vars = I2CE_Util::transformVariables($vars);
            if (!array_key_exists('i2ce_json',$vars)) {
                return $vars;
            }
            $json = $vars['i2ce_json'];
            unset($vars['i2ce_json']);
            if (is_string($json) && strlen($json) > 0) {
                $json = array($json);
            }
            if (is_array($json)) {
                foreach ($json as $v) {
                    I2CE_Util::merge_recursive($vars, json_decode($v,true));
                }
            }
            return $vars;
        }


        public static function flattenRequestVars($vars) {
            $req = array();
            $names = array();
            self::_flattenRequestVars($vars,$req,'');
            return $req;
        }

        public static function _flattenRequestVars($vars,&$req, $prefix) {
            if (!is_array($vars)) {
                return;
            }
            foreach ($vars as $name=>$var) {
                if (is_scalar($var)) {
                    $req[$prefix . $name] = $var;
                } else if (is_array($var)) {
                    self::_flattenRequestVars($var,$req, $prefix . $name . ':');
                }
            }
        }

        /**
         * Sets/Gets the module.
         * @param string $module If non-null sets the module's name to $module.
         * @return string if $module was non-null
         */
        public function module($module=null) {
            if ($module === null) {
                return $this->module;
            } else {
                $this->module = $module;
            }
        }
        /**
         * Sets/Gets the page.
         * @param string $page If non-null sets the page's name to $page.
         * @return string if $page was non-null
         */
        public function page($page=null){
            if ($page === null) {
                return $this->page;
            } else {
                $this->page = $page;
            }
        }


        /**
         * @var protected string $page. The requested page
         */      
        protected $page;
        /**
         *@var protected string $module.  The module that contains this page.
         */
        protected $module;

        /**
         * Set the active menu
         */
        protected function setActiveMenu() {
            I2CE_ModuleFactory::callHooks( 'set_active_menu_module_' . $this->module . '_page_' . $this->page, $this );
            return true;
        }

        /**
         * Initializes any data for the page
         * @returns boolean.  True on sucess. False on failture
         */
        protected function initPage() {
            return true;
        }

        /**
         * Calls the appropriate action for the page.  Then it 
         * Displays or redirects the page as appropriate.
         * 
         * This will check to make sure the page can be seen by this user and if not redirect them to an error
         * page.  If the {@link redirect} variable has been set then the page will be redirected to the
         * new page.  Otherwise the {@link I2CE_Template::display() template display} method will be called to
         * output the combined template files to the browser.
         * @param boolean $supress_output  defaults to false.  set to true to supress the output of a webpage
         */
        public function display($supress_output = false) {
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                return $this->displayWeb($supress_output);
            } else {
                return $this->displayCommandLine();
            }
        }

        /**
         * Main display method for command line interface
         */
        protected function displayCommandLine() {
            if (!$this->initPage()) {
                I2CE::raiseError("page initialization failed");
                return ;
            }
            $this->actionCommandLine($this->args,$this->request_remainder);
        }

        /**
         * Main display method for web interface
         * @param boolean $supress_output  defaults to false.  set to true to supress the output of a webpage
         */
        protected function displayWeb($supress_output = false) {
            $i2ce_config = I2CE::getConfig()->I2CE;
            if (!$this->initPage()) {
                $pages = $i2ce_config->page;
                if (isset($pages->login) && !$this->user->logged_in()) {                            
                    $this->setRedirect('login'); //defined in module Login
                } else {
                    $this->setRedirect('noaccess'); //defined in I2CE
                }
                $this->redirect( $this->redirect );
                return;
            }
            $error = false;
            $permission = 'role(' . implode(",",$this->access) . ')';
            if (array_key_exists('tasks',$this->args) && is_array($this->args['tasks']) && count($this->args['tasks'])>0) {
                $permission .= ' | task(' . implode(',',$this->args['tasks']) . ')';
            }
            if ($this->hasPermission($permission)) {
                I2CE_ModuleFactory::callHooks('pre_page_action',$this);
                if (!$supress_output) {
                    I2CE_ModuleFactory::callHooks('pre_displayed_page_action',$this);
                }
                if ($this->loadHTMLTemplates() === false) {
                    $error = true;
                } else if ($this->setActiveMenu() === false) {
                    $error = true;
                } else if ($this->action() === false) {
                    $error = true;
                } else {
                    I2CE_ModuleFactory::callHooks('post_page_action',$this);
                }
            } else{
                if ( $this->user->logged_in()) {
                    $this->userMessage("You do not have access to the page `{$this->page}`", 'notice');
                }
                if ( $this->redirect == "" ) { 
                    //if there is a login page available, use it.   Otherwise, go to the no access page.
                    $pages = $i2ce_config->page;
                    if (isset($pages->login) && !$this->user->logged_in()) {                            
                        $this->setRedirect('login'); //defined in module Login
                    } else {
                        $this->setRedirect('noaccess'); //defined in I2CE
                    }
                }
            }                   
            if ( $this->redirect != "" ) {
                $this->redirect( $this->redirect );
                return;
            }
            if ($error ) {
                $this->userMessage("There was an unexpected error in processing the requested page","notice",false);
                I2CE_ModuleFactory::callHooks('pre_page_display_error',$this);
            }
            $this->_display($supress_output);
        }
    


        /**
         * Display the template as HTML/XML.  Sets the header and displays any buffered warnings/echoed text.
         */
        protected function _display($supress_output) {        
            if (!$supress_output
                && (array_key_exists('HTTP_HOST',$_SERVER))
                ) {
                $headers = $this->template->getHeaders();
                if (!is_array($headers)) {
                    $headers = array($headers);
                }
                foreach ($headers as $header) {
                    header($header);
                }
            }
            $classes = array();
            if ($this->template instanceof I2CE_TemplateMeister) {
                $class = get_class($this->template);
                while ($class && $class != 'I2CE_Fuzzy') {
                    $classes[] = $class;
                    $class = get_parent_class($class);
                }
                $num = count($classes);
            }
            I2CE_ModuleFactory::callHooks('pre_page_prepare_display',$this);
            for ($i=$num-1; $i >= 0 ; $i--) {
                I2CE_ModuleFactory::callHooks('pre_page_prepare_display_' .$classes[$i],$this);
            }
            if ($this->template instanceof I2CE_TemplateMeister) {
                $this->template->prepareDisplay();
            }
            for ($i=$num -1; $i >= 0; $i--) {
                I2CE_ModuleFactory::callHooks('post_page_prepare_display_' .$classes[$i],$this);
            }
            I2CE_ModuleFactory::callHooks('post_page_prepare_display',$this);
            if ($this->template instanceof I2CE_Template) {
                $this->template->checkRolesTasksAndPermissions();
            }
            I2CE_ModuleFactory::callHooks('final_page_prepare_display',$this);
            if (!$supress_output) {
                $display = '';
                if ($this->template instanceof I2CE_TemplateMeister) {
                    $display = $this->template->getDisplay();
                }
                $buffer ='';
                if (ob_get_level() == I2CE::$ob_level + 1) {
                    $buffer = ob_get_clean();
                
                }
                echo   $display; flush();
                if ($buffer) {
                    I2CE::raiseError("The page " .$_SERVER['PHP_SELF']  . " has errors");
                    $buffer = str_replace(array('<br/>','<br />'),"\n",$buffer);
                    $buffer = htmlentities($buffer,ENT_COMPAT,'UTF-8',false);
                    echo "<span class='buffered_errors'><pre>$buffer</pre></span>";
                }
            }
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
        protected function actionCommandLine($args,$request_remainder) { 
            I2CE::raiseError("No action is defined for the command line", E_USER_WARNING);
        }

        /**
         * Change the access level required to view this page.
         * 
         * If the access level requirements change after the page object has been instantiated this
         * method is used to set a new access level for the {@link $access} variable.
         * @param array of string
         */
        public  function setAccess( $access ) {
            $this->access = $access;
        }



        /**
         * Get the access level required to view this page.
         * 
         * If the access level requirements change after the page object has been instantiated this
         * method is used to set a new access level for the {@link $access} variable.
         * @return array of string
         */
        protected  function getAccess() {
            return $this->access;
        }


        






        /**
         *Get the user of this page
         *@returns I2CE_User
         */
        public function getUser() {
            return $this->user;
        }

        /**
         * Sets if this pages is a post or not
         */
        public function setIsPost($post) {
            $this->is_post = $post;
        }

        /**
         * @var protected boolean $is_post -- true if this page is a post.
         */
        protected $is_post;

        /**
         * Check to see if the current page is a POST form submission or not.
         * @return boolean
         */
        public  function isPost() {
            return $this->is_post;
        }


        /**
         * Check to see if the current page is a GET request or not
         * @return boolean
         */
        public function isGet() {
            return (!$this->is_post);
        }
    
        /**
         * Check to see if a key exists in the {@link $post} array.
         * @return boolean
         */
        public function post_exists( $key ) {
            if (is_array($key)){
                $post = &$this->post;
                foreach ($key as $k) {
                    if (!array_key_exists($k,$post)) {
                        return false;
                    }
                    $post = &$post[$k];
                }
                //we got through all the key
                return true;
            } else {
                return array_key_exists( $key, $this->post );
            }
        }
        /**
         * Get/set  the {@link $post} value for the given key.
         * @param string $key.  Defaults to null meaning we return all of the post variables (it is not slash escaped).
         * @param mixed $val.  Defaults to null. If non-null we set the post value for $key to $val
         * @return mixed if no val is set: string if a key is given and found. null if key is given nut not found.  array otherwise
         */
        public function post( $key = null, $val = null ) {
            if ($key === null) {
                $key = array();
            }
            if (is_array($key)) {
                $post = &$this->post;
                foreach ($key as $k) {
                    if (!is_array($post) || !array_key_exists($k,$post)) {
                        if ($val === null) {
                            //we are getting                            
                            return null;
                        } else {
                            //we are setting
                            $post[$k] = array();
                        }
                    }
                    $post = &$post[$k];
                }
                //we got through all the keys
                if ($val === null) { //we are getting
                    return $post;
                } else {
                    $post = $val;
                }
                
            } else {
                if ($val === null) {
                    if ( $this->post_exists( $key ) ) {
                        return $this->post[ $key ];
                    } else {
                        return null;
                    }
                } else {
                    $this->post[$key] = $val;
                }
            }
        }
        /**
         * Check to see if a key exists in the {@link $get} array.
         * @return boolean
         */
        public function get_exists( $key ) {
            if (is_array($key)){
                $get = &$this->get;
                foreach ($key as $k) {
                    if (!array_key_exists($k,$get)) {
                        return false;
                    }
                    $get = &$get[$k];
                }
                //we got through all the key
                return true;
            } else {
                return array_key_exists( $key, $this->get );
            }
        }
        /**
         * Gets/sets the {@link $get} value for the given key.
         * @param string $key.  Defaults to null meaning we return all of the post variables (it is not slash escaped).
         * @param mixed $val.  Defaults to null. If non-null we set the post value for $key to $val
         * @return mixed if no val is set: string if a key is given and found. null if key is given nut not found.  array otherwise
         */
        public function get( $key = null, $val = null ) {
            if ($key === null) {
                $key = array();
            }
            if (is_array($key)) {
                $get = &$this->get;
                foreach ($key as $k) {
                    if (!is_array($get) || !array_key_exists($k,$get)) {
                        if ($val === null) {
                            //we are getting
                            return null;
                        } else {
                            //we are setting
                            $get[$k] = array();
                        }
                    }
                    $get = &$get[$k];
                }
                //we got through all the keys
                if ($val === null) { //we are getting
                    return $get;
                } else {
                    $get = $val;
                }
            } else {
                if ($val === null) {
                    if ( $this->get_exists( $key ) ) {
                        return $this->get[ $key ];
                    } else {
                        return null;
                    }
                } else {
                    $this->get[$key] = $val;
                }
            }
        }


        /**
         * Holds a reference to a session request array if one has been
         * requested and exists in the session.
         * @var array
         */
        protected $session_req;
        /**
         * Check to see if a key exists in the {@link $session_req} array.
         * @return boolean
         */
        public function session_req_exists( $key ) {
            if (is_array($key)){
                $session_req = &$this->session_req;
                foreach ($key as $k) {
                    if (!array_key_exists($k,$session_req)) {
                        return false;
                    }
                    $session_req = &$session_req[$k];
                }
                //we got through all the key
                return true;
            } else {
                return array_key_exists( $key, $this->session_req );
            }
        }
        /**
         * Gets/sets the {@link $session_req} value for the given key.
         * @param string $key.  Defaults to null meaning we return all of the post variables (it is not slash escaped).
         * @param mixed $val.  Defaults to null. If non-null we set the post value for $key to $val
         * @return mixed if no val is set: string if a key is given and found. null if key is given nut not found.  array otherwise
         */
        public function session_req( $key = null, $val = null ) {
            if ($key === null) {
                $key = array();
            }
            if (is_array($key)) {
                $session_req = $this->session_req;
                foreach ($key as $k) {
                    if (!is_array($session_req) || !array_key_exists($k,$session_req)) {
                        return null;
                    }
                    $session_req = $session_req[$k];
                    if (!is_array($session_req)) {
                        break;
                    }
                }
                //we got through all the keys
                if ($val === null) { //we are getting
                    return $session_req;
                } else {
                    $session_req = $val;
                }
            } else {
                if ($val === null) {
                    if ( $this->session_req_exists( $key ) ) {
                        return $this->session_req[ $key ];
                    } else {
                        return null;
                    }
                } else {
                    $this->session_req[$key] = $val;
                }
            }
        }
        
    
        /**
         * Checks all the request arrays for the given key and returns
         * true if it exists.
         * @param mixed $key
         * @return boolean
         */
        public function request_exists( $key ) {
            return $this->session_req_exists( $key ) ||
                $this->get_exists( $key ) ||
                $this->post_exists( $key );
        }

        private $request_vars =false;
        /**
         * Return the given value for the key in one of the request arrays.
         * @param mixed $key
         * @return mixed
         */
        public function request( $key = null) {
            if (!is_array($this->request_vars)) {
                $this->request_vars = $this->session_req();
                I2CE_Util::merge_recursive( $this->request_vars, $this->get());
                I2CE_Util::merge_recursive( $this->request_vars, $this->post());
            }
            if ( $key === null ) {
                return $this->request_vars;
            } else    if (is_scalar($key)) {
                if (array_key_exists($key,$this->request_vars)) {
                    return $this->request_vars[$key];
                } else {
                    return null;
                }
            } else  if (is_array($key)) {
                $vars = $this->request_vars;
                foreach ($key as $k) {
                    if (!is_array($vars) || !array_key_exists($k,$vars)) {
                        return null;
                    }
                    $vars = $vars[$k];
                }
                return $vars;
            }
        }
    
        

        /**
         * Set the URL to be redirected to instead of displaying this page.
         * @param string $url
         */
        public function setRedirect( $url ) {
            if ( strpos( $url, "http" ) === false ) {
                $this->redirect = $url;
            } else {
                $this->redirect = "home";
            }
        }    
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
