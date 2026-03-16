<?php



/**
 * class that will dump out a requested file that is found
 * using a I2CE_FileSearch
 *
 * It recogonizes the following GET REQUEST Variables
 * <ul>
 *    <li>name  -- (Required) the name the file </li>
 *    <li>cat  -- (Required) the category of the file as registered with I2CE_FileSearch</li>
 *    <li>content -- (Optional) If set, it will be the content-type: for the header.  It will overide the following options: </li>
 *    <li>ext  -- (Optional) If set, it will be the extension used to determine the mime/type/content type for the file.
 *                 Useful for misnamed files</li>
 *    <li>apdContent -- (Optional) A string to append to the content-type.  Useful, for example, if it is a text file, somefile.txt,
 *         but you would like to specify the character set.</li>
 * </ul>
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
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
 * @version 0.1
 * @access public
 */


class I2CE_FileDump extends I2CE_Page{
    /**
     *protected  @var array $default_categories -- an array of default categories
     *for file extensions.
     */
    protected $default_categories;


    /**
     * Constructor method.
     */
    public function __construct($args,$request_remainder) {
        parent::__construct($args,array());
        $this->request_remainder = $request_remainder;
        $config = I2CE::getConfig()->modules->FileDump;
        if ($config->is_parent('defaultCategories')) {
            foreach ($config->defaultCategories as $cat=>$exts) {
                if (!$exts instanceof I2CE_MagicDataNode) {
                    continue;
                }
                foreach ($exts as $ext) {
                    $this->default_categories[$ext] = $cat;
                }
                
            }
        }
        $this->allowedCategories = array();
        if (isset($config->limitToCategories)) {
            $this->allowedCategories = $config->limitToCategories->getAsArray();                       
        }
    }



    /**
     * Handles creating hte I2CE_TemplateMeister templates and loading any default templates
     * @returns boolean true on success
     */
    protected function initializeTemplate() {
        //we don't want any tempaltes for this
        return true;
    }
    



    /**
     * Function to return the proper header type from a file's extension
     * some of the code extracted gratefully from  http://us3.php.net/manual/en/function.fread.php#72716
     * @param string $file the file''s name
     * @param string $ext the file''s (possible forced) extension -- lower case.  If null/empty it is not used.
     * @param string $mime_type  the file''s mime type.  Defaults to null.  
     * Will be overidden if $ext is not empty 
     * @returns array of string. the headers;
     */ 
    public function doHeader($file,$ext,$content,$apdContent) {
        $headers = array();
        if ( !($content)) {
            if ($ext) {
                $content = I2CE_MimeTypes::extToMimeType($ext); 
            }
            if (!$content) {
                I2CE::raiseError("No mime type identified for ({$file})", E_USER_NOTICE);
                $content = "application/force-download";   
            }
        }
        if ($apdContent) {
            $content .= "; " . $apdContent;
        }
        $headers[] = "Content-Type: " . $content;
        $headers[] = "Content-disposition: inline; filename=\"{$file}\"";
        return $headers;
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
        if ( $_SERVER['REQUEST_METHOD'] != "GET" && $_SERVER['REQUEST_METHOD'] != "HEAD" ) {
            //do nothing if it is not a GET request
            return; 
        }
        //              print_r($this->request_remainder);
        if (count($this->request_remainder) > 0) {
            $vars = array('name'=>implode(DIRECTORY_SEPARATOR,$this->request_remainder));
        } else if ($this->get_exists('encoded')) {
            $pairs = explode('&',$this->get('encoded'));
            $vars = array();
            foreach ($pairs as $pair) {
                list($key,$value) = explode('=',$pair);
                if (empty($value)) {
                    $value = true;
                }
                $vars[$key] = $value;
            }
        } else {
            $vars = $this->get;
        }
        $this->dump($vars);
    }





        
    protected function dump($vars) {
        $file_name = $vars['name'];
        if (empty($file_name)) {
            //do nothing if name is not set
            I2CE::raiseError("No file specified", E_USER_NOTICE);
            return;
        }
        //get the extension
        if (array_key_exists('ext',$vars)) {
            $ext = strtolower($vars['ext']);
        } else {
            $ext =  strtolower(substr(strrchr($file_name, "."), 1));
        }
        $category = null;
        if (array_key_exists('cat',$vars)) {
            $category = $vars['cat'];
        }
        if (empty($category)) {
            //try to see if we have a default category
            if (array_key_exists($ext,$this->default_categories)) {
                $category = $this->default_categories[$ext];
            }
            if (empty($category)) {
                //do nothing if no category found
                I2CE::raiseError("No file category specified for ({$file_name}). Valid categories are:" .print_r($this->default_categories,true), E_USER_NOTICE);
                I2CE::raiseError(print_r($this->default_categories,true));
                return;
            }
        }
        if (!in_array($category,$this->allowedCategories)) {
            //we are not allowed to search this category
            I2CE::raiseError("Not allowed to search category ($category).  Allowed are:\n" . print_r($this->allowedCategories,true), E_USER_NOTICE);
            return;
        }
        $file_loc = I2CE::getFileSearch()->search($category,$file_name);
        $locale = I2CE::getFileSearch()->getLocaleOfLastSearch();
        if (!($file_loc)) {
            //do nothing if we can't find the file
            I2CE::raiseError("Cannot find ({$file_name}). Search category is $category , Path is:\n"
                             . print_r(I2CE::getFileSearch()->getSearchPath($category), true), E_USER_NOTICE );
            return;
        }
        if (!array_key_exists('apdContent',$vars)) {
            $vars['apdContent'] = null;
        }
        if (!array_key_exists('content',$vars)) {
            $vars['content'] = null;
        }
        //$headers = $this->doHeader($file_name,$file_loc,$ext,$vars['content'],$vars['apdContent']);
        $headers = $this->doHeader($file_name,$ext,$vars['content'],$vars['apdContent']);
        $config = I2CE::getConfig();
        $cacheTime = 600;  // defaults to 10 minutes
        if (isset($config->modules->FileDump->cache_time)) {                    
            $cacheTime = $config->modules->FileDump->cache_time * 60; 
        }
        $ttl = 3600;  // defaults to one hour
        if ($config->is_scalar("/modules/FileDump/ttl")) {                    
            $ttl = $config->modules->FileDump->ttl * 60;
        }
        if (I2CE_Dumper::dumpContents($file_loc, $headers,   $cacheTime)) {
            I2CE_Dumper::cacheFileLocation(I2CE_PDO::details('dbname'), $file_name, $locale ,$headers,$file_loc, $cacheTime, $ttl);
        }
    }

        
}






# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
