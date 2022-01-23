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
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @since v1.0.0
 * @version v4.0.0
 */

/**
 * Static class with helper methods for I2CE objects.
 * 
 * This class mainly handles throwing errors from withing I2CE
 */


require_once 'I2CE_Error.php';
require_once 'I2CE_Dumper.php';
require_once 'I2CE_MagicData.php';
require_once 'I2CE_FileSearch.php';
require_once 'I2CE_FileSearch_Caching.php';
require_once 'I2CE_ModuleFactory.php';
require_once 'I2CE_Fuzzy.php';
require_once 'I2CE_Locales.php';
require_once 'I2CE_PDO.php';

/**
 * @package I2CE
 */
class I2CE   {    

    /**
     * @var static public string $email
     */
    public static $email='';
    /**
     * @var static protected I2CE_UserAccess_Mechanism $userAccess the user access mechanism
     */
    protected static $userAccess;


    /**
     *@var public static I2CE_FileSearch $fileSearch
     */
    protected static $fileSearch;

    /**
     * @var static protected string $userAccessProtocol the user access class 
     */
    protected static $userAccessProtocol ='';

    /**
     * @var static protected array $userAccessInit os string the user access initialization strings
     */
    protected static $userAccessInit =array();

    /**
     * @var static protected PDO PDO object
     */
    protected static $pdo;

    /**
     * Gets the registered user access protocol
     */
    public static function getUserAccessProtocol() {
        return self::$userAccessProtocol;
    }

    /**
     * Set the user access mechanism initialization string
     * @param string $userAccessInit
     * @param string $protocol. Defaults to null in which case we parse it from the init string
     * @param boolean $set_protocol.  Defatuls to false.  If true we set the current protocol to be the one we are registering
     */
    public static function setUserAccessInit($userAccessInit, $protocol = null, $set_protocol = false) {
        if (preg_match('/^([a-z_]+):\\/\\/(.*)/i',$userAccessInit, $matches)) {
            if ($protocol && $matches[1] != $protocol) {
                I2CE::raiseError("Invalid init string for protocol $protocol");
                return;
            } 
            $protocol = $matches[1];
            $init = $matches[2];
        } else {
            $protocol  = 'DEFAULT';
            $init = $userAccessInit;
        }
        if ($protocol == '') {
            $protocol = 'DEFAULT';
        }
        self::$userAccessInit[$protocol] = $init;
        if ($set_protocol) {
            self::$userAccessProtocol = $protocol;
        }
    }


    /**
     * Set the user access mechanism initialization string
     * @param string $protocol. If null, the default, then we get the init string for the currently registered protocol.
     * @returns string
     */
    public static function getUserAccessInit($protocol=null) {
        if ($protocol===null) {
            $protocol = self::$userAccessProtocol;
        } else if ($protocol === '') {
            $protocol = 'DEFAULT';
        }
        if (array_key_exists($protocol,self::$userAccessInit)) {
            return self::$userAccessInit[$protocol];
        } else {
            return '';
        }
    }

    /**
     * Set the user access mechanism
     * @param I2CE_UserAccess_Mechanism $userAccess
     */
    public static function setUserAccess($userAccess) {
        if (!$userAccess instanceof I2CE_UserAccess_Mechanism) {
            $userAcesss = false;
        }
        self::$userAccess = $userAccess;
    }


    /**
     * Get the user access mechanism
     * @returns I2CE_UserAccess_Mechansim
     */
    public static function getUserAccess() {
        if (!self::$userAccess instanceof I2CE_UserAccess_Mechanism) {
            $class = 'I2CE_UserAccess';
            if (self::$userAccessProtocol !== 'DEFAULT' ) {
                $class .= '_' . self::$userAccessProtocol;
            }
            $obj = null;
            if (class_exists($class)) {
                $obj  = new $class();
            }
            if (!$obj instanceof I2CE_UserAccess_Mechanism) {
                $obj = new I2CE_UserAccess_Mechanism(); //internal administrator access only
            }
            self::$userAccess = $obj;
        }
        return self::$userAccess;
    }

    




