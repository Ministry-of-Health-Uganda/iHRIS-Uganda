<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * This file is part of I2CE. I2CE is free software; you can
 * redistribute it and/or modify it under the terms of the GNU General
 * Public License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later
 * version. I2CE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details. You should have received a
 * copy of the GNU General Public License along with this program. If
 * not, see <http://www.gnu.org/licenses/>.
 * @package I2CE
 * @version 0.1
 * @access public
 */

if (!class_exists('I2CE_FileSearch',false)) {

    /**
     * Class to handle file searching.
     * @package I2CE
     */
    class I2CE_FileSearch {
        

        /**
         * If not null, it is the file system path that all paths are attempted to resolve to. 
         * If null, all resolved paths are absolute
         * @protected static string $relative_path
         */
        protected static $relative_path = null;


        /**
         * The root drive of the relative path e.g. "C:" on windows or '/' on unix.  
         * on windows unix it may or may  not  contains the trailing "\" due to the following maddness:
         * *realpath(C:) is C:\
         * *realpath(C:\) is C:\
         * *realpath(C:\\) is C:\
         * *realpath(C:\some) is C:\some
         * *realpath(C:\some\) is C:\some
         * @protected static string $relative_root
         */
        protected static $relative_root = null;


        /**
         * The parts of the relative path.  On windows, it does '''not''' include the drive letter.
         * @protected static array of string $relative_parts
         */
        protected static $relative_parts = array();


        /**
         * Gets the real path, realpath(),  of a given path on unix.
         * On windows, if the relative path is not set, then this is just realpath(),
         * otherwise it tries to resolve it as a path relative to the {@link $relative_root}
         * return mixed. String or false on failure
         * @param string $path true
         * @param booelan $ensure_existence.  Defaults to true in which case we ensure the existence of the path and get its realpath according to the OS
         * @returns mixed.  string or false on failure
         */
        public static  function realPath($path,$ensure_existence = true) {
            if (!is_string($path)) {
                return false;
            }
            if (self::$relative_path != null && !self::isAbsolut($path)) {
                $path =self::$relative_path . DIRECTORY_SEPARATOR . $path;
            }
            if ($ensure_existence) {
                return realpath($path);
            } else {
                return $path;
            }
        }



        /** 
         * Gets the relative real path of a given path on a windows box.
         * If the relative path is not set, then this is just realpath(),
         * otherwise it tries to resolve it as a path relative to the {@link $relative_root}
         * See {@link  windows path conventions}.
         * @param string $path
         * return mixed. String or false on failure
         */
        public static  function relativePath($path) {            
            if (!is_string($path) ) {
                return false;
            }
            if (self::$relative_root == null ) {   //this will always be the case for unix             
                return realpath($path);
            } else  if (!self::isAbsolut($path)) { 
                I2CE::raiseError("Trying to get relative path of a non-absolut path  $path"); 
                return false;
            } 
            //$path  is absolut and we have a relative path set.
            //first we make sure that the path is valid
            if (! ($path =  realpath($path))) {
                //it was a bad absolute path
                return false;
            }
            //now  let's make sure the root drives match
            $root = self::getRootPath($path);
            if ( !$root || $root != self::$relative_root) {
                //root does not match so we need to just return the path
                return $path;
            }
            //now we know we are in the same drive so we can reasonably expect to get a relative path
            $parts = self::getParts($path);
            //on windows something like C:\toolkit\platfrom will result in $parts = array(C:\, toolkit, platform)
            //so if we are on windows, let's kick off the drive. as that was already done for self::$relative_parts
            array_shift($parts);
            $relative_parts =  array_merge( array_pad(array(), count(self::$relative_parts)  , '..'), $parts);
            $real_path = 
                realpath(
                    self::$relative_root . DIRECTORY_SEPARATOR .
                    implode(DIRECTORY_SEPARATOR , array_merge(self::$relative_parts, $relative_parts))
                    );
            if ($real_path != $path) { //double check the starting path matches this relative  path
                //something failed so just reutrn the starting path
                return $path;
            }   
            return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR,$relative_parts);
        } 


        /**
         * Returns the parts of a path split up according to the directory spearator.  Does _not_ check to see if the given $path is valid
         * @param string $path
         * @param mixed string $dir_sep.  chracter or array of charaterds. Defautls to DIRECTORY_SEPARATOR.  A single character used as the directory sepearator
         * @param string $esc_char.  Defautls to '\' if $dir_sep is '/' and detaults to none if the $dir_sep is '\'.  
         *  Set it to false if you don't want any escape characters
         * @param boolean $ignore_empty.  If true (default), we dont add "empty" parts e.g. C:\some\\path returns array('c:','some','path').
         * Exmaple: \toolkit\path returns array('toolkit','path')
         * @return $array
         */
        public static   function getParts($path, $dir_sep = DIRECTORY_SEPARATOR, $esc_char = null , $ignore_empty = true) {
            if ($esc_char === null) {
                if ($dir_sep == '/') { //unix like
                    $esc_char = '\\';
                } else {
                    $esc_char = false;
                }
            }
            if (!is_string($path)) {
                return array();
            }
            $parts = array();
            $in_escape = false; 
            $len = strlen($path);
            $index = 0;
            $part = '';
            for ($i=0; $i < $len; $i++) {  
                $c= $path[$i];
                if ($in_escape) {
                    $in_escape = false;
                    $part .= $c;
                } else {
                    if ($c == $dir_sep) {  //note: here we may have c:\some\path\with\a\\double\slash
                        if (!$ignore_empty || strlen($part) > 0) {
                            $parts[] = $part;
                        }
                        $part = '';
                        $in_escape = false;
                    } else if ($c == $esc_char) {
                        $in_escape = true;
                    } else {
                        $part .= $c;                    
                        $in_escape = false;
                        
                    }
                }
            }
            if (strlen($part) > 0) {
                $parts[] = $part;
            }
            return $parts;
        }

        /**
         * Attempts to set the path for which all files are
         * referenced relative to.  not implemented for unix.
         *
         * This is mainly used for the usb-toolkit on windows:
         *
         * @param string $path
         *
         */
        public static function setRelativePath($path) {
            if (!is_string($path) || self::isUnixy()) {
                self::$relative_path =null;
                self::$relative_root = null;
                self::$relative_parts = array();
            } 
            $r_path = realpath($path . DIRECTORY_SEPARATOR) ;
            $root = self::getRootPath($r_path);
            if (!$r_path || !$root || !is_dir($r_path) ) {
                $path =null;
                $root =null;
                $parts = array();
            } else {
                $path = $r_path;
                $parts = self::getParts($r_path);
                if (!self::isUnixy()) {
                    if (count($parts) == 0) { 
                        //this shouldn't happen as there is a root path already checked.  just being paranoid
                        $path = null;
                        $root = null;
                        $parts = null;
                    } else {
                        array_shift($parts);
                    }
                }
            }
            self::$relative_path = $path;
            self::$relative_root = $root;
            self::$relative_parts = $parts;
        }

        /**
         * Returns the path which all files are attempted to resolve relative to
         *
         * @returns mixed string, the path, or null if there is no relative path
         */
        public static function getRelativePath() {
            return self::$relative_path;
        }

        /**
         * Attempts to get the base directory of the given path.
         * On unix (or non-windows) this is always '/'
         * On windows it is the drive with colon and no trailing slash, e.g. 'E:' 
         * @param string $path An absolute path
         * @returns mixed.  A string on success, null on failure
         */
        public static function getRootPath($path) {
            if (!is_string($path)) {
                return null;
            }
            if (self::isUnix()) {
                return '/';
            }
            //we are windows
            if (preg_match('/^([a-zA-Z]:)\\\\/',$path,$matches)) {  //
                return $matches[1];
	    } else {
                return null;
            }
        }
        
        /**
         * The found locale(s)
         *
         * @var mixed $found_locales.  One of: FALSE, a string, or an
         * array.  The former two in the case of a single serach, the
         * later in case of a $find_all search
         */
        protected  $found_locales;

        /**
         * Set the preferred locale(s) for a category. If not set,
         * defaults the locales default to I2CE::DEFAULT_LOCALE
         *
         * @param string $category         
         * @param mixed $locales string or array of string. The locales.
         * @param boolean $validate.  Defaults to true.
         */
        public function setPreferredLocales($category,$locales, $validate =true) {
            if ($validate) {
                $locales = I2CE_Locales::validateLocales($locales);
            }
	    $this->preferred_locales[$category] = $locales;
	}

	/**
	 * @var protected array $preferred_locales.  Keys are
	 * categories.	Values are arrrays of string, the preferred
	 * locales.
	 */
	protected $preferred_locales;
	

	/*
	 *proteced @var array $ordered_paths
	 * Keys are strings which is the category of a path
	 * values are arrays with keys numbers and values an array of paths.
	 */
	protected $ordered_paths;

	/**
	 * protected $var boolean $absolut -- wheter or not to make
	 * relative paths absolut based on the directory of the
	 * calling file
	 */
	protected $absolut;


	/**
	 * protected $var boolean $search_hidden.  Whether or not to
	 * search hidden sub-directories
	 */
	protected $search_hidden;

	/**
	 * protected @var boolean $search_cwd.	Whether or not to
	 * seach the current working directory
	 */
	protected $search_cwd;


	/**
	 * protected @var array $last_order values are the order of
	 * the last thing added to the category keys are the classes.
	 */
	protected $last_order;

	/**
	 * protected @var array $checked_directories array with keys
	 * directories and values boolean It is true if the directory
	 * has already been checked.  Used to avoid recursion,
	 */
	protected $checked_directories;

	/**
	 * Constructor for the FileSearch class
	 * @param boolean $hidden. Defaults to false.  Whether or not to search
	 *	  hidden sub-directories.  At the moment it does not have meaning
	 *	  on non unix like platforms.
	 * @param boolean $current_working. Defaults to false.	Whether or not to
	 *	  search the current working directory for the file.  If so, it
	 *	  checks there first, before the other paths.
	 * @param boolean $make_absolut.  Defaults to false.  Whether
	 *	  or not to make a relative path absolute when adding it.
	 */
	public	function __construct($hidden = false,$current_working=false,
				     $make_absolut = false) {
	    $this->preferred_locales = array(); 
	    $this->ordered_paths = array();
	    $this->search_hidden = $hidden;
	    $this->search_cwd = $current_working;
	    $this->absolut = $make_absolut;
	    $this->last_order = array();
	}

	public function reset() {
	    $this->preferred_locales = array(); 
	    $this->ordered_paths = array();
	    $this->found_locales = array();
	    unset($this->search_hidden);
	    unset($this->search_cwd);
	    unset($this->absolut);
	    $this->last_order = array();
	}

	/*
	 * Set whether or not to search the current working directory
	 * @param boolean $search
	 */ 
	public function searchCurrentWorkingDirectory($search) {
	    $this->search_cwd = $search;
	}

	/*
	 * Set whether or not to search hidden sub-directories
	 * @param boolean $search
	 */
	public function searchHiddenSubdirectories($search) {
	    $this->search_hidden = $search;
	}

	/**
	 * Set whether or not to make relative paths absolute
	 * @param boolean $absolut
	 */
	public function changeRelativeToAbsolut($absolut) {
	    $this->absolut = $absolut;
	} 

	/**
	 * Checks to see if a path is absolute.	 All files identified
	 * by a URI/L are considered to be absolute
	 *
	 * @returns boolean
	 */
    public static function isAbsolut($path) { 
        if (preg_match('/^([a-zA-Z][a-zA-Z\d+\.\-]+):/',$path)) { //urls are absolute
            return true;
        }
        if (DIRECTORY_SEPARATOR == '\\') {	//windows style
            return preg_match('/^[a-zA-Z]:\\\\/',$path);
        } else { //unix style; 
            return ($path[0] == '/'); 
        } 
    } 
	
	/**
	 *  Makes a relative file path or file url absolute relative
	 *  to the directory that the function was called in.
	 *
	 *  @param string $path a path or URL
	 *  @param int $indx the index, from the top level, to
	 *	    consider the calling file defaults to 0 which is
	 *	    the file that is calling this.
	 *
	 *  @returns string the absolute path or URL
	 */
	public static function absolut($path,$indx = 0) {
	    if (!I2CE_FileSearch::isAbsolut($path)) {
		//it is not an absolute path.	make it into one.
		$dbg_bt = debug_backtrace();
		$path = dirname ($dbg_bt[$indx]['file']) . DIRECTORY_SEPARATOR .
		    $path; 

	    }		    
        $real_path = realpath( $path );
        if ( $real_path === false ) {
            I2CE::raiseError( "File does not exist: $path" );
            return $path;
        } else {
            return $real_path;
        }
	}

	/** Get the last order set for the specified category.	
	 * @param string $category
	 * @returns int
	 */
	public function getLastOrder($category) {
	    return $this->last_order[$category];
	}

	/**
	 * Get the categories specified
	 * @returns array of string
	 */
	public function getCategories() { 
	    return array_keys($this->last_order);
	}


	/**
	 * Gets the search path for the specified catgegory
	 *
	 * @param string $category
	 * @param boolean $localized. (FALSE)
	 *
	 * @returns array.  If not localized with keys integers, the
	 * orders, and values an array of strings, the paths for that
	 * order, if $localized is TRUE, and keys are paths values are
	 * locales.
	 */
	public function getSearchPath($category,  $localized = FALSE) {
	    if (!array_key_exists($category,$this->ordered_paths) ||
		!is_array($this->ordered_paths[$category])) {
		return array();
	    }
	    if ($localized) {
		$paths = $this->ordered_paths[$category];
	    } else {
		$paths = array();
		foreach ($this->ordered_paths[$category] as $order=>$ps) {
		    $paths[$order] = array_keys($ps);
		}
	    }
	    return $paths;
	}


	/**
	 * On windows, convert a given file in a given directory to a 8.3 filename
	 * @param string $file
	 * @returns string
	 */
	protected static function findWinShortFileName($file,$dir) {	
	    $exec = "dir /X \"" . $dir .  "\"";
	    $contents = explode("\n",trim(shell_exec($exec)));
	    $begun = false;
	    foreach ($contents as $line) {
		if (!$begun) {
		    $begun =  ( substr(ltrim($line),0,12) == 'Directory of');	    
		    continue;
		}
		if (!preg_match("/^(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)\s*$/",$line,$matches)) {
		    continue;
		}
		if ($matches[6] == $file) {
		    return $matches[5];
		}
	    }
	    return $file;
	}


	/**
	 * Attempt to convert a windows long path name to its 8.3 version
	 * @param string $file.	 The absoluate path to a file/directory
	 * @returns @string
	 *
	 * Example:  C:\Program Files\my_long_dir  becomes c:\program~1\my_lon~1
	 */
	public static function findWinShortPathName($file) {	 
	    $t_file = realpath($file);
	    if (!$t_file) {
		return $file;
	    }
	    $file = $t_file;
	    $dir =dirname($file); 
	    if (is_dir($file)) {
		if ( $dir == $file) {
		    //just in case we stat with $file = c:\ or some other root directory
		    return $file;
		}
	    } else if (!is_file($file)) {
		//this is not a file.. maybe its a link or something
		return $file;
	    }
	    $short_file = self::findWinShortFileName(basename($file),$dir);	
	    $path = '';
	    $dir_part = basename($dir);
	    $last_dir = $dir;
	    $dir = dirname($dir);
	    if ($dir != $last_dir) {
		$last_dir = false;
	    }
	    while ($dir != $last_dir && $dir) {
		$path = self::findWinShortFileName($dir_part,$dir) .  DIRECTORY_SEPARATOR . $path;
		$dir_part = basename($dir);
		$last_dir = $dir;
		$dir = dirname($dir);
	    }
	    return $dir . $path . $short_file;
	}


	/**
	 * Convert a relative windows path with / to  one with \
	 * @param string $path
	 * returns $string
	 */
	public static function convertUnixToWin($path) {
	    if (self::isAbsolut($path)) {
		return $path;
	    }
	    return strtr($path,'/','\\');
	}


        /**
         * @var protected static string @os  The OS string.  
         */
        protected static $unixy= null;
        /**
         * Check to see if the operating system is unix like
         *@returns boolean
         */
        public static function isUnixy() {
            if (self::$unixy === null) {
                $os = php_uname('s');
                self::$unixy = ! (stristr($os , 'WIN') && !preg_match("/darwin/i",$os));
            }
            return self::$unixy;

        }

        /**
         * Will conver paths in Unix style to windows style if needed by calling the {convertUnixToWin()} function
         * @param string $path
         * @param boolean $strip.  If true we strip any trailing separators.  Defaults to false
         * @returns see {convertUnixToWin()}
         */
        public static function convertPath($path, $strip = false) {
            if (!self::isUnixy()) {
		$path = self::convertUnixToWin($path);
            }
            if ($strip) {
		//glob is not happy on windows with a trailing directory separator
                $path = rtrim($path,DIRECTORY_SEPARATOR);
            }
            return $path;
        }

        /**
         * A path to search for a category of files.
         * @param string $category the category of files.
         * @param string $path the parh (or glob pattern for a path) to add.  There a a few modifications to the globbing...
         *        A trailing '**' means that the paths should be added recursivley
         *        Paths are automatically checked for localized version by the presence of a 'en_US' subdirectory.
         *        specifically, if a path is added such as /my/path  and there is a subdir called /my/path/en_US  then
         *        all subdirectories which are in the preffered locales (or $locales below) are added.
         *
         *      Examples:  /usr/share/fonts/      adds in this directory to the search paths
         *                 /usr/share/fonts/*  adds in all subdirectories
         *                 /usr/share/fonts/**  recusively adds in all subdirecties
         *                 /usr/share/fonts/truetype/ttf*  adds in all directories begining with ttf
         * @param mixed $order.  If it is a  number, then it indicates the order in which this path is  searched.
         *         The lower the number, the higher the priority.
         *         If multiple paths share the same order, the order in which they are searched is not specfied
         *         The are also special string values of $order (all are relative to the category specified):
         *         <ul>
         *         <li>'LAST' -- add at the last added order </li>
         *         <li>'LOWEST' -- add so it has the lowest order thus far  </li>
         *         <li>'HIGHEST' -- add so it has the highest order thus far </li>
         *         <li>'EVEN_LOWER' -- add so it has lower order than anything thus far.  This is the default behavior </li>
         *         <li>'EVEN_HIGHER' -- add so it has higher  order than anything thus far </li>
         *         </ul>
         * @param boolean $absolut whether or not to try to make a relative path absolut.  It will
         *         overide (but not change) the global behaviour of this instance.
         * @param string $path_prefix.  Defaults to null.  Any prefix to add to a local class path (if we do make absolute)
         * @param mixed $locales null(default) , string or array of string.  Used to override the default locale settings when not the defaults of null.  
         * It is the list of localized sub-directories to search for, if the given glob is a subdirectory.
         */
        public function addPath($category,$path,$order = 'EVEN_LOWER',$absolut=null,$path_prefix=null, $locales = null) {
            $path = self::convertPath($path);
            $path_prefix = self::convertPath($path_prefix);
            if (!is_string($path) || strlen($path) == 0) {
                return FALSE;
            }
            if ($absolut === null) {
                $absolut = $this->absolut;
            }
            $recurse = false;
            if (strlen($path) >= 2 && substr($path,strlen($path)-2) == '**') {
                $recurse = true;
                $path = substr($path,0,-1);
            }
            if ($absolut && !$this->isAbsolut($path)) {
                //relative directorty which we need to change to a absolut directory
                if (($path_prefix === null) || (is_string($path_prefix) && strlen($path_prefix) == 0)) {
                    $path_prefix = $this->absolut('.' . DIRECTORY_SEPARATOR,1);
                }
                /* see  http://us.php.net/manual/en/function.glob.php#86425 for glob and preg_replace */
                $path = preg_replace('/(\*|\?|\[)/', '[$1]', $path_prefix) . DIRECTORY_SEPARATOR . $path;                
            }
            $path = self::convertPath($path,true);
	    if (strlen($path) == 0) {
		return false;
            }
            $t_locales = null;
            $found_dirs = array();
            $globs = array($path => I2CE_Locales::DEFAULT_LOCALE); 
            if (!is_array($locales)) {
                if (is_string($locales)) {
                    $locales = array($locales);
                    $locales = I2CE_Locales::validateLocales($t_locales);
                } else  if (array_key_exists($category,$this->preferred_locales) && is_array($this->preferred_locales[$category])) {
                    $locales = $this->preferred_locales[$category];
                    $locales = I2CE_Locales::validateLocales($locales);
                } else {
                    $locales = I2CE_Locales::getPreferredLocales();
                }                        
            } else {
                $locales = I2CE_Locales::validateLocales($locales);
            }
            while (count($globs) > 0)  {
                end($globs);
                $glob = key($globs);
                $locale = array_pop($globs);
                $dirs = glob($glob, GLOB_ONLYDIR | GLOB_NOSORT );
                if ($dirs === false) {
                    continue;
                }
                foreach ($dirs  as $dir) {
                    if ($locale == I2CE_Locales::DEFAULT_LOCALE && is_dir($dir . DIRECTORY_SEPARATOR . I2CE_Locales::DEFAULT_LOCALE . DIRECTORY_SEPARATOR . '.' )) {
                        //this directory is localized.
                        foreach ($locales as $t_locale){
                            $l_dir = $dir . DIRECTORY_SEPARATOR . $t_locale;
                            if (is_dir($l_dir)) {
                                $found_dirs[$l_dir] = $t_locale;
                                if ($recurse) {
                                    $globs[preg_replace('/(\*|\?|\[)/', '[$1]', $l_dir) . DIRECTORY_SEPARATOR . '*'] = $t_locale;
                                }
                            }
                        }
                    } else {
                        //this directory is not localized
                        $found_dirs[$dir] = $locale;
                        if ($recurse) {
                            $globs[preg_replace('/(\*|\?|\[)/', '[$1]', $dir) . DIRECTORY_SEPARATOR . '*'] = $locale;
                        }
                    }
                }                
            }
            if (count($found_dirs) == 0) {
                return FALSE;
            }
            if (is_string($order)) {
                if (!array_key_exists($category,$this->ordered_paths) || !is_array($this->ordered_paths[$category]) || count($this->ordered_paths[$category]) ==0) {
                    //nothing added yet. 
                    $order = 0;
                } else {
                    $order = strtoupper($order);
                    switch ($order) {
                    case 'LAST':
                        $order = $this->last_order[$category];
                        if ($order == null) {
                            //this should not happen but we are being safe
                            $order = 0;
                        }
                        break;
                    case 'LOWEST':
                        $keys =array_keys($this->ordered_paths[$category]);
                        $order =  $keys[count($keys)  - 1];
                        break;
                    case 'HIGHEST':
                        $keys =array_keys($this->ordered_paths[$category]);
                        $order =  $keys[0];
                        break;
                    case 'EVEN_HIGHER':
                        $keys =array_keys($this->ordered_paths[$category]);
                        $order =  $keys[0] -1;
                        break;
                    default: // 'EVEN_LOWER'
                        $keys =array_keys($this->ordered_paths[$category]);
                        $order =  $keys[count($keys)  - 1]  + 1;
                        break;
                    }
                }
            } else if (!is_int($order)) {
                if (class_exists('I2CE',true)) {
                    I2CE::raiseError("Invalid order ($order) when setting path ($path)",E_USER_NOTICE);
                    return FALSE;
                } else {
                    I2CE::raiseError("Invalid order ($order) when setting path ($path)",E_USER_ERROR);
		    return false;
                }
            }
            foreach ($found_dirs as $dir=>$locale) {                
                $this->ordered_paths[$category][$order][realpath($dir)] = $locale;
            }
            ksort($this->ordered_paths[$category]);
            $this->last_order[$category] = $order;
            return TRUE;
        }
        

        /**
         * Remove a path in a category
         *
         * @param string $category
         * @param string $path
         * @param boolean $absolut whether or not to try to make a
         *         relative path absolut.  It will overide (but not
         *         change) the global behaviour of this instance.
         * @param string $path_prefix.  Defaults to null.  Any prefix
         *         to add to a relative class path (if we do make
         *         absolute)
         */
        public function removePath($category, $path,
                                   $absolut=FALSE,$path_prefix=NULL)  {
            if ($absolut) {
                if (($path_prefix === NULL) ||
                    (is_string($path_prefix) &&
                     strlen($path_prefix) == 0)) {
                    $path = $this->absolut($path,1);
                } else {
                    if (!$this->isAbsolut($path)) {
                        $path = $path_prefix . DIRECTORY_SEPARATOR . $path;
                    }
                }
            }
            if (!array_key_exists($category,$this->ordered_paths) ||
                !is_array($this->ordered_paths[$category])) {
                return;
            }
            foreach ($this->ordered_paths[$category]  as $order=>$paths) {
                foreach ($paths as $p=>$locale) {
                    if ($p == $path) {
                        unset($this->ordered_paths[$category][$order][$p]);
                        if (count($this->ordered_paths[$category][$order]) == 0) {
                            unset($this->ordered_paths[$category][$order]);
                        }
                    }
                    
                }
            }
        }

        /**
         * Remove a list of paths from the category path list.
         *
         * @param   string $category
         * @param   array  $path_list    The subdirectory to limit to.
         * 
         * @returns array  List of directories
         */
        public function removePaths($category, $path_list) {
            foreach ($path_list as $path) {
                $this->removePath($category,$path);
            }
        }

        public function loadPaths($category) {
            if (!(array_key_exists($category,$this->ordered_paths))) {                 
                $factory = I2CE_ModuleFactory::instance();
                $factory->loadPaths(null,$category,false,$this);
            }
        }

        /**
         * Find a file (or directory) of a certain category
         *
         * @param string $category  the category of the file
         * @param string $filename the file name of the file we wish to find
         * @param boolean $find_all Defaults to false
         *
         * @returns mixed.  Returns either a string which is the path
         * and file name of the file we found, or null if we did not
         * find the file, or an array of file names if $find_all was
         * set to true
         */
        public function search($category, $filename, $find_all = FALSE,$namespace = false) {
            if ($namespace) {
                $filename = implode(DIRECTORY_SEPARATOR,explode('\\',$filename));
            }
            if (!(array_key_exists($category,$this->ordered_paths))) {                 
                $factory = I2CE_ModuleFactory::instance();
                $factory->loadPaths(null,$category,false,$this);
            }
            $this->resetFoundLocales($find_all);
            $files = array();
            $ordered_paths = array();
            if (array_key_exists($category,$this->ordered_paths) &&
                is_array($this->ordered_paths[$category])) {
                $ordered_paths = $this->ordered_paths[$category];
            }
            foreach( $ordered_paths as $order=>$paths) {
                foreach ($paths as $path=>$locale) {                    
                    $t_filename = $path . DIRECTORY_SEPARATOR . $filename;
                    if (is_file($t_filename) && is_readable($t_filename)) {
                        $files[] = $t_filename;
                        if (!$find_all) {
                            $this->found_locales = $locale;
                            break 2;
                        } else {
                            $this->found_locales[] = $locale;
                        }
                    }
                }
            }
            if ($find_all) {
                return $files;
            } else {
                if (count($files) == 1)  {
                    return $files[0];
                } else {
                    return null;
                }
            }
        }


        /**
         * Reset the found locales
         * @param boolean $multiple.  If true setup so we are returning multiple resutls.  otherwise we return a single result
         */
        protected function resetFoundLocales($multiple) {
            if ($multiple) {
                $this->found_locales = array();
            } else {
                $this->found_locales = false;
            }
        }

        /**
         * Get the locale(s) of the results of the last search.
         * @returns mixed.  If the last search has $find_all = false then either false if no file was found or a string, the locale in which the
         * the results was found.  If the $find_all was true, then it is an array of string, the locales of the files found.
         */
        public function getLocaleOfLastSearch() {
            return $this->found_locales;
        }



        /**
         * Finds all files with a given glob in the category
         *
         * @param string $category. 
         *
         * @param mixed $globs.  It may be a string, in which case
         * it is a glob to match file names on.  It may
         * be an array of strings, them we attempt a match against
         * each of the globs specified by the strings.
         *
         * @param boolean $find_all.  If true we find all files,
         * otherwise we return on the first match
         *
         * @param string $dir_prefix any directory we want to prepend
         * onto the file name.
         *
         * @returns array of string
         */
        public function findByGlob($category, $globs, $find_all, $dir_prefix = '') { 
            $this->resetFoundLocales($find_all);
            if (is_string($globs )) {
                $globs = array($globs);
            }

            $found_files = array();
            $this->checked_directories = array();
            //possiblly check the current working directory first
            if (($this->search_cwd)) {
                $found_files = $this->searchPathsByGlob($find_all, $globs, array("."), $dir_prefix);
            }

            if (isset($this->ordered_paths[$category])) {
                foreach ($this->ordered_paths[$category] as $paths) {
                    $ff = $this->searchPathsByGlob($find_all, $globs, $paths, $dir_prefix);
                    if(!is_array($ff) && !$find_all) {
                        return $ff;
                    }
                    else {
                        $found_files = array_merge($found_files, $ff);
                    }
                }
            }
            return $found_files;
        }


        /**
         * Finds all files with a given extension in the category
         *
         * @param string $category. 
         *
         * @param mixed $regExps.  It may be a string, in which case
         * it is a regular expression to match file names on.  It may
         * be an array of strings, them we attempt a match against
         * each of the regular expressions specified by the strings.
         *
         * @param boolean $find_all.  If true we find all files,
         * otherwise we return on the first match
         *
         * @param string $dir_prefix any directory we want to prepend
         * onto the file name.
         *
         * @returns array of string
         */
        public function findByRegularExpression($category, $regExps,
                                                $find_all, $dir_prefix = '') { 
            $this->resetFoundLocales($find_all);
            if (is_string($regExps )) {
                $regExps = array($regExps);
            }

            $found_files = array();
            $this->checked_directories = array();
            //possiblly check the current working directory first
            if (($this->search_cwd)) {
                $found_files = $this->searchPaths($find_all, $regExps, array("."), $dir_prefix);
            }

            if (isset($this->ordered_paths[$category])) {
                foreach ($this->ordered_paths[$category] as $paths) {
                    $ff = $this->searchPaths($find_all, $regExps, $paths, $dir_prefix);
                    if(!is_array($ff) && !$find_all) {
                        return $ff;
                    }
                    else {
                        $found_files = array_merge($found_files, $ff);
                    }
                }
            }
            return $found_files;
        }

        /**
         * Search paths for matches to the glob
         *
         * @param   boolean $find_all  FALSE = return only the first hit.
         * @param   array   $globs the globs
         * @param   array   $paths     Paths to look in
         * @param   string  $prefix    Directory prefix
         * 
         * @returns array  List of directories
         */
        public function searchPathsByGlob($find_all, $globs, $paths, $prefix) {
            $found_files = array();

            if ($find_all) {
                $found = $this->_resolveGlob($globs,$paths,TRUE,$prefix, FALSE);
                foreach ( $found as $file) {
                    $found_files[] = $file;
                }
            } else {
                $found_file = $this->_resolveGlob($globs,$paths,FALSE,$prefix, FALSE);
                if ($found_file !== null) {
                    return $found_file;
                }
            }
            return $found_files;
        }




        /**
         * Search paths for matches to the regular expression
         *
         * @param   boolean $find_all  FALSE = return only the first hit.
         * @param   array   $regExps   Regular expressions to match
         * @param   array   $paths     Paths to look in
         * @param   string  $prefix    Directory prefix
         * 
         * @returns array  List of directories
         */
        public function searchPaths($find_all, $regExps, $paths, $prefix) {
            $found_files = array();

            if ($find_all) {
                $found = $this->_resolve($regExps,$paths,TRUE,$prefix, FALSE);
                foreach ( $found as $file) {
                    $found_files[] = $file;
                }
            } else {
                $found_file = $this->_resolve($regExps,$paths,FALSE,$prefix, FALSE);
                if ($found_file !== null) {
                    return $found_file;
                }
            }

            return $found_files;
        }


        /**
         * Remove sub-directories from a category
         *
         * @param   string $category
         * @param   string $limit        The subdirectory to limit to.
         * 
         * @returns array  List of directories
         */
        public function limitToSubdir($category, $limit = "") {
            $ordered_paths = $this->getSearchPath($category);
            $ret = array();
            foreach ($ordered_paths as $order => $paths) {
                foreach ($paths as $p) {
                    if ($limit &&
                        substr($p, 0, strlen($limit)) !== $limit) {
                        $this->removePath($category, $p);
                    } else {
                        $ret[$p] = TRUE;
                    }
                }
            }
            return $ret;
        }

        

        /**
         * Function to search through a list of paths to find a specified file.
         *
         * @param array $regExps.  If desired_file is null then, then
         * $regExp is a list of regular expression to match file names
         * on.
         *
         * @param array $paths of string.  The keys are the paths we
         * want to search, the values are the locale they are in
         *
         * @param boolean $find_all.  If true we find all files,
         * otherwise we return on the first match
         *
         * @param string $dir_prefix any directory we want to prepend
         * onto the file name.
         *
         * @param boolean $reset_found_locales. Defualts to false
         */ 
        public function resolve($regExps,$paths,$find_all,$dir_prefix = '', $reset_found_locales = false) { 
            $this->checked_directories = array();
            return $this->_resolve($regExps,$paths,$find_all,$dir_prefix, $reset_found_locales);
        }

        /**
         * Function to search through a list of paths to find a specified file by a glob
         *
         * @param array $globs.
         *
         * @param array $paths of string.  The keys are the paths we
         * want to search, the values are the locale they are in
         *
         * @param boolean $find_all.  If true we find all files,
         * otherwise we return on the first match
         *
         * @param string $dir_prefix any directory we want to prepend
         * onto the file name.
         *
         * @param boolean $reset_found_locales. Defualts to false
         */ 
        public function resolveGlob($globs,$paths,$find_all,$dir_prefix = '', $reset_found_locales = false) { 
            $this->checked_directories = array();
            return $this->_resolveGlob($glob,$paths,$find_all,$dir_prefix, $reset_found_locales);
        }

        /**
         * Function to search through a list of paths to find a specified file.
         *
         * @param array $regExps.  If desired_file is null then, then
         * $regExp is a list of regular expression to match file names
         * on.
         *
         * @param array $paths of string.  The keys are the paths we
         * want to search, the values are the locale they are in
         *
         * @param boolean $find_all.  If true we find all files,
         * otherwise we return on the first match
         *
         * @param string $dir_prefix any directory we want to prepend
         * onto the file name.
         *
         * @param boolean $reset_found_locales. Defualts to false
         */ 
        protected function _resolve($regExps,$paths,$find_all,$dir_prefix = '', $reset_found_locales = false) { 
            if (is_string($regExps)) {
                $regExps = array($regExps);
            }
            if(!is_array($regExps)) {
                I2CE::raiseError("Called without an array of RegExps.");
                if ($find_all) {
                    return array();
                } else {
                    return null;
                }
            }
            if ($reset_found_locales) {
                $this->resetFoundLocales($find_all);
            }
            if (count($paths) == 0) {
                //we go to the point where there were no paths to check so we stop.
                if ($find_all) {
                    return array();
                } else {
                    return null;
                }
            }
            $sub_directories = array(); 
            $found_files = array();

            foreach ($paths as $path=>$locale) {
                $path_len = strlen($path); 
                $recursive_search = false;
                $depth_one_search = false;
                if (($path_len >0) && ($path[$path_len-1] == '*')) { 
                    $dir_root = null;
                    //search subdirectories
                    if (($path_len > 1) && ($path[$path_len-2] == '*')) {
                        //search sub-directories recursively
                        $recursive_search =true;
                        $path = substr($path,0,-2);
                        $path_len -= 2;
                    } else {
                        $depth_one_search = true;
                        $path = substr($path,0,-1);
                        $path_len--;
                    }
                    $pos = strrpos($path,DIRECTORY_SEPARATOR); //find the last '/' (or '\' if windows)
                    if ($pos!=$path_len -1) {
                        //we dont want to  search all subdirectories,
                        // only subdirectories that begin with a certain string.
                        $dir_root = substr($path,$pos+1);
                    }
                    if (($recursive_search) && ( ($dir_root === null) || ( is_string($dir_root) && strlen($dir_root) == 0))) {
                        $real_path = realpath($path);
                        if (array_key_exists($real_path,$this->checked_directories) && $this->checked_directories[$real_path]) {
                            //we already checked this directory so skip processing on this path
                            continue;
                        } else {
                            //we haven't checked this directory.  Mark it as checked and continue
                            $this->checked_directories[$real_path] = true;
                        }
                    }
                    $curr_dir = substr($path,0,$pos);
                } else {
                    $curr_dir = $path;
                }
                $curr_dir .= DIRECTORY_SEPARATOR . $dir_prefix;
                //try to open the directory, supress error output
                if (!is_dir($curr_dir) || !is_readable($curr_dir)) {
                    continue;
                }
                if ( !($dh = @opendir($curr_dir))) {
                    //couldn't open it so skip this directory silently.
                    continue;
                }
                while ($file = readdir($dh)) {
                    if ($depth_one_search || $recursive_search) {
                        if (is_dir($curr_dir . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . '/.')) {
                            // the current file/link is a directory
                            //we see if we need to add this directory to the list of
                            //subdirectories to search
                            if (($file == '.') || ($file == '..')) {
                                continue;
                            }
                            if (($file[0] == '.') && (!$this->search_hidden)){
                                //hidden file so lets skip it
                                continue;
                            }
                            if ((isset($dir_root)) &&  (strpos($file,$dir_root)!==0)) {
                                //the directory does not begin with the directory root
                                //so skip it
                                continue;
                            }
                            if ($recursive_search) {
                                $file .= DIRECTORY_SEPARATOR . '**';
                            }
                            if ($curr_dir[strlen($curr_dir)-1] == DIRECTORY_SEPARATOR) {
                                $sub_directories[$curr_dir  . $file] = $locale;
                            } else {
                                $sub_directories[$curr_dir . DIRECTORY_SEPARATOR . $file] = $locale;
                            }
                        }
                    }
                    
                    if (is_file($curr_dir . DIRECTORY_SEPARATOR . $file )) {
                        //it is a file
                        //check to see if it is one of the extension we want,
                        foreach ( $regExps as $regExp) {
                            if (preg_match($regExp,$file)) {
                                if ($find_all) {
                                    $found_files[] = $curr_dir . DIRECTORY_SEPARATOR . $file;
                                    $this->found_locales[] = $locale;
                                } else {
                                    closedir($dh);
                                    $this->found_locales = $locale;
                                    return $curr_dir . DIRECTORY_SEPARATOR . $file;
                                }
                                //we no longer need to check against the regular expressions.
                                continue; 
                            }
                        }
                    }
                }
                closedir($dh);
            }
            //if we got here, we either did not find the file with $find_all = false
            // or $find_all is true
            //now search the sub-directory list
            if ($find_all) {
                $found = $this->_resolve($regExps,$sub_directories,$find_all, false);
                foreach ($found as $file) {
                    $found_files[] = $file;
                }
                return $found_files;
            } else {
                return  $this->_resolve($regExps,$sub_directories,$find_all, false);
            }
        }



        /**
         * Function to search through a list of paths to find a specified file.
         *
         * @param mixed $globs string or array of string
         *
         * @param array $paths of string.  The keys are the paths we
         * want to search, the values are the locale they are in
         *
         * @param boolean $find_all.  If true we find all files,
         * otherwise we return on the first match
         *
         * @param string $dir_prefix any directory we want to prepend
         * onto the file name.
         *
         * @param boolean $reset_found_locales. Defualts to false
         */ 
        protected function _resolveGlob($globs,$paths,$find_all,$dir_prefix = '', $reset_found_locales = false) { 
            if (is_string($globs)) {
                $globs = array($globs);
            }
            if(!is_array($globs)) {
                I2CE::raiseError("Called without an array of globs.");
                if ($find_all) {
                    return array();
                } else {
                    return null;
                }
            }
            if ($reset_found_locales) {
                $this->resetFoundLocales($find_all);
            }
            if (count($paths) == 0) {
                //we go to the point where there were no paths to check so we stop.
                if ($find_all) {
                    return array();
                } else {
                    return null;
                }
            }
            $sub_directories = array(); 
            $found_files = array();

            foreach ($paths as $path=>$locale) {
                $path_len = strlen($path); 
                $recursive_search = false;
                $depth_one_search = false;
                if (($path_len >0) && ($path[$path_len-1] == '*')) { 
                    $dir_root = null;
                    //search subdirectories
                    if (($path_len > 1) && ($path[$path_len-2] == '*')) {
                        //search sub-directories recursively
                        $recursive_search =true;
                        $path = substr($path,0,-2);
                        $path_len -= 2;
                    } else {
                        $depth_one_search = true;
                        $path = substr($path,0,-1);
                        $path_len--;
                    }
                    $pos = strrpos($path,DIRECTORY_SEPARATOR); //find the last '/' (or '\' if windows)
                    if ($pos!=$path_len -1) {
                        //we dont want to  search all subdirectories,
                        // only subdirectories that begin with a certain string.
                        $dir_root = substr($path,$pos+1);
                    }
                    if (($recursive_search) && ( ($dir_root === null) || ( is_string($dir_root) && strlen($dir_root) == 0))) {
                        $real_path = realpath($path);
                        if (array_key_exists($real_path,$this->checked_directories) && $this->checked_directories[$real_path]) {
                            //we already checked this directory so skip processing on this path
                            continue;
                        } else {
                            //we haven't checked this directory.  Mark it as checked and continue
                            $this->checked_directories[$real_path] = true;
                        }
                    }
                    $curr_dir = substr($path,0,$pos);
                } else {
                    $curr_dir = $path;
                }
                $curr_dir .= DIRECTORY_SEPARATOR . $dir_prefix;
                $curr_dir = rtrim($curr_dir,DIRECTORY_SEPARATOR);

                //try to open the directory, supress error output
                if (!is_dir($curr_dir) || !is_readable($curr_dir)) {
                    continue;
                }
                

                foreach ($globs as $glob) {
                    if (!is_array($files = glob($curr_dir . DIRECTORY_SEPARATOR . $glob, GLOB_NOSORT))) {
                        continue;
                    }
                    foreach ($files as $file) {
                        if (!is_file( $file ) || !is_readable($file)) {
                            continue;
                        }
                        if ($find_all) {
                            $found_files[] = $file;
                            $this->found_locales[] = $locale;
                        } else {
                            $this->found_locales = $locale;
                            return $file;
                        }
                    }
                }
                if ($recursive_search || $depth_one_search) {
                    if (is_array($t_sub_directories = glob($curr_dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR | GLOB_NOSORT))) {
                        foreach ($t_sub_directories as $t_sub_directory) {
                            $file = basename($t_sub_directory);
                            if (!$this->search_hidden && $file[0] == '.') {
                                continue;
                            }
                            if ((isset($dir_root)) &&  (strpos($file,$dir_root)!==0)) {
                                //the directory does not begin with the directory root
                                //so skip it
                                continue;
                            }
                            if ($recursive_search) {
                                $t_sub_directory .= DIRECTORY_SEPARATOR . '**';
                            }
                            $sub_directories[$t_sub_directory] = $locale;
                        }
                    }
                }

            }
            //if we got here, we either did not find the file with $find_all = false
            // or $find_all is true
            //now search the sub-directory list

            if ($find_all) {
                return $found_files + $this->_resolveGlob($globs,$sub_directories,true, false);
            } else {
                return  $this->_resolveGlob($globs,$sub_directories,false, false);
            }
        }
        
        


    }
}

# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