    /**
     * True if the site  has been initalized.
     * @param static protected boolean $site_initalized
     */
    static protected $site_initialized;

    
    /**
     * Get/Set the site's initialization state.
     *
     * @param init.  Defaults to null in which case we return the site
     * installation state.  if non-null, it is the new site
     * initialization state.
     *
     * @returns mixed.
     */
    public static function siteInitialized($init = null) {
        if ($init === null) {
            return self::$site_initialized;
        } else {
            self::$site_initialized  = $init;
        }
    }




    /**
     * Return the connected PDO object.
     * @return PDO
     */
    public static function PDO() {
        return I2CE_PDO::PDO();
    }

    /**
     * Return the database name being used.
     * @return string
     */
    public static function dbName() {
        return I2CE_PDO::details( 'dbname' );
    }


    /**
     *try to reconnect to the database
     * @return PDO
     */
    public static function dbReconnect() {
        return I2CE_PDO::reconnect();
    }

    /**
     * Setup the session variables 
     */
    protected static function setupSession() {
        if (!array_key_exists('HTTP_HOST',$_SERVER)) {
            //we were called from the command line - make a fake session
            session_id('fake-session-' . rand(100000,999999));
            session_start();
            return; 
        }
        // Check to make sure nothing funky goes on with the session and to avoid any
        // collisions with multiple I2CE applications running on the same server.
        $session_dir = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], "/")) . '/';
        $save_dir = session_save_path();
        if ( !$save_dir || $save_dir == "" ) {
            // If the save_path isn't set, then the default is normally
            // /tmp
            $save_dir = "/tmp";
        }
        $set_session_subdir = true;
        $depth_check = preg_split( "/;/", $save_dir );
        if ( count( $depth_check ) == 2 ) {
            if ( $depth_check[0] === "0" ) {
                $save_dir = $depth_check[1];
            } else {
                $set_session_subdir = false;
            }
        }
        //php does not allow all of the valid cookies names according to:  http://tools.ietf.org/html/rfc2965 RFC 2965
        //thus we are making things as easy for poor litle php as possible
        $cookie_name =  preg_replace( "/[^0-9a-zA-z]/", "_", substr($session_dir,1) );
        if($cookie_name === "") {
            $cookie_name = "i2ce";
        }
        if ( $set_session_subdir ) {
            $new_save_dir = $save_dir . DIRECTORY_SEPARATOR . $cookie_name;
            if ( file_exists( $new_save_dir ) || mkdir( $new_save_dir, 0700 ) ) {
                session_save_path( $new_save_dir );
            }
        }
        session_name( $cookie_name );
        session_set_cookie_params( 0,  $session_dir );
        session_start();
        if ( array_key_exists( "appdir", $_SESSION ) && $_SESSION['appdir'] != $session_dir  ) {
            session_destroy();
            session_start();
        }
        $_SESSION['appdir'] = $session_dir;
    }


    /**
     * Creates  the magic data instance.
     * Sets the mafic data storage mechanisms to be used.
     * @param boolean $set_config.  Defaults to true meaning we set the magic data storage for I2CE
     * @param boolean $set_config.  Defaults to false meaning we dont replace the magic data instance
     * @returns I2CE_MagicData on success, null on failure
     */
    public static function setupMagicData($set_config = true, $replace = false) {
        $config_protocol = self::getRuntimeVariable('I2CE_CONFIG_PROTOCOL',
                                             'config_alt' );
        switch (strtolower($config_protocol)) {
        case 'mongodb':
            require_once 'I2CE_MagicDataStorageMongoDB.php';
            $config = I2CE_MagicData::instance( "config", $replace );
            $store_mongo = new I2CE_MagicDataStorageMongoDB( "config" );
            if (!$store_mongo->isAvailable()) {
                I2CE::raiseError("MongoDB config  not available.  Trying to use original config_alt table");
                //the alternative config table has not been created.  the config table may be there, so let's try it
                require_once 'I2CE_MagicDataStorageDBAlt.php';
                $store_db = new I2CE_MagicDataStorageDBAlt( "config" );            
                if (!$store_db || !$store_db->isAvailable()) {
                    I2CE::raiseError("No persistent caching storage mechanism (apc or memchached) is available. Adding simple memory storage");
                    require_once 'I2CE_MagicDataStorageMem.php';
                    $config->addStorage( new I2CE_MagicDataStorageMem());		
                } else {
                    I2CE::raiseError("Added persistent caching storage mechanism config_alt");
                    $config->addStorage($store_db); 
                }
            } else {
                require_once 'I2CE_MagicDataStorageAPC.php';
                $store_mem = new I2CE_MagicDataStorageAPC( self::dbName() . "_config" );
                if ($store_mem->isAvailable()) {
                    $config->addStorage( $store_mem );               
                }
            }
            $config->addStorage($store_mongo); //store_mongo may not be available.  that's OK on initialization
            if ($set_config) {
                self::setConfig($config);
            } 
            return $config;
        default:
            require_once 'I2CE_MagicDataStorageAPC.php';
            require_once 'I2CE_MagicDataStorageDBAlt.php';
            $store_db = new I2CE_MagicDataStorageDBAlt( "config" );
            if (!$store_db->isAvailable()) {  
                I2CE::raiseError("Alt config table not available.  Trying to use original config table");
                //the alternative config table has not been created.  the config table may be there, so let's try it
                require_once 'I2CE_MagicDataStorageDB.php';
                $store_db = new I2CE_MagicDataStorageDB( "config" );
            }
            $config = I2CE_MagicData::instance( "config", $replace );
            $store_mem = new I2CE_MagicDataStorageAPC( self::dbName() . "_config" );
            if ($store_mem->isAvailable()) {
                $config->addStorage( $store_mem );               
            }
            require_once 'I2CE_MagicDataStorageMemcached.php';
            $store_memcached = new I2CE_MagicDataStorageMemcached( self::dbName() . '_config');
            if ($store_memcached->isAvailable()) {
                $config->addStorage($store_memcached);
            } else {
                I2CE::raiseError("No memcached");
            }
            if (!$store_mem->isAvailable() && !$store_memcached->isAvailable()) {
                I2CE::raiseError("No persistent caching storage mechanism (apc or memchached) is available. Adding simple memory storage");
                require_once 'I2CE_MagicDataStorageMem.php';
                $config->addStorage( new I2CE_MagicDataStorageMem());		
            }
            $config->addStorage($store_db); //store_dn may not be available.  that's OK on initialization
            if ($set_config) {
                self::setConfig($config);
            } 
            return $config;

        }
    }

    /**
     * Create and populate the file search
     *
     * @param array $paths.  An array with keys file search categories
     * and values a path or an array of paths to add to the file
     * search
     *
     * @param boolean $clear_cache.  Default to false.  If true, we
     * clear out the APC Cache.
     */
    public static function setupFileSearch($paths=array(), $clear_cache = false) {
        if (!array_key_exists('HTTP_HOST',$_SERVER)) { //command line
            self::$fileSearch = new I2CE_FileSearch(FALSE, FALSE, TRUE);
        } else {
            self::$fileSearch = new I2CE_FileSearch_Caching(FALSE, FALSE, TRUE);
            self::$fileSearch->setPrefix(self::dbName());
        }
        self::$fileSearch->
            setPreferredLocales('MODULES',array(I2CE_Locales::DEFAULT_LOCALE));
        self::$fileSearch->
            setPreferredLocales('CLASSES',array(I2CE_Locales::DEFAULT_LOCALE));
        if ($clear_cache && function_exists('apc_clear_cache')) {
            apc_clear_cache('user');
        }
        foreach ($paths as $cat=>$path) {
            if (is_array($path)) {
                foreach ($path as $p) {
                    self::$fileSearch->addPath($cat,$p);
                }
            } else if (is_string($path)) {
                self::$fileSearch->addPath($cat,$path);
            }
        }
    }


    static public function resetFileSearch() {
        if(is_object(self::$fileSearch)) {
            self::$fileSearch->reset();
        }
    }

    /**
     * Gets a runtime variable.  For the CLI is the is from the
     * environment which may be overwridden by a ---long-option from
     * the command line.  Otherwise it is set by SetEnv in a .htaccess
     * file (which is stored in $_SERVER)
     *
     * @param string $var the variable name
     * @param string $val the defualt value;  Defaults to the zero length string
     */
    public static function getRuntimeVariable($var,$val = '') {
        $t_val = getenv($var);
        if ($t_val) {
            $val = $t_val;
        }

        if (!array_key_exists('HTTP_HOST',$_SERVER)) { //command line
            require_once ("Console/Getopt.php"); 
            $cg = new Console_Getopt(); 
            $args = $cg->readPHPArgv();
            if (is_array($args)) {
                foreach ($args as $arg) {
                    if (preg_match('/^\-\-' . $var . '=(.*)/', $arg,$matches)) {
                        $val = $matches[1];
                        break;
                    }
                }
            }
        } else {
            if ( array_key_exists( $var, $_SERVER ) ) {
                $val  = $_SERVER[$var];
            }
        }
        return $val;
    }

    /**
     * Setup the references to database names in magic data
     * @param string $user_db defaults 
     */
    protected static function setupDatabaseReferences($user_db = null) {
        $db_name =  self::dbName();
        // Set the user db and quote the name so we don't need to worry about it later
        if ($user_db === null) {
            $user_db = $db_name;
        }       
        $user_db = $db->quoteIdentifier($user_db);
        $config = self::getConfig();        

    }



        
    /**
     * returns true if the url's have been written. false if not
     */
    public static function rewrittenURLs() {
        if ( array_key_exists( 'I2CE_Rewritten', $_SERVER ) && $_SERVER['I2CE_Rewritten'] == 'On' ) {
            return true;
        } else {
            return false;
        }
    } 
    


    /**
     * Returns the base url from which the site was accessed.  If no
     * .htaccess is used, ths will include the index.php.  If rewrites
     * are used (via .htacces) this will no include the
     * index.php. Point is... this is the base url from which the site
     * was accessed, no questions asked.  This of course assumes that
     * you are now accessing the site via the command line
     *
     *@return string
     */
    public static function getAccessedBaseURL($include_http = true) {
        $script = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], "/"));
        if ($include_http) {
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                $site_url = I2CE::getProtocol() . '://' . $_SERVER['HTTP_HOST']  . $script  . '/';
            } else {
                $site_url = 'http://localhost/';
            }
        } else {
            $site_url = $script  . '/';
        }
        if ( !self::rewrittenURLs() ) {
            $site_url = $site_url . 'index.php/';
        } else if ( ($_SERVER['SCRIPT_NAME'] . '/update.php' ) == $_SERVER['PHP_SELF'] ) {
            $site_url = $site_url . 'index.php/';
        }
        return $site_url;
    }


    /**
     * Return the protocol to use for accessing this site.
     * @return string
     */
    public static function getProtocol() {
        $protocol = 'http';
        if ( array_key_exists( 'HTTPS', $_SERVER ) && $_SERVER['HTTPS'] ) {
            $protocol = 'https';
        }
        return $protocol;
    }


    
    /**
     * Gets the core system going.
     * @deprecated since version 4.0.3
     *
     * @param string $db_user the database user
     * @param string $db_pass the database user's password
     * @param string $db_name  the name of the database all data is stored in
     * @param string $user_access_init the init string for the user access mechanism
     * @param string $site_module_file  the configttion file for the site module
     * @returns boolean.  True on sucess
     */ 
    public static function initialize($db_user, $db_pass, $db_name, $user_access_init,
                                      $site_module_file,
                                      $bring_up_system = true) {
        if (empty($db_name)) {
            self::raiseError("Please set the database database", E_USER_ERROR);
            if (!array_key_exists('HTTP_HOST',$_SERVER)) { //command line
                exit(10);
            } else {
                return false;
            }
        }

        if (empty($db_user)) {
            self::raiseError( "Please set the database user", E_USER_ERROR);
            if (!array_key_exists('HTTP_HOST',$_SERVER)) { //command line
                exit(11);
            } else {
                return false;
            }
        }

        if (empty($db_pass)) {
            self::raiseError("Please set the database user's password",
                             E_USER_ERROR);
            if (!array_key_exists('HTTP_HOST',$_SERVER)) { //command line
                exit(12);
            } else {
                return false;
            }
        }

        $protocol = self::getRuntimeVariable('I2CE_DB_PROTOCOL',
                                             '/var/run/mysqld/mysqld.sock' );
        if ( (strlen($protocol) <= 0) ) {
            $protocol = 'localhost';
        }

        $db_type =  self::getRuntimeVariable('I2CE_DB_TYPE', 'mysql' );
        if (empty($db_type)) {
            $db_type = 'mysql';
        }


        $dsn = "$db_type://$db_user:$db_pass@$protocol/$db_name";

        return self::initializeDSN( $dsn, $user_access_init, $site_module_file, $bring_up_system );
    }



    /**
     * Gets the core system going.  
     * @param string $dsn dsn string to connect to the database
     * @param string $user_access_init the init string for the user access mechanism
     * @param string $site_module_file  the configttion file for the site module
     * @returns boolean.  True on sucess
     */ 
    public static function initializeDSN($dsn, $user_access_init, $site_module_file,  $bring_up_system = true) {
//      '/^(?P<user>\w+)(:(?P<password>\w+))?@(?P<host>[.\w]+)(:(?P<port>\d+))?\\\\(?P<database>\w+)$/im'
        if (empty($dsn)) {
            self::raiseError( "Please set the dsn string", E_USER_ERROR);
            if (!array_key_exists('HTTP_HOST',$_SERVER)) { //command line
                exit(11);
            } else {
                return false;
            }
        }
        I2CE_PDO::initialize( $dsn );

        return self::initializePDO( $user_access_init, $site_module_file, $bring_up_system );

    }




    /**
     * Gets the core system going with PDO 
     * @param string $user_access_init the init string for the user access mechanism
     * @param string $site_module_file  the configttion file for the site module
     * @returns boolean.  True on sucess
     */ 
    public static function initializePDO( $user_access_init, $site_module_file, $bring_up_system = true ) {
 
        /** What's this for? Does it need to be done?
        if (I2CE_Dumper::dumpStaticURL(self::$dbDetails['dbname'], 'file')) {
            exit();
        }
        **/

        if (!I2CE_PDO::setup()) {
            self::raiseError("Could not connect to the database");
            if (!array_key_exists('HTTP_HOST',$_SERVER)) { //command line
                exit(13);
            } else {
                return false;
            }
        }

        self::setupSession();
        $clear = false;
        if (!array_key_exists('HTTP_HOST',$_SERVER)) { //command line
            $clear = self::getRuntimeVariable('clear_cache','0' );            
        } else {    
            $URL =  self::getAccessedBaseURL(false) . 'clear_cache.php';
            $clear =  (substr($_SERVER['REQUEST_URI'],0,strlen($URL)) == $URL) ;
        }
        if ($clear) {
            $config = self::setupMagicData();
            $config->setIfIsSet(self::$email,"/I2CE/feedback/to");
            session_destroy();
            if (function_exists('apc_clear_cache')) {
                apc_clear_cache('user');
                I2CE::raiseError("Cleared APC User Cache");
            }
            I2CE::raiseError("Session destroyed");
            I2CE::getConfig()->clearCache();
            I2CE::raiseError( "Magic data cleared -- Execution stopping");
            die(0);
        }
        $update =false;
        if (!array_key_exists('HTTP_HOST',$_SERVER)) { //command line
            $update = self::getRuntimeVariable('update','0' );            
        } else {    
            $URL =  self::getAccessedBaseURL(false) . 'update.php';
            $update =  (substr($_SERVER['REQUEST_URI'],0,strlen($URL)) == $URL) ;
        }
        if (self::siteInitialized()) {
            self::raiseError("Already initialized!",E_USER_WARNING);
            return true;
        }
        if (!$update && $bring_up_system) {
            // just assume it is until we know otherwise.  This error
            // message to don't dumped to the screen.
            self::siteInitialized(true);
        }

        I2CE_Error::resetStoredMessages();


        if (empty($site_module_file)) {
            self::raiseError( "Please set the site module's config file",
                              E_USER_ERROR);
            if (!array_key_exists('HTTP_HOST',$_SERVER)) { //command line
                exit(14);
            } else {
                return false;
            }
        }
        $config = self::setupMagicData();
        $config->setIfIsSet(self::$email,"/I2CE/feedback/to");
        $site_module_file = I2CE_FileSearch::absolut($site_module_file,1);
        self::setupFileSearch(array('MODULES'=>array(dirname(dirname(__FILE__))  ,dirname($site_module_file)),
                                    'CLASSES'=>dirname(__FILE__)));
        self::setUserAccessInit($user_access_init, null,true);
        if ($update) {
            require_once ('I2CE_Updater.php');
            if (!I2CE_Updater::updateSite($site_module_file)) {
                if (array_key_exists('HTTP_HOST',$_SERVER)) {
                    die("<br/>Could not update site");
                } else {
                    I2CE::raiseError("\nCould not update site\n");
                    exit(15);
                }
            } else if (!array_key_exists('HTTP_HOST',$_SERVER)) { //command line
                exit(0);
            }
            return true;
        } else {
            if ($bring_up_system && !self::bringUpSystem($site_module_file)) {
                self::raiseError("Could not bring up system", E_USER_ERROR);
                exit(15);            
            }    
            I2CE::$ob_level = ob_get_level();
            return true;
        }        

    }

    /**
     * @var public $ob_level The ooutput buffer level after initializion
     */
    public static $ob_level =0;

    
    protected static function bringUpSystem($site_module_file) {
        $nocheck = self::getRuntimeVariable('nocheck','0' );       
        if ($nocheck) {
            $status = 'done';
        } else {
            $status = self::allSystemsAreGoGo($site_module_file);
        }
        if ($status == 'done') {
            $mod_factory= I2CE_ModuleFactory::instance();
            // make sure all of our classes are loaded.
            $mod_factory->loadPaths(null,'CLASSES',true);
            $mod_factory->callHooks('post_configure');
            return true;
        } else if (substr($status,0,11) == 'in_progress') {
            //if we want to add an 'in_progress time out' we should do it here
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                $url = array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : "/";                
                $msg = "Site update in progress. We will wait a few moments before retrying.";
                echo "<script type='text/javascript'>if (confirm('$msg')) {setTimeout(function() {window.location= '$url';},3000)}</script>"; //reload the requested page after 5 seconds
                flush();
                exit();
            } else {
                //we are in the command line
                //if we are in upgrading/in_progress status from the command line, this should be a background script launched from a module
                //Let us assume that the module writer know what they are doing
                $mod_factory= I2CE_ModuleFactory::instance();
                $mod_factory->loadPaths(null,'CLASSES',true); //make sure all of our classes are loaded.            
                return true;
            }
        } else if (substr($status,0,5) =='needs') {
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                $url =  rtrim(self::getAccessedBaseURL(true),'/');
                if (substr($url,-9) != 'index.php') {
                    $url .= '/index.php';
                }
                $url .= '/update.php';
                if (array_key_exists('REQUEST_METHOD',$_SERVER) && $_SERVER['REQUEST_METHOD'] == 'GET' && array_key_exists('REQUEST_URI', $_SERVER)) {
                    $url .= '?request=' . urlencode($_SERVER['REQUEST_URI']);
                }
                echo self::$updateStart;
                echo I2CE_Error::$errorImage;
                $msg = "The site needs to be updated  ($status).  Redirect to update script?";                
                echo "<a href='$url'>Redirect </a >to update script? ";
                echo "<script type='text/javascript'>if (confirm('$msg')) {setTimeout(function() {window.location= '$url';},500)}</script>";

                flush();
                exit();
            } else {
                //command line
                echo "The site " . strtr($status,'_',' ') . ".\nPlease run 'php index.php --update=1' from the site directory\n";
                return false;
            }
        } else if ($status == 'no_site') {
            return false;
        } else {
            I2CE::raiseError("Unrecognzied site status: $status");
            return false;
        }
        
        return true;
    }


    static protected $updateStart = 
        '<html><body><center>
<div id="header">
  <h2 style="color:#993300">iHRIS Site Update</h2>
</div>
<div id="message"
     style="text-align:left;width:75%;height:30%;
            padding-bottom:1em;
            overflow:auto;margin-top:0;
            background-color:#ffffcc;border:dashed;border-width:3px;border-color:#ffcc99;opacity:0.8;">
     <b style="display:block">User Notice</b>Your site needs to be updated.
</div>
';


    /**
     * Get the system status.  http://www.ursula1000.com/
     * @returns string 'gogo' means we are good. 'needs_installation'  means we need to initialize. 'needs_upgrade'
     */
    public static function allSystemsAreGoGo($site_module_file, $check_time = false) {
        $site_module_file = I2CE_FileSearch::realPath($site_module_file);
        $config = self::getConfig();
        $site_module = '';
        $config->setIfIsSet($site_module,'/config/site/module');
        $mod_factory =  I2CE_ModuleFactory::instance();
        $installed = '';

        $config->setIfIsSet($installed,"/config/site/installation");
        if ($installed == '') { 
            return 'needs_install';
        }
        if (!$site_module) {
            self::raiseError("Cannot determine what your site is.  This is bad.\n($site_module_file)", E_USER_ERROR);
            return 'no_site';
        }
        if (substr($installed,0,11) == 'in_progress') {
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                self::raiseError("Warning: Trying to access the site while update is in progress. Do you know what you are doing?");
            } else {
                self::raiseError("Warning: Trying to access the site while update is in progress. Run --update=1 --force-restart=1 to restart");
            }
            return $installed;
        } else if ($installed == 'done') {
            $previous_site_file = '';        
            if ($config->setIfIsSet($previous_site_file,'/config/data/' . $site_module . '/file')) {
                $previous_site_file = I2CE_FileSearch::realPath($previous_site_file);
                if  ( $previous_site_file != $site_module_file) {  
                    I2CE::raiseError("Need reinstall ($previous_site_file) != ($site_module_file) for site module $site_module");
                    return 'needs_reinstall';
                } 
            }
            $mod_factory= I2CE_ModuleFactory::instance();
            if (!$mod_factory->isEnabled($site_module)) {
                return 'needs_reenable';
            }
            //$installed == 'done' and the site module/site module file are good.  This should be the 'usual state of affairs'
            $times = array();
            $config->setIfIsSet($times,'/I2CE/update/times',true);
            if (!isset($times['stale'])) {
                $times['stale'] = 10;
            }
            if (!$check_time) {
                if (($times['stale'] < 0 ) || (!isset($times['last']))) {
                    $check_time = true;
                } else  if (isset($times['last']))  {
                    $check_time = ( (time() - $times['last']) > ($times['stale']));
                }
            }
            if ($check_time) { 
                I2CE::longExecution(null,false);
                //we are due to check the config files/modules for updates
                $config->__set("/I2CE/update/times/last", time());
                $updates = $mod_factory->getOutOfDateConfigFiles();
                $config->__set("/I2CE/update/times/last", time());
                if ( (count($updates['updates']) + count($updates['removals'])> 0)) {
                    return 'needs_upgrade';
                }
            }
            return 'done';
        } else {
            self::raiseError("Unknown installation status:" . $installed,E_USER_ERROR);
            return 'unknown';
        }
    }


    public static function longExecution($limits = null, $notice = false) {
        if (!is_array($limits)) {
            $limits = array('max_execution_time'=>1800, 'memory_limit'=> (256 * 1048576));
        }
        if ($notice) {
            self::raiseError("Setting long execution settings to be at least: " . print_r($limits,true));
        }
        foreach ( $limits as $key=>$desired_val) {
            $val = ini_get($key);
            $val = trim($val);
            if ( strlen($val) == 0) {
                continue;
            }
            $last = strtolower($val[strlen($val)-1]);
            if (!is_numeric($last)) {
                $val = substr($val, 0, -1);
            }
            switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
            }
            if ( ($val > 0) && ($val < $desired_val)) {
                ini_set($key,$desired_val);
            }
        }
    }

    /**
     * Get the configuration storage
     * @param string $instance.  Defaults to 'default'
     * @returns I2CE_MagicData 
     */
    public static function getConfig() {
        return self::$storage;
    }


    /**
     * Sets an instance magic data object
     * @param I2CE_MagicData $magicdata
     */
    public static function setConfig($magicData) {
        self::$storage = $magicData;
    }





    public static function getFileSearch() {
        return self::$fileSearch;
    }



    /**
     * @var protected static I2CE_MagicData
     */ 
    protected static $storage; 

  /**
     * Raise an error message, but don't display any extra trace messages to
     * keep the log file short when the trace isn't necessary.
     * @param string/mixed $message The error message.
     * @param integer $type The error type.
     * @param string $redirect The page to redirect to for critical errors.
     */
    static public function raiseMessage( $message = null, $type=E_USER_NOTICE,
                                       $redirect="" ) {
        I2CE_Error::raiseMessage($message,$type,$redirect);
    }

    /**
     * Raise an error and redirect the user for any critical errors.
     * 
     * The default redirect will go to the home page for the site.
     * @param string/mixed $message The error message.
     * @param integer $type The error type.
     * @param string $redirect The page to redirect to for critical errors.
     * @global array
     */
    static public function raiseError( $message = null, $type=E_USER_NOTICE,
                                       $redirect="" ) {
        I2CE_Error::raiseError($message,$type,$redirect);
    }
    
    static public function handleError($err_no, $err_string, $err_file = false, $err_line = false , $err_context = false) {        
        I2CE_Error::handleError($err_no,$err_string,$err_file,$err_line,$err_context);
    }


 
    /**
     * Check to see if a object is a pear error and raise an error if it is.
     * @param mixed $obj
     * @param string $message The error message to display if it is an error.
     * @param integer $type The error type to raise.
     * @param string $redirect The page to redirect to if this is a critical error.
     */
    static public function pearError( $obj, $message, $type=E_USER_NOTICE, $redirect="" ) {
        if ( PEAR::isError( $obj ) ) {
            I2CE_Error::raiseError( $message . ":\n" . $obj->getMessage() . "\n" . $obj->getUserInfo(), $type, $redirect );
            return true;
        }
        return false;
    }

    /**
     * Display a PDO Exception error
     * @param PDOException $err
     * @param string $message Any extra message to display.
     * @param integer $type The error type to raise.
     * @param string $redirect The page to redirect to if this is a critical error.
     */
    static public function pdoError( $err, $message, $type=E_USER_NOTICE, $redirect="" ) {
        I2CE_Error::raiseError( $message . ":\n" . $err->getMessage(), $type, $redirect );
    }
    

    /**
     * See if there were any warning messages set before the site was initialized
     * @returns boolean
     */
    public static function hasWarnings() {
        return I2CE_Error::hasWarnings();
    }

    
}

# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
